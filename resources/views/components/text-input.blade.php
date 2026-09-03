@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'border-2 border-cral-navy px-4 py-3 font-grotesk font-semibold focus:outline-none focus:ring-2 focus:ring-cral-red focus:border-cral-red disabled:opacity-50']) }}>
