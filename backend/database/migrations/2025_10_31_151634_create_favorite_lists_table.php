<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('favorite_lists', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignIdFor(\App\Models\User::class);
            $table->timestamp('created_at');
        });

        DB::unprepared("
        CREATE TRIGGER `createFavList` AFTER INSERT ON `users` FOR EACH ROW BEGIN
            INSERT INTO favorite_lists (name, user_id, created_at) 
            VALUES ('%favorite%', NEW.id, NOW());
        END
        ");
    }

    public function down(): void
    {
        DB::unprepared("DROP TRIGGER IF EXISTS `createFavList`");
        Schema::dropIfExists('favorite_lists');
    }
};
