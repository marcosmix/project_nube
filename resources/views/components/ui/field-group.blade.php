@props([
    'label' => null,
    'description' => null,
])

<div {{ $attributes->merge(['class' => 'space-y-1.5']) }}>
    @if ($label)
        <x-ui.label>{{ $label }}</x-ui.label>
    @endif

    {{ $slot }}

    @if ($description)
        <p class="text-xs text-slate-500">{{ $description }}</p>
    @endif
</div>
