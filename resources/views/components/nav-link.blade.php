@props(['active'])

@php
$classes = ($active ?? false)
            ? 'inline-flex items-center px-4 py-2 font-grotesk text-xs font-bold tracking-[0.15em] uppercase border-b-2 border-cral-red text-cral-red transition'
            : 'inline-flex items-center px-4 py-2 font-grotesk text-xs font-bold tracking-[0.15em] uppercase border-b-2 border-transparent text-cral-navy hover:border-cral-navy transition';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
