<?php

use App\Models\User;
use App\Models\Order;
use App\Models\Pickup;
use App\Models\Address;
use App\Models\Product;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(User::class)
                ->nullable()
                ->constrained()
                ->nullOnDelete();
            // pending, paid, processing, shipped, delivered, cancelled, completed
            $table->string('status', 30)
                ->default('pending');
            $table->decimal('total', 12, 2);
            $table->foreignIdFor(Pickup::class, 'destination_pickup_id')
                ->nullable()
                ->constrained();
            $table->foreignIdFor(Address::class, 'destination_address_id')
                ->nullable()
                ->constrained();
            $table->boolean('is_hidden')
                ->default(false);
            $table->timestamps();

            $table->index('status');
            $table->index('user_id');
            $table->index('created_at');
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Order::class)
                ->constrained()
                ->cascadeOnDelete();
            $table->foreignIdFor(Product::class)
                ->constrained()
                ->restrictOnDelete();
            $table->unsignedInteger('quantity')
                ->default(1);
            $table->text('product_attributes')
                ->nullable();
            $table->decimal('unit_price', 12, 2);
            $table->decimal('total', 12, 2); // unit_price*quantity

            $table->index('order_id');
            $table->index('product_id');
        });

        Schema::create('order_locations', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Order::class)
                ->constrained()
                ->cascadeOnDelete();
            // pickup, address
            $table->string('location_type', 20); 
            $table->string('location_id');
            $table->text('notes')
                ->nullable();
            $table->timestamp('arrived_at');
            $table->timestamp('left_at')
                ->nullable();
            $table->timestamps();

            $table->index(['order_id', 'arrived_at']);
            $table->index(['location_type', 'location_id']);
        });

        Schema::create('order_status_history', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Order::class)
                ->constrained()
                ->cascadeOnDelete();
            $table->string('old_status', 30)
                ->nullable();
            $table->string('new_status', 30);
            $table->foreignIdFor(User::class, 'changed_by_id')
                ->nullable()
                ->constrained();
            $table->text('notes')
                ->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('order_locations');
        Schema::dropIfExists('order_status_history');
    }
};
