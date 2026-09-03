@props(['value'])

<label {{ $attributes->merge(['class' => 'block font-grotesk text-[10px] font-bold tracking-[0.2em] uppercase text-cral-navy-soft mb-1.5']) }}>
    {{ $value ?? $slot }}
</label>
