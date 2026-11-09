<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body style="margin:0; padding:24px 12px; background:#F3F5FA; font-family:Arial, Helvetica, sans-serif; color:#14213D;">
    <div style="max-width:560px; margin:0 auto; background:#ffffff; border-radius:20px; overflow:hidden;">
        <div style="background:#E4002B; color:#ffffff; padding:22px 28px; font-size:13px; font-weight:bold; letter-spacing:0.04em;">
            CRAL Selex ES - Chieti
        </div>

        <div style="padding:30px 28px 8px;">
            <h1 style="margin:0 0 12px; font-size:26px; line-height:1.15; letter-spacing:-0.02em;">Congratulazioni, {{ $socio->name }}!</h1>
            <p style="margin:0 0 12px; font-size:16px; line-height:1.6;">
                La tua richiesta è stata approvata: da oggi sei ufficialmente <strong>socio del CRAL</strong>.
                Questa è la tua tessera:
            </p>
        </div>

        <div style="padding:8px 28px; text-align:center;">
            @if($immagineTessera)
                <img src="{{ $message->embedData($immagineTessera, 'tessera-cral.jpg', 'image/jpeg') }}" alt="La tua tessera CRAL, intestata a {{ $socio->name }}" width="504" style="width:100%; max-width:504px; height:auto; border:0; border-radius:12px;">
            @else
                <img src="{{ $message->embed(\App\Services\TesseraCard::percorso()) }}" alt="Tessera CRAL" width="504" style="width:100%; max-width:504px; height:auto; border:0; border-radius:12px;">
                <p style="margin:12px 0 0; font-size:16px;">
                    Intestata a <strong>{{ $socio->name }}</strong> — N. <strong>{{ \App\Services\TesseraCard::numeroBreve($socio->numero_tessera) }}</strong>
                </p>
            @endif
        </div>

        <div style="padding:8px 28px 32px;">
            <p style="margin:14px 0; font-size:16px; line-height:1.6;">
                Il tuo numero di tessera è <strong>{{ $socio->numero_tessera }}</strong>. Conservala: è personale e non va condivisa.
            </p>
            <p style="margin:14px 0 6px; font-size:16px; line-height:1.6;">Da adesso puoi:</p>
            <ul style="margin:0 0 22px; padding-left:20px; font-size:16px; line-height:1.8;">
                <li>vedere tutte le foto degli album;</li>
                <li>iscriverti alle attività e agli eventi del CRAL;</li>
                <li>consultare la libreria e prendere in prestito i libri.</li>
            </ul>

            <a href="{{ url('/login') }}" style="display:inline-block; background:#E4002B; color:#ffffff; text-decoration:none; padding:14px 28px; border-radius:999px; font-size:15px; font-weight:bold;">
                Accedi alla tua area
            </a>

            <p style="margin:26px 0 0; font-size:13px; color:#4A5878; line-height:1.6;">
                Più insieme, più valore. A presto!<br>
                Per qualsiasi domanda scrivici a <a href="mailto:{{ config('mail.admin_address') }}" style="color:#14213D;">{{ config('mail.admin_address') }}</a>.
            </p>
        </div>
    </div>
</body>
</html>
