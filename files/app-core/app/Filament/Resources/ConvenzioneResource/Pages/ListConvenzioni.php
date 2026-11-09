<?php

namespace App\Filament\Resources\ConvenzioneResource\Pages;

use App\Filament\Resources\ConvenzioneResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListConvenzioni extends ListRecords
{
    protected static string $resource = ConvenzioneResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('Nuova convenzione'),
        ];
    }
}
