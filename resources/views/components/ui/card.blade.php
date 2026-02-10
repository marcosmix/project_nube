@props(['class' => ''])

<div {{ $attributes->merge([
    'class' => "bg-card text-card-foreground flex flex-col gap-6 rounded-xl border $class"
]) }}>
    {{ $slot }}
</div>