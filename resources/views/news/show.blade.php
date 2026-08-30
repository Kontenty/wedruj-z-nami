@extends('layouts.public')

@section('title', e($newsItem->title) . ' — Wędruj z Nami')

@section('content')
<article class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <a
        href="{{ route('news.index') }}"
        class="inline-flex items-center text-primary hover:text-primary/80 font-medium transition-colors mb-8">
        &larr; Aktualności
    </a>

    <header class="mb-8">
        <h1 class="font-heading text-3xl sm:text-4xl font-bold leading-tight">
            {{ $newsItem->title }}
        </h1>
        <time
            class="mt-3 block text-gray-500"
            datetime="{{ $newsItem->published_at->format('Y-m-d') }}">
            {{ $newsItem->published_at->format('d.m.Y') }}
        </time>
    </header>

    @if($newsItem->has_cover_image)
    <div class="mb-8 rounded-lg overflow-hidden">
        <img
            src="{{ $newsItem->cover_image_url }}"
            alt="{{ $newsItem->cover_image['alt'] }}"
            class="w-full h-auto">
    </div>
    @endif

    <div class="prose prose-lg max-w-none">
        {!! Str::markdown($newsItem->body, [
        'html_input' => 'strip',
        'allow_unsafe_links' => false,
        ]) !!}
    </div>

    <div class="mt-12 pt-8 border-t border-gray-200">
        <p class="text-gray-600 mb-4">
            Odkrywaj obiekty krajoznawcze w katalogu dopasowanym do Twojego ekranu.
        </p>
        <div class="flex flex-wrap gap-4">
            <a
                href="{{ route('catalog.index') }}"
                class="inline-flex items-center px-5 py-2.5 bg-primary text-primary-foreground font-semibold rounded-lg hover:bg-primary/90 transition-colors">
                Przeglądaj katalog
            </a>
        </div>
    </div>
</article>
@endsection
