<?php

namespace Tests;

use App\Models\CMS\Site;
use App\Models\CRM\Company;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Create default fixtures for tests
        if (! Role::find(1)) {
            Role::factory()->create(['id' => 1, 'name' => 'Admin']);
        }

        if (! Company::find(1)) {
            Company::factory()->create(['id' => 1, 'name' => 'Test Company']);
        }

        if (! User::find(1)) {
            User::factory()->create([
                'id' => 1,
                'name' => 'Test User',
                'email' => 'test@test.com',
                'role_id' => 1,
                'company_id' => 1,
            ]);
        }

        if (! Site::find(1)) {
            Site::factory()->create([
                'id' => 1,
                'company_id' => 1,
                'domain' => 'test.example.com',
                'name' => 'Test Site',
            ]);
        }
    }
}
