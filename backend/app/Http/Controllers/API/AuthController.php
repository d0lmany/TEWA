<?php

namespace App\Http\Controllers\API;

use \Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Requests\LoginUserRequest;
use App\Http\Requests\StoreUserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
   public function register(StoreUserRequest $request): JsonResponse
   {
      try {
         return DB::transaction(function () use ($request) {
            $data = $request->validated();
            
            $user = User::create($data);
            $token = $this->createAuthToken($user);

            return response()->json([
               'token' => $token,
            ], 201);
         });
      } catch (\Exception $e) {
         return response()->json([
            'message' => $e->getMessage(),
         ], 500);
      }
   }

   public function login(LoginUserRequest $request): JsonResponse 
   {
      $user = User::where('email', $request->email)->first();

      if (!$user || !Hash::check($request->password, $user->password)) {
         
         return response()->json([
            'message' => 'Wrong email or password'
         ], 401);
      }

      $token = $this->createAuthToken($user);
      
      return response()->json([
         'token' => $token,
      ]);
   }

   public function show() {
      try {
         $user = Auth::user();

         return new UserResource($user);
      } catch (Exception $e) {
         return response()->json([
            'message' => 'Couldn\'t get the user',
            'data' => $e->getMessage(),
         ], 500);
      }
   }

   public function logout(): JsonResponse 
   {
      try {
         $token = Auth::user()?->currentAccessToken();
         
         if ($token) {
            $token->delete();
         }

         return response()->json([], 204);
      } catch (\Exception $e) {
         return response()->json([
            'message' => $e->getMessage()
         ], 500);
      }
   }

   private function createAuthToken(User $user): string
   {
      return $user->createToken("{$user->name}'s device")->plainTextToken;
   }
}
