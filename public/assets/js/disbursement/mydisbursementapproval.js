var __mysys_disbursement_approval_ent = new __mysys_disbursement_approval_ent();
function __mysys_disbursement_approval_ent() {
	const siteEl = document.getElementById('__siteurl');
	const mesiteurl = siteEl.getAttribute('data-mesiteurl');
	const rows = window.__approval_data || [];
	const perms = window.__approval_perms || {};

	let table = null;
	let filter = 'pending';          // approvers start on what is waiting for them
	let record = null;          // DV open in the review modal
	let decision = null;        // 'approve' | 'disapprove' while confirming

	const STATUS = {
		pending: ['tone-amber', 'Pending'],
		approved: ['tone-green', 'Approved'],
		disapproved: ['tone-red', 'Disapproved']
	};

	// Line-item groups shown in the review, in DV order
	const CLASSES = [
		{ title: 'I. Personnel Services', grids: [['ps_direct', 'Direct cost'], ['ps_indirect', 'Indirect cost']] },
		{ title: 'II. Maintenance and Other Operating Expenses', grids: [['mooe_direct', 'Direct cost'], ['mooe_indirect', 'Indirect cost']] },
		{ title: 'III. Capital Outlay', grids: [['co_direct', 'Direct cost'], ['co_indirect', 'Indirect cost']] }
	];

	// The *_CERTA/B entries are from the earlier two-step flow
	const HISTORY = {
		ADDED: ['Created', '#0f6f6f'],
		UPDATED: ['Updated', '#b45309'],
		APPROVED: ['Approved', '#15803d'],
		DISAPPROVED: ['Disapproved', '#b91c1c'],
		APPROVED_CERTA: ['Approved', '#15803d'],
		DISAPPROVED_CERTA: ['Disapproved', '#b91c1c'],
		APPROVED_CERTB: ['Approved', '#15803d'],
		DISAPPROVED_CERTB: ['Disapproved', '#b91c1c']
	};

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

	function fmtDateTime(d) {
		var dt = new Date(String(d || '').replace(' ', 'T'));
		return isNaN(dt) ? (d || '') : dt.toLocaleString('en-US', { month: 'short', day: '2-digit', year: 'numeric', hour: 'numeric', minute: '2-digit' });
	}

	// Single accounting approval (same rule as MyDisbursement::statusOf)
	function statusOf(hd) {
		if (String(hd.is_disapproved_certa) === '1') { return 'disapproved'; }
		if (String(hd.is_approved_certa) === '1') { return 'approved'; }
		return 'pending';
	}

	function chip(status) {
		var s = STATUS[status];
		return '<span class="fods-chip ' + s[0] + '"><span class="fods-dot"></span>' + s[1] + '</span>';
	}

	function moneyShort(n) {
		n = Number(n || 0);
		if (n >= 1e9) { return '₱' + (n / 1e9).toFixed(2) + 'B'; }
		if (n >= 1e6) { return '₱' + (n / 1e6).toFixed(1) + 'M'; }
		if (n >= 1e3) { return '₱' + (n / 1e3).toFixed(1) + 'K'; }
		return money(n);
	}

	//APPROVAL - YEAR (the cards and the list follow the year picked in the page head)
	const YEAR_KEY = 'fods.year.mydisbursementapproval';
	let year = String(new Date().getFullYear());
	try { year = localStorage.getItem(YEAR_KEY) || year; } catch (e) { /* storage blocked */ }

	// A DV's year is its disbursement date, or when it was encoded if that date is blank
	function yearOf(r) {
		var y = String(r.disbursement_date || '').slice(0, 4);
		return +y >= 2000 ? y : String(r.added_at || '').slice(0, 4);
	}

	function inYear(r) { return year === 'all' || yearOf(r) === year; }

	//APPROVAL - STAT CARDS: Waiting for you, Returned, Approved, Total amount
	function renderCards() {
		var $ = window.jQuery;
		var list = rows.filter(inYear);
		var amount = function (l) { return l.reduce(function (t, r) { return t + num(r.net_amount); }, 0); };
		var card = function (key, status, label) {
			var l = list.filter(function (r) { return r.status === status; });
			$('[data-card-value="' + key + '"]').text(l.length.toLocaleString());
			$('[data-card-label="' + key + '"]').text(label + ' · ' + moneyShort(amount(l)));
		};
		card('pending', 'pending', 'Waiting for you');
		card('returned', 'disapproved', 'Returned');
		card('approved', 'approved', 'Approved');
		$('[data-card-value="total"]').text(money(amount(list)));
		$('[data-card-label="total"]').text('Total amount · ' + list.length.toLocaleString() + ' DV' + (list.length === 1 ? '' : 's'));
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
			if (!inYear(row)) { return false; }
			return filter === 'all' || row.status === filter;
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
						if (type !== 'display') { return (r.dvno || '') + ' ' + (v || '') + ' ' + (r.payee_name || '') + ' ' + (r.serialno || ''); }
						return '<div style="min-width:0"><div class="fw-semibold text-dark">' + (r.dvno ? 'DV ' + esc(r.dvno) : '<span class="text-muted">No DV no. yet</span>') + '</div>'
							+ '<div class="small text-muted" style="overflow-wrap:anywhere">' + esc(v) + '</div>'
							+ '<div class="small text-muted">' + esc(r.serialno) + '</div></div>';
					}
				},
				{ data: 'payee_name', className: 'd-none d-md-table-cell', render: function (v, type) { return type === 'display' ? '<span style="overflow-wrap:anywhere">' + esc(v || '—') + '</span>' : (v || ''); } },
				{
					data: 'net_amount', className: 'text-end cell-left',
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
						var review = r.status === 'pending' && perms.approve
							? '<button type="button" class="btn btn-sm btn-fods approval-review" data-recid="' + r.recid + '"><i class="ti ti-file-search me-1"></i>Review</button>'
							: '<button type="button" class="fods-icon-btn is-teal approval-review" data-recid="' + r.recid + '" title="View"><i class="ti ti-eye"></i></button>';
						return '<div class="d-flex justify-content-end align-items-center gap-1">' + review
							+ '<button type="button" class="fods-icon-btn approval-print" data-recid="' + r.recid + '" title="Print DV"><i class="ti ti-printer"></i></button></div>';
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
		yearPicker(function () { renderCards(); table.draw(); });
		renderCards();
		applyFilter(filter);
		$(document).on('click', '#approvalTableFilter button', function () { applyFilter($(this).data('filter')); });
		$(document).on('click', '.fods-stat[data-table="approvalTable"]', function () { applyFilter($(this).data('filter')); });

		$(document).on('click', '#approvalTable .approval-review', function () { __mysys_disbursement_approval_ent.__open_review($(this).data('recid')); });
		$(document).on('click', '#approvalTable .approval-print', function () { __mysys_disbursement_approval_ent.__showPdfInModal($(this).data('recid')); });
	};

	//APPROVAL - REVIEW MODAL (read-only DV)
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
			+ field('DV no.', hd.dvno)
			+ field('ORS / BURS no.', hd.serialno)
			+ field('Date', fmtDate(hd.disbursement_date))
			+ field('Payee', hd.payee_name)
			+ field('Office', hd.payee_office)
			+ field('Address', hd.payee_address)
			+ field('Funding source', hd.funding_source)
			+ field('Fund cluster', hd.fund_cluster_code)
			+ field('Category', (hd.category || '') + (String(hd.is_vatable) === '1' ? ' · VATable' : ''))
			+ field('Prepared by', (hd.added_by || '—') + ' · ' + fmtDate(hd.added_at))
			+ field('Deductions', deductions.join(' · '), true);
	}

	function sum(list) { return (list || []).reduce(function (s, r) { return s + num(r.amount); }, 0); }

	function renderTotals(hd) {
		var g = hd.grids || {};
		var ps = sum(g.ps_direct) + sum(g.ps_indirect);
		var mooe = sum(g.mooe_direct) + sum(g.mooe_indirect);
		var co = sum(g.co_direct) + sum(g.co_indirect);
		var gross = num(hd.gross_amount) || ps + mooe + co;
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
					return '<tr><td class="text-nowrap">' + esc(r.responsibility_code) + '</td><td class="text-nowrap">' + esc(r.mfopaps_code) + '</td>'
						+ '<td class="num">' + money(r.amount) + '</td></tr>';
				}).join('');
				classTotal += total;
				tables += '<div class="rev-table-wrap"><table class="rev-table"><caption>' + spec[1] + ' · ' + list.length + ' line' + (list.length === 1 ? '' : 's') + '</caption>'
					+ '<thead><tr><th>Responsibility code</th><th>MFO/PAP</th><th class="num">Amount</th></tr></thead>'
					+ '<tbody>' + body + '</tbody>'
					+ '<tfoot><tr><td colspan="2">Subtotal</td><td class="num">' + money(total) + '</td></tr></tfoot>'
					+ '</table></div>';
			});
			if (tables) {
				html += '<div class="rev-class"><div class="rev-class-title"><span>' + cls.title + '</span><span>' + money(classTotal) + '</span></div>' + tables + '</div>';
			}
		});
		document.getElementById('reviewItems').innerHTML = html || '<div class="text-muted small mt-3">This DV has no line items.</div>';
	}

	// The DV's signatories: Box A (supervisor) is printed on the DV only;
	// accounting (Box C) carries the approval status
	function renderSigners(hd, status) {
		var state = status === 'approved' ? '<span class="fods-chip tone-green"><i class="ti ti-check"></i>Approved</span>'
			: status === 'disapproved' ? '<span class="fods-chip tone-red"><i class="ti ti-x"></i>Disapproved</span>'
			: '<span class="fods-chip tone-amber"><span class="fods-dot"></span>Pending</span>';
		var decided = status !== 'pending'
			? 'Decided by ' + esc(hd.certa_approver || '—') + (hd.certa_remarks ? ' — ' + esc(hd.certa_remarks) : '')
			: 'Waiting for accounting';
		var card = function (title, name, position, right, note, current) {
			return '<div class="rev-step' + (current ? ' is-current' : '') + '">'
				+ '<div class="rev-step-head"><span class="rev-step-box">' + title + '</span>' + right + '</div>'
				+ '<div class="rev-step-name">' + esc(name || '—') + '</div>'
				+ '<div class="rev-step-note">' + (position ? esc(position) : '') + (note ? (position ? ' · ' : '') + note : '') + '</div>'
				+ '</div>';
		};
		document.getElementById('reviewSteps').innerHTML = card('Box A · Supervisor', hd.certified_a, hd.position_a, '', '', false)
			+ card('Box C · Accounting', hd.certified_c, hd.position_c, state, decided, status === 'pending');
	}

	function renderHistory(hd) {
		var list = hd.history || [];
		document.getElementById('reviewHistory').innerHTML = !list.length
			? '<div class="text-muted small">No history recorded yet.</div>'
			: '<ul class="dv-timeline">' + list.map(function (h) {
				var k = HISTORY[String(h.action_type).toUpperCase()] || [h.action_type, '#64748b'];
				return '<li><span class="dot" style="background:' + k[1] + '"></span>'
					+ '<div class="what">' + esc(k[0]) + '</div>'
					+ '<div class="meta">' + esc(h.user_full_name || h.action_by || '—') + (h.action_by_role ? ' · ' + esc(h.action_by_role) : '') + ' · ' + esc(fmtDateTime(h.date_created)) + '</div>'
					+ (h.remarks ? '<div class="note">' + esc(h.remarks) + '</div>' : '') + '</li>';
			}).join('') + '</ul>';
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
		$.getJSON(mesiteurl + 'mydisbursementapproval', { meaction: 'MAIN-LOAD', recid: recid })
			.done(function (hd) {
				record = hd;
				var status = statusOf(hd);
				var decide = status === 'pending' && !!perms.approve;

				document.getElementById('reviewTitle').textContent = hd.dvno ? 'DV ' + hd.dvno : 'DV #' + hd.recid;
				document.getElementById('reviewSub').innerHTML = esc(hd.payee_name) + ' &nbsp;' + chip(status);

				var banner = document.getElementById('reviewBanner');
				if (status === 'approved') {
					banner.className = 'alert mb-3 alert-success';
					banner.innerHTML = '<b>Approved</b> by ' + esc(hd.certa_approver || '—') + (hd.certa_remarks ? ' — ' + esc(hd.certa_remarks) : '') + '.';
				} else if (status === 'disapproved') {
					banner.className = 'alert mb-3 alert-danger';
					banner.innerHTML = '<b>Disapproved</b> by ' + esc(hd.certa_approver || '—') + (hd.certa_remarks ? ' — ' + esc(hd.certa_remarks) : '')
						+ '. It comes back here once the preparer corrects it.';
				} else {
					banner.className = 'alert d-none mb-3';
					banner.innerHTML = '';
				}

				renderSigners(hd, status);
				renderFields(hd);
				renderTotals(hd);
				renderItems(hd.grids || {});
				renderHistory(hd);

				var remarks = document.getElementById('reviewRemarks');
				remarks.value = '';
				remarks.classList.remove('is-invalid');
				document.getElementById('reviewDecisionTitle').textContent = 'Your decision · Accounting';
				showActions(decide);

				document.getElementById('reviewFooterNote').textContent = status !== 'pending'
					? 'Already decided — view only.'
					: (perms.approve ? 'Check the voucher, then approve or disapprove.' : 'View only — you don\'t have permission to approve.');

				bootstrap.Modal.getOrCreateInstance(document.getElementById('reviewModal')).show();
			})
			.fail(function (xhr) {
				toastr.error((xhr.responseJSON && xhr.responseJSON.error) || 'Could not load this disbursement.', 'Oops!', { closeButton: true });
			});
	};

	//APPROVAL - APPROVE / DISAPPROVE (two-step: choose, then confirm)
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
			document.getElementById('reviewConfirmText').textContent = approve ? 'Approve this DV?' : 'Disapprove this DV?';
			var yes = document.getElementById('reviewConfirmYes');
			yes.className = 'btn btn-sm px-4 ' + (approve ? 'btn-success' : 'btn-danger');
			yes.textContent = approve ? 'Yes, approve' : 'Yes, disapprove';
			document.getElementById('reviewActions').classList.add('d-none');
			document.getElementById('reviewConfirm').classList.remove('d-none');
		}

		remarks.addEventListener('input', function () { remarks.classList.remove('is-invalid'); });
		document.getElementById('reviewApproveBtn').addEventListener('click', function () { ask('approve'); });
		document.getElementById('reviewDisapproveBtn').addEventListener('click', function () { ask('disapprove'); });
		document.getElementById('reviewConfirmCancel').addEventListener('click', function () { showActions(true); });

		document.getElementById('reviewConfirmYes').addEventListener('click', function () {
			if (!record || !decision) { return; }
			var btn = $(this);
			btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Saving...');
			$.ajax({
				type: 'POST',
				url: mesiteurl + 'mydisbursementapproval',
				data: {
					recid: record.recid,
					remarks: remarks.value,
					meaction: decision === 'approve' ? 'MAIN-APPROVE' : 'MAIN-DISAPPROVE'
				},
				global: false,
				cache: false,
				success: function (data) { $('.me-outp-msg').html(data); },
				error: function (xhr, status, error) { toastr.error('[DISBURSEMENT APPROVAL]', 'Error loading page...' + error, { closeButton: true }); },
				complete: function () { setTimeout(function () { btn.prop('disabled', false); }, 1200); }
			});
		});
	};

	//APPROVAL - PRINT DV
	this.__showPdfInModal = function (recid) {
		document.getElementById('approvalPrintFrame').src = mesiteurl + 'mydisbursementapproval?meaction=PRINT-DISBURSEMENT&recid=' + encodeURIComponent(recid);
		bootstrap.Modal.getOrCreateInstance(document.getElementById('approvalPrintModal')).show();
	};

	this.__approval_print = function () {
		document.getElementById('reviewPrintBtn').addEventListener('click', function () {
			if (record) { __mysys_disbursement_approval_ent.__showPdfInModal(record.recid); }
		});
	};
}

// jQuery, DataTables and Bootstrap load in the footer, after this file
document.addEventListener('DOMContentLoaded', function () {
	__mysys_disbursement_approval_ent.__approval_table();
	__mysys_disbursement_approval_ent.__approval_decision();
	__mysys_disbursement_approval_ent.__approval_print();

	if (window.__approval_open) { __mysys_disbursement_approval_ent.__open_review(window.__approval_open); }
});
