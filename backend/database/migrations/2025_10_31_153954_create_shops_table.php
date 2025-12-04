<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Seller;

return new class extends Migration
{
   public function up(): void
   {
      Schema::create('shops', function (Blueprint $table) {
         $table->id();
         $table->string('name', 300);
         $table->string('picture')->nullable();
         $table->text('description')->nullable();
         $table->foreignIdFor(Seller::class)
            ->constrained()
            ->cascadeOnDelete();
         $table->timestamps();
      });
   }

   public function down(): void
   {
      Schema::dropIfExists('shops');
   }
};
