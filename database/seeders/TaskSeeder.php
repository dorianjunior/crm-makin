<?php

namespace Database\Seeders;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Models\CRM\Company;
use App\Models\CRM\Lead;
use App\Models\CRM\Task;
use App\Models\User;
use Illuminate\Database\Seeder;

class TaskSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $companies = Company::all();

        foreach ($companies as $company) {
            $users = User::where('company_id', $company->id)->get();
            $leads = Lead::where('company_id', $company->id)->get();

            if ($users->isEmpty() || $leads->isEmpty()) {
                continue;
            }

            // Tasks pendentes
            foreach ($leads->take(10) as $lead) {
                Task::create([
                    'company_id' => $company->id,
                    'lead_id' => $lead->id,
                    'assigned_to' => $users->random()->id,
                    'title' => 'Ligar para '.$lead->name,
                    'description' => 'Realizar follow-up sobre a proposta enviada',
                    'due_date' => now()->addDays(rand(1, 7)),
                    'status' => TaskStatus::PENDING,
                    'priority' => TaskPriority::HIGH,
                ]);

                Task::create([
                    'company_id' => $company->id,
                    'lead_id' => $lead->id,
                    'assigned_to' => $users->random()->id,
                    'title' => 'Enviar proposta comercial',
                    'description' => 'Elaborar e enviar proposta personalizada',
                    'due_date' => now()->addDays(rand(1, 3)),
                    'status' => TaskStatus::PENDING,
                    'priority' => TaskPriority::HIGH,
                ]);
            }

            // Tasks em progresso
            foreach ($leads->take(5) as $lead) {
                Task::create([
                    'company_id' => $company->id,
                    'lead_id' => $lead->id,
                    'assigned_to' => $users->random()->id,
                    'title' => 'Preparar apresentação',
                    'description' => 'Criar apresentação customizada para reunião',
                    'due_date' => now()->addDays(rand(1, 5)),
                    'status' => TaskStatus::IN_PROGRESS,
                    'priority' => TaskPriority::MEDIUM,
                ]);
            }

            // Tasks completadas
            foreach ($leads->take(8) as $lead) {
                $completedBy = $users->random();
                Task::create([
                    'company_id' => $company->id,
                    'lead_id' => $lead->id,
                    'assigned_to' => $completedBy->id,
                    'title' => 'Primeira reunião realizada',
                    'description' => 'Reunião inicial de apresentação da empresa',
                    'due_date' => now()->subDays(rand(1, 10)),
                    'status' => TaskStatus::COMPLETED,
                    'priority' => TaskPriority::MEDIUM,
                    'completed_at' => now()->subDays(rand(1, 5)),
                    'completed_by' => $completedBy->id,
                ]);
            }

            // Tasks vencidas
            foreach ($leads->take(3) as $lead) {
                Task::create([
                    'company_id' => $company->id,
                    'lead_id' => $lead->id,
                    'assigned_to' => $users->random()->id,
                    'title' => 'Enviar material adicional',
                    'description' => 'Enviar estudos de caso e portfólio',
                    'due_date' => now()->subDays(rand(1, 5)),
                    'status' => TaskStatus::PENDING,
                    'priority' => TaskPriority::LOW,
                ]);
            }

            // Tasks com diferentes prioridades
            $taskTemplates = [
                ['title' => 'Agendar demonstração do produto', 'priority' => TaskPriority::HIGH],
                ['title' => 'Enviar contrato para assinatura', 'priority' => TaskPriority::HIGH],
                ['title' => 'Realizar pesquisa de satisfação', 'priority' => TaskPriority::LOW],
                ['title' => 'Atualizar dados cadastrais', 'priority' => TaskPriority::LOW],
                ['title' => 'Negociar valores da proposta', 'priority' => TaskPriority::MEDIUM],
                ['title' => 'Solicitar documentação', 'priority' => TaskPriority::MEDIUM],
            ];

            foreach ($taskTemplates as $template) {
                if ($leads->isNotEmpty()) {
                    Task::create([
                        'company_id' => $company->id,
                        'lead_id' => $leads->random()->id,
                        'assigned_to' => $users->random()->id,
                        'title' => $template['title'],
                        'description' => 'Descrição da tarefa: '.$template['title'],
                        'due_date' => now()->addDays(rand(1, 15)),
                        'status' => TaskStatus::PENDING,
                        'priority' => $template['priority'],
                    ]);
                }
            }
        }

        $this->command->info('✅ Tasks created successfully!');
    }
}
