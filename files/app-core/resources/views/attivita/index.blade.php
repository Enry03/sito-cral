<x-public-layout title="Attività — CRAL" description="Tutte le iniziative organizzate dal CRAL per i soci.">
    <x-page-hero kicker="Cosa facciamo insieme" title="Attività" subtitle="Tutte le iniziative organizzate dal CRAL per i soci." />

    <section class="container-x pb-24 sm:pb-32">
        <h2 class="text-3xl font-bold tracking-[-0.03em]" data-reveal>In programma</h2>

        @if($inProgramma->isEmpty())
            <div class="mt-8 rounded-[1.75rem] bg-cral-navy-tint px-6 py-16 text-center sm:py-20" data-reveal>
                <p class="font-display text-2xl font-bold tracking-tight">Nessuna attività in programma</p>
                <p class="mt-2 text-cral-navy-soft">Torna presto a trovarci!</p>
            </div>
        @else
            <div class="mt-8 grid gap-x-6 gap-y-12 sm:grid-cols-2 lg:grid-cols-3" data-stagger="0.07">
                @foreach($inProgramma as $i => $item)
                    <x-attivita.card :item="$item" :numero="$i + 1" />
                @endforeach
            </div>
        @endif

        @if($passate->isNotEmpty())
            <h2 class="mt-20 text-3xl font-bold tracking-[-0.03em] sm:mt-28" data-reveal>Già svolte</h2>
            <div class="mt-8 grid gap-x-6 gap-y-12 sm:grid-cols-2 lg:grid-cols-3" data-stagger="0.07">
                @foreach($passate as $item)
                    <x-attivita.card :item="$item" :passata="true" />
                @endforeach
            </div>

            <div class="mt-14">
                {{ $passate->links() }}
            </div>
        @endif
    </section>
</x-public-layout>
