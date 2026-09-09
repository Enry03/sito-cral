<?php

namespace App\Filament\Resources\AttivitaResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class FotoRelationManager extends RelationManager
{
    protected static string $relationship = 'foto';

    protected static ?string $title = 'Foto';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\FileUpload::make('percorso')
                    ->label('Immagine')
                    ->image()
                    ->maxSize(5120)
                    ->directory('galleria')
                    ->required()
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('didascalia')
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('didascalia')
            ->columns([
                Tables\Columns\ImageColumn::make('percorso')
                    ->label(''),
                Tables\Columns\TextColumn::make('didascalia'),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }
}
