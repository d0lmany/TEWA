<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreFavoriteRequest;
use App\Http\Resources\FavoriteListItemResource;
use App\Models\FavoriteListItem;
use App\Models\FavoriteList;
use Exception;
use Illuminate\Support\Facades\Auth;

class FavoriteListItemController extends Controller
{
    public function index($listId) {
        $items = FavoriteListItem::with('product')
            ->where('list_id', $listId)
            ->get();
        
        return FavoriteListItemResource::collection($items);
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
                'error' => 'Favorite list not found or access denied'
            ], 404);
        } catch (Exception $e) {
            return response()->json([
                'error' => 'Unable to process favorite action'
            ], 500);
        }
    }
}
