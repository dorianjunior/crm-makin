<?php

namespace App\Http\Controllers\API\CMS;

use App\Http\Controllers\Controller;
use App\Http\Requests\CMS\StorePortfolioRequest;
use App\Http\Requests\CMS\UpdatePortfolioRequest;
use App\Http\Resources\CMS\PortfolioResource;
use App\Models\CMS\Portfolio;
use App\Services\CMS\PublishingService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class PortfolioController extends Controller
{
    public function __construct(
        private readonly PublishingService $publishingService
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Portfolio::with(['site', 'creator']);

        if ($request->has('site_id')) {
            $query->forSite($request->site_id);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $portfolios = $query->orderBy('order')->paginate(15);

        return PortfolioResource::collection($portfolios);
    }

    public function store(StorePortfolioRequest $request): PortfolioResource
    {
        $portfolio = Portfolio::create($request->validated());

        return new PortfolioResource($portfolio->load(['site', 'creator']));
    }

    public function show(Portfolio $portfolio): PortfolioResource
    {
        $portfolio->load(['site', 'creator', 'versions', 'approvals']);

        return new PortfolioResource($portfolio);
    }

    public function update(UpdatePortfolioRequest $request, Portfolio $portfolio): PortfolioResource
    {
        $portfolio->update($request->validated());

        return new PortfolioResource($portfolio->load(['site', 'creator']));
    }

    public function destroy(Portfolio $portfolio): Response
    {
        $portfolio->delete();

        return response()->noContent();
    }

    public function publish(Portfolio $portfolio): PortfolioResource
    {
        $this->publishingService->publish($portfolio);

        return new PortfolioResource($portfolio);
    }

    public function unpublish(Portfolio $portfolio): PortfolioResource
    {
        $this->publishingService->unpublish($portfolio);

        return new PortfolioResource($portfolio);
    }

    public function requestApproval(Portfolio $portfolio): PortfolioResource
    {
        $this->publishingService->requestApproval($portfolio);

        return new PortfolioResource($portfolio);
    }
}
