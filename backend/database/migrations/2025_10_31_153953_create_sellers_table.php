<?php

use App\Models\Seller;
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
            $table->foreignIdFor(User::class);
            $table->string('code', 50);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sellers');
    }
};
