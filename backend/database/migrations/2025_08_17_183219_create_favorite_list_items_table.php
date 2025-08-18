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
        Schema::create('favorite_list_items', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('list_id')->index('list_id_fk');
            $table->unsignedBigInteger('product_id')->index('product_id_fk');
            $table->dateTime('added_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('favorite_list_items');
    }
};
