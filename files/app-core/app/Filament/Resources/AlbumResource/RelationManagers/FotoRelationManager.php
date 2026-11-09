<?php

namespace App\Filament\Resources\AlbumResource\RelationManagers;

use App\Filament\Support\FotoArchivio;
use App\Filament\Support\FotoUpload;
use App\Models\Foto;
use App\Services\GalleriaService;
use Filament\Notifications\Notification;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Arr;

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
                    ->helperText('Puoi selezionare più immagini insieme: verrà creata una foto per ciascuna.')
                    ->multiple(fn (string $operation): bool => $operation === 'create')
                    ->required()
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('didascalia')
                    ->maxLength(255)
                    ->columnSpanFull(),
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
                Tables\Actions\Action::make('daArchivio')
                    ->label('Aggiungi foto già caricate')
                    ->icon('heroicon-o-archive-box')
                    ->color('gray')
                    ->modalHeading('Aggiungi foto già caricate a questo album')
                    ->form([
                        FotoArchivio::scelta('foto_archivio')
                            ->label('Foto disponibili')
                            ->required(),
                    ])
                    ->visible(fn (): bool => Foto::sciolte()->exists())
                    ->action(function (array $data): void {
                        $n = app(GalleriaService::class)->spostaInAlbum($this->getOwnerRecord(), Arr::wrap($data['foto_archivio'] ?? []));

                        Notification::make()->success()->title($n === 1 ? "1 foto aggiunta all'album" : $n." foto aggiunte all'album")->send();
                    }),
                Tables\Actions\CreateAction::make()
                    ->using(function (array $data, Table $table) {
                        $relationship = $table->getRelationship();
                        $percorsi = Arr::wrap($data['percorso'] ?? []);
                        $comuni = Arr::except($data, ['percorso']);

                        $record = null;

                        foreach ($percorsi as $percorso) {
                            $record = $relationship->create([...$comuni, 'percorso' => $percorso, 'attivita_id' => $this->getOwnerRecord()->attivita_id]);
                        }

                        return $record;
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
