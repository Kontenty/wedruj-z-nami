<script>
  import MapPinned from 'lucide-svelte/icons/map-pinned';
  import maplibregl from 'maplibre-gl';
  import { onMount } from 'svelte';
  import 'maplibre-gl/dist/maplibre-gl.css';
  import { setPolishLanguage } from '@/lib/map-language';

  let {
    lat,
    lng,
    geojson,
    title,
    locality = null,
    voivodeship = null,
  } = $props();
  let container = $state();
  let map;
  let hasMapError = $state(false);

  const locationLabel = $derived(
    [locality, voivodeship && `woj. ${voivodeship}`].filter(Boolean).join(', '),
  );

  function parseGeometry() {
    if (!geojson) {
      return null;
    }

    try {
      return typeof geojson === 'string' ? JSON.parse(geojson) : geojson;
    } catch {
      return null;
    }
  }

  function extendBounds(bounds, coordinates) {
    if (!Array.isArray(coordinates)) {
      return;
    }

    if (
      typeof coordinates[0] === 'number' &&
      typeof coordinates[1] === 'number'
    ) {
      bounds.extend([coordinates[0], coordinates[1]]);

      return;
    }

    coordinates.forEach((coordinate) => extendBounds(bounds, coordinate));
  }

  onMount(() => {
    try {
      const geometry = parseGeometry();
      const hasCoordinates =
        lat !== null && lat !== undefined && lng !== null && lng !== undefined;
      const center = hasCoordinates
        ? [Number(lng), Number(lat)]
        : geometry?.type === 'Point'
          ? geometry.coordinates
          : [19.1, 52.1];

      map = new maplibregl.Map({
        container,
        style: 'https://tiles.openfreemap.org/styles/liberty',
        center,
        zoom: hasCoordinates ? 13 : 6,
      });

      map.addControl(new maplibregl.NavigationControl(), 'top-right');

      map.on('load', () => {
        setPolishLanguage(map);

        if (geometry && ['Polygon', 'MultiPolygon'].includes(geometry.type)) {
          map.addSource('object-area', {
            type: 'geojson',
            data: geometry,
          });
          map.addLayer({
            id: 'object-area-fill',
            type: 'fill',
            source: 'object-area',
            paint: { 'fill-color': '#136a27', 'fill-opacity': 0.2 },
          });
          map.addLayer({
            id: 'object-area-outline',
            type: 'line',
            source: 'object-area',
            paint: { 'line-color': '#136a27', 'line-width': 2 },
          });

          const bounds = new maplibregl.LngLatBounds();
          extendBounds(bounds, geometry.coordinates);

          if (!bounds.isEmpty()) {
            map.fitBounds(bounds, { padding: 32 });
          }

          return;
        }

        const pointCoordinates = hasCoordinates
          ? [Number(lng), Number(lat)]
          : geometry?.type === 'Point'
            ? geometry.coordinates
            : null;

        if (!pointCoordinates) {
          return;
        }

        new maplibregl.Marker({ color: '#136a27' })
          .setLngLat(pointCoordinates)
          .setPopup(new maplibregl.Popup().setHTML(`<strong>${title}</strong>`))
          .addTo(map);
      });

      map.on('error', () => {
        hasMapError = true;
      });
    } catch {
      hasMapError = true;
    }

    return () => map?.remove();
  });
</script>

<section
  class="overflow-hidden rounded-[1.75rem] border border-stone-200 bg-white shadow-sm print-hidden"
  aria-labelledby="object-map-heading"
>
  <div
    class="flex flex-col gap-3 border-b border-stone-200 px-6 py-5 sm:flex-row sm:items-center sm:justify-between"
  >
    <div>
      <h2
        id="object-map-heading"
        class="font-heading text-2xl font-bold text-stone-950"
      >
        Lokalizacja
      </h2>
      {#if locationLabel}
        <p class="mt-1 text-sm text-stone-500">{locationLabel}</p>
      {/if}
    </div>

    {#if locationLabel}
      <div
        class="inline-flex items-center gap-2 text-sm font-semibold text-stone-600"
      >
        <MapPinned class="size-4 text-emerald-700" />
        {locationLabel}
      </div>
    {/if}
  </div>

  {#if hasMapError}
    <p class="p-6 text-sm text-stone-600">
      Mapa jest chwilowo niedostępna. Pozostałe informacje o obiekcie są
      dostępne poniżej.
    </p>
  {:else}
    <div
      bind:this={container}
      class="h-72 w-full sm:h-80 lg:h-[25rem]"
      aria-label={`Mapa obiektu ${title}`}
    ></div>
  {/if}
</section>
