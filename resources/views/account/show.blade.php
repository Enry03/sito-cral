<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Il mio account
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 grid gap-8 lg:grid-cols-3">

            <!-- Tessera -->
            <div class="lg:col-span-1">
                <div class="rounded-2xl bg-gradient-to-br from-slate-900 to-slate-800 text-white p-6 shadow-lg relative overflow-hidden">
                    <div class="absolute -top-10 -right-10 h-40 w-40 rounded-full bg-orange-500/20 blur-2xl"></div>

                    <div class="relative flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-gradient-to-br from-amber-500 to-orange-600 text-white font-extrabold text-xs">CR</span>
                            <span class="font-bold text-sm">CRAL</span>
                        </div>
                        <span class="text-[10px] font-semibold uppercase tracking-wider text-orange-300">Tessera socio</span>
                    </div>

                    <div class="relative mt-8">
                        <p class="text-lg font-bold">{{ $user->name }}</p>
                        <p class="text-xs text-slate-400 mt-1">Socio dal {{ optional($user->data_iscrizione)->translatedFormat('d F Y') ?? '—' }}</p>
                    </div>

                    <div class="relative mt-6 flex items-end justify-between">
                        <div>
                            <p class="text-[10px] uppercase tracking-wider text-slate-400">N. tessera</p>
                            <p class="font-mono text-sm">{{ $user->numero_tessera ?? 'Non ancora assegnata' }}</p>
                        </div>
                        @if($qrDataUri)
                            <img src="{{ $qrDataUri }}" alt="QR tessera" class="h-16 w-16 rounded bg-white p-1">
                        @endif
                    </div>
                </div>

                @unless($user->numero_tessera)
                    <p class="mt-4 text-sm text-slate-500">
                        Il numero di tessera verrà assegnato dagli amministratori dopo la verifica dei tuoi dati.
                    </p>
                @endunless
            </div>

            <!-- Dati -->
            <div class="lg:col-span-2 bg-white overflow-hidden shadow-sm sm:rounded-xl border border-slate-100 p-6">
                <h3 class="font-bold text-slate-900 mb-6">I miei dati</h3>

                <dl class="grid sm:grid-cols-2 gap-x-6 gap-y-5 text-sm">
                    <div>
                        <dt class="text-slate-500">Nome e cognome</dt>
                        <dd class="mt-1 font-medium text-slate-900">{{ $user->name }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-500">Email</dt>
                        <dd class="mt-1 font-medium text-slate-900">{{ $user->email }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-500">Telefono</dt>
                        <dd class="mt-1 font-medium text-slate-900">{{ $user->telefono ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-500">Data di nascita</dt>
                        <dd class="mt-1 font-medium text-slate-900">{{ optional($user->data_nascita)->translatedFormat('d F Y') ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-500">Codice fiscale</dt>
                        <dd class="mt-1 font-medium text-slate-900">{{ $user->codice_fiscale ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-500">Indirizzo</dt>
                        <dd class="mt-1 font-medium text-slate-900">{{ $user->indirizzo ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-500">Scadenza quota</dt>
                        <dd class="mt-1 font-medium text-slate-900">{{ optional($user->data_scadenza_quota)->translatedFormat('d F Y') ?? '—' }}</dd>
                    </div>
                </dl>

                <div class="mt-8 pt-6 border-t border-slate-100">
                    <a href="{{ route('profile.edit') }}" class="inline-flex items-center text-sm font-semibold text-orange-600 hover:text-orange-700">
                        Modifica nome, email o password &rarr;
                    </a>
                    <p class="mt-1 text-xs text-slate-400">Per correggere gli altri dati anagrafici contatta gli amministratori del CRAL.</p>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
