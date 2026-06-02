<?php

namespace App\Filament\Resources\ObjectTypes\Schemas;

use App\Models\ObjectType;
use Closure;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

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
                Select::make('parent_id')
                    ->label('Typ nadrzędny')
                    ->relationship(
                        name: 'parent',
                        titleAttribute: 'name',
                        modifyQueryUsing: fn (Builder $query): Builder => $query->orderBy('name'),
                        ignoreRecord: true,
                    )
                    ->searchable()
                    ->preload()
                    ->rules(fn (?ObjectType $record): array => [
                        function (string $attribute, mixed $value, Closure $fail) use ($record): void {
                            $parentId = filled($value) ? (int) $value : null;
                            $objectType = $record ?? new ObjectType;

                            if ($objectType->wouldCreateParentLoop($parentId)) {
                                $fail('Typ obiektu nie może być swoim własnym potomkiem.');
                            }

                            if ($objectType->wouldExceedMaximumDepth($parentId)) {
                                $fail('Hierarchia typów obiektów może mieć maksymalnie 3 poziomy.');
                            }
                        },
                    ]),
                Textarea::make('description')
                    ->label('Opis')
                    ->rows(4)
                    ->columnSpanFull(),
            ]);
    }
}
