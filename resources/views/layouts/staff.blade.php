<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') — PSRental</title>

    {{-- Tailwind via CDN: tanpa Node.js / Vite / npm --}}
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        ink: { 900: '#0a0e1a', 800: '#0f1424', 700: '#111827' },
                    },
                    fontFamily: {
                        sans: ['Inter', 'ui-sans-serif', 'system-ui'],
                        mono: ['"JetBrains Mono"', 'ui-monospace', 'monospace'],
                    },
                },
            },
        };
    </script>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">

    <style>
        body { background: #0a0e1a; }
    </style>
    @stack('head')
</head>
<body class="min-h-screen bg-ink-900 font-sans text-slate-200 antialiased">

@php
    $staff = auth('staff')->user();
    $role = $staff?->role ?? '';

    // Menu sidebar per peran
    $menu = match ($role) {
        'KASIR' => [
            ['route' => 'kasir.dashboard', 'label' => 'Dashboard', 'icon' => '🏠'],
            ['route' => 'kasir.checkin', 'label' => 'Check-in', 'icon' => '🎟️'],
            ['route' => 'kasir.checkout', 'label' => 'Check-out', 'icon' => '🚪'],
            ['route' => 'kasir.pembayaran', 'label' => 'Proses Pembayaran', 'icon' => '💳'],
        ],
        'MANAJER' => [
            ['route' => 'manajer.dashboard', 'label' => 'Dashboard', 'icon' => '🏠'],
            ['route' => 'manajer.laporan.pendapatan', 'label' => 'Laporan Pendapatan', 'icon' => '💰'],
            ['route' => 'manajer.laporan.performa', 'label' => 'Performa Unit', 'icon' => '🎮'],
            ['route' => 'manajer.laporan.tren', 'label' => 'Tren & Analitik', 'icon' => '📈'],
        ],
        default => [
            ['route' => 'admin.dashboard', 'label' => 'Dashboard', 'icon' => '🏠'],
            ['route' => 'admin.units.index', 'label' => 'Data PS', 'icon' => '🎮'],
            ['route' => 'admin.jadwal.index', 'label' => 'Jadwal', 'icon' => '🗓️'],
            ['route' => 'admin.bookings.index', 'label' => 'Booking', 'icon' => '📋'],
            ['route' => 'admin.pelanggans.index', 'label' => 'Pelanggan', 'icon' => '👥'],
            ['route' => 'admin.pembayarans.index', 'label' => 'Pembayaran', 'icon' => '💳'],
            ['route' => 'admin.laporan.index', 'label' => 'Laporan', 'icon' => '📊'],
        ],
    };
@endphp

<div class="flex min-h-screen">

    {{-- Sidebar --}}
    <aside class="hidden w-64 shrink-0 flex-col border-r border-slate-800 bg-ink-800 lg:flex">
        <div class="flex items-center gap-2 px-5 py-5">
            <span class="grid h-9 w-9 place-items-center rounded-xl bg-gradient-to-br from-cyan-400 to-indigo-500 text-lg font-black text-white">PS</span>
            <span class="text-lg font-extrabold tracking-tight"><span class="text-white">PS</span><span class="text-cyan-400">Rental</span></span>
        </div>

        <p class="px-5 pb-2 text-xs font-semibold uppercase tracking-wider text-slate-500">Menu {{ $role }}</p>

        <nav class="flex-1 space-y-1 px-3">
            @foreach($menu as $item)
                <a href="{{ route($item['route']) }}"
                   class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition
                    {{ request()->routeIs($item['route']) ? 'bg-gradient-to-r from-cyan-500/20 to-indigo-500/20 text-white ring-1 ring-cyan-400/30' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                    <span>{{ $item['icon'] }}</span> {{ $item['label'] }}
                </a>
            @endforeach
        </nav>

        <div class="border-t border-slate-800 p-4">
            <div class="mb-3 flex items-center gap-3">
                <span class="grid h-9 w-9 place-items-center rounded-full bg-slate-700 text-sm font-bold text-white">{{ strtoupper(substr($staff->nama ?? 'S', 0, 1)) }}</span>
                <div class="min-w-0">
                    <p class="truncate text-sm font-semibold text-white">{{ $staff->nama ?? '-' }}</p>
                    <p class="truncate text-xs text-slate-500">{{ $staff->role ?? '-' }}</p>
                </div>
            </div>
            <form method="POST" action="{{ route('staff.logout') }}">
                @csrf
                <button type="submit" class="w-full rounded-xl border border-slate-700 px-3 py-2 text-sm font-semibold text-slate-300 hover:bg-slate-800 hover:text-white">Keluar</button>
            </form>
        </div>
    </aside>

    {{-- Konten --}}
    <div class="flex min-w-0 flex-1 flex-col">
        {{-- Topbar --}}
        <header class="sticky top-0 z-20 border-b border-slate-800 bg-ink-900/90 backdrop-blur">
            <div class="flex items-center justify-between gap-3 px-4 py-3 sm:px-6">
                <div class="flex items-center gap-3">
                    <a href="{{ route('beranda') }}" class="flex items-center gap-2 lg:hidden">
                        <span class="grid h-8 w-8 place-items-center rounded-lg bg-gradient-to-br from-cyan-400 to-indigo-500 text-sm font-black text-white">PS</span>
                    </a>
                    <div>
                        <h1 class="text-base font-bold text-white sm:text-lg">@yield('title', 'Dashboard')</h1>
                        <p class="hidden text-xs text-slate-500 sm:block">@yield('subtitle', '')</p>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <span class="hidden rounded-full border border-cyan-500/40 bg-cyan-500/10 px-3 py-1 text-xs font-semibold text-cyan-300 sm:inline-block">{{ $role }}</span>
                    <form method="POST" action="{{ route('staff.logout') }}" class="lg:hidden">
                        @csrf
                        <button type="submit" class="rounded-xl border border-slate-700 px-3 py-2 text-xs font-semibold text-slate-300 hover:bg-slate-800">Keluar</button>
                    </form>
                </div>
            </div>

            {{-- Navigasi horizontal untuk layar kecil --}}
            <nav class="flex gap-1 overflow-x-auto border-t border-slate-800 px-3 py-2 lg:hidden">
                @foreach($menu as $item)
                    <a href="{{ route($item['route']) }}"
                       class="whitespace-nowrap rounded-lg px-3 py-1.5 text-xs font-semibold {{ request()->routeIs($item['route']) ? 'bg-cyan-500/15 text-cyan-300' : 'text-slate-400' }}">
                        {{ $item['label'] }}
                    </a>
                @endforeach
            </nav>
        </header>

        <main class="flex-1 px-4 py-6 sm:px-6">
            <x-flash />
            @yield('content')
        </main>
    </div>
</div>

@stack('scripts')
</body>
</html>
