<?php

namespace App\Filament\Resources\SightseeingObjects\Tables;

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

class SightseeingObjectsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('thumbnail_url')
                    ->label('Zdjęcie')
                    ->imageSize(56)
                    ->checkFileExistence(false),
                TextColumn::make('title')
                    ->label('Nazwa')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('voivodeship.name')
                    ->label('Województwo')
                    ->sortable(),
                TextColumn::make('objectTypes.name')
                    ->label('Typy')
                    ->badge()
                    ->listWithLineBreaks()
                    ->limitList(3),
                IconColumn::make('is_unesco')
                    ->label('UNESCO')
                    ->boolean(),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'published' => 'Opublikowany',
                        default => 'Szkic',
                    })
                    ->color(fn (string $state): string => $state === 'published' ? 'success' : 'gray'),
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
                SelectFilter::make('voivodeship_id')
                    ->label('Województwo')
                    ->relationship('voivodeship', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('object_type')
                    ->label('Typ obiektu')
                    ->relationship('objectTypes', 'name')
                    ->searchable()
                    ->preload(),
                TernaryFilter::make('is_unesco')
                    ->label('UNESCO')
                    ->trueLabel('Tak')
                    ->falseLabel('Nie'),
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'draft' => 'Szkic',
                        'published' => 'Opublikowany',
                    ]),
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
