@props(['class' => ''])

<input {{ $attributes->merge([
    'class' => "w-full rounded-xl border border-border bg-input-background px-4 py-2.5 text-foreground placeholder:text-muted-foreground focus:ring-2 focus:ring-ring/50 $class"
]) }} />
