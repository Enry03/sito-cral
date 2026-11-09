<?php

namespace App\Filament\Resources\ConvenzioneResource\Pages;

use App\Filament\Resources\ConvenzioneResource;
use Filament\Resources\Pages\CreateRecord;

class CreateConvenzione extends CreateRecord
{
    protected static string $resource = ConvenzioneResource::class;

    public function getTitle(): string
    {
        return 'Nuova convenzione';
    }

    public function getBreadcrumb(): string
    {
        return 'Nuova';
    }
}
