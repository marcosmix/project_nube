@props([
    'label',
    'value',
    'hint' => null,
    'tone' => 'neutral',
])

@php
    $tones = [
        'neutral' => 'from-slate-50 to-white text-slate-950',
        'primary' => 'from-indigo-50 to-white text-slate-950',
        'accent' => 'from-orange-50 to-white text-slate-950',
        'success' => 'from-emerald-50 to-white text-slate-950',
        'warning' => 'from-amber-50 to-white text-slate-950',
        'danger' => 'from-rose-50 to-white text-slate-950',
    ];
@endphp

<div {{ $attributes->merge(['class' => 'rounded-3xl border border-slate-200 bg-gradient-to-br ' . ($tones[$tone] ?? $tones['neutral']) . ' p-5 shadow-sm ring-1 ring-slate-200/70']) }}>
    <p class="text-sm font-medium text-slate-500">{{ $label }}</p>
    <p class="mt-3 text-3xl font-semibold tracking-tight">{{ $value }}</p>

    @if ($hint)
        <p class="mt-2 text-sm text-slate-500">{{ $hint }}</p>
    @endif
</div>
