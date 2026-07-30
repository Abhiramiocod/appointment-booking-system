<?php

namespace App\Http\Controllers\Api\Auth;

use App\actions\Auth\ChangePasswordAction;
use App\actions\Auth\Login\LoginUserAction;
use App\actions\Auth\Register\RegisterUserAction;
use App\actions\Auth\UpdateProfileAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ChangePasswordRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\UpdateProfileRequest;
use App\Http\Resources\UserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

class AuthController extends Controller
{
    public function register(RegisterUserAction $action, RegisterRequest $request): JsonResponse
    {
        $result = $action->execute($request->validated());

        return response()->json([
            'user' => new UserResource($result['user']),
            'token' => $result['token'],
            'message' => $result['message'],
        ]);
    }

    public function login(LoginUserAction $loginUserAction, LoginRequest $request): JsonResponse
    {
        try {
            $result = $loginUserAction->execute($request->validated());

            return response()->json([
                'message' => $result['message'] ?? null,
                'user' => $result['user'] ?? null,
                'token' => $result['token'] ?? null,
            ], $result['status']);
        } catch (Throwable $e) {
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

    public function updateProfile(UpdateProfileRequest $request, UpdateProfileAction $updateProfileAction): JsonResponse
    {
        $user = $updateProfileAction->execute($request->user(), $request->validated());

        return response()->json([
            'user' => new UserResource($user),
            'message' => 'Profile updated successfully!',
        ]);
    }

    public function changePassword(ChangePasswordRequest $request, ChangePasswordAction $changePasswordAction): JsonResponse
    {

        $result = $changePasswordAction->execute($request->user(), $request->validated());

        return response()->json([
            'user' => new UserResource($result['user']),
            'message' => $result['message'],
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
