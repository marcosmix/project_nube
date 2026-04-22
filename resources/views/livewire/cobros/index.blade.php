<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-semibold text-slate-900">Cobros</h1>
        <p class="text-sm text-slate-500">Gestioná flujos, cuotas y pagos.</p>
    </div>
    <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">

        <div class="flex flex-col gap-3 md:flex-row md:items-end">
            <div>
                <label class="mb-1 block text-xs font-medium text-slate-600">Buscar</label>
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cliente o proyecto..."
                    class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm shadow-sm focus:border-slate-400 focus:outline-none">
            </div>

            <div>
                <label class="mb-1 block text-xs font-medium text-slate-600">Estado</label>
                <select wire:model.live="status"
                    class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm shadow-sm focus:border-slate-400 focus:outline-none">
                    <option value="">Todos</option>
                    <option value="draft">Borrador</option>
                    <option value="active">Activo</option>
                    <option value="completed">Completado</option>
                    <option value="cancelled">Cancelado</option>
                </select>
            </div>
            <div>
                <a href="{{ route('cobros.create') }}"
                    class="inline-flex rounded-xl bg-slate-900 px-4 py-2 text-sm font-medium text-black hover:bg-slate-800">
                    Nuevo flujo
                </a>
            </div>

        </div>

    </div>

    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <table class="min-w-full divide-y divide-slate-200">
            <thead class="bg-slate-50">
                <tr class="text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                    <th class="px-4 py-3">Proyecto</th>
                    <th class="px-4 py-3">Cliente</th>
                    <th class="px-4 py-3">Monto total</th>
                    <th class="px-4 py-3">Cuotas</th>
                    <th class="px-4 py-3">Pagadas</th>
                    <th class="px-4 py-3">Pendiente</th>
                    <th class="px-4 py-3">Estado</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 bg-white text-sm text-slate-700">
                @forelse ($flows as $flow)
                    <tr class="hover:bg-slate-50/70">
                        <td class="px-4 py-3">{{ $flow->project?->name }}</td>
                        <td class="px-4 py-3">
                            {{ $flow->project?->client?->contact?->organization ??
                                trim(
                                    ($flow->project?->client?->contact?->first_name ?? '') .
                                        ' ' .
                                        ($flow->project?->client?->contact?->last_name ?? ''),
                                ) }}
                        </td>
                        <td class="px-4 py-3">${{ number_format((float) $flow->total_amount, 2, ',', '.') }}</td>
                        <td class="px-4 py-3">{{ $flow->installments_count }}</td>
                        <td class="px-4 py-3">{{ $flow->paid_installments_count }}/{{ $flow->installments_count }}</td>
                        <td class="px-4 py-3">${{ number_format((float) $flow->total_balance, 2, ',', '.') }}</td>
                        <td class="px-4 py-3">
                            <span
                                class="inline-flex rounded-full bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-700">
                                {{ $flow->status->label() }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('cobros.show', $flow) }}"
                                class="inline-flex rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-50">
                                Ver detalle
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-4 py-8 text-center text-sm text-slate-500">
                            No hay flujos de cobro para mostrar.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div>
        {{ $flows->links() }}
    </div>
</div>
