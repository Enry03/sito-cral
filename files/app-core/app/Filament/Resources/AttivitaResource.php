<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AttivitaResource\Pages;
use App\Filament\Resources\AttivitaResource\RelationManagers;
use App\Filament\Support\FotoArchivio;
use App\Filament\Support\FotoUpload;
use App\Models\Attivita;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class AttivitaResource extends Resource
{
    protected static ?string $model = Attivita::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';

    protected static ?string $navigationLabel = 'Attività';

    protected static ?string $modelLabel = 'Attività';

    protected static ?string $pluralModelLabel = 'Attività';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Dettagli')
                    ->schema([
                        Forms\Components\TextInput::make('titolo')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),
                        Forms\Components\RichEditor::make('descrizione')
                            ->required()
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('luogo')
                            ->maxLength(255),
                        Forms\Components\Toggle::make('pubblicata')
                            ->label('Visibile sul sito pubblico')
                            ->default(true)
                            ->inline(false),
                        FotoUpload::make('immagine_copertina', 'attivita')
                            ->label('Immagine di copertina')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Quando')
                    ->description('Un giorno solo, un periodo più lungo (es. gita dal 10 al 15 agosto) oppure un evento che si ripete in più date.')
                    ->schema([
                        Forms\Components\DateTimePicker::make('data_evento')
                            ->label('Data e ora di inizio')
                            ->seconds(false)
                            ->displayFormat('d/m/Y H:i')
                            ->required(),

                        Forms\Components\Toggle::make('multi_giorno')
                            ->label("L'attività dura più giorni")
                            ->dehydrated(false)
                            ->live()
                            ->inline(false)
                            ->afterStateHydrated(function (Forms\Components\Toggle $component, ?Attivita $record) {
                                $component->state(filled($record?->data_fine));
                            })
                            ->afterStateUpdated(function ($state, Set $set) {
                                if (! $state) {
                                    $set('data_fine', null);
                                }
                            }),

                        Forms\Components\DatePicker::make('data_fine')
                            ->label('Ultimo giorno')
                            ->displayFormat('d/m/Y')
                            ->visible(fn (Get $get): bool => (bool) $get('multi_giorno'))
                            ->required(fn (Get $get): bool => (bool) $get('multi_giorno'))
                            ->afterOrEqual('data_evento')
                            ->dehydratedWhenHidden(),

                        Forms\Components\Toggle::make('ricorrente')
                            ->label('Evento ricorrente')
                            ->helperText('Spunta se l\'evento si ripete in altre date: potrai indicarle qui sotto.')
                            ->live()
                            ->inline(false)
                            ->columnSpanFull(),

                        Forms\Components\Repeater::make('date_ricorrenti')
                            ->label('Altre date in cui ricorre l\'evento')
                            ->simple(
                                Forms\Components\DatePicker::make('data')
                                    ->label('Data')
                                    ->displayFormat('d/m/Y')
                                    ->required()
                            )
                            ->addActionLabel('Aggiungi una data')
                            ->visible(fn (Get $get): bool => (bool) $get('ricorrente'))
                            ->minItems(fn (Get $get): int => $get('ricorrente') ? 1 : 0)
                            ->helperText('La data di inizio indicata sopra è la prima; qui aggiungi le successive. L\'orario è lo stesso.')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Iscrizioni dei soci')
                    ->description('Solo i soci CRAL con tessera possono iscriversi (non basta l\'iscrizione al sito).')
                    ->schema([
                        Forms\Components\Toggle::make('iscrizione_abilitata')
                            ->label('Permetti ai soci di iscriversi a questa attività')
                            ->live()
                            ->inline(false)
                            ->columnSpanFull(),

                        Forms\Components\DateTimePicker::make('iscrizioni_apertura')
                            ->label('Le iscrizioni aprono il')
                            ->seconds(false)
                            ->displayFormat('d/m/Y H:i')
                            ->visible(fn (Get $get): bool => (bool) $get('iscrizione_abilitata'))
                            ->required(fn (Get $get): bool => (bool) $get('iscrizione_abilitata')),
                        Forms\Components\DateTimePicker::make('iscrizioni_chiusura')
                            ->label('Le iscrizioni chiudono il')
                            ->seconds(false)
                            ->displayFormat('d/m/Y H:i')
                            ->visible(fn (Get $get): bool => (bool) $get('iscrizione_abilitata'))
                            ->required(fn (Get $get): bool => (bool) $get('iscrizione_abilitata'))
                            ->afterOrEqual('iscrizioni_apertura'),

                        Forms\Components\TextInput::make('posti_max')
                            ->label('Posti disponibili')
                            ->helperText('Lascia vuoto per posti illimitati.')
                            ->numeric()
                            ->minValue(1)
                            ->visible(fn (Get $get): bool => (bool) $get('iscrizione_abilitata')),
                        Forms\Components\TextInput::make('prezzo')
                            ->label('Quota di partecipazione')
                            ->helperText('Se indichi un importo, dopo l\'iscrizione compare il pulsante "Paga con PayPal".')
                            ->numeric()
                            ->minValue(0)
                            ->step(0.01)
                            ->prefix('€')
                            ->visible(fn (Get $get): bool => (bool) $get('iscrizione_abilitata')),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Foto dell\'attività')
                    ->description('Con più di una foto l\'album viene creato automaticamente: chi non è socio con tessera vede solo le prime due, le altre restano oscurate. Con una sola foto resta una foto singola visibile a tutti, a meno che tu non scelga di creare un album.')
                    ->schema([
                        FotoUpload::make('foto_nuove')
                            ->label('Aggiungi foto')
                            ->multiple()
                            ->reorderable()
                            ->columnSpanFull(),
                        FotoArchivio::scelta('foto_archivio')
                            ->label('Oppure scegli tra le foto già caricate'),
                        Forms\Components\TextInput::make('didascalia_foto')
                            ->label('Didascalia (facoltativa, per tutte le foto caricate ora)')
                            ->maxLength(255),
                        Forms\Components\Select::make('album_esistente')
                            ->label('Aggiungi a un album esistente di questa attività')
                            ->options(fn (?Attivita $record) => $record ? $record->album()->pluck('titolo', 'id') : [])
                            ->placeholder('Nessuno')
                            ->visible(fn (?Attivita $record): bool => $record !== null && $record->album()->exists()),
                        Forms\Components\Toggle::make('crea_album')
                            ->label('Crea un album anche con una sola foto')
                            ->helperText('Con più foto l\'album si crea da solo. Con una foto sola, se non spunti questa casella resta una foto singola.')
                            ->inline(false)
                            ->columnSpanFull(),
                    ])
                    ->columns(2)
                    ->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->withCount('iscrizioni'))
            ->defaultSort('data_evento', 'desc')
            ->columns([
                Tables\Columns\ImageColumn::make('immagine_copertina')
                    ->label(''),
                Tables\Columns\TextColumn::make('titolo')
                    ->searchable(),
                Tables\Columns\TextColumn::make('data_evento')
                    ->label('Quando')
                    ->state(fn (Attivita $record): string => trim(($record->periodoLabel() ?? '—').($record->ricorrente && $record->date_ricorrenti ? ' (+'.count($record->date_ricorrenti).' date)' : '')))
                    ->sortable(),
                Tables\Columns\TextColumn::make('luogo')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('iscrizioni_count')
                    ->label('Iscritti')
                    ->state(fn (Attivita $record): string => $record->iscrizione_abilitata
                        ? $record->iscrizioni_count.($record->posti_max ? ' / '.$record->posti_max : '')
                        : '—'),
                Tables\Columns\IconColumn::make('pubblicata')
                    ->label('Pubblicata')
                    ->boolean(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('pubblicata')
                    ->label('Pubblicata'),
                Tables\Filters\SelectFilter::make('quando')
                    ->label('Svolgimento')
                    ->options(['in_programma' => 'In programma', 'passate' => 'Già svolte'])
                    ->query(fn (Builder $query, array $data) => match ($data['value'] ?? null) {
                        'in_programma' => $query->inProgramma(),
                        'passate' => $query->passate(),
                        default => $query,
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\IscrizioniRelationManager::class,
            RelationManagers\FotoRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAttivitas::route('/'),
            'create' => Pages\CreateAttivita::route('/create'),
            'edit' => Pages\EditAttivita::route('/{record}/edit'),
        ];
    }
}
