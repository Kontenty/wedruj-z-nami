<?php

namespace App\Filament\Resources\SightseeingObjects\Pages;

use App\Filament\Resources\SightseeingObjects\Pages\Concerns\InteractsWithSightseeingObjectGeometry;
use App\Filament\Resources\SightseeingObjects\SightseeingObjectResource;
use App\Models\SightseeingObject;
use Filament\Resources\Pages\CreateRecord;
use Throwable;

class CreateSightseeingObject extends CreateRecord
{
    use InteractsWithSightseeingObjectGeometry;

    protected static string $resource = SightseeingObjectResource::class;

    protected ?bool $hasDatabaseTransactions = true;

    /** @var array<int, array{path: string, author: string|null, source: string|null, description: string|null, alt: string|null}> */
    protected array $imageItems = [];

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $this->captureImageUploadData($data);

        $data['author_id'] = auth()->id();

        return $this->prepareObjectData($data);
    }

    protected function afterCreate(): void
    {
        $storedMediaIds = [];

        try {
            $this->storeUploadedImages($this->record, $storedMediaIds);
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
        $this->imageItems = array_values(array_filter(array_map(function (mixed $item): array {
            $item['path'] = $this->normalizeImagePath($item['path'] ?? null);

            return $item;
        }, (array) ($data['images'] ?? [])), fn (array $item): bool => filled($item['path'])));

        unset($data['images']);
    }

    /** @param array<int, int> $storedMediaIds */
    protected function storeUploadedImages(SightseeingObject $record, array &$storedMediaIds): void
    {
        foreach ($this->imageItems as $item) {
            $media = $record
                ->addMediaFromDisk($item['path'], 'public')
                ->withCustomProperties(array_filter([
                    'author' => filled($item['author'] ?? null) ? (string) $item['author'] : null,
                    'source' => filled($item['source'] ?? null) ? (string) $item['source'] : null,
                    'description' => filled($item['description'] ?? null) ? (string) $item['description'] : null,
                    'alt' => filled($item['alt'] ?? null) ? (string) $item['alt'] : null,
                ]))
                ->toMediaCollection('images');

            $storedMediaIds[] = $media->getKey();
        }
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

    private function normalizeImagePath(mixed $path): ?string
    {
        if (is_array($path)) {
            $path = array_values(array_filter($path))[0] ?? null;
        }

        return filled($path) ? (string) $path : null;
    }
}
