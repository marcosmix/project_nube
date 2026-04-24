<div class="space-y-6" wire:keydown.escape.window="handleEscape">
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
                    <div class="mt-1 text-lg font-semibold text-slate-900">
                        ${{ number_format((float) $flow->total_amount, 2, ',', '.') }}
                    </div>
                </div>
                <div class="rounded-xl border border-slate-200 p-4">
                    <div class="text-xs text-slate-500">Total pagado</div>
                    <div class="mt-1 text-lg font-semibold text-slate-900">
                        ${{ number_format((float) $flow->total_paid, 2, ',', '.') }}
                    </div>
                </div>
                <div class="rounded-xl border border-slate-200 p-4">
                    <div class="text-xs text-slate-500">Pendiente</div>
                    <div class="mt-1 text-lg font-semibold text-slate-900">
                        ${{ number_format((float) $flow->total_balance, 2, ',', '.') }}
                    </div>
                </div>
                <div class="rounded-xl border border-slate-200 p-4">
                    <div class="text-xs text-slate-500">Cuotas pagadas</div>
                    <div class="mt-1 text-lg font-semibold text-slate-900">
                        {{ $flow->paid_installments_count }}/{{ $flow->installments_count }}
                    </div>
                </div>
                <div class="rounded-xl border border-slate-200 p-4">
                    <div class="text-xs text-slate-500">Estado</div>
                    <div class="mt-1 text-lg font-semibold text-slate-900">{{ $flow->status->label() }}</div>
                </div>
            </div>
        </div>
    </div>

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
                    <tr class="{{ $selectedInstallment?->id === $installment->id ? 'bg-blue-50/60' : 'hover:bg-slate-50/70' }}">
                        <td class="px-4 py-3 font-medium text-slate-900">#{{ $installment->number }}</td>
                        <td class="px-4 py-3">{{ $installment->due_date?->format('d/m/Y') }}</td>
                        <td class="px-4 py-3">${{ number_format((float) $installment->amount, 2, ',', '.') }}</td>
                        <td class="px-4 py-3">${{ number_format((float) $installment->paid_amount, 2, ',', '.') }}</td>
                        <td class="px-4 py-3">${{ number_format((float) $installment->balance_due, 2, ',', '.') }}</td>
                        <td class="px-4 py-3">
                            <span class="inline-flex items-center rounded-full border px-2.5 py-1 text-xs font-medium {{ $this->installmentStatusBadgeClasses($installment) }}">
                                {{ $installment->status->label() }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <div class="flex items-center justify-end gap-2">
                                @if ($this->canRegisterPayment($installment))
                                    <button
                                        type="button"
                                        wire:click="openRegisterPaymentModal({{ $installment->id }})"
                                        class="inline-flex items-center rounded-lg border border-blue-200 bg-blue-50 px-3 py-1.5 text-xs font-medium text-blue-700 transition hover:border-blue-300 hover:bg-blue-100 focus:outline-none focus:ring-2 focus:ring-blue-200"
                                    >
                                        Agregar pago
                                    </button>
                                @endif

                                <button
                                    type="button"
                                    wire:click="selectInstallment({{ $installment->id }})"
                                    class="inline-flex items-center rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-medium text-slate-700 transition hover:border-slate-300 hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-slate-200"
                                >
                                    Ver
                                </button>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    @if ($isInstallmentDrawerOpen && $selectedInstallment)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="installment-drawer-title" role="dialog" aria-modal="true">
            <button
                type="button"
                wire:click="closeInstallmentDrawer"
                class="fixed inset-0 z-0 bg-slate-950/35 backdrop-blur-[1px]"
                aria-label="Cerrar detalle de cuota"
            ></button>

            <div class="relative z-10 flex min-h-full justify-end">
                <div class="w-full max-w-xl min-h-screen border-l border-slate-200 bg-white shadow-2xl" wire:click.stop>
                    <div class="flex min-h-screen flex-col">
                        <div class="sticky top-0 z-10 shrink-0 flex items-start justify-between gap-4 border-b border-slate-200 bg-white px-6 py-5">
                        <div>
                            <h2 id="installment-drawer-title" class="text-2xl font-semibold text-slate-900">
                                Cuota #{{ $selectedInstallment->number }}
                            </h2>
                            <p class="mt-1 text-sm text-slate-500">
                                {{ $isVoidPaymentModalOpen ? 'Anulacion de pago' : 'Detalle e historial' }}
                            </p>
                        </div>

                        <button
                            type="button"
                            wire:click="closeInstallmentDrawer"
                            class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-slate-200 text-slate-500 transition hover:bg-slate-50 hover:text-slate-700 focus:outline-none focus:ring-2 focus:ring-slate-200"
                            aria-label="Cerrar panel lateral"
                        >
                            <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path
                                    fill-rule="evenodd"
                                    d="M4.22 4.22a.75.75 0 0 1 1.06 0L10 8.94l4.72-4.72a.75.75 0 1 1 1.06 1.06L11.06 10l4.72 4.72a.75.75 0 1 1-1.06 1.06L10 11.06l-4.72 4.72a.75.75 0 0 1-1.06-1.06L8.94 10 4.22 5.28a.75.75 0 0 1 0-1.06Z"
                                    clip-rule="evenodd"
                                />
                            </svg>
                        </button>
                    </div>

                        <div class="flex-1 px-6 py-6">
                            <div class="space-y-6 pb-6">
                                @if ($isVoidPaymentModalOpen && $paymentBeingVoided)
                                    <section class="max-w-sm space-y-4">
                                        <div class="flex items-start gap-3 rounded-2xl border border-slate-200 bg-slate-50/80 p-4">
                                            <button
                                                type="button"
                                                wire:click="closeVoidPaymentModal"
                                                class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-500 transition hover:bg-slate-50 hover:text-slate-700 focus:outline-none focus:ring-2 focus:ring-slate-200"
                                                aria-label="Volver al historial"
                                            >
                                                <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                                    <path fill-rule="evenodd" d="M11.78 15.78a.75.75 0 0 1-1.06 0l-5.25-5.25a.75.75 0 0 1 0-1.06l5.25-5.25a.75.75 0 1 1 1.06 1.06L7.81 9.25H15a.75.75 0 0 1 0 1.5H7.81l3.97 3.97a.75.75 0 0 1 0 1.06Z" clip-rule="evenodd" />
                                                </svg>
                                            </button>

                                            <div>
                                                <h3 class="text-lg font-semibold text-slate-900">Anular pago</h3>
                                                <p class="mt-1 text-sm text-slate-500">Registrá el motivo sin salir del detalle de la cuota.</p>
                                            </div>
                                        </div>

                                        <div class="rounded-2xl border border-rose-100 bg-rose-50/70 px-4 py-4">
                                            <p class="text-xs font-medium uppercase tracking-[0.18em] text-rose-700">Detalle del pago</p>
                                            <p class="mt-2 text-base font-semibold text-slate-900">
                                                ${{ number_format((float) $paymentBeingVoided->amount, 2, ',', '.') }}
                                            </p>
                                            <p class="mt-1 text-sm text-slate-600">
                                                Registrado el {{ $paymentBeingVoided->paid_at?->format('d/m/Y H:i') ?? 'Sin fecha' }}
                                            </p>
                                        </div>

                                        <form wire:submit="voidPayment" class="space-y-4 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                                            <div>
                                                <label class="mb-1 block text-xs font-medium text-slate-600">Motivo de anulacion</label>
                                                <textarea
                                                    wire:model="voidReason"
                                                    rows="5"
                                                    class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm shadow-sm focus:border-slate-400 focus:outline-none"
                                                    placeholder="Indicá por qué se anula el pago"
                                                ></textarea>
                                                @error('voidReason')
                                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                                @enderror
                                            </div>

                                            <div class="flex flex-col-reverse gap-3">
                                                <button
                                                    type="button"
                                                    wire:click="closeVoidPaymentModal"
                                                    class="inline-flex items-center justify-center rounded-xl border border-slate-200 px-4 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-50"
                                                >
                                                    Volver
                                                </button>

                                                <button
                                                    type="submit"
                                                    wire:loading.attr="disabled"
                                                    class="inline-flex items-center justify-center rounded-xl bg-rose-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-rose-700 disabled:cursor-not-allowed disabled:opacity-60"
                                                >
                                                    <span wire:loading.remove wire:target="voidPayment">Confirmar anulacion</span>
                                                    <span wire:loading wire:target="voidPayment">Anulando...</span>
                                                </button>
                                            </div>
                                        </form>
                                    </section>
                                @else
                                    <section class="rounded-2xl border border-slate-200 bg-slate-50/80 p-5">
                                        <div class="mb-4 flex items-start justify-between gap-3">
                                            <div>
                                                <h3 class="text-sm font-semibold text-slate-900">Resumen de la cuota</h3>
                                                <p class="mt-1 text-sm text-slate-500">Estado actual y montos registrados.</p>
                                            </div>

                                            @if ($this->canRegisterPayment($selectedInstallment))
                                                <button
                                                    type="button"
                                                    wire:click="openRegisterPaymentModal({{ $selectedInstallment->id }})"
                                                    class="inline-flex items-center rounded-xl border border-blue-200 bg-blue-50 px-4 py-2 text-sm font-medium text-blue-700 transition hover:border-blue-300 hover:bg-blue-100 focus:outline-none focus:ring-2 focus:ring-blue-200"
                                                >
                                                    Agregar pago
                                                </button>
                                            @endif
                                        </div>

                                        <div class="grid gap-4 sm:grid-cols-2">
                                            <div>
                                                <div class="text-xs font-medium uppercase tracking-[0.18em] text-slate-500">Vence</div>
                                                <div class="mt-1 text-sm font-medium text-slate-900">
                                                    {{ $selectedInstallment->due_date?->format('d/m/Y') ?? 'Sin fecha' }}
                                                </div>
                                            </div>
                                            <div>
                                                <div class="text-xs font-medium uppercase tracking-[0.18em] text-slate-500">Monto</div>
                                                <div class="mt-1 text-sm font-medium text-slate-900">
                                                    ${{ number_format((float) $selectedInstallment->amount, 2, ',', '.') }}
                                                </div>
                                            </div>
                                            <div>
                                                <div class="text-xs font-medium uppercase tracking-[0.18em] text-slate-500">Pagado</div>
                                                <div class="mt-1 text-sm font-medium text-slate-900">
                                                    ${{ number_format((float) $selectedInstallment->paid_amount, 2, ',', '.') }}
                                                </div>
                                            </div>
                                            <div>
                                                <div class="text-xs font-medium uppercase tracking-[0.18em] text-slate-500">Saldo</div>
                                                <div class="mt-1 text-sm font-medium text-slate-900">
                                                    ${{ number_format((float) $selectedInstallment->balance_due, 2, ',', '.') }}
                                                </div>
                                            </div>
                                            <div class="sm:col-span-2">
                                                <div class="text-xs font-medium uppercase tracking-[0.18em] text-slate-500">Estado</div>
                                                <div class="mt-2">
                                                    <span class="inline-flex items-center rounded-full border px-2.5 py-1 text-xs font-medium {{ $this->installmentStatusBadgeClasses($selectedInstallment) }}">
                                                        {{ $selectedInstallment->status->label() }}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </section>

                                    <section class="space-y-3">
                                        <div>
                                            <h3 class="text-sm font-semibold text-slate-900">Historial</h3>
                                            <p class="mt-1 text-sm text-slate-500">Movimientos y cambios registrados sobre esta cuota.</p>
                                        </div>

                                        <div class="space-y-3">
                                            @forelse ($installmentTimeline as $item)
                                                <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                                                    <div class="flex items-start justify-between gap-3">
                                                         <div class="min-w-0 flex-1">
                                                             <p class="text-sm font-medium text-slate-900">{{ $item['title'] }}</p>
                                                             <p class="mt-1 text-sm text-slate-600">{{ $item['description'] }}</p>
                                                             @if (! empty($item['notes']))
                                                                 <p class="mt-2 text-xs text-slate-500">Observación: {{ $item['notes'] }}</p>
                                                             @endif

                                                             @if (! empty($item['receipts']))
                                                                 <div class="mt-4 border-t border-slate-100 pt-4">
                                                                     <div class="flex items-center gap-2">
                                                                         <span class="text-xs font-medium uppercase tracking-[0.18em] text-slate-500">
                                                                             Comprobante
                                                                         </span>
                                                                         <span class="rounded-full bg-slate-100 px-2 py-0.5 text-[11px] font-medium text-slate-600">
                                                                             {{ count($item['receipts']) }}
                                                                         </span>
                                                                     </div>

                                                                     <div class="mt-3 grid gap-3 sm:grid-cols-2">
                                                                         @foreach ($item['receipts'] as $receipt)
                                                                             <div class="overflow-hidden rounded-2xl border border-slate-200 bg-slate-50">
                                                                                 @if ($receipt['is_image'] && $receipt['preview_url'])
                                                                                     <a
                                                                                         href="{{ $receipt['preview_url'] }}"
                                                                                         target="_blank"
                                                                                         rel="noreferrer"
                                                                                         class="group block h-52 w-full overflow-hidden bg-slate-100"
                                                                                     >
                                                                                         <img
                                                                                             src="{{ $receipt['preview_url'] }}"
                                                                                             alt="Comprobante {{ $receipt['original_name'] }}"
                                                                                             class="h-full w-full object-cover object-top transition duration-200 group-hover:scale-[1.02]"
                                                                                         >
                                                                                     </a>
                                                                                 @else
                                                                                     <div class="flex h-52 items-center justify-center bg-slate-100 px-4 text-center">
                                                                                      <div class="space-y-3">
                                                                                          <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-rose-50 text-rose-600">
                                                                                              <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                                                                                  <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 3.75h6l4.5 4.5v12a1.5 1.5 0 0 1-1.5 1.5h-9A1.5 1.5 0 0 1 6 20.25v-15A1.5 1.5 0 0 1 7.5 3.75Z" />
                                                                                                  <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 3.75v4.5H18" />
                                                                                                  <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 15.75h7.5M8.25 12.75h7.5" />
                                                                                              </svg>
                                                                                          </div>
                                                                                          <div>
                                                                                              <p class="text-sm font-medium text-slate-700">Vista previa no disponible</p>
                                                                                              <p class="mt-1 text-xs text-slate-500">Descargá el archivo para verlo completo.</p>
                                                                                          </div>
                                                                                      </div>
                                                                                     </div>
                                                                                 @endif

                                                                                 <div class="space-y-3 p-3">
                                                                                     <div>
                                                                                         <p class="truncate text-sm font-medium text-slate-900" title="{{ $receipt['original_name'] }}">
                                                                                             {{ $receipt['original_name'] }}
                                                                                         </p>
                                                                                         <p class="mt-1 text-xs text-slate-500">
                                                                                             {{ $receipt['type_label'] }} · {{ $receipt['size_label'] }}
                                                                                         </p>
                                                                                     </div>

                                                                                     <div class="flex flex-wrap items-center gap-2">
                                                                                         @if ($receipt['is_image'] && $receipt['preview_url'])
                                                                                             <a
                                                                                                 href="{{ $receipt['preview_url'] }}"
                                                                                                 target="_blank"
                                                                                                 rel="noreferrer"
                                                                                                 class="inline-flex items-center rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-medium text-slate-700 transition hover:border-slate-300 hover:bg-slate-100"
                                                                                             >
                                                                                                 Ver
                                                                                             </a>
                                                                                         @endif

                                                                                         <a
                                                                                             href="{{ $receipt['download_url'] }}"
                                                                                             class="inline-flex items-center rounded-lg border border-blue-200 bg-blue-50 px-3 py-1.5 text-xs font-medium text-blue-700 transition hover:border-blue-300 hover:bg-blue-100"
                                                                                         >
                                                                                             Descargar
                                                                                         </a>
                                                                                     </div>
                                                                                 </div>
                                                                             </div>
                                                                         @endforeach
                                                                     </div>
                                                                 </div>
                                                             @endif
                                                         </div>
                                                         <div class="shrink-0 flex flex-col items-end gap-2">
                                                             <span
                                                                 class="mt-0.5 inline-flex rounded-full px-2.5 py-1 text-[11px] font-medium {{ $item['tone'] === 'emerald' ? 'bg-emerald-100 text-emerald-700' : ($item['tone'] === 'blue' ? 'bg-blue-100 text-blue-700' : 'bg-slate-100 text-slate-700') }}"
                                                             >
                                                                {{ $item['occurred_at']?->format('d/m/Y H:i') ?? 'Sin fecha' }}
                                                            </span>

                                                            @if (! empty($item['can_void']))
                                                                <button
                                                                    type="button"
                                                                    wire:click="openVoidPaymentModal({{ $item['payment_id'] }})"
                                                                    class="inline-flex items-center rounded-lg border border-rose-200 px-3 py-1.5 text-xs font-medium text-rose-700 transition hover:bg-rose-50 focus:outline-none focus:ring-2 focus:ring-rose-100"
                                                                >
                                                                    Anular pago
                                                                </button>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            @empty
                                                <div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 px-4 py-5 text-sm text-slate-500">
                                                    Sin movimientos todavía.
                                                </div>
                                            @endforelse
                                        </div>
                                    </section>

                                    @if (! $this->canRegisterPayment($selectedInstallment))
                                        <section class="rounded-2xl border border-slate-200 bg-slate-50 p-5">
                                            <h3 class="text-sm font-semibold text-slate-900">Registro de pagos</h3>
                                            <p class="mt-2 text-sm leading-6 text-slate-600">
                                                Esta cuota no admite nuevos pagos porque no tiene saldo pendiente o el flujo está cancelado.
                                            </p>
                                        </section>
                                    @endif
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if ($isRegisterPaymentModalOpen && $paymentModalInstallment)
        <div class="fixed inset-0 z-[60]" aria-labelledby="register-payment-modal-title" role="dialog" aria-modal="true">
            <button
                type="button"
                wire:click="closeRegisterPaymentModal"
                class="absolute inset-0 bg-slate-950/45"
                aria-label="Cerrar modal de registro de pago"
            ></button>

            <div class="absolute inset-0 flex items-center justify-center p-4 sm:p-6">
                <div class="relative w-full max-w-2xl rounded-3xl border border-slate-200 bg-white shadow-2xl" wire:click.stop>
                    <div class="flex items-start justify-between gap-4 border-b border-slate-200 px-6 py-5">
                        <div>
                            <h2 id="register-payment-modal-title" class="text-xl font-semibold text-slate-900">
                                Agregar pago a cuota #{{ $paymentModalInstallment->number }}
                            </h2>
                            <p class="mt-1 text-sm text-slate-500">
                                Saldo pendiente: ${{ number_format((float) $paymentModalInstallment->balance_due, 2, ',', '.') }}
                            </p>
                        </div>

                        <button
                            type="button"
                            wire:click="closeRegisterPaymentModal"
                            class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-slate-200 text-slate-500 transition hover:bg-slate-50 hover:text-slate-700 focus:outline-none focus:ring-2 focus:ring-slate-200"
                            aria-label="Cerrar modal"
                        >
                            <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path
                                    fill-rule="evenodd"
                                    d="M4.22 4.22a.75.75 0 0 1 1.06 0L10 8.94l4.72-4.72a.75.75 0 1 1 1.06 1.06L11.06 10l4.72 4.72a.75.75 0 1 1-1.06 1.06L10 11.06l-4.72 4.72a.75.75 0 0 1-1.06-1.06L8.94 10 4.22 5.28a.75.75 0 0 1 0-1.06Z"
                                    clip-rule="evenodd"
                                />
                            </svg>
                        </button>
                    </div>

                    <div class="px-6 py-6">
                        <livewire:cobros.forms.register-payment-form
                            :installment="$paymentModalInstallment"
                            :key="'register-payment-'.$paymentModalInstallment->id.'-'.$registerPaymentModalNonce"
                        />
                    </div>
                </div>
            </div>
        </div>
    @endif

</div>
