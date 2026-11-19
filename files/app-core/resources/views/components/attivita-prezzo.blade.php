{{--
    Prezzo di un'attività, secondo chi guarda.
    - Prezzo unico: "Quota € 15,00".
    - Due prezzi, chi guarda è socio: il suo prezzo in evidenza, quello dei non soci barrato
      e quanto risparmia.
    - Due prezzi, chi guarda non è socio (anche senza login): la sua quota, e accanto quanto
      pagherebbe un socio, con l'invito a diventarlo.
    $variante: "scheda" (riquadro grande, su fondo blu) oppure "chip" (etichetta piccola).
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
    @if($variante === 'chip')
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
    @else
        {{-- Con due prezzi il riquadro occupa tutta la riga, così il confronto respira --}}
        <div {{ $attributes->class(['sm:col-span-3' => $due]) }}>
            <dt class="font-semibold text-white/60">{{ $due && $socio ? 'La tua quota da socio' : 'Quota' }}</dt>
            <dd class="mt-1 flex flex-wrap items-center gap-x-5 gap-y-3">
                <span>
                    <span class="font-display text-3xl font-bold tracking-tight">{{ $mio }}</span>
                    @if($due && $socio)
                        <s class="ml-2 text-base font-semibold text-white/55"><span class="sr-only">Prezzo per i non soci: </span>{{ $intero }}</s>
                    @endif
                </span>

                @if($due && $socio)
                    <span class="inline-flex rounded-full bg-white/15 px-3 py-1 text-xs font-bold">Risparmi {{ $risparmio }} con la tessera CRAL</span>
                @elseif($due)
                    <span class="max-w-md rounded-2xl bg-white/10 px-4 py-2.5 text-sm font-semibold leading-snug ring-1 ring-white/20">
                        I soci CRAL pagano <strong class="font-bold">{{ $ridottoTesto }}</strong>: risparmiano {{ $risparmio }}.
                        @if($puoDiventareSocio)
                            <a href="{{ route('socio.richiesta') }}" class="ml-1 whitespace-nowrap underline underline-offset-4 hover:text-white">Diventa socio</a>
                        @elseif(! $utente)
                            <a href="{{ route('register') }}" class="ml-1 whitespace-nowrap underline underline-offset-4 hover:text-white">Iscriviti e diventa socio</a>
                        @endif
                    </span>
                @endif
            </dd>
        </div>
    @endif
@endif
