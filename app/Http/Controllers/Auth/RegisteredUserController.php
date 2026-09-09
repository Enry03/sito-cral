<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\NuovaRichiestaIscrizione;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'telefono' => ['required', 'string', 'max:255'],
            'data_nascita' => ['required', 'date', 'before:today'],
            'codice_fiscale' => ['required', 'string', 'max:16'],
            'indirizzo' => ['required', 'string', 'max:255'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'privacy' => ['accepted'],
        ], [
            'privacy.accepted' => 'Devi accettare l\'informativa sulla privacy per iscriverti.',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'telefono' => $request->telefono,
            'data_nascita' => $request->data_nascita,
            'codice_fiscale' => strtoupper($request->codice_fiscale),
            'indirizzo' => $request->indirizzo,
            'password' => Hash::make($request->password),
            'ruolo' => 'socio',
            'stato_iscrizione' => 'in_attesa',
            'data_iscrizione' => now(),
        ]);

        event(new Registered($user));

        try {
            Mail::to(config('mail.admin_address'))->send(new NuovaRichiestaIscrizione($user));
        } catch (\Throwable $e) {
            Log::warning('Invio email nuova richiesta iscrizione fallito: '.$e->getMessage());
        }

        Auth::login($user);

        return redirect(route('dashboard', absolute: false));
    }
}
