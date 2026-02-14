<?php

namespace App\Services\CRM;

use App\Models\CRM\Activity;
use App\Models\CRM\Lead;
use App\Models\Admin\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class ActivityService
{
    public function getActivitiesForIndex(int $companyId, array $filters = []): LengthAwarePaginator
    {
        $query = Activity::query()
            ->with(['lead', 'user'])
            ->forCompany($companyId);

        $this->applyFilters($query, $filters);

        return $query->orderBy('created_at', 'desc')->paginate(20);
    }

    public function getStats(int $companyId): array
    {
        $baseQuery = Activity::forCompany($companyId);

        return [
            'total' => (clone $baseQuery)->count(),
            'today' => (clone $baseQuery)->today()->count(),
            'this_week' => (clone $baseQuery)->thisWeek()->count(),
            'this_month' => (clone $baseQuery)->thisMonth()->count(),
        ];
    }

    public function getLeadsForCompany(int $companyId): Collection
    {
        return Lead::where('company_id', $companyId)
            ->select('id', 'name')
            ->orderBy('name')
            ->get();
    }

    public function getUsersForCompany(int $companyId): Collection
    {
        return User::where('company_id', $companyId)
            ->select('id', 'name')
            ->orderBy('name')
            ->get();
    }

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

    public function logActivity(int $leadId, int $userId, string $type, string $description, ?int $companyId = null): Activity
    {
        return Activity::create([
            'lead_id' => $leadId,
            'user_id' => $userId,
            'company_id' => $companyId,
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

    private function applyFilters(Builder $query, array $filters): void
    {
        if (! empty($filters['search'])) {
            $query->search($filters['search']);
        }

        if (! empty($filters['type'])) {
            $query->ofType($filters['type']);
        }

        if (! empty($filters['lead_id'])) {
            $query->forLead($filters['lead_id']);
        }

        if (! empty($filters['user_id'])) {
            $query->forUser($filters['user_id']);
        }
    }
}
