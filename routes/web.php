<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check() ? redirect()->route('dashboard') : redirect()->route('login');
});

Route::get('/login', function () {
    return view('auth.login');
})->middleware('guest')->name('login');

Route::post('/login', function (\Illuminate\Http\Request $request) {
    $request->validate([
        'email'    => ['required', 'string'],
        'password' => ['required'],
    ]);

    $login    = trim($request->input('email'));
    $password = $request->input('password');

    // Determine if the input looks like an email
    $isEmail = filter_var($login, FILTER_VALIDATE_EMAIL);

    if ($isEmail) {
        // Try email login
        $attempted = \Illuminate\Support\Facades\Auth::attempt(
            ['email' => $login, 'password' => $password],
            $request->boolean('remember')
        );
        if ($attempted) {
            if (! auth()->user()->is_active) {
                \Illuminate\Support\Facades\Auth::logout();
                $request->session()->invalidate();
                return back()->withErrors([
                    'email' => 'Your account has been disabled. Please contact your administrator.',
                ])->onlyInput('email');
            }
            $request->session()->regenerate();
            return redirect()->intended(route('dashboard'));
        }
        return back()->withErrors([
            'email' => 'The email address or password is incorrect.',
        ])->onlyInput('email');
    }

    // Username login — check if any user has this username set
    $user = \App\Models\User::where('username', $login)->first();

    if (! $user) {
        return back()->withErrors([
            'email' => 'Username does not exist.',
        ])->onlyInput('email');
    }

    if (! \Illuminate\Support\Facades\Auth::attempt(
        ['email' => $user->email, 'password' => $password],
        $request->boolean('remember')
    )) {
        return back()->withErrors([
            'email' => 'The password is incorrect.',
        ])->onlyInput('email');
    }

    if (! auth()->user()->is_active) {
        \Illuminate\Support\Facades\Auth::logout();
        $request->session()->invalidate();
        return back()->withErrors([
            'email' => 'Your account has been disabled. Please contact your administrator.',
        ])->onlyInput('email');
    }

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
              ->orWhere('assignee', $user->name);
            if ($user->department) {
                $q->orWhere('department', $user->department);
            }
        })->pluck('id');

        $recentActivity = \App\Models\TicketNote::with(['ticket', 'user'])
            ->whereIn('ticket_id', $relatedTicketIds)
            ->latest()
            ->take(20)
            ->get();

        return view('dashboard', compact('tickets', 'stats', 'deptUsers', 'recentActivity'));
    })->name('dashboard');

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
        $tickets   = \App\Models\Ticket::with(['user', 'notes.user', 'knowledgeArticles'])
            ->where(function ($q) use ($user) {
                $q->where('user_id', $user->id)
                  ->orWhere('assignee', $user->name);
                if ($user->department) {
                    $q->orWhere('department', $user->department);
                }
            })
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
        }
        $allowedDepts = auth()->user()->isSuperAdmin()
            ? \App\Models\SystemSetting::allDepartments()
            : auth()->user()->effectiveRoutingDepts();
        return view('tickets.index', compact('tickets', 'deptUsers', 'type', 'allKbas', 'openTicket', 'allowedDepts'));
    })->name('tickets.index');

    // Global ticket search (all tickets, no ownership filter)
    Route::get('/tickets/search', function () {
        $q = trim(request('q', ''));
        if (strlen($q) < 1) return response()->json([]);
        $tickets = \App\Models\Ticket::with('user')
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
            'requester'     => $t->user->name ?? 'Unknown',
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
        return view('queue', compact('tickets','deptUsers','allKbas','allowedDepts') + ['activePage'=>'queue']);
    })->name('queue');

    // ── Agents ──
    Route::get('/agents', function () {
        if (!auth()->user()->isSuperAdmin()) {
            $p = auth()->user()->effectivePageAccess();
            abort_if(!($p['agents'] ?? true), 403, 'Access restricted by administrator.');
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

    // ── Categories ──
    Route::get('/categories', function () {
        $catNames = ['IT Support','Hardware','Software','Network','HR','Finance','OPIC','Dispatch','Asset/Admin','Marketing','RSO','Store','General'];
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
            abort_if(!($p['reports'] ?? true), 403, 'Access restricted by administrator.');
        }
        $allTickets = \App\Models\Ticket::all();
        $now        = now();
        $weekStart  = $now->copy()->startOfWeek();
        $monthStart = $now->copy()->startOfMonth();

        // SLA statuses
        $sla = ['met'=>0,'ok'=>0,'warning'=>0,'breached'=>0];
        foreach ($allTickets as $t) { $sla[$t->sla_status]++; }

        // Resolution time helpers
        $resolvedTickets = $allTickets->whereIn('status',['resolved','closed'])->filter(fn($t) => $t->resolved_at);
        $avgResHrs = $resolvedTickets->count()
            ? round($resolvedTickets->avg(fn($t) => $t->created_at->diffInMinutes($t->resolved_at)) / 60, 1)
            : null;

        $avgResByPriority = [];
        foreach (['high','medium','low'] as $p) {
            $pts = $resolvedTickets->where('priority',$p);
            $avgResByPriority[$p] = $pts->count()
                ? round($pts->avg(fn($t) => $t->created_at->diffInMinutes($t->resolved_at)) / 60, 1)
                : null;
        }

        // Agent performance
        $ticketCountsRaw = \App\Models\Ticket::selectRaw('assignee, status, count(*) as cnt')
            ->whereNotNull('assignee')->groupBy('assignee','status')->get()->groupBy('assignee');

        $agentPerf = \App\Models\User::whereNotNull('department')->orderBy('name')->get()
            ->map(function($user) use ($ticketCountsRaw, $allTickets) {
                $rows     = $ticketCountsRaw->get($user->name, collect());
                $total    = $rows->sum('cnt');
                $resolved = $rows->whereIn('status',['resolved','closed'])->sum('cnt');
                $open     = $rows->whereIn('status',['open','progress'])->sum('cnt');
                $agRes    = $allTickets->where('assignee',$user->name)->whereIn('status',['resolved','closed'])->filter(fn($t)=>$t->resolved_at);
                $avgHrs   = $agRes->count() ? round($agRes->avg(fn($t)=>$t->created_at->diffInMinutes($t->resolved_at))/60,1) : null;
                return (object)['name'=>$user->name,'dept'=>$user->department,'total'=>$total,'resolved'=>$resolved,'open'=>$open,'rate'=>$total>0?round($resolved/$total*100):0,'avg_hrs'=>$avgHrs];
            })->filter(fn($a)=>$a->total>0)->sortByDesc('total')->take(20);

        $slaTotal = $allTickets->whereNotNull('sla_due_at')->count();

        $stats = [
            'total'              => $allTickets->count(),
            'open'               => $allTickets->where('status','open')->count(),
            'progress'           => $allTickets->where('status','progress')->count(),
            'resolved'           => $allTickets->where('status','resolved')->count(),
            'closed'             => $allTickets->where('status','closed')->count(),
            'this_week'          => $allTickets->where('created_at','>=',$weekStart)->count(),
            'this_month'         => $allTickets->where('created_at','>=',$monthStart)->count(),
            'resolved_this_month'=> $resolvedTickets->filter(fn($t)=>$t->resolved_at->gte($monthStart))->count(),
            'resolution_rate'    => $allTickets->count() ? round($resolvedTickets->count()/$allTickets->count()*100) : 0,
            'avg_res_hrs'        => $avgResHrs,
            'avg_res_by_priority'=> $avgResByPriority,
            'sla'                => $sla,
            'sla_compliance'     => $slaTotal ? round(($sla['met']/$slaTotal)*100) : 0,
            'sla_breach_rate'    => $slaTotal ? round(($sla['breached']/$slaTotal)*100) : 0,
            'breached'           => $sla['breached'],
            'by_priority'        => ['high'=>$allTickets->where('priority','high')->count(),'medium'=>$allTickets->where('priority','medium')->count(),'low'=>$allTickets->where('priority','low')->count()],
            'by_category'        => \App\Models\Ticket::selectRaw('category, count(*) as count')->groupBy('category')->orderByDesc('count')->get(),
            'by_department'      => \App\Models\Ticket::selectRaw('department, count(*) as count')->whereNotNull('department')->groupBy('department')->orderByDesc('count')->get(),
            'agent_perf'         => $agentPerf,
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
            abort_if(!($p['knowledge_read'] ?? true), 403, 'Access restricted by administrator.');
        }
        $articles = \App\Models\KnowledgeArticle::with(['user', 'tickets:id,ticket_number,subject'])
            ->withCount('tickets')
            ->latest()->get();
        return view('knowledge.index', ['activePage' => 'knowledge', 'articles' => $articles]);
    })->name('knowledge.index');

    Route::post('/knowledge-base', function (\Illuminate\Http\Request $request) {
        if (!auth()->user()->isSuperAdmin()) {
            $p = auth()->user()->effectivePageAccess();
            abort_if(!($p['knowledge_write'] ?? true), 403, 'Access restricted by administrator.');
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
    });

    // ── Settings ──
    Route::get('/settings', function () {
        if (!auth()->user()->isSuperAdmin()) {
            $p = auth()->user()->effectivePageAccess();
            abort_if(!($p['settings'] ?? true), 403, 'Access restricted by administrator.');
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
