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

        if (! $demoCompany) {
            $this->command->error('Demo Company não encontrada!');

            return;
        }

        $sources = LeadSource::where('company_id', $demoCompany->id)->get();

        if ($sources->isEmpty()) {
            $this->command->info('Criando fontes de leads...');
            $sources = collect([
                LeadSource::create(['company_id' => $demoCompany->id, 'name' => 'Website', 'description' => 'Leads do site']),
                LeadSource::create(['company_id' => $demoCompany->id, 'name' => 'Facebook', 'description' => 'Leads do Facebook']),
                LeadSource::create(['company_id' => $demoCompany->id, 'name' => 'Instagram', 'description' => 'Leads do Instagram']),
                LeadSource::create(['company_id' => $demoCompany->id, 'name' => 'Google Ads', 'description' => 'Leads do Google Ads']),
                LeadSource::create(['company_id' => $demoCompany->id, 'name' => 'Indicação', 'description' => 'Leads por indicação']),
                LeadSource::create(['company_id' => $demoCompany->id, 'name' => 'WhatsApp', 'description' => 'Leads do WhatsApp']),
            ]);
        }

        $users = User::where('company_id', $demoCompany->id)->get();

        $leads = [
            [
                'name' => 'João Silva',
                'email' => 'joao.silva@email.com',
                'phone' => '(11) 98765-4321',
                'status' => 'new',
                'notes' => 'Interessado em consultoria de marketing digital.',
            ],
            [
                'name' => 'Maria Santos',
                'email' => 'maria.santos@empresa.com',
                'phone' => '(21) 99876-5432',
                'status' => 'contacted',
                'notes' => 'Primeira ligação realizada, demonstrou interesse.',
            ],
            [
                'name' => 'Pedro Oliveira',
                'email' => 'pedro.oliveira@tech.com',
                'phone' => '(31) 98765-1234',
                'status' => 'qualified',
                'notes' => 'Lead qualificado, orçamento aprovado internamente.',
            ],
            [
                'name' => 'Ana Costa',
                'email' => 'ana.costa@startup.io',
                'phone' => '(41) 97654-3210',
                'status' => 'negotiation',
                'notes' => 'Em negociação de valores e prazos.',
            ],
            [
                'name' => 'Carlos Mendes',
                'email' => 'carlos.mendes@corp.com',
                'phone' => '(51) 96543-2109',
                'status' => 'won',
                'notes' => 'Cliente fechado! Contrato assinado.',
            ],
            [
                'name' => 'Juliana Ferreira',
                'email' => 'juliana.ferreira@digital.com',
                'phone' => '(61) 95432-1098',
                'status' => 'lost',
                'notes' => 'Optou pela concorrência devido ao preço.',
            ],
            [
                'name' => 'Roberto Alves',
                'email' => 'roberto.alves@gmail.com',
                'phone' => '(71) 94321-0987',
                'status' => 'new',
                'notes' => 'Contato via WhatsApp, ainda não retornou.',
            ],
            [
                'name' => 'Fernanda Lima',
                'email' => 'fernanda.lima@boutique.com',
                'phone' => '(81) 93210-9876',
                'status' => 'contacted',
                'notes' => 'Enviado material por e-mail.',
            ],
            [
                'name' => 'Ricardo Souza',
                'email' => 'ricardo.souza@restaurante.com',
                'phone' => '(85) 92109-8765',
                'status' => 'qualified',
                'notes' => 'Reunião agendada para próxima semana.',
            ],
            [
                'name' => 'Patricia Rocha',
                'email' => 'patricia.rocha@advocacia.com',
                'phone' => '(27) 91098-7654',
                'status' => 'proposal',
                'notes' => 'Proposta enviada, aguardando retorno.',
            ],
            [
                'name' => 'Bruno Cardoso',
                'email' => 'bruno.cardoso@construtora.com',
                'phone' => '(48) 90987-6543',
                'status' => 'negotiation',
                'notes' => 'Negociando condições de pagamento.',
            ],
            [
                'name' => 'Camila Martins',
                'email' => 'camila.martins@saude.com',
                'phone' => '(47) 99876-5432',
                'status' => 'contacted',
                'notes' => 'Interesse em sistema de gestão.',
            ],
            [
                'name' => 'Eduardo Pereira',
                'email' => 'eduardo.pereira@educacao.com',
                'phone' => '(19) 98765-4321',
                'status' => 'new',
                'notes' => 'Lead vindo do Google Ads.',
            ],
            [
                'name' => 'Tatiana Gomes',
                'email' => 'tatiana.gomes@eventos.com',
                'phone' => '(11) 97654-3210',
                'status' => 'qualified',
                'notes' => 'Orçamento dentro do esperado.',
            ],
            [
                'name' => 'Gustavo Barbosa',
                'email' => 'gustavo.barbosa@logistica.com',
                'phone' => '(21) 96543-2109',
                'status' => 'won',
                'notes' => 'Segundo contrato fechado este mês!',
            ],
            [
                'name' => 'Vanessa Dias',
                'email' => 'vanessa.dias@beleza.com',
                'phone' => '(31) 95432-1098',
                'status' => 'lost',
                'notes' => 'Não teve orçamento aprovado.',
            ],
            [
                'name' => 'Thiago Campos',
                'email' => 'thiago.campos@tech.io',
                'phone' => '(41) 94321-0987',
                'status' => 'contacted',
                'notes' => 'Enviado apresentação da empresa.',
            ],
            [
                'name' => 'Isabela Cunha',
                'email' => 'isabela.cunha@consultoria.com',
                'phone' => '(51) 93210-9876',
                'status' => 'new',
                'notes' => 'Primeiro contato realizado.',
            ],
            [
                'name' => 'Marcelo Reis',
                'email' => 'marcelo.reis@varejo.com',
                'phone' => '(61) 92109-8765',
                'status' => 'proposal',
                'notes' => 'Proposta customizada enviada.',
            ],
            [
                'name' => 'Luciana Teixeira',
                'email' => 'luciana.teixeira@contabilidade.com',
                'phone' => '(71) 91098-7654',
                'status' => 'negotiation',
                'notes' => 'Discutindo prazo de implementação.',
            ],
        ];

        foreach ($leads as $leadData) {
            Lead::create([
                'company_id' => $demoCompany->id,
                'source_id' => $sources->random()->id,
                'assigned_to' => $users->isNotEmpty() ? $users->random()->id : null,
                'name' => $leadData['name'],
                'email' => $leadData['email'],
                'phone' => $leadData['phone'],
                'status' => $leadData['status'],
                'notes' => $leadData['notes'],
            ]);
        }

        $this->command->info('✅ '.count($leads).' leads criados com sucesso!');
    }
}
