<?php

namespace Xoco70\LaravelTournaments\TreeGen;

use Xoco70\LaravelTournaments\Models\Competitor;
use Xoco70\LaravelTournaments\Models\Team;

/**
 * Single Elimination Tree Generator
 * 
 * Generates bracket visualization for standard single elimination tournaments
 * where losers are immediately eliminated from competition.
 */
class CreateSingleEliminationTree
{
    public $groupsByRound;
    public $hasPreliminary;
    public $brackets = [];
    public $championship;
    public $numFighters;
    public $noRounds;
    
    // Visual settings
    public $playerWrapperHeight = 30;
    public $matchWrapperWidth = 150;
    public $roundSpacing = 40;
    public $matchSpacing = 42;
    public $borderWidth = 3;

    // Track eliminated fighters
    private $eliminatedFighters = [];
    private $activeFightersPerRound = [];

    public function __construct($groupsByRound, $championship, $hasPreliminary)
    {
        $this->championship = $championship;
        $this->groupsByRound = $groupsByRound;
        $this->hasPreliminary = $hasPreliminary;
    }

    /**
     * Build the tournament bracket structure
     */
    public function build()
    {
        // Get all fighters from first round
        $fighters = $this->groupsByRound->first()->map(function ($item) {
            $fighters = $item->getFightersWithBye();
            $fighter1 = $fighters->get(0);
            $fighter2 = $fighters->get(1);
            return [$fighter1, $fighter2];
        })->flatten()->all();
        
        $this->numFighters = count($fighters);

        // Calculate number of rounds
        $this->noRounds = log($this->numFighters, 2);
        $roundNumber = 1;

        // Group 2 fighters into matches
        $matches = array_chunk($fighters, 2);

        // Create first full round
        if (count($this->brackets)) {
            $roundNumber++;
        }
        
        $countMatches = count($matches);

        for ($i = 0; $i < $countMatches; $i++) {
            $this->brackets[$roundNumber][$i + 1] = $matches[$i];
        }

        // Assign fighters to brackets for all rounds
        $this->assignFightersToBracket($roundNumber, $this->hasPreliminary);

        // Adjust final round to only have 2 players
        $this->adjustFinalRound();

        // Track eliminated fighters
        $this->trackEliminatedFighters();

        // Assign visual positions
        $this->assignPositions();
    }

    /**
     * Adjust final round to only have 2 players from semifinal winners
     */
    private function adjustFinalRound()
    {
        $finalRound = $this->noRounds;
        $semifinalRound = $this->noRounds - 1;

        // Get semifinal winners
        $semifinalWinners = [];

        if (isset($this->brackets[$semifinalRound])) {
            foreach ($this->brackets[$semifinalRound] as $matchNumber => $match) {
                if (isset($match[2]) && $match[2] != null) {
                    $winnerId = $match[2];

                    if ($match[0] && $match[0]->id == $winnerId) {
                        $semifinalWinners[] = $match[0];
                    } elseif ($match[1] && $match[1]->id == $winnerId) {
                        $semifinalWinners[] = $match[1];
                    }
                }
            }
        }

        // Update final round with semifinal winners
        if (count($semifinalWinners) === 2) {
            $currentWinnerId = null;
            if (isset($this->brackets[$finalRound][1][2])) {
                $currentWinnerId = $this->brackets[$finalRound][1][2];
            }

            $this->brackets[$finalRound] = [
                1 => [
                    $semifinalWinners[0],
                    $semifinalWinners[1],
                    $currentWinnerId
                ]
            ];
        } else {
            // Ensure final only has 1 match
            if (isset($this->brackets[$finalRound])) {
                $firstMatch = reset($this->brackets[$finalRound]);
                $this->brackets[$finalRound] = [1 => $firstMatch];
            }
        }
    }

    /**
     * Track eliminated fighters per round
     */
    private function trackEliminatedFighters()
    {
        $this->eliminatedFighters = [];
        $this->activeFightersPerRound = [];

        // Start with all fighters active
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

        // Track eliminations
        foreach ($this->brackets as $roundNumber => $round) {
            $winnersThisRound = [];
            $losersThisRound = [];

            foreach ($round as $matchNumber => $match) {
                $fighter1 = $match[0] ?? null;
                $fighter2 = $match[1] ?? null;
                $winnerId = $match[2] ?? null;

                if ($winnerId != null) {
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

            if (!empty($losersThisRound)) {
                $this->eliminatedFighters[$roundNumber] = $losersThisRound;
            }

            // Calculate active fighters for next round
            if ($roundNumber < $this->noRounds) {
                $nextRound = $roundNumber + 1;
                $currentActive = $this->activeFightersPerRound[$roundNumber];

                foreach ($losersThisRound as $loserId => $loser) {
                    unset($currentActive[$loserId]);
                }

                $this->activeFightersPerRound[$nextRound] = $currentActive;
            }
        }
    }

    /**
     * Get active fighters for a specific round
     */
    public function getActiveFightersForRound($roundNumber)
    {
        return $this->activeFightersPerRound[$roundNumber] ?? $this->championship->fighters;
    }

    /**
     * Assign visual positions to all matches
     */
    private function assignPositions()
    {
        $spaceFactor = 0.5;
        $playerHeightFactor = 1;

        foreach ($this->brackets as $roundNumber => &$round) {
            foreach ($round as $matchNumber => &$match) {
                // Convert to named keys
                $match['playerA'] = $match[0];
                $match['playerB'] = $match[1];
                $match['winner_id'] = $match[2] ?? null;

                unset($match[0]);
                unset($match[1]);
                unset($match[2]);

                // Calculate positions
                $match['matchWrapperTop'] = (((2 * $matchNumber) - 1) * (pow(2, ($roundNumber) - 1)) - 1) * 
                                           (($this->matchSpacing / 2) + $this->playerWrapperHeight);
                $match['matchWrapperLeft'] = ($roundNumber - 1) * ($this->matchWrapperWidth + $this->roundSpacing - 1);
                $match['vConnectorLeft'] = floor($match['matchWrapperLeft'] + $this->matchWrapperWidth + 
                                                ($this->roundSpacing / 2) - ($this->borderWidth / 2));
                $match['vConnectorHeight'] = ($spaceFactor * $this->matchSpacing) + 
                                            ($playerHeightFactor * $this->playerWrapperHeight) + $this->borderWidth;
                $match['vConnectorTop'] = $match['hConnectorTop'] = $match['matchWrapperTop'] + $this->playerWrapperHeight;
                $match['hConnectorLeft'] = ($match['vConnectorLeft'] - ($this->roundSpacing / 2)) + 2;
                $match['hConnector2Left'] = $match['matchWrapperLeft'] + $this->matchWrapperWidth + ($this->roundSpacing / 2);

                if (!($matchNumber % 2)) {
                    $match['hConnector2Top'] = $match['vConnectorTop'] -= ($match['vConnectorHeight'] - $this->borderWidth);
                } else {
                    $match['hConnector2Top'] = $match['vConnectorTop'] + ($match['vConnectorHeight'] - $this->borderWidth);
                }
            }

            // Update spacing for next round
            $spaceFactor *= 2;
            $playerHeightFactor *= 2;
        }
    }

    /**
     * Assign fighters to brackets from championship data
     */
    private function assignFightersToBracket($numRound, $hasPreliminary)
    {
        for ($roundNumber = $numRound; $roundNumber <= $this->noRounds; $roundNumber++) {
            $groupsByRound = $this->groupsByRound->get($roundNumber + $hasPreliminary);

            if (!$groupsByRound) {
                continue;
            }

            // For final round, only 1 match
            $maxMatches = ($roundNumber == $this->noRounds) 
                ? 1 
                : ($this->numFighters / pow(2, $roundNumber));

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
     * Get round titles for display
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

            for ($i = 0; $i < $noRounds - 3; $i++) {
                $tempRounds[] = 'Last ' . $noTeamsInFirstRound;
                $noTeamsInFirstRound /= 2;
            }

            return array_merge($tempRounds, $roundTitles);
        }

        return $roundTitle[$this->numFighters];
    }

    /**
     * Print round titles HTML
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
     * Get player list dropdown HTML
     */
    public function getPlayerList($selected, $currentRound = 1)
    {
        $html = '<select><option' . ($selected == '' ? ' selected' : '') . '></option>';

        $availableFighters = $this->getActiveFightersForRound($currentRound);

        foreach ($availableFighters as $fighter) {
            $html = $this->addOptionToSelect($selected, $fighter, $html);
        }

        $html .= '</select>';

        return $html;
    }

    /**
     * Add option to select HTML
     */
    private function addOptionToSelect($selected, $fighter, $html): string
    {
        if ($fighter != null) {
            $select = $selected != null && $selected->id == $fighter->id ? ' selected' : '';
            $html .= '<option' . $select . ' value=' . ($fighter->id ?? '') . '>' . 
                     $fighter->name . '</option>';
        }

        return $html;
    }

    /**
     * Get new fighter instance based on championship type
     */
    public function getNewFighter()
    {
        if ($this->championship->category->isTeam()) {
            return new Team();
        }

        return new Competitor();
    }

    /**
     * Get bracket structure type
     */
    public function getBracketStructure()
    {
        return [
            'type' => 'single_elimination',
            'upper_rounds' => range(1, count($this->brackets))
        ];
    }
}