<?php

namespace App\Http\Controllers\Web\Social;

use App\Http\Controllers\Controller;
use App\Models\Social\InstagramAccount;
use App\Services\Social\InstagramService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class InstagramController extends Controller
{
    public function __construct(
        protected InstagramService $instagramService
    ) {}

    /**
     * Display Instagram accounts and posts
     */
    public function index(Request $request): Response
    {
        $companyId = auth()->user()->company_id;
        
        // Get all accounts for company
        $accounts = InstagramAccount::forCompany($companyId)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn($account) => [
                'id' => $account->id,
                'username' => $account->username,
                'account_type' => $account->account_type,
                'profile_picture_url' => $account->profile_picture_url,
                'followers_count' => $account->followers_count,
                'is_active' => $account->is_active,
                'token_expires_at' => $account->token_expires_at,
            ]);

        // Get posts if account selected
        $posts = [];
        $selectedAccountId = $request->get('account');
        
        if ($selectedAccountId) {
            $account = InstagramAccount::forCompany($companyId)
                ->findOrFail($selectedAccountId);
                
            try {
                $posts = $this->instagramService->fetchRecentPosts($account, 12);
            } catch (\Exception $e) {
                // Log error but don't break the page
                \Log::error('Failed to fetch Instagram posts', [
                    'account_id' => $selectedAccountId,
                    'error' => $e->getMessage()
                ]);
            }
        }

        return Inertia::render('Social/Instagram/Index', [
            'accounts' => $accounts,
            'selectedAccountId' => $selectedAccountId ? (int)$selectedAccountId : null,
            'posts' => $posts,
        ]);
    }

    /**
     * Display messages for an account
     */
    public function messages(Request $request, int $accountId): Response
    {
        $companyId = auth()->user()->company_id;
        
        $account = InstagramAccount::forCompany($companyId)
            ->findOrFail($accountId);

        // Fetch messages via service
        try {
            $messages = $this->instagramService->fetchMessages($accountId, 50);
        } catch (\Exception $e) {
            $messages = [];
            \Log::error('Failed to fetch Instagram messages', [
                'account_id' => $accountId,
                'error' => $e->getMessage()
            ]);
        }

        return Inertia::render('Social/Instagram/Messages', [
            'account' => [
                'id' => $account->id,
                'username' => $account->username,
                'profile_picture_url' => $account->profile_picture_url,
            ],
            'messages' => $messages,
        ]);
    }
}

