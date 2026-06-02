<?php

namespace App\Filament\Resources\SightseeingObjects\Pages;

use App\Filament\Resources\SightseeingObjects\SightseeingObjectResource;
use App\Models\SightseeingObject;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Query\Expression;
use Illuminate\Support\Facades\DB;

class EditSightseeingObject extends EditRecord
{
    protected static string $resource = SightseeingObjectResource::class;

    /** @var array<int, string> */
    protected array $uploadedImages = [];

    protected ?string $imageAuthor = null;

    protected ?string $imageSource = null;

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
        $this->storeUploadedImages($this->record);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function captureImageUploadData(array &$data): void
    {
        $this->uploadedImages = array_values(array_filter((array) ($data['images'] ?? [])));
        $this->imageAuthor = filled($data['image_author'] ?? null) ? (string) $data['image_author'] : null;
        $this->imageSource = filled($data['image_source'] ?? null) ? (string) $data['image_source'] : null;

        unset($data['images'], $data['image_author'], $data['image_source']);
    }

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
        }

        unset($data['geometry_type'], $data['polygon_wkt']);

        return $data;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function geometryExpression(array $data): Expression
    {
        if (($data['geometry_type'] ?? 'point') === 'polygon') {
            $polygon = str_replace("'", "''", (string) $data['polygon_wkt']);

            return DB::raw("ST_GeomFromText('{$polygon}', 4326)");
        }

        $longitude = (float) $data['longitude'];
        $latitude = (float) $data['latitude'];

        return DB::raw(sprintf("ST_GeomFromText('POINT(%F %F)', 4326)", $longitude, $latitude));
    }

    protected function storeUploadedImages(SightseeingObject $record): void
    {
        foreach ($this->uploadedImages as $path) {
            $record
                ->addMediaFromDisk($path, 'public')
                ->withCustomProperties(array_filter([
                    'author' => $this->imageAuthor,
                    'source' => $this->imageSource,
                ]))
                ->toMediaCollection('images');
        }
    }
}
