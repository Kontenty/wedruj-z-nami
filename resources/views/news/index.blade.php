@extends('layouts.public')

@section('title', 'Aktualności — Kanon')

@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <h1 class="font-heading text-3xl sm:text-4xl font-bold mb-4">
            Aktualności
        </h1>
        <p class="text-gray-600 mb-10 max-w-2xl">
            Bądź na bieżąco z nowościami w katalogu obiektów krajoznawczych Polski.
        </p>

        @if($news->isEmpty())
            <p class="text-gray-500">Brak aktualności do wyświetlenia.</p>
        @else
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($news as $newsItem)
                    <a
                        href="{{ route('news.show', $newsItem->slug) }}"
                        class="group bg-white rounded-lg overflow-hidden shadow-sm hover:shadow-md transition-shadow"
                    >
                        @if($newsItem->has_cover_image)
                            <div class="aspect-[3/2] overflow-hidden">
                                <img
                                    src="{{ $newsItem->cover_thumbnail_url }}"
                                    alt=""
                                    class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                                    loading="lazy"
                                >
                            </div>
                        @endif
                        <div class="p-4">
                            <time class="text-sm text-gray-500" datetime="{{ $newsItem->published_at->format('Y-m-d') }}">
                                {{ $newsItem->published_at->format('d.m.Y') }}
                            </time>
                            <h2 class="mt-1 font-semibold text-gray-900 group-hover:text-primary transition-colors">
                                {{ $newsItem->title }}
                            </h2>
                            @if($newsItem->excerpt)
                                <p class="mt-2 text-sm text-gray-600 line-clamp-2">
                                    {{ $newsItem->excerpt }}
                                </p>
                            @endif
                        </div>
                    </a>
                @endforeach
            </div>

            <div class="mt-10">
                {{ $news->links() }}
            </div>
        @endif
    </div>

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
                            href="#"
                            class="group bg-white rounded-lg overflow-hidden shadow-sm hover:shadow-md transition-shadow"
                            title="Szczegóły obiektu będą dostępne wkrótce"
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
@endsection
