{{-- Reusable AJAX pager. Expects $paginator (a LengthAwarePaginator).
     The fetch-and-swap script on each page delegates clicks on .pager-btn[data-page]. --}}
@if($paginator->hasPages())
@php
    $btnBase = 'background:var(--surface2);border:1px solid var(--border);border-radius:.5rem;padding:.35rem .85rem;font-size:.78rem;font-weight:600;color:var(--text);display:inline-flex;align-items:center;gap:.3rem;';
@endphp
<div style="display:flex;align-items:center;justify-content:center;gap:1rem;padding:1rem 1.25rem;border-top:1px solid var(--border);flex-wrap:wrap">
    <button type="button" class="pager-btn"
        @if(! $paginator->onFirstPage()) data-page="{{ $paginator->currentPage() - 1 }}" @endif
        style="{{ $btnBase }}{{ $paginator->onFirstPage() ? 'opacity:.4;cursor:not-allowed;pointer-events:none' : 'cursor:pointer' }}">
        <i class="bi bi-chevron-left"></i> Prev
    </button>
    <span style="font-size:.78rem;color:var(--muted)">
        Page {{ $paginator->currentPage() }} of {{ $paginator->lastPage() }}
        <span style="opacity:.5">·</span> {{ $paginator->total() }} total
    </span>
    <button type="button" class="pager-btn"
        @if($paginator->hasMorePages()) data-page="{{ $paginator->currentPage() + 1 }}" @endif
        style="{{ $btnBase }}{{ $paginator->hasMorePages() ? 'cursor:pointer' : 'opacity:.4;cursor:not-allowed;pointer-events:none' }}">
        Next <i class="bi bi-chevron-right"></i>
    </button>
</div>
@endif
