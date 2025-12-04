<?php

use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
   public function up(): void
   {
      Schema::create('reviews', function (Blueprint $table) {
         $table->id();
         $table->foreignIdFor(User::class)
            ->nullable()
            ->constrained()
            ->nullOnDelete();
         $table->foreignIdFor(Product::class)
            ->constrained()
            ->cascadeOnDelete();
         $table->text('text')->nullable();
         $table->tinyInteger('evaluation')->unsigned();
         $table->timestamps();
      });

      Schema::create('claims', function (Blueprint $table) {
         $table->id();
         $table->foreignIdFor(User::class)
            ->nullable()
            ->constrained()
            ->nullOnDelete();
         $table->enum('entity', ['product']);
         $table->string('entity_id');
         $table->string('topic');
         $table->text('text');
         $table->timestamp('created_at')->useCurrent();
      });
   }

   public function down(): void
   {
      Schema::dropIfExists('reviews');
      Schema::dropIfExists('claims');
   }
};
