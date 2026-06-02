<?php

namespace App\Filament\Resources\Articles\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ArticlesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('cover_thumbnail_url')
                    ->label('Okładka')
                    ->imageSize(56)
                    ->checkFileExistence(false),
                TextColumn::make('title')
                    ->label('Tytuł')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'published' => 'Opublikowana',
                        'archived' => 'Zarchiwizowana',
                        default => 'Szkic',
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'published' => 'success',
                        'archived' => 'warning',
                        default => 'gray',
                    }),
                IconColumn::make('is_featured')
                    ->label('Wyróżniona')
                    ->boolean(),
                TextColumn::make('author.name')
                    ->label('Autor')
                    ->toggleable(),
                TextColumn::make('published_at')
                    ->label('Publikacja')
                    ->dateTime('Y-m-d H:i')
                    ->sortable(),
                TextColumn::make('updated_at')
                    ->label('Aktualizacja')
                    ->dateTime('Y-m-d H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'draft' => 'Szkic',
                        'published' => 'Opublikowana',
                        'archived' => 'Zarchiwizowana',
                    ]),
                TernaryFilter::make('is_featured')
                    ->label('Wyróżniona')
                    ->trueLabel('Tak')
                    ->falseLabel('Nie'),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make()
                    ->requiresConfirmation()
                    ->visible(fn (): bool => auth()->user()?->isAdministrator() === true),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->requiresConfirmation()
                        ->visible(fn (): bool => auth()->user()?->isAdministrator() === true),
                ]),
            ])
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->latest('updated_at'));
    }
}
