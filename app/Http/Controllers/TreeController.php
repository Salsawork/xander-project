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
     * Update tree - FIXED dengan safe array access
     */
    public function update(Request $request, Championship $championship)
    {
        $query = FightersGroup::with('fights')
            ->where('championship_id', $championship->id);

        // Safe array access - ensure arrays exist
        $fighters = $request->singleElimination_fighters ?? [];
        $scores = $request->score ?? [];

        if ($championship->hasPreliminary()) {
            $query = $query->where('round', '>', 1);
            $fighters = $request->preliminary_fighters ?? [];
        }

        $groups = $query->orderBy('round')->orderBy('order')->get();

        DB::beginTransaction();
        try {
            // Convert to array if needed
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
                    // SAFE: Check if keys exist before accessing
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

            // Sync ke brackets
            $tournament = $championship->tournament;
            if ($tournament && $tournament->event_id) {
                $event = Event::find($tournament->event_id);
                if ($event) {
                    $this->syncAllRoundsToBracket($event, $championship);
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
     * Sync SEMUA rounds ke bracket
     */
    private function syncAllRoundsToBracket($event, $championship)
    {
        Bracket::where('event_id', $event->id)->delete();

        $allGroups = $championship->fightersGroups()
            ->with('fights')
            ->orderBy('round')
            ->orderBy('order')
            ->get();

        $maxRound = $allGroups->max('round');

        foreach ($allGroups->groupBy('round') as $round => $roundGroups) {
            $positionInRound = 1;

            // Final: hanya ambil 1 group (2 pemain)
            if ($round == $maxRound) {
                $roundGroups = $roundGroups->take(1);
            }

            foreach ($roundGroups as $group) {
                $fight = $group->fights->first();

                if ($fight && $fight->c1) {
                    $fighter1 = $this->getFighterById($fight->c1, $championship);
                    if ($fighter1) {
                        $isWinner1 = ($fight->winner_id == $fight->c1);

                        Bracket::create([
                            'event_id' => $event->id,
                            'round' => $round,
                            'position' => $positionInRound,
                            'player_name' => $this->getPlayerName($fighter1),
                            'is_winner' => $isWinner1,
                            'next_match_position' => $round < $maxRound ? (int) ceil($positionInRound / 2) : null
                        ]);

                        $positionInRound++;
                    }
                }

                if ($fight && $fight->c2) {
                    $fighter2 = $this->getFighterById($fight->c2, $championship);
                    if ($fighter2) {
                        $isWinner2 = ($fight->winner_id == $fight->c2);

                        Bracket::create([
                            'event_id' => $event->id,
                            'round' => $round,
                            'position' => $positionInRound,
                            'player_name' => $this->getPlayerName($fighter2),
                            'is_winner' => $isWinner2,
                            'next_match_position' => $round < $maxRound ? (int) ceil($positionInRound / 2) : null
                        ]);

                        $positionInRound++;
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