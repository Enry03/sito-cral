<?php

// Passi dopo che i file del sito sono al loro posto: database, sessioni/cache, foto, controllo file.
// Lo includono _aggiorna.php (caricamento con FTP) e aggiorna-sito.php (aggiornamento automatico).
//
// Si aspetta queste variabili: $app (Laravel già avviato), $base (cartella app-core),
// $inizio e $limiteSecondi (tempo a disposizione). Imposta $restanti (foto ancora da
// alleggerire) e $problemi (file arrivati vuoti o mancanti).

$restanti = $restanti ?? 0;

// ------------------------------------------------ 0. sicurezza: file riservati e upload
// Nella cartella pubblica (quella raggiungibile da internet) non deve esserci nessun
// file con le password: il .env vero sta in app-core, che il server non serve.
echo "== 0. Sicurezza ==\n";

$cartellaPubblica = isset($radice) ? (string) $radice : dirname($base);

if (basename($base) === 'app-core' && is_dir($cartellaPubblica)) {
    $envInUso = @realpath($app->environmentFilePath());
    $quarantena = $base.'/storage/app/private';

    foreach (array_merge(['.env'], array_map('basename', glob($cartellaPubblica.'/.env.*') ?: [])) as $nome) {
        $esposto = $cartellaPubblica.'/'.$nome;

        if (! is_file($esposto)) {
            continue;
        }

        $inUso = $envInUso !== false && @realpath($esposto) === $envInUso;

        if ($inUso || ! is_file($base.'/.env')) {
            echo "  ATTENZIONE: {$nome} è nella cartella pubblica ed è quello che il sito usa. Non lo tocco: spostalo in app-core con FTP.\n";
            $problemi = ($problemi ?? 0) + 1;

            continue;
        }

        // Si sposta in una cartella che il server non serve (non si cancella: resta recuperabile).
        @mkdir($quarantena, 0755, true);
        $destinazione = $quarantena.'/'.ltrim($nome, '.').'.tolto-dalla-cartella-pubblica-'.date('Ymd-His');

        if (@rename($esposto, $destinazione)) {
            echo "  RIMOSSO dalla cartella pubblica: {$nome} (scaricabile da chiunque). Era una copia del file con le password: CAMBIA la password del database e la chiave del sito.\n";
        } else {
            echo "  ATTENZIONE: {$nome} è scaricabile da internet e non riesco a spostarlo. Cancellalo con FTP dalla cartella principale e cambia la password del database.\n";
            $problemi = ($problemi ?? 0) + 1;
        }
    }

    // Nella cartella delle foto caricate non si eseguono mai programmi PHP.
    $regoleUpload = "# Qui ci sono solo foto e documenti: nessun programma PHP viene eseguito.\n"
        ."<IfModule mod_authz_core.c>\n    <FilesMatch \"\\.(php[0-9]?|phtml|phar|pl|cgi)\$\">\n        Require all denied\n    </FilesMatch>\n</IfModule>\n"
        ."<IfModule mod_php.c>\n    php_flag engine off\n</IfModule>\n";
    $cartellaUpload = $cartellaPubblica.'/uploads';

    if (is_dir($cartellaUpload) && is_writable($cartellaUpload) && (! is_file($cartellaUpload.'/.htaccess') || md5_file($cartellaUpload.'/.htaccess') !== md5($regoleUpload))) {
        @file_put_contents($cartellaUpload.'/.htaccess', $regoleUpload);
        echo "  Protetta la cartella delle foto caricate (niente PHP).\n";
    }
}

echo "  Controllo completato.\n\n";

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

    // Le foto già provate (e che non si riescono ad alleggerire di più: già ottimizzate o in un
    // formato non supportato) si ricordano, così l'aggiornamento arriva a "FATTO" invece di
    // riprovarle all'infinito. Se il file cambia, si riprova.
    $registroProvate = $app->storagePath('framework/foto-gia-provate.json');
    $provate = is_file($registroProvate) ? (array) json_decode((string) file_get_contents($registroProvate), true) : [];
    $firma = fn (string $percorso): string => $percorso.'|'.(int) @filesize($disco->path($percorso)).'|'.(int) @filemtime($disco->path($percorso));

    $daFare = [];

    foreach ($foto as $percorso) {
        if (! $disco->exists(\App\Services\ImageOptimizer::percorsoMiniatura($percorso)) && $disco->exists($percorso) && ! in_array($firma($percorso), $provate, true)) {
            $daFare[] = [$percorso, true];
        }
    }

    foreach ($copertine as $percorso) {
        if (! $disco->exists($percorso) || in_array($firma($percorso), $provate, true)) {
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
        $provate[] = $firma($percorso);
    }

    if ($elaborate > 0) {
        @file_put_contents($registroProvate, json_encode(array_values(array_unique(array_slice($provate, -2000)))));
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
