<nav x-data="{ open: false }" class="bg-white border-b-2 border-cral-navy">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-20">
            <div class="flex">
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}">
                        <x-application-logo class="h-12 w-12 object-contain" />
                    </a>
                </div>

                <div class="hidden space-x-1 sm:-my-px sm:ms-10 sm:flex">
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                        Dashboard
                    </x-nav-link>
                    <x-nav-link :href="route('documenti.index')" :active="request()->routeIs('documenti.*')">
                        Documenti
                    </x-nav-link>
                    <x-nav-link :href="route('account.show')" :active="request()->routeIs('account.*')">
                        Il mio account
                    </x-nav-link>
                    <x-nav-link :href="route('home')" :active="false">
                        Torna al sito
                    </x-nav-link>
                </div>
            </div>

            <div class="hidden sm:flex sm:items-center sm:ms-6">
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center gap-1.5 px-4 py-2 font-grotesk text-xs font-bold tracking-[0.15em] uppercase text-cral-navy hover:text-cral-red transition">
                            <span>{{ Auth::user()->name }}</span>
                            <svg class="h-3 w-3 fill-current" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile.edit')">
                            Profilo
                        </x-dropdown-link>

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf

                            <x-dropdown-link :href="route('logout')"
                                    onclick="event.preventDefault();
                                                this.closest('form').submit();">
                                Esci
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 border-2 border-cral-navy text-cral-navy transition">
                    <svg class="h-5 w-5" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden border-t-2 border-cral-navy">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                Dashboard
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('documenti.index')" :active="request()->routeIs('documenti.*')">
                Documenti
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('account.show')" :active="request()->routeIs('account.*')">
                Il mio account
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('home')" :active="false">
                Torna al sito
            </x-responsive-nav-link>
        </div>

        <div class="pt-4 pb-1 border-t-2 border-cral-navy">
            <div class="px-4">
                <div class="font-grotesk font-bold text-cral-navy">{{ Auth::user()->name }}</div>
                <div class="font-grotesk text-sm text-cral-navy-soft">{{ Auth::user()->email }}</div>
            </div>

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile.edit')">
                    Profilo
                </x-responsive-nav-link>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <x-responsive-nav-link :href="route('logout')"
                            onclick="event.preventDefault();
                                        this.closest('form').submit();">
                        Esci
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>
