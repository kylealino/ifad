var __mysys_rci_ent = new __mysys_rci_ent();
function __mysys_rci_ent() {
	const siteEl = document.getElementById('__siteurl');
	const mesiteurl = siteEl.getAttribute('data-mesiteurl');
	const perms = window.__rc_perms || {};
	const lookups = window.__rc_lookups || {};
	const rows = window.__rc_data || [];

	let table = null;
	let record = null;          // report open in the editor (null = new)

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

	//RCI - YEAR (the cards and the list follow the year picked in the page head)
	const YEAR_KEY = 'fods.year.myrci';
	let year = String(new Date().getFullYear());
	try { year = localStorage.getItem(YEAR_KEY) || year; } catch (e) { /* storage blocked */ }

	// A report has no date of its own: its year is when it was encoded
	function yearOf(r) { return String(r.created_at || '').slice(0, 4); }

	function inYear(r) { return year === 'all' || yearOf(r) === year; }

	//RCI - STAT CARDS: Reports, LDDAP-ADAs covered, DVs paid, Total amount
	function renderCards() {
		var $ = window.jQuery;
		var list = rows.filter(inYear);
		var sum = function (k) { return list.reduce(function (t, r) { return t + num(r[k]); }, 0); };
		var lddapadas = {};
		list.forEach(function (r) { String(r.lddapadanos || '').split(', ').filter(Boolean).forEach(function (n) { lddapadas[n] = true; }); });
		$('[data-card-value="reports"]').text(list.length.toLocaleString());
		$('[data-card-label="reports"]').text('Report' + (list.length === 1 ? '' : 's') + ' · ' + money(sum('net_amount')));
		$('[data-card-value="lddapadas"]').text(Object.keys(lddapadas).length.toLocaleString());
		$('[data-card-value="dvs"]').text(sum('dv_count').toLocaleString());
		$('[data-card-label="dvs"]').text('DVs paid · gross ' + money(sum('gross_amount')));
		$('[data-card-value="total"]').text(money(sum('net_amount')));
		$('[data-card-label="total"]').text('Total amount · net');
	}

	function yearPicker(onChange) {
		var sel = document.getElementById('rcYear');
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

	//RCI - LIST (search, stat cards)
	this.__rc_table = function () {
		var $ = window.jQuery;

		$.fn.dataTable.ext.search.push(function (settings, data, idx) {
			if (settings.nTable.id !== 'rcTable') { return true; }
			return inYear(settings.aoData[idx]._aData);
		});

		table = $('#rcTable').DataTable({
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
				zeroRecords: '<div class="text-center text-muted py-4"><i class="ti ti-search fs-6 d-block mb-1"></i>No matches found</div>',
				paginate: { previous: '<i class="ti ti-chevron-left"></i>', next: '<i class="ti ti-chevron-right"></i>' }
			},
			columns: [
				{
					data: 'reportno', className: 'cell-primary',
					render: function (v, type, r) {
						if (type !== 'display') { return (v || '') + ' ' + (r.lddapadanos || ''); }
						return '<div style="min-width:0"><div class="fw-semibold text-dark" style="overflow-wrap:anywhere">' + (v ? 'Report ' + esc(v) : '<span class="text-muted">No report no.</span>') + '</div>'
							+ '<div class="small text-muted" style="overflow-wrap:anywhere">' + (r.lddapadanos ? 'LDDAP-ADA ' + esc(r.lddapadanos) : '—') + '</div></div>';
					}
				},
				{
					data: 'mds_accountno', className: 'd-none d-lg-table-cell small',
					render: function (v, type, r) {
						return type === 'display' ? '<span class="fods-chip ' + (r.fund_cluster_code === '07' ? 'tone-amber' : 'tone-indigo') + '">FC ' + esc(r.fund_cluster_code) + '</span> <span class="text-muted text-nowrap">' + esc(v || '') + '</span>' : (v || '');
					}
				},
				{ data: 'dv_count', className: 'd-none d-md-table-cell', render: function (v, type) { return type === 'display' ? '<span class="text-nowrap">' + (+v).toLocaleString() + ' DV' + (+v === 1 ? '' : 's') + '</span>' : +v; } },
				{
					data: 'net_amount', className: 'text-end cell-left',
					render: function (v, type) { return type === 'display' ? '<span class="text-nowrap fw-semibold">' + money(v) + '</span>' : num(v); }
				},
				{
					data: 'created_at', className: 'd-none d-xl-table-cell text-muted small',
					render: function (v, type, r) { return type === 'display' ? fmtDate(v) + (r.created_by ? '<br>' + esc(r.created_by) : '') : (v || ''); }
				},
				{
					data: null, orderable: false, searchable: false, className: 'cell-actions text-end',
					render: function (v, type, r) {
						return '<div class="d-flex justify-content-end gap-1">'
							+ '<button type="button" class="fods-icon-btn is-teal rc-edit" data-recid="' + r.recid + '" title="' + (perms.edit ? 'Edit' : 'View') + '"><i class="ti ' + (perms.edit ? 'ti-pencil' : 'ti-eye') + '"></i></button>'
							+ '<button type="button" class="fods-icon-btn rc-print" data-recid="' + r.recid + '" title="Print"><i class="ti ti-printer"></i></button>'
							+ '</div>';
					}
				}
			]
		});

		$('#rcTableSearch').on('input', function () { table.search(this.value).draw(); });

		yearPicker(function () { renderCards(); table.draw(); });
		renderCards();
		table.draw();

		$(document).on('click', '#rcTableAdd', function () { __mysys_rci_ent.__open_rc(null); });
		$(document).on('click', '#rcTable .rc-edit', function () { __mysys_rci_ent.__open_rc($(this).data('recid')); });
		$(document).on('click', '#rcTable .rc-print', function () { __mysys_rci_ent.__showPdfInModal($(this).data('recid')); });
	};

	//RCI - LINES
	function canSave() { return record ? !!perms.edit : !!perms.add; }

	function lineHtml(d) {
		d = d || {};
		var val = function (k) { return esc(d[k] == null ? '' : d[k]); };
		var text = function (cls, k) { return '<td><input type="text" class="' + cls + '" value="' + val(k) + '" title="' + val(k) + '"></td>'; };
		var amt = function (cls, k) { return '<td class="num"><input type="number" step="any" class="' + cls + '" value="' + val(k) + '"></td>'; };
		return '<tr>'
			+ '<td class="sticky-l g-no text-muted"></td>'
			+ text('g-ld', 'lddapadano')
			+ '<td><input type="date" class="g-date" value="' + val('lddapada_date') + '"></td>'
			+ text('g-ck', 'ckno')
			+ text('g-dv', 'dvno')
			+ text('g-serial', 'serialno')
			+ text('g-rc', 'responsibility_code')
			+ text('g-payee', 'payee_name')
			+ text('g-part', 'particulars')
			+ amt('g-gross', 'gross_amount')
			+ amt('g-ph', 'philhealth_amount')
			+ amt('g-tax', 'tax_amount')
			+ amt('g-net', 'net_amount')
			+ '<td class="sticky-r"><button type="button" class="fods-icon-btn is-danger rc-remove" title="Remove line"><i class="ti ti-trash"></i></button></td>'
			+ '</tr>';
	}

	function recalc() {
		var $ = window.jQuery;
		var sum = { gross: 0, philhealth: 0, tax: 0, net: 0 };
		var trs = $('#rcGrid tbody tr');
		trs.each(function (i) {
			var tr = $(this);
			tr.find('.g-no').text(i + 1);
			sum.gross += num(tr.find('.g-gross').val());
			sum.philhealth += num(tr.find('.g-ph').val());
			sum.tax += num(tr.find('.g-tax').val());
			sum.net += num(tr.find('.g-net').val());
		});
		Object.keys(sum).forEach(function (k) { $('#rcGrid [data-sum="' + k + '"]').text(money(sum[k])); });
		$('#rcGrid').closest('.rc-grid-wrap').toggleClass('d-none', trs.length === 0);
		$('#rcEmpty').toggleClass('d-none', trs.length > 0);
		$('#rcCount').text('· ' + trs.length + ' line' + (trs.length === 1 ? '' : 's'));
		$('#rcFootCount').text(trs.length);
		$('#rcFootGross').text(money(sum.gross));
		$('#rcFootNet').text(money(sum.net));
	}

	function dvsOnReport() {
		var $ = window.jQuery;
		return $('#rcGrid tbody .g-dv').map(function () { return this.value; }).get();
	}

	function addLddapada(item) {
		var $ = window.jQuery;
		$.getJSON(mesiteurl + 'myrci', { meaction: 'LDDAPADA-LINES', id: item.id })
			.done(function (lines) {
				var have = dvsOnReport();
				var added = 0;
				(lines || []).forEach(function (l) {
					if (l.dvno && have.indexOf(l.dvno) !== -1) { return; }
					$('#rcGrid tbody').append(lineHtml(l));
					added++;
				});
				recalc();
				if (!added) { toastr.info('All DVs of LDDAP-ADA ' + item.lddapadano + ' are already on this report.', 'Nothing added', { closeButton: true }); }
			})
			.fail(function () { toastr.error('Could not load LDDAP-ADA ' + item.lddapadano + '.', 'Oops!', { closeButton: true }); });
	}

	this.__rc_lines = function () {
		var $ = window.jQuery;
		var modal = $('#rcModal');

		modal.on('input', '#rcGrid input[type=number]', recalc);
		modal.on('change', '#rcGrid input[type=text]', function () { this.title = this.value; });
		modal.on('click', '.rc-remove', function () { $(this).closest('tr').remove(); recalc(); });
		$('#rcAddBlank').on('click', function () {
			var tr = $(lineHtml({ philhealth_amount: '0' })).appendTo('#rcGrid tbody');
			recalc();
			tr.find('input').first().trigger('focus');
		});

		// LDDAP-ADA picker: each suggestion shows its date, check no., DVs and any report it is already on
		$('#ld_lookup').autocomplete({
			appendTo: '#rcModal',
			minLength: 0,
			source: function (req, resp) {
				var terms = req.term.toLowerCase().split(/\s+/).filter(Boolean);
				var out = [];
				var list = lookups.lddapadas || [];
				for (var i = 0; i < list.length && out.length < 40; i++) {
					var hay = (list[i].lddapadano + ' ' + list[i].ckno).toLowerCase();
					if (terms.every(function (t) { return hay.indexOf(t) !== -1; })) { out.push(list[i]); }
				}
				resp(out);
			},
			select: function (event, ui) {
				addLddapada(ui.item);
				$(this).val('');
				return false;
			}
		}).on('focus', function () {
			if (!this.disabled) { $(this).autocomplete('search', this.value); }
		}).autocomplete('instance')._renderItem = function (ul, item) {
			return $('<li>').append('<div><b>' + esc(item.lddapadano) + '</b> <span class="text-muted">' + money(item.net_amount) + '</span>'
				+ '<span class="ors-ac-sub">' + fmtDate(item.lddapada_date) + ' · check ' + esc(item.ckno) + ' · ' + item.dv_count + ' DV' + (+item.dv_count === 1 ? '' : 's')
				+ (item.on_rci ? ' · on report ' + esc(item.on_rci) : '') + '</span></div>').appendTo(ul);
		};

		// MDS sub-account follows the fund cluster, as on LDDAP-ADA
		document.getElementById('fund_cluster_code').addEventListener('change', function () {
			var acct = (lookups.mds_accounts || {})[this.value];
			if (acct) { document.getElementById('mds_accountno').value = acct; }
		});
	};

	//RCI - OPEN EDITOR (new, or load one report with its lines)
	function setVal(id, v) { document.getElementById(id).value = v == null ? '' : v; }

	function fill(hd) {
		var $ = window.jQuery;
		var form = document.getElementById('rcForm');
		form.reset();
		form.classList.remove('was-validated');
		setVal('recid', hd ? hd.recid : '');
		setVal('reportno', hd ? hd.reportno : '');
		setVal('mds_branch', hd ? hd.mds_branch : lookups.mds_branch);
		setVal('mds_accountno', hd ? hd.mds_accountno : (lookups.mds_accounts || {})['01']);
		var sel = document.getElementById('fund_cluster_code');
		var current = hd ? hd.fund_cluster_code : '01';
		var list = (lookups.fund_clusters || []).slice();
		if (current && list.indexOf(current) === -1) { list.push(current); }
		sel.innerHTML = '<option value="">Choose...</option>' + list.map(function (v) { return '<option value="' + esc(v) + '">' + esc(v) + '</option>'; }).join('');
		sel.value = current || '';
		$('#rcGrid tbody').html(hd ? (hd.lines || []).map(lineHtml).join('') : '');
		$('#ld_lookup').val('');
	}

	this.__open_rc = function (recid) {
		var $ = window.jQuery;
		var modalEl = document.getElementById('rcModal');

		function show(hd) {
			record = hd;
			fill(hd);
			$('#rcModalTitle').text(hd ? (hd.reportno ? 'Report ' + hd.reportno : 'RCI #' + hd.recid) : 'New RCI');
			$('#rcModalSub').text(hd ? 'Encoded ' + fmtDate(hd.created_at) + (hd.created_by ? ' by ' + hd.created_by : '') : 'Fill in the report details, then add the LDDAP-ADAs.');
			$('#rcPrintBtn').toggleClass('d-none', !hd);
			var editable = canSave();
			$('#rcModal .modal-body').find('input, select, textarea, button').prop('disabled', !editable);
			$('#rcAddRow').toggleClass('d-none', !editable);
			$('#rcModal .rc-remove').toggleClass('d-none', !editable);
			$('#submitBtn').toggleClass('d-none', !editable).text(hd ? 'Save changes' : 'Create RCI');
			recalc();
			bootstrap.Modal.getOrCreateInstance(modalEl).show();
		}

		if (!recid) { show(null); return; }
		$.getJSON(mesiteurl + 'myrci', { meaction: 'MAIN-LOAD', recid: recid })
			.done(show)
			.fail(function (xhr) {
				toastr.error((xhr.responseJSON && xhr.responseJSON.error) || 'Could not load this report.', 'Oops!', { closeButton: true });
			});
	};

	//RCI - SAVE (same payload as before: header + one "x|x" row of 12 fields per line)
	function collectLines() {
		var $ = window.jQuery;
		var out = [];
		$('#rcGrid tbody tr').each(function () {
			var tr = $(this);
			var v = function (cls) { var x = tr.find('.g-' + cls).val(); return x == null ? '' : x; };
			out.push([v('ld'), v('date'), v('ck'), v('dv'), v('serial'), v('rc'), v('payee'), v('part'), v('gross'), v('ph'), v('tax'), v('net')].join('x|x'));
		});
		return out;
	}

	this.__rc_saving = function () {
		var $ = window.jQuery;
		var form = document.getElementById('rcForm');
		form.addEventListener('submit', function (e) {
			e.preventDefault();
			if (!form.checkValidity()) {
				form.classList.add('was-validated');
				var bad = form.querySelector(':invalid');
				if (bad) { bad.focus(); }
				return;
			}
			var lines = collectLines();
			if (!lines.length) {
				toastr.warning('Add at least one LDDAP-ADA or line.', 'No lines', { closeButton: true });
				document.getElementById('ld_lookup').focus();
				return;
			}
			var val = function (id) { return document.getElementById(id).value; };
			var mparam = {
				recid: val('recid'),
				mds_branch: val('mds_branch'),
				mds_accountno: val('mds_accountno'),
				fund_cluster_code: val('fund_cluster_code'),
				reportno: val('reportno'),
				rcidtdata: lines,
				meaction: 'MAIN-SAVE'
			};

			var $btn = $('#submitBtn'), label = $btn.html();
			$btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Saving...');
			$.ajax({
				type: 'POST',
				url: mesiteurl + 'myrci',
				data: mparam,
				global: false,
				cache: false,
				success: function (data) { $('.me-outp-msg').html(data); },
				error: function (xhr, status, error) { toastr.error('[RCI]', 'Error loading page...' + error, { closeButton: true }); },
				complete: function () { setTimeout(function () { $btn.prop('disabled', false).html(label); }, 1200); }
			});
		});
	};

	//RCI - PRINT
	this.__showPdfInModal = function (recid) {
		document.getElementById('rcPrintFrame').src = mesiteurl + 'myrci?meaction=PRINT-RCI&recid=' + encodeURIComponent(recid);
		bootstrap.Modal.getOrCreateInstance(document.getElementById('rcPrintModal')).show();
	};

	this.__rc_print = function () {
		document.getElementById('rcPrintBtn').addEventListener('click', function () { if (record) { __mysys_rci_ent.__showPdfInModal(record.recid); } });
	};
}

// jQuery, DataTables, jQuery UI and Bootstrap load in the footer, after this file
document.addEventListener('DOMContentLoaded', function () {
	__mysys_rci_ent.__rc_table();
	__mysys_rci_ent.__rc_lines();
	__mysys_rci_ent.__rc_saving();
	__mysys_rci_ent.__rc_print();

	if (window.__rc_open) { __mysys_rci_ent.__open_rc(window.__rc_open); }
});
