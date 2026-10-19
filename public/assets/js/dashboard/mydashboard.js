// Dashboard: monthly chart, payee table and the per-payee flow panel.
// Runs on DOMContentLoaded because jQuery/DataTables/Bootstrap load in the footer.
var __mysys_dashboard_ent = new __mysys_dashboard_ent();
function __mysys_dashboard_ent() {
	var mesiteurl = document.getElementById('__siteurl').getAttribute('data-mesiteurl');

	var COLORS = { obl: '#0f6f6f', dv: '#4f46e5', paid: '#d97706' };

	function esc(s) {
		return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) {
			return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
		});
	}

	function money(n) {
		return '₱' + Number(n || 0).toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
	}

	function moneyShort(n) {
		n = Number(n || 0);
		var a = Math.abs(n);
		if (a >= 1e9) { return '₱' + (n / 1e9).toFixed(2) + 'B'; }
		if (a >= 1e6) { return '₱' + (n / 1e6).toFixed(1) + 'M'; }
		if (a >= 1e3) { return '₱' + (n / 1e3).toFixed(1) + 'K'; }
		return '₱' + n.toFixed(0);
	}

	function fmtDate(d) {
		if (!d || String(d).indexOf('0000') === 0) { return '—'; }
		var dt = new Date(String(d).replace(' ', 'T'));
		return isNaN(dt) ? d : dt.toLocaleDateString('en-US', { month: 'short', day: '2-digit', year: 'numeric' });
	}

	function initials(name) {
		var base = String(name || '').split(',')[0].trim();
		var t = base.split(/\s+/).filter(function (x) { return x && x.slice(-1) !== '.'; });
		if (!t.length) { return base.charAt(0).toUpperCase(); }
		return (t[0].charAt(0) + (t.length > 1 ? t[t.length - 1].charAt(0) : '')).toUpperCase();
	}

	//CHART - obligations (bars, left axis) vs disbursements/payments (lines, right axis)
	this.__chart = function (monthly) {
		var el = document.getElementById('monthlyChart');
		if (!el || typeof ApexCharts === 'undefined') { return; }

		new ApexCharts(el, {
			chart: { type: 'line', height: 320, toolbar: { show: false }, fontFamily: 'inherit', zoom: { enabled: false } },
			series: [
				{ name: 'Obligated', type: 'column', data: monthly.map(function (m) { return m.obl; }) },
				{ name: 'Disbursed', type: 'line', data: monthly.map(function (m) { return m.dv; }) },
				{ name: 'Paid', type: 'line', data: monthly.map(function (m) { return m.paid; }) }
			],
			colors: [COLORS.obl, COLORS.dv, COLORS.paid],
			stroke: { width: [0, 3, 3], curve: 'smooth' },
			plotOptions: { bar: { columnWidth: '45%', borderRadius: 4 } },
			fill: { opacity: [0.85, 1, 1] },
			markers: { size: [0, 4, 4], strokeWidth: 0 },
			dataLabels: { enabled: false },
			labels: monthly.map(function (m) { return m.label; }),
			xaxis: { labels: { rotate: -45, style: { colors: '#64748b', fontSize: '11px' } }, axisBorder: { show: false }, axisTicks: { show: false } },
			yaxis: [
				{ seriesName: 'Obligated', title: { text: 'Obligated', style: { color: COLORS.obl, fontWeight: 500 } }, labels: { formatter: moneyShort, style: { colors: '#64748b' } } },
				{ seriesName: 'Disbursed', opposite: true, title: { text: 'Disbursed / Paid', style: { color: COLORS.dv, fontWeight: 500 } }, labels: { formatter: moneyShort, style: { colors: '#64748b' } } },
				{ seriesName: 'Disbursed', show: false, labels: { formatter: moneyShort } }
			],
			grid: { borderColor: '#eef1f5', strokeDashArray: 4 },
			legend: { position: 'top', horizontalAlign: 'left', markers: { radius: 12 } },
			tooltip: { shared: true, intersect: false, y: { formatter: money } }
		}).render();
	};

	//PAYEE TABLE - search box, filter pills, row click -> flow panel
	this.__payee_table = function () {
		var $ = window.jQuery;
		var filter = 'all';

		$.fn.dataTable.ext.search.push(function (settings, data, dataIndex) {
			if (settings.nTable.id !== 'payeeTable' || filter === 'all') { return true; }
			var tr = settings.aoData[dataIndex].nTr;
			return Number(tr.getAttribute('data-' + filter)) > 0;
		});

		var dt = $('#payeeTable').DataTable({
			pageLength: 10,
			lengthChange: false,
			autoWidth: false,
			order: [[5, 'desc']],
			dom: 't<"d-flex flex-wrap justify-content-between align-items-center"ip>',
			language: {
				info: 'Showing _START_–_END_ of _TOTAL_',
				infoEmpty: 'Nothing to show',
				infoFiltered: '',
				zeroRecords: '<div class="text-center text-muted py-4"><i class="ti ti-search fs-6 d-block mb-1"></i>No payees match</div>',
				paginate: { previous: '<i class="ti ti-chevron-left"></i>', next: '<i class="ti ti-chevron-right"></i>' }
			}
		});

		$('#payeeSearch').on('input', function () { dt.search(this.value).draw(); });
		$(document).on('click', '#payeeFilter button', function () {
			filter = $(this).data('filter');
			$('#payeeFilter button').removeClass('active');
			$(this).addClass('active');
			dt.draw();
		});

		$('#payeeTable tbody').on('click', 'tr[data-payee]', function () {
			__mysys_dashboard_ent.__open_payee(this.getAttribute('data-payee'));
		});
	};

	//PAYEE FLOW PANEL
	var panelData = [];
	var panelFilter = 'all';
	var PAGE = 40;
	var shown = PAGE;

	var STAGE_CHIP = {
		1: ['tone-slate', 'Awaiting DV'],
		2: ['tone-indigo', 'Disbursed'],
		3: ['tone-amber', 'Paid'],
		4: ['tone-green', 'In RCI']
	};

	function linkBox(tone, label, lines) {
		return '<div class="db-link ' + tone + '"><div class="lbl">' + label + '</div>' + lines + '</div>';
	}

	function emptyBox(label, text) {
		return '<div class="db-link empty"><div class="lbl">' + label + '</div>' + text + '</div>';
	}

	function renderObligation(o) {
		var chip = STAGE_CHIP[o.stage] || STAGE_CHIP[1];
		var html = '<div class="db-flow">'
			+ '<div class="db-flow-head"><div style="min-width:0">'
			+ '<div class="db-flow-ref">' + esc(o.src) + ' ' + esc(o.serialno) + '</div>'
			+ '<div class="db-flow-sub">' + fmtDate(o.obl_date) + ' · ' + money(o.amount) + '</div>'
			+ '</div><div><span class="fods-chip ' + chip[0] + '">' + chip[1] + '</span></div></div>'
			+ '<div class="db-flow-body">';

		if (!o.dvs.length) {
			html += '<div class="db-waiting"><i class="ti ti-hourglass"></i>Waiting for a disbursement voucher</div>';
		}

		o.dvs.forEach(function (dv) {
			html += '<div class="db-chain">';
			html += linkBox('tone-indigo', 'Disbursement', '<div class="no">DV ' + esc(dv.dvno) + '</div>' + fmtDate(dv.disbursement_date) + '<br>' + money(dv.net_amount));

			if (dv.payments.length) {
				html += linkBox('tone-amber', 'Payment', dv.payments.map(function (p) {
					return '<div class="no">' + esc(p.channel) + ' ' + esc(p.ref_no) + '</div>' + fmtDate(p.pay_date) + ' · ' + money(p.amount);
				}).join('<hr class="my-1 opacity-25">'));
			} else {
				html += emptyBox('Payment', 'Awaiting LDDAP-ADA / ADA');
			}

			var onlyAda = dv.payments.length && dv.payments.every(function (p) { return p.channel === 'ADA'; });
			if (dv.rci.length) {
				html += linkBox('tone-green', 'RCI', dv.rci.map(function (r) {
					return '<div class="no">Report ' + esc(r.reportno) + '</div>' + fmtDate(r.rci_date);
				}).join('<hr class="my-1 opacity-25">'));
			} else if (onlyAda) {
				html += emptyBox('RCI', 'Not applicable (ADA)');
			} else {
				html += emptyBox('RCI', dv.payments.length ? 'Awaiting RCI' : '—');
			}
			html += '</div>';
		});

		return html + '</div></div>';
	}

	function renderPanel() {
		var list = panelData.filter(function (o) {
			if (panelFilter === 'moving') { return o.stage > 1; }
			if (panelFilter === 'waiting') { return o.stage === 1; }
			return true;
		});
		var body = document.getElementById('payeePanelBody');
		if (!list.length) {
			body.innerHTML = '<div class="text-center text-muted py-5"><i class="ti ti-inbox fs-6 d-block mb-1"></i>Nothing here</div>';
			return;
		}
		body.innerHTML = list.slice(0, shown).map(renderObligation).join('')
			+ (list.length > shown
				? '<button type="button" class="btn btn-sm btn-fods-ghost w-100" id="flowMore">Show more (' + (list.length - shown) + ' left)</button>'
				: '');
	}

	this.__open_payee = function (payee) {
		var panelEl = document.getElementById('payeePanel');
		document.getElementById('payeePanelTitle').textContent = payee;
		document.getElementById('payeePanelAvatar').textContent = initials(payee);
		document.getElementById('payeePanelSub').textContent = 'Loading…';
		document.getElementById('payeePanelBody').innerHTML = '<div class="text-center text-muted py-5"><span class="spinner-border spinner-border-sm me-2"></span>Loading transactions…</div>';
		panelFilter = 'all';
		shown = PAGE;
		document.querySelectorAll('#flowFilter button').forEach(function (b) { b.classList.toggle('active', b.getAttribute('data-filter') === 'all'); });
		bootstrap.Offcanvas.getOrCreateInstance(panelEl).show();

		fetch(mesiteurl + 'mydashboard?meaction=PAYEE-FLOW&payee=' + encodeURIComponent(payee), { credentials: 'same-origin' })
			.then(function (r) { return r.json(); })
			.then(function (res) {
				panelData = res.obligations || [];
				var total = panelData.reduce(function (s, o) { return s + Number(o.amount || 0); }, 0);
				var moved = panelData.filter(function (o) { return o.stage > 1; }).length;
				document.getElementById('payeePanelSub').textContent = panelData.length + ' obligation' + (panelData.length === 1 ? '' : 's')
					+ ' · ' + money(total) + ' · ' + moved + ' past obligation';
				renderPanel();
			})
			.catch(function () {
				document.getElementById('payeePanelSub').textContent = '';
				document.getElementById('payeePanelBody').innerHTML = '<div class="text-center text-danger py-5">Could not load this payee\'s transactions.</div>';
			});
	};

	this.__panel_events = function () {
		document.getElementById('flowFilter').addEventListener('click', function (e) {
			var btn = e.target.closest('button[data-filter]');
			if (!btn) { return; }
			panelFilter = btn.getAttribute('data-filter');
			shown = PAGE;
			this.querySelectorAll('button').forEach(function (b) { b.classList.toggle('active', b === btn); });
			renderPanel();
		});
		document.getElementById('payeePanelBody').addEventListener('click', function (e) {
			if (e.target.id === 'flowMore') { shown += PAGE; renderPanel(); }
		});
	};
}

// Find a record: searches ORS, BURS, DV, LDDAP-ADA and RCI (only the ones this
// user can open) as you type; each result opens that record on its own page.
__mysys_dashboard_ent.__find = function () {
	var input = document.getElementById('findText');
	if (!input) { return; }
	var mesiteurl = document.getElementById('__siteurl').getAttribute('data-mesiteurl');
	var box = document.getElementById('findResults');
	var pills = document.getElementById('findType');
	var TYPES = {
		ors: ['ORS', 'tone-teal', 'ti-file-invoice'],
		burs: ['BURS', 'tone-teal', 'ti-receipt'],
		dv: ['DV', 'tone-indigo', 'ti-file-dollar'],
		lddapada: ['LDDAP-ADA', 'tone-amber', 'ti-building-bank'],
		rci: ['RCI', 'tone-green', 'ti-receipt-2']
	};
	var type = 'all', timer = null, ctrl = null;

	function esc(s) {
		return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) {
			return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
		});
	}
	function mark(text, q) {   // highlight the search text (escaped first)
		var t = esc(text), k = esc(q);
		if (!k) { return t; }
		var i = t.toLowerCase().indexOf(k.toLowerCase());
		return i < 0 ? t : t.slice(0, i) + '<mark>' + t.slice(i, i + k.length) + '</mark>' + t.slice(i + k.length);
	}
	function money(n) { return '₱' + Number(n || 0).toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 }); }
	function fmtDate(d) {
		if (!d || String(d).indexOf('0000') === 0) { return ''; }
		var dt = new Date(String(d).replace(' ', 'T'));
		return isNaN(dt) ? '' : dt.toLocaleDateString('en-PH', { month: 'short', day: 'numeric', year: 'numeric' });
	}

	function render(data) {
		var q = data.q, html = '', any = false;
		// a record whose own number is the search text comes first (Enter opens it)
		var exact = function (t) { return data.results[t].rows.some(function (r) { return String(r.ref).toLowerCase() === q.toLowerCase(); }); };
		Object.keys(data.results || {}).sort(function (a, b) { return exact(b) - exact(a); }).forEach(function (t) {
			var g = data.results[t], meta = TYPES[t];
			if (!g.rows.length) { return; }
			any = true;
			html += '<div class="db-find-group"><span>' + meta[0] + '</span>'
				+ (g.more && type === 'all' ? '<button type="button" data-more="' + t + '">More ' + meta[0] + ' results <i class="ti ti-arrow-right"></i></button>' : '')
				+ (g.more && type !== 'all' ? '<span class="text-lowercase fw-normal">first ' + g.rows.length + ' shown, type more to narrow it down</span>' : '')
				+ '</div>';
			html += g.rows.map(function (r) {
				return '<a class="db-find-row" href="' + esc(r.link) + '">'
					+ '<span class="ic ' + meta[1] + '"><i class="ti ' + meta[2] + '"></i></span>'
					+ '<span class="flex-grow-1" style="min-width:0">'
					+ '<span class="db-find-ref d-block">' + meta[0] + ' ' + mark(r.ref, q) + '</span>'
					+ '<span class="db-find-sub d-block">' + [r.payee, r.sub].filter(Boolean).map(function (x) { return mark(x, q); }).join(' · ') + '</span>'
					+ '</span>'
					+ '<span class="db-find-end">'
					+ '<span class="db-amt fw-semibold small d-block">' + money(r.amount) + '</span>'
					+ (r.status ? '<span class="fods-chip ' + r.status[0] + '"><span class="fods-dot"></span>' + esc(r.status[1]) + '</span>' : '<span class="small text-muted">' + fmtDate(r.date) + '</span>')
					+ '</span></a>';
			}).join('');
		});
		box.innerHTML = any ? html : '<div class="db-find-empty"><i class="ti ti-search-off me-1"></i>Nothing found for “' + esc(q) + '”'
			+ (type !== 'all' ? ' in ' + TYPES[type][0] + '. <a href="#" data-more="all">Search everything</a>' : '') + '.</div>';
		box.hidden = false;
	}

	function search() {
		var q = input.value.trim();
		if (ctrl) { ctrl.abort(); }
		if (q.length < 2) { box.hidden = true; box.innerHTML = ''; return; }
		ctrl = window.AbortController ? new AbortController() : null;
		box.hidden = false;
		if (!box.innerHTML) { box.innerHTML = '<div class="db-find-empty"><span class="spinner-border spinner-border-sm"></span></div>'; }
		fetch(mesiteurl + 'mydashboard?meaction=SEARCH&type=' + encodeURIComponent(type) + '&q=' + encodeURIComponent(q),
			{ credentials: 'same-origin', headers: { 'X-Requested-With': 'XMLHttpRequest' }, signal: ctrl ? ctrl.signal : undefined })
			.then(function (r) { if (!r.ok) { throw new Error(r.status); } return r.json(); })
			.then(function (data) { if (data.q === input.value.trim()) { render(data); } })
			.catch(function (e) { if (e.name !== 'AbortError') { box.innerHTML = '<div class="db-find-empty text-danger">The search failed. Try again.</div>'; } });
	}

	function setType(t) {
		type = t;
		pills.querySelectorAll('button').forEach(function (b) { b.classList.toggle('active', b.getAttribute('data-type') === t); });
		search();
	}

	input.addEventListener('input', function () { clearTimeout(timer); timer = setTimeout(search, 250); });
	input.addEventListener('keydown', function (e) {
		if (e.key === 'Enter') {   // Enter opens the first result
			e.preventDefault();
			var first = box.querySelector('.db-find-row');
			if (first) { location.href = first.href; } else { search(); }
		} else if (e.key === 'Escape') {
			input.value = ''; search();
		} else if (e.key === 'ArrowDown') {
			var row = box.querySelector('.db-find-row');
			if (row) { e.preventDefault(); row.focus(); }
		}
	});
	box.addEventListener('keydown', function (e) {   // arrow keys move through the results
		var rows = Array.prototype.slice.call(box.querySelectorAll('.db-find-row'));
		var i = rows.indexOf(document.activeElement);
		if (i < 0) { return; }
		if (e.key === 'ArrowDown' && rows[i + 1]) { e.preventDefault(); rows[i + 1].focus(); }
		if (e.key === 'ArrowUp') { e.preventDefault(); (rows[i - 1] || input).focus(); }
	});
	pills.addEventListener('click', function (e) {
		var b = e.target.closest('button[data-type]');
		if (b) { setType(b.getAttribute('data-type')); }
	});
	box.addEventListener('click', function (e) {
		var m = e.target.closest('[data-more]');
		if (m) { e.preventDefault(); setType(m.getAttribute('data-more')); }
	});
};

document.addEventListener('DOMContentLoaded', function () {
	__mysys_dashboard_ent.__find();
	__mysys_dashboard_ent.__chart(window.__db_monthly || []);
	__mysys_dashboard_ent.__payee_table();
	__mysys_dashboard_ent.__panel_events();
});
