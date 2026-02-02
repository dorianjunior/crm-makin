<?php

namespace App\Http\Controllers\API\CMS;

use App\Http\Controllers\Controller;
use App\Http\Requests\CMS\StoreSiteRequest;
use App\Http\Requests\CMS\UpdateSiteRequest;
use App\Http\Resources\CMS\SiteResource;
use App\Models\CMS\Site;
use App\Services\CMS\SiteService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class SiteController extends Controller
{
    public function __construct(
        protected SiteService $siteService
    ) {}

    public function index(): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Site::class);

        $companyId = auth()->user()->company_id;
        $sites = $this->siteService->getByCompany($companyId);

        return SiteResource::collection($sites);
    }

    public function store(StoreSiteRequest $request): SiteResource
    {
        $this->authorize('create', Site::class);

        $data = $request->validated();
        $data['company_id'] = auth()->user()->company_id;

        $site = $this->siteService->create($data);

        return new SiteResource($site);
    }

    public function show(Site $site): SiteResource
    {
        $this->authorize('view', $site);

        return new SiteResource($site);
    }

    public function update(UpdateSiteRequest $request, Site $site): SiteResource
    {
        $this->authorize('update', $site);

        $data = $request->validated();
        $site = $this->siteService->update($site, $data);

        return new SiteResource($site);
    }

    public function destroy(Site $site): JsonResponse
    {
        $this->authorize('delete', $site);

        $this->siteService->delete($site);

        return response()->json(['message' => 'Site deleted successfully']);
    }

    public function regenerateApiKey(Site $site): JsonResponse
    {
        $this->authorize('regenerateApiKey', $site);

        $apiKey = $this->siteService->regenerateApiKey($site);

        return response()->json([
            'message' => 'API key regenerated successfully',
            'api_key' => $apiKey,
        ]);
    }

    public function activate(Site $site): SiteResource
    {
        $this->authorize('toggle', $site);

        $site = $this->siteService->activate($site);

        return new SiteResource($site);
    }

    public function deactivate(Site $site): SiteResource
    {
        $this->authorize('toggle', $site);

        $site = $this->siteService->deactivate($site);

        return new SiteResource($site);
    }
}
