// Shared by every page (loaded by templates/drf-footer.php after Bootstrap):
// site URLs, escaping, toasts, AJAX with the CSRF token, and the small
// behaviours switched on by data-drf-* attributes.
var __mysys_drf = new __mysys_drf_app();
function __mysys_drf_app() {
	const mesiteurl = document.querySelector('meta[name="drf-siteurl"]').getAttribute('content');
	const csrf = document.querySelector('meta[name="drf-csrf"]').getAttribute('content');
	const self = this;

	this.url = function (path) { return mesiteurl + path; };

	this.esc = function (s) {
		return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) {
			return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
		});
	};

	//TOAST - tone: 'success' | 'error' | '' (plain)
	this.toast = function (message, tone) {
		let box = document.querySelector('.drf-toasts');
		if (!box) {
			box = document.createElement('div');
			box.className = 'drf-toasts';
			box.setAttribute('role', 'status');
			box.setAttribute('aria-live', 'polite');
			document.body.appendChild(box);
		}
		const icon = { success: 'ti-circle-check', error: 'ti-alert-circle' }[tone] || 'ti-info-circle';
		const el = document.createElement('div');
		el.className = 'drf-toast' + (tone ? ' is-' + tone : '');
		el.innerHTML = '<i class="ti ' + icon + '"></i><span>' + self.esc(message) + '</span>';
		box.appendChild(el);
		setTimeout(function () { el.remove(); }, tone === 'error' ? 6000 : 3500);
	};

	//CONFIRM - ask before an action that matters, in a modal (not the browser's own box).
	//   app.confirm({ title, text, note, yes, tone }).then(function (ok) { if (ok) { ... } });
	// note = an extra warning line; yes = the confirm button's label;
	// tone = 'danger' for something that removes work, 'warn' to draw attention.
	this.confirm = function (ask) {
		const modalEl = document.getElementById('drfConfirmModal');
		// a page without the modal (log in, register) still gets an answer
		if (!modalEl || !window.bootstrap) { return Promise.resolve(window.confirm(ask.title + '\n\n' + (ask.text || ''))); }

		const yes = document.getElementById('drfConfirmYes');
		const note = document.getElementById('drfConfirmNote');
		const tone = ask.tone || '';
		document.getElementById('drfConfirmTitle').textContent = ask.title;
		document.getElementById('drfConfirmText').textContent = ask.text || '';
		note.textContent = ask.note || '';
		note.hidden = !ask.note;
		yes.textContent = ask.yes || 'Continue';
		yes.className = 'btn px-4 py-2 ' + (tone === 'danger' ? 'btn-danger' : 'btn-drf');
		document.getElementById('drfConfirmIcon').className = 'drf-confirm-icon' + (tone ? ' is-' + tone : '');
		document.getElementById('drfConfirmIcon').innerHTML = '<i class="ti ' + (tone ? 'ti-alert-triangle' : 'ti-help') + '"></i>';

		return new Promise(function (resolve) {
			const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
			let answer = false;
			function onYes() { answer = true; modal.hide(); }
			function onShown() { yes.focus(); }
			function onHidden() {
				yes.removeEventListener('click', onYes);
				modalEl.removeEventListener('shown.bs.modal', onShown);
				modalEl.removeEventListener('hidden.bs.modal', onHidden);
				resolve(answer);
			}
			yes.addEventListener('click', onYes);
			modalEl.addEventListener('shown.bs.modal', onShown);
			modalEl.addEventListener('hidden.bs.modal', onHidden);
			modal.show();
		});
	};

	// The server answers JSON; {error: "..."} (or anything else) rejects with a message to show
	function answer(res) {
		const type = res.headers.get('content-type') || '';
		if (type.indexOf('application/json') === -1) {
			// sent to the log in page, or the form token is no longer valid
			if (res.redirected || res.status === 401 || res.status === 403) {
				throw new Error('Your session has ended. Reload the page and sign in again.');
			}
			throw new Error(res.status === 413 ? 'Files are too big to send. Try fewer or smaller files.' : 'Something went wrong. Please try again.');
		}
		return res.json().then(function (data) {
			if (!res.ok || (data && data.error)) {
				throw new Error((data && data.error) || 'Something went wrong. Please try again.');
			}
			return data;
		});
	}

	this.get = function (path) {
		return fetch(self.url(path), { credentials: 'same-origin', headers: { 'X-Requested-With': 'XMLHttpRequest' } }).then(answer);
	};

	// data: FormData
	this.post = function (path, data) {
		return fetch(self.url(path), {
			method: 'POST',
			body: data,
			credentials: 'same-origin',
			headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': csrf }
		}).then(answer);
	};

	// data: FormData; resolves with the file the server sends back (a Blob).
	// A JSON answer means it could not: rejects with its message.
	this.postFile = function (path, data, signal) {
		return fetch(self.url(path), {
			method: 'POST',
			body: data,
			credentials: 'same-origin',
			signal: signal,
			headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': csrf }
		}).then(function (res) {
			const type = res.headers.get('content-type') || '';
			if (res.ok && type.indexOf('application/json') === -1 && type.indexOf('text/html') === -1) { return res.blob(); }
			return answer(res).then(function () { throw new Error('Something went wrong. Please try again.'); });
		});
	};

	// Disable a button and show a spinner while a request runs; returns the undo
	this.busy = function (btn, label) {
		const html = btn.innerHTML;
		btn.disabled = true;
		btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>' + self.esc(label);
		return function () { btn.disabled = false; btn.innerHTML = html; };
	};

	//SECTIONS - <select data-drf-sections="#division"> only offers the chosen division's sections
	this.__section_selects = function () {
		document.querySelectorAll('select[data-drf-sections]').forEach(function (section) {
			const division = document.querySelector(section.getAttribute('data-drf-sections'));
			const placeholder = section.options[0];
			const all = Array.prototype.slice.call(section.options, 1);

			function fill(keep) {
				section.innerHTML = '';
				section.appendChild(placeholder);
				all.forEach(function (op) {
					if (op.getAttribute('data-division') === division.value) { section.appendChild(op); }
				});
				section.value = keep;
				if (section.selectedIndex < 0) { section.value = ''; }
			}

			const chosen = section.options[section.selectedIndex];
			fill(chosen && chosen.getAttribute('data-division') === division.value ? section.value : '');
			division.addEventListener('change', function () { fill(''); });
		});
	};

	//PASSWORD - <button data-drf-password="#field"> shows or hides what was typed
	this.__password_toggle = function () {
		document.querySelectorAll('[data-drf-password]').forEach(function (btn) {
			const field = document.querySelector(btn.getAttribute('data-drf-password'));
			btn.addEventListener('click', function () {
				const show = field.type === 'password';
				field.type = show ? 'text' : 'password';
				btn.setAttribute('aria-pressed', show ? 'true' : 'false');
				btn.setAttribute('aria-label', show ? 'Hide password' : 'Show password');
				btn.querySelector('i').className = 'ti ' + (show ? 'ti-eye-off' : 'ti-eye');
				field.focus();
			});
		});
	};

	//PRINT - <a data-drf-print="SOR-2026-0001" href="...PRINT..."> shows the PDF in a
	// modal on the page. Without JavaScript the link still opens the PDF itself.
	this.__print_modal = function () {
		const modalEl = document.getElementById('drfPrintModal');
		if (!modalEl) { return; }
		const frame = document.getElementById('drfPrintFrame');
		const noPreview = document.getElementById('drfPrintNoPreview');
		const download = document.getElementById('drfPrintDownload');
		const printBtn = document.getElementById('drfPrintNow');
		// phones often have no built-in PDF viewer to show inside a page
		const canPreview = navigator.pdfViewerEnabled !== false;
		let href = '';

		document.addEventListener('click', function (e) {
			const link = e.target.closest('a[data-drf-print]');
			// Ctrl/Shift/middle click keep their usual meaning (open elsewhere)
			if (!link || e.button !== 0 || e.ctrlKey || e.metaKey || e.shiftKey) { return; }
			e.preventDefault();

			href = link.href;
			const name = link.getAttribute('data-drf-print');
			// a Special Order downloads as its Word file; the request form as the PDF shown
			const other = link.getAttribute('data-drf-download');
			download.href = other || href;
			download.innerHTML = '<i class="ti ti-download me-1"></i> ' + (other ? 'Download Word' : 'Download PDF');
			if (other) { download.removeAttribute('download'); } else { download.setAttribute('download', (name || 'request-form') + '.pdf'); }
			document.getElementById('drfPrintTitle').textContent = (other ? 'Special Order' : 'Print Form') + (name ? ' · ' + name : '');

			// (an HTML document shows on any device; only a PDF needs the built-in viewer)
			const show = canPreview || !!other;
			frame.hidden = !show;
			noPreview.hidden = show;
			printBtn.hidden = !show;
			if (show) { frame.src = other ? href : href + '#view=FitH'; }
			bootstrap.Modal.getOrCreateInstance(modalEl).show();
		});

		printBtn.addEventListener('click', function () {
			try {
				frame.contentWindow.focus();
				frame.contentWindow.print();
			} catch (err) {
				// a viewer that cannot be printed from here: hand the PDF to the browser
				window.open(href, '_blank', 'noopener');
			}
		});

		// drop the document when the modal closes, so the next one starts clean
		modalEl.addEventListener('hidden.bs.modal', function () { frame.src = 'about:blank'; });
	};

	//LISTS - [data-drf-list]: status pills + stat tiles ([data-drf-filter]) and the search box
	this.__list_filter = function () {
		const list = document.querySelector('[data-drf-list]');
		if (!list) { return; }
		const rows = Array.prototype.slice.call(list.querySelectorAll('tbody tr'));
		const search = list.querySelector('[data-drf-search]');
		const nomatch = list.querySelector('[data-drf-nomatch]');
		let status = 'all';

		function apply() {
			const term = search.value.trim().toLowerCase();
			let shown = 0;
			rows.forEach(function (tr) {
				const ok = (status === 'all' || tr.getAttribute('data-status') === status)
					&& (term === '' || tr.textContent.toLowerCase().indexOf(term) !== -1);
				tr.hidden = !ok;
				if (ok) { shown++; }
			});
			if (nomatch) { nomatch.hidden = shown > 0; }
		}

		document.querySelectorAll('[data-drf-filter]').forEach(function (btn) {
			btn.addEventListener('click', function () {
				status = btn.getAttribute('data-drf-filter');
				document.querySelectorAll('[data-drf-filter]').forEach(function (b) {
					const on = b.getAttribute('data-drf-filter') === status;
					b.classList.toggle(b.classList.contains('drf-stat') ? 'is-active' : 'active', on);
				});
				apply();
			});
		});
		search.addEventListener('input', apply);
	};
}

document.addEventListener('DOMContentLoaded', function () {
	__mysys_drf.__section_selects();
	__mysys_drf.__list_filter();
	__mysys_drf.__print_modal();
	__mysys_drf.__password_toggle();
});
