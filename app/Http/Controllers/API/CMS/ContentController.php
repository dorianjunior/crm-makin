<?php

namespace App\Http\Controllers\API\Public;

use App\Http\Controllers\Controller;
use App\Http\Resources\CMS\PageResource;
use App\Http\Resources\CMS\PostResource;
use App\Models\CMS\Page;
use App\Models\CMS\Post;
use App\Models\CMS\Site;
use Illuminate\Http\Request;

/**
 * API Pública para sites dos clientes
 *
 * Endpoint: https://sua-plataforma.com/api/public/...
 *
 * Usado pelos sites dos clientes para:
 * - Buscar páginas
 * - Buscar posts
 * - Listar portfolio
 *
 * Autenticação: API Key do site
 */
class ContentController extends Controller
{
    /**
     * Validar site pela API key
     */
    private function validateSite(Request $request): ?Site
    {
        $apiKey = $request->header('X-Site-Key') ?? $request->query('site_key');

        if (! $apiKey) {
            abort(401, 'API Key não fornecida');
        }

        $site = Site::where('api_key', $apiKey)
            ->where('status', 'active')
            ->first();

        if (! $site) {
            abort(401, 'Site não encontrado ou inativo');
        }

        return $site;
    }

    /**
     * GET /api/public/pages
     *
     * Listar páginas públicas do site
     */
    public function pages(Request $request)
    {
        $site = $this->validateSite($request);

        $pages = Page::where('site_id', $site->id)
            ->where('status', 'published')
            ->where('company_id', $site->company_id)
            ->orderBy('order')
            ->get();

        return PageResource::collection($pages);
    }

    /**
     * GET /api/public/pages/{slug}
     *
     * Buscar página específica
     */
    public function page(Request $request, string $slug)
    {
        $site = $this->validateSite($request);

        $page = Page::where('site_id', $site->id)
            ->where('slug', $slug)
            ->where('status', 'published')
            ->where('company_id', $site->company_id)
            ->firstOrFail();

        return new PageResource($page);
    }

    /**
     * GET /api/public/posts
     *
     * Listar posts do blog
     */
    public function posts(Request $request)
    {
        $site = $this->validateSite($request);

        $posts = Post::where('site_id', $site->id)
            ->where('status', 'published')
            ->where('company_id', $site->company_id)
            ->orderBy('published_at', 'desc')
            ->paginate(10);

        return PostResource::collection($posts);
    }

    /**
     * POST /api/public/leads
     *
     * Criar lead a partir do formulário do site
     */
    public function createLead(Request $request)
    {
        $site = $this->validateSite($request);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:20',
            'company' => 'nullable|string|max:255',
            'message' => 'nullable|string',
        ]);

        $lead = \App\Models\CRM\Lead::create([
            'company_id' => $site->company_id,
            'source_id' => $site->lead_source_id, // Fonte: "Site"
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'company' => $validated['company'] ?? null,
            'notes' => $validated['message'] ?? null,
            'status' => 'new',
        ]);

        // Disparar evento para notificação em tempo real (opcional)
        // event(new \App\Events\LeadCreated($lead));

        return response()->json([
            'success' => true,
            'message' => 'Lead criado com sucesso!',
            'data' => [
                'id' => $lead->id,
                'name' => $lead->name,
            ],
        ], 201);
    }
}
