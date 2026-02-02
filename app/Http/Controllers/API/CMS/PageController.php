<?php

namespace App\Http\Controllers\API\CMS;

use App\Http\Controllers\Controller;
use App\Http\Requests\CMS\StorePageRequest;
use App\Http\Requests\CMS\UpdatePageRequest;
use App\Http\Resources\CMS\PageResource;
use App\Models\CMS\Page;
use App\Services\CMS\ContentService;
use App\Services\CMS\PublishingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PageController extends Controller
{
    public function __construct(
        protected ContentService $contentService,
        protected PublishingService $publishingService
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Page::class);

        $query = Page::with('creator');

        if ($request->has('site_id')) {
            $query->where('site_id', $request->site_id);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $pages = $query->orderBy('order')->get();

        return PageResource::collection($pages);
    }

    public function store(StorePageRequest $request): PageResource
    {
        $this->authorize('create', Page::class);

        $data = $request->validated();
        $page = $this->contentService->createPage($data);

        return new PageResource($page->load('creator'));
    }

    public function show(Page $page): PageResource
    {
        $this->authorize('view', $page);

        return new PageResource($page->load('creator'));
    }

    public function update(UpdatePageRequest $request, Page $page): PageResource
    {
        $this->authorize('update', $page);

        $data = $request->validated();
        $page = $this->contentService->updatePage($page, $data);

        return new PageResource($page->load('creator'));
    }

    public function destroy(Page $page): JsonResponse
    {
        $this->authorize('delete', $page);

        $this->contentService->deletePage($page);

        return response()->json(['message' => 'Page deleted successfully']);
    }

    public function publish(Page $page): JsonResponse
    {
        $this->authorize('publish', $page);

        $this->publishingService->publish($page, auth()->id());

        return response()->json(['message' => 'Page published successfully']);
    }

    public function unpublish(Page $page): JsonResponse
    {
        $this->authorize('unpublish', $page);

        $this->publishingService->unpublish($page);

        return response()->json(['message' => 'Page unpublished successfully']);
    }

    public function requestApproval(Request $request, Page $page): JsonResponse
    {
        $approval = $this->publishingService->requestApproval(
            $page,
            auth()->id(),
            $request->input('message')
        );

        return response()->json([
            'message' => 'Approval requested successfully',
            'approval_id' => $approval->id,
        ]);
    }
}
