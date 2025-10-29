<?php

namespace Xoco70\LaravelTournaments\TreeGen;

use Illuminate\Support\Collection;
use Xoco70\LaravelTournaments\Exceptions\TreeGenerationException;
use Xoco70\LaravelTournaments\Models\ChampionshipSettings;
use Xoco70\LaravelTournaments\Models\SingleEliminationFight;

/**
 * FIXED: Double Elimination Tree Generator
 * 
 * Correct Structure:
 * - Round 1: All players (8 matches for 16 players)
 * - Upper Bracket: Winners path (Round 2, 3, 4)
 * - Lower Bracket: Losers path (starts RIGHT AFTER Round 1)
 *   - LB Round 1: Receives losers from Upper Round 2
 *   - LB Round 2: Winners from LB Round 1 advance
 *   - LB Round 3: Receives losers from Upper Round 3
 *   - LB Round 4: Winners from LB Round 3 advance
 *   - LB Round 5: Receives losers from Upper Round 4
 *   - LB Round 6: Winners from LB Round 5 advance (to Grand Final)
 * - Grand Final: Upper winner vs Lower winner
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
     * FIXED: Create correct Double Elimination structure
     * 
     * For 16 players:
     * - Round 1: 8 matches
     * - Upper: Rounds 2-4 (4, 2, 1 matches)
     * - Lower: Rounds 5-10 (4, 4, 2, 2, 1, 1 matches)
     * - Grand Final: Round 11 (1 match)
     */
    protected function pushGroups($numRounds, $numFighters)
    {
        \Log::info('=== Creating Double Elimination Structure ===', [
            'num_fighters' => $numFighters,
            'base_rounds' => $numRounds
        ]);

        // ===================================
        // UPPER BRACKET (Winners Path)
        // ===================================
        $upperRounds = $numRounds; // Rounds 2, 3, 4 for 16 players
        
        for ($round = 2; $round <= $upperRounds + 1; $round++) {
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
        // LOWER BRACKET (Losers Path)
        // Starts IMMEDIATELY after Round 1
        // ===================================
        $lowerBracketStart = $upperRounds + 2; // Round 5 for 16 players
        
        /**
         * Lower Bracket Pattern (for 16 players):
         * LB Round 1 (R5): 4 matches - receives 4 losers from Upper R2
         * LB Round 2 (R6): 4 matches - 4 winners from LB R1 vs 4 losers from Upper R3
         * LB Round 3 (R7): 2 matches - 4 winners from LB R2 → 2 matches
         * LB Round 4 (R8): 2 matches - 2 winners from LB R3 vs 2 losers from Upper R4
         * LB Round 5 (R9): 1 match   - 2 winners from LB R4 → 1 match
         * LB Round 6 (R10): 1 match  - Winner goes to Grand Final
         */
        
        $lowerBracketRounds = [];
        
        // Calculate lower bracket structure
        for ($upperRound = 2; $upperRound <= $upperRounds + 1; $upperRound++) {
            $losersFromUpper = $numFighters / pow(2, $upperRound);
            
            // First LB round receives losers from this upper round
            $lowerBracketRounds[] = [
                'matches' => $losersFromUpper,
                'type' => 'receive_losers',
                'from_upper' => $upperRound
            ];
            
            // Second LB round: winners advance
            if ($upperRound < $upperRounds + 1) {
                $lowerBracketRounds[] = [
                    'matches' => $losersFromUpper,
                    'type' => 'advance_winners',
                    'from_upper' => null
                ];
            }
        }
        
        // Last LB round before Grand Final
        $lowerBracketRounds[] = [
            'matches' => 1,
            'type' => 'final_advance',
            'from_upper' => null
        ];
        
        // Create lower bracket groups
        $lbRoundNumber = $lowerBracketStart;
        foreach ($lowerBracketRounds as $lbRound) {
            \Log::info("Lower Bracket Round {$lbRoundNumber}", [
                'matches' => $lbRound['matches'],
                'type' => $lbRound['type'],
                'from_upper' => $lbRound['from_upper'] ?? 'N/A'
            ]);
            
            for ($matchNum = 1; $matchNum <= $lbRound['matches']; $matchNum++) {
                $fighters = $this->createByeGroup(2);
                $group = $this->saveGroup($matchNum, $lbRoundNumber, null);
                $this->syncGroup($group, $fighters);
            }
            
            $lbRoundNumber++;
        }
        
        // ===================================
        // GRAND FINAL
        // ===================================
        $grandFinalRound = $lbRoundNumber;
        
        \Log::info("Grand Final Round {$grandFinalRound}");
        
        $fighters = $this->createByeGroup(2);
        $group = $this->saveGroup(1, $grandFinalRound, null);
        $this->syncGroup($group, $fighters);
        
        \Log::info('=== Double Elimination Structure Complete ===', [
            'total_rounds' => $grandFinalRound,
            'upper_rounds' => "2-" . ($upperRounds + 1),
            'lower_rounds' => "{$lowerBracketStart}-" . ($grandFinalRound - 1),
            'grand_final' => $grandFinalRound
        ]);
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