<?php
namespace App\Models\Sorf;
use CodeIgniter\Model;

/**
 * Who may use SORF (IFAD Portal system 2), set from the portal's User
 * Management. A portal account (FODS myua_user) uses SORF through its member
 * row in the SORF database (sorf.myua_user, same username): its SORF role, and
 * is_active = whether SORF is switched on for them. The member row's password
 * column is not used: everyone signs in to the portal.
 */
class SorfMemberModel extends Model
{
	protected $db;
	protected $cuser;

	// Every table / column of the SORF database that holds a username
	private const USERNAME_COLUMNS = [
		'myua_user' => ['username', 'added_by'],
		'tbl_division' => ['added_by'],
		'tbl_document_type' => ['added_by'],
		'tbl_employee' => ['added_by'],
		'tbl_request_attachment' => ['added_by'],
		'tbl_request_doc' => ['added_by', 'released_by'],
		'tbl_request_dt' => ['added_by'],
		'tbl_request_hd' => ['added_by', 'handled_by', 'noted_by_user'],
		'tbl_request_lto' => ['added_by'],
		'tbl_request_lto_expense' => ['added_by'],
		'tbl_request_msg' => ['added_by'],
		'tbl_request_seen' => ['added_by', 'username'],
		'tbl_roles' => ['added_by'],
		'tbl_role_permission' => ['added_by'],
		'tbl_section' => ['added_by'],
	];

	public function __construct(){
		parent::__construct();
		$this->db = \Config\Database::connect('sorf');
		$this->cuser = session('__xsys_myuserzicas__');
	}

	//SORF ROLES - for the User Management form
	public function roles() {
		return $this->db->query("SELECT `recid`, `role_name`, `is_active` FROM tbl_roles ORDER BY `is_active` DESC, `role_name` ASC")->getResultArray();
	}

	//MEMBERS - username => SORF role of everyone SORF is switched on for
	public function member_roles() {
		$out = [];
		foreach ($this->db->query("
			SELECT u.`username`, u.`role_id`, COALESCE(r.`role_name`, '') role_name, COALESCE(r.`is_active`, 0) role_active
			FROM myua_user u LEFT JOIN tbl_roles r ON r.`recid` = u.`role_id`
			WHERE u.`is_active` = 1
		")->getResultArray() as $m) {
			$out[strtolower($m['username'])] = ['role_id' => (int) $m['role_id'], 'role_name' => $m['role_name'], 'role_active' => (int) $m['role_active']];
		}
		return $out;
	}

	/**
	 * Give an account a SORF role ($roleId > 0) or switch SORF off for it (0).
	 * Copies the name and position from the portal account. Kept only if
	 * someone can still manage SORF roles afterwards. Returns '' or the error.
	 */
	public function set_role(string $username, string $fullName, string $position, int $roleId) {
		$member = $this->db->query("SELECT `recid`, `role_id`, `is_active` FROM myua_user WHERE `username` = ? LIMIT 1", [$username])->getRowArray();

		if ($roleId > 0) {
			$role = $this->db->query("SELECT `recid`, `is_active` FROM tbl_roles WHERE `recid` = ?", [$roleId])->getRowArray();
			if (!$role) {
				return 'Choose a SORF role from the list.';
			}
			$unchanged = $member && (int) $member['role_id'] === $roleId && (int) $member['is_active'] === 1;
			if (!$unchanged && (int) $role['is_active'] !== 1) {
				return 'That SORF role is switched off. Choose another one.';
			}
		} elseif (!$member || (int) $member['is_active'] === 0) {
			return '';   // no SORF access before, none now
		}

		return model('App\Models\Sorf\MyRoleModel')->guarded(function () use ($member, $username, $fullName, $position, $roleId) {
			if ($roleId === 0) {
				$this->db->query("UPDATE myua_user SET `is_active` = 0 WHERE `recid` = ?", [$member['recid']]);
			} elseif ($member) {
				$this->db->query("
					UPDATE myua_user SET `role_id` = ?, `is_active` = 1, `full_name` = ?, `position` = ? WHERE `recid` = ?
				", [$roleId, $fullName, $position, $member['recid']]);
			} else {
				$this->db->query("
					INSERT INTO myua_user (`username`, `hash_password`, `full_name`, `position`, `role_id`, `is_active`, `added_by`)
					VALUES (?, '', ?, ?, ?, 1, ?)
				", [$username, $fullName, $position, $roleId, (string) $this->cuser]);
			}
		});
	}

	/**
	 * A portal account's username changed: file everything it did in SORF
	 * under the new one. Returns '' or the error.
	 */
	public function rename(string $old, string $new) {
		if ($old === '' || $new === '' || $old === $new) {
			return '';
		}
		$taken = $this->db->query("SELECT `recid` FROM myua_user WHERE `username` = ? LIMIT 1", [$new])->getRowArray();
		$mine = $this->db->query("SELECT `recid` FROM myua_user WHERE `username` = ? LIMIT 1", [$old])->getRowArray();
		if ($taken && $mine && strcasecmp($old, $new) !== 0) {
			return "The Document Request System already has an account named {$new}.";
		}

		$this->db->transStart();
		foreach (self::USERNAME_COLUMNS as $table => $columns) {
			foreach ($columns as $column) {
				$this->db->query("UPDATE `{$table}` SET `{$column}` = ? WHERE `{$column}` = ?", [$new, $old]);
			}
		}
		$this->db->transComplete();

		return $this->db->transStatus() ? '' : 'The username was not changed in the Document Request System.';
	}

} //end main class
