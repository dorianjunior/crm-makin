<?php

namespace App\Http\Controllers;

use App\Models\AIConversation;
use App\Models\AIPromptTemplate;
use App\Services\GeminiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class AIConversationController extends Controller
{
    /**
     * Get all conversations for a company.
     */
    public function index(Request $request): JsonResponse
    {
        $companyId = $request->user()->company_id;

        $query = AIConversation::where('company_id', $companyId)
            ->with(['lead', 'promptTemplate']);

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by channel
        if ($request->has('channel')) {
            $query->channel($request->channel);
        }

        // Filter by lead
        if ($request->has('lead_id')) {
            $query->where('lead_id', $request->lead_id);
        }

        $conversations = $query->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 15));

        return response()->json($conversations);
    }

    /**
     * Get a specific conversation with messages.
     */
    public function show(int $id): JsonResponse
    {
        $conversation = AIConversation::with(['messages', 'lead', 'promptTemplate', 'feedback'])
            ->findOrFail($id);

        // Verify permission
        $this->authorize('view', $conversation);

        return response()->json([
            'data' => $conversation,
        ]);
    }

    /**
     * Create a new conversation.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'ai_prompt_template_id' => 'required|exists:ai_prompt_templates,id',
            'lead_id' => 'nullable|exists:leads,id',
            'conversationable_type' => 'nullable|string',
            'conversationable_id' => 'nullable|integer',
            'channel' => 'required|string|in:whatsapp,instagram,email,chat',
            'system_prompt' => 'nullable|string',
            'context_data' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $data = $validator->validated();
        $data['company_id'] = $request->user()->company_id;
        $data['conversation_id'] = Str::uuid();

        // Get prompt template
        $template = AIPromptTemplate::findOrFail($data['ai_prompt_template_id']);

        // Use template system prompt if not provided
        if (empty($data['system_prompt'])) {
            $data['system_prompt'] = $template->system_prompt;
        }

        // Get AI setting and set model/provider
        try {
            $service = GeminiService::forCompany($data['company_id']);
            $data['provider'] = $service->setting->provider ?? 'gemini';
            $data['model'] = $service->setting->model ?? 'gemini-pro';
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'No active AI setting found for company',
            ], 400);
        }

        $conversation = AIConversation::create($data);

        // Increment template usage
        $template->incrementUsage();

        return response()->json([
            'message' => 'Conversation created successfully',
            'data' => $conversation,
        ], 201);
    }

    /**
     * Send a message in a conversation.
     */
    public function sendMessage(Request $request, int $id): JsonResponse
    {
        $conversation = AIConversation::findOrFail($id);

        // Verify permission
        $this->authorize('update', $conversation);

        $validator = Validator::make($request->all(), [
            'message' => 'required|string',
            'variables' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            // Get AI service
            $service = GeminiService::forCompany($conversation->company_id);

            // Save user message
            $userMessage = $conversation->addMessage([
                'role' => 'user',
                'content' => $request->message,
            ]);

            // Get conversation history
            $history = $conversation->messages()
                ->whereIn('role', ['user', 'assistant'])
                ->orderBy('created_at', 'asc')
                ->take(20) // Limit history to last 20 messages
                ->get()
                ->map(fn($msg) => $msg->toContextArray())
                ->toArray();

            // Generate AI response
            $response = $service->chat($request->message, $history);

            // Save assistant message
            $assistantMessage = $conversation->addMessage([
                'role' => 'assistant',
                'content' => $response['content'],
                'input_tokens' => $response['tokens']['input'],
                'output_tokens' => $response['tokens']['output'],
                'total_tokens' => $response['tokens']['total'],
                'cost' => $response['cost'],
                'processing_time_ms' => $response['processing_time_ms'],
                'model_version' => $response['model_version'],
            ]);

            return response()->json([
                'message' => 'Message sent successfully',
                'data' => [
                    'user_message' => $userMessage,
                    'assistant_message' => $assistantMessage,
                    'conversation' => $conversation->fresh(),
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to generate AI response',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Complete a conversation.
     */
    public function complete(int $id): JsonResponse
    {
        $conversation = AIConversation::findOrFail($id);

        // Verify permission
        $this->authorize('update', $conversation);

        $conversation->markAsCompleted();

        return response()->json([
            'message' => 'Conversation completed successfully',
            'data' => $conversation->fresh(),
        ]);
    }

    /**
     * Get conversation statistics.
     */
    public function statistics(Request $request): JsonResponse
    {
        $companyId = $request->user()->company_id;

        $stats = [
            'total_conversations' => AIConversation::where('company_id', $companyId)->count(),
            'active_conversations' => AIConversation::where('company_id', $companyId)->active()->count(),
            'completed_conversations' => AIConversation::where('company_id', $companyId)->completed()->count(),
            'total_messages' => AIConversation::where('company_id', $companyId)->sum('message_count'),
            'total_tokens' => AIConversation::where('company_id', $companyId)
                ->selectRaw('SUM(total_input_tokens + total_output_tokens) as total')
                ->value('total'),
            'total_cost' => AIConversation::where('company_id', $companyId)->sum('total_cost'),
            'avg_satisfaction' => AIConversation::where('company_id', $companyId)
                ->whereNotNull('user_satisfaction_rating')
                ->avg('user_satisfaction_rating'),
            'lead_conversion_rate' => $this->calculateConversionRate($companyId),
        ];

        return response()->json([
            'data' => $stats,
        ]);
    }

    /**
     * Calculate lead conversion rate.
     */
    protected function calculateConversionRate(int $companyId): float
    {
        $total = AIConversation::where('company_id', $companyId)
            ->whereNotNull('lead_id')
            ->count();

        if ($total === 0) {
            return 0;
        }

        $converted = AIConversation::where('company_id', $companyId)
            ->whereNotNull('lead_id')
            ->where('lead_converted', true)
            ->count();

        return round(($converted / $total) * 100, 2);
    }
}
