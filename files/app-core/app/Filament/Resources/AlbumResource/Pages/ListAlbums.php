<?php

namespace App\Filament\Resources\AlbumResource\Pages;

use App\Filament\Resources\AlbumResource;
use App\Filament\Resources\FotoResource;
use App\Filament\Support\AzioniGalleria;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAlbums extends ListRecords
{
    protected static string $resource = AlbumResource::class;

    public function getTitle(): string
    {
        return 'Galleria foto';
    }

    protected function getHeaderActions(): array
    {
        return [
            AzioniGalleria::creaAlbum()->color('primary'),
            AzioniGalleria::creaMacroAlbum(),
            AzioniGalleria::caricaFoto()->color('gray'),

            Actions\Action::make('fotoSingole')
                ->label('Foto singole e archivio')
                ->icon('heroicon-o-archive-box')
                ->color('gray')
                ->url(fn () => FotoResource::getUrl('index')),
        ];
    }
}
