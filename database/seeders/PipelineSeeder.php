<?php

namespace Database\Seeders;

use App\Models\CRM\Company;
use App\Models\CRM\Pipeline;
use App\Models\CRM\PipelineStage;
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
            // Sales Pipeline (Default)
            $salesPipeline = Pipeline::create([
                'company_id' => $company->id,
                'name' => 'Pipeline de Vendas',
                'description' => 'Pipeline principal para gestão de vendas e negociações',
                'is_active' => true,
                'is_default' => true,
            ]);

            $salesStages = [
                ['name' => 'Novo Lead', 'order' => 1, 'probability' => 10, 'color' => '#94A3B8'],
                ['name' => 'Primeiro Contato', 'order' => 2, 'probability' => 20, 'color' => '#3B82F6'],
                ['name' => 'Qualificação', 'order' => 3, 'probability' => 40, 'color' => '#8B5CF6'],
                ['name' => 'Proposta Enviada', 'order' => 4, 'probability' => 60, 'color' => '#F59E0B'],
                ['name' => 'Negociação', 'order' => 5, 'probability' => 80, 'color' => '#F97316'],
                ['name' => 'Fechado Ganho', 'order' => 6, 'probability' => 100, 'color' => '#10B981'],
                ['name' => 'Fechado Perdido', 'order' => 7, 'probability' => 0, 'color' => '#EF4444'],
            ];

            foreach ($salesStages as $stage) {
                PipelineStage::create([
                    'pipeline_id' => $salesPipeline->id,
                    'name' => $stage['name'],
                    'order' => $stage['order'],
                    'probability' => $stage['probability'],
                    'color' => $stage['color'],
                ]);
            }

            // Support Pipeline
            $supportPipeline = Pipeline::create([
                'company_id' => $company->id,
                'name' => 'Pipeline de Suporte',
                'description' => 'Pipeline para gestão de tickets e atendimento ao cliente',
                'is_active' => true,
                'is_default' => false,
            ]);

            $supportStages = [
                ['name' => 'Novo Ticket', 'order' => 1, 'probability' => 25, 'color' => '#64748B'],
                ['name' => 'Em Progresso', 'order' => 2, 'probability' => 50, 'color' => '#3B82F6'],
                ['name' => 'Aguardando Cliente', 'order' => 3, 'probability' => 50, 'color' => '#F59E0B'],
                ['name' => 'Resolvido', 'order' => 4, 'probability' => 75, 'color' => '#10B981'],
                ['name' => 'Fechado', 'order' => 5, 'probability' => 100, 'color' => '#6B7280'],
            ];

            foreach ($supportStages as $stage) {
                PipelineStage::create([
                    'pipeline_id' => $supportPipeline->id,
                    'name' => $stage['name'],
                    'order' => $stage['order'],
                    'probability' => $stage['probability'],
                    'color' => $stage['color'],
                ]);
            }
        }

        $this->command->info('Pipelines and stages created successfully!');
    }
}
