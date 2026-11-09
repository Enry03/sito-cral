<?php

namespace App\Filament\Resources\AttivitaResource\RelationManagers;

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

    protected static ?string $title = 'Partecipanti';

    protected static ?string $modelLabel = 'iscrizione';

    protected static ?string $pluralModelLabel = 'iscrizioni';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('user_id')
                    ->label('Socio')
                    ->options(fn () => User::query()
                        ->where('ruolo', 'socio')
                        ->where('stato_iscrizione', 'attivo')
                        ->whereNotNull('numero_tessera')
                        ->orderBy('name')
                        ->get()
                        ->mapWithKeys(fn (User $u) => [$u->id => $u->name.' ('.$u->numero_tessera.')']))
                    ->searchable()
                    ->required()
                    ->unique(ignoreRecord: true, modifyRuleUsing: fn ($rule) => $rule->where('attivita_id', $this->getOwnerRecord()->id)),
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
                    ->label('Tessera'),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Socio')
                    ->searchable(),
                Tables\Columns\TextColumn::make('user.email')
                    ->label('Email')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('user.telefono')
                    ->label('Telefono')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Iscritto il')
                    ->dateTime('d/m/Y H:i'),
                Tables\Columns\TextColumn::make('importo')
                    ->money('EUR'),
                Tables\Columns\IconColumn::make('pagato')
                    ->label('Pagato')
                    ->boolean(),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Iscrivi un socio')
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['pagato_il'] = ! empty($data['pagato']) ? now() : null;

                        return $data;
                    }),
            ])
            ->actions([
                Tables\Actions\Action::make('segnaPagato')
                    ->label('Segna pagato')
                    ->icon('heroicon-o-banknotes')
                    ->color('success')
                    ->visible(fn (IscrizioneAttivita $record): bool => ! $record->pagato)
                    ->requiresConfirmation()
                    ->action(fn (IscrizioneAttivita $record) => $record->update(['pagato' => true, 'pagato_il' => now()])),
                Tables\Actions\EditAction::make()
                    ->mutateFormDataUsing(function (array $data, IscrizioneAttivita $record): array {
                        $data['pagato_il'] = ! empty($data['pagato']) ? ($record->pagato_il ?? now()) : null;

                        return $data;
                    }),
                Tables\Actions\DeleteAction::make()
                    ->modalDescription('Il socio non risulterà più iscritto a questa attività.'),
            ]);
    }
}
