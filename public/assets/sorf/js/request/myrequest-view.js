// Request page (request/request-view.php): the conversation between the
// requester and HR (messages + files) - or, for the "Noted by" signatory,
// the request's log - plus signing/approval and HR's status update.
var __mysys_request_view = new __mysys_request_view_ent();
function __mysys_request_view_ent() {
	const app = __mysys_drf;
	const esc = app.esc;
	const cfg = window.__request_view || {};

	const body = document.getElementById('chatBody');
	const POLL_MS = 6000;
	const MAX_FILES = 5;
	const MONTHS = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

	let lastId = 0;
	let loading = false;
	let picked = [];
	let reloading = false;

	// "2026-10-08 14:05:00" -> "Oct 8, 2:05 PM"
	function when(stamp) {
		const p = String(stamp).split(/[- :]/).map(Number);
		if (p.length < 5) { return ''; }
		const hour = p[3] % 12 === 0 ? 12 : p[3] % 12;
		return MONTHS[p[1] - 1] + ' ' + p[2] + ', ' + hour + ':' + String(p[4]).padStart(2, '0') + ' ' + (p[3] < 12 ? 'AM' : 'PM');
	}

	function size(bytes) {
		return bytes >= 1048576 ? (bytes / 1048576).toFixed(1) + ' MB' : Math.max(1, Math.round(bytes / 1024)) + ' KB';
	}

	function fileHtml(f) {
		const href = app.url('myrequest?meaction=CHAT-FILE&id=' + f.recid);
		if (f.is_image) {
			return '<a href="' + href + '" target="_blank" rel="noopener"><img src="' + href + '" alt="' + esc(f.name) + '" loading="lazy"></a>';
		}
		return '<a class="drf-file" href="' + href + '"><i class="ti ti-file-download"></i><span>' + esc(f.name) + '</span><small>' + size(f.size) + '</small></a>';
	}

	function messageHtml(m) {
		if (m.is_system) {
			return '<div class="drf-msg-note">' + esc(m.message) + '<br>' + esc(m.sender_name) + ' · ' + when(m.added_at) + '</div>';
		}
		const mine = m.added_by === cfg.me;
		return '<div class="drf-msg' + (mine ? ' is-mine' : '') + '">'
			+ '<div class="drf-msg-meta">' + (mine ? 'You' : esc((m.is_hr ? 'HR · ' : '') + m.sender_name)) + ' · ' + when(m.added_at) + '</div>'
			+ (m.message !== '' ? '<div class="drf-msg-bubble">' + esc(m.message) + '</div>' : '')
			+ (m.files.length ? '<div class="drf-msg-files">' + m.files.map(fileHtml).join('') + '</div>' : '')
			+ '</div>';
	}

	// Someone else moved the request on while this page was open (signed it,
	// returned it, HR updated it): the steps and buttons on the page are stale
	function showStatus(status) {
		if (status === cfg.status) { return; }
		cfg.status = status;
		const chip = document.getElementById('requestStatus');
		chip.className = 'drf-chip ' + (cfg.tones[status] || 'tone-slate');
		chip.querySelector('[data-status-text]').textContent = status;
		refresh('This request was updated.');
	}

	// HR shared, started or withdrew the Special Order: the banner and its
	// buttons on the page are stale
	function showDoc(doc) {
		if (doc === undefined || doc === cfg.doc) { return; }
		cfg.doc = doc;
		refresh('The Special Order was updated.');
	}

	// Reload for the new state, unless that would throw away something being
	// typed (or files picked) - then just say so, once
	let told = false;
	function refresh(what) {
		const typing = Array.prototype.some.call(document.querySelectorAll('#chatMessage, #noted_remarks'), function (el) { return el.value.trim() !== ''; });
		if (!typing && !picked.length) {
			if (!reloading) { later(); }
		} else if (!told) {
			told = true;
			app.toast(what + ' Reload the page to see it.', '');
		}
	}

	function later() {
		reloading = true;
		setTimeout(function () { window.location.reload(); }, 900);
	}

	function load() {
		if (loading) { return Promise.resolve(); }
		loading = true;
		return app.get('myrequest?meaction=CHAT-LOAD&recid=' + cfg.recid + '&after_id=' + lastId).then(function (res) {
			const empty = document.getElementById('chatEmpty');
			if (res.messages.length) {
				if (empty) { empty.remove(); }
				// stay at the bottom unless the reader scrolled up to older messages
				const atBottom = body.scrollHeight - body.scrollTop - body.clientHeight < 80;
				body.insertAdjacentHTML('beforeend', res.messages.map(messageHtml).join(''));
				lastId = res.messages[res.messages.length - 1].recid;
				if (atBottom) { body.scrollTop = body.scrollHeight; }
			} else if (empty) {
				empty.innerHTML = cfg.canChat ? '<i class="ti ti-message-circle"></i>No messages yet.' : '<i class="ti ti-history"></i>Nothing logged yet.';
			}
			showStatus(res.status);
			showDoc(res.doc);
		}).finally(function () { loading = false; });
	}

	//CONVERSATION - first load, then look for new messages while the tab is open
	this.__chat_thread = function () {
		load().catch(function (err) { app.toast(err.message, 'error'); });
		setInterval(function () {
			if (!document.hidden) { load().catch(function () {}); }
		}, POLL_MS);
	};

	//CONVERSATION - SEND a message and/or files
	this.__chat_send = function () {
		const form = document.getElementById('chatForm');
		if (!form) { return; }   // the signatory only reads the log
		const message = document.getElementById('chatMessage');
		const files = document.getElementById('chatFiles');
		const chips = document.getElementById('chatPicked');
		const btn = document.getElementById('chatSend');

		function showPicked() {
			chips.innerHTML = picked.map(function (f, i) {
				return '<span class="drf-file"><i class="ti ti-paperclip"></i><span>' + esc(f.name) + '</span><small>' + size(f.size) + '</small>'
					+ '<button type="button" data-unpick="' + i + '" aria-label="Remove ' + esc(f.name) + '"><i class="ti ti-x"></i></button></span>';
			}).join('');
		}

		document.getElementById('chatAttach').addEventListener('click', function () { files.click(); });
		files.addEventListener('change', function () {
			picked = picked.concat(Array.prototype.slice.call(files.files));
			if (picked.length > MAX_FILES) {
				picked = picked.slice(0, MAX_FILES);
				app.toast('Attach up to ' + MAX_FILES + ' files at a time.', 'error');
			}
			files.value = '';
			showPicked();
		});
		chips.addEventListener('click', function (e) {
			const unpick = e.target.closest('[data-unpick]');
			if (!unpick) { return; }
			picked.splice(Number(unpick.getAttribute('data-unpick')), 1);
			showPicked();
		});

		// Enter sends, Shift+Enter starts a new line
		message.addEventListener('keydown', function (e) {
			if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); form.requestSubmit(); }
		});
		message.addEventListener('input', function () {
			message.style.height = 'auto';
			message.style.height = Math.min(message.scrollHeight + 2, 120) + 'px';
		});

		form.addEventListener('submit', function (e) {
			e.preventDefault();
			if (message.value.trim() === '' && !picked.length) { message.focus(); return; }

			const data = new FormData();
			data.append('meaction', 'CHAT-SEND');
			data.append('recid', cfg.recid);
			data.append('message', message.value);
			picked.forEach(function (f) { data.append('attachments[]', f); });

			const done = app.busy(btn, '');
			app.post('myrequest', data).then(function () {
				message.value = '';
				message.style.height = 'auto';
				picked = [];
				showPicked();
				return load();
			}).then(function () {
				body.scrollTop = body.scrollHeight;
			}).catch(function (err) {
				app.toast(err.message, 'error');
			}).finally(function () {
				done();
				message.focus();
			});
		});
	};

	//HR - UPDATE STATUS / remarks / S.O. number
	this.__hr_status = function () {
		const form = document.getElementById('statusForm');
		if (!form) { return; }
		const btn = document.getElementById('statusSubmit');

		form.addEventListener('submit', function (e) {
			e.preventDefault();
			const data = new FormData(form);
			data.append('meaction', 'STATUS-SAVE');

			const done = app.busy(btn, 'Saving...');
			app.post('myhrrequest', data).then(function (res) {
				app.toast(res.message, 'success');
				// the details card shows the remarks and S.O. number too
				later();
			}).catch(function (err) {
				done();
				app.toast(err.message, 'error');
			});
		});
	};

	//APPROVAL - the "Noted by" signatory returns the request, or signs and
	// approves it (which submits it to HR)
	this.__approval = function () {
		const form = document.getElementById('approvalForm');
		if (!form) { return; }
		const pad = __mysys_drf_signature(form.querySelector('[data-drf-sign]'));
		const remarks = document.getElementById('noted_remarks');

		form.querySelectorAll('[data-approval]').forEach(function (btn) {
			btn.addEventListener('click', function () {
				const action = btn.getAttribute('data-approval');
				if (action === 'approve' && pad.state() !== 'new') {
					app.toast('Sign the request to approve it.', 'error');
					return;
				}
				if (action === 'return' && remarks.value.trim() === '') {
					app.toast('Tell the requester what needs to change.', 'error');
					remarks.focus();
					return;
				}
				const data = new FormData(form);
				data.append('meaction', 'APPROVAL-SAVE');
				data.append('action', action);
				data.append('signature', action === 'approve' ? pad.value() : '');

				const done = app.busy(btn, 'Saving...');
				app.post('myapproval', data).then(function (res) {
					app.toast(res.message, 'success');
					later();
				}).catch(function (err) {
					done();
					app.toast(err.message, 'error');
				});
			});
		});
	};
}

document.addEventListener('DOMContentLoaded', function () {
	__mysys_request_view.__chat_thread();
	__mysys_request_view.__chat_send();
	__mysys_request_view.__hr_status();
	__mysys_request_view.__approval();
});
