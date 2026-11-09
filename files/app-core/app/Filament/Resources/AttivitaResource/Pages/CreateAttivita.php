<?php

namespace App\Filament\Resources\AttivitaResource\Pages;

use App\Filament\Resources\AttivitaResource;
use App\Filament\Resources\AttivitaResource\Concerns\GestisceFotoAttivita;
use Filament\Resources\Pages\CreateRecord;

class CreateAttivita extends CreateRecord
{
    use GestisceFotoAttivita;

    protected static string $resource = AttivitaResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return $this->estraiCampiFoto($data);
    }

    protected function afterCreate(): void
    {
        $this->elaboraFotoAttivita();
    }
}
