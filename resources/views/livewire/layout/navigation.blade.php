<?php

use App\Livewire\Actions\Logout;
use Livewire\Volt\Component;

new class extends Component
{
    /**
     * Log the current user out of the application.
     */
    public function logout(Logout $logout): void
    {
        $logout();

        $this->redirect('/', navigate: true);
    }
}; ?>

<nav x-data="{ open: false }" class="border-b border-blue-950/70 text-white shadow-[0_10px_35px_rgba(7,0,110,0.32)]" style="background-color: #07006e;">
    <!-- Primary Navigation Menu -->
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="flex min-h-[76px] items-center justify-between gap-4 py-3">
            <div class="flex items-center gap-6">
                <!-- Logo -->
                <div class="flex shrink-0 items-center">
                    <a href="{{ route('dashboard') }}" wire:navigate class="flex items-center gap-3 rounded-2xl px-2 py-1.5 transition hover:bg-white/5">
                        <x-application-logo class="block h-8 w-auto sm:h-9" />
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden items-center gap-1 rounded-full border border-white/10 bg-white/5 p-1.5 shadow-inner sm:flex">
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')" wire:navigate>
                        {{ __('Dashboard') }}
                    </x-nav-link>

                    <x-nav-link :href="route('ventas.index')" :active="request()->routeIs('ventas.*')" wire:navigate>
                        {{ __('Ventas') }}
                    </x-nav-link>

                    <x-nav-link :href="route('proyectos.index')" :active="request()->routeIs('proyectos.*')" wire:navigate>
                        {{ __('Proyectos') }}
                    </x-nav-link>

                      <x-nav-link :href="route('cobros.index')" :active="request()->routeIs('cobros.*')" wire:navigate>
                        {{ __('Cobros') }}
                    </x-nav-link>

                    <x-nav-link :href="route('clientes.index')" :active="request()->routeIs('clientes.*')" wire:navigate>
                        {{ __('Clientes') }}
                    </x-nav-link>

                    <x-nav-link :href="route('developers.index')" :active="request()->routeIs('developers.*')" wire:navigate>
                        {{ __('Developers') }}
                    </x-nav-link>

                </div>
            </div>

            <!-- Settings Dropdown -->
            <div class="hidden sm:flex sm:items-center sm:ms-6">
                <x-dropdown align="right" width="56" contentClasses="overflow-hidden rounded-2xl border border-slate-200 bg-white py-2 shadow-xl">
                    <x-slot name="trigger">
                        <button
                            class="inline-flex items-center gap-3 rounded-2xl border border-white/10 bg-white/5 px-3 py-2 text-sm font-medium text-slate-200 transition hover:bg-white/10 hover:text-white focus:outline-none focus:ring-2 focus:ring-indigo-400/60">
                            <span class="flex h-9 w-9 items-center justify-center rounded-full bg-gradient-to-br from-indigo-500 to-sky-500 text-xs font-semibold text-white shadow-sm">
                                {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                            </span>

                            <span class="max-w-[11rem] truncate" x-data="{{ json_encode(['name' => auth()->user()->name]) }}" x-text="name"
                                x-on:profile-updated.window="name = $event.detail.name"></span>

                            <div>
                                <svg class="h-4 w-4 fill-current text-slate-400" xmlns="http://www.w3.org/2000/svg"
                                     viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                        clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile')" wire:navigate>
                            {{ __('Profile') }}
                        </x-dropdown-link>

                        <x-dropdown-link :href="route('settings.whatsapp')" wire:navigate>
                            {{ __('Configuración WhatsApp') }}
                        </x-dropdown-link>

                        <!-- Authentication -->
                        <button wire:click="logout" class="w-full text-start">
                            <x-dropdown-link>
                                {{ __('Log Out') }}
                            </x-dropdown-link>
                        </button>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Hamburger -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open"
                    class="inline-flex h-11 w-11 items-center justify-center rounded-2xl border border-white/10 bg-white/5 text-slate-300 transition hover:bg-white/10 hover:text-white focus:outline-none focus:ring-2 focus:ring-indigo-400/60">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{ 'hidden': open, 'inline-flex': !open }" class="inline-flex"
                            stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{ 'hidden': !open, 'inline-flex': open }" class="hidden" stroke-linecap="round"
                            stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{ 'block': open, 'hidden': !open }" class="hidden border-t border-white/10 sm:hidden" style="background-color: rgba(7, 0, 110, 0.95);">
        <div class="space-y-2 px-4 pb-4 pt-4">
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')" wire:navigate>
                {{ __('Dashboard') }}
            </x-responsive-nav-link>

            <x-responsive-nav-link :href="route('ventas.index')" :active="request()->routeIs('ventas.*')" wire:navigate>
                {{ __('Ventas') }}
            </x-responsive-nav-link>

            <x-responsive-nav-link :href="route('proyectos.index')" :active="request()->routeIs('proyectos.*')" wire:navigate>
                {{ __('Proyectos') }}
            </x-responsive-nav-link>

             <x-responsive-nav-link :href="route('cobros.index')" :active="request()->routeIs('cobros.*')" wire:navigate>
                {{ __('Cobros') }}
            </x-responsive-nav-link>


            <x-responsive-nav-link :href="route('clientes.index')" :active="request()->routeIs('clientes.*')" wire:navigate>
                {{ __('Clientes') }}
            </x-responsive-nav-link>

            <x-responsive-nav-link :href="route('developers.index')" :active="request()->routeIs('developers.*')" wire:navigate>
                {{ __('Developers') }}
            </x-responsive-nav-link>
        </div>

        <!-- Responsive Settings Options -->
        <div class="border-t border-white/10 px-4 pb-4 pt-4">
            <div class="rounded-3xl border border-white/10 bg-white/5 px-4 py-4">
                <div class="text-sm font-semibold text-white" x-data="{{ json_encode(['name' => auth()->user()->name]) }}" x-text="name"
                    x-on:profile-updated.window="name = $event.detail.name"></div>
                <div class="mt-1 text-sm text-slate-400">{{ auth()->user()->email }}</div>

                <div class="mt-4 space-y-2">
                    <x-responsive-nav-link :href="route('profile')" wire:navigate>
                        {{ __('Profile') }}
                    </x-responsive-nav-link>

                    <x-responsive-nav-link :href="route('settings.whatsapp')" wire:navigate>
                        {{ __('Configuración WhatsApp') }}
                    </x-responsive-nav-link>

                    <!-- Authentication -->
                    <button wire:click="logout" class="w-full text-start">
                        <x-responsive-nav-link>
                            {{ __('Log Out') }}
                        </x-responsive-nav-link>
                    </button>
                </div>
            </div>
        </div>
    </div>
</nav>
