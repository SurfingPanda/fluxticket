<!DOCTYPE html>
<html lang="en" id="html-root">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — FluxTickets</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>

    <style>
        *, *::before, *::after { box-sizing: border-box; }

        /* ───── Page ───── */
        html, body { height: 100%; margin: 0; }
        body {
            min-height: 100vh;
            display: flex;
            align-items: stretch;
            font-family: 'Segoe UI', system-ui, sans-serif;
            background: #0f172a;
            transition: background .3s;
        }

        /* ───── Background SVG pattern ───── */
        .bg-pattern {
            position: fixed; inset: 0; z-index: 0; pointer-events: none;
            background-color: #0f172a;
            background-image:
                radial-gradient(circle at 20% 50%, rgba(99,102,241,.18) 0%, transparent 55%),
                radial-gradient(circle at 80% 20%, rgba(139,92,246,.15) 0%, transparent 50%),
                radial-gradient(circle at 60% 80%, rgba(79,70,229,.12) 0%, transparent 45%);
        }
        .dark .bg-pattern { background-color: #0f172a; }
        body:not(.dark) .bg-pattern {
            background-color: #f0f4ff;
            background-image:
                radial-gradient(circle at 20% 50%, rgba(99,102,241,.12) 0%, transparent 55%),
                radial-gradient(circle at 80% 20%, rgba(139,92,246,.10) 0%, transparent 50%),
                radial-gradient(circle at 60% 80%, rgba(79,70,229,.08) 0%, transparent 45%);
        }

        /* Floating ticket shapes */
        .ticket-float {
            position: fixed; pointer-events: none; z-index: 0;
            opacity: .07;
        }
        body:not(.dark) .ticket-float { opacity: .18; }
        body:not(.dark) .ticket-float rect,
        body:not(.dark) .ticket-float line,
        body:not(.dark) .ticket-float circle { stroke: #4f46e5; }
        body:not(.dark) .ticket-float path { fill: #4f46e5; }
        .ticket-float:nth-child(1) { animation: floatUD 9s ease-in-out infinite; }
        .ticket-float:nth-child(2) { animation: floatUD 12s ease-in-out infinite; animation-delay: -4s; }
        .ticket-float:nth-child(3) { animation: floatUD 7s ease-in-out infinite; animation-delay: -2s; }

        @keyframes floatUD {
            0%, 100% { transform: translateY(0); }
            50%       { transform: translateY(-22px); }
        }
        /* Preserve rotation on the rotated ones */
        .ticket-float.rot-20 { animation: floatUD20 12s ease-in-out infinite; animation-delay: -4s; }
        .ticket-float.rot-n12 { animation: floatUDn12 7s ease-in-out infinite; animation-delay: -2s; }
        @keyframes floatUD20 {
            0%, 100% { transform: rotate(20deg) translateY(0); }
            50%       { transform: rotate(20deg) translateY(-22px); }
        }
        @keyframes floatUDn12 {
            0%, 100% { transform: rotate(-12deg) translateY(0); }
            50%       { transform: rotate(-12deg) translateY(-22px); }
        }

        /* ───── Wrapper ───── */
        .page-wrapper {
            position: relative; z-index: 1;
            display: flex; width: 100%; min-height: 100vh;
            align-items: center; justify-content: center;
            padding: 2rem 1rem;
        }

        /* ───── Split card ───── */
        .split-card {
            display: flex;
            width: 100%; max-width: 960px;
            border-radius: 1.5rem;
            overflow: hidden;
            box-shadow: 0 30px 80px rgba(0,0,0,.45);
        }

        /* ── Left panel ── */
        .left-panel {
            flex: 1;
            background: linear-gradient(145deg, #4338ca 0%, #6d28d9 55%, #7c3aed 100%);
            padding: 3rem 2.5rem;
            display: flex; flex-direction: column; justify-content: center;
            position: relative; overflow: hidden;
        }
        .left-panel::before {
            content: '';
            position: absolute; inset: 0;
            background: radial-gradient(circle at 80% 10%, rgba(255,255,255,.12) 0%, transparent 55%),
                        radial-gradient(circle at 10% 90%, rgba(255,255,255,.07) 0%, transparent 40%);
        }
        /* decorative ticket shapes on left panel */
        .left-panel .deco {
            position: absolute; border-radius: 1rem;
            border: 2px solid rgba(255,255,255,.12);
        }
        .left-panel .deco-1 {
            width: 160px; height: 80px;
            top: -20px; right: -30px;
            transform: rotate(15deg);
        }
        .left-panel .deco-2 {
            width: 120px; height: 60px;
            bottom: 40px; left: -20px;
            transform: rotate(-10deg);
        }
        .left-panel .deco-3 {
            width: 90px; height: 45px;
            bottom: 120px; right: 20px;
            transform: rotate(5deg);
            opacity: .5;
        }

        /* ── Right panel ── */
        .right-panel {
            flex: 0 0 420px;
            background: #1e293b;
            display: flex; flex-direction: column;
            transition: background .3s;
        }
        body:not(.dark) .right-panel { background: #ffffff; }

        .right-panel .card-hero {
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
            padding: 2rem 2.5rem;
            text-align: center;
            position: relative; overflow: hidden;
        }
        .right-panel .card-hero::before {
            content: '';
            position: absolute; inset: 0;
            background: radial-gradient(circle at 70% 30%, rgba(255,255,255,.15) 0%, transparent 60%);
        }

        .right-panel .form-area {
            padding: 2rem 2.5rem;
            flex: 1;
        }

        .right-panel .card-footer-area {
            padding: .875rem 2.5rem;
            text-align: center;
            border-top: 1px solid #334155;
        }
        body:not(.dark) .right-panel .card-footer-area { border-top-color: #f1f5f9; }

        /* ───── Inputs ───── */
        .field-label {
            font-size: .72rem; font-weight: 700;
            text-transform: uppercase; letter-spacing: .07em;
            color: #94a3b8;
        }
        body:not(.dark) .field-label { color: #64748b; }

        .form-control {
            border-radius: 0 !important;
            border-left: 0 !important;
            font-size: .875rem;
            background: #0f172a !important;
            border-color: #334155 !important;
            color: #e2e8f0 !important;
            transition: border-color .2s, box-shadow .2s;
        }
        body:not(.dark) .form-control {
            background: #f8fafc !important;
            border-color: #e2e8f0 !important;
            color: #1e293b !important;
        }
        .form-control::placeholder { color: #475569; }
        body:not(.dark) .form-control::placeholder { color: #94a3b8; }
        .form-control:focus {
            border-color: #6366f1 !important;
            box-shadow: 0 0 0 3px rgba(99,102,241,.25) !important;
        }

        .input-group-text {
            border-radius: 0 !important;
            border-right: 0 !important;
            font-size: .875rem;
            background: #0f172a !important;
            border-color: #334155 !important;
            color: #64748b !important;
        }
        body:not(.dark) .input-group-text {
            background: #f8fafc !important;
            border-color: #e2e8f0 !important;
        }
        .input-group > :first-child { border-radius: .6rem 0 0 .6rem !important; }
        .input-group > :last-child  { border-radius: 0 .6rem .6rem 0 !important; }

        /* ───── Login button ───── */
        .btn-login {
            background: linear-gradient(135deg, #4f46e5, #7c3aed);
            border: none; border-radius: .75rem;
            font-weight: 600; letter-spacing: .025em;
            transition: opacity .2s, transform .15s, box-shadow .2s;
            box-shadow: 0 4px 15px rgba(99,102,241,.4);
        }
        .btn-login:hover { opacity: .9; transform: translateY(-1px); box-shadow: 0 8px 20px rgba(99,102,241,.45); }
        .btn-login:active { transform: translateY(0); }

        /* ───── Dark toggle ───── */
        .dark-toggle {
            width: 2.75rem; height: 1.5rem;
            background: #6366f1; border-radius: 9999px;
            position: relative; cursor: pointer;
            transition: background .3s; border: none; outline: none; flex-shrink: 0;
        }
        .dark-toggle::after {
            content: '';
            position: absolute; top: 3px; left: 3px;
            width: 1.125rem; height: 1.125rem;
            background: white; border-radius: 50%;
            transition: transform .3s;
            box-shadow: 0 1px 3px rgba(0,0,0,.2);
        }
        body:not(.dark) .dark-toggle { background: #cbd5e1; }
        body:not(.dark) .dark-toggle::after { transform: translateX(0); }
        body.dark .dark-toggle::after { transform: translateX(1.25rem); }

        /* ───── Feature pills ───── */
        .feature-item {
            display: flex; align-items: flex-start; gap: .75rem;
            margin-bottom: 1.5rem;
        }
        .feature-icon {
            width: 2.25rem; height: 2.25rem; flex-shrink: 0;
            background: rgba(255,255,255,.15);
            border-radius: .6rem;
            display: flex; align-items: center; justify-content: center;
            backdrop-filter: blur(4px);
        }

        /* ───── Responsive ───── */
        @media (max-width: 768px) {
            .left-panel { display: none; }
            .right-panel { flex: 1; }
        }

        a { text-decoration: none !important; }
    </style>
</head>
<body class="dark">

    {{-- Page background --}}
    <div class="bg-pattern"></div>

    {{-- Floating decorative ticket SVGs --}}
    <svg class="ticket-float" style="top:8%;left:5%;width:180px" viewBox="0 0 200 90" fill="none" xmlns="http://www.w3.org/2000/svg">
        <rect x="2" y="2" width="196" height="86" rx="12" stroke="white" stroke-width="3"/>
        <line x1="60" y1="2" x2="60" y2="88" stroke="white" stroke-width="2" stroke-dasharray="6 4"/>
        <circle cx="60" cy="2"  r="8" fill="#0f172a" stroke="white" stroke-width="2"/>
        <circle cx="60" cy="88" r="8" fill="#0f172a" stroke="white" stroke-width="2"/>
    </svg>
    <svg class="ticket-float rot-20" style="bottom:10%;right:4%;width:140px" viewBox="0 0 200 90" fill="none" xmlns="http://www.w3.org/2000/svg">
        <rect x="2" y="2" width="196" height="86" rx="12" stroke="white" stroke-width="3"/>
        <line x1="140" y1="2" x2="140" y2="88" stroke="white" stroke-width="2" stroke-dasharray="6 4"/>
        <circle cx="140" cy="2"  r="8" fill="#0f172a" stroke="white" stroke-width="2"/>
        <circle cx="140" cy="88" r="8" fill="#0f172a" stroke="white" stroke-width="2"/>
    </svg>
    <svg class="ticket-float rot-n12" style="top:55%;left:2%;width:100px" viewBox="0 0 200 90" fill="none" xmlns="http://www.w3.org/2000/svg">
        <rect x="2" y="2" width="196" height="86" rx="12" stroke="white" stroke-width="3"/>
    </svg>

    {{-- Dark mode toggle --}}
    <div class="position-fixed top-0 end-0 m-3 d-flex align-items-center gap-2" style="z-index:50">
        <i class="bi bi-sun" style="color:#fbbf24;font-size:.9rem"></i>
        <button class="dark-toggle" id="darkToggle" aria-label="Toggle dark mode"></button>
        <i class="bi bi-moon-stars" style="color:#818cf8;font-size:.9rem"></i>
    </div>

    <div class="page-wrapper">
        <div class="split-card">

            {{-- ══ LEFT PANEL ══ --}}
            <div class="left-panel">
                <div class="deco deco-1"></div>
                <div class="deco deco-2"></div>
                <div class="deco deco-3"></div>

                <div style="position:relative;z-index:1">
                    {{-- Logo --}}
                    <div class="d-flex align-items-center gap-3 mb-4">
                        {{-- Custom FluxTickets icon: ticket shape + lightning bolt --}}
                        <div style="width:48px;height:48px;background:rgba(255,255,255,.2);border-radius:.875rem;display:flex;align-items:center;justify-content:center;backdrop-filter:blur(4px);flex-shrink:0">
                            <svg width="28" height="28" viewBox="0 0 28 28" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <!-- ticket body -->
                                <rect x="1" y="6" width="26" height="16" rx="3" stroke="white" stroke-width="1.8"/>
                                <!-- left notch -->
                                <circle cx="1" cy="14" r="3" fill="rgba(255,255,255,0)" stroke="white" stroke-width="1.8"/>
                                <!-- right notch -->
                                <circle cx="27" cy="14" r="3" fill="rgba(255,255,255,0)" stroke="white" stroke-width="1.8"/>
                                <!-- perforated line -->
                                <line x1="9" y1="6" x2="9" y2="22" stroke="white" stroke-width="1.4" stroke-dasharray="2.5 2"/>
                                <!-- lightning bolt -->
                                <path d="M16 8.5L12.5 14.5H15.5L13 19.5L19 12.5H15.5L18 8.5Z" fill="white"/>
                            </svg>
                        </div>
                        <span class="text-white fw-bold" style="font-size:1.35rem;letter-spacing:-.01em">FluxTickets</span>
                    </div>

                    <h2 class="text-white fw-bold mb-2" style="font-size:1.6rem;line-height:1.3">
                        Streamline your<br>support workflow
                    </h2>
                    <p style="color:rgba(255,255,255,.7);font-size:.9rem;line-height:1.65;margin-bottom:2rem">
                        A centralized ticketing platform that helps teams track, manage, and resolve service requests efficiently — all in one place.
                    </p>

                    {{-- Features --}}
                    <div class="feature-item">
                        <div class="feature-icon">
                            <i class="bi bi-lightning-charge-fill text-white" style="font-size:.9rem"></i>
                        </div>
                        <div>
                            <div class="text-white fw-semibold" style="font-size:.875rem">Fast Ticket Resolution</div>
                            <div style="color:rgba(255,255,255,.6);font-size:.8rem;margin-top:.15rem">Assign, prioritize, and close tickets in record time.</div>
                        </div>
                    </div>

                    <div class="feature-item">
                        <div class="feature-icon">
                            <i class="bi bi-people-fill text-white" style="font-size:.9rem"></i>
                        </div>
                        <div>
                            <div class="text-white fw-semibold" style="font-size:.875rem">Team Collaboration</div>
                            <div style="color:rgba(255,255,255,.6);font-size:.8rem;margin-top:.15rem">Keep everyone in sync with shared queues and notes.</div>
                        </div>
                    </div>

                    <div class="feature-item" style="margin-bottom:0">
                        <div class="feature-icon">
                            <i class="bi bi-bar-chart-fill text-white" style="font-size:.9rem"></i>
                        </div>
                        <div>
                            <div class="text-white fw-semibold" style="font-size:.875rem">Real-time Analytics</div>
                            <div style="color:rgba(255,255,255,.6);font-size:.8rem;margin-top:.15rem">Monitor performance and SLA compliance at a glance.</div>
                        </div>
                    </div>

                    {{-- Ticket decoration --}}
                    <div class="mt-4 pt-3" style="border-top:1px solid rgba(255,255,255,.15)">
                        <div style="display:flex;gap:.5rem">
                            <span style="background:rgba(255,255,255,.15);color:rgba(255,255,255,.8);font-size:.72rem;padding:.25rem .65rem;border-radius:9999px;backdrop-filter:blur(4px)">#SmartRouting</span>
                            <span style="background:rgba(255,255,255,.15);color:rgba(255,255,255,.8);font-size:.72rem;padding:.25rem .65rem;border-radius:9999px;backdrop-filter:blur(4px)">#SLAReady</span>
                            <span style="background:rgba(255,255,255,.15);color:rgba(255,255,255,.8);font-size:.72rem;padding:.25rem .65rem;border-radius:9999px;backdrop-filter:blur(4px)">#AutoAssign</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ══ RIGHT PANEL ══ --}}
            <div class="right-panel">

                {{-- Hero --}}
                <div class="card-hero">
                    <div style="position:relative;z-index:1">
                        <div class="d-inline-flex align-items-center justify-content-center mb-2"
                             style="width:48px;height:48px;background:rgba(255,255,255,.2);border-radius:.875rem;backdrop-filter:blur(4px)">
                            <svg width="26" height="26" viewBox="0 0 28 28" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <rect x="1" y="6" width="26" height="16" rx="3" stroke="white" stroke-width="1.8"/>
                                <circle cx="1" cy="14" r="3" fill="rgba(255,255,255,0)" stroke="white" stroke-width="1.8"/>
                                <circle cx="27" cy="14" r="3" fill="rgba(255,255,255,0)" stroke="white" stroke-width="1.8"/>
                                <line x1="9" y1="6" x2="9" y2="22" stroke="white" stroke-width="1.4" stroke-dasharray="2.5 2"/>
                                <path d="M16 8.5L12.5 14.5H15.5L13 19.5L19 12.5H15.5L18 8.5Z" fill="white"/>
                            </svg>
                        </div>
                        <h4 class="text-white fw-bold mb-1" style="letter-spacing:-.01em">Welcome back</h4>
                        <p class="mb-0" style="color:rgba(255,255,255,.7);font-size:.825rem">Sign in to your FluxTickets account</p>
                    </div>
                </div>

                {{-- Form --}}
                <div class="form-area">

                    @if ($errors->any())
                    <div class="alert alert-danger d-flex align-items-center gap-2 py-2 px-3 mb-3 rounded-3" style="font-size:.82rem">
                        <i class="bi bi-exclamation-triangle-fill flex-shrink-0"></i>
                        <span>{{ $errors->first() }}</span>
                    </div>
                    @endif

                    @if (session('status'))
                    <div class="alert alert-success d-flex align-items-center gap-2 py-2 px-3 mb-3 rounded-3" style="font-size:.82rem">
                        <i class="bi bi-check-circle-fill flex-shrink-0"></i>
                        <span>{{ session('status') }}</span>
                    </div>
                    @endif

                    <form method="POST" action="{{ route('login') }}" id="loginForm">
                        @csrf

                        {{-- Email or Username --}}
                        <div class="mb-3">
                            <label for="email" class="field-label d-block mb-2">Email Address or Username</label>
                            <div class="input-group">
                                <span class="input-group-text px-3"><i class="bi bi-person-fill"></i></span>
                                <input type="text" id="email" name="email"
                                       value="{{ old('email') }}"
                                       placeholder="you@example.com or username"
                                       required autofocus autocomplete="username"
                                       class="form-control py-2">
                            </div>
                        </div>

                        {{-- Password --}}
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <label for="password" class="field-label mb-0">Password</label>
                                @if (Route::has('password.request'))
                                    <a href="{{ route('password.request') }}"
                                       style="font-size:.78rem;font-weight:500;color:#818cf8">
                                        Forgot password?
                                    </a>
                                @endif
                            </div>
                            <div class="input-group">
                                <span class="input-group-text px-3"><i class="bi bi-lock"></i></span>
                                <input type="password" id="password" name="password"
                                       placeholder="••••••••"
                                       required autocomplete="current-password"
                                       class="form-control py-2 @error('password') is-invalid @enderror">
                                <button type="button"
                                        class="input-group-text px-3"
                                        style="cursor:pointer" onclick="togglePassword()">
                                    <i class="bi bi-eye" id="toggleIcon"></i>
                                </button>
                                @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        {{-- Remember me --}}
                        <div class="d-flex align-items-center gap-2 mb-4">
                            <input class="form-check-input mt-0" type="checkbox" id="remember" name="remember"
                                   style="width:1rem;height:1rem;accent-color:#6366f1;cursor:pointer;background-color:transparent;border-color:#475569">
                            <label for="remember" class="mb-0" style="font-size:.825rem;color:#64748b;cursor:pointer;user-select:none">
                                Keep me signed in
                            </label>
                        </div>

                        {{-- Submit --}}
                        <button type="submit" id="loginBtn" class="btn btn-login w-100 text-white py-2" style="font-size:.9rem">
                            <span id="loginBtnContent"><i class="bi bi-box-arrow-in-right me-2"></i>Sign In</span>
                            <span id="loginBtnLoading" style="display:none">
                                <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true" style="width:.85rem;height:.85rem;border-width:2px"></span>Signing in…
                            </span>
                        </button>
                    </form>
                </div>

                {{-- Footer --}}
                <div class="card-footer-area">
                    <span style="font-size:.78rem;color:#64748b">
                        Don't have an account yet? Contact your administrator.
                    </span>
                </div>

            </div>{{-- end right panel --}}
        </div>{{-- end split card --}}

        <p class="position-fixed bottom-0 start-50 translate-middle-x pb-2 mb-0 text-center"
           style="font-size:.7rem;color:#334155;z-index:1">
            &copy; {{ date('Y') }} FluxTickets. All rights reserved.
        </p>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // ── Dark mode ──
        const body   = document.body;
        const toggle = document.getElementById('darkToggle');

        // Default dark; restore saved preference
        const saved = localStorage.getItem('theme');
        if (saved === 'light') body.classList.remove('dark');
        else body.classList.add('dark');

        toggle.addEventListener('click', () => {
            body.classList.toggle('dark');
            localStorage.setItem('theme', body.classList.contains('dark') ? 'dark' : 'light');
        });

        // ── Sign In loading state ──
        document.getElementById('loginForm').addEventListener('submit', function() {
            const btn     = document.getElementById('loginBtn');
            const content = document.getElementById('loginBtnContent');
            const loading = document.getElementById('loginBtnLoading');
            btn.disabled  = true;
            content.style.display = 'none';
            loading.style.display = '';
        });

        // ── Password toggle ──
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
