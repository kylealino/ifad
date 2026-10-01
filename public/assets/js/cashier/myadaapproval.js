var __mysys_ada_approval_ent = new __mysys_ada_approval_ent();
function __mysys_ada_approval_ent() {
	const siteEl = document.getElementById('__siteurl');
	const mesiteurl = siteEl.getAttribute('data-mesiteurl');
	const rows = window.__approval_data || [];
	const perms = window.__approval_perms || {};

	let table = null;
	let filter = 'pending';          // approvers start on what is waiting for them
	let record = null;               // ADA open in the review modal
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

	function moneyShort(n) {
		n = Number(n || 0);
		if (n >= 1e9) { return '₱' + (n / 1e9).toFixed(2) + 'B'; }
		if (n >= 1e6) { return '₱' + (n / 1e6).toFixed(1) + 'M'; }
		if (n >= 1e3) { return '₱' + (n / 1e3).toFixed(1) + 'K'; }
		return money(n);
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
	const YEAR_KEY = 'fods.year.myadaapproval';
	let year = String(new Date().getFullYear());
	try { year = localStorage.getItem(YEAR_KEY) || year; } catch (e) { /* storage blocked */ }

	// An ADA's year is its ADA date, or when it was encoded if that date is blank
	function yearOf(r) {
		var y = String(r.ada_date || '').slice(0, 4);
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
		$('[data-card-label="total"]').text('Total amount · ' + list.length.toLocaleString() + ' ADA' + (list.length === 1 ? '' : 's'));
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
					data: 'adano', className: 'cell-primary',
					render: function (v, type, r) {
						if (type !== 'display') { return (v || '') + ' ' + (r.dvno || '') + ' ' + (r.payee_name || ''); }
						return '<div style="min-width:0"><div class="fw-semibold text-dark" style="overflow-wrap:anywhere">' + (v ? 'ADA ' + esc(v) : '<span class="text-muted">No ADA no. yet</span>') + '</div>'
							+ '<div class="small text-muted">DV ' + esc(r.dvno || '—') + ' · ' + fmtDate(r.ada_date) + '</div></div>';
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

		$(document).on('click', '#approvalTable .approval-review', function () { __mysys_ada_approval_ent.__open_review($(this).data('recid')); });
		$(document).on('click', '#approvalTable .approval-print', function () { __mysys_ada_approval_ent.__showPdfInModal($(this).data('recid')); });
	};

	//APPROVAL - REVIEW MODAL (read-only ADA)
	function field(label, value) {
		return '<div class="rev-field"><div class="lbl">' + label + '</div><div class="val">' + (value ? esc(value) : '<span class="text-muted">—</span>') + '</div></div>';
	}

	function tile(lbl, amt, grand, raw) {
		return '<div class="rev-total' + (grand ? ' grand' : '') + '"><div class="lbl">' + lbl + '</div><div class="amt">' + (raw ? esc(amt) : money(amt)) + '</div></div>';
	}

	function renderReview(hd) {
		document.getElementById('reviewFields').innerHTML = field('ADA no.', hd.adano)
			+ field('DV no.', hd.dvno)
			+ field('ADA date', fmtDate(hd.ada_date))
			+ field('Payee', hd.payee_name)
			+ field('Account no.', hd.payee_accountno)
			+ field('Signatory 1', (hd.certified_a || '') + (hd.position_a ? ' · ' + hd.position_a : ''))
			+ field('Signatory 2', (hd.certified_b || '') + (hd.position_b ? ' · ' + hd.position_b : ''))
			+ field('Prepared by', (hd.added_by || '—') + ' · ' + fmtDate(hd.added_at));
		document.getElementById('reviewTotals').innerHTML = tile('Net amount', hd.net_amount, true);
		document.getElementById('reviewItems').innerHTML = '<div class="fods-card mt-3"><div class="fods-card-body pt-3"><div class="fods-section mt-0">Particulars</div>'
			+ '<div style="white-space:pre-wrap; overflow-wrap:anywhere">' + (hd.particulars ? esc(hd.particulars) : '<span class="text-muted">—</span>') + '</div></div></div>';
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
		$.getJSON(mesiteurl + 'myadaapproval', { meaction: 'MAIN-LOAD', recid: recid })
			.done(function (hd) {
				record = hd;
				var status = statusOf(hd);
				var decide = status === 'pending' && !!perms.approve;

				document.getElementById('reviewTitle').textContent = hd.adano ? 'ADA ' + hd.adano : 'ADA #' + hd.recid;
				document.getElementById('reviewSub').innerHTML = esc(hd.payee_name) + ' &nbsp;' + chip(status);

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
				toastr.error((xhr.responseJSON && xhr.responseJSON.error) || 'Could not load this ADA.', 'Oops!', { closeButton: true });
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
			document.getElementById('reviewConfirmText').textContent = approve ? 'Approve this ADA?' : 'Disapprove this ADA?';
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
				url: mesiteurl + 'myadaapproval',
				data: {
					recid: record.recid,
					remarks: remarks.value,
					meaction: decision === 'approve' ? 'MAIN-APPROVE' : 'MAIN-DISAPPROVE'
				},
				global: false,
				cache: false,
				success: function (data) { $('.me-outp-msg').html(data); },
				error: function (xhr, status, error) { toastr.error('[ADA APPROVAL]', 'Error loading page...' + error, { closeButton: true }); },
				complete: function () { setTimeout(function () { btn.prop('disabled', false); }, 1200); }
			});
		});
	};

	//APPROVAL - PRINT
	this.__showPdfInModal = function (recid) {
		document.getElementById('approvalPrintFrame').src = mesiteurl + 'myadaapproval?meaction=PRINT-ADA&recid=' + encodeURIComponent(recid);
		bootstrap.Modal.getOrCreateInstance(document.getElementById('approvalPrintModal')).show();
	};

	this.__approval_print = function () {
		document.getElementById('reviewPrintBtn').addEventListener('click', function () {
			if (record) { __mysys_ada_approval_ent.__showPdfInModal(record.recid); }
		});
	};
}

// jQuery, DataTables and Bootstrap load in the footer, after this file
document.addEventListener('DOMContentLoaded', function () {
	__mysys_ada_approval_ent.__approval_table();
	__mysys_ada_approval_ent.__approval_decision();
	__mysys_ada_approval_ent.__approval_print();

	if (window.__approval_open) { __mysys_ada_approval_ent.__open_review(window.__approval_open); }
});
