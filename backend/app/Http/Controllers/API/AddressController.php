<?php

namespace App\Http\Controllers\API;

use App\Models\Address;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\AddressRequest;
use App\Http\Resources\AddressResource;
use Exception;

class AddressController extends Controller
{
    public function index()
    {
        $userId = Auth::id();
        $addresses = Address::where('user_id', $userId)
            ->with('pickup')
            ->get();
        
        return AddressResource::collection($addresses);
    }

    public function store(AddressRequest $request)
    {
        $data = $request->validated();
        $data['user_id'] = Auth::id();

        $address = Address::create($data);

        return response()->json([
            'id' => $address->id,
            'message' => 'Address created'
        ], 201);
    }

    public function update(AddressRequest $request, Address $address)
    {
        try {
            $userId = Auth::id();
            
            if ($address->user_id !== $userId) {
                return response()->json([
                    'message' => 'Is not your address',
                ], 403);
            }
            
            $data = $request->validated();
            
            return DB::transaction(function () use ($data, $address, $userId) {
                $newDefaultId = null;
                if (!empty($data['is_default']) && !$address->is_default) {
                    $oldDefault = Address::where('user_id', $userId)
                        ->where('is_default', true)
                        ->where('id', '!=', $address->id)
                        ->first();
                    if ($oldDefault) {
                        $oldDefault->update(['is_default' => false]);
                    }
                    $newDefaultId = $address->id;
                }

                $address->update($data);
                return response()->json([
                    'message' => 'Updated',
                    'new_default' => $newDefaultId
                ]);
            });
        } catch (Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy(Address $address)
    {
        if ($address->user_id !== Auth::id()) {
            return response()->json([
                'message' => 'Is not your address',
            ], 403);
        }

        return response()->json([
            'deleted' => $address->delete()
        ]);
    }
}
