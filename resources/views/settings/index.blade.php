@extends('layouts.app')
@section('title','Settings')
@section('topbar-title','Settings')
@section('topbar-sub','Manage your profile and account preferences')

@push('styles')
/* ── Settings layout ── */
.settings-grid { display:grid; grid-template-columns:1fr 1fr; gap:1.25rem; align-items:start; }
@media(max-width:860px){ .settings-grid { grid-template-columns:1fr; } }

/* ── Cards ── */
.s-card {
    background:var(--surface); border:1px solid var(--border); border-radius:1.1rem;
    overflow:hidden; transition:border-color .2s, box-shadow .2s;
}
.s-card:hover { border-color:rgba(99,102,241,.3); box-shadow:0 8px 28px rgba(0,0,0,.12); }
.s-card-head {
    padding:1rem 1.35rem; border-bottom:1px solid var(--border);
    display:flex; align-items:center; gap:.55rem;
    background:linear-gradient(135deg,rgba(99,102,241,.06),rgba(124,58,237,.04));
}
.s-card-head .s-icon { width:34px;height:34px;border-radius:.65rem;display:flex;align-items:center;justify-content:center;font-size:.9rem;flex-shrink:0; }
.s-card-head .s-title { font-size:.82rem;font-weight:700;color:var(--text);text-transform:uppercase;letter-spacing:.06em; }
.s-card-head .s-sub { font-size:.7rem;color:var(--muted);margin-top:.05rem; }
.s-card-body { padding:1.35rem; }

/* ── Avatar ── */
.avatar-ring {
    width:90px;height:90px;border-radius:50%;
    background:linear-gradient(135deg,#4f46e5,#7c3aed);
    display:flex;align-items:center;justify-content:center;
    font-size:2rem;font-weight:700;color:white;flex-shrink:0;
    border:3px solid var(--border);
    box-shadow:0 4px 20px rgba(99,102,241,.25);
    position:relative;overflow:hidden;
}
.avatar-ring img { width:100%;height:100%;object-fit:cover;border-radius:50%; }
.avatar-overlay {
    position:absolute;inset:0;border-radius:50%;
    background:rgba(0,0,0,.45);display:flex;align-items:center;justify-content:center;
    opacity:0;transition:opacity .2s;cursor:pointer;
}
.avatar-wrap:hover .avatar-overlay { opacity:1; }
.avatar-wrap { position:relative;display:inline-block;cursor:pointer; }

/* ── Read-only field ── */
.s-readonly {
    display:flex;align-items:center;gap:.65rem;
    background:var(--surface2);border:1px solid var(--border);
    border-radius:.65rem;padding:.5rem .9rem;font-size:.83rem;color:var(--muted);
}
.s-readonly i { font-size:.8rem;flex-shrink:0; }
.s-badge { font-size:.65rem;font-weight:700;background:rgba(99,102,241,.12);color:#818cf8;padding:.15rem .5rem;border-radius:.35rem;margin-left:auto;white-space:nowrap; }

/* ── Buttons ── */
.s-btn-primary { background:linear-gradient(135deg,#4f46e5,#7c3aed);border:none;border-radius:.7rem;color:white;font-size:.82rem;font-weight:600;padding:.55rem 1.2rem;cursor:pointer;display:inline-flex;align-items:center;gap:.4rem;box-shadow:0 3px 12px rgba(99,102,241,.35);transition:opacity .2s,transform .15s; }
.s-btn-primary:hover { opacity:.9;transform:translateY(-1px); }
.s-btn-primary:disabled { opacity:.65;cursor:not-allowed;transform:none; }
.s-btn-danger { background:rgba(248,113,113,.1);border:1px solid rgba(248,113,113,.25);border-radius:.7rem;color:#f87171;font-size:.82rem;font-weight:600;padding:.5rem 1.15rem;cursor:pointer;display:inline-flex;align-items:center;gap:.4rem;transition:background .15s,box-shadow .15s; }
.s-btn-danger:hover { background:rgba(248,113,113,.18);box-shadow:0 3px 10px rgba(248,113,113,.2); }
.s-btn-edit { background:rgba(99,102,241,.12);border:1px solid rgba(99,102,241,.25);border-radius:.7rem;color:#818cf8;font-size:.82rem;font-weight:600;padding:.5rem 1.1rem;cursor:pointer;display:inline-flex;align-items:center;gap:.4rem;transition:background .15s,box-shadow .15s; }
.s-btn-edit:hover { background:rgba(99,102,241,.2);box-shadow:0 3px 10px rgba(99,102,241,.18); }
.s-btn-ghost { background:transparent;border:1px solid var(--border);border-radius:.7rem;color:var(--muted);font-size:.82rem;font-weight:600;padding:.5rem 1.1rem;cursor:pointer;display:inline-flex;align-items:center;gap:.4rem;transition:background .15s; }
.s-btn-ghost:hover { background:var(--surface2); }

/* ── Spinner ── */
@keyframes spin { to { transform:rotate(360deg); } }
.btn-spinner { width:13px;height:13px;border:2px solid rgba(255,255,255,.35);border-top-color:white;border-radius:50%;animation:spin .6s linear infinite;flex-shrink:0; }

/* ── Info rows ── */
.info-row { display:flex;justify-content:space-between;align-items:center;padding:.6rem 0;border-bottom:1px solid var(--border); }
.info-row:last-child { border-bottom:none;padding-bottom:0; }
.info-row .ir-lbl { font-size:.76rem;color:var(--muted);font-weight:600; }
.info-row .ir-val { font-size:.8rem;color:var(--text);font-weight:600;text-align:right;word-break:break-all;max-width:60%; }

/* ── Input error ── */
.field-err { color:#f87171;font-size:.71rem;margin-top:.2rem; }

/* ── Flash ── */
.flash-ok { background:rgba(52,211,153,.1);border:1px solid rgba(52,211,153,.25);color:#34d399;border-radius:.65rem;padding:.55rem .9rem;font-size:.78rem;font-weight:600;display:flex;align-items:center;gap:.4rem;margin-bottom:1rem; }
.flash-err { background:rgba(248,113,113,.1);border:1px solid rgba(248,113,113,.25);color:#f87171;border-radius:.65rem;padding:.55rem .9rem;font-size:.78rem;font-weight:600;display:flex;align-items:center;gap:.4rem;margin-bottom:1rem; }

/* ── Confirm Modal ── */
.confirm-backdrop {
    display:none;position:fixed;inset:0;background:rgba(0,0,0,.55);
    z-index:1000;align-items:center;justify-content:center;
    backdrop-filter:blur(3px);
}
.confirm-backdrop.open { display:flex; }
.confirm-modal {
    background:var(--surface);border:1px solid var(--border);border-radius:1.1rem;
    padding:1.75rem 1.75rem 1.4rem;max-width:400px;width:90%;
    box-shadow:0 20px 60px rgba(0,0,0,.4);
    animation:modalIn .18s ease;
}
@keyframes modalIn { from { opacity:0;transform:scale(.93) translateY(8px); } to { opacity:1;transform:none; } }
.confirm-modal-icon { width:44px;height:44px;border-radius:.85rem;background:rgba(99,102,241,.15);display:flex;align-items:center;justify-content:center;font-size:1.2rem;color:#818cf8;margin-bottom:1rem; }
.confirm-modal-title { font-size:.95rem;font-weight:700;color:var(--text);margin-bottom:.35rem; }
.confirm-modal-sub { font-size:.78rem;color:var(--muted);line-height:1.55;margin-bottom:1.25rem; }
.confirm-modal-actions { display:flex;gap:.65rem;justify-content:flex-end; }
@endpush

@section('content')
@php $u = auth()->user(); @endphp

@if($errors->any())
<div class="flash-err"><i class="bi bi-exclamation-circle-fill"></i> Please fix the errors below.</div>
@endif

{{-- ── PROFILE CARD (full width) ── --}}
<div class="s-card" style="margin-bottom:1.25rem">
    <div class="s-card-head">
        <div class="s-icon" style="background:rgba(99,102,241,.15)"><i class="bi bi-person-circle" style="color:#818cf8"></i></div>
        <div><div class="s-title">Profile Information</div><div class="s-sub">Update your name and profile photo</div></div>
        <button type="button" class="s-btn-edit" id="editProfileBtn" onclick="toggleProfileEdit()" style="margin-left:auto">
            <i class="bi bi-pencil-fill" id="editBtnIcon"></i>
            <span id="editBtnLabel">Edit Profile</span>
        </button>
    </div>
    <div class="s-card-body">
        <form method="POST" action="{{ route('settings.profile') }}" enctype="multipart/form-data" id="profileForm">
            @csrf @method('PUT')
            <div style="display:flex;align-items:flex-start;gap:1.5rem;flex-wrap:wrap">
                {{-- Avatar --}}
                <div style="display:flex;flex-direction:column;align-items:center;gap:.5rem">
                    <label for="photoInput" class="avatar-wrap" title="Click to change photo">
                        <div class="avatar-ring" id="avatarRing">
                            @if($u->profile_photo)
                                <img src="{{ asset('storage/' . $u->profile_photo) }}" id="avatarImg" alt="Avatar">
                            @else
                                <span id="avatarInitial">{{ strtoupper(substr($u->name, 0, 1)) }}</span>
                                <img src="" id="avatarImg" alt="Avatar" style="display:none;width:100%;height:100%;object-fit:cover;position:absolute;inset:0;border-radius:50%">
                            @endif
                            <div class="avatar-overlay">
                                <i class="bi bi-camera-fill" style="color:white;font-size:1.2rem"></i>
                            </div>
                        </div>
                        <input type="file" id="photoInput" name="profile_photo" accept="image/*" style="display:none" onchange="previewPhoto(this)">
                    </label>
                    <span style="font-size:.69rem;color:var(--muted);text-align:center;max-width:90px;line-height:1.35">Click photo to change</span>
                </div>

                {{-- Fields --}}
                <div style="flex:1;min-width:220px">
                    {{-- Row 1: Full Name + Username --}}
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:.85rem;margin-bottom:.85rem">
                        <div>
                            <label class="m-label">Full Name</label>
                            <input class="m-input profile-editable" name="name" type="text"
                                value="{{ old('name', $u->name) }}" required placeholder="Your full name" disabled>
                            @error('name')<div class="field-err">{{ $message }}</div>@enderror
                        </div>
                        <div>
                            <label class="m-label">Username</label>
                            <input class="m-input profile-editable" name="username" type="text"
                                value="{{ old('username', $u->username) }}" placeholder="e.g. arvin.leano" disabled>
                            @error('username')<div class="field-err">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    {{-- Row 2: Employee ID + Job Title --}}
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:.85rem;margin-bottom:.85rem">
                        <div>
                            <label class="m-label">Employee ID</label>
                            <input class="m-input profile-editable" name="employee_id" type="text"
                                value="{{ old('employee_id', $u->employee_id) }}" placeholder="e.g. EMP-0001" disabled>
                            @error('employee_id')<div class="field-err">{{ $message }}</div>@enderror
                        </div>
                        <div>
                            <label class="m-label">Job Title</label>
                            <input class="m-input profile-editable" name="job_title" type="text"
                                value="{{ old('job_title', $u->job_title) }}" placeholder="e.g. IT Support Specialist" disabled>
                            @error('job_title')<div class="field-err">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    {{-- Row 3: Primary Contact + Secondary Contact --}}
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:.85rem;margin-bottom:.85rem">
                        <div>
                            <label class="m-label">Primary Contact</label>
                            <input class="m-input profile-editable" name="primary_contact" type="tel"
                                value="{{ old('primary_contact', $u->primary_contact) }}" placeholder="e.g. +63 912 345 6789" disabled>
                            @error('primary_contact')<div class="field-err">{{ $message }}</div>@enderror
                        </div>
                        <div>
                            <label class="m-label">Secondary Contact</label>
                            <input class="m-input profile-editable" name="secondary_contact" type="tel"
                                value="{{ old('secondary_contact', $u->secondary_contact) }}" placeholder="e.g. +63 998 765 4321" disabled>
                            @error('secondary_contact')<div class="field-err">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    {{-- Row 4: Email + Department (editable in edit mode) --}}
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:.85rem">
                        {{-- Email: read-only display vs editable input --}}
                        <div>
                            <label class="m-label">Email Address</label>
                            <div class="s-readonly profile-view-field">
                                <i class="bi bi-envelope"></i>
                                <span style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap">{{ $u->email }}</span>
                                <span class="s-badge">Read only</span>
                            </div>
                            <input class="m-input profile-editable profile-edit-field" name="email" type="email"
                                value="{{ old('email', $u->email) }}" required placeholder="your@email.com"
                                disabled style="display:none">
                            @error('email')<div class="field-err">{{ $message }}</div>@enderror
                        </div>
                        {{-- Department: read-only display vs editable input --}}
                        <div>
                            <label class="m-label">Department</label>
                            <div class="s-readonly profile-view-field">
                                <i class="bi bi-building"></i>
                                {{ $u->department ?: '—' }}
                                <span class="s-badge">Read only</span>
                            </div>
                            <input class="m-input profile-editable profile-edit-field" name="department" type="text"
                                value="{{ old('department', $u->department) }}" placeholder="e.g. IT"
                                disabled style="display:none">
                            @error('department')<div class="field-err">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <div style="margin-top:1.1rem;display:none" id="saveProfileWrap">
                        <button type="button" class="s-btn-primary" id="saveProfileBtn" onclick="openConfirmModal()">
                            <i class="bi bi-floppy2-fill" id="saveBtnIcon"></i>
                            <span class="btn-spinner" id="saveBtnSpinner" style="display:none"></span>
                            <span id="saveBtnLabel">Save Profile</span>
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- ── PASSWORD + ACCOUNT (side by side) ── --}}
<div class="settings-grid">

    {{-- Change Password --}}
    <div class="s-card">
        <div class="s-card-head">
            <div class="s-icon" style="background:rgba(251,191,36,.12)"><i class="bi bi-shield-lock-fill" style="color:#fbbf24"></i></div>
            <div><div class="s-title">Change Password</div><div class="s-sub">Keep your account secure</div></div>
        </div>
        <div class="s-card-body">
            <form method="POST" action="{{ route('settings.password') }}" id="passwordForm">
                @csrf @method('PUT')
                <div class="m-field">
                    <label class="m-label">Current Password</label>
                    <input class="m-input" name="current_password" type="password" placeholder="Enter current password" required>
                    @error('current_password')<div class="field-err">{{ $message }}</div>@enderror
                </div>
                <div class="m-field">
                    <label class="m-label">New Password</label>
                    <div style="position:relative">
                        <input class="m-input" name="password" id="newPassword" type="password" placeholder="Minimum 8 characters" required style="padding-right:2.5rem">
                        <button type="button" onclick="togglePw('newPassword','newPwEye')" style="position:absolute;right:.75rem;top:50%;transform:translateY(-50%);background:none;border:none;color:var(--muted);cursor:pointer;padding:0;font-size:.9rem;line-height:1">
                            <i class="bi bi-eye" id="newPwEye"></i>
                        </button>
                    </div>
                    @error('password')<div class="field-err">{{ $message }}</div>@enderror
                </div>
                <div class="m-field" style="margin-bottom:1.1rem">
                    <label class="m-label">Confirm New Password</label>
                    <div style="position:relative">
                        <input class="m-input" name="password_confirmation" id="confirmPassword" type="password" placeholder="Repeat new password" required style="padding-right:2.5rem">
                        <button type="button" onclick="togglePw('confirmPassword','confirmPwEye')" style="position:absolute;right:.75rem;top:50%;transform:translateY(-50%);background:none;border:none;color:var(--muted);cursor:pointer;padding:0;font-size:.9rem;line-height:1">
                            <i class="bi bi-eye" id="confirmPwEye"></i>
                        </button>
                    </div>
                </div>
                <button type="button" class="s-btn-primary" onclick="openPwConfirmModal()">
                    <i class="bi bi-key-fill"></i> Update Password
                </button>
            </form>
        </div>
    </div>

    {{-- Account Info + Sign Out --}}
    <div class="s-card">
        <div class="s-card-head">
            <div class="s-icon" style="background:rgba(6,182,212,.12)"><i class="bi bi-info-circle-fill" style="color:#06b6d4"></i></div>
            <div><div class="s-title">Account Information</div><div class="s-sub">Your account details</div></div>
        </div>
        <div class="s-card-body">
            <div style="background:var(--surface2);border:1px solid var(--border);border-radius:.75rem;padding:.85rem 1rem;margin-bottom:1.1rem">
                @foreach([
                    ['Account ID',   '#' . $u->id,                          'bi-hash'],
                    ['Member Since', $u->created_at->format('F j, Y'),      'bi-calendar-event'],
                    ['Department',   $u->department ?: '—',                 'bi-building'],
                    ['Email',        $u->email,                             'bi-envelope'],
                ] as [$lbl, $val, $icon])
                <div class="info-row">
                    <div style="display:flex;align-items:center;gap:.45rem" class="ir-lbl">
                        <i class="bi {{ $icon }}" style="font-size:.75rem;color:var(--muted)"></i>{{ $lbl }}
                    </div>
                    <span class="ir-val">{{ $val }}</span>
                </div>
                @endforeach
            </div>

            <div style="display:flex;align-items:center;gap:.55rem;padding:.65rem .85rem;background:rgba(99,102,241,.08);border:1px solid rgba(99,102,241,.2);border-radius:.65rem;margin-bottom:1.1rem">
                <i class="bi bi-person-badge-fill" style="color:#818cf8"></i>
                <span style="font-size:.78rem;color:#818cf8;font-weight:600">Agent Account</span>
                <span style="margin-left:auto;font-size:.68rem;color:var(--muted)">Active</span>
                <span style="width:8px;height:8px;border-radius:50%;background:#34d399;flex-shrink:0"></span>
            </div>

            <div style="border-top:1px solid var(--border);padding-top:.9rem">
                <div style="font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:var(--muted);margin-bottom:.6rem">Session</div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="s-btn-danger">
                        <i class="bi bi-box-arrow-right"></i> Sign Out
                    </button>
                </form>
            </div>
        </div>
    </div>

</div>

{{-- ── CONFIRM UPDATE PASSWORD MODAL ── --}}
<div class="confirm-backdrop" id="pwConfirmBackdrop" onclick="closePwConfirmModal(event)">
    <div class="confirm-modal">
        <div class="confirm-modal-icon" style="background:rgba(251,191,36,.15)"><i class="bi bi-shield-lock-fill" style="color:#fbbf24"></i></div>
        <div class="confirm-modal-title">Update Password?</div>
        <div class="confirm-modal-sub">
            Are you sure you want to change your password? You will need your new password to sign in next time.
        </div>
        <div class="confirm-modal-actions">
            <button type="button" class="s-btn-ghost" onclick="closePwConfirmModal()">Cancel</button>
            <button type="button" class="s-btn-primary" id="confirmPwBtn" onclick="confirmPwSave()">
                <i class="bi bi-key-fill" id="confirmPwBtnIcon"></i>
                <span class="btn-spinner" id="confirmPwBtnSpinner" style="display:none"></span>
                <span id="confirmPwBtnLabel">Yes, Update Password</span>
            </button>
        </div>
    </div>
</div>

{{-- ── CONFIRM SAVE MODAL ── --}}
<div class="confirm-backdrop" id="confirmBackdrop" onclick="closeConfirmModal(event)">
    <div class="confirm-modal">
        <div class="confirm-modal-icon"><i class="bi bi-floppy2-fill"></i></div>
        <div class="confirm-modal-title">Save Profile Changes?</div>
        <div class="confirm-modal-sub">
            You're about to update your profile information. Please review your changes before confirming.
        </div>
        <div class="confirm-modal-actions">
            <button type="button" class="s-btn-ghost" onclick="closeConfirmModal()">Cancel</button>
            <button type="button" class="s-btn-primary" id="confirmSaveBtn" onclick="confirmSave()">
                <i class="bi bi-floppy2-fill" id="confirmSaveBtnIcon"></i>
                <span class="btn-spinner" id="confirmSaveBtnSpinner" style="display:none"></span>
                <span id="confirmSaveBtnLabel">Yes, Save Changes</span>
            </button>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
function previewPhoto(input) {
    if (!input.files || !input.files[0]) return;
    const reader = new FileReader();
    reader.onload = function(e) {
        const img     = document.getElementById('avatarImg');
        const initial = document.getElementById('avatarInitial');
        img.src = e.target.result;
        img.style.display = '';
        img.style.position = 'absolute';
        img.style.inset = '0';
        img.style.borderRadius = '50%';
        if (initial) initial.style.display = 'none';
    };
    reader.readAsDataURL(input.files[0]);
}

let profileEditMode = false;

function toggleProfileEdit() {
    profileEditMode = !profileEditMode;
    const editables   = document.querySelectorAll('.profile-editable');
    const viewFields  = document.querySelectorAll('.profile-view-field');
    const editFields  = document.querySelectorAll('.profile-edit-field');
    const saveWrap    = document.getElementById('saveProfileWrap');
    const editIcon    = document.getElementById('editBtnIcon');
    const editLabel   = document.getElementById('editBtnLabel');

    if (profileEditMode) {
        editables.forEach(i => i.removeAttribute('disabled'));
        viewFields.forEach(el => el.style.display = 'none');
        editFields.forEach(el => el.style.display  = '');
        saveWrap.style.display = 'block';
        editIcon.className  = 'bi bi-x-lg';
        editLabel.textContent = 'Cancel';
    } else {
        editables.forEach(i => i.setAttribute('disabled', ''));
        viewFields.forEach(el => el.style.display = '');
        editFields.forEach(el => el.style.display  = 'none');
        saveWrap.style.display = 'none';
        editIcon.className  = 'bi bi-pencil-fill';
        editLabel.textContent = 'Edit Profile';
    }
}

// Move modals to <body> to escape overflow-x:hidden on .main-wrap
document.addEventListener('DOMContentLoaded', function () {
    document.body.appendChild(document.getElementById('confirmBackdrop'));
    document.body.appendChild(document.getElementById('pwConfirmBackdrop'));
});

function togglePw(inputId, iconId) {
    const input = document.getElementById(inputId);
    const icon  = document.getElementById(iconId);
    const show  = input.type === 'password';
    input.type  = show ? 'text' : 'password';
    icon.className = show ? 'bi bi-eye-slash' : 'bi bi-eye';
}

function openPwConfirmModal() {
    document.getElementById('pwConfirmBackdrop').classList.add('open');
}

function closePwConfirmModal(e) {
    if (e && e.target !== document.getElementById('pwConfirmBackdrop')) return;
    document.getElementById('pwConfirmBackdrop').classList.remove('open');
}

function confirmPwSave() {
    const btn     = document.getElementById('confirmPwBtn');
    const icon    = document.getElementById('confirmPwBtnIcon');
    const spinner = document.getElementById('confirmPwBtnSpinner');
    const label   = document.getElementById('confirmPwBtnLabel');
    btn.disabled          = true;
    icon.style.display    = 'none';
    spinner.style.display = 'inline-block';
    label.textContent     = 'Updating…';
    document.getElementById('passwordForm').submit();
}

function openConfirmModal() {
    document.getElementById('confirmBackdrop').classList.add('open');
}

function closeConfirmModal(e) {
    if (e && e.target !== document.getElementById('confirmBackdrop')) return;
    document.getElementById('confirmBackdrop').classList.remove('open');
}

function confirmSave() {
    const btn     = document.getElementById('confirmSaveBtn');
    const icon    = document.getElementById('confirmSaveBtnIcon');
    const spinner = document.getElementById('confirmSaveBtnSpinner');
    const label   = document.getElementById('confirmSaveBtnLabel');

    btn.disabled          = true;
    icon.style.display    = 'none';
    spinner.style.display = 'inline-block';
    label.textContent     = 'Saving…';

    document.getElementById('profileForm').submit();
}
</script>
@endpush
