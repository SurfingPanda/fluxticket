<!DOCTYPE html>
<html lang="en" id="html-root">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FluxTickets — Smart Service Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <style>
        *, *::before, *::after { box-sizing: border-box; }
        :root { --bg:#0f172a; --surface:#1e293b; --surface2:#263348; --border:#334155; --text:#e2e8f0; --muted:#94a3b8; --accent:#6366f1; --accent2:#7c3aed; }
        body:not(.dark) { --bg:#e8edf6; --surface:#ffffff; --surface2:#f0f4fb; --border:#b8c6d8; --text:#0f172a; --muted:#475569; }
        html, body { margin:0; padding:0; min-height:100%; font-family:'Segoe UI',system-ui,sans-serif; background:var(--bg); color:var(--text); transition:background .3s,color .3s; scroll-behavior:smooth; }

        /* ── Scrollbar ── */
        ::-webkit-scrollbar { width:5px; } ::-webkit-scrollbar-track { background:transparent; } ::-webkit-scrollbar-thumb { background:var(--border); border-radius:9999px; }

        /* ── Animations ── */
        @keyframes fadeUp   { from{opacity:0;transform:translateY(32px)} to{opacity:1;transform:translateY(0)} }
        @keyframes shimmer  { from{background-position:-400% 0} to{background-position:400% 0} }
        @keyframes float    { 0%,100%{transform:translateY(0)} 50%{transform:translateY(-12px)} }
        @keyframes pulse-ring { 0%{transform:scale(1);opacity:.6} 100%{transform:scale(1.55);opacity:0} }
        @keyframes spin     { to{transform:rotate(360deg)} }

        .fade-up { animation:fadeUp .7s cubic-bezier(.22,.68,0,1.2) both; }
        .fade-up-1 { animation-delay:.1s; }
        .fade-up-2 { animation-delay:.2s; }
        .fade-up-3 { animation-delay:.3s; }
        .fade-up-4 { animation-delay:.4s; }
        .fade-up-5 { animation-delay:.5s; }

        /* ── Navbar ── */
        .navbar { position:fixed; top:0; left:0; right:0; z-index:100; display:flex; align-items:center; justify-content:space-between; padding:.875rem 2rem; background:rgba(15,23,42,.85); backdrop-filter:blur(16px); border-bottom:1px solid rgba(51,65,85,.5); transition:background .3s; }
        body:not(.dark) .navbar { background:rgba(232,237,246,.85); }
        .nav-logo { display:flex; align-items:center; gap:.6rem; text-decoration:none; }
        .nav-logo img { width:52px; height:52px; object-fit:contain; filter:drop-shadow(0 2px 8px rgba(180,20,40,.55)); transition:transform .3s ease; }
        .nav-logo:hover img { transform:scale(1.08) rotate(-4deg); }
        .nav-logo-text { font-size:1.1rem; font-weight:800; color:var(--text); letter-spacing:-.02em; }
        .nav-actions { display:flex; align-items:center; gap:.75rem; }
        .btn-login { background:linear-gradient(135deg,#4f46e5,#7c3aed); border:none; border-radius:.6rem; color:white; font-size:.875rem; font-weight:600; padding:.5rem 1.35rem; cursor:pointer; text-decoration:none; display:inline-flex; align-items:center; gap:.4rem; transition:opacity .2s,transform .15s; box-shadow:0 3px 14px rgba(99,102,241,.4); }
        .btn-login:hover { opacity:.9; transform:translateY(-1px); color:white; }
        @keyframes spin { to { transform:rotate(360deg); } }
        .dark-toggle { width:2.4rem; height:1.3rem; background:#6366f1; border-radius:9999px; position:relative; cursor:pointer; transition:background .3s; border:none; outline:none; flex-shrink:0; }
        .dark-toggle::after { content:''; position:absolute; top:2px; left:2px; width:.95rem; height:.95rem; background:white; border-radius:50%; transition:transform .3s; box-shadow:0 1px 3px rgba(0,0,0,.2); }
        body:not(.dark) .dark-toggle { background:#cbd5e1; }
        body.dark .dark-toggle::after { transform:translateX(1.1rem); }

        /* ── Hero ── */
        .hero { min-height:100vh; display:flex; align-items:center; justify-content:center; position:relative; overflow:hidden; padding:7rem 1.5rem 4rem; }
        .hero-bg { position:absolute; inset:0; z-index:0; }
        .hero-orb { position:absolute; border-radius:50%; filter:blur(80px); opacity:.18; pointer-events:none; }
        .hero-orb-1 { width:600px; height:600px; background:radial-gradient(circle,#4f46e5,transparent); top:-10%; left:-5%; }
        .hero-orb-2 { width:500px; height:500px; background:radial-gradient(circle,#7c3aed,transparent); bottom:-10%; right:-5%; }
        .hero-orb-3 { width:300px; height:300px; background:radial-gradient(circle,#dc2626,transparent); top:40%; left:40%; opacity:.1; }
        .hero-grid { position:absolute; inset:0; background-image:linear-gradient(rgba(99,102,241,.06) 1px,transparent 1px),linear-gradient(90deg,rgba(99,102,241,.06) 1px,transparent 1px); background-size:48px 48px; }
        .hero-inner { position:relative; z-index:1; max-width:900px; margin:0 auto; text-align:center; }
        .hero-badge { display:inline-flex; align-items:center; gap:.5rem; background:rgba(99,102,241,.12); border:1px solid rgba(99,102,241,.3); border-radius:9999px; padding:.35rem 1rem; font-size:.78rem; font-weight:600; color:#818cf8; margin-bottom:1.75rem; }
        .hero-badge i { font-size:.8rem; }
        .hero-title { font-size:clamp(2.4rem,6vw,4.2rem); font-weight:900; line-height:1.1; letter-spacing:-.03em; margin-bottom:1.25rem; }
        .hero-title .grad { background:linear-gradient(135deg,#818cf8,#a78bfa,#c084fc); -webkit-background-clip:text; -webkit-text-fill-color:transparent; background-clip:text; }
        .hero-sub { font-size:clamp(1rem,2vw,1.2rem); color:var(--muted); max-width:620px; margin:0 auto 2.5rem; line-height:1.7; }
        .hero-cta { display:flex; align-items:center; justify-content:center; gap:1rem; flex-wrap:wrap; }
        .btn-hero-primary { background:linear-gradient(135deg,#4f46e5,#7c3aed); border:none; border-radius:.75rem; color:white; font-size:1rem; font-weight:700; padding:.8rem 2rem; cursor:pointer; text-decoration:none; display:inline-flex; align-items:center; gap:.5rem; transition:opacity .2s,transform .2s; box-shadow:0 6px 24px rgba(99,102,241,.45); }
        .btn-hero-primary:hover { opacity:.92; transform:translateY(-2px); color:white; box-shadow:0 10px 32px rgba(99,102,241,.55); }
        .btn-hero-ghost { background:var(--surface); border:1px solid var(--border); border-radius:.75rem; color:var(--text); font-size:1rem; font-weight:600; padding:.8rem 1.75rem; cursor:pointer; text-decoration:none; display:inline-flex; align-items:center; gap:.5rem; transition:background .2s,transform .2s; }
        .btn-hero-ghost:hover { background:var(--surface2); transform:translateY(-2px); color:var(--text); }
        .hero-logo { width:180px; height:180px; object-fit:contain; filter:drop-shadow(0 12px 48px rgba(180,20,40,.65)) drop-shadow(0 0 72px rgba(99,102,241,.35)); animation:float 4s ease-in-out infinite; margin-bottom:2rem; }

        /* ── Stats strip ── */
        .stats-strip { background:var(--surface); border-top:1px solid var(--border); border-bottom:1px solid var(--border); padding:2rem 1.5rem; }
        .stats-grid { max-width:1100px; margin:0 auto; display:grid; grid-template-columns:repeat(4,1fr); gap:1.5rem; text-align:center; }
        .stat-num { font-size:2rem; font-weight:900; background:linear-gradient(135deg,#818cf8,#a78bfa); -webkit-background-clip:text; -webkit-text-fill-color:transparent; background-clip:text; line-height:1; margin-bottom:.35rem; }
        .stat-label { font-size:.8rem; font-weight:600; color:var(--muted); text-transform:uppercase; letter-spacing:.06em; }
        @media(max-width:640px) { .stats-grid { grid-template-columns:repeat(2,1fr); } }

        /* ── Section ── */
        .section { padding:5rem 1.5rem; }
        .section-inner { max-width:1100px; margin:0 auto; }
        .section-label { font-size:.72rem; font-weight:700; text-transform:uppercase; letter-spacing:.1em; color:#818cf8; margin-bottom:.75rem; }
        .section-title { font-size:clamp(1.75rem,4vw,2.6rem); font-weight:900; letter-spacing:-.03em; line-height:1.15; margin-bottom:1rem; }
        .section-sub { font-size:1rem; color:var(--muted); max-width:560px; line-height:1.7; }

        /* ── Features ── */
        .features-grid { display:grid; grid-template-columns:repeat(3,1fr); gap:1.25rem; margin-top:3rem; }
        @media(max-width:900px) { .features-grid { grid-template-columns:repeat(2,1fr); } }
        @media(max-width:560px) { .features-grid { grid-template-columns:1fr; } }
        .feat-card { background:var(--surface); border:1px solid var(--border); border-radius:1.1rem; padding:1.75rem 1.5rem; transition:transform .2s,box-shadow .2s,border-color .2s; position:relative; overflow:hidden; }
        .feat-card::before { content:''; position:absolute; inset:0; background:linear-gradient(135deg,rgba(99,102,241,.06),transparent); opacity:0; transition:opacity .3s; }
        .feat-card:hover { transform:translateY(-4px); box-shadow:0 12px 36px rgba(0,0,0,.2); border-color:rgba(99,102,241,.35); }
        .feat-card:hover::before { opacity:1; }
        .feat-icon { width:44px; height:44px; border-radius:.75rem; display:flex; align-items:center; justify-content:center; font-size:1.2rem; margin-bottom:1.1rem; flex-shrink:0; }
        .feat-title { font-size:.95rem; font-weight:700; color:var(--text); margin-bottom:.5rem; }
        .feat-desc { font-size:.83rem; color:var(--muted); line-height:1.65; }

        /* ── How it works ── */
        .how-grid { display:grid; grid-template-columns:repeat(4,1fr); gap:1rem; margin-top:3rem; position:relative; }
        .how-grid::before { content:''; position:absolute; top:28px; left:12.5%; right:12.5%; height:2px; background:linear-gradient(90deg,#4f46e5,#7c3aed,#4f46e5); opacity:.3; z-index:0; }
        @media(max-width:768px) { .how-grid { grid-template-columns:repeat(2,1fr); } .how-grid::before { display:none; } }
        @media(max-width:480px) { .how-grid { grid-template-columns:1fr; } }
        .how-step { text-align:center; position:relative; z-index:1; }
        .how-num { width:56px; height:56px; border-radius:50%; background:linear-gradient(135deg,#4f46e5,#7c3aed); display:flex; align-items:center; justify-content:center; font-size:1.1rem; font-weight:800; color:white; margin:0 auto 1rem; box-shadow:0 6px 20px rgba(99,102,241,.4); position:relative; }
        .how-num::after { content:''; position:absolute; inset:-4px; border-radius:50%; border:2px solid rgba(99,102,241,.3); animation:pulse-ring 2.5s ease-out infinite; }
        .how-step:nth-child(2) .how-num::after { animation-delay:.6s; }
        .how-step:nth-child(3) .how-num::after { animation-delay:1.2s; }
        .how-step:nth-child(4) .how-num::after { animation-delay:1.8s; }
        .how-title { font-size:.9rem; font-weight:700; color:var(--text); margin-bottom:.4rem; }
        .how-desc { font-size:.8rem; color:var(--muted); line-height:1.6; }

        /* ── Ticket types ── */
        .types-grid { display:grid; grid-template-columns:repeat(2,1fr); gap:1rem; margin-top:2.5rem; }
        @media(max-width:560px) { .types-grid { grid-template-columns:1fr; } }
        .type-card { background:var(--surface2); border:1px solid var(--border); border-radius:.9rem; padding:1.25rem 1.5rem; display:flex; align-items:flex-start; gap:1rem; transition:border-color .2s,transform .2s; }
        .type-card:hover { border-color:rgba(99,102,241,.4); transform:translateY(-2px); }
        .type-icon { width:38px; height:38px; border-radius:.65rem; display:flex; align-items:center; justify-content:center; font-size:1rem; flex-shrink:0; }
        .type-info .type-name { font-size:.88rem; font-weight:700; color:var(--text); margin-bottom:.25rem; }
        .type-info .type-desc { font-size:.78rem; color:var(--muted); line-height:1.55; }

        /* ── KPI Cards ── */
        .kpi-grid { }
        .kpi-card { background:var(--surface); border:1px solid var(--border); border-radius:1.1rem; padding:1.5rem 1.25rem; text-align:center; transition:transform .2s,box-shadow .2s,border-color .2s; position:relative; overflow:hidden; }
        .kpi-card::before { content:''; position:absolute; inset:0; background:var(--accent-bg); opacity:0; transition:opacity .3s; }
        .kpi-card:hover { transform:translateY(-4px); box-shadow:0 12px 36px rgba(0,0,0,.2); border-color:var(--accent-c); }
        .kpi-card:hover::before { opacity:1; }
        .kpi-icon { font-size:1.5rem; color:var(--accent-c); margin-bottom:.6rem; position:relative; }
        .kpi-num { font-size:2.2rem; font-weight:900; color:var(--accent-c); line-height:1; margin-bottom:.4rem; position:relative; }
        .kpi-label { font-size:.72rem; font-weight:700; text-transform:uppercase; letter-spacing:.07em; color:var(--muted); position:relative; }
        @media(max-width:640px) { .kpi-grid { grid-template-columns:repeat(2,1fr) !important; } }
        @media(max-width:380px)  { .kpi-grid { grid-template-columns:1fr !important; } }

        /* ── Chart panels ── */
        .chart-panel { background:var(--surface); border:1px solid var(--border); border-radius:1.1rem; padding:1.5rem; transition:border-color .2s; }
        .chart-panel:hover { border-color:rgba(99,102,241,.35); }
        .chart-panel-title { font-size:.82rem; font-weight:700; color:var(--text); display:flex; align-items:center; gap:.5rem; }
        @media(max-width:700px) { .charts-row { grid-template-columns:1fr !important; } }

        /* ── Footer ── */
        .footer { background:var(--surface); border-top:1px solid var(--border); padding:2rem 1.5rem; }
        .footer-inner { max-width:1100px; margin:0 auto; display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:1rem; }
        .footer-logo { display:flex; align-items:center; gap:.5rem; }
        .footer-logo img { width:28px; height:28px; object-fit:contain; filter:drop-shadow(0 1px 4px rgba(180,20,40,.4)); }
        .footer-logo span { font-size:.88rem; font-weight:700; color:var(--text); }
        .footer-copy { font-size:.78rem; color:var(--muted); }
        .footer-links { display:flex; gap:1.5rem; }
        .footer-links a { font-size:.8rem; color:var(--muted); text-decoration:none; transition:color .15s; }
        .footer-links a:hover { color:#818cf8; }

        /* ── Responsive nav ── */
        @media(max-width:480px) {
            .navbar { padding:.75rem 1rem; }
            .nav-logo-text { font-size:.95rem; }
            .hero { padding:6rem 1rem 3rem; }
            .section { padding:3.5rem 1rem; }
        }
    </style>
</head>
<body class="dark">

{{-- ── Navbar ── --}}
<nav class="navbar">
    <a href="{{ route('landing') }}" class="nav-logo">
        <img src="{{ asset('logo/Gemini_Generated_Image_1w1sif1w1sif1w1s-removebg-preview.png') }}" alt="FluxTickets">
        <span class="nav-logo-text">FluxTickets</span>
    </a>
    <div class="nav-actions">
        <div style="display:flex;align-items:center;gap:.4rem">
            <i class="bi bi-sun" style="color:#fbbf24;font-size:.8rem"></i>
            <button class="dark-toggle" id="darkToggle" aria-label="Toggle dark mode"></button>
            <i class="bi bi-moon-stars" style="color:#818cf8;font-size:.8rem"></i>
        </div>
        <a href="{{ route('login') }}" class="btn-login" id="signInBtn" onclick="handleSignIn(event)">
            <span id="signInIcon"><i class="bi bi-box-arrow-in-right"></i></span>
            <span id="signInSpinner" style="display:none;width:14px;height:14px;border:2px solid rgba(255,255,255,.35);border-top-color:white;border-radius:50%;animation:spin .6s linear infinite;flex-shrink:0"></span>
            <span id="signInText"> Sign In</span>
        </a>
    </div>
</nav>

{{-- ── Hero ── --}}
<section class="hero">
    <div class="hero-bg">
        <div class="hero-grid"></div>
        <div class="hero-orb hero-orb-1"></div>
        <div class="hero-orb hero-orb-2"></div>
        <div class="hero-orb hero-orb-3"></div>
    </div>
    <div class="hero-inner">
        <div class="fade-up">
            <img src="{{ asset('logo/Gemini_Generated_Image_1w1sif1w1sif1w1s-removebg-preview.png') }}" alt="FluxTickets" class="hero-logo">
        </div>
        <div class="hero-badge fade-up fade-up-1">
            <i class="bi bi-lightning-charge-fill"></i> Smart Service Management Platform
        </div>
        <h1 class="hero-title fade-up fade-up-2">
            Resolve tickets faster.<br>
            <span class="grad">Work smarter.</span>
        </h1>
        <p class="hero-sub fade-up fade-up-3">
            A centralized helpdesk platform built for modern teams — track, assign, and resolve service requests with speed, clarity, and full SLA visibility.
        </p>
        <div class="hero-cta fade-up fade-up-4">
            <a href="{{ route('login') }}" class="btn-hero-primary">
                <i class="bi bi-box-arrow-in-right"></i> Get Started
            </a>
            <a href="#features" class="btn-hero-ghost">
                <i class="bi bi-grid-1x2"></i> Explore Features
            </a>
        </div>
    </div>
</section>

{{-- ── Stats ── --}}
<div class="stats-strip">
    <div class="stats-grid">
        <div>
            <div class="stat-num">99%</div>
            <div class="stat-label">SLA Compliance</div>
        </div>
        <div>
            <div class="stat-num">4</div>
            <div class="stat-label">Ticket Types</div>
        </div>
        <div>
            <div class="stat-num">Real-time</div>
            <div class="stat-label">Analytics & Reports</div>
        </div>
        <div>
            <div class="stat-num">Multi</div>
            <div class="stat-label">Department Routing</div>
        </div>
    </div>
</div>

{{-- ── Features ── --}}
<section class="section" id="features">
    <div class="section-inner">
        <div class="section-label">Features</div>
        <h2 class="section-title">Everything your team needs</h2>
        <p class="section-sub">Purpose-built tools to manage every stage of your support workflow — from ticket creation to resolution.</p>

        <div class="features-grid">
            <div class="feat-card fade-up fade-up-1">
                <div class="feat-icon" style="background:rgba(99,102,241,.15);color:#818cf8"><i class="bi bi-lightning-charge-fill"></i></div>
                <div class="feat-title">Fast Ticket Resolution</div>
                <div class="feat-desc">Assign, prioritize, and close tickets in record time with smart workflows and clear ownership.</div>
            </div>
            <div class="feat-card fade-up fade-up-2">
                <div class="feat-icon" style="background:rgba(52,211,153,.12);color:#34d399"><i class="bi bi-clock-history"></i></div>
                <div class="feat-title">SLA Tracking</div>
                <div class="feat-desc">Set deadlines per priority, monitor compliance in real-time, and get alerted before breaches happen.</div>
            </div>
            <div class="feat-card fade-up fade-up-3">
                <div class="feat-icon" style="background:rgba(251,191,36,.12);color:#fbbf24"><i class="bi bi-arrow-left-right"></i></div>
                <div class="feat-title">Smart Routing</div>
                <div class="feat-desc">Automatically or manually route tickets to the right department or agent with full audit trails.</div>
            </div>
            <div class="feat-card fade-up fade-up-1">
                <div class="feat-icon" style="background:rgba(59,130,246,.12);color:#60a5fa"><i class="bi bi-people-fill"></i></div>
                <div class="feat-title">Team Collaboration</div>
                <div class="feat-desc">Keep everyone aligned with shared queues, internal notes, file attachments, and activity logs.</div>
            </div>
            <div class="feat-card fade-up fade-up-2">
                <div class="feat-icon" style="background:rgba(236,72,153,.12);color:#f472b6"><i class="bi bi-bar-chart-line-fill"></i></div>
                <div class="feat-title">Real-time Analytics</div>
                <div class="feat-desc">Monitor agent performance, resolution rates, and ticket trends with rich, live dashboards.</div>
            </div>
            <div class="feat-card fade-up fade-up-3">
                <div class="feat-icon" style="background:rgba(124,58,237,.12);color:#a78bfa"><i class="bi bi-book-half"></i></div>
                <div class="feat-title">Knowledge Base</div>
                <div class="feat-desc">Build and link a library of solutions to recurring issues so agents resolve faster every time.</div>
            </div>
        </div>
    </div>
</section>

{{-- ── How it works ── --}}
<section class="section" style="background:var(--surface);border-top:1px solid var(--border);border-bottom:1px solid var(--border)" id="how">
    <div class="section-inner">
        <div class="section-label">How It Works</div>
        <h2 class="section-title">Four steps to resolution</h2>
        <div class="how-grid">
            <div class="how-step fade-up fade-up-1">
                <div class="how-num">1</div>
                <div class="how-title">Submit</div>
                <div class="how-desc">Users submit tickets with type, priority, and description in seconds.</div>
            </div>
            <div class="how-step fade-up fade-up-2">
                <div class="how-num">2</div>
                <div class="how-title">Assign</div>
                <div class="how-desc">Tickets are routed to the right agent or department automatically.</div>
            </div>
            <div class="how-step fade-up fade-up-3">
                <div class="how-num">3</div>
                <div class="how-title">Resolve</div>
                <div class="how-desc">Agents collaborate, add notes, and resolve within the SLA window.</div>
            </div>
            <div class="how-step fade-up fade-up-4">
                <div class="how-num">4</div>
                <div class="how-title">Report</div>
                <div class="how-desc">Leadership reviews analytics and improves team performance continuously.</div>
            </div>
        </div>
    </div>
</section>

{{-- ── Ticket Types ── --}}
<section class="section" id="types">
    <div class="section-inner">
        <div class="section-label">Ticket Types</div>
        <h2 class="section-title">Handle every request type</h2>
        <p class="section-sub">FluxTickets supports four structured ticket types — ensuring the right process for every issue.</p>
        <div class="types-grid">
            <div class="type-card">
                <div class="type-icon" style="background:rgba(248,113,113,.15);color:#f87171"><i class="bi bi-exclamation-triangle-fill"></i></div>
                <div class="type-info">
                    <div class="type-name">Incident</div>
                    <div class="type-desc">Report unexpected outages or failures that impact operations and need immediate attention.</div>
                </div>
            </div>
            <div class="type-card">
                <div class="type-icon" style="background:rgba(99,102,241,.15);color:#818cf8"><i class="bi bi-tools"></i></div>
                <div class="type-info">
                    <div class="type-name">Service Request</div>
                    <div class="type-desc">Request new services, access provisioning, or standard operational tasks.</div>
                </div>
            </div>
            <div class="type-card">
                <div class="type-icon" style="background:rgba(52,211,153,.12);color:#34d399"><i class="bi bi-question-circle-fill"></i></div>
                <div class="type-info">
                    <div class="type-name">Question</div>
                    <div class="type-desc">Ask the support team anything — linked to the knowledge base for faster answers.</div>
                </div>
            </div>
            <div class="type-card">
                <div class="type-icon" style="background:rgba(251,191,36,.12);color:#fbbf24"><i class="bi bi-arrow-repeat"></i></div>
                <div class="type-info">
                    <div class="type-name">Change Request</div>
                    <div class="type-desc">Propose and track changes to systems, processes, or configurations with full history.</div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ── Analytics / Reports ── --}}
<section class="section" style="padding-top:0" id="analytics">
    <div class="section-inner">
        <div class="section-label">Reports & Analytics</div>
        <h2 class="section-title">Live system snapshot</h2>
        <p class="section-sub">Real-time metrics from the FluxTickets platform — updated every time the page loads.</p>

        {{-- KPI cards --}}
        @php $total = max($landingStats['total'], 1); @endphp
        <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:1rem;margin-top:2.5rem" class="kpi-grid">
            <div class="kpi-card" style="--accent-c:#818cf8;--accent-bg:rgba(99,102,241,.12)">
                <div class="kpi-icon"><i class="bi bi-ticket-perforated-fill"></i></div>
                <div class="kpi-num" data-target="{{ $landingStats['total'] }}">0</div>
                <div class="kpi-label">Total Tickets</div>
            </div>
            <div class="kpi-card" style="--accent-c:#34d399;--accent-bg:rgba(52,211,153,.1)">
                <div class="kpi-icon"><i class="bi bi-check-circle-fill"></i></div>
                <div class="kpi-num" data-target="{{ $landingStats['res_rate'] }}" data-suffix="%">0</div>
                <div class="kpi-label">Resolution Rate</div>
            </div>
            <div class="kpi-card" style="--accent-c:#22d3ee;--accent-bg:rgba(34,211,238,.1)">
                <div class="kpi-icon"><i class="bi bi-clock-fill"></i></div>
                <div class="kpi-num" data-target="{{ $landingStats['sla_rate'] }}" data-suffix="%">0</div>
                <div class="kpi-label">SLA Compliance</div>
            </div>
            <div class="kpi-card" style="--accent-c:#f472b6;--accent-bg:rgba(244,114,182,.1)">
                <div class="kpi-icon"><i class="bi bi-people-fill"></i></div>
                <div class="kpi-num" data-target="{{ $landingStats['agents'] }}">0</div>
                <div class="kpi-label">Active Agents</div>
            </div>
        </div>

        {{-- Charts row --}}
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:1.25rem;margin-top:1.25rem" class="charts-row">

            {{-- Status breakdown --}}
            <div class="chart-panel">
                <div class="chart-panel-title"><i class="bi bi-bar-chart-fill" style="color:#818cf8"></i> Tickets by Status</div>
                @php
                    $statuses = [
                        ['label'=>'Open',       'val'=>$landingStats['open'],     'color'=>'#818cf8', 'bg'=>'rgba(99,102,241,.15)'],
                        ['label'=>'In Progress','val'=>$landingStats['progress'], 'color'=>'#fbbf24', 'bg'=>'rgba(251,191,36,.12)'],
                        ['label'=>'Resolved',   'val'=>$landingStats['resolved'], 'color'=>'#34d399', 'bg'=>'rgba(52,211,153,.12)'],
                        ['label'=>'Closed',     'val'=>$landingStats['closed'],   'color'=>'#94a3b8', 'bg'=>'rgba(148,163,184,.12)'],
                    ];
                @endphp
                <div style="display:flex;flex-direction:column;gap:.85rem;margin-top:1.1rem">
                    @foreach($statuses as $s)
                    @php $pct = $total > 0 ? round($s['val'] / $total * 100) : 0; @endphp
                    <div>
                        <div style="display:flex;justify-content:space-between;font-size:.78rem;margin-bottom:.35rem">
                            <span style="color:var(--text);font-weight:600">{{ $s['label'] }}</span>
                            <span style="color:var(--muted)">{{ $s['val'] }} <span style="color:{{ $s['color'] }};font-weight:700">({{ $pct }}%)</span></span>
                        </div>
                        <div style="background:var(--surface2);border-radius:9999px;height:8px;overflow:hidden">
                            <div class="bar-fill" style="height:100%;width:0;background:{{ $s['color'] }};border-radius:9999px;box-shadow:0 0 8px {{ $s['color'] }}55;transition:width 1.2s cubic-bezier(.22,.68,0,1.2)" data-width="{{ $pct }}"></div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- Priority & Type --}}
            <div style="display:flex;flex-direction:column;gap:1.25rem">
                {{-- Priority donut-style --}}
                <div class="chart-panel" style="flex:1">
                    <div class="chart-panel-title"><i class="bi bi-flag-fill" style="color:#f87171"></i> Tickets by Priority</div>
                    @php
                        $priorities = [
                            ['label'=>'High',   'val'=>$landingStats['high'],   'color'=>'#f87171'],
                            ['label'=>'Medium', 'val'=>$landingStats['medium'], 'color'=>'#fbbf24'],
                            ['label'=>'Low',    'val'=>$landingStats['low'],    'color'=>'#34d399'],
                        ];
                    @endphp
                    <div style="display:flex;gap:.65rem;margin-top:1rem;flex-wrap:wrap">
                        @foreach($priorities as $p)
                        @php $pct = $total > 0 ? round($p['val'] / $total * 100) : 0; @endphp
                        <div style="flex:1;min-width:70px;background:var(--surface2);border:1px solid var(--border);border-radius:.75rem;padding:.85rem .6rem;text-align:center;position:relative;overflow:hidden">
                            <div style="position:absolute;bottom:0;left:0;right:0;height:{{ $pct }}%;background:{{ $p['color'] }}18;transition:height 1.4s ease;border-radius:.75rem" class="bar-fill-v" data-height="{{ $pct }}"></div>
                            <div style="position:relative;font-size:1.25rem;font-weight:900;color:{{ $p['color'] }}">{{ $p['val'] }}</div>
                            <div style="position:relative;font-size:.7rem;color:var(--muted);font-weight:600;margin-top:.2rem">{{ $p['label'] }}</div>
                            <div style="position:relative;font-size:.68rem;color:{{ $p['color'] }};font-weight:700">{{ $pct }}%</div>
                        </div>
                        @endforeach
                    </div>
                </div>

                {{-- Type breakdown --}}
                <div class="chart-panel" style="flex:1">
                    <div class="chart-panel-title"><i class="bi bi-tags-fill" style="color:#a78bfa"></i> Tickets by Type</div>
                    @php
                        $types = [
                            ['label'=>'Incident',        'val'=>$landingStats['by_type']['Incident'],        'color'=>'#f87171'],
                            ['label'=>'Service Request', 'val'=>$landingStats['by_type']['Service Request'], 'color'=>'#818cf8'],
                            ['label'=>'Question',        'val'=>$landingStats['by_type']['Question'],        'color'=>'#34d399'],
                            ['label'=>'Change Request',  'val'=>$landingStats['by_type']['Change Request'],  'color'=>'#fbbf24'],
                        ];
                    @endphp
                    <div style="display:flex;flex-direction:column;gap:.5rem;margin-top:.85rem">
                        @foreach($types as $t)
                        @php $pct = $total > 0 ? round($t['val'] / $total * 100) : 0; @endphp
                        <div style="display:flex;align-items:center;gap:.65rem">
                            <div style="width:8px;height:8px;border-radius:50%;background:{{ $t['color'] }};flex-shrink:0;box-shadow:0 0 6px {{ $t['color'] }}88"></div>
                            <span style="font-size:.75rem;color:var(--text);flex:1;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">{{ $t['label'] }}</span>
                            <div style="flex:2;background:var(--surface2);border-radius:9999px;height:6px;overflow:hidden">
                                <div class="bar-fill" style="height:100%;width:0;background:{{ $t['color'] }};border-radius:9999px;transition:width 1.3s cubic-bezier(.22,.68,0,1.2)" data-width="{{ $pct }}"></div>
                            </div>
                            <span style="font-size:.72rem;color:var(--muted);min-width:28px;text-align:right">{{ $t['val'] }}</span>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        {{-- CTA at bottom of analytics --}}
        <div style="text-align:center;margin-top:2.5rem">
            <a href="{{ route('login') }}" class="btn-hero-primary">
                <i class="bi bi-box-arrow-in-right"></i> View Full Reports
            </a>
        </div>
    </div>
</section>

{{-- ── Footer ── --}}
<footer class="footer">
    <div class="footer-inner">
        <div class="footer-logo">
            <img src="{{ asset('logo/Gemini_Generated_Image_1w1sif1w1sif1w1s-removebg-preview.png') }}" alt="FluxTickets">
            <span>FluxTickets</span>
        </div>
        <div class="footer-copy">&copy; {{ date('Y') }} FluxTickets. All rights reserved.</div>
    </div>
</footer>

<script>
    // ── Dark mode toggle ──
    const root = document.getElementById('html-root');
    const body = document.body;
    const btn  = document.getElementById('darkToggle');

    function applyTheme(dark) {
        if (dark) { body.classList.add('dark'); body.classList.remove('light'); }
        else       { body.classList.remove('dark'); body.classList.add('light'); }
    }
    const saved = localStorage.getItem('theme');
    applyTheme(saved !== 'light');
    btn.addEventListener('click', () => {
        const isDark = body.classList.contains('dark');
        applyTheme(!isDark);
        localStorage.setItem('theme', isDark ? 'light' : 'dark');
    });

    // ── Scroll reveal ──
    const revealEls = document.querySelectorAll(
        '.feat-card, .how-step, .type-card, .stats-strip, .section-label, .section-title, .section-sub, .cta-banner, .stat-num'
    );

    revealEls.forEach((el, i) => {
        el.style.opacity = '0';
        el.style.transform = 'translateY(30px)';
        el.style.transition = `opacity .6s ease ${(i % 4) * 0.08}s, transform .6s cubic-bezier(.22,.68,0,1.2) ${(i % 4) * 0.08}s`;
    });

    const revealObserver = new IntersectionObserver((entries) => {
        entries.forEach(e => {
            if (e.isIntersecting) {
                e.target.style.opacity = '1';
                e.target.style.transform = 'translateY(0)';
                revealObserver.unobserve(e.target);
            }
        });
    }, { threshold: 0.08, rootMargin: '0px 0px -40px 0px' });

    revealEls.forEach(el => revealObserver.observe(el));

    // ── Navbar shrink on scroll ──
    const navbar = document.querySelector('.navbar');
    window.addEventListener('scroll', () => {
        if (window.scrollY > 60) {
            navbar.style.padding = '.5rem 2rem';
            navbar.style.background = document.body.classList.contains('dark')
                ? 'rgba(15,23,42,.97)' : 'rgba(232,237,246,.97)';
            navbar.style.boxShadow = '0 4px 24px rgba(0,0,0,.25)';
        } else {
            navbar.style.padding = '.875rem 2rem';
            navbar.style.background = '';
            navbar.style.boxShadow = '';
        }
    }, { passive: true });

    // ── Animated bar fills ──
    const barObserver = new IntersectionObserver((entries) => {
        entries.forEach(e => {
            if (!e.isIntersecting) return;
            e.target.querySelectorAll('.bar-fill').forEach(bar => {
                bar.style.width = bar.dataset.width + '%';
            });
            e.target.querySelectorAll('.bar-fill-v').forEach(bar => {
                bar.style.height = bar.dataset.height + '%';
            });
            barObserver.unobserve(e.target);
        });
    }, { threshold: 0.2 });
    document.querySelectorAll('.chart-panel').forEach(p => barObserver.observe(p));

    // ── Counter animation ──
    const counterObserver = new IntersectionObserver((entries) => {
        entries.forEach(e => {
            if (!e.isIntersecting) return;
            e.target.querySelectorAll('.kpi-num').forEach(el => {
                const target = +el.dataset.target;
                const suffix = el.dataset.suffix || '';
                const duration = 1400;
                const start = performance.now();
                function tick(now) {
                    const progress = Math.min((now - start) / duration, 1);
                    const ease = 1 - Math.pow(1 - progress, 3);
                    el.textContent = Math.round(ease * target) + suffix;
                    if (progress < 1) requestAnimationFrame(tick);
                }
                requestAnimationFrame(tick);
            });
            counterObserver.unobserve(e.target);
        });
    }, { threshold: 0.3 });
    document.querySelectorAll('.kpi-grid').forEach(g => counterObserver.observe(g));

    // ── Parallax orbs on scroll ──
    const orb1 = document.querySelector('.hero-orb-1');
    const orb2 = document.querySelector('.hero-orb-2');
    const orb3 = document.querySelector('.hero-orb-3');
    window.addEventListener('scroll', () => {
        const y = window.scrollY;
        if (orb1) orb1.style.transform = `translateY(${y * 0.18}px)`;
        if (orb2) orb2.style.transform = `translateY(${-y * 0.12}px)`;
        if (orb3) orb3.style.transform = `translateY(${y * 0.08}px)`;
    }, { passive: true });

    // ── Sign In loading state ──
    function handleSignIn(e) {
        const btn     = document.getElementById('signInBtn');
        const icon    = document.getElementById('signInIcon');
        const spinner = document.getElementById('signInSpinner');
        const text    = document.getElementById('signInText');
        icon.style.display    = 'none';
        spinner.style.display = 'inline-block';
        text.textContent      = ' Signing in…';
        btn.style.opacity     = '.75';
        btn.style.pointerEvents = 'none';
    }
</script>
</body>
</html>
