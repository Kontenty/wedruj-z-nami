@extends('layouts.public')

@section('title', 'Kanon — Katalog obiektów krajoznawczych Polski')

@section('content')
    {{-- Hero Section --}}
    <section class="bg-primary text-primary-foreground">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 sm:py-24">
            <h1 class="font-heading text-3xl sm:text-4xl lg:text-5xl font-bold leading-tight">
                Katalog obiektów krajoznawczych Polski
            </h1>
            <p class="mt-4 max-w-2xl text-lg text-primary-foreground/80">
                Odkrywaj zabytki, muzea, miejsca historyczne i przyrodnicze w całej Polsce.
                Katalog tworzony przez Polskie Towarzystwo Turystyczno-Krajoznawcze.
            </p>
            <div class="mt-8 flex flex-wrap gap-4">
                <a
                    href="{{ route('catalog.index') }}"
                    class="inline-flex items-center rounded-lg bg-white px-6 py-3 font-semibold text-primary transition-colors hover:bg-gray-100 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-white focus-visible:ring-offset-2 focus-visible:ring-offset-primary"
                >
                    Pokaż mapę
                </a>
                <a
                    href="{{ route('catalog.index', ['view' => 'list']) }}"
                    class="inline-flex items-center rounded-lg border-2 border-white/30 px-6 py-3 font-semibold text-white transition-colors hover:bg-white/10 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-white focus-visible:ring-offset-2 focus-visible:ring-offset-primary"
                >
                    Przeglądaj katalog
                </a>
            </div>
        </div>
    </section>

    {{-- For Whom Section --}}
    <section class="py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h2 class="font-heading text-2xl sm:text-3xl font-bold text-center mb-12">
                Dla kogo?
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="text-center p-6">
                    <div class="w-12 h-12 mx-auto mb-4 bg-primary/10 rounded-full flex items-center justify-center">
                        <svg class="w-6 h-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </div>
                    <h3 class="font-heading text-lg font-semibold mb-2">Turyści</h3>
                    <p class="text-gray-600">
                        Planujesz wycieczkę? Znajdź ciekawe obiekty w okolicy lub na trasie podróży.
                    </p>
                </div>
                <div class="text-center p-6">
                    <div class="w-12 h-12 mx-auto mb-4 bg-primary/10 rounded-full flex items-center justify-center">
                        <svg class="w-6 h-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                        </svg>
                    </div>
                    <h3 class="font-heading text-lg font-semibold mb-2">Nauczyciele</h3>
                    <p class="text-gray-600">
                        Szukasz materiałów do lekcji? Katalog zawiera zweryfikowane informacje krajoznawcze.
                    </p>
                </div>
                <div class="text-center p-6">
                    <div class="w-12 h-12 mx-auto mb-4 bg-primary/10 rounded-full flex items-center justify-center">
                        <svg class="w-6 h-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" />
                        </svg>
                    </div>
                    <h3 class="font-heading text-lg font-semibold mb-2">Organizatorzy wycieczek</h3>
                    <p class="text-gray-600">
                        Planujesz trasę wycieczki? Przeglądaj obiekty według regionów i typów.
                    </p>
                </div>
            </div>
        </div>
    </section>

    {{-- Latest Objects Section --}}
    @if($latestObjects->isNotEmpty())
        <section class="py-16 bg-gray-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <h2 class="font-heading text-2xl sm:text-3xl font-bold mb-8">
                    Najnowsze obiekty
                </h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    @foreach($latestObjects as $object)
                        <a
                            href="{{ route('catalog.show', $object->slug) }}"
                            class="group overflow-hidden rounded-lg bg-white shadow-sm transition-shadow hover:shadow-md focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-600 focus-visible:ring-offset-2"
                        >
                            <div class="aspect-[4/3] overflow-hidden">
                                <img
                                    src="{{ $object->card_url }}"
                                    alt="{{ $object->title }}"
                                    class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                                    loading="lazy"
                                >
                            </div>
                            <div class="p-4">
                                <h3 class="font-semibold text-gray-900 group-hover:text-primary transition-colors">
                                    {{ $object->title }}
                                </h3>
                                @if($object->voivodeship)
                                    <p class="mt-1 text-sm text-gray-500">
                                        {{ $object->voivodeship->name }}
                                    </p>
                                @endif
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- Latest News Section --}}
    @if($latestNews->isNotEmpty())
        <section class="py-16">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between mb-8">
                    <h2 class="font-heading text-2xl sm:text-3xl font-bold">
                        Aktualności
                    </h2>
                    <a
                        href="{{ route('news.index') }}"
                        class="font-medium text-primary transition-colors hover:text-primary/80 focus-visible:rounded-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-600 focus-visible:ring-offset-2"
                    >
                        Zobacz wszystkie &rarr;
                    </a>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    @foreach($latestNews as $newsItem)
                        <a
                            href="{{ route('news.show', $newsItem->slug) }}"
                            class="group overflow-hidden rounded-lg bg-white shadow-sm transition-shadow hover:shadow-md focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-600 focus-visible:ring-offset-2"
                        >
                            @if($newsItem->has_cover_image)
                                <div class="aspect-[3/2] overflow-hidden">
                                    <img
                                        src="{{ $newsItem->cover_thumbnail_url }}"
                                        alt="{{ $newsItem->title }}"
                                        class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                                        loading="lazy"
                                    >
                                </div>
                            @endif
                            <div class="p-4">
                                <time class="text-sm text-gray-500" datetime="{{ $newsItem->published_at->format('Y-m-d') }}">
                                    {{ $newsItem->published_at->format('d.m.Y') }}
                                </time>
                                <h3 class="mt-1 font-semibold text-gray-900 group-hover:text-primary transition-colors">
                                    {{ $newsItem->title }}
                                </h3>
                                @if($newsItem->excerpt)
                                    <p class="mt-2 text-sm text-gray-600 line-clamp-2">
                                        {{ $newsItem->excerpt }}
                                    </p>
                                @endif
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>
    @endif
@endsection
