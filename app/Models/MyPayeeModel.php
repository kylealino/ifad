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
		$is_vatable = $this->request->getPostGet('is_vatable');
		$vat_percent = $this->request->getPostGet('vat_percent');
		$ewt_percent = $this->request->getPostGet('ewt_percent');
		$pt_percent = $this->request->getPostGet('pt_percent');

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


		if (empty($recid)) {
			$accessquery = $this->db->query("
				SELECT `recid`FROM tbl_user_access WHERE `username` = '{$this->cuser}' AND `access_code` = '5002' AND `is_active` = '1'
			");
			if ($accessquery->getNumRows() == 0) {
				echo "
				<script>
				toastr.error('Saving Access Denied! Please Contact the Administrator.', 'Oops!', {
						progressBar: true,
						closeButton: true,
						timeOut:2000,
					});
				</script>
				";
				die();
			}
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
					`is_vatable`,
					`vat_percent`,
					`ewt_percent`,
					`pt_percent`,
					`added_by`
				) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
				  [
					$payee_name,
					$payee_account_num,
					$payee_office,
					$payee_tin,
					$contact_no,
					$payee_address,
					$disb_method,
					$currency,
					$is_vatable,
					$vat_percent,
					$ewt_percent,
					$pt_percent,
					$this->cuser
				]
			);
			$status = "Payee Saved successfully";
			$color = "success";
		}else{
			$accessquery = $this->db->query("
				SELECT `recid`FROM tbl_user_access WHERE `username` = '{$this->cuser}' AND `access_code` = '5003' AND `is_active` = '1'
			");
			if ($accessquery->getNumRows() == 0) {
				echo "
				<script>
				toastr.error('Updating Access Denied! Please Contact the Administrator.', 'Oops!', {
						progressBar: true,
						closeButton: true,
						timeOut:2000,
					});
				</script>
				";
				die();
			}
			$query = $this->db->query("
				UPDATE tbl_payee SET 
				`payee_name` = ?,
				`payee_account_num` = ?,
				`payee_office` = ?,
				`payee_tin` = ?,
				`contact_no` = ?,
				`payee_address` = ?,
				`disb_method` = ?,
				`currency` = ?,
				`is_vatable` = ?,
				`vat_percent` = ?,
				`ewt_percent` = ?,
				`pt_percent` = ?
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
					$is_vatable,
					$vat_percent,
					$ewt_percent,
					$pt_percent,
					$recid
				]
			);
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
						timeOut:2500,
					});
				setTimeout(function() {
						window.location.href = 'mypayee?meaction=MAIN'; // Redirect to MAIN view
					}, 2500); // 2-second delay for user to see the toast
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

		$accessquery = $this->db->query("
			SELECT `recid`FROM tbl_user_access WHERE `username` = '{$this->cuser}' AND `access_code` = '5004' AND `is_active` = '1'
		");
		if ($accessquery->getNumRows() == 0) {
			echo "
			<script>
			toastr.error('Deleting Access Denied! Please Contact the Administrator.', 'Oops!', {
					progressBar: true,
					closeButton: true,
					timeOut:2000,
				});
			</script>
			";
			die();
		}


		$query = $this->db->query("
			DELETE FROM `tbl_payee` WHERE `recid` = '$recid'
		");
		$status = "Payee deleted successfully";
		
		if ($query) {
			// Echo JavaScript to show the toast and then redirect
			echo "
			<script>
				toastr.warning('{$status}!', 'Well Done!', {
						progressBar: true,
						closeButton: true,
						timeOut:2500,
					});
				setTimeout(function() {
						window.location.href = 'mypayee?meaction=MAIN'; // Redirect to MAIN view
					}, 2500); // 2-second delay for user to see the toast
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