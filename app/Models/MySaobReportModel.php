<?php
namespace App\Models;
use CodeIgniter\Model;

class MySaobReportModel extends Model
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

	// Months in order: the hd flags are is_<key>, the line columns <value>_revision
	const MONTHS = [
		'jan' => 'january', 'feb' => 'february', 'mar' => 'march', 'apr' => 'april',
		'may' => 'may', 'jun' => 'june', 'jul' => 'july', 'aug' => 'august',
		'sep' => 'september', 'oct' => 'october', 'nov' => 'november', 'dec' => 'december',
	];

	// Line tables (tbl_saob_<key>_dt) and the payload field each one is posted in
	const GRIDS = [
		'ps'   => 'budgetdtdata',
		'mooe' => 'budgetmooedtdata',
		'co'   => 'budgetcodtdata',
	];

	// Months are switched on in order. The revision month is the last one of an
	// unbroken Jan..N run (1-12); 0 when no month is on or the run is broken.
	public static function revision_month(array $hd) {
		$flags = [];
		foreach (array_keys(self::MONTHS) as $month) {
			$flags[] = ($hd['is_' . $month] ?? 0) == 1;
		}
		$n = 0;
		while ($n < 12 && $flags[$n]) {
			$n++;
		}
		return in_array(true, array_slice($flags, $n), true) ? 0 : $n;
	}

	//SAOB - ONE RECORD WITH ITS LINE ITEMS (editor modal)
	public function saob_load($recid) {
		$hd = $this->db->query("SELECT * FROM tbl_saob_hd WHERE `recid` = ?", [$recid])->getRowArray();
		if (empty($hd)) {
			return null;
		}

		$revisions = implode(', ', array_map(fn ($month) => "`{$month}_revision`", self::MONTHS));
		$hd['grids'] = [];
		foreach (array_keys(self::GRIDS) as $key) {
			$hd['grids'][$key] = $this->db->query("
				SELECT `recid`, `object_code`, `particulars`, `code`, `approved_budget`, {$revisions}
				FROM tbl_saob_{$key}_dt
				WHERE `project_id` = ?
				ORDER BY `recid` ASC
			", [$recid])->getResultArray();
		}
		return $hd;
	}

	//SAOB - SAVE
	public function saob_save() {
		$recid = $this->request->getPostGet('recid');
		$program_title = $this->request->getPostGet('program_title');
		$project_title = $this->request->getPostGet('project_title');
		$department = $this->request->getPostGet('department');
		$agency = $this->request->getPostGet('agency');
		$current_year = $this->request->getPostGet('current_year');

		$total_proposed_combined = $this->request->getPostGet('total_proposed_combined');
		$total_approved_combined = $this->request->getPostGet('total_approved_combined');

		//REVISION MONTHS
		$flags = [];
		foreach (array_keys(self::MONTHS) as $month) {
			$flags['is_' . $month] = $this->request->getPostGet('is_' . $month) == 1 ? 1 : 0;
		}

		$lines = [];
		foreach (self::GRIDS as $key => $field) {
			$lines[$key] = $this->request->getPostGet($field);
		}

		if (empty($program_title)) {
			echo "
			<script>
			toastr.error('Program title is required!', 'Oops!', {
					progressBar: true,
					closeButton: true,
					timeOut:2000,
				});
			</script>
			";
			die();
		}
		if (empty($department)) {
			echo "
			<script>
			toastr.error('Department is required!', 'Oops!', {
					progressBar: true,
					closeButton: true,
					timeOut:2000,
				});
			</script>
			";
			die();
		}
		if (empty($agency)) {
			echo "
			<script>
			toastr.error('Agency is required!', 'Oops!', {
					progressBar: true,
					closeButton: true,
					timeOut:2000,
				});
			</script>
			";
			die();
		}
		if (empty($current_year)) {
			echo "
			<script>
			toastr.error('Year is required!', 'Oops!', {
					progressBar: true,
					closeButton: true,
					timeOut:2000,
				});
			</script>
			";
			die();
		}

		if (empty($lines['ps']) && empty($lines['mooe']) && empty($lines['co'])) {
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
		if ($total_approved_combined !== $total_proposed_combined ) {
			echo "
			<script>
			toastr.error('Approved budget must be equal to Proposed Realignment!', 'Oops!', {
					progressBar: true,
					closeButton: true,
					timeOut:2000,
				});
			</script>
			";
			die();
		}

		if (empty($recid)) {
			fods_require_access(\Config\AccessCodes::SAOB_ADD, 'Saving');
			$old = [];
			$trx = $this->get_ctr_saob('LIB','fods','CTRL_NO01');//TRANSACTION NO

			$this->db->transStart();

			//INSERTING HD DATA
			$this->db->query("
				INSERT INTO tbl_saob_hd (
					trxno, program_title, project_title, department, agency, current_year,
					is_jan, is_feb, is_mar, is_apr, is_may, is_jun, is_jul,
					is_aug, is_sep, is_oct, is_nov, is_dec, added_at, added_by
				)
				VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?)",
				array_merge(
					[$trx, $program_title, $project_title, $department, $agency, $current_year],
					array_values($flags),
					[$this->cuser]
				)
			);

			$project_id = $this->db->insertID();

			$status = "SAOB saved successfully";
			$color = "success";
		}else{
			fods_require_access(\Config\AccessCodes::SAOB_UPDATE, 'Updating');
			$old = $this->db->query("SELECT * FROM tbl_saob_hd WHERE `recid` = ?", [$recid])->getRowArray();
			if (empty($old)) {
				echo "
				<script>
				toastr.error('SAOB not found', 'Oops!', {
						progressBar: true,
						closeButton: true,
						timeOut:2000,
					});
				</script>
				";
				die();
			}

			$this->db->transStart();

			$this->db->query("
				UPDATE tbl_saob_hd
				SET
					program_title = ?,
					project_title = ?,
					department = ?,
					agency = ?,
					current_year = ?,
					is_jan = ?,
					is_feb = ?,
					is_mar = ?,
					is_apr = ?,
					is_may = ?,
					is_jun = ?,
					is_jul = ?,
					is_aug = ?,
					is_sep = ?,
					is_oct = ?,
					is_nov = ?,
					is_dec = ?
				WHERE recid = ?
			", array_merge(
				[$program_title, $project_title, $department, $agency, $current_year],
				array_values($flags),
				[$recid]
			));

			$project_id = $recid;

			$status = "SAOB updated successfully";
			$color = "info";
		}

		// The proposed realignment is recorded under the revision month, but only
		// when that month is switched on by this save. Saving again without a new
		// month keeps the realignments already recorded.
		$revision_month = self::revision_month($flags);
		$month_keys = array_keys(self::MONTHS);
		$is_new_month = $revision_month > 0 && (empty($old) || $old['is_' . $month_keys[$revision_month - 1]] != 1);

		// blank or non-numeric amounts are saved as 0
		$num = fn ($v) => is_numeric($v) ? $v : 0;
		$revision_columns = implode(', ', array_map(fn ($month) => "`{$month}_revision`", self::MONTHS));

		//INSERTING PS / MOOE / CO DT DATA (each line: object code, particulars, UACS, approved,
		//to-date, proposed, revised, then the 12 monthly revisions)
		foreach (array_keys(self::GRIDS) as $key) {
			$this->db->query("DELETE FROM tbl_saob_{$key}_dt WHERE `project_id` = ?", [$project_id]);
			if (empty($lines[$key])) {
				continue;
			}
			for($aa = 0; $aa < count($lines[$key]); $aa++){
				$medata = explode("x|x",$lines[$key][$aa]);
				$object_code = $medata[0];
				$particulars = $medata[1] ?? '';
				$code = $medata[2] ?? '';
				$approved_budget = $num($medata[3] ?? 0);
				$proposed_realignment = $num($medata[5] ?? 0);
				$revised_allotment = $num($medata[6] ?? 0);

				$revisions = [];
				for($bb = 0; $bb < 12; $bb++){
					$revisions[] = $num($medata[7 + $bb] ?? 0);
				}
				if ($is_new_month) {
					$revisions[$revision_month - 1] = $proposed_realignment;
				}

				$this->db->query("
					INSERT INTO tbl_saob_{$key}_dt (
						`project_id`,
						`project_title`,
						`object_code`,
						`particulars`,
						`code`,
						`approved_budget`,
						`revised_allotment`,
						{$revision_columns},
						`added_by`
					)
					VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
					array_merge(
						[$project_id, $project_title, $object_code, $particulars, $code, $approved_budget, $revised_allotment],
						$revisions,
						[$this->cuser]
					)
				);
			}
		}

		$this->db->transComplete();

		//HISTORY
		if ($this->db->transStatus()) {
			$ref = trim($project_title . ' ' . $current_year);
			if (empty($recid)) {
				MyHistoryModel::log('saob', $project_id, 'CREATED', '', null, $ref);
			} else {
				MyHistoryModel::log('saob', $recid, 'UPDATED', '', MyHistoryModel::diff($old, MyHistoryModel::row('tbl_saob_hd', $recid)), $ref);
			}
		}

		if ($this->db->transStatus()) {
			// Echo JavaScript to show the toast and then redirect
			echo "
			<script>
				document.getElementById('submitBtn').disabled = true;
				toastr.$color('{$status}!', 'Well Done!', {
						progressBar: true,
						closeButton: true,
						timeOut:2000,
					});
				setTimeout(function() {
						window.location.href = 'mysaobrpt?meaction=MAIN'; // Redirect to MAIN view
					}, 1000); // reload after 1 s; the toast still runs its 2.5 s (fods-flash.js carries it over)
			</script>
			";
			exit; // Stop further PHP execution after the toast
		} else {
			// Nothing was written: the header and its lines are saved together
			echo "
			<script>
			toastr.error('The SAOB could not be saved. Nothing was changed.', 'Oops!', {
					progressBar: true,
					closeButton: true,
					timeOut:3000,
				});
			</script>
			";
			exit;
		}

	}
	
	public function budget_approve() { 
		$recid = $this->request->getPostGet('recid');
		$approver = $this->request->getPostGet('approver');
		$remarks = $this->request->getPostGet('remarks');

		fods_require_access(\Config\AccessCodes::BUDGETAPPROVAL_APPROVE, 'Approve');

		$query = $this->db->query("
			UPDATE tbl_budget_hd SET `is_pending` = '0', `is_approved` = '1',`approver` = '$approver', `remarks` = '$remarks' WHERE `recid` = '$recid'
		");
		$status = "Project budget approved!";
		
		if ($query) {
			// Echo JavaScript to show the toast and then redirect
			echo "
			<script>
				toastr.success('{$status}!', 'Well Done!', {
						progressBar: true,
						closeButton: true,
						timeOut:2000,
					});
				setTimeout(function() {
						window.location.href = 'mybudgetallotment?meaction=MAIN'; // Redirect to MAIN view
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

	public function budget_disapprove() { 
		$recid = $this->request->getPostGet('recid');
		$approver = $this->request->getPostGet('approver');
		$remarks = $this->request->getPostGet('remarks');

		fods_require_access(\Config\AccessCodes::BUDGETAPPROVAL_APPROVE, 'Disapprove');

		$query = $this->db->query("
			UPDATE tbl_budget_hd SET `is_pending` = '0', `is_approved` = '0',`is_disapproved` = '1',`approver` = '$approver', `remarks` = '$remarks' WHERE `recid` = '$recid'
		");
		$status = "Project budget disapproved!";
		
		if ($query) {
			// Echo JavaScript to show the toast and then redirect
			echo "
			<script>
				toastr.success('{$status}!', 'Well Done!', {
						progressBar: true,
						closeButton: true,
						timeOut:2000,
					});
				setTimeout(function() {
						window.location.href = 'mybudgetallotment?meaction=MAIN'; // Redirect to MAIN view
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

	public function budget_attachment_upload() { 
		$file = $this->request->getFile('userfile');
        $trxno = $this->request->getPostGet('hd_trxno');

        $query = $this->db->query("SELECT `recid` FROM tbl_budget_hd WHERE `trxno` = '$trxno' ");
        if ($query->getNumRows() > 0) {
            $rw = $query->getRowArray();
            $recid = $rw['recid'];
        } 

        if (!$file || !$file->isValid()) {
            return redirect()->to('mybudgetallotment?meaction=MAIN')
                            ->with('error', 'No file selected or invalid file.');
        }

        $uploadPath = FCPATH . 'uploads/';

        if (!is_dir($uploadPath)) {
            mkdir($uploadPath, 0777, true);
        }

        $originalName = pathinfo($file->getName(), PATHINFO_FILENAME);

        $newFileName = $originalName . '_' . $file->getRandomName();
        $file->move($uploadPath, $newFileName);

        $query = $this->db->query("
        INSERT INTO `tbl_budget_attachments`(
            `trxno`,
            `file_name`,
            `added_by`
        )
        VALUES(
            '$trxno',
            '$newFileName',
            '{$this->cuser}'
        )
        ");

        $status = 'File uploaded successfully';
        $redirectUrl = 'mybudgetallotment?meaction=MAIN&recid=' . $recid;
        
		if ($query) {
			// Echo JavaScript to show the toast and then redirect
			echo "
			<script>
				toastr.success('{$status}!', 'Well Done!', {
						progressBar: true,
						closeButton: true,
						timeOut:2000,
					});
				setTimeout(function() {
						window.location.href = 'mybudgetallotment?meaction=MAIN&recid=$recid'; 
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

	public function get_ctr_budget($class,$supp,$dbname,$mfld='') { 
		$accessquery = $this->db->query("
		CREATE TABLE if not exists `myctr_budget` (
		  `CTR_YEAR` varchar(4) DEFAULT '0000',
		  `CTR_MONTH` varchar(2) DEFAULT '00',
		  `CTR_DAY` varchar(2) DEFAULT '00',
		  `CTRL_NO01` varchar(15) DEFAULT '00000000',
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
		
		$qctr = $this->db->query("select {$xfield} from myctr_budget WHERE CTR_YEAR = '$xsysyear' AND CTR_MONTH = '$xsysmonth' AND CTR_DAY = '$xsysday'  limit 1");
		if($qctr->getNumRows() == 0) {
			$xnumb = '00001';
			$query = $this->db->query( "insert into myctr_budget (CTR_YEAR,CTR_MONTH,CTR_DAY,{$xfield}) values('$xsysyear','$xsysmonth','$xsysday','$xnumb')");
			$qctr->freeResult();
		} else {
			$qctr->freeResult();
			$qctr = $this->db->query( "select {$xfield} MYFIELD from myctr_budget WHERE CTR_YEAR = '$xsysyear' AND CTR_MONTH = '$xsysmonth' AND CTR_DAY = '$xsysday' limit 1");
			$rctr = $qctr->getRowArray();
			if(trim($rctr['MYFIELD'],' ') == '') { 
				$xnumb = '00001';
			} else {
				$xnumb = $rctr['MYFIELD'];
				$qctr = $this->db->query("select ('{$xnumb}' + 1) XNUMB");
				$rctr = $qctr->getRowArray();
				$xnumb = trim($rctr['XNUMB'],' ');
				$xnumb = str_pad($xnumb + 0,5,"0",STR_PAD_LEFT);
				$query = $this->db->query("update myctr_budget set {$xfield} = '{$xnumb}'");
			}
		}
		return  $class . '-' . $xnumb . '-' . $xsysmonth . $xsysday;//.$supp
	} 

	public function get_ctr_saob($class,$supp,$dbname,$mfld='') { 
		$accessquery = $this->db->query("
		CREATE TABLE if not exists `myctr_saob` (
		  `CTR_YEAR` varchar(4) DEFAULT '0000',
		  `CTR_MONTH` varchar(2) DEFAULT '00',
		  `CTR_DAY` varchar(2) DEFAULT '00',
		  `CTRL_NO01` varchar(15) DEFAULT '00000000',
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
		
		$qctr = $this->db->query("select {$xfield} from myctr_saob WHERE CTR_YEAR = '$xsysyear' AND CTR_MONTH = '$xsysmonth' AND CTR_DAY = '$xsysday'  limit 1");
		if($qctr->getNumRows() == 0) {
			$xnumb = '00001';
			$query = $this->db->query( "insert into myctr_saob (CTR_YEAR,CTR_MONTH,CTR_DAY,{$xfield}) values('$xsysyear','$xsysmonth','$xsysday','$xnumb')");
			$qctr->freeResult();
		} else {
			$qctr->freeResult();
			$qctr = $this->db->query( "select {$xfield} MYFIELD from myctr_saob WHERE CTR_YEAR = '$xsysyear' AND CTR_MONTH = '$xsysmonth' AND CTR_DAY = '$xsysday' limit 1");
			$rctr = $qctr->getRowArray();
			if(trim($rctr['MYFIELD'],' ') == '') { 
				$xnumb = '00001';
			} else {
				$xnumb = $rctr['MYFIELD'];
				$qctr = $this->db->query("select ('{$xnumb}' + 1) XNUMB");
				$rctr = $qctr->getRowArray();
				$xnumb = trim($rctr['XNUMB'],' ');
				$xnumb = str_pad($xnumb + 0,5,"0",STR_PAD_LEFT);
				$query = $this->db->query("update myctr_saob set {$xfield} = '{$xnumb}'");
			}
		}
		return  $class . '-' . $xnumb . '-' . $xsysmonth . $xsysday;//.$supp
	} 

	public function savings_save() { 
		$project_id = $this->request->getPostGet('project_id');
		$program_title = $this->request->getPostGet('sprogram_title');
		$savings_date = $this->request->getPostGet('savings_date');

		$savingsdtdata = $this->request->getPostGet('savingsdtdata');

		if (empty($savings_date)) {
			return $this->response->setJSON([
				'status'  => 'success',
				'message' => 'Savings Recorded Successfully!'
			]);
			die();
		}


		if (empty($savingsdtdata)) {
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

		if (!empty($project_id) && empty($savings_date)) {
			fods_require_access(\Config\AccessCodes::SAOB_ADD, 'Saving');

			//INSERTING PS DT DATA
			if (!empty($savingsdtdata)) {
				for($aa = 0; $aa < count($savingsdtdata); $aa++){
					$medata = explode("x|x",$savingsdtdata[$aa]);
					$project_title = $medata[0]; 
					$responsibility_code = $medata[1]; 
					$project_leader = $medata[2]; 
					$declared_savings = $medata[3]; 
					$other_expenses = $medata[4]; 
					$cna = $medata[5];  
					$total_obligations = $medata[6]; 
					$balance_savings = $medata[7]; 

					$query = $this->db->query("
						INSERT INTO `tbl_saob_savings`(
							`project_id`,
							`program_title`,
							`project_title`,
							`responsibility_code`,
							`project_leader`,
							`declared_savings`,
							`other_expenses`,
							`cna`,
							`total_obligations`,
							`balance_savings`,
							`savings_date`,
							`added_by`
						)
						VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)", 
						[
							$project_id,
							$program_title,
							$project_title,
							$responsibility_code,
							$project_leader,
							$declared_savings,
							$other_expenses,
							$cna,
							$total_obligations,
							$balance_savings,
							$savings_date,
							$this->cuser
						]
					);
					
				}
			}

			$status = "Savings Recorded Successfully!";
			$color = "success";
		}else{
			fods_require_access(\Config\AccessCodes::SAOB_UPDATE, 'Updating');
			if (!empty($savingsdtdata)) {
				$query = $this->db->query("DELETE FROM tbl_saob_savings WHERE `project_id` = '$project_id' AND `savings_date` = '$savings_date'");
				for($aa = 0; $aa < count($savingsdtdata); $aa++){
					$medata = explode("x|x",$savingsdtdata[$aa]);
					$project_title = $medata[0]; 
					$responsibility_code = $medata[1]; 
					$project_leader = $medata[2]; 
					$declared_savings = $medata[3]; 
					$other_expenses = $medata[4]; 
					$cna = $medata[5];  
					$total_obligations = $medata[6]; 
					$balance_savings = $medata[7]; 

					$query = $this->db->query("
						INSERT INTO `tbl_saob_savings`(
							`project_id`,
							`program_title`,
							`project_title`,
							`responsibility_code`,
							`project_leader`,
							`declared_savings`,
							`other_expenses`,
							`cna`,
							`total_obligations`,
							`balance_savings`,
							`savings_date`,
							`added_by`
						)
						VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)", 
						[
							$project_id,
							$program_title,
							$project_title,
							$responsibility_code,
							$project_leader,
							$declared_savings,
							$other_expenses,
							$cna,
							$total_obligations,
							$balance_savings,
							$savings_date,
							$this->cuser
						]
					);
					
				}
			}else{
				$query = $this->db->query("DELETE FROM tbl_saob_savings WHERE `project_id` = '$project_id' AND `savings_date` = '$savings_date'");
			}
			$status = "Savings Updated Successfully!";
			$color = "info";
		}


		if ($query) {
			// Echo JavaScript to show the toast and then redirect
			echo "
			<script>
				document.getElementById('submitBtnSavings').disabled = true;
				toastr.$color('{$status}!', 'Well Done!', {
						progressBar: true,
						closeButton: true,
						timeOut:2000,
					});
				setTimeout(function() {
						window.location.href = 'mysaobrpt?meaction=MAIN'; // Redirect to MAIN view
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
	
	//MONTHLY STATUS - one row per project of a program, as of the end of a month (YYYY-MM).
	// Shared by the Print status PDF (saob-savings-pdf.php) and the status dashboard, so both
	// show the same figures. Allotment = direct + indirect MOOE approved budget, admin cost =
	// savings lines, obligations = ORS direct + indirect MOOE lines matched on project title.
	public function monthly_status_rows($program, $month) {
		$start = date('Y-m-01', strtotime(strlen($month) === 7 ? $month . '-01' : $month));
		$end   = date('Y-m-t', strtotime($start));
		$year  = date('Y', strtotime($start));

		return $this->db->query("
SELECT
    budget.program_title,
    budget.project_title,
    budget.division_name AS unit_division,
    budget.responsibility_code,
    budget.project_leader,

    /* TOTAL ALLOTMENT */
      (
            COALESCE((SELECT SUM(dmooe.approved_budget) FROM tbl_budget_direct_mooe_dt dmooe
                  JOIN tbl_budget_hd hd ON dmooe.project_id = hd.recid
                  WHERE hd.project_title = budget.project_title),0)
            + COALESCE((SELECT SUM(idmooe.approved_budget) FROM tbl_budget_indirect_mooe_dt idmooe
                  JOIN tbl_budget_hd hd ON idmooe.project_id = hd.recid
                  WHERE hd.project_title = budget.project_title),0)
      ) AS allotment,

    /* ADMIN COST */
    COALESCE((SELECT SUM(approved_budget)
              FROM tbl_budget_savings_dt
              WHERE project_id = budget.recid),0) AS admin_cost,

    /* THIS MONTH */

   (
      + COALESCE((SELECT SUM(dmooe.amount) FROM tbl_ors_direct_mooe_dt dmooe
         JOIN tbl_ors_hd hd ON dmooe.project_id = hd.recid
         WHERE dmooe.project_title = budget.project_title AND hd.ors_date BETWEEN :start: AND :end:),0)
      + COALESCE((SELECT SUM(idmooe.amount) FROM tbl_ors_indirect_mooe_dt idmooe
         JOIN tbl_ors_hd hd ON idmooe.project_id = hd.recid
         WHERE idmooe.project_title = budget.project_title AND hd.ors_date BETWEEN :start: AND :end:),0)
   ) AS this_month,

    /* TO DATE (Jan to Selected Month) */
    (
      + COALESCE((SELECT SUM(dmooe.amount) FROM tbl_ors_direct_mooe_dt dmooe
            JOIN tbl_ors_hd hd ON dmooe.project_id = hd.recid
            WHERE dmooe.project_title = budget.project_title AND hd.ors_date BETWEEN :ystart: AND :end:),0)
      + COALESCE((SELECT SUM(idmooe.amount) FROM tbl_ors_indirect_mooe_dt idmooe
            JOIN tbl_ors_hd hd ON idmooe.project_id = hd.recid
            WHERE idmooe.project_title = budget.project_title AND hd.ors_date BETWEEN :ystart: AND :end:),0)
    ) AS todate


FROM tbl_budget_hd budget
WHERE TRIM(budget.program_title) = TRIM(:program:)
AND budget.duration_from <= :yend:
AND budget.duration_to   >= :ystart:

ORDER BY 
    CASE 
        /* --- PROGRAM-SPECIFIC TOP PRIORITIES --- */
        
        /* 1. Technical Services: 4-2026 goes FIRST */
        WHEN budget.responsibility_code LIKE '%-4-2026' 
             AND budget.responsibility_code NOT LIKE '%-4-%-2026'  /* Exclude patterns with extra numbers */
             AND budget.program_title = 'Technical Services on Food and Nutrition'
             THEN 0

        /* 2. Technical Services: 4-4-2026 goes SECOND */
        WHEN budget.responsibility_code LIKE '%-4-4-2026' 
             AND budget.program_title = 'Technical Services on Food and Nutrition'
             THEN 1

        /* 3. Nutritional Assessment: 3-2026 priority */
        WHEN budget.responsibility_code LIKE '%-3-2026' 
             AND budget.program_title = 'Nutritional Assessment and Monitoring on Food and Nutrition'
             THEN 2

        /* 4. Nutrigenomics: 2-3-2026 priority */
        WHEN budget.responsibility_code LIKE '%-00000-2-3-2026' 
             AND budget.program_title LIKE '%Expanding the FNRI%'
             THEN 3

        /* 5. ENNS program: 3-3-2026 at the top */
        WHEN budget.responsibility_code LIKE '%-3-3-2026' 
             AND budget.program_title LIKE '%Expanded National Nutrition Survey%' 
             THEN 4
             
        /* 6. ENNS program: 3-4-2026 directly under 3-3-2026 */
        WHEN budget.responsibility_code LIKE '%-3-4-2026' 
             AND budget.program_title LIKE '%Expanded National Nutrition Survey%' 
             THEN 5

        /* --- GENERAL GROUPS (Adjusted Priorities) --- */

        /* Group 1: Admin/Support Units (FAD/MIS) */
        WHEN budget.responsibility_code LIKE '%-3-4(FAD)-%' THEN 6
        WHEN budget.responsibility_code LIKE '%-3-4(MIS)-%' THEN 7
        
        /* Group 2: The 2-3 series Manual sequencing */
        WHEN budget.responsibility_code LIKE '%-00000-2-3-1-2026' THEN 8
        WHEN budget.responsibility_code LIKE '%-00000-2-3-2-2026' THEN 9
        
        /* Propelling (2-3-3) for Nutrigenomics */
        WHEN budget.responsibility_code LIKE '%-00000-2-3-3-2026' 
             AND budget.program_title LIKE '%Expanding the FNRI%'
             THEN 10
        
        /* Group 3: Generic codes */
        WHEN budget.responsibility_code LIKE '%-3-3-2026' THEN 11
        WHEN budget.responsibility_code = :rc_general: THEN 12

        /* --- PROGRAM-SPECIFIC BOTTOM PRIORITIES --- */

        /* Move Unprogrammed to the absolute bottom for ENNS */
        WHEN budget.project_title = 'Unprogrammed' 
             AND budget.program_title LIKE '%Expanded National Nutrition Survey%' 
             THEN 14

        ELSE 13
    END ASC,

    /* Secondary sort for numerical codes */
    CAST(
        SUBSTRING_INDEX(
            SUBSTRING_INDEX(budget.responsibility_code, '-', -2),
        '-', 1
        ) AS UNSIGNED
    ) ASC,

    /* Tertiary sort: year */
    CAST(
        SUBSTRING_INDEX(budget.responsibility_code, '-', -1)
    AS UNSIGNED) ASC
 
		", [
			'program'    => (string) $program,
			'start'      => $start,
			'end'        => $end,
			'ystart'     => $year . '-01-01',
			'yend'       => $year . '-12-31',
			'rc_general' => '19-003-00-00000-2-' . $year,
		])->getResultArray();
	}

	//MONTHLY STATUS DASHBOARD - the rows with their computed columns, totals, and the
	// program's obligations per month from January up to the month picked
	public function monthly_status($program, $month) {
		$rows = [];
		$tot = ['allotment' => 0, 'admin_cost' => 0, 'revised' => 0, 'this_month' => 0, 'todate' => 0, 'balance' => 0];
		foreach ($this->monthly_status_rows($program, $month) as $r) {
			$row = [
				'responsibility_code' => (string) $r['responsibility_code'],
				'project_title'       => (string) $r['project_title'],
				'unit_division'       => (string) $r['unit_division'],
				'project_leader'      => (string) $r['project_leader'],
				'allotment'           => round((float) $r['allotment'], 2),
				'admin_cost'          => round((float) $r['admin_cost'], 2),
				'this_month'          => round((float) $r['this_month'], 2),
				'todate'              => round((float) $r['todate'], 2),
			];
			$row['revised'] = round($row['allotment'] + $row['admin_cost'], 2);
			$row['balance'] = round($row['revised'] - $row['todate'], 2);
			$row['percent'] = $row['revised'] > 0 ? round($row['todate'] / $row['revised'] * 100, 2) : 0;
			foreach ($tot as $k => $v) {
				$tot[$k] = $v + $row[$k];
			}
			$rows[] = $row;
		}
		$tot = array_map(fn ($v) => round($v, 2), $tot);
		$tot['percent'] = $tot['revised'] > 0 ? round($tot['todate'] / $tot['revised'] * 100, 2) : 0;

		// Obligations per month (same lines as the To date column, each title once)
		$start = date('Y-01-01', strtotime($month . '-01'));
		$end   = date('Y-m-t', strtotime($month . '-01'));
		$byMonth = [];
		$titles = array_values(array_unique(array_column($rows, 'project_title')));
		if ($titles) {
			foreach (['tbl_ors_direct_mooe_dt', 'tbl_ors_indirect_mooe_dt'] as $table) {
				$q = $this->db->query("
					SELECT MONTH(hd.ors_date) AS m, SUM(dt.amount) AS amount
					FROM {$table} dt
					JOIN tbl_ors_hd hd ON dt.project_id = hd.recid
					WHERE dt.project_title IN ? AND hd.ors_date BETWEEN ? AND ?
					GROUP BY MONTH(hd.ors_date)
				", [$titles, $start, $end])->getResultArray();
				foreach ($q as $x) {
					$byMonth[(int) $x['m']] = ($byMonth[(int) $x['m']] ?? 0) + (float) $x['amount'];
				}
			}
		}
		$monthly = [];
		for ($i = 1; $i <= (int) date('n', strtotime($month . '-01')); $i++) {
			$monthly[] = ['month' => $i, 'label' => date('M', mktime(0, 0, 0, $i, 1)), 'amount' => round($byMonth[$i] ?? 0, 2)];
		}

		return [
			'program' => (string) $program,
			'month'   => $month,
			'as_of'   => date('F j, Y', strtotime($end)),
			'rows'    => $rows,
			'totals'  => $tot,
			'monthly' => $monthly,
		];
	}

} //end main class
?>
