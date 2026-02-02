<?php

namespace App\Http\Controllers\API\Social;

use App\Http\Controllers\Controller;
use App\Jobs\Social\ProcessIncomingWhatsAppMessageJob;
use App\Models\Social\WhatsAppAccount;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WhatsAppWebhookController extends Controller
{
    /**
     * Verify webhook (WhatsApp challenge verification)
     */
    public function verify(Request $request): string|JsonResponse
    {
        $mode = $request->query('hub_mode');
        $token = $request->query('hub_verify_token');
        $challenge = $request->query('hub_challenge');

        Log::info('WhatsApp webhook verification attempt', [
            'mode' => $mode,
            'token_provided' => $token ? 'yes' : 'no',
        ]);

        // Verify token matches any of our accounts
        $account = WhatsAppAccount::where('verify_token', $token)->first();

        if ($mode === 'subscribe' && $account) {
            Log::info('WhatsApp webhook verified successfully', [
                'account_id' => $account->id,
            ]);
            return $challenge;
        }

        Log::warning('WhatsApp webhook verification failed', [
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

        Log::info('WhatsApp webhook received', ['payload' => $body]);

        // Verify signature
        if (! $this->verifySignature($request)) {
            Log::warning('Invalid WhatsApp webhook signature');
            return response()->json(['error' => 'Invalid signature'], 403);
        }

        // Process webhook payload
        if (isset($body['entry'])) {
            foreach ($body['entry'] as $entry) {
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

        // Get the phone number ID from the payload to find the correct account
        $body = $request->getContent();
        $data = json_decode($body, true);

        if (! isset($data['entry'][0]['changes'][0]['value']['metadata']['phone_number_id'])) {
            return true; // Can't verify without phone_number_id, but allow for status updates
        }

        $phoneNumberId = $data['entry'][0]['changes'][0]['value']['metadata']['phone_number_id'];

        $account = WhatsAppAccount::where('phone_number_id', $phoneNumberId)->first();

        if (! $account) {
            Log::warning('WhatsApp account not found for webhook', [
                'phone_number_id' => $phoneNumberId,
            ]);
            return false;
        }

        // Verify signature using app secret (from config or account metadata)
        $appSecret = config('services.whatsapp.app_secret') ?? $account->metadata['app_secret'] ?? null;

        if (! $appSecret) {
            Log::warning('WhatsApp app secret not configured');
            return true; // Allow if secret not configured (development)
        }

        $expected = 'sha256=' . hash_hmac('sha256', $body, $appSecret);

        return hash_equals($expected, $signature);
    }

    /**
     * Process webhook entry
     */
    protected function processEntry(array $entry): void
    {
        foreach ($entry['changes'] ?? [] as $change) {
            if ($change['field'] === 'messages') {
                $this->processMessages($change['value']);
            } elseif ($change['field'] === 'message_template_status_update') {
                $this->processTemplateStatusUpdate($change['value']);
            }
        }
    }

    /**
     * Process incoming messages
     */
    protected function processMessages(array $value): void
    {
        $phoneNumberId = $value['metadata']['phone_number_id'] ?? null;

        if (! $phoneNumberId) {
            Log::warning('WhatsApp webhook missing phone_number_id');
            return;
        }

        // Find account
        $account = WhatsAppAccount::where('phone_number_id', $phoneNumberId)->first();

        if (! $account) {
            Log::warning('WhatsApp account not found', [
                'phone_number_id' => $phoneNumberId,
            ]);
            return;
        }

        // Process each message
        foreach ($value['messages'] ?? [] as $message) {
            ProcessIncomingWhatsAppMessageJob::dispatch($account->id, $message);
        }

        // Process status updates
        foreach ($value['statuses'] ?? [] as $status) {
            $this->processStatusUpdate($status);
        }
    }

    /**
     * Process message status update
     */
    protected function processStatusUpdate(array $status): void
    {
        $messageId = $status['id'];
        $statusValue = $status['status']; // sent, delivered, read, failed

        Log::info('WhatsApp status update', [
            'message_id' => $messageId,
            'status' => $statusValue,
        ]);

        $message = \App\Models\Social\WhatsAppMessage::where('message_id', $messageId)->first();

        if ($message) {
            $errorMessage = null;

            if ($statusValue === 'failed') {
                $errorMessage = $status['errors'][0]['title'] ?? 'Unknown error';
            }

            $message->updateStatus($statusValue, $errorMessage);
        }
    }

    /**
     * Process template status update
     */
    protected function processTemplateStatusUpdate(array $value): void
    {
        Log::info('WhatsApp template status update', [
            'template' => $value,
        ]);

        // TODO: Implement template status tracking if using message templates
    }
}
