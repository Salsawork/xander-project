<?php

namespace App\Http\Controllers\adminController;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use App\Models\Bracket;
use Illuminate\Support\Facades\DB;
use Xoco70\LaravelTournaments\Exceptions\TreeGenerationException;
use Xoco70\LaravelTournaments\Models\Championship;
use Xoco70\LaravelTournaments\Models\ChampionshipSettings;
use Xoco70\LaravelTournaments\Models\Competitor;
use Xoco70\LaravelTournaments\Models\Team;
use Xoco70\LaravelTournaments\Models\Tournament;
use App\Models\Event;


class TournamentController extends Controller
{
    public function index(Request $request)
    {
        $query = Tournament::with('event'); // panggil relasi event

        // Jika ada parameter search, filter berdasarkan nama tournament
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        // Hanya ambil tournament yang punya event_id
        $query->whereNotNull('event_id');

        // Urutkan data terbaru
        $tournaments = $query->orderBy('created_at', 'desc')->get();

        return view('dash.admin.tournament.index', compact('tournaments'));
    }

    public function show(Tournament $tournament)
    {
        return redirect()->route('events.show', ['event' => $tournament->id]);
    }

    public function create()
    {
        // Ambil semua events yang tersedia
        $events = Event::orderBy('start_date', 'desc')->get();

        // Show the form to create a new tournament
        return view('dash.admin.tournament.create', compact('events'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'hasPreliminary' => 'required',
            'preliminaryGroupSize' => 'required',
            'numFighters' => 'required|integer|min:2|max:64',
            'isTeam' => 'required',
            'treeType' => 'required',
            'fightingAreas' => 'required',
            'event_id' => 'required|exists:events,id',
        ]);

        DB::beginTransaction();

        try {
            // 1️⃣ Buat Tournament dengan event_id
            $tournamentData = [
                'name' => $data['name'],
                'user_id' => auth()->id(),
                'slug' => uniqid() . '-' . time(),
                'dateIni' => now(),
                'dateFin' => now()->addDays(7),
                'event_id' => $data['event_id'],
            ];

            $tournament = Tournament::create($tournamentData);

            // 2️⃣ Buat Championship
            $championship = $tournament->championships()->create([
                'name' => $tournament->name . ' Championship',
                'category_id' => 1,
            ]);

            // 3️⃣ Update Event yang sudah ada dengan tournament_id
            $event = Event::find($data['event_id']);
            if ($event) {
                $finalsFormat = $data['treeType'] == 1 ? 'Single Elimination' : 'Playoff';

                $event->update([
                    'tournament_id' => $tournament->id,
                    'name' => $tournament->name,
                    'finals_format' => $finalsFormat,
                ]);
            }

            // 4️⃣ Generate fighters/competitors dan bracket tree
            $numFighters = (int) $data['numFighters'];
            $isTeam = (int) ($data['isTeam'] ?? 0);

            // Provision fighters
            $championship = $this->provisionObjects($request, $isTeam, $numFighters, $tournament);

            // Generate bracket tree dari championship
            $generation = $championship->chooseGenerationStrategy();
            $generation->run();

            // 5️⃣ Generate brackets untuk event dari championship yang sudah ada
            $this->generateBracketsFromChampionship($event, $championship);

            DB::commit();

            return redirect()->route('tournament.edit', $tournament->slug)
                ->with('success', 'Tournament dan Bracket berhasil dibuat dan terhubung dengan Event!');
        } catch (TreeGenerationException $e) {
            DB::rollBack();
            return redirect()->back()
                ->withInput()
                ->withErrors('Gagal generate bracket: ' . $e->getMessage());
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->withInput()
                ->withErrors('Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function edit(Tournament $tournament)
    {
        $tournament->load(
            'competitors',
            'championships.settings',
            'championships.category'
        );

        // Ambil semua events yang tersedia
        $events = Event::orderBy('start_date', 'desc')->get();

        return view('dash.admin.tournament.edit', compact('tournament', 'events'));
    }

    public function update(Tournament $tournament, Championship $championship, Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'hasPreliminary' => 'required',
            'preliminaryGroupSize' => 'required',
            'numFighters' => 'required',
            'isTeam' => 'required',
            'treeType' => 'required',
            'fightingAreas' => 'required',
            'event_id' => 'required|exists:events,id',
        ]);

        DB::beginTransaction();

        try {
            // Update tournament data including event_id
            $tournament->update([
                'name' => $data['name'],
                'event_id' => $data['event_id'],
            ]);

            // Delete existing bracket data
            $this->deleteEverything($championship->id);

            $numFighters = $request->numFighters;
            $isTeam = $request->isTeam ?? 0;

            // Re-generate championship data
            $championship = $this->provisionObjects($request, $isTeam, $numFighters, $tournament);
            $generation = $championship->chooseGenerationStrategy();
            $generation->run();

            // Update atau ambil event
            $event = Event::find($data['event_id']);
            if ($event) {
                $finalsFormat = $request->treeType == 1 ? 'Single Elimination' : 'Playoff';

                // Update event dengan data tournament
                $event->update([
                    'tournament_id' => $tournament->id,
                    'name' => $request->name,
                    'finals_format' => $finalsFormat,
                ]);

                // Re-generate brackets untuk event
                $this->generateBracketsFromChampionship($event, $championship);
            }

            DB::commit();

            return back()
                ->with('success', 'Tournament, Event, dan Bracket berhasil di-update!')
                ->with('numFighters', $numFighters)
                ->with('isTeam', $isTeam);
        } catch (TreeGenerationException $e) {
            DB::rollBack();
            return redirect()->back()
                ->withErrors($e->getMessage());
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->withErrors('Error: ' . $e->getMessage());
        }
    }

    public function destroy(Tournament $tournament)
    {
        $tournament->delete();

        return redirect()->route('tournament.index')
            ->with('success', 'Tournament deleted successfully');
    }

    /**
     * Generate brackets dari championship fighters ke dalam tabel brackets
     */
    private function generateBracketsFromChampionship(Event $event, Championship $championship)
    {
        // Hapus bracket lama jika ada
        Bracket::where('event_id', $event->id)->delete();

        // Ambil semua fighters groups yang sudah di-generate
        $fightersGroups = $championship->fightersGroups()
            ->where('round', '>=', 1)
            ->orderBy('round')
            ->orderBy('order')
            ->get()
            ->groupBy('round');

        if ($fightersGroups->isEmpty()) {
            return;
        }

        $totalRounds = $fightersGroups->count();

        // Generate brackets untuk setiap round
        foreach ($fightersGroups as $roundNumber => $groups) {
            $position = 1;

            foreach ($groups as $group) {
                $fighters = $group->getFightersWithBye();

                foreach ($fighters as $fighter) {
                    // Calculate next match position
                    $nextMatchPosition = $roundNumber < $totalRounds
                        ? (int) ceil($position / 2)
                        : null;

                    Bracket::create([
                        'event_id' => $event->id,
                        'player_name' => $fighter ? ($fighter->fullName ?? 'BYE') : 'TBD',
                        'round' => $roundNumber,
                        'position' => $position,
                        'next_match_position' => $nextMatchPosition,
                        'is_winner' => false,
                    ]);

                    $position++;
                }
            }
        }

        // Update winners berdasarkan fight results jika ada
        $this->updateWinnersFromFights($event, $championship);
    }

    /**
     * Update winners berdasarkan fight results
     */
    private function updateWinnersFromFights(Event $event, Championship $championship)
    {
        $fights = $championship->fights()
            ->whereNotNull('winner_id')
            ->with('winner')
            ->get();

        foreach ($fights as $fight) {
            if ($fight->winner) {
                $winnerName = $fight->winner->fullName;

                Bracket::where('event_id', $event->id)
                    ->where('player_name', $winnerName)
                    ->where('round', $fight->round ?? 1)
                    ->update(['is_winner' => true]);
            }
        }
    }

    private function deleteEverything($championshipId)
    {
        // Get fighters groups and delete them
        $fightersGroups = DB::table('fighters_groups')->where('championship_id', $championshipId)->get();
        foreach ($fightersGroups as $fightersGroup) {
            DB::table('fight')->where('fighters_group_id', $fightersGroup->id)->delete();
            DB::table('fighters_group_competitor')->where('fighters_group_id', $fightersGroup->id)->delete();
            DB::table('fighters_group_team')->where('fighters_group_id', $fightersGroup->id)->delete();
        }
        DB::table('fighters_groups')->where('championship_id', $championshipId)->delete();
        DB::table('competitor')->where('championship_id', $championshipId)->delete();
        DB::table('team')->where('championship_id', $championshipId)->delete();
    }

    protected function provisionObjects(Request $request, $isTeam, $numFighters, Tournament $tournament)
    {
        if ($isTeam) {
            $championship = Championship::find($tournament->championships[1]->id);
            factory(Team::class, (int) $numFighters)->create(['championship_id' => $championship->id]);
        } else {
            $championship = Championship::find($tournament->championships[0]->id);
            $users = factory(User::class, (int) $numFighters)->create();
            foreach ($users as $user) {
                factory(Competitor::class)->create(
                    [
                        'championship_id' => $championship->id,
                        'user_id'      => $user->id,
                        'confirmed'    => 1,
                        'short_id'     => $user->id,
                    ]
                );
            }
        }
        $championship->settings = ChampionshipSettings::createOrUpdate($request, $championship);

        return $championship;
    }
}