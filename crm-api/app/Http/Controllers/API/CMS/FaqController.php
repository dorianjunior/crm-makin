<?php

namespace App\Http\Controllers\API\CMS;

use App\Http\Controllers\Controller;
use App\Http\Requests\CMS\StoreFaqRequest;
use App\Http\Requests\CMS\UpdateFaqRequest;
use App\Http\Resources\CMS\FaqResource;
use App\Models\CMS\Faq;
use App\Services\CMS\PublishingService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class FaqController extends Controller
{
    public function __construct(
        private readonly PublishingService $publishingService
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Faq::with(['site', 'creator']);

        if ($request->has('site_id')) {
            $query->forSite($request->site_id);
        }

        if ($request->has('category')) {
            $query->byCategory($request->category);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $faqs = $query->orderBy('category')->orderBy('order')->paginate(50);

        return FaqResource::collection($faqs);
    }

    public function store(StoreFaqRequest $request): FaqResource
    {
        $faq = Faq::create($request->validated());

        return new FaqResource($faq->load(['site', 'creator']));
    }

    public function show(Faq $faq): FaqResource
    {
        $faq->load(['site', 'creator', 'versions', 'approvals']);

        return new FaqResource($faq);
    }

    public function update(UpdateFaqRequest $request, Faq $faq): FaqResource
    {
        $faq->update($request->validated());

        return new FaqResource($faq->load(['site', 'creator']));
    }

    public function destroy(Faq $faq): Response
    {
        $faq->delete();

        return response()->noContent();
    }

    public function publish(Faq $faq): FaqResource
    {
        $this->publishingService->publish($faq);

        return new FaqResource($faq);
    }

    public function unpublish(Faq $faq): FaqResource
    {
        $this->publishingService->unpublish($faq);

        return new FaqResource($faq);
    }

    public function requestApproval(Faq $faq): FaqResource
    {
        $this->publishingService->requestApproval($faq);

        return new FaqResource($faq);
    }
}
