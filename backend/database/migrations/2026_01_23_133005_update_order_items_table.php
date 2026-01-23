<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            # waited (for review), cancelled (for part-cancelled), completed (if the review for the item created)
            $table->string('status', 30)
                ->nullable();
        });
    }
};
