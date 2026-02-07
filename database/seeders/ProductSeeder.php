<?php

namespace Database\Seeders;

use App\Models\CRM\Company;
use App\Models\CRM\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $companies = Company::all();

        $products = [
            // Produtos
            [
                'name' => 'Website Institucional',
                'sku' => 'WEB-INST-001',
                'type' => 'product',
                'description' => 'Website institucional responsivo com até 5 páginas, design moderno e otimizado para SEO.',
                'price' => 2500.00,
                'active' => true,
            ],
            [
                'name' => 'E-commerce Completo',
                'sku' => 'WEB-ECOM-001',
                'type' => 'product',
                'description' => 'Loja virtual completa com carrinho, checkout, pagamento online e painel administrativo.',
                'price' => 5000.00,
                'active' => true,
            ],
            [
                'name' => 'Sistema CRM',
                'sku' => 'SYS-CRM-001',
                'type' => 'product',
                'description' => 'Sistema de gerenciamento de relacionamento com clientes, pipelines e automações.',
                'price' => 8000.00,
                'active' => true,
            ],
            [
                'name' => 'Landing Page',
                'sku' => 'WEB-LAND-001',
                'type' => 'product',
                'description' => 'Página de conversão otimizada com design persuasivo e integração com ferramentas de marketing.',
                'price' => 800.00,
                'active' => true,
            ],
            [
                'name' => 'App Mobile (iOS + Android)',
                'sku' => 'APP-MOB-001',
                'type' => 'product',
                'description' => 'Aplicativo nativo para iOS e Android com até 10 telas.',
                'price' => 12000.00,
                'active' => true,
            ],
            [
                'name' => 'Dashboard Analytics',
                'sku' => 'SYS-DASH-001',
                'type' => 'product',
                'description' => 'Painel de análise de dados com gráficos interativos e relatórios personalizados.',
                'price' => 3500.00,
                'active' => true,
            ],

            // Serviços
            [
                'name' => 'Manutenção Mensal',
                'sku' => 'SRV-MAINT-001',
                'type' => 'service',
                'description' => 'Manutenção mensal incluindo atualizações, backup, monitoramento e suporte técnico.',
                'price' => 500.00,
                'active' => true,
            ],
            [
                'name' => 'Consultoria de Marketing Digital',
                'sku' => 'SRV-CONS-001',
                'type' => 'service',
                'description' => 'Consultoria estratégica em marketing digital, SEO, mídia paga e redes sociais.',
                'price' => 1500.00,
                'active' => true,
            ],
            [
                'name' => 'Gestão de Redes Sociais',
                'sku' => 'SRV-SOCIAL-001',
                'type' => 'service',
                'description' => 'Gerenciamento completo de redes sociais com até 12 posts mensais e relatórios.',
                'price' => 800.00,
                'active' => true,
            ],
            [
                'name' => 'Criação de Conteúdo',
                'sku' => 'SRV-CONT-001',
                'type' => 'service',
                'description' => 'Criação de conteúdo para blog, redes sociais ou email marketing (pacote de 10 peças).',
                'price' => 600.00,
                'active' => true,
            ],
            [
                'name' => 'SEO Avançado',
                'sku' => 'SRV-SEO-001',
                'type' => 'service',
                'description' => 'Otimização avançada para motores de busca incluindo análise técnica, keywords e link building.',
                'price' => 2000.00,
                'active' => true,
            ],
            [
                'name' => 'Treinamento WordPress',
                'sku' => 'SRV-TRAIN-001',
                'type' => 'service',
                'description' => 'Treinamento completo de WordPress para gestão de conteúdo (4 horas).',
                'price' => 400.00,
                'active' => false,
            ],
        ];

        foreach ($companies as $company) {
            // Limpa produtos antigos
            Product::where('company_id', $company->id)->delete();

            foreach ($products as $productData) {
                Product::create(array_merge($productData, [
                    'company_id' => $company->id,
                ]));
            }
        }

        $this->command->info('✅ Products seeded successfully!');
    }
}
