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
use Illuminate\Validation\Rule;
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
        // "socio" = richiesta di tessera CRAL (servono i dati anagrafici);
        // "iscritto" = solo account del sito (basta email e password).
        $request->validate([
            'tipo' => ['required', 'in:socio,iscritto'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'telefono' => ['required_if:tipo,socio', 'nullable', 'string', 'max:255'],
            'data_nascita' => ['required_if:tipo,socio', 'nullable', 'date', 'before:today'],
            'codice_fiscale' => ['required_if:tipo,socio', 'nullable', 'string', 'max:16'],
            'indirizzo' => ['required_if:tipo,socio', 'nullable', 'string', 'max:255'],
            'metodo_pagamento' => ['required_if:tipo,socio', 'nullable', Rule::in(User::metodiAmmessi())],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'privacy' => ['accepted'],
        ], [
            'privacy.accepted' => "Devi accettare l'informativa sulla privacy per iscriverti.",
            'metodo_pagamento.required_if' => 'Scegli come vuoi pagare la quota di tessera.',
            'metodo_pagamento.in' => 'Questo modo di pagamento non è disponibile: scegline un altro.',
            'tipo.required' => 'Scegli se vuoi diventare socio CRAL o iscriverti solo al sito.',
        ]);

        $isSocio = $request->tipo === 'socio';

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'telefono' => $request->telefono,
            'data_nascita' => $isSocio ? $request->data_nascita : null,
            'codice_fiscale' => $isSocio ? strtoupper((string) $request->codice_fiscale) : null,
            'indirizzo' => $isSocio ? $request->indirizzo : null,
            'metodo_pagamento' => $isSocio ? $request->metodo_pagamento : null,
            'password' => Hash::make($request->password),
            'ruolo' => $request->tipo,
            'stato_iscrizione' => 'in_attesa',
            'data_iscrizione' => now(),
            'richiesta_il' => now(),
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
