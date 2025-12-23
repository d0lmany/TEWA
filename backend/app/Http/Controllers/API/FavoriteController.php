<?php

namespace App\Http\Controllers\API;

use App\Models\FavoriteList;
use App\Models\FavoriteListItem;
use App\Http\Controllers\Controller;
use App\Http\Requests\ChangeListFavoriteItemRequest;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\FavoriteListResource;
use App\Http\Resources\FavoriteListItemResource;
use App\Http\Requests\StoreFavoriteRequest;
use App\Http\Requests\UpdateFavoriteListRequest;
use Exception;

class FavoriteController extends Controller
{
    public function index() {
        $userId = Auth::id();
        $lists = FavoriteList::with('favoriteListItems.product.category')
            ->where('user_id', $userId)
            ->get();

        return FavoriteListResource::collection($lists);
    }
    
    public function indexBg() {
        $userId = Auth::id();
        $lists = FavoriteList::with('favoriteListItems')
            ->where('user_id', $userId)
            ->get();

        return FavoriteListResource::collection($lists);
    }

    public function store(UpdateFavoriteListRequest $request) {
        $data = $request->validated();
        $data['user_id'] = Auth::id();

        try {
            $list = FavoriteList::create($data);

            return response()->json([
                'data' => $list,
                'message' => 'List created'
            ], 201);
        } catch (\Illuminate\Database\QueryException $e) {
            return response()->json([
                'message' => 'Duplicate entry'
            ], 422);
        }
    }

    public function toggle(StoreFavoriteRequest $request)
    {
        $fiData = $request->validated();

        try {
            $userId = Auth::id();
            $productId = $fiData['product_id'];
            $listId = $fiData['list_id'] ?? null;

            if ($listId) {
                $favoriteList = FavoriteList::where('user_id', $userId)
                    ->where('id', $listId)
                    ->firstOrFail();
            } else {
                $favoriteList = FavoriteList::where('user_id', $userId)
                    ->where('name', '__favorite__')
                    ->first();

                if (!$favoriteList) {
                    $favoriteList = FavoriteList::create([
                        'user_id' => $userId,
                        'name' => '__favorite__',
                    ]);
                }
            }

            $existingItem = FavoriteListItem::where('product_id', $productId)
                ->where('list_id', $favoriteList->id)
                ->first();

            if ($existingItem) {
                $existingItem->delete();
                return response()->json([
                    'message' => 'Product removed from favorites',
                    'action' => 'removed'
                ], 200);
            }

            $favoriteItem = $favoriteList->favoriteListItems()->create([
                'product_id' => $productId,
                'list_id' => $favoriteList->id,
            ]);

            return response()->json([
                'message' => 'Product added to favorites',
                'action' => 'added',
                'data' => new FavoriteListItemResource($favoriteItem)
            ], 201);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Favorite list not found or access denied'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Unable to process favorite action'
            ], 500);
        }
    }

    public function changeList(ChangeListFavoriteItemRequest $request, FavoriteListItem $item) {
        $data = $request->validated();

        try {
            $list = FavoriteList::where('id', $item->list_id)
                ->firstOrFail();

            if ($list->user_id !== Auth::id()) {
                return response()->json([
                    'message' => 'Is not your item'
                ], 403);
            }

            $item->update($data);
            return response()->json([
                'message' => 'Updated'
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function update(UpdateFavoriteListRequest $request, FavoriteList $favorite) {
        $data = $request->validated();

        if ($favorite->user_id !== Auth::id()) {
            return response()->json([
                'message' => 'Is not your list'
            ], 403);
        }

        try {
            $favorite->update($data);
            return response()->json([
                'message' => 'Updated',
            ]);
        } catch (\Illuminate\Database\QueryException $e) {
            return response()->json([
                'message' => 'Duplicate entry'
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function clear(FavoriteList $favorite) {
        if ($favorite->user_id !== Auth::id()) {
            return response()->json([
                'message' => 'Is not your list',
            ], 403);
        }

        try {
            FavoriteListItem::where('list_id', $favorite->id)
                ->delete();
            
            return response()->json([], 204);
        } catch (\Exception $error) {
            return response()->json([
                'message' => $error->getMessage()
            ], 500);
        }
    }

    public function destroy(FavoriteList $favorite) {
        if ($favorite->user_id !== Auth::id()) {
            return response()->json([
                'message' => 'Is not your list',
            ], 403);
        }

        try {
            $favorite->delete();
            
            return response()->json([], 204);
        } catch (\Exception $error) {
            return response()->json([
                'message' => $error->getMessage()
            ], 500);
        }
    }
}
