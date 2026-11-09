<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\AttivitaController;
use App\Http\Controllers\DocumentoController;
use App\Http\Controllers\FotoController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LibreriaController;
use App\Http\Controllers\PagamentoTesseraController;
use App\Http\Controllers\PaypalIpnController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RichiestaSocioController;
use App\Http\Controllers\TesseraVerificaController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Livelli di accesso
|--------------------------------------------------------------------------
| ospite          non ha fatto il login
| iscritto        account approvato (middleware "socio.attivo"), senza tessera
| socio CRAL      account "socio" approvato con tessera (middleware "socio.tessera")
*/

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/attivita', [AttivitaController::class, 'index'])->name('attivita.index');
Route::get('/attivita/{slug}', [AttivitaController::class, 'show'])->name('attivita.show');

Route::get('/foto', [FotoController::class, 'index'])->name('foto.index');
Route::get('/foto/album/{album}', [FotoController::class, 'album'])->name('foto.album');

// Notifica automatica di PayPal: arriva da PayPal, non da un utente (niente login né CSRF).
Route::post('/paypal/ipn', PaypalIpnController::class)->middleware('throttle:60,1')->name('paypal.ipn');

Route::get('/tessera/{numeroTessera}', [TesseraVerificaController::class, 'show'])->name('tessera.verifica');

Route::get('/privacy-policy', function () {
    return view('privacy');
})->name('privacy');

Route::get('/attesa-approvazione', function () {
    // Chi ha già l'accesso non ha nulla da aspettare.
    return auth()->user()->haAccessoSito()
        ? redirect()->route('dashboard')
        : view('attesa-approvazione');
})->middleware('auth')->name('attesa-approvazione');

Route::middleware(['auth'])->group(function () {
    // Ritorno da PayPal dopo il pagamento della quota di tessera.
    Route::get('/richiesta-tessera/pagamento', [PagamentoTesseraController::class, 'ritorno'])
        ->middleware('throttle:10,1')
        ->name('tessera.pagamento.ritorno');

    // Chi ha una richiesta di tessera aperta può cambiare il modo di pagamento.
    Route::post('/richiesta-tessera/metodo', [PagamentoTesseraController::class, 'metodo'])
        ->middleware('throttle:10,1')
        ->name('tessera.metodo');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Utenti con account approvato (iscritti e soci).
Route::middleware(['auth', 'socio.attivo'])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    // Catalogo della libreria: visibile a iscritti e soci, non agli ospiti.
    Route::prefix('libreria')->name('libreria.')->group(function () {
        Route::get('/', [LibreriaController::class, 'index'])->name('index');
        Route::get('/prestiti', [LibreriaController::class, 'mieiPrestiti'])->middleware('socio.tessera')->name('prestiti');
        Route::get('/{libro}', [LibreriaController::class, 'show'])->whereNumber('libro')->name('show');
        Route::post('/{libro}/prestito', [LibreriaController::class, 'presta'])
            ->middleware(['socio.tessera', 'throttle:10,1'])
            ->whereNumber('libro')
            ->name('prestito');
    });

    // Un iscritto può chiedere la tessera di socio.
    Route::get('/diventa-socio', [RichiestaSocioController::class, 'show'])->name('socio.richiesta');
    Route::post('/diventa-socio', [RichiestaSocioController::class, 'store'])
        ->middleware('throttle:6,1')
        ->name('socio.richiesta.invia');
});

// Soci CRAL con tessera.
Route::middleware(['auth', 'socio.attivo', 'socio.tessera'])->group(function () {
    Route::get('/account', [AccountController::class, 'show'])->name('account.show');

    Route::get('/documenti', [DocumentoController::class, 'index'])->name('documenti.index');
    Route::get('/documenti/{documento}/scarica', [DocumentoController::class, 'download'])->name('documenti.download');

    Route::post('/attivita/{slug}/partecipa', [AttivitaController::class, 'partecipa'])
        ->middleware('throttle:20,1')
        ->name('attivita.partecipa');
    Route::delete('/attivita/{slug}/partecipa', [AttivitaController::class, 'annullaPartecipazione'])
        ->middleware('throttle:20,1')
        ->name('attivita.annulla');
});

require __DIR__.'/auth.php';
