<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\CRM\StoreTaskRequest;
use App\Http\Requests\CRM\UpdateTaskRequest;
use App\Models\CRM\Task;
use App\Services\CRM\TaskService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class TaskController extends Controller
{
    public function __construct(
        private readonly TaskService $taskService
    ) {}

    public function index(): Response
    {
        $companyId = auth()->user()->company_id;
        $filters = request()->only(['search', 'status', 'priority', 'assigned_to']);

        $tasks = $this->taskService->getTasksForIndex($companyId, $filters);
        $leads = $this->taskService->getLeadsForCompany($companyId);
        $users = $this->taskService->getUsersForCompany($companyId);
        $stats = $this->taskService->getStats($companyId);

        return Inertia::render('CRM/Tasks/Index', [
            'tasks' => $tasks,
            'leads' => $leads,
            'users' => $users,
            'stats' => $stats,
        ]);
    }

    public function store(StoreTaskRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['company_id'] = auth()->user()->company_id;

        $this->taskService->create($data);

        return redirect()
            ->back()
            ->with('success', 'Tarefa criada com sucesso!');
    }

    public function update(UpdateTaskRequest $request, Task $task): RedirectResponse
    {
        // Verificar multi-tenant
        if ($task->company_id !== auth()->user()->company_id) {
            abort(403, 'Unauthorized');
        }

        // Se mudou para completed, registrar conclusão
        if ($request->status === 'completed' && $task->status !== 'completed') {
            $this->taskService->complete($task, auth()->id());
        }
        // Se estava completed e mudou status, reabrir
        elseif ($task->status === 'completed' && $request->status !== 'completed') {
            $this->taskService->reopen($task);
            $this->taskService->update($task, $request->except(['status']));
        } else {
            $this->taskService->update($task, $request->validated());
        }

        return redirect()
            ->back()
            ->with('success', 'Tarefa atualizada com sucesso!');
    }

    public function destroy(Task $task): RedirectResponse
    {
        // Verificar multi-tenant
        if ($task->company_id !== auth()->user()->company_id) {
            abort(403, 'Unauthorized');
        }

        $this->taskService->delete($task);

        return redirect()
            ->back()
            ->with('success', 'Tarefa excluída com sucesso!');
    }

    /**
     * Toggle task completion status
     */
    public function toggleComplete(Task $task): RedirectResponse
    {
        // Verificar multi-tenant
        if ($task->company_id !== auth()->user()->company_id) {
            abort(403, 'Unauthorized');
        }

        if ($task->status === 'completed') {
            $this->taskService->reopen($task);
        } else {
            $this->taskService->complete($task, auth()->id());
        }

        return redirect()
            ->back()
            ->with('success', 'Status da tarefa atualizado!');
    }
}
