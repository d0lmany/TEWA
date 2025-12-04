<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
   public function up(): void
   {
      Schema::create('sellers', function (Blueprint $table) {
         $table->id();
         $table->string('full_name', 300);
         $table->foreignIdFor(User::class)
            ->nullable()
            ->constrained()
            ->nullOnDelete();
         $table->string('code', 50);
         $table->enum('type', ['individual', 'LLC', 'self-employed']);
         $table->text('payment_account')
            ->nullable();
         $table->dateTime('verified_at')
            ->nullable();
         $table->timestamps();
      });

      Schema::create('seller_verifications', function (Blueprint $table) {
         $table->id();
         $table->foreignIdFor(User::class)
            ->nullable()
            ->constrained()
            ->nullOnDelete();
         $table->text('passport_numbers');
         $table->text('passport_scan');
         $table->timestamp('created_at')->useCurrent();
      });
   }

   public function down(): void
   {
      Schema::dropIfExists('sellers');
      Schema::dropIfExists('seller_verifications');
   }
};
