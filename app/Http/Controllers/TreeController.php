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
    /**
     * Display a listing of trees.
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
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

    /**
     * Build Tree.
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\Response|string
     */
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

    /**
     * @param Request $request
     * @param $isTeam
     * @param $numFighters
     *
     * @return Championship
     */
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
     * @param Request $request
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    // Update method update() di TreeController.php
    // Ganti seluruh method dengan versi baru ini

    // Update method update() di TreeController.php
    // Ganti seluruh method dengan versi baru ini

    // Update method update() di TreeController.php
    // Ganti seluruh method dengan versi baru ini

    public function update(Request $request, Championship $championship)
    {
        $numFighter = 0;
        $query = FightersGroup::with('fights')
            ->where('championship_id', $championship->id);

        $fighters = $request->singleElimination_fighters;
        $scores = $request->score;

        if ($championship->hasPreliminary()) {
            $query = $query->where('round', '>', 1);
            $fighters = $request->preliminary_fighters;
        }

        $groups = $query->get();

        DB::beginTransaction();
        try {
            foreach ($groups as $group) {
                foreach ($group->fights as $fight) {
                    $fight->c1 = $fighters[$numFighter];
                    $fight->winner_id = $this->getWinnerId($fighters, $scores, $numFighter);
                    $numFighter++;

                    $fight->c2 = $fighters[$numFighter];
                    if ($fight->winner_id == null) {
                        $fight->winner_id = $this->getWinnerId($fighters, $scores, $numFighter);
                    }
                    $numFighter++;
                    $fight->save();
                }
            }

            // 🆕 AUTO SYNC: Update brackets jika ada event terhubung
            $tournament = $championship->tournament;
            if ($tournament && $tournament->event_id) {
                $event = Event::find($tournament->event_id);
                if ($event) {
                    $this->syncBracketsWithFights($event, $championship);
                }
            }

            DB::commit();
            return back()->with('success', 'Tree updated and brackets synced successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors('Error updating tree: ' . $e->getMessage());
        }
    }

    /**
     * Helper: Dapatkan winner dari fight
     */
    private function getWinnerFromFight($fight)
    {
        if (!$fight->winner_id) {
            return null;
        }

        if ($fight->c1 == $fight->winner_id && $fight->fighter1) {
            return $fight->fighter1;
        }

        if ($fight->c2 == $fight->winner_id && $fight->fighter2) {
            return $fight->fighter2;
        }

        try {
            $competitor = \Xoco70\LaravelTournaments\Models\Competitor::find($fight->winner_id);
            if ($competitor) return $competitor;

            $team = \Xoco70\LaravelTournaments\Models\Team::find($fight->winner_id);
            if ($team) return $team;
        } catch (\Exception $e) {
            \Log::error('Error getting winner', [
                'fight_id' => $fight->id,
                'winner_id' => $fight->winner_id,
                'error' => $e->getMessage()
            ]);
        }

        return null;
    }

    /**
     * Helper: Dapatkan nama winner
     */
    private function getWinnerName($winner)
    {
        if (!$winner) return 'Unknown';
        if (isset($winner->fullName)) return $winner->fullName;
        if (isset($winner->name)) return $winner->name;
        if (isset($winner->user->name)) return $winner->user->name;
        return 'Unknown';
    }

    /**
     * 🆕 Sync brackets dengan fight results
     */
    private function syncBracketsWithFights($event, $championship)
    {
        $fights = $championship->fights()
            ->whereNotNull('winner_id')
            ->get();

        foreach ($fights as $fight) {
            $winner = $this->getWinnerFromFight($fight);
            if (!$winner) continue;

            $winnerName = $this->getWinnerName($winner);

            $fightersGroup = $fight->fightersGroup;
            $round = $fightersGroup ? $fightersGroup->round : 1;

            Bracket::where('event_id', $event->id)
                ->where('player_name', $winnerName)
                ->where('round', $round)
                ->update(['is_winner' => true]);

            $this->advanceWinnerToNextRound($event, $winnerName, $round);
        }
    }

    /**
     * 🆕 Advance winner ke round berikutnya
     */
    private function advanceWinnerToNextRound($event, $winnerName, $currentRound)
    {
        $nextRound = $currentRound + 1;

        // Dapatkan total rounds
        $maxRound = Bracket::where('event_id', $event->id)->max('round');

        if ($currentRound >= $maxRound) {
            return;
        }

        // Tentukan posisi di round berikutnya
        $currentBracket = Bracket::where('event_id', $event->id)
            ->where('player_name', $winnerName)
            ->where('round', $currentRound)
            ->first();

        if (!$currentBracket) return;

        $nextPosition = $currentBracket->next_match_position;
        if (!$nextPosition) return;

        // Update bracket di round berikutnya
        $nextBracket = Bracket::where('event_id', $event->id)
            ->where('round', $nextRound)
            ->where('position', $nextPosition)
            ->first();

        if ($nextBracket && $nextBracket->player_name === 'TBD') {
            $nextBracket->update(['player_name' => $winnerName]);
        }
    }

    public function getWinnerId($fighters, $scores, $numFighter)
    {
        return $scores[$numFighter] != null ? $fighters[$numFighter] : null;
    }
}
