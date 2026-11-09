<?php

namespace App\Services;

use RuntimeException;

/**
 * Un'operazione (prestito, iscrizione a un'attività) non è consentita: il
 * messaggio è pensato per essere mostrato direttamente all'utente.
 */
class OperazioneNonConsentita extends RuntimeException
{
}
