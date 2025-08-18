<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\CartItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CartItemController extends Controller
{
    public function add(Request $request) {
        $userId = Auth::id();
        $productId = $request->product_id;

        \DB::table('cart_items')
            ->updateOrInsert(
                ['user_id' => $userId, 'product_id' => $productId],
                ['quantity' => \DB::raw('COALESCE(quantity, 0) + 1'),]
            );

        $cartItem = CartItem::where(
            ['user_id' => $userId, 'product_id' => $productId]
        )->first();

        return response()->json([
            'message' => 'added',
            'data' => $cartItem
        ], 201);
    }

    public function decrement(Request $request) {
        $userId = Auth::id();
        $productId = $request->product_id;

        $cartItem = CartItem::where([
            'user_id' => $userId,
            'product_id' => $productId
        ])->first();

        if (!$cartItem) {
            return response()->json([
                'message' => 'not found'
            ], 404);
        }

        if ($cartItem->quantity > 1) {
            $cartItem->decrement('quantity');
            $cartItem->quantity--;
            return response()->json([
                'message' => 'reduced',
                'data' => $cartItem
            ]);
        } else {
            $cartItem->delete();
            return response()->json([], 204);
        }
    }

    public function remove(Request $request) {
        $userId = Auth::id();
        $productId = $request->product_id;

        $deleted = CartItem::where([
            'user_id' => $userId,
            'product_id' => $productId
        ])->delete();

        if ($deleted) {
            return response()->json([], 204);
        } else {
            return response()->json(['error' => 'not found'], 404);
        }
    }

    public function index() {
        $userId = Auth::id();
        $cart = CartItem::where('user_id', $userId)->with('product')->get();

        return response()->json(['data' => $cart]);
    }
}
