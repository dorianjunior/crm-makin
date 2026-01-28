<?php

namespace App\Contracts;

interface MessageServiceInterface
{
    /**
     * Fetch messages from the service
     */
    public function fetchMessages(int $accountId, ?int $limit = 50): array;

    /**
     * Send a message
     */
    public function sendMessage(int $accountId, string $recipientId, string $content): array;

    /**
     * Check connection status
     */
    public function isConnected(int $accountId): bool;

    /**
     * Disconnect account
     */
    public function disconnect(int $accountId): bool;
}
