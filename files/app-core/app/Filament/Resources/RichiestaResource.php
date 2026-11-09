<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RichiestaResource\Pages;
use App\Models\User;
use App\Services\OperazioneNonConsentita;
use App\Services\RichiesteIscrizioneService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

/**
 * Richieste di iscrizione in attesa: chi vuole diventare socio CRAL e chi si
 * iscrive solo al sito, ben distinte, con il pulsante per accettarle o rifiutarle.
 */
class RichiestaResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $slug = 'richieste';

    protected static ?string $navigationIcon = 'heroicon-o-inbox-arrow-down';

    protected static ?string $navigationLabel = 'Richieste';

    protected static ?string $modelLabel = 'Richiesta';

    protected static ?string $pluralModelLabel = 'Richieste';

    protected static ?int $navigationSort = 0;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->richiesteInSospeso();
    }

    public static function getNavigationBadge(): ?string
    {
        $da = static::getEloquentQuery()->count();

        return $da > 0 ? (string) $da : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function getNavigationBadgeTooltip(): ?string
    {
        return 'Richieste di iscrizione da gestire';
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('richiesta_il', 'asc')
            ->emptyStateHeading('Nessuna richiesta in attesa')
            ->emptyStateDescription('Quando qualcuno si iscrive o chiede la tessera lo vedrai qui.')
            ->emptyStateIcon('heroicon-o-inbox')
            ->columns([
                Tables\Columns\TextColumn::make('richiesta_il')
                    ->label('Richiesta del')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                Tables\Columns\TextColumn::make('name')
                    ->label('Nome')
                    ->searchable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('tipo')
                    ->label('Cosa chiede')
                    ->badge()
                    ->state(fn (User $record): ?string => $record->richiestaLabel())
                    ->color(fn (User $record): string => match ($record->tipoRichiesta()) {
                        'socio' => 'danger',
                        'upgrade' => 'warning',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('metodo_pagamento')
                    ->label('Pagamento scelto')
                    ->badge()
                    ->state(fn (User $record): ?string => $record->chiedeTessera() ? $record->metodoPagamentoLabel() : null)
                    ->color(fn (User $record): string => match ($record->metodo_pagamento) {
                        'paypal' => 'info',
                        'contanti' => 'success',
                        'email' => 'warning',
                        default => 'gray',
                    })
                    ->placeholder('—'),
                Tables\Columns\TextColumn::make('quota')
                    ->label('Quota tessera')
                    ->badge()
                    ->state(fn (User $record): ?string => match (true) {
                        ! $record->chiedeTessera() => null,
                        $record->pagatoConPaypal() => 'PayPal: pagamento ricevuto',
                        (bool) $record->quota_pagata => 'Pagata',
                        $record->pagamento_dichiarato_il !== null => 'PayPal dichiarato (da verificare)',
                        default => 'Non ancora pagata',
                    })
                    ->color(fn (?string $state): string => match ($state) {
                        'PayPal: pagamento ricevuto', 'Pagata' => 'success',
                        'PayPal dichiarato (da verificare)' => 'warning',
                        default => 'gray',
                    })
                    ->placeholder('—'),
                Tables\Columns\TextColumn::make('email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('telefono')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('codice_fiscale')
                    ->label('Codice fiscale')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('data_nascita')
                    ->label('Data di nascita')
                    ->date('d/m/Y')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('indirizzo')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('tipo')
                    ->label('Tipo di richiesta')
                    ->options([
                        'socio' => 'Diventare socio CRAL',
                        'upgrade' => 'Diventare socio (già iscritto)',
                        'sito' => 'Iscrizione al sito',
                    ])
                    ->query(fn (Builder $query, array $data) => match ($data['value'] ?? null) {
                        'socio' => $query->where('stato_iscrizione', 'in_attesa')->where('ruolo', 'socio'),
                        'sito' => $query->where('stato_iscrizione', 'in_attesa')->where('ruolo', 'iscritto'),
                        'upgrade' => $query->where('stato_iscrizione', 'attivo'),
                        default => $query,
                    }),
            ])
            ->actions([
                Tables\Actions\Action::make('accetta')
                    ->label('Accetta')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->modalHeading(fn (User $record): string => 'Accetta: '.$record->richiestaLabel())
                    ->modalDescription(fn (User $record): string => $record->chiedeTessera()
                        ? "{$record->name} diventa socio CRAL e riceve via email la tessera con il suo nome."
                        : "{$record->name} potrà accedere al sito e riceve una email di conferma.")
                    ->modalSubmitActionLabel('Accetta')
                    ->form(fn (User $record): array => $record->chiedeTessera() ? [
                        Forms\Components\TextInput::make('numero_tessera')
                            ->label('Numero di tessera')
                            ->helperText('Proposto in automatico: puoi cambiarlo. Compare sulla tessera inviata via email.')
                            ->default(fn () => User::prossimoNumeroTessera())
                            ->required()
                            ->maxLength(255)
                            ->unique(table: User::class, column: 'numero_tessera', ignorable: $record),
                        Forms\Components\Toggle::make('quota_pagata')
                            ->label('Quota di tessera incassata')
                            ->default($record->quota_pagata || $record->pagamento_dichiarato_il !== null)
                            ->helperText(match (true) {
                                $record->pagatoConPaypal() => 'PayPal ha confermato il pagamento il '.$record->quota_pagata_il?->format('d/m/Y').' (operazione '.$record->paypal_txn_id.').',
                                $record->pagamento_dichiarato_il !== null => 'Ha segnalato il pagamento con PayPal il '.$record->pagamento_dichiarato_il->format('d/m/Y').': verifica l\'incasso nel tuo conto PayPal.',
                                default => 'Scelta dell\'utente: '.($record->metodoPagamentoLabel() ?? 'nessuna').'. Spunta se hai già ricevuto la quota (di persona, con bonifico o altro).',
                            }),
                    ] : [])
                    ->action(function (User $record, array $data) {
                        try {
                            $esito = app(RichiesteIscrizioneService::class)->accetta(
                                $record,
                                $data['numero_tessera'] ?? null,
                                (bool) ($data['quota_pagata'] ?? false),
                            );
                        } catch (OperazioneNonConsentita $e) {
                            Notification::make()->danger()->title('Richiesta non accettata')->body($e->getMessage())->persistent()->send();

                            return;
                        }

                        $notifica = Notification::make()->title('Richiesta accettata: '.$record->name);

                        $esito['email_inviata']
                            ? $notifica->success()->body('Email di conferma inviata a '.$record->email.'.')
                            : $notifica->warning()->body("La richiesta è stata accettata ma l'email non è partita: avvisa tu {$record->email}.")->persistent();

                        $notifica->send();
                    }),
                Tables\Actions\Action::make('rifiuta')
                    ->label('Rifiuta')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading(fn (User $record): string => 'Rifiuta la richiesta di '.$record->name)
                    ->modalDescription('La richiesta viene chiusa e all\'utente non viene inviata nessuna email.')
                    ->modalSubmitActionLabel('Rifiuta')
                    ->action(function (User $record) {
                        try {
                            app(RichiesteIscrizioneService::class)->rifiuta($record);
                        } catch (OperazioneNonConsentita $e) {
                            Notification::make()->danger()->title('Operazione non riuscita')->body($e->getMessage())->send();

                            return;
                        }

                        Notification::make()->success()->title('Richiesta rifiutata')->send();
                    }),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRichieste::route('/'),
        ];
    }
}
