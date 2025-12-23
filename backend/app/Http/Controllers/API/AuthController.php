<?php

namespace App\Http\Controllers\API;

use Exception;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use \Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\LoginUserRequest;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdatePasswordUserRequest;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\UpdateUserRequest;

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
        $request = $request->validated();

        $user = User::where('email', $request['email'])->first();

        if (!$user || !Hash::check($request['password'], $user->password)) {
            
            return response()->json([
                'message' => 'Wrong email or password'
            ], 401);
        }

        $token = $this->createAuthToken($user);
        
        return response()->json([
            'token' => $token,
        ]);
    }

    public function show(): UserResource|JsonResponse {
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

    public function update(UpdateUserRequest $request): UserResource 
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $data = $request->validated();
        
        if (isset($validatedData['delete_picture']) && $validatedData['delete_picture']) {
            if ($user->picture) {
                Storage::disk('public')->delete($user->picture);
            }
            $user->picture = null;
            $user->save();
        }
        
        if ($request->hasFile('picture')) {
            if ($user->picture) {
                Storage::disk('public')->delete($user->picture);
            }
            
            $user->picture = $request->file('picture')
                ->store('profiles', 'public');
            $user->save();
        }
        
        $user->update($request->only(['name', 'birthday']));
        
        return new UserResource($user->fresh());
    }

    public function changePassword(UpdatePasswordUserRequest $request): JsonResponse
    {
        $data = $request->validated();
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (!Hash::check($data['old_password'], $user->password)) {
            return response()->json([
                'message' => 'Wrong password'
            ], 422);
        }

        $user->update([
            'password' => Hash::make($data['password']),
        ]);

        $user->currentAccessToken()->delete();

        return response()->json([
            'token' => $this->createAuthToken($user)
        ]);
    }

    public function logout(): JsonResponse 
    {
        try {
            Auth::user()?->currentAccessToken()->delete();
            
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
