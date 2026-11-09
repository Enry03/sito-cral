<?php

namespace App\Filament\Resources\AttivitaResource\Pages;

use App\Filament\Resources\AttivitaResource;
use App\Filament\Resources\AttivitaResource\Concerns\GestisceFotoAttivita;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAttivita extends EditRecord
{
    use GestisceFotoAttivita;

    protected static string $resource = AttivitaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        return $this->estraiCampiFoto($data);
    }

    protected function afterSave(): void
    {
        // Dopo aver aggiunto le foto si ricarica il modulo, così i file appena
        // caricati non restano nel campo (e non verrebbero aggiunti due volte).
        if ($this->elaboraFotoAttivita()) {
            $this->fillForm();
        }
    }
}
