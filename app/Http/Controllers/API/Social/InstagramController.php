<?php

namespace App\Http\Controllers\API\Social;

use App\Http\Controllers\Controller;
use App\Models\Social\InstagramAccount;
use App\Services\Social\InstagramService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class InstagramController extends Controller
{
    public function __construct(
        protected InstagramService $instagramService
    ) {}

    /**
     * Get OAuth authorization URL
     */
    public function getAuthUrl(): JsonResponse
    {
        $clientId = config('services.instagram.client_id');
        $redirectUri = urlencode(config('services.instagram.redirect_uri'));
        $scope = 'user_profile,user_media';

        $authUrl = 'https://api.instagram.com/oauth/authorize'
            ."?client_id={$clientId}"
            ."&redirect_uri={$redirectUri}"
            ."&scope={$scope}"
            .'&response_type=code';

        return response()->json([
            'auth_url' => $authUrl,
        ]);
    }

    /**
     * Handle OAuth callback and connect account
     */
    public function connect(Request $request): JsonResponse
    {
        $request->validate([
            'code' => 'required|string',
        ]);

        try {
            $account = $this->instagramService->connectAccount(
                $request->code,
                $request->user()->company_id
            );

            return response()->json([
                'message' => 'Instagram account connected successfully',
                'account' => [
                    'id' => $account->id,
                    'username' => $account->username,
                    'account_type' => $account->account_type,
                    'is_active' => $account->is_active,
                ],
            ], 201);
        } catch (\Exception $e) {
            Log::error('Instagram connection failed', [
                'error' => $e->getMessage(),
                'company_id' => $request->user()->company_id,
            ]);

            return response()->json([
                'message' => 'Failed to connect Instagram account',
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * List connected Instagram accounts
     */
    public function index(Request $request): JsonResponse
    {
        $companyId = $request->user()->company_id;

        $accounts = InstagramAccount::forCompany($companyId)
            ->active()
            ->get()
            ->map(function ($account) {
                return [
                    'id' => $account->id,
                    'username' => $account->username,
                    'account_type' => $account->account_type,
                    'profile_picture_url' => $account->profile_picture_url,
                    'followers_count' => $account->followers_count,
                    'is_active' => $account->is_active,
                    'is_connected' => $this->instagramService->isConnected($account->id),
                    'token_expires_at' => $account->token_expires_at?->toIso8601String(),
                    'created_at' => $account->created_at->toIso8601String(),
                ];
            });

        return response()->json([
            'accounts' => $accounts,
        ]);
    }

    /**
     * Get messages from Instagram account
     */
    public function getMessages(Request $request, int $accountId): JsonResponse
    {
        $account = InstagramAccount::forCompany($request->user()->company_id)
            ->findOrFail($accountId);

        try {
            $messages = $this->instagramService->fetchMessages($accountId, $request->limit ?? 50);

            return response()->json([
                'account_id' => $accountId,
                'messages' => $messages,
                'count' => count($messages),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to fetch Instagram messages', [
                'account_id' => $accountId,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Failed to fetch messages',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get posts from Instagram account
     */
    public function getPosts(Request $request, int $accountId): JsonResponse
    {
        $account = InstagramAccount::forCompany($request->user()->company_id)
            ->findOrFail($accountId);

        try {
            $posts = $this->instagramService->fetchRecentPosts($account, $request->limit ?? 25);

            return response()->json([
                'account_id' => $accountId,
                'posts' => $posts,
                'count' => count($posts),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to fetch Instagram posts', [
                'account_id' => $accountId,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Failed to fetch posts',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Disconnect Instagram account
     */
    public function disconnect(Request $request, int $accountId): JsonResponse
    {
        $account = InstagramAccount::forCompany($request->user()->company_id)
            ->findOrFail($accountId);

        $this->instagramService->disconnect($accountId);

        return response()->json([
            'message' => 'Instagram account disconnected successfully',
        ]);
    }

    /**
     * Refresh access token
     */
    public function refreshToken(Request $request, int $accountId): JsonResponse
    {
        $account = InstagramAccount::forCompany($request->user()->company_id)
            ->findOrFail($accountId);

        try {
            $updatedAccount = $this->instagramService->refreshAccessToken($account);

            return response()->json([
                'message' => 'Token refreshed successfully',
                'token_expires_at' => $updatedAccount->token_expires_at->toIso8601String(),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to refresh Instagram token', [
                'account_id' => $accountId,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Failed to refresh token',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
