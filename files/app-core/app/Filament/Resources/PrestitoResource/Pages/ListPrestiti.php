<?php

namespace App\Filament\Resources\PrestitoResource\Pages;

use App\Filament\Resources\PrestitoResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPrestiti extends ListRecords
{
    protected static string $resource = PrestitoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Registra un prestito'),
        ];
    }
}
