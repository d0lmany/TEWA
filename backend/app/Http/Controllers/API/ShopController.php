<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\ShopResource;
use App\Models\Shop;

class ShopController extends Controller
{
    public function show(Shop $shop)
    {
        return new ShopResource($shop);
    }
}
