@props([
    'name',
    'show' => false,
    'maxWidth' => '2xl',
    'title' => null,
    'description' => null,
])

@php
    $maxWidths = [
        'sm' => 'sm:max-w-sm',
        'md' => 'sm:max-w-md',
        'lg' => 'sm:max-w-lg',
        'xl' => 'sm:max-w-xl',
        '2xl' => 'sm:max-w-2xl',
        '3xl' => 'sm:max-w-3xl',
    ];

    $panelWidth = $maxWidths[$maxWidth] ?? $maxWidths['2xl'];
@endphp

<div
    x-data="{
        show: @js($show),
        focusables() {
            let selector = 'a, button, input:not([type=\'hidden\']), textarea, select, details, [tabindex]:not([tabindex=\'-1\'])'
            return [...$el.querySelectorAll(selector)].filter((el) => ! el.hasAttribute('disabled'))
        },
        firstFocusable() { return this.focusables()[0] },
        lastFocusable() { return this.focusables().slice(-1)[0] },
        nextFocusable() { return this.focusables()[this.nextFocusableIndex()] || this.firstFocusable() },
        prevFocusable() { return this.focusables()[this.prevFocusableIndex()] || this.lastFocusable() },
        nextFocusableIndex() { return (this.focusables().indexOf(document.activeElement) + 1) % (this.focusables().length + 1) },
        prevFocusableIndex() { return Math.max(0, this.focusables().indexOf(document.activeElement)) - 1 },
    }"
    x-init="$watch('show', value => document.body.classList.toggle('overflow-y-hidden', value))"
    x-on:open-modal.window="$event.detail == '{{ $name }}' ? show = true : null"
    x-on:close-modal.window="$event.detail == '{{ $name }}' ? show = false : null"
    x-on:close.stop="show = false"
    x-on:keydown.escape.window="show = false"
    x-on:keydown.tab.prevent="$event.shiftKey || nextFocusable().focus()"
    x-on:keydown.shift.tab.prevent="prevFocusable().focus()"
    x-show="show"
    x-cloak
    class="fixed inset-0 z-50 overflow-y-auto px-4 py-6 sm:px-6"
>
    <div
        x-show="show"
        class="fixed inset-0 bg-slate-950/45 backdrop-blur-sm"
        x-on:click="show = false"
        x-transition:enter="ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
    ></div>

    <div class="relative flex min-h-full items-center justify-center">
        <div
            x-show="show"
            class="relative w-full overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-2xl ring-1 ring-slate-200/70 {{ $panelWidth }}"
            x-transition:enter="ease-out duration-200"
            x-transition:enter-start="opacity-0 translate-y-4 sm:scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
            x-transition:leave="ease-in duration-150"
            x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
            x-transition:leave-end="opacity-0 translate-y-4 sm:scale-95"
        >
            @if ($title || $description || isset($header))
                <div class="border-b border-slate-200 px-6 py-5">
                    @isset($header)
                        {{ $header }}
                    @else
                        @if ($title)
                            <h2 class="text-lg font-semibold text-slate-950">{{ $title }}</h2>
                        @endif

                        @if ($description)
                            <p class="mt-1 text-sm text-slate-500">{{ $description }}</p>
                        @endif
                    @endisset
                </div>
            @endif

            <div class="px-6 py-6">
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
