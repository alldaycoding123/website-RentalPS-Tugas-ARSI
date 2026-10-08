@props(['label' => '', 'value' => '', 'icon' => '📊', 'accent' => 'cyan'])

<div {{ $attributes->merge(['class' => 'rounded-2xl border border-slate-800 bg-[#111827] p-5']) }}>
    <div class="flex items-center justify-between">
        <span class="text-sm text-slate-400">{{ $label }}</span>
        <span class="text-xl">{{ $icon }}</span>
    </div>
    <p class="mt-2 text-2xl font-bold text-white">{{ $value }}</p>
    @if($slot)
        <div class="mt-1 text-xs text-slate-500">{{ $slot }}</div>
    @endif
</div>
