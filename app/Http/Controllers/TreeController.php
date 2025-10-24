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
     * Update tree dengan COMPLETE STRUCTURE
     */
    public function update(Request $request, Championship $championship)
    {
        $query = FightersGroup::with('fights')
            ->where('championship_id', $championship->id);

        $fighters = $request->singleElimination_fighters ?? [];
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
                        'c1' => $fight->c1,
                        'c2' => $fight->c2,
                        'winner_id' => $fight->winner_id
                    ]);
                }
            }

            // Check if this is Double Elimination
            $settings = $championship->getSettings();
            $isDoubleElimination = ($settings->treeType == 0);

            if ($isDoubleElimination) {
                // AUTO-FILL DOUBLE ELIMINATION: Winners to next round, Losers to lower bracket
                $this->autoFillDoubleElimination($championship);
            } else {
                // AUTO-FILL SINGLE ELIMINATION: Winners to next round only
                $this->autoFillNextRounds($championship);
            }

            // Sync ke brackets
            $tournament = $championship->tournament;
            if ($tournament && $tournament->event_id) {
                $event = Event::find($tournament->event_id);
                if ($event) {
                    $this->generateCompleteStructure($event, $championship);
                    $this->propagateWinnersInBracket($event, $championship);
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

    private function autoFillDoubleElimination($championship)
    {
        $allGroups = $championship->fightersGroups()
            ->with('fights')
            ->orderBy('round')
            ->orderBy('order')
            ->get();

        $maxRound = $allGroups->max('round');
        $round1Count = $championship->groupsByRound(1)->count();

        // Calculate bracket structure
        $numRounds = intval(log($round1Count * 2, 2));
        $upperRounds = $numRounds + 1; // Round 2 to upperRounds
        $lowerBracketStart = $upperRounds + 1;
        $grandFinalRound = $maxRound;
        $lowerBracketEnd = $grandFinalRound - 1;

        \Log::info('Auto-fill Double Elimination Structure', [
            'total_rounds' => $maxRound,
            'upper_rounds' => "2-{$upperRounds}",
            'lower_bracket_rounds' => "{$lowerBracketStart}-{$lowerBracketEnd}",
            'grand_final' => $grandFinalRound
        ]);

        // ============================================
        // STEP 1: Process Upper Bracket (Rounds 2 to upperRounds)
        // ============================================
        for ($round = 2; $round <= $upperRounds; $round++) {
            $currentRoundGroups = $allGroups->where('round', $round)->values();

            foreach ($currentRoundGroups as $groupIndex => $group) {
                $fight = $group->fights->first();
                if (!$fight || !$fight->winner_id) continue;

                $winnerId = $fight->winner_id;
                $loserId = ($fight->c1 == $winnerId) ? $fight->c2 : $fight->c1;

                // WINNER: Advances to next upper round
                if ($round < $upperRounds) {
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
                    // Upper bracket final winner goes to Grand Final as c1
                    $grandFinalGroup = $allGroups->where('round', $grandFinalRound)->first();
                    if ($grandFinalGroup) {
                        $grandFinalFight = $grandFinalGroup->fights->first();
                        if ($grandFinalFight) {
                            $grandFinalFight->c1 = $winnerId;
                            $grandFinalFight->save();
                        }
                    }
                }

                // LOSER: Drops to Lower Bracket
                if ($loserId && $loserId != 'BYE' && $loserId != 'TBD' && $loserId != '') {
                    $this->dropLoserToLowerBracket($loserId, $round, $upperRounds, $allGroups, $lowerBracketStart);
                }
            }
        }

        // ============================================
        // STEP 2: Process Lower Bracket
        // ============================================
        for ($round = $lowerBracketStart; $round <= $lowerBracketEnd; $round++) {
            $currentRoundGroups = $allGroups->where('round', $round)->values();

            foreach ($currentRoundGroups as $groupIndex => $group) {
                $fight = $group->fights->first();
                if (!$fight || !$fight->winner_id) continue;

                $winnerId = $fight->winner_id;

                // WINNER: Advances to next lower round
                if ($round < $lowerBracketEnd) {
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
                    // Lower bracket final winner goes to Grand Final as c2
                    $grandFinalGroup = $allGroups->where('round', $grandFinalRound)->first();
                    if ($grandFinalGroup) {
                        $grandFinalFight = $grandFinalGroup->fights->first();
                        if ($grandFinalFight) {
                            $grandFinalFight->c2 = $winnerId;
                            $grandFinalFight->save();
                        }
                    }
                }

                // LOSER: Eliminated from tournament (no second chance)
            }
        }
    }

    private function moveLoserToLowerBracket($loserId, $upperRound, $allGroups, $lowerBracketStart)
    {
        // Calculate target lower bracket round
        // Round 1 losers -> LB Round 1 (lowerBracketStart)
        // Round 2 losers -> LB Round 3 (lowerBracketStart + 2)
        // Round 3 losers -> LB Round 5 (lowerBracketStart + 4)

        $lowerTargetRound = $lowerBracketStart + (($upperRound - 1) * 2);

        \Log::info('Moving loser to lower bracket', [
            'loser' => $loserId,
            'from_upper_round' => $upperRound,
            'to_lower_round' => $lowerTargetRound
        ]);

        // Find empty slot in target lower bracket round
        $lowerBracketGroups = $allGroups->where('round', $lowerTargetRound);

        foreach ($lowerBracketGroups as $group) {
            $fight = $group->fights->first();

            if (!$fight) {
                continue;
            }

            // Try to fill c1 first
            if (!$fight->c1 || $fight->c1 == 'BYE' || $fight->c1 == 'TBD' || $fight->c1 == '') {
                $fight->c1 = $loserId;
                $fight->save();
                \Log::info('Loser placed in lower bracket c1', [
                    'loser' => $loserId,
                    'round' => $lowerTargetRound,
                    'group' => $group->order
                ]);
                return;
            }

            // Then try c2
            if (!$fight->c2 || $fight->c2 == 'BYE' || $fight->c2 == 'TBD' || $fight->c2 == '') {
                $fight->c2 = $loserId;
                $fight->save();
                \Log::info('Loser placed in lower bracket c2', [
                    'loser' => $loserId,
                    'round' => $lowerTargetRound,
                    'group' => $group->order
                ]);
                return;
            }
        }

        \Log::warning('Could not place loser in lower bracket - no empty slots', [
            'loser' => $loserId,
            'target_round' => $lowerTargetRound
        ]);
    }

    private function dropLoserToLowerBracket($loserId, $upperRound, $upperRounds, $allGroups, $lowerBracketStart)
    {
        // Calculate target lower bracket round
        // Upper Round 2 -> Lower Round 1 (lowerBracketStart)
        // Upper Round 3 -> Lower Round 3 (lowerBracketStart + 2)
        // Upper Round 4 -> Lower Round 5 (lowerBracketStart + 4)
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

            // Try to fill c1 first (if empty or placeholder)
            if ($this->isEmptySlot($fight->c1)) {
                $fight->c1 = $loserId;
                $fight->save();
                \Log::info("Loser placed in LB Round {$lowerTargetRound}, Match {$group->order}, Position c1");
                return;
            }

            // Then try c2
            if ($this->isEmptySlot($fight->c2)) {
                $fight->c2 = $loserId;
                $fight->save();
                \Log::info("Loser placed in LB Round {$lowerTargetRound}, Match {$group->order}, Position c2");
                return;
            }
        }

        \Log::warning('Could not place loser - no empty slots', [
            'loser_id' => $loserId,
            'target_round' => $lowerTargetRound
        ]);
    }

    private function isEmptySlot($value)
    {
        return !$value || $value == '' || $value == 'BYE' || $value == 'TBD';
    }


    /**
     * AUTO-FILL fighters ke round berikutnya
     */
    private function autoFillNextRounds($championship)
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
     * Generate COMPLETE bracket structure untuk SEMUA rounds
     * Bahkan yang belum ada data - akan diisi dengan TBD
     */
    private function generateCompleteStructure($event, $championship)
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
    /**
     * Propagate winners ketika update
     * Ini untuk fill TBD dengan nama pemenang dari round sebelumnya
     */
    private function propagateWinnersInBracket($event, $championship)
    {
        $maxRound = Bracket::where('event_id', $event->id)->max('round');

        for ($round = 1; $round < $maxRound; $round++) {
            $winners = Bracket::where('event_id', $event->id)
                ->where('round', $round)
                ->where('is_winner', true)
                ->where('player_name', '!=', 'TBD')
                ->get();

            foreach ($winners as $winner) {
                if ($winner->next_match_position) {
                    $isFirstPosition = ($winner->position % 2 == 1);
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
                        }
                    }
                }
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
