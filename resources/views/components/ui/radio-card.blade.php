@props([
    'checked' => false,
])

@php
    $classes = $checked
        ? 'border-sky-300 bg-sky-50 text-sky-950 ring-1 ring-sky-200'
        : 'border-slate-200 bg-white text-slate-900 hover:border-slate-300';
@endphp

<label {{ $attributes->merge(['class' => 'block cursor-pointer rounded-2xl border px-4 py-4 transition ' . $classes]) }}>
    {{ $slot }}
</label>
