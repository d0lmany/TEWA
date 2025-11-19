<?php

namespace App\Http\Controllers\API;

use App\Models\FavoriteList;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\FavoriteListResource;

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
