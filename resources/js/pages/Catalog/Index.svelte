<script>
  import { router } from '@inertiajs/svelte';
  import { onMount } from 'svelte';
  import { index as catalogIndex } from '@/routes/catalog';
  import ActiveFilterChips from './ActiveFilterChips.svelte';
  import CatalogMap from './CatalogMap.svelte';
  import CatalogViewToggle from './CatalogViewToggle.svelte';
  import EmptyState from './EmptyState.svelte';
  import MobileFilterSheet from './MobileFilterSheet.svelte';
  import ObjectGrid from './ObjectGrid.svelte';
  import TopFilterBar from './TopFilterBar.svelte';

  let {
    objects,
    mapObjects,
    filters,
    objectTypes,
    voivodeships,
    initialView = null,
  } = $props();

  const desktopMediaQuery = '(min-width: 768px)';

  let activeView = $state(
    typeof window !== 'undefined' &&
      window.matchMedia(desktopMediaQuery).matches
      ? 'split'
      : 'map',
  );
  let hasExplicitView = $state(false);
  let highlightedObjectId = $state(null);
  let selectedObjectId = $state(null);
  let showMobileFilters = $state(false);
  let isLoading = $state(false);
  let fitBoundsVersion = $state(0);

  const showsMap = $derived(activeView === 'map' || activeView === 'split');
  const showsList = $derived(activeView === 'list' || activeView === 'split');
  const isSplitView = $derived(activeView === 'split');

  const activeFilters = $derived({
    q: filters.q || '',
    voivodeships: filters.voivodeships || [],
    objectTypes: filters.objectTypes || [],
    unesco: filters.unesco === true,
  });
  const hasPreFilters = $derived(
    !!(
      filters.q ||
      filters.voivodeships?.length ||
      filters.objectTypes?.length ||
      filters.unesco
    ),
  );
  const resultCount = $derived(
    objects.meta?.total ?? (objects.data ?? []).length,
  );

  $effect(() => {
    if (hasPreFilters) {
      fitBoundsVersion += 1;
    }
  });

  $effect(() => {
    hasExplicitView = initialView !== null;

    if (initialView !== null) {
      activeView = initialView;
    }
  });

  onMount(() => {
    if (hasExplicitView) {
      return;
    }

    const media = window.matchMedia(desktopMediaQuery);

    const syncView = () => {
      activeView = media.matches ? 'split' : 'map';
    };

    syncView();
    media.addEventListener('change', syncView);

    return () => {
      media.removeEventListener('change', syncView);
    };
  });

  function sanitizeQuery(nextQuery) {
    return Object.fromEntries(
      Object.entries(nextQuery).filter(([, value]) => {
        if (Array.isArray(value)) {
          return value.length > 0;
        }

        return (
          value !== '' &&
          value !== false &&
          value !== null &&
          value !== undefined
        );
      }),
    );
  }

  function currentQuery() {
    return sanitizeQuery({
      ...filters,
      view: activeView,
    });
  }

  function requestFitBounds() {
    fitBoundsVersion += 1;
  }

  function visit(nextFilters) {
    const query = sanitizeQuery({
      ...nextFilters,
      view: activeView,
    });

    router.get(catalogIndex.url(), query, {
      preserveState: true,
      replace: true,
      only: ['objects', 'mapObjects', 'filters', 'initialView'],
      onStart: () => (isLoading = true),
      onFinish: () => {
        isLoading = false;
        requestFitBounds();
      },
    });
  }

  function handlePageChange(page) {
    const query = sanitizeQuery({
      ...filters,
      view: activeView,
      page,
    });

    router.get(catalogIndex.url(), query, {
      preserveState: true,
      replace: true,
      only: ['objects', 'mapObjects', 'filters', 'initialView'],
      onStart: () => (isLoading = true),
      onFinish: () => (isLoading = false),
    });
  }

  function handleViewChange(nextView) {
    if (nextView === activeView) {
      return;
    }

    activeView = nextView;
    hasExplicitView = true;

    if (nextView !== 'list') {
      requestFitBounds();
    }

    router.get(
      catalogIndex.url(),
      sanitizeQuery({
        ...currentQuery(),
        view: nextView,
      }),
      {
        preserveState: true,
        replace: true,
        only: ['initialView'],
      },
    );
  }

  function clearFilters() {
    visit({ q: '', voivodeships: [], objectTypes: [], unesco: false });
  }

  function scrollToObject() {}
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

    <CatalogViewToggle
      bind:activeView
      onChange={handleViewChange}
      class="md:hidden"
    />

    <div class="hidden md:flex md:justify-end">
      <CatalogViewToggle
        bind:activeView
        onChange={handleViewChange}
        class="md:w-fit"
      />
    </div>

    <main
      id="main-content"
      class={isSplitView
        ? 'grid gap-4 focus:outline-none lg:grid-cols-[minmax(0,65fr)_minmax(22rem,35fr)]'
        : 'grid gap-4 focus:outline-none'}
      tabindex="-1"
    >
      {#if showsMap}
        <section
          class={'h-[70vh] overflow-hidden rounded-3xl border border-stone-200 bg-white shadow-sm ' +
            (isSplitView ? 'lg:sticky lg:top-4' : '')}
          aria-labelledby="catalog-map-heading"
        >
          <h2 id="catalog-map-heading" class="sr-only">Mapa obiektów</h2>
          <CatalogMap
            objects={mapObjects.data ?? mapObjects}
            {highlightedObjectId}
            {selectedObjectId}
            onSelect={scrollToObject}
            {fitBoundsVersion}
          />
        </section>
      {/if}

      {#if showsList}
        <section aria-labelledby="catalog-results-heading">
          <div class="mb-3 flex items-center gap-3">
            <h2 id="catalog-results-heading" class="sr-only">
              Wyniki katalogu
            </h2>
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
      {/if}
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
