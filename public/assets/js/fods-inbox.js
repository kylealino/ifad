// Topbar for templates/myheader.php: the bell (notifications: approvals
// waiting for you, MyNotifications; badge loaded after the page, then every
// minute; fresh numbers when opened), the Messages icon (chats) and, beside
// it, the Helpdesk icon (tickets). Each has an unread badge (chats checked
// every 20 s, tickets every minute, while the tab is visible) and a dropdown
// loaded each time it opens. Each chats update is also announced as the
// "fods:convs" event, which the chat heads (fods-chatheads.js) use to pop up
// new messages. The Messages and Helpdesk pages call window.fodsInbox.refresh()
// after they mark things read.
(function () {
	var boxes = [];

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

	// Messages: latest chats
	function renderChats(url, data) {
		if (!data.convs.length) {
			return '<div class="fods-inbox-empty">No chats yet — start one with New message.</div>';
		}
		return data.convs.map(function (c) {
			var typing = (c.members || []).filter(function (m) { return m.is_typing; }).length;
			var text = typing ? 'typing…' : c.last_message == null ? 'No messages yet' : (c.last_message === '' ? 'Sent an attachment' : c.last_message);
			return '<a class="fods-inbox-item' + (c.unread ? ' is-unread' : '') + '" href="' + url + '?meaction=MAIN&conv=' + c.recid + '"'
				+ ' data-conv="' + c.recid + '" data-name="' + esc(c.name) + '" data-type="' + esc(c.conv_type) + '">'
				+ '<span class="fods-avatar ' + (c.conv_type === 'group' ? 'tone-indigo' : 'tone-teal') + '">' + (c.conv_type === 'group' ? '<i class="ti ti-users"></i>' : esc(initials(c.name))) + '</span>'
				+ '<span class="fods-inbox-main"><span class="fods-inbox-title"><span>' + esc(c.name) + '</span><small>' + ago(c.last_at) + '</small></span>'
				+ '<span class="fods-inbox-text d-block' + (typing ? ' text-success' : '') + '">' + esc(text) + '</span></span>'
				+ (c.unread ? '<span class="badge bg-danger rounded-pill">' + c.unread + '</span>' : '')
				+ '</a>';
		}).join('');
	}

	// Helpdesk: tickets with news first, then your other open tickets
	function renderTickets(url, data) {
		if (!data.tickets.length) {
			return '<div class="fods-inbox-empty">No ticket updates. Need help? Use New ticket.</div>';
		}
		return data.tickets.map(function (t) {
			var bug = t.category === 'Bug report';
			return '<a class="fods-inbox-item' + (t.unread ? ' is-unread' : '') + '" href="' + url + '?meaction=MAIN&tab=' + (t.mine ? 'tickets' : 'desk') + '&ticket=' + t.recid + '">'
				+ '<span class="fods-avatar ' + (bug ? 'tone-red' : 'tone-amber') + '"><i class="ti ' + (bug ? 'ti-bug' : 'ti-lifebuoy') + '"></i></span>'
				+ '<span class="fods-inbox-main"><span class="fods-inbox-title"><span>' + esc(t.subject) + '</span><small>' + ago(t.updated_at) + '</small></span>'
				+ '<span class="fods-inbox-text d-block">' + esc(t.ticket_no) + ' · ' + esc(t.status) + (t.unread ? ' · new reply' : '') + '</span></span>'
				+ (t.unread ? '<span class="badge bg-danger rounded-pill">' + t.unread + '</span>' : '')
				+ '</a>';
		}).join('');
	}

	// Notifications (the bell): one row per module with something waiting for
	// you, linking to the page where it is decided
	function renderNotifs(url, data) {
		if (!data.groups || !data.groups.length) {
			return '<div class="fods-inbox-empty"><i class="ti ti-circle-check d-block mb-1" style="font-size:1.5rem;color:#15803d"></i>You\'re all caught up. Nothing is waiting for you.</div>';
		}
		return data.groups.map(function (g) {
			return '<a class="fods-inbox-item is-unread" href="' + esc(g.url) + '">'
				+ '<span class="fods-avatar ' + (g.system === 'SORF' ? 'tone-indigo' : 'tone-green') + '"><i class="ti ' + esc(g.icon) + '"></i></span>'
				+ '<span class="fods-inbox-main"><span class="fods-inbox-title"><span>' + esc(g.count) + ' ' + esc(g.label) + ' to approve</span><small>' + esc(g.system) + '</small></span>'
				+ (g.oldest ? '<span class="fods-inbox-text d-block">' + esc(g.oldest) + '</span>' : '')
				+ '</span>'
				+ '<span class="badge bg-danger rounded-pill">' + esc(g.count) + '</span>'
				+ '</a>';
		}).join('');
	}

	// openQuery: extra query string when the dropdown opens (fresh numbers)
	function setup(btnId, badgeId, bodyId, render, pollMs, onData, openQuery) {
		var btn = document.getElementById(btnId);
		if (!btn) { return; }
		var url = btn.getAttribute('data-url');
		var badge = document.getElementById(badgeId);
		var body = document.getElementById(bodyId);

		function setBadge(n) {
			n = parseInt(n, 10) || 0;
			badge.hidden = n <= 0;
			badge.textContent = n > 99 ? '99+' : String(n);
		}

		function load(extra) {
			return fetch(url + '?meaction=INBOX' + (extra || ''), { credentials: 'same-origin', headers: { 'X-Requested-With': 'XMLHttpRequest' } })
				.then(function (r) { if (!r.ok) { throw new Error(r.status); } return r.json(); })
				.then(function (data) { if (onData) { onData(data); } return data; });
		}

		btn.addEventListener('show.bs.dropdown', function () {
			load(openQuery).then(function (data) { setBadge(data.unread); body.innerHTML = render(url, data); })
				.catch(function () { body.innerHTML = '<div class="fods-inbox-empty">Couldn\'t load this. Try again.</div>'; });
		});

		var refreshOne = function () { return load().then(function (data) { setBadge(data.unread); return data; }).catch(function () {}); };
		boxes.push(refreshOne);
		setInterval(function () { if (!document.hidden) { refreshOne(); } }, pollMs);
	}

	setup('fodsInboxBtn', 'fodsInboxBadge', 'fodsInboxBody', renderChats, 20000, function (data) {
		document.dispatchEvent(new CustomEvent('fods:convs', { detail: data.convs || [] }));
	});
	setup('fodsHelpdeskBtn', 'fodsHelpdeskBadge', 'fodsHelpdeskBody', renderTickets, 60000);
	var notifAt = boxes.length;
	setup('fodsNotifBtn', 'fodsNotifBadge', 'fodsNotifBody', renderNotifs, 60000, null, '&fresh=1');
	if (!boxes.length) { return; }
	// the bell's badge is not rendered with the page (it can take a moment): load it now
	if (boxes.length > notifAt) { setTimeout(boxes[notifAt], 600); }

	function refresh() { return Promise.all(boxes.map(function (f) { return f(); })); }

	// the chat heads want the current unread counts as soon as the page is up
	if (document.getElementById('fodsChatHeads')) { setTimeout(function () { boxes[0](); }, 800); }

	document.addEventListener('visibilitychange', function () { if (!document.hidden) { refresh(); } });

	window.fodsInbox = { refresh: refresh };
})();
