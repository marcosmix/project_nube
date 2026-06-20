@props([
    'width' => '7xl',
])

@php
    $widths = [
        '7xl' => 'max-w-7xl',
        'wide' => 'max-w-[96rem]',
        'full' => 'max-w-none',
    ];

    $containerWidth = $widths[$width] ?? $widths['7xl'];
@endphp

<div {{ $attributes->merge(['class' => 'mx-auto flex w-full '.$containerWidth.' flex-col gap-8 px-4 py-8 sm:px-6 lg:px-8 lg:py-10']) }}>
    {{ $slot }}
</div>
