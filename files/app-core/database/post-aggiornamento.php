<?php

// Passi dopo che i file del sito sono al loro posto: database, sessioni/cache, foto, controllo file.
// Lo includono _aggiorna.php (caricamento con FTP) e aggiorna-sito.php (aggiornamento automatico).
//
// Si aspetta queste variabili: $app (Laravel già avviato), $base (cartella app-core),
// $inizio e $limiteSecondi (tempo a disposizione). Imposta $restanti (foto ancora da
// alleggerire) e $problemi (file arrivati vuoti o mancanti).

$restanti = $restanti ?? 0;

// ------------------------------------------------------------ 1. migration
echo "== 1. Database ==\n";
\Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
echo trim(\Illuminate\Support\Facades\Artisan::output())."\n";

// ------------------------------------------- 2. sessioni e cache su file
echo "\n== 2. Velocità: sessioni e cache ==\n";
$envPath = defined('AGGIORNA_ENV_PATH') ? AGGIORNA_ENV_PATH : $app->environmentFilePath();

foreach (['framework/sessions', 'framework/cache/data', 'framework/views'] as $cartella) {
    $percorso = $app->storagePath($cartella);

    if (! is_dir($percorso)) {
        @mkdir($percorso, 0755, true);
    }
}

$scrivibile = is_dir($app->storagePath('framework/sessions')) && is_writable($app->storagePath('framework/sessions'))
    && is_dir($app->storagePath('framework/cache/data')) && is_writable($app->storagePath('framework/cache/data'));

if (! file_exists($envPath) || ! is_writable($envPath)) {
    echo "File .env non modificabile: salto questo passaggio (nessun danno, il sito funziona come prima).\n";
} elseif (! $scrivibile) {
    echo "Le cartelle storage/framework non sono scrivibili: salto questo passaggio.\n";
} else {
    $env = file_get_contents($envPath);
    $nuovo = preg_replace('/^SESSION_DRIVER=database\s*$/m', 'SESSION_DRIVER=file', $env, -1, $sessioni);
    $nuovo = preg_replace('/^CACHE_STORE=database\s*$/m', 'CACHE_STORE=file', $nuovo, -1, $cache);

    if ($sessioni + $cache === 0) {
        echo "Già impostato su file: niente da fare.\n";
    } else {
        $copia = dirname($envPath).'/.env.prima-ottimizzazione';

        if (! file_exists($copia)) {
            copy($envPath, $copia);
        }

        file_put_contents($envPath, $nuovo);
        echo 'Fatto: '.($sessioni ? 'sessioni ' : '').($cache ? 'cache ' : '')."ora su file (copia di sicurezza: .env.prima-ottimizzazione).\n";
        echo "Nota: chi è collegato in questo momento dovrà rifare il login una volta.\n";
    }
}

// ----------------------------------------------------------------- 3. foto
echo "\n== 3. Foto ==\n";
echo 'GD: '.(extension_loaded('gd') ? 'ok' : 'NON disponibile').' · EXIF: '.(function_exists('exif_read_data') ? 'ok' : 'NON disponibile')
    .' · memory_limit: '.ini_get('memory_limit').' · upload_max_filesize: '.ini_get('upload_max_filesize')."\n";

$ottimizzatore = new \App\Services\ImageOptimizer();

if (! \App\Services\ImageOptimizer::disponibile() || ! function_exists('exif_read_data')) {
    echo "Il server non ha tutte le estensioni per alleggerire le foto: le foto restano come sono\n"
        ."(il sito funziona uguale, e dal pannello le nuove foto vengono comunque ridotte dal browser).\n";
} else {
    $disco = \Illuminate\Support\Facades\Storage::disk('public');

    $foto = \App\Models\Foto::query()->pluck('percorso')->unique()->values();
    $copertine = \App\Models\Attivita::query()->whereNotNull('immagine_copertina')->pluck('immagine_copertina')->unique()->values();

    $daFare = [];

    foreach ($foto as $percorso) {
        if (! $disco->exists(\App\Services\ImageOptimizer::percorsoMiniatura($percorso)) && $disco->exists($percorso)) {
            $daFare[] = [$percorso, true];
        }
    }

    foreach ($copertine as $percorso) {
        if (! $disco->exists($percorso)) {
            continue;
        }

        $info = @getimagesize($disco->path($percorso));

        if ($info && (max($info[0], $info[1]) > \App\Services\ImageOptimizer::LATO_MAX || filesize($disco->path($percorso)) > \App\Services\ImageOptimizer::PESO_MAX_ORIGINALE)) {
            $daFare[] = [$percorso, false];
        }
    }

    $elaborate = 0;
    $risparmiati = 0;

    foreach ($daFare as $indice => [$percorso, $derivati]) {
        if (microtime(true) - $inizio > $limiteSecondi) {
            break;
        }

        $prima = $disco->exists($percorso) ? filesize($disco->path($percorso)) : 0;

        try {
            $ottimizzatore->elabora($percorso, true, $derivati);
        } catch (\Throwable $e) {
            echo "  ! {$percorso}: ".$e->getMessage()."\n";
        }

        clearstatcache(true, $disco->path($percorso));
        $risparmiati += max(0, $prima - (int) filesize($disco->path($percorso)));
        $elaborate++;
    }

    $restanti = count($daFare) - $elaborate;

    echo "Foto da alleggerire: ".count($daFare).' · elaborate ora: '.$elaborate.' · spazio recuperato: '.round($risparmiati / 1048576, 1)." MB\n";
}

// ------------------------------------------- 4. controllo file e pulizia
echo "\n== 4. Controllo dei file del sito ==\n";
$problemi = 0;
$pubblico = fn (string $percorso) => $app->publicPath($percorso);

// 4a. I file grafici e JavaScript del sito devono essere arrivati interi (non vuoti).
$manifestPath = $pubblico('build/manifest.json');
$referenziati = [];

if (is_file($manifestPath)) {
    $manifest = json_decode((string) file_get_contents($manifestPath), true) ?: [];

    foreach ($manifest as $voce) {
        foreach (array_merge([$voce['file'] ?? null], $voce['css'] ?? []) as $file) {
            if ($file) {
                $referenziati[] = $file;
            }
        }
    }

    foreach ($referenziati as $file) {
        $dimensione = is_file($pubblico('build/'.$file)) ? filesize($pubblico('build/'.$file)) : -1;

        if ($dimensione <= 0) {
            $problemi++;
            echo "  ! PROBLEMA: build/{$file} ".($dimensione < 0 ? 'manca' : 'è vuoto (0 byte)').": va ricaricato con FileZilla.\n";
        } else {
            echo "  ok build/{$file} (".number_format($dimensione, 0, ',', '.')." byte)\n";
        }
    }
} else {
    $problemi++;
    echo "  ! PROBLEMA: manca build/manifest.json\n";
}

foreach (['bricolage-latin', 'figtree-latin'] as $carattere) {
    $dimensione = is_file($pubblico("fonts/{$carattere}.woff2")) ? filesize($pubblico("fonts/{$carattere}.woff2")) : -1;

    if ($dimensione <= 0) {
        $problemi++;
        echo "  ! PROBLEMA: fonts/{$carattere}.woff2 ".($dimensione < 0 ? 'manca' : 'è vuoto').".\n";
    } else {
        echo "  ok fonts/{$carattere}.woff2\n";
    }
}

// 4b. Pulizia: l'immagine vuota della tessera non deve più essere scaricabile dal sito
//     (adesso sta in una cartella non pubblica e serve solo per l'email di approvazione).
$vecchiaTessera = $pubblico('images/tessera-cral.jpg');

if (is_file($vecchiaTessera)) {
    echo @unlink($vecchiaTessera)
        ? "  ok eliminata dalla cartella pubblica l'immagine vuota della tessera\n"
        : "  ! non riesco a eliminare images/tessera-cral.jpg: cancellala con FileZilla (cartella images).\n";
}

// 4b-bis. Pulizia: file del vecchio aspetto grafico che non servono più (nessuna pagina li usa).
$obsoleti = [
    'resources/views/components/tessera-card.blade.php',
    'resources/views/components/nav-link.blade.php',
    'resources/views/components/responsive-nav-link.blade.php',
    'resources/views/components/dropdown.blade.php',
    'resources/views/components/dropdown-link.blade.php',
    'resources/views/layouts/app.blade.php',
    'resources/views/layouts/navigation.blade.php',
    'app/View/Components/AppLayout.php',
];
$rimossi = 0;

foreach ($obsoleti as $relativo) {
    if (is_file($base.'/'.$relativo) && @unlink($base.'/'.$relativo)) {
        $rimossi++;
    }
}

if ($rimossi > 0) {
    echo "  ok file del vecchio aspetto grafico eliminati: {$rimossi}\n";
}

// 4c. Pulizia: file grafici/JavaScript di versioni precedenti (solo se quelli nuovi sono tutti a posto).
if ($problemi === 0 && $referenziati !== [] && is_dir($pubblico('build/assets'))) {
    $tenuti = array_map(fn ($file) => basename($file), $referenziati);
    $eliminati = 0;

    foreach (glob($pubblico('build/assets').'/*') ?: [] as $file) {
        if (is_file($file) && ! in_array(basename($file), $tenuti, true) && @unlink($file)) {
            $eliminati++;
        }
    }

    echo "  ok vecchi file eliminati: {$eliminati}\n";
}

// 4d. Svuota le copie temporanee (pagine già compilate, configurazioni in cache).
try {
    \Illuminate\Support\Facades\Artisan::call('optimize:clear');
    echo "  ok copie temporanee svuotate\n";
} catch (\Throwable $e) {
    echo '  ! pulizia delle copie temporanee non riuscita: '.$e->getMessage()."\n";
}

if ($problemi > 0) {
    echo "\n  ATTENZIONE: {$problemi} file non sono arrivati interi. Il sito potrebbe sembrare 'rotto' (menu o grafica mancanti)\n"
        ."  finché non li ricarichi. Questo file NON si elimina da solo: dopo aver sistemato, riaprilo per ricontrollare.\n";
}
