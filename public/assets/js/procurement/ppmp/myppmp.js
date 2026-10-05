var __mysys_ppmp_ent = new __mysys_ppmp_ent();
function __mysys_ppmp_ent() {
	const siteEl = document.getElementById('__siteurl');
	const mesiteurl = siteEl.getAttribute('data-mesiteurl');
	const perms = window.__ppmp_perms || {};
	const lookups = window.__ppmp_lookups || {};
	const rows = window.__ppmp_data || [];

	let table = null;
	let filter = 'all';
	let record = null;          // PPMP open in the editor (null = new)

	const STATUS = {
		unmarked: ['tone-slate', 'Not marked'],
		indicative: ['tone-amber', 'Indicative'],
		final: ['tone-green', 'Final']
	};

	// Item columns, in the order ppmp_save() reads them. Each row is sent as
	// description x|x type x|x quantity x|x unit x|x unit cost x|x mode x|x pre-procurement
	// x|x start x|x end x|x delivery from x|x delivery to x|x funds x|x budget x|x documents x|x remarks
	const FIELDS = ['item_desc', 'item_type', 'quantity', 'size', 'unit_cost', 'mop', 'is_preproc', 'proc_start', 'proc_end',
		'expected_delivery_from', 'expected_delivery_to', 'funding_source', 'estimated_budget', 'attached_document', 'remarks'];

	// Fields that suggest from a short list (free text is still allowed)
	const SUGGEST = { item_type: 'item_types', size: 'units', mop: 'modes', is_preproc: 'preproc', funding_source: 'funding_sources' };

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
		var s = STATUS[status] || STATUS.unmarked;
		return '<span class="fods-chip ' + s[0] + '"><span class="fods-dot"></span>' + s[1] + '</span>';
	}

	// Final wins when both switches are on (same rule as MyPPMP::statusOf)
	function statusOf(hd) {
		if (String(hd.is_final) === '1') { return 'final'; }
		if (String(hd.is_indicative) === '1') { return 'indicative'; }
		return 'unmarked';
	}

	//PPMP - YEAR (the cards and the list follow the fiscal year picked in the page head)
	const YEAR_KEY = 'fods.year.myppmp';
	let year = String(new Date().getFullYear());
	try { year = localStorage.getItem(YEAR_KEY) || year; } catch (e) { /* storage blocked */ }

	// A PPMP's year is its fiscal year, or when it was encoded if that is blank
	function yearOf(r) {
		var y = String(r.fiscal_year || '').slice(0, 4);
		return +y >= 2000 ? y : String(r.added_at || '').slice(0, 4);
	}

	function inYear(r) { return year === 'all' || yearOf(r) === year; }

	//PPMP - STAT CARDS: PPMPs, Indicative, Final, Estimated budget
	function renderCards() {
		var $ = window.jQuery;
		var list = rows.filter(inYear);
		var amount = function (l) { return l.reduce(function (t, r) { return t + num(r.amount); }, 0); };
		var items = list.reduce(function (t, r) { return t + num(r.items); }, 0);
		var card = function (key, status, label) {
			var l = list.filter(function (r) { return r.status === status; });
			$('[data-card-value="' + key + '"]').text(l.length.toLocaleString());
			$('[data-card-label="' + key + '"]').text(label + ' · ' + money(amount(l)));
		};
		$('[data-card-value="records"]').text(list.length.toLocaleString());
		$('[data-card-label="records"]').text('PPMPs · ' + items.toLocaleString() + ' item' + (items === 1 ? '' : 's'));
		card('indicative', 'indicative', 'Indicative');
		card('final', 'final', 'Final');
		$('[data-card-value="total"]').text(money(amount(list)));
		$('[data-card-label="total"]').text('Estimated budget');
	}

	function yearPicker(onChange) {
		var sel = document.getElementById('ppmpYear');
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

	//PPMP - LIST (search, status pills, stat tiles)
	this.__ppmp_table = function () {
		var $ = window.jQuery;

		$.fn.dataTable.ext.search.push(function (settings, data, idx) {
			if (settings.nTable.id !== 'ppmpTable') { return true; }
			var row = settings.aoData[idx]._aData;
			return inYear(row) && (filter === 'all' || row.status === filter);
		});

		table = $('#ppmpTable').DataTable({
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
					data: 'ppmpno', className: 'cell-primary',
					render: function (v, type, r) {
						if (type !== 'display') { return (v || '') + ' ' + (r.project_title || '') + ' ' + (r.responsibility_code || '') + ' ' + (r.end_user || ''); }
						return '<div style="min-width:0"><div class="fw-semibold text-dark">' + esc(v || 'No PPMP no.') + '</div>'
							+ '<div class="small text-muted" style="overflow-wrap:anywhere">' + esc(r.project_title) + '</div>'
							+ '<div class="small text-muted" style="overflow-wrap:anywhere">' + esc(r.responsibility_code) + '<span class="d-md-none"> · ' + esc(r.end_user) + '</span></div></div>';
					}
				},
				{ data: 'end_user', className: 'd-none d-md-table-cell', render: function (v, type) { return type === 'display' ? '<span style="overflow-wrap:anywhere">' + esc(v || '—') + '</span>' : (v || ''); } },
				{ data: 'fiscal_year', className: 'd-none d-lg-table-cell text-nowrap', render: function (v, type) { return type === 'display' ? esc(v || '—') : (v || ''); } },
				{
					data: 'amount', className: 'text-end cell-left',
					render: function (v, type, r) {
						if (type !== 'display') { return num(v); }
						return '<span class="text-nowrap fw-semibold">' + money(v) + '</span><div class="small text-muted">' + num(r.items) + ' item' + (num(r.items) === 1 ? '' : 's') + '</div>';
					}
				},
				{
					data: 'status', className: 'cell-right',
					render: function (v, type) { return type === 'display' ? chip(v) : (STATUS[v] || STATUS.unmarked)[1]; }
				},
				{
					data: 'added_at', className: 'd-none d-xl-table-cell text-muted small text-nowrap',
					render: function (v, type, r) { return type === 'display' ? fmtDate(v) + (r.added_by ? '<br>' + esc(r.added_by) : '') : (v || ''); }
				},
				{
					data: null, orderable: false, searchable: false, className: 'cell-actions text-end',
					render: function (v, type, r) {
						return '<div class="d-flex justify-content-end gap-1">'
							+ '<button type="button" class="fods-icon-btn is-teal ppmp-edit" data-recid="' + r.recid + '" title="' + (perms.edit ? 'Edit' : 'View') + '"><i class="ti ' + (perms.edit ? 'ti-pencil' : 'ti-eye') + '"></i></button>'
							+ '<button type="button" class="fods-icon-btn ppmp-print" data-recid="' + r.recid + '" title="Print PPMP"><i class="ti ti-printer"></i></button>'
							+ '</div>';
					}
				}
			]
		});

		$('#ppmpTableSearch').on('input', function () { table.search(this.value).draw(); });

		function applyFilter(key) {
			filter = key;
			$('#ppmpTableFilter button').removeClass('active').filter('[data-filter="' + key + '"]').addClass('active');
			$('.fods-stat[data-table="ppmpTable"]').removeClass('is-active');
			if (key !== 'all') { $('.fods-stat[data-table="ppmpTable"][data-filter="' + key + '"]').addClass('is-active'); }
			table.draw();
		}
		$(document).on('click', '#ppmpTableFilter button', function () { applyFilter($(this).data('filter')); });
		$(document).on('click', '.fods-stat[data-table="ppmpTable"]', function () { applyFilter($(this).data('filter')); });

		yearPicker(function () { renderCards(); table.draw(); });
		renderCards();
		table.draw();

		$(document).on('click', '#ppmpTableAdd', function () { __mysys_ppmp_ent.__open_ppmp(null); });
		$(document).on('click', '#ppmpTable .ppmp-edit', function () { __mysys_ppmp_ent.__open_ppmp($(this).data('recid')); });
		$(document).on('click', '#ppmpTable .ppmp-print', function () { __mysys_ppmp_ent.__showPdfInModal($(this).data('recid')); });
	};

	// New PPMPs need Add, saved ones need Update
	function canSave() {
		return record ? !!perms.edit : !!perms.add;
	}

	//PPMP - ITEMS GRID
	function rowHtml(d) {
		d = d || {};
		var val = function (k) { return esc(d[k] == null ? '' : d[k]); };
		// title= shows the full text of long values on hover
		var text = function (k, label, extra) {
			return '<input type="text" class="g-' + k + '" value="' + val(k) + '" title="' + val(k) + '" aria-label="' + label + '"' + (extra || '') + '>';
		};
		var number = function (k, label) {
			// saved zeros show as blanks
			var v = d[k] == null || num(d[k]) === 0 ? '' : esc(d[k]);
			return '<input type="number" step="any" class="g-' + k + '" value="' + v + '" placeholder="0" aria-label="' + label + '">';
		};
		var month = function (k, label) {
			return '<input type="month" class="g-' + k + '" value="' + val(k) + '" aria-label="' + label + '">';
		};
		return '<tr>'
			+ '<td class="sticky-l"><div class="ppmp-row-actions">'
			+ '<button type="button" class="fods-icon-btn is-danger ppmp-remove" title="Remove item"><i class="ti ti-trash"></i></button>'
			+ '<button type="button" class="fods-icon-btn is-teal ppmp-insert" title="Insert an item below"><i class="ti ti-row-insert-bottom"></i></button>'
			+ '</div></td>'
			+ '<td>' + text('item_desc', 'Description', ' maxlength="250" placeholder="Type or pick an item"') + '</td>'
			+ '<td>' + text('item_type', 'Type', ' maxlength="50" placeholder="Pick a type"') + '</td>'
			+ '<td class="num">' + number('quantity', 'Quantity') + '</td>'
			+ '<td>' + text('size', 'Unit', ' maxlength="50" placeholder="Unit"') + '</td>'
			+ '<td class="num">' + number('unit_cost', 'Unit cost') + '</td>'
			+ '<td>' + text('mop', 'Mode of procurement', ' maxlength="50" placeholder="Pick a mode"') + '</td>'
			+ '<td>' + text('is_preproc', 'Pre-procurement conference', ' maxlength="10" placeholder="Yes / No"') + '</td>'
			+ '<td>' + month('proc_start', 'Start of procurement') + '</td>'
			+ '<td>' + month('proc_end', 'End of procurement') + '</td>'
			+ '<td><div class="ppmp-range">' + month('expected_delivery_from', 'Delivery from') + '<span>to</span>' + month('expected_delivery_to', 'Delivery to') + '</div></td>'
			+ '<td>' + text('funding_source', 'Source of funds', ' maxlength="50" placeholder="Pick a fund"') + '</td>'
			+ '<td class="num">' + number('estimated_budget', 'Estimated budget') + '</td>'
			+ '<td>' + text('attached_document', 'Supporting documents', ' maxlength="100"') + '</td>'
			+ '<td>' + text('remarks', 'Remarks', ' maxlength="150"') + '</td>'
			+ '</tr>';
	}

	function gridHtml(items) {
		var th = function (label, full, cls) { return '<th' + (cls ? ' class="' + cls + '"' : '') + ' title="' + full + '">' + label + '</th>'; };
		return '<div class="ppmp-grid-head">'
			+ '<div><div class="ppmp-grid-title">Items to procure <span class="ppmp-grid-sub" data-count></span></div></div>'
			+ '<div class="d-flex align-items-center gap-3"><span class="ppmp-grid-hint d-none" data-hint><i class="ti ti-arrows-horizontal me-1"></i>Scroll sideways for more columns</span>'
			+ '<button type="button" class="btn btn-sm btn-fods-ghost ppmp-add"><i class="ti ti-plus me-1"></i>Add item</button></div>'
			+ '</div>'
			+ '<div class="ppmp-grid-wrap"><table class="ppmp-grid">'
			+ '<colgroup><col class="c-act"><col class="c-desc"><col class="c-type"><col class="c-qty"><col class="c-unit"><col class="c-amt"><col class="c-mode"><col class="c-yn">'
			+ '<col class="c-month"><col class="c-month"><col class="c-range"><col class="c-fund"><col class="c-amt"><col class="c-text"><col class="c-text"></colgroup>'
			+ '<thead><tr><th class="sticky-l"></th>'
			+ th('Description', 'General description and objective of the project to be procured')
			+ th('Type', 'Type of the project to be procured')
			+ th('Quantity', 'Quantity', 'num')
			+ th('Unit', 'Size')
			+ th('Unit cost', 'Unit cost', 'num')
			+ th('Mode of procurement', 'Mode of procurement')
			+ th('Pre-proc. conf.', 'Pre-procurement conference, if applicable (Yes/No)')
			+ th('Procurement start', 'Start of procurement activity')
			+ th('Procurement end', 'End of procurement activity')
			+ th('Delivery period', 'Expected delivery / implementation period')
			+ th('Source of funds', 'Source of funds')
			+ th('Estimated budget', 'Estimated budget / authorized budgetary allocation (PHP)', 'num')
			+ th('Supporting documents', 'Attached supporting documents')
			+ th('Remarks', 'Remarks')
			+ '</tr></thead>'
			+ '<tbody>' + (items || []).map(rowHtml).join('') + '</tbody>'
			+ '<tfoot><tr><td class="sticky-l">Total</td><td colspan="11"></td><td class="num" data-sum></td><td colspan="2"></td></tr></tfoot>'
			+ '</table></div>'
			+ '<div class="ppmp-empty d-none">No items yet — use <b>Add item</b>.</div>';
	}

	// Set both ways: the same modal is reused for records the user can and cannot edit
	function applyLocks() {
		var $ = window.jQuery;
		var locked = !canSave();
		$('#ppmpPaneDetails').find('input, select, textarea').prop('disabled', locked);
		if (locked) { $('#ppmpGrid').find('input, .ppmp-add, .ppmp-remove, .ppmp-insert').prop('disabled', true); }
	}

	function recalc() {
		var $ = window.jQuery;
		var card = $('#ppmpGrid');
		var trs = card.find('tbody tr');
		var sum = 0;
		trs.each(function () { sum += num($(this).find('.g-estimated_budget').val()); });
		card.find('[data-sum]').text(money(sum));
		card.find('[data-count]').text('· ' + trs.length + ' item' + (trs.length === 1 ? '' : 's'));
		card.find('.ppmp-empty').toggleClass('d-none', trs.length > 0);
		card.find('.ppmp-grid-wrap, [data-hint]').toggleClass('d-none', trs.length === 0);
		$('[data-tab-total]').text(trs.length ? trs.length + (Math.abs(sum) >= 0.005 ? ' · ' + money(sum) : '') : '');
		$('#ppmpFootItems').text(trs.length.toLocaleString());
		$('#ppmpFootBudget').text(money(sum));
	}

	this.__ppmp_grid = function () {
		var $ = window.jQuery;
		var modal = $('#ppmpModal');

		function addRow(after) {
			var tr = $(rowHtml(null));
			if (after) { tr.insertAfter(after); } else { tr.appendTo('#ppmpGrid tbody'); }
			recalc();
			tr.find('input').first().trigger('focus');
		}

		modal.on('click', '.ppmp-add', function () { addRow(null); });
		modal.on('click', '.ppmp-insert', function () { addRow($(this).closest('tr')); });

		modal.on('click', '.ppmp-remove', function () {
			$(this).closest('tr').remove();
			recalc();
		});

		// Quantity x unit cost fills the estimated budget until someone types a budget
		modal.on('input', '.g-quantity, .g-unit_cost', function () {
			var tr = $(this).closest('tr');
			var budget = tr.find('.g-estimated_budget');
			if (budget.val() === '' || budget.data('auto')) {
				var product = num(tr.find('.g-quantity').val()) * num(tr.find('.g-unit_cost').val());
				budget.val(product ? product.toFixed(2) : '').data('auto', true);
			}
			recalc();
		});
		modal.on('input', '.g-estimated_budget', function () {
			$(this).data('auto', false);
			recalc();
		});

		// keep the hover text in step with values typed or filled by a lookup
		modal.on('change autocompleteclose', '.ppmp-grid input[type=text]', function () { this.title = this.value; });

		// Item description: picking a product fills its unit and price when those are blank
		modal.on('focus', '.g-item_desc', function () {
			var input = $(this);
			if (input.data('ui-autocomplete')) { return; }
			input.autocomplete({
				appendTo: '#ppmpModal',
				minLength: 1,
				source: function (req, resp) {
					var term = req.term.toLowerCase();
					var out = [];
					var list = lookups.products || [];
					for (var i = 0; i < list.length && out.length < 40; i++) {
						if (String(list[i].value).toLowerCase().indexOf(term) !== -1) { out.push(list[i]); }
					}
					resp(out);
				},
				select: function (event, ui) {
					var tr = input.closest('tr');
					input.val(ui.item.value);
					if (!tr.find('.g-size').val() && ui.item.uom) { tr.find('.g-size').val(ui.item.uom); }
					if (!num(tr.find('.g-unit_cost').val()) && num(ui.item.price)) { tr.find('.g-unit_cost').val(ui.item.price).trigger('input'); }
					return false;
				}
			}).autocomplete('instance')._renderItem = function (ul, item) {
				return $('<li>').append('<div>' + esc(item.value) + (item.uom ? '<span class="ppmp-ac-sub">' + esc(item.uom) + (num(item.price) ? ' · ' + money(item.price) : '') + '</span>' : '') + '</div>').appendTo(ul);
			};
		});

		// Short pick lists: the whole list opens on focus
		Object.keys(SUGGEST).forEach(function (field) {
			modal.on('focus', '.g-' + field, function () {
				var input = $(this);
				if (!input.data('ui-autocomplete')) {
					input.autocomplete({ source: lookups[SUGGEST[field]] || [], appendTo: '#ppmpModal', minLength: 0 });
				}
				if (!this.readOnly && !this.disabled) { input.autocomplete('search', ''); }
			});
		});
	};

	//PPMP - DETAILS (responsibility code and signatory lookups)
	this.__ppmp_details = function () {
		var $ = window.jQuery;
		var projects = {};
		var rcItems = (lookups.projects || []).map(function (p) {
			var title = String(p.project_title || '').replace(/\s+/g, ' ').trim();
			projects[p.responsibility_code] = title;
			return { label: p.responsibility_code, value: p.responsibility_code, title: title, find: (p.responsibility_code + ' ' + title).toLowerCase() };
		});

		// Responsibility code fills the project title
		$('#responsibility_code').autocomplete({
			appendTo: '#ppmpModal',
			minLength: 0,
			source: function (req, resp) {
				var terms = req.term.toLowerCase().split(/\s+/).filter(Boolean);
				resp(rcItems.filter(function (it) { return terms.every(function (t) { return it.find.indexOf(t) !== -1; }); }).slice(0, 50));
			},
			select: function (event, ui) {
				$('#responsibility_code').val(ui.item.value).removeClass('is-invalid');
				$('#project_title').val(ui.item.title);
				return false;
			}
		}).on('focus', function () { $(this).autocomplete('search', this.value); })
			.autocomplete('instance')._renderItem = function (ul, item) {
				return $('<li>').append('<div><b>' + esc(item.value) + '</b><span class="ppmp-ac-sub">' + esc(item.title) + '</span></div>').appendTo(ul);
			};

		// Typed by hand: follow the code when it is a known one, clear the title when it is not
		$('#responsibility_code').on('change', function () {
			var code = this.value.trim();
			$('#project_title').val(Object.prototype.hasOwnProperty.call(projects, code) ? projects[code] : '');
		});

		$('#submitted_by').autocomplete({ source: lookups.signatories || [], appendTo: '#ppmpModal', minLength: 0 })
			.on('focus', function () { $(this).autocomplete('search', ''); });
	};

	//PPMP - OPEN EDITOR (new, or load one PPMP with its items)
	function setVal(id, v) { document.getElementById(id).value = v == null ? '' : v; }

	function fill(hd) {
		var form = document.getElementById('ppmpForm');
		form.reset();
		form.classList.remove('was-validated');

		['recid', 'ppmpno', 'end_user', 'responsibility_code', 'project_title', 'prepared_by', 'submitted_by'].forEach(function (f) { setVal(f, hd ? hd[f] : ''); });

		var sel = document.getElementById('fiscal_year');
		var current = hd ? String(hd.fiscal_year || '') : '';
		var years = (lookups.fiscal_years || []).map(String);
		// keep a saved year that is no longer in the list
		if (current && years.indexOf(current) === -1) { years.push(current); }
		sel.innerHTML = '<option value="">Choose...</option>' + years.map(function (y) { return '<option value="' + esc(y) + '">' + esc(y) + '</option>'; }).join('');
		sel.value = current;

		document.getElementById('is_indicative').checked = !!hd && String(hd.is_indicative) === '1';
		document.getElementById('is_final').checked = !!hd && String(hd.is_final) === '1';
	}

	function setHeader(hd) {
		var $ = window.jQuery;

		$('#ppmpModalTitle').text(hd ? (hd.ppmpno ? 'PPMP ' + hd.ppmpno : 'PPMP #' + hd.recid) : 'New PPMP');
		$('#ppmpModalSub').html(hd
			? esc(hd.end_user) + ' · ' + esc(hd.fiscal_year) + ' &nbsp;' + chip(statusOf(hd))
			: 'Fill in the project details, then the items to procure.');

		$('#ppmpPrintBtn').toggleClass('d-none', !hd);

		var banner = $('#ppmpBanner').removeClass('alert-info d-none');
		if (!canSave()) {
			banner.addClass('alert-info').html('<b>View only</b> — you don\'t have permission to ' + (record ? 'edit' : 'add') + ' PPMPs.');
		} else {
			banner.addClass('d-none');
		}

		$('#submitBtn').toggleClass('d-none', !canSave()).text(hd ? 'Save changes' : 'Create PPMP');
	}

	this.__open_ppmp = function (recid) {
		var $ = window.jQuery;
		var modalEl = document.getElementById('ppmpModal');
		bootstrap.Tab.getOrCreateInstance(document.querySelector('[data-bs-target="#ppmpPaneDetails"]')).show();

		function show(hd) {
			record = hd;
			fill(hd);
			setHeader(hd);
			$('#ppmpGrid').html(gridHtml(hd ? hd.items : null));
			applyLocks();
			recalc();
			bootstrap.Modal.getOrCreateInstance(modalEl).show();
		}

		if (!recid) { show(null); return; }

		$.getJSON(mesiteurl + 'myppmp', { meaction: 'MAIN-LOAD', recid: recid })
			.done(show)
			.fail(function (xhr) {
				toastr.error((xhr.responseJSON && xhr.responseJSON.error) || 'Could not load this PPMP.', 'Oops!', { closeButton: true });
			});
	};

	//PPMP - SAVE (same payload as before: header fields + one "x|x" row per item)
	function collectItems() {
		var $ = window.jQuery;
		var out = [];
		$('#ppmpGrid tbody tr').each(function () {
			var tr = $(this);
			var values = FIELDS.map(function (f) { var x = tr.find('.g-' + f).val(); return x == null ? '' : x; });
			if (values.every(function (v) { return !String(v).trim(); })) { return; }  // skip untouched lines instead of saving empty rows
			out.push(values.join('x|x'));
		});
		return out;
	}

	this.__ppmp_saving = function () {
		var $ = window.jQuery;
		var form = document.getElementById('ppmpForm');
		form.addEventListener('submit', function (e) {
			e.preventDefault();
			if (!form.checkValidity()) {
				form.classList.add('was-validated');
				bootstrap.Tab.getOrCreateInstance(document.querySelector('[data-bs-target="#ppmpPaneDetails"]')).show();
				var bad = form.querySelector(':invalid');
				if (bad) { bad.focus(); }
				return;
			}

			var val = function (id) { return document.getElementById(id).value; };
			var mparam = {
				recid: val('recid'),
				ppmpno: val('ppmpno'),
				end_user: val('end_user'),
				fiscal_year: val('fiscal_year'),
				project_title: val('project_title'),
				responsibility_code: val('responsibility_code'),
				prepared_by: val('prepared_by'),
				submitted_by: val('submitted_by'),
				ppmpdtdata: collectItems(),
				is_indicative: document.getElementById('is_indicative').checked ? 1 : 0,
				is_final: document.getElementById('is_final').checked ? 1 : 0,
				meaction: 'PPMP-SAVE'
			};

			if (!mparam.ppmpdtdata.length) {
				bootstrap.Tab.getOrCreateInstance(document.querySelector('[data-bs-target="#ppmpPaneItems"]')).show();
				toastr.warning('Add at least one item before saving.', 'No items yet', { progressBar: true, closeButton: true, timeOut: 2500 });
				return;
			}

			var $btn = $('#submitBtn'), label = $btn.html();
			var restore = function () { $btn.prop('disabled', false).html(label); };
			$btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Saving...');
			$.ajax({
				type: 'POST',
				url: mesiteurl + 'myppmp',
				data: mparam,
				global: false,
				cache: false,
				success: function (data) {
					$('.me-outp-msg').html(data);
					// a saved PPMP reloads the page, so the button stays off until then
					if (String(data).indexOf('window.location') === -1) { restore(); }
				},
				error: function (xhr, status, error) {
					toastr.error('The PPMP could not be saved. ' + (error || ''), 'Oops!', { closeButton: true });
					restore();
				}
			});
		});
	};

	//PPMP - PRINT
	this.__showPdfInModal = function (recid) {
		document.getElementById('ppmpPrintFrame').src = mesiteurl + 'myppmp?meaction=PPMP-PRINT&recid=' + encodeURIComponent(recid);
		bootstrap.Modal.getOrCreateInstance(document.getElementById('ppmpPrintModal')).show();
	};

	this.__ppmp_print = function () {
		document.getElementById('ppmpPrintBtn').addEventListener('click', function () { if (record) { __mysys_ppmp_ent.__showPdfInModal(record.recid); } });
	};
}

// jQuery, DataTables, jQuery UI and Bootstrap load in the footer, after this file
document.addEventListener('DOMContentLoaded', function () {
	__mysys_ppmp_ent.__ppmp_table();
	__mysys_ppmp_ent.__ppmp_grid();
	__mysys_ppmp_ent.__ppmp_details();
	__mysys_ppmp_ent.__ppmp_saving();
	__mysys_ppmp_ent.__ppmp_print();

	// Deep link: ?recid= opens that PPMP right away
	if (window.__ppmp_open) { __mysys_ppmp_ent.__open_ppmp(window.__ppmp_open); }
});
