<?php

namespace App\Filament\Resources\FotoResource\Pages;

use App\Filament\Resources\AlbumResource;
use App\Filament\Resources\FotoResource;
use App\Filament\Support\AzioniGalleria;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListFotos extends ListRecords
{
    protected static string $resource = FotoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            AzioniGalleria::caricaFoto(),
            AzioniGalleria::creaAlbum(),
            AzioniGalleria::creaMacroAlbum(),

            Actions\Action::make('gestisciAlbum')
                ->label('Vai agli album')
                ->icon('heroicon-o-rectangle-stack')
                ->color('gray')
                ->url(fn () => AlbumResource::getUrl('index')),
        ];
    }
}
