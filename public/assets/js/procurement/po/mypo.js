var __mysys_po_ent = new __mysys_po_ent();
function __mysys_po_ent() {
	const siteEl = document.getElementById('__siteurl');
	const mesiteurl = siteEl.getAttribute('data-mesiteurl');
	const perms = window.__po_perms || {};
	const lookups = window.__po_lookups || {};
	const rows = window.__po_data || [];

	let table = null;
	let filter = 'all';
	let record = null;          // PO open in the editor (null = new)
	let rfqs = [];              // RFQs of the PR the PO is prepared from

	const STATUS = {
		pending_a: ['tone-amber', 'Box A · accounting'],
		pending_b: ['tone-indigo', 'Box B · approval'],
		approved: ['tone-green', 'Approved'],
		disapproved: ['tone-red', 'Disapproved']
	};

	// Statuses behind each pill / card (any other key is a single status)
	const GROUPS = { pending: ['pending_a', 'pending_b'] };
	function inGroup(r, key) { return key === 'all' || (GROUPS[key] || [key]).indexOf(r.status) !== -1; }

	// Header fields posted as they are (the rest are handled one by one)
	const TEXT_FIELDS = ['pono', 'po_date', 'pr_id', 'prno', 'pr_date', 'supplier_name', 'supplier_address', 'supplier_tel', 'supplier_tin',
		'mode_of_procurement', 'place_of_delivery', 'date_of_delivery', 'delivery_term', 'payment_term', 'charge_to', 'end_user',
		'fund_cluster', 'funds_available', 'ors_burs_no', 'ors_burs_date', 'ors_burs_amount',
		'approved_by', 'approved_position', 'accountant', 'accountant_position'];

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

	function toast(kind, message, title) {
		toastr[kind](message, title, { progressBar: true, closeButton: true, timeOut: 2500 });
	}

	function today() { return new Date().toLocaleDateString('en-CA'); }

	// Box A (accounting) approves first, then Box B (same rule as MyPO::statusOf)
	function statusOf(hd) {
		if (String(hd.is_disapproved_certa) === '1' || String(hd.is_disapproved_certb) === '1') { return 'disapproved'; }
		if (String(hd.is_approved_certb) === '1') { return 'approved'; }
		if (String(hd.is_approved_certa) === '1') { return 'pending_b'; }
		return 'pending_a';
	}

	// Box B follows the PO total (same rule as MyPOModel::approver_for)
	function approverFor(total) {
		var rule = lookups.approval || {};
		return num(total) >= num(rule.limit) ? (rule.above || {}) : (rule.below || {});
	}

	//PO - YEAR (the cards and the list follow the year picked in the page head)
	const YEAR_KEY = 'fods.year.mypo';
	let year = String(new Date().getFullYear());
	try { year = localStorage.getItem(YEAR_KEY) || year; } catch (e) { /* storage blocked */ }

	// A PO's year is its PO date, or when it was encoded if that date is blank
	function yearOf(r) {
		var y = String(r.po_date || '').slice(0, 4);
		return +y >= 2000 ? y : String(r.added_at || '').slice(0, 4);
	}

	function inYear(r) { return year === 'all' || yearOf(r) === year; }

	//PO - STAT CARDS: Pending, Returned, Approved, Total amount
	function renderCards() {
		var $ = window.jQuery;
		var list = rows.filter(inYear);
		var amount = function (l) { return l.reduce(function (t, r) { return t + num(r.total_amount); }, 0); };
		var card = function (key, group, label) {
			var l = list.filter(function (r) { return inGroup(r, group); });
			$('[data-card-value="' + key + '"]').text(l.length.toLocaleString());
			$('[data-card-label="' + key + '"]').text(label + ' · ' + money(amount(l)));
		};
		card('pending', 'pending', 'Pending');
		card('returned', 'disapproved', 'Returned');
		card('approved', 'approved', 'Approved');
		$('[data-card-value="total"]').text(money(amount(list)));
		$('[data-card-label="total"]').text('Total amount · ' + list.length.toLocaleString() + ' PO' + (list.length === 1 ? '' : 's'));
	}

	function yearPicker(onChange) {
		var sel = document.getElementById('poYear');
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

	//PO - LIST (search, status pills, stat tiles)
	this.__po_table = function () {
		var $ = window.jQuery;

		$.fn.dataTable.ext.search.push(function (settings, data, idx) {
			if (settings.nTable.id !== 'poTable') { return true; }
			var row = settings.aoData[idx]._aData;
			return inYear(row) && inGroup(row, filter);
		});

		table = $('#poTable').DataTable({
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
					data: 'pono', className: 'cell-primary',
					render: function (v, type, r) {
						if (type !== 'display') { return (v || '') + ' ' + (r.prno || '') + ' ' + (r.supplier_name || '') + ' ' + (r.charge_to || '') + ' ' + (r.end_user || ''); }
						return '<div style="min-width:0"><div class="fw-semibold text-dark">' + (v ? 'PO ' + esc(v) : '<span class="text-muted">No PO no. yet</span>') + '</div>'
							+ '<div class="small text-muted">' + (r.prno ? 'PR ' + esc(r.prno) : 'No PR') + (r.mode_of_procurement ? ' · ' + esc(r.mode_of_procurement) : '') + '</div>'
							+ '<div class="small text-muted" style="overflow-wrap:anywhere">' + esc(r.charge_to) + '</div>'
							+ '<div class="small text-muted d-md-none">' + esc(r.supplier_name) + '</div></div>';
					}
				},
				{
					data: 'supplier_name', className: 'd-none d-md-table-cell',
					render: function (v, type, r) {
						if (type !== 'display') { return v || ''; }
						return '<span style="overflow-wrap:anywhere">' + esc(v || '—') + '</span>' + (r.end_user ? '<div class="small text-muted">For ' + esc(r.end_user) + '</div>' : '');
					}
				},
				{ data: 'po_date', className: 'd-none d-lg-table-cell text-nowrap', render: function (v, type) { return type === 'display' ? fmtDate(v) : (v || ''); } },
				{
					data: 'total_amount', className: 'text-end cell-left',
					render: function (v, type, r) {
						if (type !== 'display') { return num(v); }
						return '<span class="text-nowrap fw-semibold">' + money(v) + '</span><div class="small text-muted">' + num(r.items) + ' item' + (num(r.items) === 1 ? '' : 's') + '</div>';
					}
				},
				{
					data: 'status', className: 'cell-right',
					render: function (v, type, r) {
						if (type !== 'display') { return (STATUS[v] || STATUS.pending_a)[1]; }
						return chip(v) + (r.ors_burs_no ? '<div class="small text-muted mt-1" style="overflow-wrap:anywhere">' + esc(r.ors_burs_no) + '</div>' : '');
					}
				},
				{
					data: 'added_at', className: 'd-none d-xl-table-cell text-muted small text-nowrap',
					render: function (v, type, r) { return type === 'display' ? fmtDate(v) + (r.added_by ? '<br>' + esc(r.added_by) : '') : (v || ''); }
				},
				{
					data: null, orderable: false, searchable: false, className: 'cell-actions text-end',
					render: function (v, type, r) {
						var editable = perms.edit && (r.status === 'pending_a' || r.status === 'disapproved');
						return '<div class="d-flex justify-content-end gap-1">'
							+ '<button type="button" class="fods-icon-btn is-teal po-edit" data-recid="' + r.recid + '" title="' + (editable ? 'Edit' : 'View') + '"><i class="ti ' + (editable ? 'ti-pencil' : 'ti-eye') + '"></i></button>'
							+ '<button type="button" class="fods-icon-btn po-print" data-recid="' + r.recid + '" title="Print PO"><i class="ti ti-printer"></i></button>'
							+ '</div>';
					}
				}
			]
		});

		$('#poTableSearch').on('input', function () { table.search(this.value).draw(); });

		function applyFilter(key) {
			filter = key;
			$('#poTableFilter button').removeClass('active').filter('[data-filter="' + key + '"]').addClass('active');
			$('.fods-stat[data-table="poTable"]').removeClass('is-active');
			if (key !== 'all') { $('.fods-stat[data-table="poTable"][data-filter="' + key + '"]').addClass('is-active'); }
			table.draw();
		}
		$(document).on('click', '#poTableFilter button', function () { applyFilter($(this).data('filter')); });
		$(document).on('click', '.fods-stat[data-table="poTable"]', function () { applyFilter($(this).data('filter')); });

		yearPicker(function () { renderCards(); table.draw(); });
		renderCards();
		table.draw();

		$(document).on('click', '#poTableAdd', function () { __mysys_po_ent.__open_po(null); });
		$(document).on('click', '#poTable .po-edit', function () { __mysys_po_ent.__open_po($(this).data('recid')); });
		$(document).on('click', '#poTable .po-print', function () { __mysys_po_ent.__showPdfInModal($(this).data('recid')); });
	};

	// Editable while waiting for Box A, or after a disapproval (saving resubmits it)
	function canSave() {
		if (!record) { return !!perms.add; }
		var status = statusOf(record);
		return !!perms.edit && (status === 'pending_a' || status === 'disapproved');
	}

	//PO - ITEMS GRID
	function rowHtml(d) {
		d = d || {};
		var val = function (k) { return esc(d[k] == null ? '' : d[k]); };
		return '<tr>'
			+ '<td class="sticky-l"><div class="po-row-actions">'
			+ '<button type="button" class="fods-icon-btn is-danger po-remove" title="Remove item"><i class="ti ti-trash"></i></button>'
			+ '<button type="button" class="fods-icon-btn is-teal po-insert" title="Insert an item below"><i class="ti ti-row-insert-bottom"></i></button>'
			+ '</div></td>'
			+ '<td><input type="text" class="g-stock" maxlength="100" value="' + val('stock_no') + '" aria-label="Stock / property no."></td>'
			+ '<td><input type="text" class="g-unit" maxlength="50" value="' + val('unit') + '" placeholder="Unit" aria-label="Unit"></td>'
			+ '<td><textarea class="g-desc" rows="2" maxlength="500" placeholder="Description" aria-label="Description">' + val('item_desc') + '</textarea></td>'
			+ '<td class="num"><input type="number" step="1" min="0" class="g-qty" value="' + val('quantity') + '" placeholder="0" aria-label="Quantity"></td>'
			+ '<td class="num"><input type="number" step="any" class="g-cost" value="' + val('unit_cost') + '" placeholder="0.00" aria-label="Unit cost"></td>'
			+ '<td class="num sticky-r"><input type="number" class="g-amount" readonly tabindex="-1" aria-label="Amount"></td>'
			+ '</tr>';
	}

	function gridHtml(items) {
		return '<div class="po-grid-head">'
			+ '<div><div class="po-grid-title">Items <span class="po-grid-sub" data-count></span></div></div>'
			+ '<div class="d-flex align-items-center gap-2">'
			+ '<button type="button" class="btn btn-sm btn-fods-ghost po-reload d-none" title="Replace the items with those of the purchase request"><i class="ti ti-refresh me-1"></i>Reload from PR</button>'
			+ '<button type="button" class="btn btn-sm btn-fods-ghost po-add"><i class="ti ti-plus me-1"></i>Add item</button></div>'
			+ '</div>'
			+ '<div class="po-grid-wrap"><table class="po-grid">'
			+ '<colgroup><col class="c-act"><col class="c-stock"><col class="c-unit"><col class="c-desc"><col class="c-qty"><col class="c-amt"><col class="c-amt"></colgroup>'
			+ '<thead><tr><th class="sticky-l"></th><th>Stock / property no.</th><th>Unit</th><th>Description</th><th class="num">Quantity</th><th class="num">Unit cost</th><th class="num sticky-r">Amount</th></tr></thead>'
			+ '<tbody>' + (items || []).map(rowHtml).join('') + '</tbody>'
			+ '<tfoot><tr><td class="sticky-l">Total</td><td colspan="5"></td><td class="num sticky-r" data-sum></td></tr></tfoot>'
			+ '</table></div>'
			+ '<div class="po-empty d-none">No items yet — pick a purchase request on the Details tab, or use <b>Add item</b>.</div>';
	}

	// Set both ways: the same modal is reused for records the user can and cannot edit
	function applyLocks() {
		var $ = window.jQuery;
		var locked = !canSave();
		$('#poPaneDetails, #poPaneTerms, #poPaneSign').find('input, select, textarea, button').prop('disabled', locked);
		if (locked) { $('#poGrid').find('input, textarea, button').prop('disabled', true); }
	}

	// Amount = quantity x unit cost per line (rounded like the save); the PO total is their sum
	function recalc() {
		var $ = window.jQuery;
		var card = $('#poGrid');
		var trs = card.find('tbody tr');
		var sum = 0;
		trs.each(function () {
			var tr = $(this);
			var amount = Math.round(Math.trunc(num(tr.find('.g-qty').val())) * Math.round(num(tr.find('.g-cost').val()) * 100)) / 100;
			tr.find('.g-amount').val(amount.toFixed(2));
			sum += amount;
		});
		card.find('[data-sum]').text(money(sum));
		card.find('[data-count]').text('· ' + trs.length + ' item' + (trs.length === 1 ? '' : 's'));
		card.find('.po-empty').toggleClass('d-none', trs.length > 0);
		card.find('.po-grid-wrap').toggleClass('d-none', trs.length === 0);
		card.find('.po-reload').toggleClass('d-none', !document.getElementById('pr_id').value);
		$('[data-tab-total]').text(trs.length ? trs.length + (Math.abs(sum) >= 0.005 ? ' · ' + money(sum) : '') : '');
		$('#poFootItems').text(trs.length.toLocaleString());
		$('#poFootTotal').text(money(sum));

		// Box B follows the total: keep the saved approver on a locked PO
		if (!record || canSave()) {
			var approver = approverFor(sum);
			setVal('approved_by', approver.name || '');
			setVal('approved_position', approver.position || '');
		}
	}

	this.__po_grid = function () {
		var $ = window.jQuery;
		var modal = $('#poModal');

		function addRow(after) {
			var tr = $(rowHtml(null));
			if (after) { tr.insertAfter(after); } else { tr.appendTo('#poGrid tbody'); }
			recalc();
			tr.find('input').first().trigger('focus');
		}

		modal.on('click', '.po-add', function () { addRow(null); });
		modal.on('click', '.po-insert', function () { addRow($(this).closest('tr')); });

		modal.on('click', '.po-remove', function () {
			$(this).closest('tr').remove();
			recalc();
		});

		modal.on('input', '.g-qty, .g-cost', recalc);

		// Long descriptions: the box grows to show the whole text (measured once
		// the Items tab is visible, and again while typing)
		function autosize(el) {
			el.style.height = 'auto';
			el.style.height = (el.scrollHeight + 2) + 'px';
		}
		modal.on('input', '.g-desc', function () { autosize(this); });
		$('[data-bs-target="#poPaneItems"]').on('shown.bs.tab', function () {
			$('#poGrid .g-desc').each(function () { autosize(this); });
		});

		modal.on('click', '.po-reload', function () {
			var id = document.getElementById('pr_id').value;
			if (id) { loadPR(id, true); }
		});
	};

	//PO - SOURCE PR (copies its charge-to, end-user, fund cluster and items) AND ITS RFQS
	function renderRfqs(list) {
		rfqs = list || [];
		var sel = document.getElementById('po_rfq');
		sel.innerHTML = '<option value="">' + (rfqs.length ? 'Choose an RFQ...' : 'No RFQ on this PR') + '</option>'
			+ rfqs.map(function (q, i) {
				return '<option value="' + i + '">' + esc(q.company_name || '—') + (q.quotation_no ? ' — ' + esc(q.quotation_no) : '') + '</option>';
			}).join('');
		sel.disabled = !rfqs.length || !canSave();
	}

	// itemsOnly: "Reload from PR" refreshes the items and leaves the rest alone
	function loadPR(id, itemsOnly) {
		var $ = window.jQuery;
		$.getJSON(mesiteurl + 'mypo', { meaction: 'PR-LOAD', id: id })
			.done(function (pr) {
				$('#poGrid tbody').html((pr.items || []).map(function (it) {
					return rowHtml({ unit: it.unit, item_desc: it.item_desc, quantity: Math.trunc(num(it.quantity)), unit_cost: num(it.unit_cost).toFixed(2) });
				}).join(''));
				recalc();
				if (itemsOnly) { return; }

				setVal('pr_id', pr.recid);
				setVal('prno', pr.prno);
				setVal('pr_date', String(pr.pr_date || '').indexOf('0000') === 0 ? '' : pr.pr_date);
				setVal('charge_to', pr.charge_to);
				setVal('end_user', pr.end_user);
				if (pr.fund_cluster) { setVal('fund_cluster', pr.fund_cluster); }
				renderRfqs(pr.rfqs);
				recalc();
				$('#poSourceHint').html('Copied from <b>PR ' + esc(pr.prno) + '</b>' + (pr.purpose ? ' — ' + esc(pr.purpose) : '') + '. Check the items, then pick the supplier.');
			})
			.fail(function (xhr) {
				toast('error', (xhr.responseJSON && xhr.responseJSON.error) || 'Could not load that purchase request.', 'Oops!');
			});
	}

	// Supplier lookup (payees) by name
	var supplierIndex = {};
	function fillSupplier(name, onlyBlanks) {
		var s = supplierIndex[String(name || '').trim().toLowerCase()];
		if (!s) { return false; }
		[['supplier_address', s.address], ['supplier_tin', s.tin], ['supplier_tel', s.tel]].forEach(function (p) {
			var el = document.getElementById(p[0]);
			if (!onlyBlanks || !el.value.trim()) { el.value = p[1] || ''; }
		});
		return true;
	}

	// jQuery UI autocomplete: the whole short list opens on focus
	function pickList(selector, list) {
		var $ = window.jQuery;
		$(selector).autocomplete({ source: list || [], appendTo: '#poModal', minLength: 0 })
			.on('focus', function () { if (!this.disabled) { $(this).autocomplete('search', ''); } });
	}

	this.__po_details = function () {
		var $ = window.jQuery;
		(lookups.suppliers || []).forEach(function (s) { supplierIndex[String(s.value).trim().toLowerCase()] = s; });

		// Purchase request: picking one prepares the PO from it
		$('#prno').autocomplete({
			appendTo: '#poModal',
			minLength: 0,
			source: function (req, resp) {
				var terms = req.term.toLowerCase().split(/\s+/).filter(Boolean);
				var list = lookups.prs || [];
				var out = [];
				for (var i = 0; i < list.length && out.length < 40; i++) {
					var hay = (list[i].prno + ' ' + list[i].end_user + ' ' + list[i].purpose).toLowerCase();
					if (terms.every(function (t) { return hay.indexOf(t) !== -1; })) { out.push(list[i]); }
				}
				resp(out);
			},
			select: function (event, ui) {
				$('#prno').val(ui.item.prno);
				loadPR(ui.item.id, false);
				return false;
			}
		}).on('focus', function () {
			if (!this.disabled) { $(this).autocomplete('search', this.value); }
		}).autocomplete('instance')._renderItem = function (ul, item) {
			return $('<li>').append('<div><b>PR ' + esc(item.prno) + '</b>'
				+ '<span class="po-ac-sub">' + esc([item.end_user, item.purpose].filter(Boolean).join(' · ')) + (num(item.estimated_cost) ? ' · ' + money(item.estimated_cost) : '') + '</span></div>').appendTo(ul);
		};

		// Typed by hand: the PO is only linked to a PR picked from the list
		$('#prno').on('change', function () {
			var typed = this.value.trim();
			var linked = (lookups.prs || []).filter(function (p) { return String(p.id) === String(document.getElementById('pr_id').value); })[0];
			if (!linked || linked.prno !== typed) {
				setVal('pr_id', '');
				renderRfqs([]);
				recalc();
			}
		});

		// RFQ of the PR: fills the supplier and the terms it quoted
		$('#po_rfq').on('change', function () {
			var q = rfqs[this.value];
			if (!q) { return; }
			setVal('supplier_name', q.company_name);
			setVal('supplier_address', q.company_address);
			if (q.delivery_period) { setVal('delivery_term', q.delivery_period); }
			if (q.terms) { setVal('payment_term', q.terms); }
			fillSupplier(q.company_name, true);
			document.getElementById('supplier_name').classList.remove('is-invalid');
		});

		// Supplier: picking a payee fills the address, TIN and telephone
		$('#supplier_name').autocomplete({
			appendTo: '#poModal',
			minLength: 1,
			source: function (req, resp) {
				var term = req.term.toLowerCase();
				var out = [];
				var list = lookups.suppliers || [];
				for (var i = 0; i < list.length && out.length < 40; i++) {
					if (String(list[i].value).toLowerCase().indexOf(term) !== -1) { out.push(list[i]); }
				}
				resp(out);
			},
			select: function (event, ui) {
				$('#supplier_name').val(ui.item.value).removeClass('is-invalid');
				fillSupplier(ui.item.value, false);
				return false;
			}
		}).autocomplete('instance')._renderItem = function (ul, item) {
			return $('<li>').append('<div>' + esc(item.value) + (item.address ? '<span class="po-ac-sub">' + esc(item.address) + '</span>' : '') + '</div>').appendTo(ul);
		};

		$('#accountant').on('change', function () {
			setVal('accountant_position', $(this).find(':selected').data('position') || '');
		});

		pickList('#mode_of_procurement', lookups.modes);
		pickList('#fund_cluster', lookups.fund_clusters);

		// ORS / BURS: picking one fills its date and amount
		$('#ors_burs_no').autocomplete({
			appendTo: '#poModal',
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
				var o = ui.item;
				$('#ors_burs_no').val(o.serialno);
				setVal('ors_burs_date', String(o.obligation_date || '').indexOf('0000') === 0 ? '' : o.obligation_date);
				setVal('ors_burs_amount', num(o.amount).toFixed(2));
				return false;
			}
		}).on('focus', function () {
			if (!this.disabled) { $(this).autocomplete('search', this.value); }
		}).autocomplete('instance')._renderItem = function (ul, item) {
			return $('<li>').append('<div><b>' + esc(item.serialno) + '</b> <span class="fods-chip ' + (item.source === 'BURS' ? 'tone-amber' : 'tone-indigo') + ' ms-1" style="font-size:.65rem">' + esc(item.source) + '</span>'
				+ '<span class="po-ac-sub">' + esc(item.payee_name) + ' · ' + money(item.amount) + '</span></div>').appendTo(ul);
		};
	};

	//PO - OPEN EDITOR (new, or load one PO with its items)
	function setVal(id, v) { document.getElementById(id).value = v == null ? '' : v; }

	// Box A: accounting signatories, keeping a saved one who is no longer in the list
	function accountantOptions(current, position) {
		var sel = document.getElementById('accountant');
		var html = '<option value="">Choose...</option>';
		var seen = false;
		(lookups.accountants || []).forEach(function (o) {
			if (o.full_name === current) { seen = true; }
			html += '<option value="' + esc(o.full_name) + '" data-position="' + esc(o.position) + '">' + esc(o.full_name) + '</option>';
		});
		if (current && !seen) {
			html += '<option value="' + esc(current) + '" data-position="' + esc(position || '') + '">' + esc(current) + '</option>';
		}
		sel.innerHTML = html;
		sel.value = current || '';
		setVal('accountant_position', current ? (sel.selectedOptions[0] ? sel.selectedOptions[0].getAttribute('data-position') : position) || position || '' : '');
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
		$('#poSignStatus' + box.toUpperCase()).html(html);
		$('#poSignNote' + box.toUpperCase()).html(note);
	}

	function fill(hd) {
		var form = document.getElementById('poForm');
		form.reset();
		form.classList.remove('was-validated');

		var defaults = lookups.defaults || {};
		['recid'].concat(TEXT_FIELDS).forEach(function (f) {
			// a new PO starts with the usual terms and signatories
			setVal(f, hd ? hd[f] : (defaults[f] != null ? defaults[f] : ''));
		});
		if (hd) {
			['po_date', 'pr_date', 'ors_burs_date'].forEach(function (f) { if (String(hd[f] || '').indexOf('0000') === 0) { setVal(f, ''); } });
			// saved zeros show as blanks
			['funds_available', 'ors_burs_amount'].forEach(function (f) { if (!num(hd[f])) { setVal(f, ''); } });
			if (String(hd.pr_id) === '0') { setVal('pr_id', ''); }
		} else {
			setVal('po_date', today());
		}

		accountantOptions(hd ? hd.accountant : defaults.accountant, hd ? hd.accountant_position : defaults.accountant_position);
		var rule = lookups.approval || {};
		document.getElementById('poApprovalRule').textContent = 'Follows the PO total: below ' + money(rule.limit) + ' ' + ((rule.below || {}).name || '')
			+ ', ' + money(rule.limit) + ' and above ' + ((rule.above || {}).name || '') + '.';

		document.getElementById('poSourceHint').textContent = hd && hd.prno
			? 'Prepared from PR ' + hd.prno + '. Picking another PR replaces the items.'
			: 'Picking one copies its charge-to, end-user, fund cluster and items into this PO.';
	}

	function setHeader(hd) {
		var $ = window.jQuery;

		$('#poModalTitle').text(hd ? (hd.pono ? 'PO ' + hd.pono : 'Purchase order #' + hd.recid) : 'New purchase order');
		$('#poModalSub').html(hd
			? esc(hd.supplier_name || '') + ' &nbsp;' + chip(statusOf(hd))
			: 'Pick the purchase request to prepare it from, then the supplier.');

		$('#poPrintBtn').toggleClass('d-none', !hd);

		var status = hd ? statusOf(hd) : null;
		var banner = $('#poBanner').removeClass('alert-success alert-danger alert-info d-none');
		if (status === 'pending_b') {
			banner.addClass('alert-info').html('<b>Approved by accounting</b>' + (hd.certa_approver ? ' (' + esc(hd.certa_approver) + ')' : '')
				+ ' and waiting for ' + esc(hd.approved_by || 'Box B') + '. It can no longer be edited.');
		} else if (status === 'approved') {
			banner.addClass('alert-success').html('<b>Approved</b> — Box A by ' + esc(hd.certa_approver || '—') + ', Box B by ' + esc(hd.certb_approver || '—') + '.');
		} else if (status === 'disapproved') {
			var box = String(hd.is_disapproved_certa) === '1' ? 'a' : 'b';
			banner.addClass('alert-danger').html('<b>Disapproved at Box ' + box.toUpperCase() + '</b>' + (hd['cert' + box + '_approver'] ? ' by ' + esc(hd['cert' + box + '_approver']) : '')
				+ (hd['cert' + box + '_remarks'] ? ' — ' + esc(hd['cert' + box + '_remarks']) : '')
				+ (canSave() ? '<div class="small mt-1">Saving your corrections sends it back to Box A.</div>' : ''));
		} else if (!canSave()) {
			banner.addClass('alert-info').html('<b>View only</b> — you don\'t have permission to ' + (record ? 'edit' : 'add') + ' purchase orders.');
		} else {
			banner.addClass('d-none');
		}

		signStatus('a', hd);
		signStatus('b', hd);

		$('#submitBtn').toggleClass('d-none', !canSave()).text(!hd ? 'Create PO' : (status === 'disapproved' ? 'Save & resubmit' : 'Save changes'));
	}

	this.__open_po = function (recid) {
		var $ = window.jQuery;
		var modalEl = document.getElementById('poModal');

		function show(hd) {
			record = hd;
			fill(hd);
			setHeader(hd);
			$('#poGrid').html(gridHtml(hd ? hd.items : null));
			applyLocks();
			renderRfqs(hd ? hd.rfqs : []);
			recalc();
			bootstrap.Tab.getOrCreateInstance(document.querySelector('[data-bs-target="#poPaneDetails"]')).show();
			bootstrap.Modal.getOrCreateInstance(modalEl).show();
		}

		if (!recid) { show(null); return; }

		$.getJSON(mesiteurl + 'mypo', { meaction: 'MAIN-LOAD', recid: recid })
			.done(show)
			.fail(function (xhr) {
				toast('error', (xhr.responseJSON && xhr.responseJSON.error) || 'Could not load this purchase order.', 'Oops!');
			});
	};

	//PO - SAVE (header fields + one "x|x" row per item: stock no. x|x unit x|x description x|x quantity x|x unit cost)
	function collectItems() {
		var $ = window.jQuery;
		var out = [];
		$('#poGrid tbody tr').each(function () {
			var tr = $(this);
			var v = function (cls) { var x = tr.find('.g-' + cls).val(); return x == null ? '' : String(x); };
			var blank = !v('stock').trim() && !v('unit').trim() && !v('desc').trim() && num(v('qty')) === 0 && num(v('cost')) === 0;
			if (blank) { return; }  // skip untouched lines instead of saving empty rows
			out.push([v('stock'), v('unit'), v('desc'), v('qty'), v('cost')].join('x|x'));
		});
		return out;
	}

	this.__po_saving = function () {
		var $ = window.jQuery;
		var form = document.getElementById('poForm');
		form.addEventListener('submit', function (e) {
			e.preventDefault();
			if (!form.checkValidity()) {
				form.classList.add('was-validated');
				var bad = form.querySelector(':invalid');
				var pane = bad ? bad.closest('.tab-pane') : null;
				bootstrap.Tab.getOrCreateInstance(document.querySelector('[data-bs-target="#' + (pane ? pane.id : 'poPaneDetails') + '"]')).show();
				if (bad) { setTimeout(function () { bad.focus(); }, 200); }
				return;
			}
			recalc();

			var mparam = { recid: document.getElementById('recid').value };
			TEXT_FIELDS.forEach(function (f) { mparam[f] = document.getElementById(f).value; });
			mparam.podtdata = collectItems();
			mparam.meaction = 'MAIN-SAVE';

			if (!mparam.podtdata.length) {
				bootstrap.Tab.getOrCreateInstance(document.querySelector('[data-bs-target="#poPaneItems"]')).show();
				toast('warning', 'Add at least one item before saving.', 'No items yet');
				return;
			}

			var $btn = $('#submitBtn'), label = $btn.html();
			var restore = function () { $btn.prop('disabled', false).html(label); };
			$btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Saving...');
			$.ajax({
				type: 'POST',
				url: mesiteurl + 'mypo',
				data: mparam,
				global: false,
				cache: false,
				success: function (data) {
					$('.me-outp-msg').html(data);
					// a saved PO reloads the page, so the button stays off until then
					if (String(data).indexOf('window.location') === -1) { restore(); }
				},
				error: function (xhr, status, error) {
					toast('error', 'The PO could not be saved. ' + (error || ''), 'Oops!');
					restore();
				}
			});
		});
	};

	//PO - PRINT
	this.__showPdfInModal = function (recid) {
		document.getElementById('poPrintFrame').src = mesiteurl + 'mypo?meaction=PRINT-PO&recid=' + encodeURIComponent(recid);
		bootstrap.Modal.getOrCreateInstance(document.getElementById('poPrintModal')).show();
	};

	this.__po_print = function () {
		document.getElementById('poPrintBtn').addEventListener('click', function () { if (record) { __mysys_po_ent.__showPdfInModal(record.recid); } });
	};
}

// jQuery, DataTables, jQuery UI and Bootstrap load in the footer, after this file
document.addEventListener('DOMContentLoaded', function () {
	__mysys_po_ent.__po_table();
	__mysys_po_ent.__po_grid();
	__mysys_po_ent.__po_details();
	__mysys_po_ent.__po_saving();
	__mysys_po_ent.__po_print();

	// Deep link: ?recid= opens that PO right away
	if (window.__po_open) { __mysys_po_ent.__open_po(window.__po_open); }
});
