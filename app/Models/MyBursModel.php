<?php
namespace App\Models;
use CodeIgniter\Model;

class MyBursModel extends Model
{

    protected $db;

    public function __construct(){
        parent::__construct();
        $this->session = session();
        $this->request = \Config\Services::request();
		$this->db = \Config\Database::connect();
		$this->cuser = $this->session->get('__xsys_myuserzicas__');
        
    }

	public function burs_save() { 
		$recid = $this->request->getPostGet('recid');
		$serialno = $this->request->getPostGet('serialno');
		$particulars = $this->request->getPostGet('particulars');
		$funding_source = $this->request->getPostGet('funding_source');
		$payee_name = $this->request->getPostGet('payee_name');
		$payee_office = $this->request->getPostGet('payee_office');
		$payee_address = $this->request->getPostGet('payee_address');
		$certified_a = $this->request->getPostGet('certified_a');
		$position_a = $this->request->getPostGet('position_a');
		$certified_b = $this->request->getPostGet('certified_b');
		$position_b = $this->request->getPostGet('position_b');
		$budgetdtdata = $this->request->getPostGet('budgetdtdata');
		$budgetdtindirectdata = $this->request->getPostGet('budgetdtindirectdata');
		$budgetmooedtdata = $this->request->getPostGet('budgetmooedtdata');
		$budgetmooeindirectdtdata = $this->request->getPostGet('budgetmooeindirectdtdata');
		$budgetcodtdata = $this->request->getPostGet('budgetcodtdata');
		$budgetindirectcodtdata = $this->request->getPostGet('budgetindirectcodtdata');
		$burs_date = $this->request->getPostGet('burs_date');
		
		// var_dump($burs_date);
		// die();
		
		// $cseqn =  $this->get_ctr_burs('01',$funding_source,'CTRL_NO01');//TRANSACTION NO
		// $trx = empty($serialno) ? $cseqn : $serialno;

		// var_dump(
		// 	$certified_a,
		// 	$position_a,
		// 	$certified_b,
		// 	$position_b
		// );
		// die();
		// var_dump(
		// 	$program_title,
		// 	$particulars,
		// 	$funding_source,
		// 	$payee_name,
		// 	$payee_office,
		// 	$payee_address,
		// 	$budgetdtdata
		// );
		// die();

		// var_dump($budgetdtdata);
		// die();

		if (empty($funding_source)) {
			echo "
			<script>
			toastr.error('Funding source is required!', 'Oops!', {
					progressBar: true,
					closeButton: true,
					timeOut:2000,
				});
			</script>
			";
			die();
		}
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
		
		if (empty($budgetdtdata) && empty($budgetdtindirectdata) && empty($budgetmooedtdata) && empty($budgetmooeindirectdtdata) && empty($budgetcodtdata) && empty($budgetindirectcodtdata)) {
			echo "
			<script>
			toastr.error('No particulars found!', 'Oops!', {
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
				SELECT `recid`FROM tbl_user_access WHERE `username` = '{$this->cuser}' AND `access_code` = '11002' AND `is_active` = '1'
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
			//INSERTING HD DATA
			//burs SERIALNO
			$query = $this->db->query("
				INSERT INTO tbl_burs_hd (
					`serialno`,
					`particulars`,
					`funding_source`,
					`payee_name`,
					`payee_office`,
					`payee_address`,
					`certified_a`,
					`position_a`,
					`certified_b`,
					`position_b`,
					`added_by`,
					`burs_date`
				)
				VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)", 
				[
					$serialno,
					$particulars,
					$funding_source,
					$payee_name,
					$payee_office,
					$payee_address,
					$certified_a,
					$position_a,
					$certified_b,
					$position_b,
					$this->cuser,
					$burs_date
				]
			);

			$project_id = $this->db->insertID();

			//INSERTING PS DT DATA
			if (!empty($budgetdtdata)) {
				for($aa = 0; $aa < count($budgetdtdata); $aa++){
					$medata = explode("x|x",$budgetdtdata[$aa]);
					$project_title = $medata[0]; 
					$responsibility_code = $medata[1]; 
					$mfopaps_code = $medata[2]; 
					$sub_object_code = $medata[3];
					$uacs_code = $medata[4];  
					$amount = $medata[5]; 

					$query = $this->db->query("
						INSERT INTO tbl_burs_direct_ps_dt (
							`project_id`,
							`project_title`,
							`responsibility_code`,
							`mfopaps_code`,
							`sub_object_code`,
							`uacs_code`,
							`amount`,
							`added_by`
						)
						VALUES (?,?, ?, ?, ?, ?, ?, ?)", 
						[
							$project_id,
							$project_title,
							$responsibility_code,
							$mfopaps_code,
							$sub_object_code,
							$uacs_code,
							$amount,
							$this->cuser
						]
					);
					
				}
			}

			if (!empty($budgetdtindirectdata)) {
				for($aa = 0; $aa < count($budgetdtindirectdata); $aa++){
					$medata = explode("x|x",$budgetdtindirectdata[$aa]);
					$project_title = $medata[0]; 
					$responsibility_code = $medata[1]; 
					$mfopaps_code = $medata[2]; 
					$sub_object_code = $medata[3];
					$uacs_code = $medata[4];  
					$amount = $medata[5]; 

					$query = $this->db->query("
						INSERT INTO tbl_burs_indirect_ps_dt (
							`project_id`,
							`project_title`,
							`responsibility_code`,
							`mfopaps_code`,
							`sub_object_code`,
							`uacs_code`,
							`amount`,
							`added_by`
						)
						VALUES (?, ?, ?, ?, ?, ?, ?, ?)", 
						[
							$project_id,
							$project_title,
							$responsibility_code,
							$mfopaps_code,
							$sub_object_code,
							$uacs_code,
							$amount,
							$this->cuser
						]
					);
					
				}
			}

			//INSERTING MOOE DT DATA
			if (!empty($budgetmooedtdata)) {
				//this is for normal saving and updating
				for($aa = 0; $aa < count($budgetmooedtdata); $aa++){
					$medata = explode("x|x",$budgetmooedtdata[$aa]);
					$project_title = $medata[0]; 
					$responsibility_code = $medata[1]; 
					$mfopaps_code = $medata[2]; 
					$sub_object_code = $medata[3];
					$uacs_code = $medata[4];  
					$amount = $medata[5]; 

					$query = $this->db->query("
						INSERT INTO tbl_burs_direct_mooe_dt (
							`project_id`,
							`project_title`,
							`responsibility_code`,
							`mfopaps_code`,
							`sub_object_code`,
							`uacs_code`,
							`amount`,
							`added_by`
						)
						VALUES (?, ?, ?, ?, ?, ?, ?, ?)", 
						[
							$project_id,
							$project_title,
							$responsibility_code,
							$mfopaps_code,
							$sub_object_code,
							$uacs_code,
							$amount,
							$this->cuser
						]

					);
				}
			}

			//INSERTING INDIRECT MOOE DT DATA
			if (!empty($budgetmooeindirectdtdata)) {
				//this is for normal saving and updating
				for($aa = 0; $aa < count($budgetmooeindirectdtdata); $aa++){
					$medata = explode("x|x",$budgetmooeindirectdtdata[$aa]);
					$project_title = $medata[0]; 
					$responsibility_code = $medata[1]; 
					$mfopaps_code = $medata[2]; 
					$sub_object_code = $medata[3];
					$uacs_code = $medata[4];  
					$amount = $medata[5]; 

					$query = $this->db->query("
						INSERT INTO tbl_burs_indirect_mooe_dt (
							`project_id`,
							`project_title`,
							`responsibility_code`,
							`mfopaps_code`,
							`sub_object_code`,
							`uacs_code`,
							`amount`,
							`added_by`
						)
						VALUES (?, ?, ?, ?, ?, ?, ?, ?)", 
						[
							$project_id,
							$project_title,
							$responsibility_code,
							$mfopaps_code,
							$sub_object_code,
							$uacs_code,
							$amount,
							$this->cuser
						]

					);
				}
			}

			//INSERTING CO DT DATA
			if (!empty($budgetcodtdata)) {
				//this is for normal saving and updating
				for($aa = 0; $aa < count($budgetcodtdata); $aa++){
					$medata = explode("x|x",$budgetcodtdata[$aa]);
					$project_title = $medata[0]; 
					$responsibility_code = $medata[1]; 
					$mfopaps_code = $medata[2]; 
					$sub_object_code = $medata[3];
					$uacs_code = $medata[4];  
					$amount = $medata[5]; 

					$query = $this->db->query("
						INSERT INTO tbl_burs_direct_co_dt (
							`project_id`,
							`project_title`,
							`responsibility_code`,
							`mfopaps_code`,
							`sub_object_code`,
							`uacs_code`,
							`amount`,
							`added_by`
						)
						VALUES (?, ?, ?, ?, ?, ?, ?, ?)", 
						[
							$project_id,
							$project_title,
							$responsibility_code,
							$mfopaps_code,
							$sub_object_code,
							$uacs_code,
							$amount,
							$this->cuser
						]

					);
				}
			}

			if (!empty($budgetindirectcodtdata)) {
				//this is for normal saving and updating
				for($aa = 0; $aa < count($budgetindirectcodtdata); $aa++){
					$medata = explode("x|x",$budgetindirectcodtdata[$aa]);
					$project_title = $medata[0]; 
					$responsibility_code = $medata[1]; 
					$mfopaps_code = $medata[2]; 
					$sub_object_code = $medata[3];
					$uacs_code = $medata[4];  
					$amount = $medata[5]; 

					$query = $this->db->query("
						INSERT INTO tbl_burs_indirect_co_dt (
							`project_id`,
							`project_title`,
							`responsibility_code`,
							`mfopaps_code`,
							`sub_object_code`,
							`uacs_code`,
							`amount`,
							`added_by`
						)
						VALUES (?, ?, ?, ?, ?, ?, ?, ?)", 
						[
							$project_id,
							$project_title,
							$responsibility_code,
							$mfopaps_code,
							$sub_object_code,
							$uacs_code,
							$amount,
							$this->cuser
						]

					);
				}
			}

			$status = "BURS Saved Successfully!";
			$color = "success";
		}else{
			$accessquery = $this->db->query("
				SELECT `recid`FROM tbl_user_access WHERE `username` = '{$this->cuser}' AND `access_code` = '11003' AND `is_active` = '1'
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
				UPDATE tbl_burs_hd
				SET
					`particulars` = ?,
					`serialno` = ?,
					`funding_source` = ?,
					`payee_name` = ?,
					`payee_office` = ?,
					`payee_address` = ?,
					`certified_a` = ?,
					`position_a` = ?,
					`certified_b` = ?,
					`position_b` = ?,
					`burs_date` = ?
				WHERE recid = ?
			", [
				$particulars,
				$serialno,
				$funding_source,
				$payee_name,
				$payee_office,
				$payee_address,
				$certified_a,
				$position_a,
				$certified_b,
				$position_b,
				$burs_date,
				$recid
			]);

			$project_id = $recid;

			if (!empty($budgetdtdata)) {
				$query = $this->db->query("DELETE FROM tbl_burs_direct_ps_dt WHERE `project_id` = '$project_id'");
				for($aa = 0; $aa < count($budgetdtdata); $aa++){
					$medata = explode("x|x",$budgetdtdata[$aa]);
					$project_title = $medata[0]; 
					$responsibility_code = $medata[1]; 
					$mfopaps_code = $medata[2]; 
					$sub_object_code = $medata[3];
					$uacs_code = $medata[4];  
					$amount = $medata[5]; 

					$query = $this->db->query("
						INSERT INTO tbl_burs_direct_ps_dt (
							`project_id`,
							`project_title`,
							`responsibility_code`,
							`mfopaps_code`,
							`sub_object_code`,
							`uacs_code`,
							`amount`,
							`added_by`
						)
						VALUES (?, ?, ?, ?, ?, ?, ?, ?)", 
						[
							$project_id,
							$project_title,
							$responsibility_code,
							$mfopaps_code,
							$sub_object_code,
							$uacs_code,
							$amount,
							$this->cuser
						]
					);
					
				}
			}else{
				$query = $this->db->query("DELETE FROM tbl_burs_direct_ps_dt WHERE `project_id` = '$project_id'");
			}

			if (!empty($budgetdtindirectdata)) {
				$query = $this->db->query("DELETE FROM tbl_burs_indirect_ps_dt WHERE `project_id` = '$project_id'");
				for($aa = 0; $aa < count($budgetdtindirectdata); $aa++){
					$medata = explode("x|x",$budgetdtindirectdata[$aa]);
					$project_title = $medata[0]; 
					$responsibility_code = $medata[1]; 
					$mfopaps_code = $medata[2]; 
					$sub_object_code = $medata[3];
					$uacs_code = $medata[4];  
					$amount = $medata[5]; 

					$query = $this->db->query("
						INSERT INTO tbl_burs_indirect_ps_dt (
							`project_id`,
							`project_title`,
							`responsibility_code`,
							`mfopaps_code`,
							`sub_object_code`,
							`uacs_code`,
							`amount`,
							`added_by`
						)
						VALUES (?, ?, ?, ?, ?, ?, ?, ?)", 
						[
							$project_id,
							$project_title,
							$responsibility_code,
							$mfopaps_code,
							$sub_object_code,
							$uacs_code,
							$amount,
							$this->cuser
						]
					);
					
				}
			}else{
				$query = $this->db->query("DELETE FROM tbl_burs_indirect_ps_dt WHERE `project_id` = '$project_id'");
			}

			if (!empty($budgetmooedtdata)) {
				$query = $this->db->query("DELETE FROM tbl_burs_direct_mooe_dt WHERE `project_id` = '$project_id'");
				for($aa = 0; $aa < count($budgetmooedtdata); $aa++){
					$medata = explode("x|x",$budgetmooedtdata[$aa]);
					$project_title = $medata[0]; 
					$responsibility_code = $medata[1]; 
					$mfopaps_code = $medata[2]; 
					$sub_object_code = $medata[3];
					$uacs_code = $medata[4];  
					$amount = $medata[5]; 

					$query = $this->db->query("
						INSERT INTO tbl_burs_direct_mooe_dt (
							`project_id`,
							`project_title`,
							`responsibility_code`,
							`mfopaps_code`,
							`sub_object_code`,
							`uacs_code`,
							`amount`,
							`added_by`
						)
						VALUES (?, ?, ?, ?, ?, ?, ?, ?)", 
						[
							$project_id,
							$project_title,
							$responsibility_code,
							$mfopaps_code,
							$sub_object_code,
							$uacs_code,
							$amount,
							$this->cuser
						]

					);
					
				}
			}else{
				$query = $this->db->query("DELETE FROM tbl_burs_direct_mooe_dt WHERE `project_id` = '$project_id'");
			}

			if (!empty($budgetmooeindirectdtdata)) {
				$query = $this->db->query("DELETE FROM tbl_burs_indirect_mooe_dt WHERE `project_id` = '$project_id'");
				for($aa = 0; $aa < count($budgetmooeindirectdtdata); $aa++){
					$medata = explode("x|x",$budgetmooeindirectdtdata[$aa]);
					$project_title = $medata[0]; 
					$responsibility_code = $medata[1]; 
					$mfopaps_code = $medata[2]; 
					$sub_object_code = $medata[3];
					$uacs_code = $medata[4];  
					$amount = $medata[5]; 

					$query = $this->db->query("
						INSERT INTO tbl_burs_indirect_mooe_dt (
							`project_id`,
							`project_title`,
							`responsibility_code`,
							`mfopaps_code`,
							`sub_object_code`,
							`uacs_code`,
							`amount`,
							`added_by`
						)
						VALUES (?, ?, ?, ?, ?, ?, ?, ?)", 
						[
							$project_id,
							$project_title,
							$responsibility_code,
							$mfopaps_code,
							$sub_object_code,
							$uacs_code,
							$amount,
							$this->cuser
						]

					);
					
				}
			}else{
				$query = $this->db->query("DELETE FROM tbl_burs_indirect_mooe_dt WHERE `project_id` = '$project_id'");
			}

			if (!empty($budgetcodtdata)) {
				$query = $this->db->query("DELETE FROM tbl_burs_direct_co_dt WHERE `project_id` = '$project_id'");
				for($aa = 0; $aa < count($budgetcodtdata); $aa++){
					$medata = explode("x|x",$budgetcodtdata[$aa]);
					$project_title = $medata[0]; 
					$responsibility_code = $medata[1]; 
					$mfopaps_code = $medata[2]; 
					$sub_object_code = $medata[3];
					$uacs_code = $medata[4];  
					$amount = $medata[5]; 

					$query = $this->db->query("
						INSERT INTO tbl_burs_direct_co_dt (
							`project_id`,
							`project_title`,
							`responsibility_code`,
							`mfopaps_code`,
							`sub_object_code`,
							`uacs_code`,
							`amount`,
							`added_by`
						)
						VALUES (?, ?, ?, ?, ?, ?, ?, ?)", 
						[
							$project_id,
							$project_title,
							$responsibility_code,
							$mfopaps_code,
							$sub_object_code,
							$uacs_code,
							$amount,
							$this->cuser
						]

					);
					
				}
			}else{
				$query = $this->db->query("DELETE FROM tbl_burs_direct_co_dt WHERE `project_id` = '$project_id'");
			}

			if (!empty($budgetindirectcodtdata)) {
				$query = $this->db->query("DELETE FROM tbl_burs_indirect_co_dt WHERE `project_id` = '$project_id'");
				for($aa = 0; $aa < count($budgetindirectcodtdata); $aa++){
					$medata = explode("x|x",$budgetindirectcodtdata[$aa]);
					$project_title = $medata[0]; 
					$responsibility_code = $medata[1]; 
					$mfopaps_code = $medata[2]; 
					$sub_object_code = $medata[3];
					$uacs_code = $medata[4];  
					$amount = $medata[5]; 

					$query = $this->db->query("
						INSERT INTO tbl_burs_indirect_co_dt (
							`project_id`,
							`project_title`,
							`responsibility_code`,
							`mfopaps_code`,
							`sub_object_code`,
							`uacs_code`,
							`amount`,
							`added_by`
						)
						VALUES (?, ?, ?, ?, ?, ?, ?, ?)", 
						[
							$project_id,
							$project_title,
							$responsibility_code,
							$mfopaps_code,
							$sub_object_code,
							$uacs_code,
							$amount,
							$this->cuser
						]

					);
					
				}
			}else{
				$query = $this->db->query("DELETE FROM tbl_burs_indirect_co_dt WHERE `project_id` = '$project_id'");
			}

			$status = "BURS Updated Successfully!";
			$color = "info";
		}

		if ($query) {
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
						window.location.href = 'myburs?meaction=MAIN'; // Redirect to MAIN view
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
	
	//CERTIFIED A APPROVAL/DISAPPROVAL
	public function burs_certifya_approve() { 
		$recid = $this->request->getPostGet('recid');
		$approver = $this->request->getPostGet('approver');
		$remarks = $this->request->getPostGet('remarks');
		$serialno = $this->request->getPostGet('serialno');
		$funding_source = $this->request->getPostGet('funding_source');

		// $cseqn =  $this->get_ctr_burs('01',$funding_source,'CTRL_NO01');//TRANSACTION NO
		// $trx = empty($serialno) ? $cseqn : $serialno;

		$accessquery = $this->db->query("
			SELECT `recid` FROM tbl_user_access WHERE `username` = '{$this->cuser}' AND `access_code` = '2005' AND `is_active` = '1'
		");
		if ($accessquery->getNumRows() == 0) {
			echo "
			<script>
			toastr.error('Approve Access Denied! Please Contact the Administrator.', 'Oops!', {
					progressBar: true,
					closeButton: true,
					timeOut:2000,
				});
			</script>
			";
			die();
		}

		$query = $this->db->query("
			UPDATE tbl_burs_hd 
			SET 
				`is_pending` = '0', 
				`is_approved_certa` = '1',
				`is_disapproved_certa` = '0',
				`certa_approver` = '$approver', 
				`certa_remarks` = '$remarks'
			WHERE `recid` = '$recid'
		");
		$status = "burs approved!";
		
		if ($query) {
			// Echo JavaScript to show the toast and then redirect
			echo "
			<script>
				toastr.success('{$status}!', 'Well Done!', {
						progressBar: true,
						closeButton: true,
						timeOut:2500,
					});
				setTimeout(function() {
						window.location.href = 'mybursapproval?meaction=MAIN'; // Redirect to MAIN view
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

	public function burs_certifya_disapprove() { 
		$recid = $this->request->getPostGet('recid');
		$approver = $this->request->getPostGet('approver');
		$remarks = $this->request->getPostGet('remarks');

		$accessquery = $this->db->query("
			SELECT `recid`FROM tbl_user_access WHERE `username` = '{$this->cuser}' AND `access_code` = '2005' AND `is_active` = '1'
		");
		if ($accessquery->getNumRows() == 0) {
			echo "
			<script>
			toastr.error('Disapprove Access Denied! Please Contact the Administrator.', 'Oops!', {
					progressBar: true,
					closeButton: true,
					timeOut:2000,
				});
			</script>
			";
			die();
		}

		$query = $this->db->query("
			UPDATE tbl_burs_hd SET `is_pending` = '0', `is_approved_certa` = '0',`is_disapproved_certa` = '1',`certa_approver` = '$approver', `certa_remarks` = '$remarks' WHERE `recid` = '$recid'
		");
		$status = "burs disapproved!";
		
		if ($query) {
			// Echo JavaScript to show the toast and then redirect
			echo "
			<script>
				toastr.success('{$status}!', 'Well Done!', {
						progressBar: true,
						closeButton: true,
						timeOut:2500,
					});
				setTimeout(function() {
						window.location.href = 'mybursapproval?meaction=MAIN'; // Redirect to MAIN view
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

	// CERTIFIED B APPROVAL/DISAPPROVAL
	public function burs_certifyb_approve() { 
		$recid = $this->request->getPostGet('recid');
		$approver = $this->request->getPostGet('approver');
		$remarks = $this->request->getPostGet('remarks');

		$accessquery = $this->db->query("
			SELECT `recid` FROM tbl_user_access WHERE `username` = '{$this->cuser}' AND `access_code` = '2006' AND `is_active` = '1'
		");
		if ($accessquery->getNumRows() == 0) {
			echo "
			<script>
			toastr.error('Approve Access Denied! Please Contact the Administrator.', 'Oops!', {
					progressBar: true,
					closeButton: true,
					timeOut:2000,
				});
			</script>
			";
			die();
		}

		$query = $this->db->query("
			UPDATE tbl_burs_hd SET `is_pending` = '0', `is_approved_certb` = '1', `is_disapproved_certb` = '0',`certb_approver` = '$approver', `certb_remarks` = '$remarks' WHERE `recid` = '$recid'
		");
		$status = "burs approved!";
		
		if ($query) {
			// Echo JavaScript to show the toast and then redirect
			echo "
			<script>
				toastr.success('{$status}!', 'Well Done!', {
						progressBar: true,
						closeButton: true,
						timeOut:2500,
					});
				setTimeout(function() {
						window.location.href = 'mybursapproval?meaction=MAIN'; // Redirect to MAIN view
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

	public function burs_certifyb_disapprove() { 
		$recid = $this->request->getPostGet('recid');
		$approver = $this->request->getPostGet('approver');
		$remarks = $this->request->getPostGet('remarks');

		$accessquery = $this->db->query("
			SELECT `recid`FROM tbl_user_access WHERE `username` = '{$this->cuser}' AND `access_code` = '2006' AND `is_active` = '1'
		");
		if ($accessquery->getNumRows() == 0) {
			echo "
			<script>
			toastr.error('Disapprove Access Denied! Please Contact the Administrator.', 'Oops!', {
					progressBar: true,
					closeButton: true,
					timeOut:2000,
				});
			</script>
			";
			die();
		}

		$query = $this->db->query("
			UPDATE tbl_burs_hd SET `is_pending` = '0', `is_approved_certb` = '0',`is_disapproved_certb` = '1',`certb_approver` = '$approver', `certb_remarks` = '$remarks' WHERE `recid` = '$recid'
		");
		$status = "burs disapproved!";
		
		if ($query) {
			// Echo JavaScript to show the toast and then redirect
			echo "
			<script>
				toastr.success('{$status}!', 'Well Done!', {
						progressBar: true,
						closeButton: true,
						timeOut:2500,
					});
				setTimeout(function() {
						window.location.href = 'mybursapproval?meaction=MAIN'; // Redirect to MAIN view
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

	public function get_ctr_burs($fund_cluster,$funding_source,$mfld='') { 
		$accessquery = $this->db->query("
		CREATE TABLE if not exists `myctr_burs` (
		  `CTR_YEAR` varchar(4) DEFAULT '0000',
		  `CTR_MONTH` varchar(2) DEFAULT '00',
		  `CTR_DAY` varchar(2) DEFAULT '00',
		  `CTRL_NO01` varchar(15) DEFAULT '000',
		  `CTRL_NO02` varchar(15) DEFAULT '00000000',
		  `CTRL_NO03` varchar(15) DEFAULT '00000000',
		  `CTRL_NO04` varchar(15) DEFAULT '00000000',
		  `CTRL_NO05` varchar(15) DEFAULT '00000000',
		  `CTRL_NO06` varchar(15) DEFAULT '00000000',
		  `CTRL_NO07` varchar(15) DEFAULT '00000000',
		  `CTRL_NO08` varchar(15) DEFAULT '00000000',
		  `CTRL_NO09` varchar(15) DEFAULT '00000000',
		  `CTRL_NO10` varchar(15) DEFAULT '00000000',
		  `CTRL_NO11` varchar(15) DEFAULT '00000000',
		  `SS_CTR` varchar(15) DEFAULT '000000',
		  UNIQUE KEY `ctr01` (`CTR_YEAR`,`CTR_MONTH`,`CTR_DAY`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8;
		");

		$xfield = (empty($mfld) ? 'CTRL_NO01' : $mfld);
		
		$q = $this->db->query("select date(now()) XSYSDATE");
		$rdate = $q->getRowArray();
		$xsysdate = $rdate['XSYSDATE'];
		$xsysdate_exp = explode('-', $xsysdate);
		$xsysyear =  $xsysdate_exp[0];
		$xsysmonth = $xsysdate_exp[1];
		$xsysday = $xsysdate_exp[2];
		
		$qctr = $this->db->query("select {$xfield} from myctr_burs WHERE CTR_YEAR = '$xsysyear' AND CTR_MONTH = '$xsysmonth' AND CTR_DAY = '$xsysday'  limit 1");
		if($qctr->getNumRows() == 0) {
			$xnumb = '001';
			$query = $this->db->query( "insert into myctr_burs (CTR_YEAR,CTR_MONTH,CTR_DAY,{$xfield}) values('$xsysyear','$xsysmonth','$xsysday','$xnumb')");
			$qctr->freeResult();
		} else {
			$qctr->freeResult();
			$qctr = $this->db->query( "select {$xfield} MYFIELD from myctr_burs WHERE CTR_YEAR = '$xsysyear' AND CTR_MONTH = '$xsysmonth' AND CTR_DAY = '$xsysday' limit 1");
			$rctr = $qctr->getRowArray();
			if(trim($rctr['MYFIELD'],' ') == '') { 
				$xnumb = '001';
			} else {
				$xnumb = $rctr['MYFIELD'];
				$qctr = $this->db->query("select ('{$xnumb}' + 1) XNUMB");
				$rctr = $qctr->getRowArray();
				$xnumb = trim($rctr['XNUMB'],' ');
				$xnumb = str_pad($xnumb + 0,3,"0",STR_PAD_LEFT);
				$query = $this->db->query("update myctr_burs set {$xfield} = '{$xnumb}'");
			}
		}
		return  $fund_cluster . '-' . $funding_source . '-' . $xsysyear . '-' . $xsysmonth . '-' . $xnumb;//.$supp
	} 

} //end main class
?>