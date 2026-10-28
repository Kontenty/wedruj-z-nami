<?php

use App\Models\Locality;
use App\Models\ObjectCollection;
use App\Models\ObjectType;
use App\Models\SightseeingObject;
use App\Models\Voivodeship;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function (): void {
    fake()->unique(true);
});

it('returns the catalog inertia page', function () {
    SightseeingObject::factory()->published()->create();

    $this->get('/katalog')
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Catalog/Index')
            ->has('objects')
            ->has('mapObjects')
            ->has('filters')
            ->missing('initialView')
            ->has('objectTypes')
            ->has('voivodeships'));
});

it('paginates collections alongside objects while keeping collections off the map', function () {
    $locality = Locality::factory()->create();

    foreach (range(1, 12) as $index) {
        SightseeingObject::factory()->published()->for($locality)->create([
            'title' => "Zamek {$index}",
            'published_at' => now()->subHour(),
        ]);
    }

    ObjectCollection::factory()->create([
        'name' => 'Zamki regionu',
        'description' => 'Wybrane zamki w regionie.',
        'created_at' => now(),
    ]);

    $this->get('/katalog')
        ->assertInertia(fn (Assert $page) => $page
            ->has('objects.data', 12)
            ->where('objects.data.0.type', 'collection')
            ->where('objects.data.0.title', 'Zamki regionu')
            ->where('objects.data.1.type', 'object')
            ->has('mapObjects.data', 12));

    $this->get('/katalog?page=2')
        ->assertInertia(fn (Assert $page) => $page
            ->has('objects.data', 1)
            ->where('objects.data.0.type', 'object')
            ->has('mapObjects.data', 12));
});

it('uses the first ordered member image for collection cards and details', function () {
    Storage::fake('public');

    $firstObject = SightseeingObject::factory()->create(['title' => 'Pierwszy obiekt']);
    $secondObject = SightseeingObject::factory()->create(['title' => 'Drugi obiekt']);
    $firstObject->addMedia(UploadedFile::fake()->image('first-member.jpg'))
        ->toMediaCollection('images');
    $secondObject->addMedia(UploadedFile::fake()->image('second-member.jpg'))
        ->toMediaCollection('images');
    $collection = ObjectCollection::factory()->create();
    $collection->sightseeingObjects()->attach($firstObject, ['position' => 1]);
    $collection->sightseeingObjects()->attach($secondObject, ['position' => 2]);

    $this->get('/katalog')
        ->assertInertia(fn (Assert $page) => $page
            ->where('objects.data.0.title', $collection->name)
            ->where('objects.data.0.thumbnail_url', $firstObject->thumbnail_url)
            ->where('objects.data.0.card_url', $firstObject->card_url));

    $this->get(route('catalog.collection.show', $collection->slug))
        ->assertInertia(fn (Assert $page) => $page
            ->where('collection.primary_image_url', $firstObject->primary_image_url));

    $collection->sightseeingObjects()->sync([
        $secondObject->id => ['position' => 1],
        $firstObject->id => ['position' => 2],
    ]);

    $this->get('/katalog')
        ->assertInertia(fn (Assert $page) => $page
            ->where('objects.data.0.thumbnail_url', $secondObject->thumbnail_url)
            ->where('objects.data.0.card_url', $secondObject->card_url));

    $this->get(route('catalog.collection.show', $collection->slug))
        ->assertInertia(fn (Assert $page) => $page
            ->where('collection.primary_image_url', $secondObject->primary_image_url));
});

it('uses image placeholders for collections with no member image', function () {
    $emptyCollection = ObjectCollection::factory()->create([
        'name' => 'Pusta kolekcja',
        'created_at' => now()->addMinute(),
    ]);
    $collectionWithoutImage = ObjectCollection::factory()->create([
        'name' => 'Kolekcja bez zdjęcia',
        'created_at' => now(),
    ]);
    $memberWithoutImage = SightseeingObject::factory()->create();
    $collectionWithoutImage->sightseeingObjects()->attach($memberWithoutImage, ['position' => 1]);

    $this->get('/katalog')
        ->assertInertia(fn (Assert $page) => $page
            ->where('objects.data.0.title', $emptyCollection->name)
            ->where('objects.data.0.thumbnail_url', '/images/placeholder-object-thumb.webp')
            ->where('objects.data.0.card_url', '/images/placeholder-object-card.webp')
            ->where('objects.data.1.title', $collectionWithoutImage->name)
            ->where('objects.data.1.thumbnail_url', '/images/placeholder-object-thumb.webp')
            ->where('objects.data.1.card_url', '/images/placeholder-object-card.webp'));

    $this->get(route('catalog.collection.show', $emptyCollection->slug))
        ->assertInertia(fn (Assert $page) => $page
            ->where('collection.primary_image_url', '/images/placeholder-object.webp'));

    $this->get(route('catalog.collection.show', $collectionWithoutImage->slug))
        ->assertInertia(fn (Assert $page) => $page
            ->where('collection.primary_image_url', '/images/placeholder-object.webp'));
});

it('does not expose a catalog view prop', function () {
    SightseeingObject::factory()->published()->create();

    $this->get('/katalog?view=list')
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->missing('initialView'));
});

it('ignores unsupported catalog view values', function () {
    SightseeingObject::factory()->published()->create();

    $this->get('/katalog?view=atlas')
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->missing('initialView'));
});

it('filters by voivodeship slug', function () {
    $mazowieckie = Locality::factory()->for(Voivodeship::factory()->create(['name' => 'Mazowieckie', 'slug' => 'mazowieckie']))->create(['name' => 'Warszawa', 'slug' => 'warszawa']);
    $malopolskie = Locality::factory()->for(Voivodeship::factory()->create(['name' => 'Małopolskie', 'slug' => 'malopolskie']))->create(['name' => 'Kraków', 'slug' => 'krakow']);
    SightseeingObject::factory()->published()->for($mazowieckie)->create(['title' => 'Warszawski zamek']);
    SightseeingObject::factory()->published()->for($malopolskie)->create(['title' => 'Krakowski zamek']);

    $this->get('/katalog?'.http_build_query(['voivodeships' => ['mazowieckie']]))
        ->assertInertia(fn (Assert $page) => $page
            ->where('objects.data.0.title', 'Warszawski zamek')
            ->missing('objects.data.1'));
});

it('filters by object type', function () {
    $type = ObjectType::factory()->create();
    $included = SightseeingObject::factory()->published()->create(['title' => 'Included type']);
    $excluded = SightseeingObject::factory()->published()->create(['title' => 'Excluded type']);
    $included->objectTypes()->attach($type);

    $this->get('/katalog?'.http_build_query(['objectTypes' => [$type->id]]))
        ->assertInertia(fn (Assert $page) => $page
            ->where('objects.data.0.title', 'Included type')
            ->missing('objects.data.1'));
});

it('filters collections by their assigned object types', function () {
    $type = ObjectType::factory()->create();
    $typedMember = SightseeingObject::factory()->published()->create();
    $untypedMember = SightseeingObject::factory()->published()->create();
    $directMatch = ObjectCollection::factory()->create([
        'name' => 'Kategoria kolekcji',
        'created_at' => now()->addMinute(),
    ]);
    $indirectOnly = ObjectCollection::factory()->create([
        'name' => 'Kategoria członka',
        'created_at' => now()->subDay(),
    ]);
    $directMatch->objectTypes()->attach($type);
    $directMatch->sightseeingObjects()->attach($untypedMember, ['position' => 1]);
    $indirectOnly->sightseeingObjects()->attach($typedMember, ['position' => 1]);
    $typedMember->objectTypes()->attach($type);

    $this->get('/katalog?'.http_build_query(['objectTypes' => [$type->id]]))
        ->assertInertia(fn (Assert $page) => $page
            ->where('objects.data.0.title', 'Kategoria kolekcji')
            ->where('objects.data.0.type', 'collection')
            ->missing('objects.data.2'));
});

it('filters collections by the voivodeship of an included object', function () {
    $targetVoivodeship = Voivodeship::factory()->create(['name' => 'Małopolskie', 'slug' => 'malopolskie']);
    $otherVoivodeship = Voivodeship::factory()->create(['name' => 'Mazowieckie', 'slug' => 'mazowieckie']);
    $targetLocality = Locality::factory()->for($targetVoivodeship)->create();
    $otherLocality = Locality::factory()->for($otherVoivodeship)->create();
    $targetObject = SightseeingObject::factory()->published()->for($targetLocality)->create();
    $otherObject = SightseeingObject::factory()->published()->for($otherLocality)->create();
    $matchingCollection = ObjectCollection::factory()->create([
        'name' => 'Kolekcja małopolska',
        'created_at' => now()->addMinute(),
    ]);
    $nonMatchingCollection = ObjectCollection::factory()->create([
        'name' => 'Kolekcja mazowiecka',
        'created_at' => now()->subDay(),
    ]);
    $matchingCollection->sightseeingObjects()->attach($targetObject, ['position' => 1]);
    $nonMatchingCollection->sightseeingObjects()->attach($otherObject, ['position' => 1]);

    $this->get('/katalog?'.http_build_query(['voivodeships' => ['malopolskie']]))
        ->assertInertia(fn (Assert $page) => $page
            ->where('objects.data.0.title', 'Kolekcja małopolska')
            ->has('objects.data', 2)
            ->missing('objects.data.2'));
});

it('filters collections by the unesco status of an included object', function () {
    $unescoObject = SightseeingObject::factory()->published()->unesco()->create();
    $regularObject = SightseeingObject::factory()->published()->create();
    $matchingCollection = ObjectCollection::factory()->create([
        'name' => 'Kolekcja UNESCO',
        'created_at' => now()->addMinute(),
    ]);
    $nonMatchingCollection = ObjectCollection::factory()->create([
        'name' => 'Kolekcja zwykła',
        'created_at' => now()->subDay(),
    ]);
    $matchingCollection->sightseeingObjects()->attach($unescoObject, ['position' => 1]);
    $nonMatchingCollection->sightseeingObjects()->attach($regularObject, ['position' => 1]);

    $this->get('/katalog?unesco=true')
        ->assertInertia(fn (Assert $page) => $page
            ->where('objects.data.0.title', 'Kolekcja UNESCO')
            ->where('objects.data.0.type', 'collection')
            ->has('objects.data', 2)
            ->missing('objects.data.2'));
});

it('filters by unesco', function () {
    SightseeingObject::factory()->published()->unesco()->create(['title' => 'UNESCO object']);
    SightseeingObject::factory()->published()->create(['title' => 'Regular object']);

    $this->get('/katalog?unesco=true')
        ->assertInertia(fn (Assert $page) => $page
            ->where('objects.data.0.title', 'UNESCO object')
            ->missing('objects.data.1'));
});

it('includes the unesco flag in catalog objects', function () {
    SightseeingObject::factory()->published()->unesco()->create([
        'published_at' => now(),
        'title' => 'UNESCO object',
    ]);

    SightseeingObject::factory()->published()->create([
        'published_at' => now()->subDay(),
        'title' => 'Regular object',
    ]);

    $this->get('/katalog')
        ->assertInertia(fn (Assert $page) => $page
            ->where('objects.data.0.is_unesco', true)
            ->where('objects.data.1.is_unesco', false));
});

it('searches by partial title phrase', function () {
    SightseeingObject::factory()->published()->create(['title' => 'Zamek w Malborku']);
    SightseeingObject::factory()->published()->create(['title' => 'Muzeum regionalne']);

    $this->get('/katalog?q=zamek')
        ->assertInertia(fn (Assert $page) => $page
            ->where('objects.data.0.title', 'Zamek w Malborku')
            ->missing('objects.data.1'));
});

it('combines filters correctly', function () {
    $locality = Locality::factory()->for(Voivodeship::factory()->create(['name' => 'małopolskie', 'slug' => 'malopolskie']))->create(['name' => 'Kraków', 'slug' => 'krakow']);
    SightseeingObject::factory()->published()->unesco()->for($locality)->create(['title' => 'zamek UNESCO']);
    SightseeingObject::factory()->published()->for($locality)->create(['title' => 'zamek zwykly']);

    $this->get('/katalog?'.http_build_query(['q' => 'zamek', 'voivodeships' => ['malopolskie'], 'unesco' => 'true']))
        ->assertInertia(fn (Assert $page) => $page
            ->where('objects.data.0.title', 'zamek UNESCO')
            ->missing('objects.data.1'));
});

it('excludes unpublished objects from list and map', function () {
    SightseeingObject::factory()->published()->create(['title' => 'Published']);
    SightseeingObject::factory()->create(['title' => 'Draft']);

    $this->get('/katalog')
        ->assertInertia(fn (Assert $page) => $page
            ->has('objects.data', 1)
            ->has('mapObjects.data', 1));
});

it('paginates objects at 12 per page and keeps map unpaginated', function () {
    $locality = Locality::factory()->create();

    foreach (range(1, 25) as $index) {
        SightseeingObject::factory()->published()->for($locality)->create(['title' => "Paginated object {$index}"]);
    }

    $this->get('/katalog')
        ->assertInertia(fn (Assert $page) => $page
            ->has('objects.data', 12)
            ->has('mapObjects.data', 25));
});
