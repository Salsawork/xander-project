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

    private function generateCompleteStructure($event, $championship)
    {
        // Hapus bracket lama
        Bracket::where('event_id', $event->id)->delete();

        // Hitung total rounds dari jumlah pemain di round 1
        $allGroups = $championship->fightersGroups()
            ->where('round', '>=', 1)
            ->with('fights')
            ->orderBy('round')
            ->orderBy('order')
            ->get();

        if ($allGroups->isEmpty()) {
            return;
        }

        // Cari jumlah pemain di round 1
        $round1Groups = $allGroups->where('round', 1);
        $totalPlayers = $round1Groups->count() * 2; // setiap group punya 2 fighters
        $maxRound = (int) ceil(log($totalPlayers, 2));

        \Log::info('Auto Generate Bracket Structure', [
            'total_players' => $totalPlayers,
            'max_rounds' => $maxRound,
            'championship_id' => $championship->id
        ]);

        // Generate SEMUA rounds
        for ($round = 1; $round <= $maxRound; $round++) {
            $matchesInRound = (int) ($totalPlayers / pow(2, $round));
            $position = 1;

            \Log::info('Creating round structure', [
                'round' => $round,
                'matches' => $matchesInRound
            ]);

            for ($matchNum = 1; $matchNum <= $matchesInRound; $matchNum++) {
                // Coba ambil data dari championship groups
                $group = $allGroups->where('round', $round)
                    ->where('order', $matchNum)
                    ->first();

                $player1Name = 'TBD';
                $player2Name = 'TBD';
                $isWinner1 = false;
                $isWinner2 = false;

                // Jika ada data group, ambil dari sana
                if ($group && $group->fights->isNotEmpty()) {
                    $fight = $group->fights->first();

                    // Fighter 1
                    if ($fight->c1) {
                        $fighter1 = $this->getFighterById($fight->c1, $championship);
                        $player1Name = $fighter1 ? $this->getPlayerName($fighter1) : 'TBD';
                        $isWinner1 = ($fight->winner_id == $fight->c1);
                    } else {
                        $player1Name = 'TBD';
                    }

                    // Fighter 2
                    if ($fight->c2) {
                        $fighter2 = $this->getFighterById($fight->c2, $championship);
                        $player2Name = $fighter2 ? $this->getPlayerName($fighter2) : 'TBD';
                        $isWinner2 = ($fight->winner_id == $fight->c2);
                    } else {
                        $player2Name = 'TBD';
                    }
                }

                // Buat bracket untuk player 1
                Bracket::create([
                    'event_id' => $event->id,
                    'round' => $round,
                    'position' => $position,
                    'player_name' => $player1Name,
                    'is_winner' => $isWinner1,
                    'next_match_position' => $round < $maxRound ? (int) ceil($position / 2) : null
                ]);

                $position++;

                // Buat bracket untuk player 2
                Bracket::create([
                    'event_id' => $event->id,
                    'round' => $round,
                    'position' => $position,
                    'player_name' => $player2Name,
                    'is_winner' => $isWinner2,
                    'next_match_position' => $round < $maxRound ? (int) ceil($position / 2) : null
                ]);

                $position++;
            }
        }

        // Summary
        $totalBrackets = Bracket::where('event_id', $event->id)->count();
        $byRound = Bracket::where('event_id', $event->id)
            ->selectRaw('round, count(*) as count')
            ->groupBy('round')
            ->get()
            ->pluck('count', 'round');

        \Log::info('Complete Bracket Structure Generated', [
            'event_id' => $event->id,
            'total_brackets' => $totalBrackets,
            'by_round' => $byRound
        ]);
    }
    private function getPlayerName($fighter)
    {
        if (!$fighter) return 'TBD';

        if (isset($fighter->fullName)) {
            return $fighter->fullName;
        }

        if (isset($fighter->name)) {
            return $fighter->name;
        }

        if (isset($fighter->user) && isset($fighter->user->name)) {
            return $fighter->user->name;
        }

        return 'Unknown';
    }

    private function propagateWinnersInBracket($event, $championship)
    {
        $maxRound = Bracket::where('event_id', $event->id)->max('round');

        // Loop setiap round (kecuali final)
        for ($round = 1; $round < $maxRound; $round++) {
            // Ambil semua pemenang di round ini
            $winners = Bracket::where('event_id', $event->id)
                ->where('round', $round)
                ->where('is_winner', true)
                ->where('player_name', '!=', 'TBD')
                ->get();

            foreach ($winners as $winner) {
                if ($winner->next_match_position) {
                    // Tentukan apakah pemenang ini di posisi ganjil atau genap
                    // Posisi ganjil = player 1, genap = player 2
                    $isFirstPosition = ($winner->position % 2 == 1);

                    // Di round berikutnya, cari slot yang sesuai
                    $nextRound = $round + 1;
                    $nextMatches = Bracket::where('event_id', $event->id)
                        ->where('round', $nextRound)
                        ->where('next_match_position', '=', $winner->next_match_position)
                        ->orderBy('position')
                        ->get();

                    if ($nextMatches->count() >= 2) {
                        $targetPosition = $isFirstPosition ? 0 : 1;
                        $targetBracket = $nextMatches[$targetPosition] ?? null;

                        if ($targetBracket && $targetBracket->player_name === 'TBD') {
                            $targetBracket->update([
                                'player_name' => $winner->player_name
                            ]);

                            \Log::info('Winner propagated to next round', [
                                'from_round' => $round,
                                'from_position' => $winner->position,
                                'to_round' => $nextRound,
                                'to_position' => $targetBracket->position,
                                'player' => $winner->player_name
                            ]);
                        }
                    }
                }
            }
        }
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
