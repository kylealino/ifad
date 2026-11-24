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

	// How much of a PPMP the PRs have used (quantities; MyPPMPUsageModel)
	function fullyUsed(r) { return num(r.total_qty) > 0 && num(r.used_qty) >= num(r.total_qty); }
	function usageHtml(r) {
		var total = num(r.total_qty), used = num(r.used_qty);
		if (!used) { return ''; }
		var pct = total ? Math.min(100, Math.round(used / total * 100)) : 0;
		return '<div class="small ' + (fullyUsed(r) ? 'text-danger fw-semibold' : 'text-muted') + ' mt-1">'
			+ (fullyUsed(r) ? 'Fully used' : 'Used ' + used.toLocaleString() + ' of ' + total.toLocaleString()) + '</div>'
			+ '<div class="ppmp-used-bar" title="' + pct + '% used by PRs"><span style="width:' + pct + '%"></span></div>';
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
			return inYear(row) && (filter === 'all' || (filter === 'fully_used' ? fullyUsed(row) : row.status === filter));
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
					render: function (v, type, r) { return type === 'display' ? chip(v) + usageHtml(r) : (STATUS[v] || STATUS.unmarked)[1] + (fullyUsed(r) ? ' Fully used' : ''); }
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
			+ '<td>' + text('mop', 'Mode of procurement', ' maxlength="100" placeholder="Pick a mode"') + '</td>'
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
		$('#ppmpUploadBtn').toggleClass('d-none', !canSave());
	}

	//PPMP - ITEM VERSIONS: the original items (the grid), then one version after each
	// PR that used this PPMP, oldest first, with what that PR took and what is left
	function prLabel(v) { return v.prno ? 'PR ' + v.prno : 'PR #' + v.pr_id; }

	function renderVersions(hd) {
		var bar = document.getElementById('ppmpVersions');
		var list = hd && hd.usage ? hd.usage.versions || [] : [];
		if (!list.length) { bar.classList.add('d-none'); bar.innerHTML = ''; showVersion(hd, -1); return; }
		bar.classList.remove('d-none');
		bar.innerHTML = '<button type="button" class="active" data-version="-1" role="tab">Original<small>As planned</small></button>'
			+ list.map(function (v, i) {
				return '<button type="button" data-version="' + i + '" role="tab"' + (v.fully_used ? ' class="is-done"' : '') + '>'
					+ 'Version ' + (i + 1) + ' · ' + esc(prLabel(v))
					+ '<small>' + (v.fully_used ? 'Fully used' : esc(fmtDate(v.pr_date || v.added_at))) + '</small></button>';
			}).join('');
		showVersion(hd, -1);
	}

	function showVersion(hd, index) {
		var $ = window.jQuery;
		$('#ppmpVersions button').removeClass('active').filter('[data-version="' + index + '"]').addClass('active');
		var original = index < 0;
		$('#ppmpGrid').toggleClass('d-none', !original);
		$('#ppmpVersionView').toggleClass('d-none', original);
		if (original) { return; }

		var versions = hd.usage.versions;
		var v = versions[index];
		var before = index > 0 ? versions[index - 1].left : null;
		var items = (hd.items || []).filter(function (it) { return String(it.item_desc || '').trim() !== ''; });
		var tot = { planned: 0, took: 0, left: 0, amount: 0 };

		var body = items.map(function (it) {
			var planned = num(it.quantity);
			var took = num(v.used[it.recid]);
			var left = v.left[it.recid] != null ? num(v.left[it.recid]) : planned;
			var prior = before && before[it.recid] != null ? planned - num(before[it.recid]) : 0;
			var amount = left * num(it.unit_cost);
			tot.planned += planned; tot.took += took; tot.left += left; tot.amount += amount;
			return '<tr' + (left <= 0 ? ' class="is-zero"' : '') + '>'
				+ '<td style="overflow-wrap:anywhere">' + esc(it.item_desc) + '</td>'
				+ '<td>' + esc(it.size || '') + '</td>'
				+ '<td class="num">' + planned.toLocaleString() + '</td>'
				+ '<td class="num">' + (prior ? prior.toLocaleString() : '—') + '</td>'
				+ '<td class="num' + (took ? ' took' : '') + '">' + (took ? took.toLocaleString() : '—') + '</td>'
				+ '<td class="num left">' + left.toLocaleString() + '</td>'
				+ '<td class="num">' + money(num(it.unit_cost)) + '</td>'
				+ '<td class="num">' + money(amount) + '</td></tr>';
		}).join('');

		$('#ppmpVersionView').html(
			'<div class="ppmp-grid-head"><div><div class="ppmp-grid-title">Version ' + (index + 1) + ' · after ' + esc(prLabel(v))
				+ ' <span class="ppmp-grid-sub">' + esc(fmtDate(v.pr_date || v.added_at)) + '</span>'
				+ (v.fully_used ? ' <span class="fods-chip tone-red ms-1">Fully used</span>' : '') + '</div></div>'
				+ '<a class="btn btn-sm btn-fods-ghost" href="' + mesiteurl + 'mypr?meaction=PR-MAIN&recid=' + encodeURIComponent(v.pr_id) + '" target="_blank" rel="noopener"><i class="ti ti-external-link me-1"></i>Open ' + esc(prLabel(v)) + '</a></div>'
			+ (num(v.over) > 0 ? '<div class="ppmp-version-note"><i class="ti ti-alert-triangle me-1"></i>This PR asked for ' + num(v.over).toLocaleString() + ' more than was left on this PPMP.</div>' : '')
			+ '<div class="ppmp-grid-wrap"><table class="ppmp-version-table"><thead><tr>'
			+ '<th>Description</th><th>Unit</th><th class="num">Planned</th><th class="num">Used before</th><th class="num">Used by this PR</th><th class="num">Left</th><th class="num">Unit cost</th><th class="num">Amount left</th>'
			+ '</tr></thead><tbody>' + body + '</tbody>'
			+ '<tfoot><tr><td colspan="2">Total</td><td class="num">' + tot.planned.toLocaleString() + '</td><td></td><td class="num">' + tot.took.toLocaleString() + '</td>'
			+ '<td class="num">' + tot.left.toLocaleString() + '</td><td></td><td class="num">' + money(tot.amount) + '</td></tr></tfoot></table></div>');
	}

	this.__ppmp_versions = function () {
		window.jQuery('#ppmpVersions').on('click', 'button', function () { if (record) { showVersion(record, +this.getAttribute('data-version')); } });
	};

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
			renderVersions(hd);
			renderDocs(hd ? hd.attachments : null);
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

	//PPMP - IMPORT FROM EXCEL: fills the editor from the office's PPMP spreadsheet (.xlsx, .xls or .csv,
	// the PPMP form layout). Header: Fiscal Year, PPMP No., End-User, RC (kept only when it is a
	// known responsibility code), Indicative / Final when marked. Items: the rows under
	// "Column 1 ... Column 12" until TOTAL BUDGET / Prepared by. Signatories: the name under
	// "Prepared by:" and "Submitted by:". Nothing is saved until the user clicks Save.
	function loadXlsx() {
		return new Promise(function (resolve, reject) {
			if (window.XLSX) { resolve(window.XLSX); return; }
			var s = document.createElement('script');
			s.src = window.__ppmp_xlsx;   // local copy of SheetJS, loaded on the first upload
			s.onload = function () { resolve(window.XLSX); };
			s.onerror = function () { reject(new Error('The spreadsheet reader could not be loaded.')); };
			document.head.appendChild(s);
		});
	}

	function cellText(v) {
		if (v == null) { return ''; }
		if (v instanceof Date) { return v.getFullYear() + '-' + String(v.getMonth() + 1).padStart(2, '0'); }
		return String(v).replace(/\s+/g, ' ').trim();
	}

	function lc(v) { return cellText(v).toLowerCase(); }

	// "9,360.00", "₱ 780", 780 -> 780
	function toNumber(v) {
		if (typeof v === 'number') { return v; }
		var n = parseFloat(cellText(v).replace(/[₱P,\s]/gi, ''));
		return isNaN(n) ? '' : n;
	}

	// 01/2027, 1/2027, 2027-01, Jan 2027, a date cell or an Excel date number -> 2027-01
	const MONTHS = ['jan', 'feb', 'mar', 'apr', 'may', 'jun', 'jul', 'aug', 'sep', 'oct', 'nov', 'dec'];
	function toMonth(v) {
		if (v instanceof Date) { return cellText(v); }
		if (typeof v === 'number' && v > 20000 && v < 80000) {
			var d = new Date(Math.round((v - 25569) * 86400000));
			return d.getUTCFullYear() + '-' + String(d.getUTCMonth() + 1).padStart(2, '0');
		}
		var s = cellText(v), m;
		if ((m = s.match(/^(\d{1,2})\s*[\/\-.]\s*(?:\d{1,2}\s*[\/\-.]\s*)?(\d{4})$/))) { return m[2] + '-' + m[1].padStart(2, '0'); }
		if ((m = s.match(/^(\d{4})\s*[\/\-.]\s*(\d{1,2})/))) { return m[1] + '-' + m[2].padStart(2, '0'); }
		if ((m = s.match(/^([a-z]{3})[a-z]*\.?\s*,?\s*(\d{4})$/i)) && MONTHS.indexOf(m[1].toLowerCase()) !== -1) {
			return m[2] + '-' + String(MONTHS.indexOf(m[1].toLowerCase()) + 1).padStart(2, '0');
		}
		return '';
	}

	// "03/2027" -> from = to = 2027-03; "01/2027 to 03/2027" or "01/2027 - 03/2027" -> both
	function toPeriod(v) {
		if (typeof v !== 'string') { var one = toMonth(v); return [one, one]; }
		var parts = cellText(v).match(/\d{1,2}\s*\/\s*(?:\d{1,2}\s*\/\s*)?\d{4}|\d{4}\s*-\s*\d{1,2}|[a-z]{3,9}\.?\s*,?\s*\d{4}/gi) || [];
		var from = parts.length ? toMonth(parts[0].replace(/\s+/g, '')) : '';
		var to = parts.length > 1 ? toMonth(parts[parts.length - 1].replace(/\s+/g, '')) : from;
		return [from, to || from];
	}

	function toYesNo(v) {
		var s = lc(v);
		if (s === 'yes' || s === 'y') { return 'Yes'; }
		if (s === 'no' || s === 'n') { return 'No'; }
		return cellText(v);
	}

	// GAA is the General Fund (Fund 101); the fund codes print as 101 / 184 / 174
	function toFund(v) {
		var s = lc(v);
		if (s === 'gaa' || s === '101' || s === 'general fund') { return 'General Fund'; }
		if (s === '184' || s === 'trust fund') { return 'Trust Fund'; }
		if (s === '174' || s.indexOf('project fund') === 0) { return 'Project Fund (Externally Funded)'; }
		return cellText(v);
	}

	// The value of a "Label: value" cell, or the next filled cell to its right
	function valueAfter(grid, r, c, label) {
		var own = cellText(grid[r][c]).replace(label, '').replace(/^\s*[:.]?\s*/, '');
		if (own) { return own; }
		for (var j = c + 1; j < grid[r].length; j++) {
			var t = cellText(grid[r][j]);
			if (t) { return t.replace(/^:\s*/, ''); }
		}
		return '';
	}

	function parsePPMP(grid) {
		var out = { header: {}, items: [], notes: [] };
		var find = function (re, from, to) {
			for (var r = from || 0; r < Math.min(to == null ? grid.length : to, grid.length); r++) {
				for (var c = 0; c < (grid[r] || []).length; c++) { if (re.test(cellText(grid[r][c]))) { return [r, c]; } }
			}
			return null;
		};

		// the item columns: the "Column 1 ... Column 12" row under the column titles
		var colRow = -1, cols = {};
		for (var r = 0; r < grid.length && colRow < 0; r++) {
			(grid[r] || []).forEach(function (v, c) {
				var m = cellText(v).match(/^column\s*(\d{1,2})$/i);
				if (m) { cols[+m[1]] = c; colRow = r; }
			});
		}
		if (colRow < 0 || cols[1] == null) { return null; }

		// quantity, size and unit cost share "Column 3": size and unit cost are found by their titles
		var titleOf = function (re) {
			for (var r = Math.max(0, colRow - 3); r < colRow; r++) {
				for (var c = 0; c < (grid[r] || []).length; c++) { if (re.test(cellText(grid[r][c]))) { return c; } }
			}
			return null;
		};
		var qty = cols[3];
		var at = {
			item_desc: cols[1], item_type: cols[2], quantity: qty,
			size: titleOf(/^size$/i) != null ? titleOf(/^size$/i) : (qty != null ? qty + 1 : null),
			unit_cost: titleOf(/^unit cost$/i) != null ? titleOf(/^unit cost$/i) : (qty != null ? qty + 2 : null),
			mop: cols[4], is_preproc: cols[5], proc_start: cols[6], proc_end: cols[7], delivery: cols[8],
			funding_source: cols[9], estimated_budget: cols[10], attached_document: cols[11], remarks: cols[12]
		};
		var get = function (row, k) { return at[k] == null ? '' : row[at[k]]; };

		// header (above the item columns)
		var hit;
		if ((hit = find(/^fiscal\s*year/i, 0, colRow))) {
			var fy = valueAfter(grid, hit[0], hit[1], /^fiscal\s*year/i).match(/\d{4}/);
			if (fy) { out.header.fiscal_year = fy[0]; }
		}
		if ((hit = find(/^ppmp\s*no\.?/i, 0, colRow))) { out.header.ppmpno = valueAfter(grid, hit[0], hit[1], /^ppmp\s*no\.?/i); }
		if ((hit = find(/^end-?\s*user/i, 0, colRow))) { out.header.end_user = valueAfter(grid, hit[0], hit[1], /^end-?\s*user[^:]*:?/i); }
		if ((hit = find(/^rc$|^responsibility\s*code/i, 0, colRow))) { out.header.rc = valueAfter(grid, hit[0], hit[1], /^rc$|^responsibility\s*code:?/i); }
		if ((hit = find(/^projects?,?\s*programs/i, 0, colRow))) { out.header.ppa = valueAfter(grid, hit[0], hit[1], /^projects?,?\s*programs?\s*and\s*activities\s*:?/i); }
		// Indicative / Final count only when a mark (x, ✓) sits beside the word
		['indicative', 'final'].forEach(function (k) {
			var h = find(new RegExp('^' + k + '$', 'i'), 0, colRow);
			if (!h) { return; }
			var row = grid[h[0]];
			var marked = [row[h[1] - 1], row[h[1] + 1]].some(function (v) { return /^(x|✓|✔|☑|☒|■|\/|yes)$/i.test(cellText(v)); });
			out.header['is_' + k] = marked;
		});

		// items: from below the column numbers until the totals or the signatures
		for (var i = colRow + 1; i < grid.length; i++) {
			var row = grid[i] || [];
			var joined = row.map(lc).join(' ');
			if (/total budget|prepared by|submitted by/.test(joined)) { break; }
			var desc = cellText(get(row, 'item_desc'));
			if (!desc) { continue; }
			var period = toPeriod(get(row, 'delivery'));
			var item = {
				item_desc: desc.slice(0, 250),
				item_type: cellText(get(row, 'item_type')),
				quantity: toNumber(get(row, 'quantity')),
				size: cellText(get(row, 'size')),
				unit_cost: toNumber(get(row, 'unit_cost')),
				mop: cellText(get(row, 'mop')),
				is_preproc: toYesNo(get(row, 'is_preproc')),
				proc_start: toMonth(get(row, 'proc_start')),
				proc_end: toMonth(get(row, 'proc_end')),
				expected_delivery_from: period[0],
				expected_delivery_to: period[1],
				funding_source: toFund(get(row, 'funding_source')),
				estimated_budget: toNumber(get(row, 'estimated_budget')),
				attached_document: cellText(get(row, 'attached_document')),
				remarks: cellText(get(row, 'remarks'))
			};
			if (item.estimated_budget === '' && item.quantity !== '' && item.unit_cost !== '') {
				item.estimated_budget = Math.round(item.quantity * item.unit_cost * 100) / 100;
			}
			if (item.quantity !== '' && Math.round(item.quantity) !== item.quantity) {
				out.notes.push('"' + desc.slice(0, 40) + '": quantity ' + item.quantity + ' was rounded to a whole number.');
				item.quantity = Math.round(item.quantity);
			}
			out.items.push(item);
		}

		// signatories: the first name under each label (same column, within a few rows)
		var nameUnder = function (re) {
			var h = find(re, colRow);
			if (!h) { return ''; }
			var own = cellText(grid[h[0]][h[1]]).replace(re, '').replace(/^\s*:?\s*/, '');
			if (own) { return own; }
			for (var r = h[0] + 1; r < Math.min(grid.length, h[0] + 6); r++) {
				var t = cellText((grid[r] || [])[h[1]]);
				if (t && !/^signature/i.test(t)) { return t; }
			}
			return '';
		};
		out.header.prepared_by = nameUnder(/^prepared\s*by\s*:?/i);
		out.header.submitted_by = nameUnder(/^submitted\s*by\s*:?/i);
		return out;
	}

	function applyImport(data, fileName) {
		var $ = window.jQuery;
		var h = data.header;
		var notes = data.notes.slice();
		var set = function (id, v) { if (v) { setVal(id, String(v).slice(0, +(document.getElementById(id).getAttribute('maxlength') || 250))); } };

		set('ppmpno', h.ppmpno);
		set('end_user', h.end_user);
		set('prepared_by', h.prepared_by);
		set('submitted_by', h.submitted_by);
		if (h.fiscal_year) {
			var sel = document.getElementById('fiscal_year');
			if (!sel.querySelector('option[value="' + h.fiscal_year + '"]')) { sel.insertAdjacentHTML('beforeend', '<option value="' + esc(h.fiscal_year) + '">' + esc(h.fiscal_year) + '</option>'); }
			sel.value = h.fiscal_year;
		}
		if (h.is_indicative) { document.getElementById('is_indicative').checked = true; }
		if (h.is_final) { document.getElementById('is_final').checked = true; }

		// the responsibility code: only a known one (it sets the project title)
		var known = (lookups.projects || []).filter(function (p) { return String(p.responsibility_code).trim().toLowerCase() === String(h.rc || '').trim().toLowerCase(); })[0];
		if (known) {
			setVal('responsibility_code', known.responsibility_code);
			setVal('project_title', String(known.project_title || '').replace(/\s+/g, ' ').trim());
		} else if (h.rc || h.ppa) {
			notes.push('Pick the responsibility code: the file has "' + (h.rc || h.ppa) + '", which is not a responsibility code.');
		}
		if (!h.ppmpno && !document.getElementById('ppmpno').value) { notes.push('The file has no PPMP no.: type one before saving.'); }

		$('#ppmpGrid').html(gridHtml(data.items));
		applyLocks();
		recalc();
		bootstrap.Tab.getOrCreateInstance(document.querySelector('[data-bs-target="#ppmpPaneItems"]')).show();

		var total = data.items.reduce(function (t, it) { return t + num(it.estimated_budget); }, 0);
		toastr.success(data.items.length + ' item' + (data.items.length === 1 ? '' : 's') + ' (' + money(total) + ') from ' + esc(fileName) + '. Check them, then save.',
			'Imported from Excel', { progressBar: true, closeButton: true, timeOut: 4000 });
		if (notes.length) {
			toastr.warning(notes.map(esc).join('<br>'), 'Before saving', { closeButton: true, timeOut: 0, extendedTimeOut: 0 });
		}
	}

	function readFile(file) {
		var reader = new FileReader();
		reader.onload = function () {
			loadXlsx().then(function (XLSX) {
				var wb = XLSX.read(new Uint8Array(reader.result), { type: 'array', cellDates: true });
				// the first sheet that has the PPMP item columns
				var data = null;
				wb.SheetNames.some(function (n) {
					data = parsePPMP(XLSX.utils.sheet_to_json(wb.Sheets[n], { header: 1, raw: true, defval: '', blankrows: true }));
					return data && data.items.length;
				});
				if (!data || !data.items.length) {
					toastr.error('No PPMP items were found in ' + esc(file.name) + '. Use the PPMP form, with the "Column 1 ... Column 12" row above the items.', 'Cannot read the file', { closeButton: true, timeOut: 6000 });
					return;
				}
				applyImport(data, file.name);
			}).catch(function (err) {
				toastr.error(esc(err && err.message ? err.message : 'The file could not be read.'), 'Cannot read the file', { closeButton: true });
			});
		};
		reader.onerror = function () { toastr.error('The file could not be read.', 'Cannot read the file', { closeButton: true }); };
		reader.readAsArrayBuffer(file);
	}

	this.__ppmp_upload = function () {
		var $ = window.jQuery;
		var input = document.getElementById('ppmpUploadFile');

		$('#ppmpUploadBtn').on('click', function () {
			var count = $('#ppmpGrid tbody tr').length;
			if (count && !window.confirm('Replace the ' + count + ' item' + (count === 1 ? '' : 's') + ' in this PPMP with the items in the file?')) { return; }
			input.value = '';
			input.click();
		});

		input.addEventListener('change', function () {
			var file = this.files && this.files[0];
			if (!file) { return; }
			if (!/\.(xlsx|xls|csv)$/i.test(file.name)) {
				toastr.error('Pick an Excel (.xlsx, .xls) or CSV file.', 'Cannot read the file', { closeButton: true });
				return;
			}
			readFile(file);
		});
	};

	//PPMP - DOCUMENTS: supporting files kept with a saved PPMP. Adding and removing take
	// effect right away (no Save needed); PDFs and images open in a preview, others download.
	const DOC_KINDS = [
		[/pdf/, 'ti-file-text', 'tone-red'],
		[/^image\//, 'ti-photo', 'tone-indigo'],
		[/word/, 'ti-file-description', 'tone-indigo'],
		[/sheet|excel|csv/, 'ti-file-spreadsheet', 'tone-green'],
		[/presentation|powerpoint/, 'ti-presentation', 'tone-amber'],
		[/zip/, 'ti-file-zip', 'tone-slate']
	];
	const docLimits = window.__ppmp_doc_limits || { mb: 10, files: 10 };

	function docKind(mime) {
		for (var i = 0; i < DOC_KINDS.length; i++) { if (DOC_KINDS[i][0].test(mime || '')) { return DOC_KINDS[i]; } }
		return [null, 'ti-file', 'tone-slate'];
	}

	function fileSize(n) {
		n = num(n);
		return n >= 1048576 ? (n / 1048576).toFixed(1) + ' MB' : Math.max(1, Math.round(n / 1024)) + ' KB';
	}

	function canPreview(d) { return String(d.is_image) === '1' || d.mime_type === 'application/pdf'; }

	function renderDocs(list) {
		var $ = window.jQuery;
		var saved = !!record;
		var editable = saved && !!perms.attach;
		list = list || [];
		$('#ppmpDocCount').text(list.length ? list.length : '');
		$('#ppmpDocsNew').toggleClass('d-none', saved);
		$('#ppmpDocsBody').toggleClass('d-none', !saved);
		$('#ppmpDrop').toggleClass('d-none', !editable);
		if (!saved) { $('#ppmpDocs').empty(); return; }
		if (!list.length) {
			$('#ppmpDocs').html('<div class="ppmp-docs-empty">No supporting documents yet.</div>');
			return;
		}
		$('#ppmpDocs').html(list.map(function (d) {
			var k = docKind(d.mime_type);
			var url = mesiteurl + d.url;
			return '<div class="ppmp-doc" data-doc="' + esc(d.recid) + '">'
				+ '<span class="ppmp-doc-icon fods-chip ' + k[2] + '"><i class="ti ' + k[1] + '"></i></span>'
				+ '<div class="ppmp-doc-main"><button type="button" class="ppmp-doc-name ppmp-doc-open" title="' + esc(d.orig_name) + '">' + esc(d.orig_name) + '</button>'
				+ '<div class="ppmp-doc-meta">' + fileSize(d.file_size) + ' · ' + esc(d.added_by_name || d.added_by) + ' · ' + esc(fmtDate(d.added_at)) + '</div></div>'
				+ '<div class="ppmp-doc-acts">'
				+ (canPreview(d) ? '<button type="button" class="fods-icon-btn ppmp-doc-open" title="View"><i class="ti ti-eye"></i></button>' : '')
				+ '<a class="fods-icon-btn" href="' + esc(url) + '&dl=1" title="Download"><i class="ti ti-download"></i></a>'
				+ (editable ? '<button type="button" class="fods-icon-btn is-danger ppmp-doc-remove" title="Remove"><i class="ti ti-trash"></i></button>' : '')
				+ '</div></div>';
		}).join(''));
	}

	function openDoc(d) {
		var url = mesiteurl + d.url;
		if (!canPreview(d)) { window.location.href = url + '&dl=1'; return; }
		document.getElementById('ppmpDocTitle').textContent = d.orig_name;
		document.getElementById('ppmpDocDownload').setAttribute('href', url + '&dl=1');
		document.getElementById('ppmpDocView').innerHTML = String(d.is_image) === '1'
			? '<img src="' + esc(url) + '" alt="' + esc(d.orig_name) + '">'
			: '<iframe src="' + esc(url) + '" title="' + esc(d.orig_name) + '"></iframe>';
		bootstrap.Modal.getOrCreateInstance(document.getElementById('ppmpDocModal')).show();
	}

	function uploadDocs(files) {
		var $ = window.jQuery;
		files = Array.prototype.slice.call(files || []);
		if (!files.length || !record || !perms.attach) { return; }
		if (files.length > docLimits.files) {
			toastr.warning('Add up to ' + docLimits.files + ' files at a time.', 'Too many files', { closeButton: true, timeOut: 2500 });
			return;
		}
		var big = files.filter(function (f) { return f.size > docLimits.mb * 1048576; })[0];
		if (big) {
			toastr.warning(esc(big.name) + ' is over ' + docLimits.mb + ' MB.', 'File too big', { closeButton: true, timeOut: 2500 });
			return;
		}
		var fd = new FormData();
		fd.append('meaction', 'DOC-UPLOAD');
		fd.append('recid', record.recid);
		files.forEach(function (f) { fd.append('attachments[]', f, f.name); });
		var drop = $('#ppmpDrop').addClass('is-busy');
		$.ajax({ type: 'POST', url: mesiteurl + 'myppmp', data: fd, processData: false, contentType: false, dataType: 'json', global: false })
			.done(function (res) {
				record.attachments = res.attachments;
				renderDocs(res.attachments);
				toastr.success(files.length + ' document' + (files.length === 1 ? '' : 's') + ' added.', 'Saved', { progressBar: true, timeOut: 2000 });
			})
			.fail(function (xhr) {
				toastr.error(esc((xhr.responseJSON && xhr.responseJSON.error) || 'The files could not be uploaded.'), 'Cannot add the files', { closeButton: true, timeOut: 4000 });
			})
			.always(function () { drop.removeClass('is-busy'); });
	}

	// Files pasted (screenshots too): clipboardData.files, or items when that is empty
	function clipboardFiles(e) {
		var cd = (e.originalEvent || e).clipboardData;
		if (!cd) { return []; }
		var files = Array.prototype.slice.call(cd.files || []);
		if (!files.length && cd.items) {
			Array.prototype.forEach.call(cd.items, function (it) { if (it.kind === 'file') { var f = it.getAsFile(); if (f) { files.push(f); } } });
		}
		var stamp = new Date().toISOString().replace(/[-:]/g, '').replace('T', '-').slice(0, 15);
		return files.map(function (f) {
			return (!f.name || f.name === 'image.png') ? new File([f], 'screenshot-' + stamp + '.png', { type: f.type || 'image/png' }) : f;
		});
	}

	this.__ppmp_docs = function () {
		var $ = window.jQuery;
		var input = document.getElementById('ppmpDocFile');
		var drop = $('#ppmpDrop');

		$('#ppmpDocAdd').on('click', function () { input.value = ''; input.click(); });
		input.addEventListener('change', function () { uploadDocs(this.files); });

		drop.on('dragenter dragover', function (e) { e.preventDefault(); drop.addClass('is-over'); })
			.on('dragleave dragend', function () { drop.removeClass('is-over'); })
			.on('drop', function (e) {
				e.preventDefault();
				drop.removeClass('is-over');
				uploadDocs(e.originalEvent.dataTransfer && e.originalEvent.dataTransfer.files);
			});
		// paste anywhere in the Documents tab
		$('#ppmpModal').on('paste', function (e) {
			if (!$('#ppmpPaneDocs').hasClass('active') || $('#ppmpDrop').hasClass('d-none')) { return; }
			var files = clipboardFiles(e);
			if (files.length) { e.preventDefault(); uploadDocs(files); }
		});

		$('#ppmpDocs').on('click', '.ppmp-doc-open', function () {
			var id = $(this).closest('.ppmp-doc').data('doc');
			var d = (record && record.attachments || []).filter(function (x) { return String(x.recid) === String(id); })[0];
			if (d) { openDoc(d); }
		});
		$('#ppmpDocs').on('click', '.ppmp-doc-remove', function () {
			var id = $(this).closest('.ppmp-doc').data('doc');
			var d = (record && record.attachments || []).filter(function (x) { return String(x.recid) === String(id); })[0];
			if (!d || !window.confirm('Remove "' + d.orig_name + '" from this PPMP?')) { return; }
			$.ajax({ type: 'POST', url: mesiteurl + 'myppmp', data: { meaction: 'DOC-REMOVE', id: id }, dataType: 'json', global: false })
				.done(function (res) {
					record.attachments = res.attachments;
					renderDocs(res.attachments);
					toastr.info('Document removed.', '', { progressBar: true, timeOut: 2000 });
				})
				.fail(function (xhr) {
					toastr.error(esc((xhr.responseJSON && xhr.responseJSON.error) || 'The document could not be removed.'), 'Oops!', { closeButton: true });
				});
		});

		// the preview stops loading when it closes
		document.getElementById('ppmpDocModal').addEventListener('hidden.bs.modal', function () { document.getElementById('ppmpDocView').innerHTML = ''; });
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
	__mysys_ppmp_ent.__ppmp_versions();
	__mysys_ppmp_ent.__ppmp_upload();
	__mysys_ppmp_ent.__ppmp_docs();

	// Deep link: ?recid= opens that PPMP right away
	if (window.__ppmp_open) { __mysys_ppmp_ent.__open_ppmp(window.__ppmp_open); }
});
