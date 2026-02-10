<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Proyectos Nube App</title>
  @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-white text-gray-900 antialiased">
  {{-- Top bar --}}
  <header class="sticky top-0 z-40 border-b border-gray-100 bg-white/70 backdrop-blur">
    <div class="mx-auto flex max-w-7xl items-center justify-between px-6 py-4">
      <div class="flex items-center gap-3">
        {{-- Logo Nube: reemplazá por tu imagen si querés --}}
        <div class="h-10 w-10 rounded-xl bg-gradient-to-br from-blue-600 to-purple-600 text-white grid place-items-center font-bold">
          N
        </div>
        <div class="leading-tight">
          <p class="font-semibold">Nube</p>
          <p class="text-xs text-gray-500">Proyectos Nube App</p>
        </div>
      </div>

      <nav class="flex items-center gap-3">
        @auth
          <a href="{{ url('/dashboard') }}"
             class="rounded-lg px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100 transition">
            Mi cuenta
          </a>
        @else
          <a href="{{ route('login') }}"
             class="rounded-lg px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100 transition">
            Ingresar
          </a>
          <a href="{{ route('register') }}"
             class="rounded-lg bg-gray-900 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-800 transition">
            Crear cuenta
          </a>
        @endauth
      </nav>
    </div>
  </header>

  {{-- HERO --}}
  <section class="relative overflow-hidden">
    <div class="absolute inset-0 -z-10 bg-gradient-to-br from-blue-50 via-white to-purple-50"></div>
    <div class="absolute -top-24 left-1/2 -z-10 h-72 w-72 -translate-x-1/2 rounded-full bg-blue-200/40 blur-3xl"></div>
    <div class="absolute -bottom-24 right-10 -z-10 h-72 w-72 rounded-full bg-purple-200/40 blur-3xl"></div>

    <div class="mx-auto max-w-7xl px-6 py-20">
      <div class="grid items-center gap-14 lg:grid-cols-2">
        {{-- Copy --}}
        <div>
          <div class="inline-flex items-center gap-2 rounded-full bg-blue-100 px-4 py-2 text-sm text-blue-700">
            <span>🚀</span>
            <span>Gestión interna en tiempo real</span>
          </div>

          <h1 class="mt-6 text-5xl font-bold tracking-tight sm:text-6xl">
            Proyectos Nube App
            <span class="block bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text text-transparent">
              control total, sin caos
            </span>
          </h1>

          <p class="mt-6 text-xl text-gray-600">
            Aplicación interna de Nube para gestionar proyectos de manera ágil, con costos claros,
            fechas definidas, información centralizada y automatización de tareas.
          </p>

          {{-- CTAs --}}
          <div class="mt-10 flex flex-col gap-4 sm:flex-row sm:items-center">
            @auth
              <a href="{{ url('/dashboard') }}"
                 class="inline-flex items-center justify-center rounded-xl bg-gray-900 px-6 py-3 text-base font-semibold text-white hover:bg-gray-800 transition">
                Ir a mi cuenta
                <svg class="ml-2 h-5 w-5" viewBox="0 0 24 24" fill="none">
                  <path d="M5 12h14" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                  <path d="M13 6l6 6-6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
              </a>
            @else
              <a href="{{ route('register') }}"
                 class="inline-flex items-center justify-center rounded-xl bg-gray-900 px-6 py-3 text-base font-semibold text-white hover:bg-gray-800 transition">
                Crear cuenta
                <svg class="ml-2 h-5 w-5" viewBox="0 0 24 24" fill="none">
                  <path d="M5 12h14" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                  <path d="M13 6l6 6-6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
              </a>

              <a href="{{ route('login') }}"
                 class="inline-flex items-center justify-center rounded-xl border border-gray-300 bg-white px-6 py-3 text-base font-semibold text-gray-800 hover:bg-gray-50 transition">
                Ingresar
              </a>
            @endauth
          </div>

          {{-- Mini trust row --}}
          <div class="mt-10 grid grid-cols-2 gap-4 sm:grid-cols-3">
            <div class="rounded-xl border border-gray-200 bg-white/70 p-4">
              <p class="text-sm font-semibold">Multiusuario</p>
              <p class="text-sm text-gray-600">Roles, accesos y trazabilidad</p>
            </div>
            <div class="rounded-xl border border-gray-200 bg-white/70 p-4">
              <p class="text-sm font-semibold">Costos claros</p>
              <p class="text-sm text-gray-600">Estados, márgenes y costos</p>
            </div>
            <div class="hidden sm:block rounded-xl border border-gray-200 bg-white/70 p-4">
              <p class="text-sm font-semibold">Automatización</p>
              <p class="text-sm text-gray-600">Procesos formales sin fricción</p>
            </div>
          </div>
        </div>

        {{-- Hero Image / Screenshot --}}
        <div class="relative">
          <div class="absolute -inset-6 -z-10 rounded-3xl bg-gradient-to-br from-blue-200/40 to-purple-200/40 blur-2xl"></div>

          <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-2xl">
            {{-- Reemplazá el src por screenshot del sistema o imagen de marca --}}
            <img
              src="https://images.unsplash.com/photo-1521737604893-d14cc237f11d?auto=format&fit=crop&w=1600&q=80"
              alt="Vista del sistema / equipo colaborando"
              class="h-auto w-full"
            />
          </div>

          {{-- Badge flotante --}}
          <div class="absolute -bottom-6 left-6 rounded-2xl border border-gray-200 bg-white px-5 py-4 shadow-lg">
            <p class="text-sm font-semibold">Información centralizada</p>
            <p class="text-sm text-gray-600">Todo el proyecto en un solo lugar</p>
          </div>
        </div>
      </div>
    </div>
  </section>

  {{-- FEATURES --}}
  <section class="py-20 bg-white">
    <div class="mx-auto max-w-7xl px-6">
      <div class="mx-auto max-w-3xl text-center">
        <h2 class="text-4xl font-bold tracking-tight sm:text-5xl">Potenciá tu gestión de proyectos</h2>
        <p class="mt-4 text-xl text-gray-600">
          Herramientas diseñadas para optimizar cada fase de tus proyectos en ejecución.
        </p>
      </div>

      <div class="mt-14 grid gap-6 md:grid-cols-2 lg:grid-cols-3">
        @php
          $features = [
            ['title' => 'Agilidad en ejecución', 'desc' => 'Flujo claro para tareas, estados y responsables.', 'icon' => 'zap'],
            ['title' => 'Claridad en costos', 'desc' => 'Costos, presupuestos y márgenes visibles.', 'icon' => 'bar'],
            ['title' => 'Fechas claras', 'desc' => 'Entregables, hitos y deadlines sin confusión.', 'icon' => 'clock'],
            ['title' => 'Información centralizada', 'desc' => 'Documentos, notas y decisiones en un solo lugar.', 'icon' => 'file'],
            ['title' => 'Automatización formal', 'desc' => 'Procesos repetibles para escalar sin caos.', 'icon' => 'check'],
            ['title' => 'Multiusuario', 'desc' => 'Accesos por rol: equipo y clientes con control.', 'icon' => 'users'],
          ];
        @endphp

        @foreach ($features as $f)
          <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm hover:shadow-lg transition">
            <div class="mb-4 inline-flex h-12 w-12 items-center justify-center rounded-xl bg-blue-100 text-blue-700">
              {{-- Iconos SVG simples --}}
              @switch($f['icon'])
                @case('zap')
                  <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none">
                    <path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>
                  </svg>
                  @break
                @case('bar')
                  <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none">
                    <path d="M4 20V10" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                    <path d="M10 20V4" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                    <path d="M16 20v-7" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                    <path d="M22 20V8" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                  </svg>
                  @break
                @case('clock')
                  <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none">
                    <path d="M12 22a10 10 0 1 0-10-10 10 10 0 0 0 10 10Z" stroke="currentColor" stroke-width="2"/>
                    <path d="M12 6v6l4 2" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                  </svg>
                  @break
                @case('file')
                  <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>
                    <path d="M14 2v6h6" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>
                  </svg>
                  @break
                @case('check')
                  <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none">
                    <path d="M20 6 9 17l-5-5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                  </svg>
                  @break
                @case('users')
                  <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" stroke="currentColor" stroke-width="2"/>
                    <path d="M9 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8Z" stroke="currentColor" stroke-width="2"/>
                    <path d="M23 21v-2a4 4 0 0 0-3-3.87" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                    <path d="M16 3.13a4 4 0 0 1 0 7.75" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                  </svg>
                  @break
              @endswitch
            </div>

            <h3 class="text-xl font-semibold">{{ $f['title'] }}</h3>
            <p class="mt-2 text-gray-600">{{ $f['desc'] }}</p>
          </div>
        @endforeach
      </div>
    </div>
  </section>

  {{-- HOW IT WORKS --}}
  <section class="py-20 bg-gradient-to-br from-gray-50 to-blue-50">
    <div class="mx-auto max-w-7xl px-6">
      <div class="mx-auto max-w-3xl text-center">
        <h2 class="text-4xl font-bold tracking-tight sm:text-5xl">¿Cómo funciona?</h2>
        <p class="mt-4 text-xl text-gray-600">Cuatro pasos simples para ordenar la ejecución y la comunicación.</p>
      </div>

      @php
        $steps = [
          ['n' => '01', 't' => 'Creá el proyecto', 'd' => 'Definí objetivos, plazos y asigná responsables.'],
          ['n' => '02', 't' => 'Coordiná el trabajo', 'd' => 'Tareas claras, estados visibles y seguimiento continuo.'],
          ['n' => '03', 't' => 'Mantené informado al cliente', 'd' => 'Acceso controlado para ver avances y decisiones.'],
          ['n' => '04', 't' => 'Analizá y optimizá', 'd' => 'Reportes y métricas para mejorar el proceso.'],
        ];
      @endphp

      <div class="mt-14 space-y-6">
        @foreach ($steps as $i => $s)
          <div class="flex flex-col gap-6 rounded-2xl border border-gray-200 bg-white p-6 shadow-sm hover:shadow-md transition md:flex-row md:items-center md:p-8">
            <div class="flex-shrink-0">
              <div class="grid h-16 w-16 place-items-center rounded-xl bg-gradient-to-br from-blue-600 to-purple-600 text-2xl font-semibold text-white">
                {{ $s['n'] }}
              </div>
            </div>

            <div class="flex-1">
              <h3 class="text-2xl font-semibold">{{ $s['t'] }}</h3>
              <p class="mt-1 text-lg text-gray-600">{{ $s['d'] }}</p>
            </div>

            @if ($i < count($steps) - 1)
              <div class="hidden md:block text-gray-400">
                <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none">
                  <path d="M5 12h14" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                  <path d="M13 6l6 6-6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
              </div>
            @endif
          </div>
        @endforeach
      </div>
    </div>
  </section>

  {{-- FOOTER / CTA --}}
  <footer class="bg-gray-900 text-white">
    <div class="mx-auto max-w-7xl px-6 py-16">
      <div class="mx-auto max-w-3xl text-center">
        <h2 class="text-3xl font-bold sm:text-4xl">¿Listo para ordenar la ejecución?</h2>
        <p class="mt-4 text-xl text-gray-400">
          Entrá y gestioná proyectos con claridad, seguimiento real y comunicación simple.
        </p>

        <div class="mt-8 flex flex-col items-center justify-center gap-4 sm:flex-row">
          @auth
            <a href="{{ url('/dashboard') }}"
               class="inline-flex w-full items-center justify-center rounded-xl bg-blue-600 px-8 py-3 text-lg font-semibold hover:bg-blue-700 transition sm:w-auto">
              Ir a mi cuenta
            </a>
          @else
            <a href="{{ route('login') }}"
               class="inline-flex w-full items-center justify-center rounded-xl bg-blue-600 px-8 py-3 text-lg font-semibold hover:bg-blue-700 transition sm:w-auto">
              Ingresar
            </a>
            <a href="{{ route('register') }}"
               class="inline-flex w-full items-center justify-center rounded-xl border-2 border-gray-600 px-8 py-3 text-lg font-semibold hover:bg-gray-800 transition sm:w-auto">
              Crear cuenta
            </a>
          @endauth
        </div>
      </div>

      <div class="mt-12 border-t border-gray-800 pt-8 text-center text-gray-400">
        © {{ date('Y') }} Nube · Proyectos Nube App
      </div>
    </div>
  </footer>
</body>
</html>
