@extends('layouts.app')
@section('title','Integrations')
@section('topbar-title','Integrations')
@section('topbar-sub','Connect FluxTickets with external services and tools')

@push('styles')
    .int-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(300px,1fr)); gap:1rem; }
    .int-card { background:var(--surface); border:1px solid var(--border); border-radius:1rem; padding:1.35rem; display:flex; flex-direction:column; gap:.85rem; transition:border-color .2s,transform .2s,box-shadow .2s; }
    .int-card:hover { border-color:rgba(99,102,241,.3); transform:translateY(-2px); box-shadow:0 8px 24px rgba(0,0,0,.15); }
    .int-logo { width:44px; height:44px; border-radius:.65rem; display:flex; align-items:center; justify-content:center; font-size:1.3rem; flex-shrink:0; }
    .int-status-on  { display:inline-flex; align-items:center; gap:.3rem; background:rgba(52,211,153,.12); color:#34d399; border-radius:9999px; padding:.18rem .65rem; font-size:.68rem; font-weight:700; }
    .int-status-off { display:inline-flex; align-items:center; gap:.3rem; background:var(--surface2); color:var(--muted); border-radius:9999px; padding:.18rem .65rem; font-size:.68rem; font-weight:700; border:1px solid var(--border); }
    .int-status-on::before  { content:''; width:6px; height:6px; border-radius:50%; background:#34d399; }
    .int-status-off::before { content:''; width:6px; height:6px; border-radius:50%; background:var(--muted); }
    .int-btn { width:100%; background:var(--surface2); border:1px solid var(--border); border-radius:.6rem; color:var(--muted); font-size:.8rem; font-weight:600; padding:.45rem .85rem; cursor:pointer; transition:all .15s; text-align:center; }
    .int-btn:hover { background:rgba(99,102,241,.1); border-color:rgba(99,102,241,.3); color:#818cf8; }
    .int-btn.connected { background:rgba(248,113,113,.08); border-color:rgba(248,113,113,.25); color:#f87171; }
    .int-btn.connected:hover { background:rgba(248,113,113,.15); }
    .coming-badge { display:inline-flex; align-items:center; padding:.15rem .55rem; border-radius:9999px; font-size:.63rem; font-weight:700; background:rgba(251,191,36,.1); color:#fbbf24; border:1px solid rgba(251,191,36,.2); }
@endpush

@section('content')
@php
$integrations = [
    [
        'name'        => 'Email (SMTP)',
        'description' => 'Send ticket notifications, replies, and alerts via your SMTP server.',
        'icon'        => '✉️',
        'bg'          => 'rgba(99,102,241,.12)',
        'connected'   => false,
        'coming'      => false,
    ],
    [
        'name'        => 'Slack',
        'description' => 'Post ticket updates and alerts to Slack channels in real time.',
        'icon'        => '💬',
        'bg'          => 'rgba(74,144,226,.12)',
        'connected'   => false,
        'coming'      => true,
    ],
    [
        'name'        => 'Microsoft Teams',
        'description' => 'Receive ticket notifications directly in your Teams workspace.',
        'icon'        => '🟦',
        'bg'          => 'rgba(99,102,241,.08)',
        'connected'   => false,
        'coming'      => true,
    ],
    [
        'name'        => 'Google Workspace',
        'description' => 'Sync users and authenticate via Google accounts.',
        'icon'        => '🔵',
        'bg'          => 'rgba(52,211,153,.08)',
        'connected'   => false,
        'coming'      => true,
    ],
    [
        'name'        => 'Webhook',
        'description' => 'Send real-time HTTP payloads to any external URL on ticket events.',
        'icon'        => '🔗',
        'bg'          => 'rgba(251,191,36,.1)',
        'connected'   => false,
        'coming'      => false,
    ],
    [
        'name'        => 'REST API',
        'description' => 'Integrate FluxTickets into your own apps using the REST API and API keys.',
        'icon'        => '⚙️',
        'bg'          => 'rgba(248,113,113,.08)',
        'connected'   => false,
        'coming'      => false,
    ],
];
@endphp

<div class="int-grid">
    @foreach($integrations as $i)
    <div class="int-card">
        <div style="display:flex;align-items:center;gap:.85rem">
            <div class="int-logo" style="background:{{ $i['bg'] }}">{{ $i['icon'] }}</div>
            <div style="flex:1;min-width:0">
                <div style="font-size:.9rem;font-weight:700;color:var(--text);display:flex;align-items:center;gap:.5rem">
                    {{ $i['name'] }}
                    @if($i['coming'])<span class="coming-badge">Coming Soon</span>@endif
                </div>
                <div style="font-size:.75rem;color:var(--muted);margin-top:.15rem">{{ $i['description'] }}</div>
            </div>
            <div>
                @if($i['connected'])
                    <span class="int-status-on">Connected</span>
                @else
                    <span class="int-status-off">Not Connected</span>
                @endif
            </div>
        </div>
        @if(!$i['coming'])
        <button class="int-btn {{ $i['connected'] ? 'connected' : '' }}" @disabled($i['coming'])>
            {{ $i['connected'] ? 'Disconnect' : 'Configure' }}
        </button>
        @else
        <button class="int-btn" disabled style="opacity:.45;cursor:not-allowed">Configure</button>
        @endif
    </div>
    @endforeach
</div>
@endsection
