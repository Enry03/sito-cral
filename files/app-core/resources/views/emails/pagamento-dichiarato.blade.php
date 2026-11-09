<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body style="margin:0; padding:24px 12px; background:#F3F5FA; font-family:Arial, Helvetica, sans-serif; color:#14213D;">
    <div style="max-width:520px; margin:0 auto; background:#ffffff; border-radius:20px; overflow:hidden;">
        <div style="background:#14213D; color:#ffffff; padding:22px 28px; font-size:13px; font-weight:bold; letter-spacing:0.04em;">
            Pagamento tessera con {{ $metodo }} da verificare
        </div>
        <div style="padding:28px;">
            @if($bonifico)
                <p style="margin:0 0 14px; font-size:16px; line-height:1.55;">
                    <strong>{{ $richiedente->name }}</strong> ({{ $richiedente->email }}) segnala di aver fatto il bonifico per la quota di tessera.
                </p>
                <p style="margin:0 0 20px; font-size:14px; color:#4A5878; line-height:1.6;">
                    Controlla sul conto che l'accredito sia arrivato e poi approva la richiesta.
                    Nella causale dovrebbe esserci il suo nome: <strong>{{ app(\App\Services\DatiAzienda::class)->causale($richiedente->name) }}</strong>.
                </p>
            @else
                <p style="margin:0 0 14px; font-size:16px; line-height:1.55;">
                    <strong>{{ $richiedente->name }}</strong> ({{ $richiedente->email }}) è tornato sul sito dopo aver pagato la quota di tessera con PayPal.
                </p>
                <p style="margin:0 0 20px; font-size:14px; color:#4A5878; line-height:1.6;">
                    La conferma automatica di PayPal non è ancora arrivata: controlla il pagamento nel tuo conto PayPal e poi approva la richiesta.
                    Riferimento del pagamento: <strong>tessera-{{ $richiedente->id }}</strong>.
                </p>
            @endif

            <a href="{{ url('/admin/richieste') }}" style="display:inline-block; background:#E4002B; color:#ffffff; text-decoration:none; padding:14px 26px; border-radius:999px; font-size:14px; font-weight:bold;">
                Vai alle richieste
            </a>
        </div>
    </div>
</body>
</html>
