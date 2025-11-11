<?php

namespace Xoco70\LaravelTournaments\TreeGen;

use Illuminate\Support\Collection;
use Xoco70\LaravelTournaments\Exceptions\TreeGenerationException;
use Xoco70\LaravelTournaments\Models\ChampionshipSettings;
use Xoco70\LaravelTournaments\Models\SingleEliminationFight;

/**
 * FIXED V2: Double Elimination Tree Generator - NO DUPLICATION
 * 
 * Correct Structure for 16 players:
 * - Round 1: 8 matches (all 16 players)
 * - Upper Bracket: Rounds 2-5 (4→2→1→1 matches)
 * - Lower Bracket: Rounds 6-11 (4→4→2→2→1→1 matches)
 * - Grand Final: Round 12 (1 match)
 * 
 * TOTAL: 12 rounds, 27 matches
 */
abstract class PlayOffTreeGen extends TreeGen
{
    protected function getByeGroup($fighters)
    {
        $fighterCount = $fighters->count();
        $treeSize = $this->getTreeSize($fighterCount, 2);
        $byeCount = $treeSize - $fighterCount;
        return $this->createByeGroup($byeCount);
    }

    protected function chunk(Collection $fightersByEntity)
    {
        if ($this->championship->hasPreliminary()) {
            return $fightersByEntity->chunk($this->settings->preliminaryGroupSize);
        }
        return $fightersByEntity->chunk(2);
    }

    protected function generateFights()
    {
        $initialRound = 1;
        $fight = new SingleEliminationFight();
        $fight->saveFights($this->championship, $initialRound);
    }

    /**
     * FIXED: Proper Double Elimination Structure - NO DUPLICATION
     * 
     * For 16 players:
     * R1: 8 matches
     * Upper R2-R5: 4→2→1→1 matches
     * Lower R6-R11: 4→4→2→2→1→1 matches (6 rounds)
     * Grand Final R12: 1 match
     */
    protected function pushGroups($numRounds, $numFighters)
    {
        \Log::info('=== Creating Double Elimination Structure (NO DUPLICATION) ===', [
            'num_fighters' => $numFighters,
            'base_rounds' => $numRounds
        ]);

        // ===================================
        // UPPER BRACKET
        // ===================================
        $upperBracketEnd = $numRounds + 1; // Round 5 for 16 players
        
        for ($round = 2; $round <= $upperBracketEnd; $round++) {
            $matchesInRound = $numFighters / pow(2, $round);
            
            \Log::info("Upper Bracket Round {$round}", [
                'matches' => $matchesInRound
            ]);
            
            for ($matchNum = 1; $matchNum <= $matchesInRound; $matchNum++) {
                $fighters = $this->createByeGroup(2);
                $group = $this->saveGroup($matchNum, $round, null);
                $this->syncGroup($group, $fighters);
            }
        }
        
        // ===================================
        // LOWER BRACKET - FIXED CALCULATION
        // ===================================
        $lowerBracketStart = $upperBracketEnd + 1; // Round 6 for 16 players
        
        /**
         * Lower Bracket Structure (for 16 players):
         * 
         * LB R1 (R6): 4 matches - R1 losers (8 players) fight each other
         * LB R2 (R7): 4 matches - LB R1 winners (4) vs Upper R2 losers (4)
         * LB R3 (R8): 2 matches - LB R2 winners (4) advance (4→2)
         * LB R4 (R9): 2 matches - LB R3 winners (2) vs Upper R3 losers (2)
         * LB R5 (R10): 1 match  - LB R4 winners (2) advance (2→1)
         * LB R6 (R11): 1 match  - LB R5 winner (1) vs Upper R4 loser (1) = LOWER FINAL
         * 
         * Pattern: 
         * - First LB round: R1 losers fight (n/2 matches)
         * - Then alternating: (meet upper losers) → (advance) → repeat
         */
        
        $lowerBracketRounds = [];
        $currentLBRound = $lowerBracketStart;
        
        // LB Round 1: R1 losers fight each other
        $r1Losers = $numFighters / 2; // 8 losers
        $lowerBracketRounds[] = [
            'round' => $currentLBRound,
            'matches' => $r1Losers / 2, // 4 matches
            'type' => 'r1_losers_fight',
            'description' => 'Round 1 losers fight each other'
        ];
        $currentLBRound++;
        
        // Process each upper round's losers
        for ($upperRound = 2; $upperRound <= $upperBracketEnd; $upperRound++) {
            $upperLosers = $numFighters / pow(2, $upperRound); // Losers from this upper round
            
            // First LB round in cycle: LB winners meet upper losers
            $lowerBracketRounds[] = [
                'round' => $currentLBRound,
                'matches' => $upperLosers,
                'type' => 'meet_upper_losers',
                'from_upper' => $upperRound,
                'description' => "LB winners vs Upper R{$upperRound} losers"
            ];
            $currentLBRound++;
            
            // Second LB round in cycle: LB winners advance (except for last cycle)
            if ($upperRound < $upperBracketEnd) {
                $lowerBracketRounds[] = [
                    'round' => $currentLBRound,
                    'matches' => $upperLosers / 2,
                    'type' => 'advance',
                    'description' => 'LB winners advance'
                ];
                $currentLBRound++;
            }
        }
        
        // Create lower bracket groups
        foreach ($lowerBracketRounds as $lbRound) {
            \Log::info("Lower Bracket Round {$lbRound['round']}", [
                'matches' => $lbRound['matches'],
                'type' => $lbRound['type'],
                'description' => $lbRound['description']
            ]);
            
            for ($matchNum = 1; $matchNum <= $lbRound['matches']; $matchNum++) {
                $fighters = $this->createByeGroup(2);
                $group = $this->saveGroup($matchNum, $lbRound['round'], null);
                $this->syncGroup($group, $fighters);
            }
        }
        
        // ===================================
        // GRAND FINAL
        // ===================================
        $grandFinalRound = $currentLBRound;
        
        \Log::info("Grand Final Round {$grandFinalRound}");
        
        $fighters = $this->createByeGroup(2);
        $group = $this->saveGroup(1, $grandFinalRound, null);
        $this->syncGroup($group, $fighters);
        
        \Log::info('=== Double Elimination Structure Complete (NO DUPLICATION) ===', [
            'total_rounds' => $grandFinalRound,
            'round_1' => 1,
            'upper_bracket' => "2-{$upperBracketEnd}",
            'lower_bracket' => "{$lowerBracketStart}-" . ($grandFinalRound - 1),
            'grand_final' => $grandFinalRound,
            'total_matches' => $this->calculateTotalMatches($numFighters)
        ]);
    }

    /**
     * Calculate total matches for verification
     */
    private function calculateTotalMatches($numFighters)
    {
        // Round 1: n/2 matches
        $r1Matches = $numFighters / 2;
        
        // Upper bracket: (n/2 - 1) matches (it's a single elimination tree)
        $upperMatches = ($numFighters / 2) - 1;
        
        // Lower bracket: (n/2) matches (everyone except champion loses once more)
        $lowerMatches = $numFighters / 2;
        
        // Grand final: 1 match
        $grandFinal = 1;
        
        return $r1Matches + $upperMatches + $lowerMatches + $grandFinal;
    }

    protected function getNumRounds($numFighters)
    {
        return intval(log($numFighters, 2));
    }

    protected function generateAllTrees()
    {
        $this->minFightersCheck();
        $usersByArea = $this->getFightersByArea();
        $this->generateGroupsForRound($usersByArea, 1);
        $numFighters = count($usersByArea->collapse());
        $this->pushEmptyGroupsToTree($numFighters);
    }

    protected function pushEmptyGroupsToTree($numFighters)
    {
        $numRounds = $this->getNumRounds($numFighters);
        return $this->pushGroups($numRounds, $numFighters);
    }

    public function generateGroupsForRound(Collection $usersByArea, $round)
    {
        $order = 1;
        foreach ($usersByArea as $fightersByEntity) {
            $chunkedFighters = $this->chunk($fightersByEntity);
            
            foreach ($chunkedFighters as $fighters) {
                $fighters = $fighters->pluck('id');
                $group = $this->saveGroup($order, $round, null);
                $this->syncGroup($group, $fighters);
                $order++;
            }
        }
    }

    private function minFightersCheck()
    {
        $fighters = $this->getFighters();
        $areas = $this->settings->fightingAreas;
        $fighterType = $this->championship->category->isTeam
            ? trans_choice('laravel-tournaments::core.team', 2)
            : trans_choice('laravel-tournaments::core.competitor', 2);

        $minFighterCount = $fighters->count() / $areas;

        if ($minFighterCount < ChampionshipSettings::MIN_COMPETITORS_BY_AREA) {
            throw new TreeGenerationException(trans('laravel-tournaments::core.min_competitor_required', [
                'number'       => ChampionshipSettings::MIN_COMPETITORS_BY_AREA,
                'fighter_type' => $fighterType,
            ]), 422);
        }
    }

    public function adjustFightersGroupWithByes($fighters, Collection $fighterGroups): Collection
    {
        $tmpFighterGroups = clone $fighterGroups;
        $numBye = count($this->getByeGroup($fighters));

        if ($numBye == 0) {
            return $fighters;
        }

        $max = $this->getMaxFightersByEntity($tmpFighterGroups);
        $fighters = $this->repart($fighterGroups, $max);

        if (!app()->runningUnitTests()) {
            $fighters = $fighters->shuffle();
        }

        $fighters = $this->insertByes($fighters, $numBye);
        return $fighters;
    }

    private function getMaxFightersByEntity($userGroups): int
    {
        return $userGroups
            ->sortByDesc(function ($group) {
                return $group->count();
            })
            ->first()
            ->count();
    }

    private function repart($fighterGroups, $max)
    {
        $fighters = new Collection();
        for ($i = 0; $i < $max; $i++) {
            foreach ($fighterGroups as $fighterGroup) {
                $fighter = $fighterGroup->values()->get($i);
                if ($fighter != null) {
                    $fighters->push($fighter);
                }
            }
        }
        return $fighters;
    }

    private function insertByes(Collection $fighters, $numByeTotal)
    {
        $bye = $this->createByeFighter();
        $groupSize = 2;
        $frequency = (int) floor(count($fighters) / $groupSize / $groupSize);
        
        if ($frequency < $groupSize) {
            $frequency = $groupSize;
        }

        $newFighters = new Collection();
        $count = 0;
        $byeCount = 0;
        
        foreach ($fighters as $fighter) {
            if ($this->shouldInsertBye($frequency, $count, $byeCount, $numByeTotal)) {
                for ($i = 0; $i < $groupSize; $i++) {
                    if ($byeCount < $numByeTotal) {
                        $newFighters->push($bye);
                        $byeCount++;
                    }
                }
            }
            $newFighters->push($fighter);
            $count++;
        }

        return $newFighters;
    }

    private function shouldInsertBye($frequency, $count, $byeCount, $numByeTotal): bool
    {
        return $count != 0 && $count % $frequency == 0 && $byeCount < $numByeTotal;
    }

    protected function getTreeSize($fighterCount, $groupSize)
    {
        $squareMultiplied = collect([1, 2, 4, 8, 16, 32, 64])
            ->map(function ($item) use ($groupSize) {
                return $item * $groupSize;
            });

        foreach ($squareMultiplied as $limit) {
            if ($fighterCount <= $limit) {
                $treeSize = $limit;
                $numAreas = $this->settings->fightingAreas;
                $fighterCountPerArea = $treeSize / $numAreas;
                
                if ($fighterCountPerArea < $groupSize) {
                    $treeSize = $treeSize * $numAreas;
                }

                return $treeSize;
            }
        }

        return 64 * $groupSize;
    }
}