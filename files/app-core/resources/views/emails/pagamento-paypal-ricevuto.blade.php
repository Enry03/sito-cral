<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body style="margin:0; padding:24px 12px; background:#F3F5FA; font-family:Arial, Helvetica, sans-serif; color:#14213D;">
    <div style="max-width:520px; margin:0 auto; background:#ffffff; border-radius:20px; overflow:hidden;">
        <div style="background:#E4002B; color:#ffffff; padding:22px 28px; font-size:13px; font-weight:bold; letter-spacing:0.04em;">
            Pagamento PayPal ricevuto
        </div>
        <div style="padding:28px;">
            <p style="margin:0 0 14px; font-size:16px; line-height:1.55;">
                <strong>{{ $richiedente->name }}</strong> ({{ $richiedente->email }}) ha pagato la quota di tessera con PayPal.
            </p>
            <p style="margin:0 0 20px; font-size:14px; color:#4A5878; line-height:1.6;">
                PayPal ha confermato l'incasso (operazione <strong>{{ $richiedente->paypal_txn_id }}</strong>).
                La richiesta di iscrizione è già nel pannello: ti resta da approvarla perché parta l'email con la tessera.
            </p>

            <a href="{{ url('/admin/richieste') }}" style="display:inline-block; background:#E4002B; color:#ffffff; text-decoration:none; padding:14px 26px; border-radius:999px; font-size:14px; font-weight:bold;">
                Vai alle richieste
            </a>
        </div>
    </div>
</body>
</html>
