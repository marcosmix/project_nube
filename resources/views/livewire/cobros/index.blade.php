@php
    $visibleFlows = $flows->getCollection();
    $visibleAmount = $visibleFlows->sum(fn ($flow) => (float) $flow->total_amount);
    $visibleBalance = $visibleFlows->sum(fn ($flow) => (float) $flow->total_balance);
    $statusBadgeVariants = [
        'draft' => 'warning',
        'active' => 'primary',
        'completed' => 'success',
        'cancelled' => 'danger',
    ];
    $scheduledStatusBadgeVariants = [
        'active' => 'primary',
        'paused' => 'warning',
        'cancelled' => 'danger',
    ];
@endphp

<div class="space-y-6">
    <div class="grid gap-4 md:grid-cols-3">
        <x-ui.stat-card
            label="Resultados"
            :value="number_format($flows->total(), 0, ',', '.')"
            hint="Flujos que coinciden con los filtros actuales."
            tone="primary"
        />

        <x-ui.stat-card
            label="Monto visible"
            :value="'$' . number_format($visibleAmount, 2, ',', '.')"
            hint="Suma de los flujos cargados en esta pagina."
            tone="accent"
        />

        <x-ui.stat-card
            label="Saldo pendiente visible"
            :value="'$' . number_format($visibleBalance, 2, ',', '.')"
            hint="Ayuda a priorizar seguimientos desde la grilla."
            tone="success"
        />
    </div>

    <div class="rounded-3xl border border-slate-200 bg-white p-2 shadow-sm ring-1 ring-slate-200/70">
        <div class="grid gap-2 sm:grid-cols-2">
            <button type="button" wire:click="setTab('flows')" class="rounded-2xl px-4 py-3 text-left transition {{ $tab === 'flows' ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-950' }}">
                <div class="text-sm font-semibold">Flujos de cobro</div>
                <div class="mt-1 text-xs {{ $tab === 'flows' ? 'text-indigo-100' : 'text-slate-500' }}">Cuotas y pagos vinculados a operaciones.</div>
            </button>
            <button type="button" wire:click="setTab('scheduled')" class="rounded-2xl px-4 py-3 text-left transition {{ $tab === 'scheduled' ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-950' }}">
                <div class="text-sm font-semibold">Cobros programados</div>
                <div class="mt-1 text-xs {{ $tab === 'scheduled' ? 'text-indigo-100' : 'text-slate-500' }}">Cobros recurrentes por cliente.</div>
            </button>
        </div>
    </div>

    @if($tab === 'flows')
        <x-ui.card class="space-y-5 bg-slate-50/70">
            <div class="flex flex-col gap-2 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <h2 class="text-lg font-semibold text-slate-950">Vista operativa</h2>
                    <p class="text-sm text-slate-500">Filtra rapido y entra al detalle sin perder contexto.</p>
                </div>

                <p class="text-sm text-slate-500">{{ $flows->count() }} resultados en esta pagina</p>
            </div>

            <div class="grid gap-4 md:grid-cols-[minmax(0,1.6fr)_220px]">
                <div>
                    <x-ui.label for="cobros-search">Buscar</x-ui.label>
                    <x-ui.input id="cobros-search" type="text" wire:model.live.debounce.300ms="search" placeholder="Cliente o proyecto..." />
                </div>

                <div>
                    <x-ui.label for="cobros-status">Estado</x-ui.label>
                    <x-ui.select id="cobros-status" wire:model.live="status">
                        <option value="">Todos</option>
                        <option value="draft">Borrador</option>
                        <option value="active">Activo</option>
                        <option value="completed">Completado</option>
                        <option value="cancelled">Cancelado</option>
                    </x-ui.select>
                </div>
            </div>
        </x-ui.card>

        @if ($flows->count())
            <x-ui.card padding="none" class="overflow-hidden">
                <x-ui.table>
                    <x-slot:head>
                        <th class="px-5 py-4">Proyecto</th>
                        <th class="px-5 py-4">Cliente</th>
                        <th class="px-5 py-4">Monto total</th>
                        <th class="px-5 py-4">Cuotas</th>
                        <th class="px-5 py-4">Pagadas</th>
                        <th class="px-5 py-4">Pendiente</th>
                        <th class="px-5 py-4">Estado</th>
                        <th class="px-5 py-4 text-right">Acciones</th>
                    </x-slot:head>

                    @foreach ($flows as $flow)
                        @php
                            $clientName = $flow->project?->client?->organization_name
                                ?? trim(($flow->project?->client?->contact?->first_name ?? '') . ' ' . ($flow->project?->client?->contact?->last_name ?? ''));

                            $statusKey = $flow->status->value ?? null;
                        @endphp

                        <tr class="align-top transition hover:bg-slate-50/80">
                            <td class="px-5 py-4">
                                <div class="font-medium text-slate-950">{{ $flow->project?->name }}</div>
                                <div class="mt-1 text-xs text-slate-500">Flujo #{{ $flow->id }}</div>
                            </td>

                            <td class="px-5 py-4 text-slate-600">{{ $clientName ?: 'Sin cliente asociado' }}</td>
                            <td class="px-5 py-4 font-medium text-slate-950">${{ number_format((float) $flow->total_amount, 2, ',', '.') }}</td>
                            <td class="px-5 py-4 text-slate-600">{{ $flow->installments_count }}</td>
                            <td class="px-5 py-4 text-slate-600">{{ $flow->paid_installments_count }}/{{ $flow->installments_count }}</td>
                            <td class="px-5 py-4 font-medium text-slate-950">${{ number_format((float) $flow->total_balance, 2, ',', '.') }}</td>
                            <td class="px-5 py-4">
                                <x-ui.badge :variant="$statusBadgeVariants[$statusKey] ?? 'neutral'">
                                    {{ $flow->status->label() }}
                                </x-ui.badge>
                            </td>
                            <td class="px-5 py-4 text-right">
                                <x-ui.button href="{{ route('cobros.show', $flow) }}" variant="secondary" size="sm">Ver detalle</x-ui.button>
                            </td>
                        </tr>
                    @endforeach
                </x-ui.table>
            </x-ui.card>
        @else
            <x-ui.empty-state title="No hay flujos para mostrar" description="Ajusta los filtros o crea un nuevo flujo para empezar a gestionar cobros desde este modulo.">
                <x-ui.button href="{{ route('cobros.create') }}" variant="accent">Crear flujo</x-ui.button>
            </x-ui.empty-state>
        @endif

        <div class="pt-1">
            {{ $flows->links() }}
        </div>
    @else
        <x-ui.card class="space-y-5 bg-slate-50/70">
            <div class="flex flex-col gap-2 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <h2 class="text-lg font-semibold text-slate-950">Cobros programados</h2>
                    <p class="text-sm text-slate-500">Seguimiento de cobros recurrentes por cliente.</p>
                </div>
                <x-ui.button href="{{ route('cobros.scheduled.create') }}" variant="primary">Nuevo Cobro</x-ui.button>
            </div>

            <div>
                <x-ui.label for="scheduled-search">Buscar</x-ui.label>
                <x-ui.input id="scheduled-search" wire:model.live.debounce.300ms="scheduledSearch" placeholder="Referencia, cliente o detalle..." />
            </div>
        </x-ui.card>

        @if($scheduledCharges->count())
            <div class="grid gap-4 lg:grid-cols-2">
                @foreach($scheduledCharges as $scheduledCharge)
                    @php
                        $statusKey = $scheduledCharge->status->value ?? null;
                        $attachment = $scheduledCharge->attachments->first();
                    @endphp
                    <x-ui.card class="transition hover:-translate-y-0.5 hover:shadow-md">
                        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                            <div>
                                <div class="text-xs font-semibold uppercase tracking-[0.18em] text-indigo-600">{{ $scheduledCharge->frequency->label() }}</div>
                                <h3 class="mt-2 text-lg font-semibold text-slate-950">{{ $scheduledCharge->reference_name }}</h3>
                                <p class="mt-1 text-sm text-slate-500">{{ $scheduledCharge->client?->organization_name ?? 'Sin cliente' }}</p>
                            </div>
                            <x-ui.badge :variant="$scheduledStatusBadgeVariants[$statusKey] ?? 'neutral'">{{ $scheduledCharge->status->label() }}</x-ui.badge>
                        </div>

                        <div class="mt-5 grid gap-3 sm:grid-cols-3">
                            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                                <div class="text-[11px] font-medium uppercase tracking-[0.18em] text-slate-500">Monto</div>
                                <div class="mt-2 text-sm font-semibold text-slate-950">${{ number_format((float) $scheduledCharge->amount, 2, ',', '.') }}</div>
                            </div>
                            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                                <div class="text-[11px] font-medium uppercase tracking-[0.18em] text-slate-500">Fecha</div>
                                <div class="mt-2 text-sm font-semibold text-slate-950">{{ $scheduledCharge->charge_date?->format('d/m/Y') }}</div>
                            </div>
                            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                                <div class="text-[11px] font-medium uppercase tracking-[0.18em] text-slate-500">Archivo</div>
                                <div class="mt-2 text-sm font-semibold text-slate-950">
                                    @if($attachment)
                                        <a href="{{ route('attachments.download', $attachment) }}" class="text-indigo-600 hover:text-indigo-700">Descargar</a>
                                    @else
                                        Sin archivo
                                    @endif
                                </div>
                            </div>
                        </div>

                        @if($scheduledCharge->detail)
                            <p class="mt-4 rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-600">{{ $scheduledCharge->detail }}</p>
                        @endif
                    </x-ui.card>
                @endforeach
            </div>
        @else
            <x-ui.empty-state title="No hay cobros programados" description="Crea el primero para registrar cobros recurrentes por cliente.">
                <x-ui.button href="{{ route('cobros.scheduled.create') }}" variant="primary">Nuevo Cobro</x-ui.button>
            </x-ui.empty-state>
        @endif

        <div class="pt-1">
            {{ $scheduledCharges->links() }}
        </div>
    @endif
</div>
