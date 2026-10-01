<?php
namespace App\Models;
use CodeIgniter\Model;

class MyOrsModel extends Model
{

    protected $db;

    public function __construct(){
        parent::__construct();
        $this->session = session();
        $this->request = \Config\Services::request();
		$this->db = \Config\Database::connect();
		$this->cuser = $this->session->get('__xsys_myuserzicas__');
        
    }

	public function ors_save() { 
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
		$ors_date = $this->request->getPostGet('ors_date');
		$fund_cluster_code = $this->request->getPostGet('fund_cluster_code');
		$category = $this->request->getPostGet('category');
		$is_vatable = $this->request->getPostGet('is_vatable');
		$vat_percent = $this->request->getPostGet('vat_percent');
		$ewt_percent = $this->request->getPostGet('ewt_percent');
		$pt_percent = $this->request->getPostGet('pt_percent');
		$gross_amount = $this->request->getPostGet('gross_amount');
		$total_deduction = $this->request->getPostGet('total_deduction');
		$net_amount = $this->request->getPostGet('net_amount');
		$other_deductions = $this->request->getPostGet('other_deductions');
		$income_tax = $this->request->getPostGet('income_tax');
		$deduction_name = $this->request->getPostGet('deduction_name');
		$sss = $this->request->getPostGet('sss');
		$hdmf = $this->request->getPostGet('hdmf');
		$pagibig_mp2 = $this->request->getPostGet('pagibig_mp2');
		$pagibig_loan = $this->request->getPostGet('pagibig_loan');
		$hmo = $this->request->getPostGet('hmo');
		$wtax = $this->request->getPostGet('wtax');
		// var_dump($ors_date);
		// die();
		
		// $cseqn =  $this->get_ctr_ors('01',$funding_source,'CTRL_NO01');//TRANSACTION NO
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

			fods_require_access(\Config\AccessCodes::ORS_ADD, 'Saving');
			//INSERTING HD DATA
			//ORS SERIALNO
			$query = $this->db->query("
				INSERT INTO tbl_ors_hd (
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
					`ors_date`,
					`fund_cluster_code`,
					`is_vatable`,
					`vat_percent`,
					`ewt_percent`,
					`pt_percent`,
					`other_deductions`,
					`income_tax`,
					`deduction_name`,
					`sss`,
					`hdmf`,
					`pagibig_mp2`,
					`pagibig_loan`,
					`hmo`,
					`wtax`,
					`category`,
					`gross_amount`,
					`total_deduction`,
					`net_amount`
				)
				VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)", 
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
					$ors_date,
					$fund_cluster_code,
					$is_vatable,
					$vat_percent,
					$ewt_percent,
					$pt_percent,
					$other_deductions,
					$income_tax,
					$deduction_name,
					$sss,
					$hdmf,
					$pagibig_mp2,
					$pagibig_loan,
					$hmo,
					$wtax,
					$category,
					$gross_amount,
					$total_deduction,
					$net_amount
				]
			);

			$project_id = $this->db->insertID();

			//INSERTING PS DT DATA
			if (!empty($budgetdtdata)) {
				for($aa = 0; $aa < count($budgetdtdata); $aa++){
					$medata = explode("x|x",$budgetdtdata[$aa]);
					$program_title = $medata[0]; 
					$project_title = $medata[1]; 
					$responsibility_code = $medata[2]; 
					$mfopaps_code = $medata[3];
					$sub_object_code = $medata[4];  
					$uacs_code = $medata[5]; 
					$amount = $medata[6]; 

					$query = $this->db->query("
						INSERT INTO tbl_ors_direct_ps_dt (
							`project_id`,
							`program_title`,
							`project_title`,
							`responsibility_code`,
							`mfopaps_code`,
							`sub_object_code`,
							`uacs_code`,
							`amount`,
							`added_by`
						)
						VALUES (?,?, ?, ?, ?, ?, ?, ?,?)", 
						[
							$project_id,
							$program_title,
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
					$program_title = $medata[0]; 
					$project_title = $medata[1]; 
					$responsibility_code = $medata[2]; 
					$mfopaps_code = $medata[3];
					$sub_object_code = $medata[4];  
					$uacs_code = $medata[5]; 
					$amount = $medata[6]; 

					$query = $this->db->query("
						INSERT INTO tbl_ors_indirect_ps_dt (
							`project_id`,
							`program_title`,
							`project_title`,
							`responsibility_code`,
							`mfopaps_code`,
							`sub_object_code`,
							`uacs_code`,
							`amount`,
							`added_by`
						)
						VALUES (?, ?, ?, ?, ?, ?, ?, ?,?)", 
						[
							$project_id,
							$program_title,
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
					$program_title = $medata[0]; 
					$project_title = $medata[1]; 
					$responsibility_code = $medata[2]; 
					$mfopaps_code = $medata[3];
					$sub_object_code = $medata[4];  
					$uacs_code = $medata[5]; 
					$amount = $medata[6]; 

					$query = $this->db->query("
						INSERT INTO tbl_ors_direct_mooe_dt (
							`project_id`,
							`program_title`,
							`project_title`,
							`responsibility_code`,
							`mfopaps_code`,
							`sub_object_code`,
							`uacs_code`,
							`amount`,
							`added_by`
						)
						VALUES (?, ?, ?, ?, ?, ?, ?, ?,?)", 
						[
							$project_id,
							$program_title,
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
					$program_title = $medata[0]; 
					$project_title = $medata[1]; 
					$responsibility_code = $medata[2]; 
					$mfopaps_code = $medata[3];
					$sub_object_code = $medata[4];  
					$uacs_code = $medata[5]; 
					$amount = $medata[6]; 

					$query = $this->db->query("
						INSERT INTO tbl_ors_indirect_mooe_dt (
							`project_id`,
							`program_title`,
							`project_title`,
							`responsibility_code`,
							`mfopaps_code`,
							`sub_object_code`,
							`uacs_code`,
							`amount`,
							`added_by`
						)
						VALUES (?, ?, ?, ?, ?, ?, ?, ?,?)", 
						[
							$project_id,
							$program_title,
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
					$program_title = $medata[0]; 
					$project_title = $medata[1]; 
					$responsibility_code = $medata[2]; 
					$mfopaps_code = $medata[3];
					$sub_object_code = $medata[4];  
					$uacs_code = $medata[5]; 
					$amount = $medata[6]; 

					$query = $this->db->query("
						INSERT INTO tbl_ors_direct_co_dt (
							`project_id`,
							`program_title`,
							`project_title`,
							`responsibility_code`,
							`mfopaps_code`,
							`sub_object_code`,
							`uacs_code`,
							`amount`,
							`added_by`
						)
						VALUES (?, ?, ?, ?, ?, ?, ?, ?,?)", 
						[
							$project_id,
							$program_title,
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
					$program_title = $medata[0]; 
					$project_title = $medata[1]; 
					$responsibility_code = $medata[2]; 
					$mfopaps_code = $medata[3];
					$sub_object_code = $medata[4];  
					$uacs_code = $medata[5]; 
					$amount = $medata[6]; 

					$query = $this->db->query("
						INSERT INTO tbl_ors_indirect_co_dt (
							`project_id`,
							`program_title`,
							`project_title`,
							`responsibility_code`,
							`mfopaps_code`,
							`sub_object_code`,
							`uacs_code`,
							`amount`,
							`added_by`
						)
						VALUES (?, ?, ?, ?, ?, ?, ?, ?,?)", 
						[
							$project_id,
							$program_title,
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

			$status = "ORS saved successfully";
			$color = "success";
		}else{
			fods_require_access(\Config\AccessCodes::ORS_UPDATE, 'Updating');

			// Once the requesting office has certified it, the ORS is locked;
			// a disapproved ORS can be corrected and goes back to Certification A
			$current = $this->db->query("SELECT `is_approved_certa`, `is_disapproved_certa`, `is_approved_certb`, `is_disapproved_certb` FROM tbl_ors_hd WHERE `recid` = ?", [$recid])->getRowArray();
			if (empty($current)) {
				echo "
				<script>
				toastr.error('ORS not found', 'Oops!', {
						progressBar: true,
						closeButton: true,
						timeOut:2000,
					});
				</script>
				";
				die();
			}
			$is_disapproved = $current['is_disapproved_certa'] == '1' || $current['is_disapproved_certb'] == '1';
			if ($current['is_approved_certa'] == '1' && !$is_disapproved) {
				echo "
				<script>
				toastr.error('This ORS is already certified and can no longer be edited', 'Locked', {
						progressBar: true,
						closeButton: true,
						timeOut:2500,
					});
				</script>
				";
				die();
			}
			if ($is_disapproved) {
				$this->db->query("
					UPDATE tbl_ors_hd
					SET
						`is_pending` = '1',
						`is_approved_certa` = '0',
						`is_disapproved_certa` = '0',
						`is_approved_certb` = '0',
						`is_disapproved_certb` = '0'
					WHERE `recid` = ?
				", [$recid]);
			}

			$query = $this->db->query("
				UPDATE tbl_ors_hd
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
					`ors_date` = ?,
					`fund_cluster_code` = ?,
					`is_vatable` = ?,
					`vat_percent` = ?,
					`ewt_percent` = ?,
					`pt_percent` = ?,
					`other_deductions` = ?,
					`income_tax` = ?,
					`deduction_name` = ?,
					`sss` = ?,
					`hdmf` = ?,
					`pagibig_mp2` = ?,
					`pagibig_loan` = ?,
					`hmo` = ?,
					`wtax` = ?,
					`category` = ?,
					`gross_amount` = ?,
					`total_deduction` = ?,
					`net_amount` = ?
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
				$ors_date,
				$fund_cluster_code,
				$is_vatable,
				$vat_percent,
				$ewt_percent,
				$pt_percent,
				$other_deductions,
				$income_tax,
				$deduction_name,
				$sss,
				$hdmf,
				$pagibig_mp2,
				$pagibig_loan,
				$hmo,
				$wtax,
				$category,
				$gross_amount,
				$total_deduction,
				$net_amount,
				$recid
			]);

			$project_id = $recid;

			if (!empty($budgetdtdata)) {
				$query = $this->db->query("DELETE FROM tbl_ors_direct_ps_dt WHERE `project_id` = ?", [$project_id]);
				for($aa = 0; $aa < count($budgetdtdata); $aa++){
					$medata = explode("x|x",$budgetdtdata[$aa]);
					$program_title = $medata[0]; 
					$project_title = $medata[1]; 
					$responsibility_code = $medata[2]; 
					$mfopaps_code = $medata[3];
					$sub_object_code = $medata[4];  
					$uacs_code = $medata[5]; 
					$amount = $medata[6]; 

					$query = $this->db->query("
						INSERT INTO tbl_ors_direct_ps_dt (
							`program_title`,
							`project_id`,
							`project_title`,
							`responsibility_code`,
							`mfopaps_code`,
							`sub_object_code`,
							`uacs_code`,
							`amount`,
							`added_by`
						)
						VALUES (?, ?, ?, ?, ?, ?, ?, ?,?)", 
						[
							$program_title,
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
				$query = $this->db->query("DELETE FROM tbl_ors_direct_ps_dt WHERE `project_id` = ?", [$project_id]);
			}

			if (!empty($budgetdtindirectdata)) {
				$query = $this->db->query("DELETE FROM tbl_ors_indirect_ps_dt WHERE `project_id` = ?", [$project_id]);
				for($aa = 0; $aa < count($budgetdtindirectdata); $aa++){
					$medata = explode("x|x",$budgetdtindirectdata[$aa]);
					$program_title = $medata[0]; 
					$project_title = $medata[1]; 
					$responsibility_code = $medata[2]; 
					$mfopaps_code = $medata[3];
					$sub_object_code = $medata[4];  
					$uacs_code = $medata[5]; 
					$amount = $medata[6]; 

					$query = $this->db->query("
						INSERT INTO tbl_ors_indirect_ps_dt (
							`project_id`,
							`program_title`,
							`project_title`,
							`responsibility_code`,
							`mfopaps_code`,
							`sub_object_code`,
							`uacs_code`,
							`amount`,
							`added_by`
						)
						VALUES (?, ?, ?, ?, ?, ?, ?, ?,?)", 
						[
							$project_id,
							$program_title,
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
				$query = $this->db->query("DELETE FROM tbl_ors_indirect_ps_dt WHERE `project_id` = ?", [$project_id]);
			}

			if (!empty($budgetmooedtdata)) {
				$query = $this->db->query("DELETE FROM tbl_ors_direct_mooe_dt WHERE `project_id` = ?", [$project_id]);
				for($aa = 0; $aa < count($budgetmooedtdata); $aa++){
					$medata = explode("x|x",$budgetmooedtdata[$aa]);
					$program_title = $medata[0]; 
					$project_title = $medata[1]; 
					$responsibility_code = $medata[2]; 
					$mfopaps_code = $medata[3];
					$sub_object_code = $medata[4];  
					$uacs_code = $medata[5]; 
					$amount = $medata[6]; 

					$query = $this->db->query("
						INSERT INTO tbl_ors_direct_mooe_dt (
							`project_id`,
							`program_title`,
							`project_title`,
							`responsibility_code`,
							`mfopaps_code`,
							`sub_object_code`,
							`uacs_code`,
							`amount`,
							`added_by`
						)
						VALUES (?, ?, ?, ?, ?, ?, ?, ?,?)", 
						[
							$project_id,
							$program_title,
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
				$query = $this->db->query("DELETE FROM tbl_ors_direct_mooe_dt WHERE `project_id` = ?", [$project_id]);
			}

			if (!empty($budgetmooeindirectdtdata)) {
				$query = $this->db->query("DELETE FROM tbl_ors_indirect_mooe_dt WHERE `project_id` = ?", [$project_id]);
				for($aa = 0; $aa < count($budgetmooeindirectdtdata); $aa++){
					$medata = explode("x|x",$budgetmooeindirectdtdata[$aa]);
					$program_title = $medata[0]; 
					$project_title = $medata[1]; 
					$responsibility_code = $medata[2]; 
					$mfopaps_code = $medata[3];
					$sub_object_code = $medata[4];  
					$uacs_code = $medata[5]; 
					$amount = $medata[6]; 

					$query = $this->db->query("
						INSERT INTO tbl_ors_indirect_mooe_dt (
							`project_id`,
							`program_title`,
							`project_title`,
							`responsibility_code`,
							`mfopaps_code`,
							`sub_object_code`,
							`uacs_code`,
							`amount`,
							`added_by`
						)
						VALUES (?, ?, ?, ?, ?, ?, ?, ?,?)", 
						[
							$project_id,
							$program_title,
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
				$query = $this->db->query("DELETE FROM tbl_ors_indirect_mooe_dt WHERE `project_id` = ?", [$project_id]);
			}

			if (!empty($budgetcodtdata)) {
				$query = $this->db->query("DELETE FROM tbl_ors_direct_co_dt WHERE `project_id` = ?", [$project_id]);
				for($aa = 0; $aa < count($budgetcodtdata); $aa++){
					$medata = explode("x|x",$budgetcodtdata[$aa]);
					$program_title = $medata[0]; 
					$project_title = $medata[1]; 
					$responsibility_code = $medata[2]; 
					$mfopaps_code = $medata[3];
					$sub_object_code = $medata[4];  
					$uacs_code = $medata[5]; 
					$amount = $medata[6]; 

					$query = $this->db->query("
						INSERT INTO tbl_ors_direct_co_dt (
							`project_id`,
							`program_title`,
							`project_title`,
							`responsibility_code`,
							`mfopaps_code`,
							`sub_object_code`,
							`uacs_code`,
							`amount`,
							`added_by`
						)
						VALUES (?, ?, ?, ?, ?, ?, ?, ?,?)", 
						[
							$project_id,
							$program_title,
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
				$query = $this->db->query("DELETE FROM tbl_ors_direct_co_dt WHERE `project_id` = ?", [$project_id]);
			}

			if (!empty($budgetindirectcodtdata)) {
				$query = $this->db->query("DELETE FROM tbl_ors_indirect_co_dt WHERE `project_id` = ?", [$project_id]);
				for($aa = 0; $aa < count($budgetindirectcodtdata); $aa++){
					$medata = explode("x|x",$budgetindirectcodtdata[$aa]);
					$program_title = $medata[0]; 
					$project_title = $medata[1]; 
					$responsibility_code = $medata[2]; 
					$mfopaps_code = $medata[3];
					$sub_object_code = $medata[4];  
					$uacs_code = $medata[5]; 
					$amount = $medata[6]; 

					$query = $this->db->query("
						INSERT INTO tbl_ors_indirect_co_dt (
							`project_id`,
							`program_title`,
							`project_title`,
							`responsibility_code`,
							`mfopaps_code`,
							`sub_object_code`,
							`uacs_code`,
							`amount`,
							`added_by`
						)
						VALUES (?, ?, ?, ?, ?, ?, ?, ?,?)", 
						[
							$project_id,
							$program_title,
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
				$query = $this->db->query("DELETE FROM tbl_ors_indirect_co_dt WHERE `project_id` = ?", [$project_id]);
			}

			$status = !empty($is_disapproved) ? "ORS updated and sent back for certification" : "ORS updated successfully";
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
						window.location.href = 'myors?meaction=MAIN'; // Redirect to MAIN view
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
	
	//ORS - ONE RECORD WITH ITS LINE ITEMS (editor / review modals)
	public function ors_load($recid) {
		$hd = $this->db->query("SELECT * FROM tbl_ors_hd WHERE `recid` = ?", [$recid])->getRowArray();
		if (empty($hd)) {
			return null;
		}

		$grids = [
			'ps_direct'     => 'tbl_ors_direct_ps_dt',
			'ps_indirect'   => 'tbl_ors_indirect_ps_dt',
			'mooe_direct'   => 'tbl_ors_direct_mooe_dt',
			'mooe_indirect' => 'tbl_ors_indirect_mooe_dt',
			'co_direct'     => 'tbl_ors_direct_co_dt',
			'co_indirect'   => 'tbl_ors_indirect_co_dt',
		];
		$hd['grids'] = [];
		foreach ($grids as $key => $table) {
			$hd['grids'][$key] = $this->db->query("
				SELECT `recid`, `program_title`, `project_title`, `responsibility_code`, `mfopaps_code`, `sub_object_code`, `uacs_code`, `amount`
				FROM {$table}
				WHERE `project_id` = ?
				ORDER BY `recid` ASC
			", [$recid])->getResultArray();
		}
		return $hd;
	}

	//CERTIFIED A / B - APPROVAL / DISAPPROVAL
	public function ors_certifya_approve() { $this->ors_certify('a', true); }
	public function ors_certifya_disapprove() { $this->ors_certify('a', false); }
	public function ors_certifyb_approve() { $this->ors_certify('b', true); }
	public function ors_certifyb_disapprove() { $this->ors_certify('b', false); }

	// Box A (requesting office) decides first, then Box B (budget). Each
	// step only applies while the ORS is waiting at that step.
	private function ors_certify($box, $approve) {
		$recid = $this->request->getPostGet('recid');
		$remarks = trim((string) $this->request->getPostGet('remarks'));
		$approver = $this->cuser;

		fods_require_access(\Config\AccessCodes::ORSAPPROVAL_APPROVE, $approve ? 'Approve' : 'Disapprove');

		$hd = $this->db->query("SELECT `is_approved_certa`, `is_disapproved_certa`, `is_approved_certb`, `is_disapproved_certb` FROM tbl_ors_hd WHERE `recid` = ?", [$recid])->getRowArray();
		if ($box === 'a') {
			$waiting = !empty($hd) && $hd['is_approved_certa'] == '0' && $hd['is_disapproved_certa'] == '0';
		} else {
			$waiting = !empty($hd) && $hd['is_approved_certa'] == '1' && $hd['is_approved_certb'] == '0' && $hd['is_disapproved_certb'] == '0';
		}
		if (!$waiting) {
			echo "
			<script>
			toastr.error('This ORS is not waiting for Certification " . strtoupper($box) . "', 'Oops!', {
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

		if ($box === 'a') {
			$query = $this->db->query("
				UPDATE tbl_ors_hd
				SET
					`is_pending` = '0',
					`is_approved_certa` = ?,
					`is_disapproved_certa` = ?,
					`certa_approver` = ?,
					`certa_remarks` = ?
				WHERE `recid` = ?
			", [$approve ? 1 : 0, $approve ? 0 : 1, $approver, mb_substr($remarks, 0, 100), $recid]);
		} else {
			$query = $this->db->query("
				UPDATE tbl_ors_hd
				SET
					`is_pending` = '0',
					`is_approved_certb` = ?,
					`is_disapproved_certb` = ?,
					`certb_approver` = ?,
					`certb_remarks` = ?
				WHERE `recid` = ?
			", [$approve ? 1 : 0, $approve ? 0 : 1, $approver, mb_substr($remarks, 0, 100), $recid]);
		}
		$status = $approve ? "ORS certified (Box " . strtoupper($box) . ")" : "ORS disapproved";
		$color = $approve ? "success" : "warning";

		if ($query) {
			// Echo JavaScript to show the toast and then redirect
			echo "
			<script>
				toastr.$color('{$status}', 'Well Done!', {
						progressBar: true,
						closeButton: true,
						timeOut:2500,
					});
				setTimeout(function() {
						window.location.href = 'myorsapproval'; // Redirect to MAIN view
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

	public function get_ctr_ors($fund_cluster,$funding_source,$mfld='') { 
		$accessquery = $this->db->query("
		CREATE TABLE if not exists `myctr_ors` (
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
		
		$qctr = $this->db->query("select {$xfield} from myctr_ors WHERE CTR_YEAR = '$xsysyear' AND CTR_MONTH = '$xsysmonth' AND CTR_DAY = '$xsysday'  limit 1");
		if($qctr->getNumRows() == 0) {
			$xnumb = '001';
			$query = $this->db->query( "insert into myctr_ors (CTR_YEAR,CTR_MONTH,CTR_DAY,{$xfield}) values('$xsysyear','$xsysmonth','$xsysday','$xnumb')");
			$qctr->freeResult();
		} else {
			$qctr->freeResult();
			$qctr = $this->db->query( "select {$xfield} MYFIELD from myctr_ors WHERE CTR_YEAR = '$xsysyear' AND CTR_MONTH = '$xsysmonth' AND CTR_DAY = '$xsysday' limit 1");
			$rctr = $qctr->getRowArray();
			if(trim($rctr['MYFIELD'],' ') == '') { 
				$xnumb = '001';
			} else {
				$xnumb = $rctr['MYFIELD'];
				$qctr = $this->db->query("select ('{$xnumb}' + 1) XNUMB");
				$rctr = $qctr->getRowArray();
				$xnumb = trim($rctr['XNUMB'],' ');
				$xnumb = str_pad($xnumb + 0,3,"0",STR_PAD_LEFT);
				$query = $this->db->query("update myctr_ors set {$xfield} = '{$xnumb}'");
			}
		}
		return  $fund_cluster . '-' . $funding_source . '-' . $xsysyear . '-' . $xsysmonth . '-' . $xnumb;//.$supp
	} 

	
} //end main class
?>