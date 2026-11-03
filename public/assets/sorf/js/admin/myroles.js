// Administration - Roles (admin/roles-main.php, admin/role-form.php):
// save a role and its permissions, delete a role nobody has.
var __mysys_roles = new __mysys_roles_ent();
function __mysys_roles_ent() {
	const app = __mysys_drf;

	//PERMISSIONS - ticking one ticks what it needs (data-requires); unticking a
	// needed one unticks what depends on it
	this.__role_requires = function () {
		const form = document.getElementById('roleForm');
		if (!form) { return; }
		const boxes = Array.prototype.slice.call(form.querySelectorAll('input[name="perms[]"][type="checkbox"]'));
		const byCode = {};
		boxes.forEach(function (b) { byCode[b.value] = b; });

		form.addEventListener('change', function (e) {
			const box = e.target;
			if (!byCode[box.value] || byCode[box.value] !== box) { return; }
			if (box.checked) {
				(box.getAttribute('data-requires') || '').split(' ').filter(Boolean).forEach(function (code) {
					if (byCode[code] && !byCode[code].checked) {
						byCode[code].checked = true;
						app.toast('"' + labelOf(byCode[code]) + '" was ticked too: "' + labelOf(box) + '" needs it.', '');
					}
				});
			} else {
				boxes.forEach(function (other) {
					if (other.checked && !other.disabled && (other.getAttribute('data-requires') || '').split(' ').indexOf(box.value) !== -1) {
						other.checked = false;
						app.toast('"' + labelOf(other) + '" was unticked too: it needs "' + labelOf(box) + '".', '');
					}
				});
			}
		});

		function labelOf(box) {
			return form.querySelector('label[for="' + box.id + '"] strong').textContent;
		}
	};

	//SAVE
	this.__role_saving = function () {
		const form = document.getElementById('roleForm');
		if (!form) { return; }
		const btn = document.getElementById('roleSubmit');

		form.addEventListener('submit', function (e) {
			e.preventDefault();
			if (!form.checkValidity()) {
				form.classList.add('was-validated');
				form.querySelector(':invalid').focus();
				app.toast('Please fill in the required fields.', 'error');
				return;
			}
			const none = !form.querySelector('input[name="perms[]"]:checked, input[type="hidden"][name="perms[]"]');
			const sure = !none ? Promise.resolve(true) : app.confirm({
				title: 'Save a role with no permissions?',
				text: 'Accounts with this role can sign in but open nothing.',
				yes: 'Save Anyway',
				tone: 'warn'
			});

			sure.then(function (ok) {
				if (!ok) { return; }
				const data = new FormData(form);
				data.append('meaction', 'ROLE-SAVE');
				const done = app.busy(btn, 'Saving...');
				app.post('myroles', data).then(function (res) {
					app.toast(res.message, 'success');
					setTimeout(function () { window.location.href = app.url('myroles'); }, 800);
				}).catch(function (err) {
					done();
					app.toast(err.message, 'error');
				});
			});
		});
	};

	//DELETE - only a role no account has (the button is only shown then)
	this.__role_delete = function () {
		document.addEventListener('click', function (e) {
			const b = e.target.closest('[data-role-delete]');
			if (!b) { return; }
			app.confirm({
				title: 'Delete the role "' + b.getAttribute('data-name') + '"?',
				text: 'No account has it. To keep it for later, switch it off instead.',
				yes: 'Delete Role',
				tone: 'danger'
			}).then(function (ok) {
				if (!ok) { return; }
				const data = new FormData();
				data.append('meaction', 'ROLE-DELETE');
				data.append('recid', b.getAttribute('data-role-delete'));
				const done = app.busy(b, '');
				app.post('myroles', data).then(function (res) {
					app.toast(res.message, 'success');
					setTimeout(function () { window.location.href = app.url('myroles'); }, 800);
				}).catch(function (err) {
					done();
					app.toast(err.message, 'error');
				});
			});
		});
	};
}

document.addEventListener('DOMContentLoaded', function () {
	__mysys_roles.__role_requires();
	__mysys_roles.__role_saving();
	__mysys_roles.__role_delete();
});
