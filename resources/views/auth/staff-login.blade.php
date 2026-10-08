@extends('layouts.auth')

@section('title', 'Login Staff')

@section('content')
    <div class="rounded-2xl border border-slate-800 bg-[#111827] p-6 shadow-xl shadow-black/30">
        <h1 class="text-xl font-bold text-white">Login Staff</h1>
        <p class="mt-1 text-sm text-slate-400">Akses Kasir, Admin, atau Manajer (akun aktif saja).</p>

        <form method="POST" action="{{ route('staff.login.proses') }}" class="mt-6 space-y-4">
            @csrf

            <div>
                <label for="email" class="mb-1.5 block text-sm font-medium text-slate-300">Email</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
                       placeholder="admin@psrental.test"
                       class="w-full rounded-xl border border-slate-700 bg-ink-800 px-3 py-2.5 text-sm text-white placeholder-slate-500 focus:border-cyan-400 focus:outline-none focus:ring-1 focus:ring-cyan-400">
                @error('email') <p class="mt-1 text-xs text-rose-400">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="password" class="mb-1.5 block text-sm font-medium text-slate-300">Password</label>
                <input id="password" type="password" name="password" required
                       placeholder="••••••••"
                       class="w-full rounded-xl border border-slate-700 bg-ink-800 px-3 py-2.5 text-sm text-white placeholder-slate-500 focus:border-cyan-400 focus:outline-none focus:ring-1 focus:ring-cyan-400">
                @error('password') <p class="mt-1 text-xs text-rose-400">{{ $message }}</p> @enderror
            </div>

            <x-button type="submit" class="w-full">Masuk</x-button>
        </form>

        <div class="mt-5 rounded-xl border border-slate-800 bg-ink-800/60 p-3 text-xs text-slate-400">
            <p class="font-semibold text-slate-300">Akun demo</p>
            <p class="mt-1">Kasir: <span class="font-mono">kasir@psrental.test</span></p>
            <p>Admin: <span class="font-mono">admin@psrental.test</span></p>
            <p>Manajer: <span class="font-mono">manajer@psrental.test</span></p>
            <p class="mt-1">Password: <span class="font-mono">password123</span></p>
        </div>

        <a href="{{ route('beranda') }}" class="mt-4 block text-center text-sm text-slate-400 hover:text-cyan-300">← Kembali ke pilih akses</a>
    </div>
@endsection
