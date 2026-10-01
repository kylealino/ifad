<?php
namespace App\Models;
use CodeIgniter\Model;

class MyDisbursementModel extends Model
{

    protected $db;

    public function __construct(){
        parent::__construct();
        $this->session = session();
        $this->request = \Config\Services::request();
		$this->db = \Config\Database::connect();
		$this->cuser = $this->session->get('__xsys_myuserzicas__');
        
    }

    // Helper method to log history - using correct column names
    private function logHistory($transaction_id, $action_type, $action_status = null, $remarks = null, $field_changes = null, $serialno = null) {
        $ip_address = $this->request->getIPAddress();
        $user_agent = $this->request->getUserAgent()->getAgentString();
        
        // Get user role from myua_user table - using correct column names
        $roleQuery = $this->db->query("
            SELECT position as role_name
            FROM myua_user
            WHERE username = ?
            LIMIT 1
        ", [$this->cuser]);
        $role = $roleQuery->getRowArray();
        $user_role = $role ? $role['role_name'] : 'Unknown';

        // Get serialno if not provided
        if (empty($serialno)) {
            $serialQuery = $this->db->query("SELECT serialno FROM tbl_disbursement_hd WHERE recid = ?", [$transaction_id]);
            $serialData = $serialQuery->getRowArray();
            $serialno = $serialData ? $serialData['serialno'] : null;
        }
        
        // Prepare field changes as JSON
        $field_changes_json = $field_changes ? json_encode($field_changes) : null;
        
        $query = $this->db->query("
            INSERT INTO tbl_disbursement_history (
                transaction_id,
                serialno,
                action_type,
                action_status,
                action_by,
                action_by_role,
                remarks,
                field_changes,
                ip_address,
                user_agent
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ", [
            $transaction_id,
            $serialno,
            $action_type,
            $action_status,
            $this->cuser,
            $user_role,
            $remarks,
            $field_changes_json,
            $ip_address,
            $user_agent
        ]);
        
        return $query;
    }

    // Method to get old data for comparison (for update tracking)
    private function getOldData($recid) {
        $query = $this->db->query("SELECT * FROM tbl_disbursement_hd WHERE recid = ?", [$recid]);
        return $query->getRowArray();
    }

    // GET TRANSACTION HISTORY - using correct column names
    public function getTransactionHistory($transaction_id) {
        $query = $this->db->query("
            SELECT
                h.`action_type`,
                h.`action_status`,
                h.`action_by`,
                h.`action_by_role`,
                h.`remarks`,
                h.`field_changes`,
                h.`date_created`,
                u.full_name as user_full_name
            FROM tbl_disbursement_history h
            LEFT JOIN myua_user u ON u.username = h.action_by
            WHERE h.transaction_id = ?
            ORDER BY h.date_created DESC, h.recid DESC
        ", [$transaction_id]);
        return $query->getResultArray();
    }

    //DISBURSEMENT - ONE DV WITH ITS LINE ITEMS AND HISTORY (editor / review modals)
    public function disbursement_load($recid) {
        $hd = $this->db->query("SELECT * FROM tbl_disbursement_hd WHERE `recid` = ?", [$recid])->getRowArray();
        if (empty($hd)) {
            return null;
        }
        $hd['grids'] = [];
        foreach (self::GRIDS as $key => $table) {
            $hd['grids'][$key] = $this->db->query("
                SELECT `recid`, `responsibility_code`, `mfopaps_code`, `amount`
                FROM tbl_disbursement_{$table}
                WHERE `project_id` = ?
                ORDER BY `recid` ASC
            ", [$recid])->getResultArray();
        }
        $hd['history'] = $this->getTransactionHistory($recid);
        return $hd;
    }

    // Line tables per grid key (tbl_ors_*, tbl_burs_* and tbl_disbursement_* share the suffixes)
    const GRIDS = [
        'ps_direct'     => 'direct_ps_dt',
        'ps_indirect'   => 'indirect_ps_dt',
        'mooe_direct'   => 'direct_mooe_dt',
        'mooe_indirect' => 'indirect_mooe_dt',
        'co_direct'     => 'direct_co_dt',
        'co_indirect'   => 'indirect_co_dt',
    ];

    //DISBURSEMENT - PREFILL FROM AN ORS / BURS (same fields the DV copied before,
    //lines summed per responsibility code)
    public function obligation_load($source, $id) {
        $source = $source === 'BURS' ? 'burs' : 'ors';
        $hd = $this->db->query("SELECT * FROM tbl_{$source}_hd WHERE `recid` = ?", [$id])->getRowArray();
        if (empty($hd)) {
            return null;
        }
        $out = [
            'source' => strtoupper($source),
            'serialno' => $hd['serialno'],
            'disbursement_date' => $hd[$source . '_date'],
        ];
        foreach (['particulars', 'funding_source', 'payee_name', 'payee_office', 'payee_address', 'certified_a', 'position_a',
                  'fund_cluster_code', 'is_vatable', 'vat_percent', 'ewt_percent', 'pt_percent', 'other_deductions', 'income_tax',
                  'deduction_name', 'sss', 'hdmf', 'pagibig_mp2', 'pagibig_loan', 'hmo', 'wtax', 'category'] as $field) {
            $out[$field] = $hd[$field];
        }
        $out['grids'] = [];
        foreach (self::GRIDS as $key => $table) {
            $out['grids'][$key] = $this->db->query("
                SELECT `responsibility_code`, MIN(`mfopaps_code`) AS mfopaps_code, SUM(`amount`) AS amount
                FROM tbl_{$source}_{$table}
                WHERE `project_id` = ?
                GROUP BY `responsibility_code`
                ORDER BY MIN(`recid`)
            ", [$id])->getResultArray();
        }
        return $out;
    }

	public function disbursement_save() { 
		$recid = $this->request->getPostGet('recid');
		$serialno = $this->request->getPostGet('serialno');
		$particulars = $this->request->getPostGet('particulars');
		$funding_source = $this->request->getPostGet('funding_source');
		$payee_name = $this->request->getPostGet('payee_name');
		$payee_office = $this->request->getPostGet('payee_office');
		$payee_address = $this->request->getPostGet('payee_address');
		$certified_a = $this->request->getPostGet('certified_a');
		$position_a = $this->request->getPostGet('position_a');
		$certified_c = $this->request->getPostGet('certified_c');
		$position_c = $this->request->getPostGet('position_c');
		$dvno = $this->request->getPostGet('dvno');
		$fund_cluster_code = $this->request->getPostGet('fund_cluster_code');
		$budgetdtdata = $this->request->getPostGet('budgetdtdata');
		$budgetdtindirectdata = $this->request->getPostGet('budgetdtindirectdata');
		$budgetmooedtdata = $this->request->getPostGet('budgetmooedtdata');
		$budgetmooeindirectdtdata = $this->request->getPostGet('budgetmooeindirectdtdata');
		$budgetcodtdata = $this->request->getPostGet('budgetcodtdata');
		$budgetindirectcodtdata = $this->request->getPostGet('budgetindirectcodtdata');
		$disbursement_date = $this->request->getPostGet('disbursement_date');
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
			fods_require_access(\Config\AccessCodes::DISBURSEMENT_ADD, 'Saving');
			//INSERTING HD DATA
			$query = $this->db->query("
				INSERT INTO tbl_disbursement_hd (
					`serialno`,
					`particulars`,
					`funding_source`,
					`payee_name`,
					`payee_office`,
					`payee_address`,
					`certified_a`,
					`position_a`,
					`certified_c`,
					`position_c`,
					`dvno`,
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
					`added_by`,
					`disbursement_date`,
					`gross_amount`,
					`total_deduction`,
					`net_amount`
				)
				VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)", 
				[
					$serialno,
					$particulars,
					$funding_source,
					$payee_name,
					$payee_office,
					$payee_address,
					$certified_a,
					$position_a,
					$certified_c,
					$position_c,
					$dvno,
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
					$this->cuser,
					$disbursement_date,
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
					$responsibility_code = $medata[0]; 
					$mfopaps_code = $medata[1];
					$amount = $medata[2]; 

					$query = $this->db->query("
						INSERT INTO tbl_disbursement_direct_ps_dt (
							`project_id`,
							`responsibility_code`,
							`mfopaps_code`,
							`amount`,
							`added_by`
						)
						VALUES (?,?, ?, ?, ?)", 
						[
							$project_id,
							$responsibility_code,
							$mfopaps_code,
							$amount,
							$this->cuser
						]
					);
					
				}
			}

			if (!empty($budgetdtindirectdata)) {
				for($aa = 0; $aa < count($budgetdtindirectdata); $aa++){
					$medata = explode("x|x",$budgetdtindirectdata[$aa]);
					$responsibility_code = $medata[0]; 
					$mfopaps_code = $medata[1];
					$amount = $medata[2]; 

					$query = $this->db->query("
						INSERT INTO tbl_disbursement_indirect_ps_dt (
							`project_id`,
							`responsibility_code`,
							`mfopaps_code`,
							`amount`,
							`added_by`
						)
						VALUES (?, ?, ?, ?, ?)", 
						[
							$project_id,
							$responsibility_code,
							$mfopaps_code,
							$amount,
							$this->cuser
						]
					);
					
				}
			}

			//INSERTING MOOE DT DATA
			if (!empty($budgetmooedtdata)) {
				for($aa = 0; $aa < count($budgetmooedtdata); $aa++){
					$medata = explode("x|x",$budgetmooedtdata[$aa]);
					$responsibility_code = $medata[0]; 
					$mfopaps_code = $medata[1];
					$amount = $medata[2]; 

					$query = $this->db->query("
						INSERT INTO tbl_disbursement_direct_mooe_dt (
							`project_id`,
							`responsibility_code`,
							`mfopaps_code`,
							`amount`,
							`added_by`
						)
						VALUES (?, ?, ?, ?, ?)", 
						[
							$project_id,
							$responsibility_code,
							$mfopaps_code,
							$amount,
							$this->cuser
						]

					);
				}
			}

			//INSERTING INDIRECT MOOE DT DATA
			if (!empty($budgetmooeindirectdtdata)) {
				for($aa = 0; $aa < count($budgetmooeindirectdtdata); $aa++){
					$medata = explode("x|x",$budgetmooeindirectdtdata[$aa]);
					$responsibility_code = $medata[0]; 
					$mfopaps_code = $medata[1];
					$amount = $medata[2]; 

					$query = $this->db->query("
						INSERT INTO tbl_disbursement_indirect_mooe_dt (
							`project_id`,
							`responsibility_code`,
							`mfopaps_code`,
							`amount`,
							`added_by`
						)
						VALUES (?, ?, ?, ?, ?)", 
						[
							$project_id,
							$responsibility_code,
							$mfopaps_code,
							$amount,
							$this->cuser
						]

					);
				}
			}

			//INSERTING CO DT DATA
			if (!empty($budgetcodtdata)) {
				for($aa = 0; $aa < count($budgetcodtdata); $aa++){
					$medata = explode("x|x",$budgetcodtdata[$aa]);
					$responsibility_code = $medata[0]; 
					$mfopaps_code = $medata[1];
					$amount = $medata[2]; 

					$query = $this->db->query("
						INSERT INTO tbl_disbursement_direct_co_dt (
							`project_id`,
							`responsibility_code`,
							`mfopaps_code`,
							`amount`,
							`added_by`
						)
						VALUES (?, ?, ?, ?, ?)", 
						[
							$project_id,
							$responsibility_code,
							$mfopaps_code,
							$amount,
							$this->cuser
						]

					);
				}
			}

			if (!empty($budgetindirectcodtdata)) {
				for($aa = 0; $aa < count($budgetindirectcodtdata); $aa++){
					$medata = explode("x|x",$budgetindirectcodtdata[$aa]);
					$responsibility_code = $medata[0]; 
					$mfopaps_code = $medata[1];
					$amount = $medata[2]; 

					$query = $this->db->query("
						INSERT INTO tbl_disbursement_indirect_co_dt (
							`project_id`,
							`responsibility_code`,
							`mfopaps_code`,
							`amount`,
							`added_by`
						)
						VALUES (?, ?, ?, ?, ?)", 
						[
							$project_id,
							$responsibility_code,
							$mfopaps_code,
							$amount,
							$this->cuser
						]

					);
				}
			}

			//UPDATING OF DVNO in the ORS / BURS it was prepared from
			$query = $this->db->query("
				UPDATE tbl_ors_hd
				SET
					`dvno` = ?
				WHERE serialno = ?
			", [
				$dvno,
				$serialno
			]);
			$this->db->query("UPDATE tbl_burs_hd SET `dvno` = ? WHERE serialno = ?", [$dvno, $serialno]);

			// LOG: Added new transaction
			$this->logHistory(
				$project_id,
				'ADDED',
				'PENDING',
				'New disbursement transaction created',
				null,
				$serialno
			);

			$status = "Disbursement saved successfully";
			$color = "success";
		}else{
			fods_require_access(\Config\AccessCodes::DISBURSEMENT_UPDATE, 'Updating');
			// Get old data before update
			$oldData = $this->getOldData($recid);
			if (empty($oldData)) {
				echo "
				<script>
				toastr.error('Disbursement not found', 'Oops!', {
						progressBar: true,
						closeButton: true,
						timeOut:2000,
					});
				</script>
				";
				die();
			}

			// Once accounting has approved it the DV is locked; a disapproved DV
			// can be corrected and goes back for approval
			$is_disapproved = $oldData['is_disapproved_certa'] == '1' || $oldData['is_disapproved_certb'] == '1';
			if ($oldData['is_approved_certa'] == '1' && !$is_disapproved) {
				echo "
				<script>
				toastr.error('This disbursement is already approved and can no longer be edited', 'Locked', {
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
					UPDATE tbl_disbursement_hd
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
				UPDATE tbl_disbursement_hd
				SET
					`particulars` = ?,
					`serialno` = ?,
					`funding_source` = ?,
					`payee_name` = ?,
					`payee_office` = ?,
					`payee_address` = ?,
					`certified_a` = ?,
					`position_a` = ?,
					`certified_c` = ?,
					`position_c` = ?,
					`dvno` = ?,
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
					`disbursement_date` = ?,
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
				$certified_c,
				$position_c,
				$dvno,
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
				$disbursement_date,
				$gross_amount,
				$total_deduction,
				$net_amount,
				$recid
			]);

			$project_id = $recid;

			if (!empty($budgetdtdata)) {
				$query = $this->db->query("DELETE FROM tbl_disbursement_direct_ps_dt WHERE `project_id` = ?", [$project_id]);
				for($aa = 0; $aa < count($budgetdtdata); $aa++){
					$medata = explode("x|x",$budgetdtdata[$aa]); 
					$responsibility_code = $medata[0]; 
					$mfopaps_code = $medata[1];
					$amount = $medata[2]; 

					$query = $this->db->query("
						INSERT INTO tbl_disbursement_direct_ps_dt (
							`project_id`,
							`responsibility_code`,
							`mfopaps_code`,
							`amount`,
							`added_by`
						)
						VALUES (?, ?, ?, ?, ?)", 
						[
							$project_id,
							$responsibility_code,
							$mfopaps_code,
							$amount,
							$this->cuser
						]
					);
					
				}
			}else{
				$query = $this->db->query("DELETE FROM tbl_disbursement_direct_ps_dt WHERE `project_id` = ?", [$project_id]);
			}

			if (!empty($budgetdtindirectdata)) {
				$query = $this->db->query("DELETE FROM tbl_disbursement_indirect_ps_dt WHERE `project_id` = ?", [$project_id]);
				for($aa = 0; $aa < count($budgetdtindirectdata); $aa++){
					$medata = explode("x|x",$budgetdtindirectdata[$aa]);
					$responsibility_code = $medata[0]; 
					$mfopaps_code = $medata[1];
					$amount = $medata[2]; 

					$query = $this->db->query("
						INSERT INTO tbl_disbursement_indirect_ps_dt (
							`project_id`,
							`responsibility_code`,
							`mfopaps_code`,
							`amount`,
							`added_by`
						)
						VALUES (?, ?, ?, ?, ?)", 
						[
							$project_id,
							$responsibility_code,
							$mfopaps_code,
							$amount,
							$this->cuser
						]
					);
					
				}
			}else{
				$query = $this->db->query("DELETE FROM tbl_disbursement_indirect_ps_dt WHERE `project_id` = ?", [$project_id]);
			}

			if (!empty($budgetmooedtdata)) {
				$query = $this->db->query("DELETE FROM tbl_disbursement_direct_mooe_dt WHERE `project_id` = ?", [$project_id]);
				for($aa = 0; $aa < count($budgetmooedtdata); $aa++){
					$medata = explode("x|x",$budgetmooedtdata[$aa]);
					$responsibility_code = $medata[0]; 
					$mfopaps_code = $medata[1];
					$amount = $medata[2]; 

					$query = $this->db->query("
						INSERT INTO tbl_disbursement_direct_mooe_dt (
							`project_id`,
							`responsibility_code`,
							`mfopaps_code`,
							`amount`,
							`added_by`
						)
						VALUES (?, ?, ?, ?, ?)", 
						[
							$project_id,
							$responsibility_code,
							$mfopaps_code,
							$amount,
							$this->cuser
						]

					);
					
				}
			}else{
				$query = $this->db->query("DELETE FROM tbl_disbursement_direct_mooe_dt WHERE `project_id` = ?", [$project_id]);
			}

			if (!empty($budgetmooeindirectdtdata)) {
				$query = $this->db->query("DELETE FROM tbl_disbursement_indirect_mooe_dt WHERE `project_id` = ?", [$project_id]);
				for($aa = 0; $aa < count($budgetmooeindirectdtdata); $aa++){
					$medata = explode("x|x",$budgetmooeindirectdtdata[$aa]);
					$responsibility_code = $medata[0]; 
					$mfopaps_code = $medata[1];
					$amount = $medata[2]; 

					$query = $this->db->query("
						INSERT INTO tbl_disbursement_indirect_mooe_dt (
							`project_id`,
							`responsibility_code`,
							`mfopaps_code`,
							`amount`,
							`added_by`
						)
						VALUES (?, ?, ?, ?, ?)", 
						[
							$project_id,
							$responsibility_code,
							$mfopaps_code,
							$amount,
							$this->cuser
						]

					);
					
				}
			}else{
				$query = $this->db->query("DELETE FROM tbl_disbursement_indirect_mooe_dt WHERE `project_id` = ?", [$project_id]);
			}

			if (!empty($budgetcodtdata)) {
				$query = $this->db->query("DELETE FROM tbl_disbursement_direct_co_dt WHERE `project_id` = ?", [$project_id]);
				for($aa = 0; $aa < count($budgetcodtdata); $aa++){
					$medata = explode("x|x",$budgetcodtdata[$aa]);
					$responsibility_code = $medata[0]; 
					$mfopaps_code = $medata[1];
					$amount = $medata[2]; 

					$query = $this->db->query("
						INSERT INTO tbl_disbursement_direct_co_dt (
							`project_id`,
							`responsibility_code`,
							`mfopaps_code`,
							`amount`,
							`added_by`
						)
						VALUES (?, ?, ?, ?, ?)", 
						[
							$project_id,
							$responsibility_code,
							$mfopaps_code,
							$amount,
							$this->cuser
						]

					);
					
				}
			}else{
				$query = $this->db->query("DELETE FROM tbl_disbursement_direct_co_dt WHERE `project_id` = ?", [$project_id]);
			}

			if (!empty($budgetindirectcodtdata)) {
				$query = $this->db->query("DELETE FROM tbl_disbursement_indirect_co_dt WHERE `project_id` = ?", [$project_id]);
				for($aa = 0; $aa < count($budgetindirectcodtdata); $aa++){
					$medata = explode("x|x",$budgetindirectcodtdata[$aa]);
					$responsibility_code = $medata[0]; 
					$mfopaps_code = $medata[1];
					$amount = $medata[2]; 

					$query = $this->db->query("
						INSERT INTO tbl_disbursement_indirect_co_dt (
							`project_id`,
							`responsibility_code`,
							`mfopaps_code`,
							`amount`,
							`added_by`
						)
						VALUES (?, ?, ?, ?, ?)", 
						[
							$project_id,
							$responsibility_code,
							$mfopaps_code,
							$amount,
							$this->cuser
						]

					);
					
				}
			}else{
				$query = $this->db->query("DELETE FROM tbl_disbursement_indirect_co_dt WHERE `project_id` = ?", [$project_id]);
			}

			// LOG: Updated transaction with field changes
			$fieldChanges = [];
			$fieldsToTrack = [
				'particulars', 'funding_source', 'payee_name', 'payee_office', 
				'payee_address', 'certified_a', 'position_a', 'certified_c', 
				'position_c', 'dvno', 'fund_cluster_code', 'category',
				'gross_amount', 'net_amount', 'disbursement_date'
			];
			
			foreach ($fieldsToTrack as $field) {
				$oldValue = $oldData[$field] ?? '';
				$newValue = $$field; // Dynamic variable
				if ($oldValue != $newValue) {
					$fieldChanges[] = [
						'field' => $field,
						'old' => $oldValue,
						'new' => $newValue
					];
				}
			}

			//UPDATING OF DVNO in the ORS / BURS it was prepared from
			$query = $this->db->query("
				UPDATE tbl_ors_hd
				SET
					`dvno` = ?
				WHERE serialno = ?
			", [
				$dvno,
				$serialno
			]);
			$this->db->query("UPDATE tbl_burs_hd SET `dvno` = ? WHERE serialno = ?", [$dvno, $serialno]);
			
			$this->logHistory(
				$project_id,
				'UPDATED',
				'PENDING',
				$is_disapproved ? 'Corrected and resubmitted for approval' : 'Transaction details updated',
				!empty($fieldChanges) ? $fieldChanges : null,
				$serialno
			);

			$status = $is_disapproved ? "Disbursement updated and sent back for approval" : "Disbursement updated successfully";
			$color = "info";
		}

		if ($query) {
			echo "
			<script>
				document.getElementById('submitBtn').disabled = true;
				toastr.{$color}('{$status}!', 'Well Done!', {
						progressBar: true,
						closeButton: true,
						timeOut:2500,
					});
				setTimeout(function() {
						window.location.href = 'mydisbursement?meaction=MAIN';
					}, 2500);
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
	
	//ACCOUNTING APPROVAL / DISAPPROVAL (single step; stored in the certa columns)
	public function disbursement_approve() { $this->disbursement_decide(true); }
	public function disbursement_disapprove() { $this->disbursement_decide(false); }

	private function disbursement_decide($approve) {
		$recid = $this->request->getPostGet('recid');
		$remarks = trim((string) $this->request->getPostGet('remarks'));
		$approver = $this->cuser;

		fods_require_access(\Config\AccessCodes::DISBURSEMENTAPPROVAL_APPROVE, $approve ? 'Approve' : 'Disapprove');

		// Only a DV that is still waiting can be decided
		$hd = $this->getOldData($recid);
		if (empty($hd) || $hd['is_approved_certa'] == '1' || $hd['is_disapproved_certa'] == '1') {
			echo "
			<script>
			toastr.error('This disbursement is not waiting for approval', 'Oops!', {
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
			UPDATE tbl_disbursement_hd
			SET
				`is_pending` = '0',
				`is_approved_certa` = ?,
				`is_disapproved_certa` = ?,
				`certa_approver` = ?,
				`certa_remarks` = ?
			WHERE `recid` = ?
		", [$approve ? 1 : 0, $approve ? 0 : 1, $approver, mb_substr($remarks, 0, 100), $recid]);

		if ($query) {
			// LOG: decision
			$this->logHistory(
				$recid,
				$approve ? 'APPROVED' : 'DISAPPROVED',
				$approve ? 'APPROVED' : 'DISAPPROVED',
				$remarks,
				['approver' => $approver]
			);

			$status = $approve ? "Disbursement approved" : "Disbursement disapproved";
			$color = $approve ? "success" : "warning";
			echo "
			<script>
				toastr.{$color}('{$status}', 'Well Done!', {
						progressBar: true,
						closeButton: true,
						timeOut:2500,
					});
				setTimeout(function() {
						window.location.href = 'mydisbursementapproval';
					}, 2500);
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

	public function get_ctr_disbursement($fund_cluster,$funding_source,$mfld='') { 
		$accessquery = $this->db->query("
		CREATE TABLE if not exists `myctr_disbursement` (
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
		
		$qctr = $this->db->query("select {$xfield} from myctr_disbursement WHERE CTR_YEAR = '$xsysyear' AND CTR_MONTH = '$xsysmonth' AND CTR_DAY = '$xsysday'  limit 1");
		if($qctr->getNumRows() == 0) {
			$xnumb = '001';
			$query = $this->db->query( "insert into myctr_disbursement (CTR_YEAR,CTR_MONTH,CTR_DAY,{$xfield}) values('$xsysyear','$xsysmonth','$xsysday','$xnumb')");
			$qctr->freeResult();
		} else {
			$qctr->freeResult();
			$qctr = $this->db->query( "select {$xfield} MYFIELD from myctr_disbursement WHERE CTR_YEAR = '$xsysyear' AND CTR_MONTH = '$xsysmonth' AND CTR_DAY = '$xsysday' limit 1");
			$rctr = $qctr->getRowArray();
			if(trim($rctr['MYFIELD'],' ') == '') { 
				$xnumb = '001';
			} else {
				$xnumb = $rctr['MYFIELD'];
				$qctr = $this->db->query("select ('{$xnumb}' + 1) XNUMB");
				$rctr = $qctr->getRowArray();
				$xnumb = trim($rctr['XNUMB'],' ');
				$xnumb = str_pad($xnumb + 0,3,"0",STR_PAD_LEFT);
				$query = $this->db->query("update myctr_disbursement set {$xfield} = '{$xnumb}'");
			}
		}
		return  $fund_cluster . '-' . $funding_source . '-' . $xsysyear . '-' . $xsysmonth . '-' . $xnumb;
	} 

} //end main class
?>