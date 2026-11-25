<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AlbumResource\Pages;
use App\Filament\Resources\AlbumResource\RelationManagers;
use App\Filament\Support\AzioniGalleria;
use App\Models\Album;
use App\Models\Attivita;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Support\Enums\FontWeight;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

/**
 * "Galleria foto" nel pannello: gli album si vedono come riquadri (con copertina,
 * numero di foto e di sotto-album), si cercano per nome o per attività e si filtrano.
 * Si entra in un album per modificarlo, cambiarne le foto e aggiungere sotto-album.
 */
class AlbumResource extends Resource
{
    protected static ?string $model = Album::class;

    protected static ?string $navigationIcon = 'heroicon-o-photo';

    protected static ?string $navigationLabel = 'Galleria foto';

    protected static ?string $modelLabel = 'Album';

    protected static ?string $pluralModelLabel = 'Album';

    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('titolo')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),
                Forms\Components\DatePicker::make('data_album')
                    ->label("Data dell'album")
                    ->helperText("Serve a metterlo in ordine cronologico sul sito. Se la lasci vuota vale la data dell'attività collegata, altrimenti quella di oggi. Per un album di foto vecchie scrivi la data in cui sono state scattate.")
                    ->displayFormat('d/m/Y')
                    ->maxDate(now()->addYear())
                    ->columnSpanFull(),
                Forms\Components\Select::make('parent_id')
                    ->label('Sta dentro un album principale (opzionale)')
                    ->helperText(fn (?Album $record): string => $record?->sottoAlbum()->exists()
                        ? 'Questo album contiene altri album, quindi non può stare dentro un altro album.'
                        : 'Per farne un sotto-album scegli il macro-album che lo contiene. Lascia vuoto per un album principale.')
                    ->options(fn (?Album $record) => AzioniGalleria::albumPrincipali($record?->id))
                    ->disabled(fn (?Album $record): bool => (bool) $record?->sottoAlbum()->exists())
                    ->searchable()
                    ->live()
                    ->columnSpanFull(),
                Forms\Components\Select::make('attivita_id')
                    ->label('Attività collegata (opzionale)')
                    ->helperText(fn (Get $get): ?string => filled($get('parent_id')) ? "Un sotto-album segue l'attività del suo macro-album." : 'I sotto-album di questo album seguono la stessa attività.')
                    ->options(fn () => Attivita::query()->orderByDesc('data_evento')->pluck('titolo', 'id'))
                    ->disabled(fn (Get $get): bool => filled($get('parent_id')))
                    ->searchable()
                    ->columnSpanFull(),
            ]);
    }

    /**
     * Le colonne della scheda di un album: copertina, titolo, riepilogo. Usate anche
     * nell'elenco dei sotto-album di un macro-album.
     *
     * @return array<int, Tables\Columns\Column|Tables\Columns\Layout\Component>
     */
    public static function colonneScheda(): array
    {
        return [
            Tables\Columns\Layout\Stack::make([
                Tables\Columns\ViewColumn::make('copertina')
                    ->view('filament.album-copertina'),
                Tables\Columns\TextColumn::make('titolo')
                    ->weight(FontWeight::Bold)
                    ->size(Tables\Columns\TextColumn\TextColumnSize::Large)
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('data')
                    ->state(fn (Album $record): ?string => $record->dataEffettiva()?->translatedFormat('j F Y'))
                    ->icon('heroicon-m-clock')
                    ->color('gray'),
                Tables\Columns\TextColumn::make('riepilogo')
                    ->state(function (Album $record): string {
                        $parti = [$record->fotoTotali().' foto'];

                        if ($record->sotto_album_count) {
                            $parti[] = $record->sotto_album_count.' album dentro';
                        }

                        if ($record->parent_id) {
                            $parti[] = 'sotto-album';
                        }

                        return implode(' · ', $parti);
                    })
                    ->color('gray'),
                Tables\Columns\TextColumn::make('macroAlbum.titolo')
                    ->prefix('Dentro: ')
                    ->searchable()
                    ->icon('heroicon-m-folder')
                    ->visible(fn (?Album $record): bool => true)
                    ->placeholder(null)
                    ->color('primary'),
                Tables\Columns\TextColumn::make('attivita.titolo')
                    ->prefix('Attività: ')
                    ->icon('heroicon-m-calendar-days')
                    ->searchable()
                    ->color('gray')
                    ->limit(40),
            ])->space(2),
        ];
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query
                ->with(['copertinaFoto', 'macroAlbum', 'attivita'])
                ->conConteggi()
                // Dal più recente: ogni macro-album è seguito dai suoi sotto-album (dal più vecchio).
                ->orderByRaw('case when album.parent_id is null then '.Album::sqlDataEffettiva('album')." else (select ".Album::sqlDataEffettiva('padre').' from album padre where padre.id = album.parent_id) end desc')
                ->orderByRaw('coalesce(album.parent_id, album.id) desc')
                ->orderByRaw('case when album.parent_id is null then 0 else 1 end')
                ->orderByRaw(Album::sqlDataEffettiva('album').' asc')
                ->orderBy('album.id'))
            ->contentGrid(['default' => 1, 'sm' => 2, 'lg' => 3, 'xl' => 4])
            ->paginated([12, 24, 48])
            ->defaultPaginationPageOption(24)
            ->searchPlaceholder('Cerca un album o un\'attività…')
            ->emptyStateHeading('Nessun album')
            ->emptyStateDescription('Crea un album, oppure un macro-album per raccogliere più album (per esempio i giorni di un evento).')
            ->emptyStateIcon('heroicon-o-photo')
            ->columns(static::colonneScheda())
            ->filters([
                Tables\Filters\SelectFilter::make('livello')
                    ->label('Tipo di album')
                    ->options([
                        'principali' => 'Album principali',
                        'macro' => 'Macro-album (con sotto-album)',
                        'sotto' => 'Sotto-album',
                        'singoli' => 'Album senza sotto-album',
                    ])
                    ->query(fn (Builder $query, array $data) => match ($data['value'] ?? null) {
                        'principali' => $query->whereNull('parent_id'),
                        'macro' => $query->whereNull('parent_id')->has('sottoAlbum'),
                        'sotto' => $query->whereNotNull('parent_id'),
                        'singoli' => $query->whereNull('parent_id')->doesntHave('sottoAlbum'),
                        default => $query,
                    }),
                Tables\Filters\SelectFilter::make('attivita_id')
                    ->label('Attività')
                    ->options(fn () => Attivita::query()->orderByDesc('data_evento')->pluck('titolo', 'id'))
                    ->searchable(),
                Tables\Filters\TernaryFilter::make('con_foto')
                    ->label('Foto')
                    ->placeholder('Tutti')
                    ->trueLabel('Con foto')
                    ->falseLabel('Senza foto (non visibili sul sito)')
                    ->queries(
                        true: fn (Builder $query) => $query->has('foto'),
                        false: fn (Builder $query) => $query->doesntHave('foto'),
                        blank: fn (Builder $query) => $query,
                    ),
            ])
            ->filtersFormColumns(3)
            ->actions([
                Tables\Actions\EditAction::make()->label('Apri e modifica'),
                Tables\Actions\DeleteAction::make()
                    ->modalDescription("Le foto dell'album non vengono cancellate: diventano foto singole, visibili a chi è iscritto. Se l'album contiene altri album, diventano album principali. Per eliminare le foto, cancellale prima dall'album."),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\SottoAlbumRelationManager::class,
            RelationManagers\FotoRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAlbums::route('/'),
            'edit' => Pages\EditAlbum::route('/{record}/edit'),
        ];
    }
}
