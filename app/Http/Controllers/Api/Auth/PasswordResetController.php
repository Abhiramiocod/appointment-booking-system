<?php

namespace App\Http\Controllers\Api\Auth;

use App\Actions\Auth\ResetPasswordAction;
use App\Actions\Auth\SendPasswordResetLinkAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Password;

class PasswordResetController extends Controller
{
    /**
     * Send a password reset link to the given user.
     */
    public function sendResetLinkEmail(ForgotPasswordRequest $request, SendPasswordResetLinkAction $action): JsonResponse
    {
        return response()->json($action->execute($request->validated()));
    }

    /**
     * Reset the given user's password.
     */
    public function reset(ResetPasswordRequest $request, ResetPasswordAction $action): JsonResponse
    {
        $result = $action->execute($request->validated());

        return response()->json(
            ['message' => $result['message']],
            $result['status']
        );
    }
}
