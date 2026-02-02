<?php

namespace Database\Factories\Social;

use App\Models\Lead;
use App\Models\Social\WhatsAppAccount;
use App\Models\Social\WhatsAppConversation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Social\WhatsAppConversation>
 */
class WhatsAppConversationFactory extends Factory
{
    protected $model = WhatsAppConversation::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $phone = $this->faker->numerify('551198#######');

        return [
            'whatsapp_account_id' => WhatsAppAccount::factory(),
            'lead_id' => null, // Can be linked later
            'conversation_id' => $phone,
            'contact_name' => $this->faker->name,
            'contact_phone' => '+'.$phone,
            'contact_profile_pic' => $this->faker->imageUrl(200, 200, 'people'),
            'is_group' => false,
            'unread_count' => $this->faker->numberBetween(0, 5),
            'last_message_at' => $this->faker->dateTimeBetween('-7 days', 'now'),
            'status' => 'active',
            'metadata' => [
                'last_seen' => $this->faker->dateTimeBetween('-1 day', 'now')->format('Y-m-d H:i:s'),
            ],
        ];
    }

    /**
     * Indicate that the conversation is linked to a lead.
     */
    public function withLead(): static
    {
        return $this->state(fn (array $attributes) => [
            'lead_id' => Lead::factory(),
        ]);
    }

    /**
     * Indicate that the conversation has unread messages.
     */
    public function unread(int $count = 3): static
    {
        return $this->state(fn (array $attributes) => [
            'unread_count' => $count,
        ]);
    }

    /**
     * Indicate that the conversation is archived.
     */
    public function archived(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'archived',
        ]);
    }

    /**
     * Indicate that the conversation is blocked.
     */
    public function blocked(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'blocked',
        ]);
    }

    /**
     * Indicate that the conversation is a group.
     */
    public function group(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_group' => true,
            'contact_name' => $this->faker->words(3, true).' Group',
            'metadata' => [
                'group_size' => $this->faker->numberBetween(3, 50),
                'group_admin' => $this->faker->phoneNumber,
            ],
        ]);
    }
}
