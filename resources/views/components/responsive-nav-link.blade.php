@props(['active'])

@php
    $classes = ($active ?? false)
        ? 'block w-full rounded-2xl border border-white/15 bg-white/10 px-4 py-3 text-start text-sm font-medium text-white shadow-sm transition'
        : 'block w-full rounded-2xl border border-transparent px-4 py-3 text-start text-sm font-medium text-slate-300 transition hover:border-white/10 hover:bg-white/5 hover:text-white focus:outline-none focus:ring-2 focus:ring-indigo-400/60';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
