@props([
    'path' => null,
    'alt' => '',
    'ratio' => '4 / 5',
    'rounded' => 'rounded-xl',
    'width' => 'w-full',
])

@php
    $src = $path
        ? (\Illuminate\Support\Str::startsWith($path, ['http://', 'https://', '/']) ? $path : asset($path))
        : null;
@endphp

@if ($src)
    <img
        src="{{ $src }}"
        alt="{{ $alt }}"
        loading="lazy"
        style="aspect-ratio: {{ $ratio }}"
        {{ $attributes->class([$width, 'object-cover', $rounded]) }}
    >
@else
    <div
        role="img"
        aria-label="{{ $alt ?: 'Foto em breve' }}"
        style="aspect-ratio: {{ $ratio }}"
        {{ $attributes->class(['flex items-center justify-center border border-dashed border-outline-variant bg-surface-container', $rounded]) }}
    >
        {{-- TODO: definir o path da foto ao renderizar este componente --}}
        <span class="font-body text-[0.65rem] uppercase tracking-[0.2em] text-on-surface-variant/60">Foto</span>
    </div>
@endif
