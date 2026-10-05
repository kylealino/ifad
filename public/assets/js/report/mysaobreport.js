var __mysys_saob_rpt_ent = new __mysys_saob_rpt_ent();
function __mysys_saob_rpt_ent() {
	const siteEl = document.getElementById('__siteurl');
	const mesiteurl = siteEl.getAttribute('data-mesiteurl');
	const perms = window.__saob_perms || {};
	const lookups = window.__saob_lookups || {};
	const rows = window.__saob_data || [];

	let table = null;
	let filter = 'all';
	let record = null;          // SAOB open in the editor (null = new, also when copying one)
	let savedMonth = 0;         // revision month already saved on that SAOB (0 = none)
	let brokenRun = false;      // saved months are not an unbroken Jan..N run

	// Revision months in order: hd flag is_<key>, line column <column>_revision
	const MONTHS = [
		{ key: 'jan', column: 'january', label: 'Jan', name: 'January' },
		{ key: 'feb', column: 'february', label: 'Feb', name: 'February' },
		{ key: 'mar', column: 'march', label: 'Mar', name: 'March' },
		{ key: 'apr', column: 'april', label: 'Apr', name: 'April' },
		{ key: 'may', column: 'may', label: 'May', name: 'May' },
		{ key: 'jun', column: 'june', label: 'Jun', name: 'June' },
		{ key: 'jul', column: 'july', label: 'Jul', name: 'July' },
		{ key: 'aug', column: 'august', label: 'Aug', name: 'August' },
		{ key: 'sep', column: 'september', label: 'Sep', name: 'September' },
		{ key: 'oct', column: 'october', label: 'Oct', name: 'October' },
		{ key: 'nov', column: 'november', label: 'Nov', name: 'November' },
		{ key: 'dec', column: 'december', label: 'Dec', name: 'December' }
	];

	// Line-item grids. "payload" is the field saob_save() reads; each row is sent as
	// object code x|x particulars x|x UACS x|x approved x|x to-date x|x proposed x|x revised
	// x|x the 12 monthly revisions (January to December)
	const GRIDS = {
		ps:   { title: 'Personnel Services', payload: 'budgetdtdata', objects: 'psobjects', uacs: 'psuacs' },
		mooe: { title: 'Maintenance and Other Operating Expenses', payload: 'budgetmooedtdata', objects: 'mooeobjects', uacs: 'mooeuacs' },
		co:   { title: 'Capital Outlay', payload: 'budgetcodtdata', objects: 'coobjects', uacs: 'couacs' }
	};

	const STATUS = {
		original: ['tone-slate', 'Original'],
		revised: ['tone-amber', 'Revised']
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

	// Totals are posted in this format and compared as text by saob_save()
	function total(n) {
		return Number(n || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
	}

	function fmtDate(d) {
		if (!d || String(d).indexOf('0000') === 0) { return '—'; }
		var dt = new Date(String(d).replace(' ', 'T'));
		return isNaN(dt) ? d : dt.toLocaleDateString('en-US', { month: 'short', day: '2-digit', year: 'numeric' });
	}

	function chip(r) {
		var s = STATUS[r.status] || STATUS.original;
		var upTo = r.status === 'revised' && +r.revised_to > 0 ? ' · ' + MONTHS[r.revised_to - 1].label : '';
		return '<span class="fods-chip ' + s[0] + '"><span class="fods-dot"></span>' + s[1] + upTo + '</span>';
	}

	// Months are switched on in order; the revision month is the last one of an
	// unbroken Jan..N run (same rule as MySaobReportModel::revision_month). 0 = none.
	function revisionMonth(flags) {
		var n = 0;
		while (n < 12 && flags[n]) { n++; }
		for (var i = n; i < 12; i++) { if (flags[i]) { return 0; } }
		return n;
	}

	//SAOB - YEAR (the cards and the list follow the year picked in the page head)
	const YEAR_KEY = 'fods.year.mysaobrpt';
	let year = String(new Date().getFullYear());
	try { year = localStorage.getItem(YEAR_KEY) || year; } catch (e) { /* storage blocked */ }

	// A SAOB's year is its allotment year, or when it was encoded if that is blank
	function yearOf(r) {
		var y = String(r.current_year || '').slice(0, 4);
		return +y >= 2000 ? y : String(r.added_at || '').slice(0, 4);
	}

	function inYear(r) { return year === 'all' || yearOf(r) === year; }

	//SAOB - STAT CARDS: Allotments, No revisions yet, With revisions, Approved budget
	function renderCards() {
		var $ = window.jQuery;
		var list = rows.filter(inYear);
		var amount = function (l) { return l.reduce(function (t, r) { return t + num(r.amount); }, 0); };
		var programs = {};
		list.forEach(function (r) { programs[String(r.program_title || '').trim().toLowerCase()] = true; });
		var programCount = Object.keys(programs).length;
		var card = function (key, status, label) {
			var l = list.filter(function (r) { return r.status === status; });
			$('[data-card-value="' + key + '"]').text(l.length.toLocaleString());
			$('[data-card-label="' + key + '"]').text(label + ' · ' + money(amount(l)));
		};
		$('[data-card-value="records"]').text(list.length.toLocaleString());
		$('[data-card-label="records"]').text('Allotments · ' + programCount.toLocaleString() + ' program' + (programCount === 1 ? '' : 's'));
		card('original', 'original', 'No revisions yet');
		card('revised', 'revised', 'With revisions');
		// SAOB totals run to hundreds of millions: the card shows the short
		// figure and the label carries the full one
		var sum = amount(list);
		var short = sum >= 1e8;
		$('[data-card-value="total"]').text(short ? money(sum) : money(sum));
		$('[data-card-label="total"]').text('Approved budget' + (short ? ' · ' + money(sum) : ''));
	}

	function yearPicker(onChange) {
		var sel = document.getElementById('saobYear');
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

	//SAOB - LIST (search, status pills, stat tiles)
	this.__saob_table = function () {
		var $ = window.jQuery;

		$.fn.dataTable.ext.search.push(function (settings, data, idx) {
			if (settings.nTable.id !== 'saobTable') { return true; }
			var row = settings.aoData[idx]._aData;
			return inYear(row) && (filter === 'all' || row.status === filter);
		});

		table = $('#saobTable').DataTable({
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
						if (type !== 'display') { return (v || '') + ' ' + (r.program_title || '') + ' ' + (r.trxno || ''); }
						return '<div style="min-width:0"><div class="fw-semibold text-dark" style="overflow-wrap:anywhere">' + esc(v || r.program_title || 'Untitled') + '</div>'
							+ '<div class="small text-muted" style="overflow-wrap:anywhere">' + esc(r.program_title) + '</div>'
							+ '<div class="small text-muted">' + esc(r.trxno) + '<span class="d-md-none"> · ' + esc(r.current_year) + '</span></div></div>';
					}
				},
				{ data: 'current_year', className: 'd-none d-md-table-cell text-nowrap', render: function (v, type) { return type === 'display' ? esc(v || '—') : (v || ''); } },
				{
					data: null, className: 'd-none d-lg-table-cell small text-muted text-nowrap', searchable: false,
					render: function (v, type, r) {
						var n = num(r.ps_lines) + num(r.mooe_lines) + num(r.co_lines);
						if (type !== 'display') { return n; }
						if (!n) { return '<span class="text-danger">No lines yet</span>'; }
						return [['PS', r.ps_lines], ['MOOE', r.mooe_lines], ['CO', r.co_lines]]
							.filter(function (p) { return num(p[1]) > 0; })
							.map(function (p) { return p[0] + ' ' + num(p[1]); }).join(' · ');
					}
				},
				{
					data: 'amount', className: 'text-end cell-left',
					render: function (v, type) { return type === 'display' ? '<span class="text-nowrap fw-semibold">' + money(v) + '</span>' : num(v); }
				},
				{
					data: 'status', className: 'cell-right',
					render: function (v, type, r) { return type === 'display' ? chip(r) : (STATUS[v] || STATUS.original)[1]; }
				},
				{
					data: 'added_at', className: 'd-none d-xl-table-cell text-muted small text-nowrap',
					render: function (v, type, r) { return type === 'display' ? fmtDate(v) + (r.added_by ? '<br>' + esc(r.added_by) : '') : (v || ''); }
				},
				{
					data: null, orderable: false, searchable: false, className: 'cell-actions text-end',
					render: function (v, type, r) {
						return '<div class="d-flex justify-content-end gap-1">'
							+ '<button type="button" class="fods-icon-btn is-teal saob-edit" data-recid="' + r.recid + '" title="' + (perms.edit ? 'Edit' : 'View') + '"><i class="ti ' + (perms.edit ? 'ti-pencil' : 'ti-eye') + '"></i></button>'
							+ (perms.add ? '<button type="button" class="fods-icon-btn saob-copy" data-recid="' + r.recid + '" title="Copy to a new allotment"><i class="ti ti-copy"></i></button>' : '')
							+ '</div>';
					}
				}
			]
		});

		$('#saobTableSearch').on('input', function () { table.search(this.value).draw(); });

		function applyFilter(key) {
			filter = key;
			$('#saobTableFilter button').removeClass('active').filter('[data-filter="' + key + '"]').addClass('active');
			$('.fods-stat[data-table="saobTable"]').removeClass('is-active');
			if (key !== 'all') { $('.fods-stat[data-table="saobTable"][data-filter="' + key + '"]').addClass('is-active'); }
			table.draw();
		}
		$(document).on('click', '#saobTableFilter button', function () { applyFilter($(this).data('filter')); });
		$(document).on('click', '.fods-stat[data-table="saobTable"]', function () { applyFilter($(this).data('filter')); });

		yearPicker(function () { renderCards(); table.draw(); });
		renderCards();
		table.draw();

		$(document).on('click', '#saobTableAdd', function () { __mysys_saob_rpt_ent.__open_saob(null); });
		$(document).on('click', '#saobTable .saob-edit', function () { __mysys_saob_rpt_ent.__open_saob($(this).data('recid')); });
		$(document).on('click', '#saobTable .saob-copy', function () { __mysys_saob_rpt_ent.__open_saob($(this).data('recid'), true); });

		// Allotments / Reports: come back to the tab that was open last
		const TAB_KEY = 'fods.tab.mysaobrpt';
		$('#saobTabs [data-bs-toggle="tab"]').on('shown.bs.tab', function () {
			try { localStorage.setItem(TAB_KEY, this.getAttribute('data-bs-target')); } catch (e) { /* storage blocked */ }
		});
		try {
			var last = document.querySelector('#saobTabs [data-bs-target="' + (localStorage.getItem(TAB_KEY) || '') + '"]');
			if (last && !window.__saob_open && !window.__saob_copy) { bootstrap.Tab.getOrCreateInstance(last).show(); }
		} catch (e) { /* storage blocked */ }
	};

	// New allotments need Add, saved ones need Update
	function canSave() {
		return record ? !!perms.edit : !!perms.add;
	}

	//SAOB - REVISION MONTHS
	function currentMonth() {
		var flags = [];
		window.jQuery('#saobMonths .saob-month').each(function () { flags.push(this.checked); });
		return revisionMonth(flags);
	}

	// Months already saved stay on and locked; the rest can be switched on to
	// record a new realignment
	function setMonths(hd) {
		var flags = MONTHS.map(function (m) { return !!hd && String(hd['is_' + m.key]) === '1'; });
		savedMonth = revisionMonth(flags);
		brokenRun = savedMonth === 0 && flags.indexOf(true) !== -1;
		window.jQuery('#saobMonths .saob-month').each(function (i) {
			this.checked = flags[i];
			this.disabled = flags[i] || brokenRun || !canSave();
		});
	}

	// The approved budget is locked once January is on; the proposed realignment
	// opens only while a new month is switched on (it is recorded under that month)
	function applyMonthRules() {
		var $ = window.jQuery;
		var month = currentMonth();
		var open = month > savedMonth;
		var editable = canSave();

		$('#saobModal .g-approved').prop('disabled', !editable || month > 0);
		var proposed = $('#saobModal .g-proposed').prop('disabled', !editable || !open);
		if (!open) { proposed.val(''); }

		var note;
		if (brokenRun) { note = 'The saved revision months are not in order, so a new realignment cannot be recorded here.'; }
		else if (open) { note = 'Realignments you enter now are recorded under <b>' + MONTHS[month - 1].name + '</b>. The approved budget is locked.'; }
		else if (savedMonth === 12) { note = 'Realigned up to <b>December</b>.'; }
		else if (savedMonth > 0) { note = 'Realigned up to <b>' + MONTHS[savedMonth - 1].name + '</b>. Switch on ' + MONTHS[savedMonth].name + ' to record another realignment.'; }
		else { note = 'No realignment yet. Switch on January to record one — the approved budget is then locked.'; }
		$('#saobMonthsNote').html(note);
	}

	//SAOB - LINE-ITEM GRIDS
	var optionCache = {};

	function optionsHtml(name) {
		if (!optionCache[name]) {
			optionCache[name] = '<option value="">Choose...</option>' + (lookups[name] || []).map(function (o) {
				var v = typeof o === 'string' ? o : o.value;
				return '<option value="' + esc(v) + '"' + (typeof o === 'string' ? '' : ' data-uacs="' + esc(o.uacs_code) + '" data-object="' + esc(o.object_code) + '"') + '>' + esc(v) + '</option>';
			}).join('');
		}
		return optionCache[name];
	}

	function setSelect(sel, value) {
		value = value == null ? '' : String(value);
		sel.value = value;
		// keep a saved value that is no longer in the list
		if (value && sel.value !== value) {
			sel.add(new Option(value, value));
			sel.value = value;
		}
		sel.title = value;
	}

	// d = a saved line (null for a blank one). A copied line keeps its codes only.
	function rowEl(key, d, copy) {
		var $ = window.jQuery;
		var g = GRIDS[key];
		d = d || {};
		var tr = $('<tr>'
			+ '<td class="sticky-l"><div class="saob-row-actions">'
			+ '<button type="button" class="fods-icon-btn is-danger saob-remove" title="Remove line"><i class="ti ti-trash"></i></button>'
			+ '<button type="button" class="fods-icon-btn is-teal saob-insert" title="Insert a line below"><i class="ti ti-row-insert-bottom"></i></button>'
			+ '</div></td>'
			+ '<td><select class="g-object" aria-label="Object code">' + optionsHtml(g.objects) + '</select></td>'
			+ '<td><select class="g-particulars" aria-label="Particulars">' + optionsHtml(g.uacs) + '</select></td>'
			+ '<td><input type="text" class="g-uacs" readonly tabindex="-1" placeholder="Auto" aria-label="UACS"></td>'
			+ '<td class="num"><input type="number" step="any" class="g-approved" placeholder="0.00" aria-label="Approved budget"></td>'
			+ '<td class="num"><input type="number" class="g-todate" readonly tabindex="-1" aria-label="To-date realignment"></td>'
			+ '<td class="num"><input type="number" step="any" class="g-proposed" placeholder="0.00" aria-label="Proposed realignment"></td>'
			+ '<td class="num sticky-r"><input type="number" class="g-revised" readonly tabindex="-1" aria-label="Revised allotment"></td>'
			+ '</tr>');

		setSelect(tr.find('.g-object')[0], d.object_code);
		setSelect(tr.find('.g-particulars')[0], d.particulars);
		tr.find('.g-uacs').val(d.code == null ? '' : d.code);

		// monthly revisions travel with the line and go back unchanged on save
		var revisions = MONTHS.map(function (m) {
			var v = copy ? 0 : d[m.column + '_revision'];
			return v == null || v === '' ? '0' : String(v);
		});
		tr.data('revisions', revisions);

		if (!copy) {
			tr.find('.g-approved').val(d.approved_budget == null ? '' : d.approved_budget);
			// realigned so far: the months up to the saved revision month
			if (savedMonth > 0) {
				tr.find('.g-todate').val(revisions.slice(0, savedMonth).reduce(function (s, v) { return s + num(v); }, 0).toFixed(2));
			}
		}
		return tr;
	}

	function gridHtml(key) {
		var g = GRIDS[key];
		return '<div class="saob-grid-head">'
			+ '<div><div class="saob-grid-title">' + g.title + ' <span class="saob-grid-sub" data-count></span></div></div>'
			+ '<div class="d-flex align-items-center gap-3"><span class="saob-grid-hint d-none" data-hint><i class="ti ti-arrows-horizontal me-1"></i>Scroll sideways for more columns</span>'
			+ '<button type="button" class="btn btn-sm btn-fods-ghost saob-add"><i class="ti ti-plus me-1"></i>Add line</button></div>'
			+ '</div>'
			+ '<div class="saob-grid-wrap"><table class="saob-grid">'
			+ '<colgroup><col class="c-act"><col class="c-object"><col class="c-particulars"><col class="c-uacs"><col class="c-amt"><col class="c-amt"><col class="c-amt"><col class="c-amt"></colgroup>'
			+ '<thead><tr><th class="sticky-l"></th><th>Object code</th><th>Particulars</th><th>UACS</th><th class="num">Approved budget</th><th class="num">To-date realignment</th><th class="num">Proposed realignment</th><th class="num sticky-r">Revised allotment</th></tr></thead>'
			+ '<tbody></tbody>'
			+ '<tfoot><tr><td class="sticky-l">Subtotal</td><td colspan="3"></td><td class="num" data-sum="approved"></td><td class="num" data-sum="todate"></td><td class="num" data-sum="proposed"></td><td class="num sticky-r" data-sum="revised"></td></tr></tfoot>'
			+ '</table></div>'
			+ '<div class="saob-empty d-none">No lines yet — use <b>Add line</b>.</div>';
	}

	// Revised allotment = approved + realigned so far + proposed, per line
	function recalc() {
		var $ = window.jQuery;
		var totalApproved = 0, totalRevised = 0;

		Object.keys(GRIDS).forEach(function (key) {
			var card = $('.saob-grid-card[data-grid="' + key + '"]');
			var trs = card.find('tbody tr');
			var sum = { approved: 0, todate: 0, proposed: 0, revised: 0 };
			trs.each(function () {
				var tr = $(this);
				var approved = num(tr.find('.g-approved').val());
				var todate = num(tr.find('.g-todate').val());
				var proposed = num(tr.find('.g-proposed').val());
				var revised = approved + todate + proposed;
				tr.find('.g-revised').val(revised.toFixed(2));
				sum.approved += approved; sum.todate += todate; sum.proposed += proposed; sum.revised += revised;
			});
			Object.keys(sum).forEach(function (k) { card.find('[data-sum="' + k + '"]').text(money(sum[k])); });
			card.find('[data-count]').text('· ' + trs.length + ' line' + (trs.length === 1 ? '' : 's'));
			card.find('.saob-empty').toggleClass('d-none', trs.length > 0);
			card.find('.saob-grid-wrap, [data-hint]').toggleClass('d-none', trs.length === 0);
			$('[data-tab-total="' + key + '"]').text(Math.abs(sum.approved) >= 0.005 ? money(sum.approved) : '');
			totalApproved += sum.approved;
			totalRevised += sum.revised;
		});

		$('#total_approved_combined').val(total(totalApproved));
		$('#total_proposed_combined').val(total(totalRevised));
		$('#saobFootApproved').text(money(totalApproved));
		$('#saobFootRevised').text(money(totalRevised));

		// Realignments move budget between lines, so the two totals must match
		var balanced = total(totalApproved) === total(totalRevised);
		$('#saobBalance').html(balanced
			? '<span class="fods-chip tone-green"><i class="ti ti-check"></i>Balanced</span>'
			: '<span class="fods-chip tone-red"><i class="ti ti-alert-triangle"></i>Off by ' + money(Math.abs(totalRevised - totalApproved)) + '</span>');
		return balanced;
	}

	function renderGrids(grids, copy) {
		var $ = window.jQuery;
		Object.keys(GRIDS).forEach(function (key) {
			var card = $('.saob-grid-card[data-grid="' + key + '"]');
			card.html(gridHtml(key));
			var tbody = card.find('tbody');
			((grids && grids[key]) || []).forEach(function (d) { tbody.append(rowEl(key, d, copy)); });
		});
	}

	// Set both ways: the same modal is reused for records the user can and cannot
	// edit. The month switches and amount columns follow setMonths() / applyMonthRules().
	function applyLocks() {
		var $ = window.jQuery;
		var locked = !canSave();
		$('#saobPaneDetails').find('input, select, textarea').not('.saob-month').prop('disabled', locked);
		if (locked) { $('#saobModal .saob-grid-card').find('input, select, .saob-add, .saob-remove, .saob-insert').prop('disabled', true); }
	}

	this.__saob_grids = function () {
		var $ = window.jQuery;
		var modal = $('#saobModal');

		function addRow(card, after) {
			var tr = rowEl(card.data('grid'), null, false);
			if (after) { tr.insertAfter(after); } else { tr.appendTo(card.find('tbody')); }
			applyMonthRules();
			recalc();
			tr.find('select').first().trigger('focus');
		}

		modal.on('click', '.saob-add', function () { addRow($(this).closest('.saob-grid-card'), null); });
		modal.on('click', '.saob-insert', function () { addRow($(this).closest('.saob-grid-card'), $(this).closest('tr')); });

		modal.on('click', '.saob-remove', function () {
			$(this).closest('tr').remove();
			recalc();
		});

		modal.on('input', '.g-approved, .g-proposed', recalc);

		modal.on('change', '.g-object', function () { this.title = this.value; });

		// Particulars fill the UACS code, and the object code when it is still blank
		modal.on('change', '.g-particulars', function () {
			var tr = $(this).closest('tr');
			var picked = $(this).find(':selected');
			var uacs = picked.data('uacs');
			tr.find('.g-uacs').val(uacs == null ? '' : uacs);
			var object = tr.find('.g-object')[0];
			if (!object.value && picked.data('object')) {
				object.value = picked.data('object');
				object.title = object.value;
			}
			this.title = this.value;
		});
	};

	//SAOB - DETAILS (program lookup, revision months)
	this.__saob_details = function () {
		var $ = window.jQuery;

		$('#saobMonths').html(MONTHS.map(function (m, i) {
			return '<div class="form-check form-switch">'
				+ '<input class="form-check-input saob-month" type="checkbox" id="is_' + m.key + '" data-month="' + i + '">'
				+ '<label class="form-check-label" for="is_' + m.key + '">' + m.label + '</label></div>';
		}).join(''));

		// Months go in order: switching one on also switches on the months before
		// it, switching one off clears the months after it
		$('#saobMonths').on('change', '.saob-month', function () {
			var i = +this.getAttribute('data-month');
			var on = this.checked;
			$('#saobMonths .saob-month').each(function (j) {
				if (this.disabled) { return; }
				if (on && j < i) { this.checked = true; }
				if (!on && j > i) { this.checked = false; }
			});
			applyMonthRules();
			recalc();
		});

		// Suggest the program titles already used on other allotments, so the
		// same program is spelled the same way every year
		var programs = {};
		rows.forEach(function (r) { var p = String(r.program_title || '').trim(); if (p) { programs[p] = true; } });
		$('#program_title').autocomplete({ source: Object.keys(programs).sort(), appendTo: '#saobModal', minLength: 0 });
	};

	//SAOB - OPEN EDITOR (new, one SAOB with its line items, or a copy of one)
	function setVal(id, v) { document.getElementById(id).value = v == null ? '' : v; }

	function fill(hd, copy) {
		var form = document.getElementById('saobForm');
		form.reset();
		form.classList.remove('was-validated');

		setVal('recid', hd && !copy ? hd.recid : '');
		setVal('trxno', hd && !copy ? hd.trxno : '');
		['program_title', 'project_title', 'department', 'agency'].forEach(function (f) { setVal(f, hd ? hd[f] : ''); });

		// a copy starts a new year: its year and revision months are picked again
		var sel = document.getElementById('current_year');
		sel.innerHTML = '<option value="">Choose...</option>' + (lookups.years || []).map(function (y) { return '<option value="' + y + '">' + y + '</option>'; }).join('');
		setSelect(sel, hd && !copy ? hd.current_year : '');

		setMonths(copy ? null : hd);
	}

	function setHeader(hd, copy) {
		var $ = window.jQuery;
		var name = hd ? (hd.project_title || hd.program_title) : '';

		$('#saobModalTitle').text(hd && !copy ? (name || 'SAOB #' + hd.recid) : 'New allotment');
		if (hd && !copy) {
			var flags = MONTHS.map(function (m) { return String(hd['is_' + m.key]) === '1'; });
			$('#saobModalSub').html(esc(hd.trxno) + ' · ' + esc(hd.current_year) + ' &nbsp;'
				+ chip({ status: flags.indexOf(true) !== -1 ? 'revised' : 'original', revised_to: revisionMonth(flags) }));
		} else if (copy) {
			$('#saobModalSub').text('Copied from ' + name + '. Pick the year, then enter the approved budget.');
		} else {
			$('#saobModalSub').text('Fill in the program details, then the line items.');
		}

		var banner = $('#saobBanner').removeClass('alert-info d-none');
		if (!canSave()) {
			banner.addClass('alert-info').html('<b>View only</b> — you don\'t have permission to ' + (record ? 'edit' : 'add') + ' allotments.');
		} else {
			banner.addClass('d-none');
		}

		$('#submitBtn').toggleClass('d-none', !canSave()).text(record ? 'Save changes' : 'Create allotment');
	}

	this.__open_saob = function (recid, copy) {
		var $ = window.jQuery;
		var modalEl = document.getElementById('saobModal');
		bootstrap.Tab.getOrCreateInstance(document.querySelector('[data-bs-target="#saobPaneDetails"]')).show();

		function show(hd) {
			record = copy ? null : hd;
			fill(hd, copy);
			setHeader(hd, copy);
			renderGrids(hd ? hd.grids : null, copy);
			applyMonthRules();
			applyLocks();
			recalc();
			bootstrap.Modal.getOrCreateInstance(modalEl).show();
		}

		if (!recid) { show(null); return; }

		$.getJSON(mesiteurl + 'mysaobrpt', { meaction: 'MAIN-LOAD', recid: recid })
			.done(show)
			.fail(function (xhr) {
				toastr.error((xhr.responseJSON && xhr.responseJSON.error) || 'Could not load this SAOB.', 'Oops!', { closeButton: true });
			});
	};

	//SAOB - SAVE (same payload as before: header fields, month flags, totals + 3 grids of "x|x" rows)
	function collectGrid(key) {
		var $ = window.jQuery;
		var out = [];
		$('.saob-grid-card[data-grid="' + key + '"] tbody tr').each(function () {
			var tr = $(this);
			var v = function (cls) { var x = tr.find('.g-' + cls).val(); return x == null ? '' : x; };
			var blank = !v('object') && !v('particulars') && num(v('approved')) === 0 && num(v('proposed')) === 0 && num(v('todate')) === 0;
			if (blank) { return; }  // skip untouched lines instead of saving empty rows
			out.push([v('object'), v('particulars'), v('uacs'), v('approved'), v('todate'), v('proposed'), v('revised')].concat(tr.data('revisions')).join('x|x'));
		});
		return out;
	}

	this.__saob_saving = function () {
		var $ = window.jQuery;
		var form = document.getElementById('saobForm');
		var details = function () { bootstrap.Tab.getOrCreateInstance(document.querySelector('[data-bs-target="#saobPaneDetails"]')).show(); };

		form.addEventListener('submit', function (e) {
			e.preventDefault();
			if (!form.checkValidity()) {
				form.classList.add('was-validated');
				details();
				var bad = form.querySelector(':invalid');
				if (bad) { bad.focus(); }
				return;
			}
			if (!recalc()) {
				details();
				toastr.warning('A realignment only moves budget between lines, so the total revised allotment must equal the total approved budget.', 'Not balanced', { progressBar: true, closeButton: true, timeOut: 4000 });
				return;
			}

			var val = function (id) { return document.getElementById(id).value; };
			var mparam = {
				recid: val('recid'),
				trxno: val('trxno'),
				program_title: val('program_title'),
				project_title: val('project_title'),
				department: val('department'),
				agency: val('agency'),
				current_year: val('current_year')
			};
			Object.keys(GRIDS).forEach(function (key) { mparam[GRIDS[key].payload] = collectGrid(key); });
			MONTHS.forEach(function (m) { mparam['is_' + m.key] = document.getElementById('is_' + m.key).checked ? 1 : 0; });
			mparam.total_approved_combined = val('total_approved_combined');
			mparam.total_proposed_combined = val('total_proposed_combined');
			mparam.meaction = 'MAIN-SAVE';

			if (!Object.keys(GRIDS).some(function (key) { return mparam[GRIDS[key].payload].length; })) {
				toastr.warning('Add at least one line item before saving.', 'No lines yet', { progressBar: true, closeButton: true, timeOut: 2500 });
				return;
			}

			var $btn = $('#submitBtn'), label = $btn.html();
			var restore = function () { $btn.prop('disabled', false).html(label); };
			$btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Saving...');
			$.ajax({
				type: 'POST',
				url: mesiteurl + 'mysaobrpt',
				data: mparam,
				global: false,
				cache: false,
				success: function (data) {
					$('.me-outp-msg').html(data);
					// a saved SAOB reloads the page, so the button stays off until then
					if (String(data).indexOf('window.location') === -1) { restore(); }
				},
				error: function (xhr, status, error) {
					toastr.error('The SAOB could not be saved. ' + (error || ''), 'Oops!', { closeButton: true });
					restore();
				}
			});
		});
	};

	//SAOB - REPORTS (SAOB PDF / CSV, ORS extraction, monthly report)
	function showPdf(title, url) {
		document.getElementById('saobPrintTitle').textContent = title;
		document.getElementById('saobPrintFrame').src = url;
		bootstrap.Modal.getOrCreateInstance(document.getElementById('saobPrintModal')).show();
	}

	// "2026-03" -> first and last day of that month
	function monthRange(value) {
		var parts = String(value).split('-');
		var last = new Date(+parts[0], +parts[1], 0).getDate();
		return { start: parts[0] + '-' + parts[1] + '-01', end: parts[0] + '-' + parts[1] + '-' + (last < 10 ? '0' : '') + last };
	}

	function warn(message) {
		toastr.warning(message, 'Oops!', { progressBar: true, closeButton: true, timeOut: 2500 });
	}

	this.__saob_reports = function () {
		var $ = window.jQuery;
		var today = new Date();
		var thisMonth = today.getFullYear() + '-' + (today.getMonth() < 9 ? '0' : '') + (today.getMonth() + 1);
		var query = function (params) {
			return Object.keys(params).map(function (k) { return k + '=' + encodeURIComponent(params[k]); }).join('&');
		};

		document.getElementById('rptMonth').innerHTML = MONTHS.map(function (m) { return '<option value="' + m.name + '">' + m.name + '</option>'; }).join('');
		document.getElementById('rptMonth').value = MONTHS[today.getMonth()].name;
		document.getElementById('rptYear').innerHTML = (lookups.report_years || []).map(function (y) { return '<option value="' + y + '">' + y + '</option>'; }).join('');
		['rptFrom', 'rptTo', 'rptProgramMonth'].forEach(function (id) { document.getElementById(id).value = thisMonth; });

		$('#rptProgram').autocomplete({ source: lookups.programs || [], minLength: 0 })
			.on('focus', function () { $(this).autocomplete('search', this.value); });

		//SAOB REPORT
		$('#rptSaobPdf').on('click', function () {
			var month = $('#rptMonth').val(), yr = $('#rptYear').val();
			if (!month || !yr) { warn('Please select both month and year.'); return; }
			showPdf('SAOB — ' + month + ' ' + yr, mesiteurl + 'mysaobrpt?' + query({ meaction: 'SAOB-PDF', month: month, year: yr }));
		});

		$('#rptSaobCsv').on('click', function () {
			var month = $('#rptMonth').val(), yr = $('#rptYear').val();
			if (!month || !yr) { warn('Please select both month and year.'); return; }
			window.location.href = mesiteurl + 'saob-export-csv?' + query({ month: month, year: yr });
		});

		//ORS EXTRACTION
		$('#rptOrsCsv').on('click', function () {
			var from = $('#rptFrom').val(), to = $('#rptTo').val();
			if (!from || !to) { warn('Please select the months to cover.'); return; }
			if (from > to) { warn('The first month comes after the last one.'); return; }
			window.location.href = mesiteurl + 'export-csv?' + query({ date_from: monthRange(from).start, date_to: monthRange(to).end });
		});

		//MONTHLY REPORT
		function monthly() {
			var program = $('#rptProgram').val().trim(), month = $('#rptProgramMonth').val();
			if (!program) { warn('Please select a program title first.'); return null; }
			if (!month) { warn('Please select a month.'); return null; }
			return { program: program, month: month };
		}

		$('#rptMonthlyCsv').on('click', function () {
			var p = monthly();
			if (!p) { return; }
			var range = monthRange(p.month);
			window.location.href = mesiteurl + 'monthly-export-csv?' + query({ date_from: range.start, date_to: range.end, monthly_program_title: p.program });
		});

		$('#rptMonthlyPrint').on('click', function () {
			var p = monthly();
			if (!p) { return; }
			showPdf('Monthly status — ' + p.program, mesiteurl + 'mysaobrpt?' + query({ meaction: 'SAVINGS-PRINT', program_title: p.program, current_year: p.month.split('-')[0], month: p.month }));
		});
	};
}

// jQuery, DataTables, jQuery UI and Bootstrap load in the footer, after this file
document.addEventListener('DOMContentLoaded', function () {
	__mysys_saob_rpt_ent.__saob_table();
	__mysys_saob_rpt_ent.__saob_grids();
	__mysys_saob_rpt_ent.__saob_details();
	__mysys_saob_rpt_ent.__saob_saving();
	__mysys_saob_rpt_ent.__saob_reports();

	// Deep links: ?recid= opens that SAOB, ?reuse_recid= starts a new one from it
	if (window.__saob_open) { __mysys_saob_rpt_ent.__open_saob(window.__saob_open); }
	else if (window.__saob_copy) { __mysys_saob_rpt_ent.__open_saob(window.__saob_copy, true); }
});
