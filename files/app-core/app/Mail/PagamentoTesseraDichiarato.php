<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Avviso agli amministratori: chi chiede la tessera dice di aver pagato (con
 * PayPal o con bonifico). L'incasso va verificato prima di approvare.
 */
class PagamentoTesseraDichiarato extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public User $richiedente)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Pagamento tessera con '.$this->nomeMetodo().' — '.$this->richiedente->name);
    }

    public function nomeMetodo(): string
    {
        return $this->richiedente->metodo_pagamento === 'bonifico' ? 'bonifico' : 'PayPal';
    }

    public function content(): Content
    {
        return new Content(view: 'emails.pagamento-dichiarato', with: [
            'bonifico' => $this->richiedente->metodo_pagamento === 'bonifico',
            'metodo' => $this->nomeMetodo(),
        ]);
    }
}
