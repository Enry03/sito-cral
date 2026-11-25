<?php

namespace App\Filament\Support;

use App\Models\Album;
use App\Models\Attivita;
use App\Models\Foto;
use App\Services\GalleriaService;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Notifications\Notification;
use Illuminate\Support\Arr;

/**
 * Le azioni della galleria che si usano sia dall'elenco degli album sia da quello
 * delle foto singole: caricare foto, creare un album, creare un macro-album.
 */
class AzioniGalleria
{
    public static function caricaFoto(): Action
    {
        return Action::make('inserisciFoto')
            ->label('Carica foto')
            ->icon('heroicon-o-photo')
            ->modalHeading('Carica foto')
            ->modalDescription("Le foto non devono per forza far parte di un album o di un'attività: potrai riprenderle più avanti quando ne crei una.")
            ->form([
                FotoUpload::make('percorso')
                    ->label('Immagini')
                    ->helperText('Puoi selezionare più immagini insieme: verrà creata una foto per ciascuna.')
                    ->multiple()
                    ->required()
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('didascalia')
                    ->maxLength(255)
                    ->helperText('Se carichi più immagini insieme, la stessa didascalia verrà applicata a tutte.'),
                Forms\Components\Select::make('attivita_id')
                    ->label('Attività collegata (opzionale)')
                    ->options(fn () => Attivita::query()->orderByDesc('data_evento')->pluck('titolo', 'id'))
                    ->searchable(),
                Forms\Components\Toggle::make('in_archivio')
                    ->label("Tienile solo nell'archivio")
                    ->helperText("Le foto non compaiono sul sito: restano a disposizione per quando creerai un'attività o un album. Se non spunti, compaiono subito tra le foto singole, visibili a tutti.")
                    ->inline(false)
                    ->columnSpanFull(),
            ])
            ->action(function (array $data): void {
                $percorsi = Arr::wrap($data['percorso'] ?? []);
                $comuni = Arr::except($data, ['percorso']);

                foreach ($percorsi as $percorso) {
                    Foto::create([...$comuni, 'percorso' => $percorso]);
                }

                Notification::make()->success()
                    ->title(count($percorsi) === 1 ? 'Foto caricata' : count($percorsi).' foto caricate')
                    ->body(($data['in_archivio'] ?? false) ? "Sono nell'archivio: non compaiono sul sito finché non le usi." : null)
                    ->send();
            });
    }

    public static function creaAlbum(): Action
    {
        return Action::make('inserisciAlbum')
            ->label('Crea album')
            ->icon('heroicon-o-rectangle-stack')
            ->color('gray')
            ->modalHeading('Crea un album')
            ->form([
                Forms\Components\TextInput::make('titolo')
                    ->label('Titolo album')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),
                Forms\Components\DatePicker::make('data_album')
                    ->label("Data dell'album (facoltativa)")
                    ->helperText("Per metterlo in ordine cronologico sul sito: se è un album di foto vecchie scrivi la data in cui sono state scattate. Se vuota vale la data dell'attività collegata.")
                    ->displayFormat('d/m/Y')
                    ->columnSpanFull(),
                Forms\Components\Select::make('parent_id')
                    ->label('Dentro un album principale (opzionale)')
                    ->helperText('Per fare un sotto-album: scegli il macro-album che lo contiene (es. "Concorso fotografico" → "Giorno 1").')
                    ->options(fn () => self::albumPrincipali())
                    ->searchable()
                    ->live()
                    ->columnSpanFull(),
                Forms\Components\Select::make('attivita_id')
                    ->label('Attività collegata (opzionale)')
                    ->options(fn () => Attivita::query()->orderByDesc('data_evento')->pluck('titolo', 'id'))
                    ->searchable()
                    ->disabled(fn (Forms\Get $get): bool => filled($get('parent_id')))
                    ->helperText(fn (Forms\Get $get): ?string => filled($get('parent_id')) ? "Un sotto-album segue l'attività del suo macro-album." : null)
                    ->columnSpanFull(),
                FotoUpload::make('percorso')
                    ->label('Nuove immagini')
                    ->helperText('Verrà creata una foto per ciascuna immagine, tutte in questo album. Le vede solo chi è iscritto al sito o socio CRAL.')
                    ->multiple()
                    ->columnSpanFull(),
                FotoArchivio::scelta('foto_archivio')
                    ->label('Oppure usa foto già caricate'),
                Forms\Components\TextInput::make('didascalia')
                    ->label('Didascalia per le nuove immagini (opzionale)')
                    ->maxLength(255)
                    ->columnSpanFull(),
            ])
            ->action(function (array $data): void {
                $nuove = array_values(array_filter(Arr::wrap($data['percorso'] ?? [])));
                $archivio = array_values(array_filter(Arr::wrap($data['foto_archivio'] ?? [])));

                if ($nuove === [] && $archivio === []) {
                    Notification::make()->warning()
                        ->title('Aggiungi almeno una foto')
                        ->body('Carica delle immagini oppure scegline tra quelle già caricate: un album vuoto non comparirebbe sul sito. Per un contenitore di album usa "Crea macro-album".')
                        ->send();

                    return;
                }

                $album = app(GalleriaService::class)->creaAlbum($data['titolo'], $data['attivita_id'] ?? null, $archivio, $data['parent_id'] ?? null, $data['data_album'] ?? null);

                foreach ($nuove as $percorso) {
                    $album->foto()->create([
                        'percorso' => $percorso,
                        'didascalia' => $data['didascalia'] ?? null,
                        'attivita_id' => $album->attivita_id,
                    ]);
                }

                Notification::make()->success()->title('Album "'.$album->titolo.'" creato')->send();
            });
    }

    /** Un album che serve solo a raccogliere altri album: i sotto-album si aggiungono dopo, aprendolo. */
    public static function creaMacroAlbum(): Action
    {
        return Action::make('creaMacroAlbum')
            ->label('Crea macro-album')
            ->icon('heroicon-o-folder-plus')
            ->color('gray')
            ->modalHeading('Crea un macro-album')
            ->modalDescription('Un macro-album raccoglie altri album (per esempio "Concorso fotografico" con dentro "Giorno 1", "Giorno 2"...). Dopo averlo creato, aprilo per aggiungere i sotto-album e le foto. Sul sito compare quando ha almeno un album con delle foto.')
            ->form([
                Forms\Components\TextInput::make('titolo')
                    ->label('Titolo del macro-album')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Select::make('attivita_id')
                    ->label('Attività collegata (opzionale)')
                    ->options(fn () => Attivita::query()->orderByDesc('data_evento')->pluck('titolo', 'id'))
                    ->searchable(),
                Forms\Components\DatePicker::make('data_album')
                    ->label("Data (facoltativa)")
                    ->helperText("Per l'ordine cronologico. Se vuota vale la data dell'attività collegata.")
                    ->displayFormat('d/m/Y'),
            ])
            ->action(function (array $data) {
                $album = Album::create(['titolo' => $data['titolo'], 'attivita_id' => $data['attivita_id'] ?? null, 'data_album' => $data['data_album'] ?? null]);

                Notification::make()->success()->title('Macro-album "'.$album->titolo.'" creato')->body('Ora aprilo per aggiungere i sotto-album.')->send();

                return redirect(\App\Filament\Resources\AlbumResource::getUrl('edit', ['record' => $album]));
            });
    }

    /** @return array<int, string> album di primo livello, per i menu */
    public static function albumPrincipali(?int $escludi = null): array
    {
        return Album::principali()
            ->when($escludi, fn ($query) => $query->whereKeyNot($escludi))
            ->orderBy('titolo')
            ->pluck('titolo', 'id')
            ->all();
    }
}
