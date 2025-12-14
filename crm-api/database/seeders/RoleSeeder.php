<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\Permission;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Admin role - todas as permissões
        $admin = Role::create(['name' => 'Admin']);
        $admin->permissions()->attach(Permission::all());

        // Manager role - gerenciamento completo exceto configurações
        $manager = Role::create(['name' => 'Manager']);
        $managerPermissions = Permission::whereNotIn('name', [
            'companies.create',
            'companies.delete',
            'settings.edit',
        ])->get();
        $manager->permissions()->attach($managerPermissions);

        // Sales role - vendas e leads
        $sales = Role::create(['name' => 'Sales']);
        $salesPermissions = Permission::whereIn('name', [
            'leads.view',
            'leads.create',
            'leads.edit',
            'leads.assign',
            'activities.view',
            'activities.create',
            'activities.edit',
            'tasks.view',
            'tasks.create',
            'tasks.edit',
            'pipelines.view',
            'products.view',
            'proposals.view',
            'proposals.create',
            'proposals.edit',
            'emails.view',
            'emails.send',
            'whatsapp.view',
            'whatsapp.send',
            'files.view',
            'files.upload',
        ])->get();
        $sales->permissions()->attach($salesPermissions);

        // Support role - suporte e atendimento
        $support = Role::create(['name' => 'Support']);
        $supportPermissions = Permission::whereIn('name', [
            'leads.view',
            'leads.edit',
            'activities.view',
            'activities.create',
            'activities.edit',
            'tasks.view',
            'tasks.create',
            'tasks.edit',
            'emails.view',
            'emails.send',
            'whatsapp.view',
            'whatsapp.send',
            'files.view',
        ])->get();
        $support->permissions()->attach($supportPermissions);

        // Viewer role - apenas visualização
        $viewer = Role::create(['name' => 'Viewer']);
        $viewerPermissions = Permission::where('name', 'like', '%.view')->get();
        $viewer->permissions()->attach($viewerPermissions);

        $this->command->info('Roles created successfully!');
    }
}
