@props(['title' => null, 'subtitle' => null])

<div {{ $attributes->merge(['class' => 'rounded-2xl border border-slate-800 bg-[#111827] p-5 shadow-lg shadow-black/20']) }}>
    @if($title)
        <div class="mb-4 flex items-start justify-between gap-3">
            <div>
                <h3 class="text-base font-semibold text-white">{{ $title }}</h3>
                @if($subtitle)
                    <p class="mt-0.5 text-sm text-slate-400">{{ $subtitle }}</p>
                @endif
            </div>
            {{ $actions ?? '' }}
        </div>
    @endif

    {{ $slot }}
</div>
