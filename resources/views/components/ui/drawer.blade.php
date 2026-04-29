@props([
    'show' => false,
    'title' => null,
    'description' => null,
    'side' => 'right',
    'width' => 'xl',
])

@php
    $widths = [
        'md' => 'max-w-md',
        'lg' => 'max-w-lg',
        'xl' => 'max-w-xl',
        '2xl' => 'max-w-2xl',
    ];

    $positions = [
        'left' => 'justify-start',
        'right' => 'justify-end',
    ];

    $enterStart = $side === 'left' ? '-translate-x-full' : 'translate-x-full';
@endphp

<div
    x-data="{ open: @js($show) }"
    x-init="$watch('open', value => document.body.classList.toggle('overflow-y-hidden', value))"
    x-show="open"
    x-cloak
    class="fixed inset-0 z-50 overflow-hidden"
>
    <div class="absolute inset-0 bg-slate-950/50 backdrop-blur-sm" x-on:click="open = false"></div>

    <div class="relative flex min-h-full {{ $positions[$side] ?? $positions['right'] }}">
        <div
            class="flex min-h-screen w-full {{ $widths[$width] ?? $widths['xl'] }} flex-col border-l border-slate-200 bg-white shadow-2xl ring-1 ring-slate-200/70"
            x-show="open"
            x-transition:enter="transform transition ease-out duration-200"
            x-transition:enter-start="{{ $enterStart }}"
            x-transition:enter-end="translate-x-0"
            x-transition:leave="transform transition ease-in duration-150"
            x-transition:leave-start="translate-x-0"
            x-transition:leave-end="{{ $enterStart }}"
            x-on:click.stop
        >
            <div class="flex items-start justify-between gap-4 border-b border-slate-200 px-6 py-5">
                <div>
                    @if ($title)
                        <h2 class="text-lg font-semibold text-slate-950">{{ $title }}</h2>
                    @endif

                    @if ($description)
                        <p class="mt-1 text-sm text-slate-500">{{ $description }}</p>
                    @endif
                </div>

                <button
                    type="button"
                    x-on:click="open = false"
                    class="inline-flex h-10 w-10 items-center justify-center rounded-2xl border border-slate-200 text-slate-500 transition hover:bg-slate-50 hover:text-slate-700 focus:outline-none focus:ring-2 focus:ring-slate-200"
                    aria-label="Cerrar panel"
                >
                    <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd" d="M4.22 4.22a.75.75 0 0 1 1.06 0L10 8.94l4.72-4.72a.75.75 0 1 1 1.06 1.06L11.06 10l4.72 4.72a.75.75 0 1 1-1.06 1.06L10 11.06l-4.72 4.72a.75.75 0 0 1-1.06-1.06L8.94 10 4.22 5.28a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd" />
                    </svg>
                </button>
            </div>

            <div class="flex-1 overflow-y-auto px-6 py-6">
                {{ $slot }}
            </div>

            @isset($footer)
                <div class="border-t border-slate-200 bg-slate-50/70 px-6 py-4">
                    {{ $footer }}
                </div>
            @endisset
        </div>
    </div>
</div>
