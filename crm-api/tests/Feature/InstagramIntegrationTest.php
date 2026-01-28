<?php

use App\Jobs\Social\SyncInstagramMessagesJob;
use App\Models\Social\InstagramAccount;
use App\Services\Social\InstagramService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->company = \App\Models\CRM\Company::factory()->create();
    $this->user = \App\Models\User::factory()->create([
        'company_id' => $this->company->id,
    ]);
});

test('can get instagram auth url', function () {
    $response = $this->actingAs($this->user)
        ->getJson('/api/social/instagram/auth-url');

    $response->assertOk()
        ->assertJsonStructure(['auth_url'])
        ->assertJson([
            'auth_url' => expect()->stringContaining('api.instagram.com/oauth/authorize'),
        ]);
});

test('can connect instagram account with valid code', function () {
    Http::fake([
        'graph.instagram.com/oauth/access_token' => Http::response([
            'access_token' => 'short_lived_token',
            'user_id' => '123456789',
        ], 200),
        'graph.instagram.com/me*' => Http::response([
            'id' => '123456789',
            'username' => 'testuser',
            'account_type' => 'BUSINESS',
        ], 200),
        'graph.instagram.com/access_token*' => Http::response([
            'access_token' => 'long_lived_token',
            'expires_in' => 5184000, // 60 days
        ], 200),
    ]);

    $response = $this->actingAs($this->user)
        ->postJson('/api/social/instagram/connect', [
            'code' => 'valid_auth_code',
            'company_id' => $this->company->id,
        ]);

    $response->assertCreated()
        ->assertJson([
            'message' => 'Instagram account connected successfully',
            'account' => [
                'username' => 'testuser',
                'account_type' => 'BUSINESS',
                'is_active' => true,
            ],
        ]);

    $this->assertDatabaseHas('instagram_accounts', [
        'company_id' => $this->company->id,
        'username' => 'testuser',
        'instagram_user_id' => '123456789',
        'is_active' => true,
    ]);
});

test('can list connected instagram accounts', function () {
    $account = InstagramAccount::factory()->create([
        'company_id' => $this->company->id,
        'username' => 'testuser',
        'is_active' => true,
    ]);

    $response = $this->actingAs($this->user)
        ->getJson('/api/social/instagram/accounts');

    $response->assertOk()
        ->assertJsonStructure([
            'accounts' => [
                '*' => [
                    'id',
                    'username',
                    'account_type',
                    'is_active',
                    'is_connected',
                    'created_at',
                ],
            ],
        ])
        ->assertJsonFragment(['username' => 'testuser']);
});

test('can disconnect instagram account', function () {
    $account = InstagramAccount::factory()->create([
        'company_id' => $this->company->id,
        'is_active' => true,
    ]);

    $response = $this->actingAs($this->user)
        ->deleteJson("/api/social/instagram/accounts/{$account->id}/disconnect");

    $response->assertOk()
        ->assertJson([
            'message' => 'Instagram account disconnected successfully',
        ]);

    $account->refresh();
    expect($account->is_active)->toBeFalse();
});

test('cannot access other company instagram accounts', function () {
    $otherCompany = \App\Models\CRM\Company::factory()->create();
    $account = InstagramAccount::factory()->create([
        'company_id' => $otherCompany->id,
    ]);

    $response = $this->actingAs($this->user)
        ->getJson("/api/social/instagram/accounts/{$account->id}/messages");

    $response->assertNotFound();
});

test('webhook verification returns challenge for valid token', function () {
    config(['services.instagram.webhook_verify_token' => 'my_verify_token']);

    $response = $this->getJson('/api/webhooks/instagram/verify?' . http_build_query([
        'hub.mode' => 'subscribe',
        'hub.verify_token' => 'my_verify_token',
        'hub.challenge' => 'challenge_string_12345',
    ]));

    expect($response->getContent())->toBe('challenge_string_12345');
});

test('webhook verification fails with invalid token', function () {
    config(['services.instagram.webhook_verify_token' => 'my_verify_token']);

    $response = $this->getJson('/api/webhooks/instagram/verify?' . http_build_query([
        'hub.mode' => 'subscribe',
        'hub.verify_token' => 'wrong_token',
        'hub.challenge' => 'challenge_string_12345',
    ]));

    $response->assertForbidden();
});

test('sync instagram messages job fetches messages', function () {
    $account = InstagramAccount::factory()->create([
        'company_id' => $this->company->id,
        'access_token' => encrypt('valid_token'),
        'token_expires_at' => now()->addDays(30),
        'is_active' => true,
    ]);

    Http::fake([
        'graph.facebook.com/*/me/conversations*' => Http::response([
            'data' => [
                ['id' => 'conv_123'],
            ],
        ], 200),
        'graph.facebook.com/*/conv_123/messages*' => Http::response([
            'data' => [
                [
                    'id' => 'msg_123',
                    'created_time' => now()->toIso8601String(),
                    'from' => ['id' => '987654321', 'username' => 'sender'],
                    'message' => 'Hello!',
                ],
            ],
        ], 200),
    ]);

    $job = new SyncInstagramMessagesJob($account->id);
    $service = app(InstagramService::class);
    $job->handle($service);

    $this->assertDatabaseHas('instagram_messages', [
        'instagram_account_id' => $account->id,
        'message_id' => 'msg_123',
        'content' => 'Hello!',
        'direction' => 'inbound',
    ]);
});
