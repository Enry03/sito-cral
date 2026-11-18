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
 * Richieste di iscrizione alle attività (tornei, gite, uscite...): chi ha chiesto
 * di partecipare e il pulsante per confermare o declinare. Tutto passa da qui.
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

    public static function getNavigationBadge(): ?string
    {
        $da = IscrizioneAttivita::inAttesa()->count();

        return $da > 0 ? (string) $da : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function getNavigationBadgeTooltip(): ?string
    {
        return 'Richieste di iscrizione a un\'attività da confermare';
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
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['user', 'attivita']))
            ->defaultSort('created_at', 'asc')
            ->emptyStateHeading('Nessuna richiesta di iscrizione')
            ->emptyStateDescription('Quando qualcuno chiede di partecipare a un\'attività lo vedrai qui.')
            ->emptyStateIcon('heroicon-o-ticket')
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Richiesta del')
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
                    ->color(fn (string $state): string => match ($state) {
                        IscrizioneAttivita::CONFERMATA => 'success',
                        IscrizioneAttivita::RIFIUTATA => 'danger',
                        default => 'warning',
                    }),
                Tables\Columns\TextColumn::make('importo')
                    ->money('EUR')
                    ->placeholder('—'),
                Tables\Columns\IconColumn::make('pagato')
                    ->label('Quota pagata')
                    ->boolean(),
                Tables\Columns\TextColumn::make('user.email')
                    ->label('Email')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('user.telefono')
                    ->label('Telefono')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('stato')
                    ->label('Stato')
                    ->options(IscrizioneAttivita::stati())
                    ->default(IscrizioneAttivita::IN_ATTESA),
                Tables\Filters\SelectFilter::make('attivita_id')
                    ->label('Attività')
                    ->options(fn () => Attivita::query()->orderByDesc('data_evento')->pluck('titolo', 'id'))
                    ->searchable(),
            ])
            ->actions([
                static::azioneConferma(),
                static::azioneRifiuta(),
            ]);
    }

    public static function azioneConferma(): Tables\Actions\Action
    {
        return Tables\Actions\Action::make('conferma')
            ->label('Conferma')
            ->icon('heroicon-o-check-circle')
            ->color('success')
            ->visible(fn (IscrizioneAttivita $record): bool => ! $record->confermata())
            ->requiresConfirmation()
            ->modalHeading(fn (IscrizioneAttivita $record): string => 'Conferma l\'iscrizione di '.$record->user->name)
            ->modalDescription(fn (IscrizioneAttivita $record): string => 'Partecipa a "'.$record->attivita->titolo.'" e riceve una email di conferma.')
            ->modalSubmitActionLabel('Conferma')
            ->action(function (IscrizioneAttivita $record) {
                try {
                    $esito = app(IscrizioneAttivitaService::class)->conferma($record);
                } catch (OperazioneNonConsentita $e) {
                    Notification::make()->danger()->title('Iscrizione non confermata')->body($e->getMessage())->persistent()->send();

                    return;
                }

                $notifica = Notification::make()->title('Iscrizione confermata: '.$record->user->name);

                $esito['email_inviata']
                    ? $notifica->success()->body('Email di conferma inviata a '.$record->user->email.'.')
                    : $notifica->warning()->body("L'iscrizione è confermata ma l'email non è partita: avvisa tu {$record->user->email}.")->persistent();

                $notifica->send();
            });
    }

    public static function azioneRifiuta(): Tables\Actions\Action
    {
        return Tables\Actions\Action::make('rifiuta')
            ->label('Declina')
            ->icon('heroicon-o-x-circle')
            ->color('danger')
            ->visible(fn (IscrizioneAttivita $record): bool => ! $record->rifiutata())
            ->requiresConfirmation()
            ->modalHeading(fn (IscrizioneAttivita $record): string => 'Declina la richiesta di '.$record->user->name)
            ->modalDescription('La richiesta viene chiusa e la persona riceve una email che la informa che non è stata accolta.')
            ->modalSubmitActionLabel('Declina')
            ->action(function (IscrizioneAttivita $record) {
                try {
                    $esito = app(IscrizioneAttivitaService::class)->rifiuta($record);
                } catch (OperazioneNonConsentita $e) {
                    Notification::make()->danger()->title('Operazione non riuscita')->body($e->getMessage())->persistent()->send();

                    return;
                }

                $notifica = Notification::make()->title('Richiesta declinata: '.$record->user->name);

                $esito['email_inviata']
                    ? $notifica->success()->body('Email inviata a '.$record->user->email.'.')
                    : $notifica->warning()->body("La richiesta è declinata ma l'email non è partita: avvisa tu {$record->user->email}.")->persistent();

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
