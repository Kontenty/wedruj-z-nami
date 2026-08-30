<?php

use App\Models\Article;
use App\Models\Locality;
use App\Models\ObjectType;
use App\Models\SightseeingObject;
use App\Models\Voivodeship;
use Illuminate\Support\Facades\Cache;

beforeEach(function (): void {
    Cache::flush();
});

test('homepage loads successfully', function () {
    $this->get('/')->assertSuccessful();
});

test('homepage contains hero section', function () {
    $this->get('/')
        ->assertSee('Odkrywaj Polskę')
        ->assertSee('Przeglądaj katalog')
        ->assertDontSee('Pokaż mapę')
        ->assertDontSee('view=list');
});

test('homepage shows latest published objects', function () {
    SightseeingObject::factory()->published()->create();
    SightseeingObject::factory()->published()->create();
    SightseeingObject::factory()->published()->create();
    SightseeingObject::factory()->published()->create();
    SightseeingObject::factory()->published()->create();

    $response = $this->get('/');

    $response->assertSuccessful();
    $response->assertSee('Najnowsze obiekty dodane do katalogu');
    expect($response->getContent())->toContain('Najnowsze obiekty dodane do katalogu');
});

test('homepage does not show unpublished objects', function () {
    SightseeingObject::factory()->published()->create(['title' => 'Published Object']);
    SightseeingObject::factory()->create(['title' => 'Draft Object']);

    $this->get('/')
        ->assertSee('Published Object')
        ->assertDontSee('Draft Object');
});

test('homepage shows latest published news', function () {
    Article::factory()->published()->create();
    Article::factory()->published()->create();
    Article::factory()->published()->create();
    Article::factory()->published()->create();

    $this->get('/')
        ->assertSuccessful()
        ->assertSee('Aktualności');
});

test('homepage does not show unpublished news', function () {
    Article::factory()->published()->count(3)->create();
    Article::factory()->create(['title' => 'Draft News']);

    $this->get('/')
        ->assertSee('Aktualności')
        ->assertDontSee('Draft News');
});

test('homepage hides news section until at least three published articles exist', function () {
    Article::factory()->published()->count(2)->create();

    $this->get('/')
        ->assertSuccessful()
        ->assertDontSee('Co nowego w katalogu i wokół niego');
});

test('homepage limits objects to four', function () {
    SightseeingObject::factory()->published()->create(['published_at' => now()->subDays(6), 'title' => 'Excluded Object']);
    SightseeingObject::factory()->published()->create(['published_at' => now()->subDays(5), 'title' => 'Fourth Object']);
    SightseeingObject::factory()->published()->create(['published_at' => now()->subDays(4), 'title' => 'Third Object']);
    SightseeingObject::factory()->published()->create(['published_at' => now()->subDays(3), 'title' => 'Second Object']);
    SightseeingObject::factory()->published()->create(['published_at' => now()->subDays(2), 'title' => 'First Object']);

    $this->get('/')
        ->assertSee('Fourth Object')
        ->assertSee('Third Object')
        ->assertSee('Second Object')
        ->assertSee('First Object')
        ->assertDontSee('Excluded Object');
});

test('homepage limits news to three', function () {
    Article::factory()->published()->create(['published_at' => now()->subDays(5), 'title' => 'Old News']);
    Article::factory()->published()->create(['published_at' => now()->subDays(4), 'title' => 'Recent News']);
    Article::factory()->published()->create(['published_at' => now()->subDays(3), 'title' => 'Newer News']);
    Article::factory()->published()->create(['published_at' => now()->subDays(2), 'title' => 'Newest News']);

    $this->get('/')
        ->assertSee('Recent News')
        ->assertSee('Newer News')
        ->assertSee('Newest News')
        ->assertDontSee('Old News');
});

test('homepage works with the database cache store on repeated requests', function () {
    config()->set('cache.default', 'database');
    Cache::flush();

    SightseeingObject::factory()->published()->create();
    Article::factory()->published()->count(3)->create();

    $this->get('/')->assertSuccessful();
    $this->get('/')->assertSuccessful();
});

test('homepage shows trust band counts and browse by type links', function () {
    $locality = Locality::factory()->for(Voivodeship::factory()->create(['name' => 'Pomorskie']))->create(['name' => 'Gdańsk', 'slug' => 'gdansk']);
    $castle = ObjectType::factory()->create(['name' => 'Zamki']);
    $museum = ObjectType::factory()->create(['name' => 'Muzea']);

    $castleObjects = SightseeingObject::factory()
        ->count(2)
        ->published()
        ->create(['locality_id' => $locality->id]);
    $museumObject = SightseeingObject::factory()
        ->published()
        ->create(['locality_id' => $locality->id]);

    $castle->sightseeingObjects()->attach($castleObjects->modelKeys());
    $museum->sightseeingObjects()->attach($museumObject->getKey());

    $this->get('/')
        ->assertSuccessful()
        ->assertSee('Opublikowane obiekty')
        ->assertSee('Przeglądaj według typu')
        ->assertSee('Województwa w katalogu')
        ->assertSee('Zamki')
        ->assertSee('Muzea')
        ->assertSee("objectTypes%5B0%5D={$castle->getKey()}", false)
        ->assertSee("objectTypes%5B0%5D={$museum->getKey()}", false);
});

test('nonexistent route renders the custom 404 page', function () {
    $this->get('/nie-ma-takiej-strony')
        ->assertNotFound()
        ->assertSee('Nie znaleziono strony')
        ->assertSee('Strona główna')
        ->assertSee('Katalog')
        ->assertSee('Aktualności');
});
