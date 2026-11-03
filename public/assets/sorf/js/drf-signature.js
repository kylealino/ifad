// Signature pad (request/signature-pad.php): draw with a mouse, finger or
// stylus, or upload a picture of a signature. Used where a request is signed:
// the request form (requester) and the request page ("Noted by" signatory).
//
//   const pad = __mysys_drf_signature(element);
//   pad.state()  -> 'kept'  the signature already on the request stays
//                   'new'   a signature was drawn or uploaded
//                   'empty' nothing to send yet
//   pad.value()  -> PNG data URL of the new signature, trimmed to the ink ('' unless 'new')
function __mysys_drf_signature(root) {
	const app = __mysys_drf;
	const canvas = root.querySelector('[data-sign-canvas]');
	const ctx = canvas.getContext('2d');
	const hint = root.querySelector('[data-sign-hint]');
	const file = root.querySelector('[data-sign-file]');
	const current = root.querySelector('[data-sign-current]');
	const fresh = root.querySelector('[data-sign-new]');

	const INK = '#0f172a';
	const MAX_UPLOAD = 5242880;      // 5 MB picture; what is sent is the small trimmed PNG
	const OUT_W = 600, OUT_H = 300;  // largest signature sent to the server

	let kept = current !== null;     // an existing signature is kept until "Sign again"
	let ink = false;
	let drawing = false;
	let last = null;

	function setInk(on) {
		ink = on;
		hint.hidden = on;
	}

	// tells the page the signature changed (the request form's live preview listens)
	function changed() {
		root.dispatchEvent(new CustomEvent('drf-sign-change', { bubbles: true }));
	}

	// Match the bitmap to the size on screen (sharp on phones), keeping what is drawn
	function fit() {
		const ratio = Math.max(window.devicePixelRatio || 1, 1);
		const w = Math.round(canvas.clientWidth * ratio);
		const h = Math.round(canvas.clientHeight * ratio);
		if (!w || !h || (canvas.width === w && canvas.height === h)) { return; }

		const copy = document.createElement('canvas');
		copy.width = canvas.width;
		copy.height = canvas.height;
		if (ink) { copy.getContext('2d').drawImage(canvas, 0, 0); }
		canvas.width = w;
		canvas.height = h;
		if (ink) { ctx.drawImage(copy, 0, 0, copy.width, copy.height, 0, 0, w, h); }
	}

	function point(e) {
		const r = canvas.getBoundingClientRect();
		return { x: (e.clientX - r.left) * canvas.width / r.width, y: (e.clientY - r.top) * canvas.height / r.height };
	}

	function line(from, to) {
		ctx.strokeStyle = INK;
		ctx.lineWidth = Math.max(2, canvas.width / 260);
		ctx.lineCap = 'round';
		ctx.lineJoin = 'round';
		ctx.beginPath();
		ctx.moveTo(from.x, from.y);
		ctx.lineTo(to.x, to.y);
		ctx.stroke();
	}

	//DRAW - pointer events cover mouse, touch and pen alike
	canvas.addEventListener('pointerdown', function (e) {
		if (e.button > 0) { return; }
		e.preventDefault();
		fit();
		canvas.setPointerCapture(e.pointerId);
		drawing = true;
		last = point(e);
		line(last, { x: last.x + 0.1, y: last.y + 0.1 });   // a tap leaves a dot
		setInk(true);
	});
	canvas.addEventListener('pointermove', function (e) {
		if (!drawing) { return; }
		e.preventDefault();
		// fast strokes arrive bundled; drawing each point keeps the curve smooth
		(e.getCoalescedEvents ? e.getCoalescedEvents() : [e]).forEach(function (ev) {
			const p = point(ev);
			line(last, p);
			last = p;
		});
	});
	['pointerup', 'pointercancel', 'pointerleave'].forEach(function (type) {
		canvas.addEventListener(type, function () {
			if (drawing) { changed(); }
			drawing = false;
		});
	});

	function clear() {
		ctx.clearRect(0, 0, canvas.width, canvas.height);
		setInk(false);
	}
	root.querySelector('[data-sign-clear]').addEventListener('click', function () { clear(); changed(); });

	//UPLOAD - a photo or scan of a signature: fitted in the pad, paper made transparent
	function place(img) {
		fit();
		clear();
		const scale = Math.min(canvas.width * 0.9 / img.width, canvas.height * 0.9 / img.height);
		const w = Math.max(1, Math.round(img.width * scale));
		const h = Math.max(1, Math.round(img.height * scale));
		const x = Math.round((canvas.width - w) / 2);
		const y = Math.round((canvas.height - h) / 2);
		ctx.drawImage(img, x, y, w, h);

		const px = ctx.getImageData(x, y, w, h);
		const d = px.data;
		const lum = function (i) { return d[i + 3] < 8 ? 255 : 0.299 * d[i] + 0.587 * d[i + 1] + 0.114 * d[i + 2]; };

		// the paper colour is read off the edge of the picture; anything about as light becomes clear
		let paper = 0, n = 0;
		for (let cx = 0; cx < w; cx += 4) { paper += lum(cx * 4) + lum(((h - 1) * w + cx) * 4); n += 2; }
		for (let cy = 0; cy < h; cy += 4) { paper += lum(cy * w * 4) + lum((cy * w + w - 1) * 4); n += 2; }
		const limit = Math.max(90, paper / n - 40);

		for (let i = 0; i < d.length; i += 4) {
			const l = lum(i);
			d[i + 3] = l >= limit ? 0 : Math.min(d[i + 3], Math.round(Math.min(1, (limit - l) / 70) * 255));
		}
		ctx.putImageData(px, x, y);
		setInk(true);
		changed();
	}

	root.querySelector('[data-sign-upload]').addEventListener('click', function () { file.click(); });
	file.addEventListener('change', function () {
		const f = file.files[0];
		file.value = '';
		if (!f) { return; }
		if (!/^image\/(png|jpeg|webp)$/.test(f.type)) { app.toast('Upload a PNG or JPG picture of your signature.', 'error'); return; }
		if (f.size > MAX_UPLOAD) { app.toast('That picture is over 5 MB. Please use a smaller one.', 'error'); return; }

		const url = URL.createObjectURL(f);
		const img = new Image();
		img.onload = function () { place(img); URL.revokeObjectURL(url); };
		img.onerror = function () { URL.revokeObjectURL(url); app.toast('That picture could not be opened.', 'error'); };
		img.src = url;
	});

	//SIGN AGAIN - replace the signature already on the request
	if (current) {
		root.querySelector('[data-sign-redo]').addEventListener('click', function () {
			kept = false;
			current.hidden = true;
			fresh.hidden = false;
			fit();
			changed();
		});
	}

	window.addEventListener('resize', fit);
	fit();

	// The drawing cut to its ink (plus a little margin) and scaled down if large
	function value() {
		if (kept || !ink) { return ''; }
		const w = canvas.width, h = canvas.height;
		const d = ctx.getImageData(0, 0, w, h).data;
		let x0 = w, y0 = h, x1 = -1, y1 = -1;
		for (let y = 0; y < h; y++) {
			for (let x = 0; x < w; x++) {
				if (d[(y * w + x) * 4 + 3] > 8) {
					if (x < x0) { x0 = x; }
					if (x > x1) { x1 = x; }
					if (y < y0) { y0 = y; }
					if (y > y1) { y1 = y; }
				}
			}
		}
		if (x1 < 0) { return ''; }

		const pad = Math.round(w / 100);
		x0 = Math.max(0, x0 - pad); y0 = Math.max(0, y0 - pad);
		x1 = Math.min(w - 1, x1 + pad); y1 = Math.min(h - 1, y1 + pad);
		const cw = x1 - x0 + 1, ch = y1 - y0 + 1;
		const scale = Math.min(1, OUT_W / cw, OUT_H / ch);

		const out = document.createElement('canvas');
		out.width = Math.max(20, Math.round(cw * scale));
		out.height = Math.max(10, Math.round(ch * scale));
		out.getContext('2d').drawImage(canvas, x0, y0, cw, ch, 0, 0, out.width, out.height);
		return out.toDataURL('image/png');
	}

	return {
		state: function () { return kept ? 'kept' : (ink ? 'new' : 'empty'); },
		value: value,
		clear: clear
	};
}
