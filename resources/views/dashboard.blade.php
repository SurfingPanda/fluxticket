<!DOCTYPE html>
<html lang="en" id="html-root">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard — FluxTickets</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>

    <style>
        *, *::before, *::after { box-sizing: border-box; }

        /* ───── Tokens ───── */
        :root {
            --bg:        #0f172a;
            --surface:   #1e293b;
            --surface2:  #263348;
            --border:    #334155;
            --text:      #e2e8f0;
            --muted:     #94a3b8;
            --accent:    #6366f1;
            --accent2:   #7c3aed;
            --sidebar-w: 240px;
        }
        body:not(.dark) {
            --bg:       #e8edf6;
            --surface:  #ffffff;
            --surface2: #f0f4fb;
            --border:   #b8c6d8;
            --text:     #0f172a;
            --muted:    #475569;
        }

        html, body { height: 100%; margin: 0; overflow-x: hidden; }
        body {
            font-family: 'Segoe UI', system-ui, sans-serif;
            background: var(--bg);
            color: var(--text);
            transition: background .3s, color .3s;
            display: flex;
        }

        /* ───── Sidebar ───── */
        .sidebar {
            width: var(--sidebar-w);
            background: var(--surface);
            border-right: 1px solid var(--border);
            display: flex; flex-direction: column;
            position: fixed; top: 0; left: 0; bottom: 0;
            z-index: 100;
            transition: width .25s ease, background .3s, border-color .3s;
            overflow: hidden;
        }

        .sidebar-brand {
            padding: .65rem 1rem;
            border-bottom: 1px solid var(--border);
            display: flex; align-items: center; gap: .65rem;
            min-height: 52px; flex-shrink: 0;
        }
        .brand-icon {
            width: 40px; height: 40px; min-width: 40px; flex-shrink: 0;
            display: flex; align-items: center; justify-content: center;
        }
        .brand-icon img { width:40px; height:40px; object-fit:contain; filter:drop-shadow(0 2px 6px rgba(180,20,40,.45)); }
        .brand-name {
            font-size: 1rem; font-weight: 700;
            color: var(--text);
            letter-spacing: -.01em;
            white-space: nowrap; overflow: hidden;
            max-width: 140px;
            transition: opacity .2s, max-width .25s;
        }
        .sidebar-toggle {
            margin-left: auto; flex-shrink: 0;
            background: transparent; border: 1px solid var(--border);
            border-radius: .4rem; width: 26px; height: 26px; min-width: 26px;
            display: flex; align-items: center; justify-content: center;
            cursor: pointer; color: var(--muted); font-size: .8rem;
            transition: background .15s, color .15s;
        }
        .sidebar-toggle:hover { background: var(--surface2); color: var(--text); }

        .sidebar-section-label {
            font-size: .65rem; font-weight: 700;
            text-transform: uppercase; letter-spacing: .08em;
            color: var(--muted);
            padding: .65rem 1.25rem .3rem;
            white-space: nowrap;
            transition: opacity .2s;
        }

        .nav-item-link {
            display: flex; align-items: center; gap: .7rem;
            padding: .38rem .875rem;
            border-radius: .5rem;
            margin: .05rem .4rem;
            color: var(--muted);
            font-size: .82rem; font-weight: 500;
            text-decoration: none;
            transition: background .15s, color .15s;
            cursor: pointer;
            white-space: nowrap;
        }
        .nav-item-link:hover { background: var(--surface2); color: var(--text); }
        .nav-item-link.active {
            background: linear-gradient(135deg, rgba(99,102,241,.2), rgba(124,58,237,.15));
            color: #818cf8;
        }
        /* Tickets dropdown */
        .nav-dropdown-trigger { width:100%; background:none; border:none; text-align:left; font-family:inherit; cursor:pointer; }
        .nav-chevron { font-size:.68rem; margin-left:auto; flex-shrink:0; transition:transform .25s cubic-bezier(.4,0,.2,1); }
        .nav-submenu { overflow:hidden; max-height:0; transition:max-height .3s cubic-bezier(.4,0,.2,1); }
        .nav-submenu.open { max-height:420px; }
        .nav-sub-item { padding-left:2.1rem !important; font-size:.78rem; }
        .sidebar.mini .nav-chevron,.sidebar.mini .nav-submenu { display:none; }
        .nav-item-link .nav-icon { font-size: 1rem; flex-shrink: 0; }
        .nav-text { transition: opacity .2s; }
        .user-info { transition: opacity .2s; min-width: 0; flex: 1; }
        .nav-badge { transition: opacity .2s; }

        /* Minimized sidebar */
        .sidebar.mini { width: 60px; }
        .sidebar.mini .brand-name,
        .sidebar.mini .sidebar-section-label,
        .sidebar.mini .nav-text,
        .sidebar.mini .user-info,
        .sidebar.mini .nav-badge { opacity: 0; pointer-events: none; max-width: 0; overflow: hidden; }
        .sidebar.mini .nav-item-link { justify-content:center; padding:.38rem 0; margin:.05rem .4rem; gap:0; }
        .sidebar.mini .nav-icon { font-size:1.05rem; }
        .sidebar.mini .sidebar-brand { justify-content:center; gap:0; padding:.85rem 0; }
        .sidebar.mini .brand-icon { display:none; }
        .sidebar.mini .sidebar-toggle { margin:0; }
        .sidebar.mini .user-chip { display:none; }
        .sidebar.mini .mini-signout { display:flex !important; }
        .mini-signout { display:none; justify-content:center; align-items:center; padding:.4rem 0; }

        .sidebar-footer {
            margin-top: auto;
            padding: .5rem .75rem;
            border-top: 1px solid var(--border);
        }
        .user-chip {
            display: flex; align-items: center; gap: .65rem;
            padding: .5rem .65rem;
            border-radius: .6rem;
            background: var(--surface2);
        }
        .avatar {
            width: 32px; height: 32px;
            background: linear-gradient(135deg, #4f46e5, #7c3aed);
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: .75rem; font-weight: 700; color: white;
            flex-shrink: 0;
        }

        /* ───── Main layout ───── */
        .main-wrap {
            margin-left: var(--sidebar-w);
            flex: 1; display: flex; flex-direction: column;
            min-height: 100vh;
            transition: margin-left .25s ease;
            overflow-x: hidden;
        }
        .main-wrap.mini { margin-left: 60px; }

        /* ───── Topbar ───── */
        .topbar {
            position: sticky; top: 0; z-index: 50;
            background: var(--surface);
            border-bottom: 1px solid var(--border);
            padding: .75rem 1.75rem;
            display: flex; align-items: center; justify-content: space-between;
            gap: 1rem;
            transition: background .3s, border-color .3s;
        }
        .topbar-title { font-size: 1.05rem; font-weight: 700; color: var(--text); }
        .topbar-search {
            display: flex; align-items: center; gap: .5rem;
            background: var(--surface2);
            border: 1px solid var(--border);
            border-radius: .6rem;
            padding: .4rem .85rem;
            width: 240px;
            transition: border-color .2s;
        }
        .topbar-search:focus-within { border-color: var(--accent); }
        .topbar-search input {
            border: none; background: transparent; outline: none;
            font-size: .825rem; color: var(--text); width: 100%;
        }
        .topbar-search input::placeholder { color: var(--muted); }

        /* ───── Content ───── */
        .content { padding: 1.75rem; flex: 1; }

        /* ───── Stat cards ───── */
        .stat-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1rem;
            margin-bottom: 1.5rem;
        }
        @media (max-width: 1100px) { .stat-grid { grid-template-columns: repeat(2,1fr); } }
        @media (max-width: 600px)  { .stat-grid { grid-template-columns: 1fr; } }

        .stat-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 1rem;
            padding: 1.25rem 1.35rem;
            display: flex; flex-direction: column; gap: .75rem;
            transition: background .3s, border-color .25s, transform .28s cubic-bezier(.22,.68,0,1.3), box-shadow .28s;
            cursor: default;
        }
        .stat-card:hover { transform: translateY(-6px) scale(1.03); }
        /* Per-card coloured glow */
        .stat-card:nth-child(1):hover { border-color:rgba(129,140,248,.55); box-shadow:0 14px 40px rgba(99,102,241,.22),0 0 0 1px rgba(129,140,248,.2); }
        .stat-card:nth-child(2):hover { border-color:rgba(251,191,36,.5);   box-shadow:0 14px 40px rgba(251,191,36,.2), 0 0 0 1px rgba(251,191,36,.2); }
        .stat-card:nth-child(3):hover { border-color:rgba(52,211,153,.5);   box-shadow:0 14px 40px rgba(52,211,153,.2), 0 0 0 1px rgba(52,211,153,.2); }
        .stat-card:nth-child(4):hover { border-color:rgba(248,113,113,.5);  box-shadow:0 14px 40px rgba(248,113,113,.2),0 0 0 1px rgba(248,113,113,.2); }
        /* Icon bounce on hover */
        .stat-card:hover .stat-icon-wrap { transform: scale(1.2) rotate(-8deg); }

        .stat-icon-wrap {
            width: 40px; height: 40px; border-radius: .75rem;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.1rem;
            transition: transform .28s cubic-bezier(.22,.68,0,1.4);
        }
        .stat-label { font-size: .72rem; font-weight: 700; text-transform: uppercase; letter-spacing: .06em; color: var(--muted); }
        .stat-value { font-size: 1.8rem; font-weight: 800; color: var(--text); line-height: 1; }
        .stat-trend { font-size: .75rem; display: flex; align-items: center; gap: .25rem; }
        .trend-up   { color: #34d399; }
        .trend-down { color: #f87171; }

        /* ───── Panels ───── */
        .panel {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 1rem;
            transition: background .3s, border-color .3s;
        }
        .panel-header {
            padding: 1rem 1.35rem;
            border-bottom: 1px solid var(--border);
            display: flex; align-items: center; justify-content: space-between;
        }
        .panel-title { font-size: .875rem; font-weight: 700; color: var(--text); }
        .panel-body { padding: 0; }

        /* ───── Table ───── */
        .flux-table { width: 100%; border-collapse: collapse; font-size: .825rem; }
        .flux-table thead th {
            padding: .65rem 1.35rem;
            font-size: .68rem; font-weight: 700;
            text-transform: uppercase; letter-spacing: .06em;
            color: var(--muted);
            border-bottom: 1px solid var(--border);
            white-space: nowrap;
        }
        .flux-table tbody tr {
            border-bottom: 1px solid var(--border);
            transition: background .15s;
            cursor: default;
        }
        .flux-table tbody tr:last-child { border-bottom: none; }
        .flux-table tbody tr:hover { background: var(--surface2); }
        .flux-table tbody td { padding: .8rem 1.35rem; color: var(--text); vertical-align: middle; }

        /* ───── Badges ───── */
        .badge-status {
            display: inline-flex; align-items: center; gap: .3rem;
            padding: .22rem .65rem; border-radius: 9999px;
            font-size: .7rem; font-weight: 600; white-space: nowrap;
        }
        .badge-status::before { content:''; width:6px; height:6px; border-radius:50%; flex-shrink:0; }
        .s-open     { background:rgba(99,102,241,.15); color:#818cf8; }
        .s-open::before     { background:#818cf8; }
        .s-progress { background:rgba(251,191,36,.12); color:#fbbf24; }
        .s-progress::before { background:#fbbf24; }
        .s-resolved { background:rgba(52,211,153,.12); color:#34d399; }
        .s-resolved::before { background:#34d399; }
        .s-closed   { background:rgba(148,163,184,.12); color:#94a3b8; }
        .s-closed::before   { background:#94a3b8; }

        .badge-priority {
            display: inline-block;
            padding: .18rem .55rem; border-radius: .35rem;
            font-size: .68rem; font-weight: 700;
        }
        .p-high   { background:rgba(248,113,113,.15); color:#f87171; }
        .p-medium { background:rgba(251,191,36,.12);  color:#fbbf24; }
        .p-low    { background:rgba(52,211,153,.12);  color:#34d399; }

        /* ───── Bottom grid ───── */
        .bottom-grid {
            display: grid;
            grid-template-columns: 1fr 320px;
            gap: 1rem;
            margin-top: 1rem;
        }
        @media (max-width: 900px) { .bottom-grid { grid-template-columns: 1fr; } }

        /* ───── Activity feed ───── */
        .activity-item {
            display: flex; gap: .85rem; align-items: flex-start;
            padding: .8rem 1.35rem;
            border-bottom: 1px solid var(--border);
        }
        .activity-item:last-child { border-bottom: none; }
        .activity-dot {
            width: 30px; height: 30px; flex-shrink: 0;
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: .75rem; margin-top: .1rem;
        }

        /* ───── Dark toggle ───── */
        .dark-toggle {
            width: 2.5rem; height: 1.35rem;
            background: #6366f1; border-radius: 9999px;
            position: relative; cursor: pointer;
            transition: background .3s; border: none; outline: none;
        }
        .dark-toggle::after {
            content: '';
            position: absolute; top: 2px; left: 2px;
            width: 1rem; height: 1rem;
            background: white; border-radius: 50%;
            transition: transform .3s;
            box-shadow: 0 1px 3px rgba(0,0,0,.2);
        }
        body:not(.dark) .dark-toggle { background: #cbd5e1; }
        body:not(.dark) .dark-toggle::after { transform: translateX(0); }
        body.dark       .dark-toggle::after { transform: translateX(1.15rem); }

        /* ───── New ticket btn ───── */
        .btn-new {
            background: linear-gradient(135deg, #4f46e5, #7c3aed);
            border: none; border-radius: .6rem;
            color: white; font-weight: 600; font-size: .8rem;
            padding: .45rem 1rem;
            display: flex; align-items: center; gap: .4rem;
            transition: opacity .2s, transform .15s;
            box-shadow: 0 3px 12px rgba(99,102,241,.35);
            cursor: pointer;
        }
        .btn-new:hover { opacity: .9; transform: translateY(-1px); }

        a { text-decoration: none !important; }

        /* ───── Modal ───── */
        .flux-modal-backdrop {
            position: fixed; inset: 0; z-index: 999;
            background: rgba(0,0,0,.55);
            backdrop-filter: blur(4px);
            display: flex; align-items: center; justify-content: center;
            padding: 1rem;
            opacity: 0; pointer-events: none;
            transition: opacity .2s;
        }
        .flux-modal-backdrop.open { opacity: 1; pointer-events: all; }

        .flux-modal {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 1.25rem;
            width: 100%; max-width: 580px;
            box-shadow: 0 30px 80px rgba(0,0,0,.45);
            transform: translateY(16px) scale(.98);
            transition: transform .25s, opacity .25s;
            opacity: 0;
            max-height: 90vh;
            display: flex; flex-direction: column;
        }
        .flux-modal-backdrop.open .flux-modal {
            transform: translateY(0) scale(1);
            opacity: 1;
        }

        .flux-modal-header {
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid var(--border);
            display: flex; align-items: center; justify-content: space-between;
            flex-shrink: 0;
        }
        .flux-modal-body {
            padding: 1.5rem;
            overflow-y: auto;
            flex: 1;
        }
        .flux-modal-footer {
            padding: 1rem 1.5rem;
            border-top: 1px solid var(--border);
            display: flex; align-items: center; justify-content: flex-end; gap: .65rem;
            flex-shrink: 0;
        }

        /* modal form fields */
        .m-field { margin-bottom: 1.1rem; }
        .m-label {
            display: block;
            font-size: .7rem; font-weight: 700;
            text-transform: uppercase; letter-spacing: .07em;
            color: var(--muted); margin-bottom: .4rem;
        }
        .m-input, .m-select, .m-textarea {
            width: 100%;
            background: var(--surface2);
            border: 1px solid var(--border);
            border-radius: .6rem;
            color: var(--text);
            font-size: .875rem;
            font-family: inherit;
            padding: .55rem .85rem;
            outline: none;
            transition: border-color .2s, box-shadow .2s;
        }
        .m-input::placeholder, .m-textarea::placeholder { color: var(--muted); }
        .m-input:focus, .m-select:focus, .m-textarea:focus {
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(99,102,241,.2);
        }
        .m-select option { background: var(--surface); color: var(--text); }
        .m-textarea { resize: vertical; min-height: 90px; }

        .m-row { display: grid; grid-template-columns: 1fr 1fr; gap: .85rem; }

        /* priority selector */
        .priority-pills { display: flex; gap: .5rem; flex-wrap: wrap; }
        .priority-pill {
            padding: .3rem .9rem; border-radius: 9999px;
            font-size: .75rem; font-weight: 600; cursor: pointer;
            border: 1.5px solid transparent;
            transition: all .15s;
        }
        .priority-pill[data-val="low"]    { background:rgba(52,211,153,.1);  color:#34d399; }
        .priority-pill[data-val="medium"] { background:rgba(251,191,36,.1);  color:#fbbf24; }
        .priority-pill[data-val="high"]   { background:rgba(248,113,113,.1); color:#f87171; }
        .priority-pill[data-val="low"].selected    { border-color:#34d399; box-shadow:0 0 0 3px rgba(52,211,153,.18); }
        .priority-pill[data-val="medium"].selected { border-color:#fbbf24; box-shadow:0 0 0 3px rgba(251,191,36,.18); }
        .priority-pill[data-val="high"].selected   { border-color:#f87171; box-shadow:0 0 0 3px rgba(248,113,113,.18); }

        /* file drop zone */
        .drop-zone {
            border: 1.5px dashed var(--border);
            border-radius: .75rem;
            padding: 1.25rem;
            text-align: center;
            cursor: pointer;
            transition: border-color .2s, background .2s;
        }
        .drop-zone:hover { border-color: var(--accent); background: rgba(99,102,241,.05); }

        /* cancel btn */
        .btn-cancel {
            background: var(--surface2); border: 1px solid var(--border);
            border-radius: .6rem; color: var(--muted);
            font-size: .875rem; font-weight: 600; padding: .5rem 1.1rem;
            cursor: pointer; transition: background .15s, color .15s;
        }
        .btn-cancel:hover { background: var(--border); color: var(--text); }

        .btn-submit {
            background: linear-gradient(135deg, #4f46e5, #7c3aed);
            border: none; border-radius: .6rem; color: white;
            font-size: .875rem; font-weight: 600; padding: .5rem 1.4rem;
            cursor: pointer; box-shadow: 0 3px 12px rgba(99,102,241,.35);
            display: flex; align-items: center; gap: .4rem;
            transition: opacity .2s, transform .15s;
        }
        .btn-submit:hover { opacity: .9; transform: translateY(-1px); }

        /* scrollbar */
        ::-webkit-scrollbar { width: 5px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: var(--border); border-radius: 9999px; }
        @keyframes spin { to { transform: rotate(360deg); } }

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
            .bottom-grid { grid-template-columns:1fr; }
            .flux-modal { border-radius:.875rem; max-width:calc(100vw - 1.5rem); }
            .flux-modal-body { padding:1rem 1.1rem; }
            .flux-modal-header { padding:.875rem 1rem; }
            .flux-modal-footer { padding:.75rem 1rem; }
            .m-row { grid-template-columns:1fr !important; }
        }
        @media (max-width:480px) {
            .content { padding:.65rem; }
            .stat-grid { grid-template-columns:1fr 1fr; }
        }
    </style>
</head>
<body class="dark">

{{-- Mobile sidebar overlay --}}
<div class="sidebar-overlay" id="sidebarOverlay" onclick="closeMobileSidebar()"></div>

{{-- ══════════════════════════════
     SIDEBAR
══════════════════════════════ --}}
<aside class="sidebar">

    {{-- Brand --}}
    <div class="sidebar-brand">
        <div class="brand-icon">
            <img src="{{ asset('logo/Gemini_Generated_Image_1w1sif1w1sif1w1s-removebg-preview.png') }}" alt="FluxTickets Logo">
        </div>
        <span class="brand-name">FluxTickets</span>
        <button class="sidebar-toggle" id="sidebarToggle" title="Toggle sidebar"><i class="bi bi-layout-sidebar-reverse"></i></button>
    </div>

    {{-- Nav --}}
    <div style="flex:1;overflow-y:auto;overflow-x:hidden;padding-bottom:.5rem">
        <div class="sidebar-section-label">Main</div>

        <a class="nav-item-link active" href="{{ route('dashboard') }}">
            <i class="bi bi-grid-fill nav-icon"></i><span class="nav-text">Dashboard</span>
        </a>
        <button class="nav-item-link nav-dropdown-trigger" id="ticketsDropdownTrigger" type="button">
            <i class="bi bi-ticket-perforated nav-icon"></i>
            <span class="nav-text">All Tickets</span>
            <i class="bi bi-chevron-down nav-chevron nav-badge"></i>
        </button>
        <div class="nav-submenu" id="ticketsSubmenu">
            <a class="nav-item-link nav-sub-item" href="{{ route('tickets.index') }}">
                <i class="bi bi-ticket nav-icon" style="font-size:.85rem"></i><span class="nav-text">All Tickets</span>
            </a>
            <a class="nav-item-link nav-sub-item" href="{{ route('queue') }}">
                <i class="bi bi-clock-history nav-icon" style="font-size:.85rem"></i><span class="nav-text">My Queue</span>
            </a>
            <a class="nav-item-link nav-sub-item" href="{{ route('tickets.index',['type'=>'incident']) }}">
                <i class="bi bi-exclamation-triangle nav-icon" style="font-size:.85rem"></i><span class="nav-text">Incident Tickets</span>
            </a>
            <a class="nav-item-link nav-sub-item" href="{{ route('tickets.index',['type'=>'service_request']) }}">
                <i class="bi bi-tools nav-icon" style="font-size:.85rem"></i><span class="nav-text">Service Requests</span>
            </a>
            <a class="nav-item-link nav-sub-item" href="{{ route('tickets.index',['type'=>'question']) }}">
                <i class="bi bi-question-circle nav-icon" style="font-size:.85rem"></i><span class="nav-text">Question</span>
            </a>
            <a class="nav-item-link nav-sub-item" href="{{ route('tickets.index',['type'=>'change_request']) }}">
                <i class="bi bi-arrow-repeat nav-icon" style="font-size:.85rem"></i><span class="nav-text">Change Request</span>
            </a>
            <a class="nav-item-link nav-sub-item" href="{{ route('tickets.index',['submitted'=>1]) }}">
                <i class="bi bi-send nav-icon" style="font-size:.85rem"></i><span class="nav-text">Submitted Requests</span>
            </a>
        </div>

        @php
        $_du    = auth()->user();
        $_disSA = $_du && $_du->role === 'super_admin';
        $_dperm = $_disSA ? array_fill_keys(['agents','reports','knowledge_read','knowledge_write','settings'], true)
                          : $_du->effectivePageAccess();
        $_dShowAgents  = $_disSA || !empty($_dperm['agents']);
        $_dShowReports = $_disSA || !empty($_dperm['reports']);
        $_dShowKb      = $_disSA || !empty($_dperm['knowledge_read']);
        $_dShowSettings= $_disSA || !empty($_dperm['settings']);
        $_dShowManage  = $_dShowAgents || $_dShowReports || $_dShowKb || $_disSA;
        @endphp

        @if($_dShowManage)
        <div class="sidebar-section-label">Manage</div>
        @if($_dShowAgents)
        <a class="nav-item-link" href="{{ route('agents.index') }}">
            <i class="bi bi-people nav-icon"></i><span class="nav-text">Agents</span>
        </a>
        @endif
        @if($_dShowReports)
        <a class="nav-item-link" href="{{ route('reports.index') }}">
            <i class="bi bi-bar-chart-line nav-icon"></i><span class="nav-text">Reports</span>
        </a>
        @endif
        @if($_dShowKb)
        <a class="nav-item-link" href="{{ route('knowledge.index') }}">
            <i class="bi bi-book nav-icon"></i><span class="nav-text">Knowledge Base</span>
        </a>
        @endif
        @if($_disSA)
        <a class="nav-item-link" href="{{ route('roles.index') }}">
            <i class="bi bi-shield-lock nav-icon"></i><span class="nav-text">Role Access & Permission</span>
        </a>
        @endif
        @endif

        @if($_dShowSettings)
        <div class="sidebar-section-label">System</div>
        <a class="nav-item-link" href="{{ route('settings.index') }}">
            <i class="bi bi-gear nav-icon"></i><span class="nav-text">Settings</span>
        </a>
        @endif
    </div>

    {{-- User --}}
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

{{-- ══════════════════════════════
     MAIN CONTENT
══════════════════════════════ --}}
<div class="main-wrap">

    {{-- Topbar --}}
    <header class="topbar">
        <div class="d-flex align-items-center gap-2">
            <button class="mob-hamburger" id="mobHamburger" aria-label="Menu"><i class="bi bi-list"></i></button>
            <div>
                <div class="topbar-title">Dashboard</div>
                <div style="font-size:.72rem;color:var(--muted)">{{ now()->format('l, F j, Y') }}</div>
            </div>
        </div>

        <div class="d-flex align-items-center gap-3">
            {{-- Search --}}
            <div class="topbar-search d-none d-md-flex">
                <i class="bi bi-search" style="color:var(--muted);font-size:.8rem"></i>
                <input type="text" placeholder="Search tickets…">
            </div>

            {{-- Dark toggle --}}
            <div class="d-flex align-items-center gap-2">
                <i class="bi bi-sun" style="color:#fbbf24;font-size:.8rem"></i>
                <button class="dark-toggle" id="darkToggle" aria-label="Toggle dark mode"></button>
                <i class="bi bi-moon-stars" style="color:#818cf8;font-size:.8rem"></i>
            </div>

            {{-- Notifications --}}
            @include('partials.notif-bell')

            {{-- New ticket --}}
            <button class="btn-new" onclick="openModal()">
                <i class="bi bi-plus-lg"></i><span class="btn-new-text"> New Ticket</span>
            </button>
        </div>
    </header>

    {{-- Page body --}}
    <main class="content">

        {{-- ── Flash / Validation alerts ── --}}
        @if(session('success'))
            <div id="flash-success" style="display:flex;align-items:center;gap:.65rem;background:rgba(52,211,153,.12);border:1px solid rgba(52,211,153,.3);color:#34d399;padding:.65rem 1rem;border-radius:.75rem;font-size:.85rem;font-weight:500;margin-bottom:1.25rem">
                <i class="bi bi-check-circle-fill"></i> {{ session('success') }}
            </div>
        @endif
        @if($errors->any())
            <div style="background:rgba(248,113,113,.1);border:1px solid rgba(248,113,113,.3);color:#f87171;padding:.65rem 1rem;border-radius:.75rem;font-size:.85rem;font-weight:500;margin-bottom:1.25rem">
                <i class="bi bi-exclamation-triangle-fill me-1"></i> {{ $errors->first() }}
            </div>
        @endif

        {{-- ── Greeting ── --}}
        <div class="mb-4">
            <h5 style="font-weight:700;font-size:1.1rem;margin-bottom:.2rem">
                @php
                    $h = now()->hour;
                    $greet = $h >= 18 ? 'evening' : ($h >= 12 ? 'afternoon' : ($h >= 5 ? 'morning' : 'evening'));
                @endphp
                Good {{ $greet }},
                {{ auth()->user()->name ?? 'there' }} 👋
            </h5>
            <p style="color:var(--muted);font-size:.825rem;margin:0">Here's what's happening with your tickets today.</p>
        </div>

        {{-- ── Stat cards ── --}}
        <div class="stat-grid">

            <div class="stat-card">
                <div class="d-flex align-items-center justify-content-between">
                    <span class="stat-label">Open</span>
                    <div class="stat-icon-wrap" style="background:rgba(99,102,241,.15)">
                        <i class="bi bi-ticket-perforated" style="color:#818cf8"></i>
                    </div>
                </div>
                <div class="stat-value">{{ $stats['open'] }}</div>
                <div class="stat-trend" style="color:var(--muted)">Total open tickets</div>
            </div>

            <div class="stat-card">
                <div class="d-flex align-items-center justify-content-between">
                    <span class="stat-label">In Progress</span>
                    <div class="stat-icon-wrap" style="background:rgba(251,191,36,.12)">
                        <i class="bi bi-arrow-repeat" style="color:#fbbf24"></i>
                    </div>
                </div>
                <div class="stat-value">{{ $stats['progress'] }}</div>
                <div class="stat-trend" style="color:var(--muted)">Being worked on</div>
            </div>

            <div class="stat-card">
                <div class="d-flex align-items-center justify-content-between">
                    <span class="stat-label">Resolved</span>
                    <div class="stat-icon-wrap" style="background:rgba(52,211,153,.12)">
                        <i class="bi bi-check-circle" style="color:#34d399"></i>
                    </div>
                </div>
                <div class="stat-value">{{ $stats['resolved'] }}</div>
                <div class="stat-trend" style="color:var(--muted)">Successfully closed</div>
            </div>

            <div class="stat-card">
                <div class="d-flex align-items-center justify-content-between">
                    <span class="stat-label">Total</span>
                    <div class="stat-icon-wrap" style="background:rgba(248,113,113,.12)">
                        <i class="bi bi-stack" style="color:#f87171"></i>
                    </div>
                </div>
                <div class="stat-value">{{ array_sum($stats) }}</div>
                <div class="stat-trend" style="color:var(--muted)">All time tickets</div>
            </div>

        </div>

        {{-- ── Recent Tickets Table ── --}}
        @php
            $statusMap   = ['open'=>['s-open','Open'],'progress'=>['s-progress','In Progress'],'resolved'=>['s-resolved','Resolved'],'closed'=>['s-closed','Closed']];
            $priorityMap = ['high'=>'p-high','medium'=>'p-medium','low'=>'p-low'];
        @endphp
        <div class="panel mb-0">
            <div class="panel-header">
                <span class="panel-title"><i class="bi bi-ticket-perforated me-2" style="color:var(--accent)"></i>Recent Tickets</span>
                <a href="{{ route('tickets.index') }}" style="font-size:.775rem;color:#818cf8;font-weight:600">View all <i class="bi bi-arrow-right"></i></a>
            </div>
            <div class="panel-body" style="overflow-x:hidden">
                @if($tickets->isEmpty())
                    <div style="padding:3rem;text-align:center;color:var(--muted)">
                        <i class="bi bi-ticket-perforated" style="font-size:2rem;opacity:.3;display:block;margin-bottom:.75rem"></i>
                        <div style="font-size:.875rem;font-weight:500">No tickets yet</div>
                        <div style="font-size:.78rem;margin-top:.25rem">Click <b>New Ticket</b> to submit your first request.</div>
                    </div>
                @else
                <table class="flux-table">
                    <thead>
                        <tr>
                            <th>Ticket ID</th>
                            <th>Subject</th>
                            <th>Requester</th>
                            <th>Priority</th>
                            <th>Status</th>
                            <th>Created</th>
                            <th>Last Updated</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($tickets as $t)
                        <tr>
                            <td><span style="font-size:.72rem;font-weight:700;color:#818cf8;font-family:monospace">{{ $t->ticket_number }}</span></td>
                            <td style="max-width:220px">
                                <div style="font-weight:500;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">{{ $t->subject }}</div>
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div style="width:24px;height:24px;background:linear-gradient(135deg,#4f46e5,#7c3aed);border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:.6rem;font-weight:700;color:white;flex-shrink:0">
                                        {{ strtoupper(substr($t->user->name ?? '?', 0, 1)) }}
                                    </div>
                                    <span>{{ $t->user->name ?? '—' }}</span>
                                </div>
                            </td>
                            <td>
                                <span class="badge-priority {{ $priorityMap[$t->priority] ?? 'p-low' }}">
                                    {{ ucfirst($t->priority) }}
                                </span>
                            </td>
                            <td>
                                <span class="badge-status {{ $statusMap[$t->status][0] ?? 's-open' }}">
                                    {{ $statusMap[$t->status][1] ?? 'Open' }}
                                </span>
                            </td>
                            <td style="color:var(--muted);font-size:.78rem;white-space:nowrap">{{ $t->created_at->diffForHumans() }}</td>
                            <td style="color:var(--muted);font-size:.78rem;white-space:nowrap">{{ $t->updated_at->diffForHumans() }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                @endif
            </div>
        </div>

        {{-- ── Bottom row ── --}}
        <div class="bottom-grid">

            {{-- SLA Overview --}}
            <div class="panel">
                <div class="panel-header">
                    <span class="panel-title"><i class="bi bi-bar-chart-line me-2" style="color:var(--accent)"></i>SLA Overview</span>
                    <span style="font-size:.72rem;color:var(--muted)">Live</span>
                </div>
                <div style="padding:1.25rem 1.35rem;display:flex;flex-direction:column;gap:1rem">
                    @php
                    use App\Models\Ticket;
                    $allT    = Ticket::whereNotNull('sla_due_at')->get();
                    $total   = $allT->count();

                    $metCnt      = $allT->filter(fn($t) => $t->sla_status === 'met')->count();
                    $okCnt       = $allT->filter(fn($t) => $t->sla_status === 'ok')->count();
                    $warnCnt     = $allT->filter(fn($t) => $t->sla_status === 'warning')->count();
                    $breachCnt   = $allT->filter(fn($t) => $t->sla_status === 'breached')->count();

                    $pct = fn($n) => $total > 0 ? round(($n / $total) * 100) : 0;

                    $slaRows = [
                        ['label'=>'Met (Resolved within SLA)',   'n'=>$metCnt,    'color'=>'#818cf8'],
                        ['label'=>'On Track (Open, within SLA)', 'n'=>$okCnt,     'color'=>'#34d399'],
                        ['label'=>'At Risk (≤25% time left)',    'n'=>$warnCnt,   'color'=>'#fbbf24'],
                        ['label'=>'Breached (Overdue)',          'n'=>$breachCnt, 'color'=>'#f87171'],
                    ];
                    @endphp

                    @if($total === 0)
                        <div style="text-align:center;color:var(--muted);font-size:.8rem;padding:.5rem 0">No SLA data yet. Create tickets to see stats.</div>
                    @else
                        @foreach($slaRows as $s)
                        <div>
                            <div class="d-flex justify-content-between mb-1" style="font-size:.78rem">
                                <span style="color:var(--text);font-weight:500">{{ $s['label'] }}</span>
                                <span style="color:{{ $s['color'] }};font-weight:700">{{ $s['n'] }} / {{ $total }} ({{ $pct($s['n']) }}%)</span>
                            </div>
                            <div style="height:6px;background:var(--surface2);border-radius:9999px;overflow:hidden">
                                <div style="height:100%;width:{{ $pct($s['n']) }}%;background:{{ $s['color'] }};border-radius:9999px;transition:width .6s ease"></div>
                            </div>
                        </div>
                        @endforeach

                        {{-- SLA target lines per priority --}}
                        <div style="border-top:1px solid var(--border);padding-top:.75rem;margin-top:.25rem">
                            <div style="font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--muted);margin-bottom:.5rem">SLA Targets by Priority</div>
                            @foreach(['high'=>'1-2 days','medium'=>'2-3 days','low'=>'1-7 days'] as $p => $target)
                            @php
                                $pc = $allT->filter(fn($t)=>$t->priority===$p);
                                $pcMet = $pc->filter(fn($t)=>in_array($t->sla_status,['met','ok']))->count();
                                $pcTotal = $pc->count();
                            @endphp
                            <div class="d-flex align-items-center gap-2 mb-1" style="font-size:.75rem">
                                <span class="badge-priority p-{{ $p }}" style="min-width:52px;text-align:center">{{ ucfirst($p) }}</span>
                                <span style="color:var(--muted)">{{ $target }}</span>
                                <span style="margin-left:auto;color:var(--text);font-weight:600">{{ $pcMet }}/{{ $pcTotal }}</span>
                            </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            {{-- Activity Feed --}}
            <div class="panel">
                <div class="panel-header">
                    <span class="panel-title"><i class="bi bi-activity me-2" style="color:var(--accent)"></i>Recent Activity</span>
                </div>
                <div style="max-height:310px;overflow-y:auto;">
                    @forelse($recentActivity as $a)
                    @php
                        $isRoute = $a->type === 'route_event';
                        $icon    = $isRoute ? 'bi-arrow-left-right' : 'bi-chat-left-text-fill';
                        $color   = $isRoute ? 'rgba(251,191,36,.12)' : 'rgba(99,102,241,.15)';
                        $ic      = $isRoute ? '#fbbf24' : '#818cf8';
                        $num     = $a->ticket->ticket_number ?? '—';
                        $who     = $a->user->name ?? 'Someone';
                        $action  = $isRoute ? "routed ticket <b>{$num}</b>" : "added a note on <b>{$num}</b>";
                    @endphp
                    <div class="activity-item">
                        <div class="activity-dot" style="background:{{ $color }}">
                            <i class="bi {{ $icon }}" style="color:{{ $ic }}"></i>
                        </div>
                        <div style="flex:1;min-width:0">
                            <div style="font-size:.78rem;color:var(--text);line-height:1.4">
                                <b>{{ $who }}</b> {!! $action !!}
                            </div>
                            <div style="font-size:.7rem;color:var(--muted);margin-top:.15rem">{{ $a->created_at->diffForHumans() }}</div>
                        </div>
                    </div>
                    @empty
                    <div style="padding:1.5rem;text-align:center;color:var(--muted);font-size:.8rem">No recent activity.</div>
                    @endforelse
                </div>
            </div>

        </div>

    </main>
</div>

{{-- ══════════════════════════════
     NEW TICKET MODAL
══════════════════════════════ --}}
<div class="flux-modal-backdrop" id="ticketModal" onclick="handleBackdropClick(event)">
    <div class="flux-modal" role="dialog" aria-modal="true" aria-labelledby="modalTitle">

        {{-- Header --}}
        <div class="flux-modal-header">
            <div class="d-flex align-items-center gap-2">
                <div style="width:32px;height:32px;background:linear-gradient(135deg,#4f46e5,#7c3aed);border-radius:.5rem;display:flex;align-items:center;justify-content:center">
                    <i class="bi bi-ticket-perforated-fill text-white" style="font-size:.85rem"></i>
                </div>
                <div>
                    <div id="modalTitle" style="font-size:.95rem;font-weight:700;color:var(--text)">New Ticket</div>
                    <div style="font-size:.7rem;color:var(--muted)">Fill in the details below to submit a request</div>
                </div>
            </div>
            <button onclick="closeModal()"
                style="background:var(--surface2);border:1px solid var(--border);border-radius:.5rem;width:30px;height:30px;display:flex;align-items:center;justify-content:center;cursor:pointer;color:var(--muted);font-size:.85rem;transition:background .15s">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        {{-- Body --}}
        <div class="flux-modal-body">
            <form id="newTicketForm" method="POST" action="{{ route('tickets.store') }}" enctype="multipart/form-data">
                @csrf

                {{-- Subject --}}
                <div class="m-field">
                    <label class="m-label" for="t-subject">Subject <span style="color:#f87171">*</span></label>
                    <input class="m-input" id="t-subject" name="subject" type="text"
                           placeholder="Briefly describe the issue…" required>
                </div>

                {{-- Category & Type --}}
                <div class="m-row m-field">
                    <div>
                        <label class="m-label" for="t-category">Category <span style="color:#f87171">*</span></label>
                        <select class="m-select" id="t-category" name="category" required>
                            <option value="" disabled selected>Select category…</option>
                            <option>IT Support</option>
                            <option>Network</option>
                            <option>Hardware</option>
                            <option>Software</option>
                            <option>Access & Permissions</option>
                            <option>HR</option>
                            <option>Facilities</option>
                            <option>Other</option>
                        </select>
                    </div>
                    <div>
                        <label class="m-label" for="t-type">Request Type</label>
                        <select class="m-select" id="t-type" name="type">
                            <option value="" disabled selected>Select type…</option>
                            <option>Incident</option>
                            <option>Service Request</option>
                            <option>Question</option>
                            <option>Change Request</option>
                        </select>
                    </div>
                </div>

                {{-- Priority --}}
                <div class="m-field">
                    <label class="m-label">Priority <span style="color:#f87171">*</span></label>
                    <div class="priority-pills">
                        <span class="priority-pill selected" data-val="low"    onclick="selectPriority(this)">Low</span>
                        <span class="priority-pill"          data-val="medium" onclick="selectPriority(this)">Medium</span>
                        <span class="priority-pill"          data-val="high"   onclick="selectPriority(this)">High</span>
                    </div>
                    <input type="hidden" id="t-priority" name="priority" value="low">
                </div>

                {{-- Assigned To — Department + Person --}}
                <div class="m-field">
                    <label class="m-label">Assign To <span style="color:var(--muted);font-weight:400;text-transform:none;font-size:.7rem">(optional)</span></label>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:.65rem">
                        @php
                        $deptList = auth()->user()->isSuperAdmin()
                            ? \App\Models\SystemSetting::allDepartments()
                            : auth()->user()->effectiveRoutingDepts();
                        @endphp
                        <select class="m-select" id="t-dept" onchange="loadAssignDeptUsers(this.value,'t-assignee')">
                            <option value="" selected>— Department —</option>
                            @foreach($deptList as $d)<option value="{{ $d }}">{{ $d }}</option>@endforeach
                        </select>
                        <select class="m-select" id="t-assignee" name="assignee" disabled>
                            <option value="">Select department first…</option>
                        </select>
                    </div>
                </div>

                {{-- Description --}}
                <div class="m-field">
                    <label class="m-label" for="t-desc">Description <span style="color:#f87171">*</span></label>
                    <textarea class="m-textarea" id="t-desc" name="description"
                              placeholder="Provide as much detail as possible — steps to reproduce, error messages, affected device, etc." required></textarea>
                </div>

                {{-- Attachment --}}
                <div class="m-field" style="margin-bottom:0">
                    <label class="m-label">Attachment</label>
                    <div class="drop-zone" onclick="document.getElementById('t-file').click()">
                        <i class="bi bi-cloud-upload" style="font-size:1.4rem;color:var(--muted)"></i>
                        <div style="font-size:.8rem;color:var(--muted);margin-top:.35rem">
                            Click to upload or drag & drop a file
                        </div>
                        <div style="font-size:.7rem;color:var(--border);margin-top:.2rem">PNG, JPG, PDF, ZIP — max 10 MB</div>
                        <div id="fileNameDisplay" style="font-size:.75rem;color:#818cf8;margin-top:.3rem;display:none"></div>
                    </div>
                    <input type="file" id="t-file" name="attachment" style="display:none"
                           accept=".png,.jpg,.jpeg,.pdf,.zip,.txt,.docx"
                           onchange="showFileName(this)">
                </div>

            </form>
        </div>

        {{-- Footer --}}
        <div class="flux-modal-footer">
            <button class="btn-cancel" onclick="closeModal()">Cancel</button>
            <button type="submit" form="newTicketForm" class="btn-submit" id="submitTicketBtn">
                <i class="bi bi-send"></i> Submit Ticket
            </button>
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

    // ── Tickets dropdown ──
    (function() {
        const trigger = document.getElementById('ticketsDropdownTrigger');
        const submenu = document.getElementById('ticketsSubmenu');
        const chevron = trigger ? trigger.querySelector('.nav-chevron') : null;
        const open = localStorage.getItem('ticketsDropdown') !== 'closed';
        if (open && submenu) { submenu.classList.add('open'); if (chevron) chevron.style.transform = 'rotate(180deg)'; }
        if (trigger) trigger.addEventListener('click', function() {
            submenu.classList.toggle('open');
            const isOpen = submenu.classList.contains('open');
            if (chevron) chevron.style.transform = isOpen ? 'rotate(180deg)' : '';
            localStorage.setItem('ticketsDropdown', isOpen ? 'open' : 'closed');
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

    // ── Modal ──
    const backdrop = document.getElementById('ticketModal');

    function openModal() {
        backdrop.classList.add('open');
        document.body.style.overflow = 'hidden';
        setTimeout(() => document.getElementById('t-subject').focus(), 50);
    }

    function closeModal() {
        backdrop.classList.remove('open');
        document.body.style.overflow = '';
    }

    function handleBackdropClick(e) {
        if (e.target === backdrop) closeModal();
    }

    document.addEventListener('keydown', e => {
        if (e.key === 'Escape') closeModal();
    });

    // ── Priority pills ──
    function selectPriority(el) {
        document.querySelectorAll('.priority-pill').forEach(p => p.classList.remove('selected'));
        el.classList.add('selected');
        document.getElementById('t-priority').value = el.dataset.val;
    }

    // ── File name display ──
    function showFileName(input) {
        const display = document.getElementById('fileNameDisplay');
        if (input.files.length) {
            display.textContent = '📎 ' + input.files[0].name;
            display.style.display = 'block';
        }
    }

    // ── Assign-to dept → person dropdown ──
    const deptUsers = @json($deptUsers->map(fn($u) => $u->pluck('name')));
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
    document.getElementById('newTicketForm').addEventListener('submit', function () {
        const btn = document.getElementById('submitTicketBtn');
        btn.disabled = true;
        btn.innerHTML = '<span style="display:inline-block;width:14px;height:14px;border:2px solid rgba(255,255,255,.35);border-top-color:white;border-radius:50%;animation:spin .7s linear infinite;vertical-align:middle;margin-right:.35rem"></span>Submitting…';
    });

    // Auto-open modal if there were validation errors on ticket form
    @if($errors->any())
        document.addEventListener('DOMContentLoaded', () => openModal());
    @endif

    // Auto-dismiss flash message
    const flash = document.getElementById('flash-success');
    if (flash) setTimeout(() => { flash.style.transition='opacity .5s'; flash.style.opacity='0'; setTimeout(()=>flash.remove(),500); }, 4000);

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

        document.querySelectorAll('.panel,.bottom-grid > *').forEach((el,i) => {
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
</body>
</html>
