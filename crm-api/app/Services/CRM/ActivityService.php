<?php

namespace App\Services\CRM;

use App\Models\CRM\Activity;
use Illuminate\Support\Collection;

class ActivityService
{
    public function create(array $data): Activity
    {
        return Activity::create($data);
    }

    public function update(Activity $activity, array $data): Activity
    {
        $activity->update($data);

        return $activity->fresh();
    }

    public function delete(Activity $activity): bool
    {
        return $activity->delete();
    }

    public function getByLead(int $leadId): Collection
    {
        return Activity::where('lead_id', $leadId)
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getByUser(int $userId): Collection
    {
        return Activity::where('user_id', $userId)
            ->with('lead')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function logActivity(int $leadId, int $userId, string $type, string $description): Activity
    {
        return Activity::create([
            'lead_id' => $leadId,
            'user_id' => $userId,
            'type' => $type,
            'description' => $description,
        ]);
    }

    public function getRecentActivities(int $companyId, int $limit = 50): Collection
    {
        return Activity::whereHas('lead', function ($query) use ($companyId) {
            $query->where('company_id', $companyId);
        })
            ->with(['lead', 'user'])
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }
}
