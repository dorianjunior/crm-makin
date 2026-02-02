<?php

namespace App\Http\Controllers\API\CMS;

use App\Http\Controllers\Controller;
use App\Http\Resources\CMS\BannerResource;
use App\Http\Resources\CMS\FaqResource;
use App\Http\Resources\CMS\FormResource;
use App\Http\Resources\CMS\MenuResource;
use App\Http\Resources\CMS\PageResource;
use App\Http\Resources\CMS\PortfolioResource;
use App\Http\Resources\CMS\PostResource;
use App\Http\Resources\CMS\TeamMemberResource;
use App\Http\Resources\CMS\TestimonialResource;
use App\Models\CMS\Banner;
use App\Models\CMS\Faq;
use App\Models\CMS\Form;
use App\Models\CMS\Menu;
use App\Models\CMS\Page;
use App\Models\CMS\Portfolio;
use App\Models\CMS\Post;
use App\Models\CMS\TeamMember;
use App\Models\CMS\Testimonial;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;

class PreviewController extends Controller
{
    protected array $contentTypes = [
        'pages' => [
            'model' => Page::class,
            'resource' => PageResource::class,
        ],
        'posts' => [
            'model' => Post::class,
            'resource' => PostResource::class,
        ],
        'portfolios' => [
            'model' => Portfolio::class,
            'resource' => PortfolioResource::class,
        ],
        'faqs' => [
            'model' => Faq::class,
            'resource' => FaqResource::class,
        ],
        'testimonials' => [
            'model' => Testimonial::class,
            'resource' => TestimonialResource::class,
        ],
        'team-members' => [
            'model' => TeamMember::class,
            'resource' => TeamMemberResource::class,
        ],
        'forms' => [
            'model' => Form::class,
            'resource' => FormResource::class,
        ],
        'banners' => [
            'model' => Banner::class,
            'resource' => BannerResource::class,
        ],
        'menus' => [
            'model' => Menu::class,
            'resource' => MenuResource::class,
        ],
    ];

    /**
     * Generate a preview token for content
     */
    public function generateToken(Request $request, string $type, int $id)
    {
        if (! isset($this->contentTypes[$type])) {
            return response()->json([
                'message' => 'Tipo de conteúdo inválido.',
            ], Response::HTTP_NOT_FOUND);
        }

        $modelClass = $this->contentTypes[$type]['model'];
        $content = $modelClass::find($id);

        if (! $content) {
            return response()->json([
                'message' => 'Conteúdo não encontrado.',
            ], Response::HTTP_NOT_FOUND);
        }

        // Generate token (valid for 24 hours)
        $token = bin2hex(random_bytes(32));
        $cacheKey = "preview_token:{$token}";

        Cache::put($cacheKey, [
            'type' => $type,
            'id' => $id,
            'user_id' => auth()->id(),
        ], now()->addHours(24));

        return response()->json([
            'token' => $token,
            'expires_at' => now()->addHours(24)->toIso8601String(),
            'preview_url' => url("/api/cms/preview/{$type}/{$id}/{$token}"),
        ]);
    }

    /**
     * Public preview endpoint (no authentication required)
     */
    public function show(string $type, int $id, string $token)
    {
        $cacheKey = "preview_token:{$token}";
        $tokenData = Cache::get($cacheKey);

        if (! $tokenData) {
            return response()->json([
                'message' => 'Token de preview inválido ou expirado.',
            ], Response::HTTP_UNAUTHORIZED);
        }

        if ($tokenData['type'] !== $type || $tokenData['id'] !== $id) {
            return response()->json([
                'message' => 'Token não corresponde ao conteúdo solicitado.',
            ], Response::HTTP_FORBIDDEN);
        }

        if (! isset($this->contentTypes[$type])) {
            return response()->json([
                'message' => 'Tipo de conteúdo inválido.',
            ], Response::HTTP_NOT_FOUND);
        }

        $modelClass = $this->contentTypes[$type]['model'];
        $resourceClass = $this->contentTypes[$type]['resource'];

        $content = $modelClass::withTrashed()->find($id);

        if (! $content) {
            return response()->json([
                'message' => 'Conteúdo não encontrado.',
            ], Response::HTTP_NOT_FOUND);
        }

        $content->load(['site', 'creator', 'versions', 'approvals']);

        return new $resourceClass($content);
    }

    /**
     * Invalidate a preview token
     */
    public function revokeToken(string $token)
    {
        $cacheKey = "preview_token:{$token}";
        Cache::forget($cacheKey);

        return response()->json([
            'message' => 'Token de preview revogado com sucesso.',
        ]);
    }
}
