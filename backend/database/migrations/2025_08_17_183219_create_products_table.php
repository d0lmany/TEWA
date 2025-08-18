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
        Schema::create('products', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->decimal('discount', 5)->default(0);
            $table->unsignedInteger('quantity');
            $table->decimal('base_price', 12)->index('base_price');
            $table->string('photo');
            $table->unsignedBigInteger('category_id')->index('category_fk');
            $table->string('tags', 500)->default('[]');
            $table->dateTime('created_at')->index('create_date');
            $table->unsignedInteger('feedback_count')->default(0)->index('feedback_count');
            $table->decimal('rating', 3)->nullable()->index('rating');
            $table->enum('status', ['on', 'off', 'draft'])->default('draft');
            $table->unsignedBigInteger('shop_id')->index('shop_id_fk');

            $table->index(['status', 'quantity'], 'idx_status_quantity');
            $table->fullText(['name', 'tags'], 'search_ft');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
