var __mysys_ada_ent = new __mysys_ada_ent();
function __mysys_ada_ent() {
	const siteEl = document.getElementById('__siteurl');
	const mesiteurl = siteEl.getAttribute('data-mesiteurl');
	const perms = window.__ada_perms || {};
	const lookups = window.__ada_lookups || {};
	const rows = window.__ada_data || [];

	let table = null;
	let filter = 'all';
	let record = null;          // ADA open in the editor (null = new)

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

	//ADA - YEAR (the cards and the list follow the year picked in the page head)
	const YEAR_KEY = 'fods.year.myada';
	let year = String(new Date().getFullYear());
	try { year = localStorage.getItem(YEAR_KEY) || year; } catch (e) { /* storage blocked */ }

	// An ADA's year is its ADA date, or when it was encoded if that date is blank
	function yearOf(r) {
		var y = String(r.ada_date || '').slice(0, 4);
		return +y >= 2000 ? y : String(r.added_at || '').slice(0, 4);
	}

	function inYear(r) { return year === 'all' || yearOf(r) === year; }

	//ADA - STAT CARDS: Pending, Returned, Approved, Total amount
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
		$('[data-card-label="total"]').text('Total amount · ' + list.length.toLocaleString() + ' ADA' + (list.length === 1 ? '' : 's'));
	}

	function yearPicker(onChange) {
		var sel = document.getElementById('adaYear');
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

	//ADA - LIST (search, status pills, stat cards)
	this.__ada_table = function () {
		var $ = window.jQuery;

		$.fn.dataTable.ext.search.push(function (settings, data, idx) {
			if (settings.nTable.id !== 'adaTable') { return true; }
			var row = settings.aoData[idx]._aData;
			if (!inYear(row)) { return false; }
			return filter === 'all' || row.status === filter;
		});

		table = $('#adaTable').DataTable({
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
					data: 'adano', className: 'cell-primary',
					render: function (v, type, r) {
						if (type !== 'display') { return (v || '') + ' ' + (r.dvno || '') + ' ' + (r.payee_name || '') + ' ' + (r.particulars || ''); }
						return '<div style="min-width:0"><div class="fw-semibold text-dark" style="overflow-wrap:anywhere">' + (v ? 'ADA ' + esc(v) : '<span class="text-muted">No ADA no. yet</span>') + '</div>'
							+ '<div class="small text-muted">DV ' + esc(r.dvno || '—') + '</div>'
							+ '<div class="small text-muted d-md-none">' + esc(r.payee_name) + '</div></div>';
					}
				},
				{ data: 'payee_name', className: 'd-none d-md-table-cell', render: function (v, type) { return type === 'display' ? '<span style="overflow-wrap:anywhere">' + esc(v || '—') + '</span>' : (v || ''); } },
				{ data: 'ada_date', className: 'd-none d-lg-table-cell text-nowrap', render: function (v, type) { return type === 'display' ? fmtDate(v) : (v || ''); } },
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
							+ '<button type="button" class="fods-icon-btn is-teal ada-edit" data-recid="' + r.recid + '" title="' + (editable ? 'Edit' : 'View') + '"><i class="ti ' + (editable ? 'ti-pencil' : 'ti-eye') + '"></i></button>'
							+ '<button type="button" class="fods-icon-btn ada-print" data-recid="' + r.recid + '" title="Print"><i class="ti ti-printer"></i></button>'
							+ '</div>';
					}
				}
			]
		});

		$('#adaTableSearch').on('input', function () { table.search(this.value).draw(); });

		function applyFilter(key) {
			filter = key;
			$('#adaTableFilter button').removeClass('active').filter('[data-filter="' + key + '"]').addClass('active');
			$('.fods-stat[data-table="adaTable"]').removeClass('is-active');
			if (key !== 'all') { $('.fods-stat[data-table="adaTable"][data-filter="' + key + '"]').addClass('is-active'); }
			table.draw();
		}
		$(document).on('click', '#adaTableFilter button', function () { applyFilter($(this).data('filter')); });
		$(document).on('click', '.fods-stat[data-table="adaTable"]', function () { applyFilter($(this).data('filter')); });

		yearPicker(function () { renderCards(); table.draw(); });
		renderCards();
		table.draw();

		$(document).on('click', '#adaTableAdd', function () { __mysys_ada_ent.__open_ada(null); });
		$(document).on('click', '#adaTable .ada-edit', function () { __mysys_ada_ent.__open_ada($(this).data('recid')); });
		$(document).on('click', '#adaTable .ada-print', function () { __mysys_ada_ent.__showPdfInModal($(this).data('recid')); });
	};

	//ADA - DETAILS (DV picker fills particulars, payee, account no. and net amount)
	function setVal(id, v) { document.getElementById(id).value = v == null ? '' : v; }

	function setNet(v) {
		setVal('net_amount', v);
		document.getElementById('adaNet').textContent = money(v);
	}

	function options(id, list, current, extra) {
		var sel = document.getElementById(id);
		var html = '<option value="">Choose...</option>';
		var seen = false;
		list.forEach(function (o) {
			if (o.full_name === current) { seen = true; }
			html += '<option value="' + esc(o.full_name) + '" data-position="' + esc(o.position) + '">' + esc(o.full_name) + '</option>';
		});
		if (current && !seen) { html += '<option value="' + esc(current) + '" data-position="' + esc(extra || '') + '">' + esc(current) + '</option>'; }
		sel.innerHTML = html;
		sel.value = current || '';
	}

	function canSave() {
		if (!record) { return !!perms.add; }
		var status = statusOf(record);
		return !!perms.edit && (status === 'pending' || status === 'disapproved');
	}

	this.__ada_details = function () {
		var $ = window.jQuery;

		$('#dvno').autocomplete({
			appendTo: '#adaModal',
			minLength: 0,
			source: function (req, resp) {
				var terms = req.term.toLowerCase().split(/\s+/).filter(Boolean);
				var out = [];
				var list = lookups.dvs || [];
				for (var i = 0; i < list.length && out.length < 40; i++) {
					var hay = (list[i].dvno + ' ' + list[i].payee_name).toLowerCase();
					if (terms.every(function (t) { return hay.indexOf(t) !== -1; })) { out.push(list[i]); }
				}
				resp(out);
			},
			select: function (event, ui) {
				$('#dvno').val(ui.item.dvno).removeClass('is-invalid');
				$.getJSON(mesiteurl + 'myada', { meaction: 'DV-LOAD', dvno: ui.item.dvno })
					.done(function (dv) {
						setVal('particulars', dv.particulars);
						setVal('payee_name', dv.payee_name);
						setVal('payee_accountno', dv.payee_accountno);
						setNet(dv.net_amount);
					})
					.fail(function (xhr) { toastr.error((xhr.responseJSON && xhr.responseJSON.error) || 'Could not load that DV.', 'Oops!', { closeButton: true }); });
				return false;
			}
		}).on('focus', function () {
			if (!this.disabled) { $(this).autocomplete('search', this.value); }
		}).autocomplete('instance')._renderItem = function (ul, item) {
			return $('<li>').append('<div><b>' + esc(item.dvno) + '</b> <span class="text-muted">' + money(item.net_amount) + '</span>'
				+ '<span class="ors-ac-sub">' + esc(item.payee_name) + (item.on_ada ? ' · already on ADA ' + esc(item.on_ada) : '') + '</span></div>').appendTo(ul);
		};

		$('#certified_a').on('change', function () { $('#position_a').val($(this).find(':selected').data('position') || ''); });
		$('#certified_b').on('change', function () { $('#position_b').val($(this).find(':selected').data('position') || ''); });
	};

	function fill(hd) {
		var form = document.getElementById('adaForm');
		form.reset();
		form.classList.remove('was-validated');
		['recid', 'dvno', 'adano', 'particulars', 'payee_name', 'payee_accountno', 'position_a', 'position_b'].forEach(function (f) { setVal(f, hd ? hd[f] : ''); });
		setVal('ada_date', hd ? (String(hd.ada_date || '').indexOf('0000') === 0 ? '' : hd.ada_date) : new Date().toISOString().slice(0, 10));
		setNet(hd ? hd.net_amount : 0);
		options('certified_a', lookups.certifya || [], hd ? hd.certified_a : '', hd ? hd.position_a : '');
		options('certified_b', lookups.certifyb || [], hd ? hd.certified_b : '', hd ? hd.position_b : '');
	}

	function setHeader(hd) {
		var $ = window.jQuery;
		var status = hd ? statusOf(hd) : null;
		$('#adaModalTitle').text(hd ? (hd.adano ? 'ADA ' + hd.adano : 'ADA #' + hd.recid) : 'New ADA');
		$('#adaModalSub').html(hd ? esc(hd.payee_name) + ' &nbsp;' + chip(status) : 'Pick the DV to pay; its payee and amount fill in.');
		$('#adaPrintBtn').toggleClass('d-none', !hd);

		var banner = $('#adaBanner').removeClass('alert-success alert-danger d-none');
		if (status === 'approved') {
			banner.addClass('alert-success').html('<b>Approved</b>' + (hd.approver ? ' by ' + esc(hd.approver) : '') + (hd.remarks ? ' — ' + esc(hd.remarks) : '') + '. It can no longer be edited.');
		} else if (status === 'disapproved') {
			banner.addClass('alert-danger').html('<b>Disapproved</b>' + (hd.approver ? ' by ' + esc(hd.approver) : '') + (hd.remarks ? ' — ' + esc(hd.remarks) : '')
				+ (canSave() ? '<div class="small mt-1">Saving your corrections sends it back for approval.</div>' : ''));
		} else {
			banner.addClass('d-none');
		}
		$('#submitBtn').toggleClass('d-none', !canSave()).text(!hd ? 'Create ADA' : (status === 'disapproved' ? 'Save & resubmit' : 'Save changes'));
	}

	this.__open_ada = function (recid) {
		var $ = window.jQuery;
		var modalEl = document.getElementById('adaModal');

		function show(hd) {
			record = hd;
			fill(hd);
			setHeader(hd);
			$('#adaModal .modal-body').find('input, select, textarea').not('[readonly]').prop('disabled', !canSave());
			bootstrap.Modal.getOrCreateInstance(modalEl).show();
		}

		if (!recid) { show(null); return; }
		$.getJSON(mesiteurl + 'myada', { meaction: 'MAIN-LOAD', recid: recid })
			.done(show)
			.fail(function (xhr) {
				toastr.error((xhr.responseJSON && xhr.responseJSON.error) || 'Could not load this ADA.', 'Oops!', { closeButton: true });
			});
	};

	//ADA - SAVE (same fields as before)
	this.__ada_saving = function () {
		var $ = window.jQuery;
		var form = document.getElementById('adaForm');
		form.addEventListener('submit', function (e) {
			e.preventDefault();
			if (!form.checkValidity()) {
				form.classList.add('was-validated');
				var bad = form.querySelector(':invalid');
				if (bad) { bad.focus(); }
				return;
			}
			var val = function (id) { return document.getElementById(id).value; };
			var mparam = {
				recid: val('recid'),
				adano: val('adano'),
				dvno: val('dvno'),
				particulars: val('particulars'),
				ada_date: val('ada_date'),
				payee_name: val('payee_name'),
				payee_accountno: val('payee_accountno'),
				net_amount: val('net_amount'),
				certified_a: val('certified_a'),
				position_a: val('position_a'),
				certified_b: val('certified_b'),
				position_b: val('position_b'),
				meaction: 'MAIN-SAVE'
			};

			var $btn = $('#submitBtn'), label = $btn.html();
			$btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Saving...');
			$.ajax({
				type: 'POST',
				url: mesiteurl + 'myada',
				data: mparam,
				global: false,
				cache: false,
				success: function (data) { $('.me-outp-msg').html(data); },
				error: function (xhr, status, error) { toastr.error('[ADA]', 'Error loading page...' + error, { closeButton: true }); },
				complete: function () { setTimeout(function () { $btn.prop('disabled', false).html(label); }, 1200); }
			});
		});
	};

	//ADA - PRINT
	this.__showPdfInModal = function (recid) {
		document.getElementById('adaPrintFrame').src = mesiteurl + 'myada?meaction=PRINT-ADA&recid=' + encodeURIComponent(recid);
		bootstrap.Modal.getOrCreateInstance(document.getElementById('adaPrintModal')).show();
	};

	this.__ada_print = function () {
		document.getElementById('adaPrintBtn').addEventListener('click', function () { if (record) { __mysys_ada_ent.__showPdfInModal(record.recid); } });
	};
}

// jQuery, DataTables, jQuery UI and Bootstrap load in the footer, after this file
document.addEventListener('DOMContentLoaded', function () {
	__mysys_ada_ent.__ada_table();
	__mysys_ada_ent.__ada_details();
	__mysys_ada_ent.__ada_saving();
	__mysys_ada_ent.__ada_print();

	// Deep link (e.g. from ADA Approval): open that ADA right away
	if (window.__ada_open) { __mysys_ada_ent.__open_ada(window.__ada_open); }
});
