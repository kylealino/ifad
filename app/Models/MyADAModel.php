<?php
namespace App\Models;
use CodeIgniter\Model;

class MyADAModel extends Model
{

    protected $db;

    public function __construct(){
        parent::__construct();
        $this->session = session();
        $this->request = \Config\Services::request();
		$this->db = \Config\Database::connect();
		$this->cuser = $this->session->get('__xsys_myuserzicas__');

    }

	public function ada_save() {
		$recid = $this->request->getPostGet('recid');
		$adano = $this->request->getPostGet('adano');
		$dvno = $this->request->getPostGet('dvno');
		$particulars = $this->request->getPostGet('particulars');
		$ada_date = $this->request->getPostGet('ada_date');
		$payee_name = $this->request->getPostGet('payee_name');
		$payee_accountno = $this->request->getPostGet('payee_accountno');
		$net_amount = $this->request->getPostGet('net_amount');
		$certified_a = $this->request->getPostGet('certified_a');
		$position_a = $this->request->getPostGet('position_a');
		$certified_b = $this->request->getPostGet('certified_b');
		$position_b = $this->request->getPostGet('position_b');

		if (empty($dvno)) {
			echo "
			<script>
			toastr.error('DV no. is required', 'Oops!', {
					progressBar: true,
					closeButton: true,
					timeOut:2000,
				});
			</script>
			";
			die();
		}

		$is_resubmit = false;
		if (empty($recid)) {
			fods_require_access(\Config\AccessCodes::ADA_ADD, 'Saving');
			//INSERTING HD DATA
			$query = $this->db->query("
				INSERT INTO tbl_ada (
					`adano`,
					`dvno`,
					`particulars`,
					`payee_name`,
					`payee_accountno`,
					`ada_date`,
					`net_amount`,
					`certified_a`,
					`position_a`,
					`certified_b`,
					`position_b`,
					`added_by`
				)
				VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
				[
					$adano,
					$dvno,
					$particulars,
					$payee_name,
					$payee_accountno,
					$ada_date,
					$net_amount,
					$certified_a,
					$position_a,
					$certified_b,
					$position_b,
					$this->cuser
				]
			);

			$project_id = $this->db->insertID();

			$status = "ADA saved successfully";
			$color = "success";
		}else{
			fods_require_access(\Config\AccessCodes::ADA_UPDATE, 'Updating');

			// Once approved the ADA is locked; a disapproved one can be
			// corrected and goes back for approval
			$current = $this->db->query("SELECT `is_approved`, `is_disapproved` FROM tbl_ada WHERE `recid` = ?", [$recid])->getRowArray();
			if (empty($current)) {
				echo "
				<script>
				toastr.error('ADA not found', 'Oops!', {
						progressBar: true,
						closeButton: true,
						timeOut:2000,
					});
				</script>
				";
				die();
			}
			if ($current['is_approved'] == '1') {
				echo "
				<script>
				toastr.error('This ADA is already approved and can no longer be edited', 'Locked', {
						progressBar: true,
						closeButton: true,
						timeOut:2500,
					});
				</script>
				";
				die();
			}
			$is_resubmit = $current['is_disapproved'] == '1';

			$query = $this->db->query("
				UPDATE tbl_ada
				SET
					`adano` = ?,
					`dvno` = ?,
					`particulars` = ?,
					`payee_name` = ?,
					`payee_accountno` = ?,
					`ada_date` = ?,
					`net_amount` = ?,
					`certified_a` = ?,
					`position_a` = ?,
					`certified_b` = ?,
					`position_b` = ?,
					`is_pending` = IF(`is_disapproved` = 1, 1, `is_pending`),
					`is_disapproved` = 0
				WHERE recid = ?
			", [
				$adano,
				$dvno,
				$particulars,
				$payee_name,
				$payee_accountno,
				$ada_date,
				$net_amount,
				$certified_a,
				$position_a,
				$certified_b,
				$position_b,
				$recid
			]);

			$project_id = $recid;

			$status = $is_resubmit ? "ADA updated and sent back for approval" : "ADA updated successfully";
			$color = "info";
		}

		if ($query) {
			echo "
			<script>
				document.getElementById('submitBtn').disabled = true;
				toastr.{$color}('{$status}!', 'Well Done!', {
						progressBar: true,
						closeButton: true,
						timeOut:2500,
					});
				setTimeout(function() {
						window.location.href = 'myada?meaction=MAIN';
					}, 2500);
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

	//ADA - ONE RECORD (editor / review modals)
	public function ada_load($recid) {
		$hd = $this->db->query("SELECT * FROM tbl_ada WHERE `recid` = ?", [$recid])->getRowArray();
		return empty($hd) ? null : $hd;
	}

	//ADA - PREFILL FROM A DV: the same fields the previous form copied
	public function dv_load($dvno) {
		$dv = $this->db->query("
			SELECT
				b.`dvno`,
				b.`particulars`,
				b.`payee_name`,
				(SELECT a.`payee_account_num` FROM tbl_payee a WHERE a.`payee_name` = b.`payee_name` LIMIT 1) AS `payee_accountno`,
				b.`net_amount`,
				b.`disbursement_date`
			FROM tbl_disbursement_hd b
			WHERE b.`dvno` = ?
			ORDER BY b.`recid` DESC
			LIMIT 1
		", [$dvno])->getRowArray();
		return empty($dv) ? null : $dv;
	}

	//ADA - APPROVAL / DISAPPROVAL (single step)
	public function ada_approve() { $this->ada_decide(true); }
	public function ada_disapprove() { $this->ada_decide(false); }

	private function ada_decide($approve) {
		$recid = $this->request->getPostGet('recid');
		$remarks = trim((string) $this->request->getPostGet('remarks'));

		fods_require_access(\Config\AccessCodes::ADAAPPROVAL_APPROVE, $approve ? 'Approve' : 'Disapprove');

		// Only an ADA that is still waiting can be decided
		$hd = $this->db->query("SELECT `is_approved`, `is_disapproved` FROM tbl_ada WHERE `recid` = ?", [$recid])->getRowArray();
		if (empty($hd) || $hd['is_approved'] == '1' || $hd['is_disapproved'] == '1') {
			echo "
			<script>
			toastr.error('This ADA is not waiting for approval', 'Oops!', {
					progressBar: true,
					closeButton: true,
					timeOut:2500,
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

		$query = $this->db->query("
			UPDATE tbl_ada
			SET
				`is_pending` = '0',
				`is_approved` = ?,
				`is_disapproved` = ?,
				`approver` = ?,
				`remarks` = ?
			WHERE `recid` = ?
		", [$approve ? 1 : 0, $approve ? 0 : 1, $this->cuser, mb_substr($remarks, 0, 250), $recid]);

		$status = $approve ? "ADA approved" : "ADA disapproved";
		$color = $approve ? "success" : "warning";
		if ($query) {
			echo "
			<script>
				toastr.{$color}('{$status}', 'Well Done!', {
						progressBar: true,
						closeButton: true,
						timeOut:2500,
					});
				setTimeout(function() {
						window.location.href = 'myadaapproval';
					}, 2500);
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
