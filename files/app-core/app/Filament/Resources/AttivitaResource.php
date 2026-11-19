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
                    ->description('Vale per ogni tipo di attività (torneo, gita, uscita...). Chi ha un account approvato (iscritto al sito o socio) si iscrive da solo, subito, senza che tu debba confermare nulla. Se indichi una quota (puoi fissare un prezzo più basso per i soci CRAL), dopo l\'iscrizione sceglie come pagare (contanti, bonifico o PayPal): dalla voce "Iscrizioni attività" vedi chi è iscritto e chi ha pagato. Gli orari sono in ora italiana.')
                    ->schema([
                        Forms\Components\Toggle::make('iscrizione_abilitata')
                            ->label('Permetti l\'iscrizione a questa attività')
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
                            ->visible(fn (Get $get): bool => (bool) $get('iscrizione_abilitata'))
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('prezzo')
                            ->label('Quota intera (non soci)')
                            ->helperText('È la quota di chi non ha la tessera. Se è uguale per tutti, compila solo questa. Dopo l\'iscrizione la persona sceglie come pagare: contanti, bonifico o PayPal.')
                            ->numeric()
                            ->minValue(0)
                            ->step(0.01)
                            ->prefix('€')
                            ->live(onBlur: true)
                            ->visible(fn (Get $get): bool => (bool) $get('iscrizione_abilitata')),
                        Forms\Components\TextInput::make('prezzo_soci')
                            ->label('Quota ridotta per i soci CRAL')
                            ->helperText('Facoltativa. I soci vedono questa quota e il prezzo intero barrato; chi non è socio vede quanto risparmierebbe con la tessera. Scrivi 0 per l\'ingresso gratuito ai soci.')
                            ->numeric()
                            ->minValue(0)
                            ->step(0.01)
                            ->prefix('€')
                            ->visible(fn (Get $get): bool => (bool) $get('iscrizione_abilitata'))
                            ->rules([
                                fn (Get $get): \Closure => function (string $attribute, $value, \Closure $fail) use ($get) {
                                    if (blank($value)) {
                                        return;
                                    }

                                    if (blank($get('prezzo')) || (float) $get('prezzo') <= 0) {
                                        $fail('Per indicare la quota dei soci compila prima la quota intera.');
                                    } elseif ((float) $value > (float) $get('prezzo')) {
                                        $fail('La quota dei soci non può essere più alta di quella intera.');
                                    }
                                },
                            ]),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Modulo di iscrizione')
                    ->description('Facoltativo. Aggiungi le domande a cui chi si iscrive deve rispondere (ruolo in campo, taglia della maglietta, intolleranze, accettazione del regolamento...). Le risposte le vedi nell\'elenco degli iscritti e le scarichi in Excel.')
                    ->visible(fn (Get $get): bool => (bool) $get('iscrizione_abilitata'))
                    ->schema([
                        Forms\Components\Placeholder::make('domande_pronte_titolo')
                            ->label('Domande pronte')
                            ->content('Aggiungi con un clic una domanda già fatta, poi modificala come vuoi:')
                            ->columnSpanFull(),
                        Forms\Components\Actions::make([
                            ...collect(static::domandePronte())->map(
                                fn (array $pronta, string $chiave) => Forms\Components\Actions\Action::make('pronta_'.$chiave)
                                    ->label('+ '.$pronta['etichetta'])
                                    ->color('gray')
                                    ->size('sm')
                                    ->action(function (Get $get, Set $set) use ($pronta) {
                                        $set('campi_iscrizione', [
                                            ...($get('campi_iscrizione') ?? []),
                                            (string) \Illuminate\Support\Str::uuid() => $pronta,
                                        ]);
                                    })
                            )->values()->all(),
                        ])->label('Domande pronte')->key('domande_pronte'),

                        Forms\Components\Repeater::make('campi_iscrizione')
                            ->label('Domande')
                            ->addActionLabel('Aggiungi una domanda')
                            ->reorderable()
                            ->collapsible()
                            ->collapsed(fn (?Attivita $record): bool => (bool) $record?->haCampi())
                            ->cloneable()
                            ->itemLabel(fn (array $state): ?string => filled($state['etichetta'] ?? null) ? $state['etichetta'] : 'Nuova domanda')
                            ->schema([
                                Forms\Components\Hidden::make('id'),
                                Forms\Components\TextInput::make('etichetta')
                                    ->label('Domanda')
                                    ->placeholder('Es. Giochi in attacco o in difesa?')
                                    ->required()
                                    ->maxLength(150)
                                    ->columnSpanFull(),
                                Forms\Components\Select::make('tipo')
                                    ->label('Tipo di risposta')
                                    ->options(Attivita::tipiCampo())
                                    ->default(Attivita::CAMPO_TESTO)
                                    ->required()
                                    ->live()
                                    ->native(false),
                                Forms\Components\Toggle::make('obbligatorio')
                                    ->label('Risposta obbligatoria')
                                    ->default(true)
                                    ->inline(false)
                                    ->disabled(fn (Get $get): bool => $get('tipo') === Attivita::CAMPO_ACCETTAZIONE)
                                    ->helperText(fn (Get $get): ?string => $get('tipo') === Attivita::CAMPO_ACCETTAZIONE ? 'Chi non spunta la casella non può iscriversi.' : null),
                                Forms\Components\TagsInput::make('opzioni')
                                    ->label('Opzioni tra cui scegliere')
                                    ->placeholder('Scrivi un\'opzione e premi Invio')
                                    ->helperText('Almeno due. Per esempio: Junior, Senior.')
                                    ->visible(fn (Get $get): bool => in_array($get('tipo'), Attivita::tipiConOpzioni(), true))
                                    ->required(fn (Get $get): bool => in_array($get('tipo'), Attivita::tipiConOpzioni(), true))
                                    ->rules(fn (Get $get): array => in_array($get('tipo'), Attivita::tipiConOpzioni(), true) ? ['array', 'min:2'] : [])
                                    ->validationMessages(['min' => 'Servono almeno due opzioni.'])
                                    ->columnSpanFull(),
                                Forms\Components\TextInput::make('aiuto')
                                    ->label('Spiegazione sotto la domanda (facoltativa)')
                                    ->maxLength(255)
                                    ->columnSpanFull(),
                            ])
                            ->columns(2)
                            ->defaultItems(0)
                            ->columnSpanFull(),

                        Forms\Components\Textarea::make('messaggio_iscritti')
                            ->label('Informazioni per gli iscritti (facoltativo)')
                            ->helperText('Le vedono solo le persone iscritte, sulla pagina dell\'attività: ritrovo, orario, cosa portare, regole...')
                            ->rows(3)
                            ->maxLength(2000)
                            ->columnSpanFull(),
                    ]),

                Forms\Components\Section::make('Foto dell\'attività')
                    ->description('Le foto le vede solo chi è iscritto al sito o socio CRAL: gli altri vedono anteprime sfocate. Con più di una foto l\'album viene creato automaticamente; con una sola foto resta una foto singola, a meno che tu non scelga di creare un album.')
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

    /**
     * Domande già pronte da aggiungere con un clic al modulo di iscrizione.
     *
     * @return array<string, array<string, mixed>>
     */
    public static function domandePronte(): array
    {
        return [
            'taglia' => [
                'etichetta' => 'Taglia maglietta',
                'tipo' => Attivita::CAMPO_SCELTA,
                'opzioni' => ['XS', 'S', 'M', 'L', 'XL', 'XXL'],
                'obbligatorio' => true,
            ],
            'intolleranze' => [
                'etichetta' => 'Allergie o intolleranze alimentari',
                'tipo' => Attivita::CAMPO_TESTO,
                'opzioni' => [],
                'obbligatorio' => false,
                'aiuto' => 'Lascia vuoto se non ne hai.',
            ],
            'regolamento' => [
                'etichetta' => 'Ho letto e accetto il regolamento',
                'tipo' => Attivita::CAMPO_ACCETTAZIONE,
                'opzioni' => [],
                'obbligatorio' => true,
            ],
            'note' => [
                'etichetta' => 'Note per gli organizzatori',
                'tipo' => Attivita::CAMPO_TESTO_LUNGO,
                'opzioni' => [],
                'obbligatorio' => false,
            ],
        ];
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->withCount('iscrizioniConfermate'))
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
                Tables\Columns\TextColumn::make('iscrizioni_confermate_count')
                    ->label('Iscritti')
                    ->state(fn (Attivita $record): string => $record->iscrizione_abilitata
                        ? $record->iscrizioni_confermate_count.($record->posti_max ? ' / '.$record->posti_max : '')
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
