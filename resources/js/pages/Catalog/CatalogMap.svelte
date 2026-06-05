<script>
    import maplibregl from 'maplibre-gl';
    import { onMount } from 'svelte';
    import 'maplibre-gl/dist/maplibre-gl.css';
    import { setPolishLanguage } from '@/lib/map-language';

    let {
        objects = [],
        highlightedObjectId = null,
        selectedObjectId = null,
        onSelect,
    } = $props();
    let container = $state();
    let map;
    let popup;
    let hasMapError = $state(false);

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
                    coordinates: [
                        Number(object.longitude),
                        Number(object.latitude),
                    ],
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

    function popupHtml(object) {
        return `<div class="w-56 overflow-hidden rounded-xl bg-white"><img class="h-28 w-full object-cover" src="${object.thumbnail_url || '/images/placeholder-object-thumb.jpg'}" alt=""><div class="p-3"><h3 class="font-bold leading-tight">${object.title}</h3><p class="mt-1 text-sm text-stone-600">${object.voivodeship?.name ?? ''}</p></div></div>`;
    }

    function refreshSources() {
        if (!map?.isStyleLoaded()) {
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
        if (!map?.isStyleLoaded() || !map.getLayer('highlighted-point')) {
            return;
        }

        map.setFilter(
            'highlighted-point',
            featureIdFilter(highlightedObjectId),
        );
        map.setFilter('selected-point', featureIdFilter(selectedObjectId));
        map.setFilter(
            'highlighted-polygon',
            polygonIdFilter(highlightedObjectId),
        );
        map.setFilter('selected-polygon', polygonIdFilter(selectedObjectId));
    }

    onMount(() => {
        try {
            map = new maplibregl.Map({
                container,
                style: 'https://tiles.openfreemap.org/styles/liberty',
                center: [19.1, 52.1],
                zoom: 5,
            });
            map.addControl(new maplibregl.NavigationControl(), 'top-right');
            map.on('load', () => {
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

                        const coordinates = event.lngLat;
                        popup?.remove();
                        popup = new maplibregl.Popup()
                            .setLngLat(coordinates)
                            .setHTML(popupHtml(object))
                            .addTo(map);
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
        } catch {
            hasMapError = true;
        }

        return () => {
            popup?.remove();
            map?.remove();
        };
    });

    $effect(() => {
        refreshSources();
    });
    $effect(() => {
        refreshHighlightLayers();
    });
</script>

{#if hasMapError}
    <div
        class="flex h-full min-h-[28rem] items-center justify-center p-6 text-center text-sm text-stone-600"
    >
        Mapa jest chwilowo niedostępna. Użyj listy wyników poniżej.
    </div>
{:else}
    <div
        bind:this={container}
        class="h-full min-h-[28rem] w-full"
        aria-label="Interaktywna mapa obiektów. Lista wyników pozostaje alternatywą dostępną z klawiatury."
    ></div>
{/if}
