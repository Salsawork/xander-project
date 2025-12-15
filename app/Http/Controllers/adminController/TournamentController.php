<?php

namespace App\Http\Controllers\adminController;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use App\Models\Bracket;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Xoco70\LaravelTournaments\Exceptions\TreeGenerationException;
use Xoco70\LaravelTournaments\Models\Championship;
use Xoco70\LaravelTournaments\Models\ChampionshipSettings;
use Xoco70\LaravelTournaments\Models\Competitor;
use Xoco70\LaravelTournaments\Models\Team;
use Xoco70\LaravelTournaments\Models\Tournament;
use App\Models\Event;

/**
 * TournamentController - REFACTORED VERSION
 * 
 * Features:
 * - Proper validation for bracket size (2-256 players)
 * - Participant count validation
 * - Single & Double Elimination support
 * - Clean error handling
 * - Better logging
 */
class TournamentController extends Controller
{
    /**
     * Display tournament list
     */
    public function index(Request $request)
    {
        $query = Tournament::with('event');

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $tournaments = $query->orderBy('created_at', 'desc')->get();

        return view('dash.admin.tournament.index', compact('tournaments'));
    }

    /**
     * Show tournament (redirect to event)
     */
    public function show(Tournament $tournament)
    {
        if (!$tournament->event_id) {
            return redirect()->route('tournament.edit', $tournament->slug)
                ->with('error', 'Tournament belum terhubung dengan event');
        }

        return redirect()->route('events.show', ['event' => $tournament->event_id]);
    }

    /**
     * Show create form
     */
    public function create()
    {
        // Get events that don't have tournaments yet
        $usedEventIds = Tournament::whereNotNull('event_id')
            ->pluck('event_id')
            ->toArray();

        $events = Event::whereNotIn('id', $usedEventIds)
            ->orderBy('start_date', 'desc')
            ->get();

        if ($events->isEmpty()) {
            return redirect()->route('tournament.index')
                ->with('error', 'Tidak ada event tersedia. Silakan buat event terlebih dahulu.');
        }

        return view('dash.admin.tournament.create', compact('events'));
    }

    /**
     * Store new tournament
     */
    public function store(Request $request)
    {
        // Validate input
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'numFighters' => 'required|integer|min:2|max:256',
            'treeType' => 'required|in:0,1',
            'fightingAreas' => 'required|in:1,2,4,8',
            'event_id' => 'required|exists:events,id',
        ]);

        // Validate event not already used
        if ($this->isEventAlreadyUsed($data['event_id'])) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['event_id' => 'Event ini sudah memiliki tournament. Silakan pilih event lain.']);
        }

        // Validate bracket size
        $validationError = $this->validateBracketSize(
            (int) $data['numFighters'], 
            $data['event_id']
        );
        
        if ($validationError) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['bracket_error' => $validationError]);
        }

        DB::beginTransaction();

        try {
            // 1. Create Tournament
            $tournament = $this->createTournament($data);

            // 2. Create Championship
            $championship = $this->createChampionship($tournament);

            // 3. Update Event finals_format
            $this->updateEventFormat($data['event_id'], $data['treeType']);

            // 4. Provision fighters (create dummy data for now)
            $championship = $this->provisionFighters(
                $request,
                (int) $data['numFighters'],
                $tournament
            );

            // 5. Generate tournament tree
            $this->generateTournamentTree($championship);

            // 6. Generate brackets for display
            $event = Event::find($data['event_id']);
            $this->generateBracketsFromChampionship($event, $championship);

            DB::commit();

            Log::info('Tournament created successfully', [
                'tournament_id' => $tournament->id,
                'event_id' => $data['event_id'],
                'num_fighters' => $data['numFighters']
            ]);

            return redirect()->route('tournament.edit', $tournament->slug)
                ->with('success', 'Tournament dan Bracket berhasil dibuat!');

        } catch (TreeGenerationException $e) {
            DB::rollBack();
            Log::error('Tree generation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()
                ->withInput()
                ->withErrors(['tree_error' => 'Gagal generate bracket: ' . $e->getMessage()]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Tournament creation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()
                ->withInput()
                ->withErrors(['error' => 'Error: ' . $e->getMessage()]);
        }
    }

    /**
     * Show edit form
     */
    public function edit(Tournament $tournament)
    {
        $tournament->load(
            'competitors',
            'championships.settings',
            'championships.category'
        );

        // Get available events (exclude used by other tournaments)
        $usedEventIds = Tournament::whereNotNull('event_id')
            ->where('id', '!=', $tournament->id)
            ->pluck('event_id')
            ->toArray();

        $events = Event::whereNotIn('id', $usedEventIds)
            ->orderBy('start_date', 'desc')
            ->get();

        return view('dash.admin.tournament.edit', compact('tournament', 'events'));
    }

    /**
     * Update tournament
     */
    public function update(Tournament $tournament, Championship $championship, Request $request)
    {
        // Validate input
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'numFighters' => 'required|integer|min:2|max:256',
            'treeType' => 'required|in:0,1',
            'fightingAreas' => 'required|in:1,2,4,8',
            'event_id' => 'required|exists:events,id',
        ]);

        // Validate event not used by other tournaments
        if ($this->isEventAlreadyUsed($data['event_id'], $tournament->id)) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['event_id' => 'Event ini sudah memiliki tournament lain. Silakan pilih event lain.']);
        }

        // Validate bracket size
        $validationError = $this->validateBracketSize(
            (int) $data['numFighters'],
            $data['event_id']
        );

        if ($validationError) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['bracket_error' => $validationError]);
        }

        DB::beginTransaction();

        try {
            // 1. Update Tournament basic info
            $tournament->update([
                'name' => $data['name'],
                'event_id' => $data['event_id'],
            ]);

            // 2. Delete old tournament data
            $this->deleteChampionshipData($championship->id);

            // 3. Provision new fighters
            $championship = $this->provisionFighters(
                $request,
                (int) $data['numFighters'],
                $tournament
            );

            // 4. Generate new tree
            $this->generateTournamentTree($championship);

            // 5. Update Event format and generate brackets
            $event = Event::find($data['event_id']);
            $this->updateEventFormat($data['event_id'], $data['treeType']);
            $this->generateBracketsFromChampionship($event, $championship);

            DB::commit();

            Log::info('Tournament updated successfully', [
                'tournament_id' => $tournament->id,
                'event_id' => $data['event_id'],
                'num_fighters' => $data['numFighters']
            ]);

            return back()
                ->with('success', 'Tournament berhasil diupdate!')
                ->with('numFighters', $data['numFighters']);

        } catch (TreeGenerationException $e) {
            DB::rollBack();
            Log::error('Tree generation failed on update', [
                'tournament_id' => $tournament->id,
                'error' => $e->getMessage()
            ]);

            return redirect()->back()
                ->withErrors(['tree_error' => 'Gagal generate bracket: ' . $e->getMessage()]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Tournament update failed', [
                'tournament_id' => $tournament->id,
                'error' => $e->getMessage()
            ]);

            return redirect()->back()
                ->withErrors(['error' => 'Error: ' . $e->getMessage()]);
        }
    }

    /**
     * Delete tournament
     */
    public function destroy(Tournament $tournament)
    {
        try {
            $tournamentName = $tournament->name;
            $tournament->delete();

            Log::info('Tournament deleted', [
                'tournament_name' => $tournamentName
            ]);

            return redirect()->route('tournament.index')
                ->with('success', 'Tournament berhasil dihapus');

        } catch (\Exception $e) {
            Log::error('Tournament deletion failed', [
                'tournament_id' => $tournament->id,
                'error' => $e->getMessage()
            ]);

            return redirect()->back()
                ->with('error', 'Gagal menghapus tournament: ' . $e->getMessage());
        }
    }

    // =========================================================================
    // PRIVATE HELPER METHODS
    // =========================================================================

    /**
     * Check if event is already used by another tournament
     */
    private function isEventAlreadyUsed($eventId, $excludeTournamentId = null)
    {
        $query = Tournament::where('event_id', $eventId);

        if ($excludeTournamentId) {
            $query->where('id', '!=', $excludeTournamentId);
        }

        return $query->exists();
    }

    /**
     * Validate bracket size and participant count
     */
    private function validateBracketSize($numFighters, $eventId = null)
    {
        // Basic range validation
        if ($numFighters < 2) {
            return "Minimal jumlah pemain adalah 2 orang";
        }

        if ($numFighters > 256) {
            return "Maksimal jumlah pemain adalah 256 orang untuk performa sistem";
        }

        // Participant count validation (if event provided)
        if ($eventId) {
            $registeredCount = $this->getEventParticipantCount($eventId);

            if ($registeredCount > 0) {
                // Error: Too many participants for bracket
                if ($registeredCount > $numFighters) {
                    return "Jumlah peserta terdaftar ({$registeredCount}) melebihi kapasitas bracket ({$numFighters}). " .
                           "Silakan pilih bracket size yang lebih besar atau kurangi jumlah peserta.";
                }

                // Warning: Bracket too large for participants
                if ($registeredCount < ($numFighters / 2)) {
                    session()->flash('warning', 
                        "Bracket size ({$numFighters}) jauh lebih besar dari peserta terdaftar ({$registeredCount}). " .
                        "Pertimbangkan menggunakan bracket yang lebih kecil untuk efisiensi."
                    );
                }
            }
        }

        return null;
    }

    /**
     * Get participant count for an event
     * Tries multiple possible relations
     */
    private function getEventParticipantCount($eventId)
    {
        try {
            $event = Event::find($eventId);
            if (!$event) return 0;

            // Try different possible relations
            $relations = ['participants', 'registrations', 'users', 'attendees'];

            foreach ($relations as $relation) {
                if (method_exists($event, $relation)) {
                    try {
                        return $event->$relation()->count();
                    } catch (\Exception $e) {
                        continue;
                    }
                }
            }

            // Fallback: Check tournament competitors if exists
            if ($event->tournament) {
                $championship = $event->tournament->championships()->first();
                if ($championship) {
                    return $championship->users()->count();
                }
            }

            return 0;

        } catch (\Exception $e) {
            Log::warning('Could not count event participants', [
                'event_id' => $eventId,
                'error' => $e->getMessage()
            ]);
            return 0;
        }
    }

    /**
     * Create tournament record
     */
    private function createTournament(array $data)
    {
        return Tournament::create([
            'name' => $data['name'],
            'user_id' => auth()->id(),
            'slug' => uniqid() . '-' . time(),
            'dateIni' => now(),
            'dateFin' => now()->addDays(7),
            'event_id' => $data['event_id'],
        ]);
    }

    /**
     * Create championship for tournament
     */
    private function createChampionship(Tournament $tournament)
    {
        return $tournament->championships()->create([
            'name' => $tournament->name . ' Championship',
            'category_id' => 1, // Adjust as needed
        ]);
    }

    /**
     * Update event finals format
     */
    private function updateEventFormat($eventId, $treeType)
    {
        $event = Event::find($eventId);
        
        if ($event) {
            $finalsFormat = ($treeType == 1) ? 'Single Elimination' : 'Double Elimination';
            
            $event->update([
                'finals_format' => $finalsFormat,
            ]);
        }
    }

    /**
     * Provision fighters (create dummy data)
     * TODO: Replace with actual participant assignment
     */
    private function provisionFighters(Request $request, $numFighters, Tournament $tournament)
    {
        $isTeam = (int) ($request->isTeam ?? 0);

        if ($isTeam) {
            $championship = Championship::find($tournament->championships[1]->id);
            factory(Team::class, $numFighters)->create([
                'championship_id' => $championship->id
            ]);
        } else {
            $championship = Championship::find($tournament->championships[0]->id);
            $users = factory(User::class, $numFighters)->create();
            
            foreach ($users as $user) {
                factory(Competitor::class)->create([
                    'championship_id' => $championship->id,
                    'user_id' => $user->id,
                    'confirmed' => 1,
                    'short_id' => $user->id,
                ]);
            }
        }

        // Save championship settings
        $championship->settings = ChampionshipSettings::createOrUpdate($request, $championship);

        return $championship;
    }

    /**
     * Generate tournament tree structure
     */
    private function generateTournamentTree(Championship $championship)
    {
        $generation = $championship->chooseGenerationStrategy();
        $generation->run();

        Log::info('Tournament tree generated', [
            'championship_id' => $championship->id,
            'tree_type' => $championship->getSettings()->treeType
        ]);
    }

    /**
     * Delete all championship data
     */
    private function deleteChampionshipData($championshipId)
    {
        $fightersGroups = DB::table('fighters_groups')
            ->where('championship_id', $championshipId)
            ->get();

        foreach ($fightersGroups as $fightersGroup) {
            DB::table('fight')->where('fighters_group_id', $fightersGroup->id)->delete();
            DB::table('fighters_group_competitor')->where('fighters_group_id', $fightersGroup->id)->delete();
            DB::table('fighters_group_team')->where('fighters_group_id', $fightersGroup->id)->delete();
        }

        DB::table('fighters_groups')->where('championship_id', $championshipId)->delete();
        DB::table('competitor')->where('championship_id', $championshipId)->delete();
        DB::table('team')->where('championship_id', $championshipId)->delete();

        Log::info('Championship data deleted', [
            'championship_id' => $championshipId
        ]);
    }

    // =========================================================================
    // BRACKET GENERATION METHODS
    // =========================================================================

    /**
     * Generate brackets from championship (main entry point)
     */
    private function generateBracketsFromChampionship(Event $event, Championship $championship)
    {
        // Clear existing brackets
        Bracket::where('event_id', $event->id)->delete();

        // Get all fighter groups
        $fightersGroups = $championship->fightersGroups()
            ->where('round', '>=', 1)
            ->with('fights')
            ->orderBy('round')
            ->orderBy('order')
            ->get();

        if ($fightersGroups->isEmpty()) {
            Log::warning('No fighter groups found for bracket generation', [
                'event_id' => $event->id,
                'championship_id' => $championship->id
            ]);
            return;
        }

        // Determine tournament type
        $settings = $championship->getSettings();
        $isDoubleElimination = ($settings->treeType == 0);

        Log::info('Generating brackets', [
            'event_id' => $event->id,
            'championship_id' => $championship->id,
            'is_double_elimination' => $isDoubleElimination,
            'total_groups' => $fightersGroups->count()
        ]);

        // Generate appropriate bracket type
        if ($isDoubleElimination) {
            $this->generateDoubleEliminationBrackets($event, $championship, $fightersGroups);
        } else {
            $this->generateSingleEliminationBrackets($event, $championship, $fightersGroups);
        }

        // Update winners from fight results
        $this->updateWinnersFromFights($event, $championship);

        Log::info('Brackets generated successfully', [
            'event_id' => $event->id,
            'total_brackets' => Bracket::where('event_id', $event->id)->count()
        ]);
    }

    /**
     * Generate double elimination brackets
     */
    private function generateDoubleEliminationBrackets($event, $championship, $allGroups)
    {
        $round1Groups = $allGroups->where('round', 1);
        $numFighters = $round1Groups->count() * 2;
        $numRounds = intval(log($numFighters, 2));

        $upperBracketEnd = $numRounds + 1;
        $lowerBracketStart = $upperBracketEnd + 1;
        $maxRound = $allGroups->max('round');
        $grandFinalRound = $maxRound;

        Log::info('Double Elimination structure', [
            'fighters' => $numFighters,
            'round_1' => 1,
            'upper_bracket' => "2-{$upperBracketEnd}",
            'lower_bracket' => "{$lowerBracketStart}-" . ($grandFinalRound - 1),
            'grand_final' => $grandFinalRound
        ]);

        // Generate Round 1
        $this->generateRound1Brackets($event, $championship, $allGroups);

        // Generate Upper Bracket
        $this->generateUpperBracketBrackets($event, $championship, $allGroups, $upperBracketEnd);

        // Generate Lower Bracket
        $this->generateLowerBracketBrackets($event, $championship, $allGroups, $lowerBracketStart, $grandFinalRound);

        // Generate Grand Final
        $this->generateGrandFinalBrackets($event, $championship, $allGroups, $grandFinalRound);
    }

    /**
     * Generate Round 1 brackets
     */
    private function generateRound1Brackets($event, $championship, $allGroups)
    {
        $position = 1;
        $groups = $allGroups->where('round', 1);

        foreach ($groups as $group) {
            $fight = $group->fights->first();

            // Fighter 1
            $this->createBracketEntry($event, $championship, $fight, 1, $position, 'c1', 'round_1');
            $position++;

            // Fighter 2
            $this->createBracketEntry($event, $championship, $fight, 1, $position, 'c2', 'round_1');
            $position++;
        }
    }

    /**
     * Generate Upper Bracket brackets
     */
    private function generateUpperBracketBrackets($event, $championship, $allGroups, $upperBracketEnd)
    {
        for ($round = 2; $round <= $upperBracketEnd; $round++) {
            $position = 1;
            $groups = $allGroups->where('round', $round);

            foreach ($groups as $group) {
                $fight = $group->fights->first();

                // Fighter 1
                $this->createBracketEntry($event, $championship, $fight, $round, $position, 'c1', 'upper', $upperBracketEnd);
                $position++;

                // Fighter 2
                $this->createBracketEntry($event, $championship, $fight, $round, $position, 'c2', 'upper', $upperBracketEnd);
                $position++;
            }
        }
    }

    /**
     * Generate Lower Bracket brackets
     */
    private function generateLowerBracketBrackets($event, $championship, $allGroups, $lowerBracketStart, $grandFinalRound)
    {
        for ($round = $lowerBracketStart; $round < $grandFinalRound; $round++) {
            $position = 1;
            $groups = $allGroups->where('round', $round);

            foreach ($groups as $group) {
                $fight = $group->fights->first();

                // Fighter 1
                $this->createBracketEntry($event, $championship, $fight, $round, $position, 'c1', 'lower', $grandFinalRound);
                $position++;

                // Fighter 2
                $this->createBracketEntry($event, $championship, $fight, $round, $position, 'c2', 'lower', $grandFinalRound);
                $position++;
            }
        }
    }

    /**
     * Generate Grand Final brackets
     */
    private function generateGrandFinalBrackets($event, $championship, $allGroups, $grandFinalRound)
    {
        $grandFinalGroup = $allGroups->where('round', $grandFinalRound)->first();
        
        if ($grandFinalGroup) {
            $fight = $grandFinalGroup->fights->first();

            // Upper winner (c1)
            $this->createBracketEntry($event, $championship, $fight, $grandFinalRound, 1, 'c1', 'grand_final');

            // Lower winner (c2)
            $this->createBracketEntry($event, $championship, $fight, $grandFinalRound, 2, 'c2', 'grand_final');
        }
    }

    /**
     * Generate single elimination brackets
     */
    private function generateSingleEliminationBrackets($event, $championship, $allGroups)
    {
        $round1Groups = $allGroups->where('round', 1);
        $totalPlayers = $round1Groups->count() * 2;
        $maxRound = (int) ceil(log($totalPlayers, 2));

        Log::info('Single Elimination structure', [
            'total_players' => $totalPlayers,
            'max_rounds' => $maxRound
        ]);

        for ($round = 1; $round <= $maxRound; $round++) {
            $matchesInRound = (int) ($totalPlayers / pow(2, $round));
            $position = 1;

            for ($matchNum = 1; $matchNum <= $matchesInRound; $matchNum++) {
                $group = $allGroups->where('round', $round)
                    ->where('order', $matchNum)
                    ->first();

                $fight = ($group && $group->fights->isNotEmpty()) ? $group->fights->first() : null;

                // Fighter 1
                $this->createBracketEntry($event, $championship, $fight, $round, $position, 'c1', 'single', $maxRound);
                $position++;

                // Fighter 2
                $this->createBracketEntry($event, $championship, $fight, $round, $position, 'c2', 'single', $maxRound);
                $position++;
            }
        }
    }

    /**
     * Create a single bracket entry
     */
    private function createBracketEntry($event, $championship, $fight, $round, $position, $fighterSlot, $bracketType, $maxRound = null)
    {
        $playerName = 'TBD';
        $isWinner = false;

        if ($fight && $fight->$fighterSlot) {
            $fighter = $this->getFighterById($fight->$fighterSlot, $championship);
            $playerName = $fighter ? $this->getPlayerName($fighter) : 'TBD';
            $isWinner = ($fight->winner_id == $fight->$fighterSlot);
        }

        // Calculate next match position
        $nextMatchPosition = null;
        if ($maxRound) {
            if ($bracketType === 'single' && $round < $maxRound) {
                $nextMatchPosition = (int) ceil($position / 2);
            } elseif ($bracketType === 'round_1') {
                $nextMatchPosition = (int) ceil($position / 2);
            } elseif ($bracketType === 'upper' && $round < $maxRound) {
                $nextMatchPosition = (int) ceil($position / 2);
            } elseif ($bracketType === 'lower' && $round < ($maxRound - 1)) {
                $nextMatchPosition = (int) ceil($position / 2);
            }
        }

        Bracket::create([
            'event_id' => $event->id,
            'round' => $round,
            'position' => $position,
            'player_name' => $playerName,
            'is_winner' => $isWinner,
            'next_match_position' => $nextMatchPosition,
            'bracket_type' => $bracketType
        ]);
    }

    /**
     * Update winners from fight results
     */
    private function updateWinnersFromFights(Event $event, Championship $championship)
    {
        $fights = $championship->fights()
            ->whereNotNull('winner_id')
            ->with('winner')
            ->get();

        foreach ($fights as $fight) {
            if ($fight->winner) {
                $winnerName = $fight->winner->fullName ?? $fight->winner->name;

                Bracket::where('event_id', $event->id)
                    ->where('player_name', $winnerName)
                    ->where('round', $fight->round ?? 1)
                    ->update(['is_winner' => true]);
            }
        }
    }

    /**
     * Get fighter by ID
     */
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

    /**
     * Get player name from fighter object
     */
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