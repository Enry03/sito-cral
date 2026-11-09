<?php

namespace App\Filament\Support;

use App\Models\Foto;
use Filament\Forms\Components\CheckboxList;

/**
 * Le foto già caricate ma non ancora in un album (l'"archivio" del sito): si
 * possono riprendere quando si crea un'attività o un album.
 */
class FotoArchivio
{
    /** Quante foto mostrare al massimo nell'elenco (le più recenti). */
    private const LIMITE = 120;

    public static function scelta(string $nome = 'foto_archivio'): CheckboxList
    {
        return CheckboxList::make($nome)
            ->label('Scegli tra le foto già caricate')
            ->helperText('Foto caricate in precedenza che non fanno parte di nessun album. Quelle segnate "archivio" non sono visibili sul sito finché non le usi qui.')
            ->options(fn (): array => self::opzioni())
            ->allowHtml()
            ->columns(['default' => 2, 'sm' => 3, 'lg' => 5])
            ->gridDirection('row')
            ->visible(fn (): bool => Foto::sciolte()->exists())
            ->columnSpanFull();
    }

    /**
     * @return array<int, string> id foto => anteprima in HTML
     */
    public static function opzioni(): array
    {
        return Foto::sciolte()
            ->latest()
            ->limit(self::LIMITE)
            ->get()
            ->mapWithKeys(function (Foto $foto): array {
                $didascalia = e(\Illuminate\Support\Str::limit((string) $foto->didascalia, 40));
                $etichetta = $foto->in_archivio ? ' <strong style="color:#E4002B">· archivio</strong>' : '';

                return [$foto->id => '<span style="display:block;line-height:1.2">'
                    .'<img src="'.e($foto->miniaturaUrl()).'" alt="" loading="lazy" style="display:block;width:100%;height:84px;object-fit:cover;border-radius:6px;margin-bottom:4px">'
                    .'<span style="font-size:11px">'.($didascalia !== '' ? $didascalia : 'Senza didascalia').$etichetta.'</span>'
                    .'</span>'];
            })
            ->all();
    }
}
