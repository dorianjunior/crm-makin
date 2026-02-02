<?php

namespace App\Services\CRM;

use App\Models\CRM\Task;
use Illuminate\Support\Collection;

class TaskService
{
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

    public function getByCompany(int $companyId, array $filters = []): Collection
    {
        $query = Task::where('company_id', $companyId);

        if (isset($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['lead_id'])) {
            $query->where('lead_id', $filters['lead_id']);
        }

        return $query->with(['lead', 'user'])->orderBy('due_date')->get();
    }

    public function getByUser(int $userId, array $filters = []): Collection
    {
        $query = Task::where('user_id', $userId);

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->with(['lead'])->orderBy('due_date')->get();
    }

    public function complete(Task $task): Task
    {
        $task->update(['status' => 'completed']);

        return $task->fresh();
    }

    public function cancel(Task $task): Task
    {
        $task->update(['status' => 'cancelled']);

        return $task->fresh();
    }

    public function getOverdueTasks(int $companyId): Collection
    {
        return Task::where('company_id', $companyId)
            ->whereIn('status', ['pending', 'in_progress'])
            ->where('due_date', '<', now())
            ->with(['lead', 'user'])
            ->orderBy('due_date')
            ->get();
    }

    public function getUpcomingTasks(int $companyId, int $days = 7): Collection
    {
        return Task::where('company_id', $companyId)
            ->whereIn('status', ['pending', 'in_progress'])
            ->whereBetween('due_date', [now(), now()->addDays($days)])
            ->with(['lead', 'user'])
            ->orderBy('due_date')
            ->get();
    }
}
