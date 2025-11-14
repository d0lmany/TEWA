<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class ShopOwner
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();
        
        if (!$user || !$user->seller) {
            return response()->json([
                'message' => 'Forbidden'
            ], 403);
        }

        $shopId = $this->getShopIdFromRequest($request);
        
        if (!$shopId) {
            return response()->json([
                'message' => 'ID is not enter'
            ], 422);
        }

        $isOwner = $user->seller->shops()
            ->where('id', $shopId)
            ->exists();

        if (!$isOwner) {
            return response()->json([
                'message' => 'Forbidden'
            ], 403);
        }

        return $next($request);
    }

    private function getShopIdFromRequest(Request $request): ?int
    {
        return $request->input('shop_id') 
            ?? $request->route('shop') 
            ?? $request->input('shop.id')
            ?? ($request->route('product') ? $request->route('product')->shop_id : null);
    }
}
