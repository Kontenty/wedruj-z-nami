<script>
    import maplibregl from 'maplibre-gl';
    import { onMount } from 'svelte';
    import 'maplibre-gl/dist/maplibre-gl.css';

    let { lat, lng, geojson, title } = $props();
    let container;
    let map;

    onMount(() => {
        map = new maplibregl.Map({
            container,
            style: 'https://tiles.openfreemap.org/styles/liberty',
            center: [lng, lat],
            zoom: 13,
        });

        map.addControl(new maplibregl.NavigationControl(), 'top-right');

        map.on('load', () => {
            const geometry = typeof geojson === 'string' ? JSON.parse(geojson) : geojson;

            if (geometry && ['Polygon', 'MultiPolygon'].includes(geometry.type)) {
                map.addSource('object-area', { type: 'geojson', data: geometry });
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
                const coordinates = geometry.type === 'Polygon'
                    ? geometry.coordinates[0]
                    : geometry.coordinates[0][0];
                coordinates.forEach((c) => bounds.extend(c));
                map.fitBounds(bounds, { padding: 32 });
            } else {
                new maplibregl.Marker({ color: '#dc2626' })
                    .setLngLat([lng, lat])
                    .setPopup(new maplibregl.Popup().setHTML(`<strong>${title}</strong>`))
                    .addTo(map);
            }
        });

        return () => map.remove();
    });
</script>

<section class="mb-8 overflow-hidden rounded-2xl border border-stone-200 bg-white shadow-sm">
    <div bind:this={container} class="h-64 w-full md:h-80"></div>
</section>
