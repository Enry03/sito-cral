<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body style="margin:0; padding:24px 12px; background:#F3F5FA; font-family:Arial, Helvetica, sans-serif; color:#14213D;">
    <div style="max-width:520px; margin:0 auto; background:#ffffff; border-radius:20px; overflow:hidden;">
        <div style="background:#E4002B; color:#ffffff; padding:22px 28px; font-size:13px; font-weight:bold; letter-spacing:0.04em;">
            Nuova iscrizione a un'attività
        </div>
        <div style="padding:28px;">
            <p style="margin:0 0 18px; font-size:16px; line-height:1.55;">Qualcuno si è iscritto a un'attività. L'iscrizione è automatica: non devi confermare nulla.</p>

            <table style="width:100%; border-collapse:collapse; font-size:15px;">
                <tr>
                    <td style="padding:9px 0; color:#4A5878; width:130px;">Attività</td>
                    <td style="padding:9px 0; font-weight:bold;">{{ $iscrizione->attivita->titolo }}</td>
                </tr>
                @if($iscrizione->attivita->periodoLabel())
                <tr>
                    <td style="padding:9px 0; color:#4A5878;">Quando</td>
                    <td style="padding:9px 0;">{{ $iscrizione->attivita->periodoLabel() }}</td>
                </tr>
                @endif
                <tr>
                    <td style="padding:9px 0; color:#4A5878;">Chi</td>
                    <td style="padding:9px 0; font-weight:bold;">{{ $iscrizione->user->name }}</td>
                </tr>
                <tr>
                    <td style="padding:9px 0; color:#4A5878;">Email</td>
                    <td style="padding:9px 0;">{{ $iscrizione->user->email }}</td>
                </tr>
                <tr>
                    <td style="padding:9px 0; color:#4A5878;">Iscritto come</td>
                    <td style="padding:9px 0;">{{ $iscrizione->user->isSocioCral() ? 'Socio CRAL'.($iscrizione->user->numero_tessera ? ' ('.$iscrizione->user->numero_tessera.')' : '') : 'Iscritto al sito (senza tessera)' }}</td>
                </tr>
                @if($iscrizione->importo)
                <tr>
                    <td style="padding:9px 0; color:#4A5878;">Quota</td>
                    <td style="padding:9px 0;">€ {{ number_format((float) $iscrizione->importo, 2, ',', '.') }}</td>
                </tr>
                @endif
                @foreach($iscrizione->risposteTesto() as $domanda => $risposta)
                <tr>
                    <td style="padding:9px 0; color:#4A5878; vertical-align:top;">{{ $domanda }}</td>
                    <td style="padding:9px 0;">{{ $risposta }}</td>
                </tr>
                @endforeach
            </table>

            <a href="{{ url('/admin/iscrizioni-attivita') }}" style="display:inline-block; margin-top:24px; background:#E4002B; color:#ffffff; text-decoration:none; padding:14px 26px; border-radius:999px; font-size:14px; font-weight:bold;">
                Vedi le iscrizioni
            </a>
            @if($iscrizione->importo)
                <p style="margin:18px 0 0; font-size:13px; color:#4A5878; line-height:1.55;">La persona sceglie ora come pagare la quota: riceverai un'altra email quando segnala un bonifico o paga con PayPal. I contanti li segni tu come pagati nel pannello.</p>
            @endif
        </div>
    </div>
</body>
</html>
