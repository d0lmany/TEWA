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
        Schema::table('banner_clicks', function (Blueprint $table) {
            $table->foreign(['banner_id'], 'banner_clicks_ibfk_1')->references(['id'])->on('banners')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign(['user_id'], 'banner_clicks_ibfk_2')->references(['id'])->on('users')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('banner_clicks', function (Blueprint $table) {
            $table->dropForeign('banner_clicks_ibfk_1');
            $table->dropForeign('banner_clicks_ibfk_2');
        });
    }
};
