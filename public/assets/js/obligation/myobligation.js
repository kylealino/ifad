var __mysys_obl_ent = new __mysys_obl_ent();
function __mysys_obl_ent() {
	const siteEl = document.getElementById('__siteurl');
	const mesiteurl = siteEl.getAttribute('data-mesiteurl');
	const perms = window.__obl_perms || {};     // per form: { ors: {add, edit, approve}, burs: {...} }
	const lookups = window.__obl_lookups || {};
	const rows = window.__obl_data || [];       // ORS and BURS together, each with its type
	const types = window.__obl_types || [];     // forms this user may open

	// The two forms. Each keeps its own tables and endpoints (myors / myburs).
	const TYPES = {
		ors: { label: 'ORS', route: 'myors', date: 'ors_date', print: 'PRINT-ORS', history: 'ors', section: 'Obligation', particulars: 'TO OBLIGATE PAYMENT ', fc: '01', tone: 'tone-teal' },
		burs: { label: 'BURS', route: 'myburs', date: 'burs_date', print: 'PRINT-BURS', history: 'burs', section: 'Utilization', particulars: '', fc: '07', tone: 'tone-indigo' }
	};

	let table = null;
	let filter = 'all';
	let typeFilter = types.indexOf(window.__obl_show) !== -1 ? window.__obl_show : '';  // '' = both forms
	let record = null;          // record open in the editor (null = new)
	let type = types[0] || 'ors';  // form of the record in the editor

	function permsOf(t) { return perms[t] || {}; }
	function typeChip(t) { return '<span class="obl-type-chip ' + (TYPES[t] || TYPES.ors).tone + '">' + (TYPES[t] || TYPES.ors).label + '</span>'; }

	// Line-item grids. "payload" is the field ors_save() / burs_save() reads; each row is
	// sent as program x|x project x|x resp. code x|x MFO/PAP x|x sub-object x|x UACS x|x amount
	const GRIDS = {
		ps_direct:     { title: 'Direct cost', sub: 'Personnel Services', payload: 'budgetdtdata', lookup: 'psuacs', tab: 'ps' },
		ps_indirect:   { title: 'Indirect cost', sub: 'Personnel Services', payload: 'budgetdtindirectdata', lookup: 'psuacs', tab: 'ps' },
		mooe_direct:   { title: 'Direct cost', sub: 'Maintenance and Other Operating Expenses', payload: 'budgetmooedtdata', lookup: 'mooeuacs', tab: 'mooe' },
		mooe_indirect: { title: 'Indirect cost', sub: 'Maintenance and Other Operating Expenses', payload: 'budgetmooeindirectdtdata', lookup: 'mooeuacs', tab: 'mooe' },
		co_direct:     { title: 'Direct cost', sub: 'Capital Outlay', payload: 'budgetcodtdata', lookup: 'couacs', tab: 'co' },
		co_indirect:   { title: 'Indirect cost', sub: 'Capital Outlay', payload: 'budgetindirectcodtdata', lookup: 'couacs', tab: 'co' }
	};

	const STATUS = {
		pending_a: ['tone-amber', 'For approval · Box A (Requesting office)'],
		pending_b: ['tone-indigo', 'For approval · Box B (Budget)'],
		approved: ['tone-green', 'Approved'],
		disapproved: ['tone-red', 'Returned']
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
		var s = STATUS[status] || STATUS.pending_a;
		return '<span class="fods-chip ' + s[0] + '"><span class="fods-dot"></span>' + s[1] + '</span>';
	}

	// Box A certifies first, then Box B (same rule as MyOrs::statusOf)
	function statusOf(hd) {
		if (String(hd.is_disapproved_certa) === '1' || String(hd.is_disapproved_certb) === '1') { return 'disapproved'; }
		if (String(hd.is_approved_certb) === '1') { return 'approved'; }
		if (String(hd.is_approved_certa) === '1') { return 'pending_b'; }
		return 'pending_a';
	}

	//OBLIGATIONS - YEAR AND FORM (the cards and the list follow the year picked in the page head and the ORS / BURS pill)
	const YEAR_KEY = 'fods.year.myobligation';
	let year = String(new Date().getFullYear());
	try { year = localStorage.getItem(YEAR_KEY) || year; } catch (e) { /* storage blocked */ }

	// A record's year is its ORS / BURS date, or when it was encoded if that date is blank
	function yearOf(r) {
		var y = String(r.doc_date || '').slice(0, 4);
		return +y >= 2000 ? y : String(r.added_at || '').slice(0, 4);
	}

	function inYear(r) { return year === 'all' || yearOf(r) === year; }
	function inType(r) { return !typeFilter || r.type === typeFilter; }

	// Statuses behind each pill / card (any other key is a single status)
	const GROUPS = { pending: ['pending_a', 'pending_b'] };
	function inGroup(r, key) { return key === 'all' || (GROUPS[key] || [key]).indexOf(r.status) !== -1; }

	//OBLIGATIONS - STAT CARDS: For approval, Returned, Approved, Total amount
	function renderCards() {
		var $ = window.jQuery;
		var list = rows.filter(function (r) { return inYear(r) && inType(r); });
		var amount = function (l) { return l.reduce(function (t, r) { return t + num(r.amount); }, 0); };
		var count = function (status) { return list.filter(function (r) { return r.status === status; }).length; };
		var card = function (key, group, label) {
			var l = list.filter(function (r) { return inGroup(r, group); });
			$('[data-card-value="' + key + '"]').text(l.length.toLocaleString());
			$('[data-card-label="' + key + '"]').text(label() + ' · ' + money(amount(l)));
		};
		card('pending', 'pending', function () { return 'For approval · Box A ' + count('pending_a').toLocaleString() + ' · Box B ' + count('pending_b').toLocaleString(); });
		card('returned', 'disapproved', function () { return 'Returned'; });
		card('approved', 'approved', function () { return 'Approved'; });
		$('[data-card-value="total"]').text(money(amount(list)));
		var nOf = function (t) { return list.filter(function (r) { return r.type === t; }).length.toLocaleString(); };
		$('[data-card-label="total"]').text('Total amount · ' + (typeFilter ? list.length.toLocaleString() + ' ' + TYPES[typeFilter].label
			: (types.length > 1 ? nOf('ors') + ' ORS · ' + nOf('burs') + ' BURS' : list.length.toLocaleString() + ' ' + TYPES[types[0] || 'ors'].label)));
	}

	function yearPicker(onChange) {
		var sel = document.getElementById('oblYear');
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

	//OBLIGATIONS - LIST (search, form and status pills, stat tiles)
	this.__obl_table = function () {
		var $ = window.jQuery;

		$.fn.dataTable.ext.search.push(function (settings, data, idx) {
			if (settings.nTable.id !== 'oblTable') { return true; }
			var row = settings.aoData[idx]._aData;
			return inYear(row) && inType(row) && inGroup(row, filter);
		});

		table = $('#oblTable').DataTable({
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
					data: 'particulars', className: 'cell-primary',
					render: function (v, type, r) {
						if (type !== 'display') { return TYPES[r.type].label + ' ' + (r.serialno || '') + ' ' + (v || '') + ' ' + (r.payee_name || '') + ' ' + (r.dvno || '') + ' ' + (r.funding_source || ''); }
						return '<div style="min-width:0"><div class="fw-semibold text-dark">' + typeChip(r.type) + esc(r.serialno || 'No serial no.') + '</div>'
							+ '<div class="small text-muted" style="overflow-wrap:anywhere">' + esc(v) + '</div>'
							+ '<div class="small text-muted d-md-none">' + esc(r.payee_name) + '</div></div>';
					}
				},
				{ data: 'payee_name', className: 'd-none d-md-table-cell', render: function (v, type) { return type === 'display' ? '<span style="overflow-wrap:anywhere">' + esc(v || '—') + '</span>' : (v || ''); } },
				{ data: 'doc_date', className: 'd-none d-lg-table-cell text-nowrap', render: function (v, type) { return type === 'display' ? fmtDate(v) : (v || ''); } },
				{
					data: 'amount', className: 'text-end cell-left',
					render: function (v, type) { return type === 'display' ? '<span class="text-nowrap fw-semibold">' + money(v) + '</span>' : num(v); }
				},
				{
					data: 'status', className: 'cell-right',
					render: function (v, type) { return type === 'display' ? chip(v) : (STATUS[v] || STATUS.pending_a)[1]; }
				},
				{
					data: 'added_at', className: 'd-none d-xl-table-cell text-muted small',
					render: function (v, type, r) { return type === 'display' ? fmtDate(v) + (r.added_by ? '<br>' + esc(r.added_by) : '') : (v || ''); }
				},
				{
					data: null, orderable: false, searchable: false, className: 'cell-actions text-end',
					render: function (v, type, r) {
						var editable = permsOf(r.type).edit && (r.status === 'pending_a' || r.status === 'disapproved');
						var at = ' data-type="' + r.type + '" data-recid="' + r.recid + '"';
						return '<div class="d-flex justify-content-end gap-1">'
							+ '<button type="button" class="fods-icon-btn is-teal obl-edit"' + at + ' title="' + (editable ? 'Edit' : 'View') + '"><i class="ti ' + (editable ? 'ti-pencil' : 'ti-eye') + '"></i></button>'
							+ '<button type="button" class="fods-icon-btn obl-print"' + at + ' data-kind="FORM" title="Print ' + TYPES[r.type].label + '"><i class="ti ti-printer"></i></button>'
							+ '<button type="button" class="fods-icon-btn obl-print"' + at + ' data-kind="DV" title="Print DV"><i class="ti ti-file-dollar"></i></button>'
							+ '</div>';
					}
				}
			]
		});

		$('#oblTableSearch').on('input', function () { table.search(this.value).draw(); });

		function applyFilter(key) {
			filter = key;
			$('#oblTableFilter button').removeClass('active').filter('[data-filter="' + key + '"]').addClass('active');
			$('.fods-stat[data-table="oblTable"]').removeClass('is-active');
			if (key !== 'all') { $('.fods-stat[data-table="oblTable"][data-filter="' + key + '"]').addClass('is-active'); }
			table.draw();
		}
		$(document).on('click', '#oblTableFilter button', function () { applyFilter($(this).data('filter')); });
		$(document).on('click', '.fods-stat[data-table="oblTable"]', function () { applyFilter($(this).data('filter')); });

		// ORS and BURS / ORS / BURS
		function applyType(t) {
			typeFilter = t;
			$('#oblTypeFilter button').removeClass('active').filter(function () { return String($(this).data('type') || '') === t; }).addClass('active');
			renderCards();
			table.draw();
		}
		$(document).on('click', '#oblTypeFilter button', function () { applyType(String($(this).data('type') || '')); });
		applyType(typeFilter);

		yearPicker(function () { renderCards(); table.draw(); });
		renderCards();
		table.draw();

		$(document).on('click', '#oblTableAdd', function () { __mysys_obl_ent.__open_obl(newType(), null); });
		$(document).on('click', '#oblTable .obl-edit', function () { __mysys_obl_ent.__open_obl($(this).data('type'), $(this).data('recid')); });
		$(document).on('click', '#oblTable .obl-print', function () { __mysys_obl_ent.__showPdfInModal($(this).data('type'), $(this).data('recid'), $(this).data('kind')); });
	};

	//OBLIGATIONS - LINE-ITEM GRIDS
	function rowHtml(lookup, d) {
		d = d || {};
		var val = function (k) { return esc(d[k] == null ? '' : d[k]); };
		// title= shows the full text of long program / project names on hover
		var text = function (cls, k, extra) {
			return '<td><input type="text" class="' + cls + '" value="' + val(k) + '" title="' + val(k) + '"' + (extra || '') + '></td>';
		};
		return '<tr>'
			+ '<td class="sticky-l"><div class="obl-row-actions">'
			+ '<button type="button" class="fods-icon-btn is-danger obl-remove" title="Remove line"><i class="ti ti-trash"></i></button>'
			+ '<button type="button" class="fods-icon-btn is-teal obl-insert" title="Insert a line below"><i class="ti ti-row-insert-bottom"></i></button>'
			+ '</div></td>'
			+ text('g-program', 'program_title', ' placeholder="Type or pick a program"')
			+ text('g-rc', 'responsibility_code', ' placeholder="Type or pick a code"')
			+ text('g-project', 'project_title', ' readonly tabindex="-1" placeholder="Auto"')
			+ text('g-mfo', 'mfopaps_code', ' readonly tabindex="-1" placeholder="Auto"')
			+ text('g-sub g-lookup', 'sub_object_code', ' data-lookup="' + lookup + '" placeholder="Pick a sub-object code"')
			+ text('g-uacs', 'uacs_code', ' readonly tabindex="-1" placeholder="Auto"')
			+ '<td class="num sticky-r"><input type="number" step="any" class="g-amount" placeholder="0.00" value="' + val('amount') + '"></td>'
			+ '</tr>';
	}

	function gridHtml(key, data) {
		var g = GRIDS[key];
		return '<div class="obl-grid-head">'
			+ '<div><div class="obl-grid-title">' + g.title + ' <span class="obl-grid-sub" data-count></span></div><div class="obl-grid-sub">' + g.sub + '</div></div>'
			+ '<div class="d-flex align-items-center gap-3"><span class="obl-grid-hint d-none" data-hint><i class="ti ti-arrows-horizontal me-1"></i>Scroll sideways for more columns</span>'
			+ '<button type="button" class="btn btn-sm btn-fods-ghost obl-add"><i class="ti ti-plus me-1"></i>Add line</button></div>'
			+ '</div>'
			+ '<div class="obl-grid-wrap"><table class="obl-grid">'
			+ '<colgroup><col class="c-act"><col class="c-program"><col class="c-rc"><col class="c-project"><col class="c-mfo"><col class="c-sub"><col class="c-uacs"><col class="c-amt"></colgroup>'
			+ '<thead><tr><th class="sticky-l"></th><th>Program title</th><th>Responsibility code</th><th>Project title</th><th>MFO/PAP</th><th>Sub-object code</th><th>UACS</th><th class="num sticky-r">Amount</th></tr></thead>'
			+ '<tbody>' + (data || []).map(function (d) { return rowHtml(g.lookup, d); }).join('') + '</tbody>'
			+ '<tfoot><tr><td class="sticky-l">Subtotal</td><td colspan="6"></td><td class="num sticky-r" data-sum></td></tr></tfoot>'
			+ '</table></div>'
			+ '<div class="obl-empty d-none">No lines yet — use <b>Add line</b>.</div>';
	}

	// Editable while waiting for Box A, or after a disapproval (saving resubmits it)
	function canSave() {
		if (!record) { return !!permsOf(type).add; }
		var status = statusOf(record);
		return !!permsOf(type).edit && (status === 'pending_a' || status === 'disapproved');
	}

	function applyLocks(scope) {
		if (!canSave()) { window.jQuery(scope).find('input, select, textarea, .obl-add, .obl-remove, .obl-insert').prop('disabled', true); }
	}

	function recalc() {
		var $ = window.jQuery;
		var tabs = { ps: 0, mooe: 0, co: 0 };

		Object.keys(GRIDS).forEach(function (key) {
			var card = $('.obl-grid-card[data-grid="' + key + '"]');
			var trs = card.find('tbody tr');
			var sum = 0;
			trs.each(function () { sum += num($(this).find('.g-amount').val()); });
			card.find('[data-sum]').text(money(sum));
			card.find('[data-count]').text('· ' + trs.length + ' line' + (trs.length === 1 ? '' : 's'));
			card.find('.obl-empty').toggleClass('d-none', trs.length > 0);
			card.find('.obl-grid-wrap, [data-hint]').toggleClass('d-none', trs.length === 0);
			tabs[GRIDS[key].tab] += sum;
		});

		Object.keys(tabs).forEach(function (t) { $('[data-tab-total="' + t + '"]').text(Math.abs(tabs[t]) >= 0.005 ? money(tabs[t]) : ''); });
		applyTaxRules();
	}

	function renderGrids(grids) {
		var $ = window.jQuery;
		Object.keys(GRIDS).forEach(function (key) {
			var card = $('.obl-grid-card[data-grid="' + key + '"]');
			card.html(gridHtml(key, grids ? grids[key] : []));
			applyLocks(card);
		});
		recalc();
	}

	this.__obl_grids = function () {
		var $ = window.jQuery;
		var modal = $('#oblModal');

		modal.on('click', '.obl-add', function () {
			var card = $(this).closest('.obl-grid-card');
			var tr = $(rowHtml(GRIDS[card.data('grid')].lookup, null)).appendTo(card.find('tbody'));
			recalc();
			tr.find('input:not([readonly])').first().trigger('focus');
		});

		modal.on('click', '.obl-insert', function () {
			var card = $(this).closest('.obl-grid-card');
			var tr = $(rowHtml(GRIDS[card.data('grid')].lookup, null)).insertAfter($(this).closest('tr'));
			recalc();
			tr.find('input:not([readonly])').first().trigger('focus');
		});

		modal.on('click', '.obl-remove', function () {
			$(this).closest('tr').remove();
			recalc();
		});

		modal.on('input', '.g-amount', recalc);

		// keep the hover text in step with values typed or filled by a lookup
		modal.on('change autocompleteclose', '.obl-grid input[type=text]', function () {
			$(this).closest('tr').find('input[type=text]').each(function () { this.title = this.value; });
		});

		// Program title suggestions
		modal.on('focus', '.g-program', function () {
			var input = $(this);
			if (input.data('ui-autocomplete')) { return; }
			input.autocomplete({ source: lookups.programs || [], appendTo: '#oblModal', minLength: 0 });
		});

		function setProject(tr, item) {
			tr.find('.g-project').val(item ? item.title : '');
			tr.find('.g-mfo').val(item ? item.mfo : '');
			tr.find('input[type=text]').each(function () { this.title = this.value; });
		}

		modal.on('focus', '.g-rc', function () {
			var input = $(this);
			if (input.data('ui-autocomplete') || input.prop('readonly')) { return; }
			input.autocomplete({
				appendTo: '#oblModal',
				minLength: 1,
				source: function (req, resp) {
					var terms = req.term.toLowerCase().split(/\s+/).filter(Boolean);
					resp(rcItems().filter(function (it) { return terms.every(function (t) { return it.find.indexOf(t) !== -1; }); }).slice(0, 50));
				},
				select: function (event, ui) {
					input.val(ui.item.value);
					setProject(input.closest('tr'), ui.item);
					return false;
				}
			}).autocomplete('instance')._renderItem = function (ul, item) {
				return $('<li>').append('<div><b>' + esc(item.value) + '</b><span class="obl-ac-sub">' + esc(item.title) + (item.mfo ? ' · MFO/PAP ' + esc(item.mfo) : '') + '</span></div>').appendTo(ul);
			};
		});

		// Typed by hand: fill in when the code has only one project, clear when
		// the code no longer matches the project on the line
		modal.on('change', '.g-rc', function () {
			var tr = $(this).closest('tr');
			var code = this.value.trim();
			var matches = rcItems().filter(function (it) { return it.value === code; });
			var current = tr.find('.g-project').val();
			if (matches.length === 1) { setProject(tr, matches[0]); }
			else if (!matches.some(function (it) { return it.title === current; })) { setProject(tr, null); }
		});

		// Sub-object code fills the UACS code
		modal.on('focus', '.g-lookup', function () {
			var input = $(this);
			if (input.data('ui-autocomplete')) { return; }
			input.autocomplete({
				source: lookups[input.data('lookup')] || [],
				appendTo: '#oblModal',
				minLength: 0,
				select: function (event, ui) {
					input.val(ui.item.value);
					input.closest('tr').find('.g-uacs').val(ui.item.uacs_code);
					return false;
				}
			});
		});
	};

	// Responsibility codes of the form in the editor (ORS: FC 01 projects, BURS: FC 07).
	// One code can cover several projects, so each suggestion is a code + project pair.
	var rcCache = {};
	function rcItems() {
		if (!rcCache[type]) {
			rcCache[type] = ((lookups.projects || {})[type] || []).map(function (p) {
				var title = String(p.project_title || '').replace(/\s+/g, ' ').trim();
				return { label: p.responsibility_code, value: p.responsibility_code, title: title, mfo: p.mfopaps_code || '', find: (p.responsibility_code + ' ' + title).toLowerCase() };
			});
		}
		return rcCache[type];
	}

	//OBLIGATIONS - TAXES AND DEDUCTIONS (same rules as the previous entry form)
	function setAmounts(gross, deduction, net) {
		var $ = window.jQuery;
		$('#gross_amount').val(gross.toFixed(2));
		$('#total_deduction').val(deduction.toFixed(2));
		$('#net_amount').val(net.toFixed(2));
		$('#oblGross, #oblFootGross').text(money(gross));
		$('#oblDeduction, #oblFootDeduction').text(money(deduction));
		$('#oblNet, #oblFootNet').text(money(net));
	}

	function clear(ids) { ids.forEach(function (id) { document.getElementById(id).value = ''; }); }

	var PAYROLL = ['sss', 'hdmf', 'pagibig_mp2', 'pagibig_loan', 'hmo', 'wtax'];

	function applyTaxRules() {
		var $ = window.jQuery;
		var gross = 0;
		$('#oblModal .g-amount').each(function () { gross += num(this.value); });

		var category = $('#category').val();
		var isVat = $('#is_vatable').is(':checked');
		var otherDeductions = num($('#other_deductions').val());
		var incomeTax = num($('#income_tax').val());

		// Keep saved values untouched on a locked (view-only) record
		if (record && !canSave()) {
			setAmounts(gross, num(record.total_deduction), num(record.net_amount) || gross - num(record.total_deduction));
			return;
		}

		// PAYROLL
		if (category === 'Payroll') {
			var payroll = PAYROLL.reduce(function (s, id) { return s + num($('#' + id).val()); }, 0);
			clear(['vat_percent', 'ewt_percent', 'pt_percent', 'other_deductions', 'income_tax']);
			setAmounts(gross, payroll, gross - payroll);
			return;
		}

		// INCOME TAX
		if (category === 'Income Tax') {
			clear(['vat_percent', 'ewt_percent', 'pt_percent', 'other_deductions'].concat(PAYROLL));
			setAmounts(gross, incomeTax, gross - incomeTax);
			return;
		}

		// NO CATEGORY
		if (!category) {
			clear(['vat_percent', 'ewt_percent', 'pt_percent'].concat(PAYROLL));
			setAmounts(gross, otherDeductions + incomeTax, gross - otherDeductions - incomeTax);
			return;
		}

		// GOODS, SERVICES, MANUAL
		clear(PAYROLL);
		var net = 0, vat = 0, ewt = 0, pt = 0;

		if (category === 'Manual') {
			var vatPercent = num($('#vat_percent').val());
			var ewtPercent = num($('#ewt_percent').val());
			var ptPercent = num($('#pt_percent').val());
			if (isVat) {
				net = gross / 1.12;
				vat = net * (vatPercent / 100);
				ewt = net * (ewtPercent / 100);
				clear(['pt_percent']);
			} else {
				pt = gross * (ptPercent / 100);
				ewt = gross * (ewtPercent / 100);
				clear(['vat_percent']);
			}
		} else if (isVat) {
			net = gross / 1.12;
			$('#vat_percent').val(5);
			vat = net * 0.05;
			if (category === 'Goods') { $('#ewt_percent').val(1); ewt = net * 0.01; }
			else if (category === 'Services') { $('#ewt_percent').val(2); ewt = net * 0.02; }
			clear(['pt_percent']);
		} else {
			clear(['vat_percent']);
			if (category === 'Goods') { $('#ewt_percent').val(1); ewt = gross * 0.01; }
			else if (category === 'Services') { $('#ewt_percent').val(2); ewt = gross * 0.02; }
			$('#pt_percent').val(3);
			pt = gross * 0.03;
		}

		var deduction = vat + ewt + pt + otherDeductions + incomeTax;
		setAmounts(gross, deduction, gross - deduction);
	}

	function toggleFields() {
		var $ = window.jQuery;
		var category = $('#category').val();
		var taxed = category === 'Goods' || category === 'Services' || category === 'Manual';
		var isVat = $('#is_vatable').is(':checked');

		$('#oblModal .obl-tax').toggleClass('d-none', !taxed);
		if (taxed) {
			$('#oblModal .obl-vat').toggleClass('d-none', !isVat);
			$('#oblModal .obl-pt').toggleClass('d-none', isVat);
		}
		$('#oblModal .obl-income').toggleClass('d-none', category !== 'Income Tax');
		$('#oblModal .obl-payroll').toggleClass('d-none', category !== 'Payroll');
		$('#oblModal .obl-none').toggleClass('d-none', !!category);
	}

	this.__obl_deductions = function () {
		var $ = window.jQuery;

		$('#is_vatable').on('change', function () {
			var category = $('#category').val();
			if (!category) {
				$(this).prop('checked', false);
				toastr.warning('Select a category first before enabling VAT.', 'Validation', { progressBar: true, closeButton: true, timeOut: 2000 });
				return;
			}
			toggleFields();
			applyTaxRules();
		});

		$('#category').on('change', function () {
			var category = $(this).val();
			if (category === 'Payroll' || category === 'Income Tax') { $('#is_vatable').prop('checked', false); }
			toggleFields();
			applyTaxRules();
		});

		$('#oblModal').on('input', '#vat_percent, #ewt_percent, #pt_percent, #other_deductions, #income_tax, #sss, #hdmf, #pagibig_mp2, #pagibig_loan, #hmo, #wtax', applyTaxRules);
	};

	//OBLIGATIONS - DETAILS (payee lookup, signatories)
	var payeeIndex = {};

	function fillPayee(name) {
		var p = payeeIndex[String(name || '').trim().toLowerCase()];
		if (p) {
			document.getElementById('payee_office').value = p.office || '';
			document.getElementById('payee_address').value = p.address || '';
		}
		return p;
	}

	function options(id, list, current, extra) {
		var sel = document.getElementById(id);
		var html = '<option value="">Choose...</option>';
		var seen = false;
		list.forEach(function (o) {
			var v = typeof o === 'string' ? o : o.full_name;
			if (v === current) { seen = true; }
			html += '<option value="' + esc(v) + '"' + (o.position != null ? ' data-position="' + esc(o.position) + '"' : '') + '>' + esc(v) + '</option>';
		});
		// keep a saved value that is no longer in the list
		if (current && !seen) {
			html += '<option value="' + esc(current) + '"' + (extra != null ? ' data-position="' + esc(extra) + '"' : '') + '>' + esc(current) + '</option>';
		}
		sel.innerHTML = html;
		sel.value = current || '';
	}

	// A box's signatories (this form's, from Approver Mapping) with the saved one; a new record starts with the box's only person
	function signatory(id, posId, list, hd) {
		var def = (list || []).filter(function (o) { return String(o.is_default) === '1'; })[0];
		options(id, list || [], hd ? hd[id] : (def ? def.full_name : ''), hd ? hd[posId] : '');
		if (!hd) { setVal(posId, def ? def.position : ''); }
	}

	this.__obl_details = function () {
		var $ = window.jQuery;
		(lookups.payees || []).forEach(function (p) { payeeIndex[String(p.value).trim().toLowerCase()] = p; });

		$('#payee_name').autocomplete({
			appendTo: '#oblModal',
			minLength: 1,
			source: function (req, resp) {
				var term = req.term.toLowerCase();
				var out = [];
				var list = lookups.payees || [];
				for (var i = 0; i < list.length && out.length < 40; i++) {
					if (String(list[i].value).toLowerCase().indexOf(term) !== -1) { out.push(list[i]); }
				}
				resp(out);
			},
			select: function (event, ui) {
				$('#payee_name').val(ui.item.value).removeClass('is-invalid');
				$('#payee_office').val(ui.item.office || '');
				$('#payee_address').val(ui.item.address || '');
				return false;
			}
		}).autocomplete('instance')._renderItem = function (ul, item) {
			return $('<li>').append('<div>' + esc(item.value) + (item.office ? '<span class="obl-ac-sub">' + esc(item.office) + '</span>' : '') + '</div>').appendTo(ul);
		};
		$('#payee_name').on('change', function () {
			if (!fillPayee(this.value)) { $('#payee_office, #payee_address').val(''); }
		});

		$('#certified_a').on('change', function () { $('#position_a').val($(this).find(':selected').data('position') || ''); });
		$('#certified_b').on('change', function () { $('#position_b').val($(this).find(':selected').data('position') || ''); });
		$('#oblType').on('change', 'input', function () { switchType(this.value); });
	};

	//OBLIGATIONS - FORM TYPE (ORS or BURS: picked on a new record, fixed once saved)
	const TYPE_KEY = 'fods.obl.type';

	// Form a new record starts with: the one shown in the list, else the last one used
	function newType() {
		var ok = function (t) { return t && types.indexOf(t) !== -1 && permsOf(t).add; };
		if (ok(typeFilter)) { return typeFilter; }
		var last = '';
		try { last = localStorage.getItem(TYPE_KEY) || ''; } catch (e) { /* storage blocked */ }
		if (ok(last)) { return last; }
		return types.filter(ok)[0] || types[0];
	}

	// Labels, signatory lists, history and print follow the form
	function setType(t) {
		var $ = window.jQuery;
		type = t;
		var T = TYPES[t];
		$('#oblType input').each(function () {
			this.checked = this.value === t;
			this.disabled = !!record || !permsOf(this.value).add;
		});
		$('#oblSecTitle').text(T.section);
		$('#oblSerialLabel').text(T.label + ' serial no.');
		$('#oblDateLabel').text(T.label + ' date');
		$('#oblPrintLabel').text(T.label);
		$('#oblHistBtn').attr('data-history-module', T.history);
	}

	// A new record switched to the other form
	function switchType(t) {
		var $ = window.jQuery;
		if (record || t === type || !TYPES[t] || !permsOf(t).add) { setType(type); return; }
		var before = TYPES[type].particulars;
		var part = document.getElementById('particulars');
		setType(t);
		try { localStorage.setItem(TYPE_KEY, t); } catch (e) { /* storage blocked */ }
		// the default particulars follow the form unless something else was typed
		if (part.value === before || !part.value.trim()) { part.value = TYPES[t].particulars; }
		setVal('fund_cluster_code', TYPES[t].fc);
		signatory('certified_a', 'position_a', (lookups.certifya || {})[t], null);
		signatory('certified_b', 'position_b', (lookups.certifyb || {})[t], null);
		// lines keep their code; a project from the other fund cluster is cleared
		$('#oblModal .g-rc').each(function () { if (this.value.trim()) { $(this).trigger('change'); } });
		$('#oblModal .g-rc').each(function () { var a = $(this).data('ui-autocomplete'); if (a) { $(this).autocomplete('destroy'); } });
		setHeader(null);
	}

	//OBLIGATIONS - OPEN EDITOR (new, or load one record with its line items)
	function setVal(id, v) { document.getElementById(id).value = v == null ? '' : v; }

	function fill(hd) {
		var form = document.getElementById('oblForm');
		form.reset();
		form.classList.remove('was-validated');
		document.getElementById('payee_name').classList.remove('is-invalid');

		['recid', 'serialno', 'particulars', 'payee_name', 'payee_office', 'payee_address', 'position_a', 'position_b', 'dvno',
			'deduction_name'].forEach(function (f) { setVal(f, hd ? hd[f] : ''); });

		// saved zeros show as blanks, like the previous form
		['vat_percent', 'ewt_percent', 'pt_percent', 'other_deductions', 'income_tax'].concat(PAYROLL).forEach(function (f) {
			setVal(f, hd && num(hd[f]) ? hd[f] : '');
		});

		var d = hd ? String(hd[TYPES[type].date] || '') : '';
		setVal('doc_date', hd ? (d.indexOf('0000') === 0 ? '' : d) : new Date().toISOString().slice(0, 10));
		if (!hd) { setVal('particulars', TYPES[type].particulars); }

		options('funding_source', lookups.funding_sources || [], hd ? hd.funding_source : '');
		options('fund_cluster_code', lookups.fund_clusters || [], hd ? hd.fund_cluster_code : '');
		options('category', lookups.categories || [], hd ? hd.category : '');
		signatory('certified_a', 'position_a', (lookups.certifya || {})[type], hd);
		signatory('certified_b', 'position_b', (lookups.certifyb || {})[type], hd);
		document.getElementById('is_vatable').checked = !!hd && String(hd.is_vatable) === '1';

		toggleFields();
	}

	function signStatus(box, hd) {
		var $ = window.jQuery;
		var approved = hd && String(hd['is_approved_cert' + box]) === '1';
		var disapproved = hd && String(hd['is_disapproved_cert' + box]) === '1';
		var by = hd ? hd['cert' + box + '_approver'] : '';
		var remarks = hd ? hd['cert' + box + '_remarks'] : '';
		var html, note = '';

		if (approved) { html = '<span class="fods-chip tone-green"><i class="ti ti-check"></i>Certified</span>'; }
		else if (disapproved) { html = '<span class="fods-chip tone-red"><i class="ti ti-x"></i>Disapproved</span>'; }
		else if (!hd) { html = ''; }
		else if (box === 'b' && String(hd.is_approved_certa) !== '1') { html = '<span class="fods-chip tone-slate">After Box A</span>'; }
		else { html = '<span class="fods-chip tone-amber"><span class="fods-dot"></span>Waiting</span>'; }

		if (approved || disapproved) { note = 'By ' + esc(by || '—') + (remarks ? ' — ' + esc(remarks) : ''); }
		$('#oblSignStatus' + box.toUpperCase()).html(html);
		$('#oblSignNote' + box.toUpperCase()).html(note);
	}

	function setHeader(hd) {
		var $ = window.jQuery;
		var status = hd ? statusOf(hd) : null;

		var L = TYPES[type].label;
		$('#oblModalTitle').text(hd ? L + ' ' + (hd.serialno || '#' + hd.recid) : 'New ' + L);
		$('#oblModalSub').html(hd
			? esc(hd.payee_name) + ' &nbsp;' + chip(status)
			: 'Fill in the payee and deductions, then the line items.');

		$('#oblPrintBtn, #oblPrintDvBtn').toggleClass('d-none', !hd);

		var banner = $('#oblBanner').removeClass('alert-success alert-danger alert-info d-none');
		if (status === 'pending_b') {
			banner.addClass('alert-info').html('<b>Certified by the requesting office</b>' + (hd.certa_approver ? ' (' + esc(hd.certa_approver) + ')' : '')
				+ '; pending certification by budget (Box B). It can no longer be edited.');
		} else if (status === 'approved') {
			banner.addClass('alert-success').html('<b>Fully certified</b> — Box A by ' + esc(hd.certa_approver || '—') + ', Box B by ' + esc(hd.certb_approver || '—') + '.');
		} else if (status === 'disapproved') {
			var box = String(hd.is_disapproved_certa) === '1' ? 'a' : 'b';
			banner.addClass('alert-danger').html('<b>Disapproved at Box ' + box.toUpperCase() + '</b>' + (hd['cert' + box + '_approver'] ? ' by ' + esc(hd['cert' + box + '_approver']) : '')
				+ (hd['cert' + box + '_remarks'] ? ' — ' + esc(hd['cert' + box + '_remarks']) : '')
				+ (canSave() ? '<div class="small mt-1">Saving your corrections sends it back for certification.</div>' : ''));
		} else {
			banner.addClass('d-none');
		}

		signStatus('a', hd);
		signStatus('b', hd);

		// Certify / disapprove the box it is at (one Approve right covers Box A and Box B)
		var box = decideBox(hd);
		$('#oblApproveBtn, #oblDisapproveBtn').toggleClass('d-none', !box);
		$('#oblApproveBtn').html('<i class="ti ti-check me-1"></i>Certify Box ' + box.toUpperCase());

		$('#submitBtn').toggleClass('d-none', !canSave())
			.text(!hd ? 'Create ' + L : (status === 'disapproved' ? 'Save & resubmit' : 'Save changes'));
	}

	// Box this user can certify now ('' when none)
	function decideBox(hd) {
		var status = hd ? statusOf(hd) : '';
		return permsOf(type).approve && (status === 'pending_a' || status === 'pending_b') ? status.slice(-1) : '';
	}

	//OBLIGATIONS - CERTIFY / DISAPPROVE (remarks required to disapprove)
	this.__obl_decision = function () {
		var $ = window.jQuery;
		// Box => [step, the one after it]
		var STEPS = {
			A: ['Box A · Requesting office', 'Box B · Budget'],
			B: ['Box B · Budget', 'Certified']
		};

		function openDecision(mode) {
			var box = decideBox(record);
			if (!box) { return; }
			var approve = mode === 'approve';
			var B = box.toUpperCase();
			FodsDecision.open({
				host: document.getElementById('oblModal'),
				mode: mode,
				verb: 'Certify',
				title: (approve ? 'Certify' : 'Disapprove') + ' ' + TYPES[type].label + ' · Box ' + B,
				subtitle: TYPES[type].label + ' ' + (record.serialno || '#' + record.recid) + ' · ' + (record.payee_name || ''),
				facts: [
					['Amount', $('#oblGross').text(), true],
					['Step', (STEPS[B] || ['Box ' + B])[0]],
					['After this', approve ? (STEPS[B] || ['', ''])[1] : 'Returned to the preparer']
				],
				maxlength: 100,
				submit: function (remarks, done) {
					$.ajax({
						type: 'POST',
						url: mesiteurl + TYPES[type].route,
						data: {
							recid: record.recid,
							remarks: remarks,
							meaction: (approve ? 'MAIN-APPROVE-' : 'MAIN-DISAPPROVE-') + B
						},
						global: false,
						cache: false,
						success: function (data) { $('.me-outp-msg').html(data); FodsDecision.close(); },
						error: function (xhr, status, error) { toastr.error('[' + TYPES[type].label + ']', 'Error loading page...' + error, { closeButton: true }); },
						complete: done
					});
				}
			});
		}

		document.getElementById('oblApproveBtn').addEventListener('click', function () { openDecision('approve'); });
		document.getElementById('oblDisapproveBtn').addEventListener('click', function () { openDecision('disapprove'); });
	};

	this.__open_obl = function (t, recid) {
		var $ = window.jQuery;
		var modalEl = document.getElementById('oblModal');
		if (!TYPES[t] || types.indexOf(t) === -1) { return; }
		bootstrap.Tab.getOrCreateInstance(document.querySelector('[data-bs-target="#oblPaneDetails"]')).show();

		function show(hd) {
			record = hd;
			type = t;         // fill() reads the form; setType() after it, since fill() resets the form (and the form radios)
			fill(hd);
			setType(t);
			if (!hd) { setVal('fund_cluster_code', TYPES[t].fc); }
			setHeader(hd);
			renderGrids(hd ? hd.grids : null);
			applyLocks('#oblPaneDetails, #oblPaneSign');
			if (canSave()) { $('#oblPaneDetails, #oblPaneSign').find('input, select, textarea').not('[readonly], [name="obl_type"]').prop('disabled', false); }
			bootstrap.Modal.getOrCreateInstance(modalEl).show();
		}

		if (!recid) { show(null); return; }

		$.getJSON(mesiteurl + TYPES[t].route, { meaction: 'MAIN-LOAD', recid: recid })
			.done(show)
			.fail(function (xhr) {
				toastr.error((xhr.responseJSON && xhr.responseJSON.error) || 'Could not load this ' + TYPES[t].label + '.', 'Oops!', { closeButton: true });
			});
	};

	//OBLIGATIONS - SAVE (same payload as before: header fields + 6 grids of "x|x" rows, posted to the form's own endpoint)
	function collectGrid(key) {
		var $ = window.jQuery;
		var out = [];
		$('.obl-grid-card[data-grid="' + key + '"] tbody tr').each(function () {
			var tr = $(this);
			var v = function (cls) { var x = tr.find('.g-' + cls).val(); return x == null ? '' : x; };
			var blank = ['program', 'project', 'sub', 'uacs'].every(function (c) { return !v(c).trim(); }) && num(v('amount')) === 0;
			if (blank) { return; }  // skip untouched lines instead of saving empty rows
			out.push([v('program'), v('project'), v('rc'), v('mfo'), v('sub'), v('uacs'), v('amount')].join('x|x'));
		});
		return out;
	}

	this.__obl_saving = function () {
		var $ = window.jQuery;
		var form = document.getElementById('oblForm');
		form.addEventListener('submit', function (e) {
			e.preventDefault();
			if (!canSave()) { return; }  // view-only: no rights for this form, or already certified
			var payee = document.getElementById('payee_name');
			var knownPayee = !!fillPayee(payee.value) || (record && payee.value === record.payee_name);
			payee.classList.toggle('is-invalid', !!payee.value && !knownPayee);
			if (!form.checkValidity() || !knownPayee) {
				form.classList.add('was-validated');
				bootstrap.Tab.getOrCreateInstance(document.querySelector('[data-bs-target="#oblPaneDetails"]')).show();
				if (payee.value && !knownPayee) { payee.nextElementSibling.textContent = 'Pick a payee from the list.'; }
				else { payee.nextElementSibling.textContent = 'Payee is required.'; }
				var bad = form.querySelector(':invalid, .is-invalid');
				if (bad) { bad.focus(); }
				return;
			}
			recalc();

			var val = function (id) { return document.getElementById(id).value; };
			var mparam = {
				recid: val('recid'),
				serialno: val('serialno'),
				particulars: val('particulars'),
				funding_source: val('funding_source'),
				payee_name: val('payee_name'),
				payee_office: val('payee_office'),
				payee_address: val('payee_address'),
				certified_a: val('certified_a'),
				position_a: val('position_a'),
				certified_b: val('certified_b'),
				position_b: val('position_b'),
				category: val('category'),
				vat_percent: val('vat_percent'),
				ewt_percent: val('ewt_percent'),
				pt_percent: val('pt_percent'),
				gross_amount: val('gross_amount'),
				total_deduction: val('total_deduction'),
				net_amount: val('net_amount'),
				other_deductions: val('other_deductions'),
				income_tax: val('income_tax'),
				deduction_name: val('deduction_name'),
				sss: val('sss'),
				hdmf: val('hdmf'),
				pagibig_mp2: val('pagibig_mp2'),
				pagibig_loan: val('pagibig_loan'),
				hmo: val('hmo'),
				wtax: val('wtax'),
				fund_cluster_code: val('fund_cluster_code'),
				is_vatable: document.getElementById('is_vatable').checked ? 1 : 0,
				meaction: 'MAIN-SAVE'
			};
			mparam[TYPES[type].date] = val('doc_date');
			Object.keys(GRIDS).forEach(function (key) { mparam[GRIDS[key].payload] = collectGrid(key); });

			var $btn = $('#submitBtn'), label = $btn.html();
			$btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Saving...');
			$.ajax({
				type: 'POST',
				url: mesiteurl + TYPES[type].route,
				data: mparam,
				global: false,
				cache: false,
				success: function (data) { $('.me-outp-msg').html(data); },
				error: function (xhr, status, error) { toastr.error('[' + TYPES[type].label + ']', 'Error loading page...' + error, { closeButton: true }); },
				complete: function () { setTimeout(function () { $btn.prop('disabled', false).html(label); }, 1200); }
			});
		});
	};

	//OBLIGATIONS - PRINT ORS / BURS / DV
	this.__showPdfInModal = function (t, recid, kind) {
		var dv = kind === 'DV';
		var T = TYPES[t] || TYPES.ors;
		document.getElementById('oblPrintTitle').textContent = dv ? 'Disbursement voucher — print preview' : T.label + ' — print preview';
		document.getElementById('oblPrintFrame').src = mesiteurl + T.route + '?meaction=' + (dv ? 'PRINT-DISBURSEMENT' : T.print) + '&recid=' + encodeURIComponent(recid);
		bootstrap.Modal.getOrCreateInstance(document.getElementById('oblPrintModal')).show();
	};

	this.__obl_print = function () {
		document.getElementById('oblPrintBtn').addEventListener('click', function () { if (record) { __mysys_obl_ent.__showPdfInModal(type, record.recid, 'FORM'); } });
		document.getElementById('oblPrintDvBtn').addEventListener('click', function () { if (record) { __mysys_obl_ent.__showPdfInModal(type, record.recid, 'DV'); } });
	};
}

// jQuery, DataTables, jQuery UI and Bootstrap load in the footer, after this file
document.addEventListener('DOMContentLoaded', function () {
	__mysys_obl_ent.__obl_table();
	__mysys_obl_ent.__obl_grids();
	__mysys_obl_ent.__obl_deductions();
	__mysys_obl_ent.__obl_details();
	__mysys_obl_ent.__obl_saving();
	__mysys_obl_ent.__obl_print();
	__mysys_obl_ent.__obl_decision();

	// Deep link (old ORS / BURS and approval links redirect here): open that record right away
	if (window.__obl_open && window.__obl_open.recid) { __mysys_obl_ent.__open_obl(window.__obl_open.type, window.__obl_open.recid); }
});
