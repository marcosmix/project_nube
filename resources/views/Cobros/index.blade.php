<x-app-layout>
    <x-ui.page-container>
        <x-ui.section-header
            title="Cobros"
            description="Gestiona flujos, cuotas y pagos con una interfaz mas clara, compacta y lista para escalar al resto del ERP."
            eyebrow="ERP Nube"
        >
            <x-slot:actions>
                <div class="flex flex-col gap-3 sm:flex-row">
                    <x-ui.button href="{{ route('cobros.create') }}" variant="accent">
                        Nuevo flujo
                    </x-ui.button>
                    <x-ui.button href="{{ route('cobros.scheduled.create') }}" variant="primary">
                        Nuevo Cobro
                    </x-ui.button>
                </div>
            </x-slot:actions>
        </x-ui.section-header>

        <livewire:cobros.index />
    </x-ui.page-container>
</x-app-layout>
