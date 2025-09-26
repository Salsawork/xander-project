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
        Schema::create('brackets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->onDelete('cascade');
            $table->string('player_name');
            $table->integer('round');
            $table->integer('position');
            $table->integer('next_match_position')->nullable();
            $table->boolean('is_winner')->default(false);
            $table->timestamps();

            // Tambah index untuk pencarian yang lebih cepat
            $table->index(['event_id', 'round']);
            $table->index(['event_id', 'position']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('brackets');
    }
};
