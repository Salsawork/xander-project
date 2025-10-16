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
        
        // Adjust final round to only have 1 match (2 players)
        $this->adjustFinalRound();
        
        // Assign positions after adjustment
        $this->assignPositions();
    }

    /**
     * Adjust final round to only have 1 match with 2 players
     */
    private function adjustFinalRound()
    {
        $finalRound = (int) $this->noRounds;
        $semifinalRound = $finalRound - 1;

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
                    $currentWinnerId
                ]
            ];
        } else {
            // If no semifinal winners yet, ensure final only has 1 match
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
     * @param $selected
     *
     * @return string
     */
    public function getPlayerList($selected)
    {
        $html = '<select>
                <option' . ($selected == '' ? ' selected' : '') . '></option>';

        foreach ($this->championship->fighters as $fighter) {
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
     * CRITICAL: Final round hanya 1 match (2 players)
     */
    private function assignFightersToBracket($numRound, $hasPreliminary)
    {
        for ($roundNumber = $numRound; $roundNumber <= $this->noRounds; $roundNumber++) {
            $groupsByRound = $this->groupsByRound->get($roundNumber + $hasPreliminary);
            
            if (!$groupsByRound) {
                continue;
            }

            // CRITICAL: Untuk final round, hanya proses 1 match (2 players)
            $maxMatches = ($roundNumber == $this->noRounds) 
                ? 1  // Final: HANYA 1 match
                : ($this->numFighters / pow(2, $roundNumber)); // Round lain: normal calculation

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
                . ' value='
                . ($fighter->id ?? '')
                . '>'
                . $fighter->name
                . '</option>';
        }

        return $html;
    }
}