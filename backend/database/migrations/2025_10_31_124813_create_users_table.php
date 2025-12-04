<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
   public function up(): void
   {
      Schema::create('users', function (Blueprint $table) {
         $table->id();
         $table->string('name', 255);
         $table->string('email', 255)->unique();
         $table->string('password', 100);
         $table->date('birthday');
         $table->string('picture')->nullable();
         $table->enum('role', ['user', 'admin'])->default('user');
         $table->boolean('is_banned')->default(0);
         $table->timestamp('created_at')->useCurrent();
      });

      Schema::create('personal_access_tokens', function (Blueprint $table) {
         $table->id();
         $table->morphs('tokenable');
         $table->string('name');
         $table->string('token', 64)->unique();
         $table->text('abilities')->nullable();
         $table->timestamp('last_used_at')->nullable();
         $table->timestamp('expires_at')->nullable();
         $table->timestamps();
      });
   }

   public function down(): void
   {
      Schema::dropIfExists('personal_access_tokens');
      Schema::dropIfExists('users');
   }
};
