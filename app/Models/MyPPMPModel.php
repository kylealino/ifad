<?php
namespace App\Models;
use CodeIgniter\Model;

class MyPPMPModel extends Model
{

    protected $db;

    public function __construct(){
        parent::__construct();
        $this->session = session();
        $this->request = \Config\Services::request();
		$this->mylibzsys = model('App\Models\MyLibzSysModel');
		$this->db = \Config\Database::connect();
		$this->cuser = $this->session->get('__xsys_myuserzicas__');
        
    }

	public function ppmp_save() { 
		$recid = $this->request->getPostGet('recid');
		$ppmpno = $this->request->getPostGet('ppmpno');
		$end_user = $this->request->getPostGet('end_user');
		$fiscal_year = $this->request->getPostGet('fiscal_year');
		$project_title = $this->request->getPostGet('project_title');
		$responsibility_code = $this->request->getPostGet('responsibility_code');
		$ppmpdtdata = $this->request->getPostGet('ppmpdtdata');
		$is_indicative = $this->request->getPostGet('is_indicative');
		$is_final = $this->request->getPostGet('is_final');
		$prepared_by = $this->request->getPostGet('prepared_by');
		$submitted_by = $this->request->getPostGet('submitted_by');

		if (empty($ppmpno)) {
			echo "
			<script>
			toastr.error('PPMP NO is required!', 'Oops!', {
					progressBar: true,
					closeButton: true,
					timeOut:2000,
				});
			</script>
			";
			die();
		}
		if (empty($end_user)) {
			echo "
			<script>
			toastr.error('End user is required!', 'Oops!', {
					progressBar: true,
					closeButton: true,
					timeOut:2000,
				});
			</script>
			";
			die();
		}
		if (empty($fiscal_year)) {
			echo "
			<script>
			toastr.error('Fiscal year is required!', 'Oops!', {
					progressBar: true,
					closeButton: true,
					timeOut:2000,
				});
			</script>
			";
			die();
		}
		if (empty($responsibility_code)) {
			echo "
			<script>
			toastr.error('Responsibility code is required!', 'Oops!', {
					progressBar: true,
					closeButton: true,
					timeOut:2000,
				});
			</script>
			";
			die();
		}
		if (empty($prepared_by)) {
			echo "
			<script>
			toastr.error('Prepared by is required!', 'Oops!', {
					progressBar: true,
					closeButton: true,
					timeOut:2000,
				});
			</script>
			";
			die();
		}
		if (empty($submitted_by)) {
			echo "
			<script>
			toastr.error('Submitted by is required!', 'Oops!', {
					progressBar: true,
					closeButton: true,
					timeOut:2000,
				});
			</script>
			";
			die();
		}
		if (empty($ppmpdtdata)) {
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
		
		if (empty($recid)) {
			// $accessquery = $this->db->query("
			// 	SELECT `recid`FROM tbl_user_access WHERE `username` = '{$this->cuser}' AND `access_code` = '10002' AND `is_active` = '1'
			// ");
			// if ($accessquery->getNumRows() == 0) {
			// 	echo "
			// 	<script>
			// 	toastr.error('Saving Access Denied! Please Contact the Administrator.', 'Oops!', {
			// 			progressBar: true,
			// 			closeButton: true,
			// 			timeOut:2000,
			// 		});
			// 	</script>
			// 	";
			// 	die();
			// }
			//INSERTING HD DATA
			$query = $this->db->query(
				"INSERT INTO `tbl_ppmp_hd`(
					`ppmpno`,
					`end_user`,
					`fiscal_year`,
					`project_title`,
					`responsibility_code`,
					`is_indicative`,
					`is_final`,
					`prepared_by`,
					`submitted_by`,
					`added_by`
				)
				VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
				[
					$ppmpno,
					$end_user,
					$fiscal_year,
					$project_title,
					$responsibility_code,
					$is_indicative,
					$is_final,
					$prepared_by,
					$submitted_by,
					$this->cuser
				]
			);

			$project_id = $this->db->insertID();

			if (!empty($ppmpdtdata)) {
				for($aa = 0; $aa < count($ppmpdtdata); $aa++){
					$medata = explode("x|x",$ppmpdtdata[$aa]);
					$item_desc = $medata[0]; 
					$item_type = $medata[1]; 
					$quantity = $medata[2]; 
					$size = $medata[3];
					$unit_cost = $medata[4];
					$mop = $medata[5]; 
					$is_preproc = $medata[6]; 
					$proc_start = $medata[7]; 
					$proc_end = $medata[8]; 
					$expected_delivery_from = $medata[9]; 
					$expected_delivery_to = $medata[10]; 
					$funding_source = $medata[11]; 
					$estimated_budget = $medata[12]; 
					$attached_document = $medata[13]; 
					$remarks = $medata[14]; 

					$query = $this->db->query("
						INSERT INTO `tbl_ppmp_dt`(
							`ppmp_id`,
							`ppmpno`,
							`item_desc`,
							`item_type`,
							`quantity`,
							`size`,
							`unit_cost`,
							`mop`,
							`is_preproc`,
							`proc_start`,
							`proc_end`,
							`expected_delivery_from`,
							`expected_delivery_to`,
							`funding_source`,
							`estimated_budget`,
							`attached_document`,
							`remarks`,
							`added_by`
						)
						VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)", 
						[
							$project_id,
							$ppmpno,
							$item_desc,
							$item_type,
							$quantity,
							$size,
							$unit_cost,
							$mop,
							$is_preproc,
							$proc_start,
							$proc_end,
							$expected_delivery_from,
							$expected_delivery_to,
							$funding_source,
							$estimated_budget,
							$attached_document,
							$remarks,
							$this->cuser
						]
					);
					
				}
			}


			$status = "PPMP Saved Successfully!";
			$color = "success";
		}else{
			// $accessquery = $this->db->query("
			// 	SELECT `recid`FROM tbl_user_access WHERE `username` = '{$this->cuser}' AND `access_code` = '10003' AND `is_active` = '1'
			// ");
			// if ($accessquery->getNumRows() == 0) {
			// 	echo "
			// 	<script>
			// 	toastr.error('Updating Access Denied! Please Contact the Administrator.', 'Oops!', {
			// 			progressBar: true,
			// 			closeButton: true,
			// 			timeOut:2000,
			// 		});
			// 	</script>
			// 	";
			// 	die();
			// }

			$query = $this->db->query("
				UPDATE
					`tbl_ppmp_hd`
				SET
					`ppmpno` = ?,
					`end_user` = ?,
					`fiscal_year` = ?,
					`project_title` = ?,
					`responsibility_code` = ?,
					`is_indicative` = ?,
					`is_final` = ?,
					`prepared_by` = ?,
					`submitted_by` = ?
				WHERE recid = ?
			", [
				$ppmpno,
				$end_user,
				$fiscal_year,
				$project_title,
				$responsibility_code,
				$is_indicative,
				$is_final,
				$prepared_by,
				$submitted_by,
				$recid
			]);

			$project_id = $recid;


			if (!empty($ppmpdtdata)) {
				$query = $this->db->query("DELETE FROM tbl_ppmp_dt WHERE `ppmp_id` = '$project_id'");
				for($aa = 0; $aa < count($ppmpdtdata); $aa++){
					$medata = explode("x|x",$ppmpdtdata[$aa]);
					$item_desc = $medata[0]; 
					$item_type = $medata[1]; 
					$quantity = $medata[2]; 
					$size = $medata[3];
					$unit_cost = $medata[4];
					$mop = $medata[5]; 
					$is_preproc = $medata[6]; 
					$proc_start = $medata[7]; 
					$proc_end = $medata[8]; 
					$expected_delivery_from = $medata[9]; 
					$expected_delivery_to = $medata[10]; 
					$funding_source = $medata[11]; 
					$estimated_budget = $medata[12]; 
					$attached_document = $medata[13]; 
					$remarks = $medata[14]; 

					$query = $this->db->query("
						INSERT INTO `tbl_ppmp_dt`(
							`ppmp_id`,
							`ppmpno`,
							`item_desc`,
							`item_type`,
							`quantity`,
							`size`,
							`unit_cost`,
							`mop`,
							`is_preproc`,
							`proc_start`,
							`proc_end`,
							`expected_delivery_from`,
							`expected_delivery_to`,
							`funding_source`,
							`estimated_budget`,
							`attached_document`,
							`remarks`,
							`added_by`
						)
						VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)", 
						[
							$project_id,
							$ppmpno,
							$item_desc,
							$item_type,
							$quantity,
							$size,
							$unit_cost,
							$mop,
							$is_preproc,
							$proc_start,
							$proc_end,
							$expected_delivery_from,
							$expected_delivery_to,
							$funding_source,
							$estimated_budget,
							$attached_document,
							$remarks,
							$this->cuser
						]
					);
					
				}
			}else{
				$query = $this->db->query("DELETE FROM tbl_ppmp_dt WHERE `ppmp_id` = '$project_id'");
			}

			$status = "PPMP Updated Successfully!";
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
						window.location.href = 'myppmp?meaction=MAIN'; // Redirect to MAIN view
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