<?php

namespace App\Filament\Support;

use Filament\Forms\Components\FileUpload;

/**
 * Campo di caricamento immagini usato in tutto il pannello. Le foto vengono
 * ridimensionate dal browser prima dell'invio (lato lungo massimo 1920px):
 * l'upload è più veloce e il sito non si porta dietro file da 8 MB.
 */
class FotoUpload
{
    public static function make(string $nome = 'percorso', string $cartella = 'galleria', int $lato = 1920): FileUpload
    {
        return FileUpload::make($nome)
            ->image()
            ->maxSize(8192)
            ->directory($cartella)
            ->imageResizeMode('contain')
            ->imageResizeTargetWidth((string) $lato)
            ->imageResizeTargetHeight((string) $lato)
            ->imageResizeUpscale(false);
    }
}
