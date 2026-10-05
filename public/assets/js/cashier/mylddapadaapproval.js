var __mysys_lddapada_approval_ent = new __mysys_lddapada_approval_ent();
function __mysys_lddapada_approval_ent() {
	const siteEl = document.getElementById('__siteurl');
	const mesiteurl = siteEl.getAttribute('data-mesiteurl');
	const rows = window.__approval_data || [];
	const perms = window.__approval_perms || {};

	let table = null;
	let filter = 'pending';          // approvers start on what is waiting for them
	let record = null;               // LDDAP-ADA open in the review modal
	let decision = null;             // 'approve' | 'disapprove' while confirming

	const STATUS = {
		pending: ['tone-amber', 'Pending'],
		approved: ['tone-green', 'Approved'],
		disapproved: ['tone-red', 'Disapproved']
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

	// Single approval step (same rule as MyLDDAPADA::statusOf)
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
	const YEAR_KEY = 'fods.year.mylddapadaapproval';
	let year = String(new Date().getFullYear());
	try { year = localStorage.getItem(YEAR_KEY) || year; } catch (e) { /* storage blocked */ }

	// An LDDAP-ADA's year is its date, or when it was encoded if that date is blank
	function yearOf(r) {
		var y = String(r.lddapada_date || '').slice(0, 4);
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
			$('[data-card-label="' + key + '"]').text(label + ' · ' + money(amount(l)));
		};
		card('pending', 'pending', 'Waiting for you');
		card('returned', 'disapproved', 'Returned');
		card('approved', 'approved', 'Approved');
		$('[data-card-value="total"]').text(money(amount(list)));
		$('[data-card-label="total"]').text('Total amount · ' + list.length.toLocaleString() + ' LDDAP-ADA' + (list.length === 1 ? '' : 's'));
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

	//APPROVAL - LIST (status pills, stat cards, search)
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
					data: 'lddapadano', className: 'cell-primary',
					render: function (v, type, r) {
						if (type !== 'display') { return (v || '') + ' ' + (r.ckno || ''); }
						return '<div style="min-width:0"><div class="fw-semibold text-dark" style="overflow-wrap:anywhere">' + esc(v || '—') + '</div>'
							+ '<div class="small text-muted">Check ' + esc(r.ckno || '—') + ' · ' + r.dv_count + ' DV' + (+r.dv_count === 1 ? '' : 's') + ' · FC ' + esc(r.fund_cluster_code) + '</div></div>';
					}
				},
				{ data: 'lddapada_date', className: 'd-none d-md-table-cell text-nowrap', render: function (v, type) { return type === 'display' ? fmtDate(v) : (v || ''); } },
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
							+ '<button type="button" class="fods-icon-btn approval-print" data-recid="' + r.recid + '" title="Print"><i class="ti ti-printer"></i></button></div>';
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
		$(document).on('click', '#approvalTableFilter button', function () { applyFilter($(this).data('filter')); });
		$(document).on('click', '.fods-stat[data-table="approvalTable"]', function () { applyFilter($(this).data('filter')); });

		yearPicker(function () { renderCards(); table.draw(); });
		renderCards();
		applyFilter(filter);

		$(document).on('click', '#approvalTable .approval-review', function () { __mysys_lddapada_approval_ent.__open_review($(this).data('recid')); });
		$(document).on('click', '#approvalTable .approval-print', function () { __mysys_lddapada_approval_ent.__showPdfInModal($(this).data('recid')); });
	};

	//APPROVAL - REVIEW MODAL (read-only LDDAP-ADA)
	function field(label, value) {
		return '<div class="rev-field"><div class="lbl">' + label + '</div><div class="val">' + (value ? esc(value) : '<span class="text-muted">—</span>') + '</div></div>';
	}

	function tile(lbl, amt, grand, raw) {
		return '<div class="rev-total' + (grand ? ' grand' : '') + '"><div class="lbl">' + lbl + '</div><div class="amt">' + (raw ? esc(amt) : money(amt)) + '</div></div>';
	}

	function renderReview(hd) {
		var lines = hd.lines || [];
		document.getElementById('reviewFields').innerHTML = field('LDDAP-ADA no.', hd.lddapadano)
			+ field('Check no.', hd.ckno)
			+ field('Date', fmtDate(hd.lddapada_date))
			+ field('Fund cluster', hd.fund_cluster_code)
			+ field('MDS-GSB branch', hd.mds_branch)
			+ field('MDS sub-account no.', hd.mds_accountno)
			+ field('Internal creditor', String(hd.is_ci) === '1' ? 'Yes' : 'No')
			+ field('ADA approver', hd.ada_approver)
			+ field('Prepared by', (hd.added_by || '—') + ' · ' + fmtDate(hd.added_at));

		var sum = function (k) { return lines.reduce(function (t, l) { return t + num(l[k]); }, 0); };
		document.getElementById('reviewTotals').innerHTML = tile('DVs', String(lines.length), false, true)
			+ tile('Gross amount', sum('gross_amount')) + tile('Withholding tax', sum('total_deduction')) + tile('Net payable', sum('net_amount'), true);

		document.getElementById('reviewItems').innerHTML = !lines.length ? '<div class="text-muted small mt-3">This LDDAP-ADA has no DVs.</div>'
			: '<div class="rev-table-wrap"><table class="rev-table"><caption>Disbursement vouchers · ' + lines.length + '</caption>'
				+ '<thead><tr><th>#</th><th>DV no.</th><th>Payee</th><th>Account no.</th><th>ORS / BURS no.</th><th>Allotment class</th><th class="num">Gross</th><th class="num">Tax</th><th class="num">Net</th><th>Remarks</th></tr></thead><tbody>'
				+ lines.map(function (l, i) {
					return '<tr><td class="text-muted">' + (i + 1) + '</td><td class="text-nowrap">' + esc(l.dvno) + '</td><td>' + esc(l.payee_name) + '</td>'
						+ '<td class="text-nowrap">' + esc(l.payee_account_num) + '</td><td class="text-nowrap">' + esc(l.serialno) + '</td><td class="text-nowrap">' + esc(l.uacs_code) + '</td>'
						+ '<td class="num">' + money(l.gross_amount) + '</td><td class="num">' + money(l.total_deduction) + '</td><td class="num fw-semibold">' + money(l.net_amount) + '</td>'
						+ '<td>' + esc(l.remarks) + '</td></tr>';
				}).join('')
				+ '</tbody><tfoot><tr><td colspan="6">Total</td><td class="num">' + money(sum('gross_amount')) + '</td><td class="num">' + money(sum('total_deduction')) + '</td><td class="num">' + money(sum('net_amount')) + '</td><td></td></tr></tfoot></table></div>';
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
		$.getJSON(mesiteurl + 'mylddapadaapproval', { meaction: 'MAIN-LOAD', recid: recid })
			.done(function (hd) {
				record = hd;
				var status = statusOf(hd);
				var decide = status === 'pending' && !!perms.approve;

				document.getElementById('reviewTitle').textContent = hd.lddapadano || 'LDDAP-ADA #' + hd.recid;
				document.getElementById('reviewSub').innerHTML = 'Check ' + esc(hd.ckno || '—') + ' · ' + fmtDate(hd.lddapada_date) + ' &nbsp;' + chip(status);

				var banner = document.getElementById('reviewBanner');
				if (status === 'approved') {
					banner.className = 'alert mb-3 alert-success';
					banner.innerHTML = '<b>Approved</b> by ' + esc(hd.approver || '—') + (hd.remarks ? ' — ' + esc(hd.remarks) : '') + '.';
				} else if (status === 'disapproved') {
					banner.className = 'alert mb-3 alert-danger';
					banner.innerHTML = '<b>Disapproved</b> by ' + esc(hd.approver || '—') + (hd.remarks ? ' — ' + esc(hd.remarks) : '')
						+ '. It comes back here once the preparer corrects it.';
				} else {
					banner.className = 'alert d-none mb-3';
					banner.innerHTML = '';
				}

				renderReview(hd);

				var remarks = document.getElementById('reviewRemarks');
				remarks.value = '';
				remarks.classList.remove('is-invalid');
				showActions(decide);
				document.getElementById('reviewFooterNote').textContent = status !== 'pending'
					? 'Already decided — view only.'
					: (perms.approve ? 'Check the details, then approve or disapprove.' : 'View only — you don\'t have permission to approve.');

				bootstrap.Modal.getOrCreateInstance(document.getElementById('reviewModal')).show();
			})
			.fail(function (xhr) {
				toastr.error((xhr.responseJSON && xhr.responseJSON.error) || 'Could not load this LDDAP-ADA.', 'Oops!', { closeButton: true });
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
			document.getElementById('reviewConfirmText').textContent = approve ? 'Approve this LDDAP-ADA?' : 'Disapprove this LDDAP-ADA?';
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
				url: mesiteurl + 'mylddapadaapproval',
				data: {
					recid: record.recid,
					remarks: remarks.value,
					meaction: decision === 'approve' ? 'MAIN-APPROVE' : 'MAIN-DISAPPROVE'
				},
				global: false,
				cache: false,
				success: function (data) { $('.me-outp-msg').html(data); },
				error: function (xhr, status, error) { toastr.error('[LDDAP-ADA APPROVAL]', 'Error loading page...' + error, { closeButton: true }); },
				complete: function () { setTimeout(function () { btn.prop('disabled', false); }, 1200); }
			});
		});
	};

	//APPROVAL - PRINT
	this.__showPdfInModal = function (recid) {
		document.getElementById('approvalPrintFrame').src = mesiteurl + 'mylddapadaapproval?meaction=PRINT-LDDAPADA&recid=' + encodeURIComponent(recid);
		bootstrap.Modal.getOrCreateInstance(document.getElementById('approvalPrintModal')).show();
	};

	this.__approval_print = function () {
		document.getElementById('reviewPrintBtn').addEventListener('click', function () {
			if (record) { __mysys_lddapada_approval_ent.__showPdfInModal(record.recid); }
		});
	};
}

// jQuery, DataTables and Bootstrap load in the footer, after this file
document.addEventListener('DOMContentLoaded', function () {
	__mysys_lddapada_approval_ent.__approval_table();
	__mysys_lddapada_approval_ent.__approval_decision();
	__mysys_lddapada_approval_ent.__approval_print();

	if (window.__approval_open) { __mysys_lddapada_approval_ent.__open_review(window.__approval_open); }
});
