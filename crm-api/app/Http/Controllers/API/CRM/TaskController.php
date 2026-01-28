<?php

namespace App\Http\Controllers\API\CRM;

use App\Http\Controllers\Controller;
use App\Http\Requests\CRM\StoreTaskRequest;
use App\Http\Requests\CRM\UpdateTaskRequest;
use App\Http\Resources\CRM\TaskResource;
use App\Models\CRM\Task;
use App\Services\CRM\TaskService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class TaskController extends Controller
{
    public function __construct(
        private readonly TaskService $taskService
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $filters = $request->only(['company_id', 'user_id', 'status']);
        $tasks = $this->taskService->getByUser($filters);

        return TaskResource::collection($tasks);
    }

    public function store(StoreTaskRequest $request): TaskResource
    {
        $task = $this->taskService->create($request->validated());

        return new TaskResource($task->load(['company', 'lead', 'user']));
    }

    public function show(Task $task): TaskResource
    {
        $task->load(['company', 'lead', 'user']);

        return new TaskResource($task);
    }

    public function update(UpdateTaskRequest $request, Task $task): TaskResource
    {
        $task = $this->taskService->update($task, $request->validated());

        return new TaskResource($task->load(['company', 'lead', 'user']));
    }

    public function destroy(Task $task): Response
    {
        $this->taskService->delete($task);

        return response()->noContent();
    }

    public function complete(Task $task): TaskResource
    {
        $task = $this->taskService->complete($task);

        return new TaskResource($task);
    }

    public function cancel(Task $task): TaskResource
    {
        $task = $this->taskService->cancel($task);

        return new TaskResource($task);
    }

    public function overdue(): AnonymousResourceCollection
    {
        $tasks = $this->taskService->getOverdueTasks();

        return TaskResource::collection($tasks);
    }

    public function upcoming(Request $request): AnonymousResourceCollection
    {
        $days = $request->input('days', 7);
        $tasks = $this->taskService->getUpcomingTasks($days);

        return TaskResource::collection($tasks);
    }
}
