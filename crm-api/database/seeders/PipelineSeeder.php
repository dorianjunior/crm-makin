<?php

namespace Database\Seeders;

use App\Models\Pipeline;
use App\Models\PipelineStage;
use App\Models\Company;
use Illuminate\Database\Seeder;

class PipelineSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $companies = Company::all();

        foreach ($companies as $company) {
            // Sales Pipeline
            $salesPipeline = Pipeline::create([
                'company_id' => $company->id,
                'name' => 'Sales Pipeline',
            ]);

            $salesStages = [
                ['name' => 'New Lead', 'order' => 1],
                ['name' => 'Contact Made', 'order' => 2],
                ['name' => 'Qualification', 'order' => 3],
                ['name' => 'Proposal Sent', 'order' => 4],
                ['name' => 'Negotiation', 'order' => 5],
                ['name' => 'Closed Won', 'order' => 6],
                ['name' => 'Closed Lost', 'order' => 7],
            ];

            foreach ($salesStages as $stage) {
                PipelineStage::create([
                    'pipeline_id' => $salesPipeline->id,
                    'name' => $stage['name'],
                    'order' => $stage['order'],
                ]);
            }

            // Support Pipeline
            $supportPipeline = Pipeline::create([
                'company_id' => $company->id,
                'name' => 'Support Pipeline',
            ]);

            $supportStages = [
                ['name' => 'New Ticket', 'order' => 1],
                ['name' => 'In Progress', 'order' => 2],
                ['name' => 'Waiting Customer', 'order' => 3],
                ['name' => 'Resolved', 'order' => 4],
                ['name' => 'Closed', 'order' => 5],
            ];

            foreach ($supportStages as $stage) {
                PipelineStage::create([
                    'pipeline_id' => $supportPipeline->id,
                    'name' => $stage['name'],
                    'order' => $stage['order'],
                ]);
            }
        }

        $this->command->info('Pipelines and stages created successfully!');
    }
}
