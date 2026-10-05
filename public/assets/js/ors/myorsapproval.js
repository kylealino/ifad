var __mysys_ors_approval_ent = new __mysys_ors_approval_ent();
function __mysys_ors_approval_ent() {
	const siteEl = document.getElementById('__siteurl');
	const mesiteurl = siteEl.getAttribute('data-mesiteurl');
	const rows = window.__approval_data || [];
	const perms = window.__approval_perms || {};

	let table = null;
	let record = null;          // ORS open in the review modal
	let box = null;             // 'a' | 'b' — the step this user can decide on the open ORS
	let decision = null;        // 'approve' | 'disapprove' while confirming

	let filter = 'mine';          // approvers start on what is waiting for them

	const STATUS = {
		pending_a: ['tone-amber', 'Box A · requesting office'],
		pending_b: ['tone-indigo', 'Box B · budget'],
		approved: ['tone-green', 'Approved'],
		disapproved: ['tone-red', 'Disapproved']
	};

	// Line-item groups shown in the review, in ORS order
	const CLASSES = [
		{ title: 'I. Personnel Services', grids: [['ps_direct', 'Direct cost'], ['ps_indirect', 'Indirect cost']] },
		{ title: 'II. Maintenance and Other Operating Expenses', grids: [['mooe_direct', 'Direct cost'], ['mooe_indirect', 'Indirect cost']] },
		{ title: 'III. Capital Outlay', grids: [['co_direct', 'Direct cost'], ['co_indirect', 'Indirect cost']] }
	];

	function esc(s) {
		return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) {
			return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
		});
	}

	function num(v) { return parseFloat(v) || 0; }

	function money(n) {
		return '₱' + Number(n || 0).toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
	}

	function fmtDate(d) {
		if (!d || String(d).indexOf('0000') === 0) { return '—'; }
		var dt = new Date(String(d).replace(' ', 'T'));
		return isNaN(dt) ? d : dt.toLocaleDateString('en-US', { month: 'short', day: '2-digit', year: 'numeric' });
	}

	// Box A certifies first, then Box B (same rule as MyOrs::statusOf)
	function statusOf(hd) {
		if (String(hd.is_disapproved_certa) === '1' || String(hd.is_disapproved_certb) === '1') { return 'disapproved'; }
		if (String(hd.is_approved_certb) === '1') { return 'approved'; }
		if (String(hd.is_approved_certa) === '1') { return 'pending_b'; }
		return 'pending_a';
	}

	function chip(status) {
		var s = STATUS[status];
		return '<span class="fods-chip ' + s[0] + '"><span class="fods-dot"></span>' + s[1] + '</span>';
	}

	function canDecide(status) {
		return (status === 'pending_a' && !!perms.a) || (status === 'pending_b' && !!perms.b);
	}

	//APPROVAL - YEAR (the cards and the list follow the year picked in the page head)
	const YEAR_KEY = 'fods.year.myorsapproval';
	let year = String(new Date().getFullYear());
	try { year = localStorage.getItem(YEAR_KEY) || year; } catch (e) { /* storage blocked */ }

	// An ORS's year is its ORS date, or when it was encoded if that date is blank
	function yearOf(r) {
		var y = String(r.ors_date || '').slice(0, 4);
		return +y >= 2000 ? y : String(r.added_at || '').slice(0, 4);
	}

	function inYear(r) { return year === 'all' || yearOf(r) === year; }

	// Statuses behind each pill / card (any other key is a single status)
	// One Approve covers both boxes, so everything pending is "waiting for you"
	// (view-only users see the same list labelled Pending)
	const GROUPS = { mine: ['pending_a', 'pending_b'] };
	function inGroup(r, key) { return key === 'all' || (GROUPS[key] || [key]).indexOf(r.status) !== -1; }

	//APPROVAL - STAT CARDS: Pending, Returned, Approved, Total amount
	function renderCards() {
		var $ = window.jQuery;
		var list = rows.filter(inYear);
		var amount = function (l) { return l.reduce(function (t, r) { return t + num(r.amount); }, 0); };
		var count = function (status) { return list.filter(function (r) { return r.status === status; }).length; };
		var card = function (key, group, label) {
			var l = list.filter(function (r) { return inGroup(r, group); });
			$('[data-card-value="' + key + '"]').text(l.length.toLocaleString());
			$('[data-card-label="' + key + '"]').text(label() + ' · ' + money(amount(l)));
		};
		card('pending', 'mine', function () { return (perms.a ? 'Waiting for you' : 'Pending') + ' · Box A ' + count('pending_a').toLocaleString() + ' · Box B ' + count('pending_b').toLocaleString(); });
		if (!perms.a) { $('#approvalTableFilter [data-filter="mine"]').text('Pending'); }
		card('returned', 'disapproved', function () { return 'Returned'; });
		card('approved', 'approved', function () { return 'Approved'; });
		$('[data-card-value="total"]').text(money(amount(list)));
		$('[data-card-label="total"]').text('Total amount · ' + list.length.toLocaleString() + ' ORS' + (list.length === 1 ? '' : '') + '');
	}

	function yearPicker(onChange) {
		var sel = document.getElementById('approvalYear');
		var years = {};
		years[String(new Date().getFullYear())] = true;
		rows.forEach(function (r) { var y = yearOf(r); if (+y >= 2000) { years[y] = true; } });
		var list = Object.keys(years).sort().reverse();
		if (year !== 'all' && list.indexOf(year) === -1) { year = list[0]; }
		sel.innerHTML = list.map(function (y) { return '<option value="' + y + '">' + y + '</option>'; }).join('') + '<option value="all">All years</option>';
		sel.value = year;
		sel.addEventListener('change', function () {
			year = this.value;
			try { localStorage.setItem(YEAR_KEY, year); } catch (e) { /* storage blocked */ }
			onChange();
		});
	}

	//APPROVAL - LIST (status pills, stat tiles, search)
	this.__approval_table = function () {
		var $ = window.jQuery;

		$.fn.dataTable.ext.search.push(function (settings, data, idx) {
			if (settings.nTable.id !== 'approvalTable') { return true; }
			var row = settings.aoData[idx]._aData;
			return inYear(row) && inGroup(row, filter);
		});

		table = $('#approvalTable').DataTable({
			data: rows,
			deferRender: true,
			pageLength: 10,
			lengthChange: false,
			autoWidth: false,
			order: [[4, 'desc']],
			dom: 't<"d-flex flex-wrap justify-content-between align-items-center"ip>',
			language: {
				info: 'Showing _START_–_END_ of _TOTAL_',
				infoEmpty: 'Nothing to show',
				infoFiltered: '',
				zeroRecords: '<div class="text-center text-muted py-4"><i class="ti ti-inbox fs-6 d-block mb-1"></i>Nothing here</div>',
				paginate: { previous: '<i class="ti ti-chevron-left"></i>', next: '<i class="ti ti-chevron-right"></i>' }
			},
			columns: [
				{
					data: 'particulars', className: 'cell-primary',
					render: function (v, type, r) {
						if (type !== 'display') { return (r.serialno || '') + ' ' + (v || '') + ' ' + (r.payee_name || '') + ' ' + (r.funding_source || ''); }
						return '<div style="min-width:0"><div class="fw-semibold text-dark">' + esc(r.serialno || 'No serial no.') + '</div>'
							+ '<div class="small text-muted" style="overflow-wrap:anywhere">' + esc(v) + '</div>'
							+ '<div class="small text-muted d-md-none">' + esc(r.payee_name) + '</div></div>';
					}
				},
				{ data: 'payee_name', className: 'd-none d-md-table-cell', render: function (v, type) { return type === 'display' ? '<span style="overflow-wrap:anywhere">' + esc(v || '—') + '</span>' : (v || ''); } },
				{
					data: 'amount', className: 'text-end cell-left',
					render: function (v, type) { return type === 'display' ? '<span class="text-nowrap fw-semibold">' + money(v) + '</span>' : num(v); }
				},
				{
					data: 'status', className: 'cell-right',
					render: function (v, type) { return type === 'display' ? chip(v) : STATUS[v][1]; }
				},
				{
					data: 'added_at', className: 'd-none d-lg-table-cell text-muted small',
					render: function (v, type, r) { return type === 'display' ? fmtDate(v) + (r.added_by ? '<br>' + esc(r.added_by) : '') : (v || ''); }
				},
				{
					data: 'decided_by', className: 'd-none d-xl-table-cell text-muted small',
					render: function (v, type) { return type === 'display' ? esc(v || '—') : (v || ''); }
				},
				{
					data: null, orderable: false, searchable: false, className: 'cell-actions text-end',
					render: function (v, type, r) {
						var review = canDecide(r.status)
							? '<button type="button" class="btn btn-sm btn-fods approval-review" data-recid="' + r.recid + '"><i class="ti ti-file-search me-1"></i>Review</button>'
							: '<button type="button" class="fods-icon-btn is-teal approval-review" data-recid="' + r.recid + '" title="View"><i class="ti ti-eye"></i></button>';
						return '<div class="d-flex justify-content-end align-items-center gap-1">' + review
							+ '<button type="button" class="fods-icon-btn approval-print" data-recid="' + r.recid + '" title="Print ORS"><i class="ti ti-printer"></i></button></div>';
					}
				}
			]
		});

		$('#approvalTableSearch').on('input', function () { table.search(this.value).draw(); });

		function applyFilter(key) {
			filter = key;
			$('#approvalTableFilter button').removeClass('active').filter('[data-filter="' + key + '"]').addClass('active');
			$('.fods-stat[data-table="approvalTable"]').removeClass('is-active')
				.filter('[data-filter="' + key + '"]').addClass('is-active');
			table.draw();
		}
		applyFilter(filter);
		$(document).on('click', '#approvalTableFilter button', function () { applyFilter($(this).data('filter')); });
		$(document).on('click', '.fods-stat[data-table="approvalTable"]', function () { applyFilter($(this).data('filter')); });

		yearPicker(function () { renderCards(); table.draw(); });
		renderCards();
		table.draw();

		$(document).on('click', '#approvalTable .approval-review', function () { __mysys_ors_approval_ent.__open_review($(this).data('recid')); });
		$(document).on('click', '#approvalTable .approval-print', function () { __mysys_ors_approval_ent.__showPdfInModal($(this).data('recid')); });
	};

	//APPROVAL - REVIEW MODAL (read-only ORS)
	function field(label, value, wide) {
		return '<div class="rev-field' + (wide ? ' wide' : '') + '"><div class="lbl">' + label + '</div><div class="val">' + (value ? esc(value) : '<span class="text-muted">—</span>') + '</div></div>';
	}

	function renderFields(hd) {
		var deductions = [];
		if (num(hd.vat_percent)) { deductions.push('VAT ' + num(hd.vat_percent) + '%'); }
		if (num(hd.ewt_percent)) { deductions.push('EWT ' + num(hd.ewt_percent) + '%'); }
		if (num(hd.pt_percent)) { deductions.push('PT ' + num(hd.pt_percent) + '%'); }
		if (num(hd.other_deductions)) { deductions.push((hd.deduction_name || 'Other') + ' ' + money(hd.other_deductions)); }
		if (num(hd.income_tax)) { deductions.push('Income tax ' + money(hd.income_tax)); }
		[['sss', 'SSS'], ['hdmf', 'HDMF'], ['pagibig_mp2', 'Pag-IBIG MP2'], ['pagibig_loan', 'Pag-IBIG loan'], ['hmo', 'HMO'], ['wtax', 'WTAX']].forEach(function (p) {
			if (num(hd[p[0]])) { deductions.push(p[1] + ' ' + money(hd[p[0]])); }
		});

		document.getElementById('reviewFields').innerHTML = field('Particulars', hd.particulars, true)
			+ field('Payee', hd.payee_name)
			+ field('Office', hd.payee_office)
			+ field('Address', hd.payee_address)
			+ field('Date', fmtDate(hd.ors_date))
			+ field('Funding source', hd.funding_source)
			+ field('Fund cluster', hd.fund_cluster_code)
			+ field('Category', (hd.category || '') + (String(hd.is_vatable) === '1' ? ' · VATable' : ''))
			+ field('DV no.', hd.dvno)
			+ field('Prepared by', (hd.added_by || '—') + ' · ' + fmtDate(hd.added_at))
			+ field('Deductions', deductions.join(' · '), true);
	}

	function sum(list) { return (list || []).reduce(function (s, r) { return s + num(r.amount); }, 0); }

	function renderTotals(hd) {
		var g = hd.grids || {};
		var ps = sum(g.ps_direct) + sum(g.ps_indirect);
		var mooe = sum(g.mooe_direct) + sum(g.mooe_indirect);
		var co = sum(g.co_direct) + sum(g.co_indirect);
		var gross = ps + mooe + co;
		var deduction = num(hd.total_deduction);
		var tile = function (lbl, amt, grand) {
			return '<div class="rev-total' + (grand ? ' grand' : '') + '"><div class="lbl">' + lbl + '</div><div class="amt">' + money(amt) + '</div></div>';
		};
		document.getElementById('reviewTotals').innerHTML = tile('Personnel Services', ps) + tile('MOOE', mooe) + tile('Capital Outlay', co)
			+ tile('Gross amount', gross) + tile('Deductions', deduction) + tile('Net payable', num(hd.net_amount) || gross - deduction, true);
	}

	function renderItems(g) {
		var html = '';
		CLASSES.forEach(function (cls) {
			var tables = '';
			var classTotal = 0;
			cls.grids.forEach(function (spec) {
				var list = g[spec[0]] || [];
				if (!list.length) { return; }
				var total = 0;
				var body = list.map(function (r) {
					total += num(r.amount);
					return '<tr><td>' + esc(r.program_title) + '</td><td class="text-nowrap">' + esc(r.responsibility_code) + '</td>'
						+ '<td>' + esc(r.project_title) + '</td><td class="text-nowrap">' + esc(r.mfopaps_code) + '</td>'
						+ '<td>' + esc(r.sub_object_code) + '</td><td class="text-nowrap">' + esc(r.uacs_code) + '</td>'
						+ '<td class="num">' + money(r.amount) + '</td></tr>';
				}).join('');
				classTotal += total;
				tables += '<div class="rev-table-wrap"><table class="rev-table"><caption>' + spec[1] + ' · ' + list.length + ' line' + (list.length === 1 ? '' : 's') + '</caption>'
					+ '<thead><tr><th>Program</th><th>Resp. code</th><th>Project</th><th>MFO/PAP</th><th>Sub-object code</th><th>UACS</th><th class="num">Amount</th></tr></thead>'
					+ '<tbody>' + body + '</tbody>'
					+ '<tfoot><tr><td colspan="6">Subtotal</td><td class="num">' + money(total) + '</td></tr></tfoot>'
					+ '</table></div>';
			});
			if (tables) {
				html += '<div class="rev-class"><div class="rev-class-title"><span>' + cls.title + '</span><span>' + money(classTotal) + '</span></div>' + tables + '</div>';
			}
		});
		document.getElementById('reviewItems').innerHTML = html || '<div class="text-muted small mt-3">This ORS has no line items.</div>';
	}

	function renderSteps(hd, status) {
		var step = function (b, title) {
			var approved = String(hd['is_approved_cert' + b]) === '1';
			var disapproved = String(hd['is_disapproved_cert' + b]) === '1';
			var current = status === 'pending_' + b;
			var state = approved ? '<span class="fods-chip tone-green"><i class="ti ti-check"></i>Certified</span>'
				: disapproved ? '<span class="fods-chip tone-red"><i class="ti ti-x"></i>Disapproved</span>'
				: current ? '<span class="fods-chip tone-amber"><span class="fods-dot"></span>Waiting</span>'
				: '<span class="fods-chip tone-slate">Not yet</span>';
			var by = hd['cert' + b + '_approver'], remarks = hd['cert' + b + '_remarks'];
			var note = (approved || disapproved)
				? 'Decided by ' + esc(by || '—') + (remarks ? ' — ' + esc(remarks) : '')
				: 'Named signatory';
			return '<div class="rev-step' + (current ? ' is-current' : '') + '">'
				+ '<div class="rev-step-head"><span class="rev-step-box">' + title + '</span>' + state + '</div>'
				+ '<div class="rev-step-name">' + esc(hd['certified_' + b] || '—') + '</div>'
				+ '<div class="rev-step-note">' + (hd['position_' + b] ? esc(hd['position_' + b]) + ' · ' : '') + note + '</div>'
				+ '</div>';
		};
		document.getElementById('reviewSteps').innerHTML = step('a', 'Box A · Requesting office') + step('b', 'Box B · Budget');
	}

	function showActions(open) {
		decision = null;
		document.getElementById('reviewConfirm').classList.add('d-none');
		document.getElementById('reviewActions').classList.remove('d-none');
		document.getElementById('reviewApproveBtn').classList.toggle('d-none', !open);
		document.getElementById('reviewDisapproveBtn').classList.toggle('d-none', !open);
		document.getElementById('reviewDecisionCard').classList.toggle('d-none', !open);
	}

	this.__open_review = function (recid) {
		var $ = window.jQuery;
		$.getJSON(mesiteurl + 'myorsapproval', { meaction: 'MAIN-LOAD', recid: recid })
			.done(function (hd) {
				record = hd;
				var status = statusOf(hd);
				var decide = canDecide(status);
				box = decide ? status.slice(-1) : null;

				document.getElementById('reviewTitle').textContent = hd.serialno || 'ORS #' + hd.recid;
				document.getElementById('reviewSub').innerHTML = esc(hd.payee_name) + ' &nbsp;' + chip(status);

				var banner = document.getElementById('reviewBanner');
				if (status === 'approved') {
					banner.className = 'alert mb-3 alert-success';
					banner.innerHTML = '<b>Fully certified</b> — Box A by ' + esc(hd.certa_approver || '—') + ', Box B by ' + esc(hd.certb_approver || '—') + '.';
				} else if (status === 'disapproved') {
					var b = String(hd.is_disapproved_certa) === '1' ? 'a' : 'b';
					banner.className = 'alert mb-3 alert-danger';
					banner.innerHTML = '<b>Disapproved at Box ' + b.toUpperCase() + '</b>' + (hd['cert' + b + '_approver'] ? ' by ' + esc(hd['cert' + b + '_approver']) : '')
						+ (hd['cert' + b + '_remarks'] ? ' — ' + esc(hd['cert' + b + '_remarks']) : '') + '. It comes back here once the preparer corrects it.';
				} else {
					banner.className = 'alert d-none mb-3';
					banner.innerHTML = '';
				}

				renderSteps(hd, status);
				renderFields(hd);
				renderTotals(hd);
				renderItems(hd.grids || {});

				var remarks = document.getElementById('reviewRemarks');
				remarks.value = '';
				remarks.classList.remove('is-invalid');
				document.getElementById('reviewDecisionTitle').textContent = box === 'a' ? 'Your decision · Box A (requesting office)' : 'Your decision · Box B (budget)';
				showActions(decide);

				var note;
				if (decide) { note = 'Check the line items, then certify or disapprove.'; }
				else if (status === 'pending_a') { note = 'Waiting for the requesting office (Box A).'; }
				else if (status === 'pending_b') { note = 'Waiting for budget (Box B).'; }
				else { note = 'Already decided — view only.'; }
				document.getElementById('reviewFooterNote').textContent = note;

				bootstrap.Modal.getOrCreateInstance(document.getElementById('reviewModal')).show();
			})
			.fail(function (xhr) {
				toastr.error((xhr.responseJSON && xhr.responseJSON.error) || 'Could not load this ORS.', 'Oops!', { closeButton: true });
			});
	};

	//APPROVAL - CERTIFY / DISAPPROVE (two-step: choose, then confirm)
	this.__approval_decision = function () {
		var $ = window.jQuery;
		var remarks = document.getElementById('reviewRemarks');

		function ask(kind) {
			if (kind === 'disapprove' && !remarks.value.trim()) {
				remarks.classList.add('is-invalid');
				remarks.focus();
				toastr.warning('Add remarks so the preparer knows what to change.', 'Remarks needed', { closeButton: true });
				return;
			}
			decision = kind;
			var approve = kind === 'approve';
			document.getElementById('reviewConfirmText').textContent = (approve ? 'Certify' : 'Disapprove') + ' this ORS at Box ' + String(box).toUpperCase() + '?';
			var yes = document.getElementById('reviewConfirmYes');
			yes.className = 'btn btn-sm px-4 ' + (approve ? 'btn-success' : 'btn-danger');
			yes.textContent = approve ? 'Yes, certify' : 'Yes, disapprove';
			document.getElementById('reviewActions').classList.add('d-none');
			document.getElementById('reviewConfirm').classList.remove('d-none');
		}

		remarks.addEventListener('input', function () { remarks.classList.remove('is-invalid'); });
		document.getElementById('reviewApproveBtn').addEventListener('click', function () { ask('approve'); });
		document.getElementById('reviewDisapproveBtn').addEventListener('click', function () { ask('disapprove'); });
		document.getElementById('reviewConfirmCancel').addEventListener('click', function () { showActions(true); });

		document.getElementById('reviewConfirmYes').addEventListener('click', function () {
			if (!record || !decision || !box) { return; }
			var btn = $(this);
			btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Saving...');
			$.ajax({
				type: 'POST',
				url: mesiteurl + 'myorsapproval',
				data: {
					recid: record.recid,
					remarks: remarks.value,
					meaction: (decision === 'approve' ? 'MAIN-APPROVE-' : 'MAIN-DISAPPROVE-') + box.toUpperCase()
				},
				global: false,
				cache: false,
				success: function (data) { $('.me-outp-msg').html(data); },
				error: function (xhr, status, error) { toastr.error('[ORS APPROVAL]', 'Error loading page...' + error, { closeButton: true }); },
				complete: function () { setTimeout(function () { btn.prop('disabled', false); }, 1200); }
			});
		});
	};

	//APPROVAL - PRINT ORS
	this.__showPdfInModal = function (recid) {
		document.getElementById('approvalPrintFrame').src = mesiteurl + 'myorsapproval?meaction=PRINT-ORS&recid=' + encodeURIComponent(recid);
		bootstrap.Modal.getOrCreateInstance(document.getElementById('approvalPrintModal')).show();
	};

	this.__approval_print = function () {
		document.getElementById('reviewPrintBtn').addEventListener('click', function () {
			if (record) { __mysys_ors_approval_ent.__showPdfInModal(record.recid); }
		});
	};
}

// jQuery, DataTables and Bootstrap load in the footer, after this file
document.addEventListener('DOMContentLoaded', function () {
	__mysys_ors_approval_ent.__approval_table();
	__mysys_ors_approval_ent.__approval_decision();
	__mysys_ors_approval_ent.__approval_print();

	if (window.__approval_open) { __mysys_ors_approval_ent.__open_review(window.__approval_open); }
});
