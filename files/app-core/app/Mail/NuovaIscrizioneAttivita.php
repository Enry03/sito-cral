<?php

namespace App\Mail;

use App\Models\IscrizioneAttivita;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Avviso informativo agli amministratori: qualcuno si è iscritto a un'attività.
 * L'iscrizione è automatica, non c'è nulla da confermare.
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
            subject: 'Nuova iscrizione a "'.$this->iscrizione->attivita->titolo.'" — '.$this->iscrizione->user->name,
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.iscrizione-attivita-nuova');
    }
}
