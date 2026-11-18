<?php

namespace App\Mail;

use App\Models\IscrizioneAttivita;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Avviso agli amministratori: qualcuno ha chiesto di partecipare a un'attività
 * e la richiesta aspetta la loro conferma.
 */
class NuovaIscrizioneAttivita extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public IscrizioneAttivita $iscrizione)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Richiesta di iscrizione a "'.$this->iscrizione->attivita->titolo.'" — '.$this->iscrizione->user->name,
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.iscrizione-attivita-nuova');
    }
}
