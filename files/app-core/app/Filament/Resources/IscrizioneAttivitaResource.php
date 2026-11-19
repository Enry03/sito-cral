<?php

namespace App\Filament\Resources;

use App\Filament\Resources\IscrizioneAttivitaResource\Pages;
use App\Models\Attivita;
use App\Models\IscrizioneAttivita;
use App\Services\IscrizioneAttivitaService;
use App\Services\OperazioneNonConsentita;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

/**
 * Iscrizioni alle attività (tornei, gite, uscite...): chi si è iscritto e a che
 * punto è il pagamento della quota. L'iscrizione è automatica: qui non c'è nulla da
 * confermare, ma si segnano i pagamenti arrivati (contanti, bonifico) e, se serve,
 * si esclude o si riammette qualcuno.
 */
class IscrizioneAttivitaResource extends Resource
{
    protected static ?string $model = IscrizioneAttivita::class;

    protected static ?string $slug = 'iscrizioni-attivita';

    protected static ?string $navigationIcon = 'heroicon-o-ticket';

    protected static ?string $navigationLabel = 'Iscrizioni attività';

    protected static ?string $modelLabel = 'Iscrizione';

    protected static ?string $pluralModelLabel = 'Iscrizioni alle attività';

    protected static ?int $navigationSort = 0;

    /** Il numero accanto alla voce di menu: pagamenti segnalati da verificare. */
    public static function getNavigationBadge(): ?string
    {
        $da = IscrizioneAttivita::pagamentiDaVerificare()->count();

        return $da > 0 ? (string) $da : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function getNavigationBadgeTooltip(): ?string
    {
        return 'Pagamenti di attività segnalati (bonifico, PayPal) da verificare';
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public static function colonnaStatoPagamento(): Tables\Columns\TextColumn
    {
        return Tables\Columns\TextColumn::make('pagamento')
            ->label('Pagamento')
            ->badge()
            ->state(fn (IscrizioneAttivita $record): ?string => $record->confermata() ? $record->statoPagamento() : null)
            ->color(fn (?string $state): string => match ($state) {
                IscrizioneAttivita::PAGAMENTO_PAGATA => 'success',
                IscrizioneAttivita::PAGAMENTO_IN_ELABORAZIONE => 'warning',
                IscrizioneAttivita::PAGAMENTO_DA_PAGARE => 'danger',
                default => 'gray',
            })
            ->tooltip(fn (IscrizioneAttivita $record): ?string => $record->pagatoConPaypal()
                ? 'PayPal ha confermato il pagamento (operazione '.$record->paypal_txn_id.').'
                : ($record->statoPagamento() === IscrizioneAttivita::PAGAMENTO_IN_ELABORAZIONE ? 'Dice di aver pagato: verifica l\'incasso e segna la quota come pagata.' : null))
            ->placeholder('—');
    }

    public static function colonnaMetodoPagamento(): Tables\Columns\TextColumn
    {
        return Tables\Columns\TextColumn::make('metodo_pagamento')
            ->label('Modo di pagamento')
            ->badge()
            ->state(fn (IscrizioneAttivita $record): ?string => $record->importo !== null && (float) $record->importo > 0 ? $record->metodoPagamentoLabel() : null)
            ->color(fn (IscrizioneAttivita $record): string => match ($record->metodo_pagamento) {
                'paypal' => 'info',
                'contanti' => 'success',
                'bonifico' => 'warning',
                default => 'gray',
            })
            ->placeholder('—');
    }

    /** Le risposte date al modulo di iscrizione, una per riga ("Domanda: risposta"). */
    public static function colonnaRisposte(bool $nascostaDiDefault = false): Tables\Columns\TextColumn
    {
        return Tables\Columns\TextColumn::make('risposte')
            ->label('Risposte al modulo')
            ->state(fn (IscrizioneAttivita $record): array => collect($record->risposteTesto())
                ->map(fn (string $risposta, string $domanda): string => $domanda.': '.$risposta)
                ->values()
                ->all())
            ->listWithLineBreaks()
            ->wrap()
            ->placeholder('—')
            ->toggleable(isToggledHiddenByDefault: $nascostaDiDefault);
    }

    public static function filtroPagamento(): Tables\Filters\SelectFilter
    {
        return Tables\Filters\SelectFilter::make('pagamento')
            ->label('Pagamento')
            ->options([
                IscrizioneAttivita::PAGAMENTO_DA_PAGARE => 'Da pagare',
                IscrizioneAttivita::PAGAMENTO_IN_ELABORAZIONE => 'In elaborazione',
                IscrizioneAttivita::PAGAMENTO_PAGATA => 'Pagata',
                IscrizioneAttivita::PAGAMENTO_GRATUITA => 'Gratuita',
            ])
            ->query(function (Builder $query, array $data) {
                $quota = fn (Builder $q) => $q->where('stato', IscrizioneAttivita::CONFERMATA)->where('pagato', false);

                return match ($data['value'] ?? null) {
                    IscrizioneAttivita::PAGAMENTO_PAGATA => $query->where('pagato', true),
                    IscrizioneAttivita::PAGAMENTO_IN_ELABORAZIONE => $quota($query)->whereNotNull('pagamento_dichiarato_il'),
                    IscrizioneAttivita::PAGAMENTO_DA_PAGARE => $quota($query)->whereNull('pagamento_dichiarato_il')->where('importo', '>', 0),
                    IscrizioneAttivita::PAGAMENTO_GRATUITA => $quota($query)->where(fn (Builder $q) => $q->whereNull('importo')->orWhere('importo', '<=', 0)),
                    default => $query,
                };
            });
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['user', 'attivita']))
            ->defaultSort('created_at', 'desc')
            ->emptyStateHeading('Nessuna iscrizione')
            ->emptyStateDescription('Quando qualcuno si iscrive a un\'attività lo vedrai qui, con lo stato del pagamento.')
            ->emptyStateIcon('heroicon-o-ticket')
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Iscritto il')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                Tables\Columns\TextColumn::make('attivita.titolo')
                    ->label('Attività')
                    ->weight('bold')
                    ->searchable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Persona')
                    ->searchable(),
                Tables\Columns\TextColumn::make('tipo')
                    ->label('È')
                    ->badge()
                    ->state(fn (IscrizioneAttivita $record): string => $record->user->isSocioCral() ? 'Socio CRAL' : 'Iscritto al sito')
                    ->color(fn (string $state): string => $state === 'Socio CRAL' ? 'primary' : 'gray'),
                Tables\Columns\TextColumn::make('stato')
                    ->label('Stato')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => IscrizioneAttivita::stati()[$state] ?? $state)
                    ->color(fn (string $state): string => $state === IscrizioneAttivita::RIFIUTATA ? 'danger' : 'success'),
                Tables\Columns\TextColumn::make('importo')
                    ->label('Quota')
                    ->money('EUR')
                    ->placeholder('—'),
                static::colonnaMetodoPagamento(),
                static::colonnaStatoPagamento(),
                static::colonnaRisposte(nascostaDiDefault: true),
                Tables\Columns\TextColumn::make('user.email')
                    ->label('Email')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('user.telefono')
                    ->label('Telefono')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                static::filtroPagamento(),
                Tables\Filters\SelectFilter::make('stato')
                    ->label('Stato')
                    ->options(IscrizioneAttivita::stati()),
                Tables\Filters\SelectFilter::make('attivita_id')
                    ->label('Attività')
                    ->options(fn () => Attivita::query()->orderByDesc('data_evento')->pluck('titolo', 'id'))
                    ->searchable(),
            ])
            ->actions([
                static::azioneSegnaPagato(),
                static::azioneConferma(),
                static::azioneRifiuta(),
            ]);
    }

    /**
     * Segna la quota come incassata: contanti ricevuti da chi li incassa, bonifico
     * arrivato sul conto, o PayPal se la notifica automatica non è arrivata.
     */
    public static function azioneSegnaPagato(): Tables\Actions\Action
    {
        return Tables\Actions\Action::make('segnaPagato')
            ->label('Segna pagato')
            ->icon('heroicon-o-banknotes')
            ->color('success')
            ->visible(fn (IscrizioneAttivita $record): bool => $record->confermata() && ! $record->pagato)
            ->requiresConfirmation()
            ->modalHeading(fn (IscrizioneAttivita $record): string => 'Quota incassata da '.$record->user->name.'?')
            ->modalDescription(fn (IscrizioneAttivita $record): string => 'Conferma di aver ricevuto la quota di "'.$record->attivita->titolo.'"'
                .($record->metodoPagamentoLabel() ? ' ('.mb_strtolower($record->metodoPagamentoLabel()).')' : '').'.')
            ->modalSubmitActionLabel('Sì, è stata incassata')
            ->action(function (IscrizioneAttivita $record) {
                app(IscrizioneAttivitaService::class)->segnaPagata($record);

                Notification::make()->success()->title('Quota segnata come pagata: '.$record->user->name)->send();
            });
    }

    /** Riammette chi era stato escluso. */
    public static function azioneConferma(): Tables\Actions\Action
    {
        return Tables\Actions\Action::make('conferma')
            ->label('Riammetti')
            ->icon('heroicon-o-arrow-uturn-left')
            ->color('success')
            ->visible(fn (IscrizioneAttivita $record): bool => ! $record->confermata())
            ->requiresConfirmation()
            ->modalHeading(fn (IscrizioneAttivita $record): string => 'Riammetti '.$record->user->name)
            ->modalDescription(fn (IscrizioneAttivita $record): string => 'Torna iscritto a "'.$record->attivita->titolo.'" e riceve una email.')
            ->modalSubmitActionLabel('Riammetti')
            ->action(function (IscrizioneAttivita $record) {
                try {
                    $esito = app(IscrizioneAttivitaService::class)->conferma($record);
                } catch (OperazioneNonConsentita $e) {
                    Notification::make()->danger()->title('Operazione non riuscita')->body($e->getMessage())->persistent()->send();

                    return;
                }

                $notifica = Notification::make()->title('Iscrizione ripristinata: '.$record->user->name);

                $esito['email_inviata']
                    ? $notifica->success()->body('Email inviata a '.$record->user->email.'.')
                    : $notifica->warning()->body("L'iscrizione è ripristinata ma l'email non è partita: avvisa tu {$record->user->email}.")->persistent();

                $notifica->send();
            });
    }

    /** Esclude dall'attività chi non deve partecipare. */
    public static function azioneRifiuta(): Tables\Actions\Action
    {
        return Tables\Actions\Action::make('rifiuta')
            ->label('Escludi')
            ->icon('heroicon-o-x-circle')
            ->color('danger')
            ->visible(fn (IscrizioneAttivita $record): bool => ! $record->rifiutata())
            ->requiresConfirmation()
            ->modalHeading(fn (IscrizioneAttivita $record): string => 'Escludi '.$record->user->name.' dall\'attività')
            ->modalDescription('Non partecipa più, il posto si libera e la persona riceve una email che la informa. Se vuoi solo cancellare l\'iscrizione senza avvisarla, usa "Elimina".')
            ->modalSubmitActionLabel('Escludi')
            ->action(function (IscrizioneAttivita $record) {
                try {
                    $esito = app(IscrizioneAttivitaService::class)->rifiuta($record);
                } catch (OperazioneNonConsentita $e) {
                    Notification::make()->danger()->title('Operazione non riuscita')->body($e->getMessage())->persistent()->send();

                    return;
                }

                $notifica = Notification::make()->title('Escluso: '.$record->user->name);

                $esito['email_inviata']
                    ? $notifica->success()->body('Email inviata a '.$record->user->email.'.')
                    : $notifica->warning()->body("È stato escluso ma l'email non è partita: avvisa tu {$record->user->email}.")->persistent();

                $notifica->send();
            });
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListIscrizioniAttivita::route('/'),
        ];
    }
}
