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

	//PPMP - ONE RECORD WITH ITS ITEMS (editor modal)
	public function ppmp_load($recid) {
		$hd = $this->db->query("SELECT * FROM tbl_ppmp_hd WHERE `recid` = ?", [$recid])->getRowArray();
		if (empty($hd)) {
			return null;
		}
		$hd['items'] = $this->db->query("
			SELECT
				`recid`,
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
				`remarks`
			FROM tbl_ppmp_dt
			WHERE `ppmp_id` = ?
			ORDER BY `recid` ASC
		", [$recid])->getResultArray();
		// the versions of its items: the original, then one after each PR that used it
		$hd['usage'] = (new MyPPMPUsageModel())->versions($recid);
		// its supporting documents (Documents tab)
		$hd['attachments'] = $this->attachments($recid);
		// approval (one step): whether this user may decide it now
		$hd['approval'] = \App\Controllers\MyPPMP::approvalOf($hd);
		$hd['can_decide'] = self::can_decide($hd);
		return $hd;
	}

	// Anyone whose role has PPMP Approval - Approve decides a PPMP that is waiting
	// for approval (like LDDAP-ADA and ADA)
	public static function can_decide(array $hd) {
		return \App\Controllers\MyPPMP::approvalOf($hd) === 'pending' && fods_has_access(\Config\AccessCodes::PPMPAPPROVAL_APPROVE);
	}

	// An approved PPMP can no longer be edited (its documents still can)
	public static function is_locked(array $hd) {
		return \App\Controllers\MyPPMP::approvalOf($hd) === 'approved';
	}

	//PPMP - SAVE
	public function ppmp_save() {
		$recid = $this->request->getPostGet('recid');
		$ppmpno = $this->request->getPostGet('ppmpno');
		$end_user = $this->request->getPostGet('end_user');
		$fiscal_year = $this->request->getPostGet('fiscal_year');
		$project_title = $this->request->getPostGet('project_title');
		$responsibility_code = $this->request->getPostGet('responsibility_code');
		$ppmpdtdata = $this->request->getPostGet('ppmpdtdata');
		$is_indicative = $this->request->getPostGet('is_indicative') == 1 ? 1 : 0;
		$is_final = $this->request->getPostGet('is_final') == 1 ? 1 : 0;
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

		if (!empty($recid)) {
			$saved = $this->db->query("SELECT `is_approved`, `is_disapproved` FROM tbl_ppmp_hd WHERE `recid` = ?", [$recid])->getRowArray();
			if (!empty($saved) && self::is_locked($saved)) {
				echo "
				<script>
				toastr.error('This PPMP is already approved and can no longer be edited', 'Oops!', {
						progressBar: true,
						closeButton: true,
						timeOut:2500,
					});
				</script>
				";
				die();
			}
		}

		// A PPMP the PRs have used: an item cannot go below what they took, and the
		// PPMP no. cannot change (PRs are linked to it by that number)
		if (!empty($recid)) {
			$used = (new MyPPMPUsageModel())->used_per_key($recid);
			if (!empty($used)) {
				$fail = function ($msg) {
					echo "
					<script>
					toastr.error(" . json_encode($msg, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) . ", 'Oops!', {
							progressBar: true,
							closeButton: true,
							timeOut:4000,
						});
					</script>
					";
					die();
				};
				$old = $this->db->query("SELECT `ppmpno` FROM tbl_ppmp_hd WHERE `recid` = ?", [$recid])->getRowArray();
				if ($old && mb_strtolower(trim($old['ppmpno'])) !== mb_strtolower(trim((string) $ppmpno))) {
					$fail('This PPMP is already used by a PR, so its PPMP no. cannot be changed.');
				}
				$planned = [];
				$labels = [];
				foreach ((array) $ppmpdtdata as $line) {
					$m = array_pad(explode("x|x", (string) $line), 15, '');
					if (trim($m[0]) === '') { continue; }
					$k = MyPPMPUsageModel::key($m[0], $m[3]);
					$planned[$k] = ($planned[$k] ?? 0) + (int) str_replace(',', '', $m[2]);
					$labels[$k] = trim($m[0]) . (trim($m[3]) !== '' ? ' (' . trim($m[3]) . ')' : '');
				}
				// names of items removed from the grid, from the saved PPMP
				foreach ($this->db->query("SELECT `item_desc`, `size` FROM tbl_ppmp_dt WHERE `ppmp_id` = ?", [$recid])->getResultArray() as $r) {
					$k = MyPPMPUsageModel::key($r['item_desc'], $r['size']);
					$labels[$k] = $labels[$k] ?? (trim($r['item_desc']) . (trim($r['size']) !== '' ? ' (' . trim($r['size']) . ')' : ''));
				}
				foreach ($used as $k => $q) {
					if (($planned[$k] ?? 0) < $q) {
						$name = $labels[$k] ?? 'An item';
						$fail($name . ': the PRs have already taken ' . $q . ', so the quantity cannot go below that.');
					}
				}
			}
		}

		if (empty($recid)) {
			fods_require_access(\Config\AccessCodes::PPMP_ADD, 'Saving');

			$this->db->transStart();

			//INSERTING HD DATA
			$this->db->query(
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

			$status = "PPMP saved successfully";
			$color = "success";
		}else{
			fods_require_access(\Config\AccessCodes::PPMP_UPDATE, 'Updating');
			$before = MyHistoryModel::row('tbl_ppmp_hd', $recid);

			$current = $this->db->query("SELECT `recid`, `is_disapproved` FROM tbl_ppmp_hd WHERE `recid` = ?", [$recid])->getRowArray();
			if (empty($current)) {
				echo "
				<script>
				toastr.error('PPMP not found', 'Oops!', {
						progressBar: true,
						closeButton: true,
						timeOut:2000,
					});
				</script>
				";
				die();
			}

			$this->db->transStart();

			// saving a returned PPMP sends it back for approval
			$resubmit = $current['is_disapproved'] == '1';
			if ($resubmit) {
				$this->db->query("UPDATE tbl_ppmp_hd SET `is_pending` = '1', `is_approved` = '0', `is_disapproved` = '0' WHERE `recid` = ?", [$recid]);
			}

			$this->db->query("
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

			$status = $resubmit ? "PPMP updated and sent back for approval" : "PPMP updated successfully";
			$color = "info";
		}

		// blank or non-numeric amounts are saved as 0
		$num = fn ($v) => is_numeric($v) ? $v : 0;

		//INSERTING DT DATA (each line: description, type, quantity, unit, unit cost, mode,
		//pre-procurement, start, end, delivery from, delivery to, funds, budget, documents, remarks)
		$this->db->query("DELETE FROM tbl_ppmp_dt WHERE `ppmp_id` = ?", [$project_id]);
		for($aa = 0; $aa < count($ppmpdtdata); $aa++){
			$medata = array_pad(explode("x|x",$ppmpdtdata[$aa]), 15, '');
			$item_desc = $medata[0];
			$item_type = $medata[1];
			$quantity = $num($medata[2]);
			$size = $medata[3];
			$unit_cost = $num($medata[4]);
			$mop = $medata[5];
			$is_preproc = $medata[6];
			$proc_start = $medata[7];
			$proc_end = $medata[8];
			$expected_delivery_from = $medata[9];
			$expected_delivery_to = $medata[10];
			$funding_source = $medata[11];
			$estimated_budget = $num($medata[12]);
			$attached_document = $medata[13];
			$remarks = $medata[14];

			$this->db->query("
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

		$this->db->transComplete();

		//HISTORY
		if ($this->db->transStatus()) {
			if (empty($recid)) {
				MyHistoryModel::log('ppmp', $project_id, 'CREATED', '', null, $ppmpno);
			} else {
				MyHistoryModel::log('ppmp', $recid, $resubmit ? 'RESUBMITTED' : 'UPDATED', $resubmit ? 'Corrected and resubmitted for approval' : '', MyHistoryModel::diff($before, MyHistoryModel::row('tbl_ppmp_hd', $recid)), $ppmpno);
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
						window.location.href = 'myppmp?meaction=MAIN'; // Redirect to MAIN view
					}, 1000); // reload after 1 s; the toast still runs its 2.5 s (fods-flash.js carries it over)
			</script>
			";
			exit; // Stop further PHP execution after the toast
		} else {
			// Nothing was written: the PPMP and its items are saved together
			echo "
			<script>
			toastr.error('The PPMP could not be saved. Nothing was changed.', 'Oops!', {
					progressBar: true,
					closeButton: true,
					timeOut:3000,
				});
			</script>
			";
			exit;
		}

	}

	//PPMP - APPROVAL / DISAPPROVAL (single step, like LDDAP-ADA / ADA)
	public function ppmp_approve() { $this->ppmp_decide(true); }
	public function ppmp_disapprove() { $this->ppmp_decide(false); }

	private function ppmp_decide($approve) {
		$recid = $this->request->getPostGet('recid');
		$remarks = trim((string) $this->request->getPostGet('remarks'));

		fods_require_access(\Config\AccessCodes::PPMPAPPROVAL_APPROVE, $approve ? 'Approve' : 'Disapprove');

		// Only a PPMP that is still waiting can be decided
		$hd = $this->db->query("SELECT `recid`, `ppmpno`, `is_approved`, `is_disapproved` FROM tbl_ppmp_hd WHERE `recid` = ?", [$recid])->getRowArray();
		if (empty($hd) || !self::can_decide($hd)) {
			echo "
			<script>
			toastr.error('This PPMP is not pending approval', 'Oops!', {
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
			UPDATE tbl_ppmp_hd
			SET
				`is_pending` = '0',
				`is_approved` = ?,
				`is_disapproved` = ?,
				`approver` = ?,
				`remarks` = ?
			WHERE `recid` = ?
		", [$approve ? 1 : 0, $approve ? 0 : 1, $this->cuser, mb_substr($remarks, 0, 250), $recid]);

		if ($query) {
			MyHistoryModel::log('ppmp', $recid, $approve ? 'APPROVED' : 'DISAPPROVED', $remarks, null, $hd['ppmpno']);
			$status = $approve ? "PPMP approved" : "PPMP disapproved";
			$color = $approve ? "success" : "warning";
			echo "
			<script>
				toastr.{$color}('{$status}', 'Well Done!', {
						progressBar: true,
						closeButton: true,
						timeOut:2000,
					});
				setTimeout(function() {
						window.location.href = 'myppmp?meaction=MAIN';
					}, 1000); // reload after 1 s; the toast still runs its 2.5 s (fods-flash.js carries it over)
			</script>
			";
			exit;
		}
		echo "
		<script>
		toastr.error('The decision could not be saved. Nothing was changed.', 'Oops!', {
				progressBar: true,
				closeButton: true,
				timeOut:3000,
			});
		</script>
		";
		exit;
	}

	//PPMP - SUPPORTING DOCUMENTS (Documents tab). Files are stored under
	// writable/uploads/ppmp/<year>/ and served only through DOC-FILE.
	const DOC_MAX_BYTES = 10485760;   // 10 MB a file
	const DOC_MAX_FILES = 10;         // per upload

	public function attachments($ppmp_id) {
		return array_map(fn ($r) => $r + ['url' => 'myppmp?meaction=DOC-FILE&id=' . $r['recid']], $this->db->query("
			SELECT a.`recid`, a.`orig_name`, a.`mime_type`, a.`file_size`, a.`is_image`, a.`added_by`, a.`added_at`,
				COALESCE(NULLIF(u.`full_name`, ''), a.`added_by`) AS added_by_name
			FROM tbl_ppmp_attachment a
			LEFT JOIN myua_user u ON u.`username` = a.`added_by`
			WHERE a.`ppmp_id` = ?
			ORDER BY a.`recid` ASC
		", [$ppmp_id])->getResultArray());
	}

	// Adding and removing documents: whoever may add or update PPMPs
	public static function can_attach() {
		return fods_has_any_access([\Config\AccessCodes::PPMP_ADD, \Config\AccessCodes::PPMP_UPDATE]);
	}

	// Adds the 'attachments[]' files to a saved PPMP: ['attachments' => list] or ['error' => ...]
	public function attachment_add($recid) {
		if (!self::can_attach()) {
			return ['error' => 'You do not have permission to add documents to PPMPs.'];
		}
		$hd = $this->db->query("SELECT `recid`, `ppmpno` FROM tbl_ppmp_hd WHERE `recid` = ?", [$recid])->getRowArray();
		if (empty($hd)) {
			return ['error' => 'Save the PPMP first, then add its documents.'];
		}
		$uploads = model('App\Models\MyUploadsModel');
		[$files, $error] = $uploads->upload_files(false, self::DOC_MAX_BYTES, 0, self::DOC_MAX_FILES);
		if ($error !== '') {
			return ['error' => $error];
		}
		if (empty($files)) {
			return ['error' => 'Pick at least one file.'];
		}
		$names = [];
		foreach ($files as $up) {
			$row = $uploads->upload_store('ppmp', $up);
			$this->db->query("
				INSERT INTO tbl_ppmp_attachment (`ppmp_id`, `file_name`, `orig_name`, `mime_type`, `file_size`, `is_image`, `added_by`, `added_at`)
				VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
			", [$hd['recid'], $row['file_name'], $row['orig_name'], $row['mime_type'], $row['file_size'], $row['is_image'], $this->cuser]);
			$names[] = $row['orig_name'];
		}
		MyHistoryModel::log('ppmp', $hd['recid'], 'DOCUMENT_ADDED', implode(', ', $names), null, $hd['ppmpno']);
		return ['attachments' => $this->attachments($hd['recid'])];
	}

	// Removes one document, its stored file too: ['attachments' => list] or ['error' => ...]
	public function attachment_remove($id) {
		if (!self::can_attach()) {
			return ['error' => 'You do not have permission to remove PPMP documents.'];
		}
		$doc = $this->db->query("
			SELECT a.*, h.`ppmpno` FROM tbl_ppmp_attachment a JOIN tbl_ppmp_hd h ON h.`recid` = a.`ppmp_id` WHERE a.`recid` = ?
		", [$id])->getRowArray();
		if (empty($doc)) {
			return ['error' => 'That document was already removed.'];
		}
		$this->db->query("DELETE FROM tbl_ppmp_attachment WHERE `recid` = ?", [$doc['recid']]);
		$path = model('App\Models\MyUploadsModel')->upload_path('ppmp', $doc['file_name']);
		if ($path !== null) {
			@unlink($path);
		}
		MyHistoryModel::log('ppmp', $doc['ppmp_id'], 'DOCUMENT_REMOVED', $doc['orig_name'], null, $doc['ppmpno']);
		return ['attachments' => $this->attachments($doc['ppmp_id'])];
	}

	// One document with its path on disk (null when it is gone)
	public function attachment_file($id) {
		$doc = $this->db->query("SELECT * FROM tbl_ppmp_attachment WHERE `recid` = ?", [$id])->getRowArray();
		if (empty($doc)) {
			return null;
		}
		$path = model('App\Models\MyUploadsModel')->upload_path('ppmp', $doc['file_name']);
		return $path === null ? null : $doc + ['path' => $path];
	}


} //end main class
?>
