<?php

namespace App\Filament\Resources\ObjectTypes\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ObjectTypesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nazwa')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('sightseeing_objects_count')
                    ->label('Obiekty')
                    ->sortable(),
                TextColumn::make('updated_at')
                    ->label('Aktualizacja')
                    ->dateTime('Y-m-d H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
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
            ->modifyQueryUsing(fn (Builder $query): Builder => $query
                ->withCount('sightseeingObjects')
                ->orderBy('name'));
    }
}
