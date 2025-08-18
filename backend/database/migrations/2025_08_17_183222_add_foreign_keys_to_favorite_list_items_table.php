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
        Schema::table('favorite_list_items', function (Blueprint $table) {
            $table->foreign(['product_id'], 'favorite_list_items_ibfk_1')->references(['id'])->on('products')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign(['list_id'], 'favorite_list_items_ibfk_2')->references(['id'])->on('favorite_lists')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('favorite_list_items', function (Blueprint $table) {
            $table->dropForeign('favorite_list_items_ibfk_1');
            $table->dropForeign('favorite_list_items_ibfk_2');
        });
    }
};
