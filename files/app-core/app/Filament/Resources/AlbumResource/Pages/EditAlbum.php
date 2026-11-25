<?php

namespace App\Filament\Resources\AlbumResource\Pages;

use App\Filament\Resources\AlbumResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAlbum extends EditRecord
{
    protected static string $resource = AlbumResource::class;

    public function getTitle(): string
    {
        return $this->getRecord()->titolo;
    }

    protected function getHeaderActions(): array
    {
        $album = $this->getRecord();

        return [
            Actions\Action::make('tornaAgliAlbum')
                ->label($album->macroAlbum ? 'Torna a «'.$album->macroAlbum->titolo.'»' : 'Tutti gli album')
                ->icon('heroicon-o-arrow-uturn-left')
                ->color('gray')
                ->url(fn () => $album->macroAlbum
                    ? AlbumResource::getUrl('edit', ['record' => $album->macroAlbum])
                    : AlbumResource::getUrl('index')),
            Actions\Action::make('vediSulSito')
                ->label('Vedi sul sito')
                ->icon('heroicon-o-arrow-top-right-on-square')
                ->color('gray')
                ->url(fn () => route('foto.album', $album))
                ->openUrlInNewTab(),
            Actions\DeleteAction::make()
                ->modalDescription("Le foto non vengono cancellate: diventano foto singole. I sotto-album diventano album principali."),
        ];
    }
}
