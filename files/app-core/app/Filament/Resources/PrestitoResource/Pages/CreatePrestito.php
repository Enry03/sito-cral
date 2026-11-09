<?php

namespace App\Filament\Resources\PrestitoResource\Pages;

use App\Filament\Resources\PrestitoResource;
use App\Models\Libro;
use App\Models\User;
use App\Services\OperazioneNonConsentita;
use App\Services\PrestitoService;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreatePrestito extends CreateRecord
{
    protected static string $resource = PrestitoResource::class;

    protected static bool $canCreateAnother = false;

    protected function handleRecordCreation(array $data): Model
    {
        try {
            $prestito = app(PrestitoService::class)->presta(
                User::findOrFail($data['user_id']),
                Libro::findOrFail($data['libro_id']),
                $data['note'] ?? null,
            );
        } catch (OperazioneNonConsentita $e) {
            Notification::make()->danger()->title('Prestito non registrato')->body($e->getMessage())->persistent()->send();

            $this->halt();
        }

        // Il servizio usa le date standard: qui l'amministratore può averle cambiate.
        $prestito->update([
            'data_prestito' => $data['data_prestito'],
            'scadenza' => $data['scadenza'],
        ]);

        return $prestito;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
