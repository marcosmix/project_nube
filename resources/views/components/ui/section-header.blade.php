@props([
    'title',
    'description' => null,
    'eyebrow' => null,
])

<div {{ $attributes->merge(['class' => 'flex flex-col gap-4 border-b border-slate-200/80 pb-6 md:flex-row md:items-end md:justify-between']) }}>
    <div class="max-w-3xl space-y-2">
        @if ($eyebrow)
            <p class="text-xs font-semibold uppercase tracking-[0.22em] text-indigo-600">{{ $eyebrow }}</p>
        @endif

        <div class="space-y-2">
            <h1 class="text-3xl font-semibold tracking-tight text-slate-950">{{ $title }}</h1>

            @if ($description)
                <p class="text-sm leading-6 text-slate-500 sm:text-base">{{ $description }}</p>
            @endif
        </div>
    </div>

    @isset($actions)
        <div class="flex shrink-0 flex-wrap items-center gap-3">
            {{ $actions }}
        </div>
    @endisset
</div>
