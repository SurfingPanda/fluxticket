{{-- ── Notification Bell ── --}}
<div style="position:relative" id="notifWrap">
    <button id="notifBtn" onclick="toggleNotif(event)" title="Notifications"
        style="position:relative;background:var(--surface2);border:1px solid var(--border);border-radius:.6rem;width:34px;height:34px;display:flex;align-items:center;justify-content:center;color:var(--muted);cursor:pointer;font-size:.9rem;transition:background .15s,color .15s;outline:none">
        <i class="bi bi-bell"></i>
        <span id="notifBadge" style="position:absolute;top:4px;right:4px;min-width:16px;height:16px;background:#f87171;border-radius:9999px;border:2px solid var(--surface);font-size:.55rem;font-weight:700;color:white;display:none;align-items:center;justify-content:center;padding:0 3px;line-height:1"></span>
    </button>

    <div id="notifDropdown" style="display:none;position:absolute;top:calc(100% + 8px);right:0;width:320px;background:var(--surface);border:1px solid var(--border);border-radius:1rem;box-shadow:0 20px 60px rgba(0,0,0,.45);z-index:1001;overflow:hidden">
        <div style="padding:.7rem 1rem;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between">
            <span style="font-size:.82rem;font-weight:700;color:var(--text)">Notifications</span>
            <button onclick="markAllRead()" style="font-size:.7rem;color:#818cf8;background:none;border:none;cursor:pointer;font-weight:600;padding:0">Mark all read</button>
        </div>
        <div id="notifList" style="max-height:360px;overflow-y:auto">
            <div style="padding:2rem;text-align:center;color:var(--muted);font-size:.82rem">
                <i class="bi bi-arrow-repeat" style="font-size:1.2rem;display:block;margin-bottom:.5rem;opacity:.4"></i>
                Loading…
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    const CSRF = '{{ csrf_token() }}';
    let notifOpen = false;
    let notifData = [];

    const typeIcon  = { assigned:'bi-person-check-fill', status_changed:'bi-arrow-repeat', note_added:'bi-chat-left-text-fill', routed:'bi-arrow-left-right' };
    const typeColor = { assigned:'#818cf8', status_changed:'#34d399', note_added:'#fbbf24', routed:'#60a5fa' };

    function timeAgo(dateStr) {
        const diff = (Date.now() - new Date(dateStr)) / 1000;
        if (diff < 60)    return 'just now';
        if (diff < 3600)  return Math.floor(diff / 60) + 'm ago';
        if (diff < 86400) return Math.floor(diff / 3600) + 'h ago';
        return Math.floor(diff / 86400) + 'd ago';
    }

    function renderNotifications() {
        const list  = document.getElementById('notifList');
        const badge = document.getElementById('notifBadge');
        const unread = notifData.filter(n => !n.read_at).length;

        if (unread > 0) {
            badge.textContent  = unread > 9 ? '9+' : unread;
            badge.style.display = 'flex';
        } else {
            badge.style.display = 'none';
        }

        if (!notifData.length) {
            list.innerHTML = '<div style="padding:2rem;text-align:center;color:var(--muted);font-size:.82rem"><i class="bi bi-bell-slash" style="font-size:1.5rem;display:block;margin-bottom:.5rem;opacity:.4"></i>No notifications yet</div>';
            return;
        }

        list.innerHTML = notifData.map(n => {
            const isUnread = !n.read_at;
            const icon  = typeIcon[n.type]  || 'bi-bell-fill';
            const color = typeColor[n.type] || '#818cf8';
            const bg    = isUnread ? 'rgba(99,102,241,.06)' : 'transparent';
            return `<div onclick="openNotif(${n.id},${n.ticket_id})"
                style="display:flex;align-items:flex-start;gap:.65rem;padding:.7rem 1rem;border-bottom:1px solid var(--border);cursor:pointer;background:${bg};transition:background .15s"
                onmouseover="this.style.background='var(--surface2)'" onmouseout="this.style.background='${bg}'">
                <div style="width:28px;height:28px;min-width:28px;border-radius:50%;background:${color}22;display:flex;align-items:center;justify-content:center;margin-top:2px;flex-shrink:0">
                    <i class="bi ${icon}" style="font-size:.7rem;color:${color}"></i>
                </div>
                <div style="flex:1;min-width:0">
                    <div style="font-size:.72rem;color:var(--muted);font-weight:700;font-family:monospace;margin-bottom:.1rem">${n.ticket_number}</div>
                    <div style="font-size:.8rem;color:var(--text);line-height:1.4;white-space:normal">${n.message}</div>
                    <div style="font-size:.68rem;color:var(--muted);margin-top:.2rem">${timeAgo(n.created_at)}</div>
                </div>
                ${isUnread ? '<span style="width:7px;height:7px;min-width:7px;background:#818cf8;border-radius:50%;flex-shrink:0;margin-top:10px"></span>' : ''}
            </div>`;
        }).join('');
    }

    async function loadNotifications() {
        try {
            const r = await fetch('/notifications/data', { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
            notifData = await r.json();
            renderNotifications();
        } catch (e) {
            document.getElementById('notifList').innerHTML =
                '<div style="padding:1.5rem;text-align:center;color:var(--muted);font-size:.8rem">Could not load notifications.</div>';
        }
    }

    window.openNotif = async function (id, ticketId) {
        // Mark as read (fire-and-forget)
        fetch('/notifications/' + id + '/read', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': CSRF, 'X-Requested-With': 'XMLHttpRequest' }
        });
        const n = notifData.find(x => x.id === id);
        if (n) n.read_at = new Date().toISOString();
        renderNotifications();

        // Navigate to the ticket
        if (ticketId) {
            window.location.href = '/tickets?open=' + ticketId;
        }
    };

    // Keep markRead for backwards compat
    window.markRead = async function (id) { openNotif(id, null); };

    window.markAllRead = async function () {
        await fetch('/notifications/read-all', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': CSRF, 'X-Requested-With': 'XMLHttpRequest' }
        });
        notifData.forEach(n => n.read_at = new Date().toISOString());
        renderNotifications();
        // Close the dropdown
        notifOpen = true;
        toggleNotif();
    };

    window.toggleNotif = function (e) {
        e && e.stopPropagation();
        const dd  = document.getElementById('notifDropdown');
        const btn = document.getElementById('notifBtn');
        notifOpen = !notifOpen;
        dd.style.display = notifOpen ? 'block' : 'none';
        if (notifOpen) {
            loadNotifications();
            btn.style.background = 'linear-gradient(135deg,rgba(99,102,241,.25),rgba(124,58,237,.2))';
            btn.style.borderColor = 'rgba(99,102,241,.5)';
            btn.style.color       = '#818cf8';
            btn.style.boxShadow   = '0 0 0 3px rgba(99,102,241,.15)';
        } else {
            btn.style.background  = '';
            btn.style.borderColor = '';
            btn.style.color       = '';
            btn.style.boxShadow   = '';
        }
    };

    document.addEventListener('click', function (e) {
        const wrap = document.getElementById('notifWrap');
        if (wrap && !wrap.contains(e.target)) {
            notifOpen = false;
            const dd  = document.getElementById('notifDropdown');
            const btn = document.getElementById('notifBtn');
            if (dd)  dd.style.display  = 'none';
            if (btn) {
                btn.style.background  = '';
                btn.style.borderColor = '';
                btn.style.color       = '';
                btn.style.boxShadow   = '';
            }
        }
    });

    // Load badge count silently on page load
    loadNotifications();
})();
</script>
