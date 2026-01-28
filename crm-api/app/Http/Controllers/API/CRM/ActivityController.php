<?php

namespace App\Http\Controllers\API\CRM;

use App\Http\Controllers\Controller;
use App\Http\Requests\CRM\StoreActivityRequest;
use App\Http\Requests\CRM\UpdateActivityRequest;
use App\Http\Resources\CRM\ActivityResource;
use App\Models\CRM\Activity;
use App\Services\CRM\ActivityService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class ActivityController extends Controller
{
    public function __construct(
        private readonly ActivityService $activityService
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        if ($request->has('lead_id')) {
            $activities = $this->activityService->getByLead($request->input('lead_id'));
        } elseif ($request->has('user_id')) {
            $activities = $this->activityService->getByUser($request->input('user_id'));
        } else {
            $activities = Activity::with(['lead', 'user'])
                ->orderBy('created_at', 'desc')
                ->paginate(15);
        }

        return ActivityResource::collection($activities);
    }

    public function store(StoreActivityRequest $request): ActivityResource
    {
        $activity = $this->activityService->create($request->validated());

        return new ActivityResource($activity->load(['lead', 'user']));
    }

    public function show(Activity $activity): ActivityResource
    {
        $activity->load(['lead', 'user']);

        return new ActivityResource($activity);
    }

    public function update(UpdateActivityRequest $request, Activity $activity): ActivityResource
    {
        $activity = $this->activityService->update($activity, $request->validated());

        return new ActivityResource($activity->load(['lead', 'user']));
    }

    public function destroy(Activity $activity): Response
    {
        $this->activityService->delete($activity);

        return response()->noContent();
    }

    public function recent(Request $request): AnonymousResourceCollection
    {
        $limit = $request->input('limit', 10);
        $activities = $this->activityService->getRecentActivities($limit);

        return ActivityResource::collection($activities);
    }
}
