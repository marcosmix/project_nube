@props([
    'invalid' => false,
])

@php
    $base = 'block w-full rounded-2xl border bg-white px-4 py-2.5 pr-10 text-sm text-slate-950 shadow-sm transition focus:outline-none focus:ring-2 focus:ring-offset-0';

    $state = $invalid
        ? 'border-rose-300 focus:border-rose-400 focus:ring-rose-200'
        : 'border-slate-200 focus:border-indigo-400 focus:ring-indigo-100';
@endphp

<select {{ $attributes->merge(['class' => $base . ' ' . $state]) }}>
    {{ $slot }}
</select>
