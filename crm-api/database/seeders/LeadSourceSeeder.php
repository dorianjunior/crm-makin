<?php

namespace Database\Seeders;

use App\Models\CRM\Company;
use App\Models\CRM\LeadSource;
use Illuminate\Database\Seeder;

class LeadSourceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $companies = Company::all();

        $sources = [
            'Website',
            'Facebook',
            'Instagram',
            'LinkedIn',
            'Google Ads',
            'Email Campaign',
            'Referral',
            'Cold Call',
            'Event',
            'Direct',
            'WhatsApp',
            'Partner',
        ];

        foreach ($companies as $company) {
            foreach ($sources as $source) {
                LeadSource::create([
                    'company_id' => $company->id,
                    'name' => $source,
                ]);
            }
        }

        $this->command->info('Lead sources created successfully!');
    }
}
