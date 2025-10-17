<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreFavoriteRequest;
use App\Http\Resources\FavoriteListItemResource;
use App\Models\FavoriteListItem;
use App\Models\FavoriteList;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FavoriteListItemController extends Controller
{
    public function index($listId) {
        $items = FavoriteListItem::with('product')
            ->where('list_id', $listId)
            ->get();
        
        return FavoriteListItemResource::collection($items);
    }

    public function store(StoreFavoriteRequest $request)
    {
        /*
        1. получаем из реквеста айди юзера и айди продукта
        2. обращаемся к спискам избранного: получаем первый, совпавший с name=Избранное, user_id=айди из реквеста - получаем айди списка
        3. вставляем в FavListItems запись с айди списка, продукта, когда вставили
        4. готово! мы в шоколадке
        5. на фронте сохранить айди списка избранного куда нибудь
        */
    }

    public function destroy(FavoriteListItem $fli)
    {
        /*
        1. из запроса узнаём айди товара и айди списка
        2. по ним находим запись product_id=айди товара, list_id=айди спика - удаляем запись
        3. возвращаем ответ что записи не было (404)/ запись удалена (204)
        */
    }

    public function toggle(StoreFavoriteRequest $request)
    {
        try {
            $userId = Auth::id();
            $productId = $request->product_id;

            $existingItem = FavoriteListItem::where('product_id', $productId)
                ->whereHas('favoriteList', fn($query) => $query->where('user_id', $userId))
                ->first();

            if ($existingItem) {
                $existingItem->delete();
                return response()->json(['message' => 'Removed'], 200);
            }

            $favoriteList = FavoriteList::where('user_id', $userId)
                ->where('name', 'Избранное')
                ->firstOrFail();

            $favoriteItem = $favoriteList->favoriteListItems()->create([
                'product_id' => $request->product_id,
                'list_id' => $favoriteList->id,
            ]);

            return response()->json([
                'message' => 'Added',
                'data' => new FavoriteListItemResource($favoriteItem)
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'error' => $e
            ], 500);
        }
    }
}
