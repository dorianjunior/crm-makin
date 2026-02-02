<?php

namespace Database\Seeders;

use App\Models\CRM\Company;
use App\Models\CRM\Lead;
use App\Models\CRM\LeadSource;
use App\Models\User;
use Illuminate\Database\Seeder;

class LeadSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $demoCompany = Company::where('name', 'Demo Company')->first();
        $sources = LeadSource::where('company_id', $demoCompany->id)->get();
        $salesUsers = User::where('company_id', $demoCompany->id)
            ->whereHas('role', fn ($q) => $q->where('name', 'Sales'))
            ->get();

        $leads = [
            [
                'name' => 'Michael Johnson',
                'email' => 'michael.johnson@example.com',
                'phone' => '+1 555-0101',
                'status' => 'new',
                'notes' => 'Interested in Enterprise plan',
            ],
            [
                'name' => 'Sarah Williams',
                'email' => 'sarah.williams@example.com',
                'phone' => '+1 555-0102',
                'status' => 'contacted',
                'notes' => 'Requested demo for Professional plan',
            ],
            [
                'name' => 'David Brown',
                'email' => 'david.brown@example.com',
                'phone' => '+1 555-0103',
                'status' => 'qualified',
                'notes' => 'Budget approved, ready for proposal',
            ],
            [
                'name' => 'Emma Davis',
                'email' => 'emma.davis@example.com',
                'phone' => '+1 555-0104',
                'status' => 'proposal',
                'notes' => 'Proposal sent, waiting for feedback',
            ],
            [
                'name' => 'James Wilson',
                'email' => 'james.wilson@example.com',
                'phone' => '+1 555-0105',
                'status' => 'negotiation',
                'notes' => 'Negotiating pricing for Enterprise plan',
            ],
            [
                'name' => 'Olivia Martinez',
                'email' => 'olivia.martinez@example.com',
                'phone' => '+1 555-0106',
                'status' => 'won',
                'notes' => 'Closed deal - Professional plan',
            ],
            [
                'name' => 'William Garcia',
                'email' => 'william.garcia@example.com',
                'phone' => '+1 555-0107',
                'status' => 'new',
                'notes' => 'Inquiry from website contact form',
            ],
            [
                'name' => 'Sophia Rodriguez',
                'email' => 'sophia.rodriguez@example.com',
                'phone' => '+1 555-0108',
                'status' => 'contacted',
                'notes' => 'Follow up scheduled for next week',
            ],
        ];

        foreach ($leads as $leadData) {
            Lead::create([
                'company_id' => $demoCompany->id,
                'source_id' => $sources->random()->id,
                'assigned_to' => $salesUsers->random()->id,
                'name' => $leadData['name'],
                'email' => $leadData['email'],
                'phone' => $leadData['phone'],
                'status' => $leadData['status'],
                'notes' => $leadData['notes'],
            ]);
        }

        $this->command->info('Leads created successfully!');
    }
}
