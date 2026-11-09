<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PrestitoResource\Pages;
use App\Models\Libro;
use App\Models\Prestito;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PrestitoResource extends Resource
{
    protected static ?string $model = Prestito::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrows-right-left';

    protected static ?string $navigationGroup = 'Libreria';

    protected static ?string $navigationLabel = 'Prestiti';

    protected static ?string $modelLabel = 'Prestito';

    protected static ?string $pluralModelLabel = 'Prestiti';

    protected static ?int $navigationSort = 6;

    public static function getNavigationBadge(): ?string
    {
        $inRitardo = Prestito::scaduti()->count();

        return $inRitardo > 0 ? (string) $inRitardo : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'danger';
    }

    public static function getNavigationBadgeTooltip(): ?string
    {
        return 'Prestiti scaduti da restituire';
    }

    /**
     * Il form serve solo per registrare un prestito a nome di un socio (ad
     * esempio ritirato di persona): le altre operazioni sono azioni in tabella.
     */
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('user_id')
                    ->label('Socio')
                    ->options(fn () => User::query()
                        ->where('stato_iscrizione', 'attivo')
                        ->where(fn ($q) => $q->where('ruolo', 'admin')->orWhere(fn ($q) => $q->where('ruolo', 'socio')->whereNotNull('numero_tessera')))
                        ->orderBy('name')
                        ->get()
                        ->mapWithKeys(fn (User $u) => [$u->id => $u->name.($u->numero_tessera ? ' ('.$u->numero_tessera.')' : '')]))
                    ->searchable()
                    ->required(),
                Forms\Components\Select::make('libro_id')
                    ->label('Libro')
                    ->options(fn () => Libro::query()
                        ->conDisponibilita()
                        ->orderBy('titolo')
                        ->get()
                        ->filter(fn (Libro $l) => $l->isDisponibile())
                        ->mapWithKeys(fn (Libro $l) => [$l->id => $l->titolo.($l->autore ? ' — '.$l->autore : '')]))
                    ->helperText('Sono elencati solo i libri con almeno una copia disponibile.')
                    ->searchable()
                    ->required(),
                Forms\Components\DatePicker::make('data_prestito')
                    ->label('Data del prestito')
                    ->default(today())
                    ->displayFormat('d/m/Y')
                    ->required(),
                Forms\Components\DatePicker::make('scadenza')
                    ->label('Da restituire entro')
                    ->default(today()->addDays((int) config('cral.prestito.giorni')))
                    ->displayFormat('d/m/Y')
                    ->afterOrEqual('data_prestito')
                    ->required(),
                Forms\Components\Textarea::make('note')
                    ->rows(2)
                    ->columnSpanFull(),
            ])
            ->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('data_prestito', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('data_prestito')
                    ->label('Data')
                    ->date('d/m/Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('utente_nome')
                    ->label('Socio')
                    ->searchable(),
                Tables\Columns\TextColumn::make('utente_tessera')
                    ->label('Tessera')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('libro_titolo')
                    ->label('Libro')
                    ->searchable()
                    ->wrap(),
                Tables\Columns\TextColumn::make('libro_codice')
                    ->label('Codice')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('scadenza')
                    ->label('Scadenza')
                    ->date('d/m/Y')
                    ->sortable()
                    ->color(fn (Prestito $record): ?string => $record->isInRitardo() ? 'danger' : null)
                    ->weight(fn (Prestito $record) => $record->isInRitardo() ? 'bold' : null),
                Tables\Columns\TextColumn::make('restituito_il')
                    ->label('Restituito il')
                    ->date('d/m/Y')
                    ->placeholder('—')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('stato')
                    ->label('Stato')
                    ->badge()
                    ->state(fn (Prestito $record): string => $record->statoLabel())
                    ->color(fn (Prestito $record): string => match (true) {
                        $record->isInRitardo() => 'danger',
                        $record->stato === 'in_corso' => 'warning',
                        $record->stato === 'restituito' => 'success',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('note')
                    ->limit(40)
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('stato')
                    ->options([
                        'in_corso' => 'In prestito',
                        'restituito' => 'Restituiti',
                        'annullato' => 'Annullati',
                    ])
                    ->default('in_corso'),
                Tables\Filters\Filter::make('in_ritardo')
                    ->label('Solo in ritardo')
                    ->query(fn (Builder $query) => $query->scaduti()),
            ])
            ->actions([
                Tables\Actions\Action::make('restituito')
                    ->label('Restituito')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (Prestito $record): bool => $record->isInCorso())
                    ->requiresConfirmation()
                    ->modalHeading('Segna il libro come restituito')
                    ->action(fn (Prestito $record) => $record->registraRestituzione()),
                Tables\Actions\Action::make('prolunga')
                    ->label('Prolunga')
                    ->icon('heroicon-o-clock')
                    ->color('gray')
                    ->visible(fn (Prestito $record): bool => $record->isInCorso())
                    ->form([
                        Forms\Components\TextInput::make('giorni')
                            ->label('Giorni in più')
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(365)
                            ->default(14)
                            ->required(),
                    ])
                    ->action(fn (Prestito $record, array $data) => $record->prolunga((int) $data['giorni'])),
                Tables\Actions\Action::make('annulla')
                    ->label('Annulla')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (Prestito $record): bool => $record->isInCorso())
                    ->requiresConfirmation()
                    ->modalHeading('Annulla il prestito')
                    ->modalDescription('Il libro torna disponibile e il prestito resta nello storico come annullato.')
                    ->action(fn (Prestito $record) => $record->annulla()),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPrestiti::route('/'),
            'create' => Pages\CreatePrestito::route('/create'),
        ];
    }
}
