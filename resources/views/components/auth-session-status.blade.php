@props(['status'])

@if ($status)
    <div {{ $attributes->merge(['class' => 'font-grotesk text-sm font-semibold text-cral-navy border-2 border-cral-navy bg-cral-cream px-4 py-3']) }}>
        {{ $status }}
    </div>
@endif
