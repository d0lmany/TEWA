<?php

use App\Models\Product;
use App\Models\Tag;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tags', function (Blueprint $table) {
            $table->id();
            $table->string('name')
                ->unique();
            $table->text('description');
        });
        Schema::create('product_tag', function (Blueprint $table) {
            $table->foreignIdFor(Product::class)
                ->constrained()
                ->cascadeOnDelete();
            $table->foreignIdFor(Tag::class)
                ->constrained('tags')
                ->cascadeOnDelete();
            $table->primary(['product_id', 'tag_id']);
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('tags');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tags');
        Schema::dropIfExists('product_tag');
        
        Schema::table('products', function (Blueprint $table) {
            $table->text('tags')
                ->nullable();
        });
    }
};
