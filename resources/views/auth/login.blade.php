<!DOCTYPE html>
<html lang="en" id="html-root">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign in — FluxTickets</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        :root {
            --navy-900: #0a1430;
            --navy-800: #0f1e42;
            --navy-700: #162a5a;
            --navy-600: #1f3a78;
            --navy-500: #2d4e9b;
            --green-500: #22c55e;
            --green-600: #16a34a;
            --green-400: #4ade80;
            --text-light: #e6eaf4;
            --muted-light: rgba(230,234,244,.65);
            --muted-dim:   rgba(230,234,244,.45);
            --border-soft: rgba(255,255,255,.08);
        }

        *, *::before, *::after { box-sizing: border-box; }
        html, body { height: 100%; margin: 0; }
        body {
            min-height: 100vh;
            display: flex;
            align-items: stretch;
            font-family: 'Inter', 'Segoe UI', system-ui, sans-serif;
            color: var(--text-light);
            background: var(--navy-900);
            -webkit-font-smoothing: antialiased;
            transition: background .3s, color .3s;
        }

        /* ────── Background ────── */
        .bg-pattern {
            position: fixed; inset: 0; z-index: 0; pointer-events: none;
            background:
                radial-gradient(1200px 600px at 85% -10%, rgba(34,197,94,.10), transparent 60%),
                radial-gradient(900px 500px at 10% 110%, rgba(45,78,155,.35), transparent 60%),
                linear-gradient(160deg, var(--navy-900) 0%, #07102a 100%);
        }
        body:not(.dark) {
            background: #eef2f9;
            color: #0f1e42;
        }
        body:not(.dark) .bg-pattern {
            background:
                radial-gradient(1000px 500px at 85% -10%, rgba(34,197,94,.12), transparent 60%),
                radial-gradient(900px 500px at 10% 110%, rgba(45,78,155,.12), transparent 60%),
                linear-gradient(160deg, #f5f8ff 0%, #e7ecf7 100%);
        }

        /* Soft geometric accents */
        .orb {
            position: fixed; pointer-events: none; z-index: 0;
            border-radius: 50%;
            filter: blur(80px);
        }
        .orb-1 { width: 420px; height: 420px; top: -180px; right: -120px; background: rgba(34,197,94,.18); }
        .orb-2 { width: 380px; height: 380px; bottom: -160px; left: -140px; background: rgba(45,78,155,.35); }
        body:not(.dark) .orb-1 { background: rgba(34,197,94,.22); }
        body:not(.dark) .orb-2 { background: rgba(45,78,155,.18); }

        /* ────── Wrapper ────── */
        .page-wrapper {
            position: relative; z-index: 1;
            display: flex; width: 100%; min-height: 100vh;
            align-items: center; justify-content: center;
            padding: 2rem 1rem;
        }

        /* ────── Split card ────── */
        .split-card {
            display: flex;
            width: 100%; max-width: 1000px;
            border-radius: 1.25rem;
            overflow: hidden;
            background: rgba(15,30,66,.55);
            border: 1px solid var(--border-soft);
            backdrop-filter: blur(22px);
            -webkit-backdrop-filter: blur(22px);
            box-shadow:
                0 30px 80px rgba(0,0,0,.55),
                inset 0 1px 0 rgba(255,255,255,.04);
        }
        body:not(.dark) .split-card {
            background: rgba(255,255,255,.85);
            border-color: rgba(15,30,66,.08);
            box-shadow: 0 30px 80px rgba(15,30,66,.18);
        }

        /* ── Left panel ── */
        .left-panel {
            flex: 1;
            position: relative; overflow: hidden;
            padding: 3rem 2.75rem;
            display: flex; flex-direction: column; justify-content: center;
            background:
                radial-gradient(600px 400px at -10% 0%, rgba(34,197,94,.22), transparent 55%),
                radial-gradient(700px 500px at 110% 110%, rgba(45,78,155,.35), transparent 60%),
                linear-gradient(160deg, var(--navy-700) 0%, var(--navy-800) 55%, var(--navy-900) 100%);
            border-right: 1px solid var(--border-soft);
        }
        .left-panel::before {
            content: '';
            position: absolute; inset: 0; pointer-events: none;
            background:
                linear-gradient(180deg, transparent 0%, rgba(34,197,94,.06) 100%);
        }

        /* Thin green accent bar */
        .accent-bar {
            width: 40px; height: 3px;
            background: linear-gradient(90deg, var(--green-500), var(--green-400));
            border-radius: 2px;
            margin: 1.25rem 0 1rem;
            box-shadow: 0 0 18px rgba(34,197,94,.5);
        }

        .brand-row {
            display: flex; align-items: center; gap: .85rem;
            margin-bottom: .5rem;
        }
        .brand-row img {
            width: 52px; height: 52px; object-fit: contain;
            filter: drop-shadow(0 4px 14px rgba(34,197,94,.25));
        }
        .brand-name {
            font-size: 1.2rem; font-weight: 700;
            letter-spacing: -.01em; color: #fff;
        }

        .hero-title {
            font-size: 1.9rem; font-weight: 800;
            color: #fff; line-height: 1.2; letter-spacing: -.015em;
            margin-bottom: .85rem;
        }
        .hero-title .accent { color: var(--green-400); }
        .hero-sub {
            color: var(--muted-light); font-size: .92rem;
            line-height: 1.65; margin-bottom: 2rem; max-width: 36ch;
        }

        .feature-item {
            display: flex; align-items: flex-start; gap: .85rem;
            margin-bottom: 1.25rem;
        }
        .feature-icon {
            width: 2.3rem; height: 2.3rem; flex-shrink: 0;
            display: flex; align-items: center; justify-content: center;
            border-radius: .65rem;
            background: rgba(34,197,94,.12);
            border: 1px solid rgba(34,197,94,.25);
            color: var(--green-400);
        }
        .feature-title {
            color: #fff; font-weight: 600; font-size: .9rem;
            margin-bottom: .1rem;
        }
        .feature-desc {
            color: var(--muted-light); font-size: .8rem; line-height: 1.55;
        }

        .tag-row {
            display: flex; gap: .5rem; flex-wrap: wrap;
            padding-top: 1.25rem; margin-top: 1.5rem;
            border-top: 1px solid var(--border-soft);
        }
        .tag {
            font-size: .72rem; font-weight: 500;
            padding: .3rem .7rem; border-radius: 9999px;
            background: rgba(255,255,255,.05);
            border: 1px solid var(--border-soft);
            color: var(--muted-light);
        }
        .tag i { color: var(--green-400); margin-right: .35rem; }

        /* ── Right panel ── */
        .right-panel {
            flex: 0 0 420px;
            display: flex; flex-direction: column;
            background: rgba(10,20,48,.45);
        }
        body:not(.dark) .right-panel { background: #fff; }

        .form-header {
            padding: 2.5rem 2.5rem 1rem;
        }
        .form-header .eyebrow {
            display: inline-flex; align-items: center; gap: .45rem;
            padding: .25rem .7rem; border-radius: 9999px;
            background: rgba(34,197,94,.1);
            border: 1px solid rgba(34,197,94,.25);
            color: var(--green-400);
            font-size: .72rem; font-weight: 600;
            text-transform: uppercase; letter-spacing: .08em;
            margin-bottom: 1rem;
        }
        body:not(.dark) .form-header .eyebrow { color: var(--green-600); }
        .form-header h1 {
            color: #fff;
            font-size: 1.55rem; font-weight: 700;
            letter-spacing: -.01em;
            margin-bottom: .35rem;
        }
        body:not(.dark) .form-header h1 { color: var(--navy-800); }
        .form-header p {
            color: var(--muted-light);
            font-size: .88rem; margin-bottom: 0;
        }
        body:not(.dark) .form-header p { color: #52607a; }

        .form-area { padding: 1.25rem 2.5rem 2rem; flex: 1; }

        .card-footer-area {
            padding: 1rem 2.5rem;
            text-align: center;
            border-top: 1px solid var(--border-soft);
        }
        body:not(.dark) .card-footer-area { border-top-color: #eef2f7; }
        .card-footer-area span {
            font-size: .78rem; color: var(--muted-dim);
        }
        body:not(.dark) .card-footer-area span { color: #7a8699; }

        /* ────── Fields ────── */
        .field-label {
            font-size: .72rem; font-weight: 700;
            text-transform: uppercase; letter-spacing: .08em;
            color: var(--muted-light);
        }
        body:not(.dark) .field-label { color: #52607a; }

        .field-wrap {
            position: relative;
            background: rgba(255,255,255,.04);
            border: 1px solid var(--border-soft);
            border-radius: .7rem;
            transition: border-color .2s, box-shadow .2s, background .2s;
            display: flex; align-items: center;
        }
        body:not(.dark) .field-wrap {
            background: #f5f7fb;
            border-color: #e3e8f1;
        }
        .field-wrap:focus-within {
            border-color: var(--green-500);
            background: rgba(34,197,94,.06);
            box-shadow: 0 0 0 4px rgba(34,197,94,.12);
        }
        body:not(.dark) .field-wrap:focus-within {
            background: #fff;
            box-shadow: 0 0 0 4px rgba(34,197,94,.15);
        }
        .field-wrap .f-icon {
            flex-shrink: 0;
            padding: 0 .9rem;
            color: var(--muted-dim);
            font-size: .95rem;
        }
        body:not(.dark) .field-wrap .f-icon { color: #7a8699; }
        .field-wrap:focus-within .f-icon { color: var(--green-500); }

        .field-wrap input {
            flex: 1;
            background: transparent;
            border: none;
            outline: none;
            padding: .75rem .5rem .75rem 0;
            font-size: .9rem;
            color: var(--text-light);
            font-family: inherit;
        }
        body:not(.dark) .field-wrap input { color: var(--navy-800); }
        .field-wrap input::placeholder { color: var(--muted-dim); }
        body:not(.dark) .field-wrap input::placeholder { color: #9aa5ba; }

        .pw-toggle {
            background: none; border: none; outline: none;
            padding: 0 .9rem; cursor: pointer;
            color: var(--muted-dim);
            font-size: .95rem;
            transition: color .15s;
        }
        body:not(.dark) .pw-toggle { color: #7a8699; }
        .pw-toggle:hover { color: var(--green-400); }

        /* ────── Button ────── */
        .btn-login {
            width: 100%;
            padding: .85rem 1rem;
            border: none; border-radius: .75rem;
            background: linear-gradient(135deg, var(--green-500), var(--green-600));
            color: #03180a;
            font-weight: 700; font-size: .92rem; letter-spacing: .01em;
            cursor: pointer;
            transition: transform .15s, box-shadow .2s, filter .15s;
            box-shadow: 0 8px 24px rgba(34,197,94,.35), inset 0 1px 0 rgba(255,255,255,.25);
            display: inline-flex; align-items: center; justify-content: center; gap: .5rem;
        }
        .btn-login:hover { transform: translateY(-1px); filter: brightness(1.05); box-shadow: 0 12px 28px rgba(34,197,94,.45); }
        .btn-login:active { transform: translateY(0); }
        .btn-login:disabled { opacity: .8; cursor: not-allowed; }

        /* ────── Alerts ────── */
        .alert-compact {
            display: flex; align-items: center; gap: .55rem;
            padding: .65rem .85rem;
            border-radius: .6rem;
            font-size: .82rem;
            margin-bottom: 1rem;
        }
        .alert-err {
            background: rgba(239,68,68,.1);
            border: 1px solid rgba(239,68,68,.3);
            color: #fca5a5;
        }
        body:not(.dark) .alert-err { color: #991b1b; background: #fef2f2; border-color: #fecaca; }
        .alert-ok {
            background: rgba(34,197,94,.1);
            border: 1px solid rgba(34,197,94,.3);
            color: var(--green-400);
        }
        body:not(.dark) .alert-ok { color: var(--green-600); background: #f0fdf4; border-color: #bbf7d0; }

        /* ────── Remember + forgot ────── */
        .row-inline {
            display: flex; align-items: center; justify-content: space-between;
            margin-bottom: 1.25rem;
        }
        .remember-wrap {
            display: inline-flex; align-items: center; gap: .5rem;
            color: var(--muted-light); font-size: .82rem;
            cursor: pointer; user-select: none;
        }
        body:not(.dark) .remember-wrap { color: #52607a; }
        .remember-wrap input {
            width: 1rem; height: 1rem;
            accent-color: var(--green-500);
            cursor: pointer;
        }
        .forgot-link {
            font-size: .8rem; font-weight: 600;
            color: var(--green-400);
        }
        body:not(.dark) .forgot-link { color: var(--green-600); }
        .forgot-link:hover { filter: brightness(1.15); }

        /* ────── Dark toggle ────── */
        .theme-toggle {
            position: fixed; top: 1.25rem; right: 1.25rem; z-index: 50;
            display: flex; align-items: center; gap: .5rem;
            padding: .35rem .55rem;
            background: rgba(15,30,66,.6);
            border: 1px solid var(--border-soft);
            border-radius: 9999px;
            backdrop-filter: blur(10px);
        }
        body:not(.dark) .theme-toggle { background: rgba(255,255,255,.75); border-color: rgba(15,30,66,.08); }
        .theme-toggle i { font-size: .85rem; }
        .theme-toggle .bi-sun { color: #fbbf24; }
        .theme-toggle .bi-moon-stars { color: #a5b4fc; }
        .dark-toggle {
            width: 2.5rem; height: 1.4rem;
            background: var(--navy-600);
            border-radius: 9999px;
            position: relative; cursor: pointer;
            transition: background .3s; border: none; outline: none; flex-shrink: 0;
        }
        .dark-toggle::after {
            content: '';
            position: absolute; top: 3px; left: 3px;
            width: 1rem; height: 1rem;
            background: #fff; border-radius: 50%;
            transition: transform .3s;
            box-shadow: 0 1px 3px rgba(0,0,0,.25);
        }
        body:not(.dark) .dark-toggle { background: #cbd5e1; }
        body.dark .dark-toggle { background: var(--green-600); }
        body:not(.dark) .dark-toggle::after { transform: translateX(0); }
        body.dark .dark-toggle::after { transform: translateX(1.1rem); }

        /* ────── Responsive ────── */
        @media (max-width: 860px) {
            .left-panel { display: none; }
            .right-panel { flex: 1; }
            .split-card { max-width: 460px; }
            .form-header { padding: 2rem 1.75rem 1rem; }
            .form-area   { padding: 1rem 1.75rem 1.75rem; }
            .card-footer-area { padding: 1rem 1.75rem; }
        }

        a { text-decoration: none !important; }
        .copyright {
            position: fixed; bottom: .85rem; left: 0; right: 0;
            text-align: center;
            font-size: .7rem; color: var(--muted-dim);
            z-index: 1;
        }
        body:not(.dark) .copyright { color: #7a8699; }
    </style>
</head>
<body class="dark">

    <div class="bg-pattern"></div>
    <div class="orb orb-1"></div>
    <div class="orb orb-2"></div>

    {{-- Theme toggle --}}
    <div class="theme-toggle">
        <i class="bi bi-sun"></i>
        <button class="dark-toggle" id="darkToggle" aria-label="Toggle dark mode"></button>
        <i class="bi bi-moon-stars"></i>
    </div>

    <div class="page-wrapper">
        <div class="split-card">

            {{-- ══ LEFT PANEL ══ --}}
            <div class="left-panel">
                <div style="position:relative;z-index:1">
                    <div class="brand-row">
                        <img src="{{ asset('Image/logo.png') }}" alt="FluxTickets Logo">
                        <span class="brand-name">FluxTickets</span>
                    </div>
                    <div class="accent-bar"></div>

                    <h2 class="hero-title">
                        Streamline your<br>
                        <span class="accent">support workflow.</span>
                    </h2>
                    <p class="hero-sub">
                        A centralized ticketing platform that helps teams track, manage, and resolve service requests with clarity and speed.
                    </p>

                    <div class="feature-item">
                        <div class="feature-icon"><i class="bi bi-lightning-charge-fill"></i></div>
                        <div>
                            <div class="feature-title">Fast Ticket Resolution</div>
                            <div class="feature-desc">Assign, prioritize, and close tickets in record time.</div>
                        </div>
                    </div>

                    <div class="feature-item">
                        <div class="feature-icon"><i class="bi bi-people-fill"></i></div>
                        <div>
                            <div class="feature-title">Team Collaboration</div>
                            <div class="feature-desc">Keep everyone in sync with shared queues and notes.</div>
                        </div>
                    </div>

                    <div class="feature-item" style="margin-bottom:0">
                        <div class="feature-icon"><i class="bi bi-graph-up-arrow"></i></div>
                        <div>
                            <div class="feature-title">Real-time Analytics</div>
                            <div class="feature-desc">Monitor performance and SLA compliance at a glance.</div>
                        </div>
                    </div>

                    <div class="tag-row">
                        <span class="tag"><i class="bi bi-check2"></i>SmartRouting</span>
                        <span class="tag"><i class="bi bi-check2"></i>SLAReady</span>
                        <span class="tag"><i class="bi bi-check2"></i>AutoAssign</span>
                    </div>
                </div>
            </div>

            {{-- ══ RIGHT PANEL ══ --}}
            <div class="right-panel">
                <div class="form-header">
                    <span class="eyebrow"><i class="bi bi-shield-check"></i> Secure Sign-in</span>
                    <h1>Welcome back</h1>
                    <p>Sign in to continue to your dashboard.</p>
                </div>

                <div class="form-area">
                    @if ($errors->any())
                    <div class="alert-compact alert-err">
                        <i class="bi bi-exclamation-triangle-fill"></i>
                        <span>{{ $errors->first() }}</span>
                    </div>
                    @endif

                    @if (session('status'))
                    <div class="alert-compact alert-ok">
                        <i class="bi bi-check-circle-fill"></i>
                        <span>{{ session('status') }}</span>
                    </div>
                    @endif

                    <form method="POST" action="{{ route('login') }}" id="loginForm">
                        @csrf

                        <div class="mb-3">
                            <label for="email" class="field-label d-block mb-2">Email or Username</label>
                            <div class="field-wrap">
                                <span class="f-icon"><i class="bi bi-person"></i></span>
                                <input type="text" id="email" name="email"
                                       value="{{ old('email') }}"
                                       placeholder="you@company.com"
                                       required autofocus autocomplete="username">
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <label for="password" class="field-label mb-0">Password</label>
                                @if (Route::has('password.request'))
                                    <a href="{{ route('password.request') }}" class="forgot-link">Forgot password?</a>
                                @endif
                            </div>
                            <div class="field-wrap">
                                <span class="f-icon"><i class="bi bi-lock"></i></span>
                                <input type="password" id="password" name="password"
                                       placeholder="••••••••"
                                       required autocomplete="current-password">
                                <button type="button" class="pw-toggle" onclick="togglePassword()" aria-label="Toggle password visibility">
                                    <i class="bi bi-eye" id="toggleIcon"></i>
                                </button>
                            </div>
                        </div>

                        <div class="row-inline">
                            <label class="remember-wrap" for="remember">
                                <input type="checkbox" id="remember" name="remember">
                                Keep me signed in
                            </label>
                        </div>

                        <button type="submit" id="loginBtn" class="btn-login">
                            <span id="loginBtnContent">Sign In</span>
                            <span id="loginBtnLoading" style="display:none">
                                <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true" style="width:.9rem;height:.9rem;border-width:2px"></span>Signing in…
                            </span>
                        </button>
                    </form>
                </div>

                <div class="card-footer-area">
                    <span>Don't have an account yet? Contact your administrator.</span>
                </div>
            </div>
        </div>

        <p class="copyright">&copy; {{ date('Y') }} FluxTickets. All rights reserved.</p>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const body   = document.body;
        const toggle = document.getElementById('darkToggle');
        const saved  = localStorage.getItem('theme');
        if (saved === 'light') body.classList.remove('dark');
        else body.classList.add('dark');

        toggle.addEventListener('click', () => {
            body.classList.toggle('dark');
            localStorage.setItem('theme', body.classList.contains('dark') ? 'dark' : 'light');
        });

        document.getElementById('loginForm').addEventListener('submit', function() {
            const btn     = document.getElementById('loginBtn');
            const content = document.getElementById('loginBtnContent');
            const loading = document.getElementById('loginBtnLoading');
            btn.disabled  = true;
            content.style.display = 'none';
            loading.style.display = 'inline-flex';
        });

        function togglePassword() {
            const input = document.getElementById('password');
            const icon  = document.getElementById('toggleIcon');
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.replace('bi-eye', 'bi-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.replace('bi-eye-slash', 'bi-eye');
            }
        }
    </script>
</body>
</html>
