<?php
namespace App\Models;
use CodeIgniter\Model;

class MyPRModel extends Model
{

	// Every PR is for the institute: not entered on the form, saved and printed as this
	const ENTITY_NAME = 'FOOD AND NUTRITION RESEARCH INSTITUTE';

    protected $db;

    public function __construct(){
        parent::__construct();
        $this->session = session();
        $this->request = \Config\Services::request();
		$this->mylibzsys = model('App\Models\MyLibzSysModel');
		$this->db = \Config\Database::connect();
		$this->cuser = $this->session->get('__xsys_myuserzicas__');

    }

	// Box B (approval of the PR) follows the estimated cost (the tiers are in Approver Mapping)
	public static function approver_for($total) {
		return MyApproverMapModel::for_amount('pr', 'b', $total);
	}

	//PR - APPROVAL STEPS: Box A (requesting office), then Box B (approval)

	// Box the PR is waiting at ('a' or 'b'); '' once approved or disapproved
	public static function step_of(array $hd) {
		$status = \App\Controllers\MyPR::approvalOf($hd);
		return strpos($status, 'pending_') === 0 ? substr($status, -1) : '';
	}

	// Signatory named on a box: the requesting office head on Box A, the approving officer on Box B
	public static function signatory(array $hd, $box) {
		return trim((string) ($box === 'a' ? $hd['requested_by'] : $hd['approved_by']));
	}

	// Only the signatory named on the current box decides it (matched on the
	// user's full name); an administrator can decide any step
	public static function can_decide(array $hd) {
		$box = self::step_of($hd);
		if ($box === '' || !fods_has_access(\Config\AccessCodes::PRAPPROVAL_APPROVE)) {
			return false;
		}
		if (fods_is_admin()) {
			return true;
		}
		$name = self::signatory($hd, $box);
		return $name !== '' && mb_strtolower($name) === mb_strtolower(trim(fods_current_user()['full_name']));
	}

	// Locked once the requesting office approves Box A, until someone disapproves it
	public static function is_locked(array $hd) {
		$disapproved = $hd['is_disapproved_certa'] == '1' || $hd['is_disapproved_certb'] == '1';
		return $hd['is_approved_certa'] == '1' && !$disapproved;
	}

	//PR - ONE RECORD WITH ITS ITEMS, PPMPS AND RFQS (editor modal)
	public function pr_load($recid) {
		$hd = $this->db->query("SELECT * FROM tbl_pr_hd WHERE `recid` = ?", [$recid])->getRowArray();
		if (empty($hd)) {
			return null;
		}

		$hd['items'] = $this->db->query("
			SELECT `recid`, `item_desc`, `unit`, `quantity`, `unit_cost`, `total_cost`
			FROM tbl_pr_dt
			WHERE `pr_id` = ?
			ORDER BY `recid` ASC
		", [$recid])->getResultArray();

		$hd['ppmps'] = array_column(
			$this->db->query("SELECT `ppmpno` FROM tbl_pr_ppmp WHERE `pr_id` = ? ORDER BY `recid` ASC", [$recid])->getResultArray(),
			'ppmpno'
		);

		// RFQs hang on the PR no.
		$hd['rfqs'] = [];
		if (trim((string) $hd['prno']) !== '') {
			$hd['rfqs'] = $this->db->query("
				SELECT `recid`, `quotation_no`, `company_name`, `company_address`, `delivery_period`, `terms`, `quotation_date`
				FROM tbl_pr_rfq
				WHERE `prno` = ?
				ORDER BY `recid` ASC
			", [$hd['prno']])->getResultArray();
		}
		$hd['can_decide'] = self::can_decide($hd);
		return $hd;
	}

	//PR - ITEMS OF THE PICKED PPMPS, summed per description and unit
	public function ppmp_items(array $ppmpnos) {
		$ppmpnos = array_values(array_filter($ppmpnos, fn ($v) => is_string($v) && $v !== ''));
		if (empty($ppmpnos)) {
			return [];
		}
		$in = implode(',', array_fill(0, count($ppmpnos), '?'));

		return $this->db->query("
			SELECT
				item_desc,
				size AS unit,
				SUM(quantity) AS quantity,
				ROUND(SUM(quantity * unit_cost) / SUM(quantity), 2) AS unit_cost,
				SUM(quantity * unit_cost) AS total_cost
			FROM tbl_ppmp_dt
			WHERE ppmpno IN ($in)
			AND TRIM(item_desc) <> ''
			GROUP BY item_desc, size
			ORDER BY item_desc
		", $ppmpnos)->getResultArray();
	}

	//PR - SAVE
	public function pr_save() {
		$recid = $this->request->getPostGet('recid');
		$entity_name = self::ENTITY_NAME;
		$office = $this->request->getPostGet('office');
		$prno = trim((string) $this->request->getPostGet('prno'));
		$responsibility_code = $this->request->getPostGet('responsibility_code');
		$fund_cluster = $this->request->getPostGet('fund_cluster');
		$pr_date = $this->request->getPostGet('pr_date');
		$end_user = $this->request->getPostGet('end_user');
		$charge_to = $this->request->getPostGet('charge_to');
		$purpose = $this->request->getPostGet('purpose');
		$estimated_cost = $this->request->getPostGet('estimated_cost');
		$estimated_cost = (float) str_replace(',', '', $estimated_cost);
		$prdtdata = $this->request->getPostGet('prdtdata');
		$ppmp_list = $this->request->getPostGet('ppmp_list');
		$requested_by = trim((string) $this->request->getPostGet('requested_by'));
		$requested_position = trim((string) $this->request->getPostGet('requested_position'));

		if (empty($pr_date)) {
			echo "
			<script>
			toastr.error('PR date is required!', 'Oops!', {
					progressBar: true,
					closeButton: true,
					timeOut:2000,
				});
			</script>
			";
			die();
		}
		if (empty($prdtdata)) {
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
		// (a locked PR only takes its PR no., so it needs no signatory here)
		$saved = empty($recid) ? null : $this->db->query("SELECT `fund_cluster`, `is_approved_certa`, `is_disapproved_certa`, `is_approved_certb`, `is_disapproved_certb` FROM tbl_pr_hd WHERE `recid` = ?", [$recid])->getRowArray();

		// fund cluster is blank, 01 or 07 (an older PR may keep the value it was saved with)
		$fund_cluster = trim((string) $fund_cluster);
		if (!in_array($fund_cluster, ['', '01', '07'], true) &&!(!empty($saved) && $fund_cluster === trim((string) $saved['fund_cluster']))) {
			echo "
			<script>
			toastr.error('Pick the fund cluster: 01 or 07.', 'Oops!', {
					progressBar: true,
					closeButton: true,
					timeOut:2500,
				});
			</script>
			";
			die();
		}
		if ($requested_by === '' && !(!empty($saved) && self::is_locked($saved))) {
			echo "
			<script>
			toastr.error('Pick who requests this PR (Box A)!', 'Oops!', {
					progressBar: true,
					closeButton: true,
					timeOut:2000,
				});
			</script>
			";
			die();
		}

		// Box A: the position is the one mapped for that signatory, else their user record's
		$mapped = MyApproverMapModel::position_of('pr', 'a', $requested_by);
		$signer = $mapped !== '' ? null : $this->db->query("SELECT `position` FROM myua_user WHERE `full_name` = ? AND `is_active` = 1 LIMIT 1", [$requested_by])->getRowArray();
		if ($mapped !== '') {
			$requested_position = $mapped;
		} elseif (!empty($signer)) {
			$requested_position = (string) $signer['position'];
		}

		// Box B follows the estimated cost, summed here from the lines (quantity x unit cost)
		$lines_total = 0;
		foreach ($prdtdata as $line) {
			$medata = array_pad(explode("x|x", $line), 5, '');
			$lines_total += round((float) $medata[2] * (float) $medata[3], 2);
		}
		$approver = self::approver_for($lines_total);
		if ($approver['name'] === '' && !(!empty($saved) && self::is_locked($saved))) {
			echo "
			<script>
			toastr.error('No Box B approver is set for this estimated cost. Ask an administrator to set one in Approver Mapping.', 'Oops!', {
					progressBar: true,
					closeButton: true,
					timeOut:3500,
				});
			</script>
			";
			die();
		}
		// the estimated cost saved is the same sum, so it always matches its Box B
		$estimated_cost = $lines_total;

		// A PR takes only what is left of its PPMPs (other PRs' use counted, its own not):
		// a fully used PPMP cannot be picked, and a line cannot ask for more than is left.
		// A locked PR only sets its PR no., so it is not checked.
		if (!(!empty($saved) && self::is_locked($saved))) {
			$usage = new MyPPMPUsageModel();
			$picked = array_values(array_filter((array) $ppmp_list, fn ($p) => is_string($p) && trim($p) !== ''));
			$was = empty($recid) ? [] : array_map(fn ($r) => mb_strtolower(trim($r['ppmpno'])),
				$this->db->query("SELECT `ppmpno` FROM tbl_pr_ppmp WHERE `pr_id` = ?", [$recid])->getResultArray());
			$left = $usage->left_per_ppmp($recid ?: null);
			$fail = function ($msg) {
				echo "
				<script>
				toastr.error(" . json_encode($msg, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) . ", 'Oops!', {
						progressBar: true,
						closeButton: true,
						timeOut:4500,
					});
				</script>
				";
				die();
			};
			foreach ($picked as $p) {
				$k = mb_strtolower(trim($p));
				if (array_key_exists($k, $left) && $left[$k] <= 0 && !in_array($k, $was, true)) {
					$fail('PPMP ' . trim($p) . ' is fully used by other PRs. Remove it from this PR.');
				}
			}
			$lines = array_map(function ($line) {
				$m = array_pad(explode("x|x", (string) $line), 5, '');
				return [$m[0], $m[1], (int) str_replace(',', '', $m[2])];
			}, (array) $prdtdata);
			$over = $usage->over_for_pr($recid ?: null, $picked, $lines);
			if (!empty($over)) {
				[$desc, $unit, $asked, $have] = $over[0];
				$more = count($over) > 1 ? ' (and ' . (count($over) - 1) . ' more item' . (count($over) > 2 ? 's' : '') . ')' : '';
				$fail($desc . ($unit !== '' ? ' (' . $unit . ')' : '') . ': this PR asks for ' . $asked . ' but only ' . $have
					. ' is left on its PPMPs' . $more . '. Lower the quantity, or click Reload from PPMP.');
			}
		}

		if (empty($recid)) {
			fods_require_access(\Config\AccessCodes::PR_ADD, 'Saving');

			$this->db->transStart();

			//INSERTING HD DATA
			$this->db->query("
				INSERT INTO `tbl_pr_hd`(
					`entity_name`,
					`office`,
					`prno`,
					`responsibility_code`,
					`fund_cluster`,
					`pr_date`,
					`end_user`,
					`position`,
					`charge_to`,
					`purpose`,
					`estimated_cost`,
					`requested_by`,
					`requested_position`,
					`approved_by`,
					`approved_position`,
					`added_by`
				)
				VALUES (?, ?, ?, ?, ?, ?, ?, '', ?, ?, ?, ?, ?, ?, ?, ?)",
				[
					$entity_name,
					$office,
					$prno,
					$responsibility_code,
					$fund_cluster,
					$pr_date,
					$end_user,
					$charge_to,
					$purpose,
					$estimated_cost,
					$requested_by,
					$requested_position,
					$approver['name'],
					$approver['position'],
					$this->cuser
				]
			);

			$project_id = $this->db->insertID();

			$status = "PR saved successfully";
			$color = "success";
		}else{
			fods_require_access(\Config\AccessCodes::PR_UPDATE, 'Updating');

			$current = $this->db->query("SELECT `prno`, `is_approved_certa`, `is_disapproved_certa`, `is_approved_certb`, `is_disapproved_certb` FROM tbl_pr_hd WHERE `recid` = ?", [$recid])->getRowArray();
			if (empty($current)) {
				echo "
				<script>
				toastr.error('Purchase request not found', 'Oops!', {
						progressBar: true,
						closeButton: true,
						timeOut:2000,
					});
				</script>
				";
				die();
			}

			$before = MyHistoryModel::row('tbl_pr_hd', $recid);

			// Once the requesting office has approved Box A the PR is locked; only
			// its PR no. can still be assigned
			if (self::is_locked($current)) {
				if ($prno === trim((string) $current['prno'])) {
					echo "
					<script>
					toastr.error('This PR is already in approval and can no longer be edited', 'Locked', {
							progressBar: true,
							closeButton: true,
							timeOut:2500,
						});
					</script>
					";
					die();
				}

				$this->db->transStart();
				$this->db->query("UPDATE tbl_pr_hd SET `prno` = ? WHERE `recid` = ?", [$prno, $recid]);
				$this->db->query("UPDATE tbl_pr_dt SET `prno` = ? WHERE `pr_id` = ?", [$prno, $recid]);
				$this->db->query("UPDATE tbl_pr_ppmp SET `prno` = ? WHERE `pr_id` = ?", [$prno, $recid]);
				$this->db->transComplete();

				if ($this->db->transStatus()) {
					MyHistoryModel::log('pr', $recid, 'PRNO_SET', '', MyHistoryModel::diff($before, MyHistoryModel::row('tbl_pr_hd', $recid)), $prno);
					echo "
					<script>
						document.getElementById('submitBtn').disabled = true;
						toastr.info('PR no. saved!', 'Well Done!', {
								progressBar: true,
								closeButton: true,
								timeOut:2000,
							});
						setTimeout(function() {
								window.location.href = 'mypr?meaction=PR-MAIN';
							}, 1000); // reload after 1 s; the toast still runs its 2.5 s (fods-flash.js carries it over)
					</script>
					";
				} else {
					echo "
					<script>
					toastr.error('The PR no. could not be saved. Nothing was changed.', 'Oops!', {
							progressBar: true,
							closeButton: true,
							timeOut:3000,
						});
					</script>
					";
				}
				exit;
			}

			// A disapproved PR can be corrected and goes back to Box A
			$is_disapproved = $current['is_disapproved_certa'] == '1' || $current['is_disapproved_certb'] == '1';

			$this->db->transStart();

			if ($is_disapproved) {
				$this->db->query("
					UPDATE tbl_pr_hd
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
					`tbl_pr_hd`
				SET
					`entity_name` = ?,
					`office` = ?,
					`prno` = ?,
					`responsibility_code` = ?,
					`fund_cluster` = ?,
					`pr_date` = ?,
					`end_user` = ?,
					`charge_to` = ?,
					`purpose` = ?,
					`estimated_cost` = ?,
					`requested_by` = ?,
					`requested_position` = ?,
					`approved_by` = ?,
					`approved_position` = ?
				WHERE recid = ?
			", [
				$entity_name,
				$office,
				$prno,
				$responsibility_code,
				$fund_cluster,
				$pr_date,
				$end_user,
				$charge_to,
				$purpose,
				$estimated_cost,
				$requested_by,
				$requested_position,
				$approver['name'],
				$approver['position'],
				$recid
			]);

			$project_id = $recid;

			$status = $is_disapproved ? "PR updated and sent back for approval" : "PR updated successfully";
			$color = "info";
		}

		//PPMPS THE PR TAKES ITS ITEMS FROM
		$this->db->query("DELETE FROM tbl_pr_ppmp WHERE `pr_id` = ?", [$project_id]);
		if (!empty($ppmp_list)) {
			foreach($ppmp_list as $ppmp) {
				$this->db->query("
					INSERT INTO tbl_pr_ppmp
					(
						pr_id,
						prno,
						ppmpno,
						added_by
					)
					VALUES (?,?,?,?)",
					[
						$project_id,
						$prno,
						$ppmp,
						$this->cuser
					]
				);
			}
		}

		// blank or non-numeric amounts are saved as 0
		$num = fn ($v) => is_numeric($v) ? $v : 0;

		//INSERTING DT DATA (each line: description, unit, quantity, unit cost, total cost)
		$this->db->query("DELETE FROM tbl_pr_dt WHERE `pr_id` = ?", [$project_id]);
		for($aa = 0; $aa < count($prdtdata); $aa++){
			$medata = array_pad(explode("x|x",$prdtdata[$aa]), 5, '');
			$item_desc = $medata[0];
			$unit = $medata[1];
			$quantity = $num($medata[2]);
			$unit_cost = $num($medata[3]);
			$total_cost = $num($medata[4]);

			$this->db->query("
				INSERT INTO `tbl_pr_dt`(
					`pr_id`,
					`prno`,
					`item_desc`,
					`unit`,
					`quantity`,
					`unit_cost`,
					`total_cost`,
					`added_by`
				)
				VALUES (?, ?, ?, ?, ?, ?, ?, ?)",
				[
					$project_id,
					$prno,
					$item_desc,
					$unit,
					$quantity,
					$unit_cost,
					$total_cost,
					$this->cuser
				]
			);
		}

		$this->db->transComplete();

		//HISTORY
		if ($this->db->transStatus()) {
			if (empty($recid)) {
				MyHistoryModel::log('pr', $project_id, 'CREATED', '', null, $prno);
			} else {
				MyHistoryModel::log('pr', $recid, !empty($is_disapproved) ? 'RESUBMITTED' : 'UPDATED', '',
					MyHistoryModel::diff($before, MyHistoryModel::row('tbl_pr_hd', $recid)), $prno);
			}
		}

		if ($this->db->transStatus()) {
			// Echo JavaScript to show the toast and then redirect
			echo "
			<script>
				document.getElementById('submitBtn').disabled = true;
				toastr.$color('{$status}!', 'Well Done!', {
						progressBar: true,
						closeButton: true,
						timeOut:2000,
					});
				setTimeout(function() {
						window.location.href = 'mypr?meaction=PR-MAIN'; // Redirect to MAIN view
					}, 1000); // reload after 1 s; the toast still runs its 2.5 s (fods-flash.js carries it over)
			</script>
			";
			exit; // Stop further PHP execution after the toast
		} else {
			// Nothing was written: the PR, its PPMPs and its items are saved together
			echo "
			<script>
			toastr.error('The PR could not be saved. Nothing was changed.', 'Oops!', {
					progressBar: true,
					closeButton: true,
					timeOut:3000,
				});
			</script>
			";
			exit;
		}

	}

	//PR - ADD AN RFQ (anyone who can update PRs, once the PR has a PR no.)
	public function pr_rfq_save() {
		fods_require_access(\Config\AccessCodes::PR_UPDATE, 'Updating');
		$recid = $this->request->getPostGet('recid');
		$quotation_no = trim((string) $this->request->getPostGet('quotation_no'));
		$quotation_date = $this->request->getPostGet('quotation_date');
		$company_name = trim((string) $this->request->getPostGet('company_name'));
		// optional fields: a missing one is saved blank, not NULL
		$company_address = (string) $this->request->getPostGet('company_address');
		$delivery_period = (string) $this->request->getPostGet('delivery_period');
		$terms = (string) $this->request->getPostGet('terms');

		// The RFQ is filed under the PR no. saved on the PR
		$hd = $this->db->query("SELECT `prno` FROM tbl_pr_hd WHERE `recid` = ?", [$recid])->getRowArray();
		$prno = empty($hd) ? '' : trim((string) $hd['prno']);
		if ($prno === '') {
			echo "
			<script>
			toastr.error('Save a PR no. on this PR first', 'Oops!', {
					progressBar: true,
					closeButton: true,
					timeOut:2500,
				});
			</script>
			";
			die();
		}
		if ($quotation_no === '') {
			echo "
			<script>
			toastr.error('Quotation no. is required!', 'Oops!', {
					progressBar: true,
					closeButton: true,
					timeOut:2000,
				});
			</script>
			";
			die();
		}
		if (empty($quotation_date)) {
			echo "
			<script>
			toastr.error('Quotation date is required!', 'Oops!', {
					progressBar: true,
					closeButton: true,
					timeOut:2000,
				});
			</script>
			";
			die();
		}
		if ($company_name === '') {
			echo "
			<script>
			toastr.error('Company name is required!', 'Oops!', {
					progressBar: true,
					closeButton: true,
					timeOut:2000,
				});
			</script>
			";
			die();
		}

		//INSERTING RFQ DATA
		$query = $this->db->query("
			INSERT INTO `tbl_pr_rfq`(
			`prno`,
			`quotation_no`,
			`company_name`,
			`company_address`,
			`delivery_period`,
			`terms`,
			`quotation_date`,
			`added_by`
			)
			VALUES (?, ?, ?, ?, ?, ?, ?, ?)",
			[
				$prno,
				$quotation_no,
				$company_name,
				$company_address,
				$delivery_period,
				$terms,
				$quotation_date,
				$this->cuser
			]
		);

		if ($query) {
			MyHistoryModel::log('pr', (int) $recid, 'RFQ_ADDED', 'Quotation ' . $quotation_no . ' from ' . $company_name, null, $prno);
			// Echo JavaScript to show the toast, then reopen the PR on its RFQ tab
			$back = 'mypr?meaction=PR-MAIN&recid=' . (int) $recid . '&tab=rfq';
			echo "
			<script>
				toastr.success('RFQ added!', 'Well Done!', {
						progressBar: true,
						closeButton: true,
						timeOut:2000,
					});
				setTimeout(function() {
						window.location.href = '{$back}';
					}, 1000); // reload after 1 s; the toast still runs its 2.5 s (fods-flash.js carries it over)
			</script>
			";
			exit; // Stop further PHP execution after the toast
		} else {
			echo "
			<script>
			toastr.error('The RFQ could not be saved.', 'Oops!', {
					progressBar: true,
					closeButton: true,
					timeOut:3000,
				});
			</script>
			";
			exit;
		}

	}

	//BOX A / B - APPROVAL / DISAPPROVAL (the step the PR is waiting at)
	public function pr_approve() { $this->pr_decide(true); }
	public function pr_disapprove() { $this->pr_decide(false); }

	// Box A (requesting office) decides first, then Box B (approval). A
	// disapproval at either box sends the PR back to the preparer.
	private function pr_decide($approve) {
		$recid = $this->request->getPostGet('recid');
		$remarks = trim((string) $this->request->getPostGet('remarks'));
		$approver = $this->cuser;

		fods_require_access(\Config\AccessCodes::PRAPPROVAL_APPROVE, $approve ? 'Approve' : 'Disapprove');

		// Only a PR that is still waiting can be decided
		$hd = $this->db->query("SELECT * FROM tbl_pr_hd WHERE `recid` = ?", [$recid])->getRowArray();
		$box = empty($hd) ? '' : self::step_of($hd);
		if ($box === '') {
			echo "
			<script>
			toastr.error('This PR is not pending approval', 'Oops!', {
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
			toastr.error('Only the Box " . strtoupper($box) . " signatory of this PR or an administrator can decide this step', 'Oops!', {
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
			UPDATE tbl_pr_hd
			SET
				`is_pending` = '0',
				`is_approved_cert{$box}` = ?,
				`is_disapproved_cert{$box}` = ?,
				`cert{$box}_approver` = ?,
				`cert{$box}_remarks` = ?
			WHERE `recid` = ?
		", [$approve ? 1 : 0, $approve ? 0 : 1, $approver, mb_substr($remarks, 0, 100), $recid]);

		if ($query) {
			MyHistoryModel::log('pr', $recid, ($approve ? 'APPROVED_BOX_' : 'DISAPPROVED_BOX_') . strtoupper($box), $remarks, null, $hd['prno']);
		}
		if ($query) {
			if (!$approve) {
				$status = "PR disapproved";
			} else {
				$status = $box === 'b' ? "PR approved" : "Box A approved";
			}
			$color = $approve ? "success" : "warning";
			echo "
			<script>
				toastr.{$color}('{$status}', 'Well Done!', {
						progressBar: true,
						closeButton: true,
						timeOut:2000,
					});
				setTimeout(function() {
						window.location.href = 'mypr?meaction=PR-MAIN';
					}, 1000); // reload after 1 s; the toast still runs its 2.5 s (fods-flash.js carries it over)
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
