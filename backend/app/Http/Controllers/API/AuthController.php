<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginUserRequest;
use App\Http\Requests\StoreUserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function register(StoreUserRequest $request): UserResource
    {
        $user = User::create($request->validated());
        
        $token = $this->createAuthToken($user);
        return (new UserResource($user))->additional(['token' => $token]);
    }

    public function login(LoginUserRequest $request): JsonResponse|UserResource {
        if (!Auth::attempt($request->only(['email', 'password']))) {
            return response()->json([
                'error' => 'Wrong email or password'
            ], 401);
        }

        $user = Auth::user();

        if ($user->is_banned) {
            Auth::logout();
            return response()->json([
                'message' => 'Account is banned'
            ], 403);
        }

        $token = $this->createAuthToken($user);
        return (new UserResource($user))->additional(['token' => $token]);
    }

    public function logout(): JsonResponse {
        Auth::user()->currentAccessToken()->delete();
        return response()->json([], 204);
    }

    private function createAuthToken(User $user): string
    {
        return $user->createToken("{$user->name}'s device")->plainTextToken;
    }
}
