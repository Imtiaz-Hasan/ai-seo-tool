@props(['value' => '0', 'size' => 200])

{{-- Animated SEO score gauge. `value` is an Alpine expression evaluated in the
     surrounding x-data scope (e.g. "report.overall"), so the ring sweeps and
     recolours live as the score changes. Circumference = 2·π·54 ≈ 339.292. --}}
<div class="relative shrink-0" style="width: {{ $size }}px; height: {{ $size }}px;">
    <svg class="w-full h-full -rotate-90" viewBox="0 0 120 120">
        <circle cx="60" cy="60" r="54" fill="none" stroke-width="11"
                class="text-gray-200 dark:text-white/10" stroke="currentColor" />
        <circle cx="60" cy="60" r="54" fill="none" stroke-width="11" stroke-linecap="round"
                class="ring-progress"
                stroke-dasharray="339.292"
                :stroke="window.scoreColor({{ $value }})"
                :stroke-dashoffset="339.292 - (Math.max(0, Math.min(100, {{ $value }})) / 100) * 339.292" />
    </svg>
    <div class="absolute inset-0 flex flex-col items-center justify-center">
        <span class="text-5xl font-extrabold tracking-tight text-gray-900 dark:text-white" x-text="Math.round({{ $value }})">0</span>
        <span class="mt-0.5 text-xs font-semibold uppercase tracking-wide text-gray-400">SEO score</span>
    </div>
</div>
