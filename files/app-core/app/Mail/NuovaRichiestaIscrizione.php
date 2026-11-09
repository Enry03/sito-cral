<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NuovaRichiestaIscrizione extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public User $socio)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->descrizioneRichiesta().' — '.$this->socio->name,
        );
    }

    public function descrizioneRichiesta(): string
    {
        return match (true) {
            $this->socio->ruolo === 'iscritto' && $this->socio->richiede_tessera => 'Richiesta di tessera socio CRAL',
            $this->socio->ruolo === 'iscritto' => 'Nuova iscrizione al sito CRAL',
            default => 'Nuova richiesta di iscrizione come socio CRAL',
        };
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.nuova-richiesta',
            with: ['descrizione' => $this->descrizioneRichiesta()],
        );
    }
}
