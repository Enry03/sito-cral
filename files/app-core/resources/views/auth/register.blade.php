@php
    $tipo = old('tipo', request('tipo') === 'iscritto' ? 'iscritto' : 'socio');
    $metodo = old('metodo_pagamento');
    $quota = \App\Models\User::quotaTessera();
    $paypalAttivo = \App\Models\User::paypalAttivo();
    $contatto = config('mail.admin_address');

    $metodi = [
        'contanti' => ['Paga in contanti in azienda', 'Porti la quota a un amministratore del CRAL.'],
        'email' => ['Contattaci per email', 'Ci scrivi e ti spieghiamo come procedere.'],
        'paypal' => ['Paga con PayPal', $paypalAttivo ? 'Dopo l\'invio della richiesta trovi il pulsante per pagare.' : 'Presto disponibile.'],
    ];
@endphp

<x-guest-layout title="Iscriviti — CRAL" :wide="true">
    <h1 class="text-4xl font-bold tracking-[-0.03em]">Iscriviti</h1>
    <p class="mt-3 leading-relaxed text-cral-navy-soft">
        Ogni richiesta viene esaminata da un amministratore: riceverai l'accesso non appena sarà approvata.
    </p>

    @if($errors->any())
        <p class="notice notice-error mt-6" role="alert">Controlla i campi evidenziati e riprova.</p>
    @endif

    <form method="POST" action="{{ route('register') }}" class="reg-form mt-8 space-y-7">
        @csrf

        {{-- Tipo di iscrizione: funziona anche senza JavaScript --}}
        <fieldset>
            <legend class="label">Che tipo di iscrizione vuoi?</legend>
            <div class="grid gap-3 sm:grid-cols-2">
                <label class="block">
                    <input type="radio" name="tipo" value="socio" class="peer sr-only" @checked($tipo === 'socio')>
                    <span class="choice-box">
                        <span class="flex items-start justify-between gap-3">
                            <span class="font-display text-lg font-bold tracking-tight">Socio CRAL</span>
                            <span class="choice-dot mt-1" aria-hidden="true"></span>
                        </span>
                        <span class="mt-1 block text-sm leading-relaxed text-cral-navy-soft">Tessera associativa: tutte le foto, iscrizione alle attività e prestito dei libri.</span>
                    </span>
                </label>
                <label class="block">
                    <input type="radio" name="tipo" value="iscritto" class="peer sr-only" @checked($tipo === 'iscritto')>
                    <span class="choice-box">
                        <span class="flex items-start justify-between gap-3">
                            <span class="font-display text-lg font-bold tracking-tight">Solo iscrizione al sito</span>
                            <span class="choice-dot mt-1" aria-hidden="true"></span>
                        </span>
                        <span class="mt-1 block text-sm leading-relaxed text-cral-navy-soft">Bastano email e password: puoi consultare il catalogo della libreria. Potrai chiedere la tessera in seguito.</span>
                    </span>
                </label>
            </div>
            <x-input-error :messages="$errors->get('tipo')" />
        </fieldset>

        <div class="grid gap-5 sm:grid-cols-2">
            <div class="sm:col-span-2">
                <label for="name" class="label">Nome e cognome</label>
                <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus autocomplete="name"
                    class="field {{ $errors->has('name') ? 'is-invalid' : '' }}">
                <x-input-error :messages="$errors->get('name')" />
            </div>
            <div class="sm:col-span-2">
                <label for="email" class="label">Email</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="username"
                    class="field {{ $errors->has('email') ? 'is-invalid' : '' }}">
                <x-input-error :messages="$errors->get('email')" />
            </div>
        </div>

        {{-- Solo per chi diventa socio: dati della tessera e modo di pagamento --}}
        <div class="collapse-rows only-socio !mt-0">
            <div>
                <div class="space-y-7 pt-7">
                    <div class="grid gap-5 sm:grid-cols-2">
                        <div>
                            <label for="telefono" class="label">Telefono</label>
                            <input id="telefono" type="tel" name="telefono" value="{{ old('telefono') }}" data-socio-required autocomplete="tel"
                                class="field {{ $errors->has('telefono') ? 'is-invalid' : '' }}">
                            <x-input-error :messages="$errors->get('telefono')" />
                        </div>
                        <div>
                            <label for="data_nascita" class="label">Data di nascita</label>
                            <input id="data_nascita" type="date" name="data_nascita" value="{{ old('data_nascita') }}" data-socio-required
                                class="field {{ $errors->has('data_nascita') ? 'is-invalid' : '' }}">
                            <x-input-error :messages="$errors->get('data_nascita')" />
                        </div>
                        <div>
                            <label for="codice_fiscale" class="label">Codice fiscale</label>
                            <input id="codice_fiscale" type="text" name="codice_fiscale" value="{{ old('codice_fiscale') }}" maxlength="16" data-socio-required
                                class="field uppercase {{ $errors->has('codice_fiscale') ? 'is-invalid' : '' }}">
                            <x-input-error :messages="$errors->get('codice_fiscale')" />
                        </div>
                        <div>
                            <label for="indirizzo" class="label">Indirizzo di residenza</label>
                            <input id="indirizzo" type="text" name="indirizzo" value="{{ old('indirizzo') }}" data-socio-required autocomplete="street-address"
                                class="field {{ $errors->has('indirizzo') ? 'is-invalid' : '' }}">
                            <x-input-error :messages="$errors->get('indirizzo')" />
                        </div>
                    </div>

                    <fieldset>
                        <legend class="label">Come vuoi pagare la quota di tessera?{{ $quota > 0 ? ' (€ '.number_format($quota, 2, ',', '.').')' : '' }}</legend>
                        <p class="help mb-3 mt-0">Un amministratore riceve la tua richiesta e approva la tessera quando la quota è stata pagata.</p>
                        <div class="grid gap-3 sm:grid-cols-3">
                            @foreach($metodi as $valore => [$titolo, $descrizione])
                                @php $disattivo = $valore === 'paypal' && ! $paypalAttivo; @endphp
                                <label class="block">
                                    <input type="radio" name="metodo_pagamento" value="{{ $valore }}" class="peer sr-only" data-socio-required
                                        @checked($metodo === $valore) @disabled($disattivo)>
                                    <span class="choice-box">
                                        <span class="flex items-start justify-between gap-3">
                                            <span class="font-semibold leading-snug">{{ $titolo }}</span>
                                            <span class="choice-dot mt-0.5" aria-hidden="true"></span>
                                        </span>
                                        <span class="mt-1 block text-sm leading-relaxed text-cral-navy-soft">{{ $descrizione }}</span>
                                    </span>
                                </label>
                            @endforeach
                        </div>
                        <x-input-error :messages="$errors->get('metodo_pagamento')" />
                    </fieldset>
                </div>
            </div>
        </div>

        <div class="grid gap-5 sm:grid-cols-2">
            <div>
                <label for="password" class="label">Password</label>
                <x-password-input name="password" required autocomplete="new-password" />
                <p class="help">Almeno 10 caratteri, con maiuscole, minuscole e numeri.</p>
                <x-input-error :messages="$errors->get('password')" />
            </div>
            <div>
                <label for="password_confirmation" class="label">Conferma password</label>
                <x-password-input name="password_confirmation" required autocomplete="new-password" />
                <x-input-error :messages="$errors->get('password_confirmation')" />
            </div>
        </div>

        <div>
            <label class="flex items-start gap-3 text-sm leading-relaxed">
                <input type="checkbox" name="privacy" value="1" required @checked(old('privacy'))
                    class="mt-0.5 h-5 w-5 shrink-0 rounded-md border-cral-navy/30 text-cral-red focus:ring-cral-red/30">
                <span class="text-cral-navy-soft">
                    Ho letto e accetto l'<a href="{{ route('privacy') }}" target="_blank" rel="noopener" class="font-semibold text-cral-navy underline decoration-cral-red/40 underline-offset-4 hover:text-cral-red">informativa sulla privacy</a>
                    e acconsento al trattamento dei miei dati personali per la gestione dell'iscrizione al CRAL.
                </span>
            </label>
            <x-input-error :messages="$errors->get('privacy')" />
        </div>

        <div class="flex flex-wrap items-center justify-between gap-4 pt-1">
            <a class="text-sm font-semibold text-cral-navy-soft transition hover:text-cral-red" href="{{ route('login') }}">Hai già un account? Accedi</a>
            <button type="submit" class="btn btn-primary w-full sm:w-auto">
                Invia richiesta
                <svg class="arrow h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 01.02-1.06L11.17 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z" clip-rule="evenodd"/></svg>
            </button>
        </div>
    </form>

    {{-- I campi del socio diventano obbligatori solo se si sceglie "Socio CRAL"
         (senza JavaScript li controlla comunque il server). --}}
    <script>
        (function () {
            var form = document.querySelector('.reg-form');
            if (!form) { return; }

            function sync() {
                var scelto = form.querySelector('input[name="tipo"]:checked');
                var socio = !scelto || scelto.value === 'socio';
                form.querySelectorAll('[data-socio-required]').forEach(function (el) { el.required = socio; });
            }

            form.addEventListener('change', sync);
            sync();
        })();
    </script>
</x-guest-layout>
