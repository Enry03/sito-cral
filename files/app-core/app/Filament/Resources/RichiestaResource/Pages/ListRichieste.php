<?php

namespace App\Filament\Resources\RichiestaResource\Pages;

use App\Filament\Resources\RichiestaResource;
use Filament\Resources\Pages\ListRecords;

class ListRichieste extends ListRecords
{
    protected static string $resource = RichiestaResource::class;

    protected static ?string $title = 'Richieste di iscrizione';
}
