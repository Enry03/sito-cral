<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body style="margin:0; padding:24px 12px; background:#F3F5FA; font-family:Arial, Helvetica, sans-serif; color:#14213D;">
    <div style="max-width:520px; margin:0 auto; background:#ffffff; border-radius:20px; overflow:hidden;">
        <div style="background:#E4002B; color:#ffffff; padding:22px 28px; font-size:13px; font-weight:bold; letter-spacing:0.04em;">
            CRAL Selex ES - Chieti
        </div>
        <div style="padding:30px 28px 32px;">
            <h1 style="margin:0 0 12px; font-size:24px; line-height:1.2; letter-spacing:-0.02em;">Benvenuto, {{ $utente->name }}!</h1>
            <p style="margin:0 0 12px; font-size:16px; line-height:1.6;">
                La tua iscrizione al sito del CRAL è stata approvata: ora puoi accedere con la tua email e la password scelta
                e consultare il catalogo della libreria.
            </p>
            <p style="margin:0 0 22px; font-size:16px; line-height:1.6;">
                Vuoi vedere tutte le foto, iscriverti alle attività e prendere in prestito i libri? Dalla tua area puoi
                richiedere in qualsiasi momento la <strong>tessera di socio CRAL</strong>.
            </p>

            <a href="{{ url('/login') }}" style="display:inline-block; background:#E4002B; color:#ffffff; text-decoration:none; padding:14px 28px; border-radius:999px; font-size:15px; font-weight:bold;">
                Accedi
            </a>

            <p style="margin:26px 0 0; font-size:13px; color:#4A5878; line-height:1.6;">
                Per qualsiasi domanda scrivici a <a href="mailto:{{ config('mail.admin_address') }}" style="color:#14213D;">{{ config('mail.admin_address') }}</a>.
            </p>
        </div>
    </div>
</body>
</html>
