<?php

namespace App\Filament\Resources\AlbumResource\Pages;

use App\Filament\Resources\AlbumResource;
use App\Filament\Resources\FotoResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAlbums extends ListRecords
{
    protected static string $resource = AlbumResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('tornaAFoto')
                ->label('Torna a Galleria foto')
                ->icon('heroicon-o-arrow-uturn-left')
                ->color('gray')
                ->url(fn () => FotoResource::getUrl('index')),
        ];
    }
}
