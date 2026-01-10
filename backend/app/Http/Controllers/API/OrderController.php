<?php

namespace App\Http\Controllers\API;

use App\Models\Order;
use App\Models\Address;
use App\Models\CartItem;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use App\Models\OrderLocation;
use App\Models\ProductAttribute;
use App\Models\OrderStatusHistory;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\OrderResource;
use App\Http\Requests\StoreOrderRequest;

class OrderController extends Controller
{
    public function index()
    {
        $orders = Order::with([
            'items.product',
            'locations',
            'statusHistory',
        ])
        ->where('user_id', Auth::id())
        ->latest()
        ->get();

        return OrderResource::collection($orders);
    }

    public function store(StoreOrderRequest $request)
    {
        $validated = $request->validated();
        
        return DB::transaction(function () use ($validated) {
            $userId = Auth::id();
            
            $destination = $this->determineDestination($validated, $userId);
            if (!$destination) {
                return response()->json([
                    'message' => 'Need address'
                ], 422);
            }
            
            $orderData = $this->processCartItems($validated['cart_items'], $userId);
            
            if (isset($orderData['error'])) {
                return response()->json([
                    'message' => $orderData['error']['message'],
                    'cart_item_id' => $orderData['error']['cart_item_id'],
                    'product_id' => $orderData['error']['product_id'] ?? null,
                ], 422);
            }
            
            $order = $this->createOrder([
                'user_id' => $userId,
                'total' => $orderData['total'],
                'destination_pickup_id' => $destination['pickup_id'] ?? null,
                'destination_address_id' => $destination['address_id'] ?? null,
                'is_hidden' => $validated['is_hidden'] ?? false,
            ]);
            
            $this->createOrderItems($order, $orderData['items']);
            
            $this->createInitialLocation($order);
            
            $this->createStatusHistory($order, $userId);
            
            $this->updateProductStock($orderData['items']);
            
            $this->clearCartItems($validated['cart_items'], $userId);
            
            $orderWithRelations = $order->fresh(['items.product', 'locations', 'statusHistory']);
                    
            return response()->json([
                'message' => 'Order created',
                'order' => new OrderResource($orderWithRelations),
            ], 201);
        }, 5);
    }

    protected function determineDestination(array $data, int $userId): ?array
    {
        if (isset($data['destination_pickup_id'])) {
            return ['pickup_id' => $data['destination_pickup_id']];
        }
        
        if (isset($data['destination_address_id'])) {
            return ['address_id' => $data['destination_address_id']];
        }
        
        $defaultAddress = Address::where('user_id', $userId)
            ->where('is_default', true)
            ->first();
        
        if (!$defaultAddress) {
            return null;
        }
        
        return $defaultAddress->pickup_id 
            ? ['pickup_id' => $defaultAddress->pickup_id]
            : ['address_id' => $defaultAddress->id];
    }
    
    protected function processCartItems(array $cartItemIds, int $userId): array
    {
        $items = [];
        $total = 0;
        
        $cartItems = CartItem::with(['product'])
            ->whereIn('id', $cartItemIds)
            ->where('user_id', $userId)
            ->get();
        
        foreach ($cartItems as $cartItem) {
            $product = $cartItem->product;
            
            if ($product->status !== 'on') {
                return [
                    'error' => [
                        'message' => "Product '{$product->name}' unavailable for order",
                        'cart_item_id' => $cartItem->id,
                        'product_id' => $product->id,
                    ]
                ];
            }
            
            if ($product->quantity < $cartItem->quantity) {
                return [
                    'error' => [
                        'message' => "Insufficient product '{$product->name}' in stock. Available: {$product->quantity}, requested: {$cartItem->quantity}",
                        'cart_item_id' => $cartItem->id,
                        'product_id' => $product->id,
                    ]
                ];
            }
            
            $attributes = $cartItem->product_attributes ?? [];
            $attributesPrice = 0;
            
            if (!empty($attributes)) {
                $validAttributes = ProductAttribute::where('product_id', $product->id)
                    ->whereIn('id', $attributes)
                    ->pluck('id')
                    ->toArray();
                
                $attributesPrice = ProductAttribute::whereIn('id', $validAttributes)
                    ->sum('price');
            }
            
            $unitPrice = $product->base_price;
            $itemTotal = ($unitPrice + $attributesPrice) * $cartItem->quantity;
            
            $items[] = [
                'cart_item_id' => $cartItem->id,
                'product_id' => $product->id,
                'quantity' => $cartItem->quantity,
                'product_attributes' => !empty($attributes) ? $attributes : null,
                'unit_price' => $unitPrice,
                'total' => $itemTotal,
                'product_data' => $product,
                'attributes_price' => $attributesPrice,
            ];
            
            $total += $itemTotal;
        }
        
        return [
            'items' => $items,
            'total' => $total,
            'cart_items' => $cartItems,
        ];
    }
    
    protected function createOrder(array $data): Order
    {
        return Order::create([
            'user_id' => $data['user_id'],
            'status' => 'pending',
            'total' => $data['total'],
            'destination_pickup_id' => $data['destination_pickup_id'] ?? null,
            'destination_address_id' => $data['destination_address_id'] ?? null,
            'is_hidden' => $data['is_hidden'] ?? false,
        ]);
    }
    
    protected function createOrderItems(Order $order, array $items): void
    {
        foreach ($items as $item) {
            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $item['product_id'],
                'quantity' => $item['quantity'],
                'product_attributes' => $item['product_attributes'] 
                    ? json_encode($item['product_attributes']) 
                    : null,
                'unit_price' => $item['unit_price'],
                'total' => $item['total'],
            ]);
        }
    }
    
    protected function createInitialLocation(Order $order): void
    {
        OrderLocation::create([
            'order_id' => $order->id,
            'location_type' => 'warehouse',
            'location_id' => 'default',
            'notes' => 'Заказ создан, ожидает обработки',
            'arrived_at' => now(),
        ]);
    }
    
    protected function createStatusHistory(Order $order, int $userId): void
    {
        OrderStatusHistory::create([
            'order_id' => $order->id,
            'old_status' => null,
            'new_status' => 'pending',
            'changed_by_id' => $userId,
            'notes' => 'Заказ создан',
        ]);
    }
    
    protected function updateProductStock(array $items): void
    {
        foreach ($items as $item) {
            $product = $item['product_data'];
            $product->decrement('quantity', $item['quantity']);
        }
    }
    
    protected function clearCartItems(array $cartItemIds, int $userId): void
    {
        CartItem::whereIn('id', $cartItemIds)
            ->where('user_id', $userId)
            ->delete();
    }
}