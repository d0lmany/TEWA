<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\FavoriteListResource;
use App\Models\FavoriteList;
use Auth;

class FavoriteListController extends Controller
{
    public function indexBg() {
        $userId = Auth::id();
        $lists = FavoriteList::with('favoriteListItems')
            ->where('user_id', $userId)
            ->get();

        return FavoriteListResource::collection($lists);
    }
}
