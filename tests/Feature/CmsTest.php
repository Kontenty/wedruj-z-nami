<?php

use App\Filament\Resources\Articles\Pages\CreateArticle;
use App\Filament\Resources\SightseeingObjects\Pages\CreateSightseeingObject;
use App\Models\Article;
use App\Models\ObjectType;
use App\Models\SightseeingObject;
use App\Models\User;
use App\Models\Voivodeship;
use Livewire\Livewire;

test('cms login page renders and dashboard requires authentication', function () {
    $this->get('/cms/login')->assertSuccessful();

    $this->get('/cms')->assertRedirect('/cms/login');
});

test('only explicit cms roles can access the panel', function () {
    $administrator = User::factory()->administrator()->create();
    $editor = User::factory()->editor()->create();
    $regularUser = User::factory()->create();

    expect($administrator->hasCmsAccess())->toBeTrue()
        ->and($editor->hasCmsAccess())->toBeTrue()
        ->and($regularUser->hasCmsAccess())->toBeFalse();

    $this->actingAs($administrator)->get('/cms')->assertSuccessful();
    $this->actingAs($editor)->get('/cms')->assertSuccessful();
    $this->actingAs($regularUser)->get('/cms')->assertForbidden();
});

test('administrator can delete content while editor cannot', function () {
    $administrator = User::factory()->administrator()->create();
    $editor = User::factory()->editor()->create();
    $object = SightseeingObject::factory()->create();
    $article = Article::factory()->create();
    $objectType = ObjectType::factory()->create();

    expect($administrator->can('delete', $object))->toBeTrue()
        ->and($administrator->can('delete', $article))->toBeTrue()
        ->and($administrator->can('delete', $objectType))->toBeTrue()
        ->and($editor->can('delete', $object))->toBeFalse()
        ->and($editor->can('delete', $article))->toBeFalse()
        ->and($editor->can('delete', $objectType))->toBeFalse()
        ->and($editor->can('update', $object))->toBeTrue()
        ->and($editor->can('create', Article::class))->toBeTrue();
});

test('article create flow assigns the authenticated cms author', function () {
    $editor = User::factory()->editor()->create();

    $this->actingAs($editor);

    Livewire::test(CreateArticle::class)
        ->fillForm([
            'title' => 'Nowy szlak w katalogu',
            'excerpt' => 'Krótka zajawka aktualności.',
            'body' => 'Treść aktualności w Markdown.',
            'status' => 'published',
            'is_featured' => true,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas('articles', [
        'title' => 'Nowy szlak w katalogu',
        'author_id' => $editor->id,
        'status' => 'published',
        'published' => true,
        'is_featured' => true,
    ]);
});

test('sightseeing object create flow validates geometry and assigns author', function () {
    $editor = User::factory()->editor()->create();
    $voivodeship = Voivodeship::factory()->create();
    $objectType = ObjectType::factory()->create();

    $this->actingAs($editor);

    Livewire::test(CreateSightseeingObject::class)
        ->fillForm([
            'title' => 'Zamek testowy',
            'lead' => 'Krótki opis obiektu.',
            'description' => 'Pełny opis obiektu krajoznawczego.',
            'locality' => 'Kraków',
            'voivodeship_id' => $voivodeship->id,
            'objectTypes' => [$objectType->id],
            'geometry_type' => 'point',
            'latitude' => 50.0614,
            'longitude' => 19.9372,
            'status' => 'draft',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $object = SightseeingObject::query()->where('title', 'Zamek testowy')->firstOrFail();

    expect($object->author_id)->toBe($editor->id)
        ->and($object->objectTypes()->pluck('object_types.id')->all())->toBe([$objectType->id]);
});

test('published sightseeing objects require an image in the cms form', function () {
    $editor = User::factory()->editor()->create();
    $voivodeship = Voivodeship::factory()->create();
    $objectType = ObjectType::factory()->create();

    $this->actingAs($editor);

    Livewire::test(CreateSightseeingObject::class)
        ->fillForm([
            'title' => 'Obiekt bez zdjęcia',
            'lead' => 'Krótki opis obiektu.',
            'description' => 'Pełny opis obiektu krajoznawczego.',
            'locality' => 'Kraków',
            'voivodeship_id' => $voivodeship->id,
            'objectTypes' => [$objectType->id],
            'geometry_type' => 'point',
            'latitude' => 50.0614,
            'longitude' => 19.9372,
            'status' => 'published',
        ])
        ->call('create')
        ->assertHasFormErrors(['images']);
});

test('object type hierarchy blocks loops and more than three levels', function () {
    $root = ObjectType::factory()->create();
    $child = ObjectType::factory()->childOf($root)->create();
    $grandchild = ObjectType::factory()->childOf($child)->create();

    expect($root->wouldCreateParentLoop($grandchild->id))->toBeTrue()
        ->and((new ObjectType)->wouldExceedMaximumDepth($grandchild->id))->toBeTrue()
        ->and((new ObjectType)->wouldExceedMaximumDepth($child->id))->toBeFalse();
});
