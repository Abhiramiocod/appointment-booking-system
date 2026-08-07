<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Actions\GoogleCalendar\ConnectGoogleCalendarAction;
use App\Actions\GoogleCalendar\DisconnectGoogleCalendarAction;
use App\Actions\GoogleCalendar\HandleGoogleCalendarCallbackAction;
use App\Http\Controllers\Controller;
use App\Models\User;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class GoogleCalendarController extends Controller
{
    /**
     * Redirect user to Google OAuth for Calendar permission.
     */
    public function connect(Request $request, ConnectGoogleCalendarAction $action): RedirectResponse
    {
        return $action->execute($request);
    }

    /**
     * Handle Google OAuth Callback for Calendar.
     */
    public function callback(Request $request, HandleGoogleCalendarCallbackAction $action)
    {
        $frontendUrl = env('FRONTEND_URL');

        try {
            /** @var User $user */
            $user = $request->user();

            if (! $user && $request->has('state')) {
                try {
                    $userId = decrypt($request->input('state'));
                    $user = User::find($userId);
                } catch (Exception $e) {
                    Log::warning("Failed to decrypt state parameter in Google Calendar callback: {$e->getMessage()}");
                }
            }

            if (! $user) {
                return redirect()->to("{$frontendUrl}/customer/connections?calendar_status=unauthorized");
            }

            $action->execute($user);

            $path = $user->isCustomer() ? '/customer/connections' : ($user->isStaff() ? '/staff/connections' : '/admin/connections');

            return redirect()->to("{$frontendUrl}{$path}?calendar_status=connected");
        } catch (Exception $e) {
            Log::error("Google Calendar OAuth callback error: {$e->getMessage()}", [
                'exception' => $e,
            ]);

            return redirect()->to("{$frontendUrl}/customer/connections?calendar_status=error");
        }
    }

    /**
     * Get user's Google Calendar connection status.
     */
    public function status(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $account = $user->googleCalendarAccount;

        if (! $account) {
            return response()->json([
                'connected' => false,
                'google_email' => null,
            ]);
        }

        return response()->json([
            'connected' => true,
            'google_email' => $account->google_email,
            'expires_at' => $account->expires_at?->toIso8601String(),
        ]);
    }

    /**
     * Disconnect Google Calendar account.
     */
    public function disconnect(Request $request, DisconnectGoogleCalendarAction $action): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $success = $action->execute($user);

        return response()->json([
            'message' => $success ? 'Google Calendar disconnected successfully.' : 'No Google Calendar account connected.',
        ]);
    }
}
