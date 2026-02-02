<?php

namespace App\Http\Controllers\API\CMS;

use App\Http\Controllers\Controller;
use App\Http\Requests\CMS\StoreBannerRequest;
use App\Http\Requests\CMS\UpdateBannerRequest;
use App\Http\Resources\CMS\BannerResource;
use App\Models\CMS\Banner;
use App\Services\CMS\PublishingService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class BannerController extends Controller
{
    public function __construct(
        private readonly PublishingService $publishingService
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Banner::with(['site', 'creator']);

        if ($request->has('site_id')) {
            $query->forSite($request->site_id);
        }

        if ($request->has('location')) {
            $query->byLocation($request->location);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->boolean('active_only')) {
            $query->active();
        }

        $banners = $query->orderBy('order')->paginate(15);

        return BannerResource::collection($banners);
    }

    public function store(StoreBannerRequest $request): BannerResource
    {
        $banner = Banner::create($request->validated());

        return new BannerResource($banner->load(['site', 'creator']));
    }

    public function show(Banner $banner): BannerResource
    {
        $banner->load(['site', 'creator', 'versions', 'approvals']);

        return new BannerResource($banner);
    }

    public function update(UpdateBannerRequest $request, Banner $banner): BannerResource
    {
        $banner->update($request->validated());

        return new BannerResource($banner->load(['site', 'creator']));
    }

    public function destroy(Banner $banner): Response
    {
        $banner->delete();

        return response()->noContent();
    }

    public function publish(Banner $banner): BannerResource
    {
        $this->publishingService->publish($banner);

        return new BannerResource($banner);
    }

    public function unpublish(Banner $banner): BannerResource
    {
        $this->publishingService->unpublish($banner);

        return new BannerResource($banner);
    }

    public function requestApproval(Banner $banner): BannerResource
    {
        $this->publishingService->requestApproval($banner);

        return new BannerResource($banner);
    }
}
