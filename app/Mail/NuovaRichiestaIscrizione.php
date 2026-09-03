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
            subject: 'Nuova richiesta di iscrizione al CRAL — '.$this->socio->name,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.nuova-richiesta',
        );
    }
}
