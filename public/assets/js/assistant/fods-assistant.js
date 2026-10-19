// FODS Assistant chat, shared by the full page (myassistant) and the floating
// panel every page gets from templates/myfooter.php:
//   var chat = window.FodsAssistant.mount(element, { url, me, firstName, areas, configured });
//   chat.newChat(); chat.focus(); chat.reload();
// The conversation is kept per person in this browser tab (sessionStorage),
// so the panel and the full page continue the same chat. Answers are escaped
// before a small, safe subset of markdown is applied.
(function () {
	var KEEP = 12;              // earlier messages sent with each question
	var SITE = '';              // FODS address the answer's links are relative to (set by mount)
	// an answer may only link inside FODS: a route, optionally with a plain query string
	var SAFE_LINK = /^[a-z][a-z0-9_-]*(\?[A-Za-z0-9_=&%.,-]*)?$/;
	var TIMEOUT_MS = 120000;
	var SUGGESTIONS = [
		"What's waiting for my approval?",
		'How much have we obligated this year?',
		'Which approved DVs are not paid yet?',
		'Show the latest purchase orders'
	];

	function esc(s) {
		return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) {
			return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
		});
	}

	//FORMATTING: escape everything first, then headings, bold, code, lists, tables
	// [label](link) -> a link, only for FODS routes (t is already HTML-escaped)
	function links(t) {
		return t.replace(/\[([^\]]+)\]\(([^)\s]+)\)/g, function (all, label, href) {
			var raw = href.replace(/&amp;/g, '&');
			if (!SAFE_LINK.test(raw)) { return label; }
			return '<a class="as-link" href="' + esc(SITE + raw) + '">' + label + '</a>';
		});
	}

	function inline(t) {
		return links(t)
			.replace(/`([^`]+)`/g, '<code>$1</code>')
			.replace(/\*\*([^*]+)\*\*/g, '<strong>$1</strong>')
			.replace(/(^|[^*])\*([^*\s][^*]*)\*/g, '$1<em>$2</em>');
	}

	function isNum(cell) { return /^[\s₱\-+(]*[\d,]+(\.\d+)?%?\)?\s*$/.test(cell); }

	function tableHtml(lines) {
		var rows = lines.map(function (l) { return l.replace(/^\s*\|/, '').replace(/\|\s*$/, '').split('|').map(function (c) { return c.trim(); }); });
		var head = rows[0];
		var body = rows.slice(1).filter(function (r) { return !r.every(function (c) { return /^:?-{2,}:?$/.test(c); }); });
		var numCol = head.map(function (h, i) { return body.length > 0 && body.every(function (r) { return !r[i] || isNum(r[i]); }); });
		return '<div class="as-table"><table><thead><tr>' + head.map(function (h, i) { return '<th' + (numCol[i] ? ' class="num"' : '') + '>' + inline(h) + '</th>'; }).join('') + '</tr></thead><tbody>'
			+ body.map(function (r) { return '<tr>' + head.map(function (h, i) { return '<td' + (numCol[i] ? ' class="num"' : '') + '>' + inline(r[i] || '') + '</td>'; }).join('') + '</tr>'; }).join('')
			+ '</tbody></table></div>';
	}

	function markdown(src) {
		var lines = esc(src).replace(/\r\n/g, '\n').split('\n');
		var html = '', para = [], i = 0;
		function flush() { if (para.length) { html += '<p>' + inline(para.join('<br>')) + '</p>'; para = []; } }
		while (i < lines.length) {
			var line = lines[i];
			if (/^\s*```/.test(line)) {
				flush();
				var code = [];
				i++;
				while (i < lines.length && !/^\s*```/.test(lines[i])) { code.push(lines[i]); i++; }
				html += '<pre><code>' + code.join('\n') + '</code></pre>';
				i++;
				continue;
			}
			if (/^\s*\|.*\|\s*$/.test(line)) {
				flush();
				var tbl = [];
				while (i < lines.length && /^\s*\|.*\|\s*$/.test(lines[i])) { tbl.push(lines[i]); i++; }
				html += tableHtml(tbl);
				continue;
			}
			if (/^\s*([-*•])\s+/.test(line) || /^\s*\d+[.)]\s+/.test(line)) {
				flush();
				var ordered = /^\s*\d+[.)]\s+/.test(line);
				var marker = ordered ? /^\s*\d+[.)]\s+/ : /^\s*([-*•])\s+/;
				var items = [];
				while (i < lines.length && marker.test(lines[i])) { items.push(lines[i].replace(marker, '')); i++; }
				html += (ordered ? '<ol>' : '<ul>') + items.map(function (t) { return '<li>' + inline(t) + '</li>'; }).join('') + (ordered ? '</ol>' : '</ul>');
				continue;
			}
			var h = /^\s*#{1,6}\s+(.*)$/.exec(line);
			if (h) { flush(); html += '<h6>' + inline(h[1]) + '</h6>'; i++; continue; }
			if (/^\s*$/.test(line)) { flush(); i++; continue; }
			para.push(line);
			i++;
		}
		flush();
		return html;
	}

	function mount(root, opts) {
		opts = opts || {};
		if (opts.site) { SITE = String(opts.site).replace(/\/?$/, '/'); }
		var storeKey = 'fods.assistant.' + String(opts.me || '').toUpperCase();
		var history = [];        // [{ role: 'user'|'assistant', content, sources?, error? }]
		var busy = false;
		var first = String(opts.firstName || '').trim();
		var areas = (opts.areas || []).join(', ');

		root.classList.add('as-chat');
		root.innerHTML = '<div class="as-log"><div class="as-inner">'
			+ '<div class="as-welcome"><div class="fods-stat-icon tone-teal"><i class="ti ti-message-chatbot"></i></div>'
			+ '<h2>Hi' + (first ? ' ' + esc(first) : '') + ', what would you like to know?</h2>'
			+ (areas ? '<div class="small">You can look up: ' + esc(areas) + '.</div>' : '')
			+ '<div class="as-suggest">' + SUGGESTIONS.map(function (s) { return '<button type="button">' + esc(s) + '</button>'; }).join('') + '</div></div>'
			+ '</div></div>'
			+ '<form class="as-compose" autocomplete="off"><div class="as-compose-inner">'
			+ '<textarea class="form-control" rows="1" maxlength="2000" placeholder="Ask about FODS…" aria-label="Ask the FODS Assistant"' + (opts.configured === false ? ' disabled' : '') + '></textarea>'
			+ '<button type="submit" class="btn btn-fods px-3" style="height:40px;" aria-label="Send"' + (opts.configured === false ? ' disabled' : '') + '><i class="ti ti-send"></i></button>'
			+ '</div><div class="as-note">' + (opts.configured === false ? 'Not set up yet (no API key). Ask your administrator.' : 'AI answers can be wrong — check important figures on the record itself. Questions are logged.') + '</div></form>';

		var log = root.querySelector('.as-log');
		var inner = root.querySelector('.as-inner');
		var welcome = root.querySelector('.as-welcome');
		var form = root.querySelector('.as-compose');
		var text = form.querySelector('textarea');
		var send = form.querySelector('button[type=submit]');

		function load() {
			try { history = JSON.parse(sessionStorage.getItem(storeKey) || '[]') || []; } catch (e) { history = []; }
		}
		function save() {
			try { sessionStorage.setItem(storeKey, JSON.stringify(history.slice(-40))); } catch (e) { /* storage blocked */ }
		}

		function rowHtml(m) {
			if (m.role === 'user') {
				return '<div class="as-row mine"><div class="as-bubble">' + esc(m.content) + '</div></div>';
			}
			var sources = (m.sources || []).length
				? '<div class="as-sources"><i class="ti ti-database"></i> Looked up: ' + m.sources.map(function (s) { return '<span class="fods-chip tone-slate">' + esc(s) + '</span>'; }).join('') + '</div>'
				: '';
			return '<div class="as-row"><div class="as-avatar"><i class="ti ti-message-chatbot"></i></div><div style="min-width:0">'
				+ '<div class="as-bubble' + (m.error ? ' as-error' : '') + '">' + (m.error ? esc(m.content) : markdown(m.content)) + '</div>' + sources + '</div></div>';
		}

		function render() {
			inner.querySelectorAll('.as-row').forEach(function (el) { el.remove(); });
			welcome.classList.toggle('d-none', history.length > 0);
			inner.insertAdjacentHTML('beforeend', history.map(rowHtml).join(''));
			if (busy) { showThinking(); }
			log.scrollTop = log.scrollHeight;
		}

		function showThinking() {
			if (inner.querySelector('.as-thinking-row')) { return; }
			inner.insertAdjacentHTML('beforeend', '<div class="as-row as-thinking-row"><div class="as-avatar"><i class="ti ti-message-chatbot"></i></div><div class="as-bubble"><span class="as-thinking"><i></i><i></i><i></i></span> <span class="small text-muted">Looking it up…</span></div></div>');
			log.scrollTop = log.scrollHeight;
		}

		function setBusy(on) {
			busy = on;
			send.disabled = on || opts.configured === false;
			if (on) { showThinking(); } else { var t = inner.querySelector('.as-thinking-row'); if (t) { t.remove(); } }
		}

		function ask(question) {
			question = String(question || '').trim();
			if (!question || busy || opts.configured === false) { return; }
			load();   // the other view (panel / page) may have added to the chat
			var earlier = history.filter(function (m) { return !m.error; }).slice(-KEEP).map(function (m) { return { role: m.role, content: m.content }; });
			history.push({ role: 'user', content: question });
			save();
			render();
			setBusy(true);

			var ctrl = window.AbortController ? new AbortController() : null;
			var timer = ctrl ? setTimeout(function () { ctrl.abort(); }, TIMEOUT_MS) : null;
			var body = new URLSearchParams({ meaction: 'ASK', question: question, history: JSON.stringify(earlier) });
			fetch(opts.url, { method: 'POST', body: body, credentials: 'same-origin', headers: { 'X-Requested-With': 'XMLHttpRequest' }, signal: ctrl ? ctrl.signal : undefined })
				.then(function (r) { return r.json().catch(function () { return { error: 'The assistant could not be reached. Try again.' }; }); })
				.then(function (data) {
					history.push(data && data.ok
						? { role: 'assistant', content: data.answer, sources: data.sources || [] }
						: { role: 'assistant', content: (data && data.error) || 'No answer came back. Try again.', error: true });
				})
				.catch(function (e) {
					history.push({ role: 'assistant', content: e && e.name === 'AbortError' ? 'That took too long. Try a narrower question.' : 'The assistant could not be reached. Try again.', error: true });
				})
				.then(function () {
					if (timer) { clearTimeout(timer); }
					save();
					setBusy(false);
					render();
					text.focus();
				});
		}

		text.addEventListener('input', function () { text.style.height = 'auto'; text.style.height = Math.min(text.scrollHeight, 160) + 'px'; });
		text.addEventListener('keydown', function (e) {
			if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); form.requestSubmit ? form.requestSubmit() : form.dispatchEvent(new Event('submit', { cancelable: true })); }
		});
		form.addEventListener('submit', function (e) {
			e.preventDefault();
			var q = text.value;
			if (!q.trim() || busy) { return; }
			text.value = ''; text.style.height = 'auto';
			ask(q);
		});
		root.querySelector('.as-suggest').addEventListener('click', function (e) {
			var b = e.target.closest('button');
			if (b) { ask(b.textContent); }
		});

		load();
		render();

		return {
			newChat: function () { if (busy) { return; } history = []; save(); render(); text.focus(); },
			focus: function () { if (!text.disabled) { text.focus(); } },
			reload: function () { if (!busy) { load(); render(); } }
		};
	}

	window.FodsAssistant = { mount: mount };
})();
