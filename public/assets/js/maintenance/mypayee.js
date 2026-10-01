var __mysys_payee_ent = new __mysys_payee_ent();
function __mysys_payee_ent() {
	const mesiteurl = document.getElementById('__siteurl').getAttribute('data-mesiteurl');
	const perms = window.__payee_perms || {};
	const rows = window.__payee_data || [];
	const byId = {};
	rows.forEach(function (r) { byId[r.recid] = r; });

	let table = null;
	let filter = 'all';

	const METHOD_TONE = { 'LDDAP-ADA': 'tone-indigo', 'LDDAP-IC': 'tone-indigo', 'ADA': 'tone-teal', 'CHECK': 'tone-amber', 'CASH': 'tone-green' };

	const FILTERS = {
		lddap: function (r) { return r.disb_method === 'LDDAP-ADA'; },
		noaccount: function (r) { return blank(r.payee_account_num); },
		notin: function (r) { return blank(r.payee_tin); }
	};

	function blank(v) { return v == null || String(v).trim() === ''; }

	function esc(s) {
		return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) {
			return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
		});
	}

	function dash(v) { return blank(v) ? '<span class="text-muted">—</span>' : esc(v); }

	function initials(name) {
		var base = String(name || '').split(',')[0].trim();
		var t = base.split(/\s+/).filter(function (x) { return x && x.slice(-1) !== '.'; });
		if (!t.length) { return base.charAt(0).toUpperCase(); }
		return (t[0].charAt(0) + (t.length > 1 ? t[t.length - 1].charAt(0) : '')).toUpperCase();
	}

	//PAYEE - TABLE (search, filter pills, stat tiles)
	this.__payee_table = function () {
		var $ = window.jQuery;

		$.fn.dataTable.ext.search.push(function (settings, data, idx) {
			if (settings.nTable.id !== 'payeeTable' || filter === 'all') { return true; }
			return FILTERS[filter](settings.aoData[idx]._aData);
		});

		table = $('#payeeTable').DataTable({
			data: rows,
			deferRender: true,
			pageLength: 10,
			lengthChange: false,
			autoWidth: false,
			order: [[0, 'asc']],
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
					data: 'payee_name', className: 'cell-primary',
					render: function (v, type, r) {
						if (type !== 'display') { return (v || '') + ' ' + (r.payee_office || ''); }
						return '<div class="d-flex align-items-center gap-2" style="min-width:0">'
							+ '<div class="fods-avatar tone-slate">' + esc(initials(v)) + '</div>'
							+ '<div style="min-width:0"><div class="fw-semibold text-dark" style="overflow-wrap:anywhere">' + esc(v) + '</div>'
							+ (blank(r.payee_office) ? '' : '<div class="small text-muted" style="overflow-wrap:anywhere">' + esc(r.payee_office) + '</div>')
							+ '</div></div>';
					}
				},
				{
					data: 'payee_account_num', className: 'cell-left',
					render: function (v, type) {
						if (type !== 'display') { return v || ''; }
						return blank(v) ? '<span class="fods-chip tone-amber">No account</span>' : '<span style="overflow-wrap:anywhere">' + esc(v) + '</span>';
					}
				},
				{ data: 'payee_tin', className: 'd-none d-lg-table-cell', render: function (v, type) { return type === 'display' ? dash(v) : (v || ''); } },
				{
					data: 'disb_method', className: 'cell-right',
					render: function (v, type) {
						if (type !== 'display') { return v || ''; }
						return blank(v) ? '<span class="text-muted">—</span>' : '<span class="fods-chip ' + (METHOD_TONE[v] || 'tone-slate') + '">' + esc(v) + '</span>';
					}
				},
				{ data: 'contact_no', className: 'd-none d-xl-table-cell', render: function (v, type) { return type === 'display' ? dash(v) : (v || ''); } },
				{
					data: null, orderable: false, searchable: false, className: 'cell-actions text-end',
					render: function (v, type, r) {
						var html = '';
						if (perms.edit) { html += '<button type="button" class="fods-icon-btn is-teal payee-edit" data-recid="' + r.recid + '" title="Edit"><i class="ti ti-pencil"></i></button>'; }
						if (perms.del) { html += '<button type="button" class="fods-icon-btn is-danger payee-delete" data-recid="' + r.recid + '" title="Delete"><i class="ti ti-trash"></i></button>'; }
						return '<div class="d-flex justify-content-end gap-1">' + html + '</div>';
					}
				}
			]
		});

		$('#payeeTableSearch').on('input', function () { table.search(this.value).draw(); });

		function applyFilter(key) {
			filter = key;
			$('#payeeTableFilter button').removeClass('active').filter('[data-filter="' + key + '"]').addClass('active');
			$('.fods-stat[data-table="payeeTable"]').removeClass('is-active');
			if (key !== 'all') { $('.fods-stat[data-table="payeeTable"][data-filter="' + key + '"]').addClass('is-active'); }
			table.draw();
		}
		$(document).on('click', '#payeeTableFilter button', function () { applyFilter($(this).data('filter')); });
		$(document).on('click', '.fods-stat[data-table="payeeTable"]', function () { applyFilter($(this).data('filter')); });
	};

	//PAYEE - ADD / EDIT MODAL (filled from the row, no page reload)
	this.__payee_modal = function () {
		var $ = window.jQuery;
		var form = document.getElementById('payeeForm');
		var modalEl = document.getElementById('payeeModal');

		function setField(name, value) {
			var el = form.elements[name];
			if (!el) { return; }
			value = value == null ? '' : String(value);
			// keep legacy values that aren't in the standard list
			if (el.tagName === 'SELECT' && value !== '' && !Array.prototype.some.call(el.options, function (op) { return op.value === value; })) {
				el.add(new Option(value, value));
			}
			el.value = value;
		}

		function open(row) {
			form.reset();
			form.classList.remove('was-validated');
			Array.prototype.forEach.call(form.elements, function (el) {
				if (el.name) { setField(el.name, row ? row[el.name] : (el.getAttribute('data-default') || '')); }
			});
			document.getElementById('payeeModalTitle').textContent = row ? row.payee_name : 'Add Payee';
			document.getElementById('payeeModalSub').textContent = row ? 'Update details or payment information.' : 'Who gets paid and how.';
			document.getElementById('submitBtn').textContent = row ? 'Save changes' : 'Create';
			bootstrap.Modal.getOrCreateInstance(modalEl).show();
		}

		modalEl.addEventListener('shown.bs.modal', function () { document.getElementById('payee_name').focus(); });
		$(document).on('click', '#payeeTableAdd', function () { open(null); });
		$(document).on('click', '#payeeTable .payee-edit', function () { open(byId[$(this).data('recid')]); });
	};

	function post(params, btn) {
		var $ = window.jQuery;
		var $btn = $(btn), label = $btn.html();
		$btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Saving...');
		$.ajax({
			type: 'POST',
			url: mesiteurl + 'mypayee',
			data: params,
			global: false,
			cache: false,
			success: function (data) { $('.me-outp-msg').html(data); },
			error: function (xhr, status, error) { toastr.error('[PAYEE]', 'Error loading page...' + error, { closeButton: true }); },
			complete: function () { setTimeout(function () { $btn.prop('disabled', false).html(label); }, 1200); }
		});
	}

	//PAYEE - SAVE
	this.__payee_saving = function () {
		var form = document.getElementById('payeeForm');
		form.addEventListener('submit', function (e) {
			e.preventDefault();
			if (!form.checkValidity()) { form.classList.add('was-validated'); return; }
			var params = window.jQuery(form).serializeArray();
			params.push({ name: 'meaction', value: 'MAIN-SAVE' });
			post(params, document.getElementById('submitBtn'));
		});
	};

	//PAYEE - DELETE (confirm modal)
	this.__payee_delete = function () {
		var $ = window.jQuery;
		var pending = null;
		var confirmEl = document.getElementById('payeeDeleteModal');
		if (!confirmEl) { return; }

		$(document).on('click', '#payeeTable .payee-delete', function () {
			pending = byId[$(this).data('recid')];
			document.getElementById('payeeDeleteName').textContent = pending.payee_name;
			bootstrap.Modal.getOrCreateInstance(confirmEl).show();
		});

		document.getElementById('payeeDeleteBtn').addEventListener('click', function () {
			if (!pending) { return; }
			post({ recid: pending.recid, meaction: 'MAIN-DELETE' }, this);
			bootstrap.Modal.getOrCreateInstance(confirmEl).hide();
		});
	};
}

// jQuery, DataTables and Bootstrap load in the footer, after this file
document.addEventListener('DOMContentLoaded', function () {
	__mysys_payee_ent.__payee_table();
	__mysys_payee_ent.__payee_modal();
	__mysys_payee_ent.__payee_saving();
	__mysys_payee_ent.__payee_delete();
});
