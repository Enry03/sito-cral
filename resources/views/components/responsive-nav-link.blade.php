@props(['active'])

@php
$classes = ($active ?? false)
            ? 'block w-full ps-4 pe-4 py-2.5 border-l-4 border-cral-red text-start font-grotesk text-sm font-bold tracking-wide uppercase text-cral-red bg-cral-cream transition'
            : 'block w-full ps-4 pe-4 py-2.5 border-l-4 border-transparent text-start font-grotesk text-sm font-bold tracking-wide uppercase text-cral-navy hover:bg-cral-cream transition';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
