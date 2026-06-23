<?php

namespace App\Filament\Resources\SightseeingObjects\Pages;

use App\Filament\Resources\SightseeingObjects\Pages\Concerns\InteractsWithSightseeingObjectGeometry;
use App\Filament\Resources\SightseeingObjects\SightseeingObjectResource;
use App\Models\SightseeingObject;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use InvalidArgumentException;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Throwable;

class EditSightseeingObject extends EditRecord
{
    use InteractsWithSightseeingObjectGeometry;

    protected static string $resource = SightseeingObjectResource::class;

    /** @var array<int, string> */
    protected array $uploadedImages = [];

    /** @var array<int, int> */
    protected array $mediaIdsToRemove = [];

    /** @var array<int, string> */
    protected array $formImagePaths = [];

    /** @var array<string, int> path → media ID for existing images */
    protected array $existingMediaMap = [];

    /** @var array<int, int> form path index → new media ID */
    protected array $newMediaIds = [];

    protected ?string $imageAuthor = null;

    protected ?string $imageSource = null;

    protected ?string $imageAlt = null;

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeFill(array $data): array
    {
        $geometry = $this->getRecordGeometryData();

        $data['geometry_type'] = $geometry['type'];
        $data['polygon_wkt'] = $geometry['polygon_wkt'];

        if ($geometry['type'] === 'point') {
            $data['latitude'] = $this->record->latitude;
            $data['longitude'] = $this->record->longitude;
        }

        $data['images'] = $this->getRecordImagePaths();

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

            $storedMediaIds = $this->storeUploadedImages($this->record);

            foreach ($this->mediaIdsToRemove as $mediaId) {
                $this->record->deleteMedia($mediaId);
            }

            $this->syncImageOrder();
            $this->refreshImageUploadState();
        } catch (Throwable $exception) {
            foreach ($storedMediaIds as $mediaId) {
                $this->record->deleteMedia($mediaId);
            }

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
        $this->formImagePaths = [];
        $this->existingMediaMap = [];
        $this->newMediaIds = [];
        $this->imageAuthor = null;
        $this->imageSource = null;
        $this->imageAlt = null;

        $allPaths = array_values(array_filter((array) ($data['images'] ?? [])));
        $existingMedia = $this->record
            ? $this->record->getMedia('images')
            : collect();
        $existingPaths = $existingMedia->map(fn ($media) => $media->getPathRelativeToRoot())->all();

        $this->uploadedImages = array_values(array_diff($allPaths, $existingPaths));
        $this->mediaIdsToRemove = $existingMedia
            ->filter(fn ($media) => ! in_array($media->getPathRelativeToRoot(), $allPaths, true))
            ->pluck('id')
            ->all();
        $this->formImagePaths = $allPaths;
        $this->existingMediaMap = $existingMedia
            ->keyBy(fn (Media $media) => $media->getPathRelativeToRoot())
            ->map(fn (Media $media) => $media->id)
            ->all();
        $this->imageAuthor = filled($data['image_author'] ?? null) ? (string) $data['image_author'] : null;
        $this->imageSource = filled($data['image_source'] ?? null) ? (string) $data['image_source'] : null;
        $this->imageAlt = filled($data['image_alt'] ?? null) ? (string) $data['image_alt'] : null;

        unset($data['images'], $data['image_author'], $data['image_source'], $data['image_alt']);
    }

    protected function validateImageOrderInput(): void
    {
        if (count($this->formImagePaths) !== count(array_unique($this->formImagePaths))) {
            throw new InvalidArgumentException('Submitted image paths must be unique.');
        }

        $knownPaths = array_flip([...array_keys($this->existingMediaMap), ...$this->uploadedImages]);

        foreach ($this->formImagePaths as $path) {
            if (! isset($knownPaths[$path])) {
                throw new InvalidArgumentException('Submitted image paths must reference object images or new uploads.');
            }
        }
    }

    /**
     * @return array<int, int>
     */
    protected function storeUploadedImages(SightseeingObject $record): array
    {
        $this->newMediaIds = [];

        foreach ($this->uploadedImages as $path) {
            $media = $record
                ->addMediaFromDisk($path, 'public')
                ->withCustomProperties(array_filter([
                    'author' => $this->imageAuthor,
                    'source' => $this->imageSource,
                    'alt' => $this->imageAlt,
                ]))
                ->toMediaCollection('images');

            $this->newMediaIds[] = $media->id;
        }

        return $this->newMediaIds;
    }

    /**
     * @return array<int, string>
     */
    protected function getRecordImagePaths(): array
    {
        return $this->record
            ->getMedia('images')
            ->map(fn (Media $media): string => $media->getPathRelativeToRoot())
            ->all();
    }

    protected function refreshImageUploadState(): void
    {
        $this->record->unsetRelation('media');

        $this->refreshFormData(['images']);
    }

    protected function syncImageOrder(): void
    {
        if ($this->formImagePaths === []) {
            return;
        }

        $newPathToId = array_combine($this->uploadedImages, $this->newMediaIds);

        $orderedIds = [];

        foreach ($this->formImagePaths as $path) {
            if (isset($this->existingMediaMap[$path])) {
                $orderedIds[] = $this->existingMediaMap[$path];
            } elseif (isset($newPathToId[$path])) {
                $orderedIds[] = $newPathToId[$path];
            }
        }

        $orderedIds = array_values(array_filter($orderedIds));

        if ($orderedIds !== []) {
            $this->record->reorderImages($orderedIds);
        }
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
