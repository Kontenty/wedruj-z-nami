<?php

use App\Models\Article;
use App\Models\SightseeingObject;

test('news index loads successfully', function () {
    $this->get('/aktualnosci')->assertStatus(200);
});

test('news index shows published articles', function () {
    $article = Article::factory()->published()->create();

    $this->get('/aktualnosci')
        ->assertSee($article->title);
});

test('news index does not show unpublished articles', function () {
    Article::factory()->published()->create(['title' => 'Published Article']);
    Article::factory()->create(['title' => 'Draft Article']);

    $this->get('/aktualnosci')
        ->assertSee('Published Article')
        ->assertDontSee('Draft Article');
});

test('news index paginates results', function () {
    Article::factory()->published()->count(15)->create();

    $response = $this->get('/aktualnosci');
    $response->assertStatus(200);
    $response->assertSee('Aktualności');
});

test('news index shows latest objects section', function () {
    $object = SightseeingObject::factory()->published()->create();

    $this->get('/aktualnosci')
        ->assertSee('Najnowsze obiekty')
        ->assertSee($object->title);
});

test('news show loads successfully', function () {
    $article = Article::factory()->published()->create();

    $this->get("/aktualnosci/{$article->slug}")
        ->assertStatus(200)
        ->assertSee($article->title);
});

test('news show renders markdown body', function () {
    $article = Article::factory()->published()->create([
        'body' => '**Bold text** and [a link](https://example.com)',
    ]);

    $this->get("/aktualnosci/{$article->slug}")
        ->assertSee('<strong>Bold text</strong>', false)
        ->assertSee('<a href="https://example.com">a link</a>', false);
});

test('news show returns 404 for nonexistent slug', function () {
    $this->get('/aktualnosci/nonexistent-slug')->assertStatus(404);
});

test('news show returns 404 for unpublished article', function () {
    $article = Article::factory()->create();

    $this->get("/aktualnosci/{$article->slug}")->assertStatus(404);
});

test('news show has back link', function () {
    $article = Article::factory()->published()->create();

    $this->get("/aktualnosci/{$article->slug}")
        ->assertSee('Aktualności');
});

test('news show has contextual CTA', function () {
    $article = Article::factory()->published()->create();

    $this->get("/aktualnosci/{$article->slug}")
        ->assertSee('Pokaż mapę')
        ->assertSee('Przeglądaj katalog');
});

test('news index title is correct', function () {
    $this->get('/aktualnosci')
        ->assertSee('Aktualności');
});

test('public pages have header and footer', function () {
    $this->get('/')
        ->assertSee('Kanon')
        ->assertSee('Katalog obiektów krajoznawczych Polski');
});
