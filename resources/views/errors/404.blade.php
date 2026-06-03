@extends('layouts.public')

@section('title', '404 — Nie znaleziono strony')

@section('content')
    <section class="mx-auto max-w-3xl px-4 py-20 text-center sm:px-6 lg:px-8">
        <p class="text-sm font-semibold uppercase tracking-[0.2em] text-emerald-700">Błąd 404</p>
        <h1 class="mt-4 font-heading text-4xl font-bold tracking-tight text-stone-900 sm:text-5xl">
            Nie znaleziono strony
        </h1>
        <p class="mt-4 text-lg text-stone-600">
            Strona, której szukasz, nie istnieje albo została przeniesiona.
        </p>

        <div class="mt-10 grid gap-3 sm:grid-cols-3">
            <a href="{{ route('home') }}" class="rounded-2xl border border-stone-300 bg-white px-5 py-3 font-medium text-stone-900 transition hover:bg-stone-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-600 focus-visible:ring-offset-2">
                Strona główna
            </a>
            <a href="{{ route('catalog.index') }}" class="rounded-2xl border border-stone-300 bg-white px-5 py-3 font-medium text-stone-900 transition hover:bg-stone-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-600 focus-visible:ring-offset-2">
                Katalog
            </a>
            <a href="{{ route('news.index') }}" class="rounded-2xl border border-stone-300 bg-white px-5 py-3 font-medium text-stone-900 transition hover:bg-stone-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-600 focus-visible:ring-offset-2">
                Aktualności
            </a>
        </div>
    </section>
@endsection
