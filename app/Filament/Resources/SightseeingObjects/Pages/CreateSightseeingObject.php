<?php

namespace App\Filament\Resources\SightseeingObjects\Pages;

use App\Filament\Resources\SightseeingObjects\Pages\Concerns\InteractsWithSightseeingObjectGeometry;
use App\Filament\Resources\SightseeingObjects\SightseeingObjectResource;
use App\Models\SightseeingObject;
use Filament\Resources\Pages\CreateRecord;

class CreateSightseeingObject extends CreateRecord
{
    use InteractsWithSightseeingObjectGeometry;

    protected static string $resource = SightseeingObjectResource::class;

    /** @var array<int, string> */
    protected array $uploadedImages = [];

    protected ?string $imageAuthor = null;

    protected ?string $imageSource = null;

    protected ?string $imageAlt = null;

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
        $this->storeUploadedImages($this->record);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function captureImageUploadData(array &$data): void
    {
        $this->uploadedImages = [];
        $this->imageAuthor = null;
        $this->imageSource = null;
        $this->imageAlt = null;

        $this->uploadedImages = array_values(array_filter((array) ($data['images'] ?? [])));
        $this->imageAuthor = filled($data['image_author'] ?? null) ? (string) $data['image_author'] : null;
        $this->imageSource = filled($data['image_source'] ?? null) ? (string) $data['image_source'] : null;
        $this->imageAlt = filled($data['image_alt'] ?? null) ? (string) $data['image_alt'] : null;

        unset($data['images'], $data['image_author'], $data['image_source'], $data['image_alt']);
    }

    protected function storeUploadedImages(SightseeingObject $record): void
    {
        foreach ($this->uploadedImages as $path) {
            $record
                ->addMediaFromDisk($path, 'public')
                ->withCustomProperties(array_filter([
                    'author' => $this->imageAuthor,
                    'source' => $this->imageSource,
                    'alt' => $this->imageAlt,
                ]))
                ->toMediaCollection('images');
        }
    }
}
