<?php

namespace App\Http\Controllers\API\CRM;

use App\Http\Controllers\Controller;
use App\Http\Requests\CRM\StoreLeadSourceRequest;
use App\Http\Requests\CRM\UpdateLeadSourceRequest;
use App\Http\Resources\CRM\LeadSourceResource;
use App\Models\CRM\LeadSource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class LeadSourceController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = LeadSource::query();

        if ($request->has('company_id')) {
            $query->where('company_id', $request->company_id);
        }

        $sources = $query->withCount('leads')->get();

        return LeadSourceResource::collection($sources);
    }

    public function store(StoreLeadSourceRequest $request): LeadSourceResource
    {
        $source = LeadSource::create($request->validated());

        return new LeadSourceResource($source);
    }

    public function show(LeadSource $leadSource): LeadSourceResource
    {
        $leadSource->loadCount('leads');

        return new LeadSourceResource($leadSource);
    }

    public function update(UpdateLeadSourceRequest $request, LeadSource $leadSource): LeadSourceResource
    {
        $leadSource->update($request->validated());

        return new LeadSourceResource($leadSource);
    }

    public function destroy(LeadSource $leadSource): Response
    {
        $leadSource->delete();

        return response()->noContent();
    }
}
