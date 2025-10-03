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
        Schema::create('tables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('venue_id')->constrained('venues')->onDelete('cascade');
            $table->string('table_number', 20);
            $table->enum('status', ['available', 'booked'])->default('available');
            $table->decimal('price_per_hour', 10, 2)->nullable()->after('venue_id');
            $table->timestamps();
            
            // Add index for better performance on frequently queried columns
            $table->index('venue_id');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tables');
    }
};
