<?php

namespace Xoco70\LaravelTournaments\TreeGen;

use Xoco70\LaravelTournaments\Models\Competitor;
use Xoco70\LaravelTournaments\Models\Team;

class CreateSingleEliminationTree
{
    public $groupsByRound;
    public $hasPreliminary;
    public $brackets = [];
    public $championship;
    public $numFighters;
    public $noRounds;
    public $playerWrapperHeight = 30;
    public $matchWrapperWidth = 150;
    public $roundSpacing = 40;
    public $matchSpacing = 42;
    public $borderWidth = 3;
    
    // NEW: Track eliminated fighters per round
    private $eliminatedFighters = [];
    private $activeFightersPerRound = [];

    public function __construct($groupsByRound, $championship, $hasPreliminary)
    {
        $this->championship = $championship;
        $this->groupsByRound = $groupsByRound;
        $this->hasPreliminary = $hasPreliminary;
    }

    public function build()
    {
        $fighters = $this->groupsByRound->first()->map(function ($item) {
            $fighters = $item->getFightersWithBye();
            $fighter1 = $fighters->get(0);
            $fighter2 = $fighters->get(1);

            return [$fighter1, $fighter2];
        })->flatten()->all();
        $this->numFighters = count($fighters);

        // Calculate the size of the first full round
        $this->noRounds = log($this->numFighters, 2);
        $roundNumber = 1;

        // Group 2 fighters into a match
        $matches = array_chunk($fighters, 2);

        // If there's already a match in the match array, increase round number
        if (count($this->brackets)) {
            $roundNumber++;
        }
        $countMatches = count($matches);
        
        // Create the first full round of fighters
        for ($i = 0; $i < $countMatches; $i++) {
            $this->brackets[$roundNumber][$i + 1] = $matches[$i];
        }

        // Assign fighters to bracket for all rounds
        $this->assignFightersToBracket($roundNumber, $this->hasPreliminary);
        
        // CRITICAL: Adjust final round to only have 2 players (1 match)
        $this->adjustFinalRound();
        
        // NEW: Track eliminated fighters per round
        $this->trackEliminatedFighters();
        
        // Assign positions after adjustment
        $this->assignPositions();
    }

    /**
     * NEW: Track which fighters are eliminated in each round
     */
    private function trackEliminatedFighters()
    {
        $this->eliminatedFighters = [];
        $this->activeFightersPerRound = [];
        
        // Start with all fighters active in round 1
        $allFighters = [];
        foreach ($this->brackets as $roundNumber => $round) {
            foreach ($round as $matchNumber => $match) {
                if (isset($match[0]) && $match[0] != null) {
                    $allFighters[$match[0]->id] = $match[0];
                }
                if (isset($match[1]) && $match[1] != null) {
                    $allFighters[$match[1]->id] = $match[1];
                }
            }
        }
        $this->activeFightersPerRound[1] = $allFighters;
        
        // For each round, determine who got eliminated
        foreach ($this->brackets as $roundNumber => $round) {
            $winnersThisRound = [];
            $losersThisRound = [];
            
            foreach ($round as $matchNumber => $match) {
                $fighter1 = $match[0] ?? null;
                $fighter2 = $match[1] ?? null;
                $winnerId = $match[2] ?? null;
                
                if ($winnerId != null) {
                    // Determine winner and loser
                    if ($fighter1 && $fighter1->id == $winnerId) {
                        $winnersThisRound[$fighter1->id] = $fighter1;
                        if ($fighter2) {
                            $losersThisRound[$fighter2->id] = $fighter2;
                        }
                    } elseif ($fighter2 && $fighter2->id == $winnerId) {
                        $winnersThisRound[$fighter2->id] = $fighter2;
                        if ($fighter1) {
                            $losersThisRound[$fighter1->id] = $fighter1;
                        }
                    }
                }
            }
            
            // Store eliminated fighters for this round
            if (!empty($losersThisRound)) {
                $this->eliminatedFighters[$roundNumber] = $losersThisRound;
            }
            
            // Calculate active fighters for next round
            if ($roundNumber < $this->noRounds) {
                $nextRound = $roundNumber + 1;
                $currentActive = $this->activeFightersPerRound[$roundNumber];
                
                // Remove losers from active list
                foreach ($losersThisRound as $loserId => $loser) {
                    unset($currentActive[$loserId]);
                }
                
                $this->activeFightersPerRound[$nextRound] = $currentActive;
            }
        }
    }

    /**
     * NEW: Get active fighters for a specific round (excludes eliminated fighters)
     */
    public function getActiveFightersForRound($roundNumber)
    {
        return $this->activeFightersPerRound[$roundNumber] ?? $this->championship->fighters;
    }

    /**
     * CRITICAL: Adjust final round to only have 2 players from semifinal winners
     */
    private function adjustFinalRound()
    {
        $finalRound = $this->noRounds;
        $semifinalRound = $this->noRounds - 1;

        // Get semifinal winners
        $semifinalWinners = [];
        
        if (isset($this->brackets[$semifinalRound])) {
            foreach ($this->brackets[$semifinalRound] as $matchNumber => $match) {
                // Check if match has winner_id
                if (isset($match[2]) && $match[2] != null) {
                    $winnerId = $match[2];
                    
                    // Determine which player is the winner
                    if ($match[0] && $match[0]->id == $winnerId) {
                        $semifinalWinners[] = $match[0];
                    } elseif ($match[1] && $match[1]->id == $winnerId) {
                        $semifinalWinners[] = $match[1];
                    }
                }
            }
        }

        // If we have exactly 2 semifinal winners, update final
        if (count($semifinalWinners) === 2) {
            // Get current final match winner_id if exists
            $currentWinnerId = null;
            if (isset($this->brackets[$finalRound][1][2])) {
                $currentWinnerId = $this->brackets[$finalRound][1][2];
            }

            // Replace final round with ONLY 1 match containing 2 semifinal winners
            $this->brackets[$finalRound] = [
                1 => [
                    $semifinalWinners[0],
                    $semifinalWinners[1],
                    $currentWinnerId  // Preserve winner if already set
                ]
            ];
        } else {
            // If no semifinal winners yet, ensure final only has 1 match
            // Keep existing final structure but make sure it's only 1 match
            if (isset($this->brackets[$finalRound])) {
                $firstMatch = reset($this->brackets[$finalRound]);
                $this->brackets[$finalRound] = [
                    1 => $firstMatch
                ];
            }
        }
    }

    private function assignPositions()
    {
        // Variables required for figuring out the height of the vertical connectors
        $spaceFactor = 0.5;
        $playerHeightFactor = 1;
        
        foreach ($this->brackets as $roundNumber => &$round) {
            foreach ($round as $matchNumber => &$match) {
                // Give teams a nicer index
                $match['playerA'] = $match[0];
                $match['playerB'] = $match[1];
                $match['winner_id'] = $match[2] ?? null;

                unset($match[0]);
                unset($match[1]);
                unset($match[2]);

                // Figure out the bracket positions
                $match['matchWrapperTop'] = (((2 * $matchNumber) - 1) * (pow(2, ($roundNumber) - 1)) - 1) * (($this->matchSpacing / 2) + $this->playerWrapperHeight);
                $match['matchWrapperLeft'] = ($roundNumber - 1) * ($this->matchWrapperWidth + $this->roundSpacing - 1);
                $match['vConnectorLeft'] = floor($match['matchWrapperLeft'] + $this->matchWrapperWidth + ($this->roundSpacing / 2) - ($this->borderWidth / 2));
                $match['vConnectorHeight'] = ($spaceFactor * $this->matchSpacing) + ($playerHeightFactor * $this->playerWrapperHeight) + $this->borderWidth;
                $match['vConnectorTop'] = $match['hConnectorTop'] = $match['matchWrapperTop'] + $this->playerWrapperHeight;
                $match['hConnectorLeft'] = ($match['vConnectorLeft'] - ($this->roundSpacing / 2)) + 2;
                $match['hConnector2Left'] = $match['matchWrapperLeft'] + $this->matchWrapperWidth + ($this->roundSpacing / 2);

                // Adjust the positions depending on the match number
                if (!($matchNumber % 2)) {
                    $match['hConnector2Top'] = $match['vConnectorTop'] -= ($match['vConnectorHeight'] - $this->borderWidth);
                } else {
                    $match['hConnector2Top'] = $match['vConnectorTop'] + ($match['vConnectorHeight'] - $this->borderWidth);
                }
            }

            // Update the spacing variables
            $spaceFactor *= 2;
            $playerHeightFactor *= 2;
        }
    }

    /**
     * Returns titles depending on number of rounds.
     *
     * @return array
     */
    public function getRoundTitles()
    {
        $semiFinalTitles = ['Semi-Finals', 'Final'];
        $quarterFinalTitles = ['Quarter-Finals', 'Semi-Finals', 'Final'];
        $roundTitle = [
            2 => ['Final'],
            3 => $semiFinalTitles,
            4 => $semiFinalTitles,
            5 => $semiFinalTitles,
            6 => $quarterFinalTitles,
            7 => $quarterFinalTitles,
            8 => $quarterFinalTitles,
        ];
        
        if ($this->numFighters > 8) {
            $roundTitles = ['Quarter-Finals', 'Semi-Finals', 'Final'];
            $noRounds = ceil(log($this->numFighters, 2));
            $noTeamsInFirstRound = pow(2, ceil(log($this->numFighters) / log(2)));
            $tempRounds = [];

            // The minus 3 is to ignore the final, semi final and quarter final rounds
            for ($i = 0; $i < $noRounds - 3; $i++) {
                $tempRounds[] = 'Last ' . $noTeamsInFirstRound;
                $noTeamsInFirstRound /= 2;
            }

            return array_merge($tempRounds, $roundTitles);
        }

        return $roundTitle[$this->numFighters];
    }

    /**
     * Print Round Titles.
     */
    public function printRoundTitles()
    {
        $roundTitles = $this->getRoundTitles();

        echo '<div id="round-titles-wrapper">';

        foreach ($roundTitles as $key => $roundTitle) {
            $left = $key * ($this->matchWrapperWidth + $this->roundSpacing - 1);
            echo '<div class="round-title" style="left: ' . $left . 'px;">' . $roundTitle . '</div>';
        }
        
        echo '</div>';
    }

    /**
     * MODIFIED: Get player list - only show active fighters for the round
     * Fighters who lost in previous rounds are excluded
     * 
     * @param $selected
     * @param $currentRound - Round number to determine which fighters are still active
     *
     * @return string
     */
    public function getPlayerList($selected, $currentRound = 1)
    {
        $html = '<select>
                <option' . ($selected == '' ? ' selected' : '') . '></option>';

        // Get only active fighters for this round (excludes eliminated fighters)
        $availableFighters = $this->getActiveFightersForRound($currentRound);

        foreach ($availableFighters as $fighter) {
            $html = $this->addOptionToSelect($selected, $fighter, $html);
        }

        $html .= '</select>';

        return $html;
    }

    public function getNewFighter()
    {
        if ($this->championship->category->isTeam()) {
            return new Team();
        }

        return new Competitor();
    }

    /**
     * Assign fighters to bracket for all rounds
     * 
     * @param $numRound
     */
    private function assignFightersToBracket($numRound, $hasPreliminary)
    {
        for ($roundNumber = $numRound; $roundNumber <= $this->noRounds; $roundNumber++) {
            $groupsByRound = $this->groupsByRound->get($roundNumber + $hasPreliminary);
            
            if (!$groupsByRound) {
                continue;
            }

            // For final round, only process the FIRST match (2 players only)
            $maxMatches = ($roundNumber == $this->noRounds) 
                ? 1  // Final: Only 1 match
                : ($this->numFighters / pow(2, $roundNumber)); // Other rounds: Normal calculation

            for ($matchNumber = 1; $matchNumber <= $maxMatches; $matchNumber++) {
                if (!isset($groupsByRound[$matchNumber - 1])) {
                    continue;
                }

                $fight = $groupsByRound[$matchNumber - 1]->fights[0];
                $fighter1 = $fight->fighter1;
                $fighter2 = $fight->fighter2;
                $winnerId = $fight->winner_id;
                
                $this->brackets[$roundNumber][$matchNumber] = [$fighter1, $fighter2, $winnerId];
            }
        }
    }

    /**
     * @param $selected
     * @param $fighter
     * @param $html
     *
     * @return string
     */
    private function addOptionToSelect($selected, $fighter, $html): string
    {
        if ($fighter != null) {
            $select = $selected != null && $selected->id == $fighter->id ? ' selected' : '';
            $html .= '<option' . $select
                . '    ue='
                . ($fighter->id ?? '')
                . '>'
                . $fighter->name
                . '</option>';
        }

        return $html;
    }
}