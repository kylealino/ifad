<?php
namespace App\Models;
use CodeIgniter\Model;

// How much of each PPMP the PRs have used, worked out live from the PRs (nothing is
// stored). A PR line uses a PPMP item when it has the same description and unit
// (case, spaces and tabs ignored), which is how LOAD-PPMP builds PR lines. PRs count
// in the order they were created; a PR takes from its PPMPs in the order they were
// picked, and from a PPMP's matching items in their order. A returned PR (disapproved
// at Box A or B) does not count. A PR line asking for more than is left is "over".
class MyPPMPUsageModel extends Model
{

    protected $db;

    public function __construct(){
        parent::__construct();
		$this->db = \Config\Database::connect();
    }

	// The key a PR line and a PPMP item are matched on
	public static function key($desc, $unit) {
		$norm = fn ($s) => mb_strtolower(trim(preg_replace('/\s+/u', ' ', (string) $s)));
		return $norm($desc) . '|' . $norm($unit);
	}

	// Runs every counted PR (but $exclude_pr) against the PPMP items:
	//   items:  ppmp_dt recid => [ppmp_id, ppmpno, key, item_desc, unit, quantity, unit_cost]
	//   prs:    pr recid => [prno, pr_date, added_at, used => [ppmp_dt recid => qty], over => [key => qty]]
	//   remaining: ppmp_dt recid => quantity left
	public function run($exclude_pr = null) {
		$items = [];
		$byPpmp = [];   // ppmpno (lower) => [ppmp_dt recid, ...]
		foreach ($this->db->query("
			SELECT d.`recid`, d.`ppmp_id`, h.`ppmpno`, d.`item_desc`, d.`size`, d.`quantity`, d.`unit_cost`
			FROM tbl_ppmp_dt d
			JOIN tbl_ppmp_hd h ON h.`recid` = d.`ppmp_id`
			WHERE TRIM(d.`item_desc`) <> ''
			ORDER BY d.`ppmp_id`, d.`recid`
		")->getResultArray() as $r) {
			$items[(int) $r['recid']] = [
				'ppmp_id' => (int) $r['ppmp_id'],
				'ppmpno' => $r['ppmpno'],
				'key' => self::key($r['item_desc'], $r['size']),
				'item_desc' => $r['item_desc'],
				'unit' => $r['size'],
				'quantity' => (int) $r['quantity'],
				'unit_cost' => (float) $r['unit_cost'],
			];
			$byPpmp[mb_strtolower(trim($r['ppmpno']))][] = (int) $r['recid'];
		}
		$remaining = array_map(fn ($i) => max(0, $i['quantity']), $items);

		// the counted PRs, oldest first, with the PPMPs they picked (in order) and their lines
		$prs = [];
		foreach ($this->db->query("
			SELECT `recid`, `prno`, `pr_date`, `added_at`
			FROM tbl_pr_hd
			WHERE `is_disapproved_certa` = 0 AND `is_disapproved_certb` = 0
			ORDER BY `added_at` ASC, `recid` ASC
		")->getResultArray() as $r) {
			if ($exclude_pr !== null && (int) $r['recid'] === (int) $exclude_pr) { continue; }
			$prs[(int) $r['recid']] = $r + ['ppmps' => [], 'lines' => [], 'used' => [], 'over' => []];
		}
		if (empty($prs)) {
			return ['items' => $items, 'prs' => [], 'remaining' => $remaining];
		}
		foreach ($this->db->query("SELECT `pr_id`, `ppmpno` FROM tbl_pr_ppmp ORDER BY `recid` ASC")->getResultArray() as $r) {
			if (isset($prs[(int) $r['pr_id']])) { $prs[(int) $r['pr_id']]['ppmps'][] = mb_strtolower(trim($r['ppmpno'])); }
		}
		foreach ($this->db->query("SELECT `pr_id`, `item_desc`, `unit`, `quantity` FROM tbl_pr_dt ORDER BY `recid` ASC")->getResultArray() as $r) {
			if (isset($prs[(int) $r['pr_id']]) && trim((string) $r['item_desc']) !== '') {
				$prs[(int) $r['pr_id']]['lines'][] = [self::key($r['item_desc'], $r['unit']), (int) $r['quantity']];
			}
		}

		foreach ($prs as $id => &$pr) {
			$ppmps = array_values(array_unique($pr['ppmps']));
			if (empty($ppmps)) { continue; }
			foreach ($pr['lines'] as [$key, $qty]) {
				$matched = false;
				foreach ($ppmps as $p) {
					foreach ($byPpmp[$p] ?? [] as $dt) {
						if ($items[$dt]['key'] !== $key) { continue; }
						$matched = true;
						$take = min($qty, $remaining[$dt]);
						if ($take > 0) {
							$remaining[$dt] -= $take;
							$pr['used'][$dt] = ($pr['used'][$dt] ?? 0) + $take;
							$qty -= $take;
						}
						if ($qty <= 0) { break 2; }
					}
				}
				// a line not on its PPMPs is the PR's own item; one asking for more than is left is over
				if ($matched && $qty > 0) {
					$pr['over'][$key] = ($pr['over'][$key] ?? 0) + $qty;
				}
			}
		}
		unset($pr);

		return ['items' => $items, 'prs' => $prs, 'remaining' => $remaining];
	}

	// One PPMP's versions: the original items, then one version per PR that used it
	// (oldest first), each with what that PR took and what is left after it
	public function versions($ppmp_id) {
		$run = $this->run();
		$mine = array_filter($run['items'], fn ($i) => $i['ppmp_id'] === (int) $ppmp_id);
		$left = array_map(fn ($i) => max(0, $i['quantity']), $mine);

		$versions = [];
		foreach ($run['prs'] as $prid => $pr) {
			$used = array_intersect_key($pr['used'], $mine);
			$over = [];
			foreach ($pr['over'] as $key => $q) {
				foreach ($mine as $i) { if ($i['key'] === $key) { $over[$key] = $q; break; } }
			}
			if (empty($used) && empty($over)) { continue; }
			foreach ($used as $dt => $q) { $left[$dt] -= $q; }
			$versions[] = [
				'pr_id' => $prid,
				'prno' => (string) $pr['prno'],
				'pr_date' => (string) $pr['pr_date'],
				'added_at' => (string) $pr['added_at'],
				'used' => (object) $used,
				'left' => (object) $left,
				'over' => array_sum($over),
				'fully_used' => array_sum($left) <= 0,
			];
		}
		$total = array_sum(array_map(fn ($i) => max(0, $i['quantity']), $mine));
		$leftNow = array_sum($left);
		return [
			'versions' => $versions,
			'used_qty' => $total - $leftNow,
			'total_qty' => $total,
			'remaining' => (object) $left,
			'fully_used' => $total > 0 && $leftNow <= 0,
		];
	}

	// Per PPMP: quantity planned and used (for the list page)
	public function summary() {
		$run = $this->run();
		$out = [];
		foreach ($run['items'] as $dt => $i) {
			$s = &$out[$i['ppmp_id']];
			$s['total_qty'] = ($s['total_qty'] ?? 0) + max(0, $i['quantity']);
			$s['used_qty'] = ($s['used_qty'] ?? 0) + (max(0, $i['quantity']) - $run['remaining'][$dt]);
			$s['used_amount'] = ($s['used_amount'] ?? 0) + (max(0, $i['quantity']) - $run['remaining'][$dt]) * $i['unit_cost'];
			unset($s);
		}
		return $out;
	}

	// What is left of the given PPMPs for a PR (its own use left out), summed per
	// description and unit, in the shape LOAD-PPMP returns
	public function remaining_items(array $ppmpnos, $pr_id = null) {
		$want = array_map(fn ($p) => mb_strtolower(trim((string) $p)), array_filter($ppmpnos, 'is_string'));
		$run = $this->run($pr_id ?: null);
		$sum = [];
		foreach ($run['items'] as $dt => $i) {
			if (!in_array(mb_strtolower(trim($i['ppmpno'])), $want, true) || $run['remaining'][$dt] <= 0) { continue; }
			$k = $i['key'];
			if (!isset($sum[$k])) { $sum[$k] = ['item_desc' => $i['item_desc'], 'unit' => $i['unit'], 'quantity' => 0, 'total_cost' => 0.0]; }
			$sum[$k]['quantity'] += $run['remaining'][$dt];
			$sum[$k]['total_cost'] += $run['remaining'][$dt] * $i['unit_cost'];
		}
		$out = array_values(array_map(fn ($s) => $s + [
			'unit_cost' => $s['quantity'] > 0 ? round($s['total_cost'] / $s['quantity'], 2) : 0,
		], $sum));
		usort($out, fn ($a, $b) => strcasecmp($a['item_desc'], $b['item_desc']));
		return $out;
	}

	// Per PPMP (lower-cased ppmpno): items, quantity planned / left and amount planned / left
	// after every counted PR (for the PR's PPMP picker)
	public function balances() {
		$run = $this->run();
		$out = [];
		foreach ($run['items'] as $dt => $i) {
			$p = mb_strtolower(trim($i['ppmpno']));
			$q = max(0, $i['quantity']);
			$b = $out[$p] ?? ['items' => 0, 'total_qty' => 0, 'left_qty' => 0, 'total_amount' => 0.0, 'left_amount' => 0.0];
			$b['items']++;
			$b['total_qty'] += $q;
			$b['left_qty'] += $run['remaining'][$dt];
			$b['total_amount'] += $q * $i['unit_cost'];
			$b['left_amount'] += $run['remaining'][$dt] * $i['unit_cost'];
			$out[$p] = $b;
		}
		return $out;
	}

	// Quantity left per PPMP (lower-cased ppmpno => qty), a PR's own use left out
	public function left_per_ppmp($pr_id = null) {
		$run = $this->run($pr_id ?: null);
		$out = [];
		foreach ($run['items'] as $dt => $i) {
			$p = mb_strtolower(trim($i['ppmpno']));
			$out[$p] = ($out[$p] ?? 0) + $run['remaining'][$dt];
		}
		return $out;
	}

	// The PR lines that ask for more than the picked PPMPs have left (the PR's own use
	// left out): [[item, unit, asked, left], ...]. Lines not on those PPMPs are not checked.
	public function over_for_pr($pr_id, array $ppmpnos, array $lines) {
		$want = array_map(fn ($p) => mb_strtolower(trim((string) $p)), $ppmpnos);
		$run = $this->run($pr_id ?: null);
		$left = [];
		foreach ($run['items'] as $dt => $i) {
			if (in_array(mb_strtolower(trim($i['ppmpno'])), $want, true)) {
				$left[$i['key']] = ($left[$i['key']] ?? 0) + $run['remaining'][$dt];
			}
		}
		$asked = [];
		foreach ($lines as [$desc, $unit, $qty]) {
			$k = self::key($desc, $unit);
			if (!array_key_exists($k, $left)) { continue; }
			if (!isset($asked[$k])) { $asked[$k] = [trim((string) $desc), trim((string) $unit), 0]; }
			$asked[$k][2] += (int) $qty;
		}
		$out = [];
		foreach ($asked as $k => [$desc, $unit, $qty]) {
			if ($qty > $left[$k]) { $out[] = [$desc, $unit, $qty, $left[$k]]; }
		}
		return $out;
	}

	// Quantity each PPMP item key has been used by the counted PRs (for the PPMP save check)
	public function used_per_key($ppmp_id) {
		$run = $this->run();
		$out = [];
		foreach ($run['items'] as $dt => $i) {
			if ($i['ppmp_id'] !== (int) $ppmp_id) { continue; }
			$out[$i['key']] = ($out[$i['key']] ?? 0) + (max(0, $i['quantity']) - $run['remaining'][$dt]);
		}
		return array_filter($out);
	}
}
?>
