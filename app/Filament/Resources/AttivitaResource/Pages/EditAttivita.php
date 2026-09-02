<?php

namespace App\Filament\Resources\AttivitaResource\Pages;

use App\Filament\Resources\AttivitaResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAttivita extends EditRecord
{
    protected static string $resource = AttivitaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
