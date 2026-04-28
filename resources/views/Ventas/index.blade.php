<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-xl font-semibold leading-tight text-foreground">Ventas</h2>
            <p class="text-sm text-slate-500">Gestioná oportunidades comerciales sin tocar todavía el flujo legacy de Proyectos.</p>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            <div class="rounded-2xl border border-border bg-card p-8 text-card-foreground shadow-sm">
                <livewire:sales.index />
            </div>
        </div>
    </div>
</x-app-layout>
