// Record history drawer, the same in every module: it slides in from the right,
// over the record window it was opened from (it is placed inside that window, so
// the window's focus handling and Esc leave it alone; Esc or the backdrop closes
// only the drawer). Each record's window has a History button in its header
// (data-history-module + data-history-field: the record id is read from that
// field in the window, and the button only shows once the record is saved);
// Abstract's page uses data-history-recid. The entries come from
// myhistory?module=&recid= (MyHistory), newest first.
(function () {
	// The kind of action each entry is, shown as a tag: [tag, tone]
	var KINDS = {
		approval: ['Approval', 'green'],
		returned: ['Returned', 'red'],
		submission: ['Submission', 'indigo'],
		created: ['New record', 'teal'],
		update: ['Update', 'amber'],
		status: ['Status', 'slate'],
		deleted: ['Deleted', 'red']
	};

	// action_type => [label, tone, icon, kind]
	var ACTIONS = {
		CREATED: ['Created', 'teal', 'ti-plus', 'created'],
		UPDATED: ['Updated', 'amber', 'ti-pencil', 'update'],
		SUBMITTED: ['Submitted for approval', 'indigo', 'ti-send', 'submission'],
		RESUBMITTED: ['Corrected and resubmitted for approval', 'indigo', 'ti-send', 'submission'],
		APPROVED: ['Approved', 'green', 'ti-check', 'approval'],
		DISAPPROVED: ['Disapproved and returned to the preparer', 'red', 'ti-arrow-back-up', 'returned'],
		DELETED: ['Deleted', 'red', 'ti-trash', 'deleted'],
		RFQ_ADDED: ['RFQ added', 'amber', 'ti-file-plus', 'update'],
		PRNO_SET: ['PR no. assigned', 'amber', 'ti-hash', 'update'],
		DOCUMENT_ADDED: ['Document added', 'teal', 'ti-paperclip', 'update'],
		DOCUMENT_REMOVED: ['Document removed', 'red', 'ti-trash', 'update'],
		ACTIVATED: ['Activated', 'slate', 'ti-player-play', 'status'],
		DEACTIVATED: ['Deactivated', 'slate', 'ti-player-pause', 'status'],
		PERMISSIONS_UPDATED: ['Permissions updated', 'amber', 'ti-shield-check', 'update']
	};

	// Field names that read better spelled out
	var FIELDS = {
		serialno: 'Serial no.', dvno: 'DV no.', prno: 'PR no.', pono: 'PO no.', adano: 'ADA no.', ckno: 'Check no.',
		lddapadano: 'LDDAP-ADA no.', trxno: 'Transaction no.', ppmpno: 'PPMP no.', reportno: 'Report no.',
		certified_a: 'Box A signatory', certified_b: 'Box B signatory', certified_c: 'Box C signatory',
		position_a: 'Box A position', position_b: 'Box B position', position_c: 'Box C position',
		uacs_code: 'UACS code', mfopaps_code: 'MFO/PAP code', payee_tin: 'TIN', payee_account_num: 'Account no.',
		vat_percent: 'VAT %', ewt_percent: 'EWT %', pt_percent: 'PT %', fund_cluster_code: 'Fund cluster', role_id: 'Role',
		contact_no: 'Contact no.', email: 'Email'
	};

	function esc(s) {
		return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) {
			return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
		});
	}

	function cap(s) { return s.charAt(0).toUpperCase() + s.slice(1); }

	// APPROVED_BOX_A -> "Box A approved"; DISAPPROVED_CERTB (older DVs) -> "Returned at Box B"
	function action(type) {
		type = String(type || '').toUpperCase();
		var box = type.match(/^(APPROVED|CERTIFIED|DISAPPROVED)_(?:BOX_|CERT)([A-Z])$/);
		if (box) {
			return box[1] === 'DISAPPROVED'
				? ['Disapproved at Box ' + box[2] + ' and returned to the preparer', 'red', 'ti-arrow-back-up', 'returned']
				: ['Box ' + box[2] + ' ' + (box[1] === 'CERTIFIED' ? 'certified' : 'approved'), 'green', 'ti-check', 'approval'];
		}
		return ACTIONS[type] || [cap(type.toLowerCase().replace(/_/g, ' ')), 'slate', 'ti-point', 'update'];
	}

	function field(name) {
		name = String(name || '');
		return FIELDS[name] || cap(name.replace(/_/g, ' '));
	}

	function toDate(d) { var dt = new Date(String(d || '').replace(' ', 'T')); return isNaN(dt) ? null : dt; }
	function day(d) { var dt = toDate(d); return dt ? dt.toLocaleDateString('en-US', { weekday: 'short', month: 'short', day: '2-digit', year: 'numeric' }) : 'Date not recorded'; }
	function time(d) { var dt = toDate(d); return dt ? dt.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit' }) : ''; }
	function dateTime(d) { var dt = toDate(d); return dt ? dt.toLocaleString('en-US', { month: 'short', day: '2-digit', year: 'numeric', hour: 'numeric', minute: '2-digit' }) : ''; }

	var list = [];
	var drawerEl = null, backdropEl = null, returnFocus = null;

	// The drawer on the right, built once
	function drawer() {
		if (drawerEl) { return drawerEl; }
		backdropEl = document.createElement('div');
		backdropEl.className = 'fods-hist-backdrop';
		backdropEl.addEventListener('click', close);

		drawerEl = document.createElement('aside');
		drawerEl.className = 'fods-hist-drawer';
		drawerEl.id = 'fodsHistoryDrawer';
		drawerEl.tabIndex = -1;
		drawerEl.setAttribute('role', 'dialog');
		drawerEl.setAttribute('aria-modal', 'true');
		drawerEl.setAttribute('aria-labelledby', 'fodsHistoryTitle');
		drawerEl.setAttribute('aria-hidden', 'true');
		drawerEl.innerHTML = '<div class="fods-hist-drawer-head"><div class="fods-modal-icon tone-slate"><i class="ti ti-history"></i></div>'
			+ '<div style="min-width:0;flex:1"><h5 class="fods-hist-drawer-title" id="fodsHistoryTitle">History</h5><div class="fods-hist-drawer-sub text-truncate" id="fodsHistorySub"></div></div>'
			+ '<button type="button" class="btn-close" data-hist-close aria-label="Close"></button></div>'
			+ '<div class="fods-hist-bar"><div class="fods-hist-summary" id="fodsHistorySummary"></div></div>'
			+ '<div class="fods-hist-drawer-body" id="fodsHistoryBody"></div>';
		drawerEl.querySelector('[data-hist-close]').addEventListener('click', close);
		return drawerEl;
	}

	function isOpen() { return !!drawerEl && drawerEl.classList.contains('is-open'); }

	// Slide in. Inside the open record window it stacks above that window and keeps its focus.
	function show(host) {
		drawer();
		host = host || document.body;
		if (backdropEl.parentNode !== host) { host.appendChild(backdropEl); host.appendChild(drawerEl); }
		returnFocus = document.activeElement;
		drawerEl.setAttribute('aria-hidden', 'false');
		void drawerEl.offsetWidth;   // start the slide from off-screen
		backdropEl.classList.add('is-open');
		drawerEl.classList.add('is-open');
		drawerEl.focus({ preventScroll: true });
	}

	function close() {
		if (!isOpen()) { return; }
		drawerEl.classList.remove('is-open');
		backdropEl.classList.remove('is-open');
		drawerEl.setAttribute('aria-hidden', 'true');
		if (returnFocus && document.contains(returnFocus)) { returnFocus.focus({ preventScroll: true }); }
		returnFocus = null;
	}

	// Esc closes the drawer only, not the record window under it
	document.addEventListener('keydown', function (e) {
		if (e.key === 'Escape' && isOpen()) { e.preventDefault(); e.stopPropagation(); close(); }
	}, true);

	// The record window closing takes the drawer with it
	document.addEventListener('hide.bs.modal', function (e) {
		if (isOpen() && drawerEl.parentNode === e.target) { close(); }
	});

	function who(h) { return h.action_by_name || h.action_by || 'Unknown user'; }

	// "Created by X on date · Last: Approved by Y, date · N entries"
	function renderSummary() {
		var el = document.getElementById('fodsHistorySummary');
		if (!list.length) { el.textContent = ''; return; }
		var first = list[list.length - 1], last = list[0];
		var created = action(first.action_type)[0] === 'Created' ? 'Created by <b>' + esc(who(first)) + '</b> · ' + esc(dateTime(first.added_at)) : '';
		var latest = list.length > 1 || !created ? 'Last: ' + esc(action(last.action_type)[0]) + ' by <b>' + esc(who(last)) + '</b>' + (dateTime(last.added_at) ? ' · ' + esc(dateTime(last.added_at)) : '') : '';
		el.innerHTML = [created, latest].filter(Boolean).join('<br>')
			+ '<span class="fods-hist-count">' + list.length + (list.length === 1 ? ' entry' : ' entries') + '</span>';
	}

	function changesTable(raw) {
		var fc;
		try { fc = raw ? JSON.parse(raw) : null; } catch (e) { fc = null; }
		if (!Array.isArray(fc) || !fc.length) { return ''; }
		return '<div class="fods-hist-changes"><table><thead><tr><th>Field</th><th>Before</th><th>After</th></tr></thead><tbody>'
			+ fc.map(function (c) {
				return '<tr><td>' + esc(field(c.field)) + '</td><td class="was">' + (c.old !== '' && c.old != null ? esc(c.old) : '<span class="nil">blank</span>') + '</td>'
					+ '<td class="now">' + (c['new'] !== '' && c['new'] != null ? esc(c['new']) : '<span class="nil">blank</span>') + '</td></tr>';
			}).join('') + '</tbody></table></div>';
	}

	function renderBody() {
		var body = document.getElementById('fodsHistoryBody');
		var shown = list;
		if (!list.length) { body.innerHTML = '<div class="fods-hist-empty"><i class="ti ti-history"></i>No history recorded yet.<div class="small">Changes and approvals are recorded from now on.</div></div>'; return; }

		var html = '', lastDay = null;
		shown.forEach(function (h) {
			var d = day(h.added_at);
			if (d !== lastDay) {
				if (lastDay !== null) { html += '</ul>'; }
				html += '<div class="fods-hist-day">' + esc(d) + '</div><ul class="fods-hist">';
				lastDay = d;
			}
			var a = action(h.action_type);
			var k = KINDS[a[3]] || KINDS.update;
			// approvals, returns and submissions stand out as tinted cards
			var decision = a[3] === 'approval' || a[3] === 'returned' || a[3] === 'submission';
			html += '<li><span class="fods-hist-icon tone-' + a[1] + '"><i class="ti ' + a[2] + '"></i></span>'
				+ '<div class="fods-hist-main' + (decision ? ' is-decision is-' + k[1] : '') + '">'
				+ '<div class="fods-hist-head"><span><span class="fods-chip tone-' + k[1] + ' fods-hist-kind">' + esc(k[0]) + '</span><span class="fods-hist-what">' + esc(a[0]) + '</span></span>'
				+ '<span class="fods-hist-time">' + esc(time(h.added_at)) + '</span></div>'
				+ '<div class="fods-hist-by">by ' + esc(who(h)) + (h.ref_no ? ' · ' + esc(h.ref_no) : '') + (String(h.is_earlier) === '1' ? ' · <span class="fods-hist-earlier" title="Taken from the record: it was saved before the audit trail started">from the record</span>' : '') + '</div>'
				+ (h.remarks ? '<div class="fods-hist-note">' + esc(h.remarks) + '</div>' : '')
				+ changesTable(h.field_changes) + '</div></li>';
		});
		body.innerHTML = html + '</ul>';
	}

	function open(module, recid, title, host) {
		var site = document.getElementById('__siteurl');
		var base = site ? site.getAttribute('data-mesiteurl') : '';
		show(host);   // attach and slide in first: the lookups below need it on the page
		list = [];
		document.getElementById('fodsHistorySub').textContent = title || '';
		document.getElementById('fodsHistorySummary').textContent = '';
		document.getElementById('fodsHistoryBody').innerHTML = '<div class="fods-hist-empty"><span class="spinner-border spinner-border-sm"></span>Loading...</div>';
		document.getElementById('fodsHistoryBody').scrollTop = 0;

		fetch(base + 'myhistory?module=' + encodeURIComponent(module) + '&recid=' + encodeURIComponent(recid), { credentials: 'same-origin', headers: { 'Accept': 'application/json' } })
			.then(function (r) { return r.json().then(function (d) { return { ok: r.ok, d: d }; }); })
			.then(function (res) {
				if (!res.ok) { throw new Error(res.d && res.d.error ? res.d.error : 'Could not load the history.'); }
				if (!title) { document.getElementById('fodsHistorySub').textContent = res.d.label || ''; }
				list = res.d.history || [];
				renderSummary();
				renderBody();
			})
			.catch(function (e) {
				document.getElementById('fodsHistoryBody').innerHTML = '<div class="fods-hist-empty text-danger"><i class="ti ti-alert-circle"></i>' + esc(e.message || 'Could not load the history.') + '</div>';
			});
	}

	window.FodsHistory = { open: open, close: close };

	// The record id: data-history-recid, or the data-history-field field (id or name) in the button's window
	function recidOf(btn) {
		var direct = btn.getAttribute('data-history-recid');
		if (direct) { return direct; }
		var name = btn.getAttribute('data-history-field');
		var scope = btn.closest('.modal') || document;
		var el = name ? (scope.querySelector('#' + name) || scope.querySelector('[name="' + name + '"]')) : null;
		return el ? String(el.value || '').trim() : '';
	}

	document.addEventListener('click', function (e) {
		var btn = e.target.closest ? e.target.closest('[data-history-module]') : null;
		if (!btn) { return; }
		var recid = recidOf(btn);
		if (!recid) { return; }
		e.preventDefault();
		var m = btn.closest('.modal');
		var heading = m ? m.querySelector('.modal-title') : null;
		open(btn.getAttribute('data-history-module'), recid, btn.getAttribute('data-history-title') || (heading ? heading.textContent.trim() : ''), m);
	});

	// A window's History button shows only for a saved record
	document.addEventListener('shown.bs.modal', function (e) {
		e.target.querySelectorAll('[data-history-field]').forEach(function (btn) {
			btn.classList.toggle('d-none', !recidOf(btn));
		});
	});
})();
