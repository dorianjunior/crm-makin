<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\CRM\StoreActivityRequest;
use App\Http\Requests\CRM\UpdateActivityRequest;
use App\Models\CRM\Activity;
use App\Services\CRM\ActivityService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class ActivityController extends Controller
{
    public function __construct(
        private readonly ActivityService $activityService
    ) {}

    public function index(): Response
    {
        $companyId = auth()->user()->company_id;
        $filters = request()->only(['search', 'type', 'lead_id', 'user_id']);

        $activities = $this->activityService->getActivitiesForIndex($companyId, $filters);
        $leads = $this->activityService->getLeadsForCompany($companyId);
        $users = $this->activityService->getUsersForCompany($companyId);
        $stats = $this->activityService->getStats($companyId);

        return Inertia::render('Activities/Index', [
            'activities' => $activities,
            'leads' => $leads,
            'users' => $users,
            'stats' => $stats,
        ]);
    }

    public function store(StoreActivityRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['user_id'] = auth()->id();
        $data['company_id'] = auth()->user()->company_id;

        $this->activityService->create($data);

        return redirect()
            ->back()
            ->with('success', 'Atividade criada com sucesso!');
    }

    public function update(UpdateActivityRequest $request, Activity $activity): RedirectResponse
    {
        // Verificar multi-tenant
        if ($activity->company_id !== auth()->user()->company_id) {
            abort(403, 'Unauthorized');
        }

        $this->activityService->update($activity, $request->validated());

        return redirect()
            ->back()
            ->with('success', 'Atividade atualizada com sucesso!');
    }

    public function destroy(Activity $activity): RedirectResponse
    {
        // Verificar multi-tenant
        if ($activity->company_id !== auth()->user()->company_id) {
            abort(403, 'Unauthorized');
        }

        $this->activityService->delete($activity);

        return redirect()
            ->back()
            ->with('success', 'Atividade excluída com sucesso!');
    }
}
