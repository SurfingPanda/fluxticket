@extends('layouts.app')
@section('title','Knowledge Base')
@section('topbar-title','Knowledge Base')
@section('topbar-sub','Browse articles and guides')

@push('styles')
<style>
    /* ── Hero ── */
    .kb-hero {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        background: linear-gradient(135deg, rgba(99,102,241,.12), rgba(124,58,237,.08));
        border: 1px solid rgba(99,102,241,.2);
        border-radius: 1rem;
        padding: 1.1rem 1.4rem;
        margin-bottom: 2rem;
    }
    .kb-hero-text { flex-shrink: 0; }
    .kb-hero-text h2 { font-size:1.05rem;font-weight:700;color:var(--text);margin:0 0 .15rem;display:flex;align-items:center;gap:.45rem; }
    .kb-hero-text p  { font-size:.76rem;color:var(--muted);margin:0; }

    /* ── Category grid ── */
    .kb-category-grid { display:grid;grid-template-columns:repeat(4,1fr);gap:1rem;margin-bottom:1.75rem; }
    .kb-cat-card { background:var(--surface);border:1px solid var(--border);border-radius:.875rem;padding:1rem 1.15rem;display:flex;align-items:center;gap:.85rem;cursor:pointer;transition:border-color .2s,transform .2s,box-shadow .2s;text-decoration:none; }
    .kb-cat-card:hover { border-color:rgba(99,102,241,.4);transform:translateY(-2px);box-shadow:0 8px 20px rgba(0,0,0,.12); }
    .kb-cat-icon { width:40px;height:40px;border-radius:.6rem;display:flex;align-items:center;justify-content:center;font-size:1.1rem;flex-shrink:0; }
    .kb-cat-title { font-size:.835rem;font-weight:700;color:var(--text);margin-bottom:.1rem; }
    .kb-cat-count { font-size:.68rem;color:var(--muted); }

    /* ── Articles panel ── */
    .kb-articles-panel { background:var(--surface);border:1px solid var(--border);border-radius:.875rem;overflow:hidden; }
    .kb-panel-header { display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:.75rem;padding:1rem 1.4rem;border-bottom:1px solid var(--border); }
    .kb-panel-header h3 { font-size:.9rem;font-weight:700;color:var(--text);margin:0;display:flex;align-items:center;gap:.5rem; }
    .kb-empty { padding:3.5rem;text-align:center;color:var(--muted); }
    .kb-empty i { font-size:2.5rem;opacity:.25;display:block;margin-bottom:.75rem; }
    .kb-empty p { font-size:.875rem;margin:0; }

    /* Table */
    .kb-table { width:100%;border-collapse:collapse; }
    .kb-table thead tr { border-bottom:1px solid var(--border); }
    .kb-table thead th { padding:.65rem 1.1rem;font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--muted);text-align:left;white-space:nowrap; }
    .kb-table tbody tr { border-bottom:1px solid var(--border);transition:background .15s; }
    .kb-table tbody tr:last-child { border-bottom:none; }
    .kb-table tbody tr:hover { background:var(--surface2); }
    .kb-table td { padding:.75rem 1.1rem;font-size:.8rem;color:var(--text);vertical-align:middle; }

    .kb-title-cell { display:flex;align-items:center;gap:.75rem; }
    .kb-article-icon { width:34px;height:34px;border-radius:.5rem;display:flex;align-items:center;justify-content:center;font-size:.9rem;flex-shrink:0; }
    .kb-article-title { font-size:.835rem;font-weight:600;color:var(--text);margin-bottom:.15rem;line-height:1.3; }
    .kb-article-tags { display:flex;flex-wrap:wrap;gap:.3rem;margin-top:.25rem; }

    .kb-tag { display:inline-flex;align-items:center;padding:.1rem .5rem;border-radius:9999px;font-size:.63rem;font-weight:600;background:rgba(99,102,241,.12);color:#818cf8; }
    .kb-status-draft { display:inline-flex;align-items:center;gap:.3rem;padding:.22rem .65rem;border-radius:9999px;font-size:.68rem;font-weight:600;background:rgba(251,191,36,.12);color:#fbbf24;white-space:nowrap; }
    .kb-status-draft::before { content:'';width:6px;height:6px;border-radius:50%;background:#fbbf24;flex-shrink:0; }
    .kb-status-published { display:inline-flex;align-items:center;gap:.3rem;padding:.22rem .65rem;border-radius:9999px;font-size:.68rem;font-weight:600;background:rgba(52,211,153,.12);color:#34d399;white-space:nowrap; }
    .kb-status-published::before { content:'';width:6px;height:6px;border-radius:50%;background:#34d399;flex-shrink:0; }

    .btn-view-article { display:inline-flex;align-items:center;gap:.35rem;background:rgba(99,102,241,.12);color:#818cf8;border:1px solid rgba(99,102,241,.25);border-radius:.5rem;padding:.35rem .8rem;font-size:.76rem;font-weight:600;cursor:pointer;transition:all .15s;white-space:nowrap; }
    .btn-view-article:hover { background:rgba(99,102,241,.22);border-color:rgba(99,102,241,.45); }

    /* ── Buttons ── */
    .btn-new-article { display:inline-flex;align-items:center;gap:.4rem;background:var(--accent);color:#fff;border:none;border-radius:.6rem;padding:.45rem 1rem;font-size:.8rem;font-weight:600;cursor:pointer;text-decoration:none;transition:opacity .15s;white-space:nowrap; }
    .btn-new-article:hover { opacity:.88;color:#fff; }

    /* ── Modal ── */
    .kb-modal-overlay { display:none;position:fixed;inset:0;background:rgba(0,0,0,.55);z-index:999;align-items:center;justify-content:center;padding:1rem; }
    .kb-modal-overlay.open { display:flex; }
    .kb-modal { background:var(--surface);border:1px solid var(--border);border-radius:1rem;width:100%;max-width:560px;box-shadow:0 20px 60px rgba(0,0,0,.3);overflow:hidden;max-height:90vh;display:flex;flex-direction:column; }
    .kb-modal-header { display:flex;align-items:center;justify-content:space-between;padding:1rem 1.25rem;border-bottom:1px solid var(--border);flex-shrink:0; }
    .kb-modal-header h3 { font-size:.95rem;font-weight:700;color:var(--text);margin:0;display:flex;align-items:center;gap:.5rem; }
    .kb-modal-close { background:none;border:none;color:var(--muted);cursor:pointer;font-size:1rem;padding:.25rem;line-height:1;border-radius:.35rem;transition:background .15s,color .15s; }
    .kb-modal-close:hover { background:var(--surface2);color:var(--text); }
    .kb-modal-body { padding:1.25rem;overflow-y:auto;flex:1; }
    .kb-modal-footer { padding:.9rem 1.25rem;border-top:1px solid var(--border);display:flex;justify-content:flex-end;gap:.6rem;flex-shrink:0; }

    /* ── Form fields ── */
    .kb-field { margin-bottom:1rem; }
    .kb-field:last-child { margin-bottom:0; }
    .kb-label { display:block;font-size:.75rem;font-weight:600;color:var(--muted);text-transform:uppercase;letter-spacing:.04em;margin-bottom:.4rem; }
    .kb-label span { color:#f87171;margin-left:.15rem; }
    .kb-input, .kb-select, .kb-textarea { width:100%;background:var(--surface2);border:1px solid var(--border);border-radius:.6rem;padding:.55rem .85rem;font-size:.855rem;color:var(--text);outline:none;transition:border-color .2s,box-shadow .2s;font-family:inherit; }
    .kb-input:focus, .kb-select:focus, .kb-textarea:focus { border-color:var(--accent);box-shadow:0 0 0 3px rgba(99,102,241,.12); }
    .kb-textarea { resize:vertical;min-height:140px;line-height:1.6; }
    .kb-select { appearance:none;background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 16 16'%3E%3Cpath fill='%2394a3b8' d='M7.247 11.14 2.451 5.658C1.885 5.013 2.345 4 3.204 4h9.592a1 1 0 0 1 .753 1.659l-4.796 5.48a1 1 0 0 1-1.506 0z'/%3E%3C/svg%3E");background-repeat:no-repeat;background-position:right .75rem center;padding-right:2.2rem; }
    .kb-row2 { display:grid;grid-template-columns:1fr 1fr;gap:.85rem; }
    .kb-hint { font-size:.7rem;color:var(--muted);margin-top:.3rem; }

    .btn-cancel-modal { background:var(--surface2);border:1px solid var(--border);border-radius:.6rem;padding:.45rem 1rem;font-size:.8rem;font-weight:600;color:var(--muted);cursor:pointer;transition:all .15s; }
    .btn-cancel-modal:hover { color:var(--text); }
    .btn-save-article { background:var(--accent);color:#fff;border:none;border-radius:.6rem;padding:.45rem 1.1rem;font-size:.8rem;font-weight:600;cursor:pointer;transition:opacity .15s;display:inline-flex;align-items:center;gap:.4rem; }
    .btn-save-article:hover { opacity:.88; }

    /* ── Delete button ── */
    .kb-delete-btn { background:rgba(248,113,113,.12);color:#f87171;border:1px solid rgba(248,113,113,.25);border-radius:.6rem;padding:.42rem .9rem;font-size:.78rem;font-weight:600;cursor:pointer;align-items:center;gap:.35rem; }
    .kb-delete-btn:hover { background:rgba(248,113,113,.2); }

    /* ── Success toast ── */
    .kb-toast { position:fixed;bottom:1.5rem;right:1.5rem;background:rgba(52,211,153,.15);border:1px solid rgba(52,211,153,.3);color:#34d399;border-radius:.75rem;padding:.7rem 1.1rem;font-size:.82rem;font-weight:600;z-index:1100;display:flex;align-items:center;gap:.5rem;box-shadow:0 8px 24px rgba(0,0,0,.2);animation:kbSlideIn .25s ease; }
    @keyframes kbSlideIn { from { opacity:0;transform:translateY(8px); } to { opacity:1;transform:translateY(0); } }

    /* ── Responsive ── */
    @media (max-width:900px) { .kb-category-grid { grid-template-columns:repeat(2,1fr); } }
    @media (max-width:560px) { .kb-row2 { grid-template-columns:1fr; } }
    @media (max-width:380px) { .kb-category-grid { grid-template-columns:1fr; } }
</style>
@endpush

@section('content')

@php
$canWrite = auth()->user()->isSuperAdmin()
    || (auth()->user()->effectivePageAccess()['knowledge_write'] ?? false);

$categories = [
    ['label'=>'IT Support',   'icon'=>'bi-headset',       'color'=>'rgba(99,102,241,.15)',  'text'=>'#818cf8',  'modal'=>null],
    ['label'=>'Hardware',     'icon'=>'bi-cpu',            'color'=>'rgba(245,158,11,.15)',  'text'=>'#f59e0b',  'modal'=>null],
    ['label'=>'Software',     'icon'=>'bi-code-square',    'color'=>'rgba(16,185,129,.15)',  'text'=>'#10b981',  'modal'=>null],
    ['label'=>'Network',      'icon'=>'bi-wifi',           'color'=>'rgba(59,130,246,.15)',  'text'=>'#3b82f6',  'modal'=>null],
    ['label'=>'HR & General', 'icon'=>'bi-person-badge',   'color'=>'rgba(236,72,153,.15)',  'text'=>'#ec4899',  'modal'=>null],
    ['label'=>'Policies',     'icon'=>'bi-shield-check',   'color'=>'rgba(168,85,247,.15)',  'text'=>'#a855f7',  'modal'=>null],
    ['label'=>'ECPOS',        'icon'=>'bi-receipt-cutoff', 'color'=>'rgba(20,184,166,.15)',  'text'=>'#14b8a6',  'modal'=>'ecposModal'],
    ['label'=>'UTAK POS',     'icon'=>'bi-shop',           'color'=>'rgba(251,146,60,.15)',  'text'=>'#fb923c',  'modal'=>'utakModal'],
];
$catCounts = $articles->groupBy('category')->map->count();
$catMapForJson = collect($categories)->keyBy('label');
$allArticlesJson = $articles->map(function($a) use ($catMapForJson) {
    $cm = $catMapForJson->get($a->category, ['icon'=>'bi-file-text','color'=>'rgba(99,102,241,.12)','text'=>'#818cf8']);
    return [
        'id'           => $a->id,
        'kba'          => $a->kba_number ?? '#KBA-' . str_pad($a->id, 4, '0', STR_PAD_LEFT),
        'title'        => $a->title,
        'category'     => $a->category,
        'content'      => $a->content,
        'tags'         => $a->tags ?? '',
        'status'       => $a->status,
        'author'       => $a->user->name ?? 'Unknown',
        'created'      => $a->created_at->format('M d, Y'),
        'updated'      => $a->updated_at->format('M d, Y'),
        'isAuthor'     => auth()->id() === $a->user_id,
        'updateUrl'    => route('knowledge.update', $a),
        'deleteUrl'    => route('knowledge.delete', $a),
        'iconColor'    => $cm['color'],
        'iconText'     => $cm['text'],
        'icon'         => $cm['icon'],
        'ticketsCount' => $a->tickets_count,
        'usedIn'       => $a->tickets->map(fn($t) => ['id' => $t->id, 'ticket_number' => $t->ticket_number, 'subject' => $t->subject])->values()->toArray(),
    ];
})->values();
@endphp

{{-- Hero --}}
<div class="kb-hero">
    <div class="kb-hero-text">
        <h2><i class="bi bi-book" style="color:#818cf8"></i> How can we help you?</h2>
        <p>Browse categories and click to view articles and guides</p>
    </div>
</div>

{{-- Category Cards --}}
<div class="kb-category-grid">
    @foreach($categories as $cat)
    <div class="kb-cat-card"
         onclick="openCategoryModal('{{ $cat['label'] }}')">
        <div class="kb-cat-icon" style="background:{{ $cat['color'] }};color:{{ $cat['text'] }}">
            <i class="bi {{ $cat['icon'] }}"></i>
        </div>
        <div>
            <div class="kb-cat-title">{{ $cat['label'] }}</div>
            <div class="kb-cat-count">{{ $catCounts->get($cat['label'], 0) }} article{{ $catCounts->get($cat['label'], 0) !== 1 ? 's' : '' }}</div>
        </div>
    </div>
    @endforeach
</div>

{{-- All Articles Panel --}}
<div class="kb-articles-panel">
    <div class="kb-panel-header">
        <h3><i class="bi bi-journals" style="color:#818cf8"></i> All Articles
            <span style="font-size:.72rem;font-weight:400;color:var(--muted);margin-left:.35rem">({{ $articles->count() }})</span>
        </h3>
        @if($canWrite)
        <button class="btn-new-article" onclick="openKbModal('newArticleModal')">
            <i class="bi bi-plus-lg"></i> New Article
        </button>
        @endif
    </div>

    @if($articles->isEmpty())
    <div class="kb-empty">
        <i class="bi bi-journals"></i>
        @if($canWrite)
        <p>No articles yet. Click <strong>New Article</strong> to get started.</p>
        @else
        <p>No articles have been published yet.</p>
        @endif
    </div>
    @else
    @php $catMap = collect($categories)->keyBy('label'); @endphp
    <div style="overflow-x:auto">
    <table class="kb-table" id="kbArticleList">
        <thead>
            <tr>
                <th>KBA ID</th>
                <th style="width:40%">Title</th>
                <th>Category</th>
                <th>Author</th>
                <th>Status</th>
                <th style="text-align:center">Used</th>
                <th>Published</th>
                <th style="text-align:right">Action</th>
            </tr>
        </thead>
        <tbody>
        @foreach($articles as $article)
        @php
            $cm       = $catMap->get($article->category, ['icon'=>'bi-file-text','color'=>'rgba(99,102,241,.12)','text'=>'#818cf8']);
            $isAuthor = auth()->id() === $article->user_id;
            $artData  = json_encode([
                'id'           => $article->id,
                'kba'          => $article->kba_number ?? '#KBA-' . str_pad($article->id, 4, '0', STR_PAD_LEFT),
                'title'        => $article->title,
                'category'     => $article->category,
                'content'      => $article->content,
                'tags'         => $article->tags ?? '',
                'status'       => $article->status,
                'author'       => $article->user->name ?? 'Unknown',
                'created'      => $article->created_at->format('M d, Y'),
                'updated'      => $article->updated_at->format('M d, Y'),
                'isAuthor'     => $isAuthor,
                'updateUrl'    => route('knowledge.update', $article),
                'deleteUrl'    => route('knowledge.delete', $article),
                'iconColor'    => $cm['color'],
                'iconText'     => $cm['text'],
                'icon'         => $cm['icon'],
                'ticketsCount' => $article->tickets_count,
                'usedIn'       => $article->tickets->map(fn($t) => [
                    'id'            => $t->id,
                    'ticket_number' => $t->ticket_number,
                    'subject'       => $t->subject,
                ])->values()->toArray(),
            ]);
        @endphp
        <tr class="kb-article-row"
            data-title="{{ strtolower($article->title) }}"
            data-cat="{{ strtolower($article->category) }}"
            data-tags="{{ strtolower($article->tags) }}">
            <td>
                <span style="font-size:.72rem;font-weight:700;color:#818cf8;font-family:monospace;white-space:nowrap">
                    {{ $article->kba_number ?? '#KBA-' . str_pad($article->id, 4, '0', STR_PAD_LEFT) }}
                </span>
            </td>
            <td>
                <div class="kb-title-cell">
                    <div class="kb-article-icon" style="background:{{ $cm['color'] }};color:{{ $cm['text'] }}">
                        <i class="bi {{ $cm['icon'] }}"></i>
                    </div>
                    <div>
                        <div class="kb-article-title">{{ $article->title }}</div>
                        @if($article->tagList())
                        <div class="kb-article-tags">
                            @foreach($article->tagList() as $tag)
                            <span class="kb-tag">{{ $tag }}</span>
                            @endforeach
                        </div>
                        @endif
                    </div>
                </div>
            </td>
            <td style="color:var(--muted);font-size:.78rem">{{ $article->category }}</td>
            <td>
                <div style="display:flex;align-items:center;gap:.5rem">
                    <div style="width:26px;height:26px;background:linear-gradient(135deg,#4f46e5,#7c3aed);border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:.65rem;font-weight:700;color:#fff;flex-shrink:0">
                        {{ strtoupper(substr($article->user->name ?? 'U', 0, 1)) }}
                    </div>
                    <span style="font-size:.78rem;color:var(--muted)">{{ $article->user->name ?? 'Unknown' }}</span>
                </div>
            </td>
            <td><span class="{{ $article->status === 'published' ? 'kb-status-published' : 'kb-status-draft' }}">{{ ucfirst($article->status) }}</span></td>
            <td style="text-align:center">
                @if($article->tickets_count > 0)
                    <button onclick='openUsedInModal({{ $artData }})' style="display:inline-flex;align-items:center;gap:.3rem;background:rgba(99,102,241,.12);border:1px solid rgba(99,102,241,.25);border-radius:9999px;padding:.15rem .55rem;font-size:.72rem;font-weight:700;color:#818cf8;cursor:pointer;transition:background .15s" onmouseover="this.style.background='rgba(99,102,241,.25)'" onmouseout="this.style.background='rgba(99,102,241,.12)'">
                        <i class="bi bi-link-45deg"></i> {{ $article->tickets_count }}
                    </button>
                @else
                    <span style="font-size:.72rem;color:var(--muted)">—</span>
                @endif
            </td>
            <td style="font-size:.75rem;color:var(--muted)">{{ $article->created_at->format('M d, Y') }}</td>
            <td style="text-align:right">
                <button class="btn-view-article" onclick='viewArticle({{ $artData }})'>
                    <i class="bi bi-eye"></i> View
                </button>
            </td>
        </tr>
        @endforeach
        </tbody>
    </table>
    </div>
    @endif
</div>

{{-- Used In Tickets Modal --}}
<div class="kb-modal-overlay" id="usedInModal" onclick="closeKbModalOutside(event,'usedInModal')">
    <div class="kb-modal" style="max-width:520px">
        <div class="kb-modal-header">
            <h3><i class="bi bi-link-45deg" style="color:#818cf8"></i> Linked Tickets</h3>
            <button class="kb-modal-close" onclick="closeKbModal('usedInModal')"><i class="bi bi-x-lg"></i></button>
        </div>
        <div class="kb-modal-body">
            <div id="used-in-kba-label" style="font-size:.78rem;color:var(--muted);margin-bottom:1rem"></div>
            <div id="used-in-ticket-list"></div>
        </div>
    </div>
</div>

{{-- New Article Modal (only rendered for users with write access) --}}
@if($canWrite)
<div class="kb-modal-overlay" id="newArticleModal" onclick="closeKbModalOutside(event,'newArticleModal')">
    <div class="kb-modal">
        <div class="kb-modal-header">
            <h3><i class="bi bi-plus-circle" style="color:#818cf8"></i> New Article</h3>
            <button class="kb-modal-close" onclick="closeKbModal('newArticleModal')"><i class="bi bi-x-lg"></i></button>
        </div>
        <form method="POST" action="{{ route('knowledge.store') }}" id="newArticleForm">
            @csrf
            <div class="kb-modal-body">

                <div class="kb-field">
                    <label class="kb-label" for="art-title">Title <span>*</span></label>
                    <input class="kb-input" type="text" id="art-title" name="title" placeholder="e.g. How to reset your password" required>
                </div>

                <div class="kb-row2">
                    <div class="kb-field">
                        <label class="kb-label" for="art-category">Category <span>*</span></label>
                        <select class="kb-select" id="art-category" name="category" required>
                            <option value="" disabled selected>Select a category…</option>
                            <option>IT Support</option>
                            <option>Hardware</option>
                            <option>Software</option>
                            <option>Network</option>
                            <option>HR &amp; General</option>
                            <option>Policies</option>
                            <option>ECPOS</option>
                            <option>UTAK POS</option>
                        </select>
                    </div>
                    <div class="kb-field">
                        <label class="kb-label" for="art-status">Status <span>*</span></label>
                        <select class="kb-select" id="art-status" name="status" required>
                            <option value="published" selected>Published</option>
                            <option value="draft">Draft</option>
                        </select>
                    </div>
                </div>

                <div class="kb-field">
                    <label class="kb-label" for="art-content">Content <span>*</span></label>
                    <textarea class="kb-textarea" id="art-content" name="content" placeholder="Write the article content here…" required></textarea>
                </div>

                <div class="kb-field">
                    <label class="kb-label" for="art-tags">Tags</label>
                    <input class="kb-input" type="text" id="art-tags" name="tags" placeholder="e.g. password, login, account">
                    <div class="kb-hint">Separate tags with commas</div>
                </div>

            </div>
            <div class="kb-modal-footer">
                <button type="button" class="btn-cancel-modal" onclick="closeKbModal('newArticleModal')">Cancel</button>
                <button type="submit" class="btn-save-article"><i class="bi bi-check-lg"></i> Save Article</button>
            </div>
        </form>
    </div>
</div>
@endif {{-- canWrite: New Article Modal --}}

{{-- Category Articles Modal --}}
<div class="kb-modal-overlay" id="categoryArticlesModal" onclick="closeKbModalOutside(event,'categoryArticlesModal')">
    <div class="kb-modal" style="max-width:600px">
        <div class="kb-modal-header">
            <h3 style="gap:.6rem">
                <span id="cat-modal-icon" style="width:28px;height:28px;border-radius:.45rem;display:inline-flex;align-items:center;justify-content:center;font-size:.85rem;flex-shrink:0"></span>
                <span id="cat-modal-title" style="flex:1"></span>
            </h3>
            <button class="kb-modal-close" onclick="closeKbModal('categoryArticlesModal')"><i class="bi bi-x-lg"></i></button>
        </div>
        <div class="kb-modal-body" id="cat-modal-body" style="min-height:120px"></div>
    </div>
</div>

{{-- View Article Modal --}}
<div class="kb-modal-overlay" id="viewArticleModal" onclick="closeKbModalOutside(event,'viewArticleModal')">
    <div class="kb-modal" style="max-width:640px">
        <div class="kb-modal-header">
            <h3 id="va-header" style="gap:.6rem">
                <span id="va-icon-wrap" style="width:28px;height:28px;border-radius:.45rem;display:inline-flex;align-items:center;justify-content:center;font-size:.85rem;flex-shrink:0"></span>
                <span id="va-title" style="flex:1;min-width:0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap"></span>
            </h3>
            <div style="display:flex;align-items:center;gap:.5rem;flex-shrink:0">
                @if($canWrite)
                <button id="va-edit-btn" class="btn-new-article" style="padding:.35rem .8rem;font-size:.76rem;display:none" onclick="openEditFromView()">
                    <i class="bi bi-pencil"></i> Edit
                </button>
                @endif
                <button class="kb-modal-close" onclick="closeKbModal('viewArticleModal')"><i class="bi bi-x-lg"></i></button>
            </div>
        </div>
        <div class="kb-modal-body">
            <div style="display:flex;flex-wrap:wrap;gap:.5rem;align-items:center;margin-bottom:1rem;font-size:.75rem;color:var(--muted)">
                <span id="va-kba" style="font-size:.72rem;font-weight:700;color:#818cf8;font-family:monospace"></span>
                <span style="opacity:.4">·</span>
                <span id="va-category" style="font-weight:600"></span>
                <span style="opacity:.4">·</span>
                <span>By <strong id="va-author" style="color:var(--text)"></strong></span>
                <span style="opacity:.4">·</span>
                <span id="va-created"></span>
                <span id="va-status-badge" style="margin-left:.25rem"></span>
            </div>
            <div id="va-tags" style="display:flex;flex-wrap:wrap;gap:.4rem;margin-bottom:1rem"></div>
            <div id="va-content" style="font-size:.875rem;color:var(--text);line-height:1.75;white-space:pre-wrap;background:var(--surface2);border-radius:.65rem;padding:1rem 1.15rem;min-height:80px"></div>
        </div>
        <div class="kb-modal-footer" style="justify-content:space-between">
            <form id="va-delete-form" method="POST" style="margin:0" onsubmit="return confirm('Delete this article?')">
                @csrf
                @method('DELETE')
                <button type="submit" id="va-delete-btn" class="kb-delete-btn" style="display:none">
                    <i class="bi bi-trash3"></i> Delete
                </button>
            </form>
            <button class="btn-cancel-modal" onclick="closeKbModal('viewArticleModal')">Close</button>
        </div>
    </div>
</div>

{{-- Edit Article Modal (only rendered for users with write access) --}}
@if($canWrite)
<div class="kb-modal-overlay" id="editArticleModal" onclick="closeKbModalOutside(event,'editArticleModal')">
    <div class="kb-modal" style="max-width:560px">
        <div class="kb-modal-header">
            <h3><i class="bi bi-pencil-square" style="color:#818cf8"></i> Edit Article</h3>
            <button class="kb-modal-close" onclick="closeKbModal('editArticleModal')"><i class="bi bi-x-lg"></i></button>
        </div>
        <form method="POST" id="editArticleForm">
            @csrf
            @method('PUT')
            <div class="kb-modal-body">
                <div class="kb-field">
                    <label class="kb-label" for="ea-title">Title <span>*</span></label>
                    <input class="kb-input" type="text" id="ea-title" name="title" required>
                </div>
                <div class="kb-row2">
                    <div class="kb-field">
                        <label class="kb-label" for="ea-category">Category <span>*</span></label>
                        <select class="kb-select" id="ea-category" name="category" required>
                            <option>IT Support</option>
                            <option>Hardware</option>
                            <option>Software</option>
                            <option>Network</option>
                            <option>HR &amp; General</option>
                            <option>Policies</option>
                            <option>ECPOS</option>
                            <option>UTAK POS</option>
                        </select>
                    </div>
                    <div class="kb-field">
                        <label class="kb-label" for="ea-status">Status <span>*</span></label>
                        <select class="kb-select" id="ea-status" name="status" required>
                            <option value="published">Published</option>
                            <option value="draft">Draft</option>
                        </select>
                    </div>
                </div>
                <div class="kb-field">
                    <label class="kb-label" for="ea-content">Content <span>*</span></label>
                    <textarea class="kb-textarea" id="ea-content" name="content" required></textarea>
                </div>
                <div class="kb-field">
                    <label class="kb-label" for="ea-tags">Tags</label>
                    <input class="kb-input" type="text" id="ea-tags" name="tags">
                    <div class="kb-hint">Separate tags with commas</div>
                </div>
            </div>
            <div class="kb-modal-footer">
                <button type="button" class="btn-cancel-modal" onclick="closeKbModal('editArticleModal')">Cancel</button>
                <button type="submit" class="btn-save-article"><i class="bi bi-check-lg"></i> Save Changes</button>
            </div>
        </form>
    </div>
</div>
@endif {{-- canWrite: Edit Article Modal --}}

{{-- Success toast --}}
@if(session('kb_success'))
<div class="kb-toast" id="kbToast"><i class="bi bi-check-circle-fill"></i> {{ session('kb_success') }}</div>
@endif

@endsection

@push('scripts')
<script>
/* ── Modal helpers ── */
function openKbModal(id) {
    document.getElementById(id).classList.add('open');
    document.body.style.overflow = 'hidden';
}
function closeKbModal(id) {
    document.getElementById(id).classList.remove('open');
    document.body.style.overflow = '';
}
function closeKbModalOutside(e, id) {
    if (e.target === document.getElementById(id)) closeKbModal(id);
}
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        document.querySelectorAll('.kb-modal-overlay.open').forEach(m => {
            m.classList.remove('open');
            document.body.style.overflow = '';
        });
    }
});

/* ── Category Articles Modal ── */
const _allArticles = @json($allArticlesJson);
const _categoriesConfig = @json($categories);

function openCategoryModal(cat) {
    const cfg = _categoriesConfig.find(c => c.label === cat) || {icon:'bi-file-text',color:'rgba(99,102,241,.12)',text:'#818cf8'};
    const icon = document.getElementById('cat-modal-icon');
    icon.style.background = cfg.color;
    icon.style.color = cfg.text;
    icon.innerHTML = `<i class="bi ${cfg.icon}"></i>`;
    document.getElementById('cat-modal-title').textContent = cat + ' Articles';

    const filtered = _allArticles.filter(a => a.category === cat);
    const body = document.getElementById('cat-modal-body');

    if (filtered.length === 0) {
        body.innerHTML = '<div class="kb-empty"><i class="bi bi-journals"></i><p>No articles in this category yet.</p></div>';
    } else {
        body.innerHTML = filtered.map(art => {
            const statusClass = art.status === 'published' ? 'kb-status-published' : 'kb-status-draft';
            const statusLabel = art.status.charAt(0).toUpperCase() + art.status.slice(1);
            return `<div style="display:flex;align-items:center;gap:.75rem;padding:.7rem .85rem;background:var(--surface2);border:1px solid var(--border);border-radius:.65rem;margin-bottom:.5rem;cursor:pointer;transition:border-color .15s,background .15s"
                         onclick="closeKbModal('categoryArticlesModal');viewArticleById(${art.id})"
                         onmouseover="this.style.borderColor='#6366f1';this.style.background='var(--surface)'"
                         onmouseout="this.style.borderColor='var(--border)';this.style.background='var(--surface2)'">
                <div style="width:34px;height:34px;border-radius:.5rem;background:${art.iconColor};color:${art.iconText};display:flex;align-items:center;justify-content:center;font-size:.9rem;flex-shrink:0">
                    <i class="bi ${art.icon}"></i>
                </div>
                <div style="flex:1;min-width:0">
                    <div style="font-size:.72rem;font-weight:700;color:#818cf8;font-family:monospace;margin-bottom:.1rem">${art.kba}</div>
                    <div style="font-size:.835rem;font-weight:600;color:var(--text);overflow:hidden;text-overflow:ellipsis;white-space:nowrap">${art.title}</div>
                </div>
                <span class="${statusClass}">${statusLabel}</span>
            </div>`;
        }).join('');
    }
    openKbModal('categoryArticlesModal');
}

function viewArticleById(id) {
    const art = _allArticles.find(a => a.id === id);
    if (art) viewArticle(art);
}

/* ── Used In Modal ── */
function openUsedInModal(art) {
    document.getElementById('used-in-kba-label').innerHTML =
        `<span style="font-weight:700;color:#818cf8">${art.kba}</span> &mdash; ${art.title}`;

    const list = document.getElementById('used-in-ticket-list');
    if (!art.usedIn || art.usedIn.length === 0) {
        list.innerHTML = '<div style="font-size:.82rem;color:var(--muted);text-align:center;padding:1rem 0">No tickets linked.</div>';
    } else {
        list.innerHTML = art.usedIn.map(t => `
            <a href="/tickets?open=${t.id}" style="display:flex;align-items:center;gap:.75rem;padding:.6rem .85rem;background:var(--surface2);border:1px solid var(--border);border-radius:.65rem;margin-bottom:.5rem;text-decoration:none;transition:border-color .15s,background .15s" onmouseover="this.style.borderColor='#6366f1';this.style.background='var(--surface)'" onmouseout="this.style.borderColor='var(--border)';this.style.background='var(--surface2)'">
                <div style="width:30px;height:30px;background:linear-gradient(135deg,#4f46e5,#7c3aed);border-radius:.4rem;display:flex;align-items:center;justify-content:center;flex-shrink:0">
                    <i class="bi bi-ticket-perforated-fill" style="color:#fff;font-size:.7rem"></i>
                </div>
                <div style="min-width:0;flex:1">
                    <div style="font-size:.72rem;font-weight:700;color:#818cf8;font-family:monospace">${t.ticket_number}</div>
                    <div style="font-size:.82rem;color:var(--text);overflow:hidden;text-overflow:ellipsis;white-space:nowrap">${t.subject}</div>
                </div>
                <i class="bi bi-arrow-right" style="color:var(--muted);font-size:.8rem;flex-shrink:0"></i>
            </a>`).join('');
    }
    openKbModal('usedInModal');
}

/* ── View Article ── */
let _currentArticle = null;

function viewArticle(art) {
    _currentArticle = art;

    // Icon
    const iconWrap = document.getElementById('va-icon-wrap');
    iconWrap.style.background = art.iconColor;
    iconWrap.style.color = art.iconText;
    iconWrap.innerHTML = `<i class="bi ${art.icon}"></i>`;

    document.getElementById('va-title').textContent    = art.title;
    document.getElementById('va-kba').textContent      = art.kba;
    document.getElementById('va-category').textContent = art.category;
    document.getElementById('va-author').textContent   = art.author;
    document.getElementById('va-created').textContent  = art.created;
    document.getElementById('va-content').textContent  = art.content;

    // Status badge
    const sb = document.getElementById('va-status-badge');
    sb.className = art.status === 'published' ? 'kb-status-published' : 'kb-status-draft';
    sb.textContent = art.status.charAt(0).toUpperCase() + art.status.slice(1);

    // Tags
    const tagsEl = document.getElementById('va-tags');
    tagsEl.innerHTML = '';
    if (art.tags) {
        art.tags.split(',').map(t => t.trim()).filter(Boolean).forEach(t => {
            const span = document.createElement('span');
            span.className = 'kb-tag';
            span.textContent = t;
            tagsEl.appendChild(span);
        });
    }

    // Author-only controls
    const editBtn   = document.getElementById('va-edit-btn');
    const deleteBtn = document.getElementById('va-delete-btn');
    const deleteForm = document.getElementById('va-delete-form');

    if (art.isAuthor) {
        editBtn.style.display   = 'inline-flex';
        deleteBtn.style.display = 'inline-flex';
        deleteForm.action = art.deleteUrl;
    } else {
        editBtn.style.display   = 'none';
        deleteBtn.style.display = 'none';
    }

    openKbModal('viewArticleModal');
}

function openEditFromView() {
    if (!_currentArticle) return;
    const art = _currentArticle;
    closeKbModal('viewArticleModal');

    document.getElementById('ea-title').value   = art.title;
    document.getElementById('ea-content').value = art.content;
    document.getElementById('ea-tags').value    = art.tags || '';
    document.getElementById('ea-category').value = art.category;
    document.getElementById('ea-status').value  = art.status;
    document.getElementById('editArticleForm').action = art.updateUrl;

    openKbModal('editArticleModal');
}

/* ── Auto-dismiss toast ── */
const toast = document.getElementById('kbToast');
if (toast) setTimeout(() => toast.style.display = 'none', 4000);
</script>
@endpush
