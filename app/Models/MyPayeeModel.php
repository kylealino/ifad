<?php
namespace App\Models;
use CodeIgniter\Model;

class MyPayeeModel extends Model
{

    protected $db;

    public function __construct(){
        parent::__construct();
        $this->session = session();
        $this->request = \Config\Services::request();
		$this->db = \Config\Database::connect();
		$this->cuser = $this->session->get('__xsys_myuserzicas__');
        
    }

	public function payee_save() { 
		$recid = $this->request->getPostGet('recid');
		$payee_name = $this->request->getPostGet('payee_name');
		$payee_account_num = $this->request->getPostGet('payee_account_num');
		$payee_office = $this->request->getPostGet('payee_office');
		$payee_tin = $this->request->getPostGet('payee_tin');
		$contact_no = $this->request->getPostGet('contact_no');
		$payee_address = $this->request->getPostGet('payee_address');
		$disb_method = $this->request->getPostGet('disb_method');
		$currency = $this->request->getPostGet('currency');

		// var_dump(
		// 	$is_vatable,
		// 	$vat_percent,
		// 	$ewt_percent,
		// 	$pt_percent
		// );
		// die();

		if (empty($payee_name)) {
			echo "
			<script>
			toastr.error('Payee name is required!', 'Oops!', {
					progressBar: true,
					closeButton: true,
					timeOut:2000,
				});
			</script>
			";
			die();
		}

		// Payees are matched by name everywhere (ORS, DV, LDDAP-ADA account no.), so a name
		// may belong to one payee only (ignoring case and spaces at the ends). Checked when a
		// payee is added or renamed, so the older duplicates can still be edited.
		$current = empty($recid) ? null : $this->db->query("SELECT `payee_name` FROM `tbl_payee` WHERE `recid` = ?", [$recid])->getRowArray();
		$renamed = !$current || mb_strtoupper(trim((string) $current['payee_name'])) !== mb_strtoupper(trim((string) $payee_name));
		$same = !$renamed ? null : $this->db->query("
			SELECT `recid` FROM `tbl_payee` WHERE UPPER(TRIM(`payee_name`)) = UPPER(TRIM(?)) AND `recid` <> ? LIMIT 1
		", [$payee_name, (int) $recid])->getRowArray();
		if ($same) {
			echo "
			<script>
			toastr.error('Another payee already has this name. Open that payee instead.', 'Oops!', {
					progressBar: true,
					closeButton: true,
					timeOut:2500,
				});
			</script>
			";
			die();
		}


		if (empty($recid)) {
			fods_require_access(\Config\AccessCodes::PAYEE_ADD, 'Saving');
			$query = $this->db->query("
				INSERT INTO `tbl_payee` (
					`payee_name`, 
					`payee_account_num`, 
					`payee_office`, 
					`payee_tin`, 
					`contact_no`,
					`payee_address`, 
					`disb_method`, 
					`currency`,
					`added_by`
				) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)",
				  [
					$payee_name,
					$payee_account_num,
					$payee_office,
					$payee_tin,
					$contact_no,
					$payee_address,
					$disb_method,
					$currency,
					$this->cuser
				]
			);
			if ($query) { MyHistoryModel::log('payee', $this->db->insertID(), 'CREATED', '', null, $payee_name); }
			$status = "Payee Saved successfully";
			$color = "success";
		}else{
			fods_require_access(\Config\AccessCodes::PAYEE_UPDATE, 'Updating');
			$before = MyHistoryModel::row('tbl_payee', $recid);
			$query = $this->db->query("
				UPDATE tbl_payee SET 
				`payee_name` = ?,
				`payee_account_num` = ?,
				`payee_office` = ?,
				`payee_tin` = ?,
				`contact_no` = ?,
				`payee_address` = ?,
				`disb_method` = ?,
				`currency` = ?
				WHERE `recid` = ?
				",
				[
					$payee_name,
					$payee_account_num,
					$payee_office,
					$payee_tin,
					$contact_no,
					$payee_address,
					$disb_method,
					$currency,
					$recid
				]
			);
			if ($query) { MyHistoryModel::log('payee', $recid, 'UPDATED', '', MyHistoryModel::diff($before, MyHistoryModel::row('tbl_payee', $recid)), $payee_name); }
			$status = "Payee Updated successfully";
			$color = "info";
		}
		
		if ($query) {
			// Echo JavaScript to show the toast and then redirect
			echo "
			<script>
				toastr.$color('{$status}!', 'Well Done!', {
						progressBar: true,
						closeButton: true,
						timeOut:2000,
					});
				setTimeout(function() {
						window.location.href = 'mypayee?meaction=MAIN'; // Redirect to MAIN view
					}, 1000); // reload after 1 s; the toast still runs its 2.5 s (fods-flash.js carries it over)
			</script>
			";
			exit; // Stop further PHP execution after the toast
		} else {
			// If there's an error, show an alert message
			echo "<script type='text/javascript'>
					alert('An error occurred while executing the query.');
				  </script>";
			exit;
		}
	}
	
	public function payee_delete() { 
		$recid = $this->request->getPostGet('recid');

		fods_require_access(\Config\AccessCodes::PAYEE_DELETE, 'Deleting');


		$before = MyHistoryModel::row('tbl_payee', $recid);
		$query = $this->db->query("DELETE FROM `tbl_payee` WHERE `recid` = ?", [$recid]);
		if ($query) { MyHistoryModel::log('payee', $recid, 'DELETED', '', null, $before['payee_name'] ?? ''); }
		$status = "Payee deleted successfully";
		
		if ($query) {
			// Echo JavaScript to show the toast and then redirect
			echo "
			<script>
				toastr.warning('{$status}!', 'Well Done!', {
						progressBar: true,
						closeButton: true,
						timeOut:2000,
					});
				setTimeout(function() {
						window.location.href = 'mypayee?meaction=MAIN'; // Redirect to MAIN view
					}, 1000); // reload after 1 s; the toast still runs its 2.5 s (fods-flash.js carries it over)
			</script>
			";
			exit; // Stop further PHP execution after the toast
		} else {
			// If there's an error, show an alert message
			echo "<script type='text/javascript'>
					alert('An error occurred while executing the query.');
				  </script>";
			exit;
		}
	}
	
} //end main class
?>
