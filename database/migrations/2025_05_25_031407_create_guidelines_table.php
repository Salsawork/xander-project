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
        Schema::create('guidelines', function (Blueprint $table) {
            $table->id();
            $table->string('title', 255);
            $table->string('slug', 255)->unique();
            $table->text('description')->nullable();
            $table->longText('content')->nullable();
            $table->enum('skill_level', ['beginner', 'intermediate', 'master', 'general']);
            $table->string('category', 100)->nullable();
            $table->string('tags', 500)->nullable();
            $table->string('featured_image', 255)->nullable();
            $table->string('youtube_url', 255)->nullable();
            $table->boolean('is_new')->default(false);
            $table->boolean('is_featured')->default(false);
            $table->integer('views_count')->default(0);
            $table->integer('reading_time_minutes')->nullable();
            $table->string('author_name', 100)->nullable();
            $table->timestamp('published_at')->nullable();
            $table->enum('status', ['draft', 'published', 'archived'])->default('draft');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('guidelines');
    }
};
