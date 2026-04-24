<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (auth()->check()) return redirect()->route('dashboard');
    $landingStats = [
        'total'    => 248,
        'open'     => 74,
        'progress' => 56,
        'resolved' => 89,
        'closed'   => 29,
        'high'     => 41,
        'medium'   => 103,
        'low'      => 104,
        'res_rate' => 48,
        'sla_rate' => 91,
        'agents'   => 18,
        'by_type'  => [
            'Incident'        => 52,
            'Service Request' => 112,
            'Question'        => 47,
            'Change Request'  => 37,
        ],
    ];
    return view('landing', compact('landingStats'));
})->name('landing');

Route::get('/login', function () {
    return view('auth.login');
})->middleware('guest')->name('login');

Route::post('/login', function (\Illuminate\Http\Request $request) {
    $request->validate([
        'email'    => ['required', 'string'],
        'password' => ['required'],
    ]);

    // Rate limit: 5 attempts per minute per IP
    $key = 'login.' . $request->ip();
    if (\Illuminate\Support\Facades\RateLimiter::tooManyAttempts($key, 5)) {
        $seconds = \Illuminate\Support\Facades\RateLimiter::availableIn($key);
        return back()->withErrors(['email' => "Too many login attempts. Please try again in {$seconds} seconds."])->onlyInput('email');
    }

    $login    = trim($request->input('email'));
    $password = $request->input('password');

    // Determine if the input looks like an email
    $isEmail = filter_var($login, FILTER_VALIDATE_EMAIL);

    $invalidMsg = ['email' => 'Invalid credentials. Please try again.'];

    // Helper: record a failed login attempt and check the 3-strike threshold
    $recordFailedLogin = function (string $identifier, ?int $userId = null) use ($request) {
        $cacheKey  = 'login_fails.' . md5(strtolower($identifier));
        $fails     = \Illuminate\Support\Facades\Cache::get($cacheKey, 0) + 1;
        \Illuminate\Support\Facades\Cache::put($cacheKey, $fails, now()->addMinutes(30));

        try {
            if ($fails >= 3) {
                \App\Models\AuditLog::create([
                    'user_id'       => $userId,
                    'action'        => 'security.login_threshold',
                    'subject_type'  => null,
                    'subject_id'    => null,
                    'subject_label' => $identifier,
                    'old_values'    => null,
                    'new_values'    => null,
                    'ip_address'    => $request->ip(),
                    'description'   => "Login failed {$fails} times for \"{$identifier}\" from IP {$request->ip()}.",
                ]);
                \Illuminate\Support\Facades\Cache::put($cacheKey, 0, now()->addMinutes(30));
            } else {
                \App\Models\AuditLog::create([
                    'user_id'       => $userId,
                    'action'        => 'security.login_failed',
                    'subject_type'  => null,
                    'subject_id'    => null,
                    'subject_label' => $identifier,
                    'old_values'    => null,
                    'new_values'    => null,
                    'ip_address'    => $request->ip(),
                    'description'   => "Failed login attempt ({$fails}/3) for \"{$identifier}\".",
                ]);
            }
        } catch (\Exception) {}
    };

    if ($isEmail) {
        $attempted = \Illuminate\Support\Facades\Auth::attempt(
            ['email' => $login, 'password' => $password],
            $request->boolean('remember')
        );
        if ($attempted) {
            if (! auth()->user()->is_active) {
                \Illuminate\Support\Facades\Auth::logout();
                $request->session()->invalidate();
                return back()->withErrors(['email' => 'Your account has been disabled. Please contact your administrator.'])->onlyInput('email');
            }
            \Illuminate\Support\Facades\RateLimiter::clear('login.' . $request->ip());
            $request->session()->regenerate();
            return redirect()->intended(route('dashboard'));
        }
        \Illuminate\Support\Facades\RateLimiter::hit('login.' . $request->ip());
        $knownUser = \App\Models\User::where('email', $login)->first();
        $recordFailedLogin($login, $knownUser?->id);
        return back()->withErrors($invalidMsg)->onlyInput('email');
    }

    // Username login
    $user = \App\Models\User::where('username', $login)->first();

    if (! $user || ! \Illuminate\Support\Facades\Auth::attempt(
        ['email' => $user->email, 'password' => $password],
        $request->boolean('remember')
    )) {
        \Illuminate\Support\Facades\RateLimiter::hit('login.' . $request->ip());
        $recordFailedLogin($login, $user?->id);
        return back()->withErrors($invalidMsg)->onlyInput('email');
    }

    if (! auth()->user()->is_active) {
        \Illuminate\Support\Facades\Auth::logout();
        $request->session()->invalidate();
        return back()->withErrors(['email' => 'Your account has been disabled. Please contact your administrator.'])->onlyInput('email');
    }

    \Illuminate\Support\Facades\RateLimiter::clear('login.' . $request->ip());
    $request->session()->regenerate();
    return redirect()->intended(route('dashboard'));
})->name('login.post');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', function () {
        $user = auth()->user();

        $tickets = \App\Models\Ticket::with('user')
            ->latest()
            ->take(5)
            ->get();

        $stats = [
            'open'     => \App\Models\Ticket::where('status', 'open')->count(),
            'progress' => \App\Models\Ticket::where('status', 'progress')->count(),
            'resolved' => \App\Models\Ticket::where('status', 'resolved')->count(),
        ];

        $deptUsers = \App\Models\User::whereNotNull('department')
            ->orderBy('name')
            ->get(['id','name','department'])
            ->groupBy('department');

        // Recent activity: notes on tickets related to this user
        $relatedTicketIds = \App\Models\Ticket::where(function ($q) use ($user) {
            $q->where('user_id', $user->id)
              ->orWhere('requester_id', $user->id)
              ->orWhere('assignee', $user->name);
            if ($user->department) {
                $q->orWhere('department', $user->department);
            }
        })->pluck('id');

        $recentActivity = \App\Models\TicketNote::with(['ticket.user', 'user'])
            ->whereIn('ticket_id', $relatedTicketIds)
            ->latest()
            ->take(20)
            ->get();

        return view('dashboard', compact('tickets', 'stats', 'deptUsers', 'recentActivity') + ['activePage' => 'dashboard']);
    })->name('dashboard');

    // ── Calendar ──
    Route::get('/calendar', function () {
        $tickets = \App\Models\Ticket::select('id','ticket_number','subject','status','priority','type','category','requester','department','description','sla_due_at','created_at','assignee')
            ->whereNotIn('status', ['resolved','closed','rejected'])
            ->where(function ($q) {
                $q->whereNotNull('sla_due_at')
                  ->orWhereIn('status', ['open','progress']);
            })
            ->get();
        return view('calendar', compact('tickets') + ['activePage' => 'calendar']);
    })->name('calendar');

    Route::get('/tickets', function () {
        $user      = auth()->user();
        $type      = request('type');
        $typeMap   = [
            'incident'        => 'Incident',
            'service_request' => 'Service Request',
            'question'        => 'Question',
            'change_request'  => 'Change Request',
        ];
        $typeFilter = $type ? ($typeMap[$type] ?? $type) : null;
        $submitted  = request()->boolean('submitted');
        $tickets   = \App\Models\Ticket::with(['user', 'notes.user', 'knowledgeArticles'])
            ->when($submitted, fn($q) => $q->where(function ($q) use ($user) {
                    $q->where('user_id', $user->id)
                      ->orWhere('requester_id', $user->id);
                }),
                fn($q) => $q->where(function ($q) use ($user) {
                    $q->where('user_id', $user->id)
                      ->orWhere('requester_id', $user->id)
                      ->orWhere('assignee', $user->name);
                    if ($user->department) {
                        $q->orWhere('department', $user->department);
                    }
                })
            )
            ->when($typeFilter, fn($q) => $q->where('type', $typeFilter))
            ->latest()
            ->get();
        $deptUsers = \App\Models\User::whereNotNull('department')
            ->orderBy('name')
            ->get(['id','name','department'])
            ->groupBy('department');
        $allKbas = \App\Models\KnowledgeArticle::orderBy('title')
            ->get(['id', 'kba_number', 'title', 'category']);
        $openTicket = null;
        if (request('open')) {
            $openTicket = \App\Models\Ticket::with(['user', 'notes.user', 'knowledgeArticles'])
                ->find((int) request('open'));
            if ($openTicket && ! $user->isSuperAdmin()
                && $openTicket->user_id !== $user->id
                && $openTicket->requester_id !== $user->id
                && $openTicket->requester !== $user->name
                && $openTicket->assignee !== $user->name
                && $openTicket->department !== $user->department) {
                $openTicket = null;
            }
        }
        $allowedDepts = auth()->user()->isSuperAdmin()
            ? \App\Models\SystemSetting::allDepartments()
            : auth()->user()->effectiveRoutingDepts();
        return view('tickets.index', compact('tickets', 'deptUsers', 'type', 'allKbas', 'openTicket', 'allowedDepts') + ['activePage' => 'tickets']);
    })->name('tickets.index');

    Route::get('/tickets/search', function () {
        $q    = trim(request('q', ''));
        $user = auth()->user();
        if (strlen($q) < 1) return response()->json([]);
        $tickets = \App\Models\Ticket::with('user')
            ->when(! $user->isSuperAdmin(), fn($query) => $query->where(function ($sub) use ($user) {
                $sub->where('user_id', $user->id)
                    ->orWhere('requester_id', $user->id)
                    ->orWhere('assignee', $user->name);
                if ($user->department) $sub->orWhere('department', $user->department);
            }))
            ->where(function ($query) use ($q) {
                $query->where('ticket_number', 'like', "%{$q}%")
                      ->orWhere('subject', 'like', "%{$q}%")
                      ->orWhereHas('user', fn($u) => $u->where('name', 'like', "%{$q}%"));
            })
            ->latest()->limit(12)->get();
        return response()->json($tickets->map(fn($t) => [
            'id'            => $t->id,
            'ticket_number' => $t->ticket_number,
            'subject'       => $t->subject,
            'status'        => $t->status,
            'priority'      => $t->priority,
            'requester'     => $t->requester ?? $t->user->name ?? 'Unknown',
        ]));
    })->name('tickets.search');

    Route::post('/tickets', [\App\Http\Controllers\TicketController::class, 'store'])
        ->name('tickets.store');

    Route::put('/tickets/{ticket}', [\App\Http\Controllers\TicketController::class, 'update'])
        ->name('tickets.update');

    Route::post('/tickets/{ticket}/assign-me', [\App\Http\Controllers\TicketController::class, 'assignToMe'])
        ->name('tickets.assign-me');

    Route::post('/tickets/{ticket}/route', [\App\Http\Controllers\TicketController::class, 'route'])
        ->name('tickets.route');

    Route::post('/tickets/{ticket}/reject', [\App\Http\Controllers\TicketController::class, 'reject'])
        ->name('tickets.reject');

    Route::post('/tickets/{ticket}/notes', [\App\Http\Controllers\TicketController::class, 'addNote'])
        ->name('tickets.notes.store');

    Route::get('/tickets/{ticket}/print', [\App\Http\Controllers\TicketController::class, 'printView'])
        ->name('tickets.print');

    Route::post('/tickets/{ticket}/kba/{article}', [\App\Http\Controllers\TicketController::class, 'attachKba'])
        ->name('tickets.kba.attach');

    Route::delete('/tickets/{ticket}/kba/{article}', [\App\Http\Controllers\TicketController::class, 'detachKba'])
        ->name('tickets.kba.detach');

    // ── My Queue ──
    Route::get('/queue', function () {
        $user    = auth()->user();
        $tickets = \App\Models\Ticket::with(['user','notes.user','knowledgeArticles'])
            ->where(function ($q) use ($user) {
                $q->where('assignee', $user->name)
                  ->orWhere('user_id', $user->id);
            })
            ->latest()->get();
        $deptUsers = \App\Models\User::whereNotNull('department')
            ->orderBy('name')->get(['id','name','department'])->groupBy('department');
        $allKbas = \App\Models\KnowledgeArticle::orderBy('title')
            ->get(['id', 'kba_number', 'title', 'category']);
        $allowedDepts = auth()->user()->isSuperAdmin()
            ? \App\Models\SystemSetting::allDepartments()
            : auth()->user()->effectiveRoutingDepts();
        $openTicket = null;
        if (request('open')) {
            $openTicket = \App\Models\Ticket::with(['user','notes.user','knowledgeArticles'])
                ->find((int) request('open'));
        }
        return view('queue', compact('tickets','deptUsers','allKbas','allowedDepts','openTicket') + ['activePage'=>'queue']);
    })->name('queue');

    // ── Agents ──
    Route::get('/agents', function () {
        if (!auth()->user()->isSuperAdmin()) {
            $p = auth()->user()->effectivePageAccess();
            abort_if(!($p['agents'] ?? false), 403, 'Access restricted by administrator.');
        }
        $ticketCounts = \App\Models\Ticket::selectRaw('assignee, status, count(*) as count')
            ->groupBy('assignee','status')->get()->groupBy('assignee');
        $agents = \App\Models\User::whereNotNull('department')->where('role','agent')->orderBy('department')->orderBy('name')->get()
            ->map(function ($u) use ($ticketCounts) {
                $rows = $ticketCounts->get($u->name, collect());
                $u->open_tickets     = $rows->where('status','open')->sum('count');
                $u->active_tickets   = $rows->where('status','progress')->sum('count');
                $u->resolved_tickets = $rows->whereIn('status',['resolved','closed'])->sum('count');
                return $u;
            });
        return view('agents.index', compact('agents') + ['activePage'=>'agents']);
    })->name('agents.index');

    Route::get('/agents/departments', function () {
        if (!auth()->user()->isSuperAdmin()) {
            $p = auth()->user()->effectivePageAccess();
            abort_if(!($p['agents'] ?? false), 403, 'Access restricted by administrator.');
        }
        $ticketCounts = \App\Models\Ticket::selectRaw('assignee, status, count(*) as count')
            ->groupBy('assignee', 'status')->get()->groupBy('assignee');
        $agents = \App\Models\User::whereNotNull('department')->where('role', 'agent')
            ->orderBy('department')->orderBy('name')->get()
            ->map(function ($u) use ($ticketCounts) {
                $rows = $ticketCounts->get($u->name, collect());
                $u->open_tickets     = $rows->where('status', 'open')->sum('count');
                $u->active_tickets   = $rows->where('status', 'progress')->sum('count');
                $u->resolved_tickets = $rows->whereIn('status', ['resolved', 'closed'])->sum('count');
                $u->total_tickets    = $u->open_tickets + $u->active_tickets + $u->resolved_tickets;
                return $u;
            });
        $departments = $agents->groupBy('department');
        return view('agents.departments', compact('departments') + ['activePage' => 'agents_departments']);
    })->name('agents.departments');

    Route::get('/reports/agents', function () {
        if (!auth()->user()->isSuperAdmin()) {
            $p = auth()->user()->effectivePageAccess();
            abort_if(!($p['reports'] ?? false), 403, 'Access restricted by administrator.');
        }
        $ticketCountsRaw = \App\Models\Ticket::selectRaw('assignee, status, count(*) as cnt')
            ->whereNotNull('assignee')->groupBy('assignee', 'status')->get()->groupBy('assignee');
        $agentAvgHrs = \App\Models\Ticket::selectRaw('assignee, avg(TIMESTAMPDIFF(MINUTE, created_at, resolved_at)) as avg_min')
            ->whereIn('status', ['resolved', 'closed'])->whereNotNull('resolved_at')->whereNotNull('assignee')
            ->groupBy('assignee')->pluck('avg_min', 'assignee');
        $agentPerf = \App\Models\User::whereNotNull('department')->where('role', 'agent')->orderBy('name')
            ->get(['name', 'department'])
            ->map(function ($user) use ($ticketCountsRaw, $agentAvgHrs) {
                $rows     = $ticketCountsRaw->get($user->name, collect());
                $total    = $rows->sum('cnt');
                $resolved = $rows->whereIn('status', ['resolved', 'closed'])->sum('cnt');
                $open     = $rows->where('status', 'open')->sum('cnt');
                $progress = $rows->where('status', 'progress')->sum('cnt');
                $avgMin   = $agentAvgHrs->get($user->name);
                return (object)[
                    'name'     => $user->name,
                    'dept'     => $user->department,
                    'total'    => $total,
                    'resolved' => $resolved,
                    'open'     => $open,
                    'progress' => $progress,
                    'rate'     => $total ? round($resolved / $total * 100) : 0,
                    'avg_hrs'  => $avgMin ? round($avgMin / 60, 1) : null,
                ];
            })->sortByDesc('total');
        return view('reports.agents', compact('agentPerf') + ['activePage' => 'reports_agents']);
    })->name('reports.agents');

    // ── Categories ──
    Route::get('/categories', function () {
        $catNames = ['IT Support','Hardware','Software','Network','HR','Finance','OPIC','Dispatch','Asset/Admin','Marketing','RSO','Store','Accounting','Security','General'];
        $rows = \App\Models\Ticket::selectRaw('category, status, count(*) as count')
            ->groupBy('category','status')->get()->groupBy('category');
        $categories = collect($catNames)->map(function ($name) use ($rows) {
            $r = $rows->get($name, collect());
            return ['name'=>$name,'total'=>$r->sum('count'),'open'=>$r->where('status','open')->sum('count'),'progress'=>$r->where('status','progress')->sum('count'),'resolved'=>$r->where('status','resolved')->sum('count'),'closed'=>$r->where('status','closed')->sum('count')];
        });
        return view('categories.index', compact('categories') + ['activePage'=>'categories']);
    })->name('categories.index');

    // ── Reports ──
    Route::get('/reports', function () {
        if (!auth()->user()->isSuperAdmin()) {
            $p = auth()->user()->effectivePageAccess();
            abort_if(!($p['reports'] ?? false), 403, 'Access restricted by administrator.');
        }
        $now        = now();
        $weekStart  = $now->copy()->startOfWeek();
        $monthStart = $now->copy()->startOfMonth();

        // All counts via DB aggregation — no full table load
        $statusCounts = \App\Models\Ticket::selectRaw('status, count(*) as cnt')->groupBy('status')->pluck('cnt','status');
        $total        = $statusCounts->sum();

        // Resolution time: only load resolved/closed tickets with timestamps
        $resolvedTickets = \App\Models\Ticket::select('priority','created_at','resolved_at')
            ->whereIn('status',['resolved','closed'])->whereNotNull('resolved_at')->get();
        $avgResHrs = $resolvedTickets->count()
            ? round($resolvedTickets->avg(fn($t) => $t->created_at->diffInMinutes($t->resolved_at)) / 60, 1)
            : null;
        $avgResByPriority = [];
        foreach (['high','medium','low'] as $pri) {
            $pts = $resolvedTickets->where('priority', $pri);
            $avgResByPriority[$pri] = $pts->count()
                ? round($pts->avg(fn($t) => $t->created_at->diffInMinutes($t->resolved_at)) / 60, 1)
                : null;
        }

        // SLA: only load sla_due_at + status columns
        $slaTickets = \App\Models\Ticket::select('status','priority','sla_due_at','created_at','resolved_at')
            ->whereNotNull('sla_due_at')->get();
        $sla = ['met'=>0,'ok'=>0,'warning'=>0,'breached'=>0];
        foreach ($slaTickets as $t) { $sla[$t->sla_status]++; }
        $slaTotal = $slaTickets->count();

        // Agent performance via DB aggregation
        $ticketCountsRaw = \App\Models\Ticket::selectRaw('assignee, status, count(*) as cnt')
            ->whereNotNull('assignee')->groupBy('assignee','status')->get()->groupBy('assignee');
        $agentAvgHrs = \App\Models\Ticket::selectRaw('assignee, avg(TIMESTAMPDIFF(MINUTE, created_at, resolved_at)) as avg_min')
            ->whereIn('status',['resolved','closed'])->whereNotNull('resolved_at')->whereNotNull('assignee')
            ->groupBy('assignee')->pluck('avg_min','assignee');
        $agentPerf = \App\Models\User::whereNotNull('department')->orderBy('name')
            ->get(['name','department'])
            ->map(function($user) use ($ticketCountsRaw, $agentAvgHrs) {
                $rows     = $ticketCountsRaw->get($user->name, collect());
                $total    = $rows->sum('cnt');
                if (! $total) return null;
                $resolved = $rows->whereIn('status',['resolved','closed'])->sum('cnt');
                $open     = $rows->whereIn('status',['open','progress'])->sum('cnt');
                $avgMin   = $agentAvgHrs->get($user->name);
                return (object)['name'=>$user->name,'dept'=>$user->department,'total'=>$total,'resolved'=>$resolved,'open'=>$open,'rate'=>round($resolved/$total*100),'avg_hrs'=>$avgMin ? round($avgMin/60,1) : null];
            })->filter()->sortByDesc('total')->take(20);

        $resolvedThisMonth = \App\Models\Ticket::whereIn('status',['resolved','closed'])
            ->whereNotNull('resolved_at')->where('resolved_at','>=',$monthStart)->count();

        $stats = [
            'total'               => $total,
            'open'                => $statusCounts->get('open', 0),
            'progress'            => $statusCounts->get('progress', 0),
            'resolved'            => $statusCounts->get('resolved', 0),
            'closed'              => $statusCounts->get('closed', 0),
            'this_week'           => \App\Models\Ticket::where('created_at','>=',$weekStart)->count(),
            'this_month'          => \App\Models\Ticket::where('created_at','>=',$monthStart)->count(),
            'resolved_this_month' => $resolvedThisMonth,
            'resolution_rate'     => $total ? round($resolvedTickets->count()/$total*100) : 0,
            'avg_res_hrs'         => $avgResHrs,
            'avg_res_by_priority' => $avgResByPriority,
            'sla'                 => $sla,
            'sla_compliance'      => $slaTotal ? round(($sla['met']/$slaTotal)*100) : 0,
            'sla_breach_rate'     => $slaTotal ? round(($sla['breached']/$slaTotal)*100) : 0,
            'breached'            => $sla['breached'],
            'by_priority'         => \App\Models\Ticket::selectRaw('priority, count(*) as cnt')->groupBy('priority')->pluck('cnt','priority'),
            'by_category'         => \App\Models\Ticket::selectRaw('category, count(*) as count')->groupBy('category')->orderByDesc('count')->get(),
            'by_department'       => \App\Models\Ticket::selectRaw('department, count(*) as count')->whereNotNull('department')->groupBy('department')->orderByDesc('count')->get(),
            'agent_perf'          => $agentPerf,
        ];

        return view('reports.index', compact('stats') + ['activePage'=>'reports']);
    })->name('reports.index');

    // ── Notifications ──
    Route::get('/notifications/data', function () {
        $notifs = \App\Models\TicketNotification::where('user_id', auth()->id())
            ->latest()->take(30)->get();
        return response()->json($notifs);
    })->name('notifications.data');

    Route::post('/notifications/read-all', function () {
        \App\Models\TicketNotification::where('user_id', auth()->id())
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
        return response()->json(['ok' => true]);
    })->name('notifications.read-all');

    Route::post('/notifications/{id}/read', function ($id) {
        \App\Models\TicketNotification::where('id', $id)
            ->where('user_id', auth()->id())
            ->update(['read_at' => now()]);
        return response()->json(['ok' => true]);
    })->name('notifications.read');

    // ── Knowledge Base ──
    Route::get('/knowledge-base', function () {
        if (!auth()->user()->isSuperAdmin()) {
            $p = auth()->user()->effectivePageAccess();
            abort_if(!($p['knowledge_read'] ?? false), 403, 'Access restricted by administrator.');
        }
        $articles = \App\Models\KnowledgeArticle::with(['user', 'tickets:id,ticket_number,subject'])
            ->withCount('tickets')
            ->latest()->get();
        return view('knowledge.index', ['activePage' => 'knowledge', 'articles' => $articles]);
    })->name('knowledge.index');

    Route::post('/knowledge-base', function (\Illuminate\Http\Request $request) {
        if (!auth()->user()->isSuperAdmin()) {
            $p = auth()->user()->effectivePageAccess();
            abort_if(!($p['knowledge_write'] ?? false), 403, 'Access restricted by administrator.');
        }
        $data = $request->validate([
            'title'    => ['required', 'string', 'max:255'],
            'category' => ['required', 'string'],
            'content'  => ['required', 'string'],
            'tags'     => ['nullable', 'string', 'max:255'],
            'status'   => ['required', 'in:draft,published'],
        ]);
        $data['user_id']    = auth()->id();
        $data['kba_number'] = \App\Models\KnowledgeArticle::generateNumber();
        \App\Models\KnowledgeArticle::create($data);
        return back()->with('kb_success', 'Article saved successfully!');
    })->name('knowledge.store');

    Route::put('/knowledge-base/{article}', function (\Illuminate\Http\Request $request, \App\Models\KnowledgeArticle $article) {
        abort_if($article->user_id !== auth()->id(), 403);
        $data = $request->validate([
            'title'    => ['required', 'string', 'max:255'],
            'category' => ['required', 'string'],
            'content'  => ['required', 'string'],
            'tags'     => ['nullable', 'string', 'max:255'],
            'status'   => ['required', 'in:draft,published'],
        ]);
        $article->update($data);
        return back()->with('kb_success', 'Article updated successfully!');
    })->name('knowledge.update');

    Route::delete('/knowledge-base/{article}', function (\App\Models\KnowledgeArticle $article) {
        abort_if($article->user_id !== auth()->id(), 403);
        $article->delete();
        return back()->with('kb_success', 'Article deleted.');
    })->name('knowledge.delete');

    // ── Agent Management (Super Admin only) ──
    Route::middleware('super_admin')->group(function () {
        Route::post('/agents', function (\Illuminate\Http\Request $request) {
            $data = $request->validate([
                'name'             => ['required', 'string', 'max:255'],
                'email'            => ['required', 'email', 'unique:users,email'],
                'password'         => ['required', 'string', 'min:8'],
                'department'       => ['required', 'string'],
                'role'             => ['required', 'in:agent,super_admin'],
                'username'         => ['nullable', 'string', 'max:100', 'unique:users,username'],
                'employee_id'      => ['nullable', 'string', 'max:100'],
                'job_title'        => ['nullable', 'string', 'max:255'],
                'primary_contact'  => ['nullable', 'string', 'max:50'],
            ]);
            $data['password'] = \Illuminate\Support\Facades\Hash::make($data['password']);
            \App\Models\User::create($data);
            return redirect()->route('agents.index')->with('agent_success', "Agent account created for {$data['name']}.");
        })->name('agents.store');
    });

    // ── Role Access & Permissions (Super Admin only) ──
    Route::middleware('super_admin')->group(function () {
        Route::get('/roles', function (\Illuminate\Http\Request $request) {
            $query = \App\Models\User::orderBy('name');
            if ($request->filled('department')) {
                $query->where('department', $request->department);
            }
            $users      = $query->paginate(6)->withQueryString();
            $totalUsers = \App\Models\User::count();
            $allDepts   = \App\Models\SystemSetting::allDepartments();
            return view('roles.index', compact('users', 'totalUsers', 'allDepts') + ['activePage' => 'roles']);
        })->name('roles.index');

        Route::put('/roles/{user}', function (\Illuminate\Http\Request $request, \App\Models\User $user) {
            $request->validate(['role' => ['required', 'in:agent,super_admin']]);
            if ($user->id === auth()->id() && $request->role !== 'super_admin') {
                return back()->with('role_error', 'You cannot remove your own Super Admin role.');
            }
            $user->update(['role' => $request->role]);
            return back()->with('role_success', "Role updated for {$user->name}.");
        })->name('roles.update');

        Route::put('/roles/{user}/toggle-active', function (\App\Models\User $user) {
            if ($user->id === auth()->id()) {
                return back()->with('role_error', 'You cannot disable your own account.');
            }
            $user->update(['is_active' => ! $user->is_active]);
            $status = $user->is_active ? 'enabled' : 'disabled';
            return back()->with('role_success', "Account {$status} for {$user->name}.")->withFragment('user-roles');
        })->name('roles.toggle-active');

        Route::put('/roles/{user}/access', function (\Illuminate\Http\Request $request, \App\Models\User $user) {
            $pageKeys   = ['reports', 'agents', 'knowledge_read', 'knowledge_write', 'settings'];
            $pageAccess = [];
            foreach ($pageKeys as $k) {
                $pageAccess[$k] = $request->boolean('page_' . $k);
            }
            $all    = \App\Models\SystemSetting::allDepartments();
            $depts  = array_values(array_filter($all, fn($d) => $request->boolean('dept_' . \Illuminate\Support\Str::slug($d))));
            $user->update(['page_access' => $pageAccess, 'routing_depts' => $depts]);
            return back()->with('role_success', "Access settings updated for {$user->name}.")->withFragment('user-roles');
        })->name('roles.user-access.update');

        Route::post('/roles/settings/page-access', function (\Illuminate\Http\Request $request) {
            $keys = ['reports', 'agents', 'knowledge_read', 'knowledge_write', 'settings'];
            $perms = [];
            foreach ($keys as $k) {
                $perms[$k] = $request->boolean($k);
            }
            \App\Models\SystemSetting::set('agent_page_access', $perms);
            return back()->with('role_success', 'Agent page access updated.');
        })->name('roles.settings.page-access');

        Route::post('/roles/settings/routing-departments', function (\Illuminate\Http\Request $request) {
            $all      = \App\Models\SystemSetting::allDepartments();
            $selected = array_filter($all, fn($d) => $request->boolean('dept_' . \Illuminate\Support\Str::slug($d)));
            \App\Models\SystemSetting::set('agent_routing_departments', array_values($selected));
            return back()->with('role_success', 'Allowed routing departments updated.');
        })->name('roles.settings.routing-departments');

        Route::delete('/roles/{user}', function (\App\Models\User $user) {
            if ($user->id === auth()->id()) {
                return back()->with('role_error', 'You cannot delete your own account.');
            }
            $name = $user->name;
            $user->delete();
            return back()->with('role_success', "Account deleted for {$name}.");
        })->name('roles.destroy');

        Route::put('/roles/{user}/reset-password', function (\App\Models\User $user) {
            if ($user->id === auth()->id()) {
                return back()->with('role_error', 'You cannot reset your own password this way.');
            }
            $user->update(['password' => \Illuminate\Support\Facades\Hash::make('Password@123')]);
            return back()->with('role_success', "Password reset for {$user->name}. New password: Password@123")->withFragment('user-roles');
        })->name('roles.reset-password');
    });

    // ── Integrations ──
    Route::get('/integrations', function () {
        if (!auth()->user()->isSuperAdmin()) {
            $p = auth()->user()->effectivePageAccess();
            abort_if(!($p['settings'] ?? false), 403, 'Access restricted by administrator.');
        }
        return view('system.integrations', ['activePage' => 'integrations']);
    })->name('integrations.index');

    // ── Audit Logs ──
    Route::get('/audit-logs', function (\Illuminate\Http\Request $request) {
        if (!auth()->user()->isSuperAdmin()) {
            $p = auth()->user()->effectivePageAccess();
            abort_if(!($p['settings'] ?? false), 403, 'Access restricted by administrator.');
        }
        try {
            // Auto-log newly breached SLA tickets
            $breached = \App\Models\Ticket::whereNotIn('status', ['resolved', 'closed'])
                ->whereNotNull('sla_due_at')
                ->where('sla_due_at', '<', now())
                ->get();
            foreach ($breached as $ticket) {
                $already = \App\Models\AuditLog::where('action', 'ticket.sla_breached')
                    ->where('subject_id', $ticket->id)->exists();
                if (!$already) {
                    \App\Models\AuditLog::create([
                        'user_id'       => null,
                        'action'        => 'ticket.sla_breached',
                        'subject_type'  => \App\Models\Ticket::class,
                        'subject_id'    => $ticket->id,
                        'subject_label' => $ticket->ticket_number,
                        'old_values'    => null,
                        'new_values'    => null,
                        'ip_address'    => null,
                        'description'   => "SLA breached for ticket {$ticket->ticket_number} (Priority: {$ticket->priority}, Due: {$ticket->sla_due_at->format('M d, Y h:i A')}).",
                    ]);
                }
            }

            // Build filtered query
            $query = \App\Models\AuditLog::with('user')->latest();

            if ($request->filled('action_filter')) {
                $query->where('action', 'like', $request->action_filter . '%');
            }
            if ($request->filled('date_from')) {
                $query->whereDate('created_at', '>=', $request->date_from);
            }
            if ($request->filled('date_to')) {
                $query->whereDate('created_at', '<=', $request->date_to);
            }
            if ($request->filled('user_filter')) {
                $query->whereHas('user', fn($q) => $q->where('name', 'like', '%'.$request->user_filter.'%'))
                      ->orWhere('subject_label', 'like', '%'.$request->user_filter.'%');
            }

            $logs = $query->paginate(50)->withQueryString();
        } catch (\Exception $e) {
            $logs = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 50);
        }
        return view('system.audit-logs', compact('logs') + ['activePage' => 'audit_logs']);
    })->name('audit.logs');

    // ── Settings ──
    Route::get('/settings', function () {
        if (!auth()->user()->isSuperAdmin()) {
            $p = auth()->user()->effectivePageAccess();
            abort_if(!($p['settings'] ?? false), 403, 'Access restricted by administrator.');
        }
        return view('settings.index', ['activePage'=>'settings']);
    })->name('settings.index');

    Route::put('/settings/profile', [\App\Http\Controllers\SettingsController::class, 'updateProfile'])
        ->name('settings.profile');

    Route::put('/settings/password', [\App\Http\Controllers\SettingsController::class, 'updatePassword'])
        ->name('settings.password');
});

Route::post('/logout', function (\Illuminate\Http\Request $request) {
    \Illuminate\Support\Facades\Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();
    return redirect()->route('login');
})->middleware('auth')->name('logout');
