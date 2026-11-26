// Toasts survive a reload. A save reloads the list after 1 s while its toast is
// meant to show for 2.5 s, so a success / info / warning toast still on screen
// when the page unloads is shown again on the next page for the time it had left.
// Loaded in templates/myfooter.php right after toastr.
(function () {
	if (!window.toastr) { return; }
	var KEY = 'fods.flash';
	var CARRY = ['success', 'info', 'warning'];   // errors never reload the page
	var live = [];

	function store(list) {
		try {
			if (list.length) { sessionStorage.setItem(KEY, JSON.stringify(list)); }
			else { sessionStorage.removeItem(KEY); }
		} catch (e) { /* storage blocked: the toast just isn't carried over */ }
	}

	CARRY.forEach(function (type) {
		var show = window.toastr[type];
		window.toastr[type] = function (message, title, options) {
			var opts = options || {};
			var ms = +(opts.timeOut != null ? opts.timeOut : (window.toastr.options.timeOut || 5000));
			if (ms > 0) {
				live.push({ type: type, message: message, title: title, opts: opts, until: Date.now() + ms, total: ms });
			}
			return show.apply(window.toastr, arguments);
		};
	});

	// Leaving the page: keep the toasts that still have time left
	window.addEventListener('pagehide', function () {
		var now = Date.now();
		store(live.filter(function (t) { return t.until - now > 400; }));
	});

	// Arriving: the same toast carries on, not a new one. It appears without the
	// fade-in, and its progress bar starts where it was and runs out with the time left.
	var saved = [];
	try { saved = JSON.parse(sessionStorage.getItem(KEY) || '[]') || []; } catch (e) { saved = []; }
	store([]);
	saved.forEach(function (t) {
		var left = t.until - Date.now();
		if (left > 300 && CARRY.indexOf(t.type) !== -1) {
			var opts = {};
			for (var k in t.opts) { if (Object.prototype.hasOwnProperty.call(t.opts, k)) { opts[k] = t.opts[k]; } }
			var bar = !!opts.progressBar;
			opts.timeOut = left;
			opts.showDuration = 0;
			opts.progressBar = false;   // drawn below, from where it had reached
			var $toast = window.toastr[t.type](t.message, t.title, opts);
			if (bar && $toast && $toast.length) {
				var el = document.createElement('div');
				el.className = 'toast-progress';
				el.style.width = Math.min(100, (left / (t.total || left)) * 100) + '%';
				$toast[0].insertBefore(el, $toast[0].firstChild);
				el.getBoundingClientRect();   // apply the start width before animating
				el.style.transition = 'width ' + left + 'ms linear';
				el.style.width = '0%';
			}
		}
	});
})();
