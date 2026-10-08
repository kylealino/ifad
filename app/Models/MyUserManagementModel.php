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

		if (empty($full_name)) { $this->errorOut('Full name is required!'); }
		if (empty($division)) { $this->errorOut('Division is required!'); }
		if (empty($section)) { $this->errorOut('Section is required!'); }
		if (empty($position)) { $this->errorOut('Position is required!'); }
		if (empty($username)) { $this->errorOut('Username is required!'); }
		if (empty($recid) && empty($password)) { $this->errorOut('Password is required for a new user!'); }

		$role_id = !empty($role_id) ? $role_id : null;
		// Signatory designations (cert_tag, ada_tag, is_ppmp_signatory) are not on
		// the user form: a new user starts with none, and saving a user leaves
		// them as they are in the database

		if (empty($recid)) {
			fods_require_access(\Config\AccessCodes::USERMGMT_ADD, 'Saving');
			$hash_password = hash('sha512', $password);
			// the user and their access are saved together, in one commit
			$this->db->transStart();
			$query = $this->db->query("
				INSERT INTO `myua_user`(
					`username`, `hash_password`, `full_name`, `division`, `section`,
					`position`, `role_id`, `cert_tag`, `ada_tag`, `is_ppmp_signatory`,
					`added_at`, `added_by`
				)
				VALUES(?, ?, ?, ?, ?, ?, ?, 0, 0, 0, NOW(), ?)
			", [
				$username, $hash_password, $full_name, $division, $section,
				$position, $role_id,
				$this->cuser
			]);

			$this->syncUserAccess($username, $role_id);
			$this->db->transComplete();

			$status = "User Saved successfully";
			$color = "success";
		} else {
			fods_require_access(\Config\AccessCodes::USERMGMT_UPDATE, 'Updating');
			$this->db->transStart();
			if (!empty($password)) {
				$hash_password = hash('sha512', $password);
				$query = $this->db->query("
					UPDATE `myua_user`
					SET `username` = ?, `hash_password` = ?, `full_name` = ?, `division` = ?,
						`section` = ?, `position` = ?, `role_id` = ?
					WHERE `recid` = ?
				", [
					$username, $hash_password, $full_name, $division, $section,
					$position, $role_id,
					$recid
				]);
			} else {
				$query = $this->db->query("
					UPDATE `myua_user`
					SET `username` = ?, `full_name` = ?, `division` = ?,
						`section` = ?, `position` = ?, `role_id` = ?
					WHERE `recid` = ?
				", [
					$username, $full_name, $division, $section, $position,
					$role_id,
					$recid
				]);
			}

			$this->syncUserAccess($username, $role_id);
			$this->db->transComplete();

			$status = "User Updated successfully";
			$color = "info";
		}

		if ($query && $this->db->transStatus()) {
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

		// All of the role's codes in one statement (an inactive role grants nothing)
		$this->db->query("
			INSERT INTO `tbl_user_access` (`username`, `access_code`, `is_active`, `added_by`)
			SELECT ?, ra.`access_code`, 1, ?
			FROM `tbl_role_access` ra
			JOIN `tbl_roles` r ON r.`recid` = ra.`role_id` AND r.`is_active` = 1
			WHERE ra.`role_id` = ?
		", [$username, $this->cuser, $role_id]);
	}

	/**
	 * Re-syncs every user currently assigned to a given role. Used after a
	 * role's permission set changes, or a role is activated/deactivated.
	 * Two statements for the whole role instead of a delete + one insert per
	 * code per user, which took minutes on MySQL 8 (each commit is flushed).
	 */
	public function syncUsersByRole($role_id) {
		$this->db->query("
			DELETE ua FROM `tbl_user_access` ua
			JOIN `myua_user` u ON u.`username` = ua.`username`
			WHERE u.`role_id` = ?
		", [$role_id]);

		// DISTINCT: a username listed twice in myua_user still gets each code once
		$this->db->query("
			INSERT INTO `tbl_user_access` (`username`, `access_code`, `is_active`, `added_by`)
			SELECT DISTINCT u.`username`, ra.`access_code`, 1, ?
			FROM `myua_user` u
			JOIN `tbl_roles` r ON r.`recid` = u.`role_id` AND r.`is_active` = 1
			JOIN `tbl_role_access` ra ON ra.`role_id` = u.`role_id`
			WHERE u.`role_id` = ?
		", [$this->cuser, $role_id]);
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

		$this->db->transStart();
		$this->db->query("UPDATE `tbl_roles` SET `is_active` = NOT `is_active` WHERE `recid` = ?", [$role_id]);
		$this->syncUsersByRole($role_id);
		$this->db->transComplete();

		if ($this->db->transStatus()) {
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

		// One transaction (one commit) for the role's codes and its users' access
		$this->db->transStart();

		$this->db->query("DELETE FROM `tbl_role_access` WHERE `role_id` = ?", [$role_id]);

		$codes = (!empty($access_codes) && is_array($access_codes)) ? array_values(array_unique(array_map('strval', $access_codes))) : [];
		if (!empty($codes)) {
			// all codes in one multi-row insert
			$params = [];
			foreach ($codes as $code) {
				array_push($params, $role_id, $code, $this->cuser);
			}
			$this->db->query("
				INSERT INTO `tbl_role_access` (`role_id`, `access_code`, `added_by`)
				VALUES " . implode(', ', array_fill(0, count($codes), '(?, ?, ?)')),
				$params
			);
		}

		$this->syncUsersByRole($role_id);

		$this->db->transComplete();

		if ($this->db->transStatus()) {
			$this->successOut('Role permissions updated', 'success', "&tab=roles");
		} else {
			$this->errorOut('An error occurred while executing the query.');
		}
	}

} //end main class
