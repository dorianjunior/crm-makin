<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use Illuminate\Http\Request;

class ActivityController extends Controller
{
    public function index(Request $request)
    {
        $query = Activity::with(['lead', 'user']);

        if ($request->has('lead_id')) {
            $query->where('lead_id', $request->lead_id);
        }

        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        $activities = $query->orderBy('created_at', 'desc')->paginate(15);
        return response()->json($activities);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'lead_id' => 'required|exists:leads,id',
            'user_id' => 'required|exists:users,id',
            'type' => 'required|in:call,email,meeting,note,task,whatsapp',
            'description' => 'required|string',
        ]);

        $activity = Activity::create($validated);
        return response()->json($activity->load(['lead', 'user']), 201);
    }

    public function show(Activity $activity)
    {
        return response()->json($activity->load(['lead', 'user']));
    }

    public function update(Request $request, Activity $activity)
    {
        $validated = $request->validate([
            'type' => 'sometimes|in:call,email,meeting,note,task,whatsapp',
            'description' => 'sometimes|string',
        ]);

        $activity->update($validated);
        return response()->json($activity->load(['lead', 'user']));
    }

    public function destroy(Activity $activity)
    {
        $activity->delete();
        return response()->json(['message' => 'Activity deleted successfully']);
    }
}
