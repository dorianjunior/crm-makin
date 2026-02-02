<?php

namespace App\Http\Controllers\API\CMS;

use App\Http\Controllers\Controller;
use App\Http\Requests\CMS\StorePostRequest;
use App\Http\Requests\CMS\UpdatePostRequest;
use App\Http\Resources\CMS\PostResource;
use App\Models\CMS\Post;
use App\Services\CMS\ContentService;
use App\Services\CMS\PublishingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PostController extends Controller
{
    public function __construct(
        protected ContentService $contentService,
        protected PublishingService $publishingService
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Post::with(['creator', 'category']);

        if ($request->has('site_id')) {
            $query->where('site_id', $request->site_id);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('category_id')) {
            $query->byCategory($request->category_id);
        }

        if ($request->has('tag')) {
            $query->byTag($request->tag);
        }

        $posts = $query->orderBy('published_at', 'desc')->get();

        return PostResource::collection($posts);
    }

    public function store(StorePostRequest $request): PostResource
    {
        $data = $request->validated();
        $post = $this->contentService->createPost($data);

        return new PostResource($post->load(['creator', 'category']));
    }

    public function show(Post $post): PostResource
    {
        return new PostResource($post->load(['creator', 'category']));
    }

    public function update(UpdatePostRequest $request, Post $post): PostResource
    {
        $data = $request->validated();
        $post = $this->contentService->updatePost($post, $data);

        return new PostResource($post->load(['creator', 'category']));
    }

    public function destroy(Post $post): JsonResponse
    {
        $this->contentService->deletePost($post);

        return response()->json(['message' => 'Post deleted successfully']);
    }

    public function publish(Post $post): JsonResponse
    {
        $this->publishingService->publish($post, auth()->id());

        return response()->json(['message' => 'Post published successfully']);
    }

    public function unpublish(Post $post): JsonResponse
    {
        $this->publishingService->unpublish($post);

        return response()->json(['message' => 'Post unpublished successfully']);
    }

    public function requestApproval(Request $request, Post $post): JsonResponse
    {
        $approval = $this->publishingService->requestApproval(
            $post,
            auth()->id(),
            $request->input('message')
        );

        return response()->json([
            'message' => 'Approval requested successfully',
            'approval_id' => $approval->id,
        ]);
    }
}
