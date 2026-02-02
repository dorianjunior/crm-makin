<?php

namespace App\Http\Controllers\API\CRM;

use App\Http\Controllers\Controller;
use App\Http\Requests\CRM\StoreSystemLogRequest;
use App\Http\Resources\CRM\SystemLogResource;
use App\Models\CRM\SystemLog;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class SystemLogController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = SystemLog::with('user');

        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->has('action')) {
            $query->where('action', $request->action);
        }

        if ($request->has('model')) {
            $query->where('model', $request->model);
        }

        $logs = $query->orderBy('created_at', 'desc')->paginate(50);

        return SystemLogResource::collection($logs);
    }

    public function store(StoreSystemLogRequest $request): SystemLogResource
    {
        $log = SystemLog::create(array_merge(
            $request->validated(),
            [
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]
        ));

        return new SystemLogResource($log->load('user'));
    }

    public function show(SystemLog $systemLog): SystemLogResource
    {
        $systemLog->load('user');

        return new SystemLogResource($systemLog);
    }
}
