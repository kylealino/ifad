var __mysys_uacs_ent = new __mysys_uacs_ent();
function __mysys_uacs_ent() {
	const mesiteurl = document.getElementById('__siteurl').getAttribute('data-mesiteurl');
	const perms = window.__uacs_perms || {};
	const rows = window.__uacs_data || [];
	const byId = {};
	rows.forEach(function (r) { byId[r.recid] = r; });

	let table = null;
	let filter = 'all';

	const CLASS = {
		'Personnel Services': ['PS', 'tone-teal'],
		'Maintenance and Other Operating Expenses': ['MOOE', 'tone-indigo'],
		'Capital Outlay': ['CO', 'tone-amber']
	};

	const FILTERS = {
		ps: function (r) { return r.allotment_class === 'Personnel Services'; },
		mooe: function (r) { return r.allotment_class === 'Maintenance and Other Operating Expenses'; },
		co: function (r) { return r.allotment_class === 'Capital Outlay'; }
	};

	function blank(v) { return v == null || String(v).trim() === ''; }

	function esc(s) {
		return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) {
			return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
		});
	}

	function dash(v) { return blank(v) ? '<span class="text-muted">—</span>' : esc(v); }

	function fmtDate(d) {
		if (!d || String(d).indexOf('0000') === 0) { return '—'; }
		var dt = new Date(String(d).replace(' ', 'T'));
		return isNaN(dt) ? d : dt.toLocaleDateString('en-US', { month: 'short', day: '2-digit', year: 'numeric' });
	}

	//UACS - TABLE (search, allotment-class pills, stat tiles)
	this.__uacs_table = function () {
		var $ = window.jQuery;

		$.fn.dataTable.ext.search.push(function (settings, data, idx) {
			if (settings.nTable.id !== 'uacsTable' || filter === 'all') { return true; }
			return FILTERS[filter](settings.aoData[idx]._aData);
		});

		table = $('#uacsTable').DataTable({
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
					data: 'uacs_code', className: 'cell-primary',
					render: function (v, type, r) {
						if (type !== 'display') { return (v || '') + ' ' + (r.sub_object_code || '') + ' ' + (r.object_code || ''); }
						return '<div style="min-width:0"><div class="fw-semibold text-dark" style="font-variant-numeric:tabular-nums">' + esc(v) + '</div>'
							+ '<div class="small text-muted" style="overflow-wrap:anywhere">' + esc(r.sub_object_code) + '</div></div>';
					}
				},
				{ data: 'object_code', className: 'd-none d-md-table-cell', render: function (v, type) { return type === 'display' ? dash(v) : (v || ''); } },
				{
					data: 'allotment_class', className: 'cell-left',
					render: function (v, type) {
						if (type !== 'display') { return v || ''; }
						var c = CLASS[v];
						return c ? '<span class="fods-chip ' + c[1] + '" title="' + esc(v) + '">' + c[0] + '</span>' : dash(v);
					}
				},
				{
					data: 'added_at', className: 'd-none d-lg-table-cell text-muted small',
					render: function (v, type, r) { return type === 'display' ? fmtDate(v) + (r.added_by ? ' · ' + esc(r.added_by) : '') : (v || ''); }
				},
				{
					data: null, orderable: false, searchable: false, className: 'cell-actions text-end',
					render: function (v, type, r) {
						return perms.edit
							? '<div class="d-flex justify-content-end"><button type="button" class="fods-icon-btn is-teal uacs-edit" data-recid="' + r.recid + '" title="Edit"><i class="ti ti-pencil"></i></button></div>'
							: '';
					}
				}
			]
		});

		$('#uacsTableSearch').on('input', function () { table.search(this.value).draw(); });

		function applyFilter(key) {
			filter = key;
			$('#uacsTableFilter button').removeClass('active').filter('[data-filter="' + key + '"]').addClass('active');
			$('.fods-stat[data-table="uacsTable"]').removeClass('is-active');
			if (key !== 'all') { $('.fods-stat[data-table="uacsTable"][data-filter="' + key + '"]').addClass('is-active'); }
			table.draw();
		}
		$(document).on('click', '#uacsTableFilter button', function () { applyFilter($(this).data('filter')); });
		$(document).on('click', '.fods-stat[data-table="uacsTable"]', function () { applyFilter($(this).data('filter')); });
	};

	//UACS - ADD / EDIT MODAL (filled from the row, no page reload)
	this.__uacs_modal = function () {
		var $ = window.jQuery;
		var form = document.getElementById('uacsForm');
		var modalEl = document.getElementById('uacsModal');

		function open(row) {
			form.reset();
			form.classList.remove('was-validated');
			Array.prototype.forEach.call(form.elements, function (el) {
				if (el.name) { el.value = row && row[el.name] != null ? row[el.name] : ''; }
			});
			document.getElementById('uacsModalTitle').textContent = row ? row.uacs_code : 'Add UACS Code';
			document.getElementById('uacsModalSub').textContent = row ? row.sub_object_code : 'An object / sub-object code and its UACS code.';
			document.getElementById('submitBtn').textContent = row ? 'Save changes' : 'Create';
			bootstrap.Modal.getOrCreateInstance(modalEl).show();
		}

		modalEl.addEventListener('shown.bs.modal', function () { document.getElementById('allotment_class').focus(); });
		$(document).on('click', '#uacsTableAdd', function () { open(null); });
		$(document).on('click', '#uacsTable .uacs-edit', function () { open(byId[$(this).data('recid')]); });
	};

	//UACS - SAVE
	this.__uacs_saving = function () {
		var $ = window.jQuery;
		var form = document.getElementById('uacsForm');

		form.addEventListener('submit', function (e) {
			e.preventDefault();
			if (!form.checkValidity()) { form.classList.add('was-validated'); return; }

			var params = $(form).serializeArray();
			params.push({ name: 'meaction', value: 'MAIN-SAVE' });

			var $btn = $('#submitBtn'), label = $btn.html();
			$btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Saving...');
			$.ajax({
				type: 'POST',
				url: mesiteurl + 'myuacs',
				data: params,
				global: false,
				cache: false,
				success: function (data) { $('.me-outp-msg').html(data); },
				error: function (xhr, status, error) { toastr.error('[UACS]', 'Error loading page...' + error, { closeButton: true }); },
				complete: function () { setTimeout(function () { $btn.prop('disabled', false).html(label); }, 1200); }
			});
		});
	};
}

// jQuery, DataTables and Bootstrap load in the footer, after this file
document.addEventListener('DOMContentLoaded', function () {
	__mysys_uacs_ent.__uacs_table();
	__mysys_uacs_ent.__uacs_modal();
	__mysys_uacs_ent.__uacs_saving();
});
