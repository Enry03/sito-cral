<button {{ $attributes->merge(['type' => 'submit', 'class' => 'btn bg-cral-red-tint text-cral-red-dark hover:bg-cral-red hover:text-white']) }}>
    {{ $slot }}
</button>
