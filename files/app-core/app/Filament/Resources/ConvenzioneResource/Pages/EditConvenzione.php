<?php

namespace App\Filament\Resources\ConvenzioneResource\Pages;

use App\Filament\Resources\ConvenzioneResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditConvenzione extends EditRecord
{
    protected static string $resource = ConvenzioneResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
