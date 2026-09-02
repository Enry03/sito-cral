<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FotoResource\Pages;
use App\Models\Attivita;
use App\Models\Foto;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class FotoResource extends Resource
{
    protected static ?string $model = Foto::class;

    protected static ?string $navigationIcon = 'heroicon-o-photo';

    protected static ?string $navigationLabel = 'Galleria foto';

    protected static ?string $modelLabel = 'Foto';

    protected static ?string $pluralModelLabel = 'Foto';

    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\FileUpload::make('percorso')
                    ->label('Immagine')
                    ->image()
                    ->directory('galleria')
                    ->required()
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('didascalia')
                    ->maxLength(255),
                Forms\Components\Select::make('attivita_id')
                    ->label('Attività collegata (opzionale)')
                    ->options(fn () => Attivita::query()->orderByDesc('data_evento')->pluck('titolo', 'id'))
                    ->searchable(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\ImageColumn::make('percorso')
                    ->label(''),
                Tables\Columns\TextColumn::make('didascalia'),
                Tables\Columns\TextColumn::make('attivita.titolo')
                    ->label('Attività'),
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

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFotos::route('/'),
            'create' => Pages\CreateFoto::route('/create'),
            'edit' => Pages\EditFoto::route('/{record}/edit'),
        ];
    }
}
