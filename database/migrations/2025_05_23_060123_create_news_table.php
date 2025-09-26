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
        Schema::create('news', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('content')->nullable();
            $table->string('image_url')->nullable();
            $table->dateTime('published_at');
            $table->boolean('is_featured')->default(false)->comment('Tampilkan di hero utama');
            $table->boolean('is_popular')->default(false)->comment('Tampilkan di bagian Popular News');
            $table->string('category')->nullable()->comment('Opsional, contoh: Championship, Tips, dll');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('news');
    }
};
