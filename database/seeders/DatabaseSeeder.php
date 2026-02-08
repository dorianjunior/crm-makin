<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Order matters! Follow dependencies
        $this->call([
            PermissionSeeder::class,
            RoleSeeder::class,
            CompanySeeder::class,
            UserSeeder::class,
            PipelineSeeder::class,
            LeadSourceSeeder::class,
            ProductSeeder::class,
            MessageTemplateSeeder::class,
            SiteSeeder::class,
            PostCategorySeeder::class,
            PageSeeder::class,
            PostSeeder::class,
            LeadSeeder::class,
            ProposalSeeder::class,
            TaskSeeder::class,
            ActivitySeeder::class,
        ]);

        $this->command->info('✅ Database seeded successfully!');
    }
}
