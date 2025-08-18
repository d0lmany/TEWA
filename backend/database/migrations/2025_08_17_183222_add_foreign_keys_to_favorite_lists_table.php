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
        Schema::table('favorite_lists', function (Blueprint $table) {
            $table->foreign(['user_id'], 'favorite_lists_ibfk_1')->references(['id'])->on('users')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('favorite_lists', function (Blueprint $table) {
            $table->dropForeign('favorite_lists_ibfk_1');
        });
    }
};
