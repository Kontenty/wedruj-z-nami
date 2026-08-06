<script>
  import { Link } from '@inertiajs/svelte';
  import ChevronRight from 'lucide-svelte/icons/chevron-right';
  import MapPin from 'lucide-svelte/icons/map-pin';
  import Printer from 'lucide-svelte/icons/printer';
  import X from 'lucide-svelte/icons/x';
  import UNESCOIcon from '@/components/UNESCOIcon.svelte';
  import { index as catalogIndex } from '@/routes/catalog';
  import ImageGallery from './ImageGallery.svelte';
  import NearbyObjects from './NearbyObjects.svelte';
  import ObjectMap from './ObjectMap.svelte';
  import PracticalInfo from './PracticalInfo.svelte';

  let { object, images, geojson, nearby } = $props();

  let showLocalityModal = $state(false);

  const locationLabel = $derived(
    [
      object.locality?.name,
      object.locality?.voivodeship?.name &&
        `woj. ${object.locality.voivodeship.name}`,
    ]
      .filter(Boolean)
      .join(', '),
  );

  function printPage() {
    window.print();
  }
</script>

<svelte:head>
  <title>{object.title} — Wędruj z Nami</title>
</svelte:head>

<div
  class="bg-[radial-gradient(circle_at_top,#eef6e8_0%,#fcfaf5_34%,#fcfaf5_100%)]"
>
  <article class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8 lg:py-10">
    <header
      class="mb-10 flex flex-col gap-8 lg:flex-row lg:items-end lg:justify-between"
    >
      <div class="min-w-0">
        <nav
          aria-label="Okruszki"
          class="mb-4 flex flex-wrap items-center gap-2 text-sm text-stone-500"
        >
          <Link
            href={catalogIndex.url()}
            class="font-medium text-stone-600 transition-colors hover:text-emerald-800 focus-visible:rounded-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-600 focus-visible:ring-offset-2"
          >
            Katalog
          </Link>
          <ChevronRight class="size-4 shrink-0 text-stone-400" />
          <Link
            href={catalogIndex.url({
              query: { voivodeships: [object.locality.voivodeship.slug] },
            })}
            class="font-medium text-stone-600 transition-colors hover:text-emerald-800 focus-visible:rounded-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-600 focus-visible:ring-offset-2"
          >
            {object.locality.voivodeship.name}
          </Link>
          <ChevronRight class="size-4 shrink-0 text-stone-400" />
          <span class="font-medium text-emerald-800">{object.title}</span>
        </nav>

        <div class="space-y-4">
          {#if locationLabel}
            <button
              type="button"
              onclick={() => (showLocalityModal = true)}
              class="inline-flex items-center gap-2 rounded-full border border-stone-200 bg-white/80 px-6 py-2 text-md font-semibold text-stone-700 shadow-xs transition hover:border-emerald-300 hover:text-emerald-800 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-600 focus-visible:ring-offset-2"
            >
              <MapPin class="size-4 text-emerald-700" />
              {locationLabel}
            </button>
          {/if}

          <h1
            class="max-w-4xl font-heading text-4xl font-black tracking-tight text-stone-950 sm:text-5xl"
          >
            {object.title}
          </h1>

          <div class="flex flex-wrap items-center gap-3">
            {#each object.objectTypes as type (type.id)}
              <span
                class="rounded-full border border-sky-200 bg-sky-100/70 px-4 py-2 text-sm font-semibold text-stone-700"
              >
                {type.name}
              </span>
            {/each}
          </div>
        </div>
      </div>

      {#if object.is_unesco}
        <div
          class="flex items-center gap-4 self-start rounded-3xl border border-amber-300/70 bg-white p-4 shadow-sm lg:self-auto"
        >
          <div
            class="flex size-14 items-center justify-center rounded-2xl bg-linear-to-br from-amber-300 via-amber-100 to-stone-50 text-amber-700"
          >
            <UNESCOIcon class="size-8" title="Obiekt UNESCO" />
          </div>
          <div class="min-w-0">
            <p
              class="text-xs font-bold uppercase tracking-[0.24em] text-amber-700"
            >
              Lista Swiatowego Dziedzictwa
            </p>
            <p class="font-heading text-lg font-bold text-stone-950">
              Obiekt UNESCO
            </p>
          </div>
        </div>
      {/if}
    </header>

    <ImageGallery {images} title={object.title} />

    <div
      class="grid grid-cols-1 gap-10 lg:grid-cols-[minmax(0,2fr)_minmax(320px,1fr)] lg:items-start"
    >
      <div class="space-y-10">
        <section class="space-y-6">
          {#if object.lead}
            <p
              class="border-l-4 border-emerald-700 pl-5 text-lg leading-8 text-stone-700 sm:pl-6 sm:text-xl"
            >
              {object.lead}
            </p>
          {/if}

          {#if object.description}
            <div
              class="prose prose-stone max-w-none prose-headings:font-heading prose-headings:text-stone-950 prose-p:leading-8 prose-a:text-emerald-800"
            >
              <!-- eslint-disable-next-line svelte/no-at-html-tags -->
              {@html object.description}
            </div>
          {/if}
        </section>

        {#if object.latitude !== null || object.longitude !== null || geojson}
          <ObjectMap
            lat={object.latitude}
            lng={object.longitude}
            {geojson}
            title={object.title}
            locality={object.locality?.name}
            voivodeship={object.locality?.voivodeship?.name}
          />
        {/if}
      </div>

      <aside class="space-y-8">
        <PracticalInfo
          openingHours={object.opening_hours}
          ticketPrices={object.ticket_prices}
          accessibility={object.accessibility}
          website={object.website}
        />

        <NearbyObjects nearby={nearby?.data ?? nearby ?? []} />
      </aside>
    </div>

    <footer
      class="mt-14 flex flex-col gap-5 border-t border-stone-200 pt-8 text-sm text-stone-600 md:flex-row md:items-center md:justify-between"
    >
      <div class="flex flex-wrap items-center gap-x-4 gap-y-2">
        {#if object.data_source}
          <p>Źródło: {object.data_source}</p>
        {/if}
        {#if object.data_source && object.source_updated_at}
          <span class="hidden h-1 w-1 rounded-full bg-stone-300 md:block"
          ></span>
        {/if}
        {#if object.source_updated_at}
          <p>Ostatnia aktualizacja: {object.source_updated_at}</p>
        {/if}
      </div>

      <div class="flex items-center gap-3">
        <button
          onclick={printPage}
          class="print-hidden inline-flex items-center gap-2 rounded-full border border-stone-300 bg-white px-4 py-2 font-semibold text-stone-700 shadow-xs transition hover:border-emerald-300 hover:text-emerald-800 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-600 focus-visible:ring-offset-2"
        >
          <Printer class="size-4" />
          Drukuj
        </button>
      </div>
    </footer>
  </article>
</div>

{#if showLocalityModal && object.locality?.description}
  <div
    class="fixed inset-0 z-50 flex items-center justify-center bg-stone-950/50 p-4"
    onclick={() => (showLocalityModal = false)}
    onkeydown={(e) => e.key === 'Escape' && (showLocalityModal = false)}
    role="dialog"
    aria-modal="true"
    aria-labelledby="locality-modal-title"
    tabindex="-1"
  >
    <div
      class="relative max-w-lg rounded-2xl border border-stone-200 bg-white p-6 shadow-2xl"
      onclick={(e) => e.stopPropagation()}
      onkeydown={(e) => e.stopPropagation()}
    >
      <button
        type="button"
        aria-label="Zamknij"
        onclick={() => (showLocalityModal = false)}
        class="absolute right-3 top-3 flex size-8 items-center justify-center rounded-full text-stone-500 transition hover:bg-stone-100 hover:text-stone-900"
      >
        <X class="size-5" />
      </button>

      <h2
        id="locality-modal-title"
        class="pr-8 font-heading text-2xl font-bold text-stone-950"
      >
        {object.locality.name}
      </h2>
      {#if object.locality.voivodeship}
        <p class="mt-1 text-sm text-stone-500">
          woj. {object.locality.voivodeship.name}
        </p>
      {/if}

      {#if object.locality.description}
        <div
          class="prose prose-stone mt-4 max-w-none prose-headings:font-heading prose-headings:text-stone-950 prose-p:leading-7 prose-a:text-emerald-800"
        >
          <!-- eslint-disable-next-line svelte/no-at-html-tags -->
          {@html object.locality.description}
        </div>
      {/if}
    </div>
  </div>
{/if}
