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
     * Provision objects for tournament
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
     * Update tree - Sync SEMUA data dari tree ke bracket
     */
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
            // Save fight results
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

            \Log::info('Fights saved', [
                'championship_id' => $championship->id,
                'total_groups' => $groups->count()
            ]);

            // Sync ke brackets
            $tournament = $championship->tournament;
            if ($tournament && $tournament->event_id) {
                $event = Event::find($tournament->event_id);
                if ($event) {
                    \Log::info('Starting bracket sync', [
                        'event_id' => $event->id,
                        'tournament_id' => $tournament->id
                    ]);

                    // Sync SEMUA fighters dari SEMUA rounds
                    $this->syncAllRoundsToBracket($event, $championship);

                    \Log::info('Bracket sync completed', ['event_id' => $event->id]);
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
     * Get winner ID from fighters and scores
     */
    public function getWinnerId($fighters, $scores, $numFighter)
    {
        return $scores[$numFighter] != null ? $fighters[$numFighter] : null;
    }

    /**
     * Sync SEMUA rounds dari tree ke bracket
     */
    private function syncAllRoundsToBracket($event, $championship)
    {
        // Hapus semua brackets lama untuk event ini
        Bracket::where('event_id', $event->id)->delete();
        
        \Log::info('Deleted old brackets', ['event_id' => $event->id]);

        // Loop SEMUA fighters groups (semua rounds)
        $allGroups = $championship->fightersGroups()
            ->with('fights')
            ->orderBy('round')
            ->orderBy('order')
            ->get();

        \Log::info('Total groups to sync', ['count' => $allGroups->count()]);

        $bracketPosition = 1;

        foreach ($allGroups->groupBy('round') as $round => $roundGroups) {
            \Log::info('Processing round', ['round' => $round, 'groups' => $roundGroups->count()]);
            
            $positionInRound = 1;

            foreach ($roundGroups as $group) {
                // Get fights untuk group ini
                $fight = $group->fights->first();
                
                // Get fighter 1
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
                            'next_match_position' => $this->calculateNextPosition($round, $positionInRound, $allGroups)
                        ]);

                        \Log::info('Created bracket for fighter 1', [
                            'round' => $round,
                            'position' => $positionInRound,
                            'player' => $this->getPlayerName($fighter1),
                            'is_winner' => $isWinner1
                        ]);

                        $positionInRound++;
                    }
                }

                // Get fighter 2
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
                            'next_match_position' => $this->calculateNextPosition($round, $positionInRound, $allGroups)
                        ]);

                        \Log::info('Created bracket for fighter 2', [
                            'round' => $round,
                            'position' => $positionInRound,
                            'player' => $this->getPlayerName($fighter2),
                            'is_winner' => $isWinner2
                        ]);

                        $positionInRound++;
                    }
                }
            }
        }

        // Summary log
        $totalBrackets = Bracket::where('event_id', $event->id)->count();
        \Log::info('Bracket sync summary', [
            'event_id' => $event->id,
            'total_brackets_created' => $totalBrackets
        ]);
    }

    /**
     * Get fighter by ID
     */
    private function getFighterById($fighterId, $championship)
    {
        // Try Competitor first
        $competitor = Competitor::where('championship_id', $championship->id)
            ->where('id', $fighterId)
            ->first();
        
        if ($competitor) {
            return $competitor;
        }

        // Try Team
        $team = Team::where('championship_id', $championship->id)
            ->where('id', $fighterId)
            ->first();

        return $team;
    }

    /**
     * Calculate next match position
     */
    private function calculateNextPosition($currentRound, $currentPosition, $allGroups)
    {
        $maxRound = $allGroups->max('round');
        
        if ($currentRound >= $maxRound) {
            return null; // Final round
        }

        return (int) ceil($currentPosition / 2);
    }

    /**
     * Get player name from fighter object
     */
    private function getPlayerName($fighter)
    {
        if (!$fighter) return 'TBD';
        
        // Try fullName first
        if (isset($fighter->fullName)) {
            return $fighter->fullName;
        }
        
        // Try name
        if (isset($fighter->name)) {
            return $fighter->name;
        }
        
        // Try user->name for Competitor
        if (isset($fighter->user) && isset($fighter->user->name)) {
            return $fighter->user->name;
        }
        
        return 'Unknown';
    }
}