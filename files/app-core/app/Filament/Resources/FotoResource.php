<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FotoResource\Pages;
use App\Filament\Support\FotoUpload;
use App\Models\Album;
use App\Models\Attivita;
use App\Models\Foto;
use App\Services\GalleriaService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class FotoResource extends Resource
{
    protected static ?string $model = Foto::class;

    protected static ?string $navigationIcon = 'heroicon-o-photo';

    protected static ?string $navigationLabel = 'Galleria foto';

    protected static ?string $modelLabel = 'Foto';

    protected static ?string $pluralModelLabel = 'Foto';

    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                FotoUpload::make('percorso')
                    ->label('Immagine')
                    ->required()
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('didascalia')
                    ->maxLength(255),
                Forms\Components\Select::make('album_id')
                    ->label('Album (opzionale)')
                    ->options(fn () => Album::query()->orderByDesc('id')->pluck('titolo', 'id'))
                    ->searchable(),
                Forms\Components\Select::make('attivita_id')
                    ->label('Attività collegata (opzionale)')
                    ->options(fn () => Attivita::query()->orderByDesc('data_evento')->pluck('titolo', 'id'))
                    ->searchable(),
                Forms\Components\Toggle::make('in_archivio')
                    ->label("Solo nell'archivio (non visibile sul sito)")
                    ->helperText("Le foto in archivio non compaiono sul sito: restano a disposizione per quando crei un'attività o un album.")
                    ->inline(false)
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\ImageColumn::make('percorso')
                    ->label(''),
                Tables\Columns\TextColumn::make('didascalia'),
                Tables\Columns\TextColumn::make('album.titolo')
                    ->label('Album'),
                Tables\Columns\TextColumn::make('attivita.titolo')
                    ->label('Attività'),
                Tables\Columns\IconColumn::make('in_archivio')
                    ->label('Sul sito')
                    ->boolean()
                    ->trueIcon('heroicon-o-archive-box')
                    ->falseIcon('heroicon-o-eye')
                    ->trueColor('warning')
                    ->falseColor('success')
                    ->tooltip(fn (Foto $record): string => $record->in_archivio ? 'Solo archivio: non è visibile sul sito' : 'Visibile sul sito'),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('in_archivio')
                    ->label('Archivio')
                    ->placeholder('Tutte')
                    ->trueLabel("Solo nell'archivio")
                    ->falseLabel('Visibili sul sito'),
                Tables\Filters\TernaryFilter::make('album_id')
                    ->label('Album')
                    ->placeholder('Tutte')
                    ->trueLabel('In un album')
                    ->falseLabel('Senza album')
                    ->queries(
                        true: fn (Builder $query) => $query->whereNotNull('album_id'),
                        false: fn (Builder $query) => $query->whereNull('album_id'),
                        blank: fn (Builder $query) => $query,
                    ),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('inAlbum')
                        ->label('Metti in un album')
                        ->icon('heroicon-o-rectangle-stack')
                        ->modalHeading('Metti le foto selezionate in un album')
                        ->form([
                            Forms\Components\Select::make('album_id')
                                ->label('Album esistente')
                                ->options(fn () => Album::query()->orderByDesc('id')->pluck('titolo', 'id'))
                                ->searchable()
                                ->live()
                                ->helperText('Scegli un album oppure scrivi qui sotto il titolo di uno nuovo.'),
                            Forms\Components\TextInput::make('nuovo_titolo')
                                ->label('Oppure crea un nuovo album')
                                ->maxLength(255)
                                ->required(fn (Get $get): bool => blank($get('album_id'))),
                        ])
                        ->action(function (Collection $records, array $data): void {
                            $galleria = app(GalleriaService::class);

                            $album = filled($data['album_id'] ?? null)
                                ? Album::findOrFail($data['album_id'])
                                : $galleria->creaAlbum($data['nuovo_titolo'], null);

                            $galleria->spostaInAlbum($album, $records->modelKeys());

                            Notification::make()->success()
                                ->title($records->count()." foto nell'album \"".$album->titolo.'"')
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion(),
                    Tables\Actions\BulkAction::make('aAttivita')
                        ->label("Collega a un'attività")
                        ->icon('heroicon-o-calendar-days')
                        ->form([
                            Forms\Components\Select::make('attivita_id')
                                ->label('Attività')
                                ->options(fn () => Attivita::query()->orderByDesc('data_evento')->pluck('titolo', 'id'))
                                ->searchable()
                                ->required(),
                        ])
                        ->action(function (Collection $records, array $data): void {
                            Foto::whereIn('id', $records->modelKeys())->update([
                                'attivita_id' => $data['attivita_id'],
                                'in_archivio' => false,
                            ]);

                            Notification::make()->success()->title($records->count()." foto collegate all'attività")->send();
                        })
                        ->deselectRecordsAfterCompletion(),
                    Tables\Actions\BulkAction::make('inArchivio')
                        ->label("Rimetti nell'archivio")
                        ->icon('heroicon-o-archive-box')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->modalDescription("Le foto escono dall'album e dall'attività e non sono più visibili sul sito: restano a disposizione per riusarle.")
                        ->action(function (Collection $records): void {
                            app(GalleriaService::class)->mettiInArchivio($records->modelKeys());

                            Notification::make()->success()->title($records->count()." foto nell'archivio")->send();
                        })
                        ->deselectRecordsAfterCompletion(),
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFotos::route('/'),
            'edit' => Pages\EditFoto::route('/{record}/edit'),
        ];
    }
}
