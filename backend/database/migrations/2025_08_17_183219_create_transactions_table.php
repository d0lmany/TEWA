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
        Schema::create('transactions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('order_id')->index('order_id_fk');
            $table->unsignedBigInteger('user_id')->index('user_id_fk');
            $table->enum('status', ['pending', 'completed', 'failed', 'refunded'])->index('status_i');
            $table->enum('payment_method', ['crypto', 'card', 'cash', 'system']);
            $table->integer('gateway_transaction_id');
            $table->json('gateway_response');
            $table->dateTime('created_at');
            $table->dateTime('updated_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
