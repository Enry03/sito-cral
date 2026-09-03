<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center border-2 border-cral-navy bg-cral-red px-6 py-3 font-grotesk text-xs font-bold tracking-[0.15em] uppercase text-white stack-shadow-sm hover:translate-x-[2px] hover:translate-y-[2px] hover:shadow-none focus:outline-none focus:ring-2 focus:ring-cral-red focus:ring-offset-2 transition disabled:opacity-50']) }}>
    {{ $slot }}
</button>
