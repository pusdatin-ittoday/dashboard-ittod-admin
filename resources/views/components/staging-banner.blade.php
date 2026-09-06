@php
    $isStaging = app()->environment('staging') 
        || (bool) env('IS_STAGING', false) 
        || str_contains(config('app.url', ''), 'staging')
        || str_contains(request()->getHost(), 'staging');
@endphp

@if($isStaging)
    <div class="bg-gradient-to-r from-amber-600 via-amber-500 to-amber-600 text-white text-xs sm:text-sm font-semibold py-1.5 px-4 text-center shadow-md relative z-50 flex items-center justify-center gap-2 sm:gap-3 tracking-wide border-b border-amber-400">
        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-black bg-black/25 text-amber-100 uppercase tracking-widest border border-amber-300/50 animate-pulse">
            STAGING
        </span>
        <span class="truncate">⚠️ Perhatian: Lingkungan Staging & Testing — Data terpisah dari Production</span>
        <span class="hidden sm:inline-flex items-center px-2 py-0.5 rounded text-[10px] font-black bg-black/25 text-amber-100 uppercase tracking-widest border border-amber-300/50 animate-pulse">
            STAGING
        </span>
    </div>
@endif
