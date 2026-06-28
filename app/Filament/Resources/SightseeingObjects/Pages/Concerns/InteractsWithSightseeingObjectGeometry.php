<?php

namespace App\Filament\Resources\SightseeingObjects\Pages\Concerns;

use Illuminate\Database\Query\Expression;
use Illuminate\Support\Facades\DB;

trait InteractsWithSightseeingObjectGeometry
{
    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function prepareObjectData(array $data): array
    {
        $data['published'] = ($data['status'] ?? 'draft') === 'published';

        if ($data['published'] && blank($data['published_at'] ?? null)) {
            $data['published_at'] = now();
        }

        if (! $data['published']) {
            $data['published_at'] = null;
        }

        if (isset($data['geometry_type'])) {
            $data['geometry'] = $this->geometryExpression($data);

            if ($data['geometry_type'] === 'polygon') {
                $data['latitude'] = null;
                $data['longitude'] = null;
            }

            $this->clearStaleOsmMetadata($data);
        }

        unset($data['geometry_type'], $data['polygon_wkt'], $data['osm_geometry_wkt']);

        return $data;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function geometryExpression(array $data): Expression
    {
        if (($data['geometry_type'] ?? 'point') === 'polygon') {
            return DB::raw(sprintf(
                'ST_GeomFromText(%s, 4326)',
                $this->quoteGeometryWkt((string) $data['polygon_wkt'])
            ));
        }

        $longitude = (float) $data['longitude'];
        $latitude = (float) $data['latitude'];

        return DB::raw(sprintf("ST_GeomFromText('POINT(%F %F)', 4326)", $longitude, $latitude));
    }

    protected function quoteGeometryWkt(string $wkt): string
    {
        return DB::getPdo()->quote($wkt);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function clearStaleOsmMetadata(array &$data): void
    {
        $hasCurrentOsmMetadata = filled($data['osm_id'] ?? null) && filled($data['osm_type'] ?? null);
        $hasStoredOsmMetadata = filled($this->record->osm_id ?? null) && filled($this->record->osm_type ?? null);

        if (! $hasCurrentOsmMetadata && ! $hasStoredOsmMetadata) {
            return;
        }

        if (($data['geometry_type'] ?? 'point') !== 'polygon') {
            $data['osm_id'] = null;
            $data['osm_type'] = null;

            return;
        }

        $importedGeometry = trim((string) ($data['osm_geometry_wkt'] ?? $this->currentRecordGeometryWkt() ?? ''));
        $currentGeometry = trim((string) ($data['polygon_wkt'] ?? ''));

        if ($importedGeometry === '' || $currentGeometry !== $importedGeometry) {
            $data['osm_id'] = null;
            $data['osm_type'] = null;
        }
    }

    protected function currentRecordGeometryWkt(): ?string
    {
        if (! isset($this->record) || $this->record === null) {
            return null;
        }

        return $this->record
            ->newQuery()
            ->whereKey($this->record)
            ->selectRaw('ST_AsText(geometry) as geometry_wkt')
            ->value('geometry_wkt');
    }
}
