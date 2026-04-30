@props([
    'label',
    'value',
    'hint' => null,
    'tone' => 'neutral',
    'change' => null,
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

    $changeTones = [
        'neutral' => 'border-slate-200 bg-white/80 text-slate-600',
        'success' => 'border-emerald-200 bg-emerald-50 text-emerald-700',
        'danger' => 'border-rose-200 bg-rose-50 text-rose-700',
    ];
@endphp

<div {{ $attributes->merge(['class' => 'rounded-3xl border border-slate-200 bg-gradient-to-br ' . ($tones[$tone] ?? $tones['neutral']) . ' p-5 shadow-sm ring-1 ring-slate-200/70']) }}>
    <p class="text-sm font-medium text-slate-500">{{ $label }}</p>
    <div class="mt-3 flex items-start justify-between gap-3">
        <p class="text-3xl font-semibold tracking-tight">{{ $value }}</p>

        @if ($change)
            <span class="inline-flex items-center rounded-full border px-2.5 py-1 text-xs font-medium {{ $changeTones[$change['tone']] ?? $changeTones['neutral'] }}">
                {{ $change['label'] }}
            </span>
        @endif
    </div>

    @if ($hint)
        <p class="mt-2 text-sm text-slate-500">{{ $hint }}</p>
    @endif

    @if ($change)
        <p class="mt-3 text-xs uppercase tracking-[0.18em] text-slate-400">{{ $change['context'] }}</p>
    @endif
</div>
