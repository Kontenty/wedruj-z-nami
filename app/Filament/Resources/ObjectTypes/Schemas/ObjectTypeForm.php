<?php

namespace App\Filament\Resources\ObjectTypes\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ObjectTypeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nazwa')
                    ->required()
                    ->maxLength(255),
                TextInput::make('slug')
                    ->label('Slug')
                    ->maxLength(255)
                    ->unique(ignoreRecord: true)
                    ->helperText('Pozostaw puste, aby wygenerować automatycznie z nazwy.'),
                Textarea::make('description')
                    ->label('Opis')
                    ->rows(4)
                    ->columnSpanFull(),
            ]);
    }
}
