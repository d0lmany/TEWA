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
        Schema::table('seller_moderations', function (Blueprint $table) {
            $table->foreign(['seller_id'], 'seller_moderations_ibfk_1')->references(['id'])->on('sellers')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('seller_moderations', function (Blueprint $table) {
            $table->dropForeign('seller_moderations_ibfk_1');
        });
    }
};
