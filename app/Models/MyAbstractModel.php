<?php
namespace App\Models;
use CodeIgniter\Model;

class MyAbstractModel extends Model
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

	public function abstract_save() { 
		$recid = $this->request->getPostGet('recid');
		$prno = $this->request->getPostGet('prno');
		$transaction_no = $this->request->getPostGet('transaction_no');
		$abstract_date = $this->request->getPostGet('abstract_date');
		$availability_date = $this->request->getPostGet('availability_date');
		$bidder_1 = $this->request->getPostGet('bidder_1');
		$bidder_2 = $this->request->getPostGet('bidder_2');
		$bidder_3 = $this->request->getPostGet('bidder_3');
		$bidder_4 = $this->request->getPostGet('bidder_4');
		$bidder_5 = $this->request->getPostGet('bidder_5');
		$abstractdtdata = $this->request->getPostGet('abstractdtdata');


		if (empty($prno)) {
			echo "
			<script>
			toastr.error('PR NO is required!', 'Oops!', {
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
			$query = $this->db->query("
				INSERT INTO `tbl_abstract_hd`(
					`prno`,
					`transaction_no`,
					`abstract_date`,
					`availability_date`,
					`bidder_1`,
					`bidder_2`,
					`bidder_3`,
					`bidder_4`,
					`bidder_5`,
					`added_by`
				)
				VALUES (?, ?, ?,?, ?,?, ?, ?, ?,?)", 
				[
					$prno,
					$transaction_no,
					$abstract_date,
					$availability_date,
					$bidder_1,
					$bidder_2,
					$bidder_3,
					$bidder_4,
					$bidder_5,
					$this->cuser
				]
			);

			$query = $this->db->query("
			SELECT `recid` FROM tbl_abstract_hd WHERE `prno` = '$prno'
			");
			$rw = $query->getRowArray();
			$project_id = $rw['recid'];

			if (!empty($abstractdtdata)) {
				for($aa = 0; $aa < count($abstractdtdata); $aa++){
					$medata = explode("x|x",$abstractdtdata[$aa]);
					$quantity = $medata[0]; 
					$unit = $medata[1]; 
					$item_desc = $medata[2]; 
					$bidder_dt1 = $medata[3];
					$bidder_dt2 = $medata[4];
					$bidder_dt3 = $medata[5]; 
					$bidder_dt4 = $medata[6]; 
					$bidder_dt5 = $medata[7]; 

					$query = $this->db->query("
						INSERT INTO `tbl_abstract_dt`(
							`pr_id`,
							`prno`,
							`quantity`,
							`unit`,
							`item_desc`,
							`bidder_dt1`,
							`bidder_dt2`,
							`bidder_dt3`,
							`bidder_dt4`,
							`bidder_dt5`,
							`added_by`
						)
						VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)", 
						[
							$project_id,
							$prno,
							$quantity,
							$unit,
							$item_desc,
							$bidder_dt1,
							$bidder_dt2,
							$bidder_dt3,
							$bidder_dt4,
							$bidder_dt5,
							$this->cuser
						]
					);
					
				}
			}

			$status = "Abstract Saved Successfully!";
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
					`tbl_abstract_hd`
				SET
					`prno` = ?,
					`transaction_no` = ?,
					`abstract_date` = ?,
					`availability_date` = ?,
					`bidder_1` = ?,
					`bidder_2` = ?,
					`bidder_3` = ?,
					`bidder_4` = ?,
					`bidder_5` = ?
				WHERE recid = ?
			", [
				$prno,
				$transaction_no,
				$abstract_date,
				$availability_date,
				$bidder_1,
				$bidder_2,
				$bidder_3,
				$bidder_4,
				$bidder_5,
				$recid
			]);

			$project_id = $recid;

			if (!empty($abstractdtdata)) {
				$query = $this->db->query("DELETE FROM tbl_abstract_dt WHERE `pr_id` = '$project_id'");
				for($aa = 0; $aa < count($abstractdtdata); $aa++){
					$medata = explode("x|x",$abstractdtdata[$aa]);
					$quantity = $medata[0]; 
					$unit = $medata[1]; 
					$item_desc = $medata[2]; 
					$bidder_dt1 = $medata[3];
					$bidder_dt2 = $medata[4];
					$bidder_dt3 = $medata[5]; 
					$bidder_dt4 = $medata[6]; 
					$bidder_dt5 = $medata[7]; 
					
					$query = $this->db->query("
						INSERT INTO `tbl_abstract_dt`(
							`pr_id`,
							`prno`,
							`quantity`,
							`unit`,
							`item_desc`,
							`bidder_dt1`,
							`bidder_dt2`,
							`bidder_dt3`,
							`bidder_dt4`,
							`bidder_dt5`,
							`added_by`
						)
						VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)", 
						[
							$project_id,
							$prno,
							$quantity,
							$unit,
							$item_desc,
							$bidder_dt1,
							$bidder_dt2,
							$bidder_dt3,
							$bidder_dt4,
							$bidder_dt5,
							$this->cuser
						]
					);
					
				}
			}else{
				$query = $this->db->query("DELETE FROM tbl_abstract_dt WHERE `project_id` = '$project_id'");
			}

			$status = "Abstract Updated Successfully!";
			$color = "info";
		}


		echo "
		<script>
			document.getElementById('submitBtn').disabled = true;

			// Use dynamic toastr method (must be success/error/info/warning)
			toastr.{$color}('{$status}!', 'Well Done!', {
				progressBar: true,
				closeButton: true,
				timeOut: 2500,
			});

			setTimeout(function() {
				window.location.href = 'myabstract?meaction=MAIN&recid=$project_id';
			}, 2500);
		</script>
		";
		exit;


		
	}
	

} //end main class
?>