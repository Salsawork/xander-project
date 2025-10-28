<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Xoco70\LaravelTournaments\Exceptions\TreeGenerationException;
use Xoco70\LaravelTournaments\Models\Championship;
use Xoco70\LaravelTournaments\Models\ChampionshipSettings;
use Xoco70\LaravelTournaments\Models\Competitor;
use Xoco70\LaravelTournaments\Models\FightersGroup;
use Xoco70\LaravelTournaments\Models\Team;
use App\Models\Bracket;
use App\Models\Event;
use Xoco70\LaravelTournaments\Models\Tournament;

class TreeController extends Controller
{
    public function index()
    {
        $tournament = Tournament::with(
            'competitors',
            'championships.settings',
            'championships.category'
        )->first();

        return view('laravel-tournaments::tree.index')
            ->with('tournament', $tournament);
    }

    public function store(Request $request, $championshipId)
    {
        $this->deleteEverything();
        $numFighters = $request->numFighters;
        $isTeam = $request->isTeam ?? 0;
        $championship = $this->provisionObjects($request, $isTeam, $numFighters);
        $generation = $championship->chooseGenerationStrategy();

        try {
            $generation->run();
        } catch (TreeGenerationException $e) {
            redirect()->back()
                ->withErrors($e->getMessage());
        }

        return back()
            ->with('numFighters', $numFighters)
            ->with('isTeam', $isTeam);
    }

    private function deleteEverything()
    {
        DB::table('fight')->delete();
        DB::table('fighters_groups')->delete();
        DB::table('fighters_group_competitor')->delete();
        DB::table('fighters_group_team')->delete();
        DB::table('competitor')->delete();
        DB::table('team')->delete();
    }

    protected function provisionObjects(Request $request, $isTeam, $numFighters)
    {
        if ($isTeam) {
            $championship = Championship::find(2);
            factory(Team::class, (int) $numFighters)->create(['championship_id' => $championship->id]);
        } else {
            $championship = Championship::find(1);
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

    /**
     * Update tree - MAIN METHOD
     * Handles both Single and Double Elimination
     */
    public function update(Request $request, Championship $championship)
    {
        $query = FightersGroup::with('fights')
            ->where('championship_id', $championship->id);

        $fighters = $request->singleElimination_fighters ?? $request->playOff_fighters ?? [];
        $scores = $request->score ?? [];

        if ($championship->hasPreliminary()) {
            $query = $query->where('round', '>', 1);
            $fighters = $request->preliminary_fighters ?? [];
        }

        $groups = $query->orderBy('round')->orderBy('order')->get();

        DB::beginTransaction();
        try {
            if (!is_array($fighters)) {
                $fighters = $fighters->toArray() ?? [];
            }
            if (!is_array($scores)) {
                $scores = $scores->toArray() ?? [];
            }

            \Log::info('Update tree started', [
                'championship_id' => $championship->id,
                'fighters_count' => count($fighters),
                'scores_count' => count($scores),
                'groups_count' => $groups->count()
            ]);

            $numFighter = 0;

            // Save fight results
            foreach ($groups as $group) {
                foreach ($group->fights as $fight) {
                    if (isset($fighters[$numFighter])) {
                        $fight->c1 = $fighters[$numFighter];
                        if (isset($scores[$numFighter]) && $scores[$numFighter] != null) {
                            $fight->winner_id = $fighters[$numFighter];
                        }
                    }
                    $numFighter++;

                    if (isset($fighters[$numFighter])) {
                        $fight->c2 = $fighters[$numFighter];
                        if ($fight->winner_id == null && isset($scores[$numFighter]) && $scores[$numFighter] != null) {
                            $fight->winner_id = $fighters[$numFighter];
                        }
                    }
                    $numFighter++;

                    $fight->save();

                    \Log::info('Fight saved', [
                        'fight_id' => $fight->id,
                        'round' => $group->round,
                        'c1' => $fight->c1,
                        'c2' => $fight->c2,
                        'winner_id' => $fight->winner_id
                    ]);
                }
            }

            // Check tournament type
            $settings = $championship->getSettings();
            $isDoubleElimination = ($settings->treeType == 0); // 0 = Double Elimination (Playoff)

            \Log::info('Tournament type detected', [
                'treeType' => $settings->treeType,
                'isDoubleElimination' => $isDoubleElimination
            ]);

            if ($isDoubleElimination) {
                // AUTO-FILL DOUBLE ELIMINATION
                $this->autoFillDoubleElimination($championship);
            } else {
                // AUTO-FILL SINGLE ELIMINATION
                $this->autoFillSingleElimination($championship);
            }

            // Sync to brackets table
            $tournament = $championship->tournament;
            if ($tournament && $tournament->event_id) {
                $event = Event::find($tournament->event_id);
                if ($event) {
                    $this->generateBracketsFromChampionship($event, $championship, $isDoubleElimination);
                }
            }

            DB::commit();
            return back()->with('success', 'Tree updated successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Tree update error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()->withErrors('Error updating tree: ' . $e->getMessage());
        }
    }

    /**
     * AUTO-FILL for Double Elimination
     * Upper Bracket -> Lower Bracket -> Grand Final
     */
    private function autoFillDoubleElimination($championship)
    {
        $allGroups = $championship->fightersGroups()
            ->with('fights')
            ->orderBy('round')
            ->orderBy('order')
            ->get();

        if ($allGroups->isEmpty()) {
            \Log::warning('No groups found for double elimination');
            return;
        }

        $round1Count = $championship->groupsByRound(1)->count();
        $numFighters = $round1Count * 2;
        $numRounds = intval(log($numFighters, 2));

        // Calculate structure
        $upperBracketEnd = $numRounds + 1;
        $lowerBracketStart = $upperBracketEnd + 1;
        $maxRound = $allGroups->max('round');
        $grandFinalRound = $maxRound;

        \Log::info('Double Elimination Auto-fill Started', [
            'num_fighters' => $numFighters,
            'round_1_matches' => $round1Count,
            'upper_bracket' => "2-{$upperBracketEnd}",
            'lower_bracket_start' => $lowerBracketStart,
            'grand_final' => $grandFinalRound
        ]);

        // =========================================
        // STEP 1: Process Round 1
        // Winners → Upper R2
        // Losers → Lower Bracket (stored for later)
        // =========================================
        $round1Losers = [];
        $round1Groups = $allGroups->where('round', 1)->values();

        foreach ($round1Groups as $groupIndex => $group) {
            $fight = $group->fights->first();
            if (!$fight || !$fight->winner_id) continue;

            $winnerId = $fight->winner_id;
            $loserId = ($fight->c1 == $winnerId) ? $fight->c2 : $fight->c1;

            // Store loser for LB Round 1
            if ($loserId && !$this->isEmptySlot($loserId)) {
                $round1Losers[] = $loserId;
            }

            // Winner advances to Upper R2
            $nextPosition = (int) ceil(($groupIndex + 1) / 2);
            $nextRoundGroup = $allGroups->where('round', 2)
                ->where('order', $nextPosition)
                ->first();

            if ($nextRoundGroup) {
                $nextFight = $nextRoundGroup->fights->first();
                if ($nextFight) {
                    $isFirstInMatch = ($groupIndex % 2 == 0);
                    if ($isFirstInMatch) {
                        $nextFight->c1 = $winnerId;
                    } else {
                        $nextFight->c2 = $winnerId;
                    }
                    $nextFight->save();
                }
            }
        }

        \Log::info('Round 1 processed', [
            'losers_collected' => count($round1Losers)
        ]);

        // =========================================
        // STEP 2: Process Upper Bracket
        // Winners → Next Upper Round
        // Losers → Lower Bracket
        // =========================================
        $lowerBracketQueue = []; // Track which LB round receives which losers

        for ($round = 2; $round <= $upperBracketEnd; $round++) {
            $currentRoundGroups = $allGroups->where('round', $round)->values();
            $losersFromThisRound = [];

            foreach ($currentRoundGroups as $groupIndex => $group) {
                $fight = $group->fights->first();
                if (!$fight || !$fight->winner_id) continue;

                $winnerId = $fight->winner_id;
                $loserId = ($fight->c1 == $winnerId) ? $fight->c2 : $fight->c1;

                \Log::info("Upper Round {$round}, Match " . ($groupIndex + 1), [
                    'winner_id' => $winnerId,
                    'loser_id' => $loserId
                ]);

                // Store loser
                if ($loserId && !$this->isEmptySlot($loserId)) {
                    $losersFromThisRound[] = $loserId;
                }

                // WINNER: Advance to next upper round
                if ($round < $upperBracketEnd) {
                    $nextPosition = (int) ceil(($groupIndex + 1) / 2);
                    $nextRoundGroup = $allGroups->where('round', $round + 1)
                        ->where('order', $nextPosition)
                        ->first();

                    if ($nextRoundGroup) {
                        $nextFight = $nextRoundGroup->fights->first();
                        if ($nextFight) {
                            $isFirstInMatch = ($groupIndex % 2 == 0);
                            if ($isFirstInMatch) {
                                $nextFight->c1 = $winnerId;
                            } else {
                                $nextFight->c2 = $winnerId;
                            }
                            $nextFight->save();
                        }
                    }
                } else {
                    // Upper bracket final winner → Grand Final c1
                    $grandFinalGroup = $allGroups->where('round', $grandFinalRound)->first();
                    if ($grandFinalGroup) {
                        $grandFinalFight = $grandFinalGroup->fights->first();
                        if ($grandFinalFight) {
                            $grandFinalFight->c1 = $winnerId;
                            $grandFinalFight->save();
                            \Log::info("Upper winner placed in Grand Final c1");
                        }
                    }
                }
            }

            // Queue losers for lower bracket
            if (!empty($losersFromThisRound)) {
                $lowerBracketQueue[$round] = $losersFromThisRound;
                \Log::info("Queued {count} losers from Upper Round {$round}", [
                    'count' => count($losersFromThisRound),
                    'round' => $round
                ]);
            }
        }

        // =========================================
        // STEP 3: Fill Lower Bracket
        // =========================================
        $lbRoundNumber = $lowerBracketStart;

        // LB Round 1: Round 1 losers fight each other
        if (!empty($round1Losers)) {
            $this->fillLowerBracketRound($allGroups, $lbRoundNumber, $round1Losers, null);
            $lbRoundNumber++;
        }

        // Process queued losers from upper bracket
        foreach ($lowerBracketQueue as $upperRound => $losers) {
            // First: Upper losers vs LB survivors
            $this->fillLowerBracketRound($allGroups, $lbRoundNumber, $losers, 'vs_survivors');
            $lbRoundNumber++;

            // Second: LB winners advance
            if ($lbRoundNumber < $grandFinalRound) {
                $this->processLowerBracketAdvancement($allGroups, $lbRoundNumber - 1, $lbRoundNumber);
                $lbRoundNumber++;
            }
        }

        // Final LB round winner → Grand Final c2
        $finalLBRound = $grandFinalRound - 1;
        $finalLBGroup = $allGroups->where('round', $finalLBRound)->first();
        if ($finalLBGroup) {
            $fight = $finalLBGroup->fights->first();
            if ($fight && $fight->winner_id) {
                $grandFinalGroup = $allGroups->where('round', $grandFinalRound)->first();
                if ($grandFinalGroup) {
                    $grandFinalFight = $grandFinalGroup->fights->first();
                    if ($grandFinalFight) {
                        $grandFinalFight->c2 = $fight->winner_id;
                        $grandFinalFight->save();
                        \Log::info("Lower winner placed in Grand Final c2");
                    }
                }
            }
        }
    }

    private function processLowerBracketAdvancement($allGroups, $currentRound, $nextRound)
    {
        $currentGroups = $allGroups->where('round', $currentRound)->values();

        foreach ($currentGroups as $groupIndex => $group) {
            $fight = $group->fights->first();
            if (!$fight || !$fight->winner_id) continue;

            // Advance winner to next round
            $nextPosition = (int) ceil(($groupIndex + 1) / 2);
            $nextGroup = $allGroups->where('round', $nextRound)
                ->where('order', $nextPosition)
                ->first();

            if ($nextGroup) {
                $nextFight = $nextGroup->fights->first();
                if ($nextFight) {
                    $isFirstInMatch = ($groupIndex % 2 == 0);
                    if ($isFirstInMatch) {
                        $nextFight->c1 = $fight->winner_id;
                    } else {
                        $nextFight->c2 = $fight->winner_id;
                    }
                    $nextFight->save();
                }
            }
        }
    }

    private function fillLowerBracketRound($allGroups, $lbRoundNumber, $losers, $type = null)
    {
        $lbGroups = $allGroups->where('round', $lbRoundNumber)->sortBy('order')->values();

        \Log::info("Filling LB Round {$lbRoundNumber}", [
            'num_losers' => count($losers),
            'num_groups' => $lbGroups->count(),
            'type' => $type ?? 'direct'
        ]);

        $loserIndex = 0;

        foreach ($lbGroups as $group) {
            $fight = $group->fights->first();
            if (!$fight) continue;

            // Fill c1 with loser if empty
            if ($this->isEmptySlot($fight->c1) && isset($losers[$loserIndex])) {
                $fight->c1 = $losers[$loserIndex];
                $loserIndex++;
            }

            // Fill c2 with loser if empty
            if ($this->isEmptySlot($fight->c2) && isset($losers[$loserIndex])) {
                $fight->c2 = $losers[$loserIndex];
                $loserIndex++;
            }

            $fight->save();
        }
    }

    /**
     * Drop loser from upper bracket to lower bracket
     */
    private function dropLoserToLowerBracket($loserId, $upperRound, $upperRounds, $allGroups, $lowerBracketStart)
    {
        // Calculate target lower bracket round
        // Upper Round 2 -> Lower Round 1 (lowerBracketStart)
        // Upper Round 3 -> Lower Round 3 (lowerBracketStart + 2)
        // Pattern: LB_Round = LB_Start + ((UpperRound - 2) * 2)

        $lowerTargetRound = $lowerBracketStart + (($upperRound - 2) * 2);

        \Log::info('Dropping loser to lower bracket', [
            'loser_id' => $loserId,
            'from_upper_round' => $upperRound,
            'to_lower_round' => $lowerTargetRound
        ]);

        // Find empty slot in target lower bracket round
        $lowerBracketGroups = $allGroups->where('round', $lowerTargetRound)->sortBy('order');

        foreach ($lowerBracketGroups as $group) {
            $fight = $group->fights->first();
            if (!$fight) continue;

            // Try c1 first
            if ($this->isEmptySlot($fight->c1)) {
                $fight->c1 = $loserId;
                $fight->save();
                \Log::info("Loser placed in LB Round {$lowerTargetRound}, Match {$group->order}, c1");
                return;
            }

            // Then c2
            if ($this->isEmptySlot($fight->c2)) {
                $fight->c2 = $loserId;
                $fight->save();
                \Log::info("Loser placed in LB Round {$lowerTargetRound}, Match {$group->order}, c2");
                return;
            }
        }

        \Log::warning('Could not place loser - no empty slots', [
            'loser_id' => $loserId,
            'target_round' => $lowerTargetRound
        ]);
    }

    /**
     * Check if slot is empty
     */
    private function isEmptySlot($value)
    {
        return !$value || $value == '' || $value == 'BYE' || $value == 'TBD';
    }

    /**
     * AUTO-FILL for Single Elimination
     */
    private function autoFillSingleElimination($championship)
    {
        $allGroups = $championship->fightersGroups()
            ->with('fights')
            ->orderBy('round')
            ->orderBy('order')
            ->get();

        $maxRound = $allGroups->max('round');

        for ($round = 1; $round < $maxRound; $round++) {
            $currentRoundGroups = $allGroups->where('round', $round)->values();

            foreach ($currentRoundGroups as $groupIndex => $group) {
                $fight = $group->fights->first();

                if ($fight && $fight->winner_id) {
                    $nextPosition = (int) ceil(($groupIndex + 1) / 2);
                    $nextRoundGroup = $allGroups->where('round', $round + 1)
                        ->where('order', $nextPosition)
                        ->first();

                    if ($nextRoundGroup) {
                        $nextFight = $nextRoundGroup->fights->first();

                        if ($nextFight) {
                            $isFirstInMatch = ($groupIndex % 2 == 0);

                            if ($isFirstInMatch) {
                                $nextFight->c1 = $fight->winner_id;
                            } else {
                                $nextFight->c2 = $fight->winner_id;
                            }

                            $nextFight->save();
                        }
                    }
                }
            }
        }
    }

    /**
     * Generate brackets from championship
     */
    private function generateBracketsFromChampionship($event, $championship, $isDoubleElimination)
    {
        Bracket::where('event_id', $event->id)->delete();

        $allGroups = $championship->fightersGroups()
            ->where('round', '>=', 1)
            ->with('fights')
            ->orderBy('round')
            ->orderBy('order')
            ->get();

        if ($allGroups->isEmpty()) {
            return;
        }

        \Log::info('Generating Brackets', [
            'event_id' => $event->id,
            'is_double_elimination' => $isDoubleElimination,
            'total_groups' => $allGroups->count()
        ]);

        if ($isDoubleElimination) {
            $this->generateDoubleEliminationBrackets($event, $championship, $allGroups);
        } else {
            $this->generateSingleEliminationBrackets($event, $championship, $allGroups);
        }
    }

    /**
     * Generate Double Elimination Brackets
     */
    private function generateDoubleEliminationBrackets($event, $championship, $allGroups)
    {
        $round1Groups = $allGroups->where('round', 1);
        $totalPlayers = $round1Groups->count() * 2;
        $maxRound = $allGroups->max('round');

        $numRounds = intval(log($totalPlayers, 2));
        $upperRounds = $numRounds + 1;
        $lowerBracketStart = $upperRounds + 1;
        $grandFinalRound = $maxRound;

        $position = 1;

        // Round 1
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

        // Upper Bracket
        $position = 1;
        for ($round = 2; $round <= $upperRounds; $round++) {
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
                    'next_match_position' => $round < $upperRounds ? (int) ceil($position / 2) : null,
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
                    'next_match_position' => $round < $upperRounds ? (int) ceil($position / 2) : null,
                    'bracket_type' => 'upper'
                ]);
                $position++;
            }
        }

        // Lower Bracket
        $lowerPosition = 1;
        for ($round = $lowerBracketStart; $round < $grandFinalRound; $round++) {
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
                    'position' => $lowerPosition,
                    'player_name' => $player1Name,
                    'is_winner' => $isWinner1,
                    'next_match_position' => $round < ($grandFinalRound - 1) ? (int) ceil($lowerPosition / 2) : null,
                    'bracket_type' => 'lower'
                ]);
                $lowerPosition++;

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
                    'position' => $lowerPosition,
                    'player_name' => $player2Name,
                    'is_winner' => $isWinner2,
                    'next_match_position' => $round < ($grandFinalRound - 1) ? (int) ceil($lowerPosition / 2) : null,
                    'bracket_type' => 'lower'
                ]);
                $lowerPosition++;
            }
        }

        // Grand Final
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

        \Log::info('Double Elimination Brackets Generated', [
            'event_id' => $event->id,
            'total_brackets' => Bracket::where('event_id', $event->id)->count()
        ]);
    }

    /**
     * Generate Single Elimination Brackets
     */
    private function generateSingleEliminationBrackets($event, $championship, $allGroups)
    {
        $round1Groups = $allGroups->where('round', 1);
        $totalPlayers = $round1Groups->count() * 2;
        $maxRound = (int) ceil(log($totalPlayers, 2));

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

                Bracket::create([
                    'event_id' => $event->id,
                    'round' => $round,
                    'position' => $position,
                    'player_name' => $player1Name,
                    'is_winner' => $isWinner1,
                    'next_match_position' => $round < $maxRound ? (int) ceil($position / 2) : null
                ]);
                $position++;

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
}
