import { Map, NavigationControl, setWorkerUrl } from 'maplibre-gl';
import workerUrl from 'maplibre-gl/dist/maplibre-gl-worker.mjs?worker&url';
import 'maplibre-gl/dist/maplibre-gl.css';
import { setPolishLanguage } from '@/lib/map-language';

setWorkerUrl(workerUrl);

const MAP_STYLE = 'https://tiles.openfreemap.org/styles/liberty';
const DEFAULT_CENTER = [19.1, 52.1] as [number, number];

export function createMap(
  container: HTMLElement,
  center: [number, number] = DEFAULT_CENTER,
  zoom = 5,
): Map {
  const map = new Map({
    container,
    style: MAP_STYLE,
    center,
    zoom,
  });

  map.addControl(new NavigationControl(), 'top-right');
  map.on('load', () => setPolishLanguage(map));

  return map;
}
