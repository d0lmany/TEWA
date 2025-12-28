<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Order;
use App\Models\Address;
use App\Models\CartItem;
use App\Models\OrderItem;
use App\Models\OrderLocation;
use App\Models\ProductAttribute;
use App\Models\OrderStatusHistory;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    public function run(): void
    {
        $createdCount = 0;
        $attempts = 0;
        $maxAttempts = 200;

        while ($createdCount < 70 && $attempts < $maxAttempts) {
            $attempts++;
            
            $user = User::whereHas('cart')
                ->inRandomOrder()
                ->first();

            if (!$user) {
                continue;
            }

            $userId = $user->id;
            
            $cart = CartItem::where('user_id', $userId)
                ->with('product')
                ->get();

            if ($cart->isEmpty()) {
                continue;
            }

            $destination = $this->determineDestination(['user_id' => $userId], $userId);
            if (!$destination) {
                continue;
            }

            $orderData = $this->processCartItems($cart->pluck('id')->toArray(), $userId);
            
            if (isset($orderData['error'])) {
                $this->command->info("Ошибка при обработке заказа: " . $orderData['error']['message']);
                continue;
            }

            if (empty($orderData['items'])) {
                continue;
            }

            try {
                DB::transaction(function () use ($userId, $destination, $orderData, $cart) {
                    $order = $this->createOrder([
                        'user_id' => $userId,
                        'total' => $orderData['total'],
                        'destination_pickup_id' => $destination['pickup_id'] ?? null,
                        'destination_address_id' => $destination['address_id'] ?? null,
                        'is_hidden' => fake()->boolean(),
                    ]);

                    $this->createOrderItems($order, $orderData['items']);
                    $this->createInitialLocation($order);
                    $this->createStatusHistory($order, $userId);
                    $this->updateProductStock($orderData['items']);
                    $this->clearCartItems($cart->pluck('id')->toArray(), $userId);
                });

                $createdCount++;
                $this->command->info("Создан заказ #{$createdCount}");
                
            } catch (\Exception $e) {
                $this->command->error("Ошибка при создании заказа: " . $e->getMessage());
                continue;
            }
        }

        if ($attempts >= $maxAttempts) {
            $this->command->warn("Достигнут лимит попыток. Создано {$createdCount} заказов из 70 запланированных.");
        } else {
            $this->command->info("Успешно создано {$createdCount} заказов.");
        }
    }

    private function determineDestination(array $data, int $userId): ?array
    {
        if (isset($data["destination_pickup_id"])) {
            return ["pickup_id" => $data["destination_pickup_id"]];
        }

        if (isset($data["destination_address_id"])) {
            return ["address_id" => $data["destination_address_id"]];
        }

        $defaultAddress = Address::where("user_id", $userId)
            ->where("is_default", true)
            ->first();

        if (!$defaultAddress) {
            $anyAddress = Address::where("user_id", $userId)->first();
            if (!$anyAddress) {
                return null;
            }
            
            return $anyAddress->pickup_id
                ? ["pickup_id" => $anyAddress->pickup_id]
                : ["address_id" => $anyAddress->id];
        }

        return $defaultAddress->pickup_id
            ? ["pickup_id" => $defaultAddress->pickup_id]
            : ["address_id" => $defaultAddress->id];
    }

    private function processCartItems(array $cartItemIds, int $userId): array
    {
        $items = [];
        $total = 0;

        $cartItems = CartItem::with(["product"])
            ->whereIn("id", $cartItemIds)
            ->where("user_id", $userId)
            ->get();

        if ($cartItems->count() !== count($cartItemIds)) {
            return ["error" => ["message" => "Некоторые товары не найдены в корзине"]];
        }

        foreach ($cartItems as $cartItem) {
            $product = $cartItem->product;

            if (!$product) {
                return [
                    "error" => [
                        "message" => "Продукт не найден для товара в корзине",
                        "cart_item_id" => $cartItem->id,
                    ],
                ];
            }

            if ($product->status !== "on") {
                return [
                    "error" => [
                        "message" => "Продукт '{$product->name}' недоступен для заказа",
                        "cart_item_id" => $cartItem->id,
                        "product_id" => $product->id,
                    ],
                ];
            }

            if ($product->quantity < $cartItem->quantity) {
                return [
                    "error" => [
                        "message" => "Недостаточно товара '{$product->name}' на складе. Доступно: {$product->quantity}, требуется: {$cartItem->quantity}",
                        "cart_item_id" => $cartItem->id,
                        "product_id" => $product->id,
                    ],
                ];
            }

            $attributes = $cartItem->product_attributes ?? [];
            $attributesPrice = 0;

            if (!empty($attributes)) {
                $validAttributes = ProductAttribute::where(
                    "product_id",
                    $product->id,
                )
                    ->whereIn("id", $attributes)
                    ->pluck("id")
                    ->toArray();

                $attributesPrice = ProductAttribute::whereIn(
                    "id",
                    $validAttributes,
                )->sum("price");
            }

            $unitPrice = $product->base_price;
            $itemTotal = ($unitPrice + $attributesPrice) * $cartItem->quantity;

            $items[] = [
                "cart_item_id" => $cartItem->id,
                "product_id" => $product->id,
                "quantity" => $cartItem->quantity,
                "product_attributes" => !empty($attributes)
                    ? $attributes
                    : null,
                "unit_price" => $unitPrice,
                "total" => $itemTotal,
                "product_data" => $product,
                "attributes_price" => $attributesPrice,
            ];

            $total += $itemTotal;
        }

        return [
            "items" => $items,
            "total" => $total,
            "cart_items" => $cartItems,
        ];
    }

    private function createOrder(array $data): Order
    {
        return Order::create([
            "user_id" => $data["user_id"],
            "status" => "pending",
            "total" => $data["total"],
            "destination_pickup_id" => $data["destination_pickup_id"] ?? null,
            "destination_address_id" => $data["destination_address_id"] ?? null,
            "is_hidden" => $data["is_hidden"] ?? false,
        ]);
    }

    private function createOrderItems(Order $order, array $items): void
    {
        foreach ($items as $item) {
            OrderItem::create([
                "order_id" => $order->id,
                "product_id" => $item["product_id"],
                "quantity" => $item["quantity"],
                "product_attributes" => $item["product_attributes"]
                    ? json_encode($item["product_attributes"])
                    : null,
                "unit_price" => $item["unit_price"],
                "total" => $item["total"],
            ]);
        }
    }

    private function createInitialLocation(Order $order): void
    {
        OrderLocation::create([
            "order_id" => $order->id,
            "location_type" => "warehouse",
            "location_id" => "default",
            "notes" => "Заказ создан, ожидает обработки",
            "arrived_at" => now(),
        ]);
    }

    private function createStatusHistory(Order $order, int $userId): void
    {
        OrderStatusHistory::create([
            "order_id" => $order->id,
            "old_status" => null,
            "new_status" => "pending",
            "changed_by_id" => $userId,
            "notes" => "Заказ создан",
        ]);
    }

    private function updateProductStock(array $items): void
    {
        foreach ($items as $item) {
            $product = $item["product_data"];
            $product->decrement("quantity", $item["quantity"]);
        }
    }

    private function clearCartItems(array $cartItemIds, int $userId): void
    {
        CartItem::whereIn("id", $cartItemIds)
            ->where("user_id", $userId)
            ->delete();
    }
}