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
        Schema::create('billiard_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('venue_id')->constrained('venues')->onDelete('cascade');
            $table->string('title');
            $table->string('session_code')->unique();
            $table->string('game_type');
            $table->string('skill_level');
            $table->decimal('price', 10, 2);
            $table->integer('max_participants');
            $table->date('date');
            $table->time('start_time');
            $table->time('end_time');
            $table->string('promo_code')->nullable();
            $table->enum('status', ['pending', 'confirmed', 'cancelled', 'completed'])->default('pending');
            $table->timestamps();
            
            // Tambah index untuk performa query
            $table->index('venue_id');
            $table->index('date');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('billiard_sessions');
    }
};
