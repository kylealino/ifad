var __mysys_budget_allotment_ent = new __mysys_budget_allotment_ent();
function __mysys_budget_allotment_ent() {
	const siteEl = document.getElementById('__siteurl');
	const mesiteurl = siteEl.getAttribute('data-mesiteurl');
	const uploadsurl = siteEl.getAttribute('data-uploads');
	const perms = window.__budget_perms || {};
	const lookups = window.__budget_lookups || {};
	const rows = window.__budget_data || [];

	let table = null;
	let filter = 'all';
	let record = null;          // allotment open in the editor (null = new)

	// Line-item grids. "payload" is the field budget_save() reads; each row
	// is sent as its values joined with "x|x" in the order the model expects.
	const GRIDS = {
		ps_direct:     { title: 'Direct cost', sub: 'Personnel Services', payload: 'budgetdtdata', kind: 'item', lookup: 'psuacs', tab: 'ps' },
		ps_indirect:   { title: 'Indirect cost', sub: 'Personnel Services', payload: 'budgetdtindirectdata', kind: 'item', lookup: 'psuacs', tab: 'ps' },
		mooe_direct:   { title: 'Direct cost', sub: 'Maintenance and Other Operating Expenses', payload: 'budgetmooedtdata', kind: 'item', lookup: 'mooeuacs', tab: 'mooe' },
		mooe_indirect: { title: 'Indirect cost', sub: 'Maintenance and Other Operating Expenses', payload: 'budgetmooeindirectdtdata', kind: 'item', lookup: 'mooeuacs', tab: 'mooe' },
		co_direct:     { title: 'Direct cost', sub: 'Capital Outlay', payload: 'budgetcodtdata', kind: 'co', tab: 'co' },
		co_indirect:   { title: 'Indirect cost', sub: 'Capital Outlay', payload: 'budgetindirectcodtdata', kind: 'co', tab: 'co' },
		ac:            { title: 'Admin cost', sub: 'Not included in the approved/proposed totals', payload: 'budgetacdtdata', kind: 'ac', tab: 'ac' }
	};

	const STATUS = {
		draft: ['tone-slate', 'Draft'],
		pending: ['tone-amber', 'Awaiting approval'],
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

	function statusOf(hd) {
		if (String(hd.is_disapproved) === '1') { return 'disapproved'; }
		if (String(hd.is_approved) === '1') { return 'approved'; }
		return hd.tagging === 'For Approval' ? 'pending' : 'draft';
	}

	//BUDGET - YEAR (the cards and the list follow the year picked in the page head)
	const YEAR_KEY = 'fods.year.mybudgetallotment';
	let year = String(new Date().getFullYear());
	try { year = localStorage.getItem(YEAR_KEY) || year; } catch (e) { /* storage blocked */ }

	// An allotment's year is when it was encoded
	function yearOf(r) {
		var y = String(r.added_at || '').slice(0, 4);
		return +y >= 2000 ? y : String(r.added_at || '').slice(0, 4);
	}

	function inYear(r) { return year === 'all' || yearOf(r) === year; }

	// Statuses behind each pill / card (any other key is a single status)
	const GROUPS = {};
	function inGroup(r, key) { return key === 'all' || (GROUPS[key] || [key]).indexOf(r.status) !== -1; }

	//BUDGET - STAT CARDS: Pending, Returned, Approved, Total amount
	function renderCards() {
		var $ = window.jQuery;
		var list = rows.filter(inYear);
		var amount = function (l) { return l.reduce(function (t, r) { return t + num(r.approved_budget); }, 0); };
		var count = function (status) { return list.filter(function (r) { return r.status === status; }).length; };
		var card = function (key, group, label) {
			var l = list.filter(function (r) { return inGroup(r, group); });
			$('[data-card-value="' + key + '"]').text(l.length.toLocaleString());
			$('[data-card-label="' + key + '"]').text(label() + ' · ' + moneyShort(amount(l)));
		};
		card('pending', 'pending', function () { return 'Pending'; });
		card('returned', 'disapproved', function () { return 'Returned'; });
		card('approved', 'approved', function () { return 'Approved'; });
		$('[data-card-value="total"]').text(money(amount(list)));
		$('[data-card-label="total"]').text('Total amount · ' + list.length.toLocaleString() + ' allotment' + (list.length === 1 ? '' : 's') + (count('draft') ? ' · incl. ' + count('draft').toLocaleString() + ' draft' + (count('draft') === 1 ? '' : 's') : ''));
	}

	function yearPicker(onChange) {
		var sel = document.getElementById('budgetYear');
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

	//BUDGET - LIST (search, status pills, stat tiles)
	this.__budget_table = function () {
		var $ = window.jQuery;

		$.fn.dataTable.ext.search.push(function (settings, data, idx) {
			if (settings.nTable.id !== 'budgetTable') { return true; }
			var row = settings.aoData[idx]._aData;
			return inYear(row) && inGroup(row, filter);
		});

		table = $('#budgetTable').DataTable({
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
					data: 'project_title', className: 'cell-primary',
					render: function (v, type, r) {
						if (type !== 'display') { return (v || '') + ' ' + (r.trxno || '') + ' ' + (r.division_name || ''); }
						return '<div style="min-width:0"><div class="fw-semibold text-dark" style="overflow-wrap:anywhere">' + esc(v) + '</div>'
							+ '<div class="small text-muted">' + esc(r.trxno) + (r.division_name ? ' · ' + esc(r.division_name) : '') + '</div></div>';
					}
				},
				{ data: 'responsibility_code', className: 'd-none d-lg-table-cell', render: function (v, type) { return type === 'display' ? esc(v || '—') : (v || ''); } },
				{
					data: 'fund_cluster_code', className: 'd-none d-md-table-cell',
					render: function (v, type) { return type === 'display' ? '<span class="fods-chip ' + (v === '07' ? 'tone-amber' : 'tone-indigo') + '">FC ' + esc(v) + '</span>' : v; }
				},
				{
					data: 'approved_budget', className: 'text-end cell-left',
					render: function (v, type) { return type === 'display' ? '<span class="text-nowrap fw-semibold">' + money(v) + '</span>' : num(v); }
				},
				{
					data: 'status', className: 'cell-right',
					render: function (v, type) {
						var s = STATUS[v] || STATUS.draft;
						return type === 'display' ? '<span class="fods-chip ' + s[0] + '"><span class="fods-dot"></span>' + s[1] + '</span>' : s[1];
					}
				},
				{
					data: 'added_at', className: 'd-none d-xl-table-cell text-muted small',
					render: function (v, type, r) { return type === 'display' ? fmtDate(v) + (r.added_by ? '<br>' + esc(r.added_by) : '') : (v || ''); }
				},
				{
					data: null, orderable: false, searchable: false, className: 'cell-actions text-end',
					render: function (v, type, r) {
						return '<div class="d-flex justify-content-end gap-1">'
							+ '<button type="button" class="fods-icon-btn is-teal budget-edit" data-recid="' + r.recid + '" title="' + (perms.edit ? 'Edit' : 'View') + '"><i class="ti ' + (perms.edit ? 'ti-pencil' : 'ti-eye') + '"></i></button>'
							+ '<button type="button" class="fods-icon-btn budget-print" data-recid="' + r.recid + '" title="Print LIB"><i class="ti ti-printer"></i></button>'
							+ '</div>';
					}
				}
			]
		});

		$('#budgetTableSearch').on('input', function () { table.search(this.value).draw(); });

		function applyFilter(key) {
			filter = key;
			$('#budgetTableFilter button').removeClass('active').filter('[data-filter="' + key + '"]').addClass('active');
			$('.fods-stat[data-table="budgetTable"]').removeClass('is-active');
			if (key !== 'all') { $('.fods-stat[data-table="budgetTable"][data-filter="' + key + '"]').addClass('is-active'); }
			table.draw();
		}
		$(document).on('click', '#budgetTableFilter button', function () { applyFilter($(this).data('filter')); });
		$(document).on('click', '.fods-stat[data-table="budgetTable"]', function () { applyFilter($(this).data('filter')); });

		yearPicker(function () { renderCards(); table.draw(); });
		renderCards();
		table.draw();

		$(document).on('click', '#budgetTableAdd', function () { __mysys_budget_allotment_ent.__open_budget(null); });
		$(document).on('click', '#budgetTable .budget-edit', function () { __mysys_budget_allotment_ent.__open_budget($(this).data('recid')); });
		$(document).on('click', '#budgetTable .budget-print', function () { __mysys_budget_allotment_ent.__showPdfInModal($(this).data('recid')); });
	};

	//BUDGET - LINE-ITEM GRIDS
	function rowHtml(kind, lookup, d) {
		d = d || {};
		var val = function (k) { return esc(d[k] == null ? '' : d[k]); };
		var numInput = function (cls, k, extra) {
			return '<td class="num"><input type="number" step="any" class="' + cls + '" value="' + val(k) + '"' + (extra || '') + '></td>';
		};
		var html = '<tr>'
			+ '<td><div class="bud-row-actions">'
			+ '<button type="button" class="fods-icon-btn is-danger bud-remove" title="Remove line"><i class="ti ti-trash"></i></button>'
			+ '<button type="button" class="fods-icon-btn is-teal bud-insert" title="Insert a line below"><i class="ti ti-row-insert-bottom"></i></button>'
			+ '</div></td>';

		if (kind === 'item') {
			html += '<td><input type="text" class="g-item" value="' + val('expense_item') + '"></td>'
				+ '<td><input type="text" class="g-part g-lookup" data-lookup="' + lookup + '" placeholder="Sub-object code" value="' + val('particulars') + '"></td>'
				+ '<td><input type="text" class="g-uacs" readonly tabindex="-1" value="' + val('code') + '"></td>';
		} else {
			html += '<td><input type="text" class="g-part" value="' + val('particulars') + '"></td>'
				+ '<td><input type="text" class="g-uacs" value="' + val('code') + '"></td>';
		}

		html += numInput('g-approved', 'approved_budget', ' data-dtid="' + val('recid') + '"');

		if (kind !== 'ac') {
			html += numInput('g-r1', 'r1_approved_budget')
				+ numInput('g-r2', 'r2_approved_budget')
				+ numInput('g-r3', 'r3_approved_budget')
				+ numInput('g-proposed', 'proposed_realignment', ' readonly tabindex="-1"');
		}
		return html + '</tr>';
	}

	function gridHtml(key, data) {
		var g = GRIDS[key];
		var head = '<th style="width:70px"></th>';
		if (g.kind === 'item') {
			head += '<th style="min-width:180px">Expense item</th><th style="min-width:220px">Particulars</th><th style="width:130px">UACS</th>';
		} else {
			head += '<th style="min-width:260px">Particulars</th><th style="width:150px">UACS</th>';
		}
		head += '<th class="num" style="width:140px">Approved</th>';
		var foot = '<td colspan="' + (g.kind === 'item' ? 4 : 3) + '">Subtotal</td><td class="num" data-sum="approved"></td>';
		if (g.kind !== 'ac') {
			head += '<th class="num" style="width:120px">1st realign</th><th class="num" style="width:120px">2nd realign</th><th class="num" style="width:120px">3rd realign</th><th class="num" style="width:140px">Proposed</th>';
			foot += '<td class="num" data-sum="r1"></td><td class="num" data-sum="r2"></td><td class="num" data-sum="r3"></td><td class="num" data-sum="proposed"></td>';
		}

		return '<div class="bud-grid-head">'
			+ '<div><div class="bud-grid-title">' + g.title + ' <span class="bud-grid-sub" data-count></span></div><div class="bud-grid-sub">' + g.sub + '</div></div>'
			+ '<button type="button" class="btn btn-sm btn-fods-ghost bud-add"><i class="ti ti-plus me-1"></i>Add line</button>'
			+ '</div>'
			+ '<div class="bud-grid-wrap"><table class="bud-grid" data-kind="' + g.kind + '" data-lookup="' + (g.lookup || '') + '">'
			+ '<thead><tr>' + head + '</tr></thead>'
			+ '<tbody>' + (data || []).map(function (d) { return rowHtml(g.kind, g.lookup, d); }).join('') + '</tbody>'
			+ '<tfoot><tr>' + foot + '</tr></tfoot>'
			+ '</table></div>'
			+ '<div class="bud-empty d-none">No lines yet — use <b>Add line</b>.</div>';
	}

	// Locks: approved amounts freeze once the allotment is tagged "For Approval";
	// a realignment column opens only while its switch is being turned on.
	function applyLocks(scope) {
		var $ = window.jQuery;
		var lockApproved = !!record && record.tagging === 'For Approval';
		$(scope).find('.g-approved').prop('disabled', lockApproved);
		[1, 2, 3].forEach(function (n) {
			var sw = document.getElementById('is_realign' + n);
			$(scope).find('.g-r' + n).prop('disabled', !(sw.checked && !sw.disabled));
		});
		if (!canSave()) { $(scope).find('input, select, textarea, .bud-add, .bud-remove, .bud-insert').prop('disabled', true); }
	}

	function canSave() { return record ? !!perms.edit : !!perms.add; }

	function recalc() {
		var $ = window.jQuery;
		var total = { approved: 0, proposed: 0 };
		var tabs = { ps: 0, mooe: 0, co: 0, ac: 0 };

		Object.keys(GRIDS).forEach(function (key) {
			var g = GRIDS[key];
			var card = $('.bud-grid-card[data-grid="' + key + '"]');
			var sum = { approved: 0, r1: 0, r2: 0, r3: 0, proposed: 0 };
			var trs = card.find('tbody tr');

			trs.each(function () {
				var tr = $(this);
				var a = num(tr.find('.g-approved').val());
				sum.approved += a;
				if (g.kind !== 'ac') {
					var r1 = num(tr.find('.g-r1').val()), r2 = num(tr.find('.g-r2').val()), r3 = num(tr.find('.g-r3').val());
					var p = a + r1 + r2 + r3;
					tr.find('.g-proposed').val(p.toFixed(2));
					sum.r1 += r1; sum.r2 += r2; sum.r3 += r3; sum.proposed += p;
				}
			});

			Object.keys(sum).forEach(function (k) { card.find('[data-sum="' + k + '"]').text(money(sum[k])); });
			card.find('[data-count]').text('· ' + trs.length + ' line' + (trs.length === 1 ? '' : 's'));
			card.find('.bud-empty').toggleClass('d-none', trs.length > 0);
			card.find('tfoot').toggleClass('d-none', trs.length === 0);

			tabs[g.tab] += sum.approved;
			if (g.kind !== 'ac') { total.approved += sum.approved; total.proposed += sum.proposed; }
		});

		Object.keys(tabs).forEach(function (t) { $('[data-tab-total="' + t + '"]').text(tabs[t] ? moneyShort(tabs[t]) : ''); });

		var ta = total.approved.toFixed(2), tp = total.proposed.toFixed(2);
		$('#total_approved_combined').val(ta);
		$('#total_proposed_combined').val(tp);
		$('#budTotalApproved').text(money(ta));
		$('#budTotalProposed').text(money(tp));
		// budget_save() rejects the allotment unless these two match exactly
		$('#budBalance').html(ta === tp
			? '<span class="fods-chip tone-green"><i class="ti ti-check"></i>Balanced</span>'
			: '<span class="fods-chip tone-red"><i class="ti ti-alert-triangle"></i>Off by ' + money(Math.abs(tp - ta)) + '</span>');
	}

	function renderGrids(grids) {
		var $ = window.jQuery;
		Object.keys(GRIDS).forEach(function (key) {
			var card = $('.bud-grid-card[data-grid="' + key + '"]');
			card.html(gridHtml(key, grids ? grids[key] : []));
			applyLocks(card);
		});
		recalc();
	}

	this.__budget_grids = function () {
		var $ = window.jQuery;
		var modal = $('#budgetModal');

		modal.on('click', '.bud-add', function () {
			var card = $(this).closest('.bud-grid-card');
			var g = GRIDS[card.data('grid')];
			var tr = $(rowHtml(g.kind, g.lookup, null)).appendTo(card.find('tbody'));
			applyLocks(tr);
			recalc();
			tr.find('input:not([readonly]):not(:disabled)').first().trigger('focus');
		});

		modal.on('click', '.bud-insert', function () {
			var card = $(this).closest('.bud-grid-card');
			var g = GRIDS[card.data('grid')];
			var tr = $(rowHtml(g.kind, g.lookup, null)).insertAfter($(this).closest('tr'));
			applyLocks(tr);
			recalc();
			tr.find('input:not([readonly]):not(:disabled)').first().trigger('focus');
		});

		modal.on('click', '.bud-remove', function () {
			$(this).closest('tr').remove();
			recalc();
		});

		modal.on('input', '.bud-grid input[type=number]', recalc);

		// Sub-object code lookup fills the UACS code (PS / MOOE grids)
		modal.on('focus', '.g-lookup', function () {
			var input = $(this);
			if (input.data('ui-autocomplete')) { return; }
			input.autocomplete({
				source: lookups[input.data('lookup')] || [],
				appendTo: '#budgetModal',
				minLength: 0,
				select: function (event, ui) {
					input.val(ui.item.value);
					input.closest('tr').find('.g-uacs').val(ui.item.uacs_code);
					return false;
				}
			});
		});

		[1, 2, 3].forEach(function (n) {
			document.getElementById('is_realign' + n).addEventListener('change', function () { applyLocks('#budgetModal'); });
		});
	};

	//BUDGET - DETAILS (project lookup, program lookup, duration, extension)
	function computeDuration() {
		var from = document.getElementById('duration_from').value;
		var to = document.getElementById('duration_to').value;
		var extendedTo = document.getElementById('extended_to').value;
		var out = document.getElementById('project_duration');

		if (!from || (!to && !extendedTo)) { out.value = ''; return; }

		var fromDate = new Date(from);
		var endDate = new Date(to);
		if (extendedTo) {
			var extDate = new Date(extendedTo);
			if (!isNaN(extDate) && extDate > fromDate) { endDate = extDate; }
		}
		if (endDate < fromDate) { out.value = 'Invalid range'; return; }

		endDate.setDate(endDate.getDate() + 1); // include the last day
		var years = endDate.getFullYear() - fromDate.getFullYear();
		var months = endDate.getMonth() - fromDate.getMonth();
		if (months < 0) { years--; months += 12; }

		var result = '';
		if (years > 0) { result += years + (years === 1 ? ' year' : ' years'); }
		if (months > 0) { result += (result ? ' and ' : '') + months + (months === 1 ? ' month' : ' months'); }
		out.value = result || '0 months';
	}

	function toggleExtension() {
		var on = document.getElementById('with_extension').checked;
		document.querySelectorAll('.bud-ext').forEach(function (el) { el.classList.toggle('d-none', !on); });
	}

	this.__budget_details = function () {
		var $ = window.jQuery;
		var projects = {};
		(lookups.projects || []).forEach(function (p) { projects[p.project_title] = p; });

		$('#project_title').autocomplete({
			source: Object.keys(projects),
			appendTo: '#budgetModal',
			minLength: 1,
			select: function (event, ui) {
				var p = projects[ui.item.value];
				if (!p) { return; }
				$('#responsibility_code').val(p.responsibility_code || '');
				$('#division_name').val(p.division_name || '');
				$('#fund_cluster_code').val(p.fund_cluster_code || '');
				$('#project_leader').val(p.project_leader || '');
			}
		});

		$('#program_title').autocomplete({ source: lookups.programs || [], appendTo: '#budgetModal', minLength: 0 });

		['duration_from', 'duration_to', 'extended_from', 'extended_to'].forEach(function (id) {
			document.getElementById(id).addEventListener('change', computeDuration);
		});
		document.getElementById('with_extension').addEventListener('change', toggleExtension);
	};

	//BUDGET - OPEN EDITOR (new, or load one allotment with its line items)
	function setVal(id, v) { document.getElementById(id).value = v == null ? '' : v; }

	function fill(hd) {
		var $ = window.jQuery;
		var form = document.getElementById('budgetForm');
		form.reset();
		form.classList.remove('was-validated');

		var fields = ['recid', 'trxno', 'program_title', 'project_title', 'responsibility_code', 'fund_cluster_code', 'division_name',
			'project_leader', 'program_leader', 'duration_from', 'duration_to', 'extended_from', 'extended_to',
			'implementing_agency', 'monitoring_agency', 'funding_agency', 'collaborating_agencies', 'lddap_refno'];
		fields.forEach(function (f) { setVal(f, hd ? hd[f] : ''); });
		['duration_from', 'duration_to', 'extended_from', 'extended_to'].forEach(function (f) {
			if (String(document.getElementById(f).value).indexOf('0000') === 0) { setVal(f, ''); }
		});

		document.getElementById('tagging').value = hd && hd.tagging ? hd.tagging : 'Save to Draft';
		document.getElementById('with_extension').checked = !!hd && String(hd.with_extension) === '1';

		// Realignment switches: after approval, only the next realignment can be turned on
		var flags = [1, 2, 3].map(function (n) { return !!hd && String(hd['is_realign' + n]) === '1'; });
		var canRealign = !!hd && String(hd.is_approved) === '1' && hd.tagging === 'For Approval';
		var next = flags.indexOf(false);
		[1, 2, 3].forEach(function (n, i) {
			var sw = document.getElementById('is_realign' + n);
			sw.checked = flags[i];
			sw.disabled = !(canRealign && i === next);
		});
		$('#realignHint').text(canRealign
			? (next === -1 ? 'All three realignments are done.' : 'Turn on the ' + ['1st', '2nd', '3rd'][next] + ' realignment to edit its column.')
			: 'Available once the allotment is approved.');

		toggleExtension();
		computeDuration();
	}

	function setHeader(hd) {
		var $ = window.jQuery;
		var status = hd ? statusOf(hd) : null;
		var s = status ? STATUS[status] : null;

		$('#budgetModalTitle').text(hd ? hd.project_title : 'New Budget Allotment');
		$('#budgetModalSub').html(hd
			? esc(hd.trxno) + ' &nbsp;<span class="fods-chip ' + s[0] + '"><span class="fods-dot"></span>' + s[1] + '</span>'
			: 'Fill in the project details, then the line items.');

		$('#budgetPrintBtn').toggleClass('d-none', !hd);
		var canDecide = !!hd && status === 'pending' && !!perms.approve;
		$('#budgetApproveBtn, #budgetDisapproveBtn').toggleClass('d-none', !canDecide);

		var banner = $('#budgetDecision').removeClass('alert-success alert-danger d-none');
		if (status === 'approved' || status === 'disapproved') {
			banner.addClass(status === 'approved' ? 'alert-success' : 'alert-danger').html(
				'<b>' + (status === 'approved' ? 'Approved' : 'Disapproved') + '</b>' + (hd.approver ? ' by ' + esc(hd.approver) : '')
				+ (hd.remarks ? ' — ' + esc(hd.remarks) : ''));
		} else {
			banner.addClass('d-none');
		}

		$('#submitBtn').toggleClass('d-none', !canSave()).text(hd ? 'Save changes' : 'Create allotment');
	}

	function renderFiles(hd) {
		var $ = window.jQuery;
		var files = hd ? (hd.attachments || []) : [];
		$('#budFilesCount').text(files.length ? files.length : '');
		$('#budFilesList').html(files.length
			? '<div class="list-group list-group-flush">' + files.map(function (f, i) {
				return '<a class="list-group-item list-group-item-action d-flex align-items-center gap-2" target="_blank" href="' + uploadsurl + encodeURIComponent(f.file_name) + '">'
					+ '<i class="ti ti-file"></i><span class="text-truncate">' + (i + 1) + '. ' + esc(f.file_name) + '</span><i class="ti ti-external-link ms-auto text-muted"></i></a>';
			}).join('') + '</div>'
			: '<div class="text-muted small">No files attached.</div>');

		var canUpload = !!hd && !!hd.trxno && !!perms.edit;
		$('#budUploadRow').toggleClass('d-none', !canUpload);
		$('#budUploadNote').toggleClass('d-none', !!hd || !perms.edit);
	}

	this.__open_budget = function (recid) {
		var $ = window.jQuery;
		var modalEl = document.getElementById('budgetModal');
		bootstrap.Tab.getOrCreateInstance(document.querySelector('[data-bs-target="#budPaneDetails"]')).show();

		function show(hd) {
			record = hd;
			fill(hd);
			setHeader(hd);
			renderGrids(hd ? hd.grids : null);
			renderFiles(hd);
			bootstrap.Modal.getOrCreateInstance(modalEl).show();
		}

		if (!recid) { show(null); return; }

		$.getJSON(mesiteurl + 'mybudgetallotment', { meaction: 'MAIN-LOAD', recid: recid })
			.done(show)
			.fail(function (xhr) {
				toastr.error((xhr.responseJSON && xhr.responseJSON.error) || 'Could not load this allotment.', 'Oops!', { closeButton: true });
			});
	};

	function post(params, btn, target) {
		var $ = window.jQuery;
		var $btn = $(btn), label = $btn.html();
		$btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Saving...');
		$.ajax({
			type: 'POST',
			url: mesiteurl + 'mybudgetallotment',
			data: params,
			global: false,
			cache: false,
			success: function (data) { $('.me-outp-msg').html(data); },
			error: function (xhr, status, error) { toastr.error('[BUDGET]', 'Error loading page...' + error, { closeButton: true }); },
			complete: function () { setTimeout(function () { $btn.prop('disabled', false).html(label); }, 1200); }
		});
	}

	//BUDGET - SAVE (same payload as before: header fields + 7 grids of "x|x" rows)
	function collectGrid(key) {
		var $ = window.jQuery;
		var g = GRIDS[key];
		var out = [];
		$('.bud-grid-card[data-grid="' + key + '"] tbody tr').each(function () {
			var tr = $(this);
			var v = function (cls) { var x = tr.find('.g-' + cls).val(); return x == null ? '' : x; };
			var dtid = tr.find('.g-approved').attr('data-dtid') || '';

			var blank = !v('item').trim() && !v('part').trim() && !v('uacs').trim()
				&& ['approved', 'r1', 'r2', 'r3'].every(function (c) { return num(v(c)) === 0; });
			if (blank) { return; }  // skip untouched lines instead of saving empty rows

			if (g.kind === 'item') {
				out.push([v('item'), v('part'), v('uacs'), v('approved'), dtid, v('r1'), v('r2'), v('r3'), v('proposed')].join('x|x'));
			} else if (g.kind === 'co') {
				out.push([v('part'), v('uacs'), v('approved'), dtid, v('r1'), v('r2'), v('r3'), v('proposed')].join('x|x'));
			} else {
				out.push([v('part'), v('uacs'), v('approved'), dtid].join('x|x'));
			}
		});
		return out;
	}

	this.__budget_saving = function () {
		var form = document.getElementById('budgetForm');
		form.addEventListener('submit', function (e) {
			e.preventDefault();
			if (!form.checkValidity()) {
				form.classList.add('was-validated');
				bootstrap.Tab.getOrCreateInstance(document.querySelector('[data-bs-target="#budPaneDetails"]')).show();
				var bad = form.querySelector(':invalid');
				if (bad) { bad.focus(); }
				return;
			}
			recalc();

			var val = function (id) { return document.getElementById(id).value; };
			var mparam = {
				recid: val('recid'),
				trxno: val('trxno'),
				project_title: val('project_title'),
				responsibility_code: val('responsibility_code'),
				fund_cluster_code: val('fund_cluster_code'),
				division_name: val('division_name'),
				project_leader: val('project_leader'),
				program_title: val('program_title'),
				project_duration: val('project_duration'),
				duration_from: val('duration_from'),
				duration_to: val('duration_to'),
				program_leader: val('program_leader'),
				monitoring_agency: val('monitoring_agency'),
				collaborating_agencies: val('collaborating_agencies'),
				implementing_agency: val('implementing_agency'),
				funding_agency: val('funding_agency'),
				tagging: val('tagging'),
				is_realign1: document.getElementById('is_realign1').checked ? 1 : 0,
				is_realign2: document.getElementById('is_realign2').checked ? 1 : 0,
				is_realign3: document.getElementById('is_realign3').checked ? 1 : 0,
				total_approved_combined: val('total_approved_combined'),
				total_proposed_combined: val('total_proposed_combined'),
				with_extension: document.getElementById('with_extension').checked ? 1 : 0,
				extended_from: val('extended_from'),
				extended_to: val('extended_to'),
				lddap_refno: val('lddap_refno'),
				meaction: 'MAIN-SAVE'
			};
			Object.keys(GRIDS).forEach(function (key) { mparam[GRIDS[key].payload] = collectGrid(key); });

			post(mparam, document.getElementById('submitBtn'));
		});
	};

	//BUDGET - APPROVE / DISAPPROVE
	this.__budget_decision = function () {
		var mode = 'approve';
		var decisionEl = document.getElementById('budgetDecisionModal');

		function openDecision(m) {
			mode = m;
			var approve = m === 'approve';
			document.getElementById('budDecisionTitle').textContent = approve ? 'Approve allotment' : 'Disapprove allotment';
			document.getElementById('budDecisionSub').textContent = record ? record.trxno + ' · ' + record.project_title : '';
			document.getElementById('budDecisionIcon').className = 'fods-modal-icon ' + (approve ? 'tone-green' : 'tone-red');
			document.getElementById('budDecisionIcon').innerHTML = '<i class="ti ' + (approve ? 'ti-check' : 'ti-x') + '"></i>';
			var btn = document.getElementById('budDecisionConfirm');
			btn.className = 'btn btn-sm px-4 ' + (approve ? 'btn-success' : 'btn-danger');
			btn.textContent = approve ? 'Approve' : 'Disapprove';
			document.getElementById('budDecisionRemarks').value = '';
			bootstrap.Modal.getOrCreateInstance(decisionEl).show();
		}

		document.getElementById('budgetApproveBtn').addEventListener('click', function () { openDecision('approve'); });
		document.getElementById('budgetDisapproveBtn').addEventListener('click', function () { openDecision('disapprove'); });

		document.getElementById('budDecisionConfirm').addEventListener('click', function () {
			if (!record) { return; }
			post({
				recid: record.recid,
				approver: document.getElementById('budDecisionBy').value,
				remarks: document.getElementById('budDecisionRemarks').value,
				meaction: mode === 'approve' ? 'MAIN-APPROVE' : 'MAIN-DISAPPROVE'
			}, this);
			bootstrap.Modal.getOrCreateInstance(decisionEl).hide();
		});
	};

	//BUDGET - ATTACHMENTS
	this.__budget_upload = function () {
		var $ = window.jQuery;
		document.getElementById('budUploadBtn').addEventListener('click', function () {
			var file = document.getElementById('budUploadFile').files[0];
			if (!file || !record) { toastr.warning('Choose a file to upload first.', 'Upload', { closeButton: true }); return; }

			var formData = new FormData();
			formData.append('userfile', file);
			formData.append('hd_trxno', record.trxno);
			formData.append('meaction', 'MAIN-UPLOAD');

			var btn = $(this), label = btn.html();
			btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Uploading...');
			$.ajax({
				url: mesiteurl + 'mybudgetallotment',
				method: 'POST',
				data: formData,
				contentType: false,
				processData: false,
				success: function (data) { $('.me-outp-msg').html(data); },
				error: function () { toastr.error('An error occurred while uploading the file.', 'Oops!', { closeButton: true }); },
				complete: function () { setTimeout(function () { btn.prop('disabled', false).html(label); }, 1200); }
			});
		});
	};

	//BUDGET - PRINT LIB
	this.__showPdfInModal = function (recid) {
		document.getElementById('budgetPrintFrame').src = mesiteurl + 'mybudgetallotment?meaction=PRINT-LIB&recid=' + encodeURIComponent(recid);
		bootstrap.Modal.getOrCreateInstance(document.getElementById('budgetPrintModal')).show();
	};

	this.__budget_print = function () {
		document.getElementById('budgetPrintBtn').addEventListener('click', function () {
			if (record) { __mysys_budget_allotment_ent.__showPdfInModal(record.recid); }
		});
	};
}

// jQuery, DataTables, jQuery UI and Bootstrap load in the footer, after this file
document.addEventListener('DOMContentLoaded', function () {
	__mysys_budget_allotment_ent.__budget_table();
	__mysys_budget_allotment_ent.__budget_grids();
	__mysys_budget_allotment_ent.__budget_details();
	__mysys_budget_allotment_ent.__budget_saving();
	__mysys_budget_allotment_ent.__budget_decision();
	__mysys_budget_allotment_ent.__budget_upload();
	__mysys_budget_allotment_ent.__budget_print();

	// Deep link (e.g. from Budget Approval): open that allotment right away
	var open = window.__budget_open || {};
	if (open.recid) { __mysys_budget_allotment_ent.__open_budget(open.recid); }
});
