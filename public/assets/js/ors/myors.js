var __mysys_ors_ent = new __mysys_ors_ent();
function __mysys_ors_ent() {
	const siteEl = document.getElementById('__siteurl');
	const mesiteurl = siteEl.getAttribute('data-mesiteurl');
	const perms = window.__ors_perms || {};
	const lookups = window.__ors_lookups || {};
	const rows = window.__ors_data || [];

	let table = null;
	let filter = 'all';
	let record = null;          // ORS open in the editor (null = new)

	// Line-item grids. "payload" is the field ors_save() reads; each row is
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
		pending_a: ['tone-amber', 'Requesting office'],
		pending_b: ['tone-indigo', 'Budget review'],
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

	//ORS - YEAR (the cards and the list follow the year picked in the page head)
	const YEAR_KEY = 'fods.year.myors';
	let year = String(new Date().getFullYear());
	try { year = localStorage.getItem(YEAR_KEY) || year; } catch (e) { /* storage blocked */ }

	// An ORS's year is its ORS date, or when it was encoded if that date is blank
	function yearOf(r) {
		var y = String(r.ors_date || '').slice(0, 4);
		return +y >= 2000 ? y : String(r.added_at || '').slice(0, 4);
	}

	function inYear(r) { return year === 'all' || yearOf(r) === year; }

	// Statuses behind each pill / card (any other key is a single status)
	const GROUPS = { pending: ['pending_a', 'pending_b'] };
	function inGroup(r, key) { return key === 'all' || (GROUPS[key] || [key]).indexOf(r.status) !== -1; }

	//ORS - STAT CARDS: Pending, Returned, Approved, Total amount
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
		card('pending', 'pending', function () { return 'Pending · Box A ' + count('pending_a').toLocaleString() + ' · Box B ' + count('pending_b').toLocaleString(); });
		card('returned', 'disapproved', function () { return 'Returned'; });
		card('approved', 'approved', function () { return 'Approved'; });
		$('[data-card-value="total"]').text(money(amount(list)));
		$('[data-card-label="total"]').text('Total amount · ' + list.length.toLocaleString() + ' ORS' + (list.length === 1 ? '' : '') + '');
	}

	function yearPicker(onChange) {
		var sel = document.getElementById('orsYear');
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

	//ORS - LIST (search, status pills, stat tiles)
	this.__ors_table = function () {
		var $ = window.jQuery;

		$.fn.dataTable.ext.search.push(function (settings, data, idx) {
			if (settings.nTable.id !== 'orsTable') { return true; }
			var row = settings.aoData[idx]._aData;
			return inYear(row) && inGroup(row, filter);
		});

		table = $('#orsTable').DataTable({
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
						if (type !== 'display') { return (r.serialno || '') + ' ' + (v || '') + ' ' + (r.payee_name || '') + ' ' + (r.dvno || '') + ' ' + (r.funding_source || ''); }
						return '<div style="min-width:0"><div class="fw-semibold text-dark">' + esc(r.serialno || 'No serial no.') + '</div>'
							+ '<div class="small text-muted" style="overflow-wrap:anywhere">' + esc(v) + '</div>'
							+ '<div class="small text-muted d-md-none">' + esc(r.payee_name) + '</div></div>';
					}
				},
				{ data: 'payee_name', className: 'd-none d-md-table-cell', render: function (v, type) { return type === 'display' ? '<span style="overflow-wrap:anywhere">' + esc(v || '—') + '</span>' : (v || ''); } },
				{ data: 'ors_date', className: 'd-none d-lg-table-cell text-nowrap', render: function (v, type) { return type === 'display' ? fmtDate(v) : (v || ''); } },
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
						var editable = perms.edit && (r.status === 'pending_a' || r.status === 'disapproved');
						return '<div class="d-flex justify-content-end gap-1">'
							+ '<button type="button" class="fods-icon-btn is-teal ors-edit" data-recid="' + r.recid + '" title="' + (editable ? 'Edit' : 'View') + '"><i class="ti ' + (editable ? 'ti-pencil' : 'ti-eye') + '"></i></button>'
							+ '<button type="button" class="fods-icon-btn ors-print" data-recid="' + r.recid + '" data-kind="ORS" title="Print ORS"><i class="ti ti-printer"></i></button>'
							+ '<button type="button" class="fods-icon-btn ors-print" data-recid="' + r.recid + '" data-kind="DV" title="Print DV"><i class="ti ti-file-dollar"></i></button>'
							+ '</div>';
					}
				}
			]
		});

		$('#orsTableSearch').on('input', function () { table.search(this.value).draw(); });

		function applyFilter(key) {
			filter = key;
			$('#orsTableFilter button').removeClass('active').filter('[data-filter="' + key + '"]').addClass('active');
			$('.fods-stat[data-table="orsTable"]').removeClass('is-active');
			if (key !== 'all') { $('.fods-stat[data-table="orsTable"][data-filter="' + key + '"]').addClass('is-active'); }
			table.draw();
		}
		$(document).on('click', '#orsTableFilter button', function () { applyFilter($(this).data('filter')); });
		$(document).on('click', '.fods-stat[data-table="orsTable"]', function () { applyFilter($(this).data('filter')); });

		yearPicker(function () { renderCards(); table.draw(); });
		renderCards();
		table.draw();

		$(document).on('click', '#orsTableAdd', function () { __mysys_ors_ent.__open_ors(null); });
		$(document).on('click', '#orsTable .ors-edit', function () { __mysys_ors_ent.__open_ors($(this).data('recid')); });
		$(document).on('click', '#orsTable .ors-print', function () { __mysys_ors_ent.__showPdfInModal($(this).data('recid'), $(this).data('kind')); });
	};

	//ORS - LINE-ITEM GRIDS
	function rowHtml(lookup, d) {
		d = d || {};
		var val = function (k) { return esc(d[k] == null ? '' : d[k]); };
		// title= shows the full text of long program / project names on hover
		var text = function (cls, k, extra) {
			return '<td><input type="text" class="' + cls + '" value="' + val(k) + '" title="' + val(k) + '"' + (extra || '') + '></td>';
		};
		return '<tr>'
			+ '<td class="sticky-l"><div class="ors-row-actions">'
			+ '<button type="button" class="fods-icon-btn is-danger ors-remove" title="Remove line"><i class="ti ti-trash"></i></button>'
			+ '<button type="button" class="fods-icon-btn is-teal ors-insert" title="Insert a line below"><i class="ti ti-row-insert-bottom"></i></button>'
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
		return '<div class="ors-grid-head">'
			+ '<div><div class="ors-grid-title">' + g.title + ' <span class="ors-grid-sub" data-count></span></div><div class="ors-grid-sub">' + g.sub + '</div></div>'
			+ '<div class="d-flex align-items-center gap-3"><span class="ors-grid-hint d-none" data-hint><i class="ti ti-arrows-horizontal me-1"></i>Scroll sideways for more columns</span>'
			+ '<button type="button" class="btn btn-sm btn-fods-ghost ors-add"><i class="ti ti-plus me-1"></i>Add line</button></div>'
			+ '</div>'
			+ '<div class="ors-grid-wrap"><table class="ors-grid">'
			+ '<colgroup><col class="c-act"><col class="c-program"><col class="c-rc"><col class="c-project"><col class="c-mfo"><col class="c-sub"><col class="c-uacs"><col class="c-amt"></colgroup>'
			+ '<thead><tr><th class="sticky-l"></th><th>Program title</th><th>Responsibility code</th><th>Project title</th><th>MFO/PAP</th><th>Sub-object code</th><th>UACS</th><th class="num sticky-r">Amount</th></tr></thead>'
			+ '<tbody>' + (data || []).map(function (d) { return rowHtml(g.lookup, d); }).join('') + '</tbody>'
			+ '<tfoot><tr><td class="sticky-l">Subtotal</td><td colspan="6"></td><td class="num sticky-r" data-sum></td></tr></tfoot>'
			+ '</table></div>'
			+ '<div class="ors-empty d-none">No lines yet — use <b>Add line</b>.</div>';
	}

	// Editable while waiting for Box A, or after a disapproval (saving resubmits it)
	function canSave() {
		if (!record) { return !!perms.add; }
		var status = statusOf(record);
		return !!perms.edit && (status === 'pending_a' || status === 'disapproved');
	}

	function applyLocks(scope) {
		if (!canSave()) { window.jQuery(scope).find('input, select, textarea, .ors-add, .ors-remove, .ors-insert').prop('disabled', true); }
	}

	function recalc() {
		var $ = window.jQuery;
		var tabs = { ps: 0, mooe: 0, co: 0 };

		Object.keys(GRIDS).forEach(function (key) {
			var card = $('.ors-grid-card[data-grid="' + key + '"]');
			var trs = card.find('tbody tr');
			var sum = 0;
			trs.each(function () { sum += num($(this).find('.g-amount').val()); });
			card.find('[data-sum]').text(money(sum));
			card.find('[data-count]').text('· ' + trs.length + ' line' + (trs.length === 1 ? '' : 's'));
			card.find('.ors-empty').toggleClass('d-none', trs.length > 0);
			card.find('.ors-grid-wrap, [data-hint]').toggleClass('d-none', trs.length === 0);
			tabs[GRIDS[key].tab] += sum;
		});

		Object.keys(tabs).forEach(function (t) { $('[data-tab-total="' + t + '"]').text(Math.abs(tabs[t]) >= 0.005 ? money(tabs[t]) : ''); });
		applyTaxRules();
	}

	function renderGrids(grids) {
		var $ = window.jQuery;
		Object.keys(GRIDS).forEach(function (key) {
			var card = $('.ors-grid-card[data-grid="' + key + '"]');
			card.html(gridHtml(key, grids ? grids[key] : []));
			applyLocks(card);
		});
		recalc();
	}

	this.__ors_grids = function () {
		var $ = window.jQuery;
		var modal = $('#orsModal');

		modal.on('click', '.ors-add', function () {
			var card = $(this).closest('.ors-grid-card');
			var tr = $(rowHtml(GRIDS[card.data('grid')].lookup, null)).appendTo(card.find('tbody'));
			recalc();
			tr.find('input:not([readonly])').first().trigger('focus');
		});

		modal.on('click', '.ors-insert', function () {
			var card = $(this).closest('.ors-grid-card');
			var tr = $(rowHtml(GRIDS[card.data('grid')].lookup, null)).insertAfter($(this).closest('tr'));
			recalc();
			tr.find('input:not([readonly])').first().trigger('focus');
		});

		modal.on('click', '.ors-remove', function () {
			$(this).closest('tr').remove();
			recalc();
		});

		modal.on('input', '.g-amount', recalc);

		// keep the hover text in step with values typed or filled by a lookup
		modal.on('change autocompleteclose', '.ors-grid input[type=text]', function () {
			$(this).closest('tr').find('input[type=text]').each(function () { this.title = this.value; });
		});

		// Program title suggestions
		modal.on('focus', '.g-program', function () {
			var input = $(this);
			if (input.data('ui-autocomplete')) { return; }
			input.autocomplete({ source: lookups.programs || [], appendTo: '#orsModal', minLength: 0 });
		});

		// Responsibility code fills the project title and MFO/PAP. One code can
		// cover several projects, so each suggestion is a code + project pair.
		var rcItems = (lookups.projects || []).map(function (p) {
			var title = String(p.project_title || '').replace(/\s+/g, ' ').trim();
			return { label: p.responsibility_code, value: p.responsibility_code, title: title, mfo: p.mfopaps_code || '', find: (p.responsibility_code + ' ' + title).toLowerCase() };
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
				appendTo: '#orsModal',
				minLength: 1,
				source: function (req, resp) {
					var terms = req.term.toLowerCase().split(/\s+/).filter(Boolean);
					resp(rcItems.filter(function (it) { return terms.every(function (t) { return it.find.indexOf(t) !== -1; }); }).slice(0, 50));
				},
				select: function (event, ui) {
					input.val(ui.item.value);
					setProject(input.closest('tr'), ui.item);
					return false;
				}
			}).autocomplete('instance')._renderItem = function (ul, item) {
				return $('<li>').append('<div><b>' + esc(item.value) + '</b><span class="ors-ac-sub">' + esc(item.title) + (item.mfo ? ' · MFO/PAP ' + esc(item.mfo) : '') + '</span></div>').appendTo(ul);
			};
		});

		// Typed by hand: fill in when the code has only one project, clear when
		// the code no longer matches the project on the line
		modal.on('change', '.g-rc', function () {
			var tr = $(this).closest('tr');
			var code = this.value.trim();
			var matches = rcItems.filter(function (it) { return it.value === code; });
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
				appendTo: '#orsModal',
				minLength: 0,
				select: function (event, ui) {
					input.val(ui.item.value);
					input.closest('tr').find('.g-uacs').val(ui.item.uacs_code);
					return false;
				}
			});
		});
	};

	//ORS - TAXES AND DEDUCTIONS (same rules as the previous entry form)
	function setAmounts(gross, deduction, net) {
		var $ = window.jQuery;
		$('#gross_amount').val(gross.toFixed(2));
		$('#total_deduction').val(deduction.toFixed(2));
		$('#net_amount').val(net.toFixed(2));
		$('#orsGross, #orsFootGross').text(money(gross));
		$('#orsDeduction, #orsFootDeduction').text(money(deduction));
		$('#orsNet, #orsFootNet').text(money(net));
	}

	function clear(ids) { ids.forEach(function (id) { document.getElementById(id).value = ''; }); }

	var PAYROLL = ['sss', 'hdmf', 'pagibig_mp2', 'pagibig_loan', 'hmo', 'wtax'];

	function applyTaxRules() {
		var $ = window.jQuery;
		var gross = 0;
		$('#orsModal .g-amount').each(function () { gross += num(this.value); });

		var category = $('#category').val();
		var isVat = $('#is_vatable').is(':checked');
		var otherDeductions = num($('#other_deductions').val());
		var incomeTax = num($('#income_tax').val());

		// Keep saved values untouched on a locked (view-only) ORS
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

		$('#orsModal .ors-tax').toggleClass('d-none', !taxed);
		if (taxed) {
			$('#orsModal .ors-vat').toggleClass('d-none', !isVat);
			$('#orsModal .ors-pt').toggleClass('d-none', isVat);
		}
		$('#orsModal .ors-income').toggleClass('d-none', category !== 'Income Tax');
		$('#orsModal .ors-payroll').toggleClass('d-none', category !== 'Payroll');
		$('#orsModal .ors-none').toggleClass('d-none', !!category);
	}

	this.__ors_deductions = function () {
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

		$('#orsModal').on('input', '#vat_percent, #ewt_percent, #pt_percent, #other_deductions, #income_tax, #sss, #hdmf, #pagibig_mp2, #pagibig_loan, #hmo, #wtax', applyTaxRules);
	};

	//ORS - DETAILS (payee lookup, signatories)
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

	this.__ors_details = function () {
		var $ = window.jQuery;
		(lookups.payees || []).forEach(function (p) { payeeIndex[String(p.value).trim().toLowerCase()] = p; });

		$('#payee_name').autocomplete({
			appendTo: '#orsModal',
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
			return $('<li>').append('<div>' + esc(item.value) + (item.office ? '<span class="ors-ac-sub">' + esc(item.office) + '</span>' : '') + '</div>').appendTo(ul);
		};
		$('#payee_name').on('change', function () {
			if (!fillPayee(this.value)) { $('#payee_office, #payee_address').val(''); }
		});

		$('#certified_a').on('change', function () { $('#position_a').val($(this).find(':selected').data('position') || ''); });
		$('#certified_b').on('change', function () { $('#position_b').val($(this).find(':selected').data('position') || ''); });
	};

	//ORS - OPEN EDITOR (new, or load one ORS with its line items)
	function setVal(id, v) { document.getElementById(id).value = v == null ? '' : v; }

	function fill(hd) {
		var form = document.getElementById('orsForm');
		form.reset();
		form.classList.remove('was-validated');
		document.getElementById('payee_name').classList.remove('is-invalid');

		['recid', 'serialno', 'particulars', 'payee_name', 'payee_office', 'payee_address', 'position_a', 'position_b', 'dvno',
			'deduction_name'].forEach(function (f) { setVal(f, hd ? hd[f] : ''); });

		// saved zeros show as blanks, like the previous form
		['vat_percent', 'ewt_percent', 'pt_percent', 'other_deductions', 'income_tax'].concat(PAYROLL).forEach(function (f) {
			setVal(f, hd && num(hd[f]) ? hd[f] : '');
		});

		setVal('ors_date', hd ? (String(hd.ors_date || '').indexOf('0000') === 0 ? '' : hd.ors_date) : new Date().toISOString().slice(0, 10));
		if (!hd) { setVal('particulars', 'TO OBLIGATE PAYMENT '); }

		options('funding_source', lookups.funding_sources || [], hd ? hd.funding_source : '');
		options('fund_cluster_code', lookups.fund_clusters || [], hd ? hd.fund_cluster_code : '');
		options('category', lookups.categories || [], hd ? hd.category : '');
		options('certified_a', lookups.certifya || [], hd ? hd.certified_a : '', hd ? hd.position_a : '');
		options('certified_b', lookups.certifyb || [], hd ? hd.certified_b : '', hd ? hd.position_b : '');
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
		$('#orsSignStatus' + box.toUpperCase()).html(html);
		$('#orsSignNote' + box.toUpperCase()).html(note);
	}

	function setHeader(hd) {
		var $ = window.jQuery;
		var status = hd ? statusOf(hd) : null;

		$('#orsModalTitle').text(hd ? (hd.serialno || 'ORS #' + hd.recid) : 'New ORS');
		$('#orsModalSub').html(hd
			? esc(hd.payee_name) + ' &nbsp;' + chip(status)
			: 'Fill in the payee and deductions, then the line items.');

		$('#orsPrintBtn, #orsPrintDvBtn').toggleClass('d-none', !hd);

		var banner = $('#orsBanner').removeClass('alert-success alert-danger alert-info d-none');
		if (status === 'pending_b') {
			banner.addClass('alert-info').html('<b>Certified by the requesting office</b>' + (hd.certa_approver ? ' (' + esc(hd.certa_approver) + ')' : '')
				+ ' and waiting for budget. It can no longer be edited.');
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

		$('#submitBtn').toggleClass('d-none', !canSave())
			.text(!hd ? 'Create ORS' : (status === 'disapproved' ? 'Save & resubmit' : 'Save changes'));
	}

	this.__open_ors = function (recid) {
		var $ = window.jQuery;
		var modalEl = document.getElementById('orsModal');
		bootstrap.Tab.getOrCreateInstance(document.querySelector('[data-bs-target="#orsPaneDetails"]')).show();

		function show(hd) {
			record = hd;
			fill(hd);
			setHeader(hd);
			renderGrids(hd ? hd.grids : null);
			applyLocks('#orsPaneDetails, #orsPaneSign');
			if (canSave()) { $('#orsPaneDetails, #orsPaneSign').find('input, select, textarea').not('[readonly]').prop('disabled', false); }
			bootstrap.Modal.getOrCreateInstance(modalEl).show();
		}

		if (!recid) { show(null); return; }

		$.getJSON(mesiteurl + 'myors', { meaction: 'MAIN-LOAD', recid: recid })
			.done(show)
			.fail(function (xhr) {
				toastr.error((xhr.responseJSON && xhr.responseJSON.error) || 'Could not load this ORS.', 'Oops!', { closeButton: true });
			});
	};

	//ORS - SAVE (same payload as before: header fields + 6 grids of "x|x" rows)
	function collectGrid(key) {
		var $ = window.jQuery;
		var out = [];
		$('.ors-grid-card[data-grid="' + key + '"] tbody tr').each(function () {
			var tr = $(this);
			var v = function (cls) { var x = tr.find('.g-' + cls).val(); return x == null ? '' : x; };
			var blank = ['program', 'project', 'sub', 'uacs'].every(function (c) { return !v(c).trim(); }) && num(v('amount')) === 0;
			if (blank) { return; }  // skip untouched lines instead of saving empty rows
			out.push([v('program'), v('project'), v('rc'), v('mfo'), v('sub'), v('uacs'), v('amount')].join('x|x'));
		});
		return out;
	}

	this.__ors_saving = function () {
		var $ = window.jQuery;
		var form = document.getElementById('orsForm');
		form.addEventListener('submit', function (e) {
			e.preventDefault();
			var payee = document.getElementById('payee_name');
			var knownPayee = !!fillPayee(payee.value) || (record && payee.value === record.payee_name);
			payee.classList.toggle('is-invalid', !!payee.value && !knownPayee);
			if (!form.checkValidity() || !knownPayee) {
				form.classList.add('was-validated');
				bootstrap.Tab.getOrCreateInstance(document.querySelector('[data-bs-target="#orsPaneDetails"]')).show();
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
				ors_date: val('ors_date'),
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
			Object.keys(GRIDS).forEach(function (key) { mparam[GRIDS[key].payload] = collectGrid(key); });

			var $btn = $('#submitBtn'), label = $btn.html();
			$btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Saving...');
			$.ajax({
				type: 'POST',
				url: mesiteurl + 'myors',
				data: mparam,
				global: false,
				cache: false,
				success: function (data) { $('.me-outp-msg').html(data); },
				error: function (xhr, status, error) { toastr.error('[ORS]', 'Error loading page...' + error, { closeButton: true }); },
				complete: function () { setTimeout(function () { $btn.prop('disabled', false).html(label); }, 1200); }
			});
		});
	};

	//ORS - PRINT ORS / DV
	this.__showPdfInModal = function (recid, kind) {
		var dv = kind === 'DV';
		document.getElementById('orsPrintTitle').textContent = dv ? 'Disbursement voucher — print preview' : 'ORS — print preview';
		document.getElementById('orsPrintFrame').src = mesiteurl + 'myors?meaction=' + (dv ? 'PRINT-DISBURSEMENT' : 'PRINT-ORS') + '&recid=' + encodeURIComponent(recid);
		bootstrap.Modal.getOrCreateInstance(document.getElementById('orsPrintModal')).show();
	};

	this.__ors_print = function () {
		document.getElementById('orsPrintBtn').addEventListener('click', function () { if (record) { __mysys_ors_ent.__showPdfInModal(record.recid, 'ORS'); } });
		document.getElementById('orsPrintDvBtn').addEventListener('click', function () { if (record) { __mysys_ors_ent.__showPdfInModal(record.recid, 'DV'); } });
	};
}

// jQuery, DataTables, jQuery UI and Bootstrap load in the footer, after this file
document.addEventListener('DOMContentLoaded', function () {
	__mysys_ors_ent.__ors_table();
	__mysys_ors_ent.__ors_grids();
	__mysys_ors_ent.__ors_deductions();
	__mysys_ors_ent.__ors_details();
	__mysys_ors_ent.__ors_saving();
	__mysys_ors_ent.__ors_print();

	// Deep link (e.g. from ORS Approval): open that ORS right away
	if (window.__ors_open) { __mysys_ors_ent.__open_ors(window.__ors_open); }
});
