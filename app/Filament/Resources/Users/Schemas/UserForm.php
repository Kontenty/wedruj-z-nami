<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Models\User;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Validation\Rule;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Dane użytkownika')
                    ->schema([
                        TextInput::make('name')
                            ->label('Nazwa użytkownika')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true),
                        TextInput::make('email')
                            ->label('Adres e-mail')
                            ->email()
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->live(onBlur: true),
                        Select::make('role')
                            ->label('Rola')
                            ->options([
                                User::ROLE_ADMINISTRATOR => 'Administrator',
                                User::ROLE_EDITOR => 'Edytor',
                            ])
                            ->required()
                            ->rules([
                                Rule::in([
                                    User::ROLE_ADMINISTRATOR,
                                    User::ROLE_EDITOR,
                                ]),
                            ]),
                    ])
                    ->columns(2),

                Section::make('Hasło')
                    ->schema([
                        TextInput::make('password')
                            ->label('Hasło')
                            ->password()
                            ->revealable()
                            ->dehydrated(fn (?string $state): bool => filled($state))
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->same('password_confirmation')
                            ->helperText('Pozostaw puste podczas edycji, aby zachować obecne hasło.'),
                        TextInput::make('password_confirmation')
                            ->label('Potwierdzenie hasła')
                            ->password()
                            ->revealable()
                            ->dehydrated(false)
                            ->required(fn (Get $get): bool => filled($get('password'))),
                    ])
                    ->columns(2),
            ]);
    }
}
