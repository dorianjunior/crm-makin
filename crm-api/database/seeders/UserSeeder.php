<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Company;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $demoCompany = Company::where('name', 'Demo Company')->first();
        $adminRole = Role::where('name', 'Admin')->first();
        $managerRole = Role::where('name', 'Manager')->first();
        $salesRole = Role::where('name', 'Sales')->first();
        $supportRole = Role::where('name', 'Support')->first();

        // Admin user
        User::create([
            'company_id' => $demoCompany->id,
            'role_id' => $adminRole->id,
            'name' => 'Admin User',
            'email' => 'admin@demo.com',
            'password' => Hash::make('password'),
            'active' => true,
        ]);

        // Manager user
        User::create([
            'company_id' => $demoCompany->id,
            'role_id' => $managerRole->id,
            'name' => 'Manager User',
            'email' => 'manager@demo.com',
            'password' => Hash::make('password'),
            'active' => true,
        ]);

        // Sales users
        User::create([
            'company_id' => $demoCompany->id,
            'role_id' => $salesRole->id,
            'name' => 'John Sales',
            'email' => 'john@demo.com',
            'password' => Hash::make('password'),
            'active' => true,
        ]);

        User::create([
            'company_id' => $demoCompany->id,
            'role_id' => $salesRole->id,
            'name' => 'Jane Sales',
            'email' => 'jane@demo.com',
            'password' => Hash::make('password'),
            'active' => true,
        ]);

        // Support user
        User::create([
            'company_id' => $demoCompany->id,
            'role_id' => $supportRole->id,
            'name' => 'Support User',
            'email' => 'support@demo.com',
            'password' => Hash::make('password'),
            'active' => true,
        ]);

        $this->command->info('Users created successfully!');
    }
}
