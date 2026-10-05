var __mysys_proc_pr_ent = new __mysys_proc_pr_ent();
function __mysys_proc_pr_ent() {
	const siteEl = document.getElementById('__siteurl');
	const mesiteurl = siteEl.getAttribute('data-mesiteurl');
	const perms = window.__pr_perms || {};
	const lookups = window.__pr_lookups || {};
	const rows = window.__pr_data || [];

	let table = null;
	let filter = 'all';
	let record = null;          // PR open in the editor (null = new)
	let selectedPPMP = [];      // PPMP nos. the PR takes its items from

	const STATUS = {
		unnumbered: ['tone-amber', 'No PR no. yet'],
		numbered: ['tone-indigo', 'PR no. assigned'],
		rfq: ['tone-green', 'RFQ issued']
	};

	const APPROVAL = {
		pending_a: ['tone-amber', 'Box A · requesting office'],
		pending_b: ['tone-indigo', 'Box B · approval'],
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
		var s = STATUS[status] || STATUS.unnumbered;
		return '<span class="fods-chip ' + s[0] + '"><span class="fods-dot"></span>' + s[1] + '</span>';
	}

	function approvalChip(a) {
		var s = APPROVAL[a] || APPROVAL.pending_a;
		return '<span class="fods-chip ' + s[0] + '"><span class="fods-dot"></span>' + s[1] + '</span>';
	}

	// Box A (requesting office) approves first, then Box B (same rule as MyPR::approvalOf)
	function approvalOf(hd) {
		if (String(hd.is_disapproved_certa) === '1' || String(hd.is_disapproved_certb) === '1') { return 'disapproved'; }
		if (String(hd.is_approved_certb) === '1') { return 'approved'; }
		if (String(hd.is_approved_certa) === '1') { return 'pending_b'; }
		return 'pending_a';
	}

	// Box B follows the estimated cost (same rule as MyPRModel::approver_for)
	function approverFor(total) {
		var rule = lookups.approval || {};
		return (num(total) >= num(rule.limit) ? rule.above : rule.below) || {};
	}

	// Filter pills and cards: approval steps, or where the PR is in procurement
	function inGroup(r, key) {
		if (key === 'all') { return true; }
		if (key === 'in_approval') { return r.approval === 'pending_a' || r.approval === 'pending_b'; }
		if (APPROVAL[key]) { return r.approval === key; }
		return r.status === key;
	}

	function toast(kind, message, title) {
		toastr[kind](message, title, { progressBar: true, closeButton: true, timeOut: 2500 });
	}

	//PR - YEAR (the cards and the list follow the year picked in the page head)
	const YEAR_KEY = 'fods.year.mypr';
	let year = String(new Date().getFullYear());
	try { year = localStorage.getItem(YEAR_KEY) || year; } catch (e) { /* storage blocked */ }

	// A PR's year is its PR date, or when it was encoded if that date is blank
	function yearOf(r) {
		var y = String(r.pr_date || '').slice(0, 4);
		return +y >= 2000 ? y : String(r.added_at || '').slice(0, 4);
	}

	function inYear(r) { return year === 'all' || yearOf(r) === year; }

	//PR - STAT CARDS: Waiting for a PR no., PR no. assigned, RFQ issued, Estimated cost
	function renderCards() {
		var $ = window.jQuery;
		var list = rows.filter(inYear);
		var amount = function (l) { return l.reduce(function (t, r) { return t + num(r.estimated_cost); }, 0); };
		var card = function (key, label) {
			var l = list.filter(function (r) { return r.status === key; });
			$('[data-card-value="' + key + '"]').text(l.length.toLocaleString());
			$('[data-card-label="' + key + '"]').text(label + ' · ' + money(amount(l)));
		};
		card('unnumbered', 'Waiting for a PR no.');
		card('numbered', 'PR no. assigned');
		card('rfq', 'RFQ issued');
		$('[data-card-value="total"]').text(money(amount(list)));
		$('[data-card-label="total"]').text('Estimated cost · ' + list.length.toLocaleString() + ' PR' + (list.length === 1 ? '' : 's'));
	}

	function yearPicker(onChange) {
		var sel = document.getElementById('prYear');
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

	//PR - LIST (search, status pills, stat tiles)
	this.__pr_table = function () {
		var $ = window.jQuery;

		$.fn.dataTable.ext.search.push(function (settings, data, idx) {
			if (settings.nTable.id !== 'prTable') { return true; }
			var row = settings.aoData[idx]._aData;
			return inYear(row) && inGroup(row, filter);
		});

		table = $('#prTable').DataTable({
			data: rows,
			deferRender: true,
			pageLength: 10,
			lengthChange: false,
			autoWidth: false,
			order: [[6, 'desc']],
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
					data: 'prno', className: 'cell-primary',
					render: function (v, type, r) {
						if (type !== 'display') { return (v || '') + ' ' + (r.purpose || '') + ' ' + (r.charge_to || '') + ' ' + (r.end_user || '') + ' ' + (r.office || ''); }
						return '<div style="min-width:0"><div class="fw-semibold text-dark">' + (v ? 'PR ' + esc(v) : '<span class="text-muted">No PR no. yet</span>') + '</div>'
							+ '<div class="small text-muted" style="overflow-wrap:anywhere">' + esc(r.purpose) + '</div>'
							+ '<div class="small text-muted" style="overflow-wrap:anywhere">' + esc(r.charge_to) + '</div>'
							+ '<div class="small text-muted d-md-none">' + esc(r.end_user) + '</div></div>';
					}
				},
				{
					data: 'end_user', className: 'd-none d-md-table-cell',
					render: function (v, type, r) {
						if (type !== 'display') { return v || ''; }
						return '<span style="overflow-wrap:anywhere">' + esc(v || '—') + '</span>' + (r.office ? '<div class="small text-muted">' + esc(r.office) + '</div>' : '');
					}
				},
				{ data: 'pr_date', className: 'd-none d-lg-table-cell text-nowrap', render: function (v, type) { return type === 'display' ? fmtDate(v) : (v || ''); } },
				{
					data: 'estimated_cost', className: 'text-end cell-left',
					render: function (v, type, r) {
						if (type !== 'display') { return num(v); }
						return '<span class="text-nowrap fw-semibold">' + money(v) + '</span><div class="small text-muted">' + num(r.items) + ' item' + (num(r.items) === 1 ? '' : 's') + '</div>';
					}
				},
				{
					data: 'approval', className: 'cell-right',
					render: function (v, type, r) {
						if (type !== 'display') { return (APPROVAL[v] || APPROVAL.pending_a)[1] + ' ' + (r.waiting_on || ''); }
						// while pending, name whose turn it is
						var waiting = v === 'pending_a' || v === 'pending_b';
						return approvalChip(v) + (waiting ? '<div class="small text-muted mt-1" style="overflow-wrap:anywhere">' + (r.waiting_on ? esc(r.waiting_on) : 'No signatory named') + '</div>' : '');
					}
				},
				{
					data: 'status', className: 'd-none d-lg-table-cell',
					render: function (v, type, r) {
						if (type !== 'display') { return (STATUS[v] || STATUS.unnumbered)[1]; }
						return chip(v) + (v === 'rfq' ? '<div class="small text-muted mt-1">' + num(r.rfqs) + ' RFQ' + (num(r.rfqs) === 1 ? '' : 's') + '</div>' : '');
					}
				},
				{
					data: 'added_at', className: 'd-none d-xl-table-cell text-muted small text-nowrap',
					render: function (v, type, r) { return type === 'display' ? fmtDate(v) + (r.added_by ? '<br>' + esc(r.added_by) : '') : (v || ''); }
				},
				{
					data: null, orderable: false, searchable: false, className: 'cell-actions text-end',
					render: function (v, type, r) {
						return '<div class="d-flex justify-content-end gap-1">'
							+ '<button type="button" class="fods-icon-btn is-teal pr-edit" data-recid="' + r.recid + '" title="' + (perms.edit ? 'Edit' : 'View') + '"><i class="ti ' + (perms.edit ? 'ti-pencil' : 'ti-eye') + '"></i></button>'
							+ '<button type="button" class="fods-icon-btn pr-print" data-recid="' + r.recid + '" title="Print PR"><i class="ti ti-printer"></i></button>'
							+ '</div>';
					}
				}
			]
		});

		$('#prTableSearch').on('input', function () { table.search(this.value).draw(); });

		function applyFilter(key) {
			filter = key;
			$('#prTableFilter button').removeClass('active').filter('[data-filter="' + key + '"]').addClass('active');
			$('.fods-stat[data-table="prTable"]').removeClass('is-active');
			if (key !== 'all') { $('.fods-stat[data-table="prTable"][data-filter="' + key + '"]').addClass('is-active'); }
			table.draw();
		}
		$(document).on('click', '#prTableFilter button', function () { applyFilter($(this).data('filter')); });
		$(document).on('click', '.fods-stat[data-table="prTable"]', function () { applyFilter($(this).data('filter')); });

		yearPicker(function () { renderCards(); table.draw(); });
		renderCards();
		table.draw();

		$(document).on('click', '#prTableAdd', function () { __mysys_proc_pr_ent.__open_pr(null); });
		$(document).on('click', '#prTable .pr-edit', function () { __mysys_proc_pr_ent.__open_pr($(this).data('recid')); });
		$(document).on('click', '#prTable .pr-print', function () { __mysys_proc_pr_ent.__showPdfInModalPR($(this).data('recid')); });
	};

	// Locked once the requesting office approves Box A (until it is disapproved)
	function isLocked() {
		var a = record ? approvalOf(record) : null;
		return a === 'pending_b' || a === 'approved';
	}

	// New PRs need Add, saved ones need Update and must not be locked
	function canSave() {
		return record ? !!perms.edit && !isLocked() : !!perms.add;
	}

	// Anyone who can save PRs assigns the PR no., even on a locked PR
	function canSetPrno() {
		return record ? !!perms.edit : !!perms.add;
	}

	//PR - ITEMS GRID
	function rowHtml(d) {
		d = d || {};
		var val = function (k) { return esc(d[k] == null ? '' : d[k]); };
		var number = function (cls, k, label, extra) {
			return '<input type="number" step="any" class="' + cls + '" value="' + val(k) + '" placeholder="0" aria-label="' + label + '"' + (extra || '') + '>';
		};
		return '<tr>'
			+ '<td class="sticky-l"><div class="pr-row-actions">'
			+ '<button type="button" class="fods-icon-btn is-danger pr-remove" title="Remove item"><i class="ti ti-trash"></i></button>'
			+ '<button type="button" class="fods-icon-btn is-teal pr-insert" title="Insert an item below"><i class="ti ti-row-insert-bottom"></i></button>'
			+ '</div></td>'
			+ '<td><textarea class="g-desc" rows="1" maxlength="250" placeholder="Type or pick an item" aria-label="Item description">' + val('item_desc') + '</textarea></td>'
			+ '<td><input type="text" class="g-unit" maxlength="50" value="' + val('unit') + '" placeholder="Unit" aria-label="Unit"></td>'
			+ '<td class="num">' + number('g-qty', 'quantity', 'Quantity') + '</td>'
			+ '<td class="num">' + number('g-cost', 'unit_cost', 'Unit cost') + '</td>'
			+ '<td class="num sticky-r">' + number('g-total', 'total_cost', 'Total cost', ' readonly tabindex="-1"') + '</td>'
			+ '</tr>';
	}

	function gridHtml(items) {
		return '<div class="pr-grid-head">'
			+ '<div><div class="pr-grid-title">Items <span class="pr-grid-sub" data-count></span></div></div>'
			+ '<div class="d-flex align-items-center gap-2">'
			+ '<button type="button" class="btn btn-sm btn-fods-ghost pr-reload d-none" title="Replace the items with those of the PPMPs picked"><i class="ti ti-refresh me-1"></i>Reload from PPMP</button>'
			+ '<button type="button" class="btn btn-sm btn-fods-ghost pr-add"><i class="ti ti-plus me-1"></i>Add item</button></div>'
			+ '</div>'
			+ '<div class="pr-grid-wrap"><table class="pr-grid">'
			+ '<colgroup><col class="c-act"><col class="c-desc"><col class="c-unit"><col class="c-qty"><col class="c-amt"><col class="c-amt"></colgroup>'
			+ '<thead><tr><th class="sticky-l"></th><th>Item description</th><th>Unit</th><th class="num">Quantity</th><th class="num">Unit cost</th><th class="num sticky-r">Total cost</th></tr></thead>'
			+ '<tbody>' + (items || []).map(rowHtml).join('') + '</tbody>'
			+ '<tfoot><tr><td class="sticky-l">Total</td><td colspan="4"></td><td class="num sticky-r" data-sum></td></tr></tfoot>'
			+ '</table></div>'
			+ '<div class="pr-empty d-none">No items yet — pick a PPMP on the Details tab, or use <b>Add item</b>.</div>';
	}

	// Set both ways: the same modal is reused for records the user can and cannot edit
	function applyLocks() {
		var $ = window.jQuery;
		var locked = !canSave();
		$('#prPaneDetails, #prPaneSign').find('input, select, textarea, button').prop('disabled', locked);
		if (locked) { $('#prGrid').find('input, textarea, button').prop('disabled', true); }
		$('#prno').prop('disabled', !canSetPrno());
	}

	// Total cost = quantity x unit cost per line; the estimated cost is their sum
	function recalc() {
		var $ = window.jQuery;
		var card = $('#prGrid');
		var trs = card.find('tbody tr');
		var sum = 0;
		trs.each(function () {
			var tr = $(this);
			var total = num(tr.find('.g-qty').val()) * num(tr.find('.g-cost').val());
			tr.find('.g-total').val(total.toFixed(2));
			sum += total;
		});
		card.find('[data-sum]').text(money(sum));
		card.find('[data-count]').text('· ' + trs.length + ' item' + (trs.length === 1 ? '' : 's'));
		card.find('.pr-empty').toggleClass('d-none', trs.length > 0);
		card.find('.pr-grid-wrap').toggleClass('d-none', trs.length === 0);
		card.find('.pr-reload').toggleClass('d-none', selectedPPMP.length === 0);
		$('[data-tab-total]').text(trs.length ? trs.length + (Math.abs(sum) >= 0.005 ? ' · ' + money(sum) : '') : '');
		$('#prFootItems').text(trs.length.toLocaleString());
		$('#prFootCost').text(money(sum));
		// Box B follows the estimated cost: keep the saved approver on a locked PR
		if (!isLocked()) {
			var approver = approverFor(sum);
			setVal('approved_by', approver.name || '');
			setVal('approved_position', approver.position || '');
		}
		// posted in this format; pr_save() strips the commas
		$('#estimated_cost').val(Number(sum || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
	}

	//PR - PPMPS (the items come from the PPMPs picked, summed per description and unit)
	function renderSelectedPPMP() {
		var editable = canSave();
		document.getElementById('selected_ppmp_list').innerHTML = selectedPPMP.length
			? selectedPPMP.map(function (p) {
				return '<span class="fods-chip tone-teal" data-ppmp="' + esc(p) + '">' + esc(p)
					+ (editable ? '<button type="button" class="pr-ppmp-remove" data-ppmp="' + esc(p) + '" aria-label="Remove ' + esc(p) + '">&times;</button>' : '') + '</span>';
			}).join('')
			: '<span class="pr-note">No PPMP picked yet.</span>';
	}

	function loadPPMPItems() {
		var $ = window.jQuery;
		var tbody = $('#prGrid tbody');
		if (selectedPPMP.length === 0) {
			tbody.empty();
			recalc();
			return;
		}
		$.ajax({
			url: mesiteurl + 'mypr?meaction=LOAD-PPMP',
			type: 'POST',
			data: { ppmpno: selectedPPMP },
			dataType: 'json',
			success: function (res) {
				tbody.html((res || []).map(rowHtml).join(''));
				recalc();
			},
			error: function (xhr) {
				toast('error', (xhr.responseJSON && xhr.responseJSON.error) || 'Could not load the PPMP items.', 'Oops!');
			}
		});
	}

	this.__pr_ppmp = function () {
		var $ = window.jQuery;

		$('#btn_add_ppmp').on('click', function () {
			var ppmp = $('#ppmp_lookup').val();
			if (!ppmp) { toast('warning', 'Select a PPMP first.', 'Oops!'); return; }
			if (selectedPPMP.indexOf(ppmp) !== -1) { toast('warning', 'That PPMP is already added.', 'Oops!'); return; }
			selectedPPMP.push(ppmp);
			$('#ppmp_lookup').val('');
			renderSelectedPPMP();
			loadPPMPItems();
		});

		$('#selected_ppmp_list').on('click', '.pr-ppmp-remove', function () {
			var ppmp = String($(this).data('ppmp'));
			selectedPPMP = selectedPPMP.filter(function (p) { return p !== ppmp; });
			renderSelectedPPMP();
			loadPPMPItems();
		});

		$('#prModal').on('click', '.pr-reload', loadPPMPItems);

		$('#requested_by').on('change', function () {
			setVal('requested_position', $(this).find(':selected').data('position') || '');
		});
	};

	this.__pr_grid = function () {
		var $ = window.jQuery;
		var modal = $('#prModal');

		function addRow(after) {
			var tr = $(rowHtml(null));
			if (after) { tr.insertAfter(after); } else { tr.appendTo('#prGrid tbody'); }
			recalc();
			tr.find('textarea').first().trigger('focus');
		}

		modal.on('click', '.pr-add', function () { addRow(null); });
		modal.on('click', '.pr-insert', function () { addRow($(this).closest('tr')); });

		modal.on('click', '.pr-remove', function () {
			$(this).closest('tr').remove();
			recalc();
		});

		modal.on('input', '.g-qty, .g-cost', recalc);

		// Item description: picking a product fills its unit and price when those are blank
		modal.on('focus', '.g-desc', function () {
			var input = $(this);
			if (input.data('ui-autocomplete')) { return; }
			input.autocomplete({
				appendTo: '#prModal',
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
					if (!tr.find('.g-unit').val() && ui.item.uom) { tr.find('.g-unit').val(ui.item.uom); }
					if (!num(tr.find('.g-cost').val()) && num(ui.item.price)) { tr.find('.g-cost').val(ui.item.price); }
					recalc();
					return false;
				}
			}).autocomplete('instance')._renderItem = function (ul, item) {
				return $('<li>').append('<div>' + esc(item.value) + (item.uom ? '<span class="pr-ac-sub">' + esc(item.uom) + (num(item.price) ? ' · ' + money(item.price) : '') + '</span>' : '') + '</div>').appendTo(ul);
			};
		});
	};

	//PR - RFQ (on any saved PR, once it has a PR no.)
	function renderRfqs(hd) {
		var $ = window.jQuery;
		var list = hd ? (hd.rfqs || []) : [];
		$('#prRfqTab').toggleClass('d-none', !hd);
		$('#prRfqCount').text(list.length ? list.length : '');

		$('#prRfqList').html(!list.length
			? '<div class="text-muted small">No RFQ yet.</div>'
			: '<div style="overflow-x:auto"><table class="pr-rfq-table"><thead><tr><th>Quotation no.</th><th>Company</th><th>Delivery period</th><th>Terms of payment</th><th>Date</th><th></th></tr></thead><tbody>'
				+ list.map(function (q) {
					return '<tr><td class="fw-semibold">' + esc(q.quotation_no || '—') + '</td>'
						+ '<td>' + esc(q.company_name || '—') + (q.company_address ? '<div class="small text-muted">' + esc(q.company_address) + '</div>' : '') + '</td>'
						+ '<td>' + esc(q.delivery_period || '—') + '</td><td>' + esc(q.terms || '—') + '</td>'
						+ '<td class="text-nowrap">' + fmtDate(q.quotation_date) + '</td>'
						+ '<td class="text-end"><button type="button" class="fods-icon-btn pr-rfq-print" data-rfq="' + q.recid + '" title="Print RFQ"><i class="ti ti-printer"></i></button></td></tr>';
				}).join('') + '</tbody></table></div>');

		// an RFQ is filed under the saved PR no.
		var ready = !!hd && String(hd.prno || '').trim() !== '' && !!perms.edit;
		$('#prRfqForm').find('input, textarea, button').prop('disabled', !ready);
		$('#prRfqNote').text(ready ? 'Filed under PR ' + hd.prno + '.'
			: (!hd || String(hd.prno || '').trim() === '' ? 'Save a PR no. on this PR before adding RFQs.' : 'You don\'t have permission to add RFQs.'));
		['quotation_no', 'company_name', 'company_address', 'delivery_period', 'terms'].forEach(function (f) { setVal(f, ''); });
		setVal('quotation_date', new Date().toLocaleDateString('en-CA'));
		$('#prRfqForm .is-invalid').removeClass('is-invalid');
	}

	this.__pr_rfq = function () {
		var $ = window.jQuery;
		var required = ['quotation_no', 'quotation_date', 'company_name'];

		function addRfq() {
			if (!record) { return; }
			var missing = required.filter(function (f) { return !document.getElementById(f).value.trim(); });
			required.forEach(function (f) { document.getElementById(f).classList.toggle('is-invalid', missing.indexOf(f) !== -1); });
			if (missing.length) {
				document.getElementById(missing[0]).focus();
				toast('warning', 'Quotation no., date and company name are required.', 'Oops!');
				return;
			}

			var val = function (id) { return document.getElementById(id).value; };
			var mparam = {
				recid: record.recid,
				prno: record.prno,
				quotation_no: val('quotation_no'),
				quotation_date: val('quotation_date'),
				company_name: val('company_name'),
				company_address: val('company_address'),
				delivery_period: val('delivery_period'),
				terms: val('terms'),
				meaction: 'PR-RFQ-SAVE'
			};

			var $btn = $('#prRfqAdd'), label = $btn.html();
			var restore = function () { $btn.prop('disabled', false).html(label); };
			$btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Adding...');
			$.ajax({
				type: 'POST',
				url: mesiteurl + 'mypr',
				data: mparam,
				global: false,
				cache: false,
				success: function (data) {
					$('.me-outp-msg').html(data);
					// an added RFQ reloads the page, so the button stays off until then
					if (String(data).indexOf('window.location') === -1) { restore(); }
				},
				error: function (xhr, status, error) {
					toast('error', 'The RFQ could not be saved. ' + (error || ''), 'Oops!');
					restore();
				}
			});
		}

		$('#prRfqAdd').on('click', addRfq);

		// Enter in an RFQ field adds the RFQ instead of saving the PR
		$('#prRfqForm').on('keydown', 'input', function (e) {
			if (e.key === 'Enter') { e.preventDefault(); addRfq(); }
		}).on('input', '.is-invalid', function () { this.classList.remove('is-invalid'); });

		$('#prRfqList').on('click', '.pr-rfq-print', function () {
			if (record) { __mysys_proc_pr_ent.__showPdfInModalRFQ(record.recid, $(this).data('rfq')); }
		});
	};

	//PR - OPEN EDITOR (new, or load one PR with its items, PPMPs and RFQs)
	function setVal(id, v) { document.getElementById(id).value = v == null ? '' : v; }

	function fill(hd) {
		var form = document.getElementById('prForm');
		form.reset();
		form.classList.remove('was-validated');

		['recid', 'prno', 'entity_name', 'office', 'responsibility_code', 'fund_cluster', 'end_user', 'charge_to', 'purpose'].forEach(function (f) { setVal(f, hd ? hd[f] : ''); });

		// a blank saved date stays blank so it gets filled in; a new PR starts today
		setVal('pr_date', hd ? (String(hd.pr_date || '').indexOf('0000') === 0 ? '' : hd.pr_date) : new Date().toLocaleDateString('en-CA'));

		document.getElementById('ppmp_lookup').innerHTML = '<option value="">Choose a PPMP...</option>' + (lookups.ppmps || []).map(function (p) {
			var sub = [p.end_user, p.fiscal_year].filter(Boolean).join(', ');
			return '<option value="' + esc(p.value) + '">' + esc(p.value) + (sub ? ' — ' + esc(sub) : '') + '</option>';
		}).join('');

		selectedPPMP = hd ? (hd.ppmps || []).slice() : [];

		signatoryOptions(hd ? hd.requested_by : '', hd ? hd.requested_position : '');
		setVal('approved_by', hd ? hd.approved_by : '');
		setVal('approved_position', hd ? hd.approved_position : '');
		var rule = lookups.approval || {};
		document.getElementById('prApprovalRule').textContent = 'Follows the estimated cost: below ' + money(rule.limit) + ' ' + ((rule.below || {}).name || '')
			+ ', ' + money(rule.limit) + ' and above ' + ((rule.above || {}).name || '') + '.';
	}

	// Box A: heads of the requesting offices, keeping a saved one who is no longer in the list
	function signatoryOptions(current, position) {
		var sel = document.getElementById('requested_by');
		var html = '<option value="">Choose...</option>';
		var seen = false;
		(lookups.signatories || []).forEach(function (o) {
			if (o.full_name === current) { seen = true; }
			html += '<option value="' + esc(o.full_name) + '" data-position="' + esc(o.position) + '">' + esc(o.full_name) + (o.position ? ' — ' + esc(o.position) : '') + '</option>';
		});
		if (current && !seen) {
			html += '<option value="' + esc(current) + '" data-position="' + esc(position || '') + '">' + esc(current) + '</option>';
		}
		sel.innerHTML = html;
		sel.value = current || '';
		setVal('requested_position', current ? position || (sel.selectedOptions[0] ? sel.selectedOptions[0].getAttribute('data-position') : '') || '' : '');
	}

	// Where each box stands, shown on the Certification tab
	function signStatus(box, hd) {
		var $ = window.jQuery;
		var approved = !!hd && String(hd['is_approved_cert' + box]) === '1';
		var disapproved = !!hd && String(hd['is_disapproved_cert' + box]) === '1';
		var html = '', note = '';
		if (approved) { html = '<span class="fods-chip tone-green"><i class="ti ti-check"></i>Approved</span>'; }
		else if (disapproved) { html = '<span class="fods-chip tone-red"><i class="ti ti-x"></i>Disapproved</span>'; }
		else if (!hd) { html = ''; }
		else if (box === 'b' && String(hd.is_approved_certa) !== '1') { html = '<span class="fods-chip tone-slate">After Box A</span>'; }
		else { html = '<span class="fods-chip tone-amber"><span class="fods-dot"></span>Waiting</span>'; }
		if (approved || disapproved) { note = 'By ' + esc(hd['cert' + box + '_approver'] || '—') + (hd['cert' + box + '_remarks'] ? ' — ' + esc(hd['cert' + box + '_remarks']) : ''); }
		$('#prSignStatus' + box.toUpperCase()).html(html);
		$('#prSignNote' + box.toUpperCase()).html(note);
	}

	function setHeader(hd) {
		var $ = window.jQuery;
		// same rule as MyPR::statusOf
		var status = hd ? (String(hd.prno || '').trim() === '' ? 'unnumbered' : ((hd.rfqs || []).length ? 'rfq' : 'numbered')) : null;

		$('#prModalTitle').text(hd ? (hd.prno ? 'PR ' + hd.prno : 'Purchase request #' + hd.recid) : 'New purchase request');
		$('#prModalSub').html(hd
			? esc(hd.end_user || hd.office || '') + ' &nbsp;' + approvalChip(approvalOf(hd)) + ' ' + chip(status)
			: 'Fill in the request, then pick the PPMPs to take the items from.');

		$('#prPrintBtn').toggleClass('d-none', !hd);

		var approval = hd ? approvalOf(hd) : null;
		var prnoOnly = isLocked() && canSetPrno();
		var banner = $('#prBanner').removeClass('alert-success alert-danger alert-info d-none');
		if (approval === 'pending_b') {
			banner.addClass('alert-info').html('<b>Approved by the requesting office</b>' + (hd.certa_approver ? ' (' + esc(hd.certa_approver) + ')' : '')
				+ ' and waiting for ' + esc(hd.approved_by || 'Box B') + '. It can no longer be edited' + (prnoOnly ? ' — you can still assign its PR no.' : '.'));
		} else if (approval === 'approved') {
			banner.addClass('alert-success').html('<b>Approved</b> — Box A by ' + esc(hd.certa_approver || '—') + ', Box B by ' + esc(hd.certb_approver || '—') + '.'
				+ (prnoOnly ? '<div class="small mt-1">Only the PR no. can still be changed.</div>' : ''));
		} else if (approval === 'disapproved') {
			var box = String(hd.is_disapproved_certa) === '1' ? 'a' : 'b';
			banner.addClass('alert-danger').html('<b>Disapproved at Box ' + box.toUpperCase() + '</b>' + (hd['cert' + box + '_approver'] ? ' by ' + esc(hd['cert' + box + '_approver']) : '')
				+ (hd['cert' + box + '_remarks'] ? ' — ' + esc(hd['cert' + box + '_remarks']) : '')
				+ (canSave() ? '<div class="small mt-1">Saving your corrections sends it back to Box A.</div>' : ''));
		} else if (!canSave()) {
			banner.addClass('alert-info').html('<b>View only</b> — you don\'t have permission to ' + (record ? 'edit' : 'add') + ' purchase requests.');
		} else {
			banner.addClass('d-none');
		}

		signStatus('a', hd);
		signStatus('b', hd);

		$('#submitBtn').toggleClass('d-none', !canSave() && !prnoOnly).text(prnoOnly ? 'Save PR no.' : (hd ? 'Save changes' : 'Create PR'));
	}

	this.__open_pr = function (recid, tab) {
		var $ = window.jQuery;
		var modalEl = document.getElementById('prModal');

		function show(hd) {
			record = hd;
			fill(hd);
			setHeader(hd);
			renderSelectedPPMP();
			$('#prGrid').html(gridHtml(hd ? hd.items : null));
			renderRfqs(hd);
			applyLocks();
			recalc();

			var pane = tab === 'rfq' && hd ? '#prPaneRfq' : '#prPaneDetails';
			bootstrap.Tab.getOrCreateInstance(document.querySelector('[data-bs-target="' + pane + '"]')).show();
			bootstrap.Modal.getOrCreateInstance(modalEl).show();
		}

		if (!recid) { show(null); return; }

		$.getJSON(mesiteurl + 'mypr', { meaction: 'MAIN-LOAD', recid: recid })
			.done(show)
			.fail(function (xhr) {
				toast('error', (xhr.responseJSON && xhr.responseJSON.error) || 'Could not load this purchase request.', 'Oops!');
			});
	};

	//PR - SAVE (same payload as before: header fields, PPMP nos. + one "x|x" row per item)
	function collectItems() {
		var $ = window.jQuery;
		var out = [];
		$('#prGrid tbody tr').each(function () {
			var tr = $(this);
			var v = function (cls) { var x = tr.find('.g-' + cls).val(); return x == null ? '' : x; };
			var blank = !v('desc').trim() && !v('unit').trim() && num(v('qty')) === 0 && num(v('cost')) === 0;
			if (blank) { return; }  // skip untouched lines instead of saving empty rows
			out.push([v('desc'), v('unit'), v('qty'), v('cost'), v('total')].join('x|x'));
		});
		return out;
	}

	this.__pr_saving = function () {
		var $ = window.jQuery;
		var form = document.getElementById('prForm');
		form.addEventListener('submit', function (e) {
			e.preventDefault();
			if (!form.checkValidity()) {
				form.classList.add('was-validated');
				var bad = form.querySelector(':invalid');
				var pane = bad ? bad.closest('.tab-pane') : null;
				bootstrap.Tab.getOrCreateInstance(document.querySelector('[data-bs-target="#' + (pane ? pane.id : 'prPaneDetails') + '"]')).show();
				if (bad) { bad.focus(); }
				return;
			}
			recalc();

			var val = function (id) { return document.getElementById(id).value; };
			var mparam = {
				recid: val('recid'),
				entity_name: val('entity_name'),
				office: val('office'),
				prno: val('prno'),
				responsibility_code: val('responsibility_code'),
				fund_cluster: val('fund_cluster'),
				pr_date: val('pr_date'),
				end_user: val('end_user'),
				charge_to: val('charge_to'),
				purpose: val('purpose'),
				estimated_cost: val('estimated_cost'),
				requested_by: val('requested_by'),
				requested_position: val('requested_position'),
				prdtdata: collectItems(),
				ppmp_list: selectedPPMP,
				meaction: 'PR-SAVE'
			};

			if (!mparam.prdtdata.length) {
				bootstrap.Tab.getOrCreateInstance(document.querySelector('[data-bs-target="#prPaneItems"]')).show();
				toast('warning', 'Add at least one item before saving.', 'No items yet');
				return;
			}

			var $btn = $('#submitBtn'), label = $btn.html();
			var restore = function () { $btn.prop('disabled', false).html(label); };
			$btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Saving...');
			$.ajax({
				type: 'POST',
				url: mesiteurl + 'mypr',
				data: mparam,
				global: false,
				cache: false,
				success: function (data) {
					$('.me-outp-msg').html(data);
					// a saved PR reloads the page, so the button stays off until then
					if (String(data).indexOf('window.location') === -1) { restore(); }
				},
				error: function (xhr, status, error) {
					toast('error', 'The PR could not be saved. ' + (error || ''), 'Oops!');
					restore();
				}
			});
		});
	};

	//PR - PRINT PR / RFQ
	function showPdf(title, url) {
		document.getElementById('prPrintTitle').textContent = title;
		document.getElementById('prPrintFrame').src = url;
		bootstrap.Modal.getOrCreateInstance(document.getElementById('prPrintModal')).show();
	}

	this.__showPdfInModalPR = function (recid) {
		showPdf('Purchase request — print preview', mesiteurl + 'mypr?meaction=PR-PRINT&recid=' + encodeURIComponent(recid));
	};

	this.__showPdfInModalRFQ = function (recid, rfqRecid) {
		showPdf('Request for quotation — print preview', mesiteurl + 'mypr?meaction=RFQ-PRINT&recid=' + encodeURIComponent(recid) + '&rfq_recid=' + encodeURIComponent(rfqRecid));
	};

	this.__pr_print = function () {
		document.getElementById('prPrintBtn').addEventListener('click', function () { if (record) { __mysys_proc_pr_ent.__showPdfInModalPR(record.recid); } });
	};
}

// jQuery, DataTables, jQuery UI and Bootstrap load in the footer, after this file
document.addEventListener('DOMContentLoaded', function () {
	__mysys_proc_pr_ent.__pr_table();
	__mysys_proc_pr_ent.__pr_grid();
	__mysys_proc_pr_ent.__pr_ppmp();
	__mysys_proc_pr_ent.__pr_rfq();
	__mysys_proc_pr_ent.__pr_saving();
	__mysys_proc_pr_ent.__pr_print();

	// Deep link: ?recid= opens that PR right away (&tab=rfq on its RFQ tab)
	if (window.__pr_open) { __mysys_proc_pr_ent.__open_pr(window.__pr_open, window.__pr_tab); }
});
