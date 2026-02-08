<?php

namespace Database\Seeders;

use App\Models\CMS\Page;
use App\Models\CMS\Site;
use App\Models\User;
use Illuminate\Database\Seeder;

class PageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $sites = Site::where('active', true)->get();

        $pages = [
            [
                'title' => 'Início',
                'slug' => 'inicio',
                'excerpt' => 'Bem-vindo ao nosso site',
                'content' => '<h1>Bem-vindo!</h1><p>Esta é a página inicial do nosso site.</p>',
                'seo_title' => 'Página Inicial - Bem-vindo',
                'seo_description' => 'Conheça nossa empresa e nossos serviços',
                'status' => 'published',
                'order' => 1,
            ],
            [
                'title' => 'Sobre Nós',
                'slug' => 'sobre-nos',
                'excerpt' => 'Conheça nossa história e missão',
                'content' => '<h1>Sobre Nós</h1><p>Somos uma empresa dedicada a oferecer as melhores soluções.</p>',
                'seo_title' => 'Sobre Nós - Nossa História',
                'seo_description' => 'Conheça nossa história, missão e valores',
                'status' => 'published',
                'order' => 2,
            ],
            [
                'title' => 'Serviços',
                'slug' => 'servicos',
                'excerpt' => 'Nossos principais serviços',
                'content' => '<h1>Nossos Serviços</h1><p>Oferecemos uma gama completa de serviços.</p>',
                'seo_title' => 'Serviços - O que oferecemos',
                'seo_description' => 'Conheça todos os serviços que oferecemos',
                'status' => 'published',
                'order' => 3,
            ],
            [
                'title' => 'Portfólio',
                'slug' => 'portfolio',
                'excerpt' => 'Conheça nossos projetos',
                'content' => '<h1>Portfólio</h1><p>Veja alguns dos nossos melhores projetos.</p>',
                'seo_title' => 'Portfólio - Nossos Projetos',
                'seo_description' => 'Galeria com nossos principais projetos e cases de sucesso',
                'status' => 'published',
                'order' => 4,
            ],
            [
                'title' => 'Contato',
                'slug' => 'contato',
                'excerpt' => 'Entre em contato conosco',
                'content' => '<h1>Contato</h1><p>Entre em contato através do formulário abaixo.</p>',
                'seo_title' => 'Contato - Fale Conosco',
                'seo_description' => 'Entre em contato conosco para tirar suas dúvidas',
                'status' => 'published',
                'order' => 5,
            ],
            [
                'title' => 'Política de Privacidade',
                'slug' => 'politica-de-privacidade',
                'excerpt' => 'Nossa política de privacidade e proteção de dados',
                'content' => '<h1>Política de Privacidade</h1><p>Sua privacidade é importante para nós.</p>',
                'seo_title' => 'Política de Privacidade',
                'seo_description' => 'Leia nossa política de privacidade e proteção de dados',
                'status' => 'published',
                'order' => 6,
            ],
            [
                'title' => 'Termos de Uso',
                'slug' => 'termos-de-uso',
                'excerpt' => 'Termos e condições de uso do site',
                'content' => '<h1>Termos de Uso</h1><p>Ao utilizar este site, você concorda com estes termos.</p>',
                'seo_title' => 'Termos de Uso',
                'seo_description' => 'Leia os termos e condições de uso do nosso site',
                'status' => 'published',
                'order' => 7,
            ],
            [
                'title' => 'FAQ - Perguntas Frequentes',
                'slug' => 'faq',
                'excerpt' => 'Respostas para as perguntas mais comuns',
                'content' => '<h1>FAQ</h1><p>Encontre respostas para as perguntas mais frequentes.</p>',
                'seo_title' => 'FAQ - Perguntas Frequentes',
                'seo_description' => 'Tire suas dúvidas com nossas perguntas frequentes',
                'status' => 'published',
                'order' => 8,
            ],
            [
                'title' => 'Página em Rascunho',
                'slug' => 'pagina-rascunho',
                'excerpt' => 'Esta página ainda está sendo elaborada',
                'content' => '<h1>Em construção</h1><p>Esta página está em desenvolvimento.</p>',
                'seo_title' => 'Página em Rascunho',
                'seo_description' => 'Esta página ainda está sendo elaborada',
                'status' => 'draft',
                'order' => 99,
            ],
        ];

        foreach ($sites as $site) {
            $user = User::where('company_id', $site->company_id)->first();

            if (!$user) {
                continue;
            }

            foreach ($pages as $pageData) {
                $slug = $pageData['slug'] . '-' . $site->id;

                // Skip if page already exists
                if (Page::where('slug', $slug)->exists()) {
                    continue;
                }

                Page::create([
                    'site_id' => $site->id,
                    'created_by' => $user->id,
                    'title' => $pageData['title'],
                    'slug' => $slug,
                    'excerpt' => $pageData['excerpt'],
                    'content' => $pageData['content'],
                    'seo_title' => $pageData['seo_title'],
                    'seo_description' => $pageData['seo_description'],
                    'status' => $pageData['status'],
                    'published_at' => $pageData['status'] === 'published' ? now() : null,
                    'order' => $pageData['order'],
                ]);
            }

            $this->command->info("✅ Created " . count($pages) . " pages for site: {$site->name}");
        }
    }
}
