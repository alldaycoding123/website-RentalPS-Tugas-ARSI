<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Login') — PSRental</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: { ink: { 900: '#0a0e1a', 800: '#0f1424', 700: '#111827' } },
                    fontFamily: {
                        sans: ['Inter', 'ui-sans-serif', 'system-ui'],
                        mono: ['"JetBrains Mono"', 'ui-monospace', 'monospace'],
                    },
                },
            },
        };
    </script>

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">
    <style>body { background: radial-gradient(900px 500px at 50% -10%, #14203a 0%, #0a0e1a 60%); }</style>
    @stack('head')
</head>
<body class="flex min-h-screen items-center justify-center bg-ink-900 p-4 font-sans text-slate-200 antialiased">

<div class="w-full max-w-md">
    <a href="{{ route('beranda') }}" class="mb-6 flex items-center justify-center gap-2">
        <span class="grid h-10 w-10 place-items-center rounded-xl bg-gradient-to-br from-cyan-400 to-indigo-500 text-xl font-black text-white">PS</span>
        <span class="text-xl font-extrabold tracking-tight"><span class="text-white">PS</span><span class="text-cyan-400">Rental</span></span>
    </a>

    <x-flash />

    @yield('content')
</div>

@stack('scripts')
</body>
</html>
