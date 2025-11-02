<?php

use App\Models\Category;
use App\Models\Product;
use App\Models\Shop;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->decimal('discount', 5, 2);
            $table->unsignedInteger('quantity');
            $table->decimal('base_price', 12, 2);
            $table->string('photo');
            $table->foreignIdFor(Category::class);
            $table->text('tags')->default('[]');
            $table->enum('status', ['on', 'off', 'draft']);
            $table->foreignIdFor(Shop::class);
            $table->timestamp('created_at');
        });

        Schema::create('product_attributes', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Product::class);
            $table->string('attr_key', 100);
            $table->string('attr_value', 100);
            $table->decimal('price', 12, 2);
            $table->boolean('is_variant')->default(0);
            $table->boolean('is_default')->default(0);
        });

        Schema::create('product_details', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Product::class);
            $table->text('album')->default('[]');
            $table->text('description');
            $table->text('application')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
        Schema::dropIfExists('product_attributes');
        Schema::dropIfExists('product_details');
    }
};
