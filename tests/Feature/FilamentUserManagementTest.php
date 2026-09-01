<?php

use App\Filament\Resources\Users\Pages\CreateUser;
use App\Filament\Resources\Users\Pages\EditUser;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;

test('cms users resource is available only to administrators', function () {
    $administrator = User::factory()->administrator()->create();
    $editor = User::factory()->editor()->create();
    $resourceRoute = route('filament.admin.resources.users.index');

    $this->actingAs($administrator)->get($resourceRoute)->assertSuccessful();
    $this->actingAs($editor)->get($resourceRoute)->assertForbidden();
});

test('cms users can use the built-in profile page', function () {
    $administrator = User::factory()->administrator()->create();
    $editor = User::factory()->editor()->create();
    $profileRoute = route('filament.admin.auth.profile');

    $this->actingAs($administrator)->get($profileRoute)->assertSuccessful();
    $this->actingAs($editor)->get($profileRoute)->assertSuccessful();
});

test('cms profile page links back to the panel', function () {
    $administrator = User::factory()->administrator()->create();

    $response = $this->actingAs($administrator)->get(route('filament.admin.auth.profile'));

    $response
        ->assertSuccessful()
        ->assertSee('Wróć do panelu')
        ->assertSee('href="'.url('/cms').'"', false);
});

test('administrator can create and edit cms users including their password', function () {
    $administrator = User::factory()->administrator()->create();

    $this->actingAs($administrator);

    Livewire::test(CreateUser::class)
        ->fillForm([
            'name' => 'Nowy edytor',
            'email' => 'edytor@example.com',
            'role' => User::ROLE_EDITOR,
            'password' => 'pierwsze-haslo',
            'password_confirmation' => 'pierwsze-haslo',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $createdUser = User::query()->where('email', 'edytor@example.com')->firstOrFail();

    expect(Hash::check('pierwsze-haslo', $createdUser->password))->toBeTrue();

    Livewire::test(EditUser::class, ['record' => $createdUser->getRouteKey()])
        ->fillForm([
            'name' => 'Zmieniony edytor',
            'email' => $createdUser->email,
            'role' => User::ROLE_EDITOR,
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $createdUser->refresh();

    expect($createdUser->name)->toBe('Zmieniony edytor')
        ->and(Hash::check('pierwsze-haslo', $createdUser->password))->toBeTrue();

    Livewire::test(EditUser::class, ['record' => $createdUser->getRouteKey()])
        ->fillForm([
            'name' => $createdUser->name,
            'email' => $createdUser->email,
            'role' => User::ROLE_EDITOR,
            'password' => 'drugie-haslo',
            'password_confirmation' => 'drugie-haslo',
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect(Hash::check('drugie-haslo', $createdUser->fresh()->password))->toBeTrue();
});

test('user deletion is restricted and cannot remove the current or last administrator', function () {
    $administrator = User::factory()->administrator()->create();
    $secondAdministrator = User::factory()->administrator()->create();
    $editor = User::factory()->editor()->create();

    expect($administrator->can('delete', $administrator))->toBeFalse()
        ->and($administrator->can('delete', $secondAdministrator))->toBeTrue()
        ->and($administrator->can('delete', $editor))->toBeTrue()
        ->and($editor->can('delete', $administrator))->toBeFalse();

    $secondAdministrator->delete();

    expect($administrator->can('delete', $administrator))->toBeFalse()
        ->and($administrator->can('delete', $editor))->toBeTrue();
});
