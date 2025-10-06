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
        if (!Schema::hasTable('order_sparrings')) {
            Schema::create('order_sparrings', function (Blueprint $table) {
                $table->id();
                $table->uuid('order_id');
                $table->foreignId('athlete_id')->references('id')->on('users')->onDelete('restrict');
                $table->foreignId('schedule_id')->constrained('sparring_schedules')->onDelete('restrict');
                $table->decimal('price', 10, 0);
                $table->timestamps();

                $table->foreign('order_id')
                    ->references('id')
                    ->on('orders')
                    ->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_sparrings');
    }
};
