<?php

namespace App\Mail;

use App\Models\IscrizioneAttivita;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Esito della richiesta di iscrizione a un'attività, per chi l'ha mandata:
 * confermata o non accolta.
 */
class IscrizioneAttivitaEsito extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public IscrizioneAttivita $iscrizione)
    {
    }

    public function envelope(): Envelope
    {
        $titolo = $this->iscrizione->attivita->titolo;

        return new Envelope(
            subject: $this->iscrizione->confermata()
                ? 'Iscrizione confermata: '.$titolo
                : 'Richiesta di iscrizione non accolta: '.$titolo,
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.iscrizione-attivita-esito');
    }
}
