<?php

use App\Models\Article;

test('article published scope returns published articles newest first', function () {
    $older = Article::factory()->published()->create(['published_at' => now()->subDays(4)]);
    $newer = Article::factory()->published()->create(['published_at' => now()->subDay()]);
    Article::factory()->create();

    expect(Article::published()->pluck('id')->all())->toBe([$newer->id, $older->id]);
});

test('article factory creates draft news by default', function () {
    $article = Article::factory()->create();

    expect($article->published)->toBeFalse()
        ->and($article->status)->toBe('draft')
        ->and($article->published_at)->toBeNull();
});

test('article slugs are generated with collision handling', function () {
    $first = Article::query()->create([
        'title' => 'Nowe obiekty w katalogu',
        'body' => 'Treść aktualności.',
    ]);
    $second = Article::query()->create([
        'title' => 'Nowe obiekty w katalogu',
        'body' => 'Druga treść aktualności.',
    ]);

    expect($first->slug)->toBe('nowe-obiekty-w-katalogu')
        ->and($second->slug)->toBe('nowe-obiekty-w-katalogu-2');
});
