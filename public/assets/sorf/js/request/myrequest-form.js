// Request form (request/request-form.php): builds "03 — Request Details" for
// the chosen document type from Config\RequestForms, then saves.
var __mysys_request_form = new __mysys_request_form_ent();
function __mysys_request_form_ent() {
	const app = __mysys_drf;
	const esc = app.esc;
	const cfg = window.__request_form || {};
	const record = cfg.record || null;

	const form = document.getElementById('requestForm');
	const docType = document.getElementById('document_id');
	const details = document.getElementById('requestDetails');

	// NAME (upper case) -> position, to fill the position beside a chosen name
	const positions = {};
	(cfg.employees || []).forEach(function (e) { positions[e.full_name.toUpperCase()] = e.position; });

	// Grid share of each field type in a line
	const WIDTHS = { staff: '2fr', textarea: '2fr', text: '1.4fr', money: '1fr' };
	const FUNDS = [['general_fund', 'General Fund'], ['project_fund', 'Project Funds'], ['other_fund', 'Others']];

	let nextRow = 0;

	// the signature pad, shared by saving and the live preview
	let signPad = null;
	function pad() {
		if (!signPad) { signPad = __mysys_drf_signature(form.querySelector('[data-drf-sign]')); }
		return signPad;
	}

	function val(v) { return v == null ? '' : String(v); }

	// One input of a line: rows[<n>][<key>]
	function input(field, n, value) {
		const name = 'rows[' + n + '][' + field.key + ']';
		const label = esc(field.label);
		let html = '<label class="drf-row-label">' + label + '</label>';
		if (field.type === 'textarea') {
			return html + '<textarea class="form-control" name="' + name + '" rows="1" aria-label="' + label + '">' + esc(value) + '</textarea>';
		}
		if (field.type === 'money') {
			return html + '<input type="text" inputmode="decimal" class="form-control is-money" name="' + name + '" value="' + esc(value) + '" placeholder="0.00" aria-label="' + label + '">';
		}
		return html + '<input type="text" class="form-control" name="' + name + '" value="' + esc(value) + '" aria-label="' + label + '"'
			+ (field.type === 'staff' ? ' list="employeeList" data-staff autocomplete="off"' : '') + '>';
	}

	function rowHtml(def, row) {
		const n = nextRow++;
		return '<div class="drf-row">'
			+ def.fields.map(function (f) { return '<div>' + input(f, n, row ? val(row[f.key]) : '') + '</div>'; }).join('')
			+ '<div><button type="button" class="drf-icon-btn is-danger" data-row-remove title="Remove line" aria-label="Remove line"><i class="ti ti-trash"></i></button></div>'
			+ '</div>';
	}

	function linesHtml(def, rows) {
		return '<div class="drf-subhead"><span>' + (def.layout === 'lto' ? 'Authority to travel is granted to' : 'Request lines') + '</span>'
			+ '<button type="button" class="btn btn-sm btn-drf-ghost" id="rowAdd"><i class="ti ti-plus me-1"></i>Add line</button></div>'
			+ '<div class="drf-rows" id="rowList">'
			+ '<div class="drf-rows-head">' + def.fields.map(function (f) { return '<div>' + esc(f.label) + '</div>'; }).join('') + '<div></div></div>'
			+ (rows.length ? rows : [null]).map(function (r) { return rowHtml(def, r); }).join('')
			+ '</div>';
	}

	// Local Travel Order: destination, funds and the expense grid
	function ltoHtml(lto, expenses) {
		function area(key, label, required) {
			return '<label class="form-label" for="lto_' + key + '">' + label + (required ? ' <span class="drf-req">*</span>' : '') + '</label>'
				+ '<textarea class="form-control" id="lto_' + key + '" name="lto[' + key + ']" rows="3"' + (required ? ' required' : '') + '>' + esc(val(lto[key])) + '</textarea>';
		}
		function expense(code, label, cls) {
			const line = expenses[code] || {};
			return '<div class="drf-row"><div class="' + cls + '">' + esc(label) + '</div>'
				+ FUNDS.map(function (f) {
					return '<div><label class="drf-row-label">' + f[1] + '</label>'
						+ '<input type="text" class="form-control" name="expenses[' + code + '][' + f[0] + ']" value="' + esc(val(line[f[0]])) + '" maxlength="50" aria-label="' + esc(label + ', ' + f[1]) + '"></div>';
				}).join('') + '</div>';
		}

		let grid = '';
		cfg.ltoExpenses.forEach(function (group) {
			grid += group.code !== '' ? expense(group.code, group.label, 'drf-row-group')
				: '<div class="drf-row"><div class="drf-row-group">' + esc(group.label) + '</div></div>';
			Object.keys(group.items).forEach(function (code) { grid += expense(code, group.items[code], 'drf-row-item'); });
		});

		return '<div class="drf-subhead"><span>Travel</span></div>'
			+ '<div class="row g-3">'
			+ '<div class="col-md-6">' + area('destination', 'Destination/s', true) + '</div>'
			+ '<div class="col-md-6">' + area('travel_dates', 'Inclusive Date/s of Travel', true) + '</div>'
			+ '<div class="col-12">' + area('purpose', 'Purpose(s) of the Travel', true) + '</div>'
			+ '</div>'
			+ '<div class="drf-subhead"><span>Fund the travel expenses are charged to</span></div>'
			+ '<div class="row g-3 align-items-end">'
			+ '<div class="col-md-4"><div class="form-check mb-2"><input class="form-check-input" type="checkbox" id="lto_general" name="lto[is_general_fund]" value="1"' + (Number(lto.is_general_fund) === 1 ? ' checked' : '') + '>'
			+ '<label class="form-check-label" for="lto_general">General Fund</label></div></div>'
			+ '<div class="col-md-4"><label class="form-label" for="lto_project_fund">Project Funds (pls. specify)</label><input type="text" class="form-control" id="lto_project_fund" name="lto[project_fund]" value="' + esc(val(lto.project_fund)) + '" maxlength="150"></div>'
			+ '<div class="col-md-4"><label class="form-label" for="lto_other_fund">Others (e.g. sponsor/requesting agency)</label><input type="text" class="form-control" id="lto_other_fund" name="lto[other_fund]" value="' + esc(val(lto.other_fund)) + '" maxlength="150"></div>'
			+ '</div>'
			+ '<div class="drf-subhead"><span>Travel expenses to be incurred</span></div>'
			+ '<p class="drf-hint">Enter the amount (or a short note) under the fund each expense is charged to. Leave blank what does not apply.</p>'
			+ '<div class="drf-rows" style="--drf-cols: 1.6fr 1fr 1fr 1fr">'
			+ '<div class="drf-rows-head"><div>Expense</div>' + FUNDS.map(function (f) { return '<div>' + f[1] + '</div>'; }).join('') + '</div>'
			+ grid + '</div>'
			+ '<div class="mt-3">' + area('remarks', 'Remarks/Special Instructions', false) + '</div>';
	}

	//DETAILS - (re)build section 03 for the chosen document type
	this.__request_details = function () {
		function build(keepRecord) {
			const def = cfg.forms[docType.value];
			if (!def) {
				details.innerHTML = '<p class="drf-hint mb-0">Select a document type above to enter the required information.</p>';
				return;
			}
			const hd = keepRecord ? record.hd : {};
			const isLto = def.layout === 'lto';
			nextRow = 0;

			details.innerHTML = '<div class="row g-3">'
				+ '<div class="col-md-4"><label class="form-label" for="request_date">Date of Request <span class="drf-req">*</span></label>'
				+ '<input type="date" class="form-control" id="request_date" name="request_date" value="' + esc(hd.request_date || cfg.today) + '" required></div>'
				+ (isLto ? '' : '<div class="col-md-8"><label class="form-label" for="subject">Subject <span class="drf-req">*</span></label>'
					+ '<input type="text" class="form-control" id="subject" name="subject" value="' + esc(val(hd.subject)) + '" maxlength="255" required></div>')
				+ '<div class="' + (isLto ? 'col-md-8' : 'col-12') + '"><label class="form-label" for="noted_by">' + esc(def.noted_label.replace(/:/g, '')) + ' <span class="drf-req">*</span></label>'
				+ '<input type="text" class="form-control" id="noted_by" name="noted_by" value="' + esc(val(hd.noted_by)) + '" maxlength="100" list="approverList" autocomplete="off" placeholder="Choose the chief who will sign" required>'
				+ '<div class="drf-hint mt-1">' + (cfg.approvers > 0
					? 'Pick a registered user. Once they sign and approve the request, it is submitted to HR.'
					: 'Nobody else has registered yet. Whoever notes your request needs an account here to sign it.') + '</div></div>'
				+ '</div>'
				+ linesHtml(def, keepRecord ? record.rows : [])
				+ (isLto ? ltoHtml(keepRecord && record.lto ? record.lto : {}, keepRecord ? (record.expenses || {}) : {}) : '');

			document.getElementById('rowList').style.setProperty('--drf-cols', def.fields.map(function (f) { return WIDTHS[f.type]; }).join(' ') + ' 34px');
		}

		docType.addEventListener('change', function () { build(false); });
		build(record !== null);
	};

	//LINES - add / remove, and fill the position of a chosen staff name
	this.__request_rows = function () {
		details.addEventListener('click', function (e) {
			const list = document.getElementById('rowList');
			if (e.target.closest('#rowAdd')) {
				list.insertAdjacentHTML('beforeend', rowHtml(cfg.forms[docType.value], null));
				list.lastElementChild.querySelector('input, textarea').focus();
				return;
			}
			const remove = e.target.closest('[data-row-remove]');
			if (!remove) { return; }
			const row = remove.closest('.drf-row');
			// always keep one line to type in
			if (list.querySelectorAll('.drf-row').length > 1) {
				row.remove();
			} else {
				row.querySelectorAll('input, textarea').forEach(function (el) { el.value = ''; });
			}
		});

		details.addEventListener('change', function (e) {
			if (!e.target.matches('[data-staff]')) { return; }
			const position = positions[e.target.value.trim().toUpperCase()];
			const beside = e.target.closest('.drf-row').querySelector('[name$="[position]"]');
			if (position && beside && beside.value.trim() === '') { beside.value = position; }
		});
	};

	//SAVE - signed by the requester, then sent to the "Noted by" signatory
	this.__request_saving = function () {
		const btn = document.getElementById('requestSubmit');
		const sign = pad();
		form.addEventListener('submit', function (e) {
			e.preventDefault();
			if (!form.checkValidity()) {
				form.classList.add('was-validated');
				const first = form.querySelector(':invalid');
				if (first) { first.focus(); }
				app.toast('Please fill in the required fields.', 'error');
				return;
			}
			if (sign.state() === 'empty') {
				app.toast('Please sign the request before submitting it.', 'error');
				form.querySelector('[data-drf-sign]').scrollIntoView({ behavior: 'smooth', block: 'center' });
				return;
			}
			const data = new FormData(form);
			data.set('signature', sign.value());   // '' keeps the signature already on the request
			data.set('document_id', docType.value);   // a disabled select is left out of the form
			data.append('meaction', 'MAIN-SAVE');

			const done = app.busy(btn, 'Saving...');
			app.post('myrequest', data).then(function (res) {
				app.toast(res.message, 'success');
				window.location.href = app.url('myrequest?meaction=VIEW&recid=' + res.recid);
			}).catch(function (err) {
				done();
				app.toast(err.message, 'error');
			});
		});
	};

	//PREVIEW - the request form PDF beside the form, made again a moment after
	// each change. PDF.js draws the pages off-screen and they replace the old
	// ones in one go, so the preview never flashes. (The browser's own PDF
	// viewer shows a dark page each time it opens a new file.)
	this.__request_preview = function () {
		const panel = document.getElementById('previewPanel');
		if (!panel) { return; }
		const pages = document.getElementById('previewPages');
		const state = document.getElementById('previewState');
		const empty = document.getElementById('previewEmpty');
		const full = document.getElementById('previewFull');
		const pdfjs = window.pdfjsLib || null;
		const WAIT_MS = 600;
		if (pdfjs) { pdfjs.GlobalWorkerOptions.workerSrc = cfg.pdfWorker; }

		let latest = null;           // newest PDF as a link, for "Full size"
		let bytes = null;            // newest PDF, to draw again when the panel is resized
		let drawnWidth = 0;
		let timer = null;
		let ctrl = null;
		let seq = 0;

		function setState(text, tone) {
			state.textContent = text;
			state.className = 'drf-preview-state' + (tone ? ' is-' + tone : '');
		}

		// Every page as a canvas the width of the panel, sharp on high-DPI screens
		function draw(data) {
			const width = Math.max(200, pages.clientWidth - 24);
			const ratio = Math.max(window.devicePixelRatio || 1, 1);
			// PDF.js takes the bytes over; it gets a copy so they can be drawn again
			return pdfjs.getDocument({ data: data.slice(0) }).promise.then(function (doc) {
				const jobs = [];
				for (let n = 1; n <= doc.numPages; n++) {
					jobs.push(doc.getPage(n).then(function (page) {
						const viewport = page.getViewport({ scale: width / page.getViewport({ scale: 1 }).width * ratio });
						const canvas = document.createElement('canvas');
						canvas.className = 'drf-preview-page';
						canvas.width = Math.floor(viewport.width);
						canvas.height = Math.floor(viewport.height);
						canvas.style.width = Math.floor(viewport.width / ratio) + 'px';
						return page.render({ canvasContext: canvas.getContext('2d'), viewport: viewport }).promise.then(function () { return canvas; });
					}));
				}
				return Promise.all(jobs).then(function (canvases) {
					doc.destroy();
					drawnWidth = width;
					return canvases;
				});
			});
		}

		// The new pages take the old ones' place at once; the reader stays where they were
		function paint(token) {
			if (!bytes || !pdfjs) { return Promise.resolve(); }
			return draw(bytes).then(function (canvases) {
				if (token !== seq) { return; }   // a newer preview is on its way
				const top = pages.scrollTop;
				pages.replaceChildren.apply(pages, canvases);
				pages.scrollTop = top;
			});
		}

		function show(blob, token) {
			if (latest) { URL.revokeObjectURL(latest); }
			latest = URL.createObjectURL(blob);
			full.href = latest;
			full.hidden = false;
			return blob.arrayBuffer().then(function (data) {
				bytes = data;
				return paint(token);
			});
		}

		function update() {
			const chosen = !!cfg.forms[docType.value];
			empty.hidden = chosen;
			if (!chosen) {
				setState('', '');
				return;
			}
			const sign = pad();
			const data = new FormData(form);
			data.set('document_id', docType.value);   // a disabled select is left out of the form
			data.set('signature', sign.state() === 'new' ? sign.value() : '');
			data.set('signature_state', sign.state());
			data.append('meaction', 'FORM-PREVIEW');

			// only the newest answer counts
			if (ctrl) { ctrl.abort(); }
			ctrl = new AbortController();
			const mine = ++seq;
			setState('Updating…', 'busy');
			app.postFile('myrequest', data, ctrl.signal).then(function (blob) {
				if (mine !== seq) { return; }
				return show(blob, mine).then(function () {
					if (mine !== seq) { return; }
					// without PDF.js the "Full size" link still opens the form
					setState(pdfjs ? 'Up to date' : 'Open it with "Full size"', pdfjs ? 'ok' : '');
				});
			}).catch(function (err) {
				if (err.name === 'AbortError' || mine !== seq) { return; }
				setState(err.message, 'error');
			});
		}

		// a resized panel (window, sidebar) gets the pages drawn again at its width
		let resizeTimer = null;
		window.addEventListener('resize', function () {
			clearTimeout(resizeTimer);
			resizeTimer = setTimeout(function () {
				if (bytes && Math.abs(Math.max(200, pages.clientWidth - 24) - drawnWidth) > 4) { paint(seq); }
			}, 250);
		});

		function later() {
			clearTimeout(timer);
			timer = setTimeout(update, WAIT_MS);
		}

		form.addEventListener('input', later);
		form.addEventListener('change', later);
		form.addEventListener('drf-sign-change', later);
		// adding or removing a line changes the form without an input event
		details.addEventListener('click', function (e) {
			if (e.target.closest('#rowAdd, [data-row-remove]')) { later(); }
		});
		update();
	};
}

document.addEventListener('DOMContentLoaded', function () {
	__mysys_request_form.__request_details();
	__mysys_request_form.__request_rows();
	__mysys_request_form.__request_saving();
	__mysys_request_form.__request_preview();
});
