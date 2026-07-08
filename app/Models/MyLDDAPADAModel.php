<?php
namespace App\Models;
use CodeIgniter\Model;

class MyLDDAPADAModel extends Model
{

    protected $db;

    public function __construct(){
        parent::__construct();
        $this->session = session();
        $this->request = \Config\Services::request();
		$this->db = \Config\Database::connect();
		$this->cuser = $this->session->get('__xsys_myuserzicas__');
        
    }

	public function lddapada_save() { 
		$recid = $this->request->getPostGet('recid');
		$serialno = $this->request->getPostGet('serialno');
		$dvno = $this->request->getPostGet('dvno');
		$lddapadano = $this->request->getPostGet('lddapadano');
		$mds_branch = $this->request->getPostGet('mds_branch');
		$mds_accountno = $this->request->getPostGet('mds_accountno');
		$lddapada_date = $this->request->getPostGet('lddapada_date');
		$fund_cluster_code = $this->request->getPostGet('fund_cluster_code');
		$ada_approver = $this->request->getPostGet('ada_approver');
		$is_ci = $this->request->getPostGet('is_ci');
		$dvdtdata = $this->request->getPostGet('dvdtdata');
		$dvno_list = $this->request->getPostGet('dvno_list');

		if (empty($lddapada_date)) {
			echo "
			<script>
			toastr.error('LDDAPADA date is required!', 'Oops!', {
					progressBar: true,
					closeButton: true,
					timeOut:2000,
				});
			</script>
			";
			die();
		}
		if (empty($fund_cluster_code)) {
			echo "
			<script>
			toastr.error('Fund cluster code is required!', 'Oops!', {
					progressBar: true,
					closeButton: true,
					timeOut:2000,
				});
			</script>
			";
			die();
		}
		if (empty($dvno_list)) {
			echo "
			<script>
			toastr.error('No DV selected!', 'Oops!', {
					progressBar: true,
					closeButton: true,
					timeOut:2000,
				});
			</script>
			";
			die();
		}
		
		if (!empty($dvdtdata)) {
			for($aa = 0; $aa < count($dvdtdata); $aa++){
				$medata = explode("x|x",$dvdtdata[$aa]);
				$dv_fundingsource = $medata[0]; 
			}
		}

		$query = $this->db->query("SELECT `funding_source` FROM tbl_disbursement_hd WHERE `dvno` = '$dv_fundingsource'");
		$rw = $query->getRowArray();
		$funding_source = $rw['funding_source'];

		if (empty($recid)) {
			$cseqn =  $this->get_ctr_lddapada($fund_cluster_code,$funding_source,'CTRL_NO01');//TRANSACTION NO

			if ($is_ci == '0') {
				$ckno =  $this->get_ctr_ada('CTRL_NO01');//TRANSACTION NO
			}

			if ($is_ci == '1') {
				$ckno =  $this->get_ctr_ci('9926','CTRL_NO01');//TRANSACTION NO
			}
			//INSERTING HD DATA
			//disbursement SERIALNO
			$query = $this->db->query("
				INSERT INTO `tbl_lddapada_hd`(
					`lddapadano`,
					`ckno`,
					`mds_branch`,
					`mds_accountno`,
					`lddapada_date`,
					`fund_cluster_code`,
					`funding_source`,
					`is_ci`,
					`ada_approver`,
					`added_by`
				)
				VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)", 
				[
					$cseqn,
					$ckno,
					$mds_branch,
					$mds_accountno,
					$lddapada_date,
					$fund_cluster_code,
					$funding_source,
					$is_ci,
					$ada_approver,
					$this->cuser
				]
			);

			$project_id = $this->db->insertID();

			if (!empty($dvno_list)) {
				foreach($dvno_list as $dv) {
					$this->db->query("
						INSERT INTO tbl_lddapada_dvno 
						(
							lddapada_id, 
							lddapadano, 
							dvno, 
							added_by
						)
						VALUES (?,?,?,?)",
						[
							$project_id,
							$lddapadano,
							$dv,
							$this->cuser
						]
					);
				}
			}
			if (!empty($dvdtdata)) {
				for($aa = 0; $aa < count($dvdtdata); $aa++){
					$medata = explode("x|x",$dvdtdata[$aa]);
					$dvno = $medata[0]; 
					$payee_name = $medata[1]; 
					$payee_account_num = $medata[2];
					$serialno = $medata[3]; 
					$uacs_code = $medata[4]; 
					$gross_amount = $medata[5]; 
					$total_deduction = $medata[6]; 
					$net_amount = $medata[7]; 
					$remarks = $medata[8]; 

					$query = $this->db->query("
						INSERT INTO `tbl_lddapada_dt`(
							`lddapada_id`,
							`dvno`,
							`payee_name`,
							`payee_account_num`,
							`serialno`,
							`uacs_code`,
							`gross_amount`,
							`total_deduction`,
							`net_amount`,
							`remarks`,
							`added_by`
						)
						VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)", 
						[
							$project_id,
							$dvno,
							$payee_name,
							$payee_account_num,
							$serialno,
							$uacs_code,
							$gross_amount,
							$total_deduction,
							$net_amount,
							$remarks,
							$this->cuser
						]
					);
					
				}
			}

			$status = "LDDAP-ADA Saved Successfully!";
			$color = "success";
		}else{
			$query = $this->db->query("
				UPDATE tbl_lddapada_hd
				SET
					`lddapadano` = ?,
					`mds_branch` = ?,
					`mds_accountno` = ?,
					`lddapada_date` = ?,
					`fund_cluster_code` = ?,
					`funding_source` = ?,
					`ada_approver` = ?
				WHERE recid = ?
			", [
				$lddapadano,
				$mds_branch,
				$mds_accountno,
				$lddapada_date,
				$fund_cluster_code,
				$funding_source,
				$ada_approver,
				$recid
			]);

			$project_id = $recid;

			if (!empty($dvno_list)) {
				$query = $this->db->query("DELETE FROM tbl_lddapada_dvno WHERE `lddapada_id` = '$project_id'");
				foreach($dvno_list as $dv) {
					$this->db->query("
						INSERT INTO tbl_lddapada_dvno 
						(
							lddapada_id, 
							lddapadano, 
							dvno, 
							added_by
						)
						VALUES (?,?,?,?)",
						[
							$project_id,
							$lddapadano,
							$dv,
							$this->cuser
						]
					);
				}
			}else{
				$query = $this->db->query("DELETE FROM tbl_pr_ppmp WHERE `lddapada_id` = '$project_id'");
			}

			if (!empty($dvdtdata)) {
				$query = $this->db->query("DELETE FROM tbl_lddapada_dt WHERE `lddapada_id` = '$project_id'");
				for($aa = 0; $aa < count($dvdtdata); $aa++){
					$medata = explode("x|x",$dvdtdata[$aa]);
					$dvno = $medata[0]; 
					$payee_name = $medata[1]; 
					$payee_account_num = $medata[2];
					$serialno = $medata[3]; 
					$uacs_code = $medata[4]; 
					$gross_amount = $medata[5]; 
					$total_deduction = $medata[6]; 
					$net_amount = $medata[7]; 
					$remarks = $medata[8]; 

					$query = $this->db->query("
						INSERT INTO `tbl_lddapada_dt`(
							`lddapada_id`,
							`dvno`,
							`payee_name`,
							`payee_account_num`,
							`serialno`,
							`uacs_code`,
							`gross_amount`,
							`total_deduction`,
							`net_amount`,
							`remarks`,
							`added_by`
						)
						VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)", 
						[
							$project_id,
							$dvno,
							$payee_name,
							$payee_account_num,
							$serialno,
							$uacs_code,
							$gross_amount,
							$total_deduction,
							$net_amount,
							$remarks,
							$this->cuser
						]
					);
					
				}
			}else{
				$query = $this->db->query("DELETE FROM tbl_lddapada_dt WHERE `lddapada_id` = '$project_id'");
			}

			$status = "LDDAP-ADA Updated Successfully!";
			$color = "info";
		}

		$lddapno_display = empty($recid) ? $cseqn : $lddapadano;

		if ($query) {
			// Echo JavaScript to show the toast and then redirect
			echo "
			<script>
				document.getElementById('submitBtn').disabled = true;
				document.getElementById('lddapadano').value = '" . $lddapno_display . "';
				toastr.$color('{$status}!', 'Well Done!', {
						progressBar: true,
						closeButton: true,
						timeOut:2500,
					});
				setTimeout(function() {
						window.location.href = 'mylddapada?meaction=MAIN'; // Redirect to MAIN view
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
	public function disbursement_certifya_approve() { 
		$recid = $this->request->getPostGet('recid');
		$approver = $this->request->getPostGet('approver');
		$remarks = $this->request->getPostGet('remarks');
		$serialno = $this->request->getPostGet('serialno');
		$funding_source = $this->request->getPostGet('funding_source');

		// $cseqn =  $this->get_ctr_lddapada('01',$funding_source,'CTRL_NO01');//TRANSACTION NO
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
			UPDATE tbl_disbursement_hd 
			SET 
				`is_pending` = '0', 
				`is_approved_certa` = '1',
				`is_disapproved_certa` = '0',
				`certa_approver` = '$approver', 
				`certa_remarks` = '$remarks'
			WHERE `recid` = '$recid'
		");
		$status = "disbursement approved!";
		
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
						window.location.href = 'mydisbursementapproval?meaction=MAIN'; // Redirect to MAIN view
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

	public function get_ctr_lddapada($fund_cluster,$funding_source,$mfld='') { 
		$accessquery = $this->db->query("
		CREATE TABLE if not exists `myctr_lddapada` (
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
		
		$qctr = $this->db->query("select {$xfield} from myctr_lddapada WHERE CTR_YEAR = '$xsysyear' AND CTR_MONTH = '$xsysmonth' AND CTR_DAY = '$xsysday'  limit 1");
		if($qctr->getNumRows() == 0) {
			$xnumb = '001';
			$query = $this->db->query( "insert into myctr_lddapada (CTR_YEAR,CTR_MONTH,CTR_DAY,{$xfield}) values('$xsysyear','$xsysmonth','$xsysday','$xnumb')");
			$qctr->freeResult();
		} else {
			$qctr->freeResult();
			$qctr = $this->db->query( "select {$xfield} MYFIELD from myctr_lddapada WHERE CTR_YEAR = '$xsysyear' AND CTR_MONTH = '$xsysmonth' AND CTR_DAY = '$xsysday' limit 1");
			$rctr = $qctr->getRowArray();
			if(trim($rctr['MYFIELD'],' ') == '') { 
				$xnumb = '001';
			} else {
				$xnumb = $rctr['MYFIELD'];
				$qctr = $this->db->query("select ('{$xnumb}' + 1) XNUMB");
				$rctr = $qctr->getRowArray();
				$xnumb = trim($rctr['XNUMB'],' ');
				$xnumb = str_pad($xnumb + 0,3,"0",STR_PAD_LEFT);
				$query = $this->db->query("update myctr_lddapada set {$xfield} = '{$xnumb}'");
			}
		}
		return  $fund_cluster . '-' . $funding_source .  '-' . $xsysmonth . '-' . $xnumb . '-' . $xsysyear;//.$supp
	} 

	public function get_ctr_ada($mfld='') { 
		$accessquery = $this->db->query("
		CREATE TABLE if not exists `myctr_ada` (
		  `CTR_YEAR` varchar(4) DEFAULT '0000',
		  `CTR_MONTH` varchar(2) DEFAULT '00',
		  `CTR_DAY` varchar(2) DEFAULT '00',
		  `CTRL_NO01` varchar(15) DEFAULT '0000',
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
		
		$qctr = $this->db->query("select {$xfield} from myctr_ada WHERE CTR_YEAR = '$xsysyear' AND CTR_MONTH = '$xsysmonth' AND CTR_DAY = '$xsysday'  limit 1");
		if($qctr->getNumRows() == 0) {
			$xnumb = '0001';
			$query = $this->db->query( "insert into myctr_ada (CTR_YEAR,CTR_MONTH,CTR_DAY,{$xfield}) values('$xsysyear','$xsysmonth','$xsysday','$xnumb')");
			$qctr->freeResult();
		} else {
			$qctr->freeResult();
			$qctr = $this->db->query( "select {$xfield} MYFIELD from myctr_ada WHERE CTR_YEAR = '$xsysyear' AND CTR_MONTH = '$xsysmonth' AND CTR_DAY = '$xsysday' limit 1");
			$rctr = $qctr->getRowArray();
			if(trim($rctr['MYFIELD'],' ') == '') { 
				$xnumb = '0001';
			} else {
				$xnumb = $rctr['MYFIELD'];
				$qctr = $this->db->query("select ('{$xnumb}' + 1) XNUMB");
				$rctr = $qctr->getRowArray();
				$xnumb = trim($rctr['XNUMB'],' ');
				$xnumb = str_pad($xnumb + 0,3,"0",STR_PAD_LEFT);
				$query = $this->db->query("update myctr_ada set {$xfield} = '{$xnumb}'");
			}
		}
		return  $xsysyear . $xsysmonth . $xnumb ;//.$supp
	} 

	public function get_ctr_ci($citag,$mfld='') { 
		$accessquery = $this->db->query("
		CREATE TABLE if not exists `myctr_ci` (
		  `CTR_YEAR` varchar(4) DEFAULT '0000',
		  `CTR_MONTH` varchar(2) DEFAULT '00',
		  `CTR_DAY` varchar(2) DEFAULT '00',
		  `CTRL_NO01` varchar(15) DEFAULT '0000',
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
		
		$qctr = $this->db->query("select {$xfield} from myctr_ci WHERE CTR_YEAR = '$xsysyear' AND CTR_MONTH = '$xsysmonth' AND CTR_DAY = '$xsysday'  limit 1");
		if($qctr->getNumRows() == 0) {
			$xnumb = '0001';
			$query = $this->db->query( "insert into myctr_ci (CTR_YEAR,CTR_MONTH,CTR_DAY,{$xfield}) values('$xsysyear','$xsysmonth','$xsysday','$xnumb')");
			$qctr->freeResult();
		} else {
			$qctr->freeResult();
			$qctr = $this->db->query( "select {$xfield} MYFIELD from myctr_ci WHERE CTR_YEAR = '$xsysyear' AND CTR_MONTH = '$xsysmonth' AND CTR_DAY = '$xsysday' limit 1");
			$rctr = $qctr->getRowArray();
			if(trim($rctr['MYFIELD'],' ') == '') { 
				$xnumb = '0001';
			} else {
				$xnumb = $rctr['MYFIELD'];
				$qctr = $this->db->query("select ('{$xnumb}' + 1) XNUMB");
				$rctr = $qctr->getRowArray();
				$xnumb = trim($rctr['XNUMB'],' ');
				$xnumb = str_pad($xnumb + 0,3,"0",STR_PAD_LEFT);
				$query = $this->db->query("update myctr_ci set {$xfield} = '{$xnumb}'");
			}
		}
		return  $citag . $xsysmonth . $xnumb;//.$supp
	} 
	
} //end main class
?>