<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'PSRental') — Sistem Informasi Booking Rental PlayStation</title>

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

    {{-- Font Google: Inter + JetBrains Mono --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">

    {{-- Alpine.js CDN untuk interaksi ringan --}}
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        body { background: radial-gradient(1200px 600px at 50% -10%, #14203a 0%, #0a0e1a 55%); }
    </style>
    @stack('head')
</head>
<body class="min-h-screen bg-ink-900 font-sans text-slate-200 antialiased">

<div class="mx-auto w-full max-w-6xl px-4 pb-24 pt-4 sm:px-6">

    {{-- Header + navigasi dua tab --}}
    <header class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <a href="{{ route('beranda') }}" class="flex items-center gap-2">
            <span class="grid h-9 w-9 place-items-center rounded-xl bg-gradient-to-br from-cyan-400 to-indigo-500 text-lg font-black text-white">PS</span>
            <span class="text-lg font-extrabold tracking-tight"><span class="text-white">PS</span><span class="text-cyan-400">Rental</span></span>
        </a>

        @if(session('pelanggan_id'))
            <nav class="flex items-center gap-2">
                <a href="{{ route('customer.units') }}"
                   class="rounded-xl px-3 py-2 text-sm font-semibold {{ request()->routeIs('customer.units*') ? 'bg-cyan-500/15 text-cyan-300 border border-cyan-500/40' : 'text-slate-400 border border-transparent hover:text-white' }}">
                    Unit PS
                </a>
                <a href="{{ route('customer.riwayat') }}"
                   class="rounded-xl px-3 py-2 text-sm font-semibold {{ request()->routeIs('customer.riwayat*') ? 'bg-cyan-500/15 text-cyan-300 border border-cyan-500/40' : 'text-slate-400 border border-transparent hover:text-white' }}">
                    Booking Saya
                </a>
                <form method="POST" action="{{ route('customer.logout') }}" class="inline">
                    @csrf
                    <button type="submit" class="rounded-xl border border-slate-700 px-3 py-2 text-sm font-semibold text-slate-400 hover:bg-slate-800 hover:text-white">Keluar</button>
                </form>
            </nav>
        @endif
    </header>

    <x-flash />

    <main class="mt-4">
        @yield('content')
    </main>

    <footer class="mt-10 border-t border-slate-800 pt-5 text-center text-xs text-slate-500">
        © {{ date('Y') }} PSRental — Sistem Informasi Booking Rental PlayStation · Asia/Makassar
    </footer>
</div>

@stack('scripts')
</body>
</html>
