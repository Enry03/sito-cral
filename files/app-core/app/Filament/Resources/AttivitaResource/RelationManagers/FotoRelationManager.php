<?php

namespace App\Filament\Resources\AttivitaResource\RelationManagers;

use App\Filament\Support\FotoUpload;
use App\Models\Album;
use App\Models\Foto;
use App\Services\GalleriaService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
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
                FotoUpload::make('percorso')
                    ->label('Immagini')
                    ->helperText('Puoi selezionare più foto insieme. Con più di una foto l\'album si crea da solo.')
                    ->multiple(fn (string $operation): bool => $operation === 'create')
                    ->required()
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('didascalia')
                    ->maxLength(255),
                Forms\Components\Select::make('album_id')
                    ->label('Album')
                    ->options(fn () => Album::where('attivita_id', $this->getOwnerRecord()->id)->pluck('titolo', 'id'))
                    ->placeholder('Nessun album (foto singola, visibile a tutti)'),
                Forms\Components\Toggle::make('crea_album')
                    ->label('Crea un album anche con una sola foto')
                    ->inline(false)
                    ->visible(fn (string $operation): bool => $operation === 'create'),
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
                Tables\Columns\TextColumn::make('album.titolo')
                    ->label('Album')
                    ->placeholder('Foto singola'),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->using(function (array $data) {
                        $esito = app(GalleriaService::class)->aggiungiFotoAttivita(
                            $this->getOwnerRecord(),
                            $data['percorso'] ?? [],
                            $data['didascalia'] ?? null,
                            filled($data['album_id'] ?? null) ? (int) $data['album_id'] : null,
                            (bool) ($data['crea_album'] ?? false),
                        );

                        if ($esito['album']) {
                            Notification::make()->title('Foto aggiunte all\'album "'.$esito['album']->titolo.'"')->success()->send();
                        }

                        return Foto::where('attivita_id', $this->getOwnerRecord()->id)->latest('id')->firstOrFail();
                    }),
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
}
