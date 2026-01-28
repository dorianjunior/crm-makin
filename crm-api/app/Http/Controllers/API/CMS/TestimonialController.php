<?php

namespace App\Http\Controllers\API\CMS;

use App\Http\Controllers\Controller;
use App\Http\Requests\CMS\StoreTestimonialRequest;
use App\Http\Requests\CMS\UpdateTestimonialRequest;
use App\Http\Resources\CMS\TestimonialResource;
use App\Models\CMS\Testimonial;
use App\Services\CMS\PublishingService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class TestimonialController extends Controller
{
    public function __construct(
        private readonly PublishingService $publishingService
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Testimonial::with(['site', 'creator']);

        if ($request->has('site_id')) {
            $query->forSite($request->site_id);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('min_rating')) {
            $query->highRated($request->min_rating);
        }

        $testimonials = $query->orderBy('order')->paginate(15);

        return TestimonialResource::collection($testimonials);
    }

    public function store(StoreTestimonialRequest $request): TestimonialResource
    {
        $testimonial = Testimonial::create($request->validated());

        return new TestimonialResource($testimonial->load(['site', 'creator']));
    }

    public function show(Testimonial $testimonial): TestimonialResource
    {
        $testimonial->load(['site', 'creator', 'versions', 'approvals']);

        return new TestimonialResource($testimonial);
    }

    public function update(UpdateTestimonialRequest $request, Testimonial $testimonial): TestimonialResource
    {
        $testimonial->update($request->validated());

        return new TestimonialResource($testimonial->load(['site', 'creator']));
    }

    public function destroy(Testimonial $testimonial): Response
    {
        $testimonial->delete();

        return response()->noContent();
    }

    public function publish(Testimonial $testimonial): TestimonialResource
    {
        $this->publishingService->publish($testimonial);

        return new TestimonialResource($testimonial);
    }

    public function unpublish(Testimonial $testimonial): TestimonialResource
    {
        $this->publishingService->unpublish($testimonial);

        return new TestimonialResource($testimonial);
    }

    public function requestApproval(Testimonial $testimonial): TestimonialResource
    {
        $this->publishingService->requestApproval($testimonial);

        return new TestimonialResource($testimonial);
    }
}
