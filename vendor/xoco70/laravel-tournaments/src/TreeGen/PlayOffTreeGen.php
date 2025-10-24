<?php

namespace Xoco70\LaravelTournaments\TreeGen;

use Illuminate\Support\Collection;
use Xoco70\LaravelTournaments\Exceptions\TreeGenerationException;
use Xoco70\LaravelTournaments\Models\ChampionshipSettings;
use Xoco70\LaravelTournaments\Models\SingleEliminationFight;

abstract class PlayOffTreeGen extends TreeGen
{
    /**
     * Calculate the Byes needed to fill the Championship Tree.
     */
    protected function getByeGroup($fighters)
    {
        $fighterCount = $fighters->count();
        $treeSize = $this->getTreeSize($fighterCount, 2);
        $byeCount = $treeSize - $fighterCount;

        return $this->createByeGroup($byeCount);
    }

    /**
     * Chunk Fighters into groups for fighting.
     */
    protected function chunk(Collection $fightersByEntity)
    {
        if ($this->championship->hasPreliminary()) {
            $fightersGroup = $fightersByEntity->chunk($this->settings->preliminaryGroupSize);
            return $fightersGroup;
        }

        return $fightersByEntity->chunk(2);
    }

    /**
     * Generate First Round Fights - SAMA SEPERTI SINGLE ELIMINATION
     */
    protected function generateFights()
    {
        $settings = $this->settings;
        $initialRound = 1;
        
        // Save fights - SAMA SEPERTI SINGLE ELIMINATION
        $fight = new SingleEliminationFight();
        $fight->saveFights($this->championship, $initialRound);
    }

    /**
     * FIXED: Save Groups - DOUBLE ELIMINATION STRUCTURE
     * Upper Bracket + Lower Bracket + Grand Final
     */
    protected function pushGroups($numRounds, $numFighters)
    {
        // ============================================
        // UPPER BRACKET: Standard single elimination
        // ============================================
        $upperRounds = $numRounds + 1; // Rounds 2 to upperRounds
        
        for ($roundNumber = 2; $roundNumber <= $upperRounds; $roundNumber++) {
            $maxMatches = ($numFighters / pow(2, $roundNumber));
            
            for ($matchNumber = 1; $matchNumber <= $maxMatches; $matchNumber++) {
                $fighters = $this->createByeGroup(2);
                $group = $this->saveGroup($matchNumber, $roundNumber, null);
                $this->syncGroup($group, $fighters);
            }
        }
        
        // ============================================
        // LOWER BRACKET: Losers bracket structure
        // ============================================
        // Lower bracket has (numRounds * 2 - 1) rounds
        // Because losers from each upper round join at different points
        
        $lowerBracketStart = $upperRounds + 1;
        
        // Calculate lower bracket rounds
        // Formula: For N players, lower bracket has (2N - 2) rounds
        // Where N is the number of rounds in upper bracket
        
        for ($lbRound = 0; $lbRound < ($numRounds * 2 - 1); $lbRound++) {
            $roundNumber = $lowerBracketStart + $lbRound;
            
            // Lower bracket structure alternates:
            // - Even rounds (0, 2, 4...): Upper bracket losers join
            // - Odd rounds (1, 3, 5...): Winners progress
            
            if ($lbRound % 2 == 0) {
                // Rounds where upper losers drop in
                // Matches = fighters from upper / 4 / (2^(lbRound/2))
                $matchesInRound = max(1, $numFighters / pow(2, ($lbRound / 2) + 3));
            } else {
                // Pure progression rounds
                $matchesInRound = max(1, $numFighters / pow(2, (($lbRound + 1) / 2) + 3));
            }
            
            for ($matchNumber = 1; $matchNumber <= $matchesInRound; $matchNumber++) {
                $fighters = $this->createByeGroup(2);
                $group = $this->saveGroup($matchNumber, $roundNumber, null);
                $this->syncGroup($group, $fighters);
            }
        }
        
        // ============================================
        // GRAND FINAL: One final match
        // ============================================
        $grandFinalRound = $lowerBracketStart + ($numRounds * 2 - 1);
        $fighters = $this->createByeGroup(2);
        $group = $this->saveGroup(1, $grandFinalRound, null);
        $this->syncGroup($group, $fighters);
        
        \Log::info('Double Elimination Structure Created', [
            'num_fighters' => $numFighters,
            'num_rounds_calculation' => $numRounds,
            'upper_bracket_rounds' => "2 to {$upperRounds}",
            'lower_bracket_start' => $lowerBracketStart,
            'grand_final_round' => $grandFinalRound,
            'total_rounds' => $grandFinalRound
        ]);
    }

    /**
     * Return number of rounds based on fighter count
     */
    protected function getNumRounds($numFighters)
    {
        return intval(log($numFighters, 2));
    }

    /**
     * Generate all trees with double elimination structure
     */
    protected function generateAllTrees()
    {
        $this->minFightersCheck();
        $usersByArea = $this->getFightersByArea();
        $this->generateGroupsForRound($usersByArea, 1);
        $numFighters = count($usersByArea->collapse());
        $this->pushEmptyGroupsToTree($numFighters);
    }

    /**
     * Create empty groups after round 1
     */
    protected function pushEmptyGroupsToTree($numFighters)
    {
        $numRounds = $this->getNumRounds($numFighters);
        return $this->pushGroups($numRounds, $numFighters);
    }

    /**
     * Generate groups for round 1
     */
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

    /**
     * Check minimum fighters
     */
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

    /**
     * Adjust fighters with byes - SAMA SEPERTI SINGLE ELIMINATION
     */
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

    /**
     * Get max fighters by entity
     */
    private function getMaxFightersByEntity($userGroups): int
    {
        return $userGroups
            ->sortByDesc(function ($group) {
                return $group->count();
            })
            ->first()
            ->count();
    }

    /**
     * Repart fighters
     */
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

    /**
     * Insert byes
     */
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

    /**
     * Check if bye should be inserted
     */
    private function shouldInsertBye($frequency, $count, $byeCount, $numByeTotal): bool
    {
        return $count != 0 && $count % $frequency == 0 && $byeCount < $numByeTotal;
    }
}

