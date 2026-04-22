<div class="space-y-6">
    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
            <div class="space-y-1">
                <h1 class="text-2xl font-semibold text-slate-900">Flujo de cobro</h1>
                <p class="text-sm text-slate-500">Proyecto: {{ $flow->project?->name }}</p>
                <p class="text-sm text-slate-500">
                    Cliente:
                    {{ $flow->project?->client?->contact?->organization ??
                        trim(
                            ($flow->project?->client?->contact?->first_name ?? '') .
                                ' ' .
                                ($flow->project?->client?->contact?->last_name ?? ''),
                        ) }}
                </p>
            </div>

            <div class="grid grid-cols-2 gap-3 md:grid-cols-5">
                <div class="rounded-xl border border-slate-200 p-4">
                    <div class="text-xs text-slate-500">Monto total</div>
                    <div class="mt-1 text-lg font-semibold">
                        ${{ number_format((float) $flow->total_amount, 2, ',', '.') }}</div>
                </div>
                <div class="rounded-xl border border-slate-200 p-4">
                    <div class="text-xs text-slate-500">Total pagado</div>
                    <div class="mt-1 text-lg font-semibold">${{ number_format((float) $flow->total_paid, 2, ',', '.') }}
                    </div>
                </div>
                <div class="rounded-xl border border-slate-200 p-4">
                    <div class="text-xs text-slate-500">Pendiente</div>
                    <div class="mt-1 text-lg font-semibold">
                        ${{ number_format((float) $flow->total_balance, 2, ',', '.') }}</div>
                </div>
                <div class="rounded-xl border border-slate-200 p-4">
                    <div class="text-xs text-slate-500">Cuotas pagadas</div>
                    <div class="mt-1 text-lg font-semibold">
                        {{ $flow->paid_installments_count }}/{{ $flow->installments_count }}</div>
                </div>
                <div class="rounded-xl border border-slate-200 p-4">
                    <div class="text-xs text-slate-500">Estado</div>
                    <div class="mt-1 text-lg font-semibold">{{ $flow->status->label() }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="lg:col-span-2">
            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr class="text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                            <th class="px-4 py-3">Cuota</th>
                            <th class="px-4 py-3">Vencimiento</th>
                            <th class="px-4 py-3">Monto</th>
                            <th class="px-4 py-3">Pagado</th>
                            <th class="px-4 py-3">Saldo</th>
                            <th class="px-4 py-3">Estado</th>
                            <th class="px-4 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white text-sm text-slate-700">
                        @foreach ($flow->installments as $installment)
                            <tr class="hover:bg-slate-50/70">
                                <td class="px-4 py-3">#{{ $installment->number }}</td>
                                <td class="px-4 py-3">{{ $installment->due_date?->format('d/m/Y') }}</td>
                                <td class="px-4 py-3">${{ number_format((float) $installment->amount, 2, ',', '.') }}
                                </td>
                                <td class="px-4 py-3">
                                    ${{ number_format((float) $installment->paid_amount, 2, ',', '.') }}</td>
                                <td class="px-4 py-3">
                                    ${{ number_format((float) $installment->balance_due, 2, ',', '.') }}</td>
                                <td class="px-4 py-3">{{ $installment->status->label() }}</td>
                                <td class="px-4 py-3 text-right">
                                    <button wire:click="selectInstallment({{ $installment->id }})"
                                        class="inline-flex rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-50">
                                        Ver
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            @if ($selectedInstallment)
                <div class="space-y-4">
                    <div>
                        <h2 class="text-lg font-semibold text-slate-900">Cuota #{{ $selectedInstallment->number }}</h2>
                        <p class="text-sm text-slate-500">Detalle e historial</p>
                    </div>


                    <div class="space-y-2 text-sm">
                        <div><span class="font-medium">Vence:</span>
                            {{ $selectedInstallment->due_date?->format('d/m/Y') }}</div>
                        <div><span class="font-medium">Monto:</span>
                            ${{ number_format((float) $selectedInstallment->amount, 2, ',', '.') }}</div>
                        <div><span class="font-medium">Pagado:</span>
                            ${{ number_format((float) $selectedInstallment->paid_amount, 2, ',', '.') }}</div>
                        <div><span class="font-medium">Saldo:</span>
                            ${{ number_format((float) $selectedInstallment->balance_due, 2, ',', '.') }}</div>
                        <div><span class="font-medium">Estado:</span> {{ $selectedInstallment->status->label() }}</div>
                    </div>

                    <div class="border-t border-slate-200 pt-4">
                        <h3 class="mb-2 text-sm font-semibold text-slate-800">Historial</h3>
                        <div class="space-y-2">
                            @forelse ($selectedInstallment->statusLogs as $log)
                                <div class="rounded-lg bg-slate-50 p-3 text-xs text-slate-600">
                                    <div>{{ $log->from_status ?: '—' }} → {{ $log->to_status }}</div>
                                    <div>{{ $log->changed_at?->format('d/m/Y H:i') }}</div>
                                </div>
                            @empty
                                <p class="text-xs text-slate-500">Sin movimientos todavía.</p>
                            @endforelse
                        </div>

                    </div>
                    {{-- regsitrar pagos --}}
  <livewire:cobros.forms.register-payment-form :installment="$selectedInstallment" :key="'register-payment-' .
                    $selectedInstallment->id .
                    '-' .
                    $selectedInstallment->updated_at?->timestamp" />
                </div>
                {{-- Editar cuotas --}}
                <livewire:cobros.forms.edit-future-installments-form
    :installment="$selectedInstallment"
    :key="'edit-future-installments-'.$selectedInstallment->id.'-'.$selectedInstallment->updated_at?->timestamp"
/>
            @else
                <div class="text-sm text-slate-500">
                    Seleccioná una cuota para ver su detalle.
                </div>
              
            @endif
        </div>
    </div>
</div>
