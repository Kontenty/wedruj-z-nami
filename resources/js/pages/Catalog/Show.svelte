<script>
    import { Link } from '@inertiajs/svelte';
    import { index as catalogIndex } from '@/routes/catalog';
    import ImageGallery from './ImageGallery.svelte';
    import NearbyObjects from './NearbyObjects.svelte';
    import ObjectMap from './ObjectMap.svelte';
    import PracticalInfo from './PracticalInfo.svelte';

    let { object, images, geojson, nearby } = $props();

    function printPage() {
        window.print();
    }
</script>

<svelte:head>
    <title>{object.title} — Kanon</title>
</svelte:head>

<div class="min-h-screen bg-stone-50 text-stone-950">
    <article id="main-content" class="mx-auto max-w-4xl px-4 py-8 focus:outline-none lg:px-6" tabindex="-1">
        <Link href={catalogIndex.url()} class="mb-6 inline-flex items-center gap-1 text-sm font-medium text-emerald-700 hover:underline focus-visible:rounded-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-600 focus-visible:ring-offset-2">
            ← Wróć do katalogu
        </Link>

        <h1 class="mb-4 font-heading text-3xl font-bold tracking-tight md:text-4xl">{object.title}</h1>

        <div class="mb-6 flex flex-wrap items-center gap-2 text-sm text-stone-600">
            <span class="rounded-full bg-stone-100 px-3 py-1">{object.voivodeship.name}</span>
            {#if object.locality}
                <span class="rounded-full bg-stone-100 px-3 py-1">{object.locality}</span>
            {/if}
            {#each object.objectTypes as type (type.id)}
                <span class="rounded-full bg-emerald-50 px-3 py-1 text-emerald-800">{type.name}</span>
            {/each}
            {#if object.is_unesco}
                <span class="rounded-full bg-amber-100 px-3 py-1 text-xs font-bold text-amber-900">UNESCO</span>
            {/if}
        </div>

        {#if object.lead}
            <p class="mb-8 text-lg leading-relaxed text-stone-600">{object.lead}</p>
        {/if}

        {#if object.latitude !== null || object.longitude !== null || geojson}
            <ObjectMap
                lat={object.latitude}
                lng={object.longitude}
                {geojson}
                title={object.title}
            />
        {/if}

        <ImageGallery {images} title={object.title} />

        {#if object.description}
            <div class="prose prose-lg prose-stone mb-8 max-w-none">
                <!-- eslint-disable-next-line svelte/no-at-html-tags -->
                {@html object.description}
            </div>
        {/if}

        <PracticalInfo
            openingHours={object.opening_hours}
            ticketPrices={object.ticket_prices}
            accessibility={object.accessibility}
            website={object.website}
        />

        {#if object.data_source || object.source_updated_at}
            <section class="mb-8 rounded-2xl border border-stone-200 bg-white p-6 shadow-sm print-keep-together" aria-labelledby="object-metadata-heading">
                <h2 id="object-metadata-heading" class="mb-4 font-heading text-xl font-semibold">Informacje o danych</h2>
                <dl class="space-y-4">
                    {#if object.data_source}
                        <div>
                            <dt class="text-sm font-medium text-stone-500">Źródło danych</dt>
                            <dd class="mt-1 text-stone-700">{object.data_source}</dd>
                        </div>
                    {/if}
                    {#if object.source_updated_at}
                        <div>
                            <dt class="text-sm font-medium text-stone-500">Aktualizacja danych</dt>
                            <dd class="mt-1 text-stone-700">{object.source_updated_at}</dd>
                        </div>
                    {/if}
                </dl>
            </section>
        {/if}

        <button onclick={printPage} class="print-hidden mb-8 inline-flex items-center gap-2 rounded-full border border-stone-300 bg-white px-5 py-2 text-sm font-medium text-stone-700 shadow-sm transition hover:bg-stone-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-600 focus-visible:ring-offset-2">
            🖨️ Drukuj
        </button>

        <NearbyObjects nearby={nearby?.data ?? nearby ?? []} />
    </article>
</div>
