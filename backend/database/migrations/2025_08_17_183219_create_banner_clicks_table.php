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
        Schema::create('banner_clicks', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('banner_id')->index('banner_id_fk');
            $table->unsignedBigInteger('user_id')->index('user_id_fk');
            $table->dateTime('clicked_at');
            $table->string('ip', 50);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('banner_clicks');
    }
};
