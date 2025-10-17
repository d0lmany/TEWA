<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreClaimRequest;
use App\Models\Claim;
use Illuminate\Support\Facades\Auth;

class ClaimController extends Controller
{
    public function store(StoreClaimRequest $request) {
        $product = Claim::create([
            'user_id' => Auth::id(),
            'entity' => $request->entity,
            'entity_id' => $request->entity_id,
            'topic' => $request->topic,
            'text' => $request->text,
            'created_at' => now()
        ]);
        return response()->json(['id' => $product->id], 201);
    }
}
