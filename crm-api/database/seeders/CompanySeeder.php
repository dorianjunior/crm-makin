<?php

namespace Database\Seeders;

use App\Models\Company;
use Illuminate\Database\Seeder;

class CompanySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Company::create([
            'name' => 'Demo Company',
            'domain' => 'demo.example.com',
            'plan' => 'premium',
            'status' => 'active',
        ]);

        Company::create([
            'name' => 'Acme Corporation',
            'domain' => 'acme.example.com',
            'plan' => 'enterprise',
            'status' => 'active',
        ]);

        Company::create([
            'name' => 'Tech Solutions Ltd',
            'domain' => 'techsolutions.example.com',
            'plan' => 'basic',
            'status' => 'active',
        ]);

        $this->command->info('Companies created successfully!');
    }
}
