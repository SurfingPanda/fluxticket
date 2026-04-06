@php
$typeLabels = [
    'incident'        => 'Incident Tickets',
    'service_request' => 'Service Requests',
    'question'        => 'Question',
    'change_request'  => 'Change Request',
];
$pageTitle = isset($type) && $type ? ($typeLabels[$type] ?? 'All Tickets') : 'All Tickets';
@endphp
<!DOCTYPE html>
<html lang="en" id="html-root">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $pageTitle }} — FluxTickets</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <style>
        *, *::before, *::after { box-sizing: border-box; }
        :root { --bg:#0f172a; --surface:#1e293b; --surface2:#263348; --border:#334155; --text:#e2e8f0; --muted:#94a3b8; --accent:#6366f1; --sidebar-w:240px; }
        body:not(.dark) { --bg:#e8edf6; --surface:#ffffff; --surface2:#f0f4fb; --border:#b8c6d8; --text:#0f172a; --muted:#475569; }
        html,body { height:100%; margin:0; }
        body { font-family:'Segoe UI',system-ui,sans-serif; background:var(--bg); color:var(--text); display:flex; transition:background .3s,color .3s; }

        /* Sidebar */
        .sidebar { width:var(--sidebar-w); background:var(--surface); border-right:1px solid var(--border); display:flex; flex-direction:column; position:fixed; top:0; left:0; bottom:0; z-index:100; transition:width .25s ease, background .3s, border-color .3s; overflow:hidden; }
        .sidebar-brand { padding:.65rem 1rem; border-bottom:1px solid var(--border); display:flex; align-items:center; gap:.65rem; min-height:52px; flex-shrink:0; }
        .brand-icon { width:40px; height:40px; min-width:40px; display:flex; align-items:center; justify-content:center; flex-shrink:0; }
        .brand-icon img { width:40px; height:40px; object-fit:contain; filter:drop-shadow(0 2px 6px rgba(180,20,40,.45)); }
        .brand-name { font-size:1rem; font-weight:700; color:var(--text); white-space:nowrap; overflow:hidden; transition:opacity .2s,max-width .25s; max-width:140px; }
        .sidebar-toggle { margin-left:auto; flex-shrink:0; background:transparent; border:1px solid var(--border); border-radius:.4rem; width:26px; height:26px; min-width:26px; display:flex; align-items:center; justify-content:center; cursor:pointer; color:var(--muted); font-size:.8rem; transition:background .15s,color .15s; }
        .sidebar-toggle:hover { background:var(--surface2); color:var(--text); }

        .sidebar-section-label { font-size:.65rem; font-weight:700; text-transform:uppercase; letter-spacing:.08em; color:var(--muted); padding:.65rem 1.25rem .3rem; white-space:nowrap; transition:opacity .2s; }
        .nav-item-link { display:flex; align-items:center; gap:.7rem; padding:.38rem .875rem; border-radius:.5rem; margin:.05rem .4rem; color:var(--muted); font-size:.82rem; font-weight:500; text-decoration:none !important; transition:background .15s,color .15s; cursor:pointer; white-space:nowrap; }
        .nav-item-link:hover { background:var(--surface2); color:var(--text); }
        .nav-item-link.active { background:linear-gradient(135deg,rgba(99,102,241,.2),rgba(124,58,237,.15)); color:#818cf8; }
        /* Tickets dropdown */
        .nav-dropdown-trigger { width:100%; background:none; border:none; text-align:left; font-family:inherit; cursor:pointer; }
        .nav-chevron { font-size:.68rem; margin-left:auto; flex-shrink:0; transition:transform .25s cubic-bezier(.4,0,.2,1); }
        .nav-submenu { overflow:hidden; max-height:0; transition:max-height .3s cubic-bezier(.4,0,.2,1); }
        .nav-submenu.open { max-height:350px; }
        .nav-sub-item { padding-left:2.1rem !important; font-size:.78rem; }
        .sidebar.mini .nav-chevron,.sidebar.mini .nav-submenu { display:none; }
        .nav-icon { font-size:1rem; flex-shrink:0; }
        .nav-text { transition:opacity .2s; }
        .sidebar-footer { margin-top:auto; padding:.5rem .75rem; border-top:1px solid var(--border); }
        .user-chip { display:flex; align-items:center; gap:.65rem; padding:.5rem .65rem; border-radius:.6rem; background:var(--surface2); }
        .avatar { width:32px; height:32px; background:linear-gradient(135deg,#4f46e5,#7c3aed); border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:.75rem; font-weight:700; color:white; flex-shrink:0; }
        .user-info { transition:opacity .2s; min-width:0; flex:1; }
        .nav-badge { transition:opacity .2s; }

        /* Minimized sidebar */
        .sidebar.mini { width:60px; }
        .sidebar.mini .brand-name,
        .sidebar.mini .sidebar-section-label,
        .sidebar.mini .nav-text,
        .sidebar.mini .user-info,
        .sidebar.mini .nav-badge { opacity:0; pointer-events:none; max-width:0; overflow:hidden; }
        .sidebar.mini .nav-item-link { justify-content:center; padding:.38rem 0; margin:.05rem .4rem; gap:0; }
        .sidebar.mini .nav-icon { font-size:1.05rem; }
        .sidebar.mini .sidebar-brand { justify-content:center; gap:0; padding:.85rem 0; }
        .sidebar.mini .brand-icon { display:none; }
        .sidebar.mini .sidebar-toggle { margin:0; }
        .sidebar.mini .user-chip { display:none; }
        .sidebar.mini .mini-signout { display:flex !important; }
        .mini-signout { display:none; justify-content:center; align-items:center; padding:.4rem 0; }

        /* Layout */
        .main-wrap { margin-left:var(--sidebar-w); flex:1; display:flex; flex-direction:column; min-height:100vh; transition:margin-left .25s ease; overflow-x:hidden; }
        .main-wrap.mini { margin-left:60px; }
        .topbar { position:sticky; top:0; z-index:50; background:var(--surface); border-bottom:1px solid var(--border); padding:.75rem 1.25rem; display:flex; align-items:center; justify-content:space-between; gap:.75rem; transition:background .3s,border-color .3s; }
        .content { padding:1.25rem; flex:1; overflow-x:hidden; }

        /* Panel */
        .panel { background:var(--surface); border:1px solid var(--border); border-radius:1rem; transition:background .3s,border-color .3s; }
        .panel-header { padding:.875rem 1.25rem; border-bottom:1px solid var(--border); display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:.5rem; }
        .panel-title { font-size:.875rem; font-weight:700; color:var(--text); }

        /* Table */
        .flux-table { width:100%; border-collapse:collapse; font-size:.78rem; }
        .flux-table thead th { padding:.55rem .75rem; font-size:.63rem; font-weight:700; text-transform:uppercase; letter-spacing:.05em; color:var(--muted); border-bottom:1px solid var(--border); white-space:nowrap; overflow:hidden; }
        .flux-table tbody tr { border-bottom:1px solid var(--border); transition:background .15s; }
        .flux-table tbody tr:last-child { border-bottom:none; }
        .flux-table tbody tr:hover { background:var(--surface2); }
        .flux-table tbody td { padding:.65rem .75rem; color:var(--text); vertical-align:middle; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }

        /* Badges */
        .badge-status { display:inline-flex; align-items:center; gap:.3rem; padding:.22rem .65rem; border-radius:9999px; font-size:.7rem; font-weight:600; white-space:nowrap; }
        .badge-status::before { content:''; width:6px; height:6px; border-radius:50%; flex-shrink:0; }
        .s-open { background:rgba(99,102,241,.15); color:#818cf8; } .s-open::before { background:#818cf8; }
        .s-progress { background:rgba(251,191,36,.12); color:#fbbf24; } .s-progress::before { background:#fbbf24; }
        .s-resolved { background:rgba(52,211,153,.12); color:#34d399; } .s-resolved::before { background:#34d399; }
        .s-closed { background:rgba(148,163,184,.12); color:#94a3b8; } .s-closed::before { background:#94a3b8; }
        .badge-priority { display:inline-block; padding:.18rem .55rem; border-radius:.35rem; font-size:.68rem; font-weight:700; }
        .p-high { background:rgba(248,113,113,.15); color:#f87171; }
        .p-medium { background:rgba(251,191,36,.12); color:#fbbf24; }
        .p-low { background:rgba(52,211,153,.12); color:#34d399; }

        /* Filter buttons */
        .filter-btn { background:var(--surface2); border:1px solid var(--border); border-radius:.5rem; padding:.3rem .85rem; font-size:.78rem; font-weight:600; color:var(--muted); cursor:pointer; transition:all .15s; }
        .filter-btn:hover, .filter-btn.active { background:rgba(99,102,241,.15); border-color:rgba(99,102,241,.4); color:#818cf8; }

        /* Search */
        .topbar-search { display:flex; align-items:center; gap:.5rem; background:var(--surface2); border:1px solid var(--border); border-radius:.6rem; padding:.4rem .85rem; width:220px; transition:border-color .2s; }
        .topbar-search:focus-within { border-color:var(--accent); }
        .topbar-search input { border:none; background:transparent; outline:none; font-size:.825rem; color:var(--text); width:100%; }
        .topbar-search input::placeholder { color:var(--muted); }

        /* Buttons */
        .btn-new { background:linear-gradient(135deg,#4f46e5,#7c3aed); border:none; border-radius:.6rem; color:white; font-weight:600; font-size:.8rem; padding:.45rem 1rem; display:flex; align-items:center; gap:.4rem; transition:opacity .2s,transform .15s; box-shadow:0 3px 12px rgba(99,102,241,.35); cursor:pointer; text-decoration:none !important; }
        .btn-new:hover { opacity:.9; transform:translateY(-1px); color:white; }
        .btn-view { background:var(--surface2); border:1px solid var(--border); border-radius:.45rem; padding:.25rem .7rem; font-size:.72rem; font-weight:600; color:var(--muted); cursor:pointer; transition:all .15s; white-space:nowrap; }

        /* SLA badges */
        .sla-badge { display:inline-flex; align-items:center; gap:.3rem; padding:.2rem .6rem; border-radius:9999px; font-size:.68rem; font-weight:700; white-space:nowrap; }
        .sla-ok      { background:rgba(52,211,153,.12); color:#34d399; }
        .sla-warning { background:rgba(251,191,36,.12);  color:#fbbf24; }
        .sla-breached{ background:rgba(248,113,113,.12); color:#f87171; }
        .sla-met     { background:rgba(99,102,241,.12);  color:#818cf8; }
        .sla-dot { width:6px; height:6px; border-radius:50%; flex-shrink:0; }
        .sla-ok .sla-dot { background:#34d399; }
        .sla-warning .sla-dot { background:#fbbf24; }
        .sla-breached .sla-dot { background:#f87171; }
        .sla-met .sla-dot { background:#818cf8; }
        .btn-view:hover { background:rgba(99,102,241,.15); border-color:rgba(99,102,241,.4); color:#818cf8; }
        .btn-assign { background:rgba(52,211,153,.12); border:1px solid rgba(52,211,153,.3); border-radius:.45rem; padding:.25rem .7rem; font-size:.72rem; font-weight:600; color:#34d399; cursor:pointer; transition:all .15s; white-space:nowrap; }
        .btn-assign:hover { background:rgba(52,211,153,.2); }

        /* Dark toggle */
        .dark-toggle { width:2.5rem; height:1.35rem; background:#6366f1; border-radius:9999px; position:relative; cursor:pointer; transition:background .3s; border:none; outline:none; }
        .dark-toggle::after { content:''; position:absolute; top:2px; left:2px; width:1rem; height:1rem; background:white; border-radius:50%; transition:transform .3s; box-shadow:0 1px 3px rgba(0,0,0,.2); }
        body:not(.dark) .dark-toggle { background:#cbd5e1; }
        body:not(.dark) .dark-toggle::after { transform:translateX(0); }
        body.dark .dark-toggle::after { transform:translateX(1.15rem); }

        /* ── Modal ── */
        .flux-modal-backdrop { position:fixed; inset:0; z-index:999; background:rgba(0,0,0,.6); backdrop-filter:blur(4px); display:flex; align-items:center; justify-content:center; padding:1rem; opacity:0; pointer-events:none; transition:opacity .2s; }
        .flux-modal-backdrop.open { opacity:1; pointer-events:all; }
        .flux-modal { background:var(--surface); border:1px solid var(--border); border-radius:1.25rem; width:100%; max-width:640px; box-shadow:0 30px 80px rgba(0,0,0,.45); transform:translateY(16px) scale(.98); transition:transform .25s,opacity .25s; opacity:0; max-height:92vh; display:flex; flex-direction:column; }
        .flux-modal-backdrop.open .flux-modal { transform:translateY(0) scale(1); opacity:1; }
        .flux-modal-header { padding:1.15rem 1.5rem; border-bottom:1px solid var(--border); display:flex; align-items:flex-start; justify-content:space-between; flex-shrink:0; }
        .flux-modal-body { padding:1.35rem 1.5rem; overflow-y:auto; flex:1; }
        .flux-modal-footer { padding:1rem 1.5rem; border-top:1px solid var(--border); display:flex; align-items:center; justify-content:flex-end; gap:.65rem; flex-shrink:0; flex-wrap:wrap; }

        /* Assign modal */
        .assign-modal { max-width:420px; }

        /* Modal form fields */
        .m-field { margin-bottom:1rem; }
        .m-label { display:block; font-size:.7rem; font-weight:700; text-transform:uppercase; letter-spacing:.07em; color:var(--muted); margin-bottom:.4rem; }
        .m-input, .m-select, .m-textarea { width:100%; background:var(--surface2); border:1px solid var(--border); border-radius:.6rem; color:var(--text); font-size:.875rem; font-family:inherit; padding:.5rem .85rem; outline:none; transition:border-color .2s,box-shadow .2s; }
        .m-input::placeholder, .m-textarea::placeholder { color:var(--muted); }
        .m-input:focus, .m-select:focus, .m-textarea:focus { border-color:var(--accent); box-shadow:0 0 0 3px rgba(99,102,241,.2); }
        .m-select option { background:var(--surface); color:var(--text); }
        .m-textarea { resize:vertical; min-height:100px; }
        .m-row { display:grid; grid-template-columns:1fr 1fr; gap:.85rem; }

        /* Info grid in modal */
        .info-grid { display:grid; grid-template-columns:1fr 1fr; gap:.65rem 1.25rem; margin-bottom:1.25rem; }
        .info-item .info-label { font-size:.68rem; font-weight:700; text-transform:uppercase; letter-spacing:.06em; color:var(--muted); margin-bottom:.2rem; }
        .info-item .info-val { font-size:.875rem; color:var(--text); font-weight:500; }

        /* Description box */
        .desc-box { background:var(--surface2); border:1px solid var(--border); border-radius:.75rem; padding:.85rem 1rem; font-size:.85rem; color:var(--text); line-height:1.6; white-space:pre-wrap; margin-bottom:1rem; }

        /* Section divider */
        .modal-section { font-size:.72rem; font-weight:700; text-transform:uppercase; letter-spacing:.07em; color:var(--muted); margin:.25rem 0 .75rem; display:flex; align-items:center; gap:.5rem; }
        .modal-section::after { content:''; flex:1; height:1px; background:var(--border); }

        /* Action buttons in footer */
        .btn-cancel { background:var(--surface2); border:1px solid var(--border); border-radius:.6rem; color:var(--muted); font-size:.875rem; font-weight:600; padding:.5rem 1.1rem; cursor:pointer; transition:background .15s,color .15s; }
        .btn-cancel:hover { background:var(--border); color:var(--text); }
        .btn-submit { background:linear-gradient(135deg,#4f46e5,#7c3aed); border:none; border-radius:.6rem; color:white; font-size:.875rem; font-weight:600; padding:.5rem 1.4rem; cursor:pointer; box-shadow:0 3px 12px rgba(99,102,241,.35); display:flex; align-items:center; gap:.4rem; transition:opacity .2s,transform .15s; }
        .btn-submit:hover { opacity:.9; transform:translateY(-1px); }
        .btn-accept { background:rgba(52,211,153,.15); border:1px solid rgba(52,211,153,.35); border-radius:.6rem; color:#34d399; font-size:.875rem; font-weight:600; padding:.5rem 1.1rem; cursor:pointer; transition:all .15s; display:flex; align-items:center; gap:.4rem; }
        .btn-accept:hover { background:rgba(52,211,153,.25); }

        /* Resolution badge */
        .resolution-box { background:rgba(52,211,153,.08); border:1px solid rgba(52,211,153,.2); border-radius:.75rem; padding:.85rem 1rem; font-size:.85rem; color:#34d399; line-height:1.6; white-space:pre-wrap; }

        ::-webkit-scrollbar { width:5px; } ::-webkit-scrollbar-track { background:transparent; } ::-webkit-scrollbar-thumb { background:var(--border); border-radius:9999px; }
        a { text-decoration:none !important; }
        @keyframes spin { to { transform:rotate(360deg); } }

        /* ══ Flux Animations ══ */
        @keyframes pageIn  { from{opacity:0;transform:translateY(16px)} to{opacity:1;transform:translateY(0)} }
        @keyframes pageOut { from{opacity:1;transform:translateY(0)} to{opacity:0;transform:translateY(-10px)} }
        @keyframes popIn   { from{opacity:0;transform:scale(.94) translateY(10px)} to{opacity:1;transform:scale(1) translateY(0)} }
        @keyframes shimmer { from{background-position:-400% 0} to{background-position:400% 0} }

        .content { animation:pageIn .42s cubic-bezier(.22,.68,0,1.2) both; }
        body.page-leaving .content { animation:pageOut .22s ease forwards; pointer-events:none; }

        #flux-progress { position:fixed; top:0; left:0; z-index:10000; height:3px; width:0; background:linear-gradient(90deg,#4f46e5,#7c3aed,#06b6d4,#7c3aed,#4f46e5); background-size:200% 100%; animation:shimmer 2s linear infinite; border-radius:0 9999px 9999px 0; pointer-events:none; opacity:0; transition:width .5s cubic-bezier(.4,0,.2,1),opacity .3s ease; }

        .reveal { opacity:0; transform:translateY(22px); transition:opacity .5s ease,transform .5s cubic-bezier(.22,.68,0,1.2); }
        .reveal.visible { opacity:1; transform:translateY(0); }

        .stat-card { animation:popIn .45s cubic-bezier(.22,.68,0,1.2) both; }
        .stat-card:nth-child(1) { animation-delay:.04s; }
        .stat-card:nth-child(2) { animation-delay:.12s; }
        .stat-card:nth-child(3) { animation-delay:.20s; }
        .stat-card:nth-child(4) { animation-delay:.28s; }

        .skel { background:linear-gradient(90deg,var(--surface2) 25%,var(--border) 50%,var(--surface2) 75%); background-size:400% 100%; animation:shimmer 1.6s ease infinite; border-radius:.4rem; }

        /* ── Mobile / Responsive ── */
        .mob-hamburger { display:none; background:transparent; border:1px solid var(--border); border-radius:.4rem; width:34px; height:34px; min-width:34px; align-items:center; justify-content:center; cursor:pointer; color:var(--muted); font-size:1.05rem; flex-shrink:0; transition:background .15s,color .15s; }
        .mob-hamburger:hover { background:var(--surface2); color:var(--text); }
        .sidebar-overlay { display:none; position:fixed; inset:0; z-index:99; background:rgba(0,0,0,.45); backdrop-filter:blur(2px); }
        .sidebar-overlay.open { display:block; }
        @media (max-width:768px) {
            .sidebar { transform:translateX(-100%); width:var(--sidebar-w) !important; transition:transform .25s ease,background .3s,border-color .3s; }
            .sidebar.mobile-open { transform:translateX(0); box-shadow:4px 0 30px rgba(0,0,0,.3); }
            .sidebar.mini { transform:translateX(-100%); width:var(--sidebar-w) !important; }
            .sidebar.mini.mobile-open { transform:translateX(0); }
            .main-wrap,.main-wrap.mini { margin-left:0 !important; }
            .mob-hamburger { display:flex; }
            .topbar { padding:.6rem .875rem; gap:.5rem; }
            .content { padding:.875rem; }
            .topbar-search { display:none !important; }
            .btn-new-text { display:none; }
            .flux-modal { border-radius:.875rem; max-width:calc(100vw - 1.5rem); }
            .flux-modal-body { padding:1rem 1.1rem; }
            .flux-modal-header { padding:.875rem 1rem; }
            .flux-modal-footer { padding:.75rem 1rem; }
            .m-row { grid-template-columns:1fr !important; }
        }
        @media (max-width:480px) {
            .content { padding:.65rem; }
        }
    </style>
</head>
<body class="dark">

{{-- Mobile sidebar overlay --}}
<div class="sidebar-overlay" id="sidebarOverlay" onclick="closeMobileSidebar()"></div>

{{-- SIDEBAR --}}
<aside class="sidebar">
    <div class="sidebar-brand">
        <div class="brand-icon">
            <img src="{{ asset('image/Gemini_Generated_Image_1w1sif1w1sif1w1s-removebg-preview.png') }}" alt="FluxTickets Logo">
        </div>
        <span class="brand-name">FluxTickets</span>
        <button class="sidebar-toggle" id="sidebarToggle" title="Toggle sidebar"><i class="bi bi-layout-sidebar-reverse"></i></button>
    </div>
    <div style="flex:1;overflow-y:auto;overflow-x:hidden;padding-bottom:.5rem">
        <div class="sidebar-section-label">Main</div>
        <a class="nav-item-link" href="{{ route('dashboard') }}"><i class="bi bi-grid-fill nav-icon"></i><span class="nav-text">Dashboard</span></a>
        <button class="nav-item-link nav-dropdown-trigger active" id="ticketsDropdownTrigger" type="button">
            <i class="bi bi-ticket-perforated nav-icon"></i>
            <span class="nav-text">All Tickets</span>
            <i class="bi bi-chevron-down nav-chevron nav-badge"></i>
        </button>
        <div class="nav-submenu open" id="ticketsSubmenu">
            <a class="nav-item-link nav-sub-item {{ !($type??null) ? 'active' : '' }}" href="{{ route('tickets.index') }}">
                <i class="bi bi-ticket nav-icon" style="font-size:.85rem"></i><span class="nav-text">All Tickets</span>
            </a>
            <a class="nav-item-link nav-sub-item" href="{{ route('queue') }}">
                <i class="bi bi-clock-history nav-icon" style="font-size:.85rem"></i><span class="nav-text">My Queue</span>
            </a>
            <a class="nav-item-link nav-sub-item {{ ($type??null)==='incident' ? 'active' : '' }}" href="{{ route('tickets.index',['type'=>'incident']) }}">
                <i class="bi bi-exclamation-triangle nav-icon" style="font-size:.85rem"></i><span class="nav-text">Incident Tickets</span>
            </a>
            <a class="nav-item-link nav-sub-item {{ ($type??null)==='service_request' ? 'active' : '' }}" href="{{ route('tickets.index',['type'=>'service_request']) }}">
                <i class="bi bi-tools nav-icon" style="font-size:.85rem"></i><span class="nav-text">Service Requests</span>
            </a>
            <a class="nav-item-link nav-sub-item {{ ($type??null)==='question' ? 'active' : '' }}" href="{{ route('tickets.index',['type'=>'question']) }}">
                <i class="bi bi-question-circle nav-icon" style="font-size:.85rem"></i><span class="nav-text">Question</span>
            </a>
            <a class="nav-item-link nav-sub-item {{ ($type??null)==='change_request' ? 'active' : '' }}" href="{{ route('tickets.index',['type'=>'change_request']) }}">
                <i class="bi bi-arrow-repeat nav-icon" style="font-size:.85rem"></i><span class="nav-text">Change Request</span>
            </a>
        </div>
        <div class="sidebar-section-label">Manage</div>
        <a class="nav-item-link" href="{{ route('agents.index') }}"><i class="bi bi-people nav-icon"></i><span class="nav-text">Agents</span></a>
        <a class="nav-item-link" href="{{ route('reports.index') }}"><i class="bi bi-bar-chart-line nav-icon"></i><span class="nav-text">Reports</span></a>
        <a class="nav-item-link" href="{{ route('knowledge.index') }}"><i class="bi bi-book nav-icon"></i><span class="nav-text">Knowledge Base</span></a>
        <div class="sidebar-section-label">System</div>
        <a class="nav-item-link" href="{{ route('settings.index') }}"><i class="bi bi-gear nav-icon"></i><span class="nav-text">Settings</span></a>
    </div>
    <div class="sidebar-footer">
        <div class="user-chip">
            @if(auth()->user()->profile_photo)
                <img src="{{ asset('storage/' . auth()->user()->profile_photo) }}" alt="Avatar" style="width:32px;height:32px;min-width:32px;border-radius:50%;object-fit:cover;flex-shrink:0">
            @else
                <div class="avatar">{{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}</div>
            @endif
            <div class="user-info">
                <div style="font-size:.8rem;font-weight:600;color:var(--text);white-space:nowrap;overflow:hidden;text-overflow:ellipsis">{{ auth()->user()->name ?? 'User' }}</div>
                <div style="font-size:.7rem;color:var(--muted);white-space:nowrap;overflow:hidden;text-overflow:ellipsis">{{ auth()->user()->email ?? '' }}</div>
            </div>
            <form method="POST" action="{{ route('logout') }}" style="flex-shrink:0">
                @csrf
                <button type="submit" title="Sign out" style="background:none;border:none;color:var(--muted);cursor:pointer;padding:.15rem;font-size:.9rem;line-height:1;transition:color .15s" onmouseover="this.style.color='var(--text)'" onmouseout="this.style.color='var(--muted)'"><i class="bi bi-box-arrow-right"></i></button>
            </form>
        </div>
        <div class="mini-signout">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" title="Sign out"
                    style="background:none;border:1px solid var(--border);border-radius:.5rem;color:var(--muted);cursor:pointer;width:36px;height:36px;display:flex;align-items:center;justify-content:center;font-size:1rem;transition:background .15s,color .15s"
                    onmouseover="this.style.background='rgba(248,113,113,.1)';this.style.color='#f87171';this.style.borderColor='rgba(248,113,113,.3)'"
                    onmouseout="this.style.background='none';this.style.color='var(--muted)';this.style.borderColor='var(--border)'">
                    <i class="bi bi-box-arrow-right"></i>
                </button>
            </form>
        </div>
    </div>
</aside>

{{-- MAIN --}}
<div class="main-wrap">
    <header class="topbar">
        <div style="display:flex;align-items:center;gap:.6rem">
            <button class="mob-hamburger" id="mobHamburger" aria-label="Menu"><i class="bi bi-list"></i></button>
            <div>
                <div style="font-size:1.05rem;font-weight:700;color:var(--text)">{{ $pageTitle }}</div>
                <div style="font-size:.72rem;color:var(--muted)">{{ $tickets->count() }} ticket{{ $tickets->count() !== 1 ? 's' : '' }} total</div>
            </div>
        </div>
        <div class="d-flex align-items-center gap-3">
            <div class="topbar-search d-none d-md-flex">
                <i class="bi bi-search" style="color:var(--muted);font-size:.8rem"></i>
                <input type="text" id="searchInput" placeholder="Search tickets…" oninput="applyFilters()">
            </div>
            <div class="d-flex align-items-center gap-2">
                <i class="bi bi-sun" style="color:#fbbf24;font-size:.8rem"></i>
                <button class="dark-toggle" id="darkToggle" aria-label="Toggle dark mode"></button>
                <i class="bi bi-moon-stars" style="color:#818cf8;font-size:.8rem"></i>
            </div>
            @include('partials.notif-bell')
            <button class="btn-new" onclick="openModal('newTicketModal')"><i class="bi bi-plus-lg"></i><span class="btn-new-text"> New Ticket</span></button>
        </div>
    </header>

    <main class="content">

        {{-- Flash --}}
        @if(session('success'))
        <div id="flash-ok" style="display:flex;align-items:center;gap:.65rem;background:rgba(52,211,153,.12);border:1px solid rgba(52,211,153,.3);color:#34d399;padding:.65rem 1rem;border-radius:.75rem;font-size:.85rem;font-weight:500;margin-bottom:1.25rem">
            <i class="bi bi-check-circle-fill"></i> {{ session('success') }}
        </div>
        @endif

        <div class="panel">
            <div class="panel-header">
                <span class="panel-title"><i class="bi bi-ticket-perforated me-2" style="color:var(--accent)"></i>{{ $pageTitle }}</span>
                <div style="display:flex;flex-direction:column;gap:.45rem;align-items:flex-end">
                    <div class="d-flex gap-2 flex-wrap" id="statusFilters">
                        <button class="filter-btn active" data-status="all"      onclick="filterStatus('all',this)">All</button>
                        <button class="filter-btn"        data-status="open"     onclick="filterStatus('open',this)">Open</button>
                        <button class="filter-btn"        data-status="progress" onclick="filterStatus('progress',this)">In Progress</button>
                        <button class="filter-btn"        data-status="resolved" onclick="filterStatus('resolved',this)">Resolved</button>
                        <button class="filter-btn"        data-status="closed"   onclick="filterStatus('closed',this)">Closed</button>
                    </div>
                    <div class="d-flex gap-2 flex-wrap" id="priorityFilters">
                        <span style="font-size:.68rem;color:var(--muted);align-self:center;margin-right:.15rem">Priority:</span>
                        <button class="filter-btn active" data-priority="all"    onclick="filterPriority('all',this)">All</button>
                        <button class="filter-btn"        data-priority="high"   onclick="filterPriority('high',this)" style="color:#f87171">High</button>
                        <button class="filter-btn"        data-priority="medium" onclick="filterPriority('medium',this)" style="color:#fbbf24">Medium</button>
                        <button class="filter-btn"        data-priority="low"    onclick="filterPriority('low',this)" style="color:#34d399">Low</button>
                    </div>
                </div>
            </div>

            @php
                $statusMap   = ['open'=>['s-open','Open'],'progress'=>['s-progress','In Progress'],'resolved'=>['s-resolved','Resolved'],'closed'=>['s-closed','Closed']];
                $priorityMap = ['high'=>'p-high','medium'=>'p-medium','low'=>'p-low'];
            @endphp

            @if($tickets->isEmpty())
                <div style="padding:4rem;text-align:center;color:var(--muted)">
                    <i class="bi bi-ticket-perforated" style="font-size:2.5rem;opacity:.25;display:block;margin-bottom:1rem"></i>
                    <div style="font-size:.95rem;font-weight:600">No tickets found</div>
                    <div style="font-size:.82rem;margin-top:.35rem">Submit a new ticket from the dashboard.</div>
                </div>
            @else
            <div id="tableWrap" style="overflow-x:auto;border-radius:0 0 1rem 1rem">
                <table class="flux-table" id="ticketTable">
                    <thead>
                        <tr>
                            <th>Ticket ID</th>
                            <th>Subject</th>
                            <th>Requester</th>
                            <th>Priority</th>
                            <th>Status</th>
                            <th>Type</th>
                            <th>Assigned To</th>
                            <th>Created</th>
                            <th>Last Updated</th>
                            <th style="text-align:center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($tickets as $t)
                        <tr data-status="{{ $t->status }}" data-priority="{{ $t->priority }}" data-ticket-id="{{ $t->id }}">
                            <td><span style="font-size:.72rem;font-weight:700;color:#818cf8;font-family:monospace">{{ $t->ticket_number }}</span></td>
                            <td style="max-width:220px">
                                <div style="font-weight:500;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">{{ $t->subject }}</div>
                                @if($t->description)
                                <div style="font-size:.72rem;color:var(--muted);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:200px">{{ $t->description }}</div>
                                @endif
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div style="width:25px;height:25px;background:linear-gradient(135deg,#4f46e5,#7c3aed);border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:.6rem;font-weight:700;color:white;flex-shrink:0">
                                        {{ strtoupper(substr($t->user->name ?? '?', 0, 1)) }}
                                    </div>
                                    <span style="white-space:nowrap">{{ $t->user->name ?? '—' }}</span>
                                </div>
                            </td>
                            <td><span class="badge-priority {{ $priorityMap[$t->priority] ?? 'p-low' }}">{{ ucfirst($t->priority) }}</span></td>
                            <td><span class="badge-status {{ $statusMap[$t->status][0] ?? 's-open' }}">{{ $statusMap[$t->status][1] ?? 'Open' }}</span></td>
                            <td style="color:var(--muted);font-size:.82rem;white-space:nowrap">{{ $t->type ?: '—' }}</td>
                            <td style="color:var(--muted);white-space:nowrap;font-size:.82rem">{{ $t->assignee ?: 'Unassigned' }}</td>
                            <td style="color:var(--muted);font-size:.78rem;white-space:nowrap">{{ $t->created_at->diffForHumans() }}</td>
                            <td style="color:var(--muted);font-size:.78rem;white-space:nowrap">{{ $t->updated_at->diffForHumans() }}</td>
                            <td style="text-align:center;white-space:nowrap">
                                @php
                                    $authUser    = auth()->user();
                                    $isSubmitter    = $t->user_id === $authUser->id;
                                    $isAssignee     = $t->assignee === $authUser->name;
                                    $isRoutedToDept = $t->department && $t->department === $authUser->department;
                                    $alreadyAssigned = !empty($t->assignee);
                                    $showAccept  = !$isSubmitter && !$isAssignee && $isRoutedToDept && !$alreadyAssigned;
                                @endphp
                                <div class="d-flex gap-2 justify-content-center">
                                    <button class="btn-view" onclick='openView(@json($t->toArray()), @json($t->user->name ?? "Unknown"))'>
                                        <i class="bi bi-eye me-1"></i>View
                                    </button>
                                    @if($showAccept)
                                    <button class="btn-assign" onclick="openAssign({{ $t->id }}, '{{ addslashes($t->ticket_number) }}')">
                                        <i class="bi bi-person-check me-1"></i>Accept
                                    </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        </div>
    </main>
</div>

{{-- ════════════════════════════════════
     VIEW / EDIT TICKET MODAL
════════════════════════════════════ --}}
<div class="flux-modal-backdrop" id="viewModal" onclick="handleBdClick(event,'viewModal')">
    <div class="flux-modal">
        <div class="flux-modal-header">
            <div>
                <div style="display:flex;align-items:center;gap:.6rem;margin-bottom:.2rem">
                    <div style="width:28px;height:28px;background:linear-gradient(135deg,#4f46e5,#7c3aed);border-radius:.45rem;display:flex;align-items:center;justify-content:center;flex-shrink:0">
                        <i class="bi bi-ticket-perforated-fill text-white" style="font-size:.75rem"></i>
                    </div>
                    <span id="vm-number" style="font-size:.85rem;font-weight:800;color:#818cf8;font-family:monospace"></span>
                    <span id="vm-status-badge" class="badge-status"></span>
                </div>
                <div id="vm-subject" style="font-size:1rem;font-weight:700;color:var(--text)"></div>
            </div>
            <button onclick="closeModal('viewModal')" style="background:var(--surface2);border:1px solid var(--border);border-radius:.5rem;width:30px;height:30px;display:flex;align-items:center;justify-content:center;cursor:pointer;color:var(--muted);font-size:.85rem;flex-shrink:0">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <div class="flux-modal-body">

            {{-- Info grid --}}
            <div class="info-grid">
                <div class="info-item"><div class="info-label">Requester</div><div class="info-val" id="vm-requester"></div></div>
                <div class="info-item"><div class="info-label">Category</div><div class="info-val" id="vm-category"></div></div>
                <div class="info-item"><div class="info-label">Type</div><div class="info-val" id="vm-type"></div></div>
                <div class="info-item"><div class="info-label">Created</div><div class="info-val" id="vm-created"></div></div>
                <div class="info-item"><div class="info-label">Assigned To</div><div class="info-val" id="vm-assignee"></div></div>
                <div class="info-item"><div class="info-label">Resolved By</div><div class="info-val" id="vm-resolved-by"></div></div>
                <div class="info-item"><div class="info-label">Last Updated</div><div class="info-val" id="vm-updated-at"></div></div>
                <div class="info-item">
                    <div class="info-label">SLA Deadline</div>
                    <div class="info-val" id="vm-sla-due"></div>
                </div>
                <div class="info-item">
                    <div class="info-label">SLA Status</div>
                    <div id="vm-sla-badge"></div>
                </div>
            </div>

            {{-- SLA progress bar --}}
            <div id="vm-sla-bar-wrap" style="margin-bottom:1rem;display:none">
                <div style="display:flex;justify-content:space-between;font-size:.7rem;color:var(--muted);margin-bottom:.3rem">
                    <span>SLA Progress</span>
                    <span id="vm-sla-pct-label"></span>
                </div>
                <div style="height:6px;background:var(--surface2);border-radius:9999px;overflow:hidden">
                    <div id="vm-sla-bar" style="height:100%;border-radius:9999px;transition:width .4s ease"></div>
                </div>
                <div style="font-size:.7rem;color:var(--muted);margin-top:.25rem" id="vm-sla-time-label"></div>
            </div>

            {{-- Description --}}
            <div class="modal-section">Description</div>
            <div class="desc-box" id="vm-description"></div>

            {{-- Resolution (read) --}}
            <div id="vm-resolution-section" style="display:none">
                <div class="modal-section">Resolution</div>
                <div class="resolution-box" id="vm-resolution"></div>
            </div>

            {{-- Edit form — only visible when assigned to current user --}}
            <div id="vm-edit-section" style="display:none">
                <div class="modal-section">Update Ticket</div>
                <form id="editTicketForm" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <div class="m-row m-field">
                        <div>
                            <label class="m-label">Status</label>
                            <select class="m-select" name="status" id="vm-status-sel">
                                <option value="open">Open</option>
                                <option value="progress">In Progress</option>
                                <option value="resolved">Resolved</option>
                                <option value="closed">Closed</option>
                            </select>
                        </div>
                        <div>
                            <label class="m-label">Priority</label>
                            <select class="m-select" name="priority" id="vm-priority-sel">
                                <option value="low">Low</option>
                                <option value="medium">Medium</option>
                                <option value="high">High</option>
                            </select>
                        </div>
                    </div>

                    <div class="m-field">
                        <label class="m-label">Assigned To <span style="color:var(--muted);font-weight:400;text-transform:none;font-size:.7rem">(read only)</span></label>
                        <input class="m-input" name="assignee" id="vm-assignee-inp" type="text" placeholder="Unassigned" readonly
                               style="opacity:.65;cursor:not-allowed;pointer-events:none">
                    </div>

                    <div class="m-field">
                        <label class="m-label">Resolution Notes <span style="color:var(--muted);font-weight:400;text-transform:none;font-size:.7rem">(required when resolving/closing)</span></label>
                        <textarea class="m-textarea" name="resolution" id="vm-resolution-inp" placeholder="Describe how the issue was resolved…"></textarea>
                    </div>

                    {{-- Resolution image upload --}}
                    <div class="m-field" style="margin-bottom:0">
                        <label class="m-label">Resolution Image <span style="color:var(--muted);font-weight:400;text-transform:none;font-size:.7rem">(optional — screenshot, photo, etc.)</span></label>
                        <div style="border:1.5px dashed var(--border);border-radius:.65rem;padding:.85rem;text-align:center;cursor:pointer;transition:border-color .2s,background .2s"
                             onclick="document.getElementById('res-img-input').click()"
                             onmouseover="this.style.borderColor='var(--accent)'"
                             onmouseout="this.style.borderColor='var(--border)'">
                            <i class="bi bi-image" style="font-size:1.2rem;color:var(--muted)"></i>
                            <div style="font-size:.78rem;color:var(--muted);margin-top:.25rem">Click to upload an image</div>
                            <div id="res-img-display" style="font-size:.75rem;color:#818cf8;margin-top:.25rem;display:none"></div>
                        </div>
                        <input type="file" id="res-img-input" name="resolution_image" accept="image/*" style="display:none"
                               onchange="showResImg(this)">
                        {{-- Existing resolution image download link --}}
                        <div id="res-img-preview-wrap" style="display:none;margin-top:.5rem">
                            <a id="res-img-preview" href="#" download target="_blank"
                               style="display:inline-flex;align-items:center;gap:.4rem;font-size:.8rem;color:#818cf8;text-decoration:none;background:rgba(99,102,241,.08);border:1px solid rgba(99,102,241,.2);border-radius:.5rem;padding:.35rem .75rem">
                                <i class="bi bi-paperclip"></i>
                                <span id="res-img-filename">resolution-image</span>
                            </a>
                            <div style="font-size:.7rem;color:var(--muted);margin-top:.25rem">
                                Uploaded <span id="res-img-uploaded-at"></span> &middot; upload a new one to replace
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            {{-- Read-only notice — shown when not the assignee --}}
            <div id="vm-readonly-notice" style="display:none;margin-top:.5rem">
                <div style="display:flex;align-items:center;gap:.65rem;background:rgba(99,102,241,.08);border:1px solid rgba(99,102,241,.2);border-radius:.75rem;padding:.75rem 1rem;font-size:.82rem;color:#818cf8">
                    <i class="bi bi-lock-fill" style="flex-shrink:0"></i>
                    <span>You are viewing this ticket in <b>read-only</b> mode. Click <b>Accept</b> to take ownership and enable editing.</span>
                </div>
            </div>

            {{-- ── Linked KBAs ── --}}
            <div class="modal-section" style="margin-top:1.25rem">Linked Knowledge Articles</div>
            <div id="vm-kba-list" style="margin-bottom:.6rem"></div>
            <div style="position:relative;margin-bottom:1rem">
                <div style="display:flex;gap:.5rem;align-items:center">
                    <div style="position:relative;flex:1">
                        <i class="bi bi-search" style="position:absolute;left:.65rem;top:50%;transform:translateY(-50%);color:var(--muted);font-size:.8rem;pointer-events:none"></i>
                        <input type="text" id="vm-kba-search" placeholder="Search KBAs to attach…"
                            style="width:100%;background:var(--surface2);border:1px solid var(--border);border-radius:.6rem;color:var(--text);font-size:.8rem;padding:.4rem .75rem .4rem 2rem;outline:none"
                            oninput="filterKbaDropdown(this.value)" onfocus="showKbaDropdown()">
                    </div>
                </div>
                <div id="vm-kba-dropdown" style="display:none;position:absolute;top:calc(100% + 4px);left:0;right:0;background:var(--surface);border:1px solid var(--border);border-radius:.65rem;box-shadow:0 8px 24px rgba(0,0,0,.3);max-height:200px;overflow-y:auto;z-index:9999"></div>
            </div>

            {{-- ── Activity & Notes timeline ── --}}
            <div class="modal-section" style="margin-top:1.25rem">Activity &amp; Notes</div>
            <div id="vm-notes-timeline" style="margin-bottom:.85rem"></div>

            {{-- Add Note form (visible to submitter + assignee) --}}
            <div id="vm-add-note-section" style="display:none">
                <div id="vm-note-error" style="display:none;background:rgba(248,113,113,.1);border:1px solid rgba(248,113,113,.3);color:#f87171;padding:.45rem .75rem;border-radius:.6rem;font-size:.78rem;margin-bottom:.5rem"></div>
                <textarea class="m-textarea" id="vm-note-input" placeholder="Add a note or comment to this ticket…" style="min-height:72px;margin-bottom:.5rem;width:100%"></textarea>
                {{-- File attachment --}}
                <div style="margin-bottom:.5rem">
                    <label style="display:flex;align-items:center;gap:.5rem;background:var(--surface2);border:1px dashed var(--border);border-radius:.6rem;padding:.4rem .75rem;cursor:pointer;font-size:.78rem;color:var(--muted);transition:border-color .15s" onmouseover="this.style.borderColor='var(--accent)'" onmouseout="this.style.borderColor='var(--border)'">
                        <i class="bi bi-paperclip"></i>
                        <span id="vm-note-file-label">Attach a file (max 5 MB)</span>
                        <input type="file" id="vm-note-file" style="display:none" onchange="updateNoteFileLabel(this)">
                    </label>
                </div>
                <button type="button" id="vm-note-submit" onclick="submitNote()"
                    style="background:linear-gradient(135deg,#4f46e5,#7c3aed);border:none;border-radius:.6rem;color:white;font-size:.8rem;font-weight:600;padding:.4rem 1rem;cursor:pointer;display:flex;align-items:center;gap:.4rem;box-shadow:0 2px 8px rgba(99,102,241,.3)">
                    <i class="bi bi-chat-left-text-fill"></i> Add Note
                </button>
            </div>
        </div>

        <div class="flux-modal-footer" style="flex-wrap:wrap;gap:.5rem">
            <button class="btn-cancel" onclick="closeModal('viewModal')">Close</button>
            {{-- Print button (always visible) --}}
            <a id="vm-print-btn" href="#" target="_blank"
               style="background:var(--surface2);border:1px solid var(--border);border-radius:.6rem;color:var(--muted);font-size:.875rem;font-weight:600;padding:.5rem 1rem;display:flex;align-items:center;gap:.4rem;text-decoration:none !important;transition:background .15s,color .15s"
               onmouseover="this.style.color='var(--text)'" onmouseout="this.style.color='var(--muted)'">
                <i class="bi bi-printer"></i> Print / PDF
            </a>
            {{-- Route button (always visible) --}}
            <button id="vm-route-btn" onclick="openRouteFromView()"
                style="background:rgba(251,191,36,.1);border:1px solid rgba(251,191,36,.25);border-radius:.6rem;color:#fbbf24;font-size:.875rem;font-weight:600;padding:.5rem 1rem;display:flex;align-items:center;gap:.4rem;cursor:pointer;transition:background .15s">
                <i class="bi bi-arrow-left-right"></i> Route
            </button>
            <button id="vm-save-btn" class="btn-submit" style="display:none" onclick="document.getElementById('editTicketForm').submit()">
                <i class="bi bi-floppy"></i> Save Changes
            </button>
        </div>
    </div>
</div>

{{-- ════════════════════════════════════
     ROUTE TICKET MODAL
════════════════════════════════════ --}}
<div class="flux-modal-backdrop" id="routeModal" onclick="handleBdClick(event,'routeModal')">
    <div class="flux-modal" style="max-width:480px">
        <div class="flux-modal-header">
            <div>
                <div style="font-size:.95rem;font-weight:700;color:var(--text)">Route Ticket</div>
                <div id="route-sub" style="font-size:.75rem;color:var(--muted);margin-top:.1rem">Forward to another department or technician</div>
            </div>
            <button onclick="closeModal('routeModal')" style="background:var(--surface2);border:1px solid var(--border);border-radius:.5rem;width:30px;height:30px;display:flex;align-items:center;justify-content:center;cursor:pointer;color:var(--muted);font-size:.85rem;">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
        <div class="flux-modal-body">
            <form id="routeForm" method="POST">
                @csrf
                <div class="m-field">
                    <label class="m-label">Department <span style="color:#f87171">*</span></label>
                    <select class="m-select" id="route-dept" name="department" required onchange="loadRouteDeptUsers(this.value)">
                        <option value="" disabled selected>Select department…</option>
                        @foreach($allowedDepts ?? ['IT','HR','Finance','OPIC','Dispatch','Asset/Admin','Marketing','RSO','Store'] as $d)
                        <option value="{{ $d }}">{{ $d }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="m-field">
                    <label class="m-label">Route To <span style="color:#f87171">*</span></label>
                    <select class="m-select" id="route-person" name="routed_to" required disabled>
                        <option value="" disabled selected>Select department first…</option>
                    </select>
                </div>
                <div class="m-field" style="margin-bottom:0">
                    <label class="m-label">Routing Note</label>
                    <textarea class="m-textarea" name="routing_note" placeholder="Reason for routing or additional context…" style="min-height:80px"></textarea>
                </div>
            </form>
        </div>
        <div class="flux-modal-footer">
            <button class="btn-cancel" onclick="closeModal('routeModal')">Cancel</button>
            <button type="button" id="routeSubmitBtn" onclick="submitRouteTicket()"
                style="background:rgba(251,191,36,.15);border:1px solid rgba(251,191,36,.3);border-radius:.6rem;color:#d97706;font-size:.875rem;font-weight:600;padding:.5rem 1.25rem;cursor:pointer;display:flex;align-items:center;gap:.4rem">
                <i class="bi bi-arrow-left-right"></i> Route Ticket
            </button>
        </div>
    </div>
</div>

{{-- ════════════════════════════════════
     ASSIGN-TO-ME MODAL
════════════════════════════════════ --}}
<div class="flux-modal-backdrop" id="assignModal" onclick="handleBdClick(event,'assignModal')">
    <div class="flux-modal assign-modal">
        <div class="flux-modal-header">
            <div>
                <div style="font-size:.95rem;font-weight:700;color:var(--text)">Accept Ticket</div>
                <div id="assign-sub" style="font-size:.75rem;color:var(--muted);margin-top:.15rem"></div>
            </div>
            <button onclick="closeModal('assignModal')" style="background:var(--surface2);border:1px solid var(--border);border-radius:.5rem;width:30px;height:30px;display:flex;align-items:center;justify-content:center;cursor:pointer;color:var(--muted);font-size:.85rem;">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
        <div class="flux-modal-body">
            <div style="text-align:center;padding:1rem 0 1.5rem">
                <div style="width:56px;height:56px;background:rgba(52,211,153,.15);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 1rem">
                    <i class="bi bi-person-check-fill" style="font-size:1.5rem;color:#34d399"></i>
                </div>
                <p style="font-size:.9rem;color:var(--text);font-weight:500;margin-bottom:.35rem">
                    Assign this ticket to yourself?
                </p>
                <p style="font-size:.8rem;color:var(--muted);margin:0">
                    The ticket status will be set to <b>In Progress</b> and you'll be listed as the assignee.
                </p>
            </div>
        </div>
        <div class="flux-modal-footer">
            <button class="btn-cancel" onclick="closeModal('assignModal')">Cancel</button>
            <form id="assignForm" method="POST" style="display:inline">
                @csrf
                <button type="submit" class="btn-accept">
                    <i class="bi bi-person-check"></i> Yes, Assign to Me
                </button>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // ── Dark mode ──
    const body   = document.body;
    const toggle = document.getElementById('darkToggle');
    const saved  = localStorage.getItem('theme');
    if (saved === 'light') body.classList.remove('dark');
    else body.classList.add('dark');
    toggle.addEventListener('click', () => {
        body.classList.toggle('dark');
        localStorage.setItem('theme', body.classList.contains('dark') ? 'dark' : 'light');
    });

    // ── Sidebar minimize ──
    const sidebar   = document.querySelector('.sidebar');
    const mainWrap  = document.querySelector('.main-wrap');
    const sideToggle= document.getElementById('sidebarToggle');
    if (localStorage.getItem('sidebar') === 'mini') {
        sidebar.classList.add('mini');
        mainWrap.classList.add('mini');
    }
    sideToggle.addEventListener('click', () => {
        sidebar.classList.toggle('mini');
        mainWrap.classList.toggle('mini');
        localStorage.setItem('sidebar', sidebar.classList.contains('mini') ? 'mini' : 'full');
    });

    // ── Mobile sidebar ──
    const _overlay = document.getElementById('sidebarOverlay');
    function closeMobileSidebar() {
        sidebar.classList.remove('mobile-open');
        if (_overlay) _overlay.classList.remove('open');
        document.body.style.overflow = '';
    }
    const _mobBtn = document.getElementById('mobHamburger');
    if (_mobBtn) {
        _mobBtn.addEventListener('click', () => {
            sidebar.classList.toggle('mobile-open');
            if (_overlay) _overlay.classList.toggle('open');
            document.body.style.overflow = sidebar.classList.contains('mobile-open') ? 'hidden' : '';
        });
    }
    document.querySelectorAll('.nav-item-link').forEach(a => {
        a.addEventListener('click', () => { if (window.innerWidth <= 768) closeMobileSidebar(); });
    });

    // Tickets dropdown
    (function() {
        const trigger = document.getElementById('ticketsDropdownTrigger');
        const submenu = document.getElementById('ticketsSubmenu');
        const chevron = trigger ? trigger.querySelector('.nav-chevron') : null;
        // Always open on tickets pages; also persist state
        if (chevron) chevron.style.transform = 'rotate(180deg)';
        localStorage.setItem('ticketsDropdown', 'open');
        if (trigger) trigger.addEventListener('click', function() {
            submenu.classList.toggle('open');
            const isOpen = submenu.classList.contains('open');
            if (chevron) chevron.style.transform = isOpen ? 'rotate(180deg)' : '';
            localStorage.setItem('ticketsDropdown', isOpen ? 'open' : 'closed');
        });
    })();

    // ── Flash auto-dismiss ──
    const flash = document.getElementById('flash-ok');
    if (flash) setTimeout(() => { flash.style.transition='opacity .5s'; flash.style.opacity='0'; setTimeout(()=>flash.remove(),500); }, 4000);

    // Re-open new ticket modal on validation error
    @if($errors->any())
        document.addEventListener('DOMContentLoaded', () => openModal('newTicketModal'));
    @endif

    // ── Filter (saveable) ──
    const _FKEY = 'flux_filter_tickets';
    let currentStatus   = 'all';
    let currentPriority = 'all';

    function _saveFilter() {
        try { localStorage.setItem(_FKEY, JSON.stringify({ status: currentStatus, priority: currentPriority })); } catch(e){}
    }
    function filterStatus(s, btn) {
        currentStatus = s;
        document.querySelectorAll('#statusFilters .filter-btn').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        _saveFilter(); applyFilters();
    }
    function filterPriority(p, btn) {
        currentPriority = p;
        document.querySelectorAll('#priorityFilters .filter-btn').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        _saveFilter(); applyFilters();
    }
    function applyFilters() {
        const q = (document.getElementById('searchInput')?.value || '').toLowerCase();
        document.querySelectorAll('#ticketTable tbody tr').forEach(row => {
            const matchStatus   = currentStatus === 'all'   || row.dataset.status   === currentStatus;
            const matchPriority = currentPriority === 'all' || row.dataset.priority === currentPriority;
            const matchSearch   = !q || row.textContent.toLowerCase().includes(q);
            row.style.display = (matchStatus && matchPriority && matchSearch) ? '' : 'none';
        });
        updateTableScroll();
    }
    function updateTableScroll() {
        const wrap = document.getElementById('tableWrap');
        if (!wrap) return;
        const visible = [...wrap.querySelectorAll('#ticketTable tbody tr')].filter(r => r.style.display !== 'none').length;
        if (visible > 9) {
            wrap.style.maxHeight = '540px';
            wrap.style.overflowY = 'auto';
        } else {
            wrap.style.maxHeight = '';
            wrap.style.overflowY = 'hidden';
        }
    }
    updateTableScroll();
    // Restore saved filter
    (function() {
        try {
            const saved = JSON.parse(localStorage.getItem(_FKEY));
            if (!saved) return;
            if (saved.status && saved.status !== 'all') {
                const b = document.querySelector('#statusFilters [data-status="'+saved.status+'"]');
                if (b) filterStatus(saved.status, b);
            }
            if (saved.priority && saved.priority !== 'all') {
                const b = document.querySelector('#priorityFilters [data-priority="'+saved.priority+'"]');
                if (b) filterPriority(saved.priority, b);
            }
        } catch(e) {}
    })();

    // Auto-open ticket from notification link (?open=ticketId)
    (function() {
        const openId = new URLSearchParams(window.location.search).get('open');
        if (!openId) return;
        // Clean URL without reloading
        history.replaceState(null, '', window.location.pathname);
        // Find the View button for this ticket and click it
        const row = document.querySelector('#ticketTable tbody tr[data-ticket-id="'+openId+'"]');
        if (row) {
            // Make sure row is visible (clear status/priority filters temporarily)
            row.style.display = '';
            const viewBtn = row.querySelector('.btn-view');
            if (viewBtn) setTimeout(() => viewBtn.click(), 200);
        }
    })();

    // ── Modal helpers ──
    function openModal(id)  { document.getElementById(id).classList.add('open');    body.style.overflow = 'hidden'; }
    function closeModal(id) { document.getElementById(id).classList.remove('open'); body.style.overflow = ''; }

    function submitRouteTicket() {
        const btn = document.getElementById('routeSubmitBtn');
        btn.disabled = true;
        btn.innerHTML = '<span style="display:inline-block;width:13px;height:13px;border:2px solid rgba(217,119,6,.35);border-top-color:#d97706;border-radius:50%;animation:spin .7s linear infinite;vertical-align:middle;margin-right:.35rem"></span> Routing…';
        document.getElementById('routeForm').submit();
    }
    function handleBdClick(e, id) { if (e.target === document.getElementById(id)) closeModal(id); }
    document.addEventListener('keydown', e => { if (e.key === 'Escape') { closeModal('viewModal'); closeModal('assignModal'); closeModal('routeModal'); closeModal('newTicketModal'); } });

    // ── Status badge classes ──
    const sMap = { open:'s-open', progress:'s-progress', resolved:'s-resolved', closed:'s-closed' };
    const sLabel = { open:'Open', progress:'In Progress', resolved:'Resolved', closed:'Closed' };

    // Current logged-in user (from Blade)
    const currentUser   = @json(auth()->user()->name);
    const currentUserId = {{ auth()->id() }};

    // Auto-open a specific ticket passed via ?open= (e.g. from KBA linked tickets)
    @if(!empty($openTicket))
    document.addEventListener('DOMContentLoaded', function() {
        const _ot = @json($openTicket->load(['user','notes.user','knowledgeArticles'])->toArray());
        openView(_ot, _ot.user?.name ?? 'Unknown');
    });
    @endif

    // ── View / Edit modal ──
    function openView(t, requesterName) {
        document.getElementById('vm-number').textContent           = t.ticket_number;
        document.getElementById('vm-subject').textContent          = t.subject;
        document.getElementById('vm-requester').textContent        = requesterName;
        document.getElementById('vm-category').textContent         = t.category;
        document.getElementById('vm-type').textContent             = t.type || '—';
        document.getElementById('vm-assignee').textContent         = t.assignee || 'Unassigned';
        document.getElementById('vm-resolved-by').textContent      = t.resolved_by || '—';
        document.getElementById('vm-created').textContent          = new Date(t.created_at).toLocaleString();
        document.getElementById('vm-updated-at').textContent       = t.updated_at ? new Date(t.updated_at).toLocaleString() : '—';
        document.getElementById('vm-description').textContent      = t.description;

        // Status badge
        const badge = document.getElementById('vm-status-badge');
        badge.className   = 'badge-status ' + (sMap[t.status] || 's-open');
        badge.textContent = sLabel[t.status] || t.status;

        // Resolution (read-only display)
        const resSection = document.getElementById('vm-resolution-section');
        if (t.resolution) {
            resSection.style.display = '';
            document.getElementById('vm-resolution').textContent = t.resolution;
        } else {
            resSection.style.display = 'none';
        }

        // Determine if current user is the assignee
        const isAssignee = t.assignee && t.assignee === currentUser;

        // Show edit form only for the assignee
        document.getElementById('vm-edit-section').style.display    = isAssignee ? '' : 'none';
        document.getElementById('vm-readonly-notice').style.display  = isAssignee ? 'none' : '';
        document.getElementById('vm-save-btn').style.display         = isAssignee ? '' : 'none';

        if (isAssignee) {
            document.getElementById('vm-status-sel').value     = t.status;
            document.getElementById('vm-priority-sel').value   = t.priority;
            document.getElementById('vm-assignee-inp').value   = t.assignee || '';
            document.getElementById('vm-resolution-inp').value = t.resolution || '';
            document.getElementById('editTicketForm').action   = '/tickets/' + t.id;

            // Show existing resolution image as download link
            const previewWrap = document.getElementById('res-img-preview-wrap');
            if (t.resolution_image) {
                const url = '/storage/' + t.resolution_image;
                const filename = t.resolution_image.split('/').pop();
                const link = document.getElementById('res-img-preview');
                link.href = url;
                link.download = filename;
                document.getElementById('res-img-filename').textContent = filename;
                const uploadedAt = t.updated_at
                    ? new Date(t.updated_at).toLocaleString('en-US',{month:'short',day:'numeric',year:'numeric',hour:'numeric',minute:'2-digit'})
                    : '';
                document.getElementById('res-img-uploaded-at').textContent = uploadedAt;
                previewWrap.style.display = '';
            } else {
                previewWrap.style.display = 'none';
            }
        }

        // ── SLA ──
        const slaDue = document.getElementById('vm-sla-due');
        const slaBadge = document.getElementById('vm-sla-badge');
        const slaBarWrap = document.getElementById('vm-sla-bar-wrap');
        const slaBar = document.getElementById('vm-sla-bar');
        const slaPctLabel = document.getElementById('vm-sla-pct-label');
        const slaTimeLabel = document.getElementById('vm-sla-time-label');

        const slaColors = { ok:'#34d399', warning:'#fbbf24', breached:'#f87171', met:'#818cf8' };
        const slaLabels = { ok:'On Track', warning:'At Risk', breached:'Breached', met:'Met' };
        const slaDays   = { high:2, medium:3, low:7 };

        if (t.sla_due_at) {
            const due      = new Date(t.sla_due_at);
            const created  = new Date(t.created_at);
            const now      = new Date();
            const isDone   = ['resolved','closed'].includes(t.status);
            const compareAt= isDone && t.resolved_at ? new Date(t.resolved_at) : now;

            // Determine status
            let ss;
            if (compareAt > due)          ss = 'breached';
            else if (isDone)              ss = 'met';
            else {
                const total = due - created;
                const rem   = due - now;
                ss = (rem / total) <= 0.25 ? 'warning' : 'ok';
            }

            slaDue.textContent = due.toLocaleDateString('en-US', { month:'short', day:'numeric', year:'numeric' })
                + ' — ' + due.toLocaleTimeString('en-US', { hour:'numeric', minute:'2-digit' });

            slaBadge.innerHTML = `<span class="sla-badge sla-${ss}"><span class="sla-dot"></span>${slaLabels[ss]}</span>`;

            // Progress bar (for open tickets only)
            if (!isDone) {
                slaBarWrap.style.display = '';
                const totalSec = (due - created) / 1000;
                const usedSec  = (now - created) / 1000;
                const pct      = Math.min(100, Math.round((usedSec / totalSec) * 100));
                const remMs    = due - now;
                const remHrs   = Math.round(remMs / 3600000);
                const remDays  = Math.floor(remHrs / 24);
                const remLabel = remMs < 0
                    ? `Overdue by ${Math.abs(remDays)}d ${Math.abs(remHrs % 24)}h`
                    : remDays > 0 ? `${remDays}d ${remHrs % 24}h remaining` : `${remHrs}h remaining`;

                const barColor = pct >= 100 ? '#f87171' : pct >= 50 ? '#fbbf24' : '#34d399';
                slaBar.style.width = pct + '%';
                slaBar.style.background = barColor;
                slaPctLabel.textContent = pct + '% elapsed';
                slaTimeLabel.textContent = remLabel;
            } else {
                slaBarWrap.style.display = 'none';
            }
        } else {
            slaDue.textContent  = '—';
            slaBadge.innerHTML  = '<span style="color:var(--muted);font-size:.8rem">—</span>';
            slaBarWrap.style.display = 'none';
        }

        // Store current ticket id/status for note submission, KBA and route modal
        _currentTicketId     = t.id;
        _currentTicketStatus = t.status;
        document.getElementById('vm-print-btn').href = '/tickets/' + t.id + '/print';
        document.getElementById('vm-route-btn').dataset.ticketId = t.id;
        document.getElementById('vm-route-btn').dataset.ticketNum = t.ticket_number;

        // ── Linked KBAs ──
        if (_kbaCache[t.id] === undefined) {
            _kbaCache[t.id] = [...(t.knowledge_articles || [])];
        }
        renderKbaList(_kbaCache[t.id]);
        document.getElementById('vm-kba-search').value = '';
        hideKbaDropdown();

        // ── Notes / Activity Timeline ──
        // Use cached notes (keeps AJAX-added notes visible on reopen)
        if (_notesCache[t.id] === undefined) {
            _notesCache[t.id] = [...(t.notes || [])];
        }
        renderTimeline(_notesCache[t.id]);

        // ── Note form — visible to submitter or assignee ──
        const isSubmitter = t.user_id === currentUserId;
        document.getElementById('vm-add-note-section').style.display = (isSubmitter || isAssignee) ? '' : 'none';
        document.getElementById('vm-note-input').value = '';
        document.getElementById('vm-note-file').value = '';
        document.getElementById('vm-note-file-label').textContent = 'Attach a file (max 5 MB)';
        document.getElementById('vm-note-error').style.display = 'none';

        // Modal skeleton loader
        const vmBody = document.querySelector('#viewModal .flux-modal-body');
        if (vmBody) {
            vmBody.style.opacity = '0';
            vmBody.style.transition = '';
        }
        openModal('viewModal');
        requestAnimationFrame(() => requestAnimationFrame(() => {
            if (vmBody) {
                vmBody.style.transition = 'opacity .3s ease';
                vmBody.style.opacity = '1';
            }
        }));
    }

    // ── Note rendering helpers ──
    let _currentTicketId     = null;
    let _currentTicketStatus = null;
    const _notesCache    = {};   // ticketId → notes[]  (updated live on AJAX add)
    const _kbaCache      = {};   // ticketId → kba[]    (updated live on attach/detach)
    const _csrfToken = document.querySelector('meta[name="csrf-token"]')?.content
        || '{{ csrf_token() }}';

    // All published KBAs (passed from server)
    const _allKbas = @json($allKbas ?? []);

    function renderKbaList(kbas) {
        const el = document.getElementById('vm-kba-list');
        if (!kbas || kbas.length === 0) {
            el.innerHTML = '<div style="font-size:.78rem;color:var(--muted);padding:.35rem 0">No KBAs linked yet.</div>';
            return;
        }
        const locked = ['resolved', 'closed'].includes(_currentTicketStatus);
        el.innerHTML = kbas.map(k => {
            const removeBtn = locked
                ? `<span title="Cannot remove from a resolved/closed ticket" style="color:var(--border);font-size:.75rem;line-height:1;display:flex;align-items:center;cursor:not-allowed"><i class="bi bi-x-lg"></i></span>`
                : `<button onclick="detachKba(${k.id})" title="Remove KBA" style="background:none;border:none;color:var(--muted);cursor:pointer;padding:0;font-size:.75rem;line-height:1;display:flex;align-items:center" onmouseover="this.style.color='#f87171'" onmouseout="this.style.color='var(--muted)'"><i class="bi bi-x-lg"></i></button>`;
            return `
            <div style="display:inline-flex;align-items:center;gap:.4rem;background:rgba(99,102,241,.1);border:1px solid rgba(99,102,241,.2);border-radius:.5rem;padding:.25rem .6rem;margin:0 .35rem .35rem 0;font-size:.78rem;color:#818cf8">
                <i class="bi bi-journal-text"></i>
                <span style="font-weight:600">${escHtml(k.kba_number || '#KBA-' + String(k.id).padStart(4,'0'))}</span>
                <span style="color:var(--muted)">·</span>
                <span>${escHtml(k.title)}</span>
                ${removeBtn}
            </div>`;
        }).join('');
    }

    function escHtml(s) {
        return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }

    function filterKbaDropdown(q) {
        const linked = (_kbaCache[_currentTicketId] || []).map(k => k.id);
        const term = q.trim().toLowerCase();
        const matches = _allKbas.filter(k =>
            !linked.includes(k.id) &&
            (!term || k.title.toLowerCase().includes(term) || k.kba_number.toLowerCase().includes(term) || (k.category || '').toLowerCase().includes(term))
        ).slice(0, 20);
        const dd = document.getElementById('vm-kba-dropdown');
        if (!matches.length) {
            dd.innerHTML = '<div style="padding:.5rem .85rem;font-size:.78rem;color:var(--muted)">No matching KBAs found.</div>';
        } else {
            dd.innerHTML = matches.map(k => `
                <div onclick="confirmAttachKba(${k.id})"
                     style="padding:.45rem .85rem;cursor:pointer;font-size:.8rem;transition:background .1s;display:flex;align-items:center;gap:.5rem"
                     onmouseover="this.style.background='var(--surface2)'" onmouseout="this.style.background=''"
                >
                    <i class="bi bi-journal-text" style="color:#818cf8;flex-shrink:0"></i>
                    <span style="color:#818cf8;font-weight:600;flex-shrink:0">${escHtml(k.kba_number || '#KBA-' + String(k.id).padStart(4,'0'))}</span>
                    <span style="color:var(--text);flex:1;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">${escHtml(k.title)}</span>
                    <span style="color:var(--muted);font-size:.72rem;flex-shrink:0">${escHtml(k.category || '')}</span>
                </div>`).join('');
        }
        dd.style.display = '';
    }

    function showKbaDropdown() {
        filterKbaDropdown(document.getElementById('vm-kba-search').value);
    }

    function hideKbaDropdown() {
        const dd = document.getElementById('vm-kba-dropdown');
        if (dd) dd.style.display = 'none';
    }

    // Close KBA dropdown when clicking outside the search area
    document.addEventListener('click', function(e) {
        const search = document.getElementById('vm-kba-search');
        const dd     = document.getElementById('vm-kba-dropdown');
        if (!dd || dd.style.display === 'none') return;
        if (search && (search.contains(e.target) || dd.contains(e.target))) return;
        dd.style.display = 'none';
    });

    let _pendingKbaId = null;

    function confirmAttachKba(articleId) {
        hideKbaDropdown();
        const kba = _allKbas.find(k => k.id === articleId);
        _pendingKbaId = articleId;
        const num   = kba ? (kba.kba_number || '#KBA-' + String(kba.id).padStart(4,'0')) : '';
        const title = kba ? kba.title : '';
        document.getElementById('kba-confirm-num').textContent   = num;
        document.getElementById('kba-confirm-title').textContent = title;
        const overlay = document.getElementById('kbaConfirmBackdrop');
        const box     = document.getElementById('kbaAttachBox');
        overlay.style.display = 'flex';
        requestAnimationFrame(() => requestAnimationFrame(() => {
            box.style.transform = 'scale(1) translateY(0)';
            box.style.opacity   = '1';
        }));
    }

    function closeKbaAttachModal() {
        const overlay = document.getElementById('kbaConfirmBackdrop');
        const box     = document.getElementById('kbaAttachBox');
        box.style.transform = 'scale(.94) translateY(8px)';
        box.style.opacity   = '0';
        setTimeout(() => {
            overlay.style.display = 'none';
            // Reset button state for next open
            const btn     = document.getElementById('kbaAttachOk');
            const icon    = document.getElementById('kbaAttachOkIcon');
            const spinner = document.getElementById('kbaAttachOkSpinner');
            const label   = document.getElementById('kbaAttachOkLabel');
            if (btn) { btn.disabled = false; icon.style.display = ''; spinner.style.display = 'none'; label.textContent = 'Yes, Attach KBA'; }
        }, 200);
        _pendingKbaId = null;
        document.getElementById('vm-kba-search').value = '';
    }

    function doAttachKba() {
        if (!_pendingKbaId) return;
        const articleId = _pendingKbaId;

        // Show loader on button
        const btn     = document.getElementById('kbaAttachOk');
        const icon    = document.getElementById('kbaAttachOkIcon');
        const spinner = document.getElementById('kbaAttachOkSpinner');
        const label   = document.getElementById('kbaAttachOkLabel');
        if (btn) {
            btn.disabled          = true;
            icon.style.display    = 'none';
            spinner.style.display = 'inline-block';
            label.textContent     = 'Attaching…';
        }

        fetch(`/tickets/${_currentTicketId}/kba/${articleId}`, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': _csrfToken, 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        }).then(r => r.json()).then(data => {
            closeKbaAttachModal();
            if (data.ok) {
                if (!_kbaCache[_currentTicketId]) _kbaCache[_currentTicketId] = [];
                _kbaCache[_currentTicketId].push(data.article);
                renderKbaList(_kbaCache[_currentTicketId]);
            }
        }).catch(() => { closeKbaAttachModal(); });
    }

    function detachKba(articleId) {
        const kba = (_kbaCache[_currentTicketId] || []).find(k => k.id === articleId);
        const label = kba ? (kba.kba_number || '#KBA-' + String(kba.id).padStart(4,'0')) + ' — ' + kba.title : 'this KBA';
        showKbaConfirm(label, function() {
            fetch(`/tickets/${_currentTicketId}/kba/${articleId}`, {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': _csrfToken, 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            }).then(r => r.json()).then(data => {
                if (data.ok) {
                    _kbaCache[_currentTicketId] = (_kbaCache[_currentTicketId] || []).filter(k => k.id !== articleId);
                    renderKbaList(_kbaCache[_currentTicketId]);
                }
            }).catch(() => {});
        });
    }

    function renderNoteHtml(n) {
        const isRoute        = n.type === 'route_event';
        const isStatusChange = n.type === 'status_change';
        const initial  = (n.user?.name || '?').charAt(0).toUpperCase();
        const dateStr  = new Date(n.created_at).toLocaleString('en-US',{month:'short',day:'numeric',year:'numeric',hour:'numeric',minute:'2-digit'});
        const rawBody  = (n.content || '')
            .replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;')
            .replace(/\*\*(.*?)\*\*/g,'<strong>$1</strong>')
            .replace(/^&gt; (.+)$/gm,'<div style="border-left:3px solid rgba(99,102,241,.3);padding-left:.6rem;color:var(--muted);margin-top:.3rem;font-size:.8rem">$1</div>');

        if (isStatusChange) {
            return `<div style="display:flex;gap:.7rem;margin-bottom:.85rem;align-items:flex-start">
                <div style="width:28px;height:28px;min-width:28px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:.65rem;font-weight:700;color:white;background:linear-gradient(135deg,#0891b2,#0e7490)">${initial}</div>
                <div style="flex:1;min-width:0">
                    <div style="display:flex;align-items:center;gap:.45rem;flex-wrap:wrap;margin-bottom:.3rem">
                        <span style="font-size:.8rem;font-weight:600;color:var(--text)">${n.user?.name || 'Unknown'}</span>
                        <span style="font-size:.63rem;font-weight:700;text-transform:uppercase;background:rgba(8,145,178,.15);color:#22d3ee;padding:.1rem .45rem;border-radius:9999px;letter-spacing:.04em"><i class="bi bi-arrow-repeat"></i> Status Update</span>
                        <span style="font-size:.7rem;color:var(--muted)">${dateStr}</span>
                    </div>
                    <div style="font-size:.83rem;color:var(--text);line-height:1.55;background:rgba(8,145,178,.07);border:1px solid rgba(8,145,178,.25);border-radius:.5rem;padding:.55rem .8rem">${rawBody}</div>
                </div>
            </div>`;
        }

        const avatarBg = isRoute ? 'linear-gradient(135deg,#2563eb,#4f46e5)' : 'linear-gradient(135deg,#4f46e5,#7c3aed)';
        const chip     = isRoute
            ? `<span style="font-size:.63rem;font-weight:700;text-transform:uppercase;background:rgba(59,130,246,.15);color:#60a5fa;padding:.1rem .45rem;border-radius:9999px;letter-spacing:.04em"><i class="bi bi-arrow-left-right"></i> Routed</span>`
            : `<span style="font-size:.63rem;font-weight:700;text-transform:uppercase;background:rgba(99,102,241,.15);color:#818cf8;padding:.1rem .45rem;border-radius:9999px;letter-spacing:.04em"><i class="bi bi-chat-left-text"></i> Note</span>`;
        const attachHtml = n.attachment
            ? `<div style="margin-top:.4rem"><a href="/storage/${n.attachment}" target="_blank" style="font-size:.75rem;color:#818cf8;display:inline-flex;align-items:center;gap:.3rem"><i class="bi bi-paperclip"></i> View attachment</a></div>`
            : '';
        return `<div style="display:flex;gap:.7rem;margin-bottom:.85rem">
            <div style="width:28px;height:28px;min-width:28px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:.65rem;font-weight:700;color:white;background:${avatarBg}">${initial}</div>
            <div style="flex:1;min-width:0">
                <div style="display:flex;align-items:center;gap:.45rem;flex-wrap:wrap;margin-bottom:.3rem">
                    <span style="font-size:.8rem;font-weight:600;color:var(--text)">${n.user?.name || 'Unknown'}</span>
                    ${chip}
                    <span style="font-size:.7rem;color:var(--muted)">${dateStr}</span>
                </div>
                <div style="font-size:.83rem;color:var(--text);line-height:1.55;background:var(--surface2);border:1px solid var(--border);border-radius:.5rem;padding:.55rem .8rem">${rawBody}${attachHtml}</div>
            </div>
        </div>`;
    }

    function renderTimeline(notes) {
        const timeline = document.getElementById('vm-notes-timeline');
        if (!notes.length) {
            timeline.innerHTML = `<div style="text-align:center;color:var(--muted);font-size:.82rem;padding:.75rem 0;font-style:italic">No notes or activity yet.</div>`;
        } else {
            timeline.innerHTML = notes.map(renderNoteHtml).join('');
        }
    }

    function updateNoteFileLabel(input) {
        const label = document.getElementById('vm-note-file-label');
        if (input.files.length) {
            const f = input.files[0];
            if (f.size > 5 * 1024 * 1024) {
                input.value = '';
                label.textContent = 'Attach a file (max 5 MB)';
                const err = document.getElementById('vm-note-error');
                err.textContent = 'File exceeds 5 MB limit.';
                err.style.display = '';
                return;
            }
            label.textContent = '📎 ' + f.name;
        } else {
            label.textContent = 'Attach a file (max 5 MB)';
        }
    }

    async function submitNote() {
        const content = document.getElementById('vm-note-input').value.trim();
        const fileInput = document.getElementById('vm-note-file');
        const errEl  = document.getElementById('vm-note-error');
        errEl.style.display = 'none';

        if (!content) { errEl.textContent = 'Note cannot be empty.'; errEl.style.display = ''; return; }

        const fd = new FormData();
        fd.append('_token', _csrfToken);
        fd.append('content', content);
        if (fileInput.files.length) fd.append('attachment', fileInput.files[0]);

        const btn = document.getElementById('vm-note-submit');
        btn.disabled = true;
        btn.innerHTML = '<span style="display:inline-block;width:12px;height:12px;border:2px solid rgba(255,255,255,.4);border-top-color:white;border-radius:50%;animation:spin .7s linear infinite;vertical-align:middle;margin-right:.3rem"></span> Adding…';

        try {
            const r = await fetch('/tickets/' + _currentTicketId + '/notes', {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': _csrfToken },
                body: fd,
            });
            const data = await r.json();
            if (!r.ok || !data.ok) throw new Error(data.message || 'Server error');

            // Update cache so note survives modal reopen
            if (!_notesCache[_currentTicketId]) _notesCache[_currentTicketId] = [];
            _notesCache[_currentTicketId].push(data.note);

            // Append the new note to the timeline
            const timeline = document.getElementById('vm-notes-timeline');
            const empty = timeline.querySelector('div[style*="font-style:italic"]');
            if (empty) timeline.innerHTML = '';
            timeline.insertAdjacentHTML('beforeend', renderNoteHtml(data.note));

            // Clear form (keep modal open)
            document.getElementById('vm-note-input').value = '';
            fileInput.value = '';
            document.getElementById('vm-note-file-label').textContent = 'Attach a file (max 5 MB)';
        } catch (e) {
            errEl.textContent = e.message || 'Failed to add note. Please try again.';
            errEl.style.display = '';
        } finally {
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-chat-left-text-fill"></i> Add Note';
        }
    }

    let _routeTicketId = null;
    function openRouteFromView() {
        const btn = document.getElementById('vm-route-btn');
        _routeTicketId = btn.dataset.ticketId;
        document.getElementById('route-sub').textContent = btn.dataset.ticketNum + ' — Forward to another department';
        document.getElementById('routeForm').action = '/tickets/' + _routeTicketId + '/route';
        // Reset dropdowns
        document.getElementById('route-dept').value = '';
        const personSel = document.getElementById('route-person');
        personSel.innerHTML = '<option value="" disabled selected>Select department first…</option>';
        personSel.disabled = true;
        closeModal('viewModal');
        openModal('routeModal');
    }

    function showResImg(input) {
        const d = document.getElementById('res-img-display');
        if (input.files.length) { d.textContent = '🖼 ' + input.files[0].name; d.style.display = 'block'; }
    }

    // ── Route dept → person dropdown ──
    const deptUsers = @json($deptUsers->map(fn($u) => $u->pluck('name')));
    function loadRouteDeptUsers(dept) {
        const sel = document.getElementById('route-person');
        sel.innerHTML = '<option value="" disabled selected>Select person…</option>';
        const users = deptUsers[dept] || [];
        users.forEach(name => {
            const opt = document.createElement('option');
            opt.value = name;
            opt.textContent = name;
            sel.appendChild(opt);
        });
        sel.disabled = users.length === 0;
    }

    // ── Assign-to dept → person dropdown (New Ticket form) ──
    function loadAssignDeptUsers(dept, targetId) {
        const sel = document.getElementById(targetId);
        sel.innerHTML = '<option value="">— No specific person —</option>';
        (deptUsers[dept] || []).forEach(name => {
            const o = document.createElement('option');
            o.value = name; o.textContent = name;
            sel.appendChild(o);
        });
        sel.disabled = false;
    }

    // ── Submit loading state (prevent duplicate submissions) ──
    document.getElementById('submitTicketBtn').addEventListener('click', function () {
        const form = document.getElementById('newTicketForm');
        if (!form.checkValidity()) { form.reportValidity(); return; }
        const btn = this;
        btn.disabled = true;
        btn.innerHTML = '<span style="display:inline-block;width:14px;height:14px;border:2px solid rgba(255,255,255,.35);border-top-color:white;border-radius:50%;animation:spin .7s linear infinite;vertical-align:middle;margin-right:.35rem"></span>Submitting…';
        form.submit();
    });

    // ── Assign-to-me modal ──
    function openAssign(id, ticketNumber) {
        document.getElementById('assign-sub').textContent = 'Ticket ' + ticketNumber;
        document.getElementById('assignForm').action = '/tickets/' + id + '/assign-me';
        openModal('assignModal');
    }

    // ── New Ticket modal ──
    function selectPriority(el) {
        document.querySelectorAll('.priority-pill').forEach(p => p.classList.remove('selected'));
        el.classList.add('selected');
        document.getElementById('t-priority').value = el.dataset.val;
    }
    function showFileName(input) {
        const d = document.getElementById('fileNameDisplay');
        if (input.files.length) { d.textContent = '📎 ' + input.files[0].name; d.style.display='block'; }
    }

    // ══ Flux Animations ══
    (function() {
        const bar = document.createElement('div');
        bar.id = 'flux-progress';
        document.body.prepend(bar);
        function showBar(pct) { bar.style.opacity='1'; bar.style.width=pct+'%'; }
        function doneBar()    { bar.style.width='100%'; setTimeout(()=>{ bar.style.opacity='0'; setTimeout(()=>bar.style.width='0',300); },200); }
        showBar(30); requestAnimationFrame(()=>showBar(72));
        window.addEventListener('load', doneBar);

        document.querySelectorAll('a.nav-item-link[href]').forEach(link => {
            link.addEventListener('click', function(e) {
                const href = this.getAttribute('href');
                if (!href || href==='#' || href.startsWith('javascript') || this.classList.contains('active')) return;
                e.preventDefault();
                showBar(55);
                document.body.classList.add('page-leaving');
                setTimeout(() => window.location.href = href, 230);
            });
        });

        const revealObs = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) { entry.target.classList.add('visible'); revealObs.unobserve(entry.target); }
            });
        }, { threshold:0.06, rootMargin:'0px 0px -20px 0px' });

        document.querySelectorAll('.panel').forEach((el,i) => {
            el.classList.add('reveal');
            el.style.transitionDelay = Math.min(i * 0.08, 0.32) + 's';
            revealObs.observe(el);
        });

        const rows = document.querySelectorAll('.flux-table tbody tr');
        rows.forEach((tr,i) => {
            tr.style.cssText += ';opacity:0;transform:translateY(8px);transition:opacity .3s ease,transform .3s ease;transition-delay:' + Math.min(i*.04,.28)+'s';
        });
        if (rows.length) requestAnimationFrame(() => requestAnimationFrame(() => {
            rows.forEach(tr => { tr.style.opacity='1'; tr.style.transform='translateY(0)'; });
        }));
    })();
</script>

{{-- ════════════════════════════════════
     NEW TICKET MODAL
════════════════════════════════════ --}}
<div class="flux-modal-backdrop" id="newTicketModal" onclick="handleBdClick(event,'newTicketModal')">
    <div class="flux-modal" style="max-width:580px">
        <div class="flux-modal-header">
            <div class="d-flex align-items-center gap-2">
                <div style="width:32px;height:32px;background:linear-gradient(135deg,#4f46e5,#7c3aed);border-radius:.5rem;display:flex;align-items:center;justify-content:center">
                    <i class="bi bi-ticket-perforated-fill text-white" style="font-size:.85rem"></i>
                </div>
                <div>
                    <div style="font-size:.95rem;font-weight:700;color:var(--text)">New Ticket</div>
                    <div style="font-size:.7rem;color:var(--muted)">Fill in the details below to submit a request</div>
                </div>
            </div>
            <button onclick="closeModal('newTicketModal')" style="background:var(--surface2);border:1px solid var(--border);border-radius:.5rem;width:30px;height:30px;display:flex;align-items:center;justify-content:center;cursor:pointer;color:var(--muted);font-size:.85rem">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
        <div class="flux-modal-body">
            <form id="newTicketForm" method="POST" action="{{ route('tickets.store') }}" enctype="multipart/form-data">
                @csrf
                @if($errors->any())
                <div style="background:rgba(248,113,113,.1);border:1px solid rgba(248,113,113,.3);color:#f87171;padding:.55rem .85rem;border-radius:.6rem;font-size:.82rem;margin-bottom:1rem">
                    <i class="bi bi-exclamation-triangle-fill me-1"></i>{{ $errors->first() }}
                </div>
                @endif
                <div class="m-field">
                    <label class="m-label" for="t-subject">Subject <span style="color:#f87171">*</span></label>
                    <input class="m-input" id="t-subject" name="subject" type="text" placeholder="Briefly describe the issue…" required>
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:.85rem" class="m-field">
                    <div>
                        <label class="m-label" for="t-category">Category <span style="color:#f87171">*</span></label>
                        <select class="m-select" id="t-category" name="category" required>
                            <option value="" disabled selected>Select category…</option>
                            <option>IT Support</option><option>Network</option><option>Hardware</option>
                            <option>Software</option><option>Access & Permissions</option><option>HR</option>
                            <option>Facilities</option><option>Other</option>
                        </select>
                    </div>
                    <div>
                        <label class="m-label" for="t-type">Request Type</label>
                        <select class="m-select" id="t-type" name="type">
                            <option value="" disabled selected>Select type…</option>
                            <option>Incident</option><option>Service Request</option>
                            <option>Question</option><option>Change Request</option>
                        </select>
                    </div>
                </div>
                <div class="m-field">
                    <label class="m-label">Priority <span style="color:#f87171">*</span></label>
                    <div style="display:flex;gap:.5rem;flex-wrap:wrap">
                        <span class="priority-pill selected" data-val="low"    onclick="selectPriority(this)" style="padding:.3rem .9rem;border-radius:9999px;font-size:.75rem;font-weight:600;cursor:pointer;border:1.5px solid transparent;background:rgba(52,211,153,.1);color:#34d399;transition:all .15s">Low</span>
                        <span class="priority-pill"          data-val="medium" onclick="selectPriority(this)" style="padding:.3rem .9rem;border-radius:9999px;font-size:.75rem;font-weight:600;cursor:pointer;border:1.5px solid transparent;background:rgba(251,191,36,.1);color:#fbbf24;transition:all .15s">Medium</span>
                        <span class="priority-pill"          data-val="high"   onclick="selectPriority(this)" style="padding:.3rem .9rem;border-radius:9999px;font-size:.75rem;font-weight:600;cursor:pointer;border:1.5px solid transparent;background:rgba(248,113,113,.1);color:#f87171;transition:all .15s">High</span>
                    </div>
                    <input type="hidden" id="t-priority" name="priority" value="low">
                </div>
                {{-- Assign To — Department + Person --}}
                <div class="m-field">
                    <label class="m-label">Assign To <span style="color:var(--muted);font-weight:400;text-transform:none;font-size:.7rem">(optional)</span></label>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:.65rem">
                        @php $deptList=['IT','HR','Finance','OPIC','Dispatch','Asset/Admin','Marketing','RSO','Store']; @endphp
                        <select class="m-select" id="nt-dept" onchange="loadAssignDeptUsers(this.value,'nt-assignee')">
                            <option value="" selected>— Department —</option>
                            @foreach($deptList as $d)<option value="{{ $d }}">{{ $d }}</option>@endforeach
                        </select>
                        <select class="m-select" id="nt-assignee" name="assignee" disabled>
                            <option value="">Select department first…</option>
                        </select>
                    </div>
                </div>
                <div class="m-field">
                    <label class="m-label" for="t-desc">Description <span style="color:#f87171">*</span></label>
                    <textarea class="m-textarea" id="t-desc" name="description" placeholder="Provide as much detail as possible…" required></textarea>
                </div>
                <div class="m-field" style="margin-bottom:0">
                    <label class="m-label">Attachment</label>
                    <div style="border:1.5px dashed var(--border);border-radius:.75rem;padding:1.1rem;text-align:center;cursor:pointer;transition:border-color .2s,background .2s" onclick="document.getElementById('t-file').click()" onmouseover="this.style.borderColor='var(--accent)'" onmouseout="this.style.borderColor='var(--border)'">
                        <i class="bi bi-cloud-upload" style="font-size:1.3rem;color:var(--muted)"></i>
                        <div style="font-size:.78rem;color:var(--muted);margin-top:.3rem">Click to upload a file</div>
                        <div id="fileNameDisplay" style="font-size:.75rem;color:#818cf8;margin-top:.25rem;display:none"></div>
                    </div>
                    <input type="file" id="t-file" name="attachment" style="display:none" onchange="showFileName(this)">
                </div>
            </form>
        </div>
        <div class="flux-modal-footer">
            <button class="btn-cancel" onclick="closeModal('newTicketModal')">Cancel</button>
            <button type="submit" form="newTicketForm" class="btn-submit" id="submitTicketBtn">
                <i class="bi bi-send"></i> Submit Ticket
            </button>
        </div>
    </div>
</div>

<style>
    .priority-pill.selected[data-val="low"]    { border-color:#34d399 !important; box-shadow:0 0 0 3px rgba(52,211,153,.18); }
    .priority-pill.selected[data-val="medium"] { border-color:#fbbf24 !important; box-shadow:0 0 0 3px rgba(251,191,36,.18); }
    .priority-pill.selected[data-val="high"]   { border-color:#f87171 !important; box-shadow:0 0 0 3px rgba(248,113,113,.18); }
    .m-input,.m-select,.m-textarea { width:100%; background:var(--surface2); border:1px solid var(--border); border-radius:.6rem; color:var(--text); font-size:.875rem; font-family:inherit; padding:.5rem .85rem; outline:none; transition:border-color .2s,box-shadow .2s; }
    .m-input::placeholder,.m-textarea::placeholder { color:var(--muted); }
    .m-input:focus,.m-select:focus,.m-textarea:focus { border-color:var(--accent); box-shadow:0 0 0 3px rgba(99,102,241,.2); }
    .m-select option { background:var(--surface); color:var(--text); }
    .m-textarea { resize:vertical; min-height:90px; }
    .m-label { display:block; font-size:.7rem; font-weight:700; text-transform:uppercase; letter-spacing:.07em; color:var(--muted); margin-bottom:.4rem; }
    .m-field { margin-bottom:1rem; }
</style>

{{-- ════════════════════════════════════
     KBA DETACH CONFIRMATION MODAL
════════════════════════════════════ --}}
<div id="kbaConfirmModal" style="display:none;position:fixed;inset:0;z-index:9999;background:rgba(0,0,0,.55);backdrop-filter:blur(4px);-webkit-backdrop-filter:blur(4px);align-items:center;justify-content:center">
    <div id="kbaConfirmBox" style="background:var(--surface,#1e1e2e);border:1px solid var(--border,rgba(255,255,255,.08));border-radius:1rem;padding:1.75rem 1.75rem 1.4rem;max-width:420px;width:90%;box-shadow:0 25px 60px rgba(0,0,0,.5);transform:scale(.94) translateY(8px);opacity:0;transition:transform .22s cubic-bezier(.34,1.56,.64,1),opacity .18s ease">
        <div style="display:flex;align-items:flex-start;gap:.9rem;margin-bottom:1.1rem">
            <div style="width:38px;height:38px;min-width:38px;border-radius:.65rem;background:rgba(248,113,113,.12);border:1px solid rgba(248,113,113,.25);display:flex;align-items:center;justify-content:center">
                <i class="bi bi-journal-x" style="color:#f87171;font-size:1rem"></i>
            </div>
            <div>
                <div style="font-size:.95rem;font-weight:700;color:var(--text,#e2e8f0);margin-bottom:.3rem">Remove KBA Link</div>
                <div id="kbaConfirmMsg" style="font-size:.83rem;color:var(--muted,#94a3b8);line-height:1.55"></div>
            </div>
        </div>
        <div style="background:rgba(251,191,36,.06);border:1px solid rgba(251,191,36,.18);border-radius:.55rem;padding:.55rem .75rem;display:flex;align-items:center;gap:.5rem;margin-bottom:1.3rem">
            <i class="bi bi-info-circle" style="color:#fbbf24;font-size:.8rem;flex-shrink:0"></i>
            <span style="font-size:.78rem;color:#fbbf24">You can re-attach this KBA later if needed.</span>
        </div>
        <div style="display:flex;justify-content:flex-end;gap:.6rem">
            <button id="kbaConfirmCancel" style="padding:.45rem 1.1rem;border-radius:.55rem;border:1px solid var(--border,rgba(255,255,255,.1));background:var(--surface2,rgba(255,255,255,.05));color:var(--text,#e2e8f0);font-size:.83rem;font-weight:600;cursor:pointer;transition:background .15s">Cancel</button>
            <button id="kbaConfirmOk" style="padding:.45rem 1.2rem;border-radius:.55rem;border:none;background:linear-gradient(135deg,#ef4444,#dc2626);color:#fff;font-size:.83rem;font-weight:600;cursor:pointer;display:flex;align-items:center;gap:.4rem;transition:opacity .15s">
                <i class="bi bi-trash3"></i> Remove
            </button>
        </div>
    </div>
</div>
<script>
    (function() {
        let _kbaConfirmCb = null;
        const overlay = document.getElementById('kbaConfirmModal');
        const box     = document.getElementById('kbaConfirmBox');
        const msg     = document.getElementById('kbaConfirmMsg');
        const okBtn   = document.getElementById('kbaConfirmOk');
        const cancelBtn = document.getElementById('kbaConfirmCancel');

        function openKbaConfirm() {
            overlay.style.display = 'flex';
            requestAnimationFrame(() => requestAnimationFrame(() => {
                box.style.transform = 'scale(1) translateY(0)';
                box.style.opacity   = '1';
            }));
        }
        function resetOkBtn() {
            okBtn.disabled = false;
            okBtn.innerHTML = '<i class="bi bi-trash3"></i> Remove';
            okBtn.style.opacity = '1';
        }
        function closeKbaConfirm() {
            box.style.transform = 'scale(.94) translateY(8px)';
            box.style.opacity   = '0';
            setTimeout(() => { overlay.style.display = 'none'; resetOkBtn(); }, 200);
            _kbaConfirmCb = null;
        }

        window.showKbaConfirm = function(label, onConfirm) {
            msg.innerHTML = `Remove <strong style="color:var(--text,#e2e8f0)">"${label}"</strong> from this ticket?`;
            _kbaConfirmCb = onConfirm;
            resetOkBtn();
            openKbaConfirm();
        };

        okBtn.addEventListener('click', function() {
            const cb = _kbaConfirmCb;
            okBtn.disabled = true;
            okBtn.innerHTML = '<span style="display:inline-block;width:13px;height:13px;border:2px solid rgba(255,255,255,.35);border-top-color:white;border-radius:50%;animation:spin .7s linear infinite;vertical-align:middle;margin-right:.3rem"></span>Removing…';
            closeKbaConfirm();
            if (typeof cb === 'function') cb();
        });
        cancelBtn.addEventListener('click', closeKbaConfirm);
        overlay.addEventListener('click', function(e) { if (e.target === overlay) closeKbaConfirm(); });
        document.addEventListener('keydown', function(e) { if (e.key === 'Escape' && overlay.style.display === 'flex') closeKbaConfirm(); });

        okBtn.addEventListener('mouseover',  () => { if (!okBtn.disabled) okBtn.style.opacity = '.85'; });
        okBtn.addEventListener('mouseout',   () => { if (!okBtn.disabled) okBtn.style.opacity = '1'; });
        cancelBtn.addEventListener('mouseover', () => cancelBtn.style.background = 'var(--surface,rgba(255,255,255,.08))');
        cancelBtn.addEventListener('mouseout',  () => cancelBtn.style.background = 'var(--surface2,rgba(255,255,255,.05))');
    })();
</script>

{{-- KBA ATTACH CONFIRMATION MODAL --}}
<div id="kbaConfirmBackdrop" style="display:none;position:fixed;inset:0;z-index:9999;background:rgba(0,0,0,.55);backdrop-filter:blur(4px);align-items:center;justify-content:center">
    <div id="kbaAttachBox" style="background:var(--surface,#1e1e2e);border:1px solid var(--border,rgba(255,255,255,.08));border-radius:1rem;padding:1.75rem 1.75rem 1.4rem;max-width:420px;width:90%;box-shadow:0 25px 60px rgba(0,0,0,.5);transform:scale(.94) translateY(8px);opacity:0;transition:transform .22s cubic-bezier(.34,1.56,.64,1),opacity .18s ease">
        <div style="display:flex;align-items:flex-start;gap:.9rem;margin-bottom:1.1rem">
            <div style="width:38px;height:38px;min-width:38px;border-radius:.65rem;background:rgba(99,102,241,.15);border:1px solid rgba(99,102,241,.3);display:flex;align-items:center;justify-content:center">
                <i class="bi bi-journal-plus" style="color:#818cf8;font-size:1rem"></i>
            </div>
            <div style="min-width:0;flex:1">
                <div style="font-size:.95rem;font-weight:700;color:var(--text,#e2e8f0);margin-bottom:.3rem">Attach Knowledge Article?</div>
                <div style="font-size:.83rem;color:var(--muted,#94a3b8);line-height:1.55">
                    You are about to link <span id="kba-confirm-num" style="color:#818cf8;font-weight:700"></span>
                    <span id="kba-confirm-title" style="color:var(--text,#e2e8f0)"></span> to this ticket.
                </div>
            </div>
        </div>
        <div style="display:flex;justify-content:flex-end;gap:.6rem">
            <button id="kbaAttachCancel" style="padding:.45rem 1.1rem;border-radius:.55rem;border:1px solid var(--border,rgba(255,255,255,.1));background:var(--surface2,rgba(255,255,255,.05));color:var(--text,#e2e8f0);font-size:.83rem;font-weight:600;cursor:pointer;transition:background .15s">Cancel</button>
            <button id="kbaAttachOk" onclick="doAttachKba()" style="padding:.45rem 1.2rem;border-radius:.55rem;border:none;background:linear-gradient(135deg,#4f46e5,#7c3aed);color:#fff;font-size:.83rem;font-weight:600;cursor:pointer;display:flex;align-items:center;gap:.4rem;box-shadow:0 3px 12px rgba(99,102,241,.35);transition:opacity .15s">
                <i class="bi bi-journal-plus" id="kbaAttachOkIcon"></i>
                <span style="display:inline-block;width:13px;height:13px;border:2px solid rgba(255,255,255,.35);border-top-color:white;border-radius:50%;animation:spin .7s linear infinite;vertical-align:middle;display:none" id="kbaAttachOkSpinner"></span>
                <span id="kbaAttachOkLabel">Yes, Attach KBA</span>
            </button>
        </div>
    </div>
</div>
<script>
(function() {
    const overlay   = document.getElementById('kbaConfirmBackdrop');
    const cancelBtn = document.getElementById('kbaAttachCancel');
    cancelBtn.addEventListener('click', closeKbaAttachModal);
    overlay.addEventListener('click', function(e) { if (e.target === overlay) closeKbaAttachModal(); });
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && overlay.style.display === 'flex') closeKbaAttachModal();
    });
})();
</script>
</body>
</html>
