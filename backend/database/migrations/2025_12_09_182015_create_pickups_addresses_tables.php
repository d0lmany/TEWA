<?php

use App\Models\Pickup;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pickups', function (Blueprint $table) {
            $table->id();
            $table->string('name', 300);
            $table->string('country', 100);
            $table->string('city', 100);
            $table->text('address');

            $table->unique(['country', 'city', 'address']);

            $table->index('country');
            $table->index('city');
            $table->index(['country', 'city']);
        });

        Schema::create('addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(User::class)
                ->constrained()
                ->cascadeOnDelete();
            $table->foreignIdFor(Pickup::class)
                ->nullable()
                ->constrained()
                ->nullOnDelete();
            $table->text('address')
                ->nullable();
            $table->boolean('is_default')
                ->default(false);

            $table->index(['user_id', 'is_default']);
            $table->index('pickup_id');
            
            $table->unique(['user_id', 'pickup_id']);
            $table->unique(['user_id', 'address'])
                ->whereNotNull('address');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pickups');
        Schema::dropIfExists('addresses');
    }
};
