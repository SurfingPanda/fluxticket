@extends('layouts.app')
@section('title','Categories')
@section('topbar-title','Categories')
@section('topbar-sub','Ticket categories and volume overview')

@push('styles')
<style>
    .cat-grid { display:grid;grid-template-columns:repeat(auto-fill,minmax(260px,1fr));gap:1rem; }
    .cat-card { background:var(--surface);border:1px solid var(--border);border-radius:.875rem;padding:1.25rem;transition:border-color .2s,transform .2s,box-shadow .2s;display:flex;flex-direction:column;gap:.75rem; }
    .cat-card:hover { border-color:rgba(99,102,241,.35);transform:translateY(-2px);box-shadow:0 8px 24px rgba(0,0,0,.15); }
    .cat-icon { width:40px;height:40px;border-radius:.65rem;display:flex;align-items:center;justify-content:center;font-size:1.1rem;flex-shrink:0; }
    .bar-track { height:6px;background:var(--surface2);border-radius:9999px;overflow:hidden;margin-top:.35rem; }
    .bar-fill { height:100%;border-radius:9999px;transition:width .6s ease; }
</style>
@endpush

@section('content')
@php
    $maxTotal = $categories->max('total') ?: 1;
    $icons = [
        'IT Support'   => ['bi-laptop','rgba(99,102,241,.15)','#818cf8'],
        'Hardware'     => ['bi-hdd','rgba(251,191,36,.12)','#fbbf24'],
        'Software'     => ['bi-code-square','rgba(59,130,246,.12)','#60a5fa'],
        'Network'      => ['bi-wifi','rgba(52,211,153,.12)','#34d399'],
        'HR'           => ['bi-person-badge','rgba(236,72,153,.12)','#f472b6'],
        'Finance'      => ['bi-currency-dollar','rgba(16,185,129,.12)','#10b981'],
        'OPIC'         => ['bi-shield-check','rgba(139,92,246,.12)','#a78bfa'],
        'Dispatch'     => ['bi-truck','rgba(245,158,11,.12)','#f59e0b'],
        'Asset/Admin'  => ['bi-box-seam','rgba(239,68,68,.12)','#f87171'],
        'Marketing'    => ['bi-megaphone','rgba(236,72,153,.12)','#ec4899'],
        'RSO'          => ['bi-shield-lock','rgba(99,102,241,.12)','#6366f1'],
        'Store'        => ['bi-shop','rgba(34,197,94,.12)','#22c55e'],
        'General'      => ['bi-question-circle','rgba(148,163,184,.12)','#94a3b8'],
    ];
@endphp
<div class="cat-grid">
    @foreach($categories as $cat)
    @php
        [$ico, $bg, $color] = $icons[$cat['name']] ?? ['bi-tag','rgba(148,163,184,.12)','#94a3b8'];
        $pct = $maxTotal > 0 ? round(($cat['total'] / $maxTotal) * 100) : 0;
    @endphp
    <div class="cat-card">
        <div style="display:flex;align-items:center;gap:.75rem">
            <div class="cat-icon" style="background:{{ $bg }}">
                <i class="bi {{ $ico }}" style="color:{{ $color }}"></i>
            </div>
            <div>
                <div style="font-size:.9rem;font-weight:700;color:var(--text)">{{ $cat['name'] }}</div>
                <div style="font-size:.72rem;color:var(--muted)">{{ $cat['total'] }} ticket{{ $cat['total'] !== 1 ? 's' : '' }} total</div>
            </div>
        </div>
        <div class="bar-track">
            <div class="bar-fill" style="width:{{ $pct }}%;background:{{ $color }}"></div>
        </div>
        <div style="display:flex;gap:.75rem;flex-wrap:wrap">
            @if($cat['open'])    <span style="font-size:.72rem;padding:.15rem .55rem;border-radius:9999px;background:rgba(99,102,241,.12);color:#818cf8">{{ $cat['open'] }} Open</span>@endif
            @if($cat['progress'])<span style="font-size:.72rem;padding:.15rem .55rem;border-radius:9999px;background:rgba(251,191,36,.12);color:#fbbf24">{{ $cat['progress'] }} In Progress</span>@endif
            @if($cat['resolved'])<span style="font-size:.72rem;padding:.15rem .55rem;border-radius:9999px;background:rgba(52,211,153,.12);color:#34d399">{{ $cat['resolved'] }} Resolved</span>@endif
            @if($cat['closed'])  <span style="font-size:.72rem;padding:.15rem .55rem;border-radius:9999px;background:rgba(148,163,184,.12);color:#94a3b8">{{ $cat['closed'] }} Closed</span>@endif
            @if(!$cat['total'])  <span style="font-size:.72rem;color:var(--muted);font-style:italic">No tickets yet</span>@endif
        </div>
    </div>
    @endforeach
</div>
@endsection
