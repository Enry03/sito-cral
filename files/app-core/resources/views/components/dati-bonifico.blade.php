{{--
    Dati per fare il bonifico della quota di tessera, inseriti dall'amministratore
    nel pannello (Dati dell'azienda). Ogni dato utile si copia con un tocco.
    - nome:    nome di chi paga, aggiunto alla causale (facoltativo)
    - importo: importo già formattato, es. "€ 25,00" (facoltativo)
--}}
@props(['nome' => null, 'importo' => null])

@php
    $bonifico = app(\App\Services\DatiAzienda::class)->bonifico();
    $causale = app(\App\Services\DatiAzienda::class)->causale($nome);

    $righe = array_filter([
        ['Intestatario', $bonifico['intestatario'] ?? null, null],
        ['IBAN', $bonifico['iban_formattato'] ?? null, $bonifico['iban'] ?? null],
        ['Banca', $bonifico['banca'] ?? null, null],
        ['BIC / SWIFT', $bonifico['bic'] ?? null, null],
        ['Importo', $importo, null],
        ['Causale', $causale.($nome ? '' : ' + il tuo nome e cognome'), $nome ? $causale : null],
    ], fn ($riga) => filled($riga[1]));
@endphp

@if($bonifico)
    <div {{ $attributes->merge(['class' => 'rounded-2xl bg-white p-5 ring-1 ring-cral-navy/10']) }}
        x-data="{ copiato: null, async copia(testo, chiave) { try { await navigator.clipboard.writeText(testo); this.copiato = chiave; setTimeout(() => { this.copiato = null }, 1800); } catch (e) {} } }">
        <p class="font-display text-lg font-bold tracking-tight">Dati per il bonifico</p>

        <dl class="mt-4 divide-y divide-cral-navy/10 text-[0.95rem]">
            @foreach($righe as $i => [$etichetta, $valore, $copiabile])
                <div class="flex flex-wrap items-center justify-between gap-x-4 gap-y-1 py-3 first:pt-0 last:pb-0">
                    <dt class="w-28 shrink-0 text-sm font-semibold text-cral-navy-soft">{{ $etichetta }}</dt>
                    <dd class="min-w-0 flex-1 break-words font-semibold {{ $etichetta === 'IBAN' ? 'font-mono tracking-wide' : '' }}">{{ $valore }}</dd>
                    @if($copiabile)
                        <button type="button" @click="copia(@js($copiabile), {{ $i }})"
                            class="rounded-full bg-cral-navy-tint px-3 py-1.5 text-xs font-semibold transition hover:bg-cral-navy/10 active:scale-95" aria-live="polite">
                            <span x-show="copiato !== {{ $i }}">Copia</span>
                            <span x-show="copiato === {{ $i }}" x-cloak class="text-cral-red">Copiato</span>
                        </button>
                    @endif
                </div>
            @endforeach
        </dl>
    </div>
@endif
