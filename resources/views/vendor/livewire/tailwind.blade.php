@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Pagination Navigation" class="flex w-full justify-center">
        <div class="w-full rounded-[1.75rem] border border-slate-200/90 bg-gradient-to-br from-white via-slate-50/70 to-white px-4 py-4 shadow-sm ring-1 ring-slate-200/70 sm:px-5">
            <div class="flex flex-col gap-4 sm:hidden">
                <div class="text-center text-xs font-medium text-slate-500">
                    @if ($paginator->firstItem())
                        Mostrando {{ number_format($paginator->firstItem(), 0, ',', '.') }}-{{ number_format($paginator->lastItem(), 0, ',', '.') }} de {{ number_format($paginator->total(), 0, ',', '.') }}
                    @else
                        Sin resultados para paginar
                    @endif
                </div>

                <div class="grid grid-cols-2 gap-3">
                    @if ($paginator->onFirstPage())
                        <span class="inline-flex h-11 items-center justify-center rounded-2xl border border-slate-200 bg-slate-100 px-4 text-sm font-medium text-slate-400">
                            Anterior
                        </span>
                    @else
                        <button type="button" wire:click="previousPage('{{ $paginator->getPageName() }}')" dusk="previousPage{{ $paginator->getPageName() == 'page' ? '' : '.' . $paginator->getPageName() }}" wire:loading.attr="disabled" class="inline-flex h-11 items-center justify-center rounded-2xl border border-slate-200 bg-white px-4 text-sm font-medium text-slate-700 shadow-sm transition hover:border-slate-300 hover:bg-slate-50 hover:text-slate-950 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                            Anterior
                        </button>
                    @endif

                    @if ($paginator->hasMorePages())
                        <button type="button" wire:click="nextPage('{{ $paginator->getPageName() }}')" dusk="nextPage{{ $paginator->getPageName() == 'page' ? '' : '.' . $paginator->getPageName() }}" wire:loading.attr="disabled" class="inline-flex h-11 items-center justify-center rounded-2xl border border-indigo-200 bg-indigo-50 px-4 text-sm font-medium text-indigo-700 shadow-sm transition hover:border-indigo-300 hover:bg-indigo-100 hover:text-indigo-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                            Siguiente
                        </button>
                    @else
                        <span class="inline-flex h-11 items-center justify-center rounded-2xl border border-slate-200 bg-slate-100 px-4 text-sm font-medium text-slate-400">
                            Siguiente
                        </span>
                    @endif
                </div>
            </div>

            <div class="hidden items-center justify-between gap-6 sm:flex">
                <div class="min-w-0">
                    <div class="text-[11px] font-semibold uppercase tracking-[0.22em] text-slate-400">Navegacion</div>
                    <div class="mt-1 text-sm text-slate-600">
                        @if ($paginator->firstItem())
                            Mostrando <span class="font-semibold text-slate-900">{{ number_format($paginator->firstItem(), 0, ',', '.') }}-{{ number_format($paginator->lastItem(), 0, ',', '.') }}</span> de <span class="font-semibold text-slate-900">{{ number_format($paginator->total(), 0, ',', '.') }}</span> resultados
                        @else
                            Sin resultados para paginar
                        @endif
                    </div>
                </div>

                <div class="flex items-center justify-center gap-2">
                    @if ($paginator->onFirstPage())
                        <span aria-disabled="true" aria-label="{{ __('pagination.previous') }}" class="inline-flex h-11 items-center justify-center rounded-2xl border border-slate-200 bg-slate-100 px-4 text-sm font-medium text-slate-400">
                            Anterior
                        </span>
                    @else
                        <button type="button" wire:click="previousPage('{{ $paginator->getPageName() }}')" dusk="previousPage{{ $paginator->getPageName() == 'page' ? '' : '.' . $paginator->getPageName() }}.after" wire:loading.attr="disabled" rel="prev" aria-label="{{ __('pagination.previous') }}" class="inline-flex h-11 items-center justify-center rounded-2xl border border-slate-200 bg-white px-4 text-sm font-medium text-slate-700 shadow-sm transition hover:border-slate-300 hover:bg-slate-50 hover:text-slate-950 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                            Anterior
                        </button>
                    @endif

                    <div class="flex items-center gap-2 rounded-2xl border border-slate-200/80 bg-white/90 px-2 py-2 shadow-inner shadow-slate-100/70 ring-1 ring-slate-200/60">
                        @foreach ($elements as $element)
                            @if (is_string($element))
                                <span class="inline-flex h-8 min-w-8 items-center justify-center rounded-xl px-2 text-sm font-medium text-slate-400">
                                    {{ $element }}
                                </span>
                            @endif

                            @if (is_array($element))
                                @foreach ($element as $page => $url)
                                    @if ($page == $paginator->currentPage())
                                        <span aria-current="page" class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-indigo-600 px-0 text-sm font-semibold text-white shadow-sm shadow-indigo-600/30 ring-1 ring-indigo-500/40">
                                            {{ $page }}
                                        </span>
                                    @else
                                        <button type="button" wire:click="gotoPage({{ $page }}, '{{ $paginator->getPageName() }}')" class="inline-flex h-8 min-w-8 items-center justify-center rounded-xl px-2 text-sm font-medium text-slate-600 transition hover:bg-slate-100 hover:text-slate-950 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                                            {{ $page }}
                                        </button>
                                    @endif
                                @endforeach
                            @endif
                        @endforeach
                    </div>

                    @if ($paginator->hasMorePages())
                        <button type="button" wire:click="nextPage('{{ $paginator->getPageName() }}')" dusk="nextPage{{ $paginator->getPageName() == 'page' ? '' : '.' . $paginator->getPageName() }}.after" wire:loading.attr="disabled" rel="next" aria-label="{{ __('pagination.next') }}" class="inline-flex h-11 items-center justify-center rounded-2xl border border-indigo-200 bg-indigo-50 px-4 text-sm font-medium text-indigo-700 shadow-sm transition hover:border-indigo-300 hover:bg-indigo-100 hover:text-indigo-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                            Siguiente
                        </button>
                    @else
                        <span aria-disabled="true" aria-label="{{ __('pagination.next') }}" class="inline-flex h-11 items-center justify-center rounded-2xl border border-slate-200 bg-slate-100 px-4 text-sm font-medium text-slate-400">
                            Siguiente
                        </span>
                    @endif
                </div>
            </div>
        </div>
    </nav>
@endif
