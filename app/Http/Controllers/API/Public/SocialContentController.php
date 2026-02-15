<?php

namespace App\Http\Controllers\API\Public;

use App\Http\Controllers\Controller;
use App\Models\CMS\Site;
use App\Models\Social\InstagramAccount;
use App\Services\Social\InstagramService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * API Pública para conteúdo de redes sociais
 *
 * Endpoint: https://sua-plataforma.com/api/public/instagram/posts
 *
 * Usado pelos sites dos clientes para:
 * - Buscar posts do Instagram
 * - Criar carrosséis de posts
 *
 * Autenticação: API Key do site
 */
class SocialContentController extends Controller
{
    public function __construct(
        protected InstagramService $instagramService
    ) {}

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
     * GET /api/public/instagram/posts
     *
     * Buscar posts recentes do Instagram para exibir em site externo
     *
     * Parâmetros:
     * - site_key: API key do site (obrigatório)
     * - username: username específico do Instagram (opcional)
     * - limit: quantidade de posts (padrão: 12, máximo: 50)
     *
     * Exemplo:
     * fetch('https://sua-plataforma.com/api/public/instagram/posts?site_key=abc123&limit=12')
     */
    public function instagramPosts(Request $request): JsonResponse
    {
        $site = $this->validateSite($request);

        $limit = min((int) $request->get('limit', 12), 50);
        $username = $request->get('username');

        // Get Instagram accounts for this company
        $query = InstagramAccount::where('company_id', $site->company_id)
            ->where('is_active', true);

        if ($username) {
            $query->where('username', $username);
        }

        $accounts = $query->get();

        if ($accounts->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Nenhuma conta do Instagram conectada',
                'posts' => [],
            ], 404);
        }

        // Fetch posts from all accounts (or specific one)
        $allPosts = [];

        foreach ($accounts as $account) {
            try {
                $posts = $this->instagramService->fetchRecentPosts($account, $limit);

                // Add account info to each post
                foreach ($posts as $post) {
                    $allPosts[] = [
                        'id' => $post['id'],
                        'username' => $post['username'] ?? $account->username,
                        'caption' => $post['caption'] ?? null,
                        'media_type' => $post['media_type'],
                        'media_url' => $post['media_url'] ?? null,
                        'thumbnail_url' => $post['thumbnail_url'] ?? null,
                        'permalink' => $post['permalink'] ?? null,
                        'timestamp' => $post['timestamp'] ?? null,
                        'like_count' => $post['like_count'] ?? 0,
                        'comments_count' => $post['comments_count'] ?? 0,
                        // For carousel albums
                        'children' => $post['children']['data'] ?? null,
                    ];
                }
            } catch (\Exception $e) {
                \Log::error('Failed to fetch Instagram posts for public API', [
                    'account_id' => $account->id,
                    'site_id' => $site->id,
                    'error' => $e->getMessage(),
                ]);
                // Continue with other accounts
            }
        }

        // Sort by timestamp (newest first)
        usort($allPosts, function ($a, $b) {
            return strtotime($b['timestamp'] ?? 0) - strtotime($a['timestamp'] ?? 0);
        });

        // Limit total posts
        $allPosts = array_slice($allPosts, 0, $limit);

        return response()->json([
            'success' => true,
            'posts' => $allPosts,
            'count' => count($allPosts),
            'accounts' => $accounts->pluck('username')->toArray(),
        ]);
    }

    /**
     * GET /api/public/instagram/accounts
     *
     * Listar contas do Instagram conectadas
     */
    public function instagramAccounts(Request $request): JsonResponse
    {
        $site = $this->validateSite($request);

        $accounts = InstagramAccount::where('company_id', $site->company_id)
            ->where('is_active', true)
            ->get()
            ->map(fn($account) => [
                'id' => $account->id,
                'username' => $account->username,
                'account_type' => $account->account_type,
                'profile_picture_url' => $account->profile_picture_url,
                'followers_count' => $account->followers_count,
            ]);

        return response()->json([
            'success' => true,
            'accounts' => $accounts,
            'count' => count($accounts),
        ]);
    }
}
