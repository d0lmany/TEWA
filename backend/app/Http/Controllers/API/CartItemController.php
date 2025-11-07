<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCartItemRequest;
use App\Http\Resources\CartResource;
use App\Models\CartItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CartItemController extends Controller
{
    public function index() {
        $userId = Auth::id();
        $cart = CartItem::where('user_id', $userId)
            ->with('product')
            ->get();

        return CartResource::collection($cart);
    }

    public function indexBg() {
        $userId = Auth::id();
        $cart = CartItem::where('user_id', $userId)->get();

        return CartResource::collection($cart);
    }

    public function store(StoreCartItemRequest $request): JsonResponse {

        $cartItemData = $request->validated();

        $userId = Auth::id();
        $productId = $cartItemData['product_id'];
        $quantityToAdd = $cartItemData['quantity'] ?? 1;
        $attributes = $cartItemData['product_attributes'] ?? [];
        sort($attributes);

        $cartItems = CartItem::where('user_id', $userId)
            ->where('product_id', $productId)
            ->get();

        $cartItem = $cartItems->first(function ($item) use ($attributes) {
            $itemAttributes = $item->product_attributes ?? [];
            sort($itemAttributes);
            return $itemAttributes == $attributes;
        });

        if ($cartItem) {
            $cartItem->quantity += $quantityToAdd;
            $cartItem->save();
        } else {
            $cartItem = CartItem::create([
                'user_id' => $userId,
                'product_id' => $productId,
                'quantity' => $quantityToAdd,
                'product_attributes' => $attributes
            ]);
        }

        return response()->json([
            'message' => $cartItem->wasRecentlyCreated ? 'created' : 'updated',
            'data' => $cartItem->load('product')
        ], 201);
    }

    public function update(Request $request, $id): JsonResponse {
        $validated = $request->validate([
            'quantity' => 'required|integer|min:1'
        ]);

        $cartItem = CartItem::where([
            'id' => $id,
            'user_id' => Auth::id()
        ])->first();

        if (!$cartItem) {
            return response()->json(['message' => 'Cart item not found'], 404);
        }

        $cartItem->quantity = $validated['quantity'];
        $cartItem->save();

        return response()->json([
            'message' => 'updated',
            'data' => $cartItem->fresh()
        ]);
    }

    public function destroy($id): JsonResponse
    {
        $cartItem = CartItem::where([
            'id' => $id,
            'user_id' => Auth::id()
        ])->first();

        if (!$cartItem) {
            return response()->json([
                'message' => 'Cart item not found'
            ], 404);
        }

        $cartItem->delete();

        return response()->json([], 204);
    }
}