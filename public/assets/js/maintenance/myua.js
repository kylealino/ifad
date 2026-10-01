var __mysys_myua_ent = new __mysys_myua_ent();
function __mysys_myua_ent() {
	const mesiteurl = $('#__siteurl').attr('data-mesiteurl');

	function showModal(id) {
		bootstrap.Modal.getOrCreateInstance(document.getElementById(id)).show();
	}

	function hideModal(id) {
		bootstrap.Modal.getOrCreateInstance(document.getElementById(id)).hide();
	}

	// Mirrors ua_initials() in the view so the modal preview matches the table
	function initials(name) {
		var base = String(name || '').split(',')[0].trim();
		var tokens = base.split(/\s+/).filter(function (t) { return t && t.slice(-1) !== '.'; });
		if (!tokens.length) { return base.charAt(0).toUpperCase(); }
		var last = tokens.length > 1 ? tokens[tokens.length - 1].charAt(0) : '';
		return (tokens[0].charAt(0) + last).toUpperCase();
	}

	// Single POST helper - every action returns a <script> (toastr + reload)
	// that gets injected into the shared output div, same as other modules.
	function post(mparam, btn, errTag) {
		var $btn = btn ? jQuery(btn) : null;
		var original = $btn ? $btn.html() : '';
		if ($btn) {
			$btn.prop('disabled', true);
			if ($btn.is('.btn')) { $btn.html('<span class="spinner-border spinner-border-sm me-1"></span>Saving...'); }
		}
		jQuery.ajax({
			type: "POST",
			url: mesiteurl + 'myua',
			data: mparam,
			global: false,
			cache: false,
			success: function(data) {
				jQuery('.me-myua-outp-msg').html(data);
			},
			error: function(xhr, status, error) {
				toastr.error(errTag, "Error loading page..." + error, { closeButton: true });
			},
			complete: function() {
				if ($btn) {
					setTimeout(function() { $btn.prop('disabled', false).html(original); }, 800);
				}
			}
		});
	}

	//TABLES - DataTables with the page's own search boxes + filter pills
	this.__tables = function() {
		var userFilter = 'all';

		jQuery.fn.dataTable.ext.search.push(function (settings, data, dataIndex) {
			if (settings.nTable.id !== 'usersTable' || userFilter === 'all') { return true; }
			var tr = settings.aoData[dataIndex].nTr;
			if (userFilter === 'active') { return tr.getAttribute('data-status') === 'active'; }
			if (userFilter === 'inactive') { return tr.getAttribute('data-status') === 'inactive'; }
			if (userFilter === 'norole') { return tr.getAttribute('data-hasrole') === '0'; }
			return true;
		});

		var dtOptions = {
			pageLength: 10,
			lengthChange: false,
			autoWidth: false,
			dom: 't<"d-flex flex-wrap justify-content-between align-items-center"ip>',
			language: {
				info: "Showing _START_–_END_ of _TOTAL_",
				infoEmpty: "Nothing to show",
				infoFiltered: "",
				zeroRecords: '<div class="text-center text-muted py-4"><i class="ti ti-search fs-6 d-block mb-1"></i>No matches found</div>',
				paginate: { previous: '<i class="ti ti-chevron-left"></i>', next: '<i class="ti ti-chevron-right"></i>' }
			},
			columnDefs: [{ targets: -1, orderable: false, searchable: false }]
		};

		var usersDt = jQuery('#usersTable').DataTable(dtOptions);
		var rolesDt = jQuery('#rolesTable').DataTable(dtOptions);

		jQuery('#usersSearch').on('input', function () { usersDt.search(this.value).draw(); });
		jQuery('#rolesSearch').on('input', function () { rolesDt.search(this.value).draw(); });

		function applyUserFilter(filter) {
			userFilter = filter;
			jQuery('#userFilter button').removeClass('active').filter('[data-filter="' + filter + '"]').addClass('active');
			jQuery('.ua-stat-filter').removeClass('is-active');
			if (filter !== 'all') { jQuery('.ua-stat-filter[data-filter="' + filter + '"]').addClass('is-active'); }
			usersDt.draw();
		}

		jQuery(document).on('click', '#userFilter button', function () {
			applyUserFilter(jQuery(this).data('filter'));
		});

		// Stat tiles filter the users table (and jump to the Users tab)
		jQuery(document).on('click', '.ua-stat-filter', function () {
			bootstrap.Tab.getOrCreateInstance(document.getElementById('users-tab')).show();
			applyUserFilter(jQuery(this).data('filter'));
		});

		// Tables initialised inside a hidden tab need their widths recalculated
		document.querySelectorAll('button[data-bs-toggle="tab"]').forEach(function (el) {
			el.addEventListener('shown.bs.tab', function () {
				usersDt.columns.adjust();
				rolesDt.columns.adjust();
			});
		});
	};

	//USER - OPEN MODAL (add = blank, edit = filled from the row's data-*)
	this.__user_modal = function() {
		function setPreview(name, isEdit) {
			var $a = jQuery('#userPreviewAvatar');
			var txt = initials(name);
			if (txt) { $a.text(txt); }
			else { $a.html('<i class="ti ' + (isEdit ? 'ti-user' : 'ti-user-plus') + '"></i>'); }
		}

		function fill(row) {
			var d = row ? jQuery(row).data() : {};
			var isEdit = !!row;

			jQuery('#userModalTitle').text(isEdit ? d.full_name : 'Add User');
			jQuery('#userModalSub').text(isEdit ? '@' + d.username + ' · update profile, role, or reset password' : 'Create an account and assign a role.');
			jQuery('#userSubmitBtn').text(isEdit ? 'Save changes' : 'Create user');
			jQuery('#recid').val(isEdit ? d.recid : '');
			jQuery('#full_name').val(d.full_name || '');
			jQuery('#division').val(d.division || '');
			jQuery('#section').val(d.section || '');
			jQuery('#position').val(d.position || '');
			jQuery('#username').val(d.username || '');
			jQuery('#role_id').val(d.role_id || '');
			jQuery('#cert_tag').val(d.cert_tag || 0);
			jQuery('#ada_tag').val(d.ada_tag || 0);
			jQuery('#is_ppmp_signatory').prop('checked', d.is_ppmp_signatory == 1);

			jQuery('#password').val('')
				.prop('required', !isEdit)
				.attr('placeholder', isEdit ? 'Leave blank to keep current' : 'Set a password')
				.attr('type', 'password');
			jQuery('#toggleIcon').removeClass('ti-eye-off').addClass('ti-eye');
			jQuery('.myua-validation').removeClass('was-validated');
			setPreview(d.full_name, isEdit);

			showModal('userModal');
		}

		jQuery(document).on('click', '#btn_add_user', function () { fill(null); });
		jQuery(document).on('click', '.me-user-edit', function () { fill(jQuery(this).closest('tr')); });
		jQuery(document).on('input', '#full_name', function () { setPreview(this.value, !!jQuery('#recid').val()); });

		jQuery(document).on('click', '#togglePassword', function () {
			var input = document.getElementById('password');
			var show = input.type === 'password';
			input.type = show ? 'text' : 'password';
			jQuery('#toggleIcon').toggleClass('ti-eye', !show).toggleClass('ti-eye-off', show);
		});

		document.getElementById('userModal').addEventListener('shown.bs.modal', function () { jQuery('#full_name').trigger('focus'); });
	};

	//USER - SAVE
	this.__user_saving = function() {
		jQuery(document).on('submit', '.myua-validation', function (event) {
			event.preventDefault();
			event.stopPropagation();
			var form = this;
			if (!form.checkValidity()) {
				jQuery(form).addClass('was-validated');
				return false;
			}
			try {
				var mparam = {
					recid: jQuery('#recid').val(),
					full_name: jQuery('#full_name').val(),
					division: jQuery('#division').val(),
					section: jQuery('#section').val(),
					position: jQuery('#position').val(),
					username: jQuery('#username').val(),
					password: jQuery('#password').val(),
					role_id: jQuery('#role_id').val(),
					cert_tag: jQuery('#cert_tag').val(),
					ada_tag: jQuery('#ada_tag').val(),
					is_ppmp_signatory: jQuery('#is_ppmp_signatory').is(':checked') ? 1 : '',
					meaction: 'MAIN-SAVE'
				};
				post(mparam, '#userSubmitBtn', '[MYUA]');
			} catch(err) {
				alert(err.message);
			}
			return false;
		});
	};

	//USER - ACTIVATE / DEACTIVATE
	this.__user_toggle = function() {
		jQuery(document).on('click', '.me-user-toggle', function () {
			post({ recid: jQuery(this).data('recid'), meaction: 'USER-TOGGLE-ACTIVE' }, this, '[MYUA]');
		});
	};

	//ROLE - OPEN MODAL (add / edit name + description)
	this.__role_modal = function() {
		function fill(row) {
			var d = row ? jQuery(row).data() : {};
			jQuery('#roleModalTitle').text(row ? 'Edit Role' : 'Add Role');
			jQuery('#roleSubmitBtn').text(row ? 'Save changes' : 'Create role');
			jQuery('#role_recid').val(row ? d.recid : '');
			jQuery('#role_name').val(d.role_name || '');
			jQuery('#role_description').val(d.description || '');
			jQuery('.myua-role-validation').removeClass('was-validated');
			showModal('roleModal');
		}

		jQuery(document).on('click', '#btn_add_role', function () { fill(null); });
		jQuery(document).on('click', '#btn_add_role_head', function () {
			bootstrap.Tab.getOrCreateInstance(document.getElementById('roles-tab')).show();
			fill(null);
		});
		jQuery(document).on('click', '.me-role-edit', function () { fill(jQuery(this).closest('tr')); });
		document.getElementById('roleModal').addEventListener('shown.bs.modal', function () { jQuery('#role_name').trigger('focus'); });
	};

	//ROLE - SAVE (name + description)
	this.__role_saving = function() {
		jQuery(document).on('submit', '.myua-role-validation', function (event) {
			event.preventDefault();
			event.stopPropagation();
			var form = this;
			if (!form.checkValidity()) {
				jQuery(form).addClass('was-validated');
				return false;
			}
			var mparam = jQuery(form).serializeArray();
			mparam.push({ name: 'meaction', value: 'ROLE-SAVE' });
			post(mparam, '#roleSubmitBtn', '[MYUA-ROLE]');
			return false;
		});
	};

	//ROLE - ACTIVATE / DEACTIVATE
	this.__role_toggle = function() {
		jQuery(document).on('click', '.me-role-toggle', function () {
			post({ role_id: jQuery(this).data('roleid'), meaction: 'ROLE-TOGGLE-ACTIVE' }, this, '[MYUA-ROLE]');
		});
	};

	//ROLE - DELETE (confirm modal)
	this.__role_delete = function() {
		var pendingRoleId = null;

		jQuery(document).on('click', '.me-role-delete', function () {
			pendingRoleId = jQuery(this).data('roleid');
			showModal('confirmRoleDeleteModal');
		});

		jQuery(document).on('click', '#confirmRoleDeleteBtn', function () {
			if (!pendingRoleId) { return; }
			post({ role_id: pendingRoleId, meaction: 'ROLE-DELETE' }, this, '[MYUA-ROLE]');
			hideModal('confirmRoleDeleteModal');
		});
	};

	//ROLE - PERMISSIONS MODAL (one shared checklist, re-checked per role)
	this.__role_permissions = function() {
		var $form = jQuery('#rolePermissionsForm');

		function syncFamilyBoxes() {
			$form.find('.perm-family').each(function () {
				var boxes = jQuery(this).find('.access-code-checkbox');
				var checked = boxes.filter(':checked').length;
				jQuery(this).toggleClass('has-selection', checked > 0);
				jQuery(this).find('.perm-family-count').text(checked + '/' + boxes.length);
				jQuery(this).find('.family-select-all')
					.prop('checked', checked === boxes.length)
					.prop('indeterminate', checked > 0 && checked < boxes.length);
			});
			var total = $form.find('.access-code-checkbox').length;
			var selected = $form.find('.access-code-checkbox:checked').length;
			jQuery('#permSelectedCount').html('<b class="text-dark">' + selected + '</b> of ' + total + ' permissions selected');
		}

		jQuery(document).on('click', '.me-role-perms', function () {
			var d = jQuery(this).closest('tr').data();
			var codes = String(d.access_codes || '').split(',').filter(Boolean);

			jQuery('#perm_role_id').val(d.recid);
			jQuery('#permModalRoleName').text(d.role_name);
			jQuery('#permSearch').val('');
			$form.find('.perm-family').show();
			$form.find('.access-code-checkbox').each(function () {
				jQuery(this).prop('checked', codes.indexOf(jQuery(this).val()) !== -1);
			});
			syncFamilyBoxes();
			$form.scrollTop(0);
			showModal('permissionsModal');
		});

		$form.on('change', '.family-select-all', function () {
			jQuery(this).closest('.perm-family').find('.access-code-checkbox').prop('checked', jQuery(this).prop('checked'));
			syncFamilyBoxes();
		});

		$form.on('change', '.access-code-checkbox', syncFamilyBoxes);

		// Select all / Clear act on the currently visible (filtered) groups only
		jQuery(document).on('click', '#permSelectAll', function () {
			$form.find('.perm-family:visible .access-code-checkbox').prop('checked', true);
			syncFamilyBoxes();
		});
		jQuery(document).on('click', '#permClearAll', function () {
			$form.find('.perm-family:visible .access-code-checkbox').prop('checked', false);
			syncFamilyBoxes();
		});

		jQuery(document).on('input', '#permSearch', function () {
			var q = this.value.trim().toLowerCase();
			$form.find('.perm-family').each(function () {
				jQuery(this).toggle(!q || jQuery(this).data('search').indexOf(q) !== -1);
			});
		});

		jQuery(document).on('click', '#btn_save_role_access', function () {
			var access_codes = [];
			$form.find('.access-code-checkbox:checked').each(function () {
				access_codes.push(jQuery(this).val());
			});
			post({
				role_id: jQuery('#perm_role_id').val(),
				access_codes: access_codes,
				meaction: 'ROLE-ACCESS-SAVE'
			}, this, '[MYUA-ROLE]');
		});
	};

}; //end main
