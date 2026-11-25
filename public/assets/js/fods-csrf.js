// CSRF token on every same-site POST (Config\Filters 'csrf').
// The token is in <meta name="csrf-token"> (templates/myheader.php). It is added
// on XMLHttpRequest itself, so every jQuery copy on the page sends it, and on fetch.
(function () {
	var meta = document.querySelector('meta[name="csrf-token"]');
	if (!meta) { return; }
	var token = meta.getAttribute('content');
	var header = meta.getAttribute('data-header') || 'X-CSRF-TOKEN';

	function sameSite(url) {
		try { return new URL(url, location.href).origin === location.origin; } catch (e) { return false; }
	}

	var open = XMLHttpRequest.prototype.open;
	var send = XMLHttpRequest.prototype.send;
	XMLHttpRequest.prototype.open = function (method, url) {
		this.__fodsCsrf = String(method).toUpperCase() !== 'GET' && String(method).toUpperCase() !== 'HEAD' && sameSite(url);
		return open.apply(this, arguments);
	};
	XMLHttpRequest.prototype.send = function () {
		if (this.__fodsCsrf) { try { this.setRequestHeader(header, token); } catch (e) { /* already sent */ } }
		return send.apply(this, arguments);
	};

	if (window.fetch) {
		var nativeFetch = window.fetch;
		window.fetch = function (input, init) {
			init = init || {};
			var method = String(init.method || (input && input.method) || 'GET').toUpperCase();
			var url = typeof input === 'string' ? input : (input && input.url) || '';
			if (method !== 'GET' && method !== 'HEAD' && sameSite(url)) {
				var headers = new Headers(init.headers || (input && input.headers) || {});
				headers.set(header, token);
				init.headers = headers;
			}
			return nativeFetch.call(this, input, init);
		};
	}
})();
