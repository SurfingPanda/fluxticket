@if($log->user)
<div style="display:flex;align-items:center;gap:.5rem">
    <div style="width:26px;height:26px;min-width:26px;background:linear-gradient(135deg,#4f46e5,#7c3aed);border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:.6rem;font-weight:700;color:white;flex-shrink:0">{{ strtoupper(substr($log->user->name, 0, 1)) }}</div>
    <span style="white-space:nowrap;font-size:.8rem">{{ $log->user->name }}</span>
</div>
@elseif($log->subject_label && str_contains($log->action, 'login'))
<span style="color:var(--muted);font-size:.78rem;font-style:italic">{{ $log->subject_label }}</span>
@else
<span style="color:var(--muted);font-size:.78rem">System</span>
@endif
