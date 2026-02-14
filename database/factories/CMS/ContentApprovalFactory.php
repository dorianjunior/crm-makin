<?php

namespace Database\Factories\CMS;

use App\Models\Admin\User;
use App\Models\CMS\Page;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CMS\ContentApproval>
 */
class ContentApprovalFactory extends Factory
{
    protected $model = \App\Models\CMS\ContentApproval::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'approvable_type' => Page::class,
            'approvable_id' => 1, // Will be overridden in tests
            'requested_by' => 1, // Default to test user
            'approved_by' => null,
            'status' => 'pending',
            'message' => fake()->sentence(10),
            'rejection_reason' => null,
            'approved_at' => null,
        ];
    }

    /**
     * Indicate that the approval is approved.
     */
    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'approved_by' => User::factory(),
            'status' => 'approved',
            'approved_at' => now(),
        ]);
    }

    /**
     * Indicate that the approval is rejected.
     */
    public function rejected(): static
    {
        return $this->state(fn (array $attributes) => [
            'approved_by' => User::factory(),
            'status' => 'rejected',
            'rejection_reason' => fake()->sentence(12),
            'approved_at' => now(),
        ]);
    }

    /**
     * Set the approvable content to a specific model.
     */
    public function forContent(string $type, int $id): static
    {
        return $this->state(fn (array $attributes) => [
            'approvable_type' => $type,
            'approvable_id' => $id,
        ]);
    }
}
