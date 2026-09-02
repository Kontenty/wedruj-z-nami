<?php

namespace App\Filament\Resources\Articles\Schemas;

use App\Services\ReadableMediaFilename;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Validation\Rule;

class ArticleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Treść')
                    ->schema([
                        TextInput::make('title')
                            ->label('Tytuł')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('slug')
                            ->label('Slug')
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->helperText('Pozostaw puste, aby wygenerować automatycznie z tytułu.'),
                        Textarea::make('excerpt')
                            ->label('Zajawka')
                            ->rows(3)
                            ->columnSpanFull(),
                        MarkdownEditor::make('body')
                            ->label('Treść')
                            ->required()
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Section::make('Publikacja')
                    ->schema([
                        Select::make('status')
                            ->label('Status')
                            ->options([
                                'draft' => 'Szkic',
                                'published' => 'Opublikowana',
                                'archived' => 'Zarchiwizowana',
                            ])
                            ->default('draft')
                            ->required()
                            ->rules([Rule::in(['draft', 'published', 'archived'])]),
                        DateTimePicker::make('published_at')
                            ->label('Data publikacji')
                            ->seconds(false),
                        Toggle::make('is_featured')
                            ->label('Wyróżniona aktualność'),
                    ])
                    ->columns(3),

                Section::make('Okładka')
                    ->schema([
                        FileUpload::make('cover_image')
                            ->label('Zdjęcie okładkowe')
                            ->image()
                            ->disk('public')
                            ->directory('cms/article-covers')
                            ->getUploadedFileNameForStorageUsing(fn ($file): string => ReadableMediaFilename::make($file))
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                            ->maxSize(5120)
                            ->helperText('Pojedyncze zdjęcie używane jako okładka aktualności.'),
                        TextInput::make('cover_author')
                            ->label('Autor zdjęcia')
                            ->maxLength(255),
                        TextInput::make('cover_source')
                            ->label('Źródło zdjęcia')
                            ->maxLength(255),
                    ])
                    ->columns(2),
            ]);
    }
}
