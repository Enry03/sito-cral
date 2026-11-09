@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'field disabled:opacity-50']) }}>
