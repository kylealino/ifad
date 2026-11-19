<?php
namespace App\Models;
use CodeIgniter\Model;

// Record history for every module (tbl_record_history). Disbursement keeps its
// own tbl_disbursement_history. Each module calls log() after a change that
// succeeded; myhistory?module=&recid= reads it back through for_record().
class MyHistoryModel extends Model
{

    protected $db;

	// Module key => label and the View codes that may read its history
	const MODULES = [
		'budget' => ['label' => 'Budget allotment', 'view' => [\Config\AccessCodes::BUDGET_VIEW, \Config\AccessCodes::BUDGETAPPROVAL_VIEW]],
		'ors' => ['label' => 'ORS', 'view' => [\Config\AccessCodes::ORS_VIEW, \Config\AccessCodes::ORSAPPROVAL_VIEW]],
		'burs' => ['label' => 'BURS', 'view' => [\Config\AccessCodes::BURS_VIEW, \Config\AccessCodes::BURSAPPROVAL_VIEW]],
		'saob' => ['label' => 'SAOB allotment', 'view' => [\Config\AccessCodes::SAOB_VIEW]],
		'ppmp' => ['label' => 'PPMP', 'view' => [\Config\AccessCodes::PPMP_VIEW]],
		'pr' => ['label' => 'Purchase request', 'view' => [\Config\AccessCodes::PR_VIEW, \Config\AccessCodes::PRAPPROVAL_VIEW]],
		'po' => ['label' => 'Purchase order', 'view' => [\Config\AccessCodes::PO_VIEW, \Config\AccessCodes::POAPPROVAL_VIEW]],
		'abstract' => ['label' => 'Abstract', 'view' => [\Config\AccessCodes::ABSTRACT_VIEW]],
		'lddapada' => ['label' => 'LDDAP-ADA', 'view' => [\Config\AccessCodes::LDDAPADA_VIEW, \Config\AccessCodes::LDDAPADAAPPROVAL_VIEW]],
		'ada' => ['label' => 'ADA', 'view' => [\Config\AccessCodes::ADA_VIEW, \Config\AccessCodes::ADAAPPROVAL_VIEW]],
		'rci' => ['label' => 'RCI', 'view' => [\Config\AccessCodes::RCI_VIEW]],
		// read from its own tbl_disbursement_history (see for_record)
		'dv' => ['label' => 'Disbursement voucher', 'view' => [\Config\AccessCodes::DISBURSEMENT_VIEW, \Config\AccessCodes::DISBURSEMENTAPPROVAL_VIEW]],
		'payee' => ['label' => 'Payee', 'view' => [\Config\AccessCodes::PAYEE_VIEW]],
		'supplies' => ['label' => 'Supply item', 'view' => [\Config\AccessCodes::SUPPLIES_VIEW]],
		'project' => ['label' => 'Project', 'view' => [\Config\AccessCodes::PROJECT_VIEW]],
		'uacs' => ['label' => 'UACS', 'view' => [\Config\AccessCodes::UACS_VIEW]],
		'user' => ['label' => 'User', 'view' => [\Config\AccessCodes::USERMGMT_VIEW]],
		'role' => ['label' => 'Role', 'view' => [\Config\AccessCodes::USERMGMT_VIEW]],
		'approvermap' => ['label' => 'Approver', 'view' => [\Config\AccessCodes::APPROVERMAP_VIEW]],
	];

	// Columns never listed as changes (the approval state is logged as its own actions)
	const IGNORED = ['recid', 'added_at', 'added_by', 'added_on', 'updated_at', 'updated_by', 'created_at', 'created_by', 'hash_password', 'approver'];
	const IGNORED_PATTERN = '/^(is_pending|is_approved\w*|is_disapproved\w*|cert[a-d]_(approver|remarks))$/';

    public function __construct(){
        parent::__construct();
        $this->session = session();
        $this->request = \Config\Services::request();
		$this->db = \Config\Database::connect();
		$this->cuser = $this->session->get('__xsys_myuserzicas__');

    }

	// The record id an approver mapping row is logged under: the person, not a row
	public static function person_id($name) {
		return md5(mb_strtolower(trim((string) $name)));
	}

	// One header row as it is now (to compare before / after an update)
	public static function row($table, $recid) {
		return \Config\Database::connect()->query("SELECT * FROM `{$table}` WHERE `recid` = ?", [$recid])->getRowArray() ?: [];
	}

	// Fields that differ between two rows of the same table: [{field, old, new}]
	public static function diff(array $old, array $new) {
		$out = [];
		foreach ($new as $field => $value) {
			if (in_array($field, self::IGNORED, true) || preg_match(self::IGNORED_PATTERN, $field) || !array_key_exists($field, $old)) {
				continue;
			}
			$a = trim((string) $old[$field]);
			$b = trim((string) $value);
			// 1500 and 1500.00 are the same amount
			if ($a === $b || (is_numeric($a) && is_numeric($b) && (float) $a == (float) $b)) {
				continue;
			}
			$out[] = ['field' => $field, 'old' => $a, 'new' => $b];
		}
		return $out;
	}

	// Log one action on a record (never stops the save it follows)
	public static function log($module, $record_id, $action_type, $remarks = '', $changes = null, $ref_no = '') {
		try {
			$request = \Config\Services::request();
			$username = (string) session('__xsys_myuserzicas__');
			$user = fods_current_user();
			\Config\Database::connect()->query("
				INSERT INTO tbl_record_history (
					`module`,
					`record_id`,
					`ref_no`,
					`action_type`,
					`remarks`,
					`field_changes`,
					`action_by`,
					`action_by_name`,
					`ip_address`,
					`user_agent`
				) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
			", [
				$module,
				(string) $record_id,
				mb_substr((string) $ref_no, 0, 150),
				$action_type,
				(string) $remarks,
				$changes ? json_encode(array_values($changes)) : null,
				$username,
				mb_substr((string) ($user['full_name'] ?? $username), 0, 150),
				(string) $request->getIPAddress(),
				mb_substr((string) $request->getUserAgent()->getAgentString(), 0, 255),
			]);
		} catch (\Throwable $e) {
			log_message('error', 'Record history not written: ' . $e->getMessage());
		}
	}

	// Where each module's records live, to fill in the history of records saved before
	// the audit trail started: [table, reference no. column, created by, created at,
	// decisions]. Decisions: 'single' (is_approved / is_disapproved, approver, remarks)
	// or the boxes with the verb their approval is logged with (cert{x}_approver / _remarks).
	const SOURCES = [
		'budget' => ['tbl_budget_hd', 'trxno', 'added_by', 'added_at', 'single'],
		'ors' => ['tbl_ors_hd', 'serialno', 'added_by', 'added_at', ['a' => 'CERTIFIED', 'b' => 'CERTIFIED']],
		'burs' => ['tbl_burs_hd', 'serialno', 'added_by', 'added_at', ['a' => 'CERTIFIED', 'b' => 'CERTIFIED']],
		'saob' => ['tbl_saob_hd', 'trxno', 'added_by', 'added_at', null],
		'ppmp' => ['tbl_ppmp_hd', 'ppmpno', 'added_by', 'added_at', null],
		'pr' => ['tbl_pr_hd', 'prno', 'added_by', 'added_at', ['a' => 'APPROVED', 'b' => 'APPROVED']],
		'po' => ['tbl_po_hd', 'pono', 'added_by', 'added_at', ['a' => 'APPROVED', 'b' => 'APPROVED']],
		'abstract' => ['tbl_abstract_hd', 'transaction_no', 'added_by', 'added_at', null],
		'lddapada' => ['tbl_lddapada_hd', 'lddapadano', 'added_by', 'added_at', 'single'],
		'ada' => ['tbl_ada', 'adano', 'added_by', 'added_at', 'single'],
		'rci' => ['tbl_rci_hd', 'reportno', 'created_by', 'created_at', null],
		'dv' => ['tbl_disbursement_hd', 'dvno', 'added_by', 'added_at', ['a' => 'APPROVED', 'c' => 'APPROVED', 'd' => 'APPROVED']],
		'payee' => ['tbl_payee', 'payee_name', 'added_by', 'added_on', null],
		'supplies' => ['mst_products', 'product_code', 'added_by', 'added_at', null],
		'project' => ['tbl_reference_project', 'project_title', 'added_by', 'added_on', null],
		'uacs' => ['mst_uacs', 'uacs_code', 'added_by', 'added_at', null],
		'user' => ['myua_user', 'username', 'added_by', 'added_at', null],
		'role' => ['tbl_roles', 'role_name', 'added_by', 'added_at', null],
	];

	// A record's history, newest first, plus what the record itself shows for anything
	// done before the audit trail started (see earlier())
	public function for_record($module, $record_id) {
		$logged = $this->logged($module, $record_id);
		return array_merge($logged, $this->earlier($module, $record_id, $logged));
	}

	// Entries the record itself shows but the history does not have (it was created, or
	// decided, before the audit trail started): its creation, from its created by / at,
	// and each decision still on it, from its approver and remarks. Decisions keep no
	// date, so they come with added_at '' (shown as "Date not recorded"), before the
	// creation, which is the oldest entry. Each is marked is_earlier = 1.
	private function earlier($module, $record_id, array $logged) {
		$src = self::SOURCES[$module] ?? null;
		if (!$src) { return []; }
		[$table, $refCol, $byCol, $atCol, $decisions] = $src;
		try {
			$hd = $this->db->query("SELECT * FROM `{$table}` WHERE `recid` = ?", [(int) $record_id])->getRowArray();
		} catch (\Throwable $e) {
			return [];
		}
		if (!$hd) { return []; }

		$types = array_map(fn ($h) => strtoupper((string) $h['action_type']), $logged);
		$has = fn ($pattern) => (bool) preg_grep($pattern, $types);
		$ref = (string) ($hd[$refCol] ?? '');
		$names = [];
		$name = function ($username) use (&$names) {
			$username = trim((string) $username);
			if ($username === '') { return ''; }
			if (!isset($names[$username])) {
				$u = $this->db->query("SELECT `full_name` FROM myua_user WHERE `username` = ? LIMIT 1", [$username])->getRowArray();
				$names[$username] = (string) ($u['full_name'] ?? '');
			}
			return $names[$username];
		};
		$entry = fn ($type, $by, $at, $remarks = '') => [
			'action_type' => $type, 'ref_no' => $ref, 'remarks' => (string) $remarks, 'field_changes' => null,
			'action_by' => trim((string) $by), 'action_by_name' => $name($by), 'added_at' => (string) $at, 'is_earlier' => 1,
		];

		$out = [];
		// decisions still on the record that the history has no entry for
		if ($decisions === 'single') {
			if (!$has('/^(APPROVED|DISAPPROVED)$/')) {
				if ((string) ($hd['is_approved'] ?? '0') === '1') {
					$out[] = $entry('APPROVED', $hd['approver'] ?? '', '', $hd['remarks'] ?? '');
				} elseif ((string) ($hd['is_disapproved'] ?? '0') === '1') {
					$out[] = $entry('DISAPPROVED', $hd['approver'] ?? '', '', $hd['remarks'] ?? '');
				}
			}
		} elseif (is_array($decisions)) {
			// newest step first, as the list is newest first
			foreach (array_reverse($decisions, true) as $box => $verb) {
				$B = strtoupper($box);
				if ($has('/^(APPROVED|CERTIFIED|DISAPPROVED)_(BOX_|CERT)' . $B . '$/')) { continue; }
				if ((string) ($hd['is_approved_cert' . $box] ?? '0') === '1') {
					$out[] = $entry($verb . '_BOX_' . $B, $hd['cert' . $box . '_approver'] ?? '', '', $hd['cert' . $box . '_remarks'] ?? '');
				} elseif ((string) ($hd['is_disapproved_cert' . $box] ?? '0') === '1') {
					$out[] = $entry('DISAPPROVED_BOX_' . $B, $hd['cert' . $box . '_approver'] ?? '', '', $hd['cert' . $box . '_remarks'] ?? '');
				}
			}
		}
		// its creation
		$at = (string) ($hd[$atCol] ?? '');
		if (!$has('/^(CREATED|ADDED)$/') && $at !== '' && strpos($at, '0000') !== 0) {
			$out[] = $entry('CREATED', $hd[$byCol] ?? '', $at);
		}
		return $out;
	}

	// The entries logged for a record, newest first (Disbursement: its own table, in the same shape)
	private function logged($module, $record_id) {
		if ($module === 'dv') {
			return $this->db->query("
				SELECT IF(h.`action_type` = 'ADDED', 'CREATED', h.`action_type`) AS action_type, IFNULL(h.`serialno`, '') AS ref_no,
					h.`remarks`, h.`field_changes`, h.`action_by`, IFNULL(u.`full_name`, '') AS action_by_name, h.`date_created` AS added_at
				FROM tbl_disbursement_history h
				LEFT JOIN myua_user u ON u.`username` = h.`action_by`
				WHERE h.`transaction_id` = ?
				ORDER BY h.`date_created` DESC, h.`recid` DESC
			", [(int) $record_id])->getResultArray();
		}
		return $this->db->query("
			SELECT `action_type`, `ref_no`, `remarks`, `field_changes`, `action_by`, `action_by_name`, `added_at`
			FROM tbl_record_history
			WHERE `module` = ? AND `record_id` = ?
			ORDER BY `added_at` DESC, `recid` DESC
		", [$module, (string) $record_id])->getResultArray();
	}

} //end main class
?>
