@php
    $statusBadgeVariants = [
        'new' => 'info',
        'contacted' => 'primary',
        'qualified' => 'accent',
        'proposal_sent' => 'warning',
        'negotiation' => 'warning',
        'won' => 'success',
        'lost' => 'danger',
        'discarded' => 'neutral',
    ];

    $maxSourceTotal = max($salesOverview['sourceBreakdown']->max('total') ?? 0, 1);
    $maxProjectTotal = max($projectOverview['distribution']->max('total') ?? 0, 1);
    $cashFlowTotal = $cashFlow['points']->sum('value');

    $barWidthClass = function (float $percent): string {
        return match (true) {
            $percent >= 100 => 'w-full',
            $percent >= 92 => 'w-11/12',
            $percent >= 84 => 'w-10/12',
            $percent >= 76 => 'w-9/12',
            $percent >= 68 => 'w-8/12',
            $percent >= 60 => 'w-7/12',
            $percent >= 52 => 'w-6/12',
            $percent >= 44 => 'w-5/12',
            $percent >= 36 => 'w-4/12',
            $percent >= 28 => 'w-3/12',
            $percent >= 20 => 'w-2/12',
            default => 'w-1/12',
        };
    };

    $segmentDotClass = function (string $key): string {
        return match ($key) {
            'new' => 'bg-sky-400',
            'contacted' => 'bg-indigo-500',
            'qualified' => 'bg-amber-400',
            'proposal_sent' => 'bg-rose-400',
            'negotiation' => 'bg-violet-500',
            'won' => 'bg-emerald-500',
            'lost' => 'bg-rose-600',
            'discarded' => 'bg-slate-400',
            default => 'bg-slate-300',
        };
    };

    $kpiLinks = [
        route('ventas.index', ['status' => 'new']),
        route('ventas.index', ['status' => 'won']),
        route('proyectos.index', ['status' => 'execution']),
        route('cobros.index'),
    ];
@endphp

<x-app-layout>
    <x-ui.page-container class="relative overflow-hidden">
        <div class="pointer-events-none absolute inset-x-0 top-0 -z-10 h-72 bg-[radial-gradient(circle_at_top_left,_rgba(56,189,248,0.18),_transparent_42%),radial-gradient(circle_at_top_right,_rgba(99,102,241,0.18),_transparent_38%),linear-gradient(180deg,rgba(248,250,252,1),rgba(248,250,252,0))]"></div>

        <div class="space-y-8">
            <x-ui.section-header
                title="Overview operacional"
                description="Ventas, ejecucion y cobros agrupados en bloques cortos para leer toda la operacion en una sola pantalla."
                eyebrow="Dashboard"
            >
                <x-slot:actions>
                    <div class="flex flex-wrap items-center gap-2 rounded-full border border-slate-200 bg-white/90 p-1 shadow-sm ring-1 ring-slate-200/70">
                        @foreach ($periodOptions as $option)
                            <a
                                href="{{ route('dashboard', ['period' => $option['value']]) }}"
                                class="rounded-full px-3 py-2 text-sm font-medium transition {{ $currentPeriod === $option['value'] ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-950' }}"
                            >
                                {{ $option['label'] }}
                            </a>
                        @endforeach
                    </div>
                </x-slot:actions>
            </x-ui.section-header>

            <div class="grid gap-6 xl:grid-cols-12">
                <x-ui.card class="space-y-6 bg-white/95 xl:col-span-8">
                    <div class="flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.24em] text-indigo-500">Resumen {{ $periodLabel }}</p>
                            <h2 class="mt-2 text-2xl font-semibold tracking-tight text-slate-950">Performance comercial y flujo de caja</h2>
                            <p class="mt-2 text-sm text-slate-500">Combina metricas cortas con graficos de lectura rapida para detectar tendencia y presion del pipeline.</p>
                        </div>

                        <div class="rounded-3xl border border-slate-200 bg-slate-50/80 px-4 py-3 text-sm text-slate-500">
                            Actualizado {{ $generatedAt->format('d/m/Y H:i') }}
                        </div>
                    </div>

                    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                        @foreach ($kpis as $index => $item)
                            <a href="{{ $kpiLinks[$index] ?? route('dashboard') }}" class="block transition hover:-translate-y-0.5">
                                <x-ui.stat-card
                                    :label="$item['label']"
                                    :value="$item['displayValue']"
                                    :hint="$item['hint']"
                                    :tone="$item['tone']"
                                    :change="$item['change'] ?? null"
                                    class="h-full"
                                />
                            </a>
                        @endforeach
                    </div>

                    <div class="grid gap-5 xl:grid-cols-[1.05fr_1.45fr]">
                        <div class="rounded-[28px] border border-slate-200 bg-slate-50/80 p-5">
                            <div class="flex items-center justify-between gap-3">
                                <div>
                                    <h3 class="text-base font-semibold text-slate-950">Canales de venta</h3>
                                    <p class="text-sm text-slate-500">Se muestran todos los origenes porque son finitos.</p>
                                </div>

                                @if ($salesOverview['topSource'])
                                    <x-ui.badge variant="primary">Top: {{ $salesOverview['topSource']['label'] }}</x-ui.badge>
                                @endif
                            </div>

                            <div class="mt-5 space-y-4">
                                @forelse ($salesOverview['sourceBreakdown'] as $source)
                                    @php
                                        $widthClass = $barWidthClass(max(($source['total'] / $maxSourceTotal) * 100, 10));
                                    @endphp
                                    <a href="{{ route('ventas.index', ['source' => $source['key']]) }}" class="block space-y-2 rounded-2xl px-2 py-2 transition hover:bg-white hover:shadow-sm">
                                        <div class="flex items-center justify-between gap-3 text-sm">
                                            <div>
                                                <span class="font-medium text-slate-900">{{ $source['label'] }}</span>
                                                <span class="ml-2 text-slate-500">{{ $source['total'] }} leads</span>
                                            </div>
                                            <span class="text-slate-500">{{ $source['conversion'] }} cierre</span>
                                        </div>

                                        <div class="h-2.5 overflow-hidden rounded-full bg-slate-200">
                                            <div class="h-full rounded-full bg-gradient-to-r from-sky-400 via-indigo-500 to-violet-500 {{ $widthClass }}"></div>
                                        </div>
                                    </a>
                                @empty
                                    <x-ui.empty-state title="Sin canales activos" description="Todavia no hay leads registrados en el periodo elegido." class="py-8" />
                                @endforelse
                            </div>
                        </div>

                        <div class="rounded-[28px] border border-slate-200 bg-white p-5">
                            <div class="flex items-center justify-between gap-3">
                                <div>
                                    <h3 class="text-base font-semibold text-slate-950">Cash flow del periodo</h3>
                                    <p class="text-sm text-slate-500">Serie corta para ver si la cobranza acelera o se plancha.</p>
                                </div>

                                <div class="text-right">
                                    <div class="text-xs uppercase tracking-[0.22em] text-slate-400">Total</div>
                                    <div class="text-lg font-semibold text-slate-950">${{ number_format($cashFlowTotal, 0, ',', '.') }}</div>
                                </div>
                            </div>

                            <div class="mt-5 rounded-[24px] border border-slate-100 bg-[linear-gradient(180deg,#f8fafc_0%,#ffffff_100%)] p-4">
                                @if ($cashFlow['points']->isNotEmpty())
                                    <svg viewBox="0 0 100 100" class="h-52 w-full">
                                        <defs>
                                            <linearGradient id="cashFlowStroke" x1="0%" y1="0%" x2="100%" y2="0%">
                                                <stop offset="0%" stop-color="#38bdf8" />
                                                <stop offset="50%" stop-color="#8b5cf6" />
                                                <stop offset="100%" stop-color="#06b6d4" />
                                            </linearGradient>
                                        </defs>

                                        <line x1="6" y1="88" x2="94" y2="88" stroke="#e2e8f0" stroke-width="1.2" />
                                        <line x1="6" y1="22" x2="94" y2="22" stroke="#f1f5f9" stroke-width="1" stroke-dasharray="2 3" />
                                        <line x1="6" y1="55" x2="94" y2="55" stroke="#f1f5f9" stroke-width="1" stroke-dasharray="2 3" />
                                        <polyline fill="none" stroke="url(#cashFlowStroke)" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" points="{{ $cashFlow['polyline'] }}" />

                                        @foreach ($cashFlow['points'] as $pointIndex => $point)
                                            @php
                                                $x = $cashFlow['points']->count() === 1 ? 50 : 6 + (($pointIndex / max($cashFlow['points']->count() - 1, 1)) * 88);
                                                $y = 88 - (($point['value'] / $cashFlow['max']) * 64);
                                            @endphp
                                            <circle cx="{{ $x }}" cy="{{ $y }}" r="1.8" fill="#ffffff" stroke="#6366f1" stroke-width="1.5" />
                                        @endforeach
                                    </svg>

                                    <div class="mt-3 flex items-start justify-between gap-2 text-center text-xs text-slate-500">
                                        @foreach ($cashFlow['points'] as $point)
                                            <div class="flex-1">
                                                <div class="font-medium text-slate-700">{{ $point['label'] }}</div>
                                                <div>${{ number_format($point['value'], 0, ',', '.') }}</div>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <x-ui.empty-state title="Sin movimientos" description="No hay pagos registrados para armar la curva en el periodo." class="py-8" />
                                @endif
                            </div>
                        </div>
                    </div>
                </x-ui.card>

                <x-ui.card class="space-y-6 bg-white/95 xl:col-span-4">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.24em] text-fuchsia-500">Pipeline</p>
                        <h2 class="mt-2 text-xl font-semibold text-slate-950">Embudo y estado del portafolio</h2>
                        <p class="mt-2 text-sm text-slate-500">Mix de dona y barras para leer etapa comercial y carga actual de proyectos.</p>
                    </div>

                    <div class="grid gap-4 lg:grid-cols-2 xl:grid-cols-1">
                        <div class="rounded-[28px] border border-slate-200 bg-slate-50/80 p-5">
                            <div class="flex items-center justify-between gap-4">
                                <div>
                                    <h3 class="text-base font-semibold text-slate-950">Estado de oportunidades</h3>
                                    <p class="text-sm text-slate-500">Distribucion dentro de {{ $periodLabel }}.</p>
                                </div>

                                <div class="text-right">
                                    <div class="text-xs uppercase tracking-[0.22em] text-slate-400">Total</div>
                                    <div class="text-lg font-semibold text-slate-950">{{ number_format($salesOverview['donutTotal'], 0, ',', '.') }}</div>
                                </div>
                            </div>

                            <div class="mt-5 flex items-center gap-5">
                                @php
                                    $circumference = 314;
                                    $offset = 0;
                                @endphp
                                <div class="flex shrink-0 flex-col items-center gap-2">
                                    <div class="relative flex h-36 w-36 items-center justify-center">
                                        <svg viewBox="0 0 120 120" class="h-36 w-36 -rotate-90">
                                        <circle cx="60" cy="60" r="50" fill="none" stroke="#e2e8f0" stroke-width="16"></circle>
                                        @foreach ($salesOverview['donutSegments'] as $segment)
                                            @php
                                                $segmentLength = max(($segment['percentage'] / 100) * $circumference, 8);
                                                $dashArray = $segmentLength.' '.max($circumference - $segmentLength, 0);
                                                $dashOffset = -$offset;
                                                $offset += $segmentLength;
                                            @endphp
                                            <circle cx="60" cy="60" r="50" fill="none" stroke="{{ $segment['color'] }}" stroke-width="16" stroke-linecap="round" stroke-dasharray="{{ $dashArray }}" stroke-dashoffset="{{ $dashOffset }}"></circle>
                                        @endforeach
                                        </svg>
                                        <div class="absolute text-center">
                                            <div class="text-xl font-semibold text-slate-950">{{ number_format($salesOverview['donutTotal'], 0, ',', '.') }}</div>
                                        </div>
                                    </div>
                                    <div class="text-[11px] font-medium uppercase tracking-[0.22em] text-slate-400">leads</div>
                                </div>

                                <div class="-mt-2 flex-1 space-y-2.5">
                                    @forelse ($salesOverview['donutSegments'] as $segment)
                                        <a href="{{ route('ventas.index', ['status' => $segment['key']]) }}" class="flex items-center justify-between gap-3 rounded-xl px-2 py-1.5 text-sm transition hover:bg-white">
                                            <div class="flex items-center gap-2">
                                                <span class="h-2.5 w-2.5 rounded-full {{ $segmentDotClass($segment['key']) }}"></span>
                                                <span class="text-slate-700">{{ $segment['label'] }}</span>
                                            </div>
                                            <span class="text-slate-500">{{ $segment['total'] }}</span>
                                        </a>
                                    @empty
                                        <span class="text-sm text-slate-500">Sin datos para el periodo.</span>
                                    @endforelse
                                </div>
                            </div>
                        </div>

                        <div class="rounded-[28px] border border-slate-200 bg-white p-5">
                            <div class="flex items-center justify-between gap-3">
                                <div>
                                    <h3 class="text-base font-semibold text-slate-950">Portafolio</h3>
                                    <p class="text-sm text-slate-500">Barras por estado de proyecto.</p>
                                </div>
                                <x-ui.badge variant="danger">{{ $quickStats['projectsAtRisk'] }} en riesgo</x-ui.badge>
                            </div>

                            <div class="mt-5 space-y-4">
                                @foreach ($projectOverview['distribution'] as $row)
                                    @php
                                        $widthClass = $barWidthClass(max(($row['total'] / $maxProjectTotal) * 100, 10));
                                    @endphp
                                    <a href="{{ route('proyectos.index', ['status' => $row['key']]) }}" class="block rounded-2xl px-2 py-2 transition hover:bg-slate-50">
                                        <div class="mb-2 flex items-center justify-between gap-3 text-sm">
                                            <span class="font-medium text-slate-700">{{ $row['label'] }}</span>
                                            <span class="text-slate-500">{{ $row['total'] }}</span>
                                        </div>
                                        <div class="h-2.5 overflow-hidden rounded-full bg-slate-200">
                                            <div class="h-full rounded-full {{ $row['color'] }} {{ $widthClass }}"></div>
                                        </div>
                                    </a>
                                @endforeach
                            </div>

                            <div class="mt-5 grid grid-cols-2 gap-3">
                                <x-ui.panel tone="warning" padding="sm" class="space-y-1">
                                    <div class="text-xs uppercase tracking-[0.18em] text-amber-800">Frenados</div>
                                    <div class="text-2xl font-semibold text-slate-950">{{ number_format($projectOverview['paused'], 0, ',', '.') }}</div>
                                </x-ui.panel>
                                <x-ui.panel tone="success" padding="sm" class="space-y-1">
                                    <div class="text-xs uppercase tracking-[0.18em] text-emerald-800">Finalizados</div>
                                    <div class="text-2xl font-semibold text-slate-950">{{ number_format($projectOverview['finishedInPeriod'], 0, ',', '.') }}</div>
                                </x-ui.panel>
                            </div>
                        </div>
                    </div>
                </x-ui.card>

                <x-ui.card class="space-y-5 bg-white/95 xl:col-span-4">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <h3 class="text-lg font-semibold text-slate-950">Leads que requieren respuesta</h3>
                            <p class="text-sm text-slate-500">Hasta 5 items para actuar rapido sin convertirlo en un listado largo.</p>
                        </div>
                        <x-ui.button href="{{ route('ventas.index') }}" variant="ghost" size="sm">Ventas</x-ui.button>
                    </div>

                    <div class="space-y-3.5">
                        @forelse ($urgentOpportunities as $opportunity)
                            <div class="rounded-[24px] border border-slate-200 bg-slate-50/80 p-4">
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <a href="{{ route('ventas.show', $opportunity['id']) }}" class="font-medium text-slate-950 transition hover:text-indigo-600">
                                            {{ $opportunity['name'] }}
                                        </a>
                                        <p class="mt-1 text-sm text-slate-500">{{ $opportunity['client'] }} · {{ $opportunity['contact'] }}</p>
                                    </div>
                                    <x-ui.badge :variant="$statusBadgeVariants[$opportunity['statusKey']] ?? 'neutral'">{{ $opportunity['status'] }}</x-ui.badge>
                                </div>

                                <div class="mt-3 flex items-center justify-between gap-3 text-sm">
                                    <span class="text-slate-700">{{ $opportunity['reason'] }}</span>
                                    <span class="text-slate-500">
                                        {{ $opportunity['replyBy'] ? 'Hasta '.$opportunity['replyBy']->format('d/m H:i') : 'Creado '.$opportunity['createdAt']?->format('d/m H:i') }}
                                    </span>
                                </div>
                            </div>
                        @empty
                            <x-ui.empty-state title="Sin alertas urgentes" description="No hay leads criticos en este momento." class="py-10" />
                        @endforelse
                    </div>
                </x-ui.card>

                <x-ui.card class="space-y-5 bg-white/95 xl:col-span-4">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <h3 class="text-lg font-semibold text-slate-950">Operaciones con foco</h3>
                            <p class="text-sm text-slate-500">Hasta 5 para mostrar solo lo que puede crecer indefinidamente.</p>
                        </div>
                        <x-ui.button href="{{ route('proyectos.index') }}" variant="ghost" size="sm">Operaciones</x-ui.button>
                    </div>

                    <div class="space-y-3.5">
                        @forelse ($riskProjects as $project)
                            <div class="rounded-[24px] border border-slate-200 bg-slate-50/80 p-4">
                                <div class="flex items-start justify-between gap-4">
                                    <div>
                                        <a href="{{ route('proyectos.show', $project['id']) }}" class="font-medium text-slate-950 transition hover:text-indigo-600">
                                            {{ $project['name'] }}
                                        </a>
                                        <p class="mt-1 text-sm text-slate-500">{{ $project['client'] }}</p>
                                    </div>
                                    @if ($project['subStatus'])
                                        <x-ui.badge :variant="$project['subStatus'] === 'Con Deuda' ? 'danger' : 'warning'">{{ $project['subStatus'] }}</x-ui.badge>
                                    @else
                                        <x-ui.badge variant="neutral">{{ $project['status'] }}</x-ui.badge>
                                    @endif
                                </div>

                                <div class="mt-3 flex items-center justify-between gap-3 text-sm text-slate-500">
                                    <span>Entrega {{ $project['estimatedEndDate']?->format('d/m/Y') ?? 'sin fecha' }}</span>
                                    <span>${{ number_format($project['totalCost'], 0, ',', '.') }}</span>
                                </div>
                            </div>
                        @empty
                            <x-ui.empty-state title="Sin riesgos visibles" description="No hay proyectos pausados, demorados o con deuda." class="py-10" />
                        @endforelse
                    </div>
                </x-ui.card>

                <x-ui.card class="space-y-5 bg-white/95 xl:col-span-4">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <h3 class="text-lg font-semibold text-slate-950">Cobros a vigilar</h3>
                            <p class="text-sm text-slate-500">Dos listas cortas compartiendo un mismo tercio de pantalla.</p>
                        </div>
                        <x-ui.button href="{{ route('cobros.index', ['status' => 'active']) }}" variant="ghost" size="sm">Cobros</x-ui.button>
                    </div>

                    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-1">
                        <div class="rounded-[24px] border border-rose-200 bg-rose-50/70 p-4">
                            <div class="mb-3 flex items-center justify-between gap-3">
                                <h4 class="font-medium text-rose-950">Cuotas atrasadas</h4>
                                <x-ui.badge variant="danger">{{ $overdueInstallments->count() }}</x-ui.badge>
                            </div>
                            <div class="space-y-3">
                                @forelse ($overdueInstallments as $installment)
                                    @php($flowUrl = $installment['flowId'] ? route('cobros.show', $installment['flowId']) : null)
                                    <a
                                        @if($flowUrl) href="{{ $flowUrl }}" @endif
                                        class="block rounded-2xl border border-rose-200/80 bg-white/80 p-3 transition hover:-translate-y-0.5 hover:shadow-sm {{ $flowUrl ? '' : 'pointer-events-none opacity-70' }}"
                                    >
                                        <div class="text-sm font-medium text-slate-900">{{ $installment['projectName'] }} · #{{ $installment['number'] }}</div>
                                        <div class="mt-1 text-xs text-slate-500">{{ $installment['client'] }} · {{ abs($installment['daysDelta']) }} dias</div>
                                        <div class="mt-2 text-sm font-semibold text-rose-700">${{ number_format($installment['balanceDue'], 0, ',', '.') }}</div>
                                    </a>
                                @empty
                                    <p class="text-sm text-rose-700">Sin cuotas atrasadas.</p>
                                @endforelse
                            </div>
                        </div>

                        <div class="rounded-[24px] border border-amber-200 bg-amber-50/70 p-4">
                            <div class="mb-3 flex items-center justify-between gap-3">
                                <h4 class="font-medium text-amber-950">Cuotas por vencer</h4>
                                <x-ui.badge variant="warning">{{ $upcomingInstallments->count() }}</x-ui.badge>
                            </div>
                            <div class="space-y-3">
                                @forelse ($upcomingInstallments as $installment)
                                    @php($flowUrl = $installment['flowId'] ? route('cobros.show', $installment['flowId']) : null)
                                    <a
                                        @if($flowUrl) href="{{ $flowUrl }}" @endif
                                        class="block rounded-2xl border border-amber-200/80 bg-white/80 p-3 transition hover:-translate-y-0.5 hover:shadow-sm {{ $flowUrl ? '' : 'pointer-events-none opacity-70' }}"
                                    >
                                        <div class="text-sm font-medium text-slate-900">{{ $installment['projectName'] }} · #{{ $installment['number'] }}</div>
                                        <div class="mt-1 text-xs text-slate-500">{{ $installment['client'] }} · {{ $installment['dueDate']?->format('d/m') ?? '-' }}</div>
                                        <div class="mt-2 text-sm font-semibold text-amber-700">${{ number_format($installment['balanceDue'], 0, ',', '.') }}</div>
                                    </a>
                                @empty
                                    <p class="text-sm text-amber-700">Sin vencimientos cercanos.</p>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </x-ui.card>

                <x-ui.card class="space-y-5 bg-white/95 xl:col-span-4">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <h3 class="text-lg font-semibold text-slate-950">Top 5 clientes nuevos</h3>
                            <p class="text-sm text-slate-500">Lista corta porque puede crecer indefinidamente.</p>
                        </div>
                        <x-ui.button href="{{ route('clientes.index') }}" variant="ghost" size="sm">Clientes</x-ui.button>
                    </div>

                    <div class="space-y-3.5">
                        @forelse ($newClients as $client)
                            <div class="rounded-[24px] border border-slate-200 bg-slate-50/80 p-4">
                                <div class="flex items-start justify-between gap-4">
                                    <div>
                                        <div class="font-medium text-slate-950">{{ $client['organization'] }}</div>
                                        <p class="mt-1 text-sm text-slate-500">{{ $client['contact'] }} · {{ $client['industry'] }}</p>
                                    </div>
                                    <span class="text-xs uppercase tracking-[0.18em] text-slate-400">{{ $client['createdAt']?->format('d/m') ?? '-' }}</span>
                                </div>

                                <div class="mt-3 flex flex-wrap gap-2">
                                    <x-ui.badge variant="info">{{ $client['opportunitiesCount'] }} oportunidades</x-ui.badge>
                                    <x-ui.badge variant="success">{{ $client['projectsCount'] }} proyectos</x-ui.badge>
                                </div>
                            </div>
                        @empty
                            <x-ui.empty-state title="Sin nuevas altas" description="No hay clientes recientes para mostrar." class="py-10" />
                        @endforelse
                    </div>
                </x-ui.card>

                <x-ui.card class="space-y-5 bg-white/95 xl:col-span-4">
                    <div>
                        <h3 class="text-lg font-semibold text-slate-950">Mapa de calor comercial</h3>
                        <p class="text-sm text-slate-500">Ultimas 4 semanas de ingreso de leads. Sirve para detectar dias con mas movimiento.</p>
                    </div>

                    <div class="grid grid-cols-4 gap-3">
                        @foreach ($heatmap['weeks'] as $week)
                            <div class="rounded-[24px] border border-slate-200 bg-slate-50/80 p-3">
                                <div class="mb-3 text-center text-xs font-semibold uppercase tracking-[0.18em] text-slate-400">{{ $week['label'] }}</div>
                                <div class="grid grid-cols-1 gap-2">
                                    @foreach ($week['days'] as $day)
                                        <div class="flex items-center gap-2">
                                            <div class="h-6 flex-1 rounded-xl {{ $day['tone'] }}"></div>
                                            <span class="w-10 text-right text-[11px] text-slate-500">{{ $day['count'] }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="flex flex-wrap items-center gap-3 text-xs text-slate-500">
                        @foreach ($heatmap['legend'] as $item)
                            <div class="flex items-center gap-2">
                                <span class="h-3 w-3 rounded-full {{ $item['tone'] }}"></span>
                                <span>{{ $item['label'] }}</span>
                            </div>
                        @endforeach
                    </div>
                </x-ui.card>

                <x-ui.card class="space-y-5 bg-white/95 xl:col-span-4">
                    <div>
                        <h3 class="text-lg font-semibold text-slate-950">Pulso rapido</h3>
                        <p class="text-sm text-slate-500">Mini widgets para cerrar la lectura ejecutiva del dashboard.</p>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-1">
                        <a href="{{ route('clientes.index') }}" class="block transition hover:-translate-y-0.5">
                            <x-ui.panel tone="info" class="space-y-1 rounded-[24px]">
                                <div class="text-xs uppercase tracking-[0.18em] text-sky-900">Clientes creados</div>
                                <div class="text-3xl font-semibold text-slate-950">{{ number_format($quickStats['clientsCreatedInPeriod'], 0, ',', '.') }}</div>
                                <div class="text-sm text-slate-600">Dentro de {{ $periodLabel }}</div>
                            </x-ui.panel>
                        </a>

                        <a href="{{ route('ventas.index', ['status' => 'new']) }}" class="block transition hover:-translate-y-0.5">
                            <x-ui.panel tone="warning" class="space-y-1 rounded-[24px]">
                                <div class="text-xs uppercase tracking-[0.18em] text-amber-900">Leads sin primer contacto</div>
                                <div class="text-3xl font-semibold text-slate-950">{{ number_format($quickStats['newLeadsWithoutContact'], 0, ',', '.') }}</div>
                                <div class="text-sm text-slate-600">Para reasignar o responder rapido</div>
                            </x-ui.panel>
                        </a>

                        <a href="{{ route('cobros.index') }}" class="block transition hover:-translate-y-0.5">
                            <x-ui.panel tone="danger" class="space-y-1 rounded-[24px]">
                                <div class="text-xs uppercase tracking-[0.18em] text-rose-900">Cuotas por vencer</div>
                                <div class="text-3xl font-semibold text-slate-950">{{ number_format($quickStats['upcomingInstallments'], 0, ',', '.') }}</div>
                                <div class="text-sm text-slate-600">Siguientes 7 dias</div>
                            </x-ui.panel>
                        </a>
                    </div>
                </x-ui.card>
            </div>
        </div>
    </x-ui.page-container>
</x-app-layout>
