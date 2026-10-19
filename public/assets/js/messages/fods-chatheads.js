// Messenger-style chat heads on every page (markup root in
// templates/myfooter.php). On the Messages page itself they only show chats
// popped out with its "Open as chat head" button (no new-message heads, and
// the topbar dropdown opens chats in the page as usual). Chats open in
// small windows docked at the bottom and stay open while you move around
// FODS; a minimized chat, or a new message in a chat that isn't open, shows
// as a round head on the right with its unread count. Uses the Messages
// endpoints (CONV-LOAD reads + marks read, MSG-SEND, CONV-TYPING) and the
// topbar inbox's updates (fods-inbox.js fires "fods:convs").
(function () {
	var root = document.getElementById('fodsChatHeads');
	if (!root) { return; }

	var url = root.getAttribute('data-url');
	var onMessages = root.getAttribute('data-page') === 'messages';
	var me = String(root.getAttribute('data-me') || '').toUpperCase();
	var KEY = 'fods.chatheads.' + me;
	var POLL_MS = 3000;          // open windows: new messages, seen, typing
	var TYPING_PING_MS = 3000;
	var MAX_HEADS = 6;
	var IMAGE_EXT = /\.(png|jpe?g|gif|webp)$/i;

	var dock = root.querySelector('.fods-ch-dock');
	var heads = root.querySelector('.fods-ch-heads');
	var wins = {};               // conv id -> open window
	// open: expanded chats (newest first); mins: minimized heads; info: name/type for heads;
	// popped: last_at of the message a head was popped (or closed) for
	var state = { open: [], mins: [], info: {}, popped: {} };
	var unread = {};             // conv id -> unread count (from the topbar inbox)

	// ------------------------------------------------------------------ helpers
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
	function isMe(u) { return String(u || '').toUpperCase() === me; }
	function toDate(v) { var d = new Date(String(v || '').replace(' ', 'T')); return isNaN(d) ? null : d; }
	function fmtTime(v) { var d = toDate(v); return d ? d.toLocaleTimeString('en-PH', { hour: 'numeric', minute: '2-digit' }) : ''; }
	function dayLabel(v) {
		var d = toDate(v); if (!d) { return ''; }
		var today = new Date(); today.setHours(0, 0, 0, 0);
		var that = new Date(d); that.setHours(0, 0, 0, 0);
		var diff = Math.round((today - that) / 86400000);
		return diff === 0 ? 'Today' : diff === 1 ? 'Yesterday' : d.toLocaleDateString('en-PH', { month: 'short', day: 'numeric', year: d.getFullYear() === today.getFullYear() ? undefined : 'numeric' });
	}
	function enabled() { return window.innerWidth >= 768; }
	function maxOpen() { return Math.max(1, Math.min(3, Math.floor((window.innerWidth - 360) / 340))); }

	function save() {
		try { sessionStorage.setItem(KEY, JSON.stringify(state)); } catch (e) { /* storage blocked */ }
	}
	function load() {
		try {
			var s = JSON.parse(sessionStorage.getItem(KEY) || 'null');
			if (s && Array.isArray(s.open)) { state = { open: s.open, mins: s.mins || [], info: s.info || {}, popped: s.popped || {} }; }
		} catch (e) { /* storage blocked */ }
	}

	function post(data) {
		return fetch(url, { method: 'POST', body: data, credentials: 'same-origin', headers: { 'X-Requested-With': 'XMLHttpRequest' } })
			.then(function (r) { return r.json().then(function (j) { if (!r.ok || !j.ok) { throw new Error((j && j.error) || 'Failed'); } return j; }); });
	}
	function refreshInbox() { if (window.fodsInbox) { window.fodsInbox.refresh(); } }
	function toast(msg) { if (window.toastr) { toastr.error(msg, 'Oops!', { closeButton: true, timeOut: 2500 }); } }

	function remember(conv) {
		state.info[conv.recid] = { name: conv.name, type: conv.conv_type };
	}

	// ------------------------------------------------------------------ heads (minimized / new)
	function renderHeads() {
		heads.innerHTML = state.mins.map(function (id) {
			var info = state.info[id] || { name: 'Chat', type: 'direct' };
			var n = unread[id] || 0;
			return '<div class="fods-ch-bubblehead" data-conv="' + id + '">'
				+ '<button type="button" class="fods-ch-headbtn" data-open="' + id + '" title="' + esc(info.name) + '" aria-label="Open chat with ' + esc(info.name) + '">'
				+ (info.type === 'group' ? '<i class="ti ti-users"></i>' : esc(initials(info.name))) + '</button>'
				+ (n ? '<span class="fods-ch-badge">' + (n > 99 ? '99+' : n) + '</span>' : '')
				+ '<button type="button" class="fods-ch-headx" data-close="' + id + '" title="Close" aria-label="Close chat"><i class="ti ti-x"></i></button>'
				+ '</div>';
		}).join('');
	}

	// ------------------------------------------------------------------ windows
	function makeWindow(id) {
		var info = state.info[id] || { name: 'Chat', type: 'direct' };
		var el = document.createElement('section');
		el.className = 'fods-ch-win';
		el.tabIndex = -1;   // clicking the window focuses it, so a paste lands here
		el.setAttribute('data-conv', id);
		el.innerHTML = '<header class="fods-ch-top">'
			+ '<span class="fods-ch-av">' + (info.type === 'group' ? '<i class="ti ti-users"></i>' : esc(initials(info.name))) + '</span>'
			+ '<div class="fods-ch-title"><div class="fods-ch-name">' + esc(info.name) + '</div><div class="fods-ch-sub"></div></div>'
			+ '<button type="button" class="fods-ch-icon" data-min="' + id + '" title="Minimize" aria-label="Minimize"><i class="ti ti-minus"></i></button>'
			+ '<button type="button" class="fods-ch-icon" data-close="' + id + '" title="Close" aria-label="Close"><i class="ti ti-x"></i></button>'
			+ '</header>'
			+ '<div class="fods-ch-body"><div class="fods-ch-loading"><span class="spinner-border spinner-border-sm"></span></div></div>'
			+ '<div class="fods-ch-typing d-none"><span class="fods-ch-dots"><i></i><i></i><i></i></span><span class="fods-ch-typing-text"></span></div>'
			+ '<div class="fods-ch-files d-none"></div>'
			+ '<form class="fods-ch-compose" autocomplete="off">'
			+ '<label class="fods-ch-icon fods-ch-attach" title="Attach files (up to 5, 10 MB each), or paste a screenshot with Ctrl+V"><i class="ti ti-paperclip"></i>'
			+ '<input type="file" multiple hidden accept="image/png,image/jpeg,image/gif,image/webp,.pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.csv,.txt,.zip"></label>'
			+ '<textarea rows="1" maxlength="2000" placeholder="Aa" aria-label="Message"></textarea>'
			+ '<button type="submit" class="fods-ch-send" aria-label="Send"><i class="ti ti-send"></i></button>'
			+ '</form>';

		var w = { id: id, el: el, conv: null, lastId: 0, lastDay: '', lastSender: '', polling: false, loaded: false, files: [], typingAt: 0 };
		var body = el.querySelector('.fods-ch-body');
		var text = el.querySelector('textarea');
		var form = el.querySelector('form');
		var fileInput = el.querySelector('input[type=file]');

		text.addEventListener('input', function () {
			text.style.height = 'auto'; text.style.height = Math.min(text.scrollHeight, 96) + 'px';
			if (text.value.trim() === '') { stopTyping(w); }
			else if (Date.now() - w.typingAt > TYPING_PING_MS) { sendTyping(w, true); }
		});
		text.addEventListener('keydown', function (e) {
			if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); form.requestSubmit ? form.requestSubmit() : form.dispatchEvent(new Event('submit', { cancelable: true })); }
		});
		// paste a screenshot anywhere in the window (it takes focus when clicked)
		el.addEventListener('paste', function (e) {
			var f = clipboardFiles(e);
			if (f.length) { e.preventDefault(); addFiles(w, f); }
		});
		fileInput.addEventListener('change', function () { addFiles(w, fileInput.files); fileInput.value = ''; });
		form.addEventListener('submit', function (e) { e.preventDefault(); send(w); });
		el.addEventListener('click', function (e) {
			var x = e.target.closest('[data-unfile]');
			if (x) { w.files.splice(+x.getAttribute('data-unfile'), 1); renderFiles(w); }
		});
		body.addEventListener('scroll', function () { /* keep position: nothing to do */ });
		return w;
	}

	// Files on the clipboard: a copied file, or a screenshot (Snipping Tool,
	// PrtScn, Win+Shift+S), which some browsers only expose through items.
	// Screenshots arrive as "image.png" or nameless, so they get a dated name.
	function clipboardFiles(e) {
		var cd = e.clipboardData;
		if (!cd) { return []; }
		var list = Array.prototype.slice.call(cd.files || []);
		if (!list.length && cd.items) {
			Array.prototype.forEach.call(cd.items, function (it) {
				var f = it.kind === 'file' ? it.getAsFile() : null;
				if (f) { list.push(f); }
			});
		}
		var d = new Date(), pad = function (n) { return (n < 10 ? '0' : '') + n; };
		var stamp = d.getFullYear() + pad(d.getMonth() + 1) + pad(d.getDate()) + '-' + pad(d.getHours()) + pad(d.getMinutes()) + pad(d.getSeconds());
		return list.map(function (f, i) {
			if (!/^image\//.test(f.type) || (f.name && !/^image\.\w+$/i.test(f.name))) { return f; }
			var ext = { 'image/jpeg': 'jpg', 'image/gif': 'gif', 'image/webp': 'webp' }[f.type] || 'png';
			try { return new File([f], 'screenshot-' + stamp + (i ? '-' + (i + 1) : '') + '.' + ext, { type: f.type }); } catch (x) { return f; }
		});
	}

	function filesHtml(files) {
		if (!files || !files.length) { return ''; }
		return '<div class="fods-ch-atts">' + files.map(function (f) {
			var href = url + '?meaction=CHAT-FILE&id=' + f.recid;
			return f.is_image
				? '<a class="fods-ch-thumb" href="' + href + '" target="_blank" rel="noopener" title="' + esc(f.name) + '"><img src="' + href + '" alt="' + esc(f.name) + '" loading="lazy"></a>'
				: '<a class="fods-ch-file" href="' + href + '" download title="Download ' + esc(f.name) + '"><i class="ti ti-file"></i><span>' + esc(f.name) + '</span></a>';
		}).join('') + '</div>';
	}

	function msgHtml(w, m) {
		var html = '';
		var day = dayLabel(m.added_at);
		if (day !== w.lastDay) { html += '<div class="fods-ch-day">' + esc(day) + '</div>'; w.lastDay = day; w.lastSender = ''; }
		var mine = isMe(m.added_by);
		if (m.is_system) {
			w.lastSender = '';
			return html + '<div class="fods-ch-note">' + esc((mine ? 'You' : m.sender_name) + ' ' + m.message) + '</div>';
		}
		var named = !mine && w.conv && w.conv.conv_type === 'group' && m.added_by !== w.lastSender;
		html += '<div class="fods-ch-row' + (mine ? ' mine' : '') + '" data-id="' + m.recid + '">'
			+ (named ? '<div class="fods-ch-sender">' + esc(String(m.sender_name).split(' ')[0]) + '</div>' : '')
			+ '<div class="fods-ch-msg" title="' + esc(fmtTime(m.added_at)) + '">'
			+ (m.message !== '' ? '<div class="fods-ch-text">' + esc(m.message) + '</div>' : '') + filesHtml(m.files)
			+ '</div></div>';
		w.lastSender = m.added_by;
		return html;
	}

	// Under your latest message: Sent / Seen <time> (direct) or Seen by N (group)
	function renderSeen(w) {
		var body = w.el.querySelector('.fods-ch-body');
		body.querySelectorAll('.fods-ch-status').forEach(function (e) { e.remove(); });
		if (!w.conv) { return; }
		var mine = body.querySelectorAll('.fods-ch-row.mine');
		var last = mine[mine.length - 1];
		if (!last) { return; }
		var id = parseInt(last.getAttribute('data-id'), 10);
		var others = w.conv.members.filter(function (m) { return !isMe(m.username); });
		var readers = others.filter(function (m) { return m.last_read_id >= id; });
		var text = 'Sent';
		if (readers.length && w.conv.conv_type !== 'group') { text = 'Seen' + (readers[0].last_read_at ? ' ' + fmtTime(readers[0].last_read_at) : ''); }
		else if (readers.length) { text = readers.length === others.length ? 'Seen by everyone' : 'Seen by ' + readers.length + ' of ' + others.length; }
		last.insertAdjacentHTML('beforeend', '<div class="fods-ch-status' + (readers.length ? ' is-seen' : '') + '">' + esc(text) + '</div>');
	}

	function renderTyping(w) {
		var box = w.el.querySelector('.fods-ch-typing');
		var who = w.conv ? w.conv.members.filter(function (m) { return !isMe(m.username) && m.is_typing; }) : [];
		box.classList.toggle('d-none', !who.length);
		if (who.length) {
			var first = function (m) { return String(m.full_name).split(' ')[0]; };
			box.querySelector('.fods-ch-typing-text').textContent = who.length === 1 ? first(who[0]) + ' is typing…' : who.length === 2 ? first(who[0]) + ' and ' + first(who[1]) + ' are typing…' : 'Several people are typing…';
		}
	}

	function setHeader(w) {
		var c = w.conv;
		remember(c);
		w.el.querySelector('.fods-ch-name').textContent = c.name;
		w.el.querySelector('.fods-ch-av').innerHTML = c.conv_type === 'group' ? '<i class="ti ti-users"></i>' : esc(initials(c.name));
		w.el.querySelector('.fods-ch-sub').textContent = c.conv_type === 'group' ? c.members.length + ' people' : '';
	}

	function append(w, list, force) {
		var body = w.el.querySelector('.fods-ch-body');
		var near = body.scrollHeight - body.scrollTop - body.clientHeight < 80;
		var html = '';
		list.forEach(function (m) { if (m.recid > w.lastId) { html += msgHtml(w, m); w.lastId = m.recid; } });
		if (html) {
			var none = body.querySelector('.fods-ch-none');
			if (none) { none.remove(); }
			body.insertAdjacentHTML('beforeend', html);
			if (force || near) { body.scrollTop = body.scrollHeight; }
		}
		renderSeen(w);
		renderTyping(w);
	}

	function fetchConv(w, initial) {
		if (w.polling) { return Promise.resolve(); }
		w.polling = true;
		var q = new URLSearchParams({ meaction: 'CONV-LOAD', conv_id: w.id });
		if (!initial) { q.set('after_id', w.lastId); }
		return fetch(url + '?' + q.toString(), { credentials: 'same-origin', headers: { 'X-Requested-With': 'XMLHttpRequest' } })
			.then(function (r) {
				if (r.status === 404) { throw { gone: true }; }
				return r.json();
			})
			.then(function (data) {
				if (!wins[w.id]) { return; }
				var had = unread[w.id] || 0;
				w.conv = data.conv;
				if (initial) {
					setHeader(w);
					w.el.querySelector('.fods-ch-body').innerHTML = data.messages.length ? '' : '<div class="fods-ch-none">No messages yet — say hello.</div>';
					w.loaded = true;
				}
				append(w, data.messages, initial);
				// reading it here clears it in the topbar too
				if (data.messages.length && (initial || had)) { unread[w.id] = 0; refreshInbox(); }
			})
			.catch(function (e) {
				if (e && e.gone) { toast("You're no longer in that chat."); close(w.id); }
			})
			.then(function () { w.polling = false; });
	}

	// ------------------------------------------------------------------ typing + sending
	function sendTyping(w, on) {
		w.typingAt = on ? Date.now() : 0;
		var fd = new FormData();
		fd.append('meaction', 'CONV-TYPING'); fd.append('conv_id', w.id); fd.append('typing', on ? 1 : 0);
		post(fd).catch(function () {});
	}
	function stopTyping(w) { if (w.typingAt) { sendTyping(w, false); } }

	function addFiles(w, list) {
		Array.prototype.forEach.call(list || [], function (f) { if (w.files.length < 5) { w.files.push(f); } });
		renderFiles(w);
	}
	function renderFiles(w) {
		var box = w.el.querySelector('.fods-ch-files');
		box.classList.toggle('d-none', !w.files.length);
		box.innerHTML = w.files.map(function (f, i) {
			return '<span class="fods-ch-chip"><i class="ti ' + (IMAGE_EXT.test(f.name) ? 'ti-photo' : 'ti-file') + '"></i>' + esc(f.name)
				+ '<button type="button" data-unfile="' + i + '" aria-label="Remove ' + esc(f.name) + '">&times;</button></span>';
		}).join('');
	}

	function send(w) {
		var text = w.el.querySelector('textarea');
		var message = text.value.trim();
		if ((!message && !w.files.length) || w.sending) { return; }
		var fd = new FormData();
		fd.append('meaction', 'MSG-SEND'); fd.append('conv_id', w.id); fd.append('message', message);
		w.files.forEach(function (f) { fd.append('attachments[]', f, f.name); });
		w.sending = true;
		var btn = w.el.querySelector('.fods-ch-send');
		btn.disabled = true;
		post(fd)
			.then(function () {
				text.value = ''; text.style.height = 'auto';
				w.files = []; renderFiles(w);
				w.typingAt = 0;        // the server cleared it with the message
				w.polling = false;
				return fetchConv(w, false);
			})
			.catch(function (e) { toast(e.message || 'Your message was not sent.'); })
			.then(function () { w.sending = false; btn.disabled = false; text.focus(); });
	}

	// ------------------------------------------------------------------ open / minimize / close
	function open(id, info, focus) {
		id = parseInt(id, 10);
		if (!id) { return; }
		if (info) { state.info[id] = { name: info.name, type: info.conv_type || info.type }; }
		state.mins = state.mins.filter(function (x) { return x !== id; });
		state.open = [id].concat(state.open.filter(function (x) { return x !== id; }));
		// too many open: the oldest ones fold into heads
		while (state.open.length > maxOpen()) {
			var old = state.open.pop();
			minimizeOnly(old);
		}
		if (!wins[id]) {
			var w = makeWindow(id);
			wins[id] = w;
			dock.appendChild(w.el);
			fetchConv(w, true);
		}
		orderDock();
		save();
		renderHeads();
		if (focus) { setTimeout(function () { var t = wins[id] && wins[id].el.querySelector('textarea'); if (t) { t.focus(); } }, 50); }
	}

	function minimizeOnly(id) {
		var w = wins[id];
		if (w) { stopTyping(w); w.el.remove(); delete wins[id]; }
		state.open = state.open.filter(function (x) { return x !== id; });
		state.mins = [id].concat(state.mins.filter(function (x) { return x !== id; })).slice(0, MAX_HEADS);
	}

	function minimize(id) { minimizeOnly(id); orderDock(); save(); renderHeads(); }

	function close(id) {
		id = parseInt(id, 10);
		var w = wins[id];
		if (w) { stopTyping(w); w.el.remove(); delete wins[id]; }
		state.open = state.open.filter(function (x) { return x !== id; });
		state.mins = state.mins.filter(function (x) { return x !== id; });
		save();
		renderHeads();
	}

	// newest window sits closest to the right edge
	function orderDock() {
		state.open.forEach(function (id) { if (wins[id]) { dock.appendChild(wins[id].el); } });
	}

	root.addEventListener('click', function (e) {
		var b;
		if ((b = e.target.closest('[data-open]'))) { open(b.getAttribute('data-open'), null, true); }
		else if ((b = e.target.closest('[data-min]'))) { minimize(parseInt(b.getAttribute('data-min'), 10)); }
		else if ((b = e.target.closest('[data-close]'))) { close(b.getAttribute('data-close')); }
		else if ((b = e.target.closest('.fods-ch-top'))) {
			// clicking a window's title bar (not its buttons) minimizes it, like Messenger
			if (!e.target.closest('button')) { minimize(parseInt(b.closest('.fods-ch-win').getAttribute('data-conv'), 10)); }
		}
	});

	// ------------------------------------------------------------------ topbar inbox
	// news from the inbox poll: unread counts, and pop a head for a new message
	document.addEventListener('fods:convs', function (e) {
		var convs = e.detail || [];
		convs.forEach(function (c) {
			unread[c.recid] = wins[c.recid] ? 0 : (c.unread || 0);
			if (state.info[c.recid] || c.unread) { remember(c); }
			if (c.unread && !onMessages && !wins[c.recid] && state.mins.indexOf(c.recid) === -1 && state.popped[c.recid] !== c.last_at && enabled()) {
				state.popped[c.recid] = c.last_at;
				state.mins = [c.recid].concat(state.mins).slice(0, MAX_HEADS);
			}
		});
		save();
		renderHeads();
	});

	// a chat picked in the topbar dropdown opens here instead of leaving the page
	document.addEventListener('click', function (e) {
		var a = e.target.closest('#fodsInboxBody a[data-conv]');
		if (!a || onMessages || !enabled() || e.ctrlKey || e.metaKey || e.shiftKey) { return; }
		e.preventDefault();
		var btn = document.getElementById('fodsInboxBtn');
		if (btn && window.bootstrap) { bootstrap.Dropdown.getOrCreateInstance(btn).hide(); }
		open(a.getAttribute('data-conv'), { name: a.getAttribute('data-name'), type: a.getAttribute('data-type') }, true);
	});

	// ------------------------------------------------------------------ polling
	setInterval(function () {
		if (document.hidden) { return; }
		Object.keys(wins).forEach(function (id) { if (wins[id].loaded) { fetchConv(wins[id], false); } });
	}, POLL_MS);
	document.addEventListener('visibilitychange', function () {
		if (document.hidden) { Object.keys(wins).forEach(function (id) { stopTyping(wins[id]); }); }
	});
	window.addEventListener('pagehide', function () { Object.keys(wins).forEach(function (id) { stopTyping(wins[id]); }); });
	window.addEventListener('resize', function () {
		while (state.open.length > maxOpen()) { minimizeOnly(state.open.pop()); }
		save(); renderHeads();
	});

	// ------------------------------------------------------------------ start: bring back what was open
	load();
	if (enabled()) {
		var reopen = state.open.slice().reverse();
		state.open = [];
		reopen.forEach(function (id) { open(id, null, false); });
	}
	renderHeads();

	window.fodsChatHeads = { open: open, close: close, enabled: enabled };
})();
