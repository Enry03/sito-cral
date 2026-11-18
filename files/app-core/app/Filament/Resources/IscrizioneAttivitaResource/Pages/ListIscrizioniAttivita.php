<?php

namespace App\Filament\Resources\IscrizioneAttivitaResource\Pages;

use App\Filament\Resources\IscrizioneAttivitaResource;
use Filament\Resources\Pages\ListRecords;

class ListIscrizioniAttivita extends ListRecords
{
    protected static string $resource = IscrizioneAttivitaResource::class;

    protected static ?string $title = 'Iscrizioni alle attività';
}
