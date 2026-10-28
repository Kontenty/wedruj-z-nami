<?php

namespace App\Filament\Resources\ObjectCollections\Pages;

use App\Filament\Resources\ObjectCollections\ObjectCollectionResource;
use App\Models\ObjectCollection;
use Filament\Resources\Pages\CreateRecord;

class CreateObjectCollection extends CreateRecord
{
    protected static string $resource = ObjectCollectionResource::class;

    protected ?bool $hasDatabaseTransactions = true;

    /** @var array<int, int> */
    protected array $memberIds = [];

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $this->memberIds = $this->extractMemberIds($data['members'] ?? []);
        unset($data['members']);

        return $data;
    }

    protected function afterCreate(): void
    {
        $this->syncMembers($this->record);
    }

    /**
     * @param  array<int|string, array{sightseeing_object_id?: int|string|null}>  $members
     * @return array<int, int>
     */
    private function extractMemberIds(array $members): array
    {
        return array_values(array_map(
            static fn (array $member): int => (int) $member['sightseeing_object_id'],
            $members,
        ));
    }

    private function syncMembers(ObjectCollection $record): void
    {
        $record->sightseeingObjects()->sync(collect($this->memberIds)
            ->mapWithKeys(fn (int $id, int $position): array => [$id => ['position' => $position + 1]])
            ->all());
    }
}
