<?php

use App\Models\Locality;
use App\Models\ObjectType;
use App\Models\SightseeingObject;
use App\Models\Voivodeship;
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
            ->where('initialView', null)
            ->has('objectTypes')
            ->has('voivodeships'));
});

it('passes the requested catalog view when it is valid', function () {
    SightseeingObject::factory()->published()->create();

    $this->get('/katalog?view=list')
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->where('initialView', 'list'));
});

it('ignores unsupported catalog view values', function () {
    SightseeingObject::factory()->published()->create();

    $this->get('/katalog?view=atlas')
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->where('initialView', null));
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
