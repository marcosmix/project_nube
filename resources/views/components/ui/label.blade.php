@props([
    'required' => false,
    'hint' => null,
])

<label {{ $attributes->merge(['class' => 'mb-2 inline-flex items-center gap-2 text-sm font-medium text-slate-700']) }}>
    <span>{{ $slot }}</span>

    @if ($required)
        <span class="text-rose-500">*</span>
    @endif

    @if ($hint)
        <span class="text-xs font-normal text-slate-400">{{ $hint }}</span>
    @endif
</label>
