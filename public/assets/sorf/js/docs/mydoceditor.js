// HR document editor (docs/doc-editor.php): start a Special Order from a
// template, edit the page like a word processor, save, share it with the requester as a draft, or withdraw it.
var __mysys_doc_editor = new __mysys_doc_editor_ent();
function __mysys_doc_editor_ent() {
	const app = __mysys_drf;
	const cfg = window.__doc_editor || {};
	const body = document.getElementById('docBody');
	const state = document.getElementById('docState');

	let dirty = false;

	function setDirty(on) {
		dirty = on;
		if (state) {
			state.textContent = on ? 'Unsaved changes' : 'Saved';
			state.classList.toggle('is-dirty', on);
		}
	}

	function send(meaction, extra) {
		const data = new FormData();
		data.append('meaction', meaction);
		data.append('recid', cfg.recid);
		Object.keys(extra || {}).forEach(function (key) { data.append(key, extra[key]); });
		return app.post('myhrdoc', data);
	}

	// the page is reloaded after an action; no "unsaved changes" prompt for that
	function reload() {
		setDirty(false);
		setTimeout(function () { window.location.reload(); }, 800);
	}

	// once shared, the work in the editor is done: back to the request page
	function backToRequest() {
		setDirty(false);
		setTimeout(function () { window.location.href = app.url('myrequest?meaction=VIEW&recid=' + cfg.recid); }, 800);
	}

	//TEMPLATE - start the document, or start over
	this.__doc_start = function () {
		document.querySelectorAll('[data-doc-start]').forEach(function (btn) {
			btn.addEventListener('click', function () {
				// starting over replaces what was typed, so that is asked first
				const again = btn.hasAttribute('data-doc-again');
				const sure = !again ? Promise.resolve(true) : app.confirm({
					title: 'Start over from the template?',
					text: 'Everything typed in this document is replaced by the template.',
					yes: 'Start Over',
					tone: 'danger'
				});

				sure.then(function (ok) {
					if (!ok) { return; }
					const done = app.busy(btn, 'Loading...');
					send('DOC-START', { template_code: document.querySelector(btn.getAttribute('data-doc-start')).value }).then(function (res) {
						app.toast(res.message, 'success');
						reload();
					}).catch(function (err) {
						done();
						app.toast(err.message, 'error');
					});
				});
			});
		});
	};

	//EDITING - toolbar, table rows, blanks
	this.__doc_editing = function () {
		if (!cfg.editable) { return; }
		const tools = document.getElementById('docTools');

		// the row the cursor is in (null when it is not in a table)
		function currentRow() {
			const sel = window.getSelection();
			if (!sel.rangeCount) { return null; }
			const node = sel.anchorNode;
			const el = node && (node.nodeType === 1 ? node : node.parentElement);
			const row = el ? el.closest('tr') : null;
			return row && body.contains(row) ? row : null;
		}

		function emptyRowLike(row) {
			const fresh = row.cloneNode(true);
			Array.prototype.forEach.call(fresh.cells, function (cell) { cell.innerHTML = '<br>'; });
			return fresh;
		}

		// buttons must not take the cursor out of the page
		tools.addEventListener('mousedown', function (e) { if (e.target.closest('button')) { e.preventDefault(); } });

		tools.addEventListener('click', function (e) {
			const btn = e.target.closest('button');
			if (!btn) { return; }
			body.focus();

			const cmd = btn.getAttribute('data-cmd');
			if (cmd) {
				document.execCommand('styleWithCSS', false, true);
				document.execCommand(cmd, false, null);
				setDirty(true);
				return;
			}

			const row = currentRow();
			if (!row) {
				app.toast('Click inside a table row first.', 'error');
				return;
			}
			const action = btn.getAttribute('data-row');
			if (action === 'delete') {
				if (row.parentElement.rows.length <= 1) {
					app.toast('A table keeps at least one row.', 'error');
					return;
				}
				row.remove();
			} else {
				const fresh = emptyRowLike(row);
				row.parentElement.insertBefore(fresh, action === 'above' ? row : row.nextSibling);
				// put the cursor in the new row
				const range = document.createRange();
				range.selectNodeContents(fresh.cells[0]);
				range.collapse(true);
				const sel = window.getSelection();
				sel.removeAllRanges();
				sel.addRange(range);
			}
			setDirty(true);
		});

		// a blank ([yellow mark]) is selected whole on click, ready to type over
		body.addEventListener('click', function (e) {
			const blank = e.target.closest('.doc-fill');
			if (!blank || !/^\[.*\]$/.test(blank.textContent.trim())) { return; }
			const range = document.createRange();
			range.selectNodeContents(blank);
			const sel = window.getSelection();
			sel.removeAllRanges();
			sel.addRange(range);
		});

		body.addEventListener('input', function () {
			setDirty(true);
			// a blank that has been filled in is ordinary text again
			body.querySelectorAll('.doc-fill').forEach(function (blank) {
				if (!/^\[.*\]$/.test(blank.textContent.trim())) { blank.classList.remove('doc-fill'); }
			});
		});

		// pasted text comes in plain, so Word/web formatting cannot break the form
		body.addEventListener('paste', function (e) {
			e.preventDefault();
			const text = (e.clipboardData || window.clipboardData).getData('text/plain');
			document.execCommand('insertText', false, text);
		});

		// Ctrl+S saves
		document.addEventListener('keydown', function (e) {
			if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 's') {
				e.preventDefault();
				document.getElementById('docSave').click();
			}
		});

		window.addEventListener('beforeunload', function (e) {
			if (dirty) { e.preventDefault(); e.returnValue = ''; }
		});
	};

	//SAVE / RELEASE / WITHDRAW
	this.__doc_saving = function () {
		const save = document.getElementById('docSave');
		const release = document.getElementById('docRelease');
		const withdraw = document.getElementById('docWithdraw');
		const preview = document.querySelector('[data-doc-preview]');

		function saveDraft(btn, label) {
			const done = app.busy(btn, label);
			return send('DOC-SAVE', { doc_html: body.innerHTML }).then(function (res) {
				setDirty(false);
				return res;
			}).finally(done);
		}

		if (save) {
			save.addEventListener('click', function () {
				saveDraft(save, 'Saving...').then(function (res) {
					app.toast(res.message, 'success');
				}).catch(function (err) { app.toast(err.message, 'error'); });
			});
		}

		// the preview shows what is saved, so unsaved typing is saved first
		if (preview && cfg.editable) {
			preview.addEventListener('click', function (e) {
				if (!dirty) { return; }
				e.preventDefault();
				e.stopPropagation();
				saveDraft(preview, 'Saving...').then(function () {
					preview.click();
				}).catch(function (err) { app.toast(err.message, 'error'); });
			}, true);
		}

		if (release) {
			release.addEventListener('click', function () {
				const blanks = body.querySelectorAll('.doc-fill').length;
				app.confirm({
					title: 'Share this draft with the requester?',
					text: 'They can view, print and download it. Every copy is marked DRAFT: it is not the final Special Order.',
					note: blanks ? blanks + (blanks === 1 ? ' blank is' : ' blanks are') + ' still not filled in (marked yellow).' : '',
					yes: 'Share Draft',
					tone: blanks ? 'warn' : ''
				}).then(function (ok) {
					if (!ok) { return; }
					const done = app.busy(release, 'Sharing...');
					send('DOC-RELEASE', { doc_html: body.innerHTML }).then(function (res) {
						app.toast(res.message, 'success');
						backToRequest();
					}).catch(function (err) {
						done();
						app.toast(err.message, 'error');
					});
				});
			});
		}

		if (withdraw) {
			withdraw.addEventListener('click', function () {
				app.confirm({
					title: 'Withdraw this draft to edit it?',
					text: 'The requester cannot open it until you share it again.',
					yes: 'Withdraw to Edit',
					tone: 'warn'
				}).then(function (ok) {
					if (!ok) { return; }
					const done = app.busy(withdraw, 'Withdrawing...');
					send('DOC-WITHDRAW').then(function (res) {
						app.toast(res.message, 'success');
						reload();
					}).catch(function (err) {
						done();
						app.toast(err.message, 'error');
					});
				});
			});
		}
	};
}

document.addEventListener('DOMContentLoaded', function () {
	__mysys_doc_editor.__doc_start();
	__mysys_doc_editor.__doc_editing();
	__mysys_doc_editor.__doc_saving();
});
