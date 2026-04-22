<x-app-layout>
    {{-- <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-foreground">
            Cobros
        </h2>
    </x-slot> --}}

    <div class="py-12">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            <div class="rounded-2xl border border-border bg-card p-8 text-card-foreground shadow-sm">
                
              
                    <livewire:cobros.show :payment-flow="$paymentFlow" />
              
                
            </div>
        </div>
    </div>
</x-app-layout>
