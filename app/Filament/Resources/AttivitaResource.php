<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AttivitaResource\Pages;
use App\Filament\Resources\AttivitaResource\RelationManagers;
use App\Models\Attivita;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

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
                Forms\Components\TextInput::make('titolo')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),
                Forms\Components\RichEditor::make('descrizione')
                    ->required()
                    ->columnSpanFull(),
                Forms\Components\DateTimePicker::make('data_evento')
                    ->label('Data e ora evento')
                    ->displayFormat('d/m/Y H:i'),
                Forms\Components\TextInput::make('luogo')
                    ->maxLength(255),
                Forms\Components\FileUpload::make('immagine_copertina')
                    ->label('Immagine di copertina')
                    ->image()
                    ->directory('attivita')
                    ->columnSpanFull(),
                Forms\Components\Toggle::make('pubblicata')
                    ->label('Visibile sul sito pubblico')
                    ->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('data_evento', 'desc')
            ->columns([
                Tables\Columns\ImageColumn::make('immagine_copertina')
                    ->label(''),
                Tables\Columns\TextColumn::make('titolo')
                    ->searchable(),
                Tables\Columns\TextColumn::make('data_evento')
                    ->label('Data evento')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                Tables\Columns\TextColumn::make('luogo')
                    ->searchable(),
                Tables\Columns\IconColumn::make('pubblicata')
                    ->label('Pubblicata')
                    ->boolean(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('pubblicata')
                    ->label('Pubblicata'),
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
