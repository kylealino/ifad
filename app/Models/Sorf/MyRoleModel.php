<?php
namespace App\Models\Sorf;
use CodeIgniter\Model;
use Config\Sorf\Permissions;

class MyRoleModel extends Model
{

	protected $db;
	protected $request;
	protected $cuser;

	public function __construct(){
		parent::__construct();
		$this->request = \Config\Services::request();
		$this->db = \Config\Database::connect('sorf');
		$this->cuser = session('__xsys_myuserzicas__');
	}

	//ROLES - the one new self-registered accounts get (null = registration is closed)
	public function default_role() {
		return $this->db->query("
			SELECT `recid`, `role_name` FROM tbl_roles WHERE `is_default` = 1 AND `is_active` = 1 ORDER BY `recid` ASC LIMIT 1
		")->getRowArray();
	}

	//ROLES - LIST with their permissions and how many accounts have each
	public function role_list() {
		$roles = $this->db->query("
			SELECT r.`recid`, r.`role_name`, r.`description`, r.`is_default`, r.`is_active`,
				COUNT(u.`recid`) users, COALESCE(SUM(u.`is_active` = 1), 0) active_users
			FROM tbl_roles r
			LEFT JOIN myua_user u ON u.`role_id` = r.`recid`
			GROUP BY r.`recid`, r.`role_name`, r.`description`, r.`is_default`, r.`is_active`
			ORDER BY r.`is_active` DESC, r.`role_name` ASC
		")->getResultArray();

		$perms = [];
		foreach ($this->db->query("SELECT `role_id`, `perm_code` FROM tbl_role_permission")->getResultArray() as $p) {
			$perms[(int) $p['role_id']][] = $p['perm_code'];
		}
		$codes = config(\Config\Sorf\Permissions::class)->codes();
		foreach ($roles as &$r) {
			foreach (['recid', 'is_default', 'is_active', 'users', 'active_users'] as $key) {
				$r[$key] = (int) $r[$key];
			}
			// in the order of the Roles page
			$r['permissions'] = array_values(array_intersect($codes, $perms[$r['recid']] ?? []));
			$r['assignable'] = $this->role_within_mine($r['permissions']);
		}
		unset($r);
		return $roles;
	}

	public function role_get($recid) {
		foreach ($this->role_list() as $role) {
			if ($role['recid'] === (int) $recid) {
				return $role;
			}
		}
		return null;
	}

	// A user manager only hands out (and only manages people with) roles that
	// do no more than their own; whoever manages roles may already do anything.
	public function role_within_mine(array $permissions) {
		$mine = drf_current_user()['permissions'];
		return in_array(Permissions::ADMIN_ROLES, $mine, true) || array_diff($permissions, $mine) === [];
	}

	// Active accounts whose active role may manage roles: never let this reach 0
	public function admins_left() {
		$row = $this->db->query("
			SELECT COUNT(*) total FROM myua_user u
			JOIN tbl_roles r ON r.`recid` = u.`role_id` AND r.`is_active` = 1
			JOIN tbl_role_permission p ON p.`role_id` = r.`recid` AND p.`perm_code` = ?
			WHERE u.`is_active` = 1
		", [Permissions::ADMIN_ROLES])->getRowArray();
		return (int) ($row['total'] ?? 0);
	}

	/**
	 * Run $change in a transaction and keep it only if someone can still
	 * manage roles afterwards. Returns '' or the error to show.
	 */
	public function guarded(callable $change) {
		$this->db->transBegin();
		try {
			$change();
		} catch (\Throwable $e) {
			$this->db->transRollback();
			log_message('error', 'Role/user change failed: ' . $e->getMessage());
			return 'The change was not saved. Please try again.';
		}
		if ($this->db->transStatus() === false) {
			$this->db->transRollback();
			return 'The change was not saved. Please try again.';
		}
		if ($this->admins_left() === 0) {
			$this->db->transRollback();
			return 'At least one active account must keep a role that can manage roles and permissions.';
		}
		$this->db->transCommit();
		return '';
	}

	//ROLES - SAVE (new, or a change): name, description, on/off, default, permissions
	public function role_save() {
		$recid = (int) $this->request->getPost('recid');
		$name = trim((string) $this->request->getPost('role_name'));
		$description = trim((string) $this->request->getPost('description'));
		$active = $this->request->getPost('is_active') ? 1 : 0;
		// nobody registers themselves in the portal: no role is given to new accounts
		$default = 0;
		$perms = config(\Config\Sorf\Permissions::class)->normalize((array) ($this->request->getPost('perms') ?? []));

		$old = null;
		if ($recid > 0) {
			$old = $this->role_get($recid);
			if ($old === null) {
				return ['error' => 'That role was not found.'];
			}
		}
		if ($name === '' || mb_strlen($name) > 100) {
			return ['error' => 'Role name is required (100 characters max).'];
		}
		if (mb_strlen($description) > 255) {
			return ['error' => 'Description is too long (255 characters max).'];
		}
		if ($this->db->query("SELECT `recid` FROM tbl_roles WHERE `role_name` = ? AND `recid` <> ? LIMIT 1", [$name, $recid])->getRowArray()) {
			return ['error' => 'Another role already has that name.'];
		}
		if ($default && !$active) {
			return ['error' => 'The role for new accounts has to be switched on.'];
		}
		// the role you are signed in with: keep your own way back to this page
		if ($recid > 0 && $recid === drf_current_user()['role_id']) {
			if (!$active) {
				return ['error' => 'You cannot switch off your own role.'];
			}
			if (!in_array(Permissions::ADMIN_ROLES, $perms, true)) {
				return ['error' => 'Your own role has to keep "Manage roles and permissions", or you would lock yourself out of this page.'];
			}
		}

		$error = $this->guarded(function () use (&$recid, $old, $name, $description, $active, $default, $perms) {
			if ($old === null) {
				$this->db->query("
					INSERT INTO tbl_roles (`role_name`, `description`, `is_default`, `is_active`, `added_by`) VALUES (?, ?, ?, ?, ?)
				", [$name, $description, $default, $active, $this->cuser]);
				$recid = (int) $this->db->insertID();
			} else {
				$this->db->query("
					UPDATE tbl_roles SET `role_name` = ?, `description` = ?, `is_default` = ?, `is_active` = ? WHERE `recid` = ?
				", [$name, $description, $default, $active, $recid]);
			}
			// only one role is given to new accounts
			if ($default) {
				$this->db->query("UPDATE tbl_roles SET `is_default` = 0 WHERE `recid` <> ?", [$recid]);
			}
			$this->db->query("DELETE FROM tbl_role_permission WHERE `role_id` = ?", [$recid]);
			foreach ($perms as $code) {
				$this->db->query("
					INSERT INTO tbl_role_permission (`role_id`, `perm_code`, `added_by`) VALUES (?, ?, ?)
				", [$recid, $code, $this->cuser]);
			}
		});
		if ($error !== '') {
			return ['error' => $error];
		}
		return ['ok' => true, 'recid' => $recid, 'message' => $old === null ? 'Role created.' : 'Role saved.'];
	}

	//ROLES - DELETE one nobody has (switching it off keeps it for later)
	public function role_delete() {
		$role = $this->role_get($this->request->getPost('recid'));
		if ($role === null) {
			return ['error' => 'That role was not found.'];
		}
		if ($role['users'] > 0) {
			return ['error' => $role['users'] . ($role['users'] === 1 ? ' account still has' : ' accounts still have') . ' this role. Give them another role first.'];
		}

		$this->db->query("DELETE FROM tbl_roles WHERE `recid` = ?", [$role['recid']]);
		return ['ok' => true, 'message' => 'Role deleted.'];
	}

} //end main class
