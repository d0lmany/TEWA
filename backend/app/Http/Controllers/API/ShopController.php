<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\ShopResource;
use App\Models\Shop;
use Exception;

class ShopController extends Controller
{
    public function show(Shop $shop)
    {
        try {
        $shop->load([
            'seller',
            'products.category',
        ])
        ->loadCount('reviews');

        return new ShopResource($shop);
        } catch (Exception $e) {
            return response()->json([
                'data' => $e->getMessage()
            ], 500);
        }
    }
}
