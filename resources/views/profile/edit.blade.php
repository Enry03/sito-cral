<x-app-layout>
    <x-slot name="header">
        <span class="font-grotesk text-xs font-bold tracking-[0.2em] uppercase text-cral-red">Area riservata</span>
        <h2 class="mt-1 font-display uppercase text-3xl tracking-wide">Profilo</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="p-6 sm:p-8 bg-white border-2 border-cral-navy">
                <div class="max-w-xl">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </div>

            <div class="p-6 sm:p-8 bg-white border-2 border-cral-navy">
                <div class="max-w-xl">
                    @include('profile.partials.update-password-form')
                </div>
            </div>

            <div class="p-6 sm:p-8 bg-white border-2 border-cral-red">
                <div class="max-w-xl">
                    @include('profile.partials.delete-user-form')
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
