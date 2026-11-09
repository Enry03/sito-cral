<?php

namespace App\Filament\Resources\FotoResource\Pages;

use App\Filament\Resources\AlbumResource;
use App\Filament\Resources\FotoResource;
use App\Filament\Support\FotoArchivio;
use App\Filament\Support\FotoUpload;
use App\Models\Attivita;
use App\Models\Foto;
use App\Services\GalleriaService;
use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Arr;

class ListFotos extends ListRecords
{
    protected static string $resource = FotoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('inserisciFoto')
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
                }),

            Actions\Action::make('inserisciAlbum')
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
                    Forms\Components\Select::make('attivita_id')
                        ->label('Attività collegata (opzionale)')
                        ->options(fn () => Attivita::query()->orderByDesc('data_evento')->pluck('titolo', 'id'))
                        ->searchable()
                        ->columnSpanFull(),
                    FotoUpload::make('percorso')
                        ->label('Nuove immagini')
                        ->helperText('Verrà creata una foto per ciascuna immagine, tutte in questo album. Chi non è socio con tessera vede solo le prime due.')
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
                            ->body("Carica delle immagini oppure scegline tra quelle già caricate: un album vuoto non comparirebbe sul sito.")
                            ->send();

                        return;
                    }

                    $album = app(GalleriaService::class)->creaAlbum($data['titolo'], $data['attivita_id'] ?? null, $archivio);

                    foreach ($nuove as $percorso) {
                        $album->foto()->create([
                            'percorso' => $percorso,
                            'didascalia' => $data['didascalia'] ?? null,
                            'attivita_id' => $album->attivita_id,
                        ]);
                    }

                    Notification::make()->success()->title('Album "'.$album->titolo.'" creato')->send();
                }),

            Actions\Action::make('gestisciAlbum')
                ->label('Gestisci album esistenti')
                ->icon('heroicon-o-cog-6-tooth')
                ->color('gray')
                ->url(fn () => AlbumResource::getUrl('index')),
        ];
    }
}
