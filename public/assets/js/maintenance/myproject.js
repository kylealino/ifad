var __mysys_project_ent = new __mysys_project_ent();
function __mysys_project_ent() {
	const mesiteurl = document.getElementById('__siteurl').getAttribute('data-mesiteurl');
	const perms = window.__project_perms || {};
	const rows = window.__project_data || [];
	const byId = {};
	rows.forEach(function (r) { byId[r.recid] = r; });

	let table = null;
	let filter = 'all';

	// One tone per fund cluster, in order of appearance
	const FC_TONES = ['tone-indigo', 'tone-amber', 'tone-green', 'tone-slate'];
	const fcTone = {};
	rows.forEach(function (r) {
		if (!(r.fund_cluster_code in fcTone)) { fcTone[r.fund_cluster_code] = FC_TONES[Object.keys(fcTone).length % FC_TONES.length]; }
	});

	function blank(v) { return v == null || String(v).trim() === ''; }

	function esc(s) {
		return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) {
			return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
		});
	}

	function dash(v) { return blank(v) ? '<span class="text-muted">—</span>' : esc(v); }

	function selectedText(form, name) {
		var sel = form.elements[name];
		return sel && sel.selectedIndex > 0 ? sel.options[sel.selectedIndex].text : '';
	}

	//PROJECT - TABLE (search, fund-cluster pills, stat tiles)
	this.__project_table = function () {
		var $ = window.jQuery;

		// filter keys are "fc:<fund cluster code>"
		$.fn.dataTable.ext.search.push(function (settings, data, idx) {
			if (settings.nTable.id !== 'projectTable' || filter === 'all') { return true; }
			return 'fc:' + settings.aoData[idx]._aData.fund_cluster_code === filter;
		});

		table = $('#projectTable').DataTable({
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
					data: 'project_title', className: 'cell-primary',
					render: function (v, type, r) {
						if (type !== 'display') { return (v || '') + ' ' + (r.responsibility_code || ''); }
						return '<div style="min-width:0"><div class="fw-semibold text-dark" style="overflow-wrap:anywhere">' + esc(v) + '</div>'
							+ '<div class="small text-muted">' + esc(r.responsibility_code || '') + '</div></div>';
					}
				},
				{
					data: 'fund_cluster_code', className: 'cell-left',
					render: function (v, type) { return type === 'display' ? '<span class="fods-chip ' + (fcTone[v] || 'tone-slate') + '">FC ' + esc(v) + '</span>' : v; }
				},
				{ data: 'division_name', className: 'cell-right', render: function (v, type) { return type === 'display' ? dash(v) : (v || ''); } },
				{ data: 'project_leader', className: 'd-none d-lg-table-cell', render: function (v, type) { return type === 'display' ? dash(v) : (v || ''); } },
				{ data: 'mfopaps_code', className: 'd-none d-xl-table-cell', render: function (v, type) { return type === 'display' ? dash(v) : (v || ''); } },
				{
					data: null, orderable: false, searchable: false, className: 'cell-actions text-end',
					render: function (v, type, r) {
						return perms.edit
							? '<div class="d-flex justify-content-end"><button type="button" class="fods-icon-btn is-teal project-edit" data-recid="' + r.recid + '" title="Edit"><i class="ti ti-pencil"></i></button></div>'
							: '';
					}
				}
			]
		});

		$('#projectTableSearch').on('input', function () { table.search(this.value).draw(); });

		function applyFilter(key) {
			filter = key;
			$('#projectTableFilter button').removeClass('active').filter('[data-filter="' + key + '"]').addClass('active');
			$('.fods-stat[data-table="projectTable"]').removeClass('is-active');
			if (key !== 'all') { $('.fods-stat[data-table="projectTable"][data-filter="' + key + '"]').addClass('is-active'); }
			table.draw();
		}
		$(document).on('click', '#projectTableFilter button', function () { applyFilter($(this).data('filter')); });
		$(document).on('click', '.fods-stat[data-table="projectTable"]', function () { applyFilter($(this).data('filter')); });
	};

	//PROJECT - ADD / EDIT MODAL (filled from the row, no page reload)
	this.__project_modal = function () {
		var $ = window.jQuery;
		var form = document.getElementById('projectForm');
		var modalEl = document.getElementById('projectModal');

		function setField(name, value) {
			var el = form.elements[name];
			if (!el) { return; }
			value = value == null ? '' : String(value);
			if (el.tagName === 'SELECT' && value !== '' && !Array.prototype.some.call(el.options, function (op) { return op.value === value; })) {
				el.add(new Option(value, value));
			}
			el.value = value;
		}

		function open(row) {
			form.reset();
			form.classList.remove('was-validated');
			Array.prototype.forEach.call(form.elements, function (el) {
				if (el.name) { setField(el.name, row ? row[el.name] : ''); }
			});
			document.getElementById('projectModalTitle').textContent = row ? row.project_title : 'Add Project';
			document.getElementById('projectModalSub').textContent = row
				? 'Responsibility code ' + (row.responsibility_code || '—')
				: 'A reference project for budget and obligations.';
			document.getElementById('submitBtn').textContent = row ? 'Save changes' : 'Create';
			bootstrap.Modal.getOrCreateInstance(modalEl).show();
		}

		modalEl.addEventListener('shown.bs.modal', function () { document.getElementById('project_title').focus(); });
		$(document).on('click', '#projectTableAdd', function () { open(null); });
		$(document).on('click', '#projectTable .project-edit', function () { open(byId[$(this).data('recid')]); });
	};

	//PROJECT - SAVE
	this.__project_saving = function () {
		var $ = window.jQuery;
		var form = document.getElementById('projectForm');

		form.addEventListener('submit', function (e) {
			e.preventDefault();
			if (!form.checkValidity()) { form.classList.add('was-validated'); return; }

			var params = $(form).serializeArray();
			// the model validates and stores the names alongside the ids
			params.push({ name: 'fund_cluster_code', value: selectedText(form, 'fundcluster_id') });
			params.push({ name: 'division_name', value: selectedText(form, 'division_id') });
			params.push({ name: 'meaction', value: 'MAIN-SAVE' });

			var $btn = $('#submitBtn'), label = $btn.html();
			$btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Saving...');
			$.ajax({
				type: 'POST',
				url: mesiteurl + 'myproject',
				data: params,
				global: false,
				cache: false,
				success: function (data) { $('.me-outp-msg').html(data); },
				error: function (xhr, status, error) { toastr.error('[PROJECT]', 'Error loading page...' + error, { closeButton: true }); },
				complete: function () { setTimeout(function () { $btn.prop('disabled', false).html(label); }, 1200); }
			});
		});
	};
}

// jQuery, DataTables and Bootstrap load in the footer, after this file
document.addEventListener('DOMContentLoaded', function () {
	__mysys_project_ent.__project_table();
	__mysys_project_ent.__project_modal();
	__mysys_project_ent.__project_saving();
});
