<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sellers', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('full_name', 300);
            $table->unsignedBigInteger('user_id')->index('user_id_fk');
            $table->string('code', 50)->unique('code_u');
            $table->enum('type', ['individual', 'sp', 'legal']);
            $table->enum('status', ['wait', 'confirm', 'reject'])->default('wait')->index('status_i');
            $table->string('payment_account', 300);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sellers');
    }
};
