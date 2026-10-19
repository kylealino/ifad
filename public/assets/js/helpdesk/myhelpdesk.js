var __mysys_helpdesk_ent = new __mysys_helpdesk_ent();
function __mysys_helpdesk_ent() {
	const mesiteurl = document.getElementById('__siteurl').getAttribute('data-mesiteurl');
	const url = mesiteurl + 'myhelpdesk';
	const perms = window.__hd_perms || {};
	const open = window.__hd_open || {};
	const me = String(window.__hd_me || '').toUpperCase();
	let tickets = window.__hd_tickets || [];
	const ticketById = {};
	tickets.forEach(function (t) { ticketById[t.recid] = t; });

	const STATUS = {
		'Open': ['tone-amber', 'Open'],
		'In progress': ['tone-indigo', 'In progress'],
		'Resolved': ['tone-green', 'Resolved'],
		'Closed': ['tone-slate', 'Closed']
	};
	const PRIORITY = { 'Low': 'tone-slate', 'Normal': 'tone-teal', 'High': 'tone-red' };
	// status buttons in the ticket review footer: [label, icon, style]
	const STATUS_ACTIONS = {
		'In progress': ['Mark in progress', 'ti-clock-play', 'btn-fods-ghost'],
		'Resolved': ['Mark resolved', 'ti-circle-check', 'btn-fods'],
		'Closed': ['Close ticket', 'ti-archive', 'btn-fods-ghost'],
		'Open': ['Reopen', 'ti-refresh', 'btn-fods-ghost']
	};
	const IMAGE_TYPES = ['image/png', 'image/jpeg', 'image/gif', 'image/webp'];
	const MAX_FILES = 5;
	const MAX_BYTES = 5 * 1024 * 1024;               // per screenshot
	const LIST_MS = 20000;                           // ticket list refresh
	const DOC_EXTS =['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'csv', 'txt', 'zip'];
	const FILE_ICON = { pdf: 'ti-file-text', doc: 'ti-file-text', docx: 'ti-file-text', txt: 'ti-file-text', xls: 'ti-file-spreadsheet', xlsx: 'ti-file-spreadsheet', csv: 'ti-file-spreadsheet', zip: 'ti-file-zip' };

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

	function isMe(username) { return String(username || '').toUpperCase() === me; }

	function toDate(v) { var d = new Date(String(v || '').replace(' ', 'T')); return isNaN(d) ? null : d; }

	function fmtTime(v) {
		var d = toDate(v);
		return d ? d.toLocaleTimeString('en-PH', { hour: 'numeric', minute: '2-digit' }) : '';
	}

	function fmtDate(v) {
		var d = toDate(v);
		return d ? d.toLocaleDateString('en-PH', { month: 'short', day: 'numeric', year: 'numeric' }) : '';
	}

	function fmtDay(v) {
		var d = toDate(v);
		if (!d) { return ''; }
		var today = new Date(); today.setHours(0, 0, 0, 0);
		var day = new Date(d); day.setHours(0, 0, 0, 0);
		var diff = Math.round((today - day) / 86400000);
		if (diff === 0) { return 'Today'; }
		if (diff === 1) { return 'Yesterday'; }
		return fmtDate(v);
	}

	function ago(v) {
		var d = toDate(v);
		if (!d) { return ''; }
		var s = Math.max(0, (Date.now() - d.getTime()) / 1000);
		if (s < 60) { return 'now'; }
		if (s < 3600) { return Math.floor(s / 60) + 'm'; }
		if (s < 86400) { return Math.floor(s / 3600) + 'h'; }
		if (s < 604800) { return Math.floor(s / 86400) + 'd'; }
		return d.toLocaleDateString('en-PH', { month: 'short', day: 'numeric' });
	}

	function chip(status) {
		var s = STATUS[status] || ['tone-slate', status];
		return '<span class="fods-chip ' + s[0] + '"><span class="fods-dot"></span>' + esc(s[1]) + '</span>';
	}

	function fileSize(n) { return n >= 1048576 ? (n / 1048576).toFixed(1) + ' MB' : Math.max(1, Math.round(n / 1024)) + ' KB'; }

	function attachmentUrl(id) { return url + '?meaction=ATTACHMENT&id=' + id; }


	function extOf(name) { var m = /\.([a-z0-9]+)$/i.exec(String(name || '')); return m ? m[1].toLowerCase() : ''; }

	function fileIcon(name) { return FILE_ICON[extOf(name)] || 'ti-file'; }

	// Last-message preview: a message can be only attachments

	// POST that only counts as done when the server answers { ok: true }
	// (a PHP warning or an empty body must not look like success)
	function postJson(settings) {
		var d = window.jQuery.Deferred();
		window.jQuery.ajax(Object.assign({ type: 'POST', url: url, dataType: 'json', global: false, cache: false }, settings))
			.done(function (data) { if (data && data.ok) { d.resolve(data); } else { d.reject({ responseJSON: data && data.error ? data : null }); } })
			.fail(function (xhr) { d.reject(xhr); });
		return d.promise();
	}

	function refreshInbox() { if (window.fodsInbox) { window.fodsInbox.refresh(); } }

	function ajaxError(xhr, fallback) {
		var msg = xhr && xhr.responseJSON && xhr.responseJSON.error ? xhr.responseJSON.error : fallback;
		toastr.error(msg, 'Oops!', { progressBar: true, closeButton: true, timeOut: 2500 });
	}

	function busy(btn, on, label) {
		var $btn = window.jQuery(btn);
		if (on) {
			$btn.data('label', $btn.html()).prop('disabled', true).html('<span class="spinner-border spinner-border-sm' + (label ? ' me-1' : '') + '"></span>' + (label || ''));
		} else {
			$btn.prop('disabled', false).html($btn.data('label'));
		}
	}

	// Keeps the address bar on what's open, so a refresh lands back there
	function setUrl(params) {
		try {
			var q = new URLSearchParams({ meaction: 'MAIN' });
			Object.keys(params).forEach(function (k) { if (params[k]) { q.set(k, params[k]); } });
			history.replaceState(null, '', url + '?' + q.toString());
		} catch (e) {}
	}

	// Picked files for a form: validates type/size/count and shows removable
	// thumbnails (images) or file tiles. opts: imagesOnly, maxBytes, maxTotal
	function filePicker(thumbsId, opts) {
		var files = [];
		var thumbs = document.getElementById(thumbsId);
		var what = opts.imagesOnly ? 'images' : 'files';
		var warn = function (msg) { toastr.warning(msg, 'Oops!', { progressBar: true, closeButton: true, timeOut: 2500 }); };

		function render() {
			thumbs.querySelectorAll('img').forEach(function (img) { URL.revokeObjectURL(img.src); });
			thumbs.innerHTML = files.map(function (f, i) {
				var x = '<button type="button" class="tk-thumb-x" data-remove="' + i + '" aria-label="Remove"><i class="ti ti-x"></i></button>';
				if (IMAGE_TYPES.indexOf(f.type) !== -1) {
					return '<div class="tk-thumb" title="' + esc(f.name) + ' (' + fileSize(f.size) + ')"><img src="' + URL.createObjectURL(f) + '" alt="">' + x + '</div>';
				}
				return '<div class="tk-thumb is-file" title="' + esc(f.name) + ' (' + fileSize(f.size) + ')"><i class="ti ' + fileIcon(f.name) + '"></i>'
					+ '<span>' + esc(f.name) + '</span><span class="text-muted">' + fileSize(f.size) + '</span>' + x + '</div>';
			}).join('');
			if (opts.onChange) { opts.onChange(); }
		}

		thumbs.addEventListener('click', function (e) {
			var btn = e.target.closest('[data-remove]');
			if (btn) { files.splice(parseInt(btn.getAttribute('data-remove'), 10), 1); render(); }
		});

		return {
			add: function (list) {
				Array.prototype.forEach.call(list || [], function (f) {
					var isImage = IMAGE_TYPES.indexOf(f.type) !== -1;
					var total = files.reduce(function (sum, x) { return sum + x.size; }, 0) + f.size;
					if (!isImage && opts.imagesOnly) {
						warn(f.name + ' is not an image (JPG, PNG, GIF or WEBP only).');
					} else if (!isImage && DOC_EXTS.indexOf(extOf(f.name)) === -1) {
						warn(f.name + " can't be sent. Allowed: images, PDF, Word, Excel, PowerPoint, CSV, TXT and ZIP.");
					} else if (f.size > opts.maxBytes) {
						warn(f.name + ' is over ' + Math.round(opts.maxBytes / 1048576) + ' MB.');
					} else if (files.length >= MAX_FILES) {
						warn('Attach up to ' + MAX_FILES + ' ' + what + ' at a time.');
					} else if (opts.maxTotal && total > opts.maxTotal) {
						warn('Files are too big together (' + Math.round(opts.maxTotal / 1048576) + ' MB per message).');
					} else {
						files.push(f);
					}
				});
				render();
			},
			appendTo: function (fd) { files.forEach(function (f) { fd.append('attachments[]', f, f.name || 'screenshot.png'); }); },
			count: function () { return files.length; },
			clear: function () { files = []; render(); }
		};
	}

	// Files (e.g. screenshots) pasted into a textarea become attachments
	function pasteFiles(textarea, picker) {
		textarea.addEventListener('paste', function (e) {
			var items = (e.clipboardData && e.clipboardData.files) || [];
			if (items.length) { e.preventDefault(); picker.add(items); }
		});
	}

	// =====================================================================
	// TICKETS (shared by My tickets and the Support desk)
	// =====================================================================
	const TICKET_POLL_MS = 5000;

	function isDone(t) { return t.status === 'Resolved' || t.status === 'Closed'; }

	function ticketIcon(t) { return t.category === 'Bug report' ? ['tone-red', 'ti-bug'] : ['tone-teal', 'ti-hand-finger']; }

	function ticketFilesHtml(files) {
		if (!files || !files.length) { return ''; }
		return '<div class="msg-atts">' + files.map(function (f) {
			return '<button type="button" class="tk-thumb" data-img-src="' + attachmentUrl(f.recid) + '" data-name="' + esc(f.name) + '" title="' + esc(f.name) + ' (' + fileSize(f.size) + ')">'
				+ '<img src="' + attachmentUrl(f.recid) + '" alt="' + esc(f.name) + '" loading="lazy"></button>';
		}).join('') + '</div>';
	}

	// A ticket as a chat: your posts on the right, the others' on the left
	// (marked Support when it isn't the requester), status changes as notes
	function ticketThreadHtml(data) {
		var requester = String(data.hd.added_by).toUpperCase();
		var day = '', last = '', html = '';
		data.thread.forEach(function (t, i) {
			var d = fmtDay(t.added_at);
			if (d !== day) { html += '<div class="msg-day">' + esc(d) + '</div>'; day = d; last = ''; }
			if (t.is_system) {
				html += '<div class="tk-sys"><span><i class="ti ti-info-circle"></i> ' + esc(t.mine ? 'You' : t.sender_name) + ' ' + esc(t.message) + ' · ' + esc(fmtTime(t.added_at)) + '</span></div>';
				last = '';
				return;
			}
			var support = String(t.added_by).toUpperCase() !== requester;
			html += '<div class="msg-row' + (t.mine ? ' mine' : '') + '">';
			if (!t.mine && t.added_by !== last) {
				html += '<div class="msg-sender">' + esc(t.sender_name) + (support ? '<span class="tk-support">Support</span>' : '') + '</div>';
			}
			html += '<div class="msg-bubble">' + (t.message ? '<div class="msg-text">' + esc(t.message) + '</div>' : '') + ticketFilesHtml(t.files) + '</div>'
				+ '<div class="msg-time">' + (i === 0 ? 'Opened the ticket · ' : '') + esc(fmtTime(t.added_at)) + '</div></div>';
			last = t.added_by;
		});
		return html;
	}

	// Status buttons: support may move a ticket anywhere; the requester can
	// close it, or reopen it once resolved/closed
	function statusMoves(hd, asSupport) {
		if (asSupport) {
			return ['In progress', 'Resolved', 'Closed', 'Open'].filter(function (s) { return s !== hd.status && !(s === 'Open' && hd.status === 'In progress'); });
		}
		var moves = [];
		if (hd.status === 'Resolved' || hd.status === 'Closed') { moves.push('Open'); }
		if (hd.status !== 'Closed') { moves.push('Closed'); }
		return moves;
	}

	function statusButtons(moves) {
		return moves.map(function (s) {
			var a = STATUS_ACTIONS[s];
			return '<button type="button" class="btn btn-sm ' + a[2] + ' px-3" data-status="' + esc(s) + '"><i class="ti ' + a[1] + ' me-1"></i>' + a[0] + '</button>';
		}).join('');
	}

	function indexTickets() {
		Object.keys(ticketById).forEach(function (k) { delete ticketById[k]; });
		tickets.forEach(function (t) { ticketById[t.recid] = t; });
	}

	// Tab counters: your tickets with news; on the desk, tickets waiting for a reply
	function updateTicketCounts() {
		var mineNew = tickets.filter(function (t) { return t.mine && t.unread > 0; }).length;
		var el = document.getElementById('ticketsCount');
		el.textContent = mineNew; el.hidden = mineNew === 0;
		var desk = document.getElementById('deskCount');
		if (desk) {
			var waiting = tickets.filter(function (t) { return t.needs_reply; }).length;
			desk.textContent = waiting; desk.hidden = waiting === 0;
		}
	}

	// Keep the lists in step with a ticket that was just loaded
	function syncTicket(data) {
		var row = ticketById[data.hd.recid];
		if (!row) { return; }
		var posts = data.thread.filter(function (t) { return !t.is_system; });
		var lastPost = posts[posts.length - 1] || {};
		row.status = data.hd.status;
		row.handled_by = data.hd.handled_by;
		row.handler_name = data.hd.handler_name;
		row.updated_at = data.hd.updated_at || data.hd.added_at;
		row.unread = 0;
		row.replies = Math.max(0, posts.length - 1);
		row.files = data.thread.reduce(function (n, t) { return n + t.files.length; }, 0);
		row.last_message = lastPost.message;
		row.last_sender = lastPost.added_by;
		row.needs_reply = !isDone(row) && String(lastPost.added_by || '').toUpperCase() === String(row.added_by).toUpperCase();
		renderTickets();
	}

	function renderTickets() {
		renderMyTickets();
		renderDesk();
		updateTicketCounts();
	}

	function reloadTickets() {
		return window.jQuery.ajax({ type: 'GET', url: url, data: { meaction: 'TICKET-LIST' }, dataType: 'json', global: false, cache: false })
			.done(function (data) {
				tickets = data.tickets || [];
				tickets.forEach(function (t) { if (t.recid === tkActive) { t.unread = 0; } });
				indexTickets();
				renderTickets();
			});
	}

	// =====================================================================
	// MY TICKETS (each ticket is a conversation with support)
	// =====================================================================
	let tkActive = 0;
	let tkBox = 'active';     // list section: 'active' (open / in progress) or 'done'
	let tkLastId = 0;
	let tkStatus = '';
	let tkPolling = false;
	let tkPicker = null;

	//MY TICKETS - LIST
	function renderMyTickets() {
		var q = document.getElementById('tkSearch').value.trim().toLowerCase();
		var mine = tickets.filter(function (t) { return t.mine; });
		var list = mine.filter(function (t) {
			if (isDone(t) !== (tkBox === 'done')) { return false; }
			return !q || (t.ticket_no + ' ' + t.subject + ' ' + t.module + ' ' + t.category).toLowerCase().indexOf(q) !== -1;
		});
		document.getElementById('tkActiveCount').textContent = mine.filter(function (t) { return !isDone(t); }).length;
		document.getElementById('tkDoneCount').textContent = mine.filter(isDone).length;

		var html = '';
		list.forEach(function (t) {
			var icon = ticketIcon(t);
			var text = t.last_message == null ? '' : (isMe(t.last_sender) ? 'You: ' : 'Support: ') + (t.last_message === '' ? 'Sent a screenshot' : t.last_message);
			html += '<button type="button" class="msg-conv' + (t.recid === tkActive ? ' active' : '') + (t.unread ? ' is-unread' : '') + '" data-ticket="' + t.recid + '">'
				+ '<div class="fods-avatar ' + icon[0] + '"><i class="ti ' + icon[1] + '"></i></div>'
				+ '<div class="msg-conv-main"><div class="msg-conv-top"><span class="msg-conv-name">' + esc(t.subject) + '</span><span class="msg-conv-time">' + ago(t.updated_at) + '</span></div>'
				+ '<div class="msg-conv-top"><span class="msg-conv-text">' + esc(text || t.ticket_no) + '</span>'
				+ (t.unread ? '<span class="badge bg-danger rounded-pill">' + t.unread + '</span>' : chip(t.status)) + '</div></div>'
				+ '</button>';
		});
		if (!list.length) {
			html = '<div class="text-center text-muted small py-5 px-3">'
				+ (q ? '<i class="ti ti-search fs-6 d-block mb-1"></i>No tickets match.'
					: tkBox === 'done' ? '<i class="ti ti-circle-check fs-6 d-block mb-1"></i>No resolved tickets yet.'
					: '<i class="ti ti-lifebuoy fs-6 d-block mb-1"></i>No open tickets. Use New ticket when you need help.')
				+ '</div>';
		}
		document.getElementById('tkList').innerHTML = html;
	}

	function setTkBox(key) {
		tkBox = key;
		window.jQuery('#tkBox button').removeClass('active').filter('[data-box="' + key + '"]').addClass('active');
		renderMyTickets();
	}

	function renderOpenTicket(data, scroll) {
		var hd = data.hd;
		var icon = ticketIcon(hd);
		var avatar = document.getElementById('tkAvatar');
		avatar.className = 'fods-avatar ' + icon[0];
		avatar.innerHTML = '<i class="ti ' + icon[1] + '"></i>';
		document.getElementById('tkName').textContent = hd.subject;
		document.getElementById('tkSub').textContent = [hd.ticket_no, hd.category, hd.priority + ' priority', hd.module, hd.handler_name ? 'Handled by ' + hd.handler_name : 'Not picked up yet'].filter(Boolean).join(' · ');
		document.getElementById('tkActions').innerHTML = chip(hd.status) + (data.perms.owner ? statusButtons(statusMoves(hd, false)) : '');

		var bar = document.getElementById('tkBar');
		var note = hd.status === 'Resolved' ? 'Support marked this resolved. Not fixed? Reply and it opens again.'
			: hd.status === 'Closed' ? 'This ticket is closed. Reply if you need to open it again.' : '';
		bar.innerHTML = note ? '<i class="ti ti-info-circle"></i> ' + esc(note) : '';
		bar.classList.toggle('d-none', !note);

		var body = document.getElementById('tkBody');
		var nearBottom = body.scrollHeight - body.scrollTop - body.clientHeight < 120;
		body.innerHTML = ticketThreadHtml(data);
		if (scroll || nearBottom) { body.scrollTop = body.scrollHeight; }
		document.getElementById('tkForm').classList.toggle('d-none', !data.perms.reply);

		tkLastId = data.thread.length ? data.thread[data.thread.length - 1].recid : 0;
		tkStatus = hd.status;
	}

	function loadTicket(scroll) {
		var id = tkActive;
		return window.jQuery.ajax({ type: 'GET', url: url, data: { meaction: 'TICKET-LOAD', recid: id }, dataType: 'json', global: false, cache: false })
			.done(function (data) {
				if (tkActive !== id) { return; }
				renderOpenTicket(data, scroll);
				syncTicket(data);
				refreshInbox();
			});
	}

	//MY TICKETS - OPEN ONE
	function openTicket(id) {
		tkActive = parseInt(id, 10);
		tkLastId = 0; tkStatus = '';
		var row = ticketById[tkActive];
		if (row && isDone(row) !== (tkBox === 'done')) { tkBox = isDone(row) ? 'done' : 'active'; }
		setTkBox(tkBox);
		document.getElementById('tkEmpty').classList.add('d-none');
		var openEl = document.getElementById('tkOpen');
		openEl.classList.remove('d-none'); openEl.classList.add('d-flex');
		document.getElementById('tkShell').classList.add('is-thread');
		document.getElementById('tkBody').innerHTML = '<div class="text-center text-muted py-5"><span class="spinner-border spinner-border-sm"></span></div>';
		setUrl({ tab: 'tickets', ticket: tkActive });
		loadTicket(true)
			.done(function () { document.getElementById('tkReplyText').focus(); })
			.fail(function (xhr) { ajaxError(xhr, 'Could not open that ticket.'); closeTicket(); });
	}

	function closeTicket() {
		tkActive = 0;
		if (tkPicker) { tkPicker.clear(); }
		document.getElementById('tkShell').classList.remove('is-thread');
		document.getElementById('tkEmpty').classList.remove('d-none');
		var openEl = document.getElementById('tkOpen');
		openEl.classList.add('d-none'); openEl.classList.remove('d-flex');
		renderMyTickets();
		setUrl({ tab: 'tickets' });
	}

	// New replies or a status change on the open ticket
	function pollTicket() {
		if (!tkActive || tkPolling || document.hidden || !document.getElementById('paneTickets').classList.contains('active')) { return; }
		var id = tkActive;
		tkPolling = true;
		window.jQuery.ajax({ type: 'GET', url: url, data: { meaction: 'TICKET-LOAD', recid: id }, dataType: 'json', global: false, cache: false })
			.done(function (data) {
				if (tkActive !== id) { return; }
				var last = data.thread.length ? data.thread[data.thread.length - 1].recid : 0;
				if (last !== tkLastId || data.hd.status !== tkStatus) {
					renderOpenTicket(data, false);
					syncTicket(data);
					refreshInbox();
				}
			})
			.always(function () { tkPolling = false; });
	}

	this.__helpdesk_mytickets = function () {
		var $ = window.jQuery;
		tkPicker = filePicker('tkReplyThumbs', {
			imagesOnly: true, maxBytes: MAX_BYTES,
			onChange: function () { var body = document.getElementById('tkBody'); body.scrollTop = body.scrollHeight; }
		});
		renderTickets();

		$(document).on('click', '#tkList .msg-conv', function () { openTicket($(this).data('ticket')); });
		$(document).on('click', '#tkBox button', function () { setTkBox($(this).data('box')); });
		$('#tkSearch').on('input', renderMyTickets);
		$('#tkBack').on('click', closeTicket);

		var fileInput = document.getElementById('tkReplyFiles');
		fileInput.addEventListener('change', function () { tkPicker.add(fileInput.files); fileInput.value = ''; });
		var text = document.getElementById('tkReplyText');
		pasteFiles(text, tkPicker);
		text.addEventListener('input', function () { text.style.height = 'auto'; text.style.height = text.scrollHeight + 'px'; });
		text.addEventListener('keydown', function (e) {
			if (e.key === 'Enter' && !e.shiftKey && !e.isComposing) { e.preventDefault(); $('#tkForm').trigger('submit'); }
		});

		//MY TICKETS - REPLY
		$('#tkForm').on('submit', function (e) {
			e.preventDefault();
			var message = text.value.trim();
			if ((!message && !tkPicker.count()) || !tkActive) { return; }
			var fd = new FormData();
			fd.append('meaction', 'TICKET-REPLY');
			fd.append('recid', tkActive);
			fd.append('message', message);
			tkPicker.appendTo(fd);

			var btn = document.getElementById('tkSendBtn');
			busy(btn, true);
			postJson({ data: fd, processData: false, contentType: false })
				.done(function () {
					text.value = ''; text.style.height = 'auto';
					tkPicker.clear();
					loadTicket(true);
				})
				.fail(function (xhr) { ajaxError(xhr, 'Your reply was not sent.'); })
				.always(function () { busy(btn, false); text.focus(); });
		});

		//MY TICKETS - CLOSE / REOPEN
		$(document).on('click', '#tkActions [data-status]', function () {
			var status = $(this).data('status'), btn = this;
			busy(btn, true, 'Saving...');
			postJson({ data: { meaction: 'TICKET-STATUS', recid: tkActive, status: status } })
				.done(function () {
					toastr.info(status === 'Open' ? 'Ticket reopened!' : 'Ticket closed!', 'Well Done!', { progressBar: true, closeButton: true, timeOut: 1500 });
					loadTicket(true);
				})
				.fail(function (xhr) { ajaxError(xhr, 'The ticket was not updated.'); busy(btn, false); });
		});

		document.getElementById('tabTickets').addEventListener('shown.bs.tab', function () { setUrl(tkActive ? { tab: 'tickets', ticket: tkActive } : { tab: 'tickets' }); });

		setInterval(pollTicket, TICKET_POLL_MS);
		setInterval(function () { if (!document.hidden) { reloadTickets(); } }, LIST_MS);
	};

	// =====================================================================
	// SUPPORT DESK (every ticket, for support staff)
	// =====================================================================
	let table = null;
	let filter = 'all';

	// Card / pill / bar filters (any other key is a single status)
	const FILTERS = {
		waiting: function (r) { return r.needs_reply; },
		done: isDone
	};

	function matches(r, key) {
		if (key === 'all') { return true; }
		if (FILTERS[key]) { return FILTERS[key](r); }
		var i = key.indexOf(':');
		if (i > 0) {
			var kind = key.slice(0, i), value = key.slice(i + 1);
			if (kind === 'type') { return r.category === value; }
			if (kind === 'pri') { return r.priority === value && !isDone(r); }
			if (kind === 'mod') { return (r.module || 'Not given') === value; }
		}
		return r.status === key;
	}

	function barsHtml(kind, counts, order) {
		var keys = order || Object.keys(counts).sort(function (a, b) { return counts[b] - counts[a]; }).slice(0, 5);
		var max = Math.max.apply(null, keys.map(function (k) { return counts[k] || 0; }).concat([1]));
		if (!keys.length) { return '<div class="small text-muted">No tickets yet.</div>'; }
		return keys.map(function (k) {
			var n = counts[k] || 0, key = kind + ':' + k;
			return '<button type="button" class="tk-bar' + (filter === key ? ' is-active' : '') + '" data-filter="' + esc(key) + '">'
				+ '<div class="tk-bar-top"><span>' + esc(k) + '</span><span>' + n + '</span></div>'
				+ '<div class="tk-bar-track"><div class="tk-bar-fill" style="width:' + Math.round(n / max * 100) + '%"></div></div></button>';
		}).join('');
	}

	//SUPPORT DESK - CARDS + BREAKDOWNS
	function renderDesk() {
		if (!perms.desk) { return; }
		var count = function (key) { return tickets.filter(function (r) { return matches(r, key); }).length; };
		['Open', 'waiting', 'In progress', 'done'].forEach(function (k) {
			document.querySelector('[data-card-value="' + k + '"]').textContent = count(k).toLocaleString('en-PH');
		});
		var fresh = tickets.filter(function (r) { return r.status === 'Open' && !r.handled_by; }).length;
		document.querySelector('[data-card-label="Open"]').textContent = 'Open' + (fresh ? ' · ' + fresh + ' not picked up' : '');
		document.querySelector('[data-card-label="done"]').textContent = 'Resolved / closed · ' + tickets.length + ' in all';

		var weekAgo = Date.now() - 7 * 86400000;
		var week = tickets.filter(function (r) { var d = toDate(r.added_at); return d && d.getTime() >= weekAgo; }).length;
		document.getElementById('deskWeek').textContent = week + ' new this week';

		var byType = {}, byPri = {}, byMod = {};
		tickets.forEach(function (r) {
			byType[r.category] = (byType[r.category] || 0) + 1;
			if (!isDone(r)) { byPri[r.priority] = (byPri[r.priority] || 0) + 1; }
			var m = r.module || 'Not given';
			byMod[m] = (byMod[m] || 0) + 1;
		});
		document.getElementById('deskByType').innerHTML = barsHtml('type', byType, ['Request', 'Bug report']);
		document.getElementById('deskByPriority').innerHTML = barsHtml('pri', byPri, ['High', 'Normal', 'Low']);
		document.getElementById('deskByModule').innerHTML = barsHtml('mod', byMod, null);

		if (table) { table.clear().rows.add(tickets).draw(false); }
	}

	//SUPPORT DESK - TABLE (search, filter pills, cards, bars)
	this.__helpdesk_desk = function () {
		var $ = window.jQuery;
		if (!perms.desk) { return; }

		$.fn.dataTable.ext.search.push(function (settings, data, idx) {
			if (settings.nTable.id !== 'ticketTable') { return true; }
			return matches(settings.aoData[idx]._aData, filter);
		});

		table = $('#ticketTable').DataTable({
			data: tickets,
			deferRender: true,
			pageLength: 10,
			lengthChange: false,
			autoWidth: false,
			order: [[3, 'desc']],
			dom: 't<"d-flex flex-wrap justify-content-between align-items-center"ip>',
			language: {
				info: 'Showing _START_–_END_ of _TOTAL_',
				infoEmpty: 'Nothing to show',
				infoFiltered: '',
				emptyTable: '<div class="text-center text-muted py-4"><i class="ti ti-lifebuoy fs-6 d-block mb-1"></i>No tickets yet.</div>',
				zeroRecords: '<div class="text-center text-muted py-4"><i class="ti ti-search fs-6 d-block mb-1"></i>No matches found</div>',
				paginate: { previous: '<i class="ti ti-chevron-left"></i>', next: '<i class="ti ti-chevron-right"></i>' }
			},
			rowId: function (r) { return 'ticket-' + r.recid; },
			columns: [
				{
					data: 'subject', className: 'cell-primary',
					render: function (v, type, r) {
						if (type !== 'display') { return r.ticket_no + ' ' + v + ' ' + r.module + ' ' + r.requester_name; }
						var icon = ticketIcon(r);
						return '<div class="d-flex align-items-start gap-2" style="min-width:0">'
							+ '<div class="fods-avatar ' + icon[0] + '"><i class="ti ' + icon[1] + '"></i></div>'
							+ '<div style="min-width:0"><div class="fw-semibold text-dark d-flex align-items-center gap-2" style="overflow-wrap:anywhere">'
							+ (r.unread ? '<span class="tk-unread" title="New activity"></span>' : '') + esc(v) + '</div>'
							+ '<div class="small text-muted">' + esc(r.ticket_no) + (r.module ? ' · ' + esc(r.module) : '')
							+ (r.replies ? ' · <i class="ti ti-message"></i> ' + r.replies : '') + (r.files ? ' · <i class="ti ti-photo"></i> ' + r.files : '') + '</div>'
							+ (r.needs_reply ? '<span class="fods-chip tone-red mt-1"><i class="ti ti-message-dots"></i> Waiting for a reply</span>' : '')
							+ '</div></div>';
					}
				},
				{
					data: 'requester_name', className: 'd-none d-md-table-cell',
					render: function (v, type, r) { return type === 'display' ? '<span class="small">' + esc(v) + (r.mine ? ' <span class="text-muted">(you)</span>' : '') + '</span>' : v; }
				},
				{
					data: 'category', className: 'd-none d-lg-table-cell',
					render: function (v, type, r) {
						if (type !== 'display') { return v; }
						return '<div class="small text-dark">' + esc(v) + '</div><span class="fods-chip ' + (PRIORITY[r.priority] || 'tone-slate') + '">' + esc(r.priority) + '</span>';
					}
				},
				{
					data: 'updated_at', className: 'd-none d-xl-table-cell',
					render: function (v, type) { return type === 'display' ? '<span class="small">' + esc(fmtDate(v)) + '<span class="d-block text-muted">' + esc(fmtTime(v)) + '</span></span>' : v; }
				},
				{
					data: 'status', className: 'cell-left',
					render: function (v, type, r) {
						if (type !== 'display') { return v; }
						return chip(v) + '<div class="small text-muted mt-1">' + (r.handler_name ? esc(r.handler_name) : 'Not picked up') + '</div>';
					}
				},
				{
					data: null, orderable: false, searchable: false, className: 'cell-actions text-end',
					render: function (v, type, r) {
						return '<button type="button" class="fods-icon-btn is-teal ticket-view" data-recid="' + r.recid + '" title="Open"><i class="ti ti-eye"></i></button>';
					}
				}
			]
		});
		renderDesk();

		$('#ticketTableSearch').on('input', function () { table.search(this.value).draw(); });

		function applyFilter(key) {
			filter = filter === key && key.indexOf(':') > 0 ? 'all' : key;   // clicking an active bar clears it
			$('#ticketTableFilter button').removeClass('active').filter(function () { return $(this).data('filter') === filter; }).addClass('active');
			$('.fods-stat[data-table="ticketTable"]').removeClass('is-active').filter(function () { return $(this).data('filter') === filter; }).addClass('is-active');
			$('.tk-bar').removeClass('is-active').filter(function () { return $(this).data('filter') === filter; }).addClass('is-active');
			table.draw();
		}
		$(document).on('click', '#ticketTableFilter button', function () { applyFilter($(this).data('filter')); });
		$(document).on('click', '.fods-stat[data-table="ticketTable"]', function () { applyFilter($(this).data('filter')); });
		$(document).on('click', '#paneDesk .tk-bar', function () { applyFilter($(this).data('filter')); });

		// DataTables sizes columns wrong while its tab is hidden
		document.getElementById('tabDesk').addEventListener('shown.bs.tab', function () { table.columns.adjust(); setUrl({ tab: 'desk' }); });
	};

	//TICKET - NEW TICKET MODAL + SAVE (everyone)
	this.__helpdesk_ticketform = function () {
		var $ = window.jQuery;
		var form = document.getElementById('ticketForm');
		var modalEl = document.getElementById('ticketModal');
		var picker = filePicker('tkThumbs', { imagesOnly: true, maxBytes: MAX_BYTES });
		var input = document.getElementById('tkFiles');
		var drop = document.getElementById('tkDrop');

		function show() {
			form.reset();
			form.classList.remove('was-validated');
			picker.clear();
			bootstrap.Modal.getOrCreateInstance(modalEl).show();
		}

		modalEl.addEventListener('shown.bs.modal', function () { document.getElementById('tkSubject').focus(); });
		$(document).on('click', '.js-new-ticket', function () {
			bootstrap.Tab.getOrCreateInstance(document.getElementById('tabTickets')).show();
			show();
		});

		input.addEventListener('change', function () { picker.add(input.files); input.value = ''; });
		pasteFiles(document.getElementById('tkDescription'), picker);
		['dragenter', 'dragover'].forEach(function (ev) {
			drop.addEventListener(ev, function (e) { e.preventDefault(); drop.classList.add('is-over'); });
		});
		['dragleave', 'drop'].forEach(function (ev) {
			drop.addEventListener(ev, function (e) { e.preventDefault(); drop.classList.remove('is-over'); });
		});
		drop.addEventListener('drop', function (e) { picker.add(e.dataTransfer.files); });

		form.addEventListener('submit', function (e) {
			e.preventDefault();
			if (!form.checkValidity()) { form.classList.add('was-validated'); return; }
			var fd = new FormData(form);
			fd.append('meaction', 'TICKET-SAVE');
			picker.appendTo(fd);

			var btn = document.getElementById('submitBtn');
			busy(btn, true, 'Sending...');
			$.ajax({
				type: 'POST',
				url: url,
				data: fd,
				processData: false,
				contentType: false,
				global: false,
				cache: false,
				success: function (data) { $('.me-outp-msg').html(data); },
				error: function (xhr, status, error) { toastr.error('[TICKET]', 'Error loading page...' + error, { closeButton: true }); },
				complete: function () { setTimeout(function () { busy(btn, false); }, 1200); }
			});
		});

		if (new URLSearchParams(location.search).get('new') === '1') { show(); }
	};

	//SUPPORT DESK - REVIEW MODAL (conversation, reply as support, status)
	let deskModal = null;

	this.__helpdesk_ticketview = function () {
		var $ = window.jQuery;
		if (!perms.desk) { return; }
		var modalEl = document.getElementById('ticketViewModal');
		var picker = filePicker('tvThumbs', { imagesOnly: true, maxBytes: MAX_BYTES });
		var current = null;

		function render(data) {
			var hd = data.hd;
			var icon = ticketIcon(hd);
			current = data;
			document.getElementById('tvIcon').className = 'fods-modal-icon ' + icon[0];
			document.getElementById('tvIcon').innerHTML = '<i class="ti ' + icon[1] + '"></i>';
			document.getElementById('tvTitle').textContent = hd.subject;
			document.getElementById('tvSub').innerHTML = esc(hd.ticket_no) + ' · ' + esc(hd.category) + ' · ' + chip(hd.status);

			document.getElementById('tvMeta').innerHTML =
				'<div><span>Requested by</span>' + esc(hd.requester_name) + '</div>'
				+ '<div><span>Filed</span>' + esc(fmtDate(hd.added_at)) + ' ' + esc(fmtTime(hd.added_at)) + '</div>'
				+ '<div><span>Where in FODS</span>' + (hd.module ? esc(hd.module) : '—') + '</div>'
				+ '<div><span>Priority</span><span class="fods-chip ' + (PRIORITY[hd.priority] || 'tone-slate') + ' d-inline-flex">' + esc(hd.priority) + '</span></div>'
				+ '<div><span>Handled by</span>' + (hd.handler_name ? esc(hd.handler_name) : 'Not picked up yet') + '</div>';

			document.getElementById('tvThread').innerHTML = ticketThreadHtml(data);

			document.getElementById('tvReplyForm').classList.toggle('d-none', !data.perms.reply);
			document.getElementById('tvReplyText').placeholder = data.perms.work && !data.perms.owner ? 'Reply to ' + hd.requester_name + '...' : 'Write a reply...';
			document.getElementById('tvActions').innerHTML = data.perms.work ? statusButtons(statusMoves(hd, true))
				: data.perms.owner ? statusButtons(statusMoves(hd, false)) : '<span class="small text-muted">View only — you can\'t reply or change the status.</span>';
		}

		function load(recid, scrollEnd) {
			return $.ajax({ type: 'GET', url: url, data: { meaction: 'TICKET-LOAD', recid: recid }, dataType: 'json', global: false, cache: false })
				.done(function (data) {
					render(data);
					syncTicket(data);
					refreshInbox();
					if (scrollEnd) {
						var body = modalEl.querySelector('.modal-body');
						body.scrollTop = body.scrollHeight;
					}
				})
				.fail(function (xhr) { ajaxError(xhr, 'Could not open that ticket.'); });
		}

		function show(recid) {
			current = null;
			picker.clear();
			document.getElementById('tvReplyText').value = '';
			document.getElementById('tvTitle').textContent = 'Loading...';
			document.getElementById('tvSub').textContent = '';
			document.getElementById('tvMeta').innerHTML = '';
			document.getElementById('tvActions').innerHTML = '';
			document.getElementById('tvReplyForm').classList.add('d-none');
			document.getElementById('tvThread').innerHTML = '<div class="text-center text-muted py-4"><span class="spinner-border spinner-border-sm"></span></div>';
			bootstrap.Modal.getOrCreateInstance(modalEl).show();
			load(recid, true).fail(function () { bootstrap.Modal.getOrCreateInstance(modalEl).hide(); });
			setUrl({ tab: 'desk', ticket: recid });
		}
		deskModal = show;

		modalEl.addEventListener('hidden.bs.modal', function () { setUrl({ tab: 'desk' }); });
		$(document).on('click', '#ticketTable .ticket-view', function (e) { e.stopPropagation(); show($(this).data('recid')); });
		$(document).on('click', '#ticketTable tbody tr', function (e) {
			var r = table.row(this).data();
			if (r && !$(e.target).closest('button, a').length) { show(r.recid); }
		});

		var input = document.getElementById('tvFiles');
		input.addEventListener('change', function () { picker.add(input.files); input.value = ''; });
		pasteFiles(document.getElementById('tvReplyText'), picker);

		//SUPPORT DESK - REPLY
		$('#tvReplyForm').on('submit', function (e) {
			e.preventDefault();
			if (!current) { return; }
			var text = document.getElementById('tvReplyText');
			if (!text.value.trim() && !picker.count()) {
				text.classList.add('is-invalid');
				toastr.warning('Type a reply or attach an image.', 'Oops!', { progressBar: true, closeButton: true, timeOut: 2000 });
				return;
			}
			text.classList.remove('is-invalid');
			var fd = new FormData();
			fd.append('meaction', 'TICKET-REPLY');
			fd.append('recid', current.hd.recid);
			fd.append('message', text.value);
			picker.appendTo(fd);

			var btn = document.getElementById('tvReplyBtn');
			busy(btn, true, 'Sending...');
			postJson({ data: fd, processData: false, contentType: false })
				.done(function () {
					text.value = '';
					picker.clear();
					toastr.success('Reply sent!', 'Well Done!', { progressBar: true, closeButton: true, timeOut: 1500 });
					load(current.hd.recid, true);
				})
				.fail(function (xhr) { ajaxError(xhr, 'Your reply was not sent.'); })
				.always(function () { busy(btn, false); });
		});

		//SUPPORT DESK - STATUS
		$(document).on('click', '#tvActions [data-status]', function () {
			if (!current) { return; }
			var status = $(this).data('status');
			var btn = this;
			busy(btn, true, 'Saving...');
			postJson({ data: { meaction: 'TICKET-STATUS', recid: current.hd.recid, status: status } })
				.done(function () {
					toastr.info('Ticket is now ' + status + '!', 'Well Done!', { progressBar: true, closeButton: true, timeOut: 1500 });
					load(current.hd.recid, true);
				})
				.fail(function (xhr) { ajaxError(xhr, 'The ticket was not updated.'); busy(btn, false); });
		});
	};

	// ?ticket=ID (topbar inbox, links): your own ticket opens as a conversation
	// in My tickets; anyone else's opens on the Support desk
	this.__helpdesk_openticket = function () {
		if (!open.ticket) { return; }
		var row = ticketById[open.ticket];
		if ((!row || row.mine) || !perms.desk) {
			bootstrap.Tab.getOrCreateInstance(document.getElementById('tabTickets')).show();
			openTicket(open.ticket);
		} else {
			bootstrap.Tab.getOrCreateInstance(document.getElementById('tabDesk')).show();
			deskModal(open.ticket);
		}
	};

	//HELPDESK - IMAGE PREVIEW (ticket screenshots)
	this.__helpdesk_images = function () {
		var $ = window.jQuery;
		// Image preview (ticket and chat images)
		$(document).on('click', '[data-img-src]', function () {
			var src = $(this).attr('data-img-src');
			document.getElementById('imgModalImg').src = src;
			document.getElementById('imgModalName').textContent = $(this).attr('data-name');
			document.getElementById('imgModalLink').href = src;
			bootstrap.Modal.getOrCreateInstance(document.getElementById('imgModal')).show();
		});
		// it opens over the ticket modal: lift its backdrop above the ticket
		document.getElementById('imgModal').addEventListener('shown.bs.modal', function () {
			var drops = document.querySelectorAll('.modal-backdrop');
			if (drops.length > 1) { drops[drops.length - 1].style.zIndex = 1065; }
		});


	};
}

// jQuery, DataTables and Bootstrap load in the footer, after this file
document.addEventListener('DOMContentLoaded', function () {
	__mysys_helpdesk_ent.__helpdesk_images();
	__mysys_helpdesk_ent.__helpdesk_desk();
	__mysys_helpdesk_ent.__helpdesk_mytickets();
	__mysys_helpdesk_ent.__helpdesk_ticketform();
	__mysys_helpdesk_ent.__helpdesk_ticketview();
	__mysys_helpdesk_ent.__helpdesk_openticket();
});
