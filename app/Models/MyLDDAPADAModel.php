<?php
namespace App\Models;
use CodeIgniter\Model;

class MyLDDAPADAModel extends Model
{

    protected $db;

    // Each signature on the form: request/column name => [label, old default name].
    // The names offered for each, its position and the default for a new
    // LDDAP-ADA are in Approver Mapping (module lddapada, box = the field).
    // ada_approver (Agency Authorized Signatory 1) is the LDDAP-ADA's ADA approver,
    // shown on LDDAP-ADA Approval.
    // LDDAP-ADAs saved before these were picked keep blank values and print the old defaults.
    public const SIGNATURES = [
        'certified_by'  => ['Certified Correct', 'BONJOBIE F. CAJANO, CPA, CTT'],
        'approved_by'   => ['Approved', 'ALEXIS M. ORTIZ'],
        'ada_approver'  => ['Agency Authorized Signatory 1', 'JOVY S. MEDINA'],
        'ada_approver2' => ['Agency Authorized Signatory 2', 'ALEXIS M. ORTIZ'],
    ];

    // Position column of each signature
    public static function position_col(string $field): string
    {
        return $field === 'certified_by' ? 'certified_position'
            : ($field === 'approved_by' ? 'approved_position' : $field . '_position');
    }

    // The four signatures to print for an LDDAP-ADA row: [field => [name, position]].
    // A blank name falls back to the old default; a blank position to the mapped one,
    // then to the user record's (older LDDAP-ADAs only stored the ADA approver's name).
    public function signatures_for(array $hd): array
    {
        $out = [];
        foreach (self::SIGNATURES as $field => [$label, $default]) {
            $name = trim((string) ($hd[$field] ?? '')) ?: $default;
            $pos = trim((string) ($hd[self::position_col($field)] ?? ''));
            if ($pos === '') { $pos = MyApproverMapModel::position_of('lddapada', $field, $name); }
            if ($pos === '') {
                $u = $this->db->query("SELECT `position` FROM myua_user WHERE `full_name` = ? LIMIT 1", [$name])->getRowArray();
                $pos = (string) ($u['position'] ?? '');
            }
            $out[$field] = [$name, $pos];
        }
        return $out;
    }

    // The four signatures from the save request. A name must be mapped to that
    // signature, or be the one this LDDAP-ADA already has; a blank position takes the mapped one.
    private function signatures_from_request(array $saved = []): array
    {
        $vals = [];
        foreach (self::SIGNATURES as $field => [$label, $default]) {
            $name = trim((string) $this->request->getPostGet($field));
            $pos = mb_substr(trim((string) $this->request->getPostGet(self::position_col($field))), 0, 150);
            if ($name === '' || (!MyApproverMapModel::is_mapped('lddapada', $field, $name) && $name !== trim((string) ($saved[$field] ?? '')))) {
                echo "
				<script>
				toastr.error('Choose the {$label} signatory.', 'Oops!', {
						progressBar: true,
						closeButton: true,
						timeOut:2500,
					});
				</script>
				";
                die();
            }
            $vals[$field] = $name;
            $vals[self::position_col($field)] = $pos !== '' ? $pos : MyApproverMapModel::position_of('lddapada', $field, $name);
        }
        return $vals;
    }

    // SET clause / values for the signature columns
    private static function signature_set(array $vals): string
    {
        return implode(', ', array_map(fn ($c) => "`{$c}` = ?", array_keys($vals)));
    }

    public function __construct(){
        parent::__construct();
        $this->session = session();
        $this->request = \Config\Services::request();
		$this->db = \Config\Database::connect();
		$this->cuser = $this->session->get('__xsys_myuserzicas__');
        
    }

	// Refuses the save when a DV is listed twice, or is already on another LDDAP-ADA
	// that is not returned (see lddapada_save)
	private function check_dvs_free($recid, array $dvs) {
		$fail = function ($msg) {
			echo "
			<script>
			toastr.error(" . json_encode($msg) . ", 'Oops!', {
					progressBar: true,
					closeButton: true,
					timeOut:3000,
				});
			</script>
			";
			die();
		};
		$dvs = array_map(fn ($d) => trim((string) $d), $dvs);
		$seen = [];
		foreach ($dvs as $dv) {
			if ($dv === '') { continue; }
			if (isset($seen[mb_strtolower($dv)])) {
				$fail('DV ' . $dv . ' is listed twice on this LDDAP-ADA!');
			}
			$seen[mb_strtolower($dv)] = true;
		}

		$saved = [];
		$returned = false;
		if (!empty($recid)) {
			$hd = $this->db->query("SELECT `is_disapproved` FROM tbl_lddapada_hd WHERE `recid` = ?", [$recid])->getRowArray();
			$returned = !empty($hd) && (string) $hd['is_disapproved'] === '1';
			foreach ($this->db->query("SELECT `dvno` FROM tbl_lddapada_dvno WHERE `lddapada_id` = ?", [$recid])->getResultArray() as $r) {
				$saved[mb_strtolower(trim((string) $r['dvno']))] = true;
			}
		}

		foreach ($dvs as $dv) {
			if ($dv === '' || (!$returned && isset($saved[mb_strtolower($dv)]))) { continue; }
			$on = $this->db->query("
				SELECT h.`lddapadano`, h.`recid`
				FROM tbl_lddapada_dvno v
				JOIN tbl_lddapada_hd h ON h.`recid` = v.`lddapada_id`
				WHERE TRIM(v.`dvno`) = ? AND h.`recid` <> ? AND h.`is_disapproved` = 0
				ORDER BY h.`recid` DESC
				LIMIT 1
			", [$dv, (int) $recid])->getRowArray();
			if (!empty($on)) {
				$where = trim((string) $on['lddapadano']) !== '' ? $on['lddapadano'] : '#' . $on['recid'];
				$fail('DV ' . $dv . ' is already on LDDAP-ADA ' . $where . '. A DV can be paid on another LDDAP-ADA only after that one is returned.');
			}
		}
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
		$ckno = $this->request->getPostGet('ckno');


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

		$rw = $this->db->query("SELECT `funding_source` FROM tbl_disbursement_hd WHERE `dvno` = ?", [$dv_fundingsource ?? ''])->getRowArray();
		$funding_source = $rw['funding_source'] ?? '';

		// if ($is_ci == '0') {
		// 	$ckno =  $this->get_ctr_ada('CTRL_NO01');//TRANSACTION NO
		// }

		// if ($is_ci == '1') {
		// 	$ckno =  $this->get_ctr_ci('9926','CTRL_NO01');//TRANSACTION NO
		// }

		$ckno = $ckno;

		// LDDAP-ADA no. is used once. Checked when it is set or changed, so records that
		// already share a number can still be edited.
		$__no = trim((string) $lddapadano);
		if ($__no !== '') {
			$__cur = empty($recid) ? null : $this->db->query("SELECT `lddapadano` AS n FROM tbl_lddapada_hd WHERE `recid` = ?", [$recid])->getRowArray();
			if (!$__cur || trim((string) $__cur['n']) !== $__no) {
				$__dup = $this->db->query("SELECT `recid` FROM tbl_lddapada_hd WHERE TRIM(`lddapadano`) = ? AND `recid` <> ? LIMIT 1", [$__no, (int) $recid])->getRowArray();
				if (!empty($__dup)) {
					echo "
					<script>
					toastr.error('That LDDAP-ADA no. is already used on another LDDAP-ADA!', 'Oops!', {
							progressBar: true,
							closeButton: true,
							timeOut:2500,
						});
					</script>
					";
					die();
				}
			}
		}

		// A DV is paid on one LDDAP-ADA only: it cannot be added while another
		// LDDAP-ADA that is not returned (disapproved) holds it. Checked for DVs added
		// by this save, and for all of them when a returned LDDAP-ADA is resubmitted,
		// so LDDAP-ADAs saved before this rule can still be edited.
		$this->check_dvs_free($recid, (array) $dvno_list);

		if (empty($recid)) {
			fods_require_access(\Config\AccessCodes::LDDAPADA_ADD, 'Saving');
			// $cseqn =  $this->get_ctr_lddapada($fund_cluster_code,$funding_source,'CTRL_NO01');//TRANSACTION NO

			$cseqn = $lddapadano;
			$sigs = $this->signatures_from_request();

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
			$this->db->query("UPDATE tbl_lddapada_hd SET " . self::signature_set($sigs) . " WHERE `recid` = ?", array_merge(array_values($sigs), [$project_id]));

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

			$status = "LDDAP-ADA saved successfully";
			$color = "success";
		}else{
			fods_require_access(\Config\AccessCodes::LDDAPADA_UPDATE, 'Updating');

			// Once approved the LDDAP-ADA is locked; a disapproved one can be
			// corrected and goes back for approval
			$current = $this->db->query("SELECT * FROM tbl_lddapada_hd WHERE `recid` = ?", [$recid])->getRowArray();
			if (empty($current)) {
				echo "
				<script>
				toastr.error('LDDAP-ADA not found', 'Oops!', {
						progressBar: true,
						closeButton: true,
						timeOut:2000,
					});
				</script>
				";
				die();
			}
			if ($current['is_approved'] == '1') {
				echo "
				<script>
				toastr.error('This LDDAP-ADA is already approved and can no longer be edited', 'Locked', {
						progressBar: true,
						closeButton: true,
						timeOut:2500,
					});
				</script>
				";
				die();
			}
			$sigs = $this->signatures_from_request($current);
			$is_resubmit = $current['is_disapproved'] == '1';
			if ($is_resubmit) {
				$this->db->query("UPDATE tbl_lddapada_hd SET `is_pending` = '1', `is_disapproved` = '0' WHERE `recid` = ?", [$recid]);
			}

			$query = $this->db->query("
				UPDATE tbl_lddapada_hd
				SET
					`ckno` = ?,
					`lddapadano` = ?,
					`mds_branch` = ?,
					`mds_accountno` = ?,
					`lddapada_date` = ?,
					`fund_cluster_code` = ?,
					`funding_source` = ?,
					`ada_approver` = ?,
					`is_ci` = ?
				WHERE recid = ?
			", [
				$ckno,
				$lddapadano,
				$mds_branch,
				$mds_accountno,
				$lddapada_date,
				$fund_cluster_code,
				$funding_source,
				$ada_approver,
				$is_ci,
				$recid
			]);
			$this->db->query("UPDATE tbl_lddapada_hd SET " . self::signature_set($sigs) . " WHERE `recid` = ?", array_merge(array_values($sigs), [$recid]));

			$project_id = $recid;

			if (!empty($dvno_list)) {
				$query = $this->db->query("DELETE FROM tbl_lddapada_dvno WHERE `lddapada_id` = ?", [$project_id]);
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
				$query = $this->db->query("DELETE FROM tbl_lddapada_dvno WHERE `lddapada_id` = ?", [$project_id]);
			}

			if (!empty($dvdtdata)) {
				$query = $this->db->query("DELETE FROM tbl_lddapada_dt WHERE `lddapada_id` = ?", [$project_id]);
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
				$query = $this->db->query("DELETE FROM tbl_lddapada_dt WHERE `lddapada_id` = ?", [$project_id]);
			}

			$status = $is_resubmit ? "LDDAP-ADA updated and sent back for approval" : "LDDAP-ADA updated successfully";
			$color = "info";
		}

		$lddapno_display = empty($recid) ? $cseqn : $lddapadano;

		//HISTORY
		if ($query) {
			if (empty($recid)) {
				MyHistoryModel::log('lddapada', $project_id, 'CREATED', '', null, $lddapno_display);
			} else {
				MyHistoryModel::log('lddapada', $recid, $is_resubmit ? 'RESUBMITTED' : 'UPDATED', '',
					MyHistoryModel::diff($current, MyHistoryModel::row('tbl_lddapada_hd', $recid)), $lddapno_display);
			}
		}

		if ($query) {
			// Echo JavaScript to show the toast and then redirect
			echo "
			<script>
				document.getElementById('submitBtn').disabled = true;
				document.getElementById('lddapadano').value = '" . $lddapno_display . "';
				toastr.$color('{$status}!', 'Well Done!', {
						progressBar: true,
						closeButton: true,
						timeOut:2000,
					});
				setTimeout(function() {
						window.location.href = 'mylddapada?meaction=MAIN'; // Redirect to MAIN view
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
	
	//LDDAP-ADA - ONE RECORD WITH ITS DV LINES (editor / review modals)
	public function lddapada_load($recid) {
		$hd = $this->db->query("SELECT * FROM tbl_lddapada_hd WHERE `recid` = ?", [$recid])->getRowArray();
		if (empty($hd)) {
			return null;
		}
		$hd['lines'] = $this->db->query("
			SELECT `recid`, `dvno`, `payee_name`, `payee_account_num`, `serialno`, `uacs_code`,
				`gross_amount`, `total_deduction`, `net_amount`, `remarks`
			FROM tbl_lddapada_dt
			WHERE `lddapada_id` = ?
			ORDER BY `recid` ASC
		", [$recid])->getResultArray();
		// the DVs in the order they were picked
		$hd['dvno_list'] = array_column($this->db->query("
			SELECT `dvno` FROM tbl_lddapada_dvno WHERE `lddapada_id` = ? ORDER BY `recid` ASC
		", [$recid])->getResultArray(), 'dvno');
		// what the printout will show for each signature (older LDDAP-ADAs: the previous fixed names)
		$hd['print_signatures'] = $this->signatures_for($hd);
		return $hd;
	}

	//LDDAP-ADA - LINE FOR EACH PICKED DV: payee, account no., amounts, and the
	//allotment class (UACS with the largest amount on the DV's own ORS / BURS)
	// A DV's withholding tax for the LDDAP-ADA (columns of tbl_disbursement_hd):
	// its total deductions less the other deductions and the payroll deductions
	// that are not taxes (SSS, HDMF, Pag-IBIG MP2 / loan, HMO)
	const WITHHOLDING_SQL = "(`total_deduction` - `other_deductions` - `sss` - `hdmf` - `pagibig_mp2` - `pagibig_loan` - `hmo`)";

	public function dv_lines($dvnos) {
		$out = [];
		foreach ((array) $dvnos as $dvno) {
			// Withholding tax = the DV's taxes only (VAT/EWT/PT, WTAX, income tax):
			// its other deductions and payroll deductions are left out. The net
			// stays the DV's net.
			$dv = $this->db->query("
				SELECT `dvno`, `payee_name`, `serialno`, `gross_amount`,
					" . self::WITHHOLDING_SQL . " AS `total_deduction`,
					`net_amount`
				FROM tbl_disbursement_hd WHERE `dvno` = ? ORDER BY `recid` DESC LIMIT 1
			", [$dvno])->getRowArray();
			if (empty($dv)) {
				continue;
			}
			$acct = $this->db->query("SELECT `payee_account_num` FROM tbl_payee WHERE `payee_name` = ? LIMIT 1", [$dv['payee_name']])->getRowArray();
			$dv['payee_account_num'] = $acct['payee_account_num'] ?? '';
			$dv['uacs_code'] = '';
			foreach (['ors', 'burs'] as $src) {
				$ob = $this->db->query("SELECT `recid` FROM tbl_{$src}_hd WHERE `serialno` = ? ORDER BY `recid` DESC LIMIT 1", [$dv['serialno']])->getRowArray();
				if (empty($ob)) {
					continue;
				}
				$top = $this->db->query("
					SELECT uacs_code FROM (
						SELECT uacs_code, amount FROM tbl_{$src}_direct_ps_dt WHERE project_id = ?
						UNION ALL SELECT uacs_code, amount FROM tbl_{$src}_direct_mooe_dt WHERE project_id = ?
						UNION ALL SELECT uacs_code, amount FROM tbl_{$src}_direct_co_dt WHERE project_id = ?
						UNION ALL SELECT uacs_code, amount FROM tbl_{$src}_indirect_ps_dt WHERE project_id = ?
						UNION ALL SELECT uacs_code, amount FROM tbl_{$src}_indirect_mooe_dt WHERE project_id = ?
						UNION ALL SELECT uacs_code, amount FROM tbl_{$src}_indirect_co_dt WHERE project_id = ?
					) x
					ORDER BY amount DESC
					LIMIT 1
				", array_fill(0, 6, $ob['recid']))->getRowArray();
				$dv['uacs_code'] = $top['uacs_code'] ?? '';
				break;
			}
			$out[] = $dv;
		}
		return $out;
	}

	//LDDAP-ADA - APPROVAL / DISAPPROVAL (single step)
	public function lddapada_approve() { $this->lddapada_decide(true); }
	public function lddapada_disapprove() { $this->lddapada_decide(false); }

	private function lddapada_decide($approve) {
		$recid = $this->request->getPostGet('recid');
		$remarks = trim((string) $this->request->getPostGet('remarks'));

		fods_require_access(\Config\AccessCodes::LDDAPADAAPPROVAL_APPROVE, $approve ? 'Approve' : 'Disapprove');

		// Only an LDDAP-ADA that is still waiting can be decided
		$hd = $this->db->query("SELECT `is_approved`, `is_disapproved` FROM tbl_lddapada_hd WHERE `recid` = ?", [$recid])->getRowArray();
		if (empty($hd) || $hd['is_approved'] == '1' || $hd['is_disapproved'] == '1') {
			echo "
			<script>
			toastr.error('This LDDAP-ADA is not pending approval', 'Oops!', {
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

		$query = $this->db->query("
			UPDATE tbl_lddapada_hd
			SET
				`is_pending` = '0',
				`is_approved` = ?,
				`is_disapproved` = ?,
				`approver` = ?,
				`remarks` = ?
			WHERE `recid` = ?
		", [$approve ? 1 : 0, $approve ? 0 : 1, $this->cuser, mb_substr($remarks, 0, 250), $recid]);

		$status = $approve ? "LDDAP-ADA approved" : "LDDAP-ADA disapproved";
		$color = $approve ? "success" : "warning";
		if ($query) {
			$ref = MyHistoryModel::row('tbl_lddapada_hd', $recid);
			MyHistoryModel::log('lddapada', $recid, $approve ? 'APPROVED' : 'DISAPPROVED', $remarks, null, $ref['lddapadano'] ?? '');
		}
		if ($query) {
			echo "
			<script>
				toastr.{$color}('{$status}', 'Well Done!', {
						progressBar: true,
						closeButton: true,
						timeOut:2000,
					});
				setTimeout(function() {
						window.location.href = 'mylddapada?meaction=MAIN';
					}, 1000); // reload after 1 s; the toast still runs its 2.5 s (fods-flash.js carries it over)
			</script>
			";
			exit;
		} else {
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
