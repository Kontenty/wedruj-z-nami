<script>
    import maplibregl from 'maplibre-gl';
    import { onMount } from 'svelte';
    import 'maplibre-gl/dist/maplibre-gl.css';
    import { setPolishLanguage } from '@/lib/map-language';

    let { lat, lng, geojson, title } = $props();
    let container = $state();
    let map;
    let hasMapError = $state(false);

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
                lat !== null &&
                lat !== undefined &&
                lng !== null &&
                lng !== undefined;
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

                if (
                    geometry &&
                    ['Polygon', 'MultiPolygon'].includes(geometry.type)
                ) {
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

                new maplibregl.Marker({ color: '#dc2626' })
                    .setLngLat(pointCoordinates)
                    .setPopup(
                        new maplibregl.Popup().setHTML(
                            `<strong>${title}</strong>`,
                        ),
                    )
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
    class="mb-8 overflow-hidden rounded-2xl border border-stone-200 bg-white shadow-sm print-hidden"
    aria-labelledby="object-map-heading"
>
    <h2 id="object-map-heading" class="sr-only">Mapa obiektu</h2>

    {#if hasMapError}
        <p class="p-4 text-sm text-stone-600">
            Mapa jest chwilowo niedostępna. Pozostałe informacje o obiekcie są
            dostępne poniżej.
        </p>
    {:else}
        <div
            bind:this={container}
            class="h-64 w-full md:h-80"
            aria-label={`Mapa obiektu ${title}`}
        ></div>
    {/if}
</section>
