<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\NotificationResource;
use App\Models\Notification;
use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;

class NotificationController extends Controller
{
    /**
     * Return latest 5 notifications for the topbar dropdown.
     */
    public function index()
    {
        return NotificationResource::collection(
            auth()->user()
                ->notifications()
                ->latest()
                ->limit(5)
                ->get()
        );
    }

    /**
     * Return all notifications with pagination (15 per page) for the full page.
     */
    public function indexAll()
    {
        $paginated = auth()->user()
            ->notifications()
            ->latest()
            ->paginate(15);

        return NotificationResource::collection($paginated);
    }

    public function markAsRead(Notification $notification): JsonResponse
    {
        abort_if($notification->user_id !== auth()->id(), 403);

        NotificationService::markAsRead($notification);

        return response()->json([
            'message' => 'Notification marked as read',
            'data' => new NotificationResource($notification->fresh()),
        ]);
    }

    public function markAllAsRead(): JsonResponse
    {
        NotificationService::markAllAsRead(auth()->user());

        return response()->json([
            'message' => 'All notifications marked as read',
        ]);
    }
}
