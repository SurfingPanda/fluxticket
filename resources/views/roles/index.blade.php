@extends('layouts.app')
@section('title','Role Access & Permission')
@section('topbar-title','Role Access & Permission')
@section('topbar-sub','Manage user roles and system permissions')

@push('styles')
<style>
    .rp-section { background:var(--surface);border:1px solid var(--border);border-radius:.875rem;overflow:hidden;margin-bottom:1.5rem; }
    .rp-section-header { display:flex;align-items:center;gap:.6rem;padding:1rem 1.4rem;border-bottom:1px solid var(--border); }
    .rp-section-header h3 { font-size:.9rem;font-weight:700;color:var(--text);margin:0; }
    .rp-section-body { padding:1.25rem 1.4rem; }

    /* Permission matrix */
    .perm-grid { display:grid;grid-template-columns:1fr repeat(2,140px);gap:0;border:1px solid var(--border);border-radius:.65rem;overflow:hidden; }
    .perm-head { background:var(--surface2);font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--muted);padding:.6rem 1rem;border-bottom:1px solid var(--border); }
    .perm-head.center { text-align:center; }
    .perm-row { display:contents; }
    .perm-row > div { padding:.6rem 1rem;border-bottom:1px solid var(--border);font-size:.82rem;color:var(--text);display:flex;align-items:center; }
    .perm-row:last-child > div { border-bottom:none; }
    .perm-row > div.center { justify-content:center; }
    .perm-label { font-weight:500; }
    .perm-check { color:#34d399;font-size:1rem; }
    .perm-cross { color:#f87171;font-size:.85rem;opacity:.5; }

    /* Toggle switch UI */
    .toggle-switch { position:relative;display:inline-block;width:38px;height:22px;flex-shrink:0; }
    .toggle-switch input { opacity:0;width:0;height:0;position:absolute; }
    .toggle-slider { position:absolute;inset:0;background:var(--border);border-radius:9999px;cursor:pointer;transition:background .2s; }
    .toggle-slider::before { content:'';position:absolute;width:16px;height:16px;left:3px;bottom:3px;background:white;border-radius:50%;transition:transform .2s;box-shadow:0 1px 3px rgba(0,0,0,.2); }
    .toggle-switch input:checked + .toggle-slider { background:#6366f1; }
    .toggle-switch input:checked + .toggle-slider::before { transform:translateX(16px); }

    /* Dept checkboxes */
    .dept-grid { display:grid;grid-template-columns:repeat(auto-fill,minmax(160px,1fr));gap:.5rem; }
    .dept-check-item { display:flex;align-items:center;gap:.55rem;padding:.55rem .85rem;background:var(--surface2);border:1px solid var(--border);border-radius:.6rem;cursor:pointer;transition:border-color .15s; }
    .dept-check-item:hover { border-color:rgba(99,102,241,.4); }
    .dept-check-item input[type=checkbox] { width:15px;height:15px;accent-color:#6366f1;cursor:pointer;flex-shrink:0; }
    .dept-check-item label { font-size:.82rem;font-weight:500;color:var(--text);cursor:pointer;margin:0; }

    /* Role badge */
    .role-badge-super { display:inline-flex;align-items:center;gap:.3rem;padding:.22rem .65rem;border-radius:9999px;font-size:.68rem;font-weight:700;background:rgba(251,146,60,.15);color:#fb923c;white-space:nowrap; }
    .role-badge-agent { display:inline-flex;align-items:center;gap:.3rem;padding:.22rem .65rem;border-radius:9999px;font-size:.68rem;font-weight:600;background:rgba(99,102,241,.12);color:#818cf8;white-space:nowrap; }

    /* Users table */
    .rp-table { width:100%;border-collapse:collapse; }
    .rp-table thead tr { border-bottom:1px solid var(--border); }
    .rp-table thead th { padding:.65rem 1.1rem;font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--muted);text-align:left;white-space:nowrap; }
    .rp-table tbody tr { border-bottom:1px solid var(--border);transition:background .15s; }
    .rp-table tbody tr:last-child { border-bottom:none; }
    .rp-table tbody tr:hover { background:var(--surface2); }
    .rp-table td { padding:.75rem 1.1rem;font-size:.82rem;color:var(--text);vertical-align:middle; }

    .rp-role-select { background:var(--surface2);border:1px solid var(--border);border-radius:.5rem;padding:.3rem .65rem;font-size:.78rem;color:var(--text);outline:none;cursor:pointer;transition:border-color .2s; }
    .rp-role-select:focus { border-color:var(--accent); }
    .btn-save-role { background:var(--accent);color:#fff;border:none;border-radius:.5rem;padding:.3rem .75rem;font-size:.76rem;font-weight:600;cursor:pointer;transition:opacity .15s; }
    .btn-save-role:hover { opacity:.85; }
    .btn-save-settings { display:inline-flex;align-items:center;gap:.4rem;background:var(--accent);color:#fff;border:none;border-radius:.6rem;padding:.45rem 1.1rem;font-size:.8rem;font-weight:600;cursor:pointer;transition:opacity .15s; }
    .btn-save-settings:hover { opacity:.88; }

    .rp-toast { position:fixed;bottom:1.5rem;right:1.5rem;border-radius:.75rem;padding:.7rem 1.1rem;font-size:.82rem;font-weight:600;z-index:1100;display:flex;align-items:center;gap:.5rem;box-shadow:0 8px 24px rgba(0,0,0,.2);animation:rpSlideIn .25s ease; }
    .rp-toast.success { background:rgba(52,211,153,.15);border:1px solid rgba(52,211,153,.3);color:#34d399; }
    .rp-toast.error   { background:rgba(248,113,113,.12);border:1px solid rgba(248,113,113,.3);color:#f87171; }
    @keyframes rpSlideIn { from { opacity:0;transform:translateY(8px); } to { opacity:1;transform:translateY(0); } }
    @keyframes deleteSpin { to { transform:rotate(360deg); } }
    .rp-self-badge { font-size:.65rem;background:rgba(99,102,241,.15);color:#818cf8;border-radius:9999px;padding:.1rem .45rem;font-weight:700;margin-left:.4rem; }
    .section-save-bar { display:flex;justify-content:flex-end;padding:.85rem 1.4rem;border-top:1px solid var(--border); }

    /* Edit Access button */
    .btn-edit-access { display:inline-flex;align-items:center;gap:.3rem;background:transparent;border:1px solid var(--border);border-radius:.5rem;padding:.3rem .65rem;font-size:.75rem;font-weight:500;color:var(--muted);cursor:pointer;transition:border-color .15s,color .15s,background .15s; }
    .btn-edit-access:hover { border-color:rgba(99,102,241,.5);color:#818cf8;background:rgba(99,102,241,.07); }
    .btn-edit-access.has-override { border-color:rgba(99,102,241,.4);color:#818cf8; }

    /* Access modal */
    .access-modal-backdrop { position:fixed;inset:0;background:rgba(0,0,0,.55);z-index:2000;display:flex;align-items:center;justify-content:center;padding:1rem;backdrop-filter:blur(3px); }
    .access-modal { background:var(--surface);border:1px solid var(--border);border-radius:1rem;width:100%;max-width:540px;max-height:90vh;overflow-y:auto;box-shadow:0 24px 60px rgba(0,0,0,.4);animation:modalIn .2s ease; }
    @keyframes modalIn { from{opacity:0;transform:translateY(12px)} to{opacity:1;transform:translateY(0)} }
    .access-modal-header { display:flex;align-items:center;justify-content:space-between;padding:1.1rem 1.4rem;border-bottom:1px solid var(--border); }
    .access-modal-header h4 { margin:0;font-size:.9rem;font-weight:700;color:var(--text); }
    .access-modal-header .modal-close { background:none;border:none;color:var(--muted);font-size:1.1rem;cursor:pointer;padding:.2rem .4rem;border-radius:.4rem;transition:color .15s,background .15s; }
    .access-modal-header .modal-close:hover { color:var(--text);background:var(--surface2); }
    .access-modal-body { padding:1.2rem 1.4rem; }
    .access-modal-footer { display:flex;align-items:center;justify-content:space-between;padding:.9rem 1.4rem;border-top:1px solid var(--border); }
    .access-section-label { font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:var(--muted);margin-bottom:.6rem; }
    .access-toggle-row { display:flex;align-items:center;justify-content:space-between;padding:.55rem .85rem;background:var(--surface2);border:1px solid var(--border);border-radius:.6rem;margin-bottom:.4rem; }
    .access-toggle-row:last-child { margin-bottom:0; }
    .access-toggle-label { font-size:.82rem;font-weight:500;color:var(--text); }
    .access-toggle-sub { font-size:.68rem;color:var(--muted);margin-top:.1rem; }
    .btn-reset-access { background:transparent;border:1px solid var(--border);color:var(--muted);border-radius:.5rem;padding:.35rem .8rem;font-size:.76rem;cursor:pointer;transition:border-color .15s,color .15s; }
    .btn-reset-access:hover { border-color:rgba(248,113,113,.4);color:#f87171; }
</style>
@endpush

@section('content')

@if(session('role_success'))
<div class="rp-toast success" id="rpToast"><i class="bi bi-check-circle-fill"></i> {{ session('role_success') }}</div>
@endif
@if(session('role_error'))
<div class="rp-toast error" id="rpToast"><i class="bi bi-exclamation-circle-fill"></i> {{ session('role_error') }}</div>
@endif

{{-- ── Permission Matrix ── --}}
<div class="rp-section">
    <div class="rp-section-header">
        <i class="bi bi-shield-check" style="color:#818cf8;font-size:1rem"></i>
        <h3>Permission Matrix</h3>
        <span style="font-size:.72rem;color:var(--muted);margin-left:.25rem">Current effective permissions per role</span>
    </div>
    <div class="rp-section-body">
        <div class="perm-grid">
            <div class="perm-head">Permission</div>
            <div class="perm-head center"><i class="bi bi-person-badge" style="margin-right:.3rem"></i>Agent</div>
            <div class="perm-head center" style="color:#fb923c"><i class="bi bi-shield-lock" style="margin-right:.3rem"></i>Super Admin</div>
            @php
            $perms = [
                ['label' => 'View Dashboard',              'agent' => true,  'super' => true],
                ['label' => 'Create & Manage Tickets',     'agent' => true,  'super' => true],
                ['label' => 'Assign & Route Tickets',      'agent' => true,  'super' => true],
                ['label' => 'View Agents',                 'agent' => true,  'super' => true],
                ['label' => 'View Reports & Analytics',    'agent' => true,  'super' => true],
                ['label' => 'Knowledge Base (Read)',        'agent' => true,  'super' => true],
                ['label' => 'Knowledge Base (Write)',       'agent' => true,  'super' => true],
                ['label' => 'Manage Personal Settings',    'agent' => true,  'super' => true],
                ['label' => 'Role Access & Permission',    'agent' => false, 'super' => true],
                ['label' => 'Assign / Change User Roles',  'agent' => false, 'super' => true],
            ];
            @endphp
            @foreach($perms as $perm)
            <div class="perm-row">
                <div class="perm-label">{{ $perm['label'] }}</div>
                <div class="center">
                    @if($perm['agent'])<i class="bi bi-check-circle-fill perm-check"></i>
                    @else<i class="bi bi-x-circle perm-cross"></i>@endif
                </div>
                <div class="center">
                    @if($perm['super'])<i class="bi bi-check-circle-fill perm-check"></i>
                    @else<i class="bi bi-x-circle perm-cross"></i>@endif
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>

{{-- ── User Roles ── --}}
<div class="rp-section" id="user-roles">
    <div class="rp-section-header">
        <i class="bi bi-people" style="color:#818cf8;font-size:1rem"></i>
        <h3>User Roles
            <span style="font-size:.72rem;font-weight:400;color:var(--muted);margin-left:.4rem">({{ $totalUsers }} users)</span>
        </h3>
        <div style="margin-left:auto">
            <form method="GET" action="{{ route('roles.index') }}#user-roles" style="display:flex;align-items:center;gap:.5rem">
                <select name="department" class="rp-role-select" onchange="this.form.submit()">
                    <option value="">All Departments</option>
                    @foreach($allDepts as $dept)
                    <option value="{{ $dept }}" {{ request('department') === $dept ? 'selected' : '' }}>{{ $dept }}</option>
                    @endforeach
                </select>
                @if(request('department'))
                <a href="{{ route('roles.index') }}#user-roles" style="font-size:.75rem;color:var(--muted);text-decoration:none;white-space:nowrap"><i class="bi bi-x-circle"></i> Clear</a>
                @endif
            </form>
        </div>
    </div>
    <div style="overflow-x:auto">
        <table class="rp-table">
            <thead>
                <tr>
                    <th>User</th>
                    <th>Department</th>
                    <th>Job Title</th>
                    <th>Current Role</th>
                    <th style="text-align:right">Change Role</th>
                </tr>
            </thead>
            <tbody>
            @foreach($users as $user)
            <tr>
                <td>
                    <div style="display:flex;align-items:center;gap:.65rem">
                        @if($user->profile_photo)
                        <img src="{{ asset('storage/'.$user->profile_photo) }}"
                             style="width:32px;height:32px;border-radius:50%;object-fit:cover;flex-shrink:0">
                        @else
                        <div style="width:32px;height:32px;background:linear-gradient(135deg,#4f46e5,#7c3aed);border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:.7rem;font-weight:700;color:#fff;flex-shrink:0">
                            {{ strtoupper(substr($user->name, 0, 1)) }}
                        </div>
                        @endif
                        <div>
                            <div style="font-size:.835rem;font-weight:600;color:var(--text)">
                                {{ $user->name }}
                                @if($user->id === auth()->id())
                                <span class="rp-self-badge">You</span>
                                @endif
                            </div>
                            <div style="font-size:.7rem;color:var(--muted)">{{ $user->email }}</div>
                        </div>
                    </div>
                </td>
                <td style="color:var(--muted);font-size:.78rem">{{ $user->department ?? '—' }}</td>
                <td style="color:var(--muted);font-size:.78rem">{{ $user->job_title ?? '—' }}</td>
                <td>
                    <div style="display:flex;flex-direction:column;gap:.3rem">
                        @if($user->role === 'super_admin')
                        <span class="role-badge-super"><i class="bi bi-shield-lock"></i> Super Admin</span>
                        @else
                        <span class="role-badge-agent"><i class="bi bi-person"></i> Agent</span>
                        @endif
                        @if(! $user->is_active)
                        <span style="display:inline-flex;align-items:center;gap:.25rem;font-size:.65rem;font-weight:600;color:#f87171;background:rgba(248,113,113,.1);border:1px solid rgba(248,113,113,.25);border-radius:9999px;padding:.1rem .45rem;width:fit-content">
                            <i class="bi bi-slash-circle"></i> Disabled
                        </span>
                        @endif
                    </div>
                </td>
                <td style="text-align:right">
                    @if($user->id === auth()->id())
                    <span style="font-size:.75rem;color:var(--muted);font-style:italic">Cannot change own role</span>
                    @else
                    <div style="display:inline-flex;align-items:center;gap:.5rem">
                        <form method="POST" action="{{ route('roles.update', $user) }}" style="display:inline-flex;align-items:center;gap:.5rem">
                            @csrf
                            @method('PUT')
                            <select name="role" class="rp-role-select">
                                <option value="agent"       {{ $user->role === 'agent'       ? 'selected' : '' }}>Agent</option>
                                <option value="super_admin" {{ $user->role === 'super_admin' ? 'selected' : '' }}>Super Admin</option>
                            </select>
                            <button type="submit" class="btn-save-role"><i class="bi bi-check-lg"></i> Save</button>
                        </form>
                        {{-- Hidden form submitted by the confirm modal --}}
                        <form method="POST" id="toggleForm_{{ $user->id }}" action="{{ route('roles.toggle-active', $user) }}" style="display:none">
                            @csrf
                            @method('PUT')
                        </form>
                        {{-- Hidden form for delete --}}
                        <form method="POST" id="deleteForm_{{ $user->id }}" action="{{ route('roles.destroy', $user) }}" style="display:none">
                            @csrf
                            @method('DELETE')
                        </form>
                        @if($user->is_active)
                        <button type="button"
                            onclick="openToggleModal({{ $user->id }}, {{ json_encode($user->name) }}, 'disable')"
                            style="display:inline-flex;align-items:center;gap:.3rem;background:transparent;border:1px solid rgba(248,113,113,.35);border-radius:.5rem;padding:.3rem .65rem;font-size:.75rem;font-weight:500;color:#f87171;cursor:pointer;transition:background .15s,border-color .15s"
                            onmouseover="this.style.background='rgba(248,113,113,.1)'" onmouseout="this.style.background='transparent'">
                            <i class="bi bi-slash-circle"></i> Disable
                        </button>
                        @else
                        <button type="button"
                            onclick="openToggleModal({{ $user->id }}, {{ json_encode($user->name) }}, 'enable')"
                            style="display:inline-flex;align-items:center;gap:.3rem;background:transparent;border:1px solid rgba(52,211,153,.35);border-radius:.5rem;padding:.3rem .65rem;font-size:.75rem;font-weight:500;color:#34d399;cursor:pointer;transition:background .15s,border-color .15s"
                            onmouseover="this.style.background='rgba(52,211,153,.1)'" onmouseout="this.style.background='transparent'">
                            <i class="bi bi-check-circle"></i> Enable
                        </button>
                        @endif
                        @if($user->role === 'agent')
                        <button type="button" class="btn-edit-access"
                            data-user-id="{{ $user->id }}"
                            data-user-name="{{ $user->name }}"
                            data-page-access="{{ json_encode($user->effectivePageAccess()) }}"
                            data-routing-depts="{{ json_encode($user->effectiveRoutingDepts()) }}"
                            data-has-override="{{ ($user->page_access !== null || $user->routing_depts !== null) ? '1' : '0' }}"
                            onclick="openAccessModal(this)">
                            <i class="bi bi-sliders"></i> Access
                        </button>
                        @endif
                        <button type="button"
                            onclick="openDeleteModal({{ $user->id }}, {{ json_encode($user->name) }})"
                            style="display:inline-flex;align-items:center;gap:.3rem;background:transparent;border:1px solid rgba(248,113,113,.35);border-radius:.5rem;padding:.3rem .65rem;font-size:.75rem;font-weight:500;color:#f87171;cursor:pointer;transition:background .15s,border-color .15s"
                            onmouseover="this.style.background='rgba(248,113,113,.1)'" onmouseout="this.style.background='transparent'">
                            <i class="bi bi-trash3"></i> Delete
                        </button>
                    </div>
                    @endif
                </td>
            </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    <div style="padding:.85rem 1.4rem;border-top:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;gap:1rem;flex-wrap:wrap">
        <span style="font-size:.75rem;color:var(--muted)">
            @if($users->total() > 0)
            Showing {{ $users->firstItem() }}–{{ $users->lastItem() }} of {{ $users->total() }} users
            @else
            No users found
            @endif
        </span>
        @if($users->hasPages())
        <div style="display:flex;align-items:center;gap:.3rem">
            {{-- Prev --}}
            @if($users->onFirstPage())
            <span style="display:inline-flex;align-items:center;justify-content:center;width:30px;height:30px;border-radius:.45rem;border:1px solid var(--border);color:var(--muted);font-size:.75rem;opacity:.4;cursor:default">
                <i class="bi bi-chevron-left"></i>
            </span>
            @else
            <a href="{{ $users->previousPageUrl().'#user-roles' }}" style="display:inline-flex;align-items:center;justify-content:center;width:30px;height:30px;border-radius:.45rem;border:1px solid var(--border);color:var(--text);font-size:.75rem;text-decoration:none;transition:border-color .15s,background .15s" onmouseover="this.style.borderColor='rgba(99,102,241,.5)';this.style.background='var(--surface2)'" onmouseout="this.style.borderColor='var(--border)';this.style.background='transparent'">
                <i class="bi bi-chevron-left"></i>
            </a>
            @endif

            {{-- Page numbers --}}
            @foreach($users->getUrlRange(1, $users->lastPage()) as $page => $url)
                @if($page == $users->currentPage())
                <span style="display:inline-flex;align-items:center;justify-content:center;width:30px;height:30px;border-radius:.45rem;background:#6366f1;color:#fff;font-size:.75rem;font-weight:700;border:1px solid #6366f1">{{ $page }}</span>
                @elseif(abs($page - $users->currentPage()) <= 2 || $page == 1 || $page == $users->lastPage())
                <a href="{{ $url }}#user-roles" style="display:inline-flex;align-items:center;justify-content:center;width:30px;height:30px;border-radius:.45rem;border:1px solid var(--border);color:var(--text);font-size:.75rem;font-weight:500;text-decoration:none;transition:border-color .15s,background .15s" onmouseover="this.style.borderColor='rgba(99,102,241,.5)';this.style.background='var(--surface2)'" onmouseout="this.style.borderColor='var(--border)';this.style.background='transparent'">{{ $page }}</a>
                @elseif(abs($page - $users->currentPage()) == 3)
                <span style="display:inline-flex;align-items:center;justify-content:center;width:30px;height:30px;color:var(--muted);font-size:.75rem">…</span>
                @endif
            @endforeach

            {{-- Next --}}
            @if($users->hasMorePages())
            <a href="{{ $users->nextPageUrl().'#user-roles' }}" style="display:inline-flex;align-items:center;justify-content:center;width:30px;height:30px;border-radius:.45rem;border:1px solid var(--border);color:var(--text);font-size:.75rem;text-decoration:none;transition:border-color .15s,background .15s" onmouseover="this.style.borderColor='rgba(99,102,241,.5)';this.style.background='var(--surface2)'" onmouseout="this.style.borderColor='var(--border)';this.style.background='transparent'">
                <i class="bi bi-chevron-right"></i>
            </a>
            @else
            <span style="display:inline-flex;align-items:center;justify-content:center;width:30px;height:30px;border-radius:.45rem;border:1px solid var(--border);color:var(--muted);font-size:.75rem;opacity:.4;cursor:default">
                <i class="bi bi-chevron-right"></i>
            </span>
            @endif
        </div>
        @endif
    </div>
</div>

{{-- ── Disable / Enable Confirmation Modal ── --}}
<div class="access-modal-backdrop" id="toggleModalBackdrop" style="display:none" onclick="if(event.target===this)closeToggleModal()">
    <div class="access-modal" style="max-width:420px">
        <div class="access-modal-header">
            <div style="display:flex;align-items:center;gap:.75rem">
                <div id="toggleModalIcon" style="width:40px;height:40px;border-radius:50%;display:flex;align-items:center;justify-content:center;flex-shrink:0;font-size:1.1rem"></div>
                <h4 id="toggleModalTitle" style="margin:0;font-size:.9rem;font-weight:700;color:var(--text)"></h4>
            </div>
            <button class="modal-close" onclick="closeToggleModal()"><i class="bi bi-x-lg"></i></button>
        </div>
        <div style="padding:1.25rem 1.4rem">
            <p id="toggleModalBody" style="font-size:.85rem;color:var(--muted);margin:0;line-height:1.6"></p>
        </div>
        <div style="display:flex;align-items:center;justify-content:flex-end;gap:.6rem;padding:.9rem 1.4rem;border-top:1px solid var(--border)">
            <button onclick="closeToggleModal()" style="background:transparent;border:1px solid var(--border);border-radius:.5rem;color:var(--muted);padding:.38rem .9rem;font-size:.78rem;cursor:pointer">Cancel</button>
            <button id="toggleModalConfirm" onclick="submitToggle()"
                style="display:inline-flex;align-items:center;gap:.4rem;border:none;border-radius:.5rem;padding:.4rem 1rem;font-size:.78rem;font-weight:700;cursor:pointer;color:#fff">
            </button>
        </div>
    </div>
</div>

{{-- ── Delete User Confirmation Modal ── --}}
<div class="access-modal-backdrop" id="deleteModalBackdrop" style="display:none" onclick="if(event.target===this)closeDeleteModal()">
    <div class="access-modal" style="max-width:420px">
        <div class="access-modal-header">
            <div style="display:flex;align-items:center;gap:.75rem">
                <div style="width:40px;height:40px;border-radius:50%;display:flex;align-items:center;justify-content:center;flex-shrink:0;font-size:1.1rem;background:rgba(248,113,113,.15);color:#f87171">
                    <i class="bi bi-trash3"></i>
                </div>
                <h4 id="deleteModalTitle" style="margin:0;font-size:.9rem;font-weight:700;color:var(--text)"></h4>
            </div>
            <button class="modal-close" onclick="closeDeleteModal()"><i class="bi bi-x-lg"></i></button>
        </div>
        <div style="padding:1.25rem 1.4rem">
            <p id="deleteModalBody" style="font-size:.85rem;color:var(--muted);margin:0;line-height:1.6"></p>
            <div style="margin-top:.85rem;padding:.75rem 1rem;background:rgba(248,113,113,.07);border:1px solid rgba(248,113,113,.2);border-radius:.6rem;font-size:.78rem;color:#f87171">
                <i class="bi bi-exclamation-triangle-fill" style="margin-right:.35rem"></i>
                This action is permanent and cannot be undone.
            </div>
        </div>
        <div style="display:flex;align-items:center;justify-content:flex-end;gap:.6rem;padding:.9rem 1.4rem;border-top:1px solid var(--border)">
            <button onclick="closeDeleteModal()" style="background:transparent;border:1px solid var(--border);border-radius:.5rem;color:var(--muted);padding:.38rem .9rem;font-size:.78rem;cursor:pointer">Cancel</button>
            <button id="deleteConfirmBtn" onclick="submitDelete()"
                style="display:inline-flex;align-items:center;gap:.4rem;border:none;border-radius:.5rem;padding:.4rem 1rem;font-size:.78rem;font-weight:700;cursor:pointer;color:#fff;background:#ef4444">
                <i class="bi bi-trash3" id="deleteConfirmIcon"></i>
                <span id="deleteConfirmText">Yes, Delete Account</span>
            </button>
        </div>
    </div>
</div>

{{-- ── Per-Agent Access Modal ── --}}
<div class="access-modal-backdrop" id="accessModalBackdrop" style="display:none" onclick="if(event.target===this)closeAccessModal()">
    <div class="access-modal">
        <div class="access-modal-header">
            <div>
                <div style="font-size:.68rem;color:var(--muted);font-weight:500;margin-bottom:.15rem">EDIT ACCESS FOR</div>
                <h4 id="modalUserName">—</h4>
            </div>
            <button class="modal-close" onclick="closeAccessModal()"><i class="bi bi-x-lg"></i></button>
        </div>
        <form method="POST" id="accessModalForm">
            @csrf
            @method('PUT')
            <div class="access-modal-body">
                {{-- Page Access --}}
                <div class="access-section-label"><i class="bi bi-layout-sidebar" style="margin-right:.3rem"></i>Page Access</div>
                @php
                $modalPages = [
                    ['key'=>'agents',          'icon'=>'bi-people',            'label'=>'Agents',                'sub'=>'View the agents directory'],
                    ['key'=>'reports',         'icon'=>'bi-bar-chart-line',    'label'=>'Reports & Analytics',   'sub'=>'View performance reports and charts'],
                    ['key'=>'knowledge_read',  'icon'=>'bi-book',              'label'=>'Knowledge Base (Read)', 'sub'=>'Browse and view articles'],
                    ['key'=>'knowledge_write', 'icon'=>'bi-pencil-square',     'label'=>'Knowledge Base (Write)','sub'=>'Create and edit articles'],
                    ['key'=>'settings',        'icon'=>'bi-gear',              'label'=>'Settings',              'sub'=>'Manage personal profile and password'],
                ];
                @endphp
                @foreach($modalPages as $mp)
                <div class="access-toggle-row">
                    <div>
                        <div class="access-toggle-label"><i class="bi {{ $mp['icon'] }}" style="margin-right:.4rem;color:var(--muted)"></i>{{ $mp['label'] }}</div>
                        <div class="access-toggle-sub">{{ $mp['sub'] }}</div>
                    </div>
                    <label class="toggle-switch">
                        <input type="checkbox" name="page_{{ $mp['key'] }}" id="modal_page_{{ $mp['key'] }}" value="1">
                        <span class="toggle-slider"></span>
                    </label>
                </div>
                @endforeach

                {{-- Routing Depts --}}
                <div class="access-section-label" style="margin-top:1.1rem"><i class="bi bi-arrow-left-right" style="margin-right:.3rem"></i>Ticket Routing Departments</div>
                <div class="dept-grid">
                    @foreach(\App\Models\SystemSetting::allDepartments() as $dept)
                    @php $slug = \Illuminate\Support\Str::slug($dept); @endphp
                    <div class="dept-check-item">
                        <input type="checkbox" id="modal_dept_{{ $slug }}" name="dept_{{ $slug }}" value="1">
                        <label for="modal_dept_{{ $slug }}">{{ $dept }}</label>
                    </div>
                    @endforeach
                </div>
            </div>
            <div class="access-modal-footer">
                <button type="button" class="btn-reset-access" onclick="resetAccessToGlobal()">
                    <i class="bi bi-arrow-counterclockwise"></i> Reset to Defaults
                </button>
                <div style="display:flex;gap:.5rem">
                    <button type="button" class="btn-reset-access" onclick="closeAccessModal()">Cancel</button>
                    <button type="submit" id="saveAccessBtn" class="btn-save-settings">
                        <i class="bi bi-check-lg" id="saveAccessIcon"></i>
                        <span id="saveAccessText">Save Access</span>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<script>
const rpToast = document.getElementById('rpToast');
if (rpToast) setTimeout(() => rpToast.style.display = 'none', 4000);

let _toggleUserId = null;

function openToggleModal(userId, userName, action) {
    _toggleUserId = userId;
    const isDisable = action === 'disable';

    document.getElementById('toggleModalIcon').style.background = isDisable ? 'rgba(248,113,113,.15)' : 'rgba(52,211,153,.15)';
    document.getElementById('toggleModalIcon').style.color      = isDisable ? '#f87171' : '#34d399';
    document.getElementById('toggleModalIcon').innerHTML        = isDisable ? '<i class="bi bi-slash-circle"></i>' : '<i class="bi bi-check-circle"></i>';
    document.getElementById('toggleModalTitle').textContent     = isDisable ? `Disable ${userName}` : `Enable ${userName}`;
    document.getElementById('toggleModalBody').textContent      = isDisable
        ? `Are you sure you want to disable ${userName}'s account? They will no longer be able to log in.`
        : `Are you sure you want to re-enable ${userName}'s account? They will be able to log in again.`;
    const btn = document.getElementById('toggleModalConfirm');
    btn.textContent  = isDisable ? 'Yes, Disable' : 'Yes, Enable';
    btn.style.background = isDisable ? '#ef4444' : '#10b981';

    const backdrop = document.getElementById('toggleModalBackdrop');
    if (backdrop.parentElement !== document.body) document.body.appendChild(backdrop);
    backdrop.style.display = 'flex';
    document.body.style.overflow = 'hidden';
}

function closeToggleModal() {
    document.getElementById('toggleModalBackdrop').style.display = 'none';
    document.body.style.overflow = '';
    _toggleUserId = null;
}

function submitToggle() {
    if (_toggleUserId) document.getElementById('toggleForm_' + _toggleUserId).submit();
}

document.addEventListener('keydown', e => {
    if (e.key === 'Escape') { closeToggleModal(); closeDeleteModal(); }
});

let _deleteUserId = null;

function openDeleteModal(userId, userName) {
    _deleteUserId = userId;
    document.getElementById('deleteModalTitle').textContent = `Delete ${userName}`;
    document.getElementById('deleteModalBody').textContent  = `Are you sure you want to permanently delete ${userName}'s account? All associated data will be removed.`;
    const backdrop = document.getElementById('deleteModalBackdrop');
    if (backdrop.parentElement !== document.body) document.body.appendChild(backdrop);
    backdrop.style.display = 'flex';
    document.body.style.overflow = 'hidden';
}

function closeDeleteModal() {
    document.getElementById('deleteModalBackdrop').style.display = 'none';
    document.body.style.overflow = '';
    _deleteUserId = null;
    const btn  = document.getElementById('deleteConfirmBtn');
    const icon = document.getElementById('deleteConfirmIcon');
    const text = document.getElementById('deleteConfirmText');
    btn.disabled      = false;
    btn.style.opacity = '';
    btn.style.cursor  = '';
    icon.className    = 'bi bi-trash3';
    icon.innerHTML    = '';
    text.textContent  = 'Yes, Delete Account';
}

function submitDelete() {
    if (!_deleteUserId) return;
    const btn  = document.getElementById('deleteConfirmBtn');
    const icon = document.getElementById('deleteConfirmIcon');
    const text = document.getElementById('deleteConfirmText');
    btn.disabled    = true;
    btn.style.opacity = '.75';
    btn.style.cursor  = 'not-allowed';
    icon.className  = '';
    icon.innerHTML  = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="animation:deleteSpin .7s linear infinite;display:inline-block"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg>';
    text.textContent = 'Deleting…';
    document.getElementById('deleteForm_' + _deleteUserId).submit();
}

const allDepts   = @json(\App\Models\SystemSetting::allDepartments());
const pageKeys   = ['agents','reports','knowledge_read','knowledge_write','settings'];
let   globalDepts, globalPageAccess;

function openAccessModal(btn) {
    const userId     = btn.dataset.userId;
    const userName   = btn.dataset.userName;
    const pageAccess = JSON.parse(btn.dataset.pageAccess);
    const depts      = JSON.parse(btn.dataset.routingDepts);

    // Store globals for reset (use data from the first load)
    if (!globalDepts)      globalDepts      = depts;
    if (!globalPageAccess) globalPageAccess = pageAccess;

    // Move backdrop to <body> so position:fixed is relative to the viewport,
    // not clipped by the overflow-x:hidden main-wrap container.
    const backdrop = document.getElementById('accessModalBackdrop');
    if (backdrop.parentElement !== document.body) {
        document.body.appendChild(backdrop);
    }

    document.getElementById('modalUserName').textContent = userName;
    document.getElementById('accessModalForm').action =
        '/roles/' + userId + '/access';

    // Set page access toggles
    pageKeys.forEach(k => {
        const el = document.getElementById('modal_page_' + k);
        if (el) el.checked = pageAccess[k] ?? true;
    });

    // Set routing dept checkboxes
    allDepts.forEach(dept => {
        const slug = dept.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '');
        const el   = document.getElementById('modal_dept_' + slug);
        if (el) el.checked = depts.includes(dept);
    });

    document.getElementById('accessModalBackdrop').style.display = 'flex';
    document.body.style.overflow = 'hidden';
}

function closeAccessModal() {
    document.getElementById('accessModalBackdrop').style.display = 'none';
    document.body.style.overflow = '';
    const btn  = document.getElementById('saveAccessBtn');
    const icon = document.getElementById('saveAccessIcon');
    const text = document.getElementById('saveAccessText');
    btn.disabled      = false;
    btn.style.opacity = '';
    btn.style.cursor  = '';
    icon.className    = 'bi bi-check-lg';
    icon.innerHTML    = '';
    text.textContent  = 'Save Access';
}

document.getElementById('accessModalForm').addEventListener('submit', function () {
    const btn  = document.getElementById('saveAccessBtn');
    const icon = document.getElementById('saveAccessIcon');
    const text = document.getElementById('saveAccessText');
    btn.disabled      = true;
    btn.style.opacity = '.75';
    btn.style.cursor  = 'not-allowed';
    icon.className    = '';
    icon.innerHTML    = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="animation:deleteSpin .7s linear infinite;display:inline-block"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg>';
    text.textContent  = 'Saving…';
});

function resetAccessToGlobal() {
    // Reset to global defaults from SystemSetting
    pageKeys.forEach(k => {
        const el = document.getElementById('modal_page_' + k);
        if (el) el.checked = true; // global default is all true
    });
    allDepts.forEach(dept => {
        const slug = dept.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '');
        const el   = document.getElementById('modal_dept_' + slug);
        if (el) el.checked = true;
    });
}

document.addEventListener('keydown', e => {
    if (e.key === 'Escape') closeAccessModal();
});
</script>
@endpush
