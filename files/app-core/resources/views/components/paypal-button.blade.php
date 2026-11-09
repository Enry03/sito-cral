@props(['iscrizione'])

@php $paypal = $iscrizione->datiPaypal(); @endphp

@if($iscrizione->richiedePagamento())
    @if($paypal)
        <form action="{{ $paypal['action'] }}" method="post" class="inline-block">
            @foreach($paypal['campi'] as $nome => $valore)
                <input type="hidden" name="{{ $nome }}" value="{{ $valore }}">
            @endforeach
            <button type="submit" class="btn bg-[#FFC439] text-cral-navy hover:-translate-y-0.5 hover:bg-[#f2b82f]">
                Paga con <span class="font-black italic"><span class="text-[#003087]">Pay</span><span class="text-[#009cde]">Pal</span></span>
                <span>— {{ number_format((float) $iscrizione->importo, 2, ',', '.') }} €</span>
            </button>
        </form>
    @else
        {{-- PayPal non è ancora configurato (manca PAYPAL_BUSINESS_EMAIL nel file .env). --}}
        <div>
            <button type="button" disabled class="btn bg-[#FFC439]/70 text-cral-navy/70">
                Paga con PayPal — {{ number_format((float) $iscrizione->importo, 2, ',', '.') }} €
            </button>
            <p class="mt-2 text-sm text-white/70">Il pagamento online sarà disponibile a breve: ti contatteremo noi per la quota.</p>
        </div>
    @endif
@endif
