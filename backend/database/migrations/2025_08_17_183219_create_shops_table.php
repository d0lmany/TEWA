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
        Schema::create('shops', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name', 300)->unique('name_u');
            $table->string('avatar')->nullable();
            $table->text('description')->nullable();
            $table->unsignedBigInteger('seller_id')->index('seller_id');
            $table->decimal('rating', 3)->nullable();
            $table->string('city');
            $table->enum('payment_type', ['crypto', 'cash', 'card', 'system']);
            $table->decimal('fee', 5)->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shops');
    }
};
