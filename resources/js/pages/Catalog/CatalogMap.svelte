<script>
  import maplibreGl from 'maplibre-gl';
  import { mount, onMount, unmount } from 'svelte';
  import 'maplibre-gl/dist/maplibre-gl.css';
  import { setPolishLanguage } from '@/lib/map-language';
  import MapPopup from './MapPopup.svelte';

  let {
    objects = [],
    highlightedObjectId = null,
    selectedObjectId = null,
    onSelect,
    fitBoundsVersion = 0,
    isFullMap = false,
    onToggleFullMap,
  } = $props();
  let container = $state();
  let map;
  let popup;
  let popupComponent;
  let resizeObserver;
  let hasMapError = $state(false);
  let mapLoaded = $state(false);

  const pointFeatures = $derived(
    (objects ?? [])
      .filter(
        (object) =>
          object.latitude !== null &&
          object.latitude !== undefined &&
          object.longitude !== null &&
          object.longitude !== undefined,
      )
      .map((object) => ({
        type: 'Feature',
        id: object.id,
        properties: { id: object.id, title: object.title },
        geometry: {
          type: 'Point',
          coordinates: [Number(object.longitude), Number(object.latitude)],
        },
      })),
  );
  const polygonFeatures = $derived(
    (objects ?? [])
      .map((object) => {
        try {
          const geometry =
            typeof object.geojson === 'string'
              ? JSON.parse(object.geojson)
              : object.geojson;

          return geometry?.type?.includes('Polygon')
            ? {
                type: 'Feature',
                id: object.id,
                properties: {
                  id: object.id,
                  title: object.title,
                },
                geometry,
              }
            : null;
        } catch {
          return null;
        }
      })
      .filter(Boolean),
  );

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

    coordinates.forEach((coordinate) => {
      extendBounds(bounds, coordinate);
    });
  }

  function calculateBounds() {
    if (objects.length === 0) {
      return null;
    }

    const bounds = new maplibreGl.LngLatBounds();

    for (const feature of pointFeatures) {
      bounds.extend(feature.geometry.coordinates);
    }

    for (const feature of polygonFeatures) {
      extendBounds(bounds, feature.geometry.coordinates);
    }

    return bounds.isEmpty() ? null : bounds;
  }

  function closePopup() {
    const currentPopup = popup;
    const currentPopupComponent = popupComponent;

    popup = null;
    popupComponent = null;

    currentPopup?.remove();

    if (currentPopupComponent) {
      unmount(currentPopupComponent);
    }
  }

  function openPopup(object, coordinates) {
    closePopup();

    const popupContainer = document.createElement('div');

    popupComponent = mount(MapPopup, {
      target: popupContainer,
      props: { object, onClose: closePopup },
    });

    popup = new maplibreGl.Popup({
      closeButton: false,
      className: 'wzn-map-popup',
    })
      .setLngLat(coordinates)
      .setDOMContent(popupContainer)
      .addTo(map);

    popup.on('close', () => {
      if (popupComponent) {
        unmount(popupComponent);
        popupComponent = null;
      }

      popup = null;
    });
  }

  function refreshSources() {
    if (!map || !mapLoaded) {
      return;
    }

    map.getSource('points')?.setData({
      type: 'FeatureCollection',
      features: pointFeatures,
    });
    map.getSource('polygons')?.setData({
      type: 'FeatureCollection',
      features: polygonFeatures,
    });
  }

  function featureIdFilter(objectId) {
    return [
      'all',
      ['!', ['has', 'point_count']],
      ['==', ['id'], objectId ?? -1],
    ];
  }

  function polygonIdFilter(objectId) {
    return ['==', ['id'], objectId ?? -1];
  }

  function refreshHighlightLayers() {
    if (!map || !mapLoaded || !map.getLayer('highlighted-point')) {
      return;
    }

    map.setFilter('highlighted-point', featureIdFilter(highlightedObjectId));
    map.setFilter('selected-point', featureIdFilter(selectedObjectId));
    map.setFilter('highlighted-polygon', polygonIdFilter(highlightedObjectId));
    map.setFilter('selected-polygon', polygonIdFilter(selectedObjectId));
  }

  onMount(() => {
    try {
      map = new maplibreGl.Map({
        container,
        style: 'https://tiles.openfreemap.org/styles/liberty',
        center: [19.1, 52.1],
        zoom: 5,
      });
      map.addControl(new maplibreGl.NavigationControl(), 'top-right');
      map.on('load', () => {
        mapLoaded = true;
        setPolishLanguage(map);
        map.addSource('points', {
          type: 'geojson',
          data: {
            type: 'FeatureCollection',
            features: pointFeatures,
          },
          cluster: true,
          clusterRadius: 40,
        });
        map.addSource('polygons', {
          type: 'geojson',
          data: {
            type: 'FeatureCollection',
            features: polygonFeatures,
          },
        });
        map.addLayer({
          id: 'polygon-fill',
          type: 'fill',
          source: 'polygons',
          paint: { 'fill-color': '#059669', 'fill-opacity': 0.2 },
        });
        map.addLayer({
          id: 'polygon-outline',
          type: 'line',
          source: 'polygons',
          paint: { 'line-color': '#047857', 'line-width': 2 },
        });
        map.addLayer({
          id: 'highlighted-polygon',
          type: 'line',
          source: 'polygons',
          filter: polygonIdFilter(highlightedObjectId),
          paint: { 'line-color': '#f59e0b', 'line-width': 4 },
        });
        map.addLayer({
          id: 'selected-polygon',
          type: 'line',
          source: 'polygons',
          filter: polygonIdFilter(selectedObjectId),
          paint: { 'line-color': '#dc2626', 'line-width': 5 },
        });
        map.addLayer({
          id: 'clusters',
          type: 'circle',
          source: 'points',
          filter: ['has', 'point_count'],
          paint: { 'circle-color': '#047857', 'circle-radius': 18 },
        });
        map.addLayer({
          id: 'cluster-count',
          type: 'symbol',
          source: 'points',
          filter: ['has', 'point_count'],
          layout: {
            'text-field': ['get', 'point_count_abbreviated'],
            'text-size': 12,
          },
          paint: { 'text-color': '#fff' },
        });
        map.addLayer({
          id: 'points',
          type: 'circle',
          source: 'points',
          filter: ['!', ['has', 'point_count']],
          paint: {
            'circle-color': '#dc2626',
            'circle-radius': 7,
            'circle-stroke-color': '#fff',
            'circle-stroke-width': 2,
          },
        });
        map.addLayer({
          id: 'highlighted-point',
          type: 'circle',
          source: 'points',
          filter: featureIdFilter(highlightedObjectId),
          paint: {
            'circle-color': '#f59e0b',
            'circle-radius': 11,
            'circle-stroke-color': '#fff',
            'circle-stroke-width': 3,
          },
        });
        map.addLayer({
          id: 'selected-point',
          type: 'circle',
          source: 'points',
          filter: featureIdFilter(selectedObjectId),
          paint: {
            'circle-color': '#dc2626',
            'circle-radius': 12,
            'circle-stroke-color': '#064e3b',
            'circle-stroke-width': 3,
          },
        });

        for (const layer of ['points', 'polygon-fill']) {
          map.on('click', layer, (event) => {
            const id = Number(event.features?.[0]?.properties?.id);
            const object = objects.find((item) => item.id === id);

            if (!object) {
              return;
            }

            openPopup(object, event.lngLat);
            onSelect?.(id);
          });
          map.on(
            'mouseenter',
            layer,
            () => (map.getCanvas().style.cursor = 'pointer'),
          );
          map.on(
            'mouseleave',
            layer,
            () => (map.getCanvas().style.cursor = ''),
          );
        }
      });

      map.on('error', () => {
        hasMapError = true;
      });

      resizeObserver = new ResizeObserver(() => {
        map?.resize();
      });

      if (container) {
        resizeObserver.observe(container);
      }
    } catch {
      hasMapError = true;
    }

    return () => {
      closePopup();
      resizeObserver?.disconnect();
      map?.remove();
    };
  });

  $effect(() => {
    void pointFeatures;
    void polygonFeatures;
    void mapLoaded;

    refreshSources();
  });
  $effect(() => {
    void highlightedObjectId;
    void selectedObjectId;

    refreshHighlightLayers();
  });
  $effect(() => {
    void fitBoundsVersion;
    void objects;
    void mapLoaded;

    if (fitBoundsVersion === 0) {
      return;
    }

    if (!map || !mapLoaded) {
      return;
    }

    if (objects.length === 0) {
      map.flyTo({ center: [19.1, 52.1], zoom: 5, duration: 1000 });
    } else {
      const bounds = calculateBounds();

      if (bounds) {
        map.fitBounds(bounds, { padding: 50, maxZoom: 12, duration: 1000 });
      }
    }
  });
</script>

{#if hasMapError}
  <div
    class="flex h-full min-h-112 items-center justify-center p-6 text-center text-sm text-stone-600"
  >
    Mapa jest chwilowo niedostępna. Użyj listy wyników poniżej.
  </div>
{:else}
  <div class="relative h-full min-h-112 w-full">
    <div
      bind:this={container}
      class="h-full w-full"
      aria-label="Interaktywna mapa obiektów. Lista wyników pozostaje alternatywą dostępną z klawiatury."
    ></div>
    <button
      type="button"
      class="absolute left-3 top-3 z-10 flex size-11 items-center justify-center rounded-lg border border-stone-200 bg-white text-stone-800 shadow-md transition hover:bg-stone-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-600 focus-visible:ring-offset-2"
      aria-label={isFullMap ? 'Przywróć widok dzielony' : 'Powiększ mapę'}
      title={isFullMap ? 'Przywróć widok dzielony' : 'Powiększ mapę'}
      onclick={onToggleFullMap}
    >
      <svg
        class="size-4 shrink-0"
        viewBox="0 0 24 24"
        fill="none"
        stroke="currentColor"
        stroke-width="2"
        stroke-linecap="round"
        stroke-linejoin="round"
        aria-hidden="true"
      >
        {#if isFullMap}
          <path d="M6 6l12 12M18 6L6 18" />
        {:else}
          <path d="m15 9 5-5m0 5V4h-5M9 15l-5 5m0-5v5h5" />
        {/if}
      </svg>
    </button>
  </div>
{/if}
