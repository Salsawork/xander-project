<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('image_url');
            $table->date('start_date');
            $table->date('end_date');
            $table->string('location');
            $table->string('game_types');
            $table->text('description')->nullable();
            $table->decimal('total_prize_money', 10, 2);
            $table->decimal('champion_prize', 10, 2);
            $table->decimal('runner_up_prize', 10, 2);
            $table->decimal('third_place_prize', 10, 2);
            $table->string('match_style');
            $table->string('finals_format');
            $table->string('divisions');
            $table->string('social_media_handle');
            $table->enum('status', ['Upcoming', 'Ongoing', 'Ended'])->default('Upcoming');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
