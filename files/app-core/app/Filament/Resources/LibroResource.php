<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LibroResource\Pages;
use App\Filament\Support\FotoUpload;
use App\Models\Libro;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class LibroResource extends Resource
{
    protected static ?string $model = Libro::class;

    protected static ?string $navigationIcon = 'heroicon-o-book-open';

    protected static ?string $navigationGroup = 'Libreria';

    protected static ?string $navigationLabel = 'Libri';

    protected static ?string $modelLabel = 'Libro';

    protected static ?string $pluralModelLabel = 'Libri';

    protected static ?int $navigationSort = 5;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Libro')
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('titolo')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('autore')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('categoria')
                            ->maxLength(255)
                            ->datalist(fn () => Libro::query()->whereNotNull('categoria')->distinct()->orderBy('categoria')->pluck('categoria')->all()),
                        Forms\Components\TextInput::make('editore')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('anno')
                            ->numeric()
                            ->minValue(1000)
                            ->maxValue((int) date('Y') + 1),
                        Forms\Components\TextInput::make('isbn')
                            ->label('ISBN')
                            ->maxLength(20),
                        Forms\Components\TextInput::make('codice')
                            ->label('Codice interno')
                            ->helperText('Il codice con cui il libro è catalogato dal CRAL (facoltativo, deve essere unico).')
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),
                        Forms\Components\TextInput::make('copie_totali')
                            ->label('Copie possedute')
                            ->numeric()
                            ->minValue(1)
                            ->default(1)
                            ->required(),
                        Forms\Components\Textarea::make('descrizione')
                            ->rows(5)
                            ->columnSpanFull(),
                        FotoUpload::make('copertina', 'libri', 800)
                            ->label('Copertina')
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->conDisponibilita())
            ->defaultSort('titolo')
            ->columns([
                Tables\Columns\ImageColumn::make('copertina')
                    ->label(''),
                Tables\Columns\TextColumn::make('titolo')
                    ->searchable()
                    ->sortable()
                    ->wrap(),
                Tables\Columns\TextColumn::make('autore')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('categoria')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('isbn')
                    ->label('ISBN')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('codice')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('copie')
                    ->label('Disponibili')
                    ->state(fn (Libro $record): string => $record->copieDisponibili().' / '.$record->copie_totali)
                    ->badge()
                    ->color(fn (Libro $record): string => $record->isDisponibile() ? 'success' : 'danger'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('categoria')
                    ->options(fn () => Libro::query()->whereNotNull('categoria')->distinct()->orderBy('categoria')->pluck('categoria', 'categoria')->all()),
                Tables\Filters\Filter::make('in_prestito')
                    ->label('Con copie in prestito')
                    ->query(fn (Builder $query) => $query->whereHas('prestiti', fn ($q) => $q->where('stato', 'in_corso'))),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->before(function (Tables\Actions\DeleteAction $action, Libro $record) {
                        if ($record->prestiti()->where('stato', 'in_corso')->exists()) {
                            Notification::make()
                                ->danger()
                                ->title('Il libro ha prestiti in corso')
                                ->body('Registra prima la restituzione, poi potrai eliminarlo.')
                                ->send();

                            $action->cancel();
                        }
                    }),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLibri::route('/'),
            'create' => Pages\CreateLibro::route('/create'),
            'edit' => Pages\EditLibro::route('/{record}/edit'),
        ];
    }
}
