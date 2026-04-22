<x-app-layout>
    {{-- <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-foreground">
            Cobros
        </h2>
    </x-slot> --}}

    <div class="py-12">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            <div class="rounded-2xl border border-border bg-card p-8 text-card-foreground shadow-sm">


                <div class="space-y-6">
                    <div>
                        <h1 class="text-2xl font-semibold text-slate-900">Nuevo flujo de cobro</h1>
                        <p class="text-sm text-slate-500">Seleccioná un proyecto y configurá el flujo inicial.</p>
                    </div>

                    <livewire:cobros.forms.create-flow-form />
                </div>


            </div>
        </div>
    </div>
</x-app-layout>
