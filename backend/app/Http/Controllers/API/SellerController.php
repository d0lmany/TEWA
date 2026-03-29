<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSeller;
use App\Models\Seller;
use App\Models\SellerVerification;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class SellerController extends Controller
{
    public function store(StoreSeller $request): JsonResponse {
        $data = $request->validated();

        if ($request->hasFile('passport_scan')) {
            $data['passport_scan'] = $request->file('passport_scan')
                ->store('scans', 'public');
        } else {
            return response()->json([
                'message' => 'Unprocessable entity'
            ], 422);
        }

        $userId = Auth::id();
        $seller = [
            'full_name' => $data['full_name'],
            'user_id' => $userId,
            'code' => $data['code'],
            'type' => $data['type'],
            'payment_account' => $data['payment_account']
        ];
        $sellerVerify = [
            'user_id' => $userId,
            'passport_numbers' => $data['passport_numbers'],
            'passport_scan' => $data['passport_scan'],
            'created_at' => now()
        ];

        try {
            Seller::create($seller);
            SellerVerification::create($sellerVerify);

            return response()->json([
                'message' => 'Seller created. Wait for verify'
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
