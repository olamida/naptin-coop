@props(['route', 'params' => []])

<div class="flex items-center gap-2">
    <a href="{{ route($route, array_merge($params, ['format' => 'xlsx'])) }}"
       title="Download Excel"
       class="inline-flex items-center gap-1.5 bg-emerald-600 hover:bg-emerald-700 text-white px-3 py-2 rounded-[10px] text-xs font-medium transition">
        <span class="material-symbols-outlined text-base">download</span> Excel
    </a>
    <a href="{{ route($route, array_merge($params, ['format' => 'pdf'])) }}"
       title="Download PDF (QR-stamped with report hash)"
       class="inline-flex items-center gap-1.5 bg-rose-600 hover:bg-rose-700 text-white px-3 py-2 rounded-[10px] text-xs font-medium transition">
        <span class="material-symbols-outlined text-base">qr_code_2</span> PDF
    </a>
</div>
