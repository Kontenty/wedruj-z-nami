<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Sleep;

class NominatimService
{
    private const ENDPOINT = 'https://nominatim.openstreetmap.org/search';

    /**
     * @return array{wkt: string, osm_id: string, osm_type: string}|null
     */
    public function searchPolygon(string $name): ?array
    {
        Sleep::for(1)->second();

        $response = Http::withHeaders([
            'User-Agent' => 'wedruj-z-nami/1.0 (kontakt@wedruj-z-nami.pl)',
        ])->get(self::ENDPOINT, [
            'q' => $name,
            'format' => 'json',
            'polygon_geojson' => 1,
            'limit' => 1,
        ]);

        if (! $response->successful()) {
            return null;
        }

        $results = $response->json();

        if ($results === [] || $results === null) {
            return null;
        }

        $result = $results[0];
        $geojson = $result['geojson'] ?? null;

        if ($geojson === null) {
            return null;
        }

        $wkt = $this->geojsonToWkt($geojson);

        if ($wkt === null) {
            return null;
        }

        return [
            'wkt' => $wkt,
            'osm_id' => (string) $result['osm_id'],
            'osm_type' => (string) $result['osm_type'],
        ];
    }

    /**
     * @param  array<string, mixed>  $geojson
     */
    private function geojsonToWkt(array $geojson): ?string
    {
        $type = $geojson['type'] ?? null;
        $coordinates = $geojson['coordinates'] ?? null;

        if ($type === null || $coordinates === null) {
            return null;
        }

        return match ($type) {
            'Polygon' => $this->polygonToWkt($coordinates),
            'MultiPolygon' => $this->multiPolygonToWkt($coordinates),
            default => null,
        };
    }

    /**
     * @param  array<int, array<int, array<int, float>>>  $coordinates
     */
    private function polygonToWkt(array $coordinates): ?string
    {
        if ($coordinates === []) {
            return null;
        }

        $rings = array_map(
            fn (array $ring): string => '('.$this->ringToWkt($ring).')',
            $coordinates,
        );

        return 'POLYGON('.implode(',', $rings).')';
    }

    /**
     * @param  array<int, array<int, array<int, array<int, float>>>>  $coordinates
     */
    private function multiPolygonToWkt(array $coordinates): ?string
    {
        if ($coordinates === []) {
            return null;
        }

        $polygons = array_map(
            fn (array $polygon): string => '('.implode(',', array_map(
                fn (array $ring): string => '('.$this->ringToWkt($ring).')',
                $polygon,
            )).')',
            $coordinates,
        );

        return 'MULTIPOLYGON('.implode(',', $polygons).')';
    }

    /**
     * @param  array<int, array<int, float>>  $ring
     */
    private function ringToWkt(array $ring): string
    {
        return implode(', ', array_map(
            fn (array $point): string => $point[0].' '.$point[1],
            $ring,
        ));
    }
}
