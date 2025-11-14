<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class ProductOwner
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();
        $product = $request->route('product');
        
        if (!$user || !$user->seller) {
            return response()->json([
                'message' => 'Route os only for sellers'
            ], 403);
        }

        $isOwner = $user->seller->shops()
            ->where('id', $product->shop_id)
            ->exists();

        if (!$isOwner) {
            return response()->json([
                'message' => 'Forbidden'
            ], 403);
        }

        return $next($request);
    }
}
