<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreClaimRequest;
use App\Models\Claim;
use Illuminate\Support\Facades\Auth;

class ClaimController extends Controller
{
    public function store(StoreClaimRequest $request) {
        $claimData = $request->validated();
        
        $claim = Claim::create([
            'user_id' => Auth::id(),
            'entity' => $claimData['entity'],
            'entity_id' => $claimData['entity_id'],
            'topic' => $claimData['topic'],
            'text' => $claimData['text'],
        ]);

        return response()->json(['id' => $claim->id], 201);
    }
}
