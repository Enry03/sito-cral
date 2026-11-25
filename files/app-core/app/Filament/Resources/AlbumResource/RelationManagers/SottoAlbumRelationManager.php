<?php

namespace App\Filament\Resources\AlbumResource\RelationManagers;

use App\Filament\Resources\AlbumResource;
use App\Filament\Support\FotoUpload;
use App\Models\Album;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

/**
 * Gli album dentro un macro-album (es. "Giorno 1", "Giorno 2" dentro "Concorso
 * fotografico"). Si vedono come riquadri; si entra in ognuno per cambiarne le foto.
 * Un sotto-album non può contenere altri album, quindi qui non compare.
 */
class SottoAlbumRelationManager extends RelationManager
{
    protected static string $relationship = 'sottoAlbum';

    protected static ?string $title = 'Album contenuti';

    protected static ?string $modelLabel = 'album';

    protected static ?string $pluralModelLabel = 'album';

    public static function canViewForRecord(Model $ownerRecord, string $pageClass): bool
    {
        return $ownerRecord->parent_id === null;
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('titolo')
                    ->label('Titolo del sotto-album')
                    ->placeholder('Es. Foto giorno 1')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),
                Forms\Components\DatePicker::make('data_album')
                    ->label('Data (facoltativa)')
                    ->helperText("Il giorno a cui si riferiscono le foto: i sotto-album si mettono in ordine di data. Se vuota vale la data dell'attività.")
                    ->displayFormat('d/m/Y'),
                FotoUpload::make('percorso')
                    ->label('Foto (facoltative, si possono aggiungere anche dopo)')
                    ->multiple()
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('titolo')
            ->modifyQueryUsing(fn ($query) => $query->with(['copertinaFoto', 'macroAlbum', 'attivita'])->conConteggi()->cronologici('asc'))
            ->contentGrid(['default' => 1, 'sm' => 2, 'xl' => 3])
            ->columns(AlbumResource::colonneScheda())
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Aggiungi un album')
                    ->modalHeading('Nuovo album dentro «'.$this->getOwnerRecord()->titolo.'»')
                    ->using(function (array $data): Album {
                        $album = $this->getOwnerRecord()->sottoAlbum()->create(['titolo' => $data['titolo'], 'data_album' => $data['data_album'] ?? null]);

                        foreach (array_values(array_filter(Arr::wrap($data['percorso'] ?? []))) as $percorso) {
                            $album->foto()->create(['percorso' => $percorso, 'attivita_id' => $album->attivita_id]);
                        }

                        return $album;
                    }),
            ])
            ->actions([
                Tables\Actions\Action::make('apri')
                    ->label('Apri e modifica le foto')
                    ->icon('heroicon-o-pencil-square')
                    ->url(fn (Album $record): string => AlbumResource::getUrl('edit', ['record' => $record])),
                Tables\Actions\DeleteAction::make()
                    ->modalDescription("Le foto dell'album non vengono cancellate: diventano foto singole."),
            ])
            ->recordUrl(fn (Album $record): string => AlbumResource::getUrl('edit', ['record' => $record]))
            ->emptyStateHeading('Nessun album qui dentro')
            ->emptyStateDescription('Aggiungi gli album che vuoi raccogliere in questo macro-album (per esempio un album per ogni giorno).')
            ->emptyStateIcon('heroicon-o-rectangle-stack');
    }
}
