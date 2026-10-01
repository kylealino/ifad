<?php
namespace App\Models;
use CodeIgniter\Model;

class MyUserManagementModel extends Model
{

    protected $db;

    public function __construct(){
        parent::__construct();
        $this->session = session();
        $this->request = \Config\Services::request();
		$this->db = \Config\Database::connect();
		$this->cuser = $this->session->get('__xsys_myuserzicas__');

    }

	private function errorOut($message) {
		echo "
		<script>
		toastr.error('{$message}', 'Oops!', {
				progressBar: true,
				closeButton: true,
				timeOut:2500,
			});
		</script>
		";
		die();
	}

	private function successOut($message, $color = 'success', $redirectQuery = '') {
		$redirectUrl = 'myua?meaction=MAIN' . $redirectQuery;
		echo "
		<script>
			toastr.{$color}('{$message}!', 'Well Done!', {
					progressBar: true,
					closeButton: true,
					timeOut:2500,
				});
			setTimeout(function() {
					window.location.href = '{$redirectUrl}';
				}, 2500);
		</script>
		";
		exit;
	}

	public function user_save() {
		$recid = $this->request->getPostGet('recid');
		$full_name = $this->request->getPostGet('full_name');
		$division = $this->request->getPostGet('division');
		$section = $this->request->getPostGet('section');
		$position = $this->request->getPostGet('position');
		$username = $this->request->getPostGet('username');
		$password = $this->request->getPostGet('password');
		$role_id = $this->request->getPostGet('role_id');
		$cert_tag = $this->request->getPostGet('cert_tag');
		$ada_tag = $this->request->getPostGet('ada_tag');
		$is_ppmp_signatory = $this->request->getPostGet('is_ppmp_signatory') ? 1 : 0;

		if (empty($full_name)) { $this->errorOut('Full name is required!'); }
		if (empty($division)) { $this->errorOut('Division is required!'); }
		if (empty($section)) { $this->errorOut('Section is required!'); }
		if (empty($position)) { $this->errorOut('Position is required!'); }
		if (empty($username)) { $this->errorOut('Username is required!'); }
		if (empty($recid) && empty($password)) { $this->errorOut('Password is required for a new user!'); }

		$role_id = !empty($role_id) ? $role_id : null;
		$cert_tag = ($cert_tag !== null && $cert_tag !== '') ? $cert_tag : 0;
		$ada_tag = ($ada_tag !== null && $ada_tag !== '') ? $ada_tag : 0;

		if (empty($recid)) {
			fods_require_access(\Config\AccessCodes::USERMGMT_ADD, 'Saving');
			$hash_password = hash('sha512', $password);
			$query = $this->db->query("
				INSERT INTO `myua_user`(
					`username`, `hash_password`, `full_name`, `division`, `section`,
					`position`, `role_id`, `cert_tag`, `ada_tag`, `is_ppmp_signatory`,
					`added_at`, `added_by`
				)
				VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?)
			", [
				$username, $hash_password, $full_name, $division, $section,
				$position, $role_id, $cert_tag, $ada_tag, $is_ppmp_signatory,
				$this->cuser
			]);

			$this->syncUserAccess($username, $role_id);

			$status = "User Saved successfully";
			$color = "success";
		} else {
			fods_require_access(\Config\AccessCodes::USERMGMT_UPDATE, 'Updating');
			if (!empty($password)) {
				$hash_password = hash('sha512', $password);
				$query = $this->db->query("
					UPDATE `myua_user`
					SET `username` = ?, `hash_password` = ?, `full_name` = ?, `division` = ?,
						`section` = ?, `position` = ?, `role_id` = ?, `cert_tag` = ?,
						`ada_tag` = ?, `is_ppmp_signatory` = ?
					WHERE `recid` = ?
				", [
					$username, $hash_password, $full_name, $division, $section,
					$position, $role_id, $cert_tag, $ada_tag, $is_ppmp_signatory,
					$recid
				]);
			} else {
				$query = $this->db->query("
					UPDATE `myua_user`
					SET `username` = ?, `full_name` = ?, `division` = ?,
						`section` = ?, `position` = ?, `role_id` = ?, `cert_tag` = ?,
						`ada_tag` = ?, `is_ppmp_signatory` = ?
					WHERE `recid` = ?
				", [
					$username, $full_name, $division, $section, $position,
					$role_id, $cert_tag, $ada_tag, $is_ppmp_signatory,
					$recid
				]);
			}

			$this->syncUserAccess($username, $role_id);

			$status = "User Updated successfully";
			$color = "info";
		}

		if ($query) {
			$this->successOut($status, $color);
		} else {
			$this->errorOut('An error occurred while executing the query.');
		}
	}

	public function user_toggle_active() {
		$recid = $this->request->getPostGet('recid');
		if (empty($recid)) { $this->errorOut('Missing user.'); }
		fods_require_access(\Config\AccessCodes::USERMGMT_UPDATE, 'Updating');

		$query = $this->db->query("UPDATE `myua_user` SET `is_active` = NOT `is_active` WHERE `recid` = ?", [$recid]);

		if ($query) {
			$this->successOut('User status updated', 'success');
		} else {
			$this->errorOut('An error occurred while executing the query.');
		}
	}

	/**
	 * Recomputes tbl_user_access for one username from the module access
	 * codes granted by their currently-assigned, active role. This is the
	 * single sync point that keeps every existing module's access check
	 * (SELECT ... FROM tbl_user_access WHERE username=... AND access_code=...)
	 * working unchanged while permissions are now managed per-role.
	 */
	public function syncUserAccess($username, $role_id) {
		if (empty($username)) { return; }

		$this->db->query("DELETE FROM `tbl_user_access` WHERE `username` = ?", [$username]);

		if (empty($role_id)) { return; }

		$roleQuery = $this->db->query("SELECT `is_active` FROM `tbl_roles` WHERE `recid` = ?", [$role_id]);
		$role = $roleQuery->getRowArray();
		if (empty($role) || (int) $role['is_active'] !== 1) { return; }

		$codesQuery = $this->db->query("SELECT `access_code` FROM `tbl_role_access` WHERE `role_id` = ?", [$role_id]);
		$codes = $codesQuery->getResultArray();

		foreach ($codes as $row) {
			$this->db->query("
				INSERT INTO `tbl_user_access` (`username`, `access_code`, `is_active`, `added_by`)
				VALUES (?, ?, 1, ?)
			", [$username, $row['access_code'], $this->cuser]);
		}
	}

	/**
	 * Re-syncs every user currently assigned to a given role. Used after a
	 * role's permission set changes, or a role is activated/deactivated.
	 */
	public function syncUsersByRole($role_id) {
		$usersQuery = $this->db->query("SELECT `username` FROM `myua_user` WHERE `role_id` = ?", [$role_id]);
		foreach ($usersQuery->getResultArray() as $row) {
			$this->syncUserAccess($row['username'], $role_id);
		}
	}

	public function role_save() {
		$recid = $this->request->getPostGet('recid');
		$role_name = trim((string) $this->request->getPostGet('role_name'));
		$description = $this->request->getPostGet('description');

		if (empty($role_name)) { $this->errorOut('Role name is required!'); }

		if (empty($recid)) {
			fods_require_access(\Config\AccessCodes::USERMGMT_ADD, 'Saving');
			$query = $this->db->query("
				INSERT INTO `tbl_roles` (`role_name`, `description`, `added_by`)
				VALUES (?, ?, ?)
			", [$role_name, $description, $this->cuser]);
			$status = "Role Saved successfully";
			$color = "success";
		} else {
			fods_require_access(\Config\AccessCodes::USERMGMT_UPDATE, 'Updating');
			$query = $this->db->query("
				UPDATE `tbl_roles` SET `role_name` = ?, `description` = ? WHERE `recid` = ?
			", [$role_name, $description, $recid]);
			$status = "Role Updated successfully";
			$color = "info";
		}

		if ($query) {
			$this->successOut($status, $color, "&tab=roles");
		} else {
			$this->errorOut('An error occurred while executing the query.');
		}
	}

	public function role_toggle_active() {
		$role_id = $this->request->getPostGet('role_id');
		if (empty($role_id)) { $this->errorOut('Missing role.'); }
		fods_require_access(\Config\AccessCodes::USERMGMT_UPDATE, 'Updating');

		$query = $this->db->query("UPDATE `tbl_roles` SET `is_active` = NOT `is_active` WHERE `recid` = ?", [$role_id]);
		$this->syncUsersByRole($role_id);

		if ($query) {
			$this->successOut('Role status updated', 'success', "&tab=roles");
		} else {
			$this->errorOut('An error occurred while executing the query.');
		}
	}

	public function role_delete() {
		$role_id = $this->request->getPostGet('role_id');
		if (empty($role_id)) { $this->errorOut('Missing role.'); }
		fods_require_access(\Config\AccessCodes::USERMGMT_DELETE, 'Deleting');

		$inUseQuery = $this->db->query("SELECT COUNT(*) AS cnt FROM `myua_user` WHERE `role_id` = ?", [$role_id]);
		$inUse = (int) $inUseQuery->getRowArray()['cnt'];
		if ($inUse > 0) {
			$this->errorOut("This role is still assigned to {$inUse} user(s). Reassign them first.");
		}

		$query = $this->db->query("DELETE FROM `tbl_roles` WHERE `recid` = ?", [$role_id]);

		if ($query) {
			$this->successOut('Role deleted', 'success', "&tab=roles");
		} else {
			$this->errorOut('An error occurred while executing the query.');
		}
	}

	public function role_access_save() {
		$role_id = $this->request->getPostGet('role_id');
		$access_codes = $this->request->getPostGet('access_codes');

		if (empty($role_id)) { $this->errorOut('Missing role.'); }
		fods_require_access(\Config\AccessCodes::USERMGMT_UPDATE, 'Updating');

		$query = $this->db->query("DELETE FROM `tbl_role_access` WHERE `role_id` = ?", [$role_id]);

		if (!empty($access_codes) && is_array($access_codes)) {
			foreach ($access_codes as $code) {
				$this->db->query("
					INSERT INTO `tbl_role_access` (`role_id`, `access_code`, `added_by`)
					VALUES (?, ?, ?)
				", [$role_id, $code, $this->cuser]);
			}
		}

		$this->syncUsersByRole($role_id);

		if ($query) {
			$this->successOut('Role permissions updated', 'success', "&tab=roles");
		} else {
			$this->errorOut('An error occurred while executing the query.');
		}
	}

} //end main class
