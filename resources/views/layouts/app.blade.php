<!DOCTYPE html>
<html lang="en" id="html-root">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'FluxTickets') — FluxTickets</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; }
        :root { --bg:#0f172a; --surface:#1e293b; --surface2:#263348; --border:#334155; --text:#e2e8f0; --muted:#94a3b8; --accent:#6366f1; --sidebar-w:240px; }
        body:not(.dark) { --bg:#e8edf6; --surface:#ffffff; --surface2:#f0f4fb; --border:#b8c6d8; --text:#0f172a; --muted:#475569; }
        html,body { height:100%; margin:0; }
        body { font-family:'Segoe UI',system-ui,sans-serif; background:var(--bg); color:var(--text); display:flex; transition:background .3s,color .3s; }

        /* ── Sidebar ── */
        .sidebar { width:var(--sidebar-w); background:var(--surface); border-right:1px solid var(--border); display:flex; flex-direction:column; position:fixed; top:0; left:0; bottom:0; z-index:100; transition:width .25s ease,background .3s,border-color .3s; overflow:hidden; }
        .sidebar-brand { padding:.65rem 1rem; border-bottom:1px solid var(--border); display:flex; align-items:center; gap:.65rem; min-height:52px; flex-shrink:0; }
        .brand-icon { width:40px; height:40px; min-width:40px; display:flex; align-items:center; justify-content:center; flex-shrink:0; }
        .brand-icon img { width:40px; height:40px; object-fit:contain; filter:drop-shadow(0 2px 6px rgba(180,20,40,.45)); }
        .brand-name { font-size:1rem; font-weight:700; color:var(--text); white-space:nowrap; overflow:hidden; transition:opacity .2s,max-width .25s; max-width:140px; }
        .sidebar-toggle { margin-left:auto; flex-shrink:0; background:transparent; border:1px solid var(--border); border-radius:.4rem; width:26px; height:26px; min-width:26px; display:flex; align-items:center; justify-content:center; cursor:pointer; color:var(--muted); font-size:.8rem; transition:background .15s,color .15s; }
        .sidebar-toggle:hover { background:var(--surface2); color:var(--text); }
        .sidebar-nav { flex:1; overflow-y:auto; overflow-x:hidden; padding-bottom:.5rem; }
        .sidebar-section-label { font-size:.65rem; font-weight:700; text-transform:uppercase; letter-spacing:.08em; color:var(--muted); padding:.65rem 1.25rem .3rem; white-space:nowrap; transition:opacity .2s,max-width .25s; }
        .nav-item-link { display:flex; align-items:center; gap:.7rem; padding:.38rem .875rem; border-radius:.5rem; margin:.05rem .4rem; color:var(--muted); font-size:.82rem; font-weight:500; text-decoration:none !important; transition:background .15s,color .15s; white-space:nowrap; }
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
        .nav-text { transition:opacity .2s,max-width .25s; max-width:160px; overflow:hidden; }
        .nav-badge { transition:opacity .2s,max-width .25s; max-width:60px; overflow:hidden; }
        .sidebar-footer { flex-shrink:0; padding:.5rem .75rem; border-top:1px solid var(--border); }
        .user-chip { display:flex; align-items:center; gap:.65rem; padding:.5rem .65rem; border-radius:.6rem; background:var(--surface2); }
        .avatar { width:32px; height:32px; min-width:32px; background:linear-gradient(135deg,#4f46e5,#7c3aed); border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:.75rem; font-weight:700; color:white; flex-shrink:0; }
        .user-info { transition:opacity .2s,max-width .25s; max-width:120px; overflow:hidden; min-width:0; flex:1; }

        /* Minimized sidebar */
        .sidebar.mini { width:60px; }
        .sidebar.mini .brand-name,
        .sidebar.mini .sidebar-section-label,
        .sidebar.mini .nav-text,
        .sidebar.mini .user-info,
        .sidebar.mini .nav-badge { opacity:0; max-width:0; pointer-events:none; overflow:hidden; }
        /* Nav items — tight, centred icon */
        .sidebar.mini .nav-item-link { justify-content:center; padding:.38rem 0; margin:.05rem .4rem; gap:0; }
        .sidebar.mini .nav-icon { font-size:1.05rem; }
        /* Brand — hide logo, keep only toggle button centred */
        .sidebar.mini .sidebar-brand { justify-content:center; gap:0; padding:.85rem 0; }
        .sidebar.mini .brand-icon { display:none; }
        .sidebar.mini .sidebar-toggle { margin:0; }
        /* Footer — hide full chip, show only sign-out icon */
        .sidebar.mini .user-chip { display:none; }
        .sidebar.mini .mini-signout { display:flex !important; }
        .mini-signout { display:none; justify-content:center; align-items:center; padding:.4rem 0; }

        /* ── Layout ── */
        .main-wrap { margin-left:var(--sidebar-w); flex:1; display:flex; flex-direction:column; min-height:100vh; transition:margin-left .25s ease; overflow-x:hidden; }
        .main-wrap.mini { margin-left:60px; }
        .topbar { position:fixed; top:0; left:var(--sidebar-w); right:0; z-index:50; background:var(--surface); border-bottom:1px solid var(--border); padding:.75rem 1.25rem; display:flex; align-items:center; justify-content:space-between; gap:.75rem; transition:left .25s ease,background .3s,border-color .3s; flex-shrink:0; }
        .main-wrap.mini .topbar { left:60px; }
        .content { padding:1.25rem; flex:1; padding-top:calc(1.25rem + 52px + 1rem); }

        /* ── Common components ── */
        .panel { background:var(--surface); border:1px solid var(--border); border-radius:1rem; transition:background .3s,border-color .3s; }
        .panel-header { padding:.875rem 1.25rem; border-bottom:1px solid var(--border); display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:.5rem; }
        .panel-title { font-size:.875rem; font-weight:700; color:var(--text); }
        .filter-btn { background:var(--surface2); border:1px solid var(--border); border-radius:.5rem; padding:.3rem .85rem; font-size:.78rem; font-weight:600; color:var(--muted); cursor:pointer; transition:all .15s; }
        .filter-btn:hover, .filter-btn.active { background:rgba(99,102,241,.15); border-color:rgba(99,102,241,.4); color:#818cf8; }
        .dark-toggle { width:2.5rem; height:1.35rem; background:#6366f1; border-radius:9999px; position:relative; cursor:pointer; transition:background .3s; border:none; outline:none; }
        .dark-toggle::after { content:''; position:absolute; top:2px; left:2px; width:1rem; height:1rem; background:white; border-radius:50%; transition:transform .3s; box-shadow:0 1px 3px rgba(0,0,0,.2); }
        body:not(.dark) .dark-toggle { background:#cbd5e1; }
        body:not(.dark) .dark-toggle::after { transform:translateX(0); }
        body.dark .dark-toggle::after { transform:translateX(1.15rem); }
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
        .flux-table { width:100%; border-collapse:collapse; font-size:.78rem; }
        .flux-table thead th { padding:.6rem .875rem; font-size:.63rem; font-weight:700; text-transform:uppercase; letter-spacing:.05em; color:var(--muted); border-bottom:1px solid var(--border); white-space:nowrap; }
        .flux-table tbody tr { border-bottom:1px solid var(--border); transition:background .15s; }
        .flux-table tbody tr:last-child { border-bottom:none; }
        .flux-table tbody tr:hover { background:var(--surface2); }
        .flux-table tbody td { padding:.7rem .875rem; color:var(--text); vertical-align:middle; }
        .m-field { margin-bottom:1rem; }
        .m-label { display:block; font-size:.7rem; font-weight:700; text-transform:uppercase; letter-spacing:.07em; color:var(--muted); margin-bottom:.4rem; }
        .m-input,.m-select,.m-textarea { width:100%; background:var(--surface2); border:1px solid var(--border); border-radius:.6rem; color:var(--text); font-size:.875rem; font-family:inherit; padding:.5rem .85rem; outline:none; transition:border-color .2s,box-shadow .2s; }
        .m-input::placeholder,.m-textarea::placeholder { color:var(--muted); }
        .m-input:focus,.m-select:focus,.m-textarea:focus { border-color:var(--accent); box-shadow:0 0 0 3px rgba(99,102,241,.2); }
        .m-select option { background:var(--surface); color:var(--text); }
        .c-dd { display:none;position:absolute;top:calc(100% + 2px);left:0;right:0;background:var(--surface2);border:1px solid var(--border);border-radius:.6rem;max-height:200px;overflow-y:auto;z-index:9999;box-shadow:0 6px 18px rgba(0,0,0,.35); }
        .c-dd-item { padding:.5rem .75rem;cursor:pointer;font-size:.82rem;border-bottom:1px solid var(--border);display:flex;justify-content:space-between;align-items:center; }
        .c-dd-item:last-child { border-bottom:none; }
        .c-dd-item:hover { background:rgba(99,102,241,.1); }
        .c-dd-item .cd-label { color:var(--text);font-weight:500; }
        .c-dd-item .cd-sub { color:var(--muted);font-size:.75rem; }
        .m-textarea { resize:vertical; min-height:90px; }
        .btn-primary { background:linear-gradient(135deg,#4f46e5,#7c3aed); border:none; border-radius:.6rem; color:white; font-size:.875rem; font-weight:600; padding:.5rem 1.25rem; cursor:pointer; box-shadow:0 3px 12px rgba(99,102,241,.3); display:inline-flex; align-items:center; gap:.4rem; transition:opacity .2s,transform .15s; }
        .btn-primary:hover { opacity:.9; transform:translateY(-1px); }
        .btn-ghost { background:var(--surface2); border:1px solid var(--border); border-radius:.6rem; color:var(--muted); font-size:.875rem; font-weight:600; padding:.5rem 1.1rem; cursor:pointer; transition:background .15s,color .15s; display:inline-flex; align-items:center; gap:.4rem; }
        .btn-ghost:hover { background:var(--border); color:var(--text); }
        .stat-card { background:var(--surface); border:1px solid var(--border); border-radius:1rem; padding:1.25rem; transition:background .3s,border-color .3s,transform .2s,box-shadow .2s; }
        .stat-card:hover { transform:translateY(-2px); box-shadow:0 8px 24px rgba(0,0,0,.15); }
        /* ── Topbar shared ── */
        .topbar-search { display:flex; align-items:center; gap:.5rem; background:var(--surface2); border:1px solid var(--border); border-radius:.6rem; padding:.4rem .85rem; width:220px; transition:border-color .2s; }
        .topbar-search:focus-within { border-color:var(--accent); }
        .topbar-search input { border:none; background:transparent; outline:none; font-size:.825rem; color:var(--text); width:100%; }
        .topbar-search input::placeholder { color:var(--muted); }
        .btn-new { background:linear-gradient(135deg,#4f46e5,#7c3aed); border:none; border-radius:.6rem; color:white; font-weight:600; font-size:.8rem; padding:.45rem 1rem; display:flex; align-items:center; gap:.4rem; transition:opacity .2s,transform .15s; box-shadow:0 3px 12px rgba(99,102,241,.35); cursor:pointer; text-decoration:none !important; white-space:nowrap; }
        .btn-new:hover { opacity:.9; transform:translateY(-1px); color:white; }
        /* ── Modals shared ── */
        .flux-modal-backdrop { position:fixed; inset:0; z-index:999; background:rgba(0,0,0,.6); backdrop-filter:blur(4px); display:flex; align-items:center; justify-content:center; padding:1rem; opacity:0; pointer-events:none; transition:opacity .2s; }
        .flux-modal-backdrop.open { opacity:1; pointer-events:all; }
        .flux-modal { background:var(--surface); border:1px solid var(--border); border-radius:1.25rem; width:100%; max-width:640px; box-shadow:0 30px 80px rgba(0,0,0,.45); transform:translateY(16px) scale(.98); transition:transform .25s,opacity .25s; opacity:0; max-height:92vh; display:flex; flex-direction:column; }
        .flux-modal-backdrop.open .flux-modal { transform:translateY(0) scale(1); opacity:1; }
        .flux-modal-header { padding:1.15rem 1.5rem; border-bottom:1px solid var(--border); display:flex; align-items:flex-start; justify-content:space-between; flex-shrink:0; }
        .flux-modal-body { padding:1.35rem 1.5rem; overflow-y:auto; flex:1; }
        .flux-modal-footer { padding:1rem 1.5rem; border-top:1px solid var(--border); display:flex; align-items:center; justify-content:flex-end; gap:.65rem; flex-shrink:0; flex-wrap:wrap; }
        .btn-cancel { background:var(--surface2); border:1px solid var(--border); border-radius:.6rem; color:var(--muted); font-size:.875rem; font-weight:600; padding:.5rem 1.1rem; cursor:pointer; transition:background .15s,color .15s; }
        .btn-cancel:hover { background:var(--border); color:var(--text); }
        .btn-submit { background:linear-gradient(135deg,#4f46e5,#7c3aed); border:none; border-radius:.6rem; color:white; font-size:.875rem; font-weight:600; padding:.5rem 1.4rem; cursor:pointer; box-shadow:0 3px 12px rgba(99,102,241,.35); display:flex; align-items:center; gap:.4rem; transition:opacity .2s,transform .15s; }
        .btn-submit:hover { opacity:.9; transform:translateY(-1px); }
        .priority-pill.selected[data-val="low"]    { border-color:#34d399 !important; box-shadow:0 0 0 3px rgba(52,211,153,.18); }
        .priority-pill.selected[data-val="medium"] { border-color:#fbbf24 !important; box-shadow:0 0 0 3px rgba(251,191,36,.18); }
        .priority-pill.selected[data-val="high"]   { border-color:#f87171 !important; box-shadow:0 0 0 3px rgba(248,113,113,.18); }
        ::-webkit-scrollbar { width:5px; height:5px; } ::-webkit-scrollbar-track { background:transparent; } ::-webkit-scrollbar-thumb { background:var(--border); border-radius:9999px; }
        a { text-decoration:none !important; }
        @keyframes spin { to { transform:rotate(360deg); } }

        /* ══ Flux Animations ══ */
        @keyframes pageIn  { from{opacity:0;transform:translateY(16px)} to{opacity:1;transform:translateY(0)} }
        @keyframes pageOut { from{opacity:1;transform:translateY(0)} to{opacity:0;transform:translateY(-10px)} }
        @keyframes popIn   { from{opacity:0;transform:scale(.94) translateY(10px)} to{opacity:1;transform:scale(1) translateY(0)} }
        @keyframes shimmer { from{background-position:-400% 0} to{background-position:400% 0} }
        @keyframes slideUp { from{opacity:0;transform:translateY(26px)} to{opacity:1;transform:translateY(0)} }

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
            /* Critical: reset topbar left so it spans the full viewport on mobile */
            .topbar, .main-wrap.mini .topbar { left:0 !important; padding:.55rem .875rem; gap:.4rem; }
            .topbar-title-sub { display:none; }
            .mob-hamburger { display:flex; }
            .content { padding:.875rem; padding-top:calc(.875rem + 48px + .5rem); }
            .topbar-search { display:none !important; }
            .btn-new-text { display:none; }
            .flux-modal { border-radius:.875rem; max-width:calc(100vw - 1.5rem); }
            .flux-modal-body { padding:1rem 1.1rem; }
            .flux-modal-header { padding:.875rem 1rem; }
            .flux-modal-footer { padding:.75rem 1rem; }
            .m-row { grid-template-columns:1fr !important; }
        }
        @media (max-width:480px) {
            .content { padding:.65rem; padding-top:calc(.65rem + 48px + .5rem); }
            .stat-grid { grid-template-columns:1fr 1fr !important; }
            /* Shrink right-side topbar items on very small screens */
            .topbar-right-icons { gap:.35rem !important; }
        }
        @stack('styles')
    </style>
</head>
<body class="dark">

{{-- Mobile sidebar overlay --}}
<div class="sidebar-overlay" id="sidebarOverlay" onclick="closeMobileSidebar()"></div>

{{-- ══ SIDEBAR ══ --}}
@php $ap = $activePage ?? ''; @endphp
<aside class="sidebar" id="appSidebar">
    <div class="sidebar-brand">
        <div class="brand-icon">
            <img src="{{ asset('logo/Gemini_Generated_Image_1w1sif1w1sif1w1s-removebg-preview.png') }}" alt="FluxTickets Logo">
        </div>
        <span class="brand-name">FluxTickets</span>
        <button class="sidebar-toggle" id="sidebarToggle" title="Collapse sidebar" type="button">
            <i class="bi bi-layout-sidebar" id="sidebarToggleIcon"></i>
        </button>
    </div>

    <div class="sidebar-nav">
        <div class="sidebar-section-label">Main</div>
        <a class="nav-item-link {{ $ap==='dashboard'?'active':'' }}" href="{{ route('dashboard') }}">
            <i class="bi bi-grid-fill nav-icon"></i><span class="nav-text">Dashboard</span>
        </a>
        <a class="nav-item-link {{ $ap==='calendar'?'active':'' }}" href="{{ route('calendar') }}">
            <i class="bi bi-calendar3 nav-icon"></i><span class="nav-text">Calendar</span>
        </a>
        <button class="nav-item-link nav-dropdown-trigger {{ in_array($ap,['tickets','queue'])?'active':'' }}" id="ticketsDropdownTrigger" type="button">
            <i class="bi bi-ticket-perforated nav-icon"></i>
            <span class="nav-text">All Tickets</span>
            <i class="bi bi-chevron-down nav-chevron nav-badge"></i>
        </button>
        <div class="nav-submenu" id="ticketsSubmenu">
            <a class="nav-item-link nav-sub-item {{ $ap==='queue'?'active':'' }}" href="{{ route('queue') }}">
                <i class="bi bi-clock-history nav-icon" style="font-size:.85rem"></i><span class="nav-text">My Queue</span>
            </a>
        </div>

        @php
        $_u     = auth()->user();
        $_isSA  = $_u && $_u->role === 'super_admin';
        $_perm  = $_isSA ? array_fill_keys(['agents','reports','knowledge_read','knowledge_write','settings'], true)
                         : $_u->effectivePageAccess();
        $_showAgents    = $_isSA || !empty($_perm['agents']);
        $_showReports   = $_isSA || !empty($_perm['reports']);
        $_showKb        = $_isSA || !empty($_perm['knowledge_read']);
        $_showSettings  = $_isSA || !empty($_perm['settings']);
        $_showManage    = $_showAgents || $_showReports || $_showKb || $_isSA;
        @endphp
        @if($_showManage)
        <div class="sidebar-section-label">Manage</div>
        @if($_showAgents)
        <button class="nav-item-link nav-dropdown-trigger {{ in_array($ap,['agents','agents_departments'])?'active':'' }}" id="agentsDropdownTrigger" type="button">
            <i class="bi bi-people nav-icon"></i>
            <span class="nav-text">Agents</span>
            <i class="bi bi-chevron-down nav-chevron nav-badge"></i>
        </button>
        <div class="nav-submenu" id="agentsSubmenu">
            <a class="nav-item-link nav-sub-item {{ $ap==='agents'?'active':'' }}" href="{{ route('agents.index') }}">
                <i class="bi bi-person-lines-fill nav-icon" style="font-size:.85rem"></i><span class="nav-text">All Agents</span>
            </a>
            <a class="nav-item-link nav-sub-item {{ $ap==='agents_departments'?'active':'' }}" href="{{ route('agents.departments') }}">
                <i class="bi bi-building nav-icon" style="font-size:.85rem"></i><span class="nav-text">Departments</span>
            </a>
        </div>
        @endif
        @if($_showReports)
        <button class="nav-item-link nav-dropdown-trigger {{ in_array($ap,['reports','reports_agents'])?'active':'' }}" id="reportsDropdownTrigger" type="button">
            <i class="bi bi-bar-chart-line nav-icon"></i>
            <span class="nav-text">Reports</span>
            <i class="bi bi-chevron-down nav-chevron nav-badge"></i>
        </button>
        <div class="nav-submenu" id="reportsSubmenu">
            <a class="nav-item-link nav-sub-item {{ $ap==='reports'?'active':'' }}" href="{{ route('reports.index') }}">
                <i class="bi bi-speedometer2 nav-icon" style="font-size:.85rem"></i><span class="nav-text">Overview</span>
            </a>
            <a class="nav-item-link nav-sub-item {{ $ap==='reports_agents'?'active':'' }}" href="{{ route('reports.agents') }}">
                <i class="bi bi-person-badge nav-icon" style="font-size:.85rem"></i><span class="nav-text">Agent Performance</span>
            </a>
        </div>
        @endif
        @if($_showKb)
        <a class="nav-item-link {{ $ap==='knowledge'?'active':'' }}" href="{{ route('knowledge.index') }}">
            <i class="bi bi-book nav-icon"></i><span class="nav-text">Knowledge Base</span>
        </a>
        @endif
        @if($_isSA)
        <a class="nav-item-link {{ $ap==='roles'?'active':'' }}" href="{{ route('roles.index') }}">
            <i class="bi bi-shield-lock nav-icon"></i><span class="nav-text">Role Access & Permission</span>
        </a>
        @endif
        @endif

        @if($_showSettings)
        <div class="sidebar-section-label">System</div>
        <button class="nav-item-link nav-dropdown-trigger {{ in_array($ap,['settings','integrations','audit_logs'])?'active':'' }}" id="systemDropdownTrigger" type="button">
            <i class="bi bi-sliders nav-icon"></i>
            <span class="nav-text">System</span>
            <i class="bi bi-chevron-down nav-chevron nav-badge"></i>
        </button>
        <div class="nav-submenu" id="systemSubmenu">
            <a class="nav-item-link nav-sub-item {{ $ap==='settings'?'active':'' }}" href="{{ route('settings.index') }}">
                <i class="bi bi-gear nav-icon" style="font-size:.85rem"></i><span class="nav-text">Settings</span>
            </a>
            <a class="nav-item-link nav-sub-item {{ $ap==='integrations'?'active':'' }}" href="{{ route('integrations.index') }}">
                <i class="bi bi-plug nav-icon" style="font-size:.85rem"></i><span class="nav-text">Integrations</span>
            </a>
            <a class="nav-item-link nav-sub-item {{ $ap==='audit_logs'?'active':'' }}" href="{{ route('audit.logs') }}">
                <i class="bi bi-journal-text nav-icon" style="font-size:.85rem"></i><span class="nav-text">Audit Logs</span>
            </a>
        </div>
        @endif
    </div>

    <div class="sidebar-footer">
        {{-- Full chip (visible when expanded) --}}
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
            <button type="button" onclick="openLogoutModal()" title="Sign out" style="background:none;border:none;color:var(--muted);cursor:pointer;padding:.15rem;font-size:.95rem;line-height:1;transition:color .15s;flex-shrink:0" onmouseover="this.style.color='var(--text)'" onmouseout="this.style.color='var(--muted)'"><i class="bi bi-box-arrow-right"></i></button>
        </div>
        {{-- Mini sign-out (visible only when collapsed) --}}
        <div class="mini-signout">
            <button type="button" onclick="openLogoutModal()" title="Sign out"
                style="background:none;border:1px solid var(--border);border-radius:.5rem;color:var(--muted);cursor:pointer;width:36px;height:36px;display:flex;align-items:center;justify-content:center;font-size:1rem;transition:background .15s,color .15s"
                onmouseover="this.style.background='rgba(248,113,113,.1)';this.style.color='#f87171';this.style.borderColor='rgba(248,113,113,.3)'"
                onmouseout="this.style.background='none';this.style.color='var(--muted)';this.style.borderColor='var(--border)'">
                <i class="bi bi-box-arrow-right"></i>
            </button>
        </div>
    </div>
</aside>

@php
$_deptUsers = \App\Models\User::whereNotNull('department')
    ->orderBy('name')->get(['id','name','department'])
    ->groupBy('department')->map(fn($g) => $g->pluck('name'));
$_allUsers = \App\Models\User::orderBy('name')->get(['id','name','department'])
    ->map(fn($u) => ['id' => $u->id, 'name' => $u->name, 'department' => $u->department ?? '']);
$_deptList = auth()->user()->isSuperAdmin()
    ? \App\Models\SystemSetting::allDepartments()
    : auth()->user()->effectiveRoutingDepts();
@endphp

{{-- ══ MAIN ══ --}}
<div class="main-wrap" id="mainWrap">
    <header class="topbar">
        <div style="display:flex;align-items:center;gap:.6rem;min-width:0;flex:1">
            <button class="mob-hamburger" id="mobHamburger" aria-label="Menu"><i class="bi bi-list"></i></button>
            <div style="min-width:0">
                <div style="font-size:1rem;font-weight:700;color:var(--text);white-space:nowrap;overflow:hidden;text-overflow:ellipsis">@yield('topbar-title')</div>
                <div class="topbar-title-sub" style="font-size:.72rem;color:var(--muted);white-space:nowrap;overflow:hidden;text-overflow:ellipsis">@yield('topbar-sub')</div>
            </div>
        </div>
        <div class="topbar-right-icons" style="display:flex;align-items:center;gap:.75rem;flex-shrink:0">
            {{-- Global Search --}}
            <div class="topbar-search d-none d-md-flex" style="position:relative">
                <i class="bi bi-search" style="color:var(--muted);font-size:.8rem;flex-shrink:0"></i>
                <input type="text" id="searchInput" placeholder="Search tickets…" autocomplete="off"
                    oninput="globalSearch(this.value); if(typeof applyFilters==='function')applyFilters();"
                    onfocus="if(this.value.trim())globalSearch(this.value)">
                <div id="globalSearchDrop" style="display:none;position:absolute;top:calc(100% + 8px);left:0;right:0;min-width:340px;background:var(--surface);border:1px solid var(--border);border-radius:.75rem;box-shadow:0 8px 28px rgba(0,0,0,.3);z-index:9999;overflow:hidden"></div>
            </div>
            {{-- Dark mode toggle --}}
            <div style="display:flex;align-items:center;gap:.5rem">
                <i class="bi bi-sun" style="color:#fbbf24;font-size:.8rem"></i>
                <button class="dark-toggle" id="darkToggle" aria-label="Toggle dark mode"></button>
                <i class="bi bi-moon-stars" style="color:#818cf8;font-size:.8rem"></i>
            </div>
            {{-- Notification bell --}}
            @include('partials.notif-bell')
            {{-- New Ticket --}}
            <button class="btn-new" onclick="openModal('newTicketModal')">
                <i class="bi bi-plus-lg"></i><span class="btn-new-text"> New Ticket</span>
            </button>
        </div>
    </header>

    <main class="content">
        @if(session('success'))
        <div id="flash-ok" style="display:flex;align-items:center;gap:.65rem;background:rgba(52,211,153,.12);border:1px solid rgba(52,211,153,.3);color:#34d399;padding:.65rem 1rem;border-radius:.75rem;font-size:.85rem;font-weight:500;margin-bottom:1.25rem">
            <i class="bi bi-check-circle-fill"></i> {{ session('success') }}
        </div>
        @endif
        @if(session('error'))
        <div style="display:flex;align-items:center;gap:.65rem;background:rgba(248,113,113,.12);border:1px solid rgba(248,113,113,.3);color:#f87171;padding:.65rem 1rem;border-radius:.75rem;font-size:.85rem;font-weight:500;margin-bottom:1.25rem">
            <i class="bi bi-exclamation-triangle-fill"></i> {{ session('error') }}
        </div>
        @endif

        @yield('content')
    </main>
</div>

@stack('modals')

{{-- ════ New Ticket Modal (layout-level, available on every page) ════ --}}
<div class="flux-modal-backdrop" id="newTicketModal" onclick="if(event.target===this)closeModal('newTicketModal')">
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
            <button onclick="closeModal('newTicketModal')" style="background:var(--surface2);border:1px solid var(--border);border-radius:.5rem;width:30px;height:30px;display:flex;align-items:center;justify-content:center;cursor:pointer;color:var(--muted);font-size:.85rem"><i class="bi bi-x-lg"></i></button>
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
                    <label class="m-label">Requester <span style="color:#f87171">*</span></label>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:.65rem">
                        <div style="position:relative">
                            <input class="m-input" id="t-requester" name="requester" type="text"
                                placeholder="Search name or type…" required
                                value="{{ old('requester') }}"
                                autocomplete="off"
                                oninput="requesterSearch(this.value)"
                                onfocus="requesterSearch(this.value)"
                                onblur="setTimeout(()=>{const d=document.getElementById('requester-dropdown');if(d)d.style.display='none'},200)">
                            <input type="hidden" id="t-requester-id" name="requester_id" value="{{ old('requester_id') }}">
                            <div id="requester-dropdown" style="display:none;position:absolute;top:100%;left:0;right:0;background:var(--surface2);border:1px solid var(--border);border-radius:.5rem;max-height:180px;overflow-y:auto;z-index:9999;margin-top:2px;box-shadow:0 4px 12px rgba(0,0,0,.3)"></div>
                        </div>
                        <input class="m-input" id="t-requester-dept" name="requester_dept" type="text"
                            placeholder="Department (auto-filled)"
                            value="{{ old('requester_dept') }}"
                            readonly
                            style="background:var(--surface);cursor:default;color:var(--muted)">
                    </div>
                </div>
                <div class="m-field">
                    <label class="m-label" for="t-subject">Subject <span style="color:#f87171">*</span></label>
                    <input class="m-input" id="t-subject" name="subject" type="text" placeholder="Briefly describe the issue…" required value="{{ old('subject') }}">
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:.85rem" class="m-field">
                    <div>
                        <label class="m-label">Category <span style="color:#f87171">*</span></label>
                        <div style="position:relative">
                            <input class="m-input" id="t-cat-txt" type="text" placeholder="Search category…"
                                autocomplete="off"
                                value="{{ old('category') }}"
                                oninput="cdFilter(this,'t-cat-dd','t-category',_cats)"
                                onfocus="cdOpen(this,'t-cat-dd','t-category',_cats)"
                                onblur="setTimeout(()=>cdClose('t-cat-dd'),200)">
                            <input type="hidden" id="t-category" name="category" value="{{ old('category') }}">
                            <div id="t-cat-dd" class="c-dd"></div>
                        </div>
                    </div>
                    <div>
                        <label class="m-label">Request Type</label>
                        <div style="position:relative">
                            <input class="m-input" id="t-type-txt" type="text" placeholder="Search type…"
                                autocomplete="off"
                                value="{{ old('type') }}"
                                oninput="cdFilter(this,'t-type-dd','t-type',_types)"
                                onfocus="cdOpen(this,'t-type-dd','t-type',_types)"
                                onblur="setTimeout(()=>cdClose('t-type-dd'),200)">
                            <input type="hidden" id="t-type" name="type" value="{{ old('type') }}">
                            <div id="t-type-dd" class="c-dd"></div>
                        </div>
                    </div>
                </div>
                <div class="m-field">
                    <label class="m-label">Priority <span style="color:#f87171">*</span></label>
                    <div style="display:flex;gap:.5rem;flex-wrap:wrap">
                        <span class="priority-pill selected" data-val="low"    onclick="selectPriority(this)" style="padding:.3rem .9rem;border-radius:9999px;font-size:.75rem;font-weight:600;cursor:pointer;border:1.5px solid transparent;background:rgba(52,211,153,.1);color:#34d399;transition:all .15s">Low</span>
                        <span class="priority-pill"          data-val="medium" onclick="selectPriority(this)" style="padding:.3rem .9rem;border-radius:9999px;font-size:.75rem;font-weight:600;cursor:pointer;border:1.5px solid transparent;background:rgba(251,191,36,.1);color:#fbbf24;transition:all .15s">Medium</span>
                        <span class="priority-pill"          data-val="high"   onclick="selectPriority(this)" style="padding:.3rem .9rem;border-radius:9999px;font-size:.75rem;font-weight:600;cursor:pointer;border:1.5px solid transparent;background:rgba(248,113,113,.1);color:#f87171;transition:all .15s">High</span>
                    </div>
                    <input type="hidden" id="t-priority" name="priority" value="{{ old('priority','low') }}">
                </div>
                <div class="m-field">
                    <label class="m-label">Assign To <span style="color:var(--muted);font-weight:400;text-transform:none;font-size:.7rem">(optional)</span></label>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:.65rem">
                        <div style="position:relative">
                            <input class="m-input" id="nt-dept-txt" type="text" placeholder="— Department —"
                                autocomplete="off"
                                oninput="cdFilter(this,'nt-dept-dd','nt-dept-val',_depts,onDeptPick)"
                                onfocus="cdOpen(this,'nt-dept-dd','nt-dept-val',_depts,onDeptPick)"
                                onblur="setTimeout(()=>cdClose('nt-dept-dd'),200)">
                            <input type="hidden" id="nt-dept-val">
                            <div id="nt-dept-dd" class="c-dd"></div>
                        </div>
                        <div style="position:relative">
                            <input class="m-input" id="nt-asn-txt" type="text" placeholder="Select department first…"
                                autocomplete="off"
                                oninput="cdFilter(this,'nt-asn-dd','nt-assignee',_assigneeItems)"
                                onfocus="cdOpen(this,'nt-asn-dd','nt-assignee',_assigneeItems)"
                                onblur="setTimeout(()=>cdClose('nt-asn-dd'),200)">
                            <input type="hidden" id="nt-assignee" name="assignee" value="">
                            <div id="nt-asn-dd" class="c-dd"></div>
                        </div>
                    </div>
                </div>
                <div class="m-field">
                    <label class="m-label" for="t-desc">Description <span style="color:#f87171">*</span></label>
                    <textarea class="m-textarea" id="t-desc" name="description" placeholder="Provide as much detail as possible…" required>{{ old('description') }}</textarea>
                </div>
                <div class="m-field" style="margin-bottom:0">
                    <label class="m-label">Attachment</label>
                    <div style="border:1.5px dashed var(--border);border-radius:.75rem;padding:1.1rem;text-align:center;cursor:pointer;transition:border-color .2s" onclick="document.getElementById('t-file').click()" onmouseover="this.style.borderColor='var(--accent)'" onmouseout="this.style.borderColor='var(--border)'">
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
            <button type="submit" form="newTicketForm" class="btn-submit" id="submitTicketBtn"><i class="bi bi-send"></i> Submit Ticket</button>
        </div>
    </div>
</div>

<script>
    const body   = document.body;
    const toggle = document.getElementById('darkToggle');
    const saved  = localStorage.getItem('theme');
    if (saved === 'light') body.classList.remove('dark'); else body.classList.add('dark');
    toggle.addEventListener('click', () => {
        body.classList.toggle('dark');
        localStorage.setItem('theme', body.classList.contains('dark') ? 'dark' : 'light');
    });

    const sidebar  = document.getElementById('appSidebar');
    const mainWrap = document.getElementById('mainWrap');

    // ── Sidebar collapse ──
    (function() {
        const toggleBtn  = document.getElementById('sidebarToggle');
        const toggleIcon = document.getElementById('sidebarToggleIcon');

        function applyMini(mini) {
            sidebar.classList.toggle('mini', mini);
            mainWrap.classList.toggle('mini', mini);
            toggleIcon.className = mini ? 'bi bi-layout-sidebar-reverse' : 'bi bi-layout-sidebar';
            toggleBtn.title = mini ? 'Expand sidebar' : 'Collapse sidebar';
        }

        // Restore saved state
        applyMini(localStorage.getItem('sidebar') === 'mini');

        toggleBtn.addEventListener('click', () => {
            const mini = !sidebar.classList.contains('mini');
            applyMini(mini);
            localStorage.setItem('sidebar', mini ? 'mini' : 'open');
        });
    })();

    // ── Tickets dropdown ──
    (function() {
        const trigger = document.getElementById('ticketsDropdownTrigger');
        const submenu = document.getElementById('ticketsSubmenu');
        const chevron = trigger ? trigger.querySelector('.nav-chevron') : null;
        const stored = localStorage.getItem('ticketsDropdown');
        const open = stored === 'open';
        if (open && submenu) { submenu.classList.add('open'); if (chevron) chevron.style.transform = 'rotate(180deg)'; }
        if (trigger) trigger.addEventListener('click', function() {
            submenu.classList.toggle('open');
            const isOpen = submenu.classList.contains('open');
            if (chevron) chevron.style.transform = isOpen ? 'rotate(180deg)' : '';
            localStorage.setItem('ticketsDropdown', isOpen ? 'open' : '');
        });
    })();

    // ── Agents dropdown ──
    (function() {
        const trigger = document.getElementById('agentsDropdownTrigger');
        const submenu = document.getElementById('agentsSubmenu');
        if (!trigger || !submenu) return;
        const chevron = trigger.querySelector('.nav-chevron');
        const stored = localStorage.getItem('agentsDropdown');
        const open = stored === 'open' || {{ in_array($ap ?? '', ['agents','agents_departments']) ? 'true' : 'false' }};
        if (open) { submenu.classList.add('open'); if (chevron) chevron.style.transform = 'rotate(180deg)'; }
        trigger.addEventListener('click', function() {
            submenu.classList.toggle('open');
            const isOpen = submenu.classList.contains('open');
            if (chevron) chevron.style.transform = isOpen ? 'rotate(180deg)' : '';
            localStorage.setItem('agentsDropdown', isOpen ? 'open' : '');
        });
    })();

    // ── Reports dropdown ──
    (function() {
        const trigger = document.getElementById('reportsDropdownTrigger');
        const submenu = document.getElementById('reportsSubmenu');
        if (!trigger || !submenu) return;
        const chevron = trigger.querySelector('.nav-chevron');
        const stored = localStorage.getItem('reportsDropdown');
        const open = stored === 'open' || {{ in_array($ap ?? '', ['reports','reports_agents']) ? 'true' : 'false' }};
        if (open) { submenu.classList.add('open'); if (chevron) chevron.style.transform = 'rotate(180deg)'; }
        trigger.addEventListener('click', function() {
            submenu.classList.toggle('open');
            const isOpen = submenu.classList.contains('open');
            if (chevron) chevron.style.transform = isOpen ? 'rotate(180deg)' : '';
            localStorage.setItem('reportsDropdown', isOpen ? 'open' : '');
        });
    })();

    // ── System dropdown ──
    (function() {
        const trigger = document.getElementById('systemDropdownTrigger');
        const submenu = document.getElementById('systemSubmenu');
        if (!trigger || !submenu) return;
        const chevron = trigger.querySelector('.nav-chevron');
        const stored = localStorage.getItem('systemDropdown');
        const open = stored === 'open' || {{ in_array($ap ?? '', ['settings','integrations','audit_logs']) ? 'true' : 'false' }};
        if (open) { submenu.classList.add('open'); if (chevron) chevron.style.transform = 'rotate(180deg)'; }
        trigger.addEventListener('click', function() {
            submenu.classList.toggle('open');
            const isOpen = submenu.classList.contains('open');
            if (chevron) chevron.style.transform = isOpen ? 'rotate(180deg)' : '';
            localStorage.setItem('systemDropdown', isOpen ? 'open' : '');
        });
    })();

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

    const flash = document.getElementById('flash-ok');
    if (flash) setTimeout(() => { flash.style.opacity='0'; flash.style.transition='opacity .5s'; setTimeout(()=>flash.remove(),500); }, 3500);

    // ── Modal helpers (available on all pages) ──
    function openModal(id) {
        document.getElementById(id).classList.add('open');
        document.body.style.overflow = 'hidden';
        if (id === 'newTicketModal') {
            const rInput  = document.getElementById('t-requester');
            const rId     = document.getElementById('t-requester-id');
            const rDept   = document.getElementById('t-requester-dept');
            // Only auto-fill if the field is empty (don't overwrite user edits)
            if (rInput && !rInput.value && window._authUser) {
                rInput.value = window._authUser.name;
                rId.value    = window._authUser.id;
                rDept.value  = window._authUser.department;
            }
        }
    }
    function closeModal(id) {
        document.getElementById(id).classList.remove('open');
        document.body.style.overflow = '';
        if (id === 'newTicketModal') {
            const rInput = document.getElementById('t-requester');
            if (rInput) rInput.value = '';
            const rId   = document.getElementById('t-requester-id');
            if (rId)    rId.value   = '';
            const rDept = document.getElementById('t-requester-dept');
            if (rDept)  rDept.value = '';
        }
    }
    document.addEventListener('keydown', e => { if(e.key==='Escape') closeModal('newTicketModal'); });

    // ── New Ticket modal helpers ──
    window.deptUsers   = @json($_deptUsers);
    window.allUsers    = @json($_allUsers);
    window._authUser   = @json(['id' => auth()->id(), 'name' => auth()->user()->name, 'department' => auth()->user()->department ?? '']);

    // Requester autocomplete
    function requesterSearch(val) {
        const dd = document.getElementById('requester-dropdown');
        const q  = val.trim().toLowerCase();
        const matches = q.length === 0
            ? window.allUsers.slice(0, 8)
            : window.allUsers.filter(u => u.name.toLowerCase().includes(q)).slice(0, 8);

        if (matches.length === 0) { dd.style.display = 'none'; return; }

        dd.innerHTML = '';
        matches.forEach(u => {
            const item = document.createElement('div');
            item.style.cssText = 'padding:.5rem .75rem;cursor:pointer;font-size:.82rem;border-bottom:1px solid var(--border);display:flex;justify-content:space-between;align-items:center';
            item.innerHTML = `<span style="color:var(--text);font-weight:500">${u.name}</span>`
                           + (u.department ? `<span style="color:var(--muted);font-size:.75rem">${u.department}</span>` : '');
            item.addEventListener('mousedown', () => {
                document.getElementById('t-requester').value    = u.name;
                document.getElementById('t-requester-id').value = u.id;
                document.getElementById('t-requester-dept').value = u.department || '';
                dd.style.display = 'none';
            });
            item.addEventListener('mouseover', () => item.style.background = 'var(--surface3, rgba(255,255,255,.06))');
            item.addEventListener('mouseout',  () => item.style.background = '');
            dd.appendChild(item);
        });
        dd.style.display = 'block';
    }

    // Clear requester_id if user types freely (not selecting from dropdown)
    document.getElementById('t-requester').addEventListener('input', function() {
        document.getElementById('t-requester-id').value   = '';
        document.getElementById('t-requester-dept').value = '';
    });

    // ── Generic custom dropdown ──
    const _cats  = ['IT Support','Network','Hardware','Software','Access & Permissions','HR','Facilities','Accounting','Security','Other'].map(v=>({val:v,label:v,sub:''}));
    const _types = ['Incident','Service Request','Question','Change Request'].map(v=>({val:v,label:v,sub:''}));
    const _depts = @json($_deptList).map(v=>({val:v,label:v,sub:''}));
    let   _assigneeItems = [];

    function cdBuild(dd, items, inputEl, hiddenEl, onPick) {
        dd.innerHTML = '';
        items.forEach(item => {
            const row = document.createElement('div');
            row.className = 'c-dd-item';
            row.innerHTML = `<span class="cd-label">${item.label}</span>`
                          + (item.sub ? `<span class="cd-sub">${item.sub}</span>` : '');
            row.addEventListener('mousedown', () => {
                inputEl.value  = item.label;
                hiddenEl.value = item.val;
                dd.style.display = 'none';
                if (onPick) onPick(item);
            });
            dd.appendChild(row);
        });
    }

    function cdOpen(inputEl, ddId, hiddenId, items, onPick) {
        const dd  = document.getElementById(ddId);
        const hid = document.getElementById(hiddenId);
        const q   = inputEl.value.trim().toLowerCase();
        const list = q ? items.filter(i => i.label.toLowerCase().includes(q)) : items;
        if (!list.length) { dd.style.display='none'; return; }
        cdBuild(dd, list, inputEl, hid, onPick);
        dd.style.display = 'block';
    }

    function cdFilter(inputEl, ddId, hiddenId, items, onPick) {
        document.getElementById(hiddenId).value = '';
        cdOpen(inputEl, ddId, hiddenId, items, onPick);
    }

    function cdClose(ddId) {
        const el = document.getElementById(ddId);
        if (el) el.style.display = 'none';
    }

    // When a department is picked in Assign To, populate the assignee dropdown
    function onDeptPick(item) {
        _assigneeItems = [{ val:'', label:'— No specific person —', sub:'' }]
            .concat((window.deptUsers[item.val] || []).map(n => ({ val:n, label:n, sub:'' })));
        const asnTxt = document.getElementById('nt-asn-txt');
        const asnHid = document.getElementById('nt-assignee');
        asnTxt.value = '';
        asnHid.value = '';
        asnTxt.placeholder = 'Search assignee…';
    }

    function selectPriority(el) {
        document.querySelectorAll('.priority-pill').forEach(p => p.classList.remove('selected'));
        el.classList.add('selected');
        document.getElementById('t-priority').value = el.dataset.val;
    }
    function showFileName(input) {
        const d = document.getElementById('fileNameDisplay');
        if (input.files.length) { d.textContent = '📎 ' + input.files[0].name; d.style.display='block'; }
    }
    document.getElementById('newTicketForm').addEventListener('submit', function(e) {
        // Validate custom-dropdown required fields
        if (!document.getElementById('t-category').value) {
            e.preventDefault();
            const inp = document.getElementById('t-cat-txt');
            inp.style.borderColor = '#f87171';
            inp.placeholder = 'Category is required';
            inp.focus();
            setTimeout(() => { inp.style.borderColor = ''; inp.placeholder = 'Search category…'; }, 2500);
            return;
        }
        const btn = document.getElementById('submitTicketBtn');
        btn.disabled = true;
        btn.innerHTML = '<span style="display:inline-block;width:14px;height:14px;border:2px solid rgba(255,255,255,.35);border-top-color:white;border-radius:50%;animation:spin .7s linear infinite;vertical-align:middle;margin-right:.35rem"></span>Submitting…';
    });
    @if($errors->any())
        document.addEventListener('DOMContentLoaded', () => openModal('newTicketModal'));
    @endif

    // ══ Flux Animations ══
    (function() {
        // Progress bar
        const bar = document.createElement('div');
        bar.id = 'flux-progress';
        document.body.prepend(bar);
        function showBar(pct) { bar.style.opacity='1'; bar.style.width=pct+'%'; }
        function doneBar()    { bar.style.width='100%'; setTimeout(()=>{ bar.style.opacity='0'; setTimeout(()=>bar.style.width='0',300); },200); }
        showBar(30); requestAnimationFrame(()=>showBar(72));
        window.addEventListener('load', doneBar);

        // Intercept nav links — page-exit transition + progress
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

        // Scroll reveal — auto-apply to panels and cards
        const revealObs = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) { entry.target.classList.add('visible'); revealObs.unobserve(entry.target); }
            });
        }, { threshold:0.06, rootMargin:'0px 0px -20px 0px' });

        document.querySelectorAll('.panel,.agent-card,.s-card,.stat-card,.settings-grid > *,.bottom-grid > *').forEach((el,i) => {
            if (!el.classList.contains('stat-card')) {
                el.classList.add('reveal');
                el.style.transitionDelay = Math.min(i * 0.07, 0.32) + 's';
            }
            revealObs.observe(el);
        });

        // Table row stagger
        const rows = document.querySelectorAll('.flux-table tbody tr');
        rows.forEach((tr,i) => {
            tr.style.cssText += ';opacity:0;transform:translateY(8px);transition:opacity .3s ease,transform .3s ease;transition-delay:' + Math.min(i*.04,.28)+'s';
        });
        if (rows.length) requestAnimationFrame(() => requestAnimationFrame(() => {
            rows.forEach(tr => { tr.style.opacity='1'; tr.style.transform='translateY(0)'; });
        }));
    })();
</script>
@stack('scripts')
<script>
/* ── Global ticket search ── */
const _gsCsrf = '{{ csrf_token() }}';
const _sMap = { open:'#34d399', progress:'#fbbf24', resolved:'#818cf8', closed:'#94a3b8' };
const _sLabel = { open:'Open', progress:'In Progress', resolved:'Resolved', closed:'Closed' };
let _gsTimer = null;

function globalSearch(q) {
    clearTimeout(_gsTimer);
    const drop = document.getElementById('globalSearchDrop');
    if (!q || q.trim().length < 1) { drop.style.display = 'none'; return; }
    _gsTimer = setTimeout(() => {
        fetch('/tickets/search?q=' + encodeURIComponent(q.trim()), {
            headers: { 'X-CSRF-TOKEN': _gsCsrf, 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        }).then(r => r.json()).then(results => {
            if (!results.length) {
                drop.innerHTML = '<div style="padding:.65rem 1rem;font-size:.8rem;color:var(--muted)">No tickets found.</div>';
            } else {
                drop.innerHTML = results.map(t => `
                    <a href="/tickets?open=${t.id}" style="display:flex;align-items:center;gap:.7rem;padding:.55rem 1rem;text-decoration:none;border-bottom:1px solid var(--border);transition:background .1s" onmouseover="this.style.background='var(--surface2)'" onmouseout="this.style.background=''">
                        <div style="width:28px;height:28px;background:linear-gradient(135deg,#4f46e5,#7c3aed);border-radius:.35rem;display:flex;align-items:center;justify-content:center;flex-shrink:0">
                            <i class="bi bi-ticket-perforated-fill" style="color:#fff;font-size:.65rem"></i>
                        </div>
                        <div style="min-width:0;flex:1">
                            <div style="font-size:.72rem;font-weight:700;color:#818cf8;font-family:monospace">${t.ticket_number}</div>
                            <div style="font-size:.8rem;color:var(--text);overflow:hidden;text-overflow:ellipsis;white-space:nowrap">${t.subject}</div>
                        </div>
                        <span style="font-size:.68rem;font-weight:600;color:${_sMap[t.status]||'#94a3b8'};background:rgba(0,0,0,.15);border-radius:9999px;padding:.1rem .45rem;flex-shrink:0">${_sLabel[t.status]||t.status}</span>
                    </a>`).join('');
            }
            drop.style.display = '';
        }).catch(() => { drop.style.display = 'none'; });
    }, 250);
}

// Close dropdown when clicking outside
document.addEventListener('click', function(e) {
    const drop = document.getElementById('globalSearchDrop');
    const wrap = document.getElementById('searchInput')?.closest('.topbar-search');
    if (drop && wrap && !wrap.contains(e.target)) drop.style.display = 'none';
});
</script>

{{-- ── Logout Confirmation Modal ── --}}
<div id="logoutModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.55);z-index:9999;align-items:center;justify-content:center;padding:1rem">
    <div style="background:var(--surface);border:1px solid var(--border);border-radius:1rem;width:100%;max-width:380px;box-shadow:0 20px 60px rgba(0,0,0,.35);overflow:hidden;animation:logoutFadeIn .2s ease">
        {{-- Header --}}
        <div style="padding:1.1rem 1.25rem;border-bottom:1px solid var(--border);display:flex;align-items:center;gap:.65rem">
            <div style="width:36px;height:36px;border-radius:.6rem;background:rgba(248,113,113,.12);display:flex;align-items:center;justify-content:center;flex-shrink:0">
                <i class="bi bi-box-arrow-right" style="color:#f87171;font-size:1rem"></i>
            </div>
            <div>
                <div style="font-size:.9rem;font-weight:700;color:var(--text)">Sign Out</div>
                <div style="font-size:.72rem;color:var(--muted)">Are you sure you want to sign out?</div>
            </div>
            <button onclick="closeLogoutModal()" style="margin-left:auto;background:none;border:none;color:var(--muted);cursor:pointer;font-size:.95rem;padding:.2rem;border-radius:.35rem;transition:color .15s" onmouseover="this.style.color='var(--text)'" onmouseout="this.style.color='var(--muted)'">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
        {{-- Body --}}
        <div style="padding:1.1rem 1.25rem">
            <p style="font-size:.835rem;color:var(--muted);margin:0">You will be returned to the login screen. Any unsaved changes may be lost.</p>
        </div>
        {{-- Footer --}}
        <div style="padding:.9rem 1.25rem;border-top:1px solid var(--border);display:flex;justify-content:flex-end;gap:.6rem">
            <button onclick="closeLogoutModal()" style="background:var(--surface2);border:1px solid var(--border);border-radius:.6rem;padding:.45rem 1rem;font-size:.8rem;font-weight:600;color:var(--muted);cursor:pointer;transition:color .15s" onmouseover="this.style.color='var(--text)'" onmouseout="this.style.color='var(--muted)'">
                Cancel
            </button>
            <form method="POST" action="{{ route('logout') }}" id="logoutForm" style="margin:0">
                @csrf
                <button type="submit" id="logoutConfirmBtn"
                    style="background:rgba(248,113,113,.15);color:#f87171;border:1px solid rgba(248,113,113,.3);border-radius:.6rem;padding:.45rem 1.1rem;font-size:.8rem;font-weight:600;cursor:pointer;display:inline-flex;align-items:center;gap:.4rem;transition:background .15s,opacity .15s;min-width:100px;justify-content:center"
                    onmouseover="this.style.background='rgba(248,113,113,.25)'" onmouseout="if(!this.dataset.loading)this.style.background='rgba(248,113,113,.15)'">
                    <i class="bi bi-box-arrow-right" id="logoutBtnIcon"></i>
                    <span id="logoutBtnText">Sign Out</span>
                </button>
            </form>
        </div>
    </div>
</div>

<style>
@keyframes logoutFadeIn { from { opacity:0;transform:scale(.96); } to { opacity:1;transform:scale(1); } }
</style>

<script>
function openLogoutModal() {
    const m = document.getElementById('logoutModal');
    m.style.display = 'flex';
    document.body.style.overflow = 'hidden';
}
function closeLogoutModal() {
    const m = document.getElementById('logoutModal');
    m.style.display = 'none';
    document.body.style.overflow = '';
}
document.getElementById('logoutModal').addEventListener('click', function(e) {
    if (e.target === this) closeLogoutModal();
});
document.getElementById('logoutForm').addEventListener('submit', function() {
    const btn  = document.getElementById('logoutConfirmBtn');
    const icon = document.getElementById('logoutBtnIcon');
    const text = document.getElementById('logoutBtnText');
    btn.dataset.loading = '1';
    btn.disabled = true;
    btn.style.opacity = '.75';
    btn.style.cursor = 'not-allowed';
    icon.className = 'bi bi-arrow-repeat spin-icon';
    text.textContent = 'Signing out...';
});
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closeLogoutModal();
});
</script>
<style>
@keyframes spin { to { transform:rotate(360deg); } }
.spin-icon { display:inline-block;animation:spin .7s linear infinite; }
</style>
</body>
</html>
