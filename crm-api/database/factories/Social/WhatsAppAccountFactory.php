<?php

namespace Database\Factories\Social;

use App\Models\Company;
use App\Models\Social\WhatsAppAccount;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Social\WhatsAppAccount>
 */
class WhatsAppAccountFactory extends Factory
{
    protected $model = WhatsAppAccount::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $phoneNumber = $this->faker->numerify('+551198#######');

        return [
            'company_id' => Company::factory(),
            'phone_number_id' => $this->faker->numerify('##############'),
            'business_account_id' => $this->faker->numerify('##############'),
            'phone_number' => $phoneNumber,
            'display_name' => $this->faker->company . ' Support',
            'access_token' => Crypt::encryptString('test_access_token_' . Str::random(40)),
            'verify_token' => Crypt::encryptString('verify_' . Str::random(20)),
            'account_type' => $this->faker->randomElement(['STANDARD', 'OFFICIAL', 'VERIFIED']),
            'quality_rating' => $this->faker->randomElement(['GREEN', 'YELLOW', 'RED', 'UNKNOWN']),
            'is_active' => true,
            'metadata' => [
                'timezone' => 'America/Sao_Paulo',
                'business_name' => $this->faker->company,
            ],
        ];
    }

    /**
     * Indicate that the account is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Indicate that the account has a red quality rating.
     */
    public function redQuality(): static
    {
        return $this->state(fn (array $attributes) => [
            'quality_rating' => 'RED',
        ]);
    }

    /**
     * Indicate that the account is verified.
     */
    public function verified(): static
    {
        return $this->state(fn (array $attributes) => [
            'account_type' => 'VERIFIED',
            'quality_rating' => 'GREEN',
        ]);
    }
}
