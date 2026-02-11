<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-foreground">
            Clientes
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            <div class="rounded-2xl border border-border bg-card p-8 text-card-foreground shadow-sm">
                
                <x-app-layout>
                    <x-slot name="header">Developers</x-slot>
                    <livewire:developers.developers-index />
                </x-app-layout>
                
            </div>
        </div>
    </div>
</x-app-layout>
