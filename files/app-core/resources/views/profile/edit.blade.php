<x-public-layout title="Profilo — CRAL">
    <x-page-hero kicker="La mia area" title="Profilo e password" subtitle="Aggiorna i tuoi dati di accesso." />

    <div class="container-x max-w-3xl space-y-6 pb-24 sm:pb-32">
        <div class="surface p-6 sm:p-8" data-reveal>
            @include('profile.partials.update-profile-information-form')
        </div>

        <div class="surface p-6 sm:p-8" data-reveal>
            @include('profile.partials.update-password-form')
        </div>

        <div class="rounded-[1.75rem] bg-cral-red-tint p-6 sm:p-8" data-reveal>
            @include('profile.partials.delete-user-form')
        </div>
    </div>
</x-public-layout>
