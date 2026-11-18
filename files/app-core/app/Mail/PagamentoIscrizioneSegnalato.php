<?php

namespace App\Mail;

use App\Models\IscrizioneAttivita;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Avviso agli amministratori sul pagamento della quota di un'attività:
 * - la persona dice di aver fatto il bonifico (o di essere tornata da PayPal): va
 *   verificato l'incasso;
 * - PayPal ha confermato il pagamento con la notifica automatica: l'iscrizione
 *   risulta già pagata.
 */
class PagamentoIscrizioneSegnalato extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public IscrizioneAttivita $iscrizione)
    {
    }

    public function envelope(): Envelope
    {
        $oggetto = $this->iscrizione->pagato
            ? 'Quota ricevuta con PayPal'
            : 'Pagamento con '.$this->nomeMetodo().' da verificare';

        return new Envelope(subject: $oggetto.' — '.$this->iscrizione->attivita->titolo.' ('.$this->iscrizione->user->name.')');
    }

    public function nomeMetodo(): string
    {
        return $this->iscrizione->metodo_pagamento === 'bonifico' ? 'bonifico' : 'PayPal';
    }

    public function content(): Content
    {
        return new Content(view: 'emails.iscrizione-attivita-pagamento', with: [
            'metodo' => $this->nomeMetodo(),
        ]);
    }
}
