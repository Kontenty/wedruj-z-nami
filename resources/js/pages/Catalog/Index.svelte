<script>
  import { router } from '@inertiajs/svelte';
  import { index as catalogIndex } from '@/routes/catalog';
  import ActiveFilterChips from './ActiveFilterChips.svelte';
  import CatalogMap from './CatalogMap.svelte';
  import EmptyState from './EmptyState.svelte';
  import MobileFilterSheet from './MobileFilterSheet.svelte';
  import MobileViewToggle from './MobileViewToggle.svelte';
  import ObjectGrid from './ObjectGrid.svelte';
  import TopFilterBar from './TopFilterBar.svelte';

  let { objects, mapObjects, filters, objectTypes, voivodeships } = $props();

  let activeView = $state('map');
  let highlightedObjectId = $state(null);
  let selectedObjectId = $state(null);
  let showMobileFilters = $state(false);
  let isLoading = $state(false);

  const activeFilters = $derived({
    q: filters.q || '',
    voivodeships: filters.voivodeships || [],
    objectTypes: filters.objectTypes || [],
    unesco: filters.unesco === true,
  });
  const resultCount = $derived(objects.meta?.total ?? (objects.data ?? []).length);

  function visit(nextFilters) {
    const query = Object.fromEntries(
      Object.entries(nextFilters).filter(
        ([, value]) =>
          value !== '' &&
          value !== false &&
          value !== null &&
          value !== undefined,
      ),
    );

    router.get(catalogIndex.url(), query, {
      preserveState: true,
      replace: true,
      only: ['objects', 'mapObjects', 'filters'],
      onStart: () => (isLoading = true),
      onFinish: () => (isLoading = false),
    });
  }

  function handlePageChange(page) {
    const query = {
      ...Object.fromEntries(
        Object.entries(filters).filter(
          ([, value]) =>
            value !== '' &&
            value !== false &&
            value !== null &&
            value !== undefined,
        ),
      ),
      page,
    };

    router.get(catalogIndex.url(), query, {
      preserveState: true,
      replace: true,
      only: ['objects', 'mapObjects', 'filters'],
      onStart: () => (isLoading = true),
      onFinish: () => (isLoading = false),
    });
  }

  function clearFilters() {
    visit({ q: '', voivodeships: [], objectTypes: [], unesco: false });
  }

  function scrollToObject(objectId) {
    selectedObjectId = objectId;
    document
      .getElementById(`object-card-${objectId}`)
      ?.scrollIntoView({ behavior: 'smooth', block: 'center' });
  }
</script>

<svelte:head><title>Katalog</title></svelte:head>

<div class="min-h-screen bg-stone-50 text-stone-950">
  <div class="mx-auto flex max-w-7xl flex-col gap-4 px-4 py-4 lg:px-6">
    <div
      class="flex flex-col gap-3 md:flex-row md:items-end md:justify-between"
    >
      <div>
        <p
          class="text-sm font-semibold uppercase tracking-[0.2em] text-emerald-700"
        >
          Wędruj z Nami
        </p>
        <h1 class="text-3xl font-bold tracking-tight md:text-4xl">
          Katalog obiektów krajoznawczych
        </h1>
      </div>
      <button
        class="rounded-full bg-emerald-700 px-5 py-2 text-sm font-semibold text-white shadow-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-600 focus-visible:ring-offset-2 md:hidden"
        onclick={() => (showMobileFilters = true)}>Filtry</button
      >
    </div>

    <TopFilterBar
      filters={activeFilters}
      {objectTypes}
      {voivodeships}
      onApply={visit}
      onClear={clearFilters}
    />

    <MobileViewToggle bind:activeView />

    <main
      id="main-content"
      class="grid gap-4 focus:outline-none lg:grid-cols-[minmax(0,65fr)_minmax(22rem,35fr)]"
      tabindex="-1"
    >
      <section
        class={(activeView === 'map' ? 'block' : 'hidden') +
          ' h-[70vh] overflow-hidden rounded-3xl border border-stone-200 bg-white shadow-sm md:block lg:sticky lg:top-4'}
        aria-labelledby="catalog-map-heading"
      >
        <h2 id="catalog-map-heading" class="sr-only">Mapa obiektów</h2>
        <CatalogMap
          objects={mapObjects.data ?? mapObjects}
          {highlightedObjectId}
          {selectedObjectId}
          onSelect={scrollToObject}
        />
      </section>

      <section
        class={(activeView === 'list' ? 'block' : 'hidden') + ' md:block'}
        aria-labelledby="catalog-results-heading"
      >
        <div class="mb-3 flex items-center justify-between gap-3">
          <h2 id="catalog-results-heading" class="sr-only">Wyniki katalogu</h2>
          <p class="text-sm font-semibold text-stone-700" aria-live="polite">
            Liczba wyników: {resultCount}
          </p>
        </div>
        <ActiveFilterChips
          filters={activeFilters}
          {voivodeships}
          {objectTypes}
          onChange={visit}
          onClear={clearFilters}
        />
        {#if (objects.data ?? []).length === 0 && !isLoading}
          <EmptyState onClear={clearFilters} />
        {:else}
          <ObjectGrid
            {objects}
            {isLoading}
            {selectedObjectId}
            onHover={(id) => (highlightedObjectId = id)}
            onPageChange={handlePageChange}
          />
        {/if}
      </section>
    </main>
  </div>
</div>

<MobileFilterSheet
  open={showMobileFilters}
  filters={activeFilters}
  {objectTypes}
  {voivodeships}
  onClose={() => (showMobileFilters = false)}
  onApply={(next) => {
    showMobileFilters = false;
    visit(next);
  }}
  onClear={() => {
    showMobileFilters = false;
    clearFilters();
  }}
/>
