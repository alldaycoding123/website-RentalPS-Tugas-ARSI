@php
    $sukses = session('success');
    $gagal = session('error');
@endphp

@if($sukses || $gagal)
    <div class="space-y-2" data-flash>
        @if($sukses)
            <div class="flex items-start gap-3 rounded-xl border border-emerald-500/40 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-200" data-flash-item>
                <span class="mt-0.5">✅</span>
                <span class="flex-1">{{ $sukses }}</span>
                <button type="button" class="text-emerald-300/70 hover:text-emerald-200" onclick="this.closest('[data-flash-item]').remove()">✕</button>
            </div>
        @endif

        @if($gagal)
            <div class="flex items-start gap-3 rounded-xl border border-rose-500/40 bg-rose-500/10 px-4 py-3 text-sm text-rose-200" data-flash-item>
                <span class="mt-0.5">⚠️</span>
                <span class="flex-1">{{ $gagal }}</span>
                <button type="button" class="text-rose-300/70 hover:text-rose-200" onclick="this.closest('[data-flash-item]').remove()">✕</button>
            </div>
        @endif
    </div>
@endif
