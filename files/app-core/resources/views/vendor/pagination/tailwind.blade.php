@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Navigazione tra le pagine" class="flex flex-wrap items-center justify-center gap-2">

        {{-- Pagina precedente --}}
        @if ($paginator->onFirstPage())
            <span class="btn btn-sm btn-ghost pointer-events-none opacity-40" aria-disabled="true">&larr; Precedente</span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="btn btn-sm btn-ghost">&larr; Precedente</a>
        @endif

        {{-- Numeri di pagina (da tablet in su) --}}
        <div class="hidden items-center gap-1 sm:flex">
            @foreach ($elements as $element)
                @if (is_string($element))
                    <span class="px-2 text-cral-navy-soft" aria-hidden="true">{{ $element }}</span>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span aria-current="page" class="grid h-10 min-w-[2.5rem] place-items-center rounded-full bg-cral-red px-3 text-sm font-bold text-white shadow-glow">{{ $page }}</span>
                        @else
                            <a href="{{ $url }}" class="grid h-10 min-w-[2.5rem] place-items-center rounded-full px-3 text-sm font-semibold transition hover:bg-cral-navy/5" aria-label="Vai alla pagina {{ $page }}">{{ $page }}</a>
                        @endif
                    @endforeach
                @endif
            @endforeach
        </div>

        <span class="px-2 text-sm font-semibold text-cral-navy-soft sm:hidden">Pagina {{ $paginator->currentPage() }} di {{ $paginator->lastPage() }}</span>

        {{-- Pagina successiva --}}
        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="btn btn-sm btn-ghost">Successiva &rarr;</a>
        @else
            <span class="btn btn-sm btn-ghost pointer-events-none opacity-40" aria-disabled="true">Successiva &rarr;</span>
        @endif
    </nav>
@endif
