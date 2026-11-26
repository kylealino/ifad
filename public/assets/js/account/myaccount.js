var __mysys_myaccount_ent = new __mysys_myaccount_ent();
function __mysys_myaccount_ent() {
	const siteEl = document.getElementById('__siteurl');
	const mesiteurl = siteEl.getAttribute('data-mesiteurl');
	const signs = +(window.__acct_signs || 0);   // Approver Mapping boxes signed under the current name

	const PHOTO_TYPES = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
	const PHOTO_MAX = 5 * 1048576;   // same limit as MyAccountModel::PHOTO_MAX_BYTES
	const PHOTO_SIZE = 512;          // sent as a 512 px square

	function toast(kind, message, title) {
		toastr[kind](message, title, { progressBar: true, closeButton: true, timeOut: 2500 });
	}

	function esc(s) {
		return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) {
			return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
		});
	}

	function busy($btn, label) {
		var html = $btn.html();
		$btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>' + label);
		return function () { $btn.prop('disabled', false).html(html); };
	}

	//MY ACCOUNT - PROFILE PICTURE (here, in the topbar and in the profile menu at once)
	function showPhoto(url) {
		var $ = window.jQuery;
		$('#acctPhoto, [data-fods-avatar]').each(function () {
			var el = $(this);
			el.toggleClass('has-photo', !!url);
			if (url) { el.html('<img src="' + esc(url) + '" alt="">'); }
			else { el.text(el.attr('data-initials') || ''); }
		});
		$('#acctPhotoRemove').toggleClass('d-none', !url);
	}

	// Centre square, 512 px, JPEG (white behind transparent PNGs)
	function squareBlob(file) {
		return new Promise(function (resolve, reject) {
			var reader = new FileReader();
			reader.onerror = function () { reject(new Error('read')); };
			reader.onload = function () {
				var img = new Image();
				img.onerror = function () { reject(new Error('image')); };
				img.onload = function () {
					var side = Math.min(img.naturalWidth, img.naturalHeight);
					var out = Math.min(PHOTO_SIZE, side);
					var canvas = document.createElement('canvas');
					canvas.width = canvas.height = out;
					var ctx = canvas.getContext('2d');
					ctx.fillStyle = '#fff';
					ctx.fillRect(0, 0, out, out);
					ctx.imageSmoothingQuality = 'high';
					ctx.drawImage(img, (img.naturalWidth - side) / 2, (img.naturalHeight - side) / 2, side, side, 0, 0, out, out);
					canvas.toBlob(function (blob) { blob ? resolve(blob) : reject(new Error('blob')); }, 'image/jpeg', 0.9);
				};
				img.src = reader.result;
			};
			reader.readAsDataURL(file);
		});
	}

	function upload(file) {
		var $ = window.jQuery;
		if (!file) { return; }
		if (PHOTO_TYPES.indexOf(file.type) === -1) { toast('warning', 'Choose a JPG, PNG, GIF or WEBP picture.', 'Not a picture'); return; }
		if (file.size > PHOTO_MAX) { toast('warning', file.name + ' is over 5 MB.', 'Picture too big'); return; }

		var $drop = $('#acctPhotoDrop').addClass('is-busy');
		var done = busy($('#acctPhotoPick'), 'Uploading...');
		var finish = function () { $drop.removeClass('is-busy'); done(); };

		squareBlob(file).then(function (blob) {
			var fd = new FormData();
			fd.append('meaction', 'PHOTO-UPLOAD');
			fd.append('attachments[]', blob, 'avatar.jpg');
			$.ajax({
				type: 'POST',
				url: mesiteurl + 'myaccount',
				data: fd,
				processData: false,
				contentType: false,
				dataType: 'json',
				global: false,
				cache: false,
				success: function (d) { showPhoto(d.url); toast('success', 'Your profile picture was updated.', 'Well Done!'); },
				error: function (xhr) { toast('error', (xhr.responseJSON && xhr.responseJSON.error) || 'The picture could not be uploaded.', 'Oops!'); },
				complete: finish
			});
		}).catch(function () {
			finish();
			toast('error', 'This picture could not be read. Try another file.', 'Oops!');
		});
	}

	this.__acct_photo = function () {
		var $ = window.jQuery;
		var input = document.getElementById('acctPhotoFile');
		var drop = document.getElementById('acctPhotoDrop');

		$('#acctPhotoPick').on('click', function () { input.click(); });
		$(drop).on('click', function () { input.click(); });
		$(drop).on('keydown', function (e) { if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); input.click(); } });
		$(input).on('change', function () { upload(this.files[0]); this.value = ''; });

		// drop a picture on the avatar
		drop.addEventListener('dragover', function (e) { e.preventDefault(); drop.classList.add('is-over'); });
		drop.addEventListener('dragleave', function () { drop.classList.remove('is-over'); });
		drop.addEventListener('drop', function (e) {
			e.preventDefault();
			drop.classList.remove('is-over');
			if (e.dataTransfer && e.dataTransfer.files.length) { upload(e.dataTransfer.files[0]); }
		});

		$('#acctPhotoRemove').on('click', function () {
			var restore = busy($(this), 'Removing...');
			$.ajax({
				type: 'POST',
				url: mesiteurl + 'myaccount',
				data: { meaction: 'PHOTO-REMOVE' },
				dataType: 'json',
				global: false,
				cache: false,
				success: function () { showPhoto(''); toast('info', 'Your profile picture was removed.', 'Well Done!'); },
				error: function (xhr) { toast('error', (xhr.responseJSON && xhr.responseJSON.error) || 'The picture could not be removed.', 'Oops!'); },
				complete: restore
			});
		});
	};

	//MY ACCOUNT - SAVE DETAILS
	this.__acct_saving = function () {
		var $ = window.jQuery;
		var form = document.getElementById('accountForm');
		var name = document.getElementById('full_name');

		// a signatory renaming themselves stops matching Approver Mapping
		$(name).on('input', function () {
			var changed = this.value.trim().toUpperCase() !== String(this.getAttribute('data-original') || '').trim().toUpperCase();
			$('#acctNameWarn').toggleClass('d-none', !(signs > 0 && changed));
		});

		// same characters MyAccountModel::account_save allows
		var phone = document.getElementById('contact_no');
		function checkPhone() { phone.setCustomValidity(/^[0-9+().\/\s-]*$/.test(phone.value.trim()) ? '' : 'invalid'); }
		phone.addEventListener('input', checkPhone);

		form.addEventListener('submit', function (e) {
			e.preventDefault();
			checkPhone();
			if (!form.checkValidity()) {
				form.classList.add('was-validated');
				var bad = form.querySelector(':invalid');
				if (bad) { bad.focus(); }
				return;
			}

			var val = function (id) { return document.getElementById(id).value.trim(); };
			var mparam = {
				full_name: val('full_name'),
				position: val('position'),
				division: val('division'),
				section: val('section'),
				email: val('email'),
				contact_no: val('contact_no'),
				meaction: 'MAIN-SAVE'
			};

			var restore = busy($('#submitBtn'), 'Saving...');
			$.ajax({
				type: 'POST',
				url: mesiteurl + 'myaccount',
				data: mparam,
				global: false,
				cache: false,
				success: function (data) {
					$('.me-outp-msg').html(data);
					// a saved account reloads the page, so the button stays off until then
					if (String(data).indexOf('window.location') === -1) { restore(); }
				},
				error: function (xhr, status, error) {
					toast('error', 'Your details could not be saved. ' + (error || ''), 'Oops!');
					restore();
				}
			});
		});
	};

	//MY ACCOUNT - CHANGE PASSWORD
	this.__acct_password = function () {
		var $ = window.jQuery;
		var form = document.getElementById('passwordForm');
		var pw = document.getElementById('new_password');
		var confirm = document.getElementById('confirm_password');

		function match() { confirm.setCustomValidity(confirm.value !== '' && confirm.value !== pw.value ? 'mismatch' : ''); }
		pw.addEventListener('input', match);
		confirm.addEventListener('input', match);

		form.addEventListener('submit', function (e) {
			e.preventDefault();
			match();
			if (!form.checkValidity()) {
				form.classList.add('was-validated');
				var bad = form.querySelector(':invalid');
				if (bad) { bad.focus(); }
				return;
			}

			var mparam = {
				current_password: document.getElementById('current_password').value,
				new_password: pw.value,
				confirm_password: confirm.value,
				meaction: 'PASSWORD-SAVE'
			};

			var restore = busy($('#passwordBtn'), 'Changing...');
			$.ajax({
				type: 'POST',
				url: mesiteurl + 'myaccount',
				data: mparam,
				global: false,
				cache: false,
				success: function (data) {
					$('.me-outp-msg').html(data);
					if (String(data).indexOf('window.location') === -1) { restore(); }
				},
				error: function (xhr, status, error) {
					toast('error', 'Your password could not be changed. ' + (error || ''), 'Oops!');
					restore();
				}
			});
		});
	};
}

// jQuery, toastr and Bootstrap load in the footer, after this file
document.addEventListener('DOMContentLoaded', function () {
	__mysys_myaccount_ent.__acct_photo();
	__mysys_myaccount_ent.__acct_saving();
	__mysys_myaccount_ent.__acct_password();
});
