<?php

namespace App\Filament\Resources\Users\Tables;

use App\Models\User;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nazwa')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('email')
                    ->label('E-mail')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('role')
                    ->label('Rola')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        User::ROLE_ADMINISTRATOR => 'Administrator',
                        User::ROLE_EDITOR => 'Edytor',
                        default => 'Brak roli',
                    })
                    ->color(fn (?string $state): string => $state === User::ROLE_ADMINISTRATOR ? 'warning' : 'gray'),
                IconColumn::make('email_verified_at')
                    ->label('E-mail zweryfikowany')
                    ->boolean(),
                TextColumn::make('updated_at')
                    ->label('Aktualizacja')
                    ->dateTime('Y-m-d H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make()
                    ->requiresConfirmation()
                    ->authorize('delete'),
            ]);
    }
}
