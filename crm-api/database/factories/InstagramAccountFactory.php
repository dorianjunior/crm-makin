<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Social\InstagramAccount>
 */
class InstagramAccountFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'company_id' => \App\Models\CRM\Company::factory(),
            'instagram_user_id' => $this->faker->unique()->numerify('##########'),
            'username' => $this->faker->unique()->userName(),
            'access_token' => encrypt('test_access_token_' . $this->faker->uuid()),
            'token_expires_at' => now()->addDays(60),
            'account_type' => $this->faker->randomElement(['BUSINESS', 'CREATOR', 'PERSONAL']),
            'profile_picture_url' => $this->faker->imageUrl(150, 150, 'people'),
            'followers_count' => $this->faker->numberBetween(100, 100000),
            'is_active' => true,
            'metadata' => [
                'media_count' => $this->faker->numberBetween(10, 1000),
                'follows_count' => $this->faker->numberBetween(50, 5000),
            ],
        ];
    }
}
