// Topbar inbox for templates/myheader.php: unread badge (refreshed every
// minute while the tab is visible) and the dropdown of latest chats and
// ticket updates, loaded each time it opens. The Messages page calls
// window.fodsInbox.refresh() after it marks things read.
(function () {
	var btn = document.getElementById('fodsInboxBtn');
	if (!btn) { return; }

	var url = btn.getAttribute('data-url');
	var badge = document.getElementById('fodsInboxBadge');
	var body = document.getElementById('fodsInboxBody');
	var POLL_MS = 60000;

	function esc(s) {
		return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) {
			return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
		});
	}

	function initials(name) {
		var base = String(name || '').split(',')[0].trim();
		var t = base.split(/\s+/).filter(function (x) { return x && x.slice(-1) !== '.'; });
		if (!t.length) { return base.charAt(0).toUpperCase(); }
		return (t[0].charAt(0) + (t.length > 1 ? t[t.length - 1].charAt(0) : '')).toUpperCase();
	}

	function ago(dt) {
		var d = new Date(String(dt || '').replace(' ', 'T'));
		if (isNaN(d)) { return ''; }
		var s = Math.max(0, (Date.now() - d.getTime()) / 1000);
		if (s < 60) { return 'now'; }
		if (s < 3600) { return Math.floor(s / 60) + 'm'; }
		if (s < 86400) { return Math.floor(s / 3600) + 'h'; }
		if (s < 604800) { return Math.floor(s / 86400) + 'd'; }
		return d.toLocaleDateString('en-PH', { month: 'short', day: 'numeric' });
	}

	function setBadge(n) {
		n = parseInt(n, 10) || 0;
		badge.hidden = n <= 0;
		badge.textContent = n > 99 ? '99+' : String(n);
	}

	function load() {
		return fetch(url + '?meaction=INBOX', { credentials: 'same-origin', headers: { 'X-Requested-With': 'XMLHttpRequest' } })
			.then(function (r) { if (!r.ok) { throw new Error(r.status); } return r.json(); });
	}

	function render(data) {
		var html = '';
		if (data.tickets.length) {
			html += '<div class="fods-inbox-label">Ticket updates</div>';
			data.tickets.forEach(function (t) {
				html += '<a class="fods-inbox-item is-unread" href="' + url + '?meaction=MAIN&tab=tickets&ticket=' + t.recid + '">'
					+ '<span class="fods-avatar tone-amber"><i class="ti ti-lifebuoy"></i></span>'
					+ '<span class="fods-inbox-main"><span class="fods-inbox-title"><span>' + esc(t.subject) + '</span><small>' + ago(t.updated_at) + '</small></span>'
					+ '<span class="fods-inbox-text d-block">' + esc(t.ticket_no) + ' · ' + esc(t.status) + '</span></span></a>';
			});
		}
		html += '<div class="fods-inbox-label">Chats</div>';
		if (!data.convs.length) {
			html += '<div class="fods-inbox-empty">No chats yet — start one from Messages.</div>';
		}
		data.convs.forEach(function (c) {
			var text = c.last_message == null ? 'No messages yet' : (c.last_message === '' ? 'Sent an attachment' : c.last_message);
			html += '<a class="fods-inbox-item' + (c.unread ? ' is-unread' : '') + '" href="' + url + '?meaction=MAIN&conv=' + c.recid + '">'
				+ '<span class="fods-avatar ' + (c.conv_type === 'group' ? 'tone-indigo' : 'tone-teal') + '">' + (c.conv_type === 'group' ? '<i class="ti ti-users"></i>' : esc(initials(c.name))) + '</span>'
				+ '<span class="fods-inbox-main"><span class="fods-inbox-title"><span>' + esc(c.name) + '</span><small>' + ago(c.last_at) + '</small></span>'
				+ '<span class="fods-inbox-text d-block">' + esc(text) + '</span></span>'
				+ (c.unread ? '<span class="badge bg-danger rounded-pill">' + c.unread + '</span>' : '')
				+ '</a>';
		});
		body.innerHTML = html;
	}

	function refresh() {
		return load().then(function (data) { setBadge(data.unread); return data; }).catch(function () {});
	}

	btn.addEventListener('show.bs.dropdown', function () {
		load().then(function (data) { setBadge(data.unread); render(data); })
			.catch(function () { body.innerHTML = '<div class="fods-inbox-empty">Couldn\'t load your messages. Try again.</div>'; });
	});

	setInterval(function () { if (!document.hidden) { refresh(); } }, POLL_MS);
	document.addEventListener('visibilitychange', function () { if (!document.hidden) { refresh(); } });

	window.fodsInbox = { refresh: refresh };
})();
