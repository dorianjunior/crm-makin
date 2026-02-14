<?php

namespace App\Http\Controllers\API\Notifications;

use App\Http\Controllers\Controller;
use App\Models\Notification\Notification;
use App\Services\Notifications\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class NotificationController extends Controller
{
    protected NotificationService $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Get all notifications for the authenticated user.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Notification::where('user_id', $request->user()->id)
            ->orderBy('created_at', 'desc');

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by channel
        if ($request->has('channel')) {
            $query->channel($request->channel);
        }

        // Filter by type
        if ($request->has('type')) {
            $query->type($request->type);
        }

        // Filter by priority
        if ($request->has('priority')) {
            $query->priority($request->priority);
        }

        // Filter unread
        if ($request->boolean('unread')) {
            $query->unread();
        }

        $notifications = $query->paginate($request->get('per_page', 20));

        return response()->json($notifications);
    }

    /**
     * Get a specific notification.
     */
    public function show(int $id): JsonResponse
    {
        $notification = Notification::findOrFail($id);

        // Verify permission
        if ($notification->user_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Auto mark as read when viewed
        if (! $notification->isRead()) {
            $notification->markAsRead();
        }

        return response()->json(['data' => $notification]);
    }

    /**
     * Create a new notification.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'notifiable_type' => 'nullable|string',
            'notifiable_id' => 'nullable|integer',
            'type' => 'required|string',
            'channel' => 'required|in:email,whatsapp,push,sms,in_app',
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'data' => 'nullable|array',
            'priority' => 'sometimes|in:low,normal,high,urgent',
            'scheduled_at' => 'nullable|date|after:now',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $data = $validator->validated();
        $data['company_id'] = $request->user()->company_id;

        $notification = $this->notificationService->create($data);

        return response()->json([
            'message' => 'Notification created successfully',
            'data' => $notification,
        ], 201);
    }

    /**
     * Mark notification as read.
     */
    public function markAsRead(int $id): JsonResponse
    {
        $notification = Notification::findOrFail($id);

        // Verify permission
        if ($notification->user_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $notification->markAsRead();

        return response()->json([
            'message' => 'Notification marked as read',
            'data' => $notification->fresh(),
        ]);
    }

    /**
     * Mark all notifications as read.
     */
    public function markAllAsRead(Request $request): JsonResponse
    {
        $count = Notification::where('user_id', $request->user()->id)
            ->whereNull('read_at')
            ->update([
                'status' => 'read',
                'read_at' => now(),
            ]);

        return response()->json([
            'message' => 'All notifications marked as read',
            'count' => $count,
        ]);
    }

    /**
     * Delete a notification.
     */
    public function destroy(int $id): JsonResponse
    {
        $notification = Notification::findOrFail($id);

        // Verify permission
        if ($notification->user_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $notification->delete();

        return response()->json([
            'message' => 'Notification deleted successfully',
        ]);
    }

    /**
     * Get notification statistics.
     */
    public function statistics(Request $request): JsonResponse
    {
        $userId = $request->user()->id;

        $stats = [
            'total' => Notification::where('user_id', $userId)->count(),
            'unread' => Notification::where('user_id', $userId)->unread()->count(),
            'by_channel' => Notification::where('user_id', $userId)
                ->selectRaw('channel, COUNT(*) as count')
                ->groupBy('channel')
                ->pluck('count', 'channel'),
            'by_priority' => Notification::where('user_id', $userId)
                ->selectRaw('priority, COUNT(*) as count')
                ->groupBy('priority')
                ->pluck('count', 'priority'),
            'recent_unread' => Notification::where('user_id', $userId)
                ->unread()
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get(),
        ];

        return response()->json(['data' => $stats]);
    }

    /**
     * Send a test notification.
     */
    public function sendTest(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'channel' => 'required|in:email,whatsapp,push,sms,in_app',
            'message' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $notification = $this->notificationService->create([
            'company_id' => $request->user()->company_id,
            'user_id' => $request->user()->id,
            'type' => 'test',
            'channel' => $request->channel,
            'title' => 'Test Notification',
            'message' => $request->message,
            'priority' => 'normal',
        ]);

        return response()->json([
            'message' => 'Test notification sent',
            'data' => $notification,
        ]);
    }
}
