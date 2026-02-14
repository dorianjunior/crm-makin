<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\SystemLog;
use Illuminate\Http\Request;

class SystemLogController extends Controller
{
    public function index(Request $request)
    {
        $query = SystemLog::with('user');

        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->has('action')) {
            $query->where('action', $request->action);
        }

        if ($request->has('entity')) {
            $query->where('entity', $request->entity);
        }

        $logs = $query->orderBy('created_at', 'desc')->paginate(50);

        return response()->json($logs);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'nullable|exists:users,id',
            'action' => 'required|string|max:255',
            'entity' => 'required|string|max:255',
            'entity_id' => 'nullable|integer',
        ]);

        $log = SystemLog::create($validated);

        return response()->json($log, 201);
    }

    public function show(SystemLog $systemLog)
    {
        return response()->json($systemLog->load('user'));
    }
}
