<?php

namespace App\Http\Controllers\adminController;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Xoco70\LaravelTournaments\Exceptions\TreeGenerationException;
use Xoco70\LaravelTournaments\Models\Championship;
use Xoco70\LaravelTournaments\Models\ChampionshipSettings;
use Xoco70\LaravelTournaments\Models\Competitor;
use Xoco70\LaravelTournaments\Models\Team;
use Xoco70\LaravelTournaments\Models\Tournament;
use App\Models\Event;


class TournamentController extends Controller
{
    public function index(Request $request)
    {
        $query = Tournament::query();

        // Jika ada parameter search, filter berdasarkan nama tournament
        if ($request->has('search') && $request->search != '') {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        // Urutkan data terbaru
        $tournaments = $query->orderBy('created_at', 'desc')->get();

        return view('dash.admin.tournament.index', compact('tournaments'));
    }


    public function create()
    {
        // Show the form to create a new tournament
        return view('dash.admin.tournament.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            // Tambahkan validasi lain jika diperlukan
        ]);

        $data['user_id'] = auth()->id();
        $data['slug'] = uniqid() . '-' . time();
        $data['dateIni'] = now();
        $data['dateFin'] = now();

        // 1️⃣ Buat Tournament
        $tournament = Tournament::create($data);

        // 2️⃣ Buat Championship (kalau masih diperlukan)
        $tournament->championships()->create([
            'name' => $tournament->name . ' Championship',
            'category_id' => 1,
        ]);

        // 3️⃣ Buat Event otomatis dari Tournament
        Event::create([
            'tournament_id' => $tournament->id,
            'name' => $tournament->name,
            'image_url' => 'default.jpg', // nanti bisa diganti dari input
            'start_date' => now(),
            'end_date' => now()->addDays(7),
            'location' => 'Belum ditentukan',
            'game_types' => 'Unknown',
            'description' => 'Event otomatis dari tournament: ' . $tournament->name,
            'total_prize_money' => 0,
            'champion_prize' => 0,
            'runner_up_prize' => 0,
            'third_place_prize' => 0,
            'match_style' => 'Unknown',
            'finals_format' => 'Single Elimination',
            'divisions' => 'General',
            'social_media_handle' => '@officialtournament',
            'status' => 'Upcoming',
        ]);

        return redirect()->route('tournament.edit', $tournament->slug)
            ->with('success', 'Tournament dan Event berhasil dibuat!');
    }


    public function edit(Tournament $tournament)
    {
        $tournament->load(
            'competitors',
            'championships.settings',
            'championships.category'
        );

        return view('dash.admin.tournament.edit', compact('tournament'));
    }

    public function update(Tournament $tournament, Championship $championship, Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'hasPreliminary' => 'required',
            'preliminaryGroupSize' => 'required',
            'numFighters' => 'required',
            'isTeam' => 'required',
            'treeType' => 'required',
            'fightingAreas' => 'required',
        ]);

        DB::beginTransaction();

        $this->deleteEverything($championship->id);
        $numFighters = $request->numFighters;
        $isTeam = $request->isTeam ?? 0;
        $championship = $this->provisionObjects($request, $isTeam, $numFighters, $tournament);
        $generation = $championship->chooseGenerationStrategy();

        try {
            $generation->run();

            DB::commit();
        } catch (TreeGenerationException $e) {
            DB::rollBack();
            return redirect()->back()
                ->withErrors($e->getMessage());
        }

        return back()
            ->with('numFighters', $numFighters)
            ->with('isTeam', $isTeam);
    }

    private function deleteEverything($championshipId)
    {
        // Get fighters groups and delete them
        $fightersGroups = DB::table('fighters_groups')->where('championship_id', $championshipId)->get();
        foreach ($fightersGroups as $fightersGroup) {
            DB::table('fight')->where('fighters_group_id', $fightersGroup->id)->delete();
            DB::table('fighters_group_competitor')->where('fighters_group_id', $fightersGroup->id)->delete();
            DB::table('fighters_group_team')->where('fighters_group_id', $fightersGroup->id)->delete();
        }
        DB::table('fighters_groups')->where('championship_id', $championshipId)->delete();
        DB::table('competitor')->where('championship_id', $championshipId)->delete();
        DB::table('team')->where('championship_id', $championshipId)->delete();
    }

    protected function provisionObjects(Request $request, $isTeam, $numFighters, Tournament $tournament)
    {
        if ($isTeam) {
            $championship = Championship::find($tournament->championships[1]->id);
            factory(Team::class, (int) $numFighters)->create(['championship_id' => $championship->id]);
        } else {
            $championship = Championship::find($tournament->championships[0]->id);
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

    public function destroy(Tournament $tournament)
    {
        $tournament->delete();

        return redirect()->route('tournament.index')
            ->with('success', 'Tournament deleted successfully');
    }
}
