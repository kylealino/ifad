<?php
namespace App\Models;
use CodeIgniter\Model;

class MyApproverMapModel extends Model
{

    protected $db;

	// Every box that names an approver, per module. A "pick" box offers its
	// mapped people on the entry form (one can be the default for a new record);
	// an "amount" box is not picked: the mapped row with the highest
	// "from amount" at or below the record's amount signs it.
	const BOXES = [
		'ors' => ['label' => 'ORS', 'boxes' => [
			'a' => ['label' => 'Box A · Requesting office', 'mode' => 'pick'],
			'b' => ['label' => 'Box B · Budget', 'mode' => 'pick'],
		]],
		'burs' => ['label' => 'BURS', 'boxes' => [
			'a' => ['label' => 'Box A · Requesting office', 'mode' => 'pick'],
			'b' => ['label' => 'Box B · Budget', 'mode' => 'pick'],
		]],
		'dv' => ['label' => 'Disbursement', 'boxes' => [
			'a' => ['label' => 'Box A · Supervisor', 'mode' => 'pick'],
			'c' => ['label' => 'Box C · Accounting', 'mode' => 'pick'],
			// picked on the DV, from the people whose amount range covers its net amount
			'd' => ['label' => 'Box D · Approved for payment', 'mode' => 'pick', 'range' => 'net amount'],
		]],
		'lddapada' => ['label' => 'LDDAP-ADA', 'boxes' => [
			'certified_by' => ['label' => 'Certified Correct', 'mode' => 'pick'],
			'approved_by' => ['label' => 'Approved', 'mode' => 'pick'],
			'ada_approver' => ['label' => 'Agency Authorized Signatory 1', 'mode' => 'pick'],
			'ada_approver2' => ['label' => 'Agency Authorized Signatory 2', 'mode' => 'pick'],
		]],
		'ada' => ['label' => 'ADA', 'boxes' => [
			'a' => ['label' => 'Signatory 1', 'mode' => 'pick'],
			'b' => ['label' => 'Signatory 2', 'mode' => 'pick'],
		]],
		'pr' => ['label' => 'Purchase Request', 'boxes' => [
			'a' => ['label' => 'Box A · Requesting office', 'mode' => 'pick'],
			'b' => ['label' => 'Box B · Approval', 'mode' => 'amount', 'amount' => 'estimated cost'],
		]],
		'po' => ['label' => 'Purchase Order', 'boxes' => [
			'a' => ['label' => 'Box A · Accounting', 'mode' => 'pick'],
			'b' => ['label' => 'Box B · Approval', 'mode' => 'amount', 'amount' => 'PO total'],
		]],
	];

	// Active rows per module|box, read once per request
	private static $cache = [];

    public function __construct(){
        parent::__construct();
        $this->session = session();
        $this->request = \Config\Services::request();
		$this->db = \Config\Database::connect();
		$this->cuser = $this->session->get('__xsys_myuserzicas__');

    }

	//APPROVER MAP - LOOKUPS (used by the modules that name approvers)

	public static function mode_of($module, $box) {
		return self::BOXES[$module]['boxes'][$box]['mode'] ?? '';
	}

	// A pick box limited by an amount: each person has a from / up to range
	public static function has_range($module, $box) {
		return !empty(self::BOXES[$module]['boxes'][$box]['range']);
	}

	// Whether a range (min, max; null max = no upper limit) covers an amount
	public static function in_range($min, $max, $amount) {
		$amount = round((float) $amount, 2);
		return $amount >= (float) $min && ($max === null || $max === '' || $amount <= (float) $max);
	}

	// The mapped row of a name on a box (null when not mapped there)
	public static function row_of($module, $box, $name) {
		$name = mb_strtolower(trim((string) $name));
		foreach (self::rows($module, $box) as $r) {
			if (mb_strtolower(trim($r['full_name'])) === $name) {
				return $r;
			}
		}
		return null;
	}

	// Active rows of a box: pick boxes by name, amount boxes by "from amount"
	public static function rows($module, $box) {
		$key = $module . '|' . $box;
		if (!isset(self::$cache[$key])) {
			$order = self::mode_of($module, $box) === 'amount' ? '`min_amount` ASC' : '`full_name` ASC';
			self::$cache[$key] = \Config\Database::connect()->query("
				SELECT `full_name`, `position`, `min_amount`, `max_amount`, `is_default`
				FROM tbl_approver_map
				WHERE `module` = ? AND `box` = ? AND `is_active` = 1
				ORDER BY {$order}
			", [$module, $box])->getResultArray();
		}
		return self::$cache[$key];
	}

	// The people offered on a pick box (full_name, position, is_default). There is no
	// default to set: a box with exactly one active person starts new records with
	// that person (is_default = 1); with several, the preparer picks.
	public static function options($module, $box) {
		$rows = self::rows($module, $box);
		$only = count($rows) === 1;
		$range = self::has_range($module, $box);
		return array_map(fn ($r) => [
			'full_name' => $r['full_name'],
			'position' => $r['position'],
			'is_default' => $only ? 1 : 0,
		] + ($range ? [
			// a range box: the form offers the people whose range covers the amount
			'min' => (float) $r['min_amount'],
			'max' => $r['max_amount'] === null ? null : (float) $r['max_amount'],
		] : []), $rows);
	}

	// Who a new record starts with on a pick box: ['name', 'position'] of its only
	// active person, blank when the box has none or several
	public static function default_of($module, $box) {
		$rows = self::rows($module, $box);
		if (count($rows) === 1) {
			return ['name' => $rows[0]['full_name'], 'position' => $rows[0]['position']];
		}
		return ['name' => '', 'position' => ''];
	}

	// Position mapped for a name on a box ('' when the name is not mapped there)
	public static function position_of($module, $box, $name) {
		$name = mb_strtolower(trim((string) $name));
		foreach (self::rows($module, $box) as $r) {
			if (mb_strtolower(trim($r['full_name'])) === $name) {
				return (string) $r['position'];
			}
		}
		return '';
	}

	public static function is_mapped($module, $box, $name) {
		$name = mb_strtolower(trim((string) $name));
		foreach (self::rows($module, $box) as $r) {
			if (mb_strtolower(trim($r['full_name'])) === $name) {
				return true;
			}
		}
		return false;
	}

	// The tiers of an amount box, lowest first (min, name, position); mirrored in JS
	public static function tiers($module, $box) {
		return array_map(fn ($r) => [
			'min' => (float) $r['min_amount'],
			'name' => $r['full_name'],
			'position' => $r['position'],
		], self::rows($module, $box));
	}

	// Who signs an amount box for this amount: the tier with the highest
	// "from amount" at or below it; blank when no tier covers it
	public static function for_amount($module, $box, $amount) {
		$out = ['name' => '', 'position' => ''];
		foreach (self::tiers($module, $box) as $t) {
			if ((float) $amount >= $t['min']) {
				$out = ['name' => $t['name'], 'position' => $t['position']];
			}
		}
		return $out;
	}

	//APPROVER MAP - LIST (every mapped row; the page groups them per person)
	public function list_rows() {
		return $this->db->query("
			SELECT `recid`, `module`, `box`, `full_name`, `position`, `min_amount`, `max_amount`, `is_default`, `is_active`,
				`added_by`, `added_at`, `updated_by`, `updated_at`
			FROM tbl_approver_map
			ORDER BY `full_name`, `module`, `box`
		")->getResultArray();
	}

	// An amount box must cover every amount: its lowest active tier starts at 0.
	// Checks the box as it would be once the people in $names are replaced by
	// $min (their new "from amount"; null = they no longer sign it while active).
	// Returns the error, or '' when the box is covered.
	private function amount_gap($module, $box, array $names, $min, $max = null) {
		$rows = $this->db->query("
			SELECT `min_amount`, `max_amount` FROM tbl_approver_map
			WHERE `module` = ? AND `box` = ? AND `is_active` = 1 AND LOWER(TRIM(`full_name`)) NOT IN ?
		", [$module, $box, $names])->getResultArray();
		$mins = array_map(fn ($r) => (float) $r['min_amount'], $rows);
		if ($min !== null) { $mins[] = (float) $min; }
		$label = self::BOXES[$module]['label'] . ' ' . self::BOXES[$module]['boxes'][$box]['label'];
		if (empty($mins)) {
			return "{$label} needs at least one active approver!";
		}
		if (self::has_range($module, $box)) {
			// the ranges together must cover every amount from 0 with no gap
			$ranges = array_map(fn ($r) => [(float) $r['min_amount'], $r['max_amount'] === null ? INF : (float) $r['max_amount']], $rows);
			if ($min !== null) { $ranges[] = [(float) $min, $max === null ? INF : (float) $max]; }
			usort($ranges, fn ($a, $b) => $a[0] <=> $b[0]);
			$reach = -0.01;   // highest amount covered so far
			foreach ($ranges as [$lo, $hi]) {
				if ($lo > $reach + 0.01) { break; }
				$reach = max($reach, $hi);
			}
			if ($reach === INF) { return ''; }
			$from = number_format(max(0, $reach + 0.01), 2);
			$next = array_values(array_filter($ranges, fn ($r) => $r[0] > $reach + 0.01));
			$to = $next ? ' to ' . number_format($next[0][0] - 0.01, 2) : ' up';
			return "{$label}: no one approves amounts from {$from}{$to}. Adjust the amounts so every amount has an approver!";
		}
		if (min($mins) > 0) {
			return "{$label} needs an approver from 0.00, or amounts below " . number_format(min($mins), 2) . ' would have no one to approve them!';
		}
		return '';
	}

	private function fail($error) {
		echo "
		<script>
		toastr.error('{$error}', 'Oops!', {
				progressBar: true,
				closeButton: true,
				timeOut:3000,
			});
		</script>
		";
		die();
	}

	//APPROVER MAP - SAVE (one person and every box they sign)
	public function approvermap_save() {
		$orig_name = trim((string) $this->request->getPostGet('orig_name'));
		$full_name = mb_substr(trim((string) $this->request->getPostGet('full_name')), 0, 100);
		$is_active = $this->request->getPostGet('is_active') ? 1 : 0;
		$boxdata = (array) $this->request->getPostGet('boxdata');

		if ($full_name === '') {
			$this->fail('Approver name is required!');
		}

		//BOXES (each: module, box, position, from amount, default)
		$boxes = [];
		for($aa = 0; $aa < count($boxdata); $aa++){
			// module, box, position, from amount, default (unused), up to amount (range boxes; blank = no limit)
			$medata = array_pad(explode("x|x", $boxdata[$aa]), 6, '');
			$module = trim($medata[0]);
			$box = trim($medata[1]);
			$mode = self::mode_of($module, $box);
			if ($mode === '') {
				continue;
			}
			$range = self::has_range($module, $box);
			$min_amount = round((float) str_replace(',', '', $medata[3]), 2);
			$max_raw = trim(str_replace(',', '', $medata[5]));
			$max_amount = ($range && $max_raw !== '') ? round((float) $max_raw, 2) : null;
			if (($mode === 'amount' || $range) && ($min_amount < 0 || ($max_amount !== null && $max_amount < 0))) {
				$this->fail('An amount cannot be negative!');
			}
			if ($max_amount !== null && $max_amount < $min_amount) {
				$this->fail(self::BOXES[$module]['label'] . ' ' . self::BOXES[$module]['boxes'][$box]['label'] . ': the up to amount cannot be lower than the from amount!');
			}
			$boxes[$module . '|' . $box] = [
				'module' => $module,
				'box' => $box,
				'position' => mb_substr(trim($medata[2]), 0, 100),
				'min_amount' => ($mode === 'amount' || $range) ? $min_amount : 0,
				'max_amount' => $max_amount,
				// no longer set: a box's only active person is its default (see options())
				'is_default' => 0,
			];
		}
		if (empty($boxes)) {
			$this->fail('Tick at least one box this person signs!');
		}

		// one entry per person: a new name, or a rename, must not already be mapped
		$names = [mb_strtolower($full_name)];
		if ($orig_name !== '') { $names[] = mb_strtolower($orig_name); }
		if ($orig_name === '' || mb_strtolower($orig_name) !== mb_strtolower($full_name)) {
			$dup = $this->db->query("SELECT `recid` FROM tbl_approver_map WHERE LOWER(TRIM(`full_name`)) = LOWER(?) LIMIT 1", [$full_name])->getRowArray();
			if (!empty($dup)) {
				$this->fail('That person is already mapped. Edit their row instead.');
			}
		}

		// on an amount box, each "from amount" belongs to one active person
		if ($is_active) {
			foreach ($boxes as $b) {
				if (self::mode_of($b['module'], $b['box']) !== 'amount') { continue; }
				$dup = $this->db->query("
					SELECT `recid` FROM tbl_approver_map
					WHERE `module` = ? AND `box` = ? AND `is_active` = 1 AND `min_amount` = ?
					  AND LOWER(TRIM(`full_name`)) NOT IN ?
					LIMIT 1
				", [$b['module'], $b['box'], $b['min_amount'], $names])->getRowArray();
				if (!empty($dup)) {
					$label = self::BOXES[$b['module']]['label'] . ' ' . self::BOXES[$b['module']]['boxes'][$b['box']]['label'];
					$this->fail("Someone else already starts at that amount on {$label}!");
				}
			}
		}

		$existing = $orig_name === '' ? [] : $this->db->query("
			SELECT `recid`, `module`, `box` FROM tbl_approver_map WHERE LOWER(TRIM(`full_name`)) = LOWER(?)
		", [$orig_name])->getResultArray();

		// every amount box this person signs, or stops signing, must still cover
		// every amount from 0
		$touched = [];
		foreach ($boxes as $k => $b) { $touched[$k] = [$b['module'], $b['box']]; }
		foreach ($existing as $row) { $touched[$row['module'] . '|' . $row['box']] = [$row['module'], $row['box']]; }
		foreach ($touched as $k => [$module, $box]) {
			if (self::mode_of($module, $box) !== 'amount' && !self::has_range($module, $box)) { continue; }
			$min = ($is_active && isset($boxes[$k])) ? $boxes[$k]['min_amount'] : null;
			$gap = $this->amount_gap($module, $box, $names, $min, $min === null ? null : $boxes[$k]['max_amount']);
			if ($gap !== '') {
				$this->fail($gap);
			}
		}

		if (empty($existing)) {
			fods_require_access(\Config\AccessCodes::APPROVERMAP_ADD, 'Saving');
		} else {
			fods_require_access(\Config\AccessCodes::APPROVERMAP_UPDATE, 'Updating');
			foreach ($existing as $row) {
				if (!isset($boxes[$row['module'] . '|' . $row['box']])) {
					fods_require_access(\Config\AccessCodes::APPROVERMAP_DELETE, 'Deleting');
					break;
				}
			}
		}

		// the boxes they sign, for the history (before the update loop below consumes $boxes)
		$summary = implode('; ', array_map(function ($b) {
			$label = self::BOXES[$b['module']]['label'] . ' ' . self::BOXES[$b['module']]['boxes'][$b['box']]['label'];
			if (self::mode_of($b['module'], $b['box']) === 'amount') {
				$label .= ' from ' . number_format($b['min_amount'], 2);
			} elseif (self::has_range($b['module'], $b['box'])) {
				$label .= ' ' . number_format($b['min_amount'], 2) . ' to ' . ($b['max_amount'] === null ? 'any amount' : number_format($b['max_amount'], 2));
			}
			return $label;
		}, $boxes));

		$this->db->transStart();

		//UPDATING / REMOVING THE BOXES THEY ALREADY HAD
		foreach ($existing as $row) {
			$key = $row['module'] . '|' . $row['box'];
			if (!isset($boxes[$key])) {
				$this->db->query("DELETE FROM tbl_approver_map WHERE `recid` = ?", [$row['recid']]);
				continue;
			}
			$b = $boxes[$key];
			$this->db->query("
				UPDATE tbl_approver_map SET
				`full_name` = ?,
				`position` = ?,
				`min_amount` = ?,
				`max_amount` = ?,
				`is_default` = ?,
				`is_active` = ?,
				`updated_by` = ?,
				`updated_at` = NOW()
				WHERE `recid` = ?
				",
				[$full_name, $b['position'], $b['min_amount'], $b['max_amount'], $b['is_default'], $is_active, $this->cuser, $row['recid']]
			);
			unset($boxes[$key]);
		}

		//INSERTING THE NEW BOXES
		foreach ($boxes as $b) {
			$this->db->query("
				INSERT INTO `tbl_approver_map` (
					`module`,
					`box`,
					`full_name`,
					`position`,
					`min_amount`,
					`max_amount`,
					`is_default`,
					`is_active`,
					`added_by`
				) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)",
				[$b['module'], $b['box'], $full_name, $b['position'], $b['min_amount'], $b['max_amount'], $b['is_default'], $is_active, $this->cuser]
			);
		}

		$this->db->transComplete();

		if ($this->db->transStatus()) {
			$renamed = $orig_name !== '' && mb_strtolower($orig_name) !== mb_strtolower($full_name) ? 'Renamed from ' . $orig_name . '. ' : '';
			MyHistoryModel::log('approvermap', MyHistoryModel::person_id($full_name), empty($existing) ? 'CREATED' : 'UPDATED',
				$renamed . ($is_active ? '' : 'Inactive. ') . 'Signs: ' . $summary, null, $full_name);
		}
		$status = empty($existing) ? "Approver Saved successfully" : "Approver Updated successfully";
		$color = empty($existing) ? "success" : "info";

		if ($this->db->transStatus()) {
			echo "
			<script>
				toastr.$color('{$status}!', 'Well Done!', {
						progressBar: true,
						closeButton: true,
						timeOut:2000,
					});
				setTimeout(function() {
						window.location.href = 'myapprovermap?meaction=MAIN';
					}, 1000); // reload after 1 s; the toast still runs its 2.5 s (fods-flash.js carries it over)
			</script>
			";
			exit;
		} else {
			echo "<script type='text/javascript'>
					alert('An error occurred while executing the query.');
				  </script>";
			exit;
		}
	}

	//APPROVER MAP - DELETE (the person from every box)
	public function approvermap_delete() {
		$full_name = trim((string) $this->request->getPostGet('full_name'));

		fods_require_access(\Config\AccessCodes::APPROVERMAP_DELETE, 'Deleting');

		// removing them must leave each amount box they sign covered from 0
		foreach ($this->db->query("
			SELECT DISTINCT `module`, `box` FROM tbl_approver_map WHERE LOWER(TRIM(`full_name`)) = LOWER(?) AND `is_active` = 1
		", [$full_name])->getResultArray() as $row) {
			if (self::mode_of($row['module'], $row['box']) !== 'amount' && !self::has_range($row['module'], $row['box'])) { continue; }
			$gap = $this->amount_gap($row['module'], $row['box'], [mb_strtolower($full_name)], null);
			if ($gap !== '') {
				$this->fail($gap);
			}
		}

		$query = $this->db->query("DELETE FROM `tbl_approver_map` WHERE LOWER(TRIM(`full_name`)) = LOWER(?)", [$full_name]);
		if ($query) { MyHistoryModel::log('approvermap', MyHistoryModel::person_id($full_name), 'DELETED', 'Removed from every box', null, $full_name); }
		$status = "Approver removed successfully";

		if ($query) {
			echo "
			<script>
				toastr.warning('{$status}!', 'Well Done!', {
						progressBar: true,
						closeButton: true,
						timeOut:2000,
					});
				setTimeout(function() {
						window.location.href = 'myapprovermap?meaction=MAIN';
					}, 1000); // reload after 1 s; the toast still runs its 2.5 s (fods-flash.js carries it over)
			</script>
			";
			exit;
		} else {
			echo "<script type='text/javascript'>
					alert('An error occurred while executing the query.');
				  </script>";
			exit;
		}
	}

} //end main class
?>
