var __mysys_budget_approval_ent = new __mysys_budget_approval_ent();
function __mysys_budget_approval_ent() {
	const siteEl = document.getElementById('__siteurl');
	const mesiteurl = siteEl.getAttribute('data-mesiteurl');
	const uploadsurl = siteEl.getAttribute('data-uploads');
	const rows = window.__approval_data || [];
	const perms = window.__approval_perms || {};

	let table = null;
	let filter = 'pending';          // approvers start on what is waiting for them
	let record = null;          // allotment open in the review modal
	let decision = null;        // 'approve' | 'disapprove' while confirming

	const STATUS = {
		pending: ['tone-amber', 'Awaiting approval'],
		approved: ['tone-green', 'Approved'],
		disapproved: ['tone-red', 'Disapproved']
	};

	// Line-item groups shown in the review, in LIB order
	const CLASSES = [
		{ title: 'I. Personnel Services', grids: [['ps_direct', 'Direct cost', 'item'], ['ps_indirect', 'Indirect cost', 'item']] },
		{ title: 'II. Maintenance and Other Operating Expenses', grids: [['mooe_direct', 'Direct cost', 'item'], ['mooe_indirect', 'Indirect cost', 'item']] },
		{ title: 'III. Capital Outlay', grids: [['co_direct', 'Direct cost', 'co'], ['co_indirect', 'Indirect cost', 'co']] },
		{ title: 'IV. Admin Cost', grids: [['ac', 'Admin cost', 'ac']] }
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

	function statusOf(hd) {
		if (String(hd.is_disapproved) === '1') { return 'disapproved'; }
		if (String(hd.is_approved) === '1') { return 'approved'; }
		return 'pending';
	}

	function chip(status) {
		var s = STATUS[status];
		return '<span class="fods-chip ' + s[0] + '"><span class="fods-dot"></span>' + s[1] + '</span>';
	}

	//APPROVAL - YEAR (the cards and the list follow the year picked in the page head)
	const YEAR_KEY = 'fods.year.mybudgetapproval';
	let year = String(new Date().getFullYear());
	try { year = localStorage.getItem(YEAR_KEY) || year; } catch (e) { /* storage blocked */ }

	// An allotment's year is when it was encoded
	function yearOf(r) {
		var y = String(r.added_at || '').slice(0, 4);
		return +y >= 2000 ? y : String(r.added_at || '').slice(0, 4);
	}

	function inYear(r) { return year === 'all' || yearOf(r) === year; }

	// Statuses behind each pill / card (any other key is a single status)
	const GROUPS = {};
	function inGroup(r, key) { return key === 'all' || (GROUPS[key] || [key]).indexOf(r.status) !== -1; }

	//APPROVAL - STAT CARDS: Pending, Returned, Approved, Total amount
	function renderCards() {
		var $ = window.jQuery;
		var list = rows.filter(inYear);
		var amount = function (l) { return l.reduce(function (t, r) { return t + num(r.approved_budget); }, 0); };
		var count = function (status) { return list.filter(function (r) { return r.status === status; }).length; };
		var card = function (key, group, label) {
			var l = list.filter(function (r) { return inGroup(r, group); });
			$('[data-card-value="' + key + '"]').text(l.length.toLocaleString());
			$('[data-card-label="' + key + '"]').text(label() + ' · ' + money(amount(l)));
		};
		card('pending', 'pending', function () { return perms.approve ? 'Waiting for you' : 'Pending'; });
		card('returned', 'disapproved', function () { return 'Returned'; });
		card('approved', 'approved', function () { return 'Approved'; });
		$('[data-card-value="total"]').text(money(amount(list)));
		$('[data-card-label="total"]').text('Total amount · ' + list.length.toLocaleString() + ' allotment' + (list.length === 1 ? '' : 's') + '');
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
					data: 'project_title', className: 'cell-primary',
					render: function (v, type, r) {
						if (type !== 'display') { return (v || '') + ' ' + (r.trxno || '') + ' ' + (r.responsibility_code || '') + ' ' + (r.division_name || ''); }
						return '<div style="min-width:0"><div class="fw-semibold text-dark" style="overflow-wrap:anywhere">' + esc(v) + '</div>'
							+ '<div class="small text-muted">' + esc(r.trxno) + (r.division_name ? ' · ' + esc(r.division_name) : '') + '</div></div>';
					}
				},
				{
					data: 'fund_cluster_code', className: 'd-none d-md-table-cell',
					render: function (v, type) { return type === 'display' ? '<span class="fods-chip ' + (v === '07' ? 'tone-amber' : 'tone-indigo') + '">FC ' + esc(v) + '</span>' : v; }
				},
				{
					data: 'approved_budget', className: 'text-end cell-left',
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
					data: 'approver', className: 'd-none d-xl-table-cell text-muted small',
					render: function (v, type, r) { return type === 'display' ? (r.status === 'pending' ? '—' : esc(v || '—')) : (v || ''); }
				},
				{
					data: null, orderable: false, searchable: false, className: 'cell-actions text-end',
					render: function (v, type, r) {
						var review = r.status === 'pending'
							? '<button type="button" class="btn btn-sm btn-fods approval-review" data-recid="' + r.recid + '"><i class="ti ti-file-search me-1"></i>Review</button>'
							: '<button type="button" class="fods-icon-btn is-teal approval-review" data-recid="' + r.recid + '" title="View"><i class="ti ti-eye"></i></button>';
						return '<div class="d-flex justify-content-end align-items-center gap-1">' + review
							+ '<button type="button" class="fods-icon-btn approval-print" data-recid="' + r.recid + '" title="Print LIB"><i class="ti ti-printer"></i></button></div>';
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

		// view-only users see the pending queue, not their own
		if (!perms.approve) { $('#approvalTableFilter [data-filter="pending"]').text('Pending'); }
		yearPicker(function () { renderCards(); table.draw(); });
		renderCards();
		table.draw();

		$(document).on('click', '#approvalTable .approval-review', function () { __mysys_budget_approval_ent.__open_review($(this).data('recid')); });
		$(document).on('click', '#approvalTable .approval-print', function () { __mysys_budget_approval_ent.__showPdfInModal($(this).data('recid')); });
	};

	//APPROVAL - REVIEW MODAL (read-only allotment)
	function field(label, value, wide) {
		return '<div class="rev-field' + (wide ? ' wide' : '') + '"><div class="lbl">' + label + '</div><div class="val">' + (value ? esc(value) : '<span class="text-muted">—</span>') + '</div></div>';
	}

	function renderFields(hd) {
		var duration = fmtDate(hd.duration_from) + ' – ' + fmtDate(hd.duration_to);
		var html = field('Program title', hd.program_title, true)
			+ field('Responsibility code', hd.responsibility_code)
			+ field('Fund cluster', hd.fund_cluster_code)
			+ field('Division', hd.division_name)
			+ field('LDDAP-ADA reference no.', hd.lddap_refno)
			+ field('Project leader', hd.project_leader)
			+ field('Program leader', hd.program_leader)
			+ field('Duration', duration)
			+ (String(hd.with_extension) === '1' ? field('Extended', fmtDate(hd.extended_from) + ' – ' + fmtDate(hd.extended_to)) : '')
			+ field('Implementing agency', hd.implementing_agency)
			+ field('Monitoring agency', hd.monitoring_agency)
			+ field('Funding agency', hd.funding_agency)
			+ field('Submitted by', (hd.added_by || '—') + ' · ' + fmtDate(hd.added_at))
			+ field('Collaborating agencies', hd.collaborating_agencies, true);
		document.getElementById('reviewFields').innerHTML = html;
	}

	function sumApproved(list) { return (list || []).reduce(function (s, r) { return s + num(r.approved_budget); }, 0); }

	function renderTotals(g) {
		var ps = sumApproved(g.ps_direct) + sumApproved(g.ps_indirect);
		var mooe = sumApproved(g.mooe_direct) + sumApproved(g.mooe_indirect);
		var co = sumApproved(g.co_direct) + sumApproved(g.co_indirect);
		var ac = sumApproved(g.ac);
		var tile = function (lbl, amt, grand) {
			return '<div class="rev-total' + (grand ? ' grand' : '') + '"><div class="lbl">' + lbl + '</div><div class="amt">' + money(amt) + '</div></div>';
		};
		document.getElementById('reviewTotals').innerHTML = tile('Personnel Services', ps) + tile('MOOE', mooe) + tile('Capital Outlay', co)
			+ tile('Admin cost', ac) + tile('Total approved budget', ps + mooe + co, true);
	}

	function renderItems(g) {
		// realignment columns only when this allotment actually has realignments
		var all = [].concat(g.ps_direct, g.ps_indirect, g.mooe_direct, g.mooe_indirect, g.co_direct, g.co_indirect);
		var realigned = all.some(function (r) { return r && (num(r.r1_approved_budget) || num(r.r2_approved_budget) || num(r.r3_approved_budget)); });

		var html = '';
		CLASSES.forEach(function (cls) {
			var tables = '';
			var classTotal = 0;
			cls.grids.forEach(function (spec) {
				var list = g[spec[0]] || [];
				if (!list.length) { return; }
				var kind = spec[2];
				var withRealign = realigned && kind !== 'ac';
				var head = (kind === 'item' ? '<th>Expense item</th>' : '') + '<th>Particulars</th><th>UACS</th><th class="num">Approved</th>'
					+ (withRealign ? '<th class="num">1st realign</th><th class="num">2nd realign</th><th class="num">3rd realign</th><th class="num">Proposed</th>' : '');
				var total = 0;
				var body = list.map(function (r) {
					total += num(r.approved_budget);
					return '<tr>' + (kind === 'item' ? '<td>' + esc(r.expense_item || '') + '</td>' : '')
						+ '<td>' + esc(r.particulars || '') + '</td><td class="text-nowrap">' + esc(r.code || '') + '</td>'
						+ '<td class="num">' + money(r.approved_budget) + '</td>'
						+ (withRealign ? '<td class="num">' + money(r.r1_approved_budget) + '</td><td class="num">' + money(r.r2_approved_budget) + '</td><td class="num">' + money(r.r3_approved_budget) + '</td><td class="num fw-semibold">' + money(num(r.approved_budget) + num(r.r1_approved_budget) + num(r.r2_approved_budget) + num(r.r3_approved_budget)) + '</td>' : '')
						+ '</tr>';
				}).join('');
				classTotal += total;
				tables += '<div class="rev-table-wrap"><table class="rev-table"><caption>' + spec[1] + ' · ' + list.length + ' line' + (list.length === 1 ? '' : 's') + '</caption>'
					+ '<thead><tr>' + head + '</tr></thead><tbody>' + body + '</tbody>'
					+ '<tfoot><tr><td colspan="' + (kind === 'item' ? 3 : 2) + '">Subtotal</td><td class="num">' + money(total) + '</td>' + (withRealign ? '<td colspan="4"></td>' : '') + '</tr></tfoot>'
					+ '</table></div>';
			});
			if (tables) {
				html += '<div class="rev-class"><div class="rev-class-title"><span>' + cls.title + '</span><span>' + money(classTotal) + '</span></div>' + tables + '</div>';
			}
		});
		document.getElementById('reviewItems').innerHTML = html || '<div class="text-muted small mt-3">This allotment has no line items.</div>';
	}

	function renderFiles(hd) {
		var files = hd.attachments || [];
		document.getElementById('reviewFiles').innerHTML = files.length
			? '<div class="list-group list-group-flush">' + files.map(function (f, i) {
				return '<a class="list-group-item list-group-item-action d-flex align-items-center gap-2" target="_blank" href="' + uploadsurl + encodeURIComponent(f.file_name) + '">'
					+ '<i class="ti ti-file"></i><span class="text-truncate">' + (i + 1) + '. ' + esc(f.file_name) + '</span><i class="ti ti-external-link ms-auto text-muted"></i></a>';
			}).join('') + '</div>'
			: '<div class="text-muted small">No files attached.</div>';
	}

	function showActions(pending) {
		pending = pending && !!perms.approve;
		decision = null;
		document.getElementById('reviewConfirm').classList.add('d-none');
		document.getElementById('reviewActions').classList.remove('d-none');
		document.getElementById('reviewApproveBtn').classList.toggle('d-none', !pending);
		document.getElementById('reviewDisapproveBtn').classList.toggle('d-none', !pending);
		document.getElementById('reviewDecisionCard').classList.toggle('d-none', !pending);
	}

	this.__open_review = function (recid) {
		var $ = window.jQuery;
		$.getJSON(mesiteurl + 'mybudgetapproval', { meaction: 'MAIN-LOAD', recid: recid })
			.done(function (hd) {
				record = hd;
				var status = statusOf(hd);
				var grids = hd.grids || {};

				document.getElementById('reviewTitle').textContent = hd.project_title;
				document.getElementById('reviewSub').innerHTML = esc(hd.trxno) + ' &nbsp;' + chip(status);

				var banner = document.getElementById('reviewBanner');
				banner.className = 'alert mb-3' + (status === 'pending' ? ' d-none' : status === 'approved' ? ' alert-success' : ' alert-danger');
				banner.innerHTML = status === 'pending' ? '' :
					'<b>' + (status === 'approved' ? 'Approved' : 'Disapproved') + '</b>' + (hd.approver ? ' by ' + esc(hd.approver) : '') + (hd.remarks ? ' — ' + esc(hd.remarks) : '');

				renderFields(hd);
				renderTotals(grids);
				renderItems(grids);
				renderFiles(hd);

				var remarks = document.getElementById('reviewRemarks');
				remarks.value = '';
				remarks.classList.remove('is-invalid');
				showActions(status === 'pending');
				document.getElementById('reviewFooterNote').textContent = status !== 'pending'
					? 'Already decided — view only.'
					: (perms.approve ? 'Check the line items, then approve or disapprove.' : 'View only — you don\'t have permission to approve.');

				bootstrap.Modal.getOrCreateInstance(document.getElementById('reviewModal')).show();
			})
			.fail(function (xhr) {
				toastr.error((xhr.responseJSON && xhr.responseJSON.error) || 'Could not load this allotment.', 'Oops!', { closeButton: true });
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
				toastr.warning('Add remarks so the budget officer knows what to change.', 'Remarks needed', { closeButton: true });
				return;
			}
			decision = kind;
			var approve = kind === 'approve';
			document.getElementById('reviewConfirmText').textContent = approve ? 'Approve this allotment?' : 'Disapprove this allotment?';
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
				url: mesiteurl + 'mybudgetapproval',
				data: {
					recid: record.recid,
					remarks: remarks.value,
					return_to: 'approval',
					meaction: decision === 'approve' ? 'MAIN-APPROVE' : 'MAIN-DISAPPROVE'
				},
				global: false,
				cache: false,
				success: function (data) { $('.me-outp-msg').html(data); },
				error: function (xhr, status, error) { toastr.error('[BUDGET APPROVAL]', 'Error loading page...' + error, { closeButton: true }); },
				complete: function () { setTimeout(function () { btn.prop('disabled', false); }, 1200); }
			});
		});
	};

	//APPROVAL - PRINT LIB
	this.__showPdfInModal = function (recid) {
		document.getElementById('approvalPrintFrame').src = mesiteurl + 'mybudgetapproval?meaction=PRINT-LIB&recid=' + encodeURIComponent(recid);
		bootstrap.Modal.getOrCreateInstance(document.getElementById('approvalPrintModal')).show();
	};

	this.__approval_print = function () {
		document.getElementById('reviewPrintBtn').addEventListener('click', function () {
			if (record) { __mysys_budget_approval_ent.__showPdfInModal(record.recid); }
		});
	};
}

// jQuery, DataTables and Bootstrap load in the footer, after this file
document.addEventListener('DOMContentLoaded', function () {
	__mysys_budget_approval_ent.__approval_table();
	__mysys_budget_approval_ent.__approval_decision();
	__mysys_budget_approval_ent.__approval_print();

	if (window.__approval_open) { __mysys_budget_approval_ent.__open_review(window.__approval_open); }
});
