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
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id')->index('user_id_fk');
            $table->enum('status', ['pending', 'paid', 'shipped', 'delivered', 'canceled']);
            $table->decimal('total', 12, 0);
            $table->json('locations');
            $table->unsignedBigInteger('deliver_pickup')->index('deliver_pickup_fk');
            $table->unsignedBigInteger('deliver_address')->nullable()->index('deliver_address_fk');
            $table->dateTime('created_at');
            $table->dateTime('updated_at');
            $table->boolean('hidden')->index('hidden_i');
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
