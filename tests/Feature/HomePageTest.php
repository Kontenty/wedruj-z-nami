<?php

use App\Models\Article;
use App\Models\SightseeingObject;
use Illuminate\Support\Facades\Cache;

beforeEach(function (): void {
    Cache::flush();
});

test('homepage loads successfully', function () {
    $this->get('/')->assertSuccessful();
});

test('homepage contains hero section', function () {
    $this->get('/')
        ->assertSee('Katalog obiektów krajoznawczych Polski')
        ->assertSee('Pokaż mapę')
        ->assertSee('Przeglądaj katalog');
});

test('homepage shows latest published objects', function () {
    SightseeingObject::factory()->published()->create();
    SightseeingObject::factory()->published()->create();
    SightseeingObject::factory()->published()->create();
    SightseeingObject::factory()->published()->create();
    SightseeingObject::factory()->published()->create();

    $response = $this->get('/');

    $response->assertSuccessful();
    $response->assertSee('Najnowsze obiekty');
    expect($response->getContent())->toContain('Najnowsze obiekty');
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
    Article::factory()->published()->create(['title' => 'Published News']);
    Article::factory()->create(['title' => 'Draft News']);

    $this->get('/')
        ->assertSee('Published News')
        ->assertDontSee('Draft News');
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

test('nonexistent route renders the custom 404 page', function () {
    $this->get('/nie-ma-takiej-strony')
        ->assertNotFound()
        ->assertSee('Nie znaleziono strony')
        ->assertSee('Strona główna')
        ->assertSee('Katalog')
        ->assertSee('Aktualności');
});
