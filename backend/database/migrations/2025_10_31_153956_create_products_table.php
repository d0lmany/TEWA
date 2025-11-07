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
            $table->string('name', 300);
            $table->unsignedInteger('quantity');
            $table->decimal('base_price', 12, 2);
            $table->string('photo');
            $table->foreignIdFor(Category::class)
                ->nullable()
                ->constrained()
                ->nullOnDelete();
            $table->json('tags')->default('[]');
            $table->decimal('discount', 5, 2)->default(0.0);
            $table->enum('status', ['on', 'off', 'draft'])->default('draft');
            $table->foreignIdFor(Shop::class)
                ->constrained()
                ->cascadeOnDelete();
            $table->timestamps();
        });

        DB::statement("
            ALTER TABLE products
            ADD COLUMN final_price DECIMAL(12,2)
            AS (base_price * (100 - discount) / 100)
            STORED
        ");

        Schema::table('products', function (Blueprint $table) {
            $table->index(['status', 'quantity'], 'idx_main_filter');

            $table->index(['status', 'category_id', 'quantity'], 'idx_category_base');
            $table->index(['status', 'category_id', 'quantity', 'created_at'], 'idx_category_created');
            $table->index(['status', 'category_id', 'quantity', 'final_price'], 'idx_category_price');

            $table->index(['status', 'quantity', 'created_at'], 'idx_global_created');
            $table->index(['status', 'quantity', 'final_price'], 'idx_global_price');
        });

        Schema::create('product_attributes', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Product::class)
                ->constrained()
                ->cascadeOnDelete();
            $table->string('attr_key', 100);
            $table->string('attr_value', 100);
            $table->decimal('price', 12, 2);
            $table->boolean('is_variant')->default(0);
            $table->boolean('is_default')->default(0);
        });

        Schema::create('product_details', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Product::class)
                ->constrained()
                ->cascadeOnDelete();
            $table->text('album')->default('[]');
            $table->text('description');
            $table->text('application')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_attributes');
        Schema::dropIfExists('product_details');
        Schema::dropIfExists('products');
    }
};
