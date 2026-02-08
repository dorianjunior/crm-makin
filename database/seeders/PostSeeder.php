<?php

namespace Database\Seeders;

use App\Models\CMS\Post;
use App\Models\CMS\PostCategory;
use App\Models\CMS\Site;
use App\Models\User;
use Illuminate\Database\Seeder;

class PostSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $sites = Site::where('active', true)->get();

        $posts = [
            [
                'title' => '5 Tendências de Tecnologia para 2026',
                'slug' => '5-tendencias-tecnologia-2026',
                'excerpt' => 'Descubra as principais tendências tecnológicas que vão dominar o mercado em 2026.',
                'content' => '<h2>As tecnologias que estão moldando o futuro</h2><p>A inteligência artificial, blockchain e computação quântica estão revolucionando o mercado...</p>',
                'seo_title' => '5 Tendências de Tecnologia para 2026 | Blog',
                'seo_description' => 'Conheça as 5 principais tendências de tecnologia que vão transformar o mercado em 2026',
                'status' => 'published',
                'category_slug' => 'tecnologia',
                'tags' => ['tecnologia', 'tendências', 'inovação', 'futuro'],
            ],
            [
                'title' => 'Como Aumentar suas Vendas com Marketing Digital',
                'slug' => 'aumentar-vendas-marketing-digital',
                'excerpt' => 'Estratégias práticas de marketing digital para impulsionar suas vendas.',
                'content' => '<h2>Marketing Digital que Converte</h2><p>Aprenda as melhores estratégias para aumentar suas vendas online...</p>',
                'seo_title' => 'Como Aumentar Vendas com Marketing Digital',
                'seo_description' => 'Descubra estratégias comprovadas de marketing digital para aumentar suas vendas',
                'status' => 'published',
                'category_slug' => 'marketing',
                'tags' => ['marketing', 'vendas', 'digital', 'estratégia'],
            ],
            [
                'title' => 'Design Thinking: O que é e como aplicar',
                'slug' => 'design-thinking-como-aplicar',
                'excerpt' => 'Entenda o conceito de Design Thinking e como aplicá-lo no seu negócio.',
                'content' => '<h2>Design Thinking na Prática</h2><p>O Design Thinking é uma abordagem centrada no usuário...</p>',
                'seo_title' => 'Design Thinking: Guia Completo',
                'seo_description' => 'Aprenda o que é Design Thinking e como aplicar no seu negócio',
                'status' => 'published',
                'category_slug' => 'design',
                'tags' => ['design', 'design thinking', 'metodologia', 'inovação'],
            ],
            [
                'title' => 'Introdução ao React: Guia para Iniciantes',
                'slug' => 'introducao-react-guia-iniciantes',
                'excerpt' => 'Um guia completo para você começar a desenvolver com React.',
                'content' => '<h2>Começando com React</h2><p>React é uma biblioteca JavaScript para construir interfaces de usuário...</p>',
                'seo_title' => 'Guia React para Iniciantes',
                'seo_description' => 'Aprenda React do zero com este guia completo para iniciantes',
                'status' => 'published',
                'category_slug' => 'desenvolvimento',
                'tags' => ['react', 'javascript', 'frontend', 'desenvolvimento'],
            ],
            [
                'title' => 'Como Escalar seu Negócio em 2026',
                'slug' => 'escalar-negocio-2026',
                'excerpt' => 'Estratégias comprovadas para escalar seu negócio e aumentar seu faturamento.',
                'content' => '<h2>Escalando com Inteligência</h2><p>Escalar um negócio requer planejamento estratégico...</p>',
                'seo_title' => 'Como Escalar seu Negócio em 2026',
                'seo_description' => 'Descubra estratégias para escalar seu negócio e multiplicar seus resultados',
                'status' => 'published',
                'category_slug' => 'negocios',
                'tags' => ['negócios', 'escalabilidade', 'crescimento', 'estratégia'],
            ],
            [
                'title' => 'SEO em 2026: O que Mudou?',
                'slug' => 'seo-2026-mudancas',
                'excerpt' => 'As principais mudanças no SEO e como se adaptar às novas diretrizes.',
                'content' => '<h2>A Evolução do SEO</h2><p>O SEO continua evoluindo e em 2026 temos novas práticas...</p>',
                'seo_title' => 'SEO em 2026: Guia Atualizado',
                'seo_description' => 'Conheça as mudanças no SEO em 2026 e como otimizar seu site',
                'status' => 'published',
                'category_slug' => 'marketing',
                'tags' => ['seo', 'otimização', 'google', 'marketing'],
            ],
            [
                'title' => 'UI/UX: Princípios Fundamentais',
                'slug' => 'ui-ux-principios-fundamentais',
                'excerpt' => 'Os princípios essenciais de UI/UX que todo designer deve conhecer.',
                'content' => '<h2>Design de Interface e Experiência</h2><p>A experiência do usuário é fundamental...</p>',
                'seo_title' => 'UI/UX: Princípios e Boas Práticas',
                'seo_description' => 'Aprenda os princípios fundamentais de UI/UX Design',
                'status' => 'published',
                'category_slug' => 'design',
                'tags' => ['ui', 'ux', 'design', 'interface'],
            ],
            [
                'title' => 'Python vs JavaScript: Qual Aprender Primeiro?',
                'slug' => 'python-vs-javascript',
                'excerpt' => 'Comparação entre Python e JavaScript para iniciantes em programação.',
                'content' => '<h2>Escolhendo sua Primeira Linguagem</h2><p>Tanto Python quanto JavaScript são excelentes...</p>',
                'seo_title' => 'Python vs JavaScript: Guia Completo',
                'seo_description' => 'Compare Python e JavaScript e descubra qual linguagem aprender primeiro',
                'status' => 'published',
                'category_slug' => 'desenvolvimento',
                'tags' => ['python', 'javascript', 'programação', 'iniciantes'],
            ],
            [
                'title' => 'Transformação Digital nas Empresas',
                'slug' => 'transformacao-digital-empresas',
                'excerpt' => 'Como a transformação digital está mudando o cenário corporativo.',
                'content' => '<h2>A Era Digital nos Negócios</h2><p>A transformação digital não é mais uma opção...</p>',
                'seo_title' => 'Transformação Digital: Guia Empresarial',
                'seo_description' => 'Entenda como a transformação digital impacta seu negócio',
                'status' => 'published',
                'category_slug' => 'negocios',
                'tags' => ['transformação digital', 'tecnologia', 'negócios', 'inovação'],
            ],
            [
                'title' => 'IA Generativa: ChatGPT e o Futuro',
                'slug' => 'ia-generativa-chatgpt-futuro',
                'excerpt' => 'Explorando o impacto da IA generativa e ferramentas como ChatGPT.',
                'content' => '<h2>A Revolução da IA Generativa</h2><p>A inteligência artificial generativa está transformando a forma como trabalhamos...</p>',
                'seo_title' => 'IA Generativa: ChatGPT e o Futuro da Tecnologia',
                'seo_description' => 'Descubra como a IA generativa está mudando o mundo',
                'status' => 'published',
                'category_slug' => 'tecnologia',
                'tags' => ['ia', 'chatgpt', 'inteligência artificial', 'futuro'],
            ],
            [
                'title' => 'Post em Rascunho - Não Publicado',
                'slug' => 'post-rascunho',
                'excerpt' => 'Este post ainda está sendo elaborado.',
                'content' => '<p>Conteúdo em desenvolvimento...</p>',
                'seo_title' => 'Post em Rascunho',
                'seo_description' => 'Este post ainda está sendo elaborado',
                'status' => 'draft',
                'category_slug' => 'noticias',
                'tags' => ['rascunho'],
            ],
            [
                'title' => 'Novidades da Empresa - Janeiro 2026',
                'slug' => 'novidades-empresa-janeiro-2026',
                'excerpt' => 'Confira as principais novidades e atualizações da empresa neste mês.',
                'content' => '<h2>Novidades de Janeiro</h2><p>Este mês trouxe grandes novidades para nossa empresa...</p>',
                'seo_title' => 'Novidades Janeiro 2026',
                'seo_description' => 'Acompanhe as novidades da empresa em janeiro de 2026',
                'status' => 'published',
                'category_slug' => 'noticias',
                'tags' => ['novidades', 'empresa', 'notícias', '2026'],
            ],
        ];

        foreach ($sites as $site) {
            $user = User::where('company_id', $site->company_id)->first();

            if (!$user) {
                continue;
            }

            foreach ($posts as $index => $postData) {
                $category = PostCategory::where('site_id', $site->id)
                    ->where('slug', $postData['category_slug'])
                    ->first();

                if (!$category) {
                    continue;
                }

                $slug = $postData['slug'] . '-' . $site->id;

                // Skip if post already exists
                if (Post::where('slug', $slug)->exists()) {
                    continue;
                }

                Post::create([
                    'site_id' => $site->id,
                    'category_id' => $category->id,
                    'created_by' => $user->id,
                    'title' => $postData['title'],
                    'slug' => $slug,
                    'excerpt' => $postData['excerpt'],
                    'content' => $postData['content'],
                    'seo_title' => $postData['seo_title'],
                    'seo_description' => $postData['seo_description'],
                    'status' => $postData['status'],
                    'published_at' => $postData['status'] === 'published' ? now()->subDays(rand(1, 30)) : null,
                    'tags' => $postData['tags'],
                ]);
            }

            $this->command->info("✅ Created " . count($posts) . " posts for site: {$site->name}");
        }
    }
}
