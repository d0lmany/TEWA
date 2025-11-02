<?php

use App\Models\FavoriteList;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('favorite_list_items', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(FavoriteList::class, 'list_id');
            $table->foreignIdFor(Product::class);
            $table->timestamp('added_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('favorite_list_items');
    }
};
