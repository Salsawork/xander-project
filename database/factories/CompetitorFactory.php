<?php

use Xoco70\LaravelTournaments\Models\Championship;
use Xoco70\LaravelTournaments\Models\Competitor;

$factory->define(Competitor::class, function (Faker\Generator $faker) {
    $tcs = Championship::pluck('id')->toArray();
    $users = config('laravel-tournaments.user.model')::pluck('id')->toArray();

    $championshipId = $faker->randomElement($tcs);
    $championship = Championship::find($championshipId);
    $tournament = $championship ? $championship->tournament : null;

    return [
        'championship_id' => $championshipId,
        'user_id'         => $faker->randomElement($users),
        'confirmed'       => $faker->numberBetween(0, 1),
        'short_id'        => $tournament && $tournament->competitors()->exists()
                                ? $tournament->competitors()->max('short_id') + 1
                                : 1,
    ];
});

