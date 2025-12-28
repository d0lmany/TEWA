<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Http\Resources\ProductResource;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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

        if ($request->has('tags')) {
            $tags = $this->normalizeTags($request->tags);
            
            if (!empty($tags)) {
                if (config('database.default') === 'pgsql') {
                    $query->whereJsonContainsAll('tags', $tags);
                } else {
                    foreach ($tags as $tag) {
                        $query->whereJsonContains('tags', $tag);
                    }
                }
            }
        }

        if ($request->filled('q') && $request->q !== 'null') {
            $target = $request->q;
            $query->where(function($q) use ($target) {
                $q->where('name', 'LIKE', "%{$target}%")
                ->orWhereJsonContains('tags', $target);
            });
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

        if (isset($productData['tags']) && is_array($productData['tags'])) {
            $productData['tags'] = json_encode($productData['tags']);
        }

        if (!isset($productData['status'])) {
            $productData['status'] = 'draft';
        }

        $product = Product::create($productData);

        return response()->json([
            'id' => $product->id,
            'message' => 'Product created successfully'
        ], 201);
    }

    public function show(Product $product): ProductResource
    {
        $product->loadMissing([
            'category.parent',
            'productDetail', 
            'attributes',
            'shop.seller',
            'reviews.user'
        ]);
        
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

    private function normalizeTags($tagsInput): array
    {
        if (is_array($tagsInput)) {
            return array_filter(array_map('trim', $tagsInput));
        }

        if (is_string($tagsInput)) {
            if ($this->isJson($tagsInput)) {
                $decoded = json_decode($tagsInput, true);
                if (is_array($decoded)) {
                    return array_filter(array_map('trim', $decoded));
                }
            }

            return array_filter(array_map('trim', explode(',', $tagsInput)));
        }

        if (is_numeric($tagsInput)) {
            return [trim((string)$tagsInput)];
        }

        return [];
    }

    private function isJson(string $string): bool
    {
        json_decode($string);
        return json_last_error() === JSON_ERROR_NONE;
    }
}
