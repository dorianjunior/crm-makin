<?php

namespace App\Http\Controllers;

use App\Models\NotificationPreference;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class NotificationPreferenceController extends Controller
{
    /**
     * Get all preferences for the authenticated user.
     */
    public function index(Request $request): JsonResponse
    {
        $preferences = NotificationPreference::where('user_id', $request->user()->id)
            ->orderBy('notification_type')
            ->get();

        return response()->json(['data' => $preferences]);
    }

    /**
     * Get a specific preference.
     */
    public function show(int $id): JsonResponse
    {
        $preference = NotificationPreference::findOrFail($id);

        // Verify permission
        if ($preference->user_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json(['data' => $preference]);
    }

    /**
     * Get preference by notification type.
     */
    public function getByType(Request $request, string $type): JsonResponse
    {
        $preference = NotificationPreference::getOrCreateForUser(
            $request->user()->id,
            $type
        );

        return response()->json(['data' => $preference]);
    }

    /**
     * Create or update a preference.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'notification_type' => 'required|string|max:255',
            'email_enabled' => 'boolean',
            'whatsapp_enabled' => 'boolean',
            'push_enabled' => 'boolean',
            'sms_enabled' => 'boolean',
            'in_app_enabled' => 'boolean',
            'schedule' => 'nullable|array',
            'schedule.timezone' => 'nullable|string',
            'schedule.working_hours' => 'nullable|array',
            'schedule.working_hours.start' => 'nullable|integer|min:0|max:23',
            'schedule.working_hours.end' => 'nullable|integer|min:0|max:24',
            'schedule.days_of_week' => 'nullable|array',
            'schedule.days_of_week.*' => 'integer|min:0|max:6',
            'filters' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $data = $validator->validated();
        $data['user_id'] = $request->user()->id;

        $preference = NotificationPreference::updateOrCreate(
            [
                'user_id' => $data['user_id'],
                'notification_type' => $data['notification_type'],
            ],
            $data
        );

        return response()->json([
            'message' => 'Preference saved successfully',
            'data' => $preference,
        ], 201);
    }

    /**
     * Update a preference.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $preference = NotificationPreference::findOrFail($id);

        // Verify permission
        if ($preference->user_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'email_enabled' => 'boolean',
            'whatsapp_enabled' => 'boolean',
            'push_enabled' => 'boolean',
            'sms_enabled' => 'boolean',
            'in_app_enabled' => 'boolean',
            'schedule' => 'nullable|array',
            'filters' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $preference->update($validator->validated());

        return response()->json([
            'message' => 'Preference updated successfully',
            'data' => $preference->fresh(),
        ]);
    }

    /**
     * Enable a channel.
     */
    public function enableChannel(int $id, string $channel): JsonResponse
    {
        $preference = NotificationPreference::findOrFail($id);

        // Verify permission
        if ($preference->user_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validChannels = ['email', 'whatsapp', 'push', 'sms', 'in_app'];
        if (! in_array($channel, $validChannels)) {
            return response()->json(['message' => 'Invalid channel'], 400);
        }

        $preference->enableChannel($channel);

        return response()->json([
            'message' => "Channel {$channel} enabled",
            'data' => $preference->fresh(),
        ]);
    }

    /**
     * Disable a channel.
     */
    public function disableChannel(int $id, string $channel): JsonResponse
    {
        $preference = NotificationPreference::findOrFail($id);

        // Verify permission
        if ($preference->user_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validChannels = ['email', 'whatsapp', 'push', 'sms', 'in_app'];
        if (! in_array($channel, $validChannels)) {
            return response()->json(['message' => 'Invalid channel'], 400);
        }

        $preference->disableChannel($channel);

        return response()->json([
            'message' => "Channel {$channel} disabled",
            'data' => $preference->fresh(),
        ]);
    }

    /**
     * Delete a preference.
     */
    public function destroy(int $id): JsonResponse
    {
        $preference = NotificationPreference::findOrFail($id);

        // Verify permission
        if ($preference->user_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $preference->delete();

        return response()->json([
            'message' => 'Preference deleted successfully',
        ]);
    }

    /**
     * Reset all preferences to default.
     */
    public function resetToDefault(Request $request): JsonResponse
    {
        $count = NotificationPreference::where('user_id', $request->user()->id)
            ->update([
                'email_enabled' => true,
                'whatsapp_enabled' => false,
                'push_enabled' => true,
                'sms_enabled' => false,
                'in_app_enabled' => true,
                'schedule' => null,
                'filters' => null,
            ]);

        return response()->json([
            'message' => 'Preferences reset to default',
            'count' => $count,
        ]);
    }
}
