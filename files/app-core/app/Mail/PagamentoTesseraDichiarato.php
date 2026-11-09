<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Avviso agli amministratori: chi chiede la tessera è tornato sul sito dopo
 * aver pagato con PayPal. L'incasso va verificato su PayPal prima di approvare.
 */
class PagamentoTesseraDichiarato extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public User $richiedente)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Pagamento tessera con PayPal — '.$this->richiedente->name);
    }

    public function content(): Content
    {
        return new Content(view: 'emails.pagamento-dichiarato');
    }
}
