@props([
    'variant' => 'neutral',
    'size' => 'md',
])

@php
    $base = 'inline-flex items-center rounded-full border font-medium';

    $sizes = [
        'sm' => 'px-2.5 py-1 text-[11px]',
        'md' => 'px-3 py-1 text-xs',
    ];

    $variants = [
        'neutral' => 'border-slate-200 bg-slate-100 text-slate-700',
        'primary' => 'border-indigo-200 bg-indigo-50 text-indigo-700',
        'accent' => 'border-orange-200 bg-orange-50 text-orange-700',
        'success' => 'border-emerald-200 bg-emerald-50 text-emerald-700',
        'warning' => 'border-amber-200 bg-amber-50 text-amber-700',
        'danger' => 'border-rose-200 bg-rose-50 text-rose-700',
        'info' => 'border-sky-200 bg-sky-50 text-sky-700',
    ];
@endphp

<span {{ $attributes->merge([
    'class' => $base . ' ' . ($variants[$variant] ?? $variants['neutral']) . ' ' . ($sizes[$size] ?? $sizes['md']),
]) }}>
    {{ $slot }}
</span>
