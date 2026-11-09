<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ConvenzioneResource\Pages;
use App\Filament\Support\FotoUpload;
use App\Models\Convenzione;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

/**
 * Aziende e negozi convenzionati: l'amministratore le aggiunge, modifica ed
 * elimina da qui. Sul sito le vedono solo i soci con tessera.
 */
class ConvenzioneResource extends Resource
{
    protected static ?string $model = Convenzione::class;

    protected static ?string $slug = 'convenzioni';

    protected static ?string $navigationIcon = 'heroicon-o-tag';

    protected static ?string $navigationLabel = 'Convenzioni';

    protected static ?string $modelLabel = 'Convenzione';

    protected static ?string $pluralModelLabel = 'Convenzioni';

    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Azienda o negozio')
                    ->description('Le convenzioni le vedono solo i soci CRAL con tessera, non gli iscritti al solo sito né gli ospiti.')
                    ->schema([
                        Forms\Components\TextInput::make('nome')
                            ->label('Nome dell\'azienda o del negozio')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('convenzione')
                            ->label('Che convenzione abbiamo')
                            ->placeholder('Es. Sconto del 10% su tutti gli articoli')
                            ->helperText('Una frase breve: compare in evidenza nell\'elenco.')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),
                        Forms\Components\Textarea::make('descrizione')
                            ->label('Descrizione')
                            ->helperText('Come si usa la convenzione, condizioni, orari, dove si trova il negozio...')
                            ->rows(6)
                            ->maxLength(5000)
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('sito_web')
                            ->label('Collegamento al sito web')
                            ->placeholder('https://www.esempio.it')
                            ->maxLength(255)
                            ->rule(fn () => function (string $attribute, $value, \Closure $fail) {
                                // Solo indirizzi web veri (http/https): niente "javascript:" o altro.
                                if (filled($value) && ! preg_match('#^(https?://)?([a-z0-9-]+\.)+[a-z]{2,}(:\d+)?([/?\#]\S*)?$#i', trim($value))) {
                                    $fail('Scrivi un indirizzo web valido, per esempio https://www.esempio.it');
                                }
                            })
                            ->columnSpanFull(),
                        Forms\Components\Toggle::make('pubblicata')
                            ->label('Visibile ai soci')
                            ->helperText('Disattivala per nascondere la convenzione senza eliminarla.')
                            ->default(true)
                            ->inline(false),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Foto')
                    ->description('Per mostrare i prodotti o il negozio. La prima foto è la copertina; puoi trascinarle per cambiare l\'ordine.')
                    ->schema([
                        FotoUpload::make('immagini', 'convenzioni')
                            ->label('Foto')
                            ->multiple()
                            ->reorderable()
                            ->maxFiles(12)
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('nome')
            ->columns([
                Tables\Columns\ImageColumn::make('copertina')
                    ->label('')
                    ->state(fn (Convenzione $record): ?string => $record->copertinaUrl())
                    ->square(),
                Tables\Columns\TextColumn::make('nome')
                    ->searchable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('convenzione')
                    ->label('Convenzione')
                    ->limit(60)
                    ->searchable(),
                Tables\Columns\TextColumn::make('sito_web')
                    ->label('Sito web')
                    ->limit(30)
                    ->toggleable(),
                Tables\Columns\IconColumn::make('pubblicata')
                    ->label('Visibile')
                    ->boolean(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->modalDescription('La convenzione e le sue foto vengono eliminate definitivamente.'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('Nessuna convenzione')
            ->emptyStateDescription('Aggiungi le aziende e i negozi convenzionati: i soci con tessera le vedranno nella sezione Convenzioni del sito.')
            ->emptyStateIcon('heroicon-o-tag');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListConvenzioni::route('/'),
            'create' => Pages\CreateConvenzione::route('/create'),
            'edit' => Pages\EditConvenzione::route('/{record}/edit'),
        ];
    }
}
