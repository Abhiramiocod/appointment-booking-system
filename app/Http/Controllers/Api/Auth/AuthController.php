<?php

namespace App\Http\Controllers\Api\Auth;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    public function register(RegisterRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $validated['role'] = UserRole::CUSTOMER;

        $user = User::create($validated);

        // Send email verification notification
        $user->sendEmailVerificationNotification();

        $token = $user->createToken('api_token')->plainTextToken;

        return response()->json([
            'user' => new UserResource($user),
            'token' => $token,
            'message' => 'Registration successful. Please check your email to verify your account.',
        ]);
    }

    public function login(LoginRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();

            if (! Auth::attempt($validated)) {
                return response()->json([
                    'message' => 'Invalid credentials',
                ], 401);
            }

            $user = $request->user();

            // Local users check for email verification
            if (! $user->hasVerifiedEmail()) {
                // Issue a token so they can authenticate to resend verification email or access unverified routes
                $token = $user->createToken('unverified_api_token')->plainTextToken;

                // Send email verification notification when trying to login
                $user->sendEmailVerificationNotification();

                return response()->json([
                    'message' => 'Please verify your email address. A verification link has been sent to your email.',
                    'token' => $token,
                    'user' => new UserResource($user),
                ], 403);
            }


            $token = $user->createToken('api_token')->plainTextToken;

            return response()->json([
                'user' => new UserResource($user),
                'token' => $token,
            ]);
        } catch (\Exception $e) {
            Log::error('Login failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'message' => 'An error occurred while logging in.',
            ], 500);
        }
    }

    public function user(Request $request): JsonResponse
    {
        return response()->json([
            'user' => new UserResource($request->user()),
        ]);
    }

    public function updateProfile(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', 'alpha_dash', 'unique:users,username,'.$user->id],
        ]);

        $user->update($validated);

        return response()->json([
            'user' => new UserResource($user),
            'message' => 'Profile updated successfully',
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->tokens()->delete();

        return response()->json([
            'message' => 'Logged out successfully',
        ]);
    }
}
