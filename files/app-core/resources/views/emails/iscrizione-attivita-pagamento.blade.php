<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body style="margin:0; padding:24px 12px; background:#F3F5FA; font-family:Arial, Helvetica, sans-serif; color:#14213D;">
    <div style="max-width:520px; margin:0 auto; background:#ffffff; border-radius:20px; overflow:hidden;">
        <div style="background:#E4002B; color:#ffffff; padding:22px 28px; font-size:13px; font-weight:bold; letter-spacing:0.04em;">
            {{ $iscrizione->pagato ? 'Quota di un\'attività pagata' : 'Pagamento da verificare' }}
        </div>
        <div style="padding:28px;">
            @if($iscrizione->pagato)
                <p style="margin:0 0 18px; font-size:16px; line-height:1.55;">PayPal ha confermato il pagamento della quota: l'iscrizione risulta già pagata, non devi fare nulla.</p>
            @else
                <p style="margin:0 0 18px; font-size:16px; line-height:1.55;">
                    {{ $iscrizione->user->name }} dice di aver pagato la quota con <strong>{{ $metodo }}</strong>.
                    Controlla l'incasso {{ $metodo === 'bonifico' ? 'sul conto bancario' : 'sul conto PayPal' }}, poi segna l'iscrizione come pagata nel pannello.
                </p>
            @endif

            <table style="width:100%; border-collapse:collapse; font-size:15px;">
                <tr>
                    <td style="padding:9px 0; color:#4A5878; width:130px;">Attività</td>
                    <td style="padding:9px 0; font-weight:bold;">{{ $iscrizione->attivita->titolo }}</td>
                </tr>
                <tr>
                    <td style="padding:9px 0; color:#4A5878;">Chi</td>
                    <td style="padding:9px 0; font-weight:bold;">{{ $iscrizione->user->name }}</td>
                </tr>
                <tr>
                    <td style="padding:9px 0; color:#4A5878;">Email</td>
                    <td style="padding:9px 0;">{{ $iscrizione->user->email }}</td>
                </tr>
                @if($iscrizione->importo)
                <tr>
                    <td style="padding:9px 0; color:#4A5878;">Importo</td>
                    <td style="padding:9px 0;">€ {{ number_format((float) $iscrizione->importo, 2, ',', '.') }}</td>
                </tr>
                @endif
                @if($iscrizione->paypal_txn_id)
                <tr>
                    <td style="padding:9px 0; color:#4A5878;">Operazione PayPal</td>
                    <td style="padding:9px 0;">{{ $iscrizione->paypal_txn_id }}</td>
                </tr>
                @endif
            </table>

            <a href="{{ url('/admin/iscrizioni-attivita') }}" style="display:inline-block; margin-top:24px; background:#E4002B; color:#ffffff; text-decoration:none; padding:14px 26px; border-radius:999px; font-size:14px; font-weight:bold;">
                Apri le iscrizioni
            </a>
        </div>
    </div>
</body>
</html>
