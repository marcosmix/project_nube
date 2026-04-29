@props(['active'])

@php
    $classes = ($active ?? false)
        ? 'inline-flex items-center rounded-full border border-white/15 bg-white/12 px-4 py-2 text-sm font-medium text-white shadow-sm backdrop-blur-sm transition'
        : 'inline-flex items-center rounded-full border border-transparent px-4 py-2 text-sm font-medium text-slate-300 transition hover:border-white/10 hover:bg-white/6 hover:text-white focus:outline-none focus:ring-2 focus:ring-indigo-400/60';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
