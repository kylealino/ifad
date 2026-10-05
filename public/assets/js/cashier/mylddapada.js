var __mysys_lddapada_ent = new __mysys_lddapada_ent();
function __mysys_lddapada_ent() {
	const siteEl = document.getElementById('__siteurl');
	const mesiteurl = siteEl.getAttribute('data-mesiteurl');
	const perms = window.__ld_perms || {};
	const lookups = window.__ld_lookups || {};
	const rows = window.__ld_data || [];

	let table = null;
	let filter = 'all';
	let record = null;          // LDDAP-ADA open in the editor (null = new)
	let picked = [];            // DV numbers in the order they were added

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

	function chip(status) {
		var s = STATUS[status] || STATUS.pending;
		return '<span class="fods-chip ' + s[0] + '"><span class="fods-dot"></span>' + s[1] + '</span>';
	}

	// Single approval step (same rule as MyLDDAPADA::statusOf)
	function statusOf(hd) {
		if (String(hd.is_disapproved) === '1') { return 'disapproved'; }
		if (String(hd.is_approved) === '1') { return 'approved'; }
		return 'pending';
	}

	//LDDAP-ADA - YEAR (the cards and the list follow the year picked in the page head)
	const YEAR_KEY = 'fods.year.mylddapada';
	let year = String(new Date().getFullYear());
	try { year = localStorage.getItem(YEAR_KEY) || year; } catch (e) { /* storage blocked */ }

	// An LDDAP-ADA's year is its date, or when it was encoded if that date is blank
	function yearOf(r) {
		var y = String(r.lddapada_date || '').slice(0, 4);
		return +y >= 2000 ? y : String(r.added_at || '').slice(0, 4);
	}

	function inYear(r) { return year === 'all' || yearOf(r) === year; }

	//LDDAP-ADA - STAT CARDS: Pending, Returned, Approved, Total amount
	function renderCards() {
		var $ = window.jQuery;
		var list = rows.filter(inYear);
		var amount = function (l) { return l.reduce(function (t, r) { return t + num(r.net_amount); }, 0); };
		var card = function (key, status, label) {
			var l = list.filter(function (r) { return r.status === status; });
			$('[data-card-value="' + key + '"]').text(l.length.toLocaleString());
			$('[data-card-label="' + key + '"]').text(label + ' · ' + money(amount(l)));
		};
		card('pending', 'pending', 'Pending');
		card('returned', 'disapproved', 'Returned');
		card('approved', 'approved', 'Approved');
		$('[data-card-value="total"]').text(money(amount(list)));
		$('[data-card-label="total"]').text('Total amount · ' + list.length.toLocaleString() + ' LDDAP-ADA' + (list.length === 1 ? '' : 's'));
	}

	function yearPicker(onChange) {
		var sel = document.getElementById('ldYear');
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

	//LDDAP-ADA - LIST (search, status pills, stat cards)
	this.__ld_table = function () {
		var $ = window.jQuery;

		$.fn.dataTable.ext.search.push(function (settings, data, idx) {
			if (settings.nTable.id !== 'ldTable') { return true; }
			var row = settings.aoData[idx]._aData;
			if (!inYear(row)) { return false; }
			return filter === 'all' || row.status === filter;
		});

		table = $('#ldTable').DataTable({
			data: rows,
			deferRender: true,
			pageLength: 10,
			lengthChange: false,
			autoWidth: false,
			order: [[5, 'desc']],
			dom: 't<"d-flex flex-wrap justify-content-between align-items-center"ip>',
			language: {
				info: 'Showing _START_–_END_ of _TOTAL_',
				infoEmpty: 'Nothing to show',
				infoFiltered: '',
				zeroRecords: '<div class="text-center text-muted py-4"><i class="ti ti-search fs-6 d-block mb-1"></i>No matches found</div>',
				paginate: { previous: '<i class="ti ti-chevron-left"></i>', next: '<i class="ti ti-chevron-right"></i>' }
			},
			columns: [
				{
					data: 'lddapadano', className: 'cell-primary',
					render: function (v, type, r) {
						if (type !== 'display') { return (v || '') + ' ' + (r.ckno || '') + ' ' + (r.mds_accountno || ''); }
						return '<div style="min-width:0"><div class="fw-semibold text-dark" style="overflow-wrap:anywhere">' + esc(v || '—') + '</div>'
							+ '<div class="small text-muted">Check ' + esc(r.ckno || '—') + ' · ' + r.dv_count + ' DV' + (+r.dv_count === 1 ? '' : 's')
							+ (String(r.is_ci) === '1' ? ' · Internal creditor' : '') + '</div></div>';
					}
				},
				{ data: 'lddapada_date', className: 'd-none d-md-table-cell text-nowrap', render: function (v, type) { return type === 'display' ? fmtDate(v) : (v || ''); } },
				{
					data: 'fund_cluster_code', className: 'd-none d-lg-table-cell',
					render: function (v, type) { return type === 'display' ? '<span class="fods-chip ' + (v === '07' ? 'tone-amber' : 'tone-indigo') + '">FC ' + esc(v) + '</span>' : v; }
				},
				{
					data: 'net_amount', className: 'text-end cell-left',
					render: function (v, type) { return type === 'display' ? '<span class="text-nowrap fw-semibold">' + money(v) + '</span>' : num(v); }
				},
				{
					data: 'status', className: 'cell-right',
					render: function (v, type) { return type === 'display' ? chip(v) : (STATUS[v] || STATUS.pending)[1]; }
				},
				{
					data: 'added_at', className: 'd-none d-xl-table-cell text-muted small',
					render: function (v, type, r) { return type === 'display' ? fmtDate(v) + (r.added_by ? '<br>' + esc(r.added_by) : '') : (v || ''); }
				},
				{
					data: null, orderable: false, searchable: false, className: 'cell-actions text-end',
					render: function (v, type, r) {
						var editable = perms.edit && (r.status === 'pending' || r.status === 'disapproved');
						return '<div class="d-flex justify-content-end gap-1">'
							+ '<button type="button" class="fods-icon-btn is-teal ld-edit" data-recid="' + r.recid + '" title="' + (editable ? 'Edit' : 'View') + '"><i class="ti ' + (editable ? 'ti-pencil' : 'ti-eye') + '"></i></button>'
							+ '<button type="button" class="fods-icon-btn ld-print" data-recid="' + r.recid + '" title="Print"><i class="ti ti-printer"></i></button>'
							+ '</div>';
					}
				}
			]
		});

		$('#ldTableSearch').on('input', function () { table.search(this.value).draw(); });

		function applyFilter(key) {
			filter = key;
			$('#ldTableFilter button').removeClass('active').filter('[data-filter="' + key + '"]').addClass('active');
			$('.fods-stat[data-table="ldTable"]').removeClass('is-active');
			if (key !== 'all') { $('.fods-stat[data-table="ldTable"][data-filter="' + key + '"]').addClass('is-active'); }
			table.draw();
		}
		$(document).on('click', '#ldTableFilter button', function () { applyFilter($(this).data('filter')); });
		$(document).on('click', '.fods-stat[data-table="ldTable"]', function () { applyFilter($(this).data('filter')); });

		yearPicker(function () { renderCards(); table.draw(); });
		renderCards();
		table.draw();

		$(document).on('click', '#ldTableAdd', function () { __mysys_lddapada_ent.__open_ld(null); });
		$(document).on('click', '#ldTable .ld-edit', function () { __mysys_lddapada_ent.__open_ld($(this).data('recid')); });
		$(document).on('click', '#ldTable .ld-print', function () { __mysys_lddapada_ent.__showPdfInModal($(this).data('recid')); });
	};

	//LDDAP-ADA - DV LINES
	function canSave() {
		if (!record) { return !!perms.add; }
		var status = statusOf(record);
		return !!perms.edit && (status === 'pending' || status === 'disapproved');
	}

	function lineHtml(d) {
		var val = function (k) { return esc(d[k] == null ? '' : d[k]); };
		var text = function (cls, k, ro) { return '<td><input type="text" class="' + cls + '" value="' + val(k) + '" title="' + val(k) + '"' + (ro ? ' readonly tabindex="-1"' : '') + '></td>'; };
		var amt = function (cls, k) { return '<td class="num"><input type="number" step="any" class="' + cls + '" value="' + val(k) + '"></td>'; };
		return '<tr data-dvno="' + val('dvno') + '">'
			+ '<td class="sticky-l g-no text-muted"></td>'
			+ text('g-dv', 'dvno', true)
			+ text('g-payee', 'payee_name')
			+ text('g-acct', 'payee_account_num')
			+ text('g-serial', 'serialno')
			+ text('g-uacs', 'uacs_code')
			+ amt('g-gross', 'gross_amount')
			+ amt('g-tax', 'total_deduction')
			+ amt('g-net', 'net_amount')
			+ text('g-remarks', 'remarks')
			+ '<td class="sticky-r"><button type="button" class="fods-icon-btn is-danger ld-remove" title="Remove DV"><i class="ti ti-trash"></i></button></td>'
			+ '</tr>';
	}

	function recalc() {
		var $ = window.jQuery;
		var sum = { gross: 0, tax: 0, net: 0 };
		var trs = $('#ldGrid tbody tr');
		trs.each(function (i) {
			var tr = $(this);
			tr.find('.g-no').text(i + 1);
			sum.gross += num(tr.find('.g-gross').val());
			sum.tax += num(tr.find('.g-tax').val());
			sum.net += num(tr.find('.g-net').val());
		});
		Object.keys(sum).forEach(function (k) { $('#ldGrid [data-sum="' + k + '"]').text(money(sum[k])); });
		$('#ldGrid').closest('.ld-grid-wrap').toggleClass('d-none', trs.length === 0);
		$('#ldEmpty').toggleClass('d-none', trs.length > 0);
		$('#ldFootCount').text(trs.length);
		$('#ldFootGross').text(money(sum.gross));
		$('#ldFootNet').text(money(sum.net));
	}

	function renderChips() {
		var $ = window.jQuery;
		var locked = !canSave();
		$('#ldChips').html(picked.length
			? picked.map(function (dv) {
				return '<span class="ld-chip" data-dvno="' + esc(dv) + '">' + esc(dv) + (locked ? '' : '<button type="button" class="ld-chip-x" title="Remove">&times;</button>') + '</span>';
			}).join('')
			: '<span class="text-muted small">No DVs yet</span>');
	}

	function removeDv(dv) {
		var $ = window.jQuery;
		picked = picked.filter(function (x) { return x !== dv; });
		$('#ldGrid tbody tr').filter(function () { return this.getAttribute('data-dvno') === dv; }).remove();
		renderChips();
		recalc();
	}

	function addDv(dv) {
		var $ = window.jQuery;
		if (!dv || picked.indexOf(dv) !== -1) {
			if (dv) { toastr.info('DV ' + dv + ' is already on this LDDAP-ADA.', 'Already added', { closeButton: true }); }
			return;
		}
		$.ajax({ type: 'POST', url: mesiteurl + 'mylddapada?meaction=LOAD-DV', data: { dvno: [dv] }, dataType: 'json' })
			.done(function (res) {
				var line = Array.isArray(res) ? res[0] : null;
				if (!line) { toastr.error('Could not load DV ' + dv + '.', 'Oops!', { closeButton: true }); return; }
				picked.push(dv);
				line.remarks = '';
				$('#ldGrid tbody').append(lineHtml(line));
				renderChips();
				recalc();
			})
			.fail(function () { toastr.error('Could not load DV ' + dv + '.', 'Oops!', { closeButton: true }); });
	}

	this.__ld_lines = function () {
		var $ = window.jQuery;
		var modal = $('#ldModal');

		modal.on('input', '#ldGrid input[type=number]', recalc);
		modal.on('change', '#ldGrid input[type=text]', function () { this.title = this.value; });
		modal.on('click', '.ld-remove', function () { removeDv($(this).closest('tr').attr('data-dvno')); });
		modal.on('click', '.ld-chip-x', function () { removeDv($(this).closest('.ld-chip').attr('data-dvno')); });

		// DV picker: each suggestion shows the payee, net amount and any LDDAP-ADA it is already on
		$('#dv_lookup').autocomplete({
			appendTo: '#ldModal',
			minLength: 0,
			source: function (req, resp) {
				var terms = req.term.toLowerCase().split(/\s+/).filter(Boolean);
				var out = [];
				var list = lookups.dvs || [];
				for (var i = 0; i < list.length && out.length < 40; i++) {
					var d = list[i];
					if (picked.indexOf(d.dvno) !== -1) { continue; }
					var hay = (d.dvno + ' ' + d.payee_name).toLowerCase();
					if (terms.every(function (t) { return hay.indexOf(t) !== -1; })) { out.push(d); }
				}
				resp(out);
			},
			select: function (event, ui) {
				addDv(ui.item.dvno);
				$(this).val('');
				return false;
			}
		}).on('focus', function () {
			if (!this.disabled) { $(this).autocomplete('search', this.value); }
		}).autocomplete('instance')._renderItem = function (ul, item) {
			return $('<li>').append('<div><b>' + esc(item.dvno) + '</b> <span class="text-muted">' + money(item.net_amount) + '</span>'
				+ '<span class="ors-ac-sub">' + esc(item.payee_name) + (item.on_lddapada ? ' · on LDDAP-ADA ' + esc(item.on_lddapada) : '') + '</span></div>').appendTo(ul);
		};
	};

	//LDDAP-ADA - DETAILS
	function setVal(id, v) { document.getElementById(id).value = v == null ? '' : v; }

	function options(id, list, current) {
		var sel = document.getElementById(id);
		var html = '<option value="">Choose...</option>';
		var seen = false;
		list.forEach(function (o) {
			var v = typeof o === 'string' ? o : o.full_name;
			if (v === current) { seen = true; }
			html += '<option value="' + esc(v) + '"' + (o.position != null ? ' data-position="' + esc(o.position) + '"' : '') + '>' + esc(v) + '</option>';
		});
		if (current && !seen) { html += '<option value="' + esc(current) + '">' + esc(current) + '</option>'; }
		sel.innerHTML = html;
		sel.value = current || '';
	}

	function showApproverPosition() {
		var $ = window.jQuery;
		$('#ada_approver_position').text($('#ada_approver').find(':selected').data('position') || '');
	}

	// MDS sub-account follows the fund cluster, as on the previous form
	function syncAccount() {
		var fc = document.getElementById('fund_cluster_code').value;
		var acct = (lookups.mds_accounts || {})[fc];
		if (acct) { setVal('mds_accountno', acct); }
	}

	this.__ld_details = function () {
		document.getElementById('fund_cluster_code').addEventListener('change', syncAccount);
		document.getElementById('ada_approver').addEventListener('change', showApproverPosition);
	};

	function fill(hd) {
		var $ = window.jQuery;
		var form = document.getElementById('ldForm');
		form.reset();
		form.classList.remove('was-validated');
		['recid', 'lddapadano', 'ckno', 'mds_accountno', 'funding_source'].forEach(function (f) { setVal(f, hd ? hd[f] : ''); });
		setVal('mds_branch', hd && hd.mds_branch ? hd.mds_branch : lookups.mds_branch);
		setVal('lddapada_date', hd ? (String(hd.lddapada_date || '').indexOf('0000') === 0 ? '' : hd.lddapada_date) : new Date().toISOString().slice(0, 10));
		options('fund_cluster_code', lookups.fund_clusters || [], hd ? hd.fund_cluster_code : '');
		options('ada_approver', lookups.approvers || [], hd ? hd.ada_approver : '');
		showApproverPosition();
		document.getElementById('is_ci').checked = !!hd && String(hd.is_ci) === '1';
		if (!hd) { syncAccount(); }

		picked = hd ? (hd.dvno_list || []).slice() : [];
		$('#ldGrid tbody').html(hd ? (hd.lines || []).map(lineHtml).join('') : '');
		$('#dv_lookup').val('');
	}

	function setHeader(hd) {
		var $ = window.jQuery;
		var status = hd ? statusOf(hd) : null;
		$('#ldModalTitle').text(hd ? (hd.lddapadano || 'LDDAP-ADA #' + hd.recid) : 'New LDDAP-ADA');
		$('#ldModalSub').html(hd ? 'Check ' + esc(hd.ckno || '—') + ' · ' + fmtDate(hd.lddapada_date) + ' &nbsp;' + chip(status) : 'Fill in the account details, then add the DVs to pay.');
		$('#ldPrintBtn').toggleClass('d-none', !hd);

		var banner = $('#ldBanner').removeClass('alert-success alert-danger d-none');
		if (status === 'approved') {
			banner.addClass('alert-success').html('<b>Approved</b>' + (hd.approver ? ' by ' + esc(hd.approver) : '') + (hd.remarks ? ' — ' + esc(hd.remarks) : '') + '. It can no longer be edited.');
		} else if (status === 'disapproved') {
			banner.addClass('alert-danger').html('<b>Disapproved</b>' + (hd.approver ? ' by ' + esc(hd.approver) : '') + (hd.remarks ? ' — ' + esc(hd.remarks) : '')
				+ (canSave() ? '<div class="small mt-1">Saving your corrections sends it back for approval.</div>' : ''));
		} else {
			banner.addClass('d-none');
		}
		$('#submitBtn').toggleClass('d-none', !canSave()).text(!hd ? 'Create LDDAP-ADA' : (status === 'disapproved' ? 'Save & resubmit' : 'Save changes'));
	}

	this.__open_ld = function (recid) {
		var $ = window.jQuery;
		var modalEl = document.getElementById('ldModal');

		function show(hd) {
			record = hd;
			fill(hd);
			setHeader(hd);
			var editable = canSave();
			$('#ldModal .modal-body').find('input, select, textarea').not('[readonly]').prop('disabled', !editable);
			$('#ldModal .ld-remove').prop('disabled', !editable).toggleClass('d-none', !editable);
			renderChips();
			recalc();
			bootstrap.Modal.getOrCreateInstance(modalEl).show();
		}

		if (!recid) { show(null); return; }
		$.getJSON(mesiteurl + 'mylddapada', { meaction: 'MAIN-LOAD', recid: recid })
			.done(show)
			.fail(function (xhr) {
				toastr.error((xhr.responseJSON && xhr.responseJSON.error) || 'Could not load this LDDAP-ADA.', 'Oops!', { closeButton: true });
			});
	};

	//LDDAP-ADA - SAVE (same payload as before: header + one "x|x" row per DV + the DV list)
	function collectLines() {
		var $ = window.jQuery;
		var out = [];
		$('#ldGrid tbody tr').each(function () {
			var tr = $(this);
			var v = function (cls) { var x = tr.find('.g-' + cls).val(); return x == null ? '' : x; };
			out.push([v('dv'), v('payee'), v('acct'), v('serial'), v('uacs'), v('gross'), v('tax'), v('net'), v('remarks')].join('x|x'));
		});
		return out;
	}

	this.__ld_saving = function () {
		var $ = window.jQuery;
		var form = document.getElementById('ldForm');
		form.addEventListener('submit', function (e) {
			e.preventDefault();
			if (!form.checkValidity()) {
				form.classList.add('was-validated');
				var bad = form.querySelector(':invalid');
				if (bad) { bad.focus(); }
				return;
			}
			if (!picked.length) {
				toastr.warning('Add at least one DV.', 'No DV selected', { closeButton: true });
				document.getElementById('dv_lookup').focus();
				return;
			}
			var val = function (id) { return document.getElementById(id).value; };
			var mparam = {
				recid: val('recid'),
				lddapadano: val('lddapadano'),
				mds_branch: val('mds_branch'),
				mds_accountno: val('mds_accountno'),
				ckno: val('ckno'),
				lddapada_date: val('lddapada_date'),
				fund_cluster_code: val('fund_cluster_code'),
				funding_source: val('funding_source'),
				ada_approver: val('ada_approver'),
				is_ci: document.getElementById('is_ci').checked ? 1 : 0,
				dvdtdata: collectLines(),
				dvno_list: picked.slice(),
				meaction: 'MAIN-SAVE'
			};

			var $btn = $('#submitBtn'), label = $btn.html();
			$btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Saving...');
			$.ajax({
				type: 'POST',
				url: mesiteurl + 'mylddapada',
				data: mparam,
				global: false,
				cache: false,
				success: function (data) { $('.me-outp-msg').html(data); },
				error: function (xhr, status, error) { toastr.error('[LDDAP-ADA]', 'Error loading page...' + error, { closeButton: true }); },
				complete: function () { setTimeout(function () { $btn.prop('disabled', false).html(label); }, 1200); }
			});
		});
	};

	//LDDAP-ADA - PRINT
	this.__showPdfInModal = function (recid) {
		document.getElementById('ldPrintFrame').src = mesiteurl + 'mylddapada?meaction=PRINT-LDDAPADA&recid=' + encodeURIComponent(recid);
		bootstrap.Modal.getOrCreateInstance(document.getElementById('ldPrintModal')).show();
	};

	this.__ld_print = function () {
		document.getElementById('ldPrintBtn').addEventListener('click', function () { if (record) { __mysys_lddapada_ent.__showPdfInModal(record.recid); } });
	};
}

// jQuery, DataTables, jQuery UI and Bootstrap load in the footer, after this file
document.addEventListener('DOMContentLoaded', function () {
	__mysys_lddapada_ent.__ld_table();
	__mysys_lddapada_ent.__ld_lines();
	__mysys_lddapada_ent.__ld_details();
	__mysys_lddapada_ent.__ld_saving();
	__mysys_lddapada_ent.__ld_print();

	// Deep link (e.g. from LDDAP-ADA Approval): open that LDDAP-ADA right away
	if (window.__ld_open) { __mysys_lddapada_ent.__open_ld(window.__ld_open); }
});
