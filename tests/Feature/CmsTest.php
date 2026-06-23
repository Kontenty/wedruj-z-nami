<?php

use App\Filament\Resources\Articles\Pages\CreateArticle;
use App\Filament\Resources\SightseeingObjects\Pages\CreateSightseeingObject;
use App\Filament\Resources\SightseeingObjects\Pages\EditSightseeingObject;
use App\Models\Article;
use App\Models\ObjectType;
use App\Models\SightseeingObject;
use App\Models\User;
use App\Models\Voivodeship;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
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

test('sightseeing object create flow persists polygon geometry', function () {
    $editor = User::factory()->editor()->create();
    $voivodeship = Voivodeship::factory()->create();
    $objectType = ObjectType::factory()->create();

    $this->actingAs($editor);

    Livewire::test(CreateSightseeingObject::class)
        ->fillForm([
            'title' => 'Park krajobrazowy',
            'lead' => 'Krótki opis obiektu.',
            'description' => 'Pełny opis obiektu krajoznawczego.',
            'locality' => 'Kraków',
            'voivodeship_id' => $voivodeship->id,
            'objectTypes' => [$objectType->id],
            'geometry_type' => 'polygon',
            'polygon_wkt' => 'POLYGON((19.9300000 50.0500000,19.9600000 50.0500000,19.9600000 50.0800000,19.9300000 50.0800000,19.9300000 50.0500000))',
            'status' => 'draft',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $object = SightseeingObject::query()->where('title', 'Park krajobrazowy')->firstOrFail();
    $geometry = DB::table('sightseeing_objects')
        ->where('id', $object->id)
        ->selectRaw('ST_GeometryType(geometry) as geometry_type_name')
        ->selectRaw('ST_AsText(geometry) as geometry_wkt')
        ->first();

    expect($object->author_id)->toBe($editor->id)
        ->and($object->latitude)->toBeNull()
        ->and($object->longitude)->toBeNull()
        ->and(strtolower((string) data_get($geometry, 'geometry_type_name')))->toContain('polygon')
        ->and((string) data_get($geometry, 'geometry_wkt'))->toStartWith('POLYGON((');
});

test('sightseeing object create flow rejects invalid polygon geometry', function () {
    $editor = User::factory()->editor()->create();
    $voivodeship = Voivodeship::factory()->create();
    $objectType = ObjectType::factory()->create();

    $this->actingAs($editor);

    Livewire::test(CreateSightseeingObject::class)
        ->fillForm([
            'title' => 'Niepoprawny poligon',
            'lead' => 'Krótki opis obiektu.',
            'description' => 'Pełny opis obiektu krajoznawczego.',
            'locality' => 'Kraków',
            'voivodeship_id' => $voivodeship->id,
            'objectTypes' => [$objectType->id],
            'geometry_type' => 'polygon',
            'polygon_wkt' => 'POLYGON((19.93 50.05,19.96 50.08,19.93 50.08,19.96 50.05))',
            'status' => 'draft',
        ])
        ->call('create')
        ->assertHasFormErrors(['polygon_wkt']);
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

test('published sightseeing object edit cannot remove all images', function () {
    Storage::fake('public');

    $editor = User::factory()->editor()->create();
    $voivodeship = Voivodeship::factory()->create();
    $objectType = ObjectType::factory()->create();
    $object = SightseeingObject::factory()->published()->create([
        'voivodeship_id' => $voivodeship->id,
        'status' => 'published',
        'published' => true,
        'published_at' => now(),
    ]);
    $object->objectTypes()->sync($objectType->id);
    $object->addMedia(UploadedFile::fake()->image('published.jpg', 200, 150))->toMediaCollection('images');

    $this->actingAs($editor);

    Livewire::test(EditSightseeingObject::class, ['record' => $object->getRouteKey()])
        ->fillForm([
            'title' => $object->title,
            'lead' => $object->lead,
            'description' => $object->description,
            'locality' => $object->locality,
            'voivodeship_id' => $voivodeship->id,
            'objectTypes' => [$objectType->id],
            'geometry_type' => 'point',
            'latitude' => 50.0614,
            'longitude' => 19.9372,
            'status' => 'published',
            'images' => [],
        ])
        ->call('save')
        ->assertHasFormErrors(['images']);

    $object->refresh();

    expect($object->getMedia('images'))->toHaveCount(1);
});

test('editing sightseeing object removes images deleted from the gallery', function () {
    Storage::fake('public');

    $editor = User::factory()->editor()->create();
    $voivodeship = Voivodeship::factory()->create();
    $objectType = ObjectType::factory()->create();
    $object = SightseeingObject::factory()->create([
        'voivodeship_id' => $voivodeship->id,
        'status' => 'draft',
    ]);
    $object->objectTypes()->sync($objectType->id);

    $first = $object->addMedia(UploadedFile::fake()->image('first.jpg', 200, 150))->toMediaCollection('images');
    $second = $object->addMedia(UploadedFile::fake()->image('second.jpg', 200, 150))->toMediaCollection('images');

    expect($object->getMedia('images'))->toHaveCount(2);

    $this->actingAs($editor);

    Livewire::test(EditSightseeingObject::class, ['record' => $object->getRouteKey()])
        ->fillForm([
            'title' => $object->title,
            'lead' => $object->lead,
            'description' => $object->description,
            'locality' => $object->locality,
            'voivodeship_id' => $voivodeship->id,
            'objectTypes' => [$objectType->id],
            'geometry_type' => 'point',
            'latitude' => 50.0614,
            'longitude' => 19.9372,
            'status' => 'draft',
            'images' => [$second->getPathRelativeToRoot()],
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $object->refresh();

    expect($object->getMedia('images'))->toHaveCount(1)
        ->and($object->getMedia('images')->first()->id)->toBe($second->id);
});

test('editing sightseeing object persists image reorder', function () {
    Storage::fake('public');

    $editor = User::factory()->editor()->create();
    $voivodeship = Voivodeship::factory()->create();
    $objectType = ObjectType::factory()->create();
    $object = SightseeingObject::factory()->create([
        'voivodeship_id' => $voivodeship->id,
        'status' => 'draft',
    ]);
    $object->objectTypes()->sync($objectType->id);

    $first = $object->addMedia(UploadedFile::fake()->image('first.jpg', 200, 150))->toMediaCollection('images');
    $second = $object->addMedia(UploadedFile::fake()->image('second.jpg', 200, 150))->toMediaCollection('images');

    expect($object->getMedia('images')->pluck('id')->all())->toBe([$first->id, $second->id]);

    $this->actingAs($editor);

    Livewire::test(EditSightseeingObject::class, ['record' => $object->getRouteKey()])
        ->fillForm([
            'title' => $object->title,
            'lead' => $object->lead,
            'description' => $object->description,
            'locality' => $object->locality,
            'voivodeship_id' => $voivodeship->id,
            'objectTypes' => [$objectType->id],
            'geometry_type' => 'point',
            'latitude' => 50.0614,
            'longitude' => 19.9372,
            'status' => 'draft',
            'images' => [$second->getPathRelativeToRoot(), $first->getPathRelativeToRoot()],
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $object->refresh();

    expect($object->getMedia('images')->pluck('id')->all())->toBe([$second->id, $first->id]);
});

test('editing sightseeing object with new images does not throw on reorder', function () {
    Storage::fake('public');

    $editor = User::factory()->editor()->create();
    $voivodeship = Voivodeship::factory()->create();
    $objectType = ObjectType::factory()->create();
    $object = SightseeingObject::factory()->create([
        'voivodeship_id' => $voivodeship->id,
        'status' => 'draft',
    ]);
    $object->objectTypes()->sync($objectType->id);

    $first = $object->addMedia(UploadedFile::fake()->image('existing.jpg', 200, 150))->toMediaCollection('images');

    expect($object->getMedia('images'))->toHaveCount(1);

    $newUpload = UploadedFile::fake()->image('new-photo.jpg', 200, 150);
    $newPath = 'cms/object-images/'.$newUpload->hashName();
    Storage::disk('public')->put($newPath, file_get_contents($newUpload->getRealPath()));

    $this->actingAs($editor);

    Livewire::test(EditSightseeingObject::class, ['record' => $object->getRouteKey()])
        ->fillForm([
            'title' => $object->title,
            'lead' => $object->lead,
            'description' => $object->description,
            'locality' => $object->locality,
            'voivodeship_id' => $voivodeship->id,
            'objectTypes' => [$objectType->id],
            'geometry_type' => 'point',
            'latitude' => 50.0614,
            'longitude' => 19.9372,
            'status' => 'draft',
            'images' => [$first->getPathRelativeToRoot(), $newPath],
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $object->refresh();

    expect($object->getMedia('images'))->toHaveCount(2);
});

test('editing sightseeing object can switch geometry to polygon', function () {
    $editor = User::factory()->editor()->create();
    $voivodeship = Voivodeship::factory()->create();
    $objectType = ObjectType::factory()->create();
    $object = SightseeingObject::factory()->point(50.0614, 19.9372)->create([
        'voivodeship_id' => $voivodeship->id,
        'status' => 'draft',
    ]);
    $object->objectTypes()->sync($objectType->id);

    $this->actingAs($editor);

    Livewire::test(EditSightseeingObject::class, ['record' => $object->getRouteKey()])
        ->fillForm([
            'title' => $object->title,
            'lead' => $object->lead,
            'description' => $object->description,
            'locality' => $object->locality,
            'voivodeship_id' => $voivodeship->id,
            'objectTypes' => [$objectType->id],
            'geometry_type' => 'polygon',
            'polygon_wkt' => 'POLYGON((19.9300000 50.0500000,19.9600000 50.0500000,19.9600000 50.0800000,19.9300000 50.0800000,19.9300000 50.0500000))',
            'status' => 'draft',
            'images' => [],
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $object->refresh();

    $geometry = DB::table('sightseeing_objects')
        ->where('id', $object->id)
        ->selectRaw('ST_GeometryType(geometry) as geometry_type_name')
        ->selectRaw('ST_AsText(geometry) as geometry_wkt')
        ->first();

    expect($object->latitude)->toBeNull()
        ->and($object->longitude)->toBeNull()
        ->and(strtolower((string) data_get($geometry, 'geometry_type_name')))->toContain('polygon')
        ->and((string) data_get($geometry, 'geometry_wkt'))->toStartWith('POLYGON((');
});
