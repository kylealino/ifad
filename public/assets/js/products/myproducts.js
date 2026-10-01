var __mysys_products_ent = new __mysys_products_ent();
function __mysys_products_ent() {
	const mesiteurl = document.getElementById('__siteurl').getAttribute('data-mesiteurl');
	const perms = window.__products_perms || {};
	const rows = window.__products_data || [];
	const byId = {};
	rows.forEach(function (r) { byId[r.recid] = r; });

	let table = null;
	let filter = 'all';

	const FILTERS = {
		'in': function (r) { return Number(r.quantity) > 0; },
		out: function (r) { return Number(r.quantity) <= 0; }
	};

	function blank(v) { return v == null || String(v).trim() === ''; }

	function esc(s) {
		return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) {
			return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
		});
	}

	function dash(v) { return blank(v) ? '<span class="text-muted">—</span>' : esc(v); }

	function money(n) {
		return '₱' + Number(n || 0).toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
	}

	//PRODUCTS - TABLE (search, filter pills, stat tiles)
	this.__products_table = function () {
		var $ = window.jQuery;

		$.fn.dataTable.ext.search.push(function (settings, data, idx) {
			if (settings.nTable.id !== 'productsTable' || filter === 'all') { return true; }
			return FILTERS[filter](settings.aoData[idx]._aData);
		});

		table = $('#productsTable').DataTable({
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
					data: 'product_desc', className: 'cell-primary',
					render: function (v, type, r) {
						if (type !== 'display') { return (v || '') + ' ' + (r.product_code || ''); }
						return '<div style="min-width:0"><div class="fw-semibold text-dark" style="overflow-wrap:anywhere">' + esc(v) + '</div>'
							+ '<div class="small text-muted">' + (r.product_code ? esc(r.product_code) : 'No code') + '</div></div>';
					}
				},
				{ data: 'uom', className: 'd-none d-md-table-cell', render: function (v, type) { return type === 'display' ? dash(v) : (v || ''); } },
				{
					data: 'price', className: 'text-end cell-left',
					render: function (v, type) { return type === 'display' ? '<span class="text-nowrap">' + money(v) + '</span>' : Number(v || 0); }
				},
				{
					data: 'quantity', className: 'text-end cell-right',
					render: function (v, type, r) {
						var q = Number(v || 0);
						if (type !== 'display') { return q; }
						if (q <= 0) { return '<span class="fods-chip tone-amber">Out of stock</span>'; }
						return '<span class="fods-chip tone-green">' + q.toLocaleString() + (r.uom ? ' ' + esc(r.uom) : '') + '</span>';
					}
				},
				{ data: 'remarks', className: 'd-none d-xl-table-cell', render: function (v, type) { return type === 'display' ? dash(v) : (v || ''); } },
				{
					data: null, orderable: false, searchable: false, className: 'cell-actions text-end',
					render: function (v, type, r) {
						return perms.edit
							? '<div class="d-flex justify-content-end"><button type="button" class="fods-icon-btn is-teal products-edit" data-recid="' + r.recid + '" title="Edit"><i class="ti ti-pencil"></i></button></div>'
							: '';
					}
				}
			]
		});

		$('#productsTableSearch').on('input', function () { table.search(this.value).draw(); });

		function applyFilter(key) {
			filter = key;
			$('#productsTableFilter button').removeClass('active').filter('[data-filter="' + key + '"]').addClass('active');
			$('.fods-stat[data-table="productsTable"]').removeClass('is-active');
			if (key !== 'all') { $('.fods-stat[data-table="productsTable"][data-filter="' + key + '"]').addClass('is-active'); }
			table.draw();
		}
		$(document).on('click', '#productsTableFilter button', function () { applyFilter($(this).data('filter')); });
		$(document).on('click', '.fods-stat[data-table="productsTable"]', function () { applyFilter($(this).data('filter')); });
	};

	//PRODUCTS - ADD / EDIT MODAL (filled from the row, no page reload)
	this.__products_modal = function () {
		var $ = window.jQuery;
		var form = document.getElementById('productsForm');
		var modalEl = document.getElementById('productsModal');

		function open(row) {
			form.reset();
			form.classList.remove('was-validated');
			Array.prototype.forEach.call(form.elements, function (el) {
				if (!el.name) { return; }
				var v = row ? row[el.name] : el.getAttribute('data-default');
				el.value = v == null ? '' : v;
			});
			document.getElementById('productsModalTitle').textContent = row ? row.product_desc : 'Add Item';
			document.getElementById('productsModalSub').textContent = row
				? (row.product_code ? 'Item code ' + row.product_code : 'Update item details.')
				: 'A supply item and its unit price.';
			document.getElementById('submitBtn').textContent = row ? 'Save changes' : 'Create';
			bootstrap.Modal.getOrCreateInstance(modalEl).show();
		}

		modalEl.addEventListener('shown.bs.modal', function () { document.getElementById('product_code').focus(); });
		$(document).on('click', '#productsTableAdd', function () { open(null); });
		$(document).on('click', '#productsTable .products-edit', function () { open(byId[$(this).data('recid')]); });
	};

	//PRODUCTS - SAVE
	this.__products_saving = function () {
		var $ = window.jQuery;
		var form = document.getElementById('productsForm');

		form.addEventListener('submit', function (e) {
			e.preventDefault();
			if (!form.checkValidity()) { form.classList.add('was-validated'); return; }

			var params = $(form).serializeArray();
			params.push({ name: 'meaction', value: 'PRODUCTS-SAVE' });

			var $btn = $('#submitBtn'), label = $btn.html();
			$btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Saving...');
			$.ajax({
				type: 'POST',
				url: mesiteurl + 'myproducts',
				data: params,
				global: false,
				cache: false,
				success: function (data) { $('.me-outp-msg').html(data); },
				error: function (xhr, status, error) { toastr.error('[PRODUCTS]', 'Error loading page...' + error, { closeButton: true }); },
				complete: function () { setTimeout(function () { $btn.prop('disabled', false).html(label); }, 1200); }
			});
		});
	};
}

// jQuery, DataTables and Bootstrap load in the footer, after this file
document.addEventListener('DOMContentLoaded', function () {
	__mysys_products_ent.__products_table();
	__mysys_products_ent.__products_modal();
	__mysys_products_ent.__products_saving();
});
