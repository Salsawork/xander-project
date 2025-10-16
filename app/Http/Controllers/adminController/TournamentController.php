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
        $query = Tournament::with('event');

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $query->whereNotNull('event_id');
        $tournaments = $query->orderBy('created_at', 'desc')->get();

        return view('dash.admin.tournament.index', compact('tournaments'));
    }

    public function show(Tournament $tournament)
    {
        return redirect()->route('events.show', ['event' => $tournament->id]);
    }

    public function create()
    {
        $events = Event::orderBy('start_date', 'desc')->get();
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
            $tournamentData = [
                'name' => $data['name'],
                'user_id' => auth()->id(),
                'slug' => uniqid() . '-' . time(),
                'dateIni' => now(),
                'dateFin' => now()->addDays(7),
                'event_id' => $data['event_id'],
            ];

            $tournament = Tournament::create($tournamentData);

            $championship = $tournament->championships()->create([
                'name' => $tournament->name . ' Championship',
                'category_id' => 1,
            ]);

            $event = Event::find($data['event_id']);
            if ($event) {
                $finalsFormat = $data['treeType'] == 1 ? 'Single Elimination' : 'Playoff';

                $event->update([
                    'tournament_id' => $tournament->id,
                    'name' => $tournament->name,
                    'finals_format' => $finalsFormat,
                ]);
            }

            $numFighters = (int) $data['numFighters'];
            $isTeam = (int) ($data['isTeam'] ?? 0);

            $championship = $this->provisionObjects($request, $isTeam, $numFighters, $tournament);

            $generation = $championship->chooseGenerationStrategy();
            $generation->run();

            $this->generateBracketsFromChampionship($event, $championship);

            DB::commit();

            return redirect()->route('tournament.edit', $tournament->slug)
                ->with('success', 'Tournament dan Bracket berhasil dibuat!');
        } catch (TreeGenerationException $e) {
            DB::rollBack();
            return redirect()->back()
                ->withInput()
                ->withErrors('Gagal generate bracket: ' . $e->getMessage());
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->withInput()
                ->withErrors('Error: ' . $e->getMessage());
        }
    }

    public function edit(Tournament $tournament)
    {
        $tournament->load(
            'competitors',
            'championships.settings',
            'championships.category'
        );

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
            $tournament->update([
                'name' => $data['name'],
                'event_id' => $data['event_id'],
            ]);

            $this->deleteEverything($championship->id);

            $numFighters = $request->numFighters;
            $isTeam = $request->isTeam ?? 0;

            $championship = $this->provisionObjects($request, $isTeam, $numFighters, $tournament);
            $generation = $championship->chooseGenerationStrategy();
            $generation->run();

            $event = Event::find($data['event_id']);
            if ($event) {
                $finalsFormat = $request->treeType == 1 ? 'Single Elimination' : 'Playoff';

                $event->update([
                    'tournament_id' => $tournament->id,
                    'name' => $request->name,
                    'finals_format' => $finalsFormat,
                ]);

                $this->generateBracketsFromChampionship($event, $championship);
            }

            DB::commit();

            return back()
                ->with('success', 'Tournament updated successfully!')
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
     * Generate brackets dari championship - FINAL ONLY
     */
    private function generateBracketsFromChampionship(Event $event, Championship $championship)
    {
        Bracket::where('event_id', $event->id)->delete();

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
        $finalRound = $totalRounds;

        // Generate semua round KECUALI final
        foreach ($fightersGroups as $roundNumber => $groups) {
            if ($roundNumber == $finalRound) {
                continue;
            }

            $position = 1;

            foreach ($groups as $group) {
                $fighters = $group->getFightersWithBye();

                foreach ($fighters as $fighter) {
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

        // Create FINAL dengan HANYA 2 pemain dari semifinal winners
        $this->createFinalBrackets($event, $championship, $finalRound);

        // Update winners dari fight results
        $this->updateWinnersFromFights($event, $championship);
    }

    /**
     * Create final brackets dengan HANYA 2 pemain - TANPA 3rd place
     */
    private function createFinalBrackets(Event $event, Championship $championship, int $finalRound)
    {
        $semifinalRound = $finalRound - 1;

        $semifinalGroups = $championship->fightersGroups()
            ->where('round', $semifinalRound)
            ->with('fights')
            ->get();

        $semifinalWinners = [];

        foreach ($semifinalGroups as $group) {
            $fight = $group->fights->first();

            if (!$fight) {
                continue;
            }

            if ($fight->winner_id) {
                $winner = $this->getFighterById($fight->winner_id, $championship);

                if ($winner) {
                    $semifinalWinners[] = [
                        'id' => $fight->winner_id,
                        'name' => $winner->fullName ?? $winner->name ?? 'Unknown',
                    ];
                }
            }
        }

        // Jika ada 2 pemenang semifinal, buat final
        if (count($semifinalWinners) === 2) {
            $finalGroup = $championship->fightersGroups()
                ->where('round', $finalRound)
                ->with('fights')
                ->first();

            $finalWinnerId = null;
            if ($finalGroup && $finalGroup->fights->first()) {
                $finalWinnerId = $finalGroup->fights->first()->winner_id;
            }

            // Finalist 1
            Bracket::create([
                'event_id' => $event->id,
                'player_name' => $semifinalWinners[0]['name'],
                'round' => $finalRound,
                'position' => 1,
                'next_match_position' => null,
                'is_winner' => ($finalWinnerId == $semifinalWinners[0]['id']),
            ]);

            // Finalist 2
            Bracket::create([
                'event_id' => $event->id,
                'player_name' => $semifinalWinners[1]['name'],
                'round' => $finalRound,
                'position' => 2,
                'next_match_position' => null,
                'is_winner' => ($finalWinnerId == $semifinalWinners[1]['id']),
            ]);
        } else {
            // TBD placeholders
            Bracket::create([
                'event_id' => $event->id,
                'player_name' => 'TBD',
                'round' => $finalRound,
                'position' => 1,
                'next_match_position' => null,
                'is_winner' => false,
            ]);

            Bracket::create([
                'event_id' => $event->id,
                'player_name' => 'TBD',
                'round' => $finalRound,
                'position' => 2,
                'next_match_position' => null,
                'is_winner' => false,
            ]);
        }
    }

    private function getFighterById($fighterId, $championship)
    {
        $competitor = Competitor::where('championship_id', $championship->id)
            ->where('id', $fighterId)
            ->first();

        if ($competitor) {
            return $competitor;
        }

        return Team::where('championship_id', $championship->id)
            ->where('id', $fighterId)
            ->first();
    }

    private function updateWinnersFromFights(Event $event, Championship $championship)
    {
        $fights = $championship->fights()
            ->whereNotNull('winner_id')
            ->with('winner')
            ->get();

        foreach ($fights as $fight) {
            if ($fight->winner) {
                $winnerName = $fight->winner->fullName ?? $fight->winner->name;

                Bracket::where('event_id', $event->id)
                    ->where('player_name', $winnerName)
                    ->where('round', $fight->round ?? 1)
                    ->update(['is_winner' => true]);
            }
        }
    }

    private function deleteEverything($championshipId)
    {
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