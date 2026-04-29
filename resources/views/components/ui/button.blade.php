@props([
    'variant' => 'primary',
    'size' => 'md',
    'type' => 'button',
    'href' => null,
])

@php
    // Variants are intentionally opinionated so teams can scale the UI without per-view tweaks.
    $base = 'inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-2xl font-medium transition duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 disabled:pointer-events-none disabled:opacity-50';

    $sizes = [
        'sm' => 'h-9 px-3.5 text-sm',
        'md' => 'h-10 px-4 text-sm',
        'lg' => 'h-11 px-5 text-base',
    ];

    $variants = [
        'primary' => 'bg-indigo-600 text-white shadow-sm hover:bg-indigo-500 focus:ring-indigo-500',
        'secondary' => 'border border-slate-200 bg-white text-slate-700 shadow-sm hover:bg-slate-50 hover:text-slate-950 focus:ring-slate-300',
        'accent' => 'bg-orange-500 text-white shadow-sm hover:bg-orange-400 focus:ring-orange-400',
        'success' => 'bg-emerald-600 text-white shadow-sm hover:bg-emerald-500 focus:ring-emerald-500',
        'warning' => 'bg-amber-500 text-slate-950 shadow-sm hover:bg-amber-400 focus:ring-amber-400',
        'danger' => 'bg-rose-600 text-white shadow-sm hover:bg-rose-500 focus:ring-rose-500',
        'ghost' => 'text-slate-600 hover:bg-slate-100 hover:text-slate-950 focus:ring-slate-300',
    ];

    $classes = $base . ' ' . ($variants[$variant] ?? $variants['primary']) . ' ' . ($sizes[$size] ?? $sizes['md']);
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
    </button>
@endif
