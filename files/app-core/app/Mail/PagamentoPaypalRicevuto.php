<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Avviso agli amministratori: PayPal ha confermato l'incasso della quota di
 * tessera. Resta da approvare la richiesta dal pannello.
 */
class PagamentoPaypalRicevuto extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public User $richiedente)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Pagamento PayPal ricevuto — '.$this->richiedente->name);
    }

    public function content(): Content
    {
        return new Content(view: 'emails.pagamento-paypal-ricevuto');
    }
}
