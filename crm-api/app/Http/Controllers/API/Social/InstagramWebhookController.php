<?php

namespace App\Http\Controllers\API\Social;

use App\Http\Controllers\Controller;
use App\Jobs\Social\ProcessIncomingInstagramMessageJob;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class InstagramWebhookController extends Controller
{
    /**
     * Verify webhook (Meta's challenge verification)
     */
    public function verify(Request $request): string|JsonResponse
    {
        $mode = $request->query('hub_mode');
        $token = $request->query('hub_verify_token');
        $challenge = $request->query('hub_challenge');

        $verifyToken = config('services.instagram.webhook_verify_token');

        if ($mode === 'subscribe' && $token === $verifyToken) {
            Log::info('Instagram webhook verified successfully');
            return $challenge;
        }

        Log::warning('Instagram webhook verification failed', [
            'mode' => $mode,
            'token' => $token,
        ]);

        return response()->json(['error' => 'Verification failed'], 403);
    }

    /**
     * Handle incoming webhook
     */
    public function handle(Request $request): JsonResponse
    {
        $body = $request->all();

        Log::info('Instagram webhook received', ['payload' => $body]);

        // Verify signature
        if (! $this->verifySignature($request)) {
            Log::warning('Invalid Instagram webhook signature');
            return response()->json(['error' => 'Invalid signature'], 403);
        }

        // Process webhook payload
        if (isset($body['object']) && $body['object'] === 'instagram') {
            foreach ($body['entry'] ?? [] as $entry) {
                $this->processEntry($entry);
            }
        }

        return response()->json(['status' => 'received'], 200);
    }

    /**
     * Verify webhook signature
     */
    protected function verifySignature(Request $request): bool
    {
        $signature = $request->header('X-Hub-Signature-256');

        if (! $signature) {
            return false;
        }

        $expected = 'sha256=' . hash_hmac(
            'sha256',
            $request->getContent(),
            config('services.instagram.client_secret')
        );

        return hash_equals($expected, $signature);
    }

    /**
     * Process webhook entry
     */
    protected function processEntry(array $entry): void
    {
        foreach ($entry['messaging'] ?? [] as $messaging) {
            // Dispatch job to process message asynchronously
            ProcessIncomingInstagramMessageJob::dispatch($messaging);
        }

        // Handle other types of events (comments, mentions, etc.)
        foreach ($entry['changes'] ?? [] as $change) {
            $this->processChange($change);
        }
    }

    /**
     * Process webhook change event
     */
    protected function processChange(array $change): void
    {
        $field = $change['field'] ?? null;
        $value = $change['value'] ?? [];

        Log::info('Instagram webhook change', [
            'field' => $field,
            'value' => $value,
        ]);

        // Handle different types of changes
        match ($field) {
            'messages' => $this->handleMessage($value),
            'comments' => $this->handleComment($value),
            'mentions' => $this->handleMention($value),
            default => Log::info('Unhandled Instagram webhook field', ['field' => $field]),
        };
    }

    /**
     * Handle incoming message
     */
    protected function handleMessage(array $value): void
    {
        ProcessIncomingInstagramMessageJob::dispatch($value);
    }

    /**
     * Handle comment
     */
    protected function handleComment(array $value): void
    {
        // Log comment for future implementation
        Log::info('Instagram comment received', ['comment' => $value]);
    }

    /**
     * Handle mention
     */
    protected function handleMention(array $value): void
    {
        // Log mention for future implementation
        Log::info('Instagram mention received', ['mention' => $value]);
    }
}
