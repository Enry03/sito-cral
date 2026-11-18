<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body style="margin:0; padding:24px 12px; background:#F3F5FA; font-family:Arial, Helvetica, sans-serif; color:#14213D;">
    <div style="max-width:520px; margin:0 auto; background:#ffffff; border-radius:20px; overflow:hidden;">
        <div style="background:{{ $iscrizione->confermata() ? '#E4002B' : '#14213D' }}; color:#ffffff; padding:22px 28px; font-size:13px; font-weight:bold; letter-spacing:0.04em;">
            {{ $iscrizione->confermata() ? 'Iscrizione confermata' : 'Richiesta non accolta' }}
        </div>
        <div style="padding:28px;">
            <p style="margin:0 0 14px; font-size:16px; line-height:1.55;">Ciao {{ $iscrizione->user->name }},</p>

            @if($iscrizione->confermata())
                <p style="margin:0 0 14px; font-size:16px; line-height:1.55;">
                    la tua iscrizione a <strong>{{ $iscrizione->attivita->titolo }}</strong> è stata <strong>confermata</strong>.
                    @if($iscrizione->attivita->periodoLabel())
                        Ti aspettiamo: {{ $iscrizione->attivita->periodoLabel() }}@if($iscrizione->attivita->luogo), {{ $iscrizione->attivita->luogo }}@endif.
                    @endif
                </p>

                @if($iscrizione->richiedePagamento())
                    <p style="margin:0 0 14px; font-size:15px; color:#4A5878; line-height:1.6;">
                        La quota di partecipazione è di <strong>€ {{ number_format((float) $iscrizione->importo, 2, ',', '.') }}</strong>.
                        Puoi pagarla dalla pagina dell'attività oppure accordarti con gli amministratori del CRAL.
                    </p>
                @endif

                <a href="{{ route('attivita.show', $iscrizione->attivita->slug) }}" style="display:inline-block; margin-top:10px; background:#E4002B; color:#ffffff; text-decoration:none; padding:14px 26px; border-radius:999px; font-size:14px; font-weight:bold;">
                    Vai all'attività
                </a>
            @else
                <p style="margin:0 0 14px; font-size:16px; line-height:1.55;">
                    purtroppo la tua richiesta di iscrizione a <strong>{{ $iscrizione->attivita->titolo }}</strong> non è stata accolta.
                </p>
                <p style="margin:0; font-size:15px; color:#4A5878; line-height:1.6;">
                    Per saperne di più scrivi a <a href="mailto:{{ config('mail.admin_address') }}" style="color:#14213D;">{{ config('mail.admin_address') }}</a>.
                </p>
            @endif
        </div>
    </div>
</body>
</html>
