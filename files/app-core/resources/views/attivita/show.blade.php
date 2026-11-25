<x-public-layout :title="$attivita->titolo.' — CRAL'" :description="\Illuminate\Support\Str::limit(strip_tags(\App\Support\HtmlSicuro::pulisci($attivita->descrizione)), 150)">

    {{-- ===================================================== copertina --}}
    <section class="navy-bg relative isolate overflow-hidden text-white">
        @if($attivita->immagine_copertina)
            <img src="{{ asset('uploads/'.$attivita->immagine_copertina) }}" alt="" decoding="async" fetchpriority="high"
                class="zoom-in absolute inset-0 -z-20 h-full w-full object-cover">
            <div class="absolute inset-0 -z-10 bg-gradient-to-t from-cral-navy-deep via-cral-navy-deep/65 to-cral-navy-deep/25"></div>
        @else
            <div class="hero-bg absolute inset-0 -z-20"></div>
        @endif

        <div class="container-x relative pb-28 pt-36 sm:pb-36 sm:pt-52">
            <a href="{{ route('attivita.index') }}" class="mb-6 inline-flex items-center gap-2 text-sm font-semibold text-white/80 transition hover:text-white" data-reveal="fade">
                <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M12.79 5.23a.75.75 0 01-.02 1.06L9.06 10l3.71 3.71a.75.75 0 11-1.06 1.06l-4.25-4.25a.75.75 0 010-1.06l4.25-4.25a.75.75 0 011.06 0z" clip-rule="evenodd"/></svg>
                Tutte le attività
            </a>

            @if($attivita->isPassata())
                <p class="mb-4"><span class="chip chip-glass" data-reveal="fade">Attività conclusa</span></p>
            @endif

            <h1 class="max-w-3xl text-4xl font-bold leading-[1.03] tracking-[-0.03em] sm:text-6xl" data-reveal data-delay="0.05">{{ $attivita->titolo }}</h1>

            <div class="mt-8 flex flex-wrap gap-3" data-reveal data-delay="0.15">
                @if($attivita->periodoLabel())
                    <span class="chip chip-glass !px-4 !py-2 !text-sm">
                        <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"/></svg>
                        {{ $attivita->periodoLabel() }}@if($attivita->orarioLabel()) · ore {{ $attivita->orarioLabel() }}@endif
                    </span>
                @endif
                @if($attivita->luogo)
                    <span class="chip chip-glass !px-4 !py-2 !text-sm">
                        <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M9.69 18.93l.01.01.01-.01a.75.75 0 00.6 0l.01.01.01-.01C14.5 16.5 17 13.2 17 9.5a7 7 0 10-14 0c0 3.7 2.5 7 6.69 9.43zM10 12a2.5 2.5 0 100-5 2.5 2.5 0 000 5z" clip-rule="evenodd"/></svg>
                        {{ $attivita->luogo }}
                    </span>
                @endif
                <x-attivita-prezzo :attivita="$attivita" :utente="auth()->user()" variante="chip" />
            </div>
        </div>

        <x-wave class="absolute inset-x-0 -bottom-0.5 h-8 text-white sm:h-14" />
    </section>

    <section class="container-x max-w-3xl pb-24 sm:pb-32">

        @if($attivita->ricorrente && $attivita->date_ricorrenti)
            <div class="rounded-[1.75rem] bg-cral-red-tint p-6" data-reveal>
                <p class="font-display text-lg font-bold tracking-tight text-cral-red-dark">Evento ricorrente: le altre date</p>
                <ul class="mt-4 flex flex-wrap gap-2">
                    @foreach($attivita->dateRicorrentiLabel() as $dataLabel)
                        <li class="chip bg-white text-cral-navy shadow-soft">{{ ucfirst($dataLabel) }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="prose-cral mt-10" data-reveal>
            {!! \App\Support\HtmlSicuro::pulisci($attivita->descrizione) !!}
        </div>

        {{-- Iscrizione all'attività: chi ha un account approvato si iscrive da solo, subito, e poi paga la quota se c'è --}}
        @if($attivita->iscrizione_abilitata)
            <div id="iscrizione" class="navy-bg relative isolate mt-14 overflow-hidden rounded-[2rem] p-7 text-white shadow-lift sm:p-10" data-reveal>
                <div class="pointer-events-none absolute -right-20 -top-20 -z-10 h-64 w-64 rounded-full bg-cral-red/40 blur-3xl" aria-hidden="true"></div>

                <p class="text-sm font-semibold text-white/70">Partecipazione</p>
                <h2 class="mt-1 text-3xl font-bold tracking-[-0.03em]">Iscriviti all'attività</h2>

                @if(session('ok_iscrizione'))
                    <p class="mt-5 rounded-2xl bg-white/15 px-4 py-3 text-sm font-semibold" role="status">{{ session('ok_iscrizione') }}</p>
                @endif
                @if(session('errore_iscrizione'))
                    <p class="mt-5 rounded-2xl bg-cral-red px-4 py-3 text-sm font-semibold" role="alert">{{ session('errore_iscrizione') }}</p>
                @endif

                <dl class="mt-6 grid gap-5 text-sm sm:grid-cols-3">
                    <div>
                        <dt class="font-semibold text-white/60">Iscrizioni aperte</dt>
                        <dd class="mt-1 font-semibold leading-relaxed">
                            dal {{ $attivita->iscrizioni_apertura?->translatedFormat('j M Y, H:i') }}<br>
                            al {{ $attivita->iscrizioni_chiusura?->translatedFormat('j M Y, H:i') }}
                        </dd>
                    </div>
                    @if($attivita->posti_max)
                        <div>
                            <dt class="font-semibold text-white/60">Posti</dt>
                            <dd class="mt-1 font-semibold">{{ $attivita->postiRimasti() }} liberi su {{ $attivita->posti_max }}</dd>
                        </div>
                    @endif
                    <x-attivita-prezzo :attivita="$attivita" :utente="auth()->user()" />
                </dl>

                <div class="mt-8">
                    @auth
                        @if(! auth()->user()->haAccessoSito())
                            <p class="text-lg font-bold">Potrai iscriverti appena la tua iscrizione al sito sarà approvata.</p>
                            <a href="{{ route('attesa-approvazione') }}" class="btn btn-primary mt-5">Stato della richiesta</a>
                        @else
                            @if($iscrizione)
                                {{-- L'iscrizione è automatica: subito iscritto, e se c'è una quota si sceglie come pagare --}}
                                @if($iscrizione->confermata())
                                    <p class="flex items-center gap-2 text-lg font-bold">
                                        <svg class="h-6 w-6 text-cral-red-glow" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.7-9.3a1 1 0 00-1.4-1.4L9 10.58 7.7 9.3a1 1 0 00-1.4 1.4l2 2a1 1 0 001.4 0l4-4z" clip-rule="evenodd"/></svg>
                                        Sei iscritto a questa attività.
                                    </p>

                                    @if($iscrizione->pagato)
                                        <p class="mt-2 text-white/75">Quota saldata{{ $iscrizione->metodo_pagamento === 'paypal' ? ' con PayPal' : '' }}. Grazie!</p>
                                    @elseif($iscrizione->richiedePagamento())
                                        <p class="mt-3 rounded-2xl bg-cral-red px-4 py-3 text-sm font-semibold" role="status">
                                            @if($iscrizione->pagamento_dichiarato_il)
                                                Stiamo verificando il tuo pagamento: l'iscrizione sarà completa appena la quota risulta pagata.
                                            @else
                                                L'iscrizione è completa solo con il pagamento: paga la quota qui sotto.
                                            @endif
                                        </p>
                                        <x-attivita-pagamento :iscrizione="$iscrizione" class="mt-4" />
                                    @endif

                                    {{-- Informazioni per chi è iscritto: ritrovo, cosa portare... --}}
                                    @if($attivita->messaggio_iscritti)
                                        <div class="mt-5 rounded-[1.5rem] bg-white/10 p-5 ring-1 ring-white/20">
                                            <p class="text-sm font-semibold text-white/70">Informazioni per gli iscritti</p>
                                            <p class="mt-1.5 whitespace-pre-line leading-relaxed">{{ $attivita->messaggio_iscritti }}</p>
                                        </div>
                                    @endif

                                    {{-- Le risposte date al modulo, con la possibilità di correggerle --}}
                                    @if($attivita->haCampi())
                                        @php
                                            $senzaRisposta = $iscrizione->domandeSenzaRisposta();
                                            $risposteDate = $iscrizione->risposteTesto();
                                        @endphp
                                        <div class="mt-5 rounded-[1.5rem] bg-white/10 p-5 ring-1 ring-white/20">
                                            <p class="text-sm font-semibold text-white/70">Le tue risposte</p>
                                            @if($risposteDate)
                                                <dl class="mt-2 grid gap-x-6 gap-y-2 text-sm sm:grid-cols-2">
                                                    @foreach($risposteDate as $domanda => $risposta)
                                                        <div>
                                                            <dt class="text-white/60">{{ $domanda }}</dt>
                                                            <dd class="font-semibold">{{ $risposta }}</dd>
                                                        </div>
                                                    @endforeach
                                                </dl>
                                            @endif
                                            @if($senzaRisposta->isNotEmpty())
                                                <p class="mt-3 rounded-xl bg-cral-red px-3 py-2 text-sm font-semibold" role="status">Mancano delle risposte: completa il modulo qui sotto.</p>
                                            @endif

                                            @unless($attivita->isPassata())
                                                <details class="mt-3" {{ $senzaRisposta->isNotEmpty() || $errors->any() ? 'open' : '' }}>
                                                    <summary class="cursor-pointer text-sm font-semibold text-white/80 underline underline-offset-4 transition hover:text-white">{{ $senzaRisposta->isNotEmpty() ? 'Completa le risposte' : 'Modifica le risposte' }}</summary>
                                                    <form method="POST" action="{{ route('attivita.risposte', $attivita->slug) }}" class="mt-4 rounded-[1.25rem] bg-white p-5 text-cral-navy">
                                                        @csrf
                                                        @method('PUT')
                                                        <x-attivita-campi :attivita="$attivita" :valori="$iscrizione->valoriPerModulo()" />
                                                        <button type="submit" class="btn btn-primary btn-sm mt-5">Salva le risposte</button>
                                                    </form>
                                                </details>
                                            @endunless
                                        </div>
                                    @endif
                                @else
                                    <p class="text-lg font-bold">Non risulti iscritto a questa attività.</p>
                                    <p class="mt-2 text-white/75">Per saperne di più scrivi a <a href="mailto:{{ config('mail.admin_address') }}" class="font-semibold underline underline-offset-4">{{ config('mail.admin_address') }}</a>.</p>
                                @endif

                                @if(! $iscrizione->rifiutata() && ! $iscrizione->pagato && ! $iscrizione->pagamento_dichiarato_il && ! $attivita->isPassata())
                                    <form method="POST" action="{{ route('attivita.annulla', $attivita->slug) }}" class="mt-5" onsubmit="return confirm('Vuoi annullare la tua iscrizione?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-sm font-semibold text-white/70 underline underline-offset-4 transition hover:text-white">Annulla la mia iscrizione</button>
                                    </form>
                                @endif
                            @elseif($statoIscrizioni === 'aperte')
                                @php
                                    $miaQuota = $attivita->prezzoPer(auth()->user());
                                    $miaQuotaLabel = $attivita->prezzoLabelPer(auth()->user());
                                @endphp
                                <form method="POST" action="{{ route('attivita.partecipa', $attivita->slug) }}">
                                    @csrf
                                    @if($attivita->haCampi())
                                        <div class="rounded-[1.5rem] bg-white p-5 text-cral-navy sm:p-6">
                                            <h3 class="font-display text-xl font-bold tracking-tight">Prima di iscriverti</h3>
                                            <p class="mt-1 text-sm text-cral-navy-soft">Rispondi a queste domande: servono a chi organizza. I campi con <span class="text-cral-red">*</span> sono obbligatori e potrai correggere le risposte anche dopo.</p>
                                            <x-attivita-campi :attivita="$attivita" class="mt-5" />
                                        </div>
                                    @endif
                                    @if(($miaQuota ?? 0) > 0)
                                        <x-attivita-metodo :importo="$miaQuotaLabel" class="{{ $attivita->haCampi() ? 'mt-4' : '' }}" />
                                    @endif
                                    <button type="submit" class="btn btn-primary {{ $attivita->haCampi() || ($miaQuota ?? 0) > 0 ? 'mt-6' : '' }}">
                                        Iscriviti{{ $miaQuotaLabel ? ' — '.$miaQuotaLabel : '' }}
                                    </button>
                                </form>
                                <p class="mt-3 max-w-md text-sm text-white/70">
                                    @if(($miaQuota ?? 0) > 0)
                                        Il pagamento della quota è obbligatorio: scegli come pagare e ti iscrivi subito, senza conferma dell'amministratore.
                                    @elseif($attivita->haCampi())
                                        Compili il modulo e sei iscritto: non serve nessuna conferma.
                                    @else
                                        Un clic e sei iscritto: non devi compilare nulla e non serve nessuna conferma.
                                    @endif
                                </p>
                            @else
                                <p class="text-lg font-bold">
                                    @if($statoIscrizioni === 'non_ancora')
                                        Le iscrizioni apriranno il {{ $attivita->iscrizioni_apertura->translatedFormat('j F Y') }} alle {{ $attivita->iscrizioni_apertura->format('H:i') }}.
                                    @elseif($statoIscrizioni === 'chiuse')
                                        Le iscrizioni sono chiuse.
                                    @elseif($statoIscrizioni === 'esaurite')
                                        Posti esauriti.
                                    @else
                                        Questa attività si è conclusa.
                                    @endif
                                </p>
                            @endif
                        @endif
                    @else
                        <p class="text-lg font-bold">Per iscriverti devi avere un account sul sito.</p>
                        <div class="mt-5 flex flex-wrap items-center gap-4">
                            <a href="{{ route('login') }}" class="btn btn-primary">Accedi</a>
                            <a href="{{ route('register') }}" class="text-sm font-semibold text-white/80 underline underline-offset-4 transition hover:text-white">Non hai un account? Iscriviti</a>
                        </div>
                    @endauth
                </div>
            </div>
        @endif

        {{-- Foto dell'evento: le vede solo chi ha un account approvato, gli altri le vedono sfocate --}}
        @if($fotoSingole->isNotEmpty() || $album->isNotEmpty())
            <div class="mt-16" @if($accesso) x-data="galleria" @endif>
                <h2 class="text-3xl font-bold tracking-[-0.03em]" data-reveal>Le foto dell'evento</h2>

                @unless($accesso)
                    <x-foto-riservate-avviso class="mt-6" />
                @endunless

                @if($album->isNotEmpty())
                    <div class="mt-6 grid gap-x-5 gap-y-10 sm:grid-cols-2" data-stagger="0.08">
                        @foreach($album as $albumItem)
                            <x-album-card :album="$albumItem" :accesso="$accesso" :lazy="$loop->index > 1" />
                        @endforeach
                    </div>
                @endif

                @if($fotoSingole->isNotEmpty())
                    <div class="mt-6 grid grid-cols-2 gap-3 sm:grid-cols-3" data-stagger="0.05">
                        @foreach($fotoSingole as $foto)
                            <div data-reveal="scale">
                                <x-foto-miniatura :foto="$foto" :accesso="$accesso" :quadrata="true" />
                                @if($accesso && $foto->didascalia)
                                    <p class="mt-2 text-sm text-cral-navy-soft">{{ $foto->didascalia }}</p>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif

                @if($accesso)
                    <x-lightbox />
                @else
                    <x-foto-riservate-popup />
                @endif
            </div>
        @endif

        @guest
            <div class="cta-bg relative isolate mt-20 overflow-hidden rounded-[2rem] px-7 py-12 text-center text-white sm:px-12" data-reveal>
                <h3 class="mx-auto max-w-lg text-3xl font-bold leading-tight tracking-[-0.03em]">Vuoi partecipare alle prossime attività?</h3>
                <p class="mx-auto mt-3 max-w-md text-white/85">Iscriviti al sito per vedere le foto degli eventi, oppure diventa socio CRAL per iscriverti alle attività, usare le convenzioni e prendere in prestito i libri.</p>
                <a href="{{ route('register') }}" class="btn btn-white mt-7">Iscriviti ora</a>
            </div>
        @endguest
    </section>
</x-public-layout>
