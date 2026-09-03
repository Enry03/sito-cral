<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center border-2 border-cral-red bg-white px-6 py-3 font-grotesk text-xs font-bold tracking-[0.15em] uppercase text-cral-red hover:bg-cral-red hover:text-white focus:outline-none focus:ring-2 focus:ring-cral-red focus:ring-offset-2 transition']) }}>
    {{ $slot }}
</button>
