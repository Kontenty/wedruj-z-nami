<?php

use App\Filament\Resources\Articles\Pages\CreateArticle;
use App\Filament\Resources\SightseeingObjects\Pages\CreateSightseeingObject;
use App\Filament\Resources\SightseeingObjects\Pages\EditSightseeingObject;
use App\Models\Article;
use App\Models\Locality;
use App\Models\ObjectType;
use App\Models\SightseeingObject;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

const SHORT_OBJECT_DESCRIPTION = 'Krótki opis obiektu.';
const FULL_OBJECT_DESCRIPTION = 'Pełny opis obiektu krajoznawczego.';
const DEFAULT_POLYGON_WKT = 'POLYGON((19.9300000 50.0500000,19.9600000 50.0500000,19.9600000 50.0800000,19.9300000 50.0800000,19.9300000 50.0500000))';
const GEOMETRY_TYPE_SQL = 'ST_GeometryType(geometry) as geometry_type_name';
const GEOMETRY_WKT_SQL = 'ST_AsText(geometry) as geometry_wkt';
const OSM_ID = '654321';

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
    $locality = Locality::factory()->create();
    $objectType = ObjectType::factory()->create();

    $this->actingAs($editor);

    Livewire::test(CreateSightseeingObject::class)
        ->fillForm([
            'title' => 'Zamek testowy',
            'lead' => SHORT_OBJECT_DESCRIPTION,
            'description' => FULL_OBJECT_DESCRIPTION,
            'locality_id' => $locality->id,
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
        ->and($object->slug)->toBe('zamek-testowy')
        ->and($object->objectTypes()->pluck('object_types.id')->all())->toBe([$objectType->id]);
});

test('sightseeing object creation rolls back when importing an image fails', function () {
    Storage::fake('public');

    $editor = User::factory()->editor()->create();
    $locality = Locality::factory()->create();
    $objectType = ObjectType::factory()->create();
    $upload = UploadedFile::fake()->image('first.jpg', 200, 150);
    $validPath = 'cms/object-images/first.jpg';

    Storage::disk('public')->put($validPath, file_get_contents($upload->getRealPath()));
    $this->actingAs($editor);

    expect(fn () => Livewire::test(CreateSightseeingObject::class)
        ->fillForm([
            'title' => 'Nieudany import zdjęć',
            'lead' => SHORT_OBJECT_DESCRIPTION,
            'description' => FULL_OBJECT_DESCRIPTION,
            'locality_id' => $locality->id,
            'objectTypes' => [$objectType->id],
            'geometry_type' => 'point',
            'latitude' => 50.0614,
            'longitude' => 19.9372,
            'status' => 'draft',
            'images' => [
                ['path' => [$validPath]],
                ['path' => ['cms/object-images/missing.jpg']],
            ],
        ])
        ->call('create'))->toThrow(Exception::class);

    expect(SightseeingObject::query()->where('title', 'Nieudany import zdjęć')->exists())->toBeFalse()
        ->and(Media::query()->where('model_type', SightseeingObject::class)->exists())->toBeFalse()
        ->and(Storage::disk('public')->allFiles())->toBe([]);
});

test('sightseeing object create flow persists polygon geometry', function () {
    $editor = User::factory()->editor()->create();
    $locality = Locality::factory()->create();
    $objectType = ObjectType::factory()->create();

    $this->actingAs($editor);

    Livewire::test(CreateSightseeingObject::class)
        ->fillForm([
            'title' => 'Park krajobrazowy',
            'lead' => SHORT_OBJECT_DESCRIPTION,
            'description' => FULL_OBJECT_DESCRIPTION,
            'locality_id' => $locality->id,
            'objectTypes' => [$objectType->id],
            'geometry_type' => 'polygon',
            'polygon_wkt' => DEFAULT_POLYGON_WKT,
            'status' => 'draft',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $object = SightseeingObject::query()->where('title', 'Park krajobrazowy')->firstOrFail();
    $geometry = DB::table('sightseeing_objects')
        ->where('id', $object->id)
        ->selectRaw(GEOMETRY_TYPE_SQL)
        ->selectRaw(GEOMETRY_WKT_SQL)
        ->first();

    expect($object->author_id)->toBe($editor->id)
        ->and($object->latitude)->toBeNull()
        ->and($object->longitude)->toBeNull()
        ->and(strtolower((string) data_get($geometry, 'geometry_type_name')))->toContain('polygon')
        ->and((string) data_get($geometry, 'geometry_wkt'))->toStartWith('POLYGON((');
});

test('sightseeing object create flow accepts polygon geometry with interior rings', function () {
    $editor = User::factory()->editor()->create();
    $locality = Locality::factory()->create();
    $objectType = ObjectType::factory()->create();

    $this->actingAs($editor);

    Livewire::test(CreateSightseeingObject::class)
        ->fillForm([
            'title' => 'Park z jeziorem',
            'lead' => SHORT_OBJECT_DESCRIPTION,
            'description' => FULL_OBJECT_DESCRIPTION,
            'locality_id' => $locality->id,
            'objectTypes' => [$objectType->id],
            'geometry_type' => 'polygon',
            'polygon_wkt' => 'POLYGON((19.9300000 50.0500000,19.9800000 50.0500000,19.9800000 50.0900000,19.9300000 50.0900000,19.9300000 50.0500000),(19.9450000 50.0600000,19.9550000 50.0600000,19.9550000 50.0700000,19.9450000 50.0700000,19.9450000 50.0600000))',
            'status' => 'draft',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $object = SightseeingObject::query()->where('title', 'Park z jeziorem')->firstOrFail();
    $geometry = DB::table('sightseeing_objects')
        ->where('id', $object->id)
        ->selectRaw(GEOMETRY_TYPE_SQL)
        ->selectRaw(GEOMETRY_WKT_SQL)
        ->first();

    expect(strtolower((string) data_get($geometry, 'geometry_type_name')))->toContain('polygon')
        ->and((string) data_get($geometry, 'geometry_wkt'))->toContain('),(');
});

test('sightseeing object create flow accepts multipolygon geometry', function () {
    $editor = User::factory()->editor()->create();
    $locality = Locality::factory()->create();
    $objectType = ObjectType::factory()->create();

    $this->actingAs($editor);

    Livewire::test(CreateSightseeingObject::class)
        ->fillForm([
            'title' => 'Zespół wysp',
            'lead' => SHORT_OBJECT_DESCRIPTION,
            'description' => FULL_OBJECT_DESCRIPTION,
            'locality_id' => $locality->id,
            'objectTypes' => [$objectType->id],
            'geometry_type' => 'polygon',
            'polygon_wkt' => 'MULTIPOLYGON(((19.9300000 50.0500000,19.9500000 50.0500000,19.9500000 50.0700000,19.9300000 50.0700000,19.9300000 50.0500000)),((19.9600000 50.0600000,19.9800000 50.0600000,19.9800000 50.0800000,19.9600000 50.0800000,19.9600000 50.0600000)))',
            'status' => 'draft',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $object = SightseeingObject::query()->where('title', 'Zespół wysp')->firstOrFail();
    $geometry = DB::table('sightseeing_objects')
        ->where('id', $object->id)
        ->selectRaw(GEOMETRY_TYPE_SQL)
        ->selectRaw(GEOMETRY_WKT_SQL)
        ->first();

    expect(strtolower((string) data_get($geometry, 'geometry_type_name')))->toContain('multipolygon')
        ->and((string) data_get($geometry, 'geometry_wkt'))->toStartWith('MULTIPOLYGON(((');
});

test('sightseeing object create flow clears imported osm metadata after manual geometry changes', function () {
    $editor = User::factory()->editor()->create();
    $locality = Locality::factory()->create();
    $objectType = ObjectType::factory()->create();

    $this->actingAs($editor);

    Livewire::test(CreateSightseeingObject::class)
        ->fillForm([
            'title' => 'Granica ręcznie poprawiona',
            'lead' => SHORT_OBJECT_DESCRIPTION,
            'description' => FULL_OBJECT_DESCRIPTION,
            'locality_id' => $locality->id,
            'objectTypes' => [$objectType->id],
            'geometry_type' => 'polygon',
            'polygon_wkt' => 'POLYGON((19.9300000 50.0500000,19.9700000 50.0500000,19.9700000 50.0900000,19.9300000 50.0900000,19.9300000 50.0500000))',
            'osm_geometry_wkt' => DEFAULT_POLYGON_WKT,
            'osm_id' => '123456',
            'osm_type' => 'relation',
            'status' => 'draft',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $object = SightseeingObject::query()->where('title', 'Granica ręcznie poprawiona')->firstOrFail();

    expect($object->osm_id)->toBeNull()
        ->and($object->osm_type)->toBeNull();
});

test('sightseeing object create flow rejects invalid polygon geometry', function () {
    $editor = User::factory()->editor()->create();
    $locality = Locality::factory()->create();
    $objectType = ObjectType::factory()->create();

    $this->actingAs($editor);

    Livewire::test(CreateSightseeingObject::class)
        ->fillForm([
            'title' => 'Niepoprawny poligon',
            'lead' => SHORT_OBJECT_DESCRIPTION,
            'description' => FULL_OBJECT_DESCRIPTION,
            'locality_id' => $locality->id,
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
    $locality = Locality::factory()->create();
    $objectType = ObjectType::factory()->create();

    $this->actingAs($editor);

    Livewire::test(CreateSightseeingObject::class)
        ->fillForm([
            'title' => 'Obiekt bez zdjęcia',
            'lead' => SHORT_OBJECT_DESCRIPTION,
            'description' => FULL_OBJECT_DESCRIPTION,
            'locality_id' => $locality->id,
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
    $locality = Locality::factory()->create();
    $objectType = ObjectType::factory()->create();
    $object = SightseeingObject::factory()->published()->create([
        'locality_id' => $locality->id,
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
            'locality_id' => $object->locality_id,
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
    $locality = Locality::factory()->create();
    $objectType = ObjectType::factory()->create();
    $object = SightseeingObject::factory()->create([
        'locality_id' => $locality->id,
        'status' => 'draft',
    ]);
    $object->objectTypes()->sync($objectType->id);

    $object->addMedia(UploadedFile::fake()->image('first.jpg', 200, 150))->toMediaCollection('images');
    $second = $object->addMedia(UploadedFile::fake()->image('second.jpg', 200, 150))->toMediaCollection('images');

    expect($object->getMedia('images'))->toHaveCount(2);

    $this->actingAs($editor);

    Livewire::test(EditSightseeingObject::class, ['record' => $object->getRouteKey()])
        ->fillForm([
            'title' => $object->title,
            'lead' => $object->lead,
            'description' => $object->description,
            'locality_id' => $object->locality_id,
            'objectTypes' => [$objectType->id],
            'geometry_type' => 'point',
            'latitude' => 50.0614,
            'longitude' => 19.9372,
            'status' => 'draft',
            'images' => [['path' => [$second->getPathRelativeToRoot()]]],
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $object->refresh();

    expect($object->getMedia('images'))->toHaveCount(1)
        ->and($object->getMedia('images')->first()->id)->toBe($second->id);
});

test('sightseeing object editing rolls back when importing an image fails', function () {
    Storage::fake('public');

    $editor = User::factory()->editor()->create();
    $locality = Locality::factory()->create();
    $objectType = ObjectType::factory()->create();
    $object = SightseeingObject::factory()->create([
        'locality_id' => $locality->id,
        'status' => 'draft',
    ]);
    $object->objectTypes()->sync($objectType->id);
    $existing = $object->addMedia(UploadedFile::fake()->image('existing.jpg', 200, 150))->toMediaCollection('images');
    $upload = UploadedFile::fake()->image('new.jpg', 200, 150);
    $newPath = 'cms/object-images/new.jpg';

    Storage::disk('public')->put($newPath, file_get_contents($upload->getRealPath()));
    $filesBefore = Storage::disk('public')->allFiles();
    $originalTitle = $object->title;

    $this->actingAs($editor);

    expect(fn () => Livewire::test(EditSightseeingObject::class, ['record' => $object->getRouteKey()])
        ->fillForm([
            'title' => 'Tytuł po nieudanej edycji',
            'lead' => $object->lead,
            'description' => $object->description,
            'locality_id' => $object->locality_id,
            'objectTypes' => [$objectType->id],
            'geometry_type' => 'point',
            'latitude' => 50.0614,
            'longitude' => 19.9372,
            'status' => 'draft',
            'images' => [
                ['path' => [$existing->getPathRelativeToRoot()]],
                ['path' => [$newPath]],
                ['path' => ['cms/object-images/missing.jpg']],
            ],
        ])
        ->call('save'))->toThrow(Exception::class);

    $object->refresh();

    $expectedFiles = array_values(array_filter($filesBefore, fn (string $path): bool => $path !== $newPath));
    $actualFiles = Storage::disk('public')->allFiles();
    sort($expectedFiles);
    sort($actualFiles);

    expect($object->title)->toBe($originalTitle)
        ->and($object->getMedia('images')->pluck('id')->all())->toBe([$existing->id])
        ->and(Media::query()->where('model_id', $object->id)->count())->toBe(1)
        ->and($actualFiles)->toBe($expectedFiles);
});

test('editing sightseeing object persists image reorder', function () {
    Storage::fake('public');

    $editor = User::factory()->editor()->create();
    $locality = Locality::factory()->create();
    $objectType = ObjectType::factory()->create();
    $object = SightseeingObject::factory()->create([
        'locality_id' => $locality->id,
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
            'locality_id' => $object->locality_id,
            'objectTypes' => [$objectType->id],
            'geometry_type' => 'point',
            'latitude' => 50.0614,
            'longitude' => 19.9372,
            'status' => 'draft',
            'images' => [
                ['path' => [$second->getPathRelativeToRoot()]],
                ['path' => [$first->getPathRelativeToRoot()]],
            ],
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $object->refresh();

    expect($object->getMedia('images')->pluck('id')->all())->toBe([$second->id, $first->id]);
});

test('editing sightseeing object with new images does not throw on reorder', function () {
    Storage::fake('public');

    $editor = User::factory()->editor()->create();
    $locality = Locality::factory()->create();
    $objectType = ObjectType::factory()->create();
    $object = SightseeingObject::factory()->create([
        'locality_id' => $locality->id,
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
            'locality_id' => $object->locality_id,
            'objectTypes' => [$objectType->id],
            'geometry_type' => 'point',
            'latitude' => 50.0614,
            'longitude' => 19.9372,
            'status' => 'draft',
            'images' => [
                [
                    'path' => [$first->getPathRelativeToRoot()],
                    'author' => 'Autor istniejącego',
                    'source' => 'Źródło istniejącego',
                    'description' => 'Opis istniejącego zdjęcia',
                ],
                [
                    'path' => [$newPath],
                    'author' => 'Autor nowego',
                    'source' => 'Źródło nowego',
                    'description' => 'Opis nowego zdjęcia',
                ],
            ],
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $object->refresh();

    expect($object->getMedia('images'))->toHaveCount(2)
        ->and($object->getMedia('images')[0]->getCustomProperty('author'))->toBe('Autor istniejącego')
        ->and($object->getMedia('images')[0]->getCustomProperty('description'))->toBe('Opis istniejącego zdjęcia')
        ->and($object->getMedia('images')[1]->getCustomProperty('author'))->toBe('Autor nowego')
        ->and($object->getMedia('images')[1]->getCustomProperty('source'))->toBe('Źródło nowego')
        ->and($object->image_items[0]['alt'])->toBe($object->title)
        ->and($object->image_items[1]['alt'])->toBe($object->title);
});

test('editing sightseeing object can switch geometry to polygon', function () {
    $editor = User::factory()->editor()->create();
    $locality = Locality::factory()->create();
    $objectType = ObjectType::factory()->create();
    $object = SightseeingObject::factory()->point(50.0614, 19.9372)->create([
        'locality_id' => $locality->id,
        'status' => 'draft',
    ]);
    $object->objectTypes()->sync($objectType->id);

    $this->actingAs($editor);

    Livewire::test(EditSightseeingObject::class, ['record' => $object->getRouteKey()])
        ->fillForm([
            'title' => $object->title,
            'lead' => $object->lead,
            'description' => $object->description,
            'locality_id' => $object->locality_id,
            'objectTypes' => [$objectType->id],
            'geometry_type' => 'polygon',
            'polygon_wkt' => DEFAULT_POLYGON_WKT,
            'status' => 'draft',
            'images' => [],
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $object->refresh();

    $geometry = DB::table('sightseeing_objects')
        ->where('id', $object->id)
        ->selectRaw(GEOMETRY_TYPE_SQL)
        ->selectRaw(GEOMETRY_WKT_SQL)
        ->first();

    expect($object->latitude)->toBeNull()
        ->and($object->longitude)->toBeNull()
        ->and(strtolower((string) data_get($geometry, 'geometry_type_name')))->toContain('polygon')
        ->and((string) data_get($geometry, 'geometry_wkt'))->toStartWith('POLYGON((');
});

test('editing sightseeing object preserves osm metadata when imported geometry stays unchanged', function () {
    $editor = User::factory()->editor()->create();
    $locality = Locality::factory()->create();
    $objectType = ObjectType::factory()->create();
    $object = SightseeingObject::factory()->polygon()->create([
        'locality_id' => $locality->id,
        'status' => 'draft',
        'osm_id' => OSM_ID,
        'osm_type' => 'relation',
    ]);
    $object->objectTypes()->sync($objectType->id);

    $geometry = DB::table('sightseeing_objects')
        ->where('id', $object->id)
        ->selectRaw(GEOMETRY_WKT_SQL)
        ->first();

    $this->actingAs($editor);

    Livewire::test(EditSightseeingObject::class, ['record' => $object->getRouteKey()])
        ->fillForm([
            'title' => $object->title,
            'lead' => $object->lead,
            'description' => $object->description,
            'locality_id' => $object->locality_id,
            'objectTypes' => [$objectType->id],
            'geometry_type' => 'polygon',
            'polygon_wkt' => (string) data_get($geometry, 'geometry_wkt'),
            'osm_geometry_wkt' => (string) data_get($geometry, 'geometry_wkt'),
            'osm_id' => OSM_ID,
            'osm_type' => 'relation',
            'status' => 'draft',
            'images' => [],
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $object->refresh();

    expect($object->osm_id)->toBe(OSM_ID)
        ->and($object->osm_type)->toBe('relation');
});

test('editing sightseeing object clears osm metadata when switching geometry to point', function () {
    $editor = User::factory()->editor()->create();
    $locality = Locality::factory()->create();
    $objectType = ObjectType::factory()->create();
    $object = SightseeingObject::factory()->polygon()->create([
        'locality_id' => $locality->id,
        'status' => 'draft',
        'osm_id' => '987654',
        'osm_type' => 'relation',
    ]);
    $object->objectTypes()->sync($objectType->id);

    $this->actingAs($editor);

    Livewire::test(EditSightseeingObject::class, ['record' => $object->getRouteKey()])
        ->fillForm([
            'title' => $object->title,
            'lead' => $object->lead,
            'description' => $object->description,
            'locality_id' => $object->locality_id,
            'objectTypes' => [$objectType->id],
            'geometry_type' => 'point',
            'latitude' => 50.0614,
            'longitude' => 19.9372,
            'osm_id' => '987654',
            'osm_type' => 'relation',
            'status' => 'draft',
            'images' => [],
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $object->refresh();

    expect($object->osm_id)->toBeNull()
        ->and($object->osm_type)->toBeNull();
});

test('editing sightseeing object can switch geometry to multipolygon', function () {
    $editor = User::factory()->editor()->create();
    $locality = Locality::factory()->create();
    $objectType = ObjectType::factory()->create();
    $object = SightseeingObject::factory()->point(50.0614, 19.9372)->create([
        'locality_id' => $locality->id,
        'status' => 'draft',
    ]);
    $object->objectTypes()->sync($objectType->id);

    $this->actingAs($editor);

    Livewire::test(EditSightseeingObject::class, ['record' => $object->getRouteKey()])
        ->fillForm([
            'title' => $object->title,
            'lead' => $object->lead,
            'description' => $object->description,
            'locality_id' => $object->locality_id,
            'objectTypes' => [$objectType->id],
            'geometry_type' => 'polygon',
            'polygon_wkt' => 'MULTIPOLYGON(((19.9300000 50.0500000,19.9500000 50.0500000,19.9500000 50.0700000,19.9300000 50.0700000,19.9300000 50.0500000)),((19.9600000 50.0600000,19.9800000 50.0600000,19.9800000 50.0800000,19.9600000 50.0800000,19.9600000 50.0600000)))',
            'status' => 'draft',
            'images' => [],
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $object->refresh();

    $geometry = DB::table('sightseeing_objects')
        ->where('id', $object->id)
        ->selectRaw(GEOMETRY_TYPE_SQL)
        ->selectRaw(GEOMETRY_WKT_SQL)
        ->first();

    expect($object->latitude)->toBeNull()
        ->and($object->longitude)->toBeNull()
        ->and(strtolower((string) data_get($geometry, 'geometry_type_name')))->toContain('multipolygon')
        ->and((string) data_get($geometry, 'geometry_wkt'))->toStartWith('MULTIPOLYGON(((');
});
