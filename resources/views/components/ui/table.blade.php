@props([])

<div {{ $attributes->merge(['class' => 'overflow-x-auto']) }}>
    <table class="min-w-full divide-y divide-slate-200 text-sm text-slate-700">
        @isset($head)
            <thead class="bg-slate-50/90 text-left text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">
                <tr>
                    {{ $head }}
                </tr>
            </thead>
        @endisset

        <tbody class="divide-y divide-slate-100 bg-white">
            {{ $slot }}
        </tbody>
    </table>
</div>
