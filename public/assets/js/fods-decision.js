// Approve / Disapprove confirmation, the same in every module (ORS, BURS, DV, Budget,
// LDDAP-ADA, ADA, PR, PO). It opens over the record window it was called from (placed
// inside that window, so it dims it and keeps its focus; Esc closes only this dialog).
// It shows what is being decided (record, amount, the step and the one after it) and
// takes the remarks: optional to approve, required to disapprove ("Reason for returning").
// Ctrl+Enter confirms; the confirm button locks while the module saves.
//
//   FodsDecision.open({
//     host: recordModalEl, mode: 'approve' | 'disapprove', verb: 'Certify' (default 'Approve'),
//     title: 'Approve DV · Box A', subtitle: 'DV 20262189 · Payee',
//     facts: [['Net amount', '₱9,059.63', true], ['Step', 'Box A · Supervisor'], ['Next', 'Box C · Accounting']],
//     maxlength: 100,
//     submit: function (remarks, done) { ...post...; on success FodsDecision.close(); always done(); }
//   });
(function () {
	var el = null, backdrop = null, opts = null, busy = false, returnFocus = null;

	function esc(s) {
		return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) {
			return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
		});
	}

	function build() {
		if (el) { return; }
		backdrop = document.createElement('div');
		backdrop.className = 'fods-confirm-backdrop';
		backdrop.addEventListener('click', function () { if (!busy) { close(); } });

		el = document.createElement('div');
		el.className = 'fods-confirm';
		el.setAttribute('role', 'dialog');
		el.setAttribute('aria-modal', 'true');
		el.setAttribute('aria-labelledby', 'fodsDecTitle');
		el.tabIndex = -1;
		el.innerHTML = '<div class="fods-confirm-head"><div class="fods-modal-icon" id="fodsDecIcon"></div>'
			+ '<div style="min-width:0;flex:1"><h5 class="fods-confirm-title" id="fodsDecTitle"></h5><div class="fods-confirm-sub" id="fodsDecSub"></div></div>'
			+ '<button type="button" class="btn-close" data-dec-close aria-label="Close"></button></div>'
			+ '<div class="fods-confirm-body"><dl class="fods-confirm-facts" id="fodsDecFacts"></dl>'
			+ '<label class="form-label" for="fodsDecRemarks" id="fodsDecLabel"></label>'
			+ '<textarea class="form-control" id="fodsDecRemarks" rows="3"></textarea>'
			+ '<div class="invalid-feedback">Give the reason for returning it.</div></div>'
			+ '<div class="fods-confirm-foot"><button type="button" class="btn btn-sm btn-fods-ghost px-3" data-dec-close>Cancel</button>'
			+ '<button type="button" class="btn btn-sm px-4" id="fodsDecConfirm"></button></div>';
		el.querySelectorAll('[data-dec-close]').forEach(function (b) { b.addEventListener('click', function () { if (!busy) { close(); } }); });
		el.querySelector('#fodsDecConfirm').addEventListener('click', confirm);
		var remarks = el.querySelector('#fodsDecRemarks');
		remarks.addEventListener('input', function () { remarks.classList.remove('is-invalid'); });
		remarks.addEventListener('keydown', function (e) {
			if (e.key === 'Enter' && (e.ctrlKey || e.metaKey)) { e.preventDefault(); confirm(); }
		});
	}

	function isOpen() { return !!el && el.classList.contains('is-open'); }

	function open(o) {
		build();
		opts = o || {};
		busy = false;
		var approve = opts.mode !== 'disapprove';
		var verb = approve ? (opts.verb || 'Approve') : 'Disapprove';

		var icon = el.querySelector('#fodsDecIcon');
		icon.className = 'fods-modal-icon ' + (approve ? 'tone-green' : 'tone-red');
		icon.innerHTML = '<i class="ti ' + (approve ? 'ti-check' : 'ti-arrow-back-up') + '"></i>';
		el.querySelector('#fodsDecTitle').textContent = opts.title || verb;
		el.querySelector('#fodsDecSub').textContent = opts.subtitle || '';
		el.querySelector('#fodsDecFacts').innerHTML = (opts.facts || []).filter(function (f) { return f && f[1] !== '' && f[1] != null; })
			.map(function (f) { return '<div class="' + (f[2] ? 'is-key' : '') + '"><dt>' + esc(f[0]) + '</dt><dd>' + esc(f[1]) + '</dd></div>'; }).join('');
		el.querySelector('#fodsDecLabel').innerHTML = approve ? 'Remarks <span class="text-muted fw-normal">(optional)</span>' : 'Reason for returning <span class="text-danger">*</span>';
		var remarks = el.querySelector('#fodsDecRemarks');
		remarks.value = '';
		remarks.classList.remove('is-invalid');
		remarks.maxLength = opts.maxlength || 100;
		var btn = el.querySelector('#fodsDecConfirm');
		btn.disabled = false;
		btn.className = 'btn btn-sm px-4 ' + (approve ? 'btn-success' : 'btn-danger');
		btn.textContent = verb;
		btn.setAttribute('data-label', verb);
		el.classList.toggle('is-return', !approve);

		var host = opts.host || document.body;
		if (el.parentNode !== host) { host.appendChild(backdrop); host.appendChild(el); }
		returnFocus = document.activeElement;
		void el.offsetWidth;
		backdrop.classList.add('is-open');
		el.classList.add('is-open');
		setTimeout(function () { remarks.focus(); }, 60);
	}

	function close() {
		if (!isOpen()) { return; }
		el.classList.remove('is-open');
		backdrop.classList.remove('is-open');
		busy = false;
		if (returnFocus && document.contains(returnFocus)) { returnFocus.focus({ preventScroll: true }); }
		returnFocus = null;
	}

	function confirm() {
		if (!isOpen() || busy || !opts) { return; }
		var remarks = el.querySelector('#fodsDecRemarks');
		if (opts.mode === 'disapprove' && !remarks.value.trim()) {
			remarks.classList.add('is-invalid');
			remarks.focus();
			return;
		}
		busy = true;
		var btn = el.querySelector('#fodsDecConfirm');
		btn.disabled = true;
		btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Saving...';
		var done = function () {
			busy = false;
			btn.disabled = false;
			btn.textContent = btn.getAttribute('data-label') || 'Approve';
		};
		try { opts.submit(remarks.value.trim(), done); } catch (e) { done(); throw e; }
	}

	// Esc closes this dialog only, not the record window under it
	document.addEventListener('keydown', function (e) {
		if (e.key === 'Escape' && isOpen()) { e.preventDefault(); e.stopPropagation(); if (!busy) { close(); } }
	}, true);

	// The record window closing takes the dialog with it
	document.addEventListener('hide.bs.modal', function (e) {
		if (isOpen() && el.parentNode === e.target) { close(); }
	});

	window.FodsDecision = { open: open, close: close };
})();
