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
        Schema::table('pending_reviews', function (Blueprint $table) {
            $table->foreign(['user_id'], 'pending_reviews_ibfk_1')->references(['id'])->on('users')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign(['product_id'], 'pending_reviews_ibfk_2')->references(['id'])->on('products')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pending_reviews', function (Blueprint $table) {
            $table->dropForeign('pending_reviews_ibfk_1');
            $table->dropForeign('pending_reviews_ibfk_2');
        });
    }
};
