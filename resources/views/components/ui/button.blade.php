@props([
    'variant' => 'default', // default | outline | ghost
    'size' => 'md',         // sm | md | lg
])

@php
$base = "inline-flex items-center justify-center rounded-xl font-medium transition focus:outline-none focus:ring-2 focus:ring-ring/50 disabled:opacity-50 disabled:pointer-events-none";

$sizes = [
    'sm' => "text-sm px-3 py-2",
    'md' => "text-sm px-4 py-2.5",
    'lg' => "text-base px-6 py-3",
];

$variants = [
    'default' => "bg-primary text-primary-foreground hover:opacity-90",
    'outline' => "border border-border bg-background hover:bg-muted",
    'ghost'   => "hover:bg-muted",
];
@endphp

<button {{ $attributes->merge(['class' => "$base {$sizes[$size]} {$variants[$variant]}"]) }}>
    {{ $slot }}
</button>