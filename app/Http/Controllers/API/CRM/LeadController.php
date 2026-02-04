<?php

namespace App\Http\Controllers\API\CRM;

use App\Http\Controllers\Controller;
use App\Http\Requests\CRM\StoreLeadRequest;
use App\Http\Requests\CRM\UpdateLeadRequest;
use App\Http\Resources\CRM\LeadResource;
use App\Models\CRM\Lead;
use App\Services\CRM\LeadService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class LeadController extends Controller
{
    public function __construct(
        private readonly LeadService $leadService
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $filters = $request->only(['status', 'assigned_to', 'source_id', 'search', 'sort', 'direction', 'page']);
        $leads = $this->leadService->getByCompany($filters);
        $stats = $this->leadService->getStats(auth()->user()->company_id);

        return LeadResource::collection($leads)->additional([
            'stats' => $stats,
        ]);
    }

    public function store(StoreLeadRequest $request): LeadResource
    {
        $lead = $this->leadService->create($request->validated());

        return new LeadResource($lead->load(['company', 'source', 'assignedUser']));
    }

    public function show(Lead $lead): LeadResource
    {
        $lead->load([
            'company',
            'source',
            'assignedUser',
            'activities',
            'tasks',
            'proposals',
            'pipelineStages',
        ]);

        return new LeadResource($lead);
    }

    public function update(UpdateLeadRequest $request, Lead $lead): LeadResource
    {
        $lead = $this->leadService->update($lead, $request->validated());

        return new LeadResource($lead->load(['company', 'source', 'assignedUser']));
    }

    public function destroy(Lead $lead): Response
    {
        $this->leadService->delete($lead);

        return response()->noContent();
    }

    public function assign(Request $request, Lead $lead): LeadResource
    {
        $request->validate(['user_id' => 'required|exists:users,id']);

        $lead = $this->leadService->assignTo($lead, $request->input('user_id'));

        return new LeadResource($lead->load(['assignedUser']));
    }

    public function changeStatus(Request $request, Lead $lead): LeadResource
    {
        $request->validate(['status' => 'required|string']);

        $lead = $this->leadService->changeStatus($lead, $request->input('status'));

        return new LeadResource($lead);
    }
}
