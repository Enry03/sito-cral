<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="utf-8">
</head>
<body style="font-family: Arial, sans-serif; background:#F5F2EA; padding: 24px; color:#14213D;">
    <div style="max-width:480px; margin:0 auto; background:#ffffff; border:2px solid #14213D;">
        <div style="background:#14213D; color:#ffffff; padding:16px 24px; font-size:12px; font-weight:bold; letter-spacing:0.15em; text-transform:uppercase;">
            Nuova richiesta di iscrizione
        </div>
        <div style="padding:24px;">
            <p style="margin:0 0 16px;">È arrivata una nuova richiesta di iscrizione al CRAL, in attesa di approvazione.</p>

            <table style="width:100%; border-collapse:collapse; font-size:14px;">
                <tr>
                    <td style="padding:8px 0; color:#4A5878; width:140px;">Nome e cognome</td>
                    <td style="padding:8px 0; font-weight:bold;">{{ $socio->name }}</td>
                </tr>
                <tr>
                    <td style="padding:8px 0; color:#4A5878;">Email</td>
                    <td style="padding:8px 0;">{{ $socio->email }}</td>
                </tr>
                <tr>
                    <td style="padding:8px 0; color:#4A5878;">Telefono</td>
                    <td style="padding:8px 0;">{{ $socio->telefono }}</td>
                </tr>
                <tr>
                    <td style="padding:8px 0; color:#4A5878;">Data nascita</td>
                    <td style="padding:8px 0;">{{ optional($socio->data_nascita)->format('d/m/Y') }}</td>
                </tr>
                <tr>
                    <td style="padding:8px 0; color:#4A5878;">Codice fiscale</td>
                    <td style="padding:8px 0;">{{ $socio->codice_fiscale }}</td>
                </tr>
                <tr>
                    <td style="padding:8px 0; color:#4A5878;">Indirizzo</td>
                    <td style="padding:8px 0;">{{ $socio->indirizzo }}</td>
                </tr>
            </table>

            <a href="{{ url('/admin/users/'.$socio->id.'/edit') }}" style="display:inline-block; margin-top:24px; background:#E4002B; color:#ffffff; text-decoration:none; padding:12px 20px; font-size:13px; font-weight:bold; letter-spacing:0.1em; text-transform:uppercase;">
                Esamina la richiesta
            </a>
        </div>
    </div>
</body>
</html>
