<?php

namespace App\Actions\Dashboard;

use App\Enums\Cobros\PaymentStatus;
use App\Enums\ExecutionSubStatus;
use App\Enums\ProjectStatus;
use App\Enums\Sales\OpportunitySource;
use App\Enums\Sales\OpportunityStatus;
use App\Models\Client;
use App\Models\Opportunity;
use App\Models\Payment;
use App\Models\PaymentInstallment;
use App\Models\Project;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class BuildDashboardDataAction
{
    public function execute(string $period = 'month'): array
    {
        $period = $this->normalizePeriod($period);
        [$start, $end, $label] = $this->resolvePeriodRange($period);
        [$previousStart, $previousEnd] = $this->resolvePreviousPeriodRange($period, $start, $end);
        $today = now()->startOfDay();

        $salesPeriod = Opportunity::query()->whereBetween('created_at', [$start, $end]);
        $salesTotal = (clone $salesPeriod)->count();
        $salesWon = (clone $salesPeriod)->where('status', OpportunityStatus::Won->value)->count();
        $salesFailed = (clone $salesPeriod)
            ->whereIn('status', [OpportunityStatus::Lost->value, OpportunityStatus::Discarded->value])
            ->count();

        $collectedAmount = (float) Payment::query()
            ->where('status', PaymentStatus::Posted->value)
            ->whereBetween('paid_at', [$start, $end])
            ->sum('amount');

        $previousSalesTotal = Opportunity::query()->whereBetween('created_at', [$previousStart, $previousEnd])->count();
        $previousSalesWon = Opportunity::query()
            ->whereBetween('created_at', [$previousStart, $previousEnd])
            ->where('status', OpportunityStatus::Won->value)
            ->count();
        $previousCollectedAmount = (float) Payment::query()
            ->where('status', PaymentStatus::Posted->value)
            ->whereBetween('paid_at', [$previousStart, $previousEnd])
            ->sum('amount');
        $previousExecutionProjects = Project::query()
            ->where('status', ProjectStatus::Execution->value)
            ->where('updated_at', '<=', $previousEnd)
            ->count();

        $executionProjects = Project::query()->where('status', ProjectStatus::Execution->value)->count();
        $pausedProjects = Project::query()->where('status', ProjectStatus::Paused->value)->count();
        $delayedProjects = Project::query()->where('execution_sub_status', ExecutionSubStatus::Delayed->value)->count();
        $withDebtProjects = Project::query()->where('execution_sub_status', ExecutionSubStatus::WithDebt->value)->count();

        $overdueInstallmentsCount = $this->basePendingInstallmentsQuery($today)->count();
        $upcomingInstallmentsCount = PaymentInstallment::query()
            ->where('due_date', '>=', $today->toDateString())
            ->where('due_date', '<=', $today->copy()->addDays(7)->toDateString())
            ->where('balance_due', '>', 0)
            ->count();

        $clientsCreatedInPeriod = Client::query()
            ->where('created_at', '>=', $start)
            ->where('created_at', '<=', $end)
            ->count();
        $newLeadsWithoutContact = Opportunity::query()
            ->where('status', OpportunityStatus::New->value)
            ->whereNull('first_contact_at')
            ->count();

        $sourceBreakdown = $this->sourceBreakdown($start, $end, $salesTotal);
        $statusBreakdown = $this->statusBreakdown($start, $end, $salesTotal);
        $donutSegments = $this->buildDonutSegments($statusBreakdown);
        $cashFlow = $this->cashFlowSeries($period, $start, $end);

        return [
            'generatedAt' => now(),
            'currentPeriod' => $period,
            'periodLabel' => $label,
            'periodOptions' => $this->periodOptions(),
            'kpis' => [
                [
                    'label' => 'Leads del periodo',
                    'displayValue' => number_format($salesTotal, 0, ',', '.'),
                    'hint' => $salesWon.' cerrados / '.$salesFailed.' caidos',
                    'change' => $this->buildComparison($salesTotal, $previousSalesTotal),
                    'tone' => 'primary',
                ],
                [
                    'label' => 'Conversion',
                    'displayValue' => $this->percentage($salesWon, $salesTotal),
                    'hint' => 'Sobre oportunidades creadas en '.$label,
                    'change' => $this->buildComparison(
                        $this->percentageInt($salesWon, $salesTotal),
                        $this->percentageInt($previousSalesWon, $previousSalesTotal),
                        isPercentage: true,
                    ),
                    'tone' => 'success',
                ],
                [
                    'label' => 'Ejecucion activa',
                    'displayValue' => number_format($executionProjects, 0, ',', '.'),
                    'hint' => $delayedProjects.' demorados / '.$withDebtProjects.' con deuda',
                    'change' => $this->buildComparison($executionProjects, $previousExecutionProjects),
                    'tone' => 'accent',
                ],
                [
                    'label' => 'Cobrado',
                    'displayValue' => '$'.number_format($collectedAmount, 0, ',', '.'),
                    'hint' => $overdueInstallmentsCount.' cuotas vencidas pendientes',
                    'change' => $this->buildComparison($collectedAmount, $previousCollectedAmount, prefix: '$'),
                    'tone' => 'warning',
                ],
            ],
            'salesOverview' => [
                'sourceBreakdown' => $sourceBreakdown,
                'statusBreakdown' => $statusBreakdown,
                'topSource' => $sourceBreakdown->first(),
                'failedRate' => $this->percentage($salesFailed, $salesTotal),
                'donutSegments' => $donutSegments['segments'],
                'donutStyle' => $donutSegments['style'],
                'donutTotal' => $salesTotal,
            ],
            'cashFlow' => $cashFlow,
            'projectOverview' => [
                'execution' => $executionProjects,
                'paused' => $pausedProjects,
                'delayed' => $delayedProjects,
                'withDebt' => $withDebtProjects,
                'finishedInPeriod' => Project::query()
                    ->where('status', ProjectStatus::Finished->value)
                    ->whereBetween('updated_at', [$start, $end])
                    ->count(),
                'distribution' => $this->projectDistribution(),
            ],
            'urgentOpportunities' => $this->urgentOpportunities(),
            'riskProjects' => $this->riskProjects(),
            'overdueInstallments' => $this->overdueInstallments($today),
            'upcomingInstallments' => $this->upcomingInstallments($today),
            'newClients' => $this->newClients(),
            'heatmap' => $this->commercialHeatmap(),
            'quickStats' => [
                'newLeadsWithoutContact' => $newLeadsWithoutContact,
                'upcomingInstallments' => $upcomingInstallmentsCount,
                'clientsCreatedInPeriod' => $clientsCreatedInPeriod,
                'projectsAtRisk' => Project::query()
                    ->where(function (Builder $query) {
                        $query->where('execution_sub_status', ExecutionSubStatus::Delayed->value)
                            ->orWhere('execution_sub_status', ExecutionSubStatus::WithDebt->value)
                            ->orWhere('status', ProjectStatus::Paused->value);
                    })
                    ->count(),
            ],
        ];
    }

    protected function normalizePeriod(string $period): string
    {
        return in_array($period, ['month', 'last_month', 'quarter'], true) ? $period : 'month';
    }

    protected function resolvePeriodRange(string $period): array
    {
        return match ($period) {
            'last_month' => [
                now()->subMonthNoOverflow()->startOfMonth(),
                now()->subMonthNoOverflow()->endOfMonth(),
                'ultimo mes',
            ],
            'quarter' => [
                now()->startOfQuarter(),
                now()->endOfQuarter(),
                'este trimestre',
            ],
            default => [
                now()->startOfMonth(),
                now()->endOfMonth(),
                'este mes',
            ],
        };
    }

    protected function periodOptions(): array
    {
        return [
            ['value' => 'month', 'label' => 'Este mes'],
            ['value' => 'last_month', 'label' => 'Ultimo mes'],
            ['value' => 'quarter', 'label' => 'Trimestre'],
        ];
    }

    protected function resolvePreviousPeriodRange(string $period, Carbon $start, Carbon $end): array
    {
        return match ($period) {
            'last_month' => [
                $start->copy()->subMonthNoOverflow()->startOfMonth(),
                $start->copy()->subMonthNoOverflow()->endOfMonth(),
            ],
            'quarter' => [
                $start->copy()->subQuarter()->startOfQuarter(),
                $start->copy()->subQuarter()->endOfQuarter(),
            ],
            default => [
                $start->copy()->subMonthNoOverflow()->startOfMonth(),
                $start->copy()->subMonthNoOverflow()->endOfMonth(),
            ],
        };
    }

    protected function sourceBreakdown(Carbon $start, Carbon $end, int $salesTotal): Collection
    {
        $totals = Opportunity::query()
            ->whereBetween('created_at', [$start, $end])
            ->selectRaw('source, count(*) as total')
            ->groupBy('source')
            ->pluck('total', 'source');

        $won = Opportunity::query()
            ->whereBetween('created_at', [$start, $end])
            ->where('status', OpportunityStatus::Won->value)
            ->selectRaw('source, count(*) as total')
            ->groupBy('source')
            ->pluck('total', 'source');

        return collect(OpportunitySource::cases())
            ->map(function (OpportunitySource $source) use ($totals, $won, $salesTotal) {
                $total = (int) ($totals[$source->value] ?? 0);
                $wonCount = (int) ($won[$source->value] ?? 0);

                return [
                    'key' => $source->value,
                    'label' => $source->label(),
                    'total' => $total,
                    'won' => $wonCount,
                    'conversion' => $this->percentage($wonCount, $total),
                    'conversionRate' => $this->percentageInt($wonCount, $total),
                    'shareRate' => $this->percentageInt($total, $salesTotal),
                ];
            })
            ->filter(fn (array $item) => $item['total'] > 0)
            ->sortByDesc('total')
            ->values();
    }

    protected function statusBreakdown(Carbon $start, Carbon $end, int $salesTotal): Collection
    {
        $totals = Opportunity::query()
            ->whereBetween('created_at', [$start, $end])
            ->selectRaw('status, count(*) as total', [])
            ->groupBy('status')
            ->pluck('total', 'status');

        return collect(OpportunityStatus::cases())
            ->map(function (OpportunityStatus $status) use ($totals, $salesTotal) {
                return [
                    'key' => $status->value,
                    'label' => $status->label(),
                    'total' => (int) ($totals[$status->value] ?? 0),
                    'percentage' => $this->percentageInt((int) ($totals[$status->value] ?? 0), $salesTotal),
                    'color' => $this->statusColor($status->value),
                ];
            })
            ->filter(fn (array $item) => $item['total'] > 0)
            ->values();
    }

    protected function buildDonutSegments(Collection $segments): array
    {
        if ($segments->isEmpty()) {
            return [
                'style' => 'conic-gradient(#e2e8f0 0deg 360deg)',
                'segments' => collect(),
            ];
        }

        $angle = 0;
        $parts = [];

        $mapped = $segments->map(function (array $segment) use (&$angle, &$parts) {
            $degrees = max((int) round(($segment['percentage'] / 100) * 360), 4);
            $from = $angle;
            $to = min($angle + $degrees, 360);
            $parts[] = $segment['color'].' '.$from.'deg '.$to.'deg';
            $angle = $to;

            return $segment;
        });

        if ($angle < 360) {
            $parts[] = '#e2e8f0 '.$angle.'deg 360deg';
        }

        return [
            'style' => 'conic-gradient('.implode(', ', $parts).')',
            'segments' => $mapped,
        ];
    }

    protected function cashFlowSeries(string $period, Carbon $start, Carbon $end): array
    {
        $buckets = collect();

        if ($period === 'quarter') {
            $cursor = $start->copy()->startOfMonth();

            while ($cursor->lte($end)) {
                $bucketStart = $cursor->copy()->startOfMonth();
                $bucketEnd = $cursor->copy()->endOfMonth();
                $buckets->push([
                    'label' => ucfirst($bucketStart->translatedFormat('M')),
                    'start' => $bucketStart,
                    'end' => $bucketEnd,
                ]);
                $cursor->addMonth();
            }
        } else {
            $cursor = $start->copy();
            $index = 1;

            while ($cursor->lte($end)) {
                $bucketStart = $cursor->copy();
                $bucketEnd = $cursor->copy()->addDays(6)->endOfDay();
                if ($bucketEnd->gt($end)) {
                    $bucketEnd = $end->copy();
                }

                $buckets->push([
                    'label' => 'S'.$index,
                    'start' => $bucketStart,
                    'end' => $bucketEnd,
                ]);

                $cursor = $bucketEnd->copy()->addDay()->startOfDay();
                $index++;
            }
        }

        $series = $buckets->map(function (array $bucket) {
            $value = (float) Payment::query()
                ->where('status', PaymentStatus::Posted->value)
                ->whereBetween('paid_at', [$bucket['start'], $bucket['end']])
                ->sum('amount');

            return [
                'label' => $bucket['label'],
                'value' => $value,
            ];
        });

        $max = max((float) $series->max('value'), 1);

        return [
            'points' => $series->values(),
            'max' => $max,
            'polyline' => $this->buildPolyline($series, $max),
        ];
    }

    protected function buildPolyline(Collection $series, float $max): string
    {
        $count = max($series->count(), 1);

        return $series->values()->map(function (array $point, int $index) use ($count, $max) {
            $x = $count === 1 ? 50 : 6 + (($index / ($count - 1)) * 88);
            $y = 88 - (($point['value'] / $max) * 64);

            return round($x, 2).','.round($y, 2);
        })->implode(' ');
    }

    protected function projectDistribution(): Collection
    {
        $totals = Project::query()
            ->selectRaw('status, count(*) as total', [])
            ->groupBy('status')
            ->pluck('total', 'status');

        $all = max((int) $totals->sum(), 1);

        return collect(ProjectStatus::cases())
            ->map(function (ProjectStatus $status) use ($totals, $all) {
                $total = (int) ($totals[$status->value] ?? 0);

                return [
                    'key' => $status->value,
                    'label' => $status->label(),
                    'total' => $total,
                    'rate' => $this->percentageInt($total, $all),
                    'color' => $this->projectColor($status->value),
                ];
            })
            ->filter(fn (array $item) => $item['total'] > 0)
            ->values();
    }

    protected function urgentOpportunities(): Collection
    {
        return Opportunity::query()
            ->with(['client.contact', 'responsibleUser'])
            ->whereNotIn('status', [
                OpportunityStatus::Won->value,
                OpportunityStatus::Lost->value,
                OpportunityStatus::Discarded->value,
            ])
            ->where(function (Builder $query) {
                $query->where(function (Builder $innerQuery) {
                    $innerQuery->whereNotNull('customer_service_window_expires_at')
                        ->where('customer_service_window_expires_at', '<=', now()->addHours(6));
                })->orWhere(function (Builder $innerQuery) {
                    $innerQuery->where('status', OpportunityStatus::New->value)
                        ->whereNull('first_contact_at')
                        ->where('created_at', '<=', now()->subHours(4));
                });
            })
            ->orderByRaw('customer_service_window_expires_at is null')
            ->orderBy('customer_service_window_expires_at')
            ->orderBy('created_at')
            ->limit(5)
            ->get()
            ->map(function (Opportunity $opportunity) {
                $reason = 'Lead nuevo sin primer contacto';

                if ($opportunity->customer_service_window_expires_at && $opportunity->customer_service_window_expires_at->isPast()) {
                    $reason = 'Ventana de respuesta vencida';
                } elseif ($opportunity->customer_service_window_expires_at) {
                    $reason = 'Ventana por vencer';
                }

                return [
                    'id' => $opportunity->id,
                    'name' => $opportunity->name,
                    'contact' => $opportunity->display_contact,
                    'client' => $opportunity->client?->organization_name ?? 'Sin cliente',
                    'responsible' => $opportunity->responsibleUser?->name ?? 'Sin asignar',
                    'statusKey' => $opportunity->status->value,
                    'status' => $opportunity->status->label(),
                    'reason' => $reason,
                    'replyBy' => $opportunity->customer_service_window_expires_at,
                    'createdAt' => $opportunity->created_at,
                ];
            });
    }

    protected function riskProjects(): Collection
    {
        return Project::query()
            ->with(['client.contact'])
            ->where(function (Builder $query) {
                $query->where('execution_sub_status', ExecutionSubStatus::Delayed->value)
                    ->orWhere('execution_sub_status', ExecutionSubStatus::WithDebt->value)
                    ->orWhere('status', ProjectStatus::Paused->value);
            })
            ->orderByRaw("case when execution_sub_status = 'with_debt' then 0 when execution_sub_status = 'delayed' then 1 else 2 end")
            ->orderByDesc('updated_at')
            ->limit(5)
            ->get()
            ->map(function (Project $project) {
                return [
                    'id' => $project->id,
                    'name' => $project->name,
                    'client' => $project->client?->organization_name ?? 'Sin cliente',
                    'status' => $project->status->label(),
                    'subStatus' => $project->execution_sub_status?->label(),
                    'estimatedEndDate' => $project->estimated_end_date,
                    'totalCost' => (float) ($project->total_cost ?? 0),
                ];
            });
    }

    protected function overdueInstallments(Carbon $today): Collection
    {
        return $this->basePendingInstallmentsQuery($today)
            ->with(['flow.project.client'])
            ->orderBy('due_date')
            ->limit(5)
            ->get()
            ->map(fn (PaymentInstallment $installment) => $this->mapInstallment($installment, $today));
    }

    protected function upcomingInstallments(Carbon $today): Collection
    {
        return PaymentInstallment::query()
            ->with(['flow.project.client'])
            ->where('due_date', '>=', $today->toDateString())
            ->where('due_date', '<=', $today->copy()->addDays(7)->toDateString())
            ->where('balance_due', '>', 0)
            ->orderBy('due_date')
            ->limit(5)
            ->get()
            ->map(fn (PaymentInstallment $installment) => $this->mapInstallment($installment, $today));
    }

    protected function newClients(): Collection
    {
        return Client::query()
            ->with(['contact'])
            ->withCount(['projects', 'opportunities'])
            ->latest()
            ->limit(5)
            ->get()
            ->map(function (Client $client) {
                return [
                    'id' => $client->id,
                    'organization' => $client->organization_name,
                    'contact' => $client->contact?->full_name ?: 'Sin contacto',
                    'industry' => $client->industry ?: 'Sin rubro',
                    'projectsCount' => $client->projects_count,
                    'opportunitiesCount' => $client->opportunities_count,
                    'createdAt' => $client->created_at,
                ];
            });
    }

    protected function commercialHeatmap(): array
    {
        $start = now()->startOfWeek()->subWeeks(3)->startOfDay();
        $end = now()->endOfWeek()->endOfDay();

        $counts = Opportunity::query()
            ->whereBetween('created_at', [$start, $end])
            ->get(['created_at'])
            ->groupBy(fn (Opportunity $opportunity) => $opportunity->created_at->format('Y-m-d'))
            ->map->count();

        $weeks = [];
        $cursor = $start->copy();

        while ($cursor->lte($end)) {
            $weekStart = $cursor->copy();
            $days = [];

            for ($i = 0; $i < 7; $i++) {
                $day = $weekStart->copy()->addDays($i);
                $count = (int) ($counts[$day->format('Y-m-d')] ?? 0);
                $days[] = [
                    'label' => $day->translatedFormat('D d'),
                    'count' => $count,
                    'tone' => $this->heatTone($count),
                ];
            }

            $weeks[] = [
                'label' => 'Sem '.($weekStart->weekOfYear),
                'days' => $days,
            ];

            $cursor->addWeek();
        }

        return [
            'weeks' => collect($weeks),
            'legend' => [
                ['label' => 'Bajo', 'tone' => 'bg-slate-200'],
                ['label' => 'Medio', 'tone' => 'bg-sky-300'],
                ['label' => 'Alto', 'tone' => 'bg-indigo-500'],
            ],
        ];
    }

    protected function basePendingInstallmentsQuery(Carbon $today): Builder
    {
        return PaymentInstallment::query()
            ->where('balance_due', '>', 0)
            ->whereDate('due_date', '<', $today);
    }

    protected function mapInstallment(PaymentInstallment $installment, Carbon $today): array
    {
        $project = $installment->flow?->project;
        $client = $project?->client;
        $days = $installment->due_date?->diffInDays($today, false);

        return [
            'id' => $installment->id,
            'number' => $installment->number,
            'projectName' => $project?->name ?? 'Proyecto no disponible',
            'client' => $client?->organization_name ?? 'Sin cliente',
            'flowId' => $installment->flow?->id,
            'dueDate' => $installment->due_date,
            'balanceDue' => (float) $installment->balance_due,
            'daysDelta' => $days,
        ];
    }

    protected function percentage(int $portion, int $total): string
    {
        return $this->percentageInt($portion, $total).'%';
    }

    protected function percentageInt(int $portion, int $total): int
    {
        if ($total === 0) {
            return 0;
        }

        return (int) round(($portion / $total) * 100);
    }

    protected function statusColor(string $status): string
    {
        return match ($status) {
            OpportunityStatus::New->value => '#38bdf8',
            OpportunityStatus::Contacted->value => '#6366f1',
            OpportunityStatus::Qualified->value => '#f59e0b',
            OpportunityStatus::ProposalSent->value => '#fb7185',
            OpportunityStatus::Negotiation->value => '#a855f7',
            OpportunityStatus::Won->value => '#10b981',
            OpportunityStatus::Lost->value => '#f43f5e',
            OpportunityStatus::Discarded->value => '#94a3b8',
            default => '#cbd5e1',
        };
    }

    protected function projectColor(string $status): string
    {
        return match ($status) {
            ProjectStatus::SaleClosed->value => 'bg-violet-400',
            ProjectStatus::Execution->value => 'bg-emerald-400',
            ProjectStatus::Paused->value => 'bg-amber-400',
            ProjectStatus::Finished->value => 'bg-slate-500',
            default => 'bg-slate-300',
        };
    }

    protected function heatTone(int $count): string
    {
        return match (true) {
            $count >= 4 => 'bg-indigo-500',
            $count >= 2 => 'bg-sky-300',
            $count >= 1 => 'bg-slate-300',
            default => 'bg-slate-100',
        };
    }

    protected function buildComparison(float|int $current, float|int $previous, bool $isPercentage = false, string $prefix = ''): array
    {
        $delta = $current - $previous;
        $direction = $delta > 0 ? 'up' : ($delta < 0 ? 'down' : 'flat');
        $tone = match ($direction) {
            'up' => 'success',
            'down' => 'danger',
            default => 'neutral',
        };

        $formattedDelta = $isPercentage
            ? abs((int) round($delta)).' pp'
            : $prefix.number_format(abs((float) $delta), 0, ',', '.');

        return [
            'direction' => $direction,
            'tone' => $tone,
            'label' => $direction === 'flat' ? 'Sin cambio' : (($direction === 'up' ? '+' : '-').$formattedDelta),
            'context' => 'vs periodo anterior',
        ];
    }
}
