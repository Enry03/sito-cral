@php
    $album = $getRecord();
    $foto = $album->copertinaVisibile();
@endphp

<div class="w-full">
    <img src="{{ $foto ? $foto->miniaturaUrl() : asset('images/album-vuoto.svg') }}" alt="" loading="lazy"
        class="h-44 w-full rounded-xl object-cover {{ $foto ? '' : 'bg-gray-100' }}">
</div>
