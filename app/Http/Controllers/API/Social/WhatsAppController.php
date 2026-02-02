<?php

namespace App\Http\Controllers\API\Social;

use App\Http\Controllers\Controller;
use App\Models\Social\WhatsAppAccount;
use App\Models\Social\WhatsAppConversation;
use App\Services\Social\WhatsAppService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WhatsAppController extends Controller
{
    public function __construct(
        protected WhatsAppService $whatsappService
    ) {}

    /**
     * List WhatsApp accounts
     */
    public function index(Request $request): JsonResponse
    {
        $companyId = $request->user()->company_id;

        $accounts = WhatsAppAccount::forCompany($companyId)
            ->active()
            ->get()
            ->map(function ($account) {
                return [
                    'id' => $account->id,
                    'phone_number' => $account->phone_number,
                    'display_name' => $account->display_name,
                    'account_type' => $account->account_type,
                    'quality_rating' => $account->quality_rating,
                    'is_active' => $account->is_active,
                    'is_connected' => $this->whatsappService->isConnected($account->id),
                    'created_at' => $account->created_at->toIso8601String(),
                ];
            });

        return response()->json([
            'accounts' => $accounts,
        ]);
    }

    /**
     * Register new WhatsApp account
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'phone_number_id' => 'required|string',
            'business_account_id' => 'required|string',
            'phone_number' => 'required|string|max:20',
            'display_name' => 'nullable|string|max:255',
            'access_token' => 'required|string',
            'verify_token' => 'required|string',
        ]);

        $account = WhatsAppAccount::create([
            'company_id' => $request->user()->company_id,
            'phone_number_id' => $request->phone_number_id,
            'business_account_id' => $request->business_account_id,
            'phone_number' => $request->phone_number,
            'display_name' => $request->display_name,
            'access_token' => $request->access_token,
            'verify_token' => $request->verify_token,
            'is_active' => true,
        ]);

        return response()->json([
            'message' => 'WhatsApp account registered successfully',
            'account' => [
                'id' => $account->id,
                'phone_number' => $account->phone_number,
                'display_name' => $account->display_name,
            ],
        ], 201);
    }

    /**
     * Get conversations
     */
    public function conversations(Request $request, int $accountId): JsonResponse
    {
        $account = WhatsAppAccount::forCompany($request->user()->company_id)
            ->findOrFail($accountId);

        try {
            $conversations = $this->whatsappService->getConversations(
                $accountId,
                $request->limit ?? 50
            );

            return response()->json([
                'account_id' => $accountId,
                'conversations' => $conversations,
                'count' => count($conversations),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to fetch WhatsApp conversations', [
                'account_id' => $accountId,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Failed to fetch conversations',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get messages from a conversation
     */
    public function messages(Request $request, int $conversationId): JsonResponse
    {
        $conversation = WhatsAppConversation::whereHas('whatsappAccount', function ($query) use ($request) {
            $query->where('company_id', $request->user()->company_id);
        })->findOrFail($conversationId);

        $messages = $conversation->messages()
            ->orderBy('created_at', 'asc')
            ->limit($request->limit ?? 100)
            ->get()
            ->map(function ($message) {
                return [
                    'id' => $message->id,
                    'message_id' => $message->message_id,
                    'direction' => $message->direction,
                    'type' => $message->type,
                    'content' => $message->content,
                    'media_url' => $message->media_url,
                    'status' => $message->status,
                    'from_phone' => $message->from_phone,
                    'to_phone' => $message->to_phone,
                    'sent_at' => $message->sent_at?->toIso8601String(),
                    'delivered_at' => $message->delivered_at?->toIso8601String(),
                    'read_at' => $message->read_at?->toIso8601String(),
                    'created_at' => $message->created_at->toIso8601String(),
                ];
            });

        return response()->json([
            'conversation_id' => $conversationId,
            'messages' => $messages,
            'count' => $messages->count(),
        ]);
    }

    /**
     * Send text message
     */
    public function sendMessage(Request $request, int $accountId): JsonResponse
    {
        $account = WhatsAppAccount::forCompany($request->user()->company_id)
            ->findOrFail($accountId);

        $request->validate([
            'to' => 'required|string',
            'message' => 'required|string|max:4096',
        ]);

        try {
            $message = $this->whatsappService->sendMessage(
                $accountId,
                $request->to,
                $request->message
            );

            return response()->json([
                'message' => 'Message sent successfully',
                'data' => $message,
            ], 201);
        } catch (\Exception $e) {
            Log::error('Failed to send WhatsApp message', [
                'account_id' => $accountId,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Failed to send message',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Send media message
     */
    public function sendMedia(Request $request, int $accountId): JsonResponse
    {
        $account = WhatsAppAccount::forCompany($request->user()->company_id)
            ->findOrFail($accountId);

        $request->validate([
            'to' => 'required|string',
            'media_url' => 'required|url',
            'media_type' => 'required|in:image,video,audio,document',
            'caption' => 'nullable|string|max:1024',
        ]);

        try {
            $message = $this->whatsappService->sendMediaMessage(
                $accountId,
                $request->to,
                $request->media_url,
                $request->media_type,
                $request->caption
            );

            return response()->json([
                'message' => 'Media sent successfully',
                'data' => $message,
            ], 201);
        } catch (\Exception $e) {
            Log::error('Failed to send WhatsApp media', [
                'account_id' => $accountId,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Failed to send media',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Mark conversation as read
     */
    public function markAsRead(Request $request, int $conversationId): JsonResponse
    {
        $conversation = WhatsAppConversation::whereHas('whatsappAccount', function ($query) use ($request) {
            $query->where('company_id', $request->user()->company_id);
        })->findOrFail($conversationId);

        $conversation->markAsRead();

        return response()->json([
            'message' => 'Conversation marked as read',
        ]);
    }

    /**
     * Disconnect WhatsApp account
     */
    public function disconnect(Request $request, int $accountId): JsonResponse
    {
        $account = WhatsAppAccount::forCompany($request->user()->company_id)
            ->findOrFail($accountId);

        $this->whatsappService->disconnect($accountId);

        return response()->json([
            'message' => 'WhatsApp account disconnected successfully',
        ]);
    }
}
