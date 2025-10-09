<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description');
            $table->unsignedBigInteger('category_id');
            $table->enum('brand', ['Mezz', 'Predator', 'Cuetec', 'Other'])->default('Other');
            $table->enum('level', ['professional', 'beginner', 'under50', 'cue-cases']);
            $table->enum('condition', ['new', 'used'])->default('new');
            $table->integer('quantity')->default(0);
            $table->string('sku')->unique()->nullable();
            $table->json('images')->nullable();
            $table->integer('weight')->nullable()->default(0);
            $table->integer('length')->nullable()->default(0);
            $table->integer('breadth')->nullable()->default(0);
            $table->integer('width')->nullable()->default(0);
            $table->decimal('pricing', 10, 0);
            $table->decimal('discount', 3, 1)->nullable()->default(0);
            $table->timestamps();
            $table->foreign('category_id')
                ->references('id')
                ->on('categories')
                ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('products');
    }
};
