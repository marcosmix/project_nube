@props([
    'title',
    'description' => null,
])

<div {{ $attributes->merge(['class' => 'rounded-3xl border border-dashed border-slate-300 bg-white/80 px-6 py-12 text-center shadow-sm']) }}>
    <div class="mx-auto max-w-md">
        @isset($icon)
            <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-100 text-slate-500">
                {{ $icon }}
            </div>
        @endisset

        <h3 class="text-lg font-semibold text-slate-950">{{ $title }}</h3>

        @if ($description)
            <p class="mt-2 text-sm leading-6 text-slate-500">{{ $description }}</p>
        @endif

        @if (trim($slot))
            <div class="mt-6 flex justify-center">
                {{ $slot }}
            </div>
        @endif
    </div>
</div>
