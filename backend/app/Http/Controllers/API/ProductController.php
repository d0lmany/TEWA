<?php

namespace App\Http\Controllers\API;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::where('status', 'on')
            ->where('quantity', '>', 0)
            ->withCount('reviews')
            ->withAvg('reviews as rating_avg', 'evaluation');

        if ($request->filled('category_id')) {
            $categoryIds = Category::where(function($query) use ($request) {
                    $query->where('id', $request->category_id)
                        ->orWhere('parent_id', $request->category_id)
                        ->orWhereIn('parent_id', function($subQuery) use ($request) {
                            $subQuery->select('id')
                                    ->from('categories')
                                    ->where('parent_id', $request->category_id);
                        });
                })
                ->pluck('id')
                ->toArray();
            
            $query->whereIn('category_id', $categoryIds);
        }

        if ($request->filled('min_rating')) {
            $minRating = (float) $request->min_rating;
            $query->having('rating_avg', '>=', $minRating);
        }

        if ($request->filled('min_price') || $request->filled('max_price')) {
            $query->where(function($q) use ($request) {
                if ($request->filled('min_price')) {
                    $q->where('final_price', '>=', (float) $request->min_price);
                }
                if ($request->filled('max_price')) {
                    $q->where('final_price', '<=', (float) $request->max_price);
                }
            });
        }

        if ($request->has('tags') && is_array($request->tags)) {
            $tagIds = array_filter(array_map('intval', $request->tags));

            if (!empty($tagIds)) {
                $query->whereHas('tags', function ($q) use ($tagIds) {
                    $q->whereIn('tags.id', $tagIds);
                });
            }
        }

        if ($request->filled('q')) {
            $search = trim($request->q);
            if ($search && $search !== 'null' && strlen($search) > 2) {
                $search = str_replace(['%', '_'], ['\%', '\_'], $search);
                $query->where('name', 'LIKE', "%{$search}%");
            }
        }

        if ($request->filled('shop_id') && intval($request->shop_id) !== 0) {
            $shop_id = intval($request->shop_id);
            $query->where('shop_id', $shop_id);
        }

        $sortField = $request->get('sort', 'reviews_count');
        $sortDirection = in_array($request->get('direction'), ['ASC', 'DESC']) 
            ? $request->get('direction') 
            : 'DESC';

        $allowedSortFields = ['reviews_count', 'rating', 'final_price', 'created_at'];
        if (!in_array($sortField, $allowedSortFields)) {
            $sortField = 'reviews_count';
        }

        switch ($sortField) {
            case 'final_price':
                $query->orderBy('final_price', $sortDirection);
                break;
            case 'rating':
                $query->orderBy('rating_avg', $sortDirection);
                break;
            default:
                $query->orderBy($sortField, $sortDirection);
        }

        $products = $query->paginate(20);

        return ProductResource::collection($products);
    }

    public function store(StoreProductRequest $request): JsonResponse
    {
        $productData = $request->validated();

        if ($request->hasFile('photo')) {
            $productData['photo'] = $request->file('photo')
                ->store('products', 'public');
        } else {
            return response()->json([
                'message' => 'Unprocessable Entity'
            ], 422);
        }

        if (!isset($productData['status'])) {
            $productData['status'] = 'draft';
        }

        $product = Product::create($productData);

        if (isset($productData['tags']) && is_array($productData['tags'])) {
            $tagIds = array_filter(
                array_map('intval', $productData['tags']),
                fn($id) => $id > 0
            );

            if (!empty($tagIds)) {
                $product->tags()->attach($tagIds);
            }
        }

        return response()->json([
            'id' => $product->id,
            'message' => 'Product created successfully'
        ], 201);
    }

    public function show(Product $product)
    {
        $conf = ConfigController::getConfig();
        $isMarket = $conf['mode'] === 'marketplace';

        $loads = [
            'category.parent',
            'productDetail', 
            'attributes',
            'reviews.user',
            'tags'
        ];

        if ($isMarket) {
            $loads[] = 'shop.seller';
        }

        $product->loadMissing($loads);
        
        if (!$product->relationLoaded('rating')) {
            $product->loadAvg('reviews as rating', 'evaluation');
        }
        return new ProductResource($product);
    }

    public function update(UpdateProductRequest $request, Product $product): ProductResource
    {
        $product->update($request->validated());
        return new ProductResource($product);
    }

    public function destroy(Product $product)
    {
        $product->delete();
        return response()->json([], 204);
    }
}
