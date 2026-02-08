<?php

namespace Database\Seeders;

use App\Models\CMS\Site;
use App\Models\CRM\Company;
use Illuminate\Database\Seeder;

class SiteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $companies = Company::all();

        $sites = [
            [
                'name' => 'Site Institucional - Empresa Principal',
                'domain' => 'exemplo.com.br',
                'active' => true,
                'settings' => [
                    'theme' => 'modern',
                    'primary_color' => '#FF6B35',
                    'logo_url' => '/images/logo-principal.png',
                    'analytics_id' => 'G-XXXXXXXXXX',
                    'seo' => [
                        'meta_title' => 'Empresa Principal - Soluções em Tecnologia',
                        'meta_description' => 'A melhor empresa de tecnologia do Brasil',
                    ],
                ],
            ],
            [
                'name' => 'Blog Corporativo',
                'domain' => 'blog.exemplo.com.br',
                'active' => true,
                'settings' => [
                    'theme' => 'editorial',
                    'primary_color' => '#2563eb',
                    'logo_url' => '/images/logo-blog.png',
                    'show_categories' => true,
                    'posts_per_page' => 12,
                    'comments_enabled' => true,
                ],
            ],
            [
                'name' => 'E-commerce',
                'domain' => 'loja.exemplo.com.br',
                'active' => true,
                'settings' => [
                    'theme' => 'shop',
                    'primary_color' => '#10b981',
                    'secondary_color' => '#059669',
                    'logo_url' => '/images/logo-loja.png',
                    'payment_methods' => ['credit_card', 'pix', 'boleto'],
                    'shipping_methods' => ['correios', 'transportadora'],
                    'currency' => 'BRL',
                ],
            ],
            [
                'name' => 'Portfólio Criativo',
                'domain' => 'portfolio.exemplo.com.br',
                'active' => true,
                'settings' => [
                    'theme' => 'portfolio',
                    'primary_color' => '#8b5cf6',
                    'logo_url' => '/images/logo-portfolio.png',
                    'layout' => 'masonry',
                    'lightbox_enabled' => true,
                ],
            ],
            [
                'name' => 'Site de Eventos',
                'domain' => 'eventos.exemplo.com.br',
                'active' => true,
                'settings' => [
                    'theme' => 'events',
                    'primary_color' => '#f59e0b',
                    'logo_url' => '/images/logo-eventos.png',
                    'registration_enabled' => true,
                    'payment_integration' => 'stripe',
                ],
            ],
            [
                'name' => 'Portal de Notícias',
                'domain' => 'noticias.exemplo.com.br',
                'active' => true,
                'settings' => [
                    'theme' => 'news',
                    'primary_color' => '#dc2626',
                    'logo_url' => '/images/logo-noticias.png',
                    'categories_menu' => true,
                    'breaking_news_banner' => true,
                    'newsletter_enabled' => true,
                ],
            ],
            [
                'name' => 'Landing Page - Curso Online',
                'domain' => 'curso.exemplo.com.br',
                'active' => true,
                'settings' => [
                    'theme' => 'landing',
                    'primary_color' => '#FF6B35',
                    'cta_button_text' => 'Inscreva-se Agora',
                    'countdown_timer' => true,
                    'testimonials_enabled' => true,
                ],
            ],
            [
                'name' => 'Site de Documentação',
                'domain' => 'docs.exemplo.com.br',
                'active' => true,
                'settings' => [
                    'theme' => 'documentation',
                    'primary_color' => '#0ea5e9',
                    'sidebar_navigation' => true,
                    'search_enabled' => true,
                    'code_highlighting' => true,
                    'versions' => ['v1.0', 'v2.0', 'v3.0'],
                ],
            ],
            [
                'name' => 'Site Imobiliário',
                'domain' => 'imoveis.exemplo.com.br',
                'active' => true,
                'settings' => [
                    'theme' => 'real-estate',
                    'primary_color' => '#0891b2',
                    'logo_url' => '/images/logo-imoveis.png',
                    'map_integration' => 'google_maps',
                    'property_types' => ['casa', 'apartamento', 'terreno', 'comercial'],
                    'advanced_search' => true,
                ],
            ],
            [
                'name' => 'Site em Manutenção',
                'domain' => 'manutencao.exemplo.com.br',
                'active' => false,
                'settings' => [
                    'theme' => 'minimal',
                    'primary_color' => '#6b7280',
                    'maintenance_message' => 'Site em manutenção. Voltamos em breve!',
                ],
            ],
            [
                'name' => 'Site Descontinuado',
                'domain' => 'old.exemplo.com.br',
                'active' => false,
                'settings' => [
                    'theme' => 'legacy',
                    'redirect_to' => 'https://exemplo.com.br',
                ],
            ],
            [
                'name' => 'Site Restaurante',
                'domain' => 'restaurante.exemplo.com.br',
                'active' => true,
                'settings' => [
                    'theme' => 'restaurant',
                    'primary_color' => '#ef4444',
                    'logo_url' => '/images/logo-restaurante.png',
                    'online_menu' => true,
                    'reservation_enabled' => true,
                    'delivery_integration' => 'ifood',
                ],
            ],
        ];

        foreach ($companies as $company) {
            foreach ($sites as $siteData) {
                Site::create([
                    'company_id' => $company->id,
                    'name' => $siteData['name'],
                    'domain' => str_replace('exemplo', strtolower($company->name), $siteData['domain']),
                    'active' => $siteData['active'],
                    'settings' => $siteData['settings'],
                ]);
            }

            $this->command->info("✅ Created {count($sites)} sites for company: {$company->name}");
        }
    }
}
