<?php

namespace Database\Factories\CMS;

use App\Models\CRM\Company;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CMS\Site>
 */
class SiteFactory extends Factory
{
    protected $model = \App\Models\CMS\Site::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'company_id' => 1, // Default to test company
            'name' => fake()->company(),
            'domain' => fake()->domainName(),
            'api_key' => Str::random(32),
            'active' => true,
            'settings' => [
                'theme' => 'default',
                'language' => 'pt-BR',
                'timezone' => 'America/Sao_Paulo',
            ],
        ];
    }

    /**
     * Indicate that the site is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'active' => false,
        ]);
    }
}
