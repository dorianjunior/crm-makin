<?php

namespace App\Http\Controllers\API\CMS;

use App\Http\Controllers\Controller;
use App\Http\Resources\CMS\ContentVersionResource;
use App\Models\CMS\Banner;
use App\Models\CMS\Faq;
use App\Models\CMS\Menu;
use App\Models\CMS\Page;
use App\Models\CMS\Portfolio;
use App\Models\CMS\Post;
use App\Models\CMS\TeamMember;
use App\Models\CMS\Testimonial;
use App\Services\CMS\VersioningService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class VersionController extends Controller
{
    protected array $contentTypes = [
        'pages' => Page::class,
        'posts' => Post::class,
        'portfolios' => Portfolio::class,
        'faqs' => Faq::class,
        'testimonials' => Testimonial::class,
        'team-members' => TeamMember::class,
        'banners' => Banner::class,
        'menus' => Menu::class,
    ];

    public function __construct(
        private readonly VersioningService $versioningService
    ) {}

    /**
     * Get version history for content
     */
    public function index(Request $request, string $type, int $id)
    {
        if (! isset($this->contentTypes[$type])) {
            return response()->json([
                'message' => 'Tipo de conteúdo inválido.',
            ], Response::HTTP_NOT_FOUND);
        }

        $modelClass = $this->contentTypes[$type];
        $content = $modelClass::find($id);

        if (! $content) {
            return response()->json([
                'message' => 'Conteúdo não encontrado.',
            ], Response::HTTP_NOT_FOUND);
        }

        $versions = $this->versioningService->getHistory($content);

        return ContentVersionResource::collection($versions);
    }

    /**
     * Get specific version
     */
    public function show(string $type, int $id, int $versionNumber)
    {
        if (! isset($this->contentTypes[$type])) {
            return response()->json([
                'message' => 'Tipo de conteúdo inválido.',
            ], Response::HTTP_NOT_FOUND);
        }

        $modelClass = $this->contentTypes[$type];
        $content = $modelClass::find($id);

        if (! $content) {
            return response()->json([
                'message' => 'Conteúdo não encontrado.',
            ], Response::HTTP_NOT_FOUND);
        }

        $version = $this->versioningService->getVersion($content, $versionNumber);

        if (! $version) {
            return response()->json([
                'message' => 'Versão não encontrada.',
            ], Response::HTTP_NOT_FOUND);
        }

        return new ContentVersionResource($version);
    }

    /**
     * Rollback to specific version
     */
    public function rollback(Request $request, string $type, int $id, int $versionNumber)
    {
        if (! isset($this->contentTypes[$type])) {
            return response()->json([
                'message' => 'Tipo de conteúdo inválido.',
            ], Response::HTTP_NOT_FOUND);
        }

        $modelClass = $this->contentTypes[$type];
        $content = $modelClass::find($id);

        if (! $content) {
            return response()->json([
                'message' => 'Conteúdo não encontrado.',
            ], Response::HTTP_NOT_FOUND);
        }

        $success = $this->versioningService->rollback($content, $versionNumber, auth()->id());

        if (! $success) {
            return response()->json([
                'message' => 'Não foi possível reverter para esta versão.',
            ], Response::HTTP_BAD_REQUEST);
        }

        return response()->json([
            'message' => "Conteúdo revertido para versão {$versionNumber} com sucesso.",
            'current_version' => $this->versioningService->getLatestVersion($content)->version_number,
        ]);
    }

    /**
     * Compare two versions
     */
    public function compare(Request $request, string $type, int $id)
    {
        $request->validate([
            'version1' => 'required|integer|min:1',
            'version2' => 'required|integer|min:1',
        ]);

        if (! isset($this->contentTypes[$type])) {
            return response()->json([
                'message' => 'Tipo de conteúdo inválido.',
            ], Response::HTTP_NOT_FOUND);
        }

        $modelClass = $this->contentTypes[$type];
        $content = $modelClass::find($id);

        if (! $content) {
            return response()->json([
                'message' => 'Conteúdo não encontrado.',
            ], Response::HTTP_NOT_FOUND);
        }

        $differences = $this->versioningService->compareVersions(
            $content,
            $request->version1,
            $request->version2
        );

        if (empty($differences)) {
            return response()->json([
                'message' => 'Versões não encontradas ou são idênticas.',
                'differences' => [],
            ]);
        }

        return response()->json([
            'content_type' => $type,
            'content_id' => $id,
            'version1' => $request->version1,
            'version2' => $request->version2,
            'differences' => $differences,
            'fields_changed' => count($differences),
        ]);
    }

    /**
     * Create manual version
     */
    public function store(Request $request, string $type, int $id)
    {
        $request->validate([
            'change_summary' => 'required|string|max:500',
        ]);

        if (! isset($this->contentTypes[$type])) {
            return response()->json([
                'message' => 'Tipo de conteúdo inválido.',
            ], Response::HTTP_NOT_FOUND);
        }

        $modelClass = $this->contentTypes[$type];
        $content = $modelClass::find($id);

        if (! $content) {
            return response()->json([
                'message' => 'Conteúdo não encontrado.',
            ], Response::HTTP_NOT_FOUND);
        }

        $version = $this->versioningService->createVersion(
            $content,
            auth()->id(),
            $request->change_summary
        );

        return response()->json([
            'message' => 'Versão criada com sucesso.',
            'version' => new ContentVersionResource($version),
        ], Response::HTTP_CREATED);
    }
}
