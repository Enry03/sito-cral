{{--
    Prezzo di un'attività, secondo chi guarda.
    - Prezzo unico: "Quota € 15,00".
    - Due prezzi: il prezzo dei soci è sempre in risalto (è lo sconto), quello dei non soci
      sta accanto. Chi è socio vede il prezzo dei non soci barrato e quanto risparmia; chi non
      è socio (anche senza login) vede cosa guadagnerebbe con la tessera e come ottenerla.
    $variante: "scheda" (riquadro grande, su fondo blu), "chip" (etichetta piccola su fondo
    scuro) oppure "riga" (una riga di testo, su fondo chiaro, per l'elenco delle attività).
--}}
@props(['attivita', 'utente' => null, 'variante' => 'scheda'])

@php
    $socio = (bool) $utente?->isSocioCral();
    $due = $attivita->haDuePrezzi();
    $mio = $attivita->prezzoLabelPer($utente);
    $intero = \App\Models\Attivita::formattaImporto((float) $attivita->prezzoNonSoci());
    $ridotto = \App\Models\Attivita::formattaImporto((float) $attivita->prezzoPerSoci());
    $ridottoTesto = $ridotto === 'Gratis' ? 'gratis' : $ridotto;
    $risparmio = \App\Models\Attivita::formattaImporto($attivita->risparmioSoci());
    $puoDiventareSocio = $utente && $utente->ruolo === 'iscritto' && ! $utente->richiede_tessera;
@endphp

@if($mio !== null)
    @if($variante === 'riga')
        <span {{ $attributes->merge(['class' => 'inline-flex flex-wrap items-baseline gap-x-2 text-sm']) }}>
            @if($due && $socio)
                <strong class="font-display text-lg font-bold text-cral-red">{{ $ridotto }}</strong>
                <s class="font-semibold text-cral-navy-soft"><span class="sr-only">invece di </span>{{ $intero }}</s>
                <span class="chip chip-soft">Prezzo soci</span>
            @elseif($due)
                <strong class="font-display text-lg font-bold">{{ $intero }}</strong>
                <span class="chip chip-soft">Soci {{ $ridottoTesto }}</span>
            @else
                <strong class="font-display text-lg font-bold">{{ $mio }}</strong>
            @endif
        </span>
    @elseif($variante === 'chip')
        <span {{ $attributes->merge(['class' => 'chip chip-glass !px-4 !py-2 !text-sm']) }}>
            @if($due && $socio)
                Quota soci <strong class="font-bold">{{ $mio }}</strong>
                <s class="text-white/60"><span class="sr-only">invece di </span>{{ $intero }}</s>
            @elseif($due)
                Quota {{ $mio }} · soci {{ $ridottoTesto }}
            @else
                Quota: {{ $mio }}
            @endif
        </span>
    @elseif($due)
        {{-- Due prezzi: occupa tutta la riga; il prezzo dei soci è il riquadro bianco in risalto --}}
        <div {{ $attributes->class(['sm:col-span-3']) }}>
            <dt class="font-semibold text-white/60">Quota di partecipazione</dt>
            <dd class="mt-2 grid gap-3 sm:grid-cols-2">
                <span class="block rounded-[1.25rem] bg-white p-4 text-cral-navy shadow-lift sm:p-5">
                    <span class="chip chip-red">{{ $socio ? 'Il tuo prezzo da socio CRAL' : 'Prezzo soci CRAL' }}</span>
                    <span class="mt-2 block font-display text-4xl font-bold tracking-tight text-cral-red">{{ $ridotto }}</span>
                    <span class="mt-1 block text-sm font-semibold text-cral-navy-soft">
                        @if($socio)
                            Risparmi {{ $risparmio }} con la tessera CRAL
                        @else
                            Con la tessera risparmi {{ $risparmio }}
                        @endif
                    </span>
                    @unless($socio)
                        @if($puoDiventareSocio)
                            <a href="{{ route('socio.richiesta') }}" class="mt-3 inline-block text-sm font-bold text-cral-red underline underline-offset-4 hover:text-cral-red-dark">Diventa socio e risparmia</a>
                        @elseif(! $utente)
                            <a href="{{ route('register') }}" class="mt-3 inline-block text-sm font-bold text-cral-red underline underline-offset-4 hover:text-cral-red-dark">Iscriviti e diventa socio</a>
                        @endif
                    @endunless
                </span>

                <span class="block rounded-[1.25rem] bg-white/10 p-4 ring-1 ring-white/20 sm:p-5">
                    <span class="text-sm font-semibold text-white/70">{{ $socio ? 'Prezzo non soci' : 'Il tuo prezzo (non socio)' }}</span>
                    @if($socio)
                        <s class="mt-2 block font-display text-3xl font-bold tracking-tight text-white/50 decoration-2"><span class="sr-only">Prezzo per i non soci: </span>{{ $intero }}</s>
                    @else
                        <span class="mt-2 block font-display text-3xl font-bold tracking-tight">{{ $intero }}</span>
                    @endif
                </span>
            </dd>
        </div>
    @else
        <div {{ $attributes }}>
            <dt class="font-semibold text-white/60">Quota</dt>
            <dd class="mt-1"><span class="font-display text-3xl font-bold tracking-tight">{{ $mio }}</span></dd>
        </div>
    @endif
@endif
