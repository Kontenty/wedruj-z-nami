<script>
    import { Link } from '@inertiajs/svelte';
    import { index as catalogIndex } from '@/routes/catalog';
    import ImageGallery from './ImageGallery.svelte';
    import NearbyObjects from './NearbyObjects.svelte';
    import ObjectMap from './ObjectMap.svelte';
    import PracticalInfo from './PracticalInfo.svelte';

    let { object, images, geojson } = $props();

    function printPage() {
        window.print();
    }
</script>

<svelte:head>
    <title>{object.title} — Kanon</title>
</svelte:head>

<div class="min-h-screen bg-stone-50 text-stone-950">
    <article class="mx-auto max-w-4xl px-4 py-8 lg:px-6">
        <Link href={catalogIndex.url()} class="mb-6 inline-flex items-center gap-1 text-sm font-medium text-emerald-700 hover:underline">
            ← Wróć do katalogu
        </Link>

        <h1 class="mb-4 font-heading text-3xl font-bold tracking-tight md:text-4xl">{object.title}</h1>

        <div class="mb-6 flex flex-wrap items-center gap-2 text-sm text-stone-600">
            <span class="rounded-full bg-stone-100 px-3 py-1">{object.voivodeship.name}</span>
            {#if object.locality}
                <span class="rounded-full bg-stone-100 px-3 py-1">{object.locality}</span>
            {/if}
            {#each object.objectTypes as type}
                <span class="rounded-full bg-emerald-50 px-3 py-1 text-emerald-800">{type.name}</span>
            {/each}
            {#if object.is_unesco}
                <span class="rounded-full bg-amber-100 px-3 py-1 text-xs font-bold text-amber-900">UNESCO</span>
            {/if}
        </div>

        {#if object.lead}
            <p class="mb-8 text-lg leading-relaxed text-stone-600">{object.lead}</p>
        {/if}

        {#if object.latitude && object.longitude}
            <ObjectMap
                lat={object.latitude}
                lng={object.longitude}
                {geojson}
                title={object.title}
            />
        {/if}

        <ImageGallery {images} title={object.title} />

        {#if object.description}
            <div class="prose prose-lg prose-stone max-w-none mb-8">
                {@html object.description}
            </div>
        {/if}

        <PracticalInfo
            openingHours={object.opening_hours}
            ticketPrices={object.ticket_prices}
            website={object.website}
        />

        <button onclick={printPage} class="mb-8 inline-flex items-center gap-2 rounded-full border border-stone-300 bg-white px-5 py-2 text-sm font-medium text-stone-700 shadow-sm transition hover:bg-stone-50 print-hidden">
            🖨️ Drukuj
        </button>

        <NearbyObjects slug={object.slug} />
    </article>
</div>
