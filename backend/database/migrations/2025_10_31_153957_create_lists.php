<?php

use App\Models\User;
use App\Models\Product;
use App\Models\FavoriteList;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
   public function up(): void
   {
      Schema::create('favorite_lists', function (Blueprint $table) {
         $table->id();
         $table->string('name');
         $table->foreignIdFor(User::class)
            ->constrained()
            ->cascadeOnDelete();
         $table->timestamp('created_at')->useCurrent();

         $table->unique(['user_id', 'name'], '');
      });

      DB::unprepared('
         CREATE TRIGGER createFavList 
         AFTER INSERT ON users 
         FOR EACH ROW 
         INSERT INTO favorite_lists (name, user_id) 
         VALUES ("__favorite__", NEW.id)
      ');
      
      Schema::create('favorite_list_items', function (Blueprint $table) {
         $table->id();
         $table->foreignIdFor(FavoriteList::class, 'list_id')
            ->constrained()
            ->cascadeOnDelete();
         $table->foreignIdFor(Product::class)
            ->nullable()
            ->constrained()
            ->nullOnDelete();
         $table->timestamp('added_at')->useCurrent();
      });

      Schema::create('cart_items', function (Blueprint $table) {
         $table->id();
         $table->foreignIdFor(User::class)
            ->constrained()
            ->cascadeOnDelete();
         $table->foreignIdFor(Product::class)
            ->nullable()
            ->constrained()
            ->nullOnDelete();
         $table->unsignedInteger('quantity');
         $table->text('product_attributes');
      });
   }

   public function down(): void
   {
      DB::unprepared("DROP TRIGGER IF EXISTS `createFavList`");
      Schema::dropIfExists('favorite_list_items');
      Schema::dropIfExists('favorite_lists');
      Schema::dropIfExists('cart_items');
   }
};
