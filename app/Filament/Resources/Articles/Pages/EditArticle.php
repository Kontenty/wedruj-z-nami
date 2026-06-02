<?php

namespace App\Filament\Resources\Articles\Pages;

use App\Filament\Resources\Articles\ArticleResource;
use App\Models\Article;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditArticle extends EditRecord
{
    protected static string $resource = ArticleResource::class;

    protected ?string $uploadedCoverImage = null;

    protected ?string $coverAuthor = null;

    protected ?string $coverSource = null;

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
        $this->captureCoverUploadData($data);

        return $this->prepareArticleData($data);
    }

    protected function afterSave(): void
    {
        $this->storeUploadedCover($this->record);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function captureCoverUploadData(array &$data): void
    {
        $coverImage = $data['cover_image'] ?? null;
        $this->uploadedCoverImage = is_array($coverImage) ? reset($coverImage) ?: null : $coverImage;
        $this->coverAuthor = filled($data['cover_author'] ?? null) ? (string) $data['cover_author'] : null;
        $this->coverSource = filled($data['cover_source'] ?? null) ? (string) $data['cover_source'] : null;

        unset($data['cover_image'], $data['cover_author'], $data['cover_source']);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function prepareArticleData(array $data): array
    {
        $data['published'] = ($data['status'] ?? 'draft') === 'published';

        if ($data['published'] && blank($data['published_at'] ?? null)) {
            $data['published_at'] = now();
        }

        if (! $data['published']) {
            $data['published_at'] = null;
        }

        return $data;
    }

    protected function storeUploadedCover(Article $record): void
    {
        if (blank($this->uploadedCoverImage)) {
            return;
        }

        $record
            ->addMediaFromDisk($this->uploadedCoverImage, 'public')
            ->withCustomProperties(array_filter([
                'author' => $this->coverAuthor,
                'source' => $this->coverSource,
            ]))
            ->toMediaCollection('cover');
    }
}
