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
        Schema::create('favorite_lists', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name')->index('name');
            $table->unsignedBigInteger('user_id')->index('user_id_fk');
            $table->dateTime('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('favorite_lists');
    }
};
