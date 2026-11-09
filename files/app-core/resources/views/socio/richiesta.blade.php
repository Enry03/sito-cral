@php
    $metodo = old('metodo_pagamento', $utente->metodo_pagamento);
    $quota = \App\Models\User::quotaTessera();
@endphp

<x-public-layout title="Diventa socio CRAL">
    <x-page-hero kicker="La mia area" title="Diventa socio CRAL"
        subtitle="Con la tessera accedi alle convenzioni, ti iscrivi alle attività e prendi in prestito i libri." tone="navy" />

    <div class="container-x -mt-2 max-w-3xl pb-24 sm:pb-32">
        @if(session('ok_pagamento'))
            <p class="notice mb-6" role="status">{{ session('ok_pagamento') }}</p>
        @endif

        @if($utente->richiede_tessera)
            <section class="surface p-6 sm:p-8" data-reveal>
                <span class="chip chip-soft">Richiesta inviata</span>
                <h2 class="mt-4 text-2xl font-bold tracking-tight sm:text-3xl">Stiamo esaminando la tua richiesta</h2>
                <p class="mt-3 leading-relaxed text-cral-navy-soft">
                    Un amministratore approverà la tua tessera a breve e ti scriverà una email con la tessera intestata a te.
                    Quando succederà potrai usare le convenzioni, iscriverti alle attività e prendere in prestito i libri della libreria.
                </p>
                <x-richiesta-stato :utente="$utente" class="mt-8" />
            </section>

            <x-tessera-pagamento :utente="$utente" class="mt-6" data-reveal />

            <a href="{{ route('dashboard') }}" class="mt-8 inline-flex items-center gap-2 text-sm font-semibold text-cral-navy-soft transition hover:text-cral-red">&larr; Torna alla tua area</a>
        @else
            <section class="surface p-6 sm:p-8" data-reveal>
                <h2 class="text-2xl font-bold tracking-tight">Completa i tuoi dati</h2>
                <p class="mt-2 leading-relaxed text-cral-navy-soft">
                    La tessera viene intestata al nome del tuo account ({{ $utente->name }}) e ti arriva via email quando un amministratore approva la richiesta.
                </p>

                <form method="POST" action="{{ route('socio.richiesta.invia') }}" class="mt-8 space-y-7">
                    @csrf

                    <div class="grid gap-5 sm:grid-cols-2">
                        <div>
                            <x-input-label for="telefono" value="Telefono" />
                            <x-text-input id="telefono" type="tel" name="telefono" :value="old('telefono', $utente->telefono)" required autocomplete="tel" />
                            <x-input-error :messages="$errors->get('telefono')" />
                        </div>
                        <div>
                            <x-input-label for="data_nascita" value="Data di nascita" />
                            <x-text-input id="data_nascita" type="date" name="data_nascita" :value="old('data_nascita')" required />
                            <x-input-error :messages="$errors->get('data_nascita')" />
                        </div>
                        <div>
                            <x-input-label for="codice_fiscale" value="Codice fiscale" />
                            <x-text-input id="codice_fiscale" type="text" name="codice_fiscale" :value="old('codice_fiscale')" maxlength="16" required class="uppercase" />
                            <x-input-error :messages="$errors->get('codice_fiscale')" />
                        </div>
                        <div>
                            <x-input-label for="indirizzo" value="Indirizzo di residenza" />
                            <x-text-input id="indirizzo" type="text" name="indirizzo" :value="old('indirizzo')" required autocomplete="street-address" />
                            <x-input-error :messages="$errors->get('indirizzo')" />
                        </div>
                    </div>

                    <fieldset>
                        <legend class="label">Come vuoi pagare la quota di tessera?{{ $quota > 0 ? ' (€ '.number_format($quota, 2, ',', '.').')' : '' }}</legend>
                        <x-scelta-pagamento class="mt-3" :selected="$metodo" :required="true" :nome="$utente->name" />
                        <x-input-error :messages="$errors->get('metodo_pagamento')" />
                    </fieldset>

                    <div class="flex flex-wrap items-center justify-between gap-4 border-t border-cral-navy/10 pt-6">
                        <a href="{{ route('dashboard') }}" class="text-sm font-semibold text-cral-navy-soft transition hover:text-cral-red">Annulla</a>
                        <x-primary-button class="w-full sm:w-auto">Richiedi la tessera</x-primary-button>
                    </div>
                </form>
            </section>
        @endif
    </div>
</x-public-layout>
