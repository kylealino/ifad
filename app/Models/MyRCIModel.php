<?php
namespace App\Models;
use CodeIgniter\Model;

class MyRCIModel extends Model
{

    protected $db;

    public function __construct(){
        parent::__construct();
        $this->session = session();
        $this->request = \Config\Services::request();
		$this->db = \Config\Database::connect();
		$this->cuser = $this->session->get('__xsys_myuserzicas__');
        
    }

	public function rci_save() {

		$recid = $this->request->getPostGet('recid');

		// HD
		$mds_branch = $this->request->getPostGet('mds_branch');
		$mds_accountno = $this->request->getPostGet('mds_accountno');
		$fund_cluster_code = $this->request->getPostGet('fund_cluster_code');
		$reportno = $this->request->getPostGet('reportno');

		// DT
		$rcidtdata = $this->request->getPostGet('rcidtdata');

		// VALIDATIONS
		if (empty($mds_branch)) {
			echo "
			<script>
			toastr.error('MDS Branch is required!', 'Oops!', {
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
			toastr.error('Fund Cluster Code is required!', 'Oops!', {
					progressBar: true,
					closeButton: true,
					timeOut:2000,
				});
			</script>
			";
			die();
		}

		if (empty($rcidtdata)) {
			echo "
			<script>
			toastr.error('No detail data found!', 'Oops!', {
					progressBar: true,
					closeButton: true,
					timeOut:2000,
				});
			</script>
			";
			die();
		}

		// =========================================================
		// SAVE
		// =========================================================
		if (empty($recid)) {
			fods_require_access(\Config\AccessCodes::RCI_ADD, 'Saving');

			// INSERT HD
			$query = $this->db->query("
				INSERT INTO `tbl_rci_hd`(
					`mds_branch`,
					`mds_accountno`,
					`fund_cluster_code`,
					`reportno`,
					`created_by`
				)
				VALUES (?, ?, ?, ?, ?)
			", [
				$mds_branch,
				$mds_accountno,
				$fund_cluster_code,
				$reportno,
				$this->cuser
			]);

			$project_id = $this->db->insertID();

			// INSERT DT
			if (!empty($rcidtdata)) {

				for($aa = 0; $aa < count($rcidtdata); $aa++){

					$medata = explode("x|x",$rcidtdata[$aa]);

					$lddapadano = $medata[0];
					$lddapada_date = $medata[1];
					$ckno = $medata[2];
					$dvno = $medata[3];
					$serialno = $medata[4];
					$responsibility_code = $medata[5];
					$payee_name = $medata[6];
					$particulars = $medata[7];
					$gross_amount = $medata[8];
					$philhealth_amount = $medata[9];
					$tax_amount = $medata[10];
					$net_amount = $medata[11];

					$query = $this->db->query("
						INSERT INTO `tbl_rci_dt`(
							`hd_rid`,
							`lddapadano`,
							`lddapada_date`,
							`ckno`,
							`dvno`,
							`serialno`,
							`responsibility_code`,
							`payee_name`,
							`particulars`,
							`gross_amount`,
							`philhealth_amount`,
							`tax_amount`,
							`net_amount`,
							`created_by`
						)
						VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
					", [
						$project_id,
						$lddapadano,
						$lddapada_date,
						$ckno,
						$dvno,
						$serialno,
						$responsibility_code,
						$payee_name,
						$particulars,
						$gross_amount,
						$philhealth_amount,
						$tax_amount,
						$net_amount,
						$this->cuser
					]);

				}
			}

			$status = "RCI saved successfully";
			$color = "success";

		}else{
			fods_require_access(\Config\AccessCodes::RCI_UPDATE, 'Updating');

			// UPDATE HD
			$query = $this->db->query("
				UPDATE tbl_rci_hd
				SET
					`mds_branch` = ?,
					`mds_accountno` = ?,
					`fund_cluster_code` = ?,
					`reportno` = ?
				WHERE recid = ?
			", [
				$mds_branch,
				$mds_accountno,
				$fund_cluster_code,
				$reportno,
				$recid
			]);

			$project_id = $recid;

			// RE-INSERT DT
			if (!empty($rcidtdata)) {
				$query = $this->db->query("DELETE FROM tbl_rci_dt WHERE `hd_rid` = ?", [$project_id]);
				for($aa = 0; $aa < count($rcidtdata); $aa++){

					$medata = explode("x|x",$rcidtdata[$aa]);

					$lddapadano = $medata[0];
					$lddapada_date = $medata[1];
					$ckno = $medata[2];
					$dvno = $medata[3];
					$serialno = $medata[4];
					$responsibility_code = $medata[5];
					$payee_name = $medata[6];
					$particulars = $medata[7];
					$gross_amount = $medata[8];
					$philhealth_amount = $medata[9];
					$tax_amount = $medata[10];
					$net_amount = $medata[11];

					$query = $this->db->query("
						INSERT INTO `tbl_rci_dt`(
							`hd_rid`,
							`lddapadano`,
							`lddapada_date`,
							`ckno`,
							`dvno`,
							`serialno`,
							`responsibility_code`,
							`payee_name`,
							`particulars`,
							`gross_amount`,
							`philhealth_amount`,
							`tax_amount`,
							`net_amount`,
							`created_by`
						)
						VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
					", [
						$project_id,
						$lddapadano,
						$lddapada_date,
						$ckno,
						$dvno,
						$serialno,
						$responsibility_code,
						$payee_name,
						$particulars,
						$gross_amount,
						$philhealth_amount,
						$tax_amount,
						$net_amount,
						$this->cuser
					]);

				}
			}else{
				$query = $this->db->query("DELETE FROM tbl_rci_dt WHERE `hd_rid` = ?", [$project_id]);
			}

			$status = "RCI updated successfully";
			$color = "info";
		}

		// =========================================================
		// RESULT
		// =========================================================
		if ($query) {

			echo "
			<script>
				document.getElementById('submitBtn').disabled = true;

				toastr.$color('{$status}!', 'Well Done!', {
						progressBar: true,
						closeButton: true,
						timeOut:2500,
					});

				setTimeout(function() {
						window.location.href = 'myrci?meaction=MAIN';
					}, 2500);

			</script>
			";

			exit;

		} else {

			echo "
			<script type='text/javascript'>
				alert('An error occurred while executing the query.');
			</script>
			";

			exit;
		}

	}
	
	//RCI - ONE REPORT WITH ITS LINES (editor modal)
	public function rci_load($recid) {
		$hd = $this->db->query("SELECT * FROM tbl_rci_hd WHERE `recid` = ?", [$recid])->getRowArray();
		if (empty($hd)) {
			return null;
		}
		$hd['lines'] = $this->db->query("
			SELECT `recid`, `lddapadano`, `lddapada_date`, `ckno`, `dvno`, `serialno`, `responsibility_code`,
				`payee_name`, `particulars`, `gross_amount`, `philhealth_amount`, `tax_amount`, `net_amount`
			FROM tbl_rci_dt
			WHERE `hd_rid` = ?
			ORDER BY `recid` ASC
		", [$recid])->getResultArray();
		return $hd;
	}

	//RCI - ONE LINE PER DV OF AN LDDAP-ADA: its check, DV and amounts, the
	//nature of payment from the DV's ORS / BURS, and the responsibility code
	//of that ORS / BURS's largest line
	public function lddapada_lines($lddapada_id) {
		$rows = $this->db->query("
			SELECT
				a.`lddapadano`,
				a.`lddapada_date`,
				a.`ckno`,
				b.`dvno`,
				b.`serialno`,
				b.`payee_name`,
				b.`gross_amount`,
				0 AS philhealth_amount,
				b.`total_deduction` AS tax_amount,
				b.`net_amount`
			FROM tbl_lddapada_hd a
			JOIN tbl_lddapada_dt b ON b.`lddapada_id` = a.`recid`
			WHERE a.`recid` = ?
			ORDER BY b.`recid` ASC
		", [$lddapada_id])->getResultArray();

		foreach ($rows as &$row) {
			$row['particulars'] = '';
			$row['responsibility_code'] = '';
			foreach (['ors', 'burs'] as $src) {
				$ob = $this->db->query("SELECT `recid`, `particulars` FROM tbl_{$src}_hd WHERE `serialno` = ? ORDER BY `recid` DESC LIMIT 1", [$row['serialno']])->getRowArray();
				if (empty($ob)) {
					continue;
				}
				$top = $this->db->query("
					SELECT responsibility_code FROM (
						SELECT responsibility_code, amount FROM tbl_{$src}_direct_ps_dt WHERE project_id = ?
						UNION ALL SELECT responsibility_code, amount FROM tbl_{$src}_direct_mooe_dt WHERE project_id = ?
						UNION ALL SELECT responsibility_code, amount FROM tbl_{$src}_direct_co_dt WHERE project_id = ?
						UNION ALL SELECT responsibility_code, amount FROM tbl_{$src}_indirect_ps_dt WHERE project_id = ?
						UNION ALL SELECT responsibility_code, amount FROM tbl_{$src}_indirect_mooe_dt WHERE project_id = ?
						UNION ALL SELECT responsibility_code, amount FROM tbl_{$src}_indirect_co_dt WHERE project_id = ?
					) x
					ORDER BY amount DESC
					LIMIT 1
				", array_fill(0, 6, $ob['recid']))->getRowArray();
				$row['particulars'] = $ob['particulars'];
				$row['responsibility_code'] = $top['responsibility_code'] ?? '';
				break;
			}
		}
		unset($row);
		return $rows;
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