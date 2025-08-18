<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreFavoriteRequest;
use App\Models\Favorite_list_item;
use App\Models\Favorite_list;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FavoriteListItemController extends Controller
{
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

    public function destroy(Favorite_list_item $fli)
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

            $favoriteList = Favorite_list::where('name', 'Избранное')
                ->where('user_id', $userId)
                ->firstOrFail();

            $favoriteItem = Favorite_list_item::where('list_id', $favoriteList->id)
                ->where('product_id', $productId)
                ->first();

            if ($favoriteItem) {
                $favoriteItem->delete();
                return response()->json([], 204);
            } else {
                $favoriteItem = Favorite_list_item::create([
                    'list_id' => $favoriteList->id,
                    'product_id' => $productId,
                    'added_at' => now()
                ]);
                return response()->json([
                    'message' => 'Added',
                    'data' => $favoriteItem
                ], 201);
            }
        } catch (Exception $e) {
            return response()->json([
                'error' => $e
            ], 500);
        }
    }
}
