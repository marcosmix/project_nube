@props([
    'tone' => 'default',
    'padding' => 'md',
])

@php
    $base = 'rounded-2xl border shadow-sm';
    $tones = [
        'default' => 'border-slate-200 bg-white',
        'subtle' => 'border-slate-200 bg-slate-50/80',
        'info' => 'border-sky-200 bg-sky-50',
        'success' => 'border-emerald-200 bg-emerald-50',
        'warning' => 'border-amber-200 bg-amber-50',
        'danger' => 'border-rose-200 bg-rose-50',
        'purple' => 'border-purple-200 bg-purple-50',
    ];
    $paddings = [
        'sm' => 'p-4',
        'md' => 'p-5',
        'lg' => 'p-6',
    ];
@endphp

<div {{ $attributes->merge(['class' => $base . ' ' . ($tones[$tone] ?? $tones['default']) . ' ' . ($paddings[$padding] ?? $paddings['md'])]) }}>
    {{ $slot }}
</div>
