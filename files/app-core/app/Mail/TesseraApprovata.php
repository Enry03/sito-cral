<?php

namespace App\Mail;

use App\Models\User;
use App\Services\TesseraCard;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Email di benvenuto per il nuovo socio, con l'immagine della sua tessera.
 */
class TesseraApprovata extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public User $socio)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Congratulazioni, sei socio CRAL! Ecco la tua tessera');
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.tessera-approvata',
            with: [
                // Null se il server non può disegnare il nome sull'immagine:
                // l'email mostra allora la grafica con il nome scritto sotto.
                'immagineTessera' => app(TesseraCard::class)->jpeg($this->socio->name, $this->socio->numero_tessera),
            ],
        );
    }
}
