<?php

namespace Database\Seeders;

use App\Models\CRM\Activity;
use App\Models\CRM\Company;
use App\Models\CRM\Lead;
use App\Models\User;
use Illuminate\Database\Seeder;

class ActivitySeeder extends Seeder
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

            $activityTypes = [
                [
                    'type' => 'call',
                    'descriptions' => [
                        'Ligação de apresentação inicial da empresa',
                        'Follow-up sobre proposta enviada',
                        'Ligação para agendar reunião presencial',
                        'Retorno de ligação solicitada pelo lead',
                        'Ligação de qualificação do lead',
                    ],
                    'durations' => [15, 20, 30, 45],
                ],
                [
                    'type' => 'meeting',
                    'descriptions' => [
                        'Reunião de apresentação do produto',
                        'Reunião de negociação comercial',
                        'Reunião de alinhamento de expectativas',
                        'Reunião para demonstração técnica',
                        'Reunião de fechamento de contrato',
                    ],
                    'durations' => [30, 45, 60, 90, 120],
                ],
                [
                    'type' => 'email',
                    'descriptions' => [
                        'Envio de proposta comercial por email',
                        'Envio de material institucional',
                        'Resposta a dúvidas sobre o produto',
                        'Envio de estudos de caso',
                        'Confirmação de reunião por email',
                    ],
                    'durations' => null,
                ],
                [
                    'type' => 'note',
                    'descriptions' => [
                        'Lead demonstrou interesse em produtos premium',
                        'Cliente solicitou prazo adicional para análise',
                        'Lead menciona concorrente X como alternativa',
                        'Necessário enviar informações sobre garantia',
                        'Cliente possui budget aprovado para este trimestre',
                    ],
                    'durations' => null,
                ],
                [
                    'type' => 'task',
                    'descriptions' => [
                        'Enviar documentação solicitada',
                        'Preparar apresentação customizada',
                        'Agendar demonstração do sistema',
                        'Solicitar aprovação do gerente',
                        'Criar proposta personalizada',
                    ],
                    'durations' => [30, 60, 90, 120],
                ],
            ];

            // Criar atividades distribuídas nos últimos 30 dias
            foreach ($leads as $lead) {
                $activitiesCount = rand(3, 10);

                for ($i = 0; $i < $activitiesCount; $i++) {
                    $activityType = $activityTypes[array_rand($activityTypes)];

                    $activity = Activity::create([
                        'company_id' => $company->id,
                        'lead_id' => $lead->id,
                        'user_id' => $users->random()->id,
                        'type' => $activityType['type'],
                        'description' => $activityType['descriptions'][array_rand($activityType['descriptions'])],
                        'notes' => rand(0, 1) ? 'Observações: ' . fake()->sentence() : null,
                        'duration' => $activityType['durations'] ? $activityType['durations'][array_rand($activityType['durations'])] : null,
                    ]);

                    // Distribuir atividades nos últimos 30 dias
                    $activity->created_at = now()->subDays(rand(0, 30))->subHours(rand(0, 23));
                    $activity->save();
                }
            }

            // Criar algumas atividades para hoje
            foreach ($leads->take(5) as $lead) {
                $activityType = $activityTypes[array_rand($activityTypes)];

                Activity::create([
                    'company_id' => $company->id,
                    'lead_id' => $lead->id,
                    'user_id' => $users->random()->id,
                    'type' => $activityType['type'],
                    'description' => $activityType['descriptions'][array_rand($activityType['descriptions'])],
                    'notes' => 'Atividade realizada hoje - ' . fake()->sentence(),
                    'duration' => $activityType['durations'] ? $activityType['durations'][array_rand($activityType['durations'])] : null,
                ]);
            }
        }

        $this->command->info('✅ Activities created successfully!');
    }
}
