<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Product::select([
            'id', 'name', 'discount', 'quantity',
            'photo', 'category_id', 'tags', 'rating',
            'base_price', 'feedback_count'
        ])
        ->where('status', 'on')
        ->where('quantity', '>', 0);

        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->has('min_rating')) {
            $query->where('rating', '>=', $request->min_rating);
        }

        if ($request->has('min_price')) {
            $query->where('base_price', '>=', $request->min_price);
        }
        if ($request->has('max_price')) {
            $query->where('base_price', '<=', $request->max_price);
        }

        if ($request->has('tags')) {
            $tags = is_array($request->tags) ? $request->tags : explode(',', $request->tags);
            $query->where(function($q) use ($tags) {
                foreach ($tags as $tag) {
                    $q->orWhereJsonContains('tags', $tag);
                }
            });
        }

        if ($request->has('q')) {
            $target = $request->q;
            $query->where(function($q) use ($target) {
                $q->where('name', 'LIKE', "%{$target}%")
                ->orWhere('tags', 'LIKE', "%{$target}%");
            });
        }

        $sortField = $request->get('sort', 'feedback_count');
        $sortDirection = $request->get('direction', 'desc');
        
        $allowedSortFields = ['feedback_count', 'rating', 'base_price', 'created_at'];
        if (!in_array($sortField, $allowedSortFields)) {
            $sortField = 'feedback_count';
        }

        if ($sortField === 'base_price') {
            $query->orderByRaw("base_price * (100 - discount) / 100 {$sortDirection}");
        } else {
            $query->orderBy($sortField, $sortDirection);
        }
        
        $query->orderBy($sortField, $sortDirection);

        $products = $query->paginate(20);

        return response()->json($products);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreProductRequest $request)
    {
        $product = Product::create($request->all());
        return response()->json(['id' => $product->id], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product)
    {
        $product
        ->load('category.parent')
        ->load('productDetail')
        ->load('productAttribute')
        ->load('shop.seller')
        ->load('review.user');
        return new ProductResource($product);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateProductRequest $request, Product $product)
    {
        $product->update($request->all());
        return $product;
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        $product->delete();
        return response()->json([], 204);
    }
}
