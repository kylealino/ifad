var __mysys_approvermap_ent = new __mysys_approvermap_ent();
function __mysys_approvermap_ent() {
	const mesiteurl = document.getElementById('__siteurl').getAttribute('data-mesiteurl');
	const perms = window.__map_perms || {};
	const BOXES = window.__map_boxes || {};
	const users = window.__map_users || [];
	const data = window.__map_data || [];

	let table = null;
	let filter = 'all';

	function blank(v) { return v == null || String(v).trim() === ''; }

	function esc(s) {
		return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) {
			return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
		});
	}

	function num(v) { return parseFloat(v) || 0; }

	function money(n) {
		return '₱' + Number(n || 0).toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
	}

	function initials(name) {
		var base = String(name || '').split(',')[0].trim();
		var t = base.split(/\s+/).filter(function (x) { return x && x.slice(-1) !== '.'; });
		if (!t.length) { return base.charAt(0).toUpperCase(); }
		return (t[0].charAt(0) + (t.length > 1 ? t[t.length - 1].charAt(0) : '')).toUpperCase();
	}

	function keyOf(name) { return String(name || '').trim().toLowerCase(); }
	function boxDef(module, box) { return ((BOXES[module] || {}).boxes || {})[box] || {}; }
	function moduleLabel(m) { return (BOXES[m] || {}).label || m; }
	// "Box A · Requesting office" -> "Box A"
	function shortBox(module, box) { return String(boxDef(module, box).label || box).split(' · ')[0]; }

	//APPROVER MAP - ONE ROW PER PERSON (matched on the name), plus one per box nobody signs
	const people = {};
	const covered = {};
	data.forEach(function (r) {
		var k = keyOf(r.full_name);
		var p = people[k] || (people[k] = { key: k, full_name: r.full_name, active: false, boxes: [], positions: {} });
		p.boxes.push(r);
		if (String(r.is_active) === '1') { p.active = true; covered[r.module + '|' + r.box] = true; }
		if (!blank(r.position)) { p.positions[r.position] = (p.positions[r.position] || 0) + 1; }
	});
	const rows = Object.keys(people).map(function (k) {
		var p = people[k];
		// the title they print most often
		p.position = Object.keys(p.positions).sort(function (a, b) { return p.positions[b] - p.positions[a]; })[0] || '';
		return p;
	});
	Object.keys(BOXES).forEach(function (m) {
		Object.keys(BOXES[m].boxes).forEach(function (b) {
			if (!covered[m + '|' + b]) { rows.push({ key: '', empty: true, module: m, box: b, boxes: [] }); }
		});
	});

	const FILTERS = {
		empty: function (r) { return !!r.empty; },
		inactive: function (r) { return !r.empty && !r.active; }
	};

	// The boxes a person signs, as chips in module order
	function signs(p) {
		var order = [];
		Object.keys(BOXES).forEach(function (m) { Object.keys(BOXES[m].boxes).forEach(function (b) { order.push(m + '|' + b); }); });
		return p.boxes.slice().sort(function (x, y) { return order.indexOf(x.module + '|' + x.box) - order.indexOf(y.module + '|' + y.box); })
			.map(function (r) {
				var def = boxDef(r.module, r.box);
				var extra = def.mode === 'amount' ? ' · from ' + money(r.min_amount)
					: (def.range ? ' · ' + money(r.min_amount) + (r.max_amount == null || r.max_amount === '' ? ' and up' : ' – ' + money(r.max_amount)) : '');
				var tone = String(r.is_active) !== '1' ? 'tone-slate' : (def.mode === 'amount' || def.range ? 'tone-indigo' : 'tone-teal');
				return '<span class="fods-chip ' + tone + '" title="' + esc(moduleLabel(r.module) + ' ' + (boxDef(r.module, r.box).label || r.box) + (r.position ? ' — ' + r.position : '')) + '">'
					+ esc(moduleLabel(r.module) + ' ' + shortBox(r.module, r.box) + extra) + '</span>';
			}).join('');
	}

	//APPROVER MAP - TABLE (search, filter pills, stat tiles)
	this.__map_table = function () {
		var $ = window.jQuery;

		$.fn.dataTable.ext.search.push(function (settings, d, idx) {
			if (settings.nTable.id !== 'mapTable' || filter === 'all') { return true; }
			var r = settings.aoData[idx]._aData;
			if (FILTERS[filter]) { return FILTERS[filter](r); }
			return r.empty ? r.module === filter : r.boxes.some(function (b) { return b.module === filter; });
		});

		table = $('#mapTable').DataTable({
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
					data: 'full_name', className: 'cell-primary',
					render: function (v, type, r) {
						// boxes nobody signs come first
						if (type === 'sort' || type === 'type') { return r.empty ? '0' + moduleLabel(r.module) + r.box : '1' + keyOf(v); }
						if (type !== 'display') { return r.empty ? 'no approver' : (v || '') + ' ' + (r.position || ''); }
						if (r.empty) { return '<span class="fods-chip tone-amber"><i class="ti ti-alert-triangle"></i>No approver yet</span>'; }
						return '<div class="d-flex align-items-center gap-2" style="min-width:0">'
							+ '<div class="fods-avatar tone-slate">' + esc(initials(v)) + '</div>'
							+ '<div style="min-width:0"><div class="fw-semibold text-dark" style="overflow-wrap:anywhere">' + esc(v) + '</div>'
							+ (blank(r.position) ? '' : '<div class="small text-muted" style="overflow-wrap:anywhere">' + esc(r.position) + '</div>')
							+ '</div></div>';
					}
				},
				{
					data: null, className: 'cell-left', orderable: false,
					render: function (v, type, r) {
						if (r.empty) {
							var label = moduleLabel(r.module) + ' ' + (boxDef(r.module, r.box).label || r.box);
							return type === 'display' ? '<span class="fw-semibold text-dark">' + esc(label) + '</span>' : label;
						}
						if (type !== 'display') { return r.boxes.map(function (b) { return moduleLabel(b.module) + ' ' + (boxDef(b.module, b.box).label || ''); }).join(' '); }
						return '<div class="map-signs">' + signs(r) + '</div>';
					}
				},
				{
					data: null, className: 'd-none d-lg-table-cell', orderable: false,
					render: function (v, type, r) {
						if (type !== 'display' || r.empty) { return ''; }
						return r.active ? '<span class="fods-chip tone-green">Active</span>' : '<span class="fods-chip tone-slate">Inactive</span>';
					}
				},
				{
					data: null, orderable: false, searchable: false, className: 'cell-actions text-end',
					render: function (v, type, r) {
						var html = '';
						if (r.empty) {
							if (perms.add || perms.edit) { html += '<button type="button" class="btn btn-sm btn-fods px-3 map-add-box" data-module="' + esc(r.module) + '" data-box="' + esc(r.box) + '"><i class="ti ti-plus me-1"></i>Add</button>'; }
						} else {
							if (perms.edit) { html += '<button type="button" class="fods-icon-btn is-teal map-edit" data-key="' + esc(r.key) + '" title="Edit"><i class="ti ti-pencil"></i></button>'; }
							if (perms.del) { html += '<button type="button" class="fods-icon-btn is-danger map-delete" data-key="' + esc(r.key) + '" title="Remove"><i class="ti ti-trash"></i></button>'; }
						}
						return '<div class="d-flex justify-content-end gap-1">' + html + '</div>';
					}
				}
			]
		});

		$('#mapTableSearch').on('input', function () { table.search(this.value).draw(); });

		function applyFilter(key) {
			filter = key;
			$('#mapTableFilter button').removeClass('active').filter('[data-filter="' + key + '"]').addClass('active');
			$('.fods-stat[data-table="mapTable"]').removeClass('is-active');
			if (key !== 'all') { $('.fods-stat[data-table="mapTable"][data-filter="' + key + '"]').addClass('is-active'); }
			table.draw();
		}
		$(document).on('click', '#mapTableFilter button', function () { applyFilter($(this).data('filter')); });
		$(document).on('click', '.fods-stat[data-table="mapTable"]', function () { applyFilter($(this).data('filter')); });
	};

	//APPROVER MAP - ADD / EDIT MODAL (one person, every box they sign)
	this.__map_modal = function () {
		var $ = window.jQuery;
		var form = document.getElementById('mapForm');
		var modalEl = document.getElementById('mapModal');
		var nameEl = document.getElementById('map_full_name');
		var posEl = document.getElementById('map_position');
		var lastPos = '';

		function boxEls() { return form.querySelectorAll('.map-box'); }

		// Picks a name in the user list; a mapped name with no matching FODS account
		// is added as its own option (marked) so it can still be edited
		function setName(name) {
			nameEl.querySelectorAll('option[data-extra]').forEach(function (o) { o.remove(); });
			if (name === '') { nameEl.value = ''; return; }
			var match = Array.prototype.filter.call(nameEl.options, function (o) { return o.value !== '' && keyOf(o.value) === keyOf(name); })[0];
			if (!match) {
				match = new Option(name + ' (no FODS account)', name);
				match.setAttribute('data-extra', '1');
				nameEl.add(match);
			}
			nameEl.value = match.value;
		}

		function setBox(el, row, checked) {
			var check = el.querySelector('.map-box-check');
			check.checked = checked;
			el.querySelector('.map-box-position').value = row ? (row.position || '') : (checked ? posEl.value : '');
			var min = el.querySelector('.map-box-min');
			if (min) { min.value = row && num(row.min_amount) ? row.min_amount : ''; }
			var max = el.querySelector('.map-box-max');
			if (max) { max.value = row && row.max_amount != null && row.max_amount !== '' ? row.max_amount : ''; }
		}

		// Show a ticked box's fields; count the ticks per module and overall
		function sync() {
			var total = 0;
			form.querySelectorAll('.map-module').forEach(function (mod) {
				var n = 0;
				mod.querySelectorAll('.map-box').forEach(function (el) {
					var on = el.querySelector('.map-box-check').checked;
					el.querySelector('.map-box-fields').classList.toggle('d-none', !on);
					if (on) { n++; }
				});
				total += n;
				mod.querySelector('.map-module-count').textContent = n + ' of ' + mod.querySelectorAll('.map-box').length;
				mod.classList.toggle('has-selection', n > 0);
			});
			document.getElementById('mapBoxCount').textContent = total ? total + ' ticked' : 'None ticked';
		}

		// presets: extra boxes to tick ("module|box" => true)
		function fill(person, presets) {
			form.reset();
			form.classList.remove('was-validated');
			form.elements.orig_name.value = person ? person.full_name : '';
			setName(person ? person.full_name : '');
			posEl.value = lastPos = person ? person.position : '';
			document.getElementById('map_is_active').checked = !person || person.active;

			var own = {};
			(person ? person.boxes : []).forEach(function (r) { own[r.module + '|' + r.box] = r; });
			boxEls().forEach(function (el) {
				var k = el.getAttribute('data-module') + '|' + el.getAttribute('data-box');
				setBox(el, own[k] || null, !!own[k] || !!(presets || {})[k]);
			});
			sync();

			document.getElementById('mapModalTitle').textContent = person ? person.full_name : 'Add Approver';
			document.getElementById('mapModalSub').textContent = person ? 'Tick or untick the boxes they sign.' : 'Pick the person, then tick every box they sign.';
			document.getElementById('submitBtn').textContent = person ? 'Save changes' : 'Create';
		}

		function open(person, presets) {
			fill(person, presets);
			bootstrap.Modal.getOrCreateInstance(modalEl).show();
		}

		form.addEventListener('change', function (e) {
			if (!e.target.classList.contains('map-box-check')) { return; }
			var el = e.target.closest('.map-box');
			var pos = el.querySelector('.map-box-position');
			if (e.target.checked && blank(pos.value)) { pos.value = posEl.value; }
			sync();
		});

		// The person's position fills each ticked box that still has the old one
		posEl.addEventListener('input', function () {
			var now = this.value;
			form.querySelectorAll('.map-box-position').forEach(function (p) {
				if (p.closest('.map-box').querySelector('.map-box-check').checked && (blank(p.value) || p.value === lastPos)) { p.value = now; }
			});
			lastPos = now;
		});

		// Picking another person switches the form to them: a mapped person loads their own
		// boxes, anyone else starts with none ticked. Boxes ticked on a new entry carry over;
		// an edited person's boxes stay with that person.
		nameEl.addEventListener('change', function () {
			var picked = nameEl.value;
			var editing = form.elements.orig_name.value !== '';
			var known = people[keyOf(picked)];
			if (editing && keyOf(picked) === keyOf(form.elements.orig_name.value)) { return; }

			var ticked = {};
			if (!editing) {
				boxEls().forEach(function (el) {
					if (el.querySelector('.map-box-check').checked) { ticked[el.getAttribute('data-module') + '|' + el.getAttribute('data-box')] = true; }
				});
			}

			if (known) {
				fill(known, ticked);
				if (!editing) { toastr.info('Already mapped: their boxes are loaded here.', '', { closeButton: true }); }
				return;
			}

			fill(null, ticked);
			setName(picked);
			// their position fills in (and fills the boxes carried over); still editable
			var u = users.filter(function (x) { return keyOf(x.full_name) === keyOf(picked); })[0];
			posEl.value = u ? (u.position || '') : '';
			posEl.dispatchEvent(new Event('input'));
		});

		modalEl.addEventListener('shown.bs.modal', function () { nameEl.focus(); });
		$(document).on('click', '#mapTableAdd', function () { open(null); });
		$(document).on('click', '#mapTable .map-add-box', function () {
			var preset = {};
			preset[$(this).attr('data-module') + '|' + $(this).attr('data-box')] = true;
			open(null, preset);
		});
		$(document).on('click', '#mapTable .map-edit', function () { open(people[$(this).attr('data-key')]); });
	};

	function post(params, btn) {
		var $ = window.jQuery;
		var $btn = $(btn), label = $btn.html();
		$btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Saving...');
		$.ajax({
			type: 'POST',
			url: mesiteurl + 'myapprovermap',
			data: params,
			global: false,
			cache: false,
			success: function (d) { $('.me-outp-msg').html(d); },
			error: function (xhr, status, error) { toastr.error('[APPROVER MAP]', 'Error loading page...' + error, { closeButton: true }); },
			complete: function () { setTimeout(function () { $btn.prop('disabled', false).html(label); }, 1200); }
		});
	}

	//APPROVER MAP - SAVE (each ticked box: module x|x box x|x position x|x from amount x|x default)
	this.__map_saving = function () {
		var form = document.getElementById('mapForm');
		form.addEventListener('submit', function (e) {
			e.preventDefault();
			if (!form.checkValidity()) { form.classList.add('was-validated'); return; }
			var boxdata = [];
			form.querySelectorAll('.map-box').forEach(function (el) {
				if (!el.querySelector('.map-box-check').checked) { return; }
				var min = el.querySelector('.map-box-min');
				var max = el.querySelector('.map-box-max');
				boxdata.push([
					el.getAttribute('data-module'),
					el.getAttribute('data-box'),
					document.getElementById('map_position').value.trim(),   // every box prints the person's position
					min ? (min.value || '0') : '0',
					'0',   // default: no longer set (a box's only active person is its default)
					max ? max.value.trim() : ''   // up to (range boxes; blank = no limit)
				].join('x|x'));
			});
			if (!boxdata.length) {
				toastr.warning('Tick at least one box this person signs.', 'Oops!', { closeButton: true });
				return;
			}
			post({
				meaction: 'MAIN-SAVE',
				orig_name: form.elements.orig_name.value,
				full_name: document.getElementById('map_full_name').value.trim(),
				is_active: document.getElementById('map_is_active').checked ? 1 : 0,
				boxdata: boxdata
			}, document.getElementById('submitBtn'));
		});
	};

	//APPROVER MAP - DELETE (confirm modal; removes the person from every box)
	this.__map_delete = function () {
		var $ = window.jQuery;
		var pending = null;
		var confirmEl = document.getElementById('mapDeleteModal');
		if (!confirmEl) { return; }

		$(document).on('click', '#mapTable .map-delete', function () {
			pending = people[$(this).attr('data-key')];
			document.getElementById('mapDeleteName').textContent = pending.full_name + ' — ' + pending.boxes.length + (pending.boxes.length === 1 ? ' box' : ' boxes');
			bootstrap.Modal.getOrCreateInstance(confirmEl).show();
		});

		document.getElementById('mapDeleteBtn').addEventListener('click', function () {
			if (!pending) { return; }
			post({ full_name: pending.full_name, meaction: 'MAIN-DELETE' }, this);
			bootstrap.Modal.getOrCreateInstance(confirmEl).hide();
		});
	};
}

// jQuery, DataTables and Bootstrap load in the footer, after this file
document.addEventListener('DOMContentLoaded', function () {
	__mysys_approvermap_ent.__map_table();
	__mysys_approvermap_ent.__map_modal();
	__mysys_approvermap_ent.__map_saving();
	__mysys_approvermap_ent.__map_delete();
});
