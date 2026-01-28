<?php

namespace Database\Factories\Social;

use App\Models\Social\WhatsAppConversation;
use App\Models\Social\WhatsAppMessage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Social\WhatsAppMessage>
 */
class WhatsAppMessageFactory extends Factory
{
    protected $model = WhatsAppMessage::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $direction = $this->faker->randomElement(['inbound', 'outbound']);
        $type = $this->faker->randomElement(['text', 'image', 'video', 'audio', 'document']);
        $status = $direction === 'inbound' ? 'received' : $this->faker->randomElement(['sent', 'delivered', 'read']);
        
        $sentAt = $this->faker->dateTimeBetween('-7 days', 'now');
        $deliveredAt = $status !== 'sent' ? $this->faker->dateTimeBetween($sentAt, 'now') : null;
        $readAt = $status === 'read' ? $this->faker->dateTimeBetween($deliveredAt ?? $sentAt, 'now') : null;
        
        return [
            'whatsapp_conversation_id' => WhatsAppConversation::factory(),
            'message_id' => 'wamid.' . $this->faker->regexify('[A-Z0-9]{50}'),
            'direction' => $direction,
            'type' => $type,
            'content' => $this->faker->sentence(10),
            'media_url' => $type !== 'text' ? $this->faker->imageUrl() : null,
            'media_mime_type' => $type !== 'text' ? $this->getMimeType($type) : null,
            'media_id' => $type !== 'text' ? $this->faker->numerify('##############') : null,
            'status' => $status,
            'error_message' => null,
            'from_phone' => $direction === 'inbound' ? '+551198' . $this->faker->numerify('#######') : '+551199' . $this->faker->numerify('#######'),
            'to_phone' => $direction === 'outbound' ? '+551198' . $this->faker->numerify('#######') : '+551199' . $this->faker->numerify('#######'),
            'sent_at' => $sentAt,
            'delivered_at' => $deliveredAt,
            'read_at' => $readAt,
            'metadata' => [
                'user_agent' => 'WhatsApp/2.23.1',
            ],
        ];
    }

    /**
     * Indicate that the message is inbound.
     */
    public function inbound(): static
    {
        return $this->state(fn (array $attributes) => [
            'direction' => 'inbound',
            'status' => 'received',
        ]);
    }

    /**
     * Indicate that the message is outbound.
     */
    public function outbound(): static
    {
        return $this->state(fn (array $attributes) => [
            'direction' => 'outbound',
            'status' => 'sent',
        ]);
    }

    /**
     * Indicate that the message is a text message.
     */
    public function text(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'text',
            'media_url' => null,
            'media_mime_type' => null,
            'media_id' => null,
        ]);
    }

    /**
     * Indicate that the message is an image.
     */
    public function image(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'image',
            'content' => $this->faker->sentence(5),
            'media_url' => $this->faker->imageUrl(640, 480),
            'media_mime_type' => 'image/jpeg',
            'media_id' => $this->faker->numerify('##############'),
        ]);
    }

    /**
     * Indicate that the message is a video.
     */
    public function video(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'video',
            'content' => $this->faker->sentence(5),
            'media_url' => 'https://example.com/video.mp4',
            'media_mime_type' => 'video/mp4',
            'media_id' => $this->faker->numerify('##############'),
        ]);
    }

    /**
     * Indicate that the message is an audio file.
     */
    public function audio(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'audio',
            'content' => null,
            'media_url' => 'https://example.com/audio.mp3',
            'media_mime_type' => 'audio/mpeg',
            'media_id' => $this->faker->numerify('##############'),
        ]);
    }

    /**
     * Indicate that the message is a document.
     */
    public function document(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'document',
            'content' => $this->faker->sentence(5),
            'media_url' => 'https://example.com/document.pdf',
            'media_mime_type' => 'application/pdf',
            'media_id' => $this->faker->numerify('##############'),
        ]);
    }

    /**
     * Indicate that the message failed to send.
     */
    public function failed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'failed',
            'error_message' => $this->faker->sentence(8),
            'delivered_at' => null,
            'read_at' => null,
        ]);
    }

    /**
     * Indicate that the message was delivered.
     */
    public function delivered(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'delivered',
            'delivered_at' => $this->faker->dateTimeBetween($attributes['sent_at'] ?? '-1 hour', 'now'),
            'read_at' => null,
        ]);
    }

    /**
     * Indicate that the message was read.
     */
    public function read(): static
    {
        return $this->state(function (array $attributes) {
            $deliveredAt = $this->faker->dateTimeBetween($attributes['sent_at'] ?? '-1 hour', 'now');
            
            return [
                'status' => 'read',
                'delivered_at' => $deliveredAt,
                'read_at' => $this->faker->dateTimeBetween($deliveredAt, 'now'),
            ];
        });
    }

    /**
     * Get MIME type for message type.
     */
    protected function getMimeType(string $type): string
    {
        return match ($type) {
            'image' => 'image/jpeg',
            'video' => 'video/mp4',
            'audio' => 'audio/mpeg',
            'document' => 'application/pdf',
            default => 'text/plain',
        };
    }
}
