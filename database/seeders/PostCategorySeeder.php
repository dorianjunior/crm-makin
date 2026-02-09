<?php

namespace Database\Seeders;

use App\Models\CMS\PostCategory;
use App\Models\CMS\Site;
use Illuminate\Database\Seeder;

class PostCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $sites = Site::all();

        $categories = [
            [
                'name' => 'Tecnologia',
                'slug' => 'tecnologia',
                'description' => 'Novidades e tendências em tecnologia',
                'order' => 1,
            ],
            [
                'name' => 'Negócios',
                'slug' => 'negocios',
                'description' => 'Estratégias e dicas para seu negócio',
                'order' => 2,
            ],
            [
                'name' => 'Marketing',
                'slug' => 'marketing',
                'description' => 'Marketing digital e estratégias de divulgação',
                'order' => 3,
            ],
            [
                'name' => 'Design',
                'slug' => 'design',
                'description' => 'Tendências em design e UX/UI',
                'order' => 4,
            ],
            [
                'name' => 'Desenvolvimento',
                'slug' => 'desenvolvimento',
                'description' => 'Programação e desenvolvimento de software',
                'order' => 5,
            ],
            [
                'name' => 'Notícias',
                'slug' => 'noticias',
                'description' => 'Últimas notícias da empresa',
                'order' => 6,
            ],
        ];

        foreach ($sites as $site) {
            foreach ($categories as $categoryData) {
                PostCategory::create([
                    'site_id' => $site->id,
                    'name' => $categoryData['name'],
                    'slug' => $categoryData['slug'],
                    'description' => $categoryData['description'],
                    'order' => $categoryData['order'],
                ]);
            }

            $this->command->info('✅ Created '.count($categories)." categories for site: {$site->name}");
        }
    }
}
