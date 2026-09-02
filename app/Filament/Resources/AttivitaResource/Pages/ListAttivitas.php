<?php

namespace App\Filament\Resources\AttivitaResource\Pages;

use App\Filament\Resources\AttivitaResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAttivitas extends ListRecords
{
    protected static string $resource = AttivitaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
