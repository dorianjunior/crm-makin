<?php

namespace Database\Seeders;

use App\Models\Admin\Permission;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            // Company permissions
            'companies.view',
            'companies.create',
            'companies.edit',
            'companies.delete',

            // User permissions
            'users.view',
            'users.create',
            'users.edit',
            'users.delete',

            // Lead permissions
            'leads.view',
            'leads.create',
            'leads.edit',
            'leads.delete',
            'leads.assign',

            // Activity permissions
            'activities.view',
            'activities.create',
            'activities.edit',
            'activities.delete',

            // Task permissions
            'tasks.view',
            'tasks.create',
            'tasks.edit',
            'tasks.delete',

            // Pipeline permissions
            'pipelines.view',
            'pipelines.create',
            'pipelines.edit',
            'pipelines.delete',

            // Product permissions
            'products.view',
            'products.create',
            'products.edit',
            'products.delete',

            // Proposal permissions
            'proposals.view',
            'proposals.create',
            'proposals.edit',
            'proposals.delete',

            // Communication permissions
            'emails.view',
            'emails.send',
            'whatsapp.view',
            'whatsapp.send',

            // File permissions
            'files.view',
            'files.upload',
            'files.delete',

            // Report permissions
            'reports.view',
            'reports.export',

            // Settings permissions
            'settings.view',
            'settings.edit',
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        $this->command->info('Permissions created successfully!');
    }
}
