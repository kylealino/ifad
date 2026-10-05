var __mysys_pr_approval_ent = new __mysys_pr_approval_ent();
function __mysys_pr_approval_ent() {
	const siteEl = document.getElementById('__siteurl');
	const mesiteurl = siteEl.getAttribute('data-mesiteurl');
	const rows = window.__approval_data || [];
	const perms = window.__approval_perms || {};

	let table = null;
	let filter = 'mine';        // approvers start on what is waiting for them
	let record = null;          // PR open in the review modal
	let decision = null;        // 'approve' | 'disapprove' while confirming

	const STATUS = {
		pending_a: ['tone-amber', 'Box A · requesting office'],
		pending_b: ['tone-indigo', 'Box B · approval'],
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

	// Box A (requesting office) approves first, then Box B (same rule as MyPR::approvalOf)
	const BOXES = ['a', 'b'];
	const BOX_ROLE = { a: 'Requesting office', b: 'Approval' };

	function statusOf(hd) {
		if (BOXES.some(function (b) { return String(hd['is_disapproved_cert' + b]) === '1'; })) { return 'disapproved'; }
		if (String(hd.is_approved_certb) === '1') { return 'approved'; }
		if (String(hd.is_approved_certa) === '1') { return 'pending_b'; }
		return 'pending_a';
	}

	// Box a status is waiting at ('' once decided)
	function boxOf(status) { return status.indexOf('pending_') === 0 ? status.slice(-1) : ''; }

	// Signatory named on a box (same rule as MyPRModel::signatory)
	function signatory(hd, box) { return box === 'a' ? hd.requested_by : hd.approved_by; }
	function signatoryPosition(hd, box) { return box === 'a' ? hd.requested_position : hd.approved_position; }

	function chip(status) {
		var s = STATUS[status] || STATUS.pending_a;
		return '<span class="fods-chip ' + s[0] + '"><span class="fods-dot"></span>' + s[1] + '</span>';
	}

	//APPROVAL - YEAR (the cards and the list follow the year picked in the page head)
	const YEAR_KEY = 'fods.year.myprapproval';
	let year = String(new Date().getFullYear());
	try { year = localStorage.getItem(YEAR_KEY) || year; } catch (e) { /* storage blocked */ }

	// A PR's year is its PR date, or when it was encoded if that date is blank
	function yearOf(r) {
		var y = String(r.pr_date || '').slice(0, 4);
		return +y >= 2000 ? y : String(r.added_at || '').slice(0, 4);
	}

	function inYear(r) { return year === 'all' || yearOf(r) === year; }

	// "mine" is the steps this user can decide (their own box, or any box for
	// an administrator); view-only users see everything still pending instead
	function inGroup(r, key) {
		if (key === 'all') { return true; }
		if (key === 'mine') { return perms.approve ? !!r.mine : boxOf(r.status) !== ''; }
		return r.status === key;
	}

	//APPROVAL - STAT CARDS: Waiting for you, Returned, Approved, Estimated cost
	function renderCards() {
		var $ = window.jQuery;
		var list = rows.filter(inYear);
		var amount = function (l) { return l.reduce(function (t, r) { return t + num(r.estimated_cost); }, 0); };
		var card = function (key, group, label) {
			var l = list.filter(function (r) { return inGroup(r, group); });
			$('[data-card-value="' + key + '"]').text(l.length.toLocaleString());
			$('[data-card-label="' + key + '"]').text(label + ' · ' + money(amount(l)));
		};
		card('pending', 'mine', perms.approve ? 'Waiting for you' : 'Pending');
		if (!perms.approve) { $('#approvalTableFilter [data-filter="mine"]').text('Pending'); }
		card('returned', 'disapproved', 'Returned');
		card('approved', 'approved', 'Approved');
		$('[data-card-value="total"]').text(money(amount(list)));
		$('[data-card-label="total"]').text('Estimated cost · ' + list.length.toLocaleString() + ' PR' + (list.length === 1 ? '' : 's'));
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
					data: 'prno', className: 'cell-primary',
					render: function (v, type, r) {
						if (type !== 'display') { return (v || '') + ' ' + (r.purpose || '') + ' ' + (r.charge_to || '') + ' ' + (r.end_user || '') + ' ' + (r.office || ''); }
						return '<div style="min-width:0"><div class="fw-semibold text-dark">' + (v ? 'PR ' + esc(v) : '<span class="text-muted">PR #' + esc(r.recid) + ' · no PR no. yet</span>') + '</div>'
							+ '<div class="small text-muted" style="overflow-wrap:anywhere">' + esc(r.purpose) + '</div>'
							+ '<div class="small text-muted" style="overflow-wrap:anywhere">' + esc(r.charge_to) + '</div>'
							+ '<div class="small text-muted d-md-none">' + esc(r.end_user) + '</div></div>';
					}
				},
				{
					data: 'end_user', className: 'd-none d-md-table-cell',
					render: function (v, type, r) {
						if (type !== 'display') { return v || ''; }
						return '<span style="overflow-wrap:anywhere">' + esc(v || '—') + '</span>' + (r.office ? '<div class="small text-muted">' + esc(r.office) + '</div>' : '');
					}
				},
				{
					data: 'estimated_cost', className: 'text-end cell-left',
					render: function (v, type) { return type === 'display' ? '<span class="text-nowrap fw-semibold">' + money(v) + '</span>' : num(v); }
				},
				{
					data: 'status', className: 'cell-right',
					render: function (v, type, r) {
						if (type !== 'display') { return (STATUS[v] || STATUS.pending_a)[1] + ' ' + (r.waiting_on || ''); }
						// while pending, name whose turn it is
						return chip(v) + (boxOf(v) ? '<div class="small text-muted mt-1" style="overflow-wrap:anywhere">' + (r.waiting_on ? esc(r.waiting_on) : 'No signatory named') + '</div>' : '');
					}
				},
				{
					data: 'added_at', className: 'd-none d-lg-table-cell text-muted small text-nowrap',
					render: function (v, type, r) { return type === 'display' ? fmtDate(v) + (r.added_by ? '<br>' + esc(r.added_by) : '') : (v || ''); }
				},
				{
					data: 'decided_by', className: 'd-none d-xl-table-cell text-muted small',
					render: function (v, type) { return type === 'display' ? esc(v || '—') : (v || ''); }
				},
				{
					data: null, orderable: false, searchable: false, className: 'cell-actions text-end',
					render: function (v, type, r) {
						var review = r.mine
							? '<button type="button" class="btn btn-sm btn-fods approval-review" data-recid="' + r.recid + '"><i class="ti ti-file-search me-1"></i>Review</button>'
							: '<button type="button" class="fods-icon-btn is-teal approval-review" data-recid="' + r.recid + '" title="View"><i class="ti ti-eye"></i></button>';
						return '<div class="d-flex justify-content-end align-items-center gap-1">' + review
							+ '<button type="button" class="fods-icon-btn approval-print" data-recid="' + r.recid + '" title="Print PR"><i class="ti ti-printer"></i></button></div>';
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

		$(document).on('click', '#approvalTable .approval-review', function () { __mysys_pr_approval_ent.__open_review($(this).data('recid')); });
		$(document).on('click', '#approvalTable .approval-print', function () { __mysys_pr_approval_ent.__showPdfInModal($(this).data('recid')); });
	};

	//APPROVAL - REVIEW MODAL (read-only PR)
	function field(label, value, wide) {
		return '<div class="rev-field' + (wide ? ' wide' : '') + '"><div class="lbl">' + label + '</div><div class="val">' + (value ? esc(value) : '<span class="text-muted">—</span>') + '</div></div>';
	}

	function renderFields(hd) {
		document.getElementById('reviewFields').innerHTML = field('Purpose', hd.purpose, true)
			+ field('PR no.', hd.prno || 'Not assigned yet')
			+ field('PR date', fmtDate(hd.pr_date))
			+ field('Entity name', hd.entity_name)
			+ field('Office / section', hd.office)
			+ field('End-user', hd.end_user)
			+ field('Fund cluster', hd.fund_cluster)
			+ field('Responsibility code', hd.responsibility_code)
			+ field('PPMP', (hd.ppmps || []).join(', '))
			+ field('Charge to', hd.charge_to, true)
			+ field('Prepared by', (hd.added_by || '—') + ' · ' + fmtDate(hd.added_at));
	}

	function renderTotals(hd) {
		var tile = function (lbl, value, grand) {
			return '<div class="rev-total' + (grand ? ' grand' : '') + '"><div class="lbl">' + lbl + '</div><div class="amt">' + value + '</div></div>';
		};
		document.getElementById('reviewTotals').innerHTML = tile('Items', String((hd.items || []).length))
			+ tile('PPMPs', String((hd.ppmps || []).length))
			+ tile('RFQs', String((hd.rfqs || []).length))
			+ tile('Estimated cost', money(hd.estimated_cost), true);
	}

	function renderItems(hd) {
		var list = hd.items || [];
		document.getElementById('reviewItems').innerHTML = !list.length
			? '<div class="text-muted small mt-3">This PR has no items.</div>'
			: '<div class="rev-table-wrap"><table class="rev-table"><caption>Items · ' + list.length + ' line' + (list.length === 1 ? '' : 's') + '</caption>'
				+ '<thead><tr><th>Description</th><th>Unit</th><th class="num">Quantity</th><th class="num">Unit cost</th><th class="num">Total cost</th></tr></thead>'
				+ '<tbody>' + list.map(function (r) {
					return '<tr><td style="overflow-wrap:anywhere">' + esc(r.item_desc) + '</td><td class="text-nowrap">' + esc(r.unit) + '</td>'
						+ '<td class="num">' + num(r.quantity) + '</td><td class="num">' + money(r.unit_cost) + '</td><td class="num">' + money(r.total_cost) + '</td></tr>';
				}).join('') + '</tbody>'
				+ '<tfoot><tr><td colspan="4">Total</td><td class="num">' + money(hd.estimated_cost) + '</td></tr></tfoot>'
				+ '</table></div>';
	}

	// The PR's two signatories, each with where its step stands
	function renderSigners(hd, status) {
		var current = boxOf(status);
		document.getElementById('reviewSteps').innerHTML = BOXES.map(function (box, i) {
			var approved = String(hd['is_approved_cert' + box]) === '1';
			var disapproved = String(hd['is_disapproved_cert' + box]) === '1';
			var by = hd['cert' + box + '_approver'], remarks = hd['cert' + box + '_remarks'];
			var position = signatoryPosition(hd, box);
			var state = approved ? '<span class="fods-chip tone-green"><i class="ti ti-check"></i>Approved</span>'
				: disapproved ? '<span class="fods-chip tone-red"><i class="ti ti-x"></i>Disapproved</span>'
				: box === current ? '<span class="fods-chip tone-amber"><span class="fods-dot"></span>Waiting</span>'
				: current && i > BOXES.indexOf(current) ? '<span class="fods-chip tone-slate">After Box ' + BOXES[i - 1].toUpperCase() + '</span>'
				: '';
			var note = approved || disapproved ? 'By ' + esc(by || '—') + (remarks ? ' — ' + esc(remarks) : '') : '';
			return '<div class="rev-step' + (box === current ? ' is-current' : '') + '">'
				+ '<div class="rev-step-head"><span class="rev-step-box">Box ' + box.toUpperCase() + ' · ' + BOX_ROLE[box] + '</span>' + state + '</div>'
				+ '<div class="rev-step-name">' + esc(signatory(hd, box) || '—') + '</div>'
				+ '<div class="rev-step-note">' + (position ? esc(position) : '') + (note ? (position ? ' · ' : '') + note : '') + '</div>'
				+ '</div>';
		}).join('');
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
		$.getJSON(mesiteurl + 'myprapproval', { meaction: 'MAIN-LOAD', recid: recid })
			.done(function (hd) {
				record = hd;
				var status = statusOf(hd);
				var box = boxOf(status);
				var decide = box !== '' && !!hd.can_decide;

				document.getElementById('reviewTitle').textContent = hd.prno ? 'PR ' + hd.prno : 'Purchase request #' + hd.recid;
				document.getElementById('reviewSub').innerHTML = esc(hd.end_user || hd.office || '') + ' &nbsp;' + chip(status);

				var banner = document.getElementById('reviewBanner');
				if (status === 'approved') {
					banner.className = 'alert mb-3 alert-success';
					banner.innerHTML = '<b>Approved</b> — ' + BOXES.map(function (b) { return 'Box ' + b.toUpperCase() + ' by ' + esc(hd['cert' + b + '_approver'] || '—'); }).join(', ') + '.';
				} else if (status === 'disapproved') {
					var at = BOXES.filter(function (b) { return String(hd['is_disapproved_cert' + b]) === '1'; })[0];
					banner.className = 'alert mb-3 alert-danger';
					banner.innerHTML = '<b>Disapproved at Box ' + at.toUpperCase() + '</b> by ' + esc(hd['cert' + at + '_approver'] || '—') + (hd['cert' + at + '_remarks'] ? ' — ' + esc(hd['cert' + at + '_remarks']) : '')
						+ '. It comes back to Box A once the preparer corrects it.';
				} else {
					banner.className = 'alert d-none mb-3';
					banner.innerHTML = '';
				}

				renderSigners(hd, status);
				renderFields(hd);
				renderTotals(hd);
				renderItems(hd);

				var remarks = document.getElementById('reviewRemarks');
				remarks.value = '';
				remarks.classList.remove('is-invalid');
				document.getElementById('reviewDecisionTitle').textContent = 'Your decision · Box ' + box.toUpperCase() + ' · ' + (BOX_ROLE[box] || '');
				showActions(decide);

				// Each box is decided by the signatory named on it (or an administrator)
				var signer = box ? signatory(hd, box) : '';
				var note = 'Already decided — view only.';
				if (decide) { note = 'Check the purchase request, then approve or disapprove Box ' + box.toUpperCase() + '.'; }
				else if (box && !perms.approve) { note = 'View only — you don\'t have permission to approve.'; }
				else if (box) {
					note = signer ? 'Waiting for ' + signer + ' (Box ' + box.toUpperCase() + '). Only that signatory or an administrator can decide this step.'
						: 'No Box ' + box.toUpperCase() + ' signatory is named on this PR — only an administrator can decide this step.';
				}
				document.getElementById('reviewFooterNote').textContent = note;

				bootstrap.Modal.getOrCreateInstance(document.getElementById('reviewModal')).show();
			})
			.fail(function (xhr) {
				toastr.error((xhr.responseJSON && xhr.responseJSON.error) || 'Could not load this purchase request.', 'Oops!', { closeButton: true });
			});
	};

	//APPROVAL - APPROVE / DISAPPROVE THE CURRENT BOX (two-step: choose, then confirm)
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
			var box = boxOf(statusOf(record)).toUpperCase();
			document.getElementById('reviewConfirmText').textContent = (approve ? 'Approve Box ' : 'Disapprove at Box ') + box + ' of this PR?';
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
			var btn = $(this), label = btn.html();
			var restore = function () { btn.prop('disabled', false).html(label); };
			btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Saving...');
			$.ajax({
				type: 'POST',
				url: mesiteurl + 'myprapproval',
				data: {
					recid: record.recid,
					remarks: remarks.value,
					meaction: decision === 'approve' ? 'MAIN-APPROVE' : 'MAIN-DISAPPROVE'
				},
				global: false,
				cache: false,
				success: function (data) {
					$('.me-outp-msg').html(data);
					// a decision reloads the page, so the button stays off until then
					if (String(data).indexOf('window.location') === -1) { restore(); }
				},
				error: function (xhr, status, error) {
					toastr.error('The decision could not be saved. ' + (error || ''), 'Oops!', { closeButton: true });
					restore();
				}
			});
		});
	};

	//APPROVAL - PRINT PR
	this.__showPdfInModal = function (recid) {
		document.getElementById('approvalPrintFrame').src = mesiteurl + 'myprapproval?meaction=PRINT-PR&recid=' + encodeURIComponent(recid);
		bootstrap.Modal.getOrCreateInstance(document.getElementById('approvalPrintModal')).show();
	};

	this.__approval_print = function () {
		document.getElementById('reviewPrintBtn').addEventListener('click', function () {
			if (record) { __mysys_pr_approval_ent.__showPdfInModal(record.recid); }
		});
	};
}

// jQuery, DataTables and Bootstrap load in the footer, after this file
document.addEventListener('DOMContentLoaded', function () {
	__mysys_pr_approval_ent.__approval_table();
	__mysys_pr_approval_ent.__approval_decision();
	__mysys_pr_approval_ent.__approval_print();

	if (window.__approval_open) { __mysys_pr_approval_ent.__open_review(window.__approval_open); }
});
