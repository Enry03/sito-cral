<?php

namespace App\Filament\Resources\AttivitaResource\RelationManagers;

use App\Filament\Resources\IscrizioneAttivitaResource;
use App\Models\IscrizioneAttivita;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class IscrizioniRelationManager extends RelationManager
{
    protected static string $relationship = 'iscrizioni';

    protected static ?string $title = 'Partecipanti e richieste';

    protected static ?string $modelLabel = 'iscrizione';

    protected static ?string $pluralModelLabel = 'iscrizioni';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('user_id')
                    ->label('Persona')
                    ->options(fn () => User::query()
                        ->whereIn('ruolo', ['socio', 'iscritto'])
                        ->where('stato_iscrizione', 'attivo')
                        ->orderBy('name')
                        ->get()
                        ->mapWithKeys(fn (User $u) => [$u->id => $u->name.($u->numero_tessera ? ' ('.$u->numero_tessera.')' : ' — iscritto al sito')]))
                    ->searchable()
                    ->required()
                    ->unique(ignoreRecord: true, modifyRuleUsing: fn ($rule) => $rule->where('attivita_id', $this->getOwnerRecord()->id)),
                Forms\Components\Select::make('stato')
                    ->label('Stato')
                    ->options(IscrizioneAttivita::stati())
                    ->default(IscrizioneAttivita::CONFERMATA)
                    ->required(),
                Forms\Components\TextInput::make('importo')
                    ->numeric()
                    ->prefix('€')
                    ->default(fn () => $this->getOwnerRecord()->haPrezzo() ? $this->getOwnerRecord()->prezzo : null),
                Forms\Components\Toggle::make('pagato')
                    ->label('Quota pagata')
                    ->inline(false),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->defaultSort('created_at')
            ->columns([
                Tables\Columns\TextColumn::make('user.numero_tessera')
                    ->label('Tessera')
                    ->placeholder('—'),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Persona')
                    ->searchable(),
                Tables\Columns\TextColumn::make('stato')
                    ->label('Stato')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => IscrizioneAttivita::stati()[$state] ?? $state)
                    ->color(fn (string $state): string => match ($state) {
                        IscrizioneAttivita::CONFERMATA => 'success',
                        IscrizioneAttivita::RIFIUTATA => 'danger',
                        default => 'warning',
                    }),
                Tables\Columns\TextColumn::make('user.email')
                    ->label('Email')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('user.telefono')
                    ->label('Telefono')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Richiesta del')
                    ->dateTime('d/m/Y H:i'),
                Tables\Columns\TextColumn::make('importo')
                    ->money('EUR'),
                Tables\Columns\IconColumn::make('pagato')
                    ->label('Pagato')
                    ->boolean(),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Iscrivi una persona')
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['pagato_il'] = ! empty($data['pagato']) ? now() : null;
                        $data['gestita_il'] = ($data['stato'] ?? IscrizioneAttivita::CONFERMATA) === IscrizioneAttivita::IN_ATTESA ? null : now();

                        return $data;
                    }),
            ])
            ->actions([
                IscrizioneAttivitaResource::azioneConferma(),
                IscrizioneAttivitaResource::azioneRifiuta(),
                Tables\Actions\Action::make('segnaPagato')
                    ->label('Segna pagato')
                    ->icon('heroicon-o-banknotes')
                    ->color('success')
                    ->visible(fn (IscrizioneAttivita $record): bool => $record->confermata() && ! $record->pagato)
                    ->requiresConfirmation()
                    ->action(fn (IscrizioneAttivita $record) => $record->update(['pagato' => true, 'pagato_il' => now()])),
                Tables\Actions\EditAction::make()
                    ->mutateFormDataUsing(function (array $data, IscrizioneAttivita $record): array {
                        $data['pagato_il'] = ! empty($data['pagato']) ? ($record->pagato_il ?? now()) : null;

                        return $data;
                    }),
                Tables\Actions\DeleteAction::make()
                    ->modalDescription('La persona non risulterà più iscritta a questa attività.'),
            ]);
    }
}
