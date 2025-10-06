<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('vouchers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('venue_id')->constrained('venues');
            $table->string('name', 100);
            $table->string('code', 50)->unique();
            $table->string('type', 50); // percentage, fixed_amount, free_time
            $table->integer('discount_percentage')->nullable();
            $table->decimal('discount_amount', 10, 2)->nullable();
            $table->decimal('minimum_purchase', 10, 2)->default(0);
            $table->integer('quota');
            $table->integer('claimed')->default(0);
            $table->timestamp('start_date')->nullable();
            $table->timestamp('end_date')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('vouchers');
    }
};