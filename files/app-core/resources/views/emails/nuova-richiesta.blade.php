<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body style="margin:0; padding:24px 12px; background:#F3F5FA; font-family:Arial, Helvetica, sans-serif; color:#14213D;">
    <div style="max-width:520px; margin:0 auto; background:#ffffff; border-radius:20px; overflow:hidden;">
        <div style="background:#E4002B; color:#ffffff; padding:22px 28px; font-size:13px; font-weight:bold; letter-spacing:0.04em;">
            {{ $descrizione }}
        </div>
        <div style="padding:28px;">
            <p style="margin:0 0 18px; font-size:16px; line-height:1.55;">È arrivata una richiesta in attesa di approvazione.</p>

            <table style="width:100%; border-collapse:collapse; font-size:15px;">
                <tr>
                    <td style="padding:9px 0; color:#4A5878; width:150px;">Nome e cognome</td>
                    <td style="padding:9px 0; font-weight:bold;">{{ $socio->name }}</td>
                </tr>
                <tr>
                    <td style="padding:9px 0; color:#4A5878;">Email</td>
                    <td style="padding:9px 0;">{{ $socio->email }}</td>
                </tr>
                @if($socio->codice_fiscale || $socio->telefono)
                <tr>
                    <td style="padding:9px 0; color:#4A5878;">Telefono</td>
                    <td style="padding:9px 0;">{{ $socio->telefono }}</td>
                </tr>
                <tr>
                    <td style="padding:9px 0; color:#4A5878;">Data di nascita</td>
                    <td style="padding:9px 0;">{{ optional($socio->data_nascita)->format('d/m/Y') }}</td>
                </tr>
                <tr>
                    <td style="padding:9px 0; color:#4A5878;">Codice fiscale</td>
                    <td style="padding:9px 0;">{{ $socio->codice_fiscale }}</td>
                </tr>
                <tr>
                    <td style="padding:9px 0; color:#4A5878;">Indirizzo</td>
                    <td style="padding:9px 0;">{{ $socio->indirizzo }}</td>
                </tr>
                @endif
                @if($socio->metodo_pagamento)
                <tr>
                    <td style="padding:9px 0; color:#4A5878;">Pagamento scelto</td>
                    <td style="padding:9px 0; font-weight:bold; color:#E4002B;">{{ $socio->metodoPagamentoLabel() }}</td>
                </tr>
                @endif
            </table>

            @if($socio->metodo_pagamento === 'paypal')
                <p style="margin:16px 0 0; font-size:14px; color:#4A5878; line-height:1.6;">
                    Ha scelto PayPal: appena paga ricevi una seconda email con la conferma dell'incasso.
                </p>
            @elseif($socio->metodo_pagamento === 'bonifico')
                <p style="margin:16px 0 0; font-size:14px; color:#4A5878; line-height:1.6;">
                    Ha scelto il bonifico: vede i dati del conto e, quando lo ha fatto, ti avvisa con un'altra email. Controlla l'accredito prima di approvare.
                    Causale attesa: <strong>{{ app(\App\Services\DatiAzienda::class)->causale($socio->name) }}</strong>.
                </p>
            @elseif($socio->metodo_pagamento === 'contanti')
                <p style="margin:16px 0 0; font-size:14px; color:#4A5878; line-height:1.6;">
                    Pagherà in contanti in azienda: spunta "Quota incassata" quando approvi la richiesta.
                </p>
            @endif

            <a href="{{ url('/admin/richieste') }}" style="display:inline-block; margin-top:24px; background:#E4002B; color:#ffffff; text-decoration:none; padding:14px 26px; border-radius:999px; font-size:14px; font-weight:bold;">
                Esamina la richiesta
            </a>
        </div>
    </div>
</body>
</html>
