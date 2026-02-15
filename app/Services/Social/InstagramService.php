<?php

namespace App\Services\Social;

use App\Contracts\MessageServiceInterface;
use App\Models\Social\InstagramAccount;
use App\Models\Social\InstagramMessage;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class InstagramService implements MessageServiceInterface
{
    protected string $graphApiUrl = 'https://graph.instagram.com';

    protected string $graphFacebookUrl = 'https://graph.facebook.com/v18.0';

    /**
     * Exchange authorization code for access token
     */
    public function connectAccount(string $code, int $companyId): InstagramAccount
    {
        // Exchange code for access token
        $response = Http::asForm()->post("{$this->graphApiUrl}/oauth/access_token", [
            'client_id' => config('services.instagram.client_id'),
            'client_secret' => config('services.instagram.client_secret'),
            'grant_type' => 'authorization_code',
            'redirect_uri' => config('services.instagram.redirect_uri'),
            'code' => $code,
        ]);

        if ($response->failed()) {
            Log::error('Instagram OAuth failed', ['response' => $response->json()]);

            throw new \Exception('Failed to obtain Instagram access token');
        }

        $data = $response->json();
        $accessToken = $data['access_token'];

        // Get user info
        $userInfo = $this->getUserInfo($accessToken);

        // Get long-lived token (60 days)
        $longLivedToken = $this->exchangeForLongLivedToken($accessToken);

        // Create or update account
        return InstagramAccount::updateOrCreate(
            [
                'instagram_user_id' => $userInfo['id'],
                'company_id' => $companyId,
            ],
            [
                'username' => $userInfo['username'],
                'access_token' => $longLivedToken['access_token'],
                'token_expires_at' => now()->addSeconds($longLivedToken['expires_in']),
                'account_type' => $userInfo['account_type'] ?? 'PERSONAL',
                'profile_picture_url' => $userInfo['profile_picture_url'] ?? null,
                'followers_count' => $userInfo['followers_count'] ?? 0,
                'is_active' => true,
                'metadata' => $userInfo,
            ]
        );
    }

    /**
     * Get user info from Instagram API
     */
    protected function getUserInfo(string $accessToken): array
    {
        $response = Http::get("{$this->graphApiUrl}/me", [
            'fields' => 'id,username,account_type,media_count',
            'access_token' => $accessToken,
        ]);

        if ($response->failed()) {
            throw new \Exception('Failed to fetch Instagram user info');
        }

        return $response->json();
    }

    /**
     * Exchange short-lived token for long-lived token (60 days)
     */
    protected function exchangeForLongLivedToken(string $shortLivedToken): array
    {
        $response = Http::get("{$this->graphApiUrl}/access_token", [
            'grant_type' => 'ig_exchange_token',
            'client_secret' => config('services.instagram.client_secret'),
            'access_token' => $shortLivedToken,
        ]);

        if ($response->failed()) {
            throw new \Exception('Failed to exchange for long-lived token');
        }

        return $response->json();
    }

    /**
     * Refresh access token
     */
    public function refreshAccessToken(InstagramAccount $account): InstagramAccount
    {
        $response = Http::get("{$this->graphApiUrl}/refresh_access_token", [
            'grant_type' => 'ig_refresh_token',
            'access_token' => $account->decrypted_token,
        ]);

        if ($response->failed()) {
            Log::error('Failed to refresh Instagram token', [
                'account_id' => $account->id,
                'response' => $response->json(),
            ]);

            throw new \Exception('Failed to refresh access token');
        }

        $data = $response->json();

        $account->update([
            'access_token' => $data['access_token'],
            'token_expires_at' => now()->addSeconds($data['expires_in']),
        ]);

        return $account->fresh();
    }

    /**
     * Fetch recent media posts
     */
    public function fetchRecentPosts(InstagramAccount $account, int $limit = 25): array
    {
        if ($account->isTokenExpired()) {
            $account = $this->refreshAccessToken($account);
        }

        $response = Http::get("{$this->graphApiUrl}/{$account->instagram_user_id}/media", [
            'fields' => 'id,caption,media_type,media_url,permalink,thumbnail_url,timestamp,username,like_count,comments_count,children{media_url,media_type}',
            'limit' => $limit,
            'access_token' => $account->decrypted_token,
        ]);

        if ($response->failed()) {
            Log::error('Failed to fetch Instagram posts', [
                'account_id' => $account->id,
                'response' => $response->json(),
            ]);

            return [];
        }

        return $response->json()['data'] ?? [];
    }

    /**
     * Fetch messages (conversations)
     * Note: Requires Instagram Messaging API access (Business/Creator accounts only)
     */
    public function fetchMessages(int $accountId, ?int $limit = 50): array
    {
        $account = InstagramAccount::findOrFail($accountId);

        if ($account->isTokenExpired()) {
            $account = $this->refreshAccessToken($account);
        }

        // Get page access token from Instagram account
        $pageAccessToken = $this->getPageAccessToken($account);

        // Fetch conversations
        $response = Http::get("{$this->graphFacebookUrl}/me/conversations", [
            'platform' => 'instagram',
            'access_token' => $pageAccessToken,
        ]);

        if ($response->failed()) {
            Log::error('Failed to fetch Instagram conversations', [
                'account_id' => $account->id,
                'response' => $response->json(),
            ]);

            return [];
        }

        $conversations = $response->json()['data'] ?? [];
        $messages = [];

        foreach ($conversations as $conversation) {
            $conversationMessages = $this->fetchConversationMessages(
                $conversation['id'],
                $pageAccessToken,
                $limit
            );

            foreach ($conversationMessages as $message) {
                $messages[] = $this->saveMessage($account, $conversation['id'], $message);
            }
        }

        return $messages;
    }

    /**
     * Fetch messages from a conversation
     */
    protected function fetchConversationMessages(string $conversationId, string $accessToken, int $limit): array
    {
        $response = Http::get("{$this->graphFacebookUrl}/{$conversationId}/messages", [
            'fields' => 'id,created_time,from,to,message',
            'limit' => $limit,
            'access_token' => $accessToken,
        ]);

        if ($response->failed()) {
            return [];
        }

        return $response->json()['data'] ?? [];
    }

    /**
     * Get Page Access Token (required for messaging)
     */
    protected function getPageAccessToken(InstagramAccount $account): string
    {
        // This assumes the Instagram account is connected to a Facebook Page
        // In production, you would store the page_id and page_access_token separately
        return $account->decrypted_token;
    }

    /**
     * Save message to database
     */
    protected function saveMessage(InstagramAccount $account, string $conversationId, array $messageData): InstagramMessage
    {
        $direction = isset($messageData['from']['id']) && $messageData['from']['id'] !== $account->instagram_user_id
            ? 'inbound'
            : 'outbound';

        return InstagramMessage::updateOrCreate(
            [
                'message_id' => $messageData['id'],
            ],
            [
                'instagram_account_id' => $account->id,
                'conversation_id' => $conversationId,
                'sender_id' => $messageData['from']['id'] ?? null,
                'sender_username' => $messageData['from']['username'] ?? null,
                'direction' => $direction,
                'type' => 'text',
                'content' => $messageData['message'] ?? null,
                'status' => 'delivered',
                'sent_at' => isset($messageData['created_time']) ? \Carbon\Carbon::parse($messageData['created_time']) : now(),
                'metadata' => $messageData,
            ]
        );
    }

    /**
     * Send message (Business accounts only)
     */
    public function sendMessage(int $accountId, string $recipientId, string $content): array
    {
        $account = InstagramAccount::findOrFail($accountId);

        if ($account->isTokenExpired()) {
            $account = $this->refreshAccessToken($account);
        }

        $pageAccessToken = $this->getPageAccessToken($account);

        $response = Http::post("{$this->graphFacebookUrl}/me/messages", [
            'recipient' => ['id' => $recipientId],
            'message' => ['text' => $content],
            'access_token' => $pageAccessToken,
        ]);

        if ($response->failed()) {
            Log::error('Failed to send Instagram message', [
                'account_id' => $account->id,
                'response' => $response->json(),
            ]);

            throw new \Exception('Failed to send message');
        }

        return $response->json();
    }

    /**
     * Check if account is connected and token is valid
     */
    public function isConnected(int $accountId): bool
    {
        $account = InstagramAccount::find($accountId);

        if (! $account || ! $account->is_active) {
            return false;
        }

        return ! $account->isTokenExpired();
    }

    /**
     * Disconnect account
     */
    public function disconnect(int $accountId): bool
    {
        $account = InstagramAccount::find($accountId);

        if (! $account) {
            return false;
        }

        $account->update(['is_active' => false]);

        return true;
    }

    /**
     * Link message to lead by matching sender info
     */
    public function linkMessageToLead(InstagramMessage $message): ?int
    {
        // Try to find lead by Instagram username or sender_id
        $lead = \App\Models\Lead::where('instagram_handle', $message->sender_username)
            ->orWhere('phone', 'like', "%{$message->sender_username}%")
            ->first();

        if ($lead) {
            $message->update(['lead_id' => $lead->id]);

            return $lead->id;
        }

        return null;
    }
}
