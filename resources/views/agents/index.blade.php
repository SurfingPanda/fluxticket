@extends('layouts.app')
@section('title','Agents')
@section('topbar-title','Agents')
@section('topbar-sub', $agents->count() . ' agents across ' . $agents->pluck('department')->filter()->unique()->count() . ' departments')

@push('styles')
<style>
    .topbar-search { display:flex;align-items:center;gap:.5rem;background:var(--surface2);border:1px solid var(--border);border-radius:.6rem;padding:.4rem .85rem;width:220px;transition:border-color .2s; }
    .topbar-search:focus-within { border-color:var(--accent); }
    .topbar-search input { border:none;background:transparent;outline:none;font-size:.825rem;color:var(--text);width:100%; }
    .topbar-search input::placeholder { color:var(--muted); }
    .dept-filter-btn { background:var(--surface2);border:1px solid var(--border);border-radius:2rem;padding:.25rem .85rem;font-size:.75rem;font-weight:600;color:var(--muted);cursor:pointer;transition:all .15s; }
    .dept-filter-btn:hover,.dept-filter-btn.active { background:rgba(99,102,241,.15);border-color:rgba(99,102,241,.4);color:#818cf8; }
    .agent-grid { display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:1rem; }
    .agent-card { background:var(--surface);border:1px solid var(--border);border-radius:.875rem;padding:1.25rem;transition:border-color .2s,transform .2s,box-shadow .2s;cursor:pointer; }
    .agent-card:hover { border-color:rgba(99,102,241,.35);transform:translateY(-2px);box-shadow:0 8px 24px rgba(0,0,0,.15); }
    .dept-chip { display:inline-flex;align-items:center;padding:.2rem .65rem;border-radius:9999px;font-size:.68rem;font-weight:700;background:rgba(99,102,241,.12);color:#818cf8; }
    .stat-pill { display:flex;align-items:center;gap:.35rem;font-size:.75rem;color:var(--muted); }
    .stat-pill strong { color:var(--text); }

    /* ── Agent Profile Modal ── */
    .ap-backdrop {
        display:none;position:fixed;inset:0;background:rgba(0,0,0,.6);
        z-index:1000;align-items:center;justify-content:center;
        backdrop-filter:blur(4px);
    }
    .ap-backdrop.open { display:flex; }
    .ap-modal {
        background:var(--surface);border:1px solid var(--border);border-radius:1.25rem;
        width:90%;max-width:480px;max-height:88vh;overflow-y:auto;
        box-shadow:0 24px 64px rgba(0,0,0,.45);
        animation:apIn .18s ease;
    }
    @keyframes apIn { from { opacity:0;transform:scale(.94) translateY(10px); } to { opacity:1;transform:none; } }
    @keyframes createAgentSpin { to { transform:rotate(360deg); } }
    .ap-header {
        display:flex;align-items:center;gap:1rem;padding:1.4rem 1.4rem 1rem;
        border-bottom:1px solid var(--border);
        background:linear-gradient(135deg,rgba(99,102,241,.08),rgba(124,58,237,.05));
        border-radius:1.25rem 1.25rem 0 0;
    }
    .ap-avatar {
        width:64px;height:64px;border-radius:50%;
        background:linear-gradient(135deg,#4f46e5,#7c3aed);
        display:flex;align-items:center;justify-content:center;
        font-size:1.5rem;font-weight:700;color:white;flex-shrink:0;
        border:3px solid var(--border);box-shadow:0 4px 16px rgba(99,102,241,.3);
        overflow:hidden;
    }
    .ap-avatar img { width:100%;height:100%;object-fit:cover; }
    .ap-name { font-size:1rem;font-weight:700;color:var(--text);margin-bottom:.15rem; }
    .ap-email { font-size:.76rem;color:var(--muted); }
    .ap-body { padding:1.25rem 1.4rem; }
    .ap-section-title { font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:var(--muted);margin-bottom:.65rem; }
    .ap-field-grid { display:grid;grid-template-columns:1fr 1fr;gap:.75rem;margin-bottom:1.1rem; }
    .ap-field { background:var(--surface2);border:1px solid var(--border);border-radius:.65rem;padding:.6rem .85rem; }
    .ap-field-label { font-size:.66rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:var(--muted);margin-bottom:.18rem; }
    .ap-field-value { font-size:.82rem;color:var(--text);font-weight:600;word-break:break-word; }
    .ap-field-value.muted { color:var(--muted);font-weight:400; }
    .ap-stat-row { display:flex;gap:.65rem;margin-bottom:1.1rem; }
    .ap-stat { flex:1;background:var(--surface2);border:1px solid var(--border);border-radius:.75rem;padding:.75rem;text-align:center; }
    .ap-stat-num { font-size:1.2rem;font-weight:700;color:var(--text);display:block; }
    .ap-stat-lbl { font-size:.68rem;color:var(--muted);margin-top:.1rem; }
    .ap-close-btn { margin-left:auto;background:rgba(255,255,255,.06);border:1px solid var(--border);border-radius:.55rem;color:var(--muted);width:32px;height:32px;display:flex;align-items:center;justify-content:center;cursor:pointer;flex-shrink:0;font-size:.85rem;transition:background .15s; }
    .ap-close-btn:hover { background:rgba(255,255,255,.12); }
    .na-select { width:100%;background:transparent;border:none;outline:none;font-size:.85rem;color:var(--text);margin-top:.15rem;cursor:pointer;appearance:none;-webkit-appearance:none; }
    /* CSS vars don't apply inside <option>, so hardcode for dark/light */
    .dark .na-select option { background:#263348;color:#e2e8f0; }
    body:not(.dark) .na-select option { background:#f0f4fb;color:#0f172a; }
</style>
@endpush


@section('content')
@if(session('agent_success'))
<div style="position:fixed;bottom:1.5rem;right:1.5rem;background:rgba(52,211,153,.15);border:1px solid rgba(52,211,153,.3);color:#34d399;border-radius:.75rem;padding:.7rem 1.1rem;font-size:.82rem;font-weight:600;z-index:1100;display:flex;align-items:center;gap:.5rem;box-shadow:0 8px 24px rgba(0,0,0,.2);animation:apIn .25s ease" id="agentToast">
    <i class="bi bi-check-circle-fill"></i> {{ session('agent_success') }}
</div>
<script>setTimeout(()=>{const t=document.getElementById('agentToast');if(t)t.style.display='none'},4000)</script>
@endif

{{-- Department filter pills + New Agent button --}}
@php $depts = $agents->pluck('department')->filter()->unique()->sort()->values(); @endphp
<div style="display:flex;align-items:center;flex-wrap:wrap;gap:.5rem;margin-bottom:1.25rem">
    <button class="dept-filter-btn active" onclick="filterDept('',this)">All Departments</button>
    @foreach($depts as $d)
    <button class="dept-filter-btn" onclick="filterDept('{{ $d }}',this)">{{ $d }}</button>
    @endforeach
    @if(auth()->user()->isSuperAdmin())
    <button onclick="openNewAgentModal()" style="margin-left:auto;display:inline-flex;align-items:center;gap:.4rem;background:#6366f1;color:#fff;border:none;border-radius:.55rem;padding:.35rem .9rem;font-size:.78rem;font-weight:700;cursor:pointer;transition:opacity .15s;white-space:nowrap" onmouseover="this.style.opacity='.85'" onmouseout="this.style.opacity='1'">
        <i class="bi bi-person-plus-fill"></i> New Agent
    </button>
    @endif
</div>

<div class="agent-grid" id="agentGrid">
    @foreach($agents as $agent)
    <div class="agent-card"
        data-dept="{{ $agent->department }}"
        data-name="{{ strtolower($agent->name) }}"
        data-email="{{ strtolower($agent->email) }}"
        onclick="openAgentModal({
            name:       {{ json_encode($agent->name) }},
            email:      {{ json_encode($agent->email) }},
            username:   {{ json_encode($agent->username ?? '') }},
            employee_id:{{ json_encode($agent->employee_id ?? '') }},
            job_title:  {{ json_encode($agent->job_title ?? '') }},
            department: {{ json_encode($agent->department ?? '') }},
            primary:    {{ json_encode($agent->primary_contact ?? '') }},
            secondary:  {{ json_encode($agent->secondary_contact ?? '') }},
            photo:      {{ $agent->profile_photo ? json_encode(asset('storage/' . $agent->profile_photo)) : 'null' }},
            open:       {{ $agent->open_tickets }},
            active:     {{ $agent->active_tickets }},
            resolved:   {{ $agent->resolved_tickets }}
        })">
        <div style="display:flex;align-items:center;gap:.85rem;margin-bottom:1rem">
            @if($agent->profile_photo)
                <img src="{{ asset('storage/' . $agent->profile_photo) }}" alt="{{ $agent->name }}"
                     style="width:44px;height:44px;border-radius:50%;object-fit:cover;flex-shrink:0;border:2px solid var(--border)">
            @else
                <div style="width:44px;height:44px;background:linear-gradient(135deg,#4f46e5,#7c3aed);border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:1rem;font-weight:700;color:white;flex-shrink:0">
                    {{ strtoupper(substr($agent->name, 0, 1)) }}
                </div>
            @endif
            <div style="min-width:0;flex:1">
                <div style="font-size:.9rem;font-weight:700;color:var(--text);white-space:nowrap;overflow:hidden;text-overflow:ellipsis">{{ $agent->name }}</div>
                <div style="font-size:.72rem;color:var(--muted);white-space:nowrap;overflow:hidden;text-overflow:ellipsis">{{ $agent->email }}</div>
            </div>
        </div>
        <div style="margin-bottom:.875rem;display:flex;flex-wrap:wrap;gap:.4rem;align-items:center">
            @if($agent->job_title)
            <span class="dept-chip" style="background:rgba(16,185,129,.12);color:#34d399"><i class="bi bi-briefcase-fill me-1"></i>{{ $agent->job_title }}</span>
            @endif
            @if($agent->department)
            <span class="dept-chip"><i class="bi bi-building me-1"></i>{{ $agent->department }}</span>
            @endif
        </div>
        <div style="display:flex;gap:1rem;padding:.65rem .75rem;background:var(--surface2);border-radius:.6rem;flex-wrap:wrap">
            <div class="stat-pill"><span style="width:8px;height:8px;border-radius:50%;background:#818cf8;flex-shrink:0;display:inline-block"></span><span>Open <strong>{{ $agent->open_tickets }}</strong></span></div>
            <div class="stat-pill"><span style="width:8px;height:8px;border-radius:50%;background:#fbbf24;flex-shrink:0;display:inline-block"></span><span>Active <strong>{{ $agent->active_tickets }}</strong></span></div>
            <div class="stat-pill"><span style="width:8px;height:8px;border-radius:50%;background:#34d399;flex-shrink:0;display:inline-block"></span><span>Resolved <strong>{{ $agent->resolved_tickets }}</strong></span></div>
        </div>
    </div>
    @endforeach
</div>

@if($agents->isEmpty())
<div style="text-align:center;color:var(--muted);padding:4rem">
    <i class="bi bi-people" style="font-size:2.5rem;opacity:.25;display:block;margin-bottom:1rem"></i>
    <div style="font-size:.95rem;font-weight:600">No agents found</div>
</div>
@endif

@if(auth()->user()->isSuperAdmin())
{{-- ── New Agent Modal ── --}}
<div class="ap-backdrop" id="newAgentModal" onclick="if(event.target===this)closeNewAgentModal()">
    <div class="ap-modal" style="max-width:520px">
        <div class="ap-header">
            <div style="width:48px;height:48px;background:linear-gradient(135deg,#4f46e5,#7c3aed);border-radius:50%;display:flex;align-items:center;justify-content:center;flex-shrink:0">
                <i class="bi bi-person-plus-fill" style="color:#fff;font-size:1.1rem"></i>
            </div>
            <div>
                <div class="ap-name">New Agent Account</div>
                <div class="ap-email">Fill in the details to create a new user</div>
            </div>
            <button class="ap-close-btn" onclick="closeNewAgentModal()"><i class="bi bi-x-lg"></i></button>
        </div>
        <form method="POST" action="{{ route('agents.store') }}">
            @csrf
            <div class="ap-body">
                @if($errors->any())
                <div style="background:rgba(248,113,113,.1);border:1px solid rgba(248,113,113,.3);color:#f87171;border-radius:.6rem;padding:.6rem .9rem;font-size:.8rem;margin-bottom:1rem">
                    <i class="bi bi-exclamation-circle-fill" style="margin-right:.4rem"></i>{{ $errors->first() }}
                </div>
                @endif

                <div class="ap-section-title">Required Information</div>
                <div class="ap-field-grid">
                    <div class="ap-field" style="grid-column:1/-1">
                        <div class="ap-field-label"><i class="bi bi-person-fill" style="margin-right:.3rem"></i>Full Name <span style="color:#f87171">*</span></div>
                        <input type="text" name="name" value="{{ old('name') }}" required placeholder="e.g. Juan Dela Cruz"
                            style="width:100%;background:transparent;border:none;outline:none;font-size:.85rem;color:var(--text);margin-top:.15rem">
                    </div>
                    <div class="ap-field">
                        <div class="ap-field-label"><i class="bi bi-envelope-fill" style="margin-right:.3rem"></i>Email <span style="color:#f87171">*</span></div>
                        <input type="email" name="email" value="{{ old('email') }}" required placeholder="user@fluxtickets.com"
                            style="width:100%;background:transparent;border:none;outline:none;font-size:.85rem;color:var(--text);margin-top:.15rem">
                    </div>
                    <div class="ap-field">
                        <div class="ap-field-label"><i class="bi bi-lock-fill" style="margin-right:.3rem"></i>Password <span style="color:#f87171">*</span></div>
                        <input type="password" name="password" required placeholder="Min 8 characters"
                            style="width:100%;background:transparent;border:none;outline:none;font-size:.85rem;color:var(--text);margin-top:.15rem">
                    </div>
                    <div class="ap-field">
                        <div class="ap-field-label"><i class="bi bi-building" style="margin-right:.3rem"></i>Department <span style="color:#f87171">*</span></div>
                        <select name="department" required class="na-select">
                            <option value="">Select…</option>
                            @foreach(\App\Models\SystemSetting::allDepartments() as $d)
                            <option value="{{ $d }}" {{ old('department')===$d ? 'selected' : '' }}>{{ $d }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="ap-field">
                        <div class="ap-field-label"><i class="bi bi-shield-check" style="margin-right:.3rem"></i>Role <span style="color:#f87171">*</span></div>
                        <select name="role" required class="na-select">
                            <option value="agent"       {{ old('role','agent')==='agent'       ? 'selected' : '' }}>Agent</option>
                            <option value="super_admin" {{ old('role')==='super_admin'          ? 'selected' : '' }}>Super Admin</option>
                        </select>
                    </div>
                </div>

                <div class="ap-section-title" style="margin-top:.25rem">Optional Details</div>
                <div class="ap-field-grid">
                    <div class="ap-field">
                        <div class="ap-field-label"><i class="bi bi-at" style="margin-right:.3rem"></i>Username</div>
                        <input type="text" name="username" value="{{ old('username') }}" placeholder="e.g. jdelacruz"
                            style="width:100%;background:transparent;border:none;outline:none;font-size:.85rem;color:var(--text);margin-top:.15rem">
                    </div>
                    <div class="ap-field">
                        <div class="ap-field-label"><i class="bi bi-badge-id" style="margin-right:.3rem"></i>Employee ID</div>
                        <input type="text" name="employee_id" value="{{ old('employee_id') }}" placeholder="e.g. EMP-001"
                            style="width:100%;background:transparent;border:none;outline:none;font-size:.85rem;color:var(--text);margin-top:.15rem">
                    </div>
                    <div class="ap-field">
                        <div class="ap-field-label"><i class="bi bi-briefcase-fill" style="margin-right:.3rem"></i>Job Title</div>
                        <input type="text" name="job_title" value="{{ old('job_title') }}" placeholder="e.g. IT Support"
                            style="width:100%;background:transparent;border:none;outline:none;font-size:.85rem;color:var(--text);margin-top:.15rem">
                    </div>
                    <div class="ap-field">
                        <div class="ap-field-label"><i class="bi bi-telephone-fill" style="margin-right:.3rem"></i>Primary Contact</div>
                        <input type="text" name="primary_contact" value="{{ old('primary_contact') }}" placeholder="e.g. 09XX XXX XXXX"
                            style="width:100%;background:transparent;border:none;outline:none;font-size:.85rem;color:var(--text);margin-top:.15rem">
                    </div>
                </div>
            </div>
            <div style="display:flex;align-items:center;justify-content:flex-end;gap:.6rem;padding:.9rem 1.4rem;border-top:1px solid var(--border)">
                <button type="button" onclick="closeNewAgentModal()" style="background:transparent;border:1px solid var(--border);border-radius:.5rem;color:var(--muted);padding:.35rem .9rem;font-size:.78rem;cursor:pointer">Cancel</button>
                <button type="submit" id="createAgentBtn" style="display:inline-flex;align-items:center;gap:.4rem;background:#6366f1;color:#fff;border:none;border-radius:.5rem;padding:.4rem 1rem;font-size:.78rem;font-weight:700;cursor:pointer">
                    <i class="bi bi-person-check-fill" id="createAgentIcon"></i>
                    <span id="createAgentText">Create Agent</span>
                </button>
            </div>
        </form>
    </div>
</div>
@endif

{{-- ── Agent Profile Modal ── --}}
<div class="ap-backdrop" id="agentModal" onclick="closeAgentModal(event)">
    <div class="ap-modal">
        <div class="ap-header">
            <div class="ap-avatar" id="apAvatar"></div>
            <div style="min-width:0;flex:1">
                <div class="ap-name" id="apName"></div>
                <div class="ap-email" id="apEmail"></div>
            </div>
            <button class="ap-close-btn" onclick="document.getElementById('agentModal').classList.remove('open')" title="Close">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
        <div class="ap-body">
            {{-- Ticket stats --}}
            <div class="ap-section-title">Ticket Summary</div>
            <div class="ap-stat-row">
                <div class="ap-stat">
                    <span class="ap-stat-num" id="apOpen" style="color:#818cf8"></span>
                    <div class="ap-stat-lbl">Open</div>
                </div>
                <div class="ap-stat">
                    <span class="ap-stat-num" id="apActive" style="color:#fbbf24"></span>
                    <div class="ap-stat-lbl">Active</div>
                </div>
                <div class="ap-stat">
                    <span class="ap-stat-num" id="apResolved" style="color:#34d399"></span>
                    <div class="ap-stat-lbl">Resolved</div>
                </div>
            </div>

            {{-- Profile details --}}
            <div class="ap-section-title">Profile Information</div>
            <div class="ap-field-grid">
                <div class="ap-field">
                    <div class="ap-field-label"><i class="bi bi-person-fill" style="margin-right:.3rem"></i>Username</div>
                    <div class="ap-field-value" id="apUsername"></div>
                </div>
                <div class="ap-field">
                    <div class="ap-field-label"><i class="bi bi-badge-id" style="margin-right:.3rem"></i>Employee ID</div>
                    <div class="ap-field-value" id="apEmployeeId"></div>
                </div>
                <div class="ap-field">
                    <div class="ap-field-label"><i class="bi bi-briefcase-fill" style="margin-right:.3rem"></i>Job Title</div>
                    <div class="ap-field-value" id="apJobTitle"></div>
                </div>
                <div class="ap-field">
                    <div class="ap-field-label"><i class="bi bi-building" style="margin-right:.3rem"></i>Department</div>
                    <div class="ap-field-value" id="apDept"></div>
                </div>
                <div class="ap-field">
                    <div class="ap-field-label"><i class="bi bi-telephone-fill" style="margin-right:.3rem"></i>Primary Contact</div>
                    <div class="ap-field-value" id="apPrimary"></div>
                </div>
                <div class="ap-field">
                    <div class="ap-field-label"><i class="bi bi-telephone" style="margin-right:.3rem"></i>Secondary Contact</div>
                    <div class="ap-field-value" id="apSecondary"></div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    let _activeDept = '';
    function filterDept(dept, btn) {
        _activeDept = dept;
        document.querySelectorAll('.dept-filter-btn').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        applyFilters();
    }
    function applyFilters() {
        const q = (document.getElementById('searchInput')?.value || '').toLowerCase();
        document.querySelectorAll('#agentGrid .agent-card').forEach(card => {
            const matchDept = !_activeDept || card.dataset.dept === _activeDept;
            const matchQ    = !q || card.dataset.name.includes(q) || card.dataset.email.includes(q) || (card.dataset.dept||'').toLowerCase().includes(q);
            card.style.display = (matchDept && matchQ) ? '' : 'none';
        });
    }

    function fill(id, val, fallback) {
        const el = document.getElementById(id);
        if (val) {
            el.textContent = val;
            el.classList.remove('muted');
        } else {
            el.textContent = fallback || '—';
            el.classList.add('muted');
        }
    }

    // Move modals to <body> to escape overflow-x:hidden on .main-wrap
    document.addEventListener('DOMContentLoaded', function () {
        document.body.appendChild(document.getElementById('agentModal'));
        const na = document.getElementById('newAgentModal');
        if (na) document.body.appendChild(na);
    });

    @if($errors->any() || session('agent_error'))
    document.addEventListener('DOMContentLoaded', openNewAgentModal);
    @endif

    function openNewAgentModal() {
        document.getElementById('newAgentModal').classList.add('open');
        document.body.style.overflow = 'hidden';
    }
    function closeNewAgentModal() {
        document.getElementById('newAgentModal').classList.remove('open');
        document.body.style.overflow = '';
        // reset button in case form had errors and modal is reopened
        const btn  = document.getElementById('createAgentBtn');
        const icon = document.getElementById('createAgentIcon');
        const text = document.getElementById('createAgentText');
        if (btn) {
            btn.disabled      = false;
            btn.style.opacity = '';
            btn.style.cursor  = '';
            icon.className    = 'bi bi-person-check-fill';
            icon.innerHTML    = '';
            text.textContent  = 'Create Agent';
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        const form = document.querySelector('#newAgentModal form');
        if (form) {
            form.addEventListener('submit', function () {
                const btn  = document.getElementById('createAgentBtn');
                const icon = document.getElementById('createAgentIcon');
                const text = document.getElementById('createAgentText');
                btn.disabled      = true;
                btn.style.opacity = '.75';
                btn.style.cursor  = 'not-allowed';
                icon.className    = '';
                icon.innerHTML    = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="animation:createAgentSpin .7s linear infinite;display:inline-block"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg>';
                text.textContent  = 'Creating…';
            });
        }
    });

    function openAgentModal(a) {
        // Avatar
        const av = document.getElementById('apAvatar');
        if (a.photo) {
            av.innerHTML = `<img src="${a.photo}" alt="${a.name}">`;
        } else {
            av.innerHTML = `<span>${a.name.charAt(0).toUpperCase()}</span>`;
        }

        // Header
        document.getElementById('apName').textContent  = a.name;
        document.getElementById('apEmail').textContent = a.email;

        // Stats
        document.getElementById('apOpen').textContent     = a.open;
        document.getElementById('apActive').textContent   = a.active;
        document.getElementById('apResolved').textContent = a.resolved;

        // Fields
        fill('apUsername',   a.username);
        fill('apEmployeeId', a.employee_id);
        fill('apJobTitle',   a.job_title);
        fill('apDept',       a.department);
        fill('apPrimary',    a.primary);
        fill('apSecondary',  a.secondary);

        document.getElementById('agentModal').classList.add('open');
    }

    function closeAgentModal(e) {
        if (e.target === document.getElementById('agentModal')) {
            document.getElementById('agentModal').classList.remove('open');
        }
    }

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            document.getElementById('agentModal').classList.remove('open');
            closeNewAgentModal();
        }
    });
</script>
@endpush
