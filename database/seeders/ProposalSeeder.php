<?php

namespace Database\Seeders;

use App\Enums\ProposalStatus;
use App\Models\CRM\Company;
use App\Models\CRM\Lead;
use App\Models\CRM\Product;
use App\Models\CRM\Proposal;
use App\Models\CRM\ProposalItem;
use Illuminate\Database\Seeder;

class ProposalSeeder extends Seeder
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

        $leads = Lead::where('company_id', $demoCompany->id)->get();
        $products = Product::where('company_id', $demoCompany->id)->where('active', true)->get();

        if ($leads->isEmpty()) {
            $this->command->error('Nenhum lead encontrado. Execute LeadSeeder primeiro.');

            return;
        }

        if ($products->isEmpty()) {
            $this->command->error('Nenhum produto encontrado. Execute ProductSeeder primeiro.');

            return;
        }

        // Limpa propostas antigas
        Proposal::whereIn('lead_id', $leads->pluck('id'))->delete();

        $proposalsData = [
            [
                'lead' => $leads->where('email', 'joao.silva@email.com')->first(),
                'status' => ProposalStatus::DRAFT,
                'notes' => 'Proposta inicial para consultoria e gestão de redes sociais.',
                'valid_until' => now()->addDays(15),
                'sent_at' => null,
                'items' => [
                    ['sku' => 'SRV-CONS-001', 'quantity' => 1], // Consultoria
                    ['sku' => 'SRV-SOCIAL-001', 'quantity' => 3], // Gestão redes sociais (3 meses)
                ],
            ],
            [
                'lead' => $leads->where('email', 'maria.santos@empresa.com')->first(),
                'status' => ProposalStatus::SENT,
                'notes' => 'Website institucional completo com otimização SEO.',
                'valid_until' => now()->addDays(30),
                'sent_at' => now()->subDays(2),
                'items' => [
                    ['sku' => 'WEB-INST-001', 'quantity' => 1], // Website
                    ['sku' => 'SRV-SEO-001', 'quantity' => 1], // SEO
                    ['sku' => 'SRV-MAINT-001', 'quantity' => 6], // Manutenção (6 meses)
                ],
            ],
            [
                'lead' => $leads->where('email', 'pedro.oliveira@tech.com')->first(),
                'status' => ProposalStatus::VIEWED,
                'notes' => 'Sistema CRM customizado com treinamento e manutenção.',
                'valid_until' => now()->addDays(20),
                'sent_at' => now()->subDays(5),
                'items' => [
                    ['sku' => 'SYS-CRM-001', 'quantity' => 1], // CRM
                    ['sku' => 'SRV-TRAIN-001', 'quantity' => 2], // Treinamento
                    ['sku' => 'SRV-MAINT-001', 'quantity' => 12], // Manutenção (12 meses)
                ],
            ],
            [
                'lead' => $leads->where('email', 'ana.costa@startup.io')->first(),
                'status' => ProposalStatus::ACCEPTED,
                'notes' => 'Landing page + gestão de redes sociais para campanha de lançamento.',
                'valid_until' => now()->addDays(10),
                'sent_at' => now()->subDays(10),
                'items' => [
                    ['sku' => 'WEB-LAND-001', 'quantity' => 1], // Landing page
                    ['sku' => 'SRV-SOCIAL-001', 'quantity' => 6], // Gestão redes (6 meses)
                    ['sku' => 'SRV-CONT-001', 'quantity' => 3], // Criação de conteúdo
                ],
            ],
            [
                'lead' => $leads->where('email', 'carlos.mendes@corp.com')->first(),
                'status' => ProposalStatus::ACCEPTED,
                'notes' => 'E-commerce completo com integração de pagamentos e dashboard analytics.',
                'valid_until' => now()->addDays(45),
                'sent_at' => now()->subDays(15),
                'items' => [
                    ['sku' => 'WEB-ECOM-001', 'quantity' => 1], // E-commerce
                    ['sku' => 'SYS-DASH-001', 'quantity' => 1], // Dashboard
                    ['sku' => 'SRV-SEO-001', 'quantity' => 1], // SEO
                    ['sku' => 'SRV-MAINT-001', 'quantity' => 12], // Manutenção (12 meses)
                ],
            ],
            [
                'lead' => $leads->where('email', 'juliana.ferreira@digital.com')->first(),
                'status' => ProposalStatus::REJECTED,
                'notes' => 'Cliente optou por outra solução temporariamente.',
                'valid_until' => now()->subDays(5),
                'sent_at' => now()->subDays(20),
                'items' => [
                    ['sku' => 'WEB-INST-001', 'quantity' => 1], // Website
                    ['sku' => 'SRV-CONS-001', 'quantity' => 1], // Consultoria
                ],
            ],
            [
                'lead' => $leads->where('email', 'roberto.lima@inovacao.com')->first() ?? $leads->first(),
                'status' => ProposalStatus::EXPIRED,
                'notes' => 'Proposta expirada sem resposta do cliente.',
                'valid_until' => now()->subDays(10),
                'sent_at' => now()->subDays(40),
                'items' => [
                    ['sku' => 'APP-MOB-001', 'quantity' => 1], // App Mobile
                    ['sku' => 'SRV-MAINT-001', 'quantity' => 12], // Manutenção
                ],
            ],
            [
                'lead' => $leads->where('status', 'qualified')->first() ?? $leads->skip(3)->first(),
                'status' => ProposalStatus::SENT,
                'notes' => 'Pacote completo de marketing digital.',
                'valid_until' => now()->addDays(25),
                'sent_at' => now()->subDays(3),
                'items' => [
                    ['sku' => 'WEB-LAND-001', 'quantity' => 2], // 2 Landing pages
                    ['sku' => 'SRV-CONS-001', 'quantity' => 1], // Consultoria
                    ['sku' => 'SRV-SOCIAL-001', 'quantity' => 12], // Gestão redes (12 meses)
                    ['sku' => 'SRV-SEO-001', 'quantity' => 1], // SEO
                ],
            ],
            [
                'lead' => $leads->where('status', 'negotiation')->first() ?? $leads->skip(4)->first(),
                'status' => ProposalStatus::VIEWED,
                'notes' => 'Dashboard personalizado para análise de vendas.',
                'valid_until' => now()->addDays(15),
                'sent_at' => now()->subDays(7),
                'items' => [
                    ['sku' => 'SYS-DASH-001', 'quantity' => 1], // Dashboard
                    ['sku' => 'SRV-TRAIN-001', 'quantity' => 1], // Treinamento
                ],
            ],
            [
                'lead' => $leads->where('status', 'new')->first() ?? $leads->skip(5)->first(),
                'status' => ProposalStatus::DRAFT,
                'notes' => 'Proposta em elaboração para pacote de serviços.',
                'valid_until' => now()->addDays(30),
                'sent_at' => null,
                'items' => [
                    ['sku' => 'WEB-INST-001', 'quantity' => 1], // Website
                    ['sku' => 'SRV-CONT-001', 'quantity' => 6], // Conteúdo (6 pacotes)
                ],
            ],
        ];

        $proposalCounter = 1;
        $currentYear = now()->year;

        foreach ($proposalsData as $data) {
            if (! $data['lead']) {
                continue;
            }

            // Gerar número da proposta no formato: PROP-YYYY-NNNN
            $proposalNumber = sprintf('PROP-%d-%04d', $currentYear, $proposalCounter++);

            $totalValue = 0;

            // Calcular total antes de criar a proposta
            foreach ($data['items'] as $itemData) {
                $product = $products->where('sku', $itemData['sku'])->first();
                if ($product) {
                    $totalValue += $product->price * $itemData['quantity'];
                }
            }

            // Criar proposta
            $proposal = Proposal::create([
                'lead_id' => $data['lead']->id,
                'number' => $proposalNumber,
                'total_value' => $totalValue,
                'status' => $data['status'],
                'notes' => $data['notes'],
                'valid_until' => $data['valid_until'],
                'sent_at' => $data['sent_at'],
            ]);

            // Criar items da proposta
            foreach ($data['items'] as $itemData) {
                $product = $products->where('sku', $itemData['sku'])->first();

                if ($product) {
                    ProposalItem::create([
                        'proposal_id' => $proposal->id,
                        'product_id' => $product->id,
                        'quantity' => $itemData['quantity'],
                        'price' => $product->price,
                    ]);
                }
            }
        }

        $this->command->info('✅ Proposals seeded successfully!');
        $this->command->info("   Created {$proposalCounter} proposals with items");
    }
}
