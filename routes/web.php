<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\AttivitaController;
use App\Http\Controllers\DocumentoController;
use App\Http\Controllers\FotoController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TesseraVerificaController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/attivita', [AttivitaController::class, 'index'])->name('attivita.index');
Route::get('/attivita/{slug}', [AttivitaController::class, 'show'])->name('attivita.show');

Route::get('/foto', [FotoController::class, 'index'])->name('foto.index');

Route::get('/tessera/{numeroTessera}', [TesseraVerificaController::class, 'show'])->name('tessera.verifica');

Route::get('/attesa-approvazione', function () {
    return view('attesa-approvazione');
})->middleware('auth')->name('attesa-approvazione');

Route::middleware(['auth'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth', 'socio.attivo'])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    Route::get('/account', [AccountController::class, 'show'])->name('account.show');

    Route::get('/documenti', [DocumentoController::class, 'index'])->name('documenti.index');
    Route::get('/documenti/{documento}/scarica', [DocumentoController::class, 'download'])->name('documenti.download');
});

require __DIR__.'/auth.php';
