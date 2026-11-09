<?php

namespace App\Filament\Resources\LibroResource\Pages;

use App\Filament\Resources\LibroResource;
use App\Services\LibraryThingImporter;
use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Storage;

class ListLibri extends ListRecords
{
    protected static string $resource = LibroResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),

            Actions\Action::make('importa')
                ->label('Importa da LibraryThing')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('gray')
                ->modalHeading('Importa i libri da LibraryThing')
                ->modalDescription('In LibraryThing apri "La tua libreria", scegli "Esporta" e scarica il file CSV (o con tabulazioni). Poi caricalo qui: i libri nuovi vengono aggiunti, quelli già presenti (stesso ISBN o stesso ID LibraryThing) vengono aggiornati.')
                ->modalSubmitActionLabel('Importa')
                ->form([
                    Forms\Components\FileUpload::make('file')
                        ->label('File esportato da LibraryThing')
                        ->disk('local')
                        ->directory('import-libri')
                        ->visibility('private')
                        ->acceptedFileTypes(['text/csv', 'text/plain', 'text/tab-separated-values', 'application/csv', 'application/vnd.ms-excel', 'application/octet-stream'])
                        ->maxSize(10240)
                        ->required(),
                    Forms\Components\Toggle::make('aggiorna')
                        ->label('Aggiorna i libri già presenti')
                        ->default(true),
                ])
                ->action(function (array $data): void {
                    $disco = Storage::disk('local');
                    $file = $data['file'];

                    try {
                        $esito = app(LibraryThingImporter::class)->importa($disco->path($file), (bool) $data['aggiorna']);
                    } catch (\Throwable $e) {
                        report($e);

                        Notification::make()
                            ->danger()
                            ->title('Importazione non riuscita')
                            ->body($e instanceof \InvalidArgumentException
                                ? $e->getMessage()
                                : "Il file non è stato letto correttamente. Controlla che sia l'esportazione di LibraryThing.")
                            ->persistent()
                            ->send();

                        return;
                    } finally {
                        $disco->delete($file);
                    }

                    $corpo = "Aggiunti: {$esito['creati']} · Aggiornati: {$esito['aggiornati']} · Saltati: {$esito['saltati']}";

                    if ($esito['errori'] !== []) {
                        $corpo .= "\n".implode("\n", array_slice($esito['errori'], 0, 5));
                    }

                    Notification::make()->success()->title('Importazione completata')->body($corpo)->persistent()->send();
                }),
        ];
    }
}
