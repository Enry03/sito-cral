<button {{ $attributes->merge(['type' => 'button', 'class' => 'inline-flex items-center border-2 border-cral-navy bg-white px-6 py-3 font-grotesk text-xs font-bold tracking-[0.15em] uppercase text-cral-navy hover:bg-cral-navy hover:text-white focus:outline-none focus:ring-2 focus:ring-cral-navy focus:ring-offset-2 disabled:opacity-50 transition']) }}>
    {{ $slot }}
</button>
