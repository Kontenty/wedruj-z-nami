<?php

use App\Models\User;
use Inertia\Support\SessionKey;
use Inertia\Testing\AssertableInertia as Assert;

test('guests are redirected to the login page', function () {
    $this->get(route('deployment.index'))->assertRedirect(route('login'));
    $this->post(route('deployment.store'), ['action' => 'diagnostics'])->assertRedirect(route('login'));
});

test('non-administrators cannot discover the panel', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->get(route('deployment.index'))->assertNotFound();
    $this->actingAs($user)->post(route('deployment.store'), ['action' => 'diagnostics'])->assertNotFound();
});

test('editors cannot discover the panel', function () {
    $editor = User::factory()->editor()->create();

    $this->actingAs($editor)->get(route('deployment.index'))->assertNotFound();
    $this->actingAs($editor)->post(route('deployment.store'), ['action' => 'diagnostics'])->assertNotFound();
});

test('administrators can view the panel with diagnostics', function () {
    $admin = User::factory()->administrator()->create();

    $this->actingAs($admin)
        ->get(route('deployment.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Deployment/Index')
            ->has('actions')
            ->where('diagnostics', fn (string $diagnostics) => str_contains($diagnostics, 'PHP:'))
        );
});

test('running an action requires recent password confirmation', function () {
    $admin = User::factory()->administrator()->create();

    $this->actingAs($admin)
        ->post(route('deployment.store'), ['action' => 'database_check'])
        ->assertRedirect(route('password.confirm'));
});

test('administrators can run a read-only action and it is audited', function () {
    $admin = User::factory()->administrator()->create();
    $logFile = storage_path('logs/deployment.log');
    @unlink($logFile);

    $this->actingAs($admin)
        ->withSession(['auth.password_confirmed_at' => time()])
        ->from(route('deployment.index'))
        ->post(route('deployment.store'), ['action' => 'database_check'])
        ->assertRedirect(route('deployment.index'));

    expect(file_exists($logFile))->toBeTrue('deployment audit log was not written');
    expect(file_get_contents($logFile))
        ->toContain('action=database_check')
        ->toContain('success=1')
        ->toContain("user={$admin->id}");
});

test('unknown actions are rejected by validation', function () {
    $admin = User::factory()->administrator()->create();

    $this->actingAs($admin)
        ->withSession(['auth.password_confirmed_at' => time()])
        ->post(route('deployment.store'), ['action' => 'create_konrad_user'])
        ->assertInvalid('action');
});

test('failure output never leaks exception traces', function () {
    $admin = User::factory()->administrator()->create();

    config(['database.default' => 'invalid-connection']);

    try {
        $response = $this->actingAs($admin)
            ->withSession(['auth.password_confirmed_at' => time()])
            ->from(route('deployment.index'))
            ->post(route('deployment.store'), ['action' => 'database_check'])
            ->assertRedirect(route('deployment.index'));

        // Inertia shares flashed data with the next page under the flash key.
        $result = $response->getSession()->get(SessionKey::FLASH_DATA)['deploymentResult'];
    } finally {
        config(['database.default' => 'mariadb']);
    }

    expect($result['success'])->toBeFalse();
    expect($result['output'])
        ->toContain('Numer referencyjny')
        ->not->toContain('#0')
        ->not->toContain('Trace');
});
