@props(['status' => 'Buka'])

@php($normalizedStatus = mb_strtolower(trim($status)))

<svg {{ $attributes->merge([
    'class' => 'context-icon',
    'viewBox' => '0 0 24 24',
    'fill' => 'none',
    'stroke' => 'currentColor',
    'stroke-width' => '2',
    'stroke-linecap' => 'round',
    'stroke-linejoin' => 'round',
    'aria-hidden' => 'true',
]) }}>
    @if ($normalizedStatus === 'buka')
        <circle cx="12" cy="12" r="9" />
        <path d="m8.5 12 2.2 2.2 4.8-4.8" />
    @elseif ($normalizedStatus === 'istirahat')
        <circle cx="12" cy="12" r="9" />
        <path d="M10 9v6m4-6v6" />
    @else
        <circle cx="12" cy="12" r="9" />
        <path d="m9 9 6 6m0-6-6 6" />
    @endif
</svg>
