<?php
namespace App\Models;
use CodeIgniter\Model;

class MyPRModel extends Model
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

	public function pr_save() { 
		$recid = $this->request->getPostGet('recid');
		$entity_name = $this->request->getPostGet('entity_name');
		$office = $this->request->getPostGet('office');
		$prno = $this->request->getPostGet('prno');
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


		if (empty($recid)) {

			//INSERTING HD DATA
			$query = $this->db->query("
				INSERT INTO `tbl_pr_hd`(
					`entity_name`,
					`office`,
					`prno`,
					`responsibility_code`,
					`fund_cluster`,
					`pr_date`,
					`end_user`,
					`charge_to`,
					`purpose`,
					`estimated_cost`,
					`added_by`
				)
				VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)", 
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
					$this->cuser
				]
			);

			$project_id = $this->db->insertID();

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

			if (!empty($prdtdata)) {
				for($aa = 0; $aa < count($prdtdata); $aa++){
					$medata = explode("x|x",$prdtdata[$aa]);
					$item_desc = $medata[0]; 
					$unit = $medata[1]; 
					$quantity = $medata[2]; 
					$unit_cost = $medata[3];
					$total_cost = $medata[4];  

					$query = $this->db->query("
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
			}


			$status = "PR Saved Successfully!";
			$color = "success";
		}else{
			$query = $this->db->query("
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
					`estimated_cost` = ?
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
				$recid
			]);

			$project_id = $recid;

			if (!empty($ppmp_list)) {
				$query = $this->db->query("DELETE FROM tbl_pr_ppmp WHERE `pr_id` = '$project_id'");
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
			}else{
				$query = $this->db->query("DELETE FROM tbl_pr_ppmp WHERE `pr_id` = '$project_id'");
			}

			//UPDATE OR INSERT OF NEW ROW DATA
			if (!empty($prdtdata)) {
				$query = $this->db->query("DELETE FROM tbl_pr_dt WHERE `pr_id` = '$recid'");
				for($aa = 0; $aa < count($prdtdata); $aa++){
					$medata = explode("x|x",$prdtdata[$aa]);
					$item_desc = $medata[0]; 
					$unit = $medata[1]; 
					$quantity = $medata[2]; 
					$unit_cost = $medata[3];
					$total_cost = $medata[4];  

					$query = $this->db->query("
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
							$recid,
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
			}else{
				$query = $this->db->query("DELETE FROM tbl_pr_dt WHERE `pr_id` = '$project_id'");
			}

			$status = "PR Updated Successfully!";
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
						window.location.href = 'mypr?meaction=PR-MAIN&recid=$project_id'; // Redirect to MAIN view
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

	public function pr_rfq_save() { 
		$recid = $this->request->getPostGet('recid');
		$prno = $this->request->getPostGet('prno');
		$quotation_no = $this->request->getPostGet('quotation_no');
		$quotation_date = $this->request->getPostGet('quotation_date');
		$company_name = $this->request->getPostGet('company_name');
		$company_address = $this->request->getPostGet('company_address');
		$delivery_period = $this->request->getPostGet('delivery_period');
		$terms = $this->request->getPostGet('terms');

		//INSERTING HD DATA
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

		$status = "RFQ Created Successfully!";
		$color = "success";
		

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
						window.location.href = 'myprocurement?meaction=PR-MAIN&recid=$recid'; // Redirect to MAIN view
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