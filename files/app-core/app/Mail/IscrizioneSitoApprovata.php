<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Conferma per chi si è iscritto al solo sito e viene approvato.
 */
class IscrizioneSitoApprovata extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public User $utente)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Il tuo account CRAL è attivo');
    }

    public function content(): Content
    {
        return new Content(view: 'emails.iscrizione-sito-approvata');
    }
}
