<?php

namespace App\Filament\Resources\SightseeingObjects\Pages;

use App\Filament\Resources\SightseeingObjects\Pages\Concerns\InteractsWithSightseeingObjectGeometry;
use App\Filament\Resources\SightseeingObjects\SightseeingObjectResource;
use App\Models\SightseeingObject;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Throwable;

class EditSightseeingObject extends EditRecord
{
    use InteractsWithSightseeingObjectGeometry;

    protected static string $resource = SightseeingObjectResource::class;

    protected ?bool $hasDatabaseTransactions = true;

    /** @var array<int, string> */
    protected array $uploadedImages = [];

    /** @var array<int, int> */
    protected array $mediaIdsToRemove = [];

    /** @var array<int, array{path: string, author: string|null, source: string|null, description: string|null, alt: string|null}> */
    protected array $imageItems = [];

    /** @var array<string, Media> path → existing media */
    protected array $existingMediaMap = [];

    /** @var array<int, int> */
    protected array $newMediaIds = [];

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeFill(array $data): array
    {
        $geometry = $this->getRecordGeometryData();

        $data['geometry_type'] = $geometry['type'];
        $data['polygon_wkt'] = $geometry['polygon_wkt'];
        $data['osm_geometry_wkt'] = filled($data['osm_id'] ?? null) && filled($data['osm_type'] ?? null)
            ? $geometry['polygon_wkt']
            : null;

        if ($geometry['type'] === 'point') {
            $data['latitude'] = $this->record->latitude;
            $data['longitude'] = $this->record->longitude;
        }

        $data['images'] = $this->getRecordImageItems();

        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->requiresConfirmation()
                ->visible(fn (): bool => auth()->user()?->isAdministrator() === true),
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        $this->captureImageUploadData($data);

        return $this->prepareObjectData($data);
    }

    protected function afterSave(): void
    {
        $storedMediaIds = [];

        try {
            $this->validateImageOrderInput();

            $this->storeUploadedImages($this->record, $storedMediaIds);

            $this->syncImageOrder();
            $this->deleteRemovedMediaAfterCommit($this->record);
        } catch (Throwable $exception) {
            $this->deleteStoredMedia($this->record, $storedMediaIds);

            throw $exception;
        }
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function captureImageUploadData(array &$data): void
    {
        $this->uploadedImages = [];
        $this->mediaIdsToRemove = [];
        $this->existingMediaMap = [];
        $this->newMediaIds = [];
        $existingMedia = $this->record
            ? $this->record->getMedia('images')
            : collect();

        $this->imageItems = array_values(array_filter(array_map(function (mixed $item): array {
            $item['path'] = $this->normalizeImagePath($item['path'] ?? null);

            return $item;
        }, (array) ($data['images'] ?? [])), fn (array $item): bool => filled($item['path'])));
        $this->mediaIdsToRemove = $existingMedia
            ->filter(fn (Media $media): bool => ! in_array($media->getPathRelativeToRoot(), array_column($this->imageItems, 'path'), true))
            ->pluck('id')
            ->all();
        $this->existingMediaMap = $existingMedia
            ->keyBy(fn (Media $media) => $media->getPathRelativeToRoot())
            ->all();
        $this->uploadedImages = array_values(array_filter(array_column($this->imageItems, 'path'), fn (string $path): bool => ! isset($this->existingMediaMap[$path])));

        unset($data['images']);
    }

    protected function validateImageOrderInput(): void
    {
        $paths = array_column($this->imageItems, 'path');

        if (count($paths) !== count(array_unique($paths))) {
            throw new InvalidArgumentException('Submitted image paths must be unique.');
        }

        $knownPaths = array_flip([...array_keys($this->existingMediaMap), ...$this->uploadedImages]);

        foreach ($paths as $path) {
            if (! isset($knownPaths[$path])) {
                throw new InvalidArgumentException('Submitted image paths must reference object images or new uploads.');
            }
        }
    }

    /** @param array<int, int> $storedMediaIds */
    protected function storeUploadedImages(SightseeingObject $record, array &$storedMediaIds): void
    {
        $this->newMediaIds = [];

        foreach ($this->imageItems as $item) {
            if (isset($this->existingMediaMap[$item['path']])) {
                $this->updateMediaProperties($this->existingMediaMap[$item['path']], $item);

                continue;
            }

            $media = $record
                ->addMediaFromDisk($item['path'], 'public')
                ->withCustomProperties($this->mediaProperties($item))
                ->toMediaCollection('images');

            $this->newMediaIds[] = $media->id;
            $storedMediaIds[] = $media->id;
        }
    }

    /**
     * @return array<int, array{media_id: int, path: string, author: string|null, source: string|null, description: string|null, alt: string|null}>
     */
    protected function getRecordImageItems(): array
    {
        return $this->record
            ->getMedia('images')
            ->map(fn (Media $media): array => [
                'media_id' => $media->id,
                'path' => [$media->getPathRelativeToRoot()],
                'author' => $media->getCustomProperty('author'),
                'source' => $media->getCustomProperty('source'),
                'description' => $media->getCustomProperty('description'),
                'alt' => $media->getCustomProperty('alt'),
            ])
            ->all();
    }

    protected function refreshImageUploadState(): void
    {
        $this->record->unsetRelation('media');

        $this->refreshFormData(['images']);
    }

    protected function syncImageOrder(): void
    {
        if ($this->imageItems === []) {
            return;
        }

        $newPathToId = array_combine($this->uploadedImages, $this->newMediaIds);

        $orderedIds = [];

        foreach ($this->imageItems as $item) {
            if (isset($this->existingMediaMap[$item['path']])) {
                $orderedIds[] = $this->existingMediaMap[$item['path']]->id;
            } elseif (isset($newPathToId[$item['path']])) {
                $orderedIds[] = $newPathToId[$item['path']];
            }
        }

        $orderedIds = array_values(array_filter($orderedIds));
        $orderedIds = [...$orderedIds, ...$this->mediaIdsToRemove];

        if ($orderedIds !== []) {
            $this->record->reorderImages($orderedIds);
        }
    }

    /** @param array{author?: mixed, source?: mixed, description?: mixed, alt?: mixed} $item */
    private function updateMediaProperties(Media $media, array $item): void
    {
        foreach (['author', 'source', 'description', 'alt'] as $property) {
            $value = $this->mediaProperties($item)[$property] ?? null;

            if ($value === null) {
                $media->forgetCustomProperty($property);
            } else {
                $media->setCustomProperty($property, $value);
            }
        }

        $media->save();
    }

    private function deleteRemovedMediaAfterCommit(SightseeingObject $record): void
    {
        $mediaIdsToRemove = $this->mediaIdsToRemove;

        if ($mediaIdsToRemove === []) {
            $this->refreshImageUploadState();

            return;
        }

        DB::afterCommit(function () use ($record, $mediaIdsToRemove): void {
            foreach ($mediaIdsToRemove as $mediaId) {
                try {
                    $record->media()->whereKey($mediaId)->first()?->delete();
                } catch (Throwable $exception) {
                    report($exception);
                }
            }

            $this->refreshImageUploadState();
        });
    }

    /** @param array<int, int> $storedMediaIds */
    private function deleteStoredMedia(SightseeingObject $record, array $storedMediaIds): void
    {
        foreach ($storedMediaIds as $mediaId) {
            try {
                $record->media()->whereKey($mediaId)->first()?->delete();
            } catch (Throwable $exception) {
                report($exception);
            }
        }
    }

    /** @param array{author?: mixed, source?: mixed, description?: mixed, alt?: mixed} $item */
    private function mediaProperties(array $item): array
    {
        return array_filter([
            'author' => filled($item['author'] ?? null) ? (string) $item['author'] : null,
            'source' => filled($item['source'] ?? null) ? (string) $item['source'] : null,
            'description' => filled($item['description'] ?? null) ? (string) $item['description'] : null,
            'alt' => filled($item['alt'] ?? null) ? (string) $item['alt'] : null,
        ], static fn (?string $value): bool => $value !== null);
    }

    private function normalizeImagePath(mixed $path): ?string
    {
        if (is_array($path)) {
            $path = array_values(array_filter($path))[0] ?? null;
        }

        return filled($path) ? (string) $path : null;
    }

    /**
     * @return array{type: string, polygon_wkt: string|null}
     */
    private function getRecordGeometryData(): array
    {
        $geometry = $this->record
            ->newQuery()
            ->whereKey($this->record)
            ->selectRaw('ST_GeometryType(geometry) as geometry_type_name')
            ->selectRaw('ST_AsText(geometry) as geometry_wkt')
            ->first();

        $geometryType = strtolower((string) data_get($geometry, 'geometry_type_name'));

        if (str_contains($geometryType, 'polygon')) {
            return [
                'type' => 'polygon',
                'polygon_wkt' => data_get($geometry, 'geometry_wkt'),
            ];
        }

        return [
            'type' => 'point',
            'polygon_wkt' => null,
        ];
    }
}
