var __mysys_messages_ent = new __mysys_messages_ent();
function __mysys_messages_ent() {
	const mesiteurl = document.getElementById('__siteurl').getAttribute('data-mesiteurl');
	const url = mesiteurl + 'mymessages';
	const open = window.__msg_open || {};
	const me = String(window.__msg_me || '').toUpperCase();
	const users = window.__msg_users || [];
	let convs = window.__msg_convs || [];

	const IMAGE_TYPES = ['image/png', 'image/jpeg', 'image/gif', 'image/webp'];
	const MAX_FILES = 5;
	const DOC_EXTS = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'csv', 'txt', 'zip'];
	const MAX_CHAT_BYTES = 10 * 1024 * 1024;         // per file
	const MAX_CHAT_TOTAL = 30 * 1024 * 1024;         // per message
	const FILE_ICON = { pdf: 'ti-file-text', doc: 'ti-file-text', docx: 'ti-file-text', txt: 'ti-file-text', xls: 'ti-file-spreadsheet', xlsx: 'ti-file-spreadsheet', csv: 'ti-file-spreadsheet', zip: 'ti-file-zip' };
	const POLL_MS = 2500;      // open chat: new messages, who has seen them, who is typing
	const LIST_MS = 20000;
	const TYPING_PING_MS = 3000;   // while you type, tell the others at most this often

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


	function fileSize(n) { return n >= 1048576 ? (n / 1048576).toFixed(1) + ' MB' : Math.max(1, Math.round(n / 1024)) + ' KB'; }


	function chatFileUrl(id) { return url + '?meaction=CHAT-FILE&id=' + id; }

	function extOf(name) { var m = /\.([a-z0-9]+)$/i.exec(String(name || '')); return m ? m[1].toLowerCase() : ''; }

	function fileIcon(name) { return FILE_ICON[extOf(name)] || 'ti-file'; }

	// Last-message preview: a message can be only attachments
	function preview(c) {
		if (c.last_message == null) { return 'No messages yet'; }
		if (c.last_system) {
			var by = c.members.filter(function (m) { return m.username === c.last_sender; })[0];
			return (isMe(c.last_sender) ? 'You' : (by ? by.full_name.split(' ')[0] : c.last_sender)) + ' ' + c.last_message;
		}
		return (isMe(c.last_sender) ? 'You: ' : '') + (c.last_message === '' ? 'Sent an attachment' : c.last_message);
	}

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

	// =====================================================================
	// CHATS
	// =====================================================================
	let activeId = 0;
	let activeConv = null;
	let lastId = 0;
	let lastDay = '';
	let lastSender = '';
	let polling = false;
	let box = 'inbox';   // chat list section: 'inbox' or 'archived'

	function convAvatar(c) {
		return c.conv_type === 'group'
			? '<div class="fods-avatar tone-indigo"><i class="ti ti-users"></i></div>'
			: '<div class="fods-avatar tone-teal">' + esc(initials(c.name)) + '</div>';
	}

	function updateChatCount() {
		var n = convs.reduce(function (sum, c) { return sum + (c.unread || 0); }, 0);
		var el = document.getElementById('chatsCount');
		el.textContent = n > 99 ? '99+' : n;
		el.hidden = n === 0;
	}

	//MESSAGES - CONVERSATION LIST
	function renderConvs() {
		var q = document.getElementById('convSearch').value.trim().toLowerCase();
		var archived = box === 'archived';
		var list = convs.filter(function (c) {
			if (!!c.is_archived !== archived) { return false; }
			if (!q) { return true; }
			return (c.name + ' ' + c.members.map(function (m) { return m.full_name + ' ' + m.username; }).join(' ')).toLowerCase().indexOf(q) !== -1;
		});

		var html = '';
		list.forEach(function (c) {
			var typing = c.recid !== activeId && (c.members || []).some(function (m) { return !isMe(m.username) && m.is_typing; });
			var text = typing ? 'typing…' : preview(c);
			html += '<button type="button" class="msg-conv' + (c.recid === activeId ? ' active' : '') + (c.unread ? ' is-unread' : '') + '" data-conv="' + c.recid + '">'
				+ convAvatar(c)
				+ '<div class="msg-conv-main"><div class="msg-conv-top"><span class="msg-conv-name">' + esc(c.name) + '</span><span class="msg-conv-time">' + ago(c.last_at) + '</span></div>'
				+ '<div class="msg-conv-top"><span class="msg-conv-text' + (typing ? ' is-typing' : '') + '">' + esc(text) + '</span>'
				+ (c.unread ? '<span class="badge bg-danger rounded-pill">' + c.unread + '</span>' : '') + '</div></div>'
				+ '</button>';
		});
		if (!list.length) {
			html = '<div class="text-center text-muted small py-5 px-3">'
				+ (q ? '<i class="ti ti-search fs-6 d-block mb-1"></i>No chats match.'
					: archived ? '<i class="ti ti-archive fs-6 d-block mb-1"></i>No archived chats. Archive a chat to tuck it away here.'
					: '<i class="ti ti-messages fs-6 d-block mb-1"></i>No chats yet. Use New message to start one.')
				+ '</div>';
		}
		document.getElementById('convList').innerHTML = html;
		document.getElementById('archivedCount').textContent = convs.filter(function (c) { return c.is_archived; }).length;
		updateChatCount();
	}

	function reloadConvs() {
		return window.jQuery.ajax({ type: 'GET', url: url, data: { meaction: 'CONV-LIST' }, dataType: 'json', global: false, cache: false })
			.done(function (data) {
				convs = data.convs || [];
				if (activeId) {
					convs.forEach(function (c) { if (c.recid === activeId) { c.unread = 0; } });
				}
				renderConvs();
			});
	}

	function messageHtml(m) {
		var html = '';
		var day = fmtDay(m.added_at);
		if (day !== lastDay) {
			html += '<div class="msg-day">' + esc(day) + '</div>';
			lastDay = day;
			lastSender = '';
		}
		var mine = isMe(m.added_by);
		if (m.is_system) {
			lastSender = '';
			return html + '<div class="msg-note"><span><i class="ti ti-info-circle"></i> ' + esc(mine ? 'You' : m.sender_name) + ' ' + esc(m.message) + ' · ' + fmtTime(m.added_at) + '</span></div>';
		}
		html += '<div class="msg-row' + (mine ? ' mine' : '') + '" data-id="' + m.recid + '">';
		if (!mine && activeConv.conv_type === 'group' && m.added_by !== lastSender) {
			html += '<div class="msg-sender">' + esc(m.sender_name) + '</div>';
		}
		html += '<div class="msg-bubble">' + (m.message !== '' ? '<div class="msg-text">' + esc(m.message) + '</div>' : '') + attachmentsHtml(m.files || []) + '</div>'
			+ '<div class="msg-time">' + fmtTime(m.added_at) + '</div></div>';
		lastSender = m.added_by;
		return html;
	}

	// Images as thumbnails (click to preview), other files as download cards
	function attachmentsHtml(files) {
		if (!files.length) { return ''; }
		return '<div class="msg-atts">' + files.map(function (f) {
			if (f.is_image) {
				return '<button type="button" class="tk-thumb" data-img-src="' + chatFileUrl(f.recid) + '" data-name="' + esc(f.name) + '" title="' + esc(f.name) + '">'
					+ '<img src="' + chatFileUrl(f.recid) + '" alt="' + esc(f.name) + '" loading="lazy"></button>';
			}
			return '<a class="msg-file" href="' + chatFileUrl(f.recid) + '" download title="Download ' + esc(f.name) + '">'
				+ '<i class="ti ' + fileIcon(f.name) + '"></i><span style="min-width:0"><span class="msg-file-name d-block">' + esc(f.name) + '</span>'
				+ '<span class="msg-file-size d-block">' + fileSize(f.size) + ' · ' + esc(extOf(f.name).toUpperCase()) + '</span></span>'
				+ '<i class="ti ti-download ms-auto" style="font-size:1.1rem"></i></a>';
		}).join('') + '</div>';
	}

	//MESSAGES - SEEN
	// Under your latest message: "Sent" until someone reads it, then "Seen"
	// (direct chat: with the time) or "Seen by N" (group). In a group, each
	// person's initials also sit under the last message they have read.
	function renderSeen() {
		var body = document.getElementById('msgBody');
		body.querySelectorAll('.msg-status, .msg-readers').forEach(function (el) { el.remove(); });
		if (!activeConv) { return; }
		var rows = Array.prototype.slice.call(body.querySelectorAll('.msg-row'));
		if (!rows.length) { return; }
		var others = activeConv.members.filter(function (m) { return !isMe(m.username); });
		var group = activeConv.conv_type === 'group';
		var idOf = function (row) { return parseInt(row.getAttribute('data-id'), 10); };

		var mine = rows.filter(function (r) { return r.classList.contains('mine'); });
		var last = mine[mine.length - 1];
		if (last) {
			var readers = others.filter(function (m) { return m.last_read_id >= idOf(last); });
			var text = 'Sent', seen = readers.length > 0;
			if (seen && !group) {
				text = 'Seen' + (readers[0].last_read_at ? ' ' + fmtTime(readers[0].last_read_at) : '');
			} else if (seen) {
				text = readers.length === others.length ? 'Seen by everyone' : 'Seen by ' + readers.length + ' of ' + others.length;
			}
			last.insertAdjacentHTML('beforeend', '<div class="msg-status' + (seen ? ' is-seen' : '') + '">'
				+ (seen ? '<i class="ti ti-checks"></i> ' : '<i class="ti ti-check"></i> ') + esc(text) + '</div>');
		}

		if (!group) { return; }
		var at = {};
		others.forEach(function (m) {
			for (var i = rows.length - 1; i >= 0; i--) {
				if (idOf(rows[i]) <= m.last_read_id) { (at[i] = at[i] || []).push(m); return; }
			}
		});
		Object.keys(at).forEach(function (i) {
			rows[i].insertAdjacentHTML('beforeend', '<div class="msg-readers">' + at[i].map(function (m) {
				var when = m.last_read_at ? ' · ' + fmtTime(m.last_read_at) : '';
				return '<span class="msg-reader" title="Seen by ' + esc(m.full_name + when) + '">' + esc(initials(m.full_name)) + '</span>';
			}).join('') + '</div>');
		});
	}

	//MESSAGES - TYPING: who else is typing in the open chat
	function renderTyping() {
		var box = document.getElementById('msgTyping');
		var who = activeConv ? activeConv.members.filter(function (m) { return !isMe(m.username) && m.is_typing; }) : [];
		box.classList.toggle('d-none', who.length === 0);
		if (!who.length) { return; }
		var first = function (m) { return activeConv.conv_type === 'group' ? String(m.full_name).split(' ')[0] : m.full_name; };
		document.getElementById('msgTypingText').textContent = who.length === 1 ? first(who[0]) + ' is typing…'
			: who.length === 2 ? first(who[0]) + ' and ' + first(who[1]) + ' are typing…'
			: 'Several people are typing…';
		var body = document.getElementById('msgBody');
		if (body.scrollHeight - body.scrollTop - body.clientHeight < 160) { body.scrollTop = body.scrollHeight; }
	}

	// You typing: tell the others at most every TYPING_PING_MS while there is
	// text in the box; stop as soon as it is emptied, sent, or you leave the chat
	let typingConv = 0;
	let typingSentAt = 0;
	function sendTyping(on) {
		var id = on ? activeId : typingConv;
		if (!id) { return; }
		typingConv = on ? id : 0;
		typingSentAt = on ? Date.now() : 0;
		postJson({ data: { meaction: 'CONV-TYPING', conv_id: id, typing: on ? 1 : 0 } }).fail(function () {});
	}
	function stopTyping() { if (typingConv) { sendTyping(false); } }

	function appendMessages(list, scroll) {
		var body = document.getElementById('msgBody');
		var nearBottom = body.scrollHeight - body.scrollTop - body.clientHeight < 120;
		var html = '';
		list.forEach(function (m) {
			if (m.recid > lastId) { html += messageHtml(m); lastId = m.recid; }
		});
		if (html) {
			body.insertAdjacentHTML('beforeend', html);
			if (scroll || nearBottom) { body.scrollTop = body.scrollHeight; }
		}
		renderSeen();
		renderTyping();
	}

	function setHeader(conv) {
		var others = conv.members.filter(function (m) { return !isMe(m.username); });
		var avatar = document.getElementById('msgAvatar');
		avatar.className = 'fods-avatar ' + (conv.conv_type === 'group' ? 'tone-indigo' : 'tone-teal');
		avatar.innerHTML = conv.conv_type === 'group' ? '<i class="ti ti-users"></i>' : esc(initials(conv.name));
		document.getElementById('msgName').textContent = conv.name;
		document.getElementById('msgSub').textContent = conv.conv_type === 'group'
			? conv.members.length + ' people · ' + conv.members.map(function (m) { return isMe(m.username) ? 'You' : m.full_name; }).join(', ')
			: (others[0] ? '@' + others[0].username : '');
		var archived = !!conv.is_archived;
		var btn = document.getElementById('msgArchive');
		btn.title = archived ? 'Move back to chats' : 'Archive chat';
		btn.innerHTML = '<i class="ti ' + (archived ? 'ti-archive-off' : 'ti-archive') + '"></i>';
		document.getElementById('msgArchivedBar').classList.toggle('d-none', !archived);
		document.getElementById('msgMembers').classList.toggle('d-none', conv.conv_type !== 'group');
		if (conv.members) { renderMembers(); }
	}

	//MESSAGES - GROUP MEMBERS (the person who created the group can remove people)
	function renderMembers() {
		var list = document.getElementById('membersList');
		if (!list || !activeConv || activeConv.conv_type !== 'group') { return; }
		var owner = isMe(activeConv.added_by);
		document.getElementById('membersTitle').textContent = activeConv.name;
		document.getElementById('membersSub').textContent = activeConv.members.length + ' people'
			+ (owner ? ' · you created this group, so you can remove people' : '');
		list.innerHTML = activeConv.members.map(function (m) {
			var creator = String(m.username).toUpperCase() === String(activeConv.added_by).toUpperCase();
			return '<div class="msg-pick msg-member">'
				+ '<span class="fods-avatar ' + (isMe(m.username) ? 'tone-teal' : 'tone-slate') + '">' + esc(initials(m.full_name)) + '</span>'
				+ '<span style="min-width:0"><span class="d-block fw-semibold text-dark small">' + esc(isMe(m.username) ? m.full_name + ' (you)' : m.full_name) + '</span>'
				+ '<span class="d-block text-muted" style="font-size:.74rem">@' + esc(m.username) + (creator ? ' · created the group' : '') + '</span></span>'
				+ (owner && !isMe(m.username) ? '<button type="button" class="fods-icon-btn is-danger" data-remove-member="' + esc(m.username) + '" title="Remove from group"><i class="ti ti-user-minus"></i></button>' : '')
				+ '</div>';
		}).join('');
	}

	function setBox(key) {
		box = key;
		window.jQuery('#convBox button').removeClass('active').filter('[data-box="' + key + '"]').addClass('active');
		renderConvs();
	}

	//MESSAGES - OPEN A CHAT
	function openConv(id) {
		var $ = window.jQuery;
		id = parseInt(id, 10);
		if (id !== activeId) { stopTyping(); }
		activeId = id;
		lastId = 0; lastDay = ''; lastSender = '';
		document.getElementById('msgEmpty').classList.add('d-none');
		var openEl = document.getElementById('msgOpen');
		openEl.classList.remove('d-none'); openEl.classList.add('d-flex');
		document.getElementById('msgShell').classList.add('is-thread');
		document.getElementById('msgBody').innerHTML = '<div class="text-center text-muted py-5"><span class="spinner-border spinner-border-sm"></span></div>';

		var listed = convs.filter(function (c) { return c.recid === id; })[0];
		if (listed) { listed.unread = 0; setHeader(listed); }
		if (listed && !!listed.is_archived !== (box === 'archived')) { box = listed.is_archived ? 'archived' : 'inbox'; }
		setBox(box);
		setUrl({ conv: id });

		$.ajax({ type: 'GET', url: url, data: { meaction: 'CONV-LOAD', conv_id: id }, dataType: 'json', global: false, cache: false })
			.done(function (data) {
				if (activeId !== id) { return; }
				activeConv = data.conv;
				setHeader(activeConv);
				document.getElementById('msgBody').innerHTML = data.messages.length ? '' : '<div class="text-center text-muted small py-5 msg-none">No messages yet — say hello.</div>';
				appendMessages(data.messages, true);
				refreshInbox();
				document.getElementById('msgText').focus();
			})
			.fail(function (xhr) {
				ajaxError(xhr, 'Could not open that chat.');
				closeConv();
			});
	}

	function closeConv() {
		stopTyping();
		activeId = 0; activeConv = null;
		document.getElementById('msgTyping').classList.add('d-none');
		if (chatPicker) { chatPicker.clear(); }
		document.getElementById('msgShell').classList.remove('is-thread');
		document.getElementById('msgEmpty').classList.remove('d-none');
		var openEl = document.getElementById('msgOpen');
		openEl.classList.add('d-none'); openEl.classList.remove('d-flex');
		renderConvs();
		setUrl({});
	}

	// New messages in the open chat (and read receipts)
	function poll() {
		if (!activeId || polling || document.hidden) { return; }
		var id = activeId;
		polling = true;
		window.jQuery.ajax({ type: 'GET', url: url, data: { meaction: 'CONV-LOAD', conv_id: id, after_id: lastId }, dataType: 'json', global: false, cache: false })
			.done(function (data) {
				if (activeId !== id) { return; }
				var membersChanged = !activeConv || activeConv.members.length !== data.conv.members.length;
				activeConv = data.conv;
				if (membersChanged) { setHeader(activeConv); }
				if (data.messages.length) {
					var none = document.querySelector('#msgBody .msg-none');
					if (none) { none.remove(); }
					appendMessages(data.messages, false);
					reloadConvs();
					refreshInbox();
				} else {
					renderSeen();
					renderTyping();
				}
			})
			.fail(function (xhr) {
				// removed from the group while it was open
				if (xhr.status === 404 && activeId === id) {
					toastr.info("You're no longer in this chat.", 'Heads up', { progressBar: true, closeButton: true, timeOut: 2500 });
					closeConv();
					reloadConvs();
				}
			})
			.always(function () { polling = false; });
	}

	let chatPicker = null;

	this.__messages_chats = function () {
		var $ = window.jQuery;
		chatPicker = filePicker('msgThumbs', {
			imagesOnly: false, maxBytes: MAX_CHAT_BYTES, maxTotal: MAX_CHAT_TOTAL,
			// the preview strip shrinks the chat: keep the latest message in view
			onChange: function () { var body = document.getElementById('msgBody'); body.scrollTop = body.scrollHeight; }
		});
		renderConvs();

		$(document).on('click', '#convBox button', function () { setBox($(this).data('box')); });

		$('#msgMembers').on('click', function () {
			renderMembers();
			bootstrap.Modal.getOrCreateInstance(document.getElementById('membersModal')).show();
		});

		// First click asks, second click removes
		$(document).on('click', '#membersList [data-remove-member]', function () {
			var $btn = $(this), username = $btn.attr('data-remove-member');
			if (!$btn.hasClass('is-confirm')) {
				$('#membersList .is-confirm').removeClass('is-confirm').html('<i class="ti ti-user-minus"></i>');
				$btn.addClass('is-confirm').text('Remove?');
				return;
			}
			var id = activeId;
			busy(this, true);
			postJson({ data: { meaction: 'CONV-REMOVE', conv_id: id, username: username } })
				.done(function () {
					toastr.warning('Removed from the group.', 'Well Done!', { progressBar: true, closeButton: true, timeOut: 1500 });
					activeConv.members = activeConv.members.filter(function (m) { return m.username !== username; });
					setHeader(activeConv);
					polling = false;
					poll();
					reloadConvs();
				})
				.fail(function (xhr) { ajaxError(xhr, 'That person was not removed.'); renderMembers(); });
		});

		// Image preview (chat images)
		$(document).on('click', '[data-img-src]', function () {
			var src = $(this).attr('data-img-src');
			document.getElementById('imgModalImg').src = src;
			document.getElementById('imgModalName').textContent = $(this).attr('data-name');
			document.getElementById('imgModalLink').href = src;
			bootstrap.Modal.getOrCreateInstance(document.getElementById('imgModal')).show();
		});
		// if it opens over another dialog, lift its backdrop above that one
		document.getElementById('imgModal').addEventListener('shown.bs.modal', function () {
			var drops = document.querySelectorAll('.modal-backdrop');
			if (drops.length > 1) { drops[drops.length - 1].style.zIndex = 1065; }
		});


		//MESSAGES - ARCHIVE / UNARCHIVE (only for you)
		$('#msgArchive').on('click', function () {
			if (!activeConv) { return; }
			var id = activeId, archive = activeConv.is_archived ? 0 : 1, btn = this;
			busy(btn, true);
			postJson({ data: { meaction: 'CONV-ARCHIVE', conv_id: id, archived: archive } })
				.done(function () {
					convs.forEach(function (c) { if (c.recid === id) { c.is_archived = archive; } });
					if (archive) {
						toastr.info('Chat archived. Find it under Archived.', 'Well Done!', { progressBar: true, closeButton: true, timeOut: 2000 });
						closeConv();
					} else {
						toastr.success('Chat moved back to your chats.', 'Well Done!', { progressBar: true, closeButton: true, timeOut: 2000 });
						activeConv.is_archived = 0;
						setHeader(activeConv);
						setBox('inbox');
					}
					refreshInbox();
				})
				.fail(function (xhr) { ajaxError(xhr, 'The chat was not updated.'); })
				.always(function () { busy(btn, false); });
		});

		// Attach: button, paste into the message box, or drop onto the chat
		var fileInput = document.getElementById('msgFiles');
		fileInput.addEventListener('change', function () { chatPicker.add(fileInput.files); fileInput.value = ''; });
		pasteFiles(document.getElementById('msgText'), chatPicker);
		var thread = document.querySelector('#msgShell .msg-thread');
		['dragenter', 'dragover'].forEach(function (ev) {
			thread.addEventListener(ev, function (e) { if (activeId) { e.preventDefault(); thread.classList.add('is-over'); } });
		});
		['dragleave', 'drop'].forEach(function (ev) {
			thread.addEventListener(ev, function (e) {
				if (ev === 'dragleave' && thread.contains(e.relatedTarget)) { return; }
				e.preventDefault(); thread.classList.remove('is-over');
			});
		});
		thread.addEventListener('drop', function (e) { if (activeId) { chatPicker.add(e.dataTransfer.files); } });

		$(document).on('click', '#convList .msg-conv', function () { openConv($(this).data('conv')); });
		$('#convSearch').on('input', renderConvs);
		$('#msgBack').on('click', closeConv);

		var text = document.getElementById('msgText');
		// grow with the text, up to the CSS max-height
		text.addEventListener('input', function () { text.style.height = 'auto'; text.style.height = text.scrollHeight + 'px'; });
		text.addEventListener('keydown', function (e) {
			if (e.key === 'Enter' && !e.shiftKey && !e.isComposing) { e.preventDefault(); $('#msgForm').trigger('submit'); }
		});

		//MESSAGES - SEND
		text.addEventListener('input', function () {
			if (!activeId) { return; }
			if (text.value.trim() === '') { stopTyping(); }
			else if (typingConv !== activeId || Date.now() - typingSentAt > TYPING_PING_MS) { sendTyping(true); }
		});
		document.addEventListener('visibilitychange', function () { if (document.hidden) { stopTyping(); } });
		window.addEventListener('pagehide', stopTyping);

		$('#msgForm').on('submit', function (e) {
			e.preventDefault();
			var message = text.value.trim();
			if ((!message && !chatPicker.count()) || !activeId) { return; }
			var fd = new FormData();
			fd.append('meaction', 'MSG-SEND');
			fd.append('conv_id', activeId);
			fd.append('message', message);
			chatPicker.appendTo(fd);

			var btn = document.getElementById('msgSendBtn');
			busy(btn, true);
			postJson({ data: fd, processData: false, contentType: false })
				.done(function () {
					text.value = ''; text.style.height = 'auto';
					typingConv = 0; typingSentAt = 0;   // the server cleared it with the message
					chatPicker.clear();
					polling = false;
					poll();
				})
				.fail(function (xhr) { ajaxError(xhr, 'Your message was not sent.'); })
				.always(function () { busy(btn, false); text.focus(); });
		});

		setInterval(poll, POLL_MS);
		setInterval(function () { if (!document.hidden) { reloadConvs(); } }, LIST_MS);
	};

	//MESSAGES - NEW CHAT (people picker)
	this.__messages_newchat = function () {
		var $ = window.jQuery;
		var modalEl = document.getElementById('convModal');
		var picked = {};
		var byUser = {};
		users.forEach(function (u) { byUser[u.username] = u; });

		function renderPeople() {
			var q = document.getElementById('convPeopleSearch').value.trim().toLowerCase();
			var html = '';
			users.forEach(function (u) {
				var hay = (u.full_name + ' ' + u.username + ' ' + (u.division || '') + ' ' + (u.position || '')).toLowerCase();
				if (q && hay.indexOf(q) === -1) { return; }
				var sub = [u.position, u.division].filter(function (v) { return v && v !== '-'; }).join(' · ');
				html += '<label class="msg-pick">'
					+ '<input type="checkbox" class="form-check-input mt-0" value="' + esc(u.username) + '"' + (picked[u.username] ? ' checked' : '') + '>'
					+ '<span class="fods-avatar tone-slate">' + esc(initials(u.full_name)) + '</span>'
					+ '<span style="min-width:0"><span class="d-block fw-semibold text-dark small">' + esc(u.full_name) + '</span>'
					+ '<span class="d-block text-muted" style="font-size:.74rem">@' + esc(u.username) + (sub ? ' · ' + esc(sub) : '') + '</span></span>'
					+ '</label>';
			});
			document.getElementById('convPeople').innerHTML = html || '<div class="text-center text-muted small py-4">No one matches.</div>';
		}

		function renderPicked() {
			var names = Object.keys(picked);
			document.getElementById('convPicked').innerHTML = names.length
				? names.map(function (u) {
					return '<span class="fods-chip tone-teal">' + esc(picked[u].full_name)
						+ ' <button type="button" class="msg-unpick" data-unpick="' + esc(u) + '" title="Remove ' + esc(picked[u].full_name) + '" aria-label="Remove"><i class="ti ti-x"></i></button></span>';
				}).join('')
				: '<span class="small text-muted">No one yet — pick from the list.</span>';
			document.getElementById('convTitleWrap').classList.toggle('d-none', names.length < 2);
			document.getElementById('convSubmitBtn').textContent = names.length > 1 ? 'Start group chat' : 'Start chat';
		}

		function show(preselect) {
			picked = {};
			if (preselect && byUser[preselect]) { picked[preselect] = byUser[preselect]; }
			document.getElementById('convForm').reset();
			renderPeople(); renderPicked();
			bootstrap.Modal.getOrCreateInstance(modalEl).show();
		}

		modalEl.addEventListener('shown.bs.modal', function () {
			document.getElementById(Object.keys(picked).length ? 'convMessage' : 'convPeopleSearch').focus();
		});
		if (new URLSearchParams(location.search).get('new') === '1') { show(null); }
		$('#convPeopleSearch').on('input', renderPeople);
		$(document).on('change', '#convPeople input[type=checkbox]', function () {
			if (this.checked) { picked[this.value] = byUser[this.value]; } else { delete picked[this.value]; }
			renderPicked();
		});
		$(document).on('click', '#convPicked [data-unpick]', function () {
			delete picked[$(this).data('unpick')];
			renderPicked(); renderPeople();
		});
		$('#convAdd, #convAddEmpty').on('click', function () {
			show(null);
		});

		//MESSAGES - START CHAT
		$('#convForm').on('submit', function (e) {
			e.preventDefault();
			var names = Object.keys(picked);
			if (!names.length) {
				toastr.warning('Pick at least one person.', 'Oops!', { progressBar: true, closeButton: true, timeOut: 2000 });
				return;
			}
			var btn = document.getElementById('convSubmitBtn');
			busy(btn, true, 'Starting...');
			$.ajax({
				type: 'POST', url: url, dataType: 'json', global: false, cache: false,
				data: { meaction: 'CONV-START', 'recipients[]': names, title: $('#convTitle').val(), message: $('#convMessage').val() }
			})
				.done(function (data) {
					bootstrap.Modal.getOrCreateInstance(modalEl).hide();
					reloadConvs().always(function () { openConv(data.conv_id); });
				})
				.fail(function (xhr) { ajaxError(xhr, 'Could not start the chat.'); })
				.always(function () { busy(btn, false); });
		});

		// ?to=USERNAME: go to your chat with them, or start one
		if (open.to) {
			var existing = convs.filter(function (c) {
				return c.conv_type === 'direct' && c.members.some(function (m) { return m.username.toUpperCase() === String(open.to).toUpperCase(); });
			})[0];
			if (existing) { openConv(existing.recid); } else { show(open.to); }
		} else if (open.conv) {
			openConv(open.conv);
		}
	};

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
}

// jQuery, DataTables and Bootstrap load in the footer, after this file
document.addEventListener('DOMContentLoaded', function () {
	__mysys_messages_ent.__messages_chats();
	__mysys_messages_ent.__messages_newchat();
});
