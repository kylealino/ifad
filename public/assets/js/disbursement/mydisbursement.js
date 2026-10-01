var __mysys_disbursement_ent = new __mysys_disbursement_ent();
function __mysys_disbursement_ent() {
	const siteEl = document.getElementById('__siteurl');
	const mesiteurl = siteEl.getAttribute('data-mesiteurl');
	const perms = window.__dv_perms || {};
	const lookups = window.__dv_lookups || {};
	const rows = window.__dv_data || [];

	let table = null;
	let filter = 'all';
	let record = null;          // DV open in the editor (null = new)

	// Line-item grids. "payload" is the field disbursement_save() reads; each
	// row is sent as responsibility code x|x MFO/PAP x|x amount
	const GRIDS = {
		ps_direct:     { title: 'Direct cost', sub: 'Personnel Services', payload: 'budgetdtdata', tab: 'ps' },
		ps_indirect:   { title: 'Indirect cost', sub: 'Personnel Services', payload: 'budgetdtindirectdata', tab: 'ps' },
		mooe_direct:   { title: 'Direct cost', sub: 'Maintenance and Other Operating Expenses', payload: 'budgetmooedtdata', tab: 'mooe' },
		mooe_indirect: { title: 'Indirect cost', sub: 'Maintenance and Other Operating Expenses', payload: 'budgetmooeindirectdtdata', tab: 'mooe' },
		co_direct:     { title: 'Direct cost', sub: 'Capital Outlay', payload: 'budgetcodtdata', tab: 'co' },
		co_indirect:   { title: 'Indirect cost', sub: 'Capital Outlay', payload: 'budgetindirectcodtdata', tab: 'co' }
	};

	const STATUS = {
		pending: ['tone-amber', 'Pending'],
		approved: ['tone-green', 'Approved'],
		disapproved: ['tone-red', 'Disapproved']
	};

	// Box C (accounting) defaults for a new DV, as on the previous form
	const DEFAULT_CERTIFIED_C = 'BONJOBIE F. CAJANO, CPA, CTT';

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

	function fmtDateTime(d) {
		var dt = new Date(String(d || '').replace(' ', 'T'));
		return isNaN(dt) ? (d || '') : dt.toLocaleString('en-US', { month: 'short', day: '2-digit', year: 'numeric', hour: 'numeric', minute: '2-digit' });
	}

	function chip(status) {
		var s = STATUS[status] || STATUS.pending;
		return '<span class="fods-chip ' + s[0] + '"><span class="fods-dot"></span>' + s[1] + '</span>';
	}

	// Single accounting approval (same rule as MyDisbursement::statusOf)
	function statusOf(hd) {
		if (String(hd.is_disapproved_certa) === '1') { return 'disapproved'; }
		if (String(hd.is_approved_certa) === '1') { return 'approved'; }
		return 'pending';
	}

	//DISBURSEMENT - YEAR (the cards and the list follow the year picked in the page head)
	const YEAR_KEY = 'fods.year.mydisbursement';
	let year = String(new Date().getFullYear());
	try { year = localStorage.getItem(YEAR_KEY) || year; } catch (e) { /* storage blocked */ }

	// A DV's year is its disbursement date, or when it was encoded if that date is blank
	function yearOf(r) {
		var y = String(r.disbursement_date || '').slice(0, 4);
		return +y >= 2000 ? y : String(r.added_at || '').slice(0, 4);
	}

	function inYear(r) { return year === 'all' || yearOf(r) === year; }

	//DISBURSEMENT - STAT CARDS: Pending, Returned, Approved, Total amount
	function renderCards() {
		var $ = window.jQuery;
		var list = rows.filter(inYear);
		var amount = function (l) { return l.reduce(function (t, r) { return t + num(r.net_amount); }, 0); };
		var card = function (key, status, label) {
			var l = list.filter(function (r) { return r.status === status; });
			$('[data-card-value="' + key + '"]').text(l.length.toLocaleString());
			$('[data-card-label="' + key + '"]').text(label + ' · ' + moneyShort(amount(l)));
		};
		card('pending', 'pending', 'Pending');
		card('returned', 'disapproved', 'Returned');
		card('approved', 'approved', 'Approved');
		$('[data-card-value="total"]').text(money(amount(list)));
		$('[data-card-label="total"]').text('Total amount · ' + list.length.toLocaleString() + ' DV' + (list.length === 1 ? '' : 's'));
	}

	function yearPicker(onChange) {
		var sel = document.getElementById('dvYear');
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

	//DISBURSEMENT - LIST (search, status pills, stat tiles)
	this.__dv_table = function () {
		var $ = window.jQuery;

		$.fn.dataTable.ext.search.push(function (settings, data, idx) {
			if (settings.nTable.id !== 'dvTable') { return true; }
			var row = settings.aoData[idx]._aData;
			if (!inYear(row)) { return false; }
			return filter === 'all' || row.status === filter;
		});

		table = $('#dvTable').DataTable({
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
						if (type !== 'display') { return (r.dvno || '') + ' ' + (v || '') + ' ' + (r.payee_name || '') + ' ' + (r.serialno || ''); }
						return '<div style="min-width:0"><div class="fw-semibold text-dark">' + (r.dvno ? 'DV ' + esc(r.dvno) : '<span class="text-muted">No DV no. yet</span>') + '</div>'
							+ '<div class="small text-muted" style="overflow-wrap:anywhere">' + esc(v) + '</div>'
							+ '<div class="small text-muted d-md-none">' + esc(r.payee_name) + '</div></div>';
					}
				},
				{ data: 'payee_name', className: 'd-none d-md-table-cell', render: function (v, type) { return type === 'display' ? '<span style="overflow-wrap:anywhere">' + esc(v || '—') + '</span>' : (v || ''); } },
				{
					data: 'serialno', className: 'd-none d-lg-table-cell small',
					render: function (v, type, r) { return type === 'display' ? esc(v || '—') + '<br><span class="text-muted">' + fmtDate(r.disbursement_date) + '</span>' : (v || ''); }
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
							+ '<button type="button" class="fods-icon-btn is-teal dv-edit" data-recid="' + r.recid + '" title="' + (editable ? 'Edit' : 'View') + '"><i class="ti ' + (editable ? 'ti-pencil' : 'ti-eye') + '"></i></button>'
							+ '<button type="button" class="fods-icon-btn dv-print" data-recid="' + r.recid + '" title="Print DV"><i class="ti ti-printer"></i></button>'
							+ '</div>';
					}
				}
			]
		});

		$('#dvTableSearch').on('input', function () { table.search(this.value).draw(); });

		function applyFilter(key) {
			filter = key;
			$('#dvTableFilter button').removeClass('active').filter('[data-filter="' + key + '"]').addClass('active');
			$('.fods-stat[data-table="dvTable"]').removeClass('is-active');
			if (key !== 'all') { $('.fods-stat[data-table="dvTable"][data-filter="' + key + '"]').addClass('is-active'); }
			table.draw();
		}
		$(document).on('click', '#dvTableFilter button', function () { applyFilter($(this).data('filter')); });
		$(document).on('click', '.fods-stat[data-table="dvTable"]', function () { applyFilter($(this).data('filter')); });

		yearPicker(function () { renderCards(); table.draw(); });
		renderCards();
		table.draw();

		$(document).on('click', '#dvTableAdd', function () { __mysys_disbursement_ent.__open_dv(null); });
		$(document).on('click', '#dvTable .dv-edit', function () { __mysys_disbursement_ent.__open_dv($(this).data('recid')); });
		$(document).on('click', '#dvTable .dv-print', function () { __mysys_disbursement_ent.__showPdfInModal($(this).data('recid')); });
	};

	//DISBURSEMENT - LINE-ITEM GRIDS
	function rowHtml(d) {
		d = d || {};
		var val = function (k) { return esc(d[k] == null ? '' : d[k]); };
		return '<tr>'
			+ '<td><div class="dv-row-actions">'
			+ '<button type="button" class="fods-icon-btn is-danger dv-remove" title="Remove line"><i class="ti ti-trash"></i></button>'
			+ '<button type="button" class="fods-icon-btn is-teal dv-insert" title="Insert a line below"><i class="ti ti-row-insert-bottom"></i></button>'
			+ '</div></td>'
			+ '<td><input type="text" class="g-rc" placeholder="Type or pick a code" value="' + val('responsibility_code') + '" title="' + val('responsibility_code') + '"></td>'
			+ '<td><input type="text" class="g-mfo" readonly tabindex="-1" placeholder="Auto" value="' + val('mfopaps_code') + '"></td>'
			+ '<td class="num"><input type="number" step="any" class="g-amount" placeholder="0.00" value="' + val('amount') + '"></td>'
			+ '</tr>';
	}

	function gridHtml(key, data) {
		var g = GRIDS[key];
		return '<div class="dv-grid-head">'
			+ '<div><div class="dv-grid-title">' + g.title + ' <span class="dv-grid-sub" data-count></span></div><div class="dv-grid-sub">' + g.sub + '</div></div>'
			+ '<button type="button" class="btn btn-sm btn-fods-ghost dv-add"><i class="ti ti-plus me-1"></i>Add line</button>'
			+ '</div>'
			+ '<div class="dv-grid-wrap"><table class="dv-grid">'
			+ '<colgroup><col class="c-act"><col class="c-rc"><col class="c-mfo"><col class="c-amt"></colgroup>'
			+ '<thead><tr><th></th><th>Responsibility code</th><th>MFO/PAP</th><th class="num">Amount</th></tr></thead>'
			+ '<tbody>' + (data || []).map(rowHtml).join('') + '</tbody>'
			+ '<tfoot><tr><td>Subtotal</td><td colspan="2"></td><td class="num" data-sum></td></tr></tfoot>'
			+ '</table></div>'
			+ '<div class="dv-empty d-none">No lines yet — use <b>Add line</b>.</div>';
	}

	// Editable while pending, or after a disapproval (saving resubmits it)
	function canSave() {
		if (!record) { return !!perms.add; }
		var status = statusOf(record);
		return !!perms.edit && (status === 'pending' || status === 'disapproved');
	}

	function applyLocks(scope) {
		if (!canSave()) { window.jQuery(scope).find('input, select, textarea, .dv-add, .dv-remove, .dv-insert').prop('disabled', true); }
	}

	function recalc() {
		var $ = window.jQuery;
		var tabs = { ps: 0, mooe: 0, co: 0 };

		Object.keys(GRIDS).forEach(function (key) {
			var card = $('.dv-grid-card[data-grid="' + key + '"]');
			var trs = card.find('tbody tr');
			var sum = 0;
			trs.each(function () { sum += num($(this).find('.g-amount').val()); });
			card.find('[data-sum]').text(money(sum));
			card.find('[data-count]').text('· ' + trs.length + ' line' + (trs.length === 1 ? '' : 's'));
			card.find('.dv-empty').toggleClass('d-none', trs.length > 0);
			card.find('.dv-grid-wrap').toggleClass('d-none', trs.length === 0);
			tabs[GRIDS[key].tab] += sum;
		});

		Object.keys(tabs).forEach(function (t) { $('[data-tab-total="' + t + '"]').text(Math.abs(tabs[t]) >= 0.005 ? moneyShort(tabs[t]) : ''); });
		applyTaxRules();
	}

	function renderGrids(grids) {
		var $ = window.jQuery;
		Object.keys(GRIDS).forEach(function (key) {
			var card = $('.dv-grid-card[data-grid="' + key + '"]');
			card.html(gridHtml(key, grids ? grids[key] : []));
			applyLocks(card);
		});
		recalc();
	}

	this.__dv_grids = function () {
		var $ = window.jQuery;
		var modal = $('#dvModal');

		modal.on('click', '.dv-add', function () {
			var card = $(this).closest('.dv-grid-card');
			var tr = $(rowHtml(null)).appendTo(card.find('tbody'));
			recalc();
			tr.find('input:not([readonly])').first().trigger('focus');
		});

		modal.on('click', '.dv-insert', function () {
			var tr = $(rowHtml(null)).insertAfter($(this).closest('tr'));
			recalc();
			tr.find('input:not([readonly])').first().trigger('focus');
		});

		modal.on('click', '.dv-remove', function () {
			$(this).closest('tr').remove();
			recalc();
		});

		modal.on('input', '.g-amount', recalc);

		// Responsibility code fills the MFO/PAP; each suggestion shows its project
		var rcItems = (lookups.projects || []).map(function (p) {
			var title = String(p.project_title || '').replace(/\s+/g, ' ').trim();
			return { label: p.responsibility_code, value: p.responsibility_code, title: title, mfo: p.mfopaps_code || '', find: (p.responsibility_code + ' ' + title).toLowerCase() };
		});

		modal.on('focus', '.g-rc', function () {
			var input = $(this);
			if (input.data('ui-autocomplete') || input.prop('disabled')) { return; }
			input.autocomplete({
				appendTo: '#dvModal',
				minLength: 1,
				source: function (req, resp) {
					var terms = req.term.toLowerCase().split(/\s+/).filter(Boolean);
					resp(rcItems.filter(function (it) { return terms.every(function (t) { return it.find.indexOf(t) !== -1; }); }).slice(0, 50));
				},
				select: function (event, ui) {
					input.val(ui.item.value).attr('title', ui.item.value);
					input.closest('tr').find('.g-mfo').val(ui.item.mfo);
					return false;
				}
			}).autocomplete('instance')._renderItem = function (ul, item) {
				return $('<li>').append('<div><b>' + esc(item.value) + '</b><span class="ors-ac-sub">' + esc(item.title) + (item.mfo ? ' · MFO/PAP ' + esc(item.mfo) : '') + '</span></div>').appendTo(ul);
			};
		});

		// Typed by hand: fill the MFO/PAP when the code is known
		modal.on('change', '.g-rc', function () {
			var code = this.value.trim();
			var hit = rcItems.filter(function (it) { return it.value === code; })[0];
			if (hit) { $(this).closest('tr').find('.g-mfo').val(hit.mfo); }
			this.title = this.value;
		});
	};

	//DISBURSEMENT - TAXES AND DEDUCTIONS (same rules as the previous DV form:
	//every amount is rounded to 2 decimals before it is totaled)
	function roundMoney(value) {
		return Math.round((Number(value || 0) + Number.EPSILON) * 100) / 100;
	}

	function getMoney(id) {
		return roundMoney(parseFloat(document.getElementById(id).value) || 0);
	}

	function setAmounts(gross, deduction, net) {
		var $ = window.jQuery;
		$('#gross_amount').val(gross.toFixed(2));
		$('#total_deduction').val(deduction.toFixed(2));
		$('#net_amount').val(net.toFixed(2));
		$('#dvGross, #dvFootGross').text(money(gross));
		$('#dvDeduction, #dvFootDeduction').text(money(deduction));
		$('#dvNet, #dvFootNet').text(money(net));
	}

	function clear(ids) { ids.forEach(function (id) { document.getElementById(id).value = ''; }); }

	var PAYROLL = ['sss', 'hdmf', 'pagibig_mp2', 'pagibig_loan', 'hmo', 'wtax'];

	function applyTaxRules() {
		var $ = window.jQuery;
		var gross = 0;
		$('#dvModal .g-amount').each(function () { gross = roundMoney(gross + roundMoney(parseFloat(this.value) || 0)); });

		var category = $('#category').val();
		var isVat = $('#is_vatable').is(':checked');
		var otherDeductions = getMoney('other_deductions');
		var incomeTax = getMoney('income_tax');

		// Keep saved values untouched on a locked (view-only) DV
		if (record && !canSave()) {
			setAmounts(gross, num(record.total_deduction), num(record.net_amount) || roundMoney(gross - num(record.total_deduction)));
			return;
		}

		// PAYROLL
		if (category === 'Payroll') {
			var payroll = roundMoney(PAYROLL.reduce(function (s, id) { return s + getMoney(id); }, 0));
			clear(['vat_percent', 'ewt_percent', 'pt_percent', 'other_deductions', 'income_tax']);
			setAmounts(gross, payroll, roundMoney(gross - payroll));
			return;
		}

		// INCOME TAX
		if (category === 'Income Tax') {
			clear(['vat_percent', 'ewt_percent', 'pt_percent', 'other_deductions'].concat(PAYROLL));
			setAmounts(gross, incomeTax, roundMoney(gross - incomeTax));
			return;
		}

		// NO CATEGORY
		if (!category) {
			clear(['vat_percent', 'ewt_percent', 'pt_percent'].concat(PAYROLL));
			var none = roundMoney(otherDeductions + incomeTax);
			setAmounts(gross, none, roundMoney(gross - none));
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
				vat = roundMoney(net * (vatPercent / 100));
				ewt = roundMoney(net * (ewtPercent / 100));
				clear(['pt_percent']);
			} else {
				pt = roundMoney(gross * (ptPercent / 100));
				ewt = roundMoney(gross * (ewtPercent / 100));
				clear(['vat_percent']);
			}
		} else if (isVat) {
			net = gross / 1.12;
			$('#vat_percent').val(5);
			vat = roundMoney(net * 0.05);
			if (category === 'Goods') { $('#ewt_percent').val(1); ewt = roundMoney(net * 0.01); }
			else if (category === 'Services') { $('#ewt_percent').val(2); ewt = roundMoney(net * 0.02); }
			clear(['pt_percent']);
		} else {
			clear(['vat_percent']);
			if (category === 'Goods') { $('#ewt_percent').val(1); ewt = roundMoney(gross * 0.01); }
			else if (category === 'Services') { $('#ewt_percent').val(2); ewt = roundMoney(gross * 0.02); }
			$('#pt_percent').val(3);
			pt = roundMoney(gross * 0.03);
		}

		var deduction = roundMoney(vat + ewt + pt + otherDeductions + incomeTax);
		setAmounts(gross, deduction, roundMoney(gross - deduction));
	}

	function toggleFields() {
		var $ = window.jQuery;
		var category = $('#category').val();
		var taxed = category === 'Goods' || category === 'Services' || category === 'Manual';
		var isVat = $('#is_vatable').is(':checked');

		$('#dvModal .dv-tax').toggleClass('d-none', !taxed);
		if (taxed) {
			$('#dvModal .dv-vat').toggleClass('d-none', !isVat);
			$('#dvModal .dv-pt').toggleClass('d-none', isVat);
		}
		$('#dvModal .dv-income').toggleClass('d-none', category !== 'Income Tax');
		$('#dvModal .dv-payroll').toggleClass('d-none', category !== 'Payroll');
		$('#dvModal .dv-none').toggleClass('d-none', !!category);
	}

	this.__dv_deductions = function () {
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

		$('#dvModal').on('input', '#vat_percent, #ewt_percent, #pt_percent, #other_deductions, #income_tax, #sss, #hdmf, #pagibig_mp2, #pagibig_loan, #hmo, #wtax', applyTaxRules);
	};

	//DISBURSEMENT - DETAILS (ORS / BURS source, payee lookup, signatories)
	var payeeIndex = {};
	var sourcePayee = '';     // payee copied from the picked ORS / BURS (may predate the payee list)

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

	// Copies an ORS / BURS into the DV: the same fields the previous form copied
	function applyObligation(o) {
		var $ = window.jQuery;
		['particulars', 'payee_name', 'payee_office', 'payee_address', 'deduction_name'].forEach(function (f) { setVal(f, o[f]); });
		['vat_percent', 'ewt_percent', 'pt_percent', 'other_deductions', 'income_tax'].concat(PAYROLL).forEach(function (f) {
			setVal(f, num(o[f]) ? o[f] : '');
		});
		setVal('serialno', o.serialno);
		sourcePayee = o.payee_name || '';
		if (o.disbursement_date && String(o.disbursement_date).indexOf('0000') !== 0) { setVal('disbursement_date', o.disbursement_date); }
		options('funding_source', lookups.funding_sources || [], o.funding_source);
		options('fund_cluster_code', lookups.fund_clusters || [], o.fund_cluster_code);
		options('category', lookups.categories || [], o.category);
		options('certified_a', lookups.certifya || [], o.certified_a, o.position_a);
		setVal('position_a', o.position_a);
		document.getElementById('is_vatable').checked = String(o.is_vatable) === '1';
		toggleFields();
		renderGrids(o.grids);
		$('#dvSourceHint').html('Copied from <b>' + esc(o.source) + ' ' + esc(o.serialno) + '</b>. Check the amounts, then save.');
	}

	this.__dv_details = function () {
		var $ = window.jQuery;
		(lookups.payees || []).forEach(function (p) { payeeIndex[String(p.value).trim().toLowerCase()] = p; });

		$('#payee_name').autocomplete({
			appendTo: '#dvModal',
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

		// ORS / BURS serial: picking one copies it into the DV
		$('#serialno').autocomplete({
			appendTo: '#dvModal',
			minLength: 0,
			source: function (req, resp) {
				var terms = req.term.toLowerCase().split(/\s+/).filter(Boolean);
				var list = lookups.obligations || [];
				var out = [];
				for (var i = 0; i < list.length && out.length < 40; i++) {
					var hay = (list[i].serialno + ' ' + list[i].payee_name + ' ' + list[i].source).toLowerCase();
					if (terms.every(function (t) { return hay.indexOf(t) !== -1; })) { out.push(list[i]); }
				}
				resp(out);
			},
			select: function (event, ui) {
				var item = ui.item;
				$('#serialno').val(item.serialno);
				$.getJSON(mesiteurl + 'mydisbursement', { meaction: 'OBLIGATION-LOAD', source: item.source, id: item.id })
					.done(applyObligation)
					.fail(function (xhr) {
						toastr.error((xhr.responseJSON && xhr.responseJSON.error) || 'Could not load that ORS / BURS.', 'Oops!', { closeButton: true });
					});
				return false;
			}
		}).on('focus', function () {
			if (!this.disabled) { $(this).autocomplete('search', this.value); }
		}).autocomplete('instance')._renderItem = function (ul, item) {
			return $('<li>').append('<div><b>' + esc(item.serialno) + '</b> <span class="fods-chip ' + (item.source === 'BURS' ? 'tone-amber' : 'tone-indigo') + ' ms-1" style="font-size:.65rem">' + esc(item.source) + '</span>'
				+ '<span class="ors-ac-sub">' + esc(item.payee_name) + (item.dvno ? ' · DV ' + esc(item.dvno) : '') + '</span></div>').appendTo(ul);
		};

		$('#certified_a').on('change', function () { $('#position_a').val($(this).find(':selected').data('position') || ''); });
		$('#certified_c').on('change', function () { $('#position_c').val($(this).find(':selected').data('position') || ''); });
	};

	//DISBURSEMENT - OPEN EDITOR (new, or load one DV with its line items)
	function setVal(id, v) { document.getElementById(id).value = v == null ? '' : v; }

	function fill(hd) {
		var form = document.getElementById('dvForm');
		form.reset();
		form.classList.remove('was-validated');
		document.getElementById('payee_name').classList.remove('is-invalid');

		['recid', 'serialno', 'dvno', 'particulars', 'payee_name', 'payee_office', 'payee_address', 'position_a', 'position_c',
			'deduction_name'].forEach(function (f) { setVal(f, hd ? hd[f] : ''); });

		// saved zeros show as blanks, like the previous form
		['vat_percent', 'ewt_percent', 'pt_percent', 'other_deductions', 'income_tax'].concat(PAYROLL).forEach(function (f) {
			setVal(f, hd && num(hd[f]) ? hd[f] : '');
		});

		setVal('disbursement_date', hd ? (String(hd.disbursement_date || '').indexOf('0000') === 0 ? '' : hd.disbursement_date) : new Date().toISOString().slice(0, 10));

		options('funding_source', lookups.funding_sources || [], hd ? hd.funding_source : '');
		options('fund_cluster_code', lookups.fund_clusters || [], hd ? hd.fund_cluster_code : '');
		options('category', lookups.categories || [], hd ? hd.category : '');
		options('certified_a', lookups.certifya || [], hd ? hd.certified_a : '', hd ? hd.position_a : '');

		// Box C: a new DV starts with the usual accountant
		var certC = hd ? hd.certified_c : '';
		if (!hd && (lookups.certifyc || []).some(function (c) { return c.full_name === DEFAULT_CERTIFIED_C; })) { certC = DEFAULT_CERTIFIED_C; }
		options('certified_c', lookups.certifyc || [], certC, hd ? hd.position_c : '');
		if (!hd) { setVal('position_c', window.jQuery('#certified_c').find(':selected').data('position') || ''); }

		document.getElementById('is_vatable').checked = !!hd && String(hd.is_vatable) === '1';
		sourcePayee = '';
		document.getElementById('dvSourceHint').textContent = 'Picking one copies its payee, deductions, signatory and line items into this DV.';

		toggleFields();
	}

	// Accounting approval status, shown on the Box C card
	function signStatus(hd) {
		var $ = window.jQuery;
		var status = hd ? statusOf(hd) : null;
		var html = '', note = '';

		if (status === 'approved') { html = '<span class="fods-chip tone-green"><i class="ti ti-check"></i>Approved</span>'; }
		else if (status === 'disapproved') { html = '<span class="fods-chip tone-red"><i class="ti ti-x"></i>Disapproved</span>'; }
		else if (status === 'pending') { html = '<span class="fods-chip tone-amber"><span class="fods-dot"></span>Pending</span>'; }

		if (status === 'approved' || status === 'disapproved') {
			note = 'By ' + esc(hd.certa_approver || '—') + (hd.certa_remarks ? ' — ' + esc(hd.certa_remarks) : '');
		}
		$('#dvSignStatus').html(html);
		$('#dvSignNote').html(note);
	}

	// History (tbl_disbursement_history) as a timeline, newest first.
	// The *_CERTA/B entries are from the earlier two-step flow.
	var HISTORY = {
		ADDED: ['Created', '#0f6f6f'],
		UPDATED: ['Updated', '#b45309'],
		APPROVED: ['Approved', '#15803d'],
		DISAPPROVED: ['Disapproved', '#b91c1c'],
		APPROVED_CERTA: ['Approved', '#15803d'],
		DISAPPROVED_CERTA: ['Disapproved', '#b91c1c'],
		APPROVED_CERTB: ['Approved', '#15803d'],
		DISAPPROVED_CERTB: ['Disapproved', '#b91c1c']
	};

	function renderHistory(hd) {
		var $ = window.jQuery;
		var list = hd ? (hd.history || []) : [];
		$('#dvHistoryTab').toggleClass('d-none', !hd);
		$('#dvHistoryCount').text(list.length ? list.length : '');
		if (!list.length) {
			$('#dvHistory').html('<div class="text-muted small">No history recorded yet.</div>');
			return;
		}
		$('#dvHistory').html('<ul class="dv-timeline">' + list.map(function (h) {
			var k = HISTORY[String(h.action_type).toUpperCase()] || [h.action_type, '#64748b'];
			var changes = '';
			try {
				var fc = h.field_changes ? JSON.parse(h.field_changes) : null;
				if (Array.isArray(fc) && fc.length) {
					changes = '<div class="changes">' + fc.map(function (c) {
						return esc(String(c.field).replace(/_/g, ' ')) + ': <s>' + esc(c.old || '—') + '</s> → ' + esc(c['new'] || '—');
					}).join('<br>') + '</div>';
				}
			} catch (e) { /* not JSON */ }
			return '<li><span class="dot" style="background:' + k[1] + '"></span>'
				+ '<div class="what">' + esc(k[0]) + '</div>'
				+ '<div class="meta">' + esc(h.user_full_name || h.action_by || '—') + (h.action_by_role ? ' · ' + esc(h.action_by_role) : '') + ' · ' + esc(fmtDateTime(h.date_created)) + '</div>'
				+ (h.remarks ? '<div class="note">' + esc(h.remarks) + '</div>' : '')
				+ changes + '</li>';
		}).join('') + '</ul>');
	}

	function setHeader(hd) {
		var $ = window.jQuery;
		var status = hd ? statusOf(hd) : null;

		$('#dvModalTitle').text(hd ? (hd.dvno ? 'DV ' + hd.dvno : 'DV #' + hd.recid) : 'New Disbursement Voucher');
		$('#dvModalSub').html(hd
			? esc(hd.payee_name) + ' &nbsp;' + chip(status)
			: 'Pick the ORS or BURS to copy from, then check the amounts.');

		$('#dvPrintBtn').toggleClass('d-none', !hd);

		var banner = $('#dvBanner').removeClass('alert-success alert-danger alert-info d-none');
		if (status === 'approved') {
			banner.addClass('alert-success').html('<b>Approved by accounting</b>' + (hd.certa_approver ? ' (' + esc(hd.certa_approver) + ')' : '')
				+ (hd.certa_remarks ? ' — ' + esc(hd.certa_remarks) : '') + '. It can no longer be edited.');
		} else if (status === 'disapproved') {
			banner.addClass('alert-danger').html('<b>Disapproved by accounting</b>' + (hd.certa_approver ? ' (' + esc(hd.certa_approver) + ')' : '')
				+ (hd.certa_remarks ? ' — ' + esc(hd.certa_remarks) : '')
				+ (canSave() ? '<div class="small mt-1">Saving your corrections sends it back for approval.</div>' : ''));
		} else {
			banner.addClass('d-none');
		}

		signStatus(hd);

		$('#submitBtn').toggleClass('d-none', !canSave())
			.text(!hd ? 'Create DV' : (status === 'disapproved' ? 'Save & resubmit' : 'Save changes'));
	}

	this.__open_dv = function (recid) {
		var $ = window.jQuery;
		var modalEl = document.getElementById('dvModal');
		bootstrap.Tab.getOrCreateInstance(document.querySelector('[data-bs-target="#dvPaneDetails"]')).show();

		function show(hd) {
			record = hd;
			fill(hd);
			setHeader(hd);
			renderGrids(hd ? hd.grids : null);
			renderHistory(hd);
			var scope = $('#dvPaneDetails, #dvPaneSign');
			if (canSave()) { scope.find('input, select, textarea').not('[readonly]').prop('disabled', false); }
			else { applyLocks(scope); }
			bootstrap.Modal.getOrCreateInstance(modalEl).show();
		}

		if (!recid) { show(null); return; }

		$.getJSON(mesiteurl + 'mydisbursement', { meaction: 'MAIN-LOAD', recid: recid })
			.done(show)
			.fail(function (xhr) {
				toastr.error((xhr.responseJSON && xhr.responseJSON.error) || 'Could not load this disbursement.', 'Oops!', { closeButton: true });
			});
	};

	//DISBURSEMENT - SAVE (same payload as before: header fields + 6 grids of "x|x" rows)
	function collectGrid(key) {
		var $ = window.jQuery;
		var out = [];
		$('.dv-grid-card[data-grid="' + key + '"] tbody tr').each(function () {
			var tr = $(this);
			var v = function (cls) { var x = tr.find('.g-' + cls).val(); return x == null ? '' : x; };
			if (!v('rc').trim() && !v('mfo').trim() && num(v('amount')) === 0) { return; }  // skip untouched lines
			out.push([v('rc'), v('mfo'), v('amount')].join('x|x'));
		});
		return out;
	}

	this.__dv_saving = function () {
		var $ = window.jQuery;
		var form = document.getElementById('dvForm');
		form.addEventListener('submit', function (e) {
			e.preventDefault();
			var payee = document.getElementById('payee_name');
			var knownPayee = !!fillPayee(payee.value) || (record && payee.value === record.payee_name) || (!!sourcePayee && payee.value === sourcePayee);
			payee.classList.toggle('is-invalid', !!payee.value && !knownPayee);
			if (!form.checkValidity() || !knownPayee) {
				form.classList.add('was-validated');
				bootstrap.Tab.getOrCreateInstance(document.querySelector('[data-bs-target="#dvPaneDetails"]')).show();
				payee.nextElementSibling.textContent = payee.value && !knownPayee ? 'Pick a payee from the list.' : 'Payee is required.';
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
				certified_c: val('certified_c'),
				position_c: val('position_c'),
				dvno: val('dvno'),
				fund_cluster_code: val('fund_cluster_code'),
				disbursement_date: val('disbursement_date'),
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
				is_vatable: document.getElementById('is_vatable').checked ? 1 : 0,
				meaction: 'MAIN-SAVE'
			};
			Object.keys(GRIDS).forEach(function (key) { mparam[GRIDS[key].payload] = collectGrid(key); });

			var $btn = $('#submitBtn'), label = $btn.html();
			$btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Saving...');
			$.ajax({
				type: 'POST',
				url: mesiteurl + 'mydisbursement',
				data: mparam,
				global: false,
				cache: false,
				success: function (data) { $('.me-outp-msg').html(data); },
				error: function (xhr, status, error) { toastr.error('[DISBURSEMENT]', 'Error loading page...' + error, { closeButton: true }); },
				complete: function () { setTimeout(function () { $btn.prop('disabled', false).html(label); }, 1200); }
			});
		});
	};

	//DISBURSEMENT - PRINT DV
	this.__showPdfInModal = function (recid) {
		document.getElementById('dvPrintFrame').src = mesiteurl + 'mydisbursement?meaction=PRINT-DISBURSEMENT&recid=' + encodeURIComponent(recid);
		bootstrap.Modal.getOrCreateInstance(document.getElementById('dvPrintModal')).show();
	};

	this.__dv_print = function () {
		document.getElementById('dvPrintBtn').addEventListener('click', function () { if (record) { __mysys_disbursement_ent.__showPdfInModal(record.recid); } });
	};
}

// jQuery, DataTables, jQuery UI and Bootstrap load in the footer, after this file
document.addEventListener('DOMContentLoaded', function () {
	__mysys_disbursement_ent.__dv_table();
	__mysys_disbursement_ent.__dv_grids();
	__mysys_disbursement_ent.__dv_deductions();
	__mysys_disbursement_ent.__dv_details();
	__mysys_disbursement_ent.__dv_saving();
	__mysys_disbursement_ent.__dv_print();

	// Deep link (e.g. from Disbursement Approval): open that DV right away
	if (window.__dv_open) { __mysys_disbursement_ent.__open_dv(window.__dv_open); }
});
