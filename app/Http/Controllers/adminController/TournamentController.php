<?php

namespace App\Http\Controllers\adminController;

// NOTE: Complete Double Elimination Implementation
// CHANGES:
// - Removed duplicate generateCompleteStructure() method
// - Fixed all method implementations
// - Added proper double elimination bracket generation

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

        $validationError = $this->validateBracketSize((int) $data['numFighters']);
        if ($validationError) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['bracket_error' => $validationError]);
        }

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
                // MODIFIED: treeType 0 = Double Elimination, 1 = Single Elimination
                $finalsFormat = $data['treeType'] == 1 ? 'Single Elimination' : 'Double Elimination';

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

        $validationError = $this->validateBracketSize((int) $data['numFighters']);
        if ($validationError) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['bracket_error' => $validationError]);
        }

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
                // MODIFIED: treeType 0 = Double Elimination
                $finalsFormat = $request->treeType == 1 ? 'Single Elimination' : 'Double Elimination';

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
     * MAIN METHOD: Generate brackets from championship
     * Detects tournament type and calls appropriate method
     */
    private function generateBracketsFromChampionship(Event $event, Championship $championship)
    {
        Bracket::where('event_id', $event->id)->delete();

        $fightersGroups = $championship->fightersGroups()
            ->where('round', '>=', 1)
            ->with('fights')
            ->orderBy('round')
            ->orderBy('order')
            ->get();

        if ($fightersGroups->isEmpty()) {
            return;
        }

        // Check tournament type
        $settings = $championship->getSettings();
        $isDoubleElimination = ($settings->treeType == 0);

        \Log::info('Generating Brackets', [
            'event_id' => $event->id,
            'championship_id' => $championship->id,
            'is_double_elimination' => $isDoubleElimination,
            'total_groups' => $fightersGroups->count()
        ]);

        if ($isDoubleElimination) {
            // Generate double elimination structure
            $this->generateDoubleEliminationBrackets($event, $championship, $fightersGroups);
        } else {
            // Generate single elimination structure
            $this->generateSingleEliminationBrackets($event, $championship, $fightersGroups);
        }

        // Update winners from fight results
        $this->updateWinnersFromFights($event, $championship);
    }

    /**
     * ADDED: Generate double elimination bracket structure
     * Upper Bracket -> Lower Bracket -> Grand Final
     */
    private function generateDoubleEliminationBrackets($event, $championship, $allGroups)
    {
        $round1Groups = $allGroups->where('round', 1);
        $numFighters = $round1Groups->count() * 2;
        $numRounds = intval(log($numFighters, 2));

        // Calculate structure
        $upperBracketEnd = $numRounds + 1;
        $lowerBracketStart = $upperBracketEnd + 1;
        $maxRound = $allGroups->max('round');
        $grandFinalRound = $maxRound;

        \Log::info('Generating Double Elimination Brackets', [
            'fighters' => $numFighters,
            'round_1' => 1,
            'upper_bracket' => "2-{$upperBracketEnd}",
            'lower_bracket' => "{$lowerBracketStart}-" . ($grandFinalRound - 1),
            'grand_final' => $grandFinalRound
        ]);

        // ==========================================
        // ROUND 1 - All players
        // ==========================================
        $position = 1;
        $groups = $allGroups->where('round', 1);

        foreach ($groups as $group) {
            $fight = $group->fights->first();

            // Fighter 1
            $player1Name = 'TBD';
            $isWinner1 = false;
            if ($fight && $fight->c1) {
                $fighter1 = $this->getFighterById($fight->c1, $championship);
                $player1Name = $fighter1 ? $this->getPlayerName($fighter1) : 'TBD';
                $isWinner1 = ($fight->winner_id == $fight->c1);
            }

            Bracket::create([
                'event_id' => $event->id,
                'round' => 1,
                'position' => $position,
                'player_name' => $player1Name,
                'is_winner' => $isWinner1,
                'next_match_position' => (int) ceil($position / 2),
                'bracket_type' => 'round_1'
            ]);
            $position++;

            // Fighter 2
            $player2Name = 'TBD';
            $isWinner2 = false;
            if ($fight && $fight->c2) {
                $fighter2 = $this->getFighterById($fight->c2, $championship);
                $player2Name = $fighter2 ? $this->getPlayerName($fighter2) : 'TBD';
                $isWinner2 = ($fight->winner_id == $fight->c2);
            }

            Bracket::create([
                'event_id' => $event->id,
                'round' => 1,
                'position' => $position,
                'player_name' => $player2Name,
                'is_winner' => $isWinner2,
                'next_match_position' => (int) ceil($position / 2),
                'bracket_type' => 'round_1'
            ]);
            $position++;
        }

        // ==========================================
        // UPPER BRACKET - Winners path
        // ==========================================
        for ($round = 2; $round <= $upperBracketEnd; $round++) {
            $position = 1;
            $groups = $allGroups->where('round', $round);

            foreach ($groups as $group) {
                $fight = $group->fights->first();

                // Fighter 1
                $player1Name = 'TBD';
                $isWinner1 = false;
                if ($fight && $fight->c1) {
                    $fighter1 = $this->getFighterById($fight->c1, $championship);
                    $player1Name = $fighter1 ? $this->getPlayerName($fighter1) : 'TBD';
                    $isWinner1 = ($fight->winner_id == $fight->c1);
                }

                Bracket::create([
                    'event_id' => $event->id,
                    'round' => $round,
                    'position' => $position,
                    'player_name' => $player1Name,
                    'is_winner' => $isWinner1,
                    'next_match_position' => $round < $upperBracketEnd ? (int) ceil($position / 2) : null,
                    'bracket_type' => 'upper'
                ]);
                $position++;

                // Fighter 2
                $player2Name = 'TBD';
                $isWinner2 = false;
                if ($fight && $fight->c2) {
                    $fighter2 = $this->getFighterById($fight->c2, $championship);
                    $player2Name = $fighter2 ? $this->getPlayerName($fighter2) : 'TBD';
                    $isWinner2 = ($fight->winner_id == $fight->c2);
                }

                Bracket::create([
                    'event_id' => $event->id,
                    'round' => $round,
                    'position' => $position,
                    'player_name' => $player2Name,
                    'is_winner' => $isWinner2,
                    'next_match_position' => $round < $upperBracketEnd ? (int) ceil($position / 2) : null,
                    'bracket_type' => 'upper'
                ]);
                $position++;
            }
        }

        // ==========================================
        // LOWER BRACKET - Losers path
        // Starts right after upper bracket ends
        // ==========================================
        for ($round = $lowerBracketStart; $round < $grandFinalRound; $round++) {
            $position = 1;
            $groups = $allGroups->where('round', $round);

            foreach ($groups as $group) {
                $fight = $group->fights->first();

                // Fighter 1
                $player1Name = 'TBD';
                $isWinner1 = false;
                if ($fight && $fight->c1) {
                    $fighter1 = $this->getFighterById($fight->c1, $championship);
                    $player1Name = $fighter1 ? $this->getPlayerName($fighter1) : 'TBD';
                    $isWinner1 = ($fight->winner_id == $fight->c1);
                }

                Bracket::create([
                    'event_id' => $event->id,
                    'round' => $round,
                    'position' => $position,
                    'player_name' => $player1Name,
                    'is_winner' => $isWinner1,
                    'next_match_position' => $round < ($grandFinalRound - 1) ? (int) ceil($position / 2) : null,
                    'bracket_type' => 'lower'
                ]);
                $position++;

                // Fighter 2
                $player2Name = 'TBD';
                $isWinner2 = false;
                if ($fight && $fight->c2) {
                    $fighter2 = $this->getFighterById($fight->c2, $championship);
                    $player2Name = $fighter2 ? $this->getPlayerName($fighter2) : 'TBD';
                    $isWinner2 = ($fight->winner_id == $fight->c2);
                }

                Bracket::create([
                    'event_id' => $event->id,
                    'round' => $round,
                    'position' => $position,
                    'player_name' => $player2Name,
                    'is_winner' => $isWinner2,
                    'next_match_position' => $round < ($grandFinalRound - 1) ? (int) ceil($position / 2) : null,
                    'bracket_type' => 'lower'
                ]);
                $position++;
            }
        }

        // ==========================================
        // GRAND FINAL
        // ==========================================
        $grandFinalGroup = $allGroups->where('round', $grandFinalRound)->first();
        if ($grandFinalGroup) {
            $fight = $grandFinalGroup->fights->first();

            // Upper winner (c1)
            $player1Name = 'TBD';
            $isWinner1 = false;
            if ($fight && $fight->c1) {
                $fighter1 = $this->getFighterById($fight->c1, $championship);
                $player1Name = $fighter1 ? $this->getPlayerName($fighter1) : 'TBD';
                $isWinner1 = ($fight->winner_id == $fight->c1);
            }

            Bracket::create([
                'event_id' => $event->id,
                'round' => $grandFinalRound,
                'position' => 1,
                'player_name' => $player1Name,
                'is_winner' => $isWinner1,
                'next_match_position' => null,
                'bracket_type' => 'grand_final'
            ]);

            // Lower winner (c2)
            $player2Name = 'TBD';
            $isWinner2 = false;
            if ($fight && $fight->c2) {
                $fighter2 = $this->getFighterById($fight->c2, $championship);
                $player2Name = $fighter2 ? $this->getPlayerName($fighter2) : 'TBD';
                $isWinner2 = ($fight->winner_id == $fight->c2);
            }

            Bracket::create([
                'event_id' => $event->id,
                'round' => $grandFinalRound,
                'position' => 2,
                'player_name' => $player2Name,
                'is_winner' => $isWinner2,
                'next_match_position' => null,
                'bracket_type' => 'grand_final'
            ]);
        }

        \Log::info('Double Elimination Brackets Complete', [
            'total_brackets' => Bracket::where('event_id', $event->id)->count()
        ]);
    }

    /**
     * ADDED: Generate single elimination bracket structure
     */
    private function generateSingleEliminationBrackets($event, $championship, $allGroups)
    {
        $round1Groups = $allGroups->where('round', 1);
        $totalPlayers = $round1Groups->count() * 2;
        $maxRound = (int) ceil(log($totalPlayers, 2));

        \Log::info('Single Elimination Structure', [
            'total_players' => $totalPlayers,
            'max_rounds' => $maxRound
        ]);

        // Generate all rounds
        for ($round = 1; $round <= $maxRound; $round++) {
            $matchesInRound = (int) ($totalPlayers / pow(2, $round));
            $position = 1;

            for ($matchNum = 1; $matchNum <= $matchesInRound; $matchNum++) {
                $group = $allGroups->where('round', $round)
                    ->where('order', $matchNum)
                    ->first();

                $player1Name = 'TBD';
                $player2Name = 'TBD';
                $isWinner1 = false;
                $isWinner2 = false;

                if ($group && $group->fights->isNotEmpty()) {
                    $fight = $group->fights->first();

                    if ($fight->c1) {
                        $fighter1 = $this->getFighterById($fight->c1, $championship);
                        $player1Name = $fighter1 ? $this->getPlayerName($fighter1) : 'TBD';
                        $isWinner1 = ($fight->winner_id == $fight->c1);
                    }

                    if ($fight->c2) {
                        $fighter2 = $this->getFighterById($fight->c2, $championship);
                        $player2Name = $fighter2 ? $this->getPlayerName($fighter2) : 'TBD';
                        $isWinner2 = ($fight->winner_id == $fight->c2);
                    }
                }

                // Player 1
                Bracket::create([
                    'event_id' => $event->id,
                    'round' => $round,
                    'position' => $position,
                    'player_name' => $player1Name,
                    'is_winner' => $isWinner1,
                    'next_match_position' => $round < $maxRound ? (int) ceil($position / 2) : null
                ]);

                $position++;

                // Player 2
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

        $totalBrackets = Bracket::where('event_id', $event->id)->count();
        \Log::info('Single Elimination Brackets Generated', [
            'event_id' => $event->id,
            'total_brackets' => $totalBrackets
        ]);
    }

    /**
     * Helper: Get player name from fighter object
     */
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

    /**
     * Helper: Get fighter by ID (Competitor or Team)
     */
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

    /**
     * Update winners from fight results
     */
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

    /**
     * Delete all tournament data
     */
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

    /**
     * Create fighters/teams for tournament
     */
    protected function provisionObjects(Request $request, $isTeam, $numFighters, Tournament $tournament)
    {
        if ($isTeam) {
            $championship = Championship::find($tournament->championships[1]->id);
            factory(Team::class, (int) $numFighters)->create(['championship_id' => $championship->id]);
        } else {
            $championship = Championship::find($tournament->championships[0]->id);
            $users = factory(User::class, (int) $numFighters)->create();
            foreach ($users as $user) {
                factory(Competitor::class)->create([
                    'championship_id' => $championship->id,
                    'user_id'      => $user->id,
                    'confirmed'    => 1,
                    'short_id'     => $user->id,
                ]);
            }
        }
        $championship->settings = ChampionshipSettings::createOrUpdate($request, $championship);

        return $championship;
    }

    /**
     * Validate bracket size
     */
    private function validateBracketSize($numFighters, $actualPlayersCount = null)
    {
        if ($actualPlayersCount !== null) {
            if ($actualPlayersCount > $numFighters) {
                return "Jumlah pemain terdaftar ({$actualPlayersCount}) melebihi kapasitas bracket yang dipilih ({$numFighters}). " .
                    "Silakan pilih bracket size yang lebih besar atau kurangi jumlah pemain.";
            }
        }

        if ($numFighters < 2) {
            return "Minimal jumlah pemain adalah 2 orang";
        }

        if ($numFighters > 64) {
            return "Maksimal jumlah pemain adalah 64 orang untuk performa sistem";
        }

        return null;
    }
}
