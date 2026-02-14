<?php

namespace App\Services\CRM;

use App\Enums\TaskStatus;
use App\Models\CRM\Lead;
use App\Models\CRM\Task;
use App\Models\Admin\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class TaskService
{
    public function getTasksForIndex(int $companyId, array $filters = []): LengthAwarePaginator
    {
        $query = Task::query()
            ->with(['lead', 'assignedUser'])
            ->forCompany($companyId);

        $this->applyFilters($query, $filters);

        return $query->orderBy('due_date', 'asc')
            ->orderBy('priority', 'desc')
            ->paginate(20);
    }

    public function getStats(int $companyId): array
    {
        $baseQuery = Task::forCompany($companyId);

        return [
            'total' => (clone $baseQuery)->count(),
            'pending' => (clone $baseQuery)->pending()->count(),
            'in_progress' => (clone $baseQuery)->inProgress()->count(),
            'completed' => (clone $baseQuery)->completed()->count(),
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

    public function create(array $data): Task
    {
        return Task::create($data);
    }

    public function update(Task $task, array $data): Task
    {
        $task->update($data);

        return $task->fresh();
    }

    public function delete(Task $task): bool
    {
        return $task->delete();
    }

    public function complete(Task $task, int $userId): Task
    {
        $task->update([
            'status' => TaskStatus::COMPLETED,
            'completed_at' => now(),
            'completed_by' => $userId,
        ]);

        return $task->fresh();
    }

    public function reopen(Task $task): Task
    {
        $task->update([
            'status' => TaskStatus::PENDING,
            'completed_at' => null,
            'completed_by' => null,
        ]);

        return $task->fresh();
    }

    public function getOverdueTasks(int $companyId): Collection
    {
        return Task::forCompany($companyId)
            ->overdue()
            ->with(['lead', 'assignedUser'])
            ->orderBy('due_date')
            ->get();
    }

    public function getUpcomingTasks(int $companyId): Collection
    {
        return Task::forCompany($companyId)
            ->upcoming()
            ->with(['lead', 'assignedUser'])
            ->orderBy('due_date')
            ->get();
    }

    public function getByLead(int $leadId): Collection
    {
        return Task::where('lead_id', $leadId)
            ->with('assignedUser')
            ->orderBy('due_date')
            ->get();
    }

    public function getByUser(int $userId): Collection
    {
        return Task::where('assigned_to', $userId)
            ->with('lead')
            ->orderBy('due_date')
            ->get();
    }

    private function applyFilters(Builder $query, array $filters): void
    {
        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['priority'])) {
            $query->where('priority', $filters['priority']);
        }

        if (! empty($filters['assigned_to'])) {
            $query->where('assigned_to', $filters['assigned_to']);
        }

        if (! empty($filters['lead_id'])) {
            $query->where('lead_id', $filters['lead_id']);
        }
    }
}
