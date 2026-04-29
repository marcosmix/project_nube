@props([
    'padding' => 'md',
])

@php
    $base = 'rounded-3xl border border-slate-200 bg-white text-slate-950 shadow-sm ring-1 ring-slate-200/70';

    // Keep spacing centralized here so modules stay visually consistent.
    $paddings = [
        'none' => '',
        'sm' => 'p-4 sm:p-5',
        'md' => 'p-5 sm:p-6',
        'lg' => 'p-6 sm:p-7',
    ];
@endphp

<div {{ $attributes->merge([
    'class' => $base . ' ' . ($paddings[$padding] ?? $paddings['md']),
]) }}>
    {{ $slot }}
</div>
