<?php

namespace App\Filament\Resources\AttivitaResource\RelationManagers;

use App\Filament\Resources\IscrizioneAttivitaResource;
use App\Filament\Support\CampiIscrizioneForm;
use App\Models\IscrizioneAttivita;
use App\Models\User;
use App\Services\EsportaIscrizioni;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class IscrizioniRelationManager extends RelationManager
{
    protected static string $relationship = 'iscrizioni';

    protected static ?string $title = 'Iscritti e pagamenti';

    protected static ?string $modelLabel = 'iscrizione';

    protected static ?string $pluralModelLabel = 'iscrizioni';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('user_id')
                    ->label('Persona')
                    ->live()
                    // La quota proposta dipende da chi è: i soci pagano la quota ridotta.
                    ->afterStateUpdated(function ($state, Set $set) {
                        $quota = $this->getOwnerRecord()->prezzoPer($state ? User::find($state) : null);

                        $set('importo', $quota !== null && $quota > 0 ? $quota : null);
                    })
                    ->options(fn () => User::query()
                        ->whereIn('ruolo', ['socio', 'iscritto', 'admin'])
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
                    ->default(fn () => $this->getOwnerRecord()->prezzoNonSoci()),
                Forms\Components\Select::make('metodo_pagamento')
                    ->label('Modo di pagamento')
                    ->options(User::metodiPagamento())
                    ->placeholder('Non scelto'),
                Forms\Components\Toggle::make('pagato')
                    ->label('Quota pagata')
                    ->inline(false),
                // Le scelte e i testi del modulo di iscrizione (ruolo in campo, taglia...): si
                // compilano qui al posto della persona e si correggono quando serve.
                ...CampiIscrizioneForm::componenti($this->getOwnerRecord()),
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
                    ->color(fn (string $state): string => $state === IscrizioneAttivita::RIFIUTATA ? 'danger' : 'success'),
                Tables\Columns\TextColumn::make('user.email')
                    ->label('Email')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('user.telefono')
                    ->label('Telefono')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Iscritto il')
                    ->dateTime('d/m/Y H:i'),
                Tables\Columns\TextColumn::make('importo')
                    ->money('EUR')
                    ->placeholder('—'),
                IscrizioneAttivitaResource::colonnaMetodoPagamento(),
                IscrizioneAttivitaResource::colonnaStatoPagamento(),
                IscrizioneAttivitaResource::colonnaRisposte(),
            ])
            ->filters([
                IscrizioneAttivitaResource::filtroPagamento(),
            ])
            ->headerActions([
                Tables\Actions\Action::make('esporta')
                    ->label('Scarica elenco (Excel)')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('gray')
                    ->action(fn () => app(EsportaIscrizioni::class)->scarica($this->getOwnerRecord())),
                Tables\Actions\CreateAction::make()
                    ->label('Iscrivi una persona')
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['pagato_il'] = ! empty($data['pagato']) ? now() : null;
                        $data['gestita_il'] = now();

                        return CampiIscrizioneForm::applica($data, $this->getOwnerRecord());
                    }),
            ])
            ->actions([
                IscrizioneAttivitaResource::azioneSegnaPagato(),
                IscrizioneAttivitaResource::azioneConferma(),
                IscrizioneAttivitaResource::azioneRifiuta(),
                Tables\Actions\EditAction::make()
                    ->mutateRecordDataUsing(fn (array $data, IscrizioneAttivita $record): array => $data + CampiIscrizioneForm::statoDa($record))
                    ->mutateFormDataUsing(function (array $data, IscrizioneAttivita $record): array {
                        $data['pagato_il'] = ! empty($data['pagato']) ? ($record->pagato_il ?? now()) : null;

                        return CampiIscrizioneForm::applica($data, $this->getOwnerRecord());
                    }),
                Tables\Actions\DeleteAction::make()
                    ->modalDescription('La persona non risulterà più iscritta a questa attività.'),
            ]);
    }
}
