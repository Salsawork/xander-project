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

        // Calculate structure
        $round1Count = $championship->groupsByRound(1)->count();
        $numFighters = $round1Count * 2;
        $numRounds = intval(log($numFighters, 2));

        $upperBracketEnd = $numRounds + 1;
        $lowerBracketStart = $upperBracketEnd + 1;
        $maxRound = $allGroups->max('round');
        $grandFinalRound = $maxRound;

        \Log::info('=== Double Elimination Auto-fill V3 (FIXED LB ADVANCEMENT) ===', [
            'fighters' => $numFighters,
            'upper_bracket' => "1-{$upperBracketEnd}",
            'lower_bracket' => "{$lowerBracketStart}-" . ($grandFinalRound - 1),
            'grand_final' => $grandFinalRound
        ]);

        // ==================================================
        // STEP 1: Process Round 1
        // ==================================================
        $round1Groups = $allGroups->where('round', 1)->sortBy('order')->values();
        $round1Losers = [];

        foreach ($round1Groups as $groupIndex => $group) {
            $fight = $group->fights->first();
            if (!$fight || !$fight->winner_id) continue;

            $winnerId = $fight->winner_id;
            $loserId = ($fight->c1 == $winnerId) ? $fight->c2 : $fight->c1;

            // Store loser
            if ($loserId && !$this->isEmptySlot($loserId)) {
                $round1Losers[] = $loserId;
            }

            // Winner advances to Upper R2
            $nextPosition = (int) ceil(($groupIndex + 1) / 2);
            $nextGroup = $allGroups->where('round', 2)
                ->where('order', $nextPosition)
                ->first();

            if ($nextGroup) {
                $nextFight = $nextGroup->fights->first();
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
            'winners_advanced' => $round1Count,
            'losers_to_lb' => count($round1Losers)
        ]);

        // ==================================================
        // STEP 2: Process Upper Bracket & Collect Losers
        // ==================================================
        $upperLosersQueue = [];

        for ($round = 2; $round <= $upperBracketEnd; $round++) {
            $roundGroups = $allGroups->where('round', $round)->sortBy('order')->values();
            $losersThisRound = [];

            foreach ($roundGroups as $groupIndex => $group) {
                $fight = $group->fights->first();
                if (!$fight || !$fight->winner_id) continue;

                $winnerId = $fight->winner_id;
                $loserId = ($fight->c1 == $winnerId) ? $fight->c2 : $fight->c1;

                // Store loser
                if ($loserId && !$this->isEmptySlot($loserId)) {
                    $losersThisRound[] = $loserId;
                }

                // Winner advances
                if ($round < $upperBracketEnd) {
                    // Advance to next upper round
                    $nextPosition = (int) ceil(($groupIndex + 1) / 2);
                    $nextGroup = $allGroups->where('round', $round + 1)
                        ->where('order', $nextPosition)
                        ->first();

                    if ($nextGroup) {
                        $nextFight = $nextGroup->fights->first();
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
                    // Upper Final winner → Grand Final c1
                    $grandGroup = $allGroups->where('round', $grandFinalRound)->first();
                    if ($grandGroup) {
                        $grandFight = $grandGroup->fights->first();
                        if ($grandFight) {
                            $grandFight->c1 = $winnerId;
                            $grandFight->save();
                            \Log::info("✓ Upper Final winner → Grand Final c1: {$winnerId}");
                        }
                    }
                }
            }

            if (!empty($losersThisRound)) {
                $upperLosersQueue[$round] = $losersThisRound;
                \Log::info("Upper R{$round}: {$roundGroups->count()} matches, " . count($losersThisRound) . " losers queued");
            }
        }

        // ==================================================
        // STEP 3: Fill Lower Bracket (FIXED ADVANCEMENT)
        // ==================================================
        $lbRound = $lowerBracketStart;

        // LB R1: Round 1 losers fight each other
        if (!empty($round1Losers)) {
            \Log::info("═══ LB R{$lbRound}: R1 losers fight each other ═══");
            $this->fillLowerBracketMatches($allGroups, $lbRound, $round1Losers);
            $lbRound++;
        }

        // Process each upper round's losers with FIXED pattern
        foreach ($upperLosersQueue as $upperRound => $upperLosers) {
            \Log::info("═══ Processing Upper R{$upperRound} losers (" . count($upperLosers) . " losers) ═══");

            // ROUND A: LB winners meet Upper losers
            \Log::info("LB R{$lbRound}: LB R" . ($lbRound - 1) . " winners MEET Upper R{$upperRound} losers");

            $lbGroups = $allGroups->where('round', $lbRound)->sortBy('order')->values();
            $prevLBRound = $lbRound - 1;

            // CRITICAL FIX: Get ALL winners from previous LB round
            $prevLBWinners = [];
            $prevLBGroups = $allGroups->where('round', $prevLBRound)->sortBy('order')->values();

            foreach ($prevLBGroups as $prevGroup) {
                $prevFight = $prevGroup->fights->first();
                if ($prevFight && $prevFight->winner_id) {
                    $prevLBWinners[] = $prevFight->winner_id;
                    \Log::info("  LB R{$prevLBRound} Match {$prevGroup->order} winner: {$prevFight->winner_id}");
                }
            }

            \Log::info("  Total LB R{$prevLBRound} winners: " . count($prevLBWinners));
            \Log::info("  Total Upper R{$upperRound} losers: " . count($upperLosers));

            // Fill matches: LB winners (c1) vs Upper losers (c2)
            foreach ($lbGroups as $matchIndex => $group) {
                $fight = $group->fights->first();
                if (!$fight) continue;

                // c1: LB winner
                if (isset($prevLBWinners[$matchIndex])) {
                    $fight->c1 = $prevLBWinners[$matchIndex];
                }

                // c2: Upper loser
                if (isset($upperLosers[$matchIndex])) {
                    $fight->c2 = $upperLosers[$matchIndex];
                }

                $fight->save();
                \Log::info("  Match {$group->order}: c1={$fight->c1} (LB winner) vs c2={$fight->c2} (Upper loser)");
            }

            $lbRound++;

            // ROUND B: LB winners advance (except for last upper round)
            if ($upperRound < $upperBracketEnd) {
                \Log::info("LB R{$lbRound}: Advancement round (winners advance)");
                $this->advanceLowerBracketWinners($allGroups, $lbRound - 1, $lbRound);
                $lbRound++;
            }
        }

        // ==================================================
        // STEP 4: Grand Final - Upper vs Lower Winners
        // ==================================================
        \Log::info('═══ GRAND FINAL SETUP ═══');

        $lowerFinalRound = $grandFinalRound - 1;

        // Get Grand Final fight
        $grandGroup = $allGroups->where('round', $grandFinalRound)->first();
        if (!$grandGroup) {
            \Log::error('Grand Final group not found!');
            return;
        }

        $grandFight = $grandGroup->fights->first();
        if (!$grandFight) {
            \Log::error('Grand Final fight not found!');
            return;
        }

        // Upper Final winner → Grand Final c1 (already set in STEP 2)
        $upperFinalGroup = $allGroups->where('round', $upperBracketEnd)->first();
        if ($upperFinalGroup) {
            $upperFight = $upperFinalGroup->fights->first();
            if ($upperFight && $upperFight->winner_id) {
                // Verify it's already in Grand Final c1
                if ($grandFight->c1 == $upperFight->winner_id) {
                    \Log::info("✓ Upper Final winner already in Grand Final c1: {$upperFight->winner_id}");
                } else {
                    // Set it if not already set
                    $grandFight->c1 = $upperFight->winner_id;
                    $grandFight->save();
                    \Log::info("✓ Upper Final winner → Grand Final c1: {$upperFight->winner_id}");
                }
            } else {
                \Log::warning('⚠ Upper Final winner not determined yet');
            }
        }

        // Lower Final winner → Grand Final c2
        $lowerFinalGroup = $allGroups->where('round', $lowerFinalRound)->first();
        if ($lowerFinalGroup) {
            $lowerFight = $lowerFinalGroup->fights->first();
            if ($lowerFight && $lowerFight->winner_id) {
                $grandFight->c2 = $lowerFight->winner_id;
                $grandFight->save();
                \Log::info("✓ Lower Final winner → Grand Final c2: {$lowerFight->winner_id}");
            } else {
                \Log::warning('⚠ Lower Final winner not determined yet');
            }
        }

        // Display Grand Final matchup
        if ($grandFight->c1 && $grandFight->c2) {
            \Log::info('🏆 GRAND FINAL MATCHUP SET!');
            \Log::info("   c1 (Upper Winner): {$grandFight->c1}");
            \Log::info("   c2 (Lower Winner): {$grandFight->c2}");

            // Check if Grand Final has been played
            if ($grandFight->winner_id) {
                $champion = $grandFight->winner_id;
                $runnerUp = ($grandFight->c1 == $champion) ? $grandFight->c2 : $grandFight->c1;
                \Log::info('👑 CHAMPION: ' . $champion);
                \Log::info('🥈 RUNNER-UP: ' . $runnerUp);
            } else {
                \Log::info('⏳ Grand Final not yet played - waiting for winner selection');
            }
        } else {
            \Log::warning('⚠ Grand Final not complete:');
            \Log::warning('   c1 (Upper): ' . ($grandFight->c1 ?? 'NOT SET'));
            \Log::warning('   c2 (Lower): ' . ($grandFight->c2 ?? 'NOT SET'));
        }

        \Log::info('=== Double Elimination Auto-fill Complete ===');
    }



    private function advanceLowerBracketWinners($allGroups, $fromRound, $toRound)
    {
        $fromGroups = $allGroups->where('round', $fromRound)->sortBy('order')->values();

        foreach ($fromGroups as $groupIndex => $group) {
            $fight = $group->fights->first();
            if (!$fight || !$fight->winner_id) continue;

            // Calculate next position
            $nextPosition = (int) ceil(($groupIndex + 1) / 2);
            $nextGroup = $allGroups->where('round', $toRound)
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

                    $slot = $isFirstInMatch ? 'c1' : 'c2';
                    \Log::info("  Advanced: Match {$group->order} winner → LB R{$toRound} Match {$nextGroup->order} ({$slot})");
                }
            }
        }
    }

    private function fillLowerBracketWithUpperLosers($allGroups, $lbRound, $upperLosers)
    {
        $lbGroups = $allGroups->where('round', $lbRound)->sortBy('order')->values();

        \Log::info("Filling LB R{$lbRound} with upper losers", [
            'losers' => count($upperLosers),
            'matches' => $lbGroups->count()
        ]);

        foreach ($lbGroups as $matchIndex => $group) {
            $fight = $group->fights->first();
            if (!$fight) continue;

            // Place upper loser in c2 (c1 should already have LB winner)
            if (isset($upperLosers[$matchIndex])) {
                if ($this->isEmptySlot($fight->c2)) {
                    $fight->c2 = $upperLosers[$matchIndex];
                } elseif ($this->isEmptySlot($fight->c1)) {
                    $fight->c1 = $upperLosers[$matchIndex];
                }
                $fight->save();
            }
        }
    }

    private function fillLowerBracketMatches($allGroups, $lbRound, $fighters)
    {
        $lbGroups = $allGroups->where('round', $lbRound)->sortBy('order')->values();

        $fighterIndex = 0;

        foreach ($lbGroups as $group) {
            $fight = $group->fights->first();
            if (!$fight) continue;

            // Fill c1
            if (isset($fighters[$fighterIndex])) {
                $fight->c1 = $fighters[$fighterIndex];
                $fighterIndex++;
            }

            // Fill c2
            if (isset($fighters[$fighterIndex])) {
                $fight->c2 = $fighters[$fighterIndex];
                $fighterIndex++;
            }

            $fight->save();
            \Log::info("  Match {$group->order}: c1={$fight->c1} vs c2={$fight->c2}");
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
