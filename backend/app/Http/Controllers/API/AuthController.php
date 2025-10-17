<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginUserRequest;
use App\Http\Requests\StoredUserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function register(StoredUserRequest $request)
    {
        return User::create($request->all());
    }

    public function login(LoginUserRequest $request) {
        if (!Auth::attempt($request->only(['email', 'password']))) {
            return response()->json([
                'error' => 'Wrong email or password'
            ], 401);
        }

        $user = Auth::user();

        return (new UserResource($user))->additional([
            'token' => $user->createToken("{$user->name}'s token")->plainTextToken,
        ]);
    }

    public function logout() {
        Auth::user()->currentAccessToken()->delete();
        return response()->json([], 204);
    }
}
