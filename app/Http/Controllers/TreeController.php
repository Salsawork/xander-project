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

            \Log::info('Fights saved successfully', [
                'championship_id' => $championship->id,
                'total_groups' => $groups->count(),
                'total_fighters_processed' => $numFighter
            ]);

            // AUTO-FILL next rounds based on winners
            $this->autoFillNextRounds($championship);

            // Sync ke brackets dengan COMPLETE STRUCTURE
            $tournament = $championship->tournament;
            if ($tournament && $tournament->event_id) {
                $event = Event::find($tournament->event_id);
                if ($event) {
                    // Gunakan generateCompleteStructure BUKAN syncAllRoundsToBracket
                    $this->generateCompleteStructure($event, $championship);
                    // Propagate winners ke slot TBD berikutnya
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

        // Hitung total rounds dari jumlah pemain di round 1
        $round1Groups = $allGroups->where('round', 1);
        $totalPlayers = $round1Groups->count() * 2;
        $maxRound = (int) ceil(log($totalPlayers, 2));

        \Log::info('Generate Complete Bracket Structure', [
            'total_players' => $totalPlayers,
            'max_rounds' => $maxRound,
            'event_id' => $event->id
        ]);

        // Generate SEMUA rounds
        for ($round = 1; $round <= $maxRound; $round++) {
            $matchesInRound = (int) ($totalPlayers / pow(2, $round));
            $position = 1;

            \Log::info('Creating round structure', [
                'round' => $round,
                'matches' => $matchesInRound
            ]);

            for ($matchNum = 1; $matchNum <= $matchesInRound; $matchNum++) {
                $group = $allGroups->where('round', $round)
                    ->where('order', $matchNum)
                    ->first();

                $player1Name = 'TBD';
                $player2Name = 'TBD';
                $isWinner1 = false;
                $isWinner2 = false;

                // Jika ada data group, ambil dari sana
                if ($group && $group->fights->isNotEmpty()) {
                    $fight = $group->fights->first();

                    // Fighter 1
                    if ($fight->c1) {
                        $fighter1 = $this->getFighterById($fight->c1, $championship);
                        $player1Name = $fighter1 ? $this->getPlayerName($fighter1) : 'TBD';
                        $isWinner1 = ($fight->winner_id == $fight->c1);
                    }

                    // Fighter 2
                    if ($fight->c2) {
                        $fighter2 = $this->getFighterById($fight->c2, $championship);
                        $player2Name = $fighter2 ? $this->getPlayerName($fighter2) : 'TBD';
                        $isWinner2 = ($fight->winner_id == $fight->c2);
                    }
                }

                // Buat bracket untuk player 1
                Bracket::create([
                    'event_id' => $event->id,
                    'round' => $round,
                    'position' => $position,
                    'player_name' => $player1Name,
                    'is_winner' => $isWinner1,
                    'next_match_position' => $round < $maxRound ? (int) ceil($position / 2) : null
                ]);

                $position++;

                // Buat bracket untuk player 2
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
        \Log::info('Complete Bracket Structure Generated', [
            'event_id' => $event->id,
            'total_brackets' => $totalBrackets
        ]);
    }

    /**
     * Propagate winners ketika update
     * Ini untuk fill TBD dengan nama pemenang dari round sebelumnya
     */
    private function propagateWinnersInBracket($event, $championship)
    {
        $maxRound = Bracket::where('event_id', $event->id)->max('round');

        // Loop setiap round (kecuali final)
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

                            \Log::info('Winner propagated to next round', [
                                'from_round' => $round,
                                'to_round' => $nextRound,
                                'player' => $winner->player_name
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