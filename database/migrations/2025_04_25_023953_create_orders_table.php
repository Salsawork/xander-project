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
        Schema::create('orders', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('user_id')->constrained()->onDelete('restrict');
            $table->string('order_number')->unique();
            $table->string('order_type')->nullable();
            $table->decimal('total', 12, 0)->default(0);
            $table->enum('payment_status', [
                'pending',
                'processing',
                'paid',
                'failed',
                'refunded'
            ])->default('pending')->index();
            $table->enum('delivery_status', [
                'pending',
                'processing',
                'packed',
                'shipped',
                'delivered',
                'cancelled',
                'returned'
            ])->default('pending')->index();
            $table->string('payment_method')->nullable();
            $table->unsignedBigInteger('bank_id')->nullable();
            $table->string('no_rekening')->nullable();
            $table->string('atas_nama')->nullable();
            $table->string('file')->nullable();
            $table->string('snap_token')->nullable();
            $table->timestamp('expired_at')->nullable(); 
            $table->timestamps();
            $table->softDeletes();
            $table->index('created_at');
            $table->index(['payment_status', 'delivery_status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
