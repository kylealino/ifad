<?php
namespace App\Models;
use CodeIgniter\Model;

class MyPOModel extends Model
{

    protected $db;

	// What a new PO starts with (the usual terms and accountant on the printed PO)
	const DEFAULTS = [
		'place_of_delivery' => 'DOST-FNRI c/o Property Section',
		'date_of_delivery' => '30 Calendar Days upon receipt of approved PO',
		'delivery_term' => '30 Calendar Days upon receipt of approved PO',
		'payment_term' => '30 Days',
		'accountant' => 'BONJOBIE F. CAJANO, CPA, CTT',
		'accountant_position' => 'Accountant III',
	];

	// Box B (approval of the PO) follows the PO total
	const APPROVAL_LIMIT = 5000000;

	public static function approver_for($total) {
		if ((float) $total >= self::APPROVAL_LIMIT) {
			return ['name' => 'ATTY. LUCIEDEN G. RAZ', 'position' => 'Director III'];
		}
		return ['name' => 'ALEXIS M. ORTIZ', 'position' => 'Chief AO, FAD'];
	}

	//PO - APPROVAL STEPS: Box A (accounting), then Box B (approval)

	// Box the PO is waiting at ('a' or 'b'); '' once approved or disapproved
	public static function step_of(array $hd) {
		$status = \App\Controllers\MyPO::statusOf($hd);
		return strpos($status, 'pending_') === 0 ? substr($status, -1) : '';
	}

	// Signatory named on a box: the accountant on Box A, the approving officer on Box B
	public static function signatory(array $hd, $box) {
		return trim((string) ($box === 'a' ? $hd['accountant'] : $hd['approved_by']));
	}

	// Only the signatory named on the current box decides it (matched on the
	// user's full name); an administrator can decide any step
	public static function can_decide(array $hd) {
		$box = self::step_of($hd);
		if ($box === '' || !fods_has_access(\Config\AccessCodes::POAPPROVAL_APPROVE)) {
			return false;
		}
		if (fods_is_admin()) {
			return true;
		}
		$name = self::signatory($hd, $box);
		return $name !== '' && mb_strtolower($name) === mb_strtolower(trim(fods_current_user()['full_name']));
	}

    public function __construct(){
        parent::__construct();
        $this->session = session();
        $this->request = \Config\Services::request();
		$this->mylibzsys = model('App\Models\MyLibzSysModel');
		$this->db = \Config\Database::connect();
		$this->cuser = $this->session->get('__xsys_myuserzicas__');

    }

	// RFQs of a PR (filed under its PR no.), to fill the supplier from
	private function rfqs_of($prno) {
		if (trim((string) $prno) === '') {
			return [];
		}
		return $this->db->query("
			SELECT `recid`, `quotation_no`, `company_name`, `company_address`, `delivery_period`, `terms`, `quotation_date`
			FROM tbl_pr_rfq
			WHERE `prno` = ?
			ORDER BY `recid` ASC
		", [$prno])->getResultArray();
	}

	//PO - ONE RECORD WITH ITS ITEMS (editor modal)
	public function po_load($recid) {
		$hd = $this->db->query("SELECT * FROM tbl_po_hd WHERE `recid` = ?", [$recid])->getRowArray();
		if (empty($hd)) {
			return null;
		}
		$hd['items'] = $this->db->query("
			SELECT `recid`, `stock_no`, `unit`, `item_desc`, `quantity`, `unit_cost`, `amount`
			FROM tbl_po_dt
			WHERE `po_id` = ?
			ORDER BY `recid` ASC
		", [$recid])->getResultArray();
		$hd['rfqs'] = $this->rfqs_of($hd['prno']);
		$hd['can_decide'] = self::can_decide($hd);
		return $hd;
	}

	//PO - THE PR IT IS PREPARED FROM (header, items without blank lines, RFQs)
	public function pr_source($pr_id) {
		$hd = $this->db->query("
			SELECT `recid`, `prno`, `pr_date`, `end_user`, `charge_to`, `purpose`, `fund_cluster`, `estimated_cost`
			FROM tbl_pr_hd
			WHERE `recid` = ?
		", [$pr_id])->getRowArray();
		if (empty($hd)) {
			return null;
		}
		$hd['items'] = $this->db->query("
			SELECT `item_desc`, `unit`, `quantity`, `unit_cost`
			FROM tbl_pr_dt
			WHERE `pr_id` = ?
			AND (TRIM(`item_desc`) <> '' OR `quantity` <> 0 OR `unit_cost` <> 0)
			ORDER BY `recid` ASC
		", [$pr_id])->getResultArray();
		$hd['rfqs'] = $this->rfqs_of($hd['prno']);
		return $hd;
	}

	//PO - SAVE
	public function po_save() {
		// optional text fields: a missing one is saved blank, not NULL
		$text = fn ($name) => trim((string) $this->request->getPostGet($name));
		// amounts may come formatted ("18,000.00"); blank or non-numeric is 0
		$num = function ($v) {
			$v = str_replace(',', '', (string) $v);
			return is_numeric($v) ? $v : 0;
		};
		// a blank date is saved as NULL
		$date = fn ($name) => $text($name) === '' ? null : $text($name);

		$recid = $this->request->getPostGet('recid');
		$pono = $text('pono');
		$po_date = $date('po_date');
		$pr_id = (int) $this->request->getPostGet('pr_id');
		$prno = $text('prno');
		$pr_date = $date('pr_date');
		$supplier_name = $text('supplier_name');
		$supplier_address = $text('supplier_address');
		$supplier_tel = $text('supplier_tel');
		$supplier_tin = $text('supplier_tin');
		$mode_of_procurement = $text('mode_of_procurement');
		$place_of_delivery = $text('place_of_delivery');
		$date_of_delivery = $text('date_of_delivery');
		$delivery_term = $text('delivery_term');
		$payment_term = $text('payment_term');
		$charge_to = $text('charge_to');
		$end_user = $text('end_user');
		$fund_cluster = $text('fund_cluster');
		$funds_available = $num($this->request->getPostGet('funds_available'));
		$ors_burs_no = $text('ors_burs_no');
		$ors_burs_date = $date('ors_burs_date');
		$ors_burs_amount = $num($this->request->getPostGet('ors_burs_amount'));
		$accountant = $text('accountant');
		$accountant_position = $text('accountant_position');
		$podtdata = $this->request->getPostGet('podtdata');

		if (empty($po_date)) {
			echo "
			<script>
			toastr.error('PO date is required!', 'Oops!', {
					progressBar: true,
					closeButton: true,
					timeOut:2000,
				});
			</script>
			";
			die();
		}
		if ($supplier_name === '') {
			echo "
			<script>
			toastr.error('Supplier is required!', 'Oops!', {
					progressBar: true,
					closeButton: true,
					timeOut:2000,
				});
			</script>
			";
			die();
		}
		if ($accountant === '') {
			echo "
			<script>
			toastr.error('Box A (accounting) signatory is required!', 'Oops!', {
					progressBar: true,
					closeButton: true,
					timeOut:2000,
				});
			</script>
			";
			die();
		}
		if (empty($podtdata)) {
			echo "
			<script>
			toastr.error('No items found!', 'Oops!', {
					progressBar: true,
					closeButton: true,
					timeOut:2000,
				});
			</script>
			";
			die();
		}
		// a PO no. is used once
		if ($pono !== '') {
			$dup = $this->db->query("SELECT `recid` FROM tbl_po_hd WHERE `pono` = ? AND `recid` <> ?", [$pono, (int) $recid])->getRowArray();
			if (!empty($dup)) {
				echo "
				<script>
				toastr.error('That PO no. is already used on another PO!', 'Oops!', {
						progressBar: true,
						closeButton: true,
						timeOut:2500,
					});
				</script>
				";
				die();
			}
		}

		//ITEMS (each line: stock / property no., unit, description, quantity, unit cost);
		//the amount is quantity x unit cost, and the PO total is their sum
		$lines = [];
		$total_amount = 0;
		for($aa = 0; $aa < count($podtdata); $aa++){
			$medata = array_pad(explode("x|x",$podtdata[$aa]), 5, '');
			$quantity = (int) $num($medata[3]);
			$unit_cost = round((float) $num($medata[4]), 2);
			$amount = round($quantity * $unit_cost, 2);
			$total_amount += $amount;
			$lines[] = [trim($medata[0]), trim($medata[1]), trim($medata[2]), $quantity, $unit_cost, $amount];
		}

		// Box A's position comes from that accountant's user record when there is one
		$user = $this->db->query("SELECT `position` FROM myua_user WHERE `full_name` = ? LIMIT 1", [$accountant])->getRowArray();
		if (!empty($user) && trim((string) $user['position']) !== '') {
			$accountant_position = trim($user['position']);
		}

		// Box B is not picked: it follows the PO total
		$approver = self::approver_for($total_amount);
		$approved_by = $approver['name'];
		$approved_position = $approver['position'];

		$header = [
			$pono, $po_date, $pr_id, $prno, $pr_date,
			$supplier_name, $supplier_address, $supplier_tel, $supplier_tin,
			$mode_of_procurement, $place_of_delivery, $date_of_delivery, $delivery_term, $payment_term,
			$charge_to, $end_user, $fund_cluster, $funds_available,
			$ors_burs_no, $ors_burs_date, $ors_burs_amount, round($total_amount, 2),
			$approved_by, $approved_position, $accountant, $accountant_position,
		];

		if (empty($recid)) {
			fods_require_access(\Config\AccessCodes::PO_ADD, 'Saving');

			$this->db->transStart();

			//INSERTING HD DATA
			$this->db->query("
				INSERT INTO `tbl_po_hd`(
					`pono`,
					`po_date`,
					`pr_id`,
					`prno`,
					`pr_date`,
					`supplier_name`,
					`supplier_address`,
					`supplier_tel`,
					`supplier_tin`,
					`mode_of_procurement`,
					`place_of_delivery`,
					`date_of_delivery`,
					`delivery_term`,
					`payment_term`,
					`charge_to`,
					`end_user`,
					`fund_cluster`,
					`funds_available`,
					`ors_burs_no`,
					`ors_burs_date`,
					`ors_burs_amount`,
					`total_amount`,
					`approved_by`,
					`approved_position`,
					`accountant`,
					`accountant_position`,
					`added_by`
				)
				VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
				array_merge($header, [$this->cuser])
			);

			$po_id = $this->db->insertID();

			$status = "PO saved successfully";
			$color = "success";
		}else{
			fods_require_access(\Config\AccessCodes::PO_UPDATE, 'Updating');

			$current = $this->db->query("SELECT `is_approved_certa`, `is_disapproved_certa`, `is_approved_certb`, `is_disapproved_certb` FROM tbl_po_hd WHERE `recid` = ?", [$recid])->getRowArray();
			if (empty($current)) {
				echo "
				<script>
				toastr.error('Purchase order not found', 'Oops!', {
						progressBar: true,
						closeButton: true,
						timeOut:2000,
					});
				</script>
				";
				die();
			}

			// Once accounting has approved Box A the PO is locked; a disapproved PO
			// can be corrected and goes back to Box A
			$is_disapproved = $current['is_disapproved_certa'] == '1' || $current['is_disapproved_certb'] == '1';
			if ($current['is_approved_certa'] == '1' && !$is_disapproved) {
				echo "
				<script>
				toastr.error('This PO is already in approval and can no longer be edited', 'Locked', {
						progressBar: true,
						closeButton: true,
						timeOut:2500,
					});
				</script>
				";
				die();
			}

			$this->db->transStart();

			if ($is_disapproved) {
				$this->db->query("
					UPDATE tbl_po_hd
					SET
						`is_pending` = '1',
						`is_approved_certa` = '0',
						`is_disapproved_certa` = '0',
						`is_approved_certb` = '0',
						`is_disapproved_certb` = '0'
					WHERE `recid` = ?
				", [$recid]);
			}

			$this->db->query("
				UPDATE
					`tbl_po_hd`
				SET
					`pono` = ?,
					`po_date` = ?,
					`pr_id` = ?,
					`prno` = ?,
					`pr_date` = ?,
					`supplier_name` = ?,
					`supplier_address` = ?,
					`supplier_tel` = ?,
					`supplier_tin` = ?,
					`mode_of_procurement` = ?,
					`place_of_delivery` = ?,
					`date_of_delivery` = ?,
					`delivery_term` = ?,
					`payment_term` = ?,
					`charge_to` = ?,
					`end_user` = ?,
					`fund_cluster` = ?,
					`funds_available` = ?,
					`ors_burs_no` = ?,
					`ors_burs_date` = ?,
					`ors_burs_amount` = ?,
					`total_amount` = ?,
					`approved_by` = ?,
					`approved_position` = ?,
					`accountant` = ?,
					`accountant_position` = ?
				WHERE recid = ?
			", array_merge($header, [$recid]));

			$po_id = $recid;

			$status = $is_disapproved ? "PO updated and sent back for approval" : "PO updated successfully";
			$color = "info";
		}

		//INSERTING DT DATA
		$this->db->query("DELETE FROM tbl_po_dt WHERE `po_id` = ?", [$po_id]);
		foreach ($lines as $line) {
			$this->db->query("
				INSERT INTO `tbl_po_dt`(
					`po_id`,
					`pono`,
					`stock_no`,
					`unit`,
					`item_desc`,
					`quantity`,
					`unit_cost`,
					`amount`,
					`added_by`
				)
				VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)",
				array_merge([$po_id, $pono], $line, [$this->cuser])
			);
		}

		$this->db->transComplete();

		if ($this->db->transStatus()) {
			// Echo JavaScript to show the toast and then redirect
			echo "
			<script>
				document.getElementById('submitBtn').disabled = true;
				toastr.$color('{$status}!', 'Well Done!', {
						progressBar: true,
						closeButton: true,
						timeOut:2500,
					});
				setTimeout(function() {
						window.location.href = 'mypo?meaction=MAIN'; // Redirect to MAIN view
					}, 2500); // 2-second delay for user to see the toast
			</script>
			";
			exit; // Stop further PHP execution after the toast
		} else {
			// Nothing was written: the PO and its items are saved together
			echo "
			<script>
			toastr.error('The PO could not be saved. Nothing was changed.', 'Oops!', {
					progressBar: true,
					closeButton: true,
					timeOut:3000,
				});
			</script>
			";
			exit;
		}

	}

	//BOX A / B - APPROVAL / DISAPPROVAL (the step the PO is waiting at)
	public function po_approve() { $this->po_decide(true); }
	public function po_disapprove() { $this->po_decide(false); }

	// Box A (accounting) decides first, then Box B (approval). A disapproval
	// at either box sends the PO back to the preparer.
	private function po_decide($approve) {
		$recid = $this->request->getPostGet('recid');
		$remarks = trim((string) $this->request->getPostGet('remarks'));
		$approver = $this->cuser;

		fods_require_access(\Config\AccessCodes::POAPPROVAL_APPROVE, $approve ? 'Approve' : 'Disapprove');

		// Only a PO that is still waiting can be decided
		$hd = $this->db->query("SELECT * FROM tbl_po_hd WHERE `recid` = ?", [$recid])->getRowArray();
		$box = empty($hd) ? '' : self::step_of($hd);
		if ($box === '') {
			echo "
			<script>
			toastr.error('This PO is not waiting for approval', 'Oops!', {
					progressBar: true,
					closeButton: true,
					timeOut:2500,
				});
			</script>
			";
			exit;
		}
		if (!self::can_decide($hd)) {
			echo "
			<script>
			toastr.error('Only the Box " . strtoupper($box) . " signatory of this PO or an administrator can decide this step', 'Oops!', {
					progressBar: true,
					closeButton: true,
					timeOut:3000,
				});
			</script>
			";
			exit;
		}
		if (!$approve && $remarks === '') {
			echo "
			<script>
			toastr.error('Remarks are required when disapproving', 'Oops!', {
					progressBar: true,
					closeButton: true,
					timeOut:2000,
				});
			</script>
			";
			exit;
		}

		// $box is 'a' or 'b' from step_of(), never from the request
		$query = $this->db->query("
			UPDATE tbl_po_hd
			SET
				`is_pending` = '0',
				`is_approved_cert{$box}` = ?,
				`is_disapproved_cert{$box}` = ?,
				`cert{$box}_approver` = ?,
				`cert{$box}_remarks` = ?
			WHERE `recid` = ?
		", [$approve ? 1 : 0, $approve ? 0 : 1, $approver, mb_substr($remarks, 0, 100), $recid]);

		if ($query) {
			if (!$approve) {
				$status = "PO disapproved";
			} else {
				$status = $box === 'b' ? "PO approved" : "Box A approved";
			}
			$color = $approve ? "success" : "warning";
			echo "
			<script>
				toastr.{$color}('{$status}', 'Well Done!', {
						progressBar: true,
						closeButton: true,
						timeOut:2500,
					});
				setTimeout(function() {
						window.location.href = 'mypoapproval';
					}, 2500);
			</script>
			";
			exit;
		} else {
			echo "
			<script>
			toastr.error('The decision could not be saved.', 'Oops!', {
					progressBar: true,
					closeButton: true,
					timeOut:3000,
				});
			</script>
			";
			exit;
		}
	}


} //end main class
?>
