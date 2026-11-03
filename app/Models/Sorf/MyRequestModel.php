<?php
namespace App\Models\Sorf;
use CodeIgniter\Model;
use Config\Sorf\Permissions;
use Config\Sorf\RequestForms;

class MyRequestModel extends Model
{

	protected $db;
	protected $session;
	protected $request;
	protected $cuser;

	// tbl_request_dt text columns => longest value kept
	public const DT_TEXT = [
		'subject' => 255, 'staff_name' => 150, 'position' => 150, 'division_agency' => 150,
		'duration' => 150, 'purpose' => 500, 'source_of_fund' => 150, 'remarks' => 255,
	];
	public const DT_MONEY = ['amount', 'total_amount', 'registration_fee'];
	public const MAX_ROWS = 50;

	public function __construct(){
		parent::__construct();
		$this->session = session();
		$this->request = \Config\Services::request();
		$this->db = \Config\Database::connect('sorf');
		$this->cuser = $this->session->get('__xsys_myuserzicas__');
	}

	//LOOKUP - divisions and their sections (registration + request form)
	public function org_lookups() {
		return [
			'divisions' => $this->db->query("
				SELECT `division_id`, `division_name` FROM tbl_division WHERE `is_active` = 1 ORDER BY `recid` ASC
			")->getResultArray(),
			'sections' => $this->db->query("
				SELECT `division_id`, `section_id`, `section_name` FROM tbl_section WHERE `is_active` = 1 ORDER BY `recid` ASC
			")->getResultArray(),
		];
	}

	//LOOKUP - everything the request form offers
	public function lookups() {
		return $this->org_lookups() + [
			'documents' => $this->db->query("
				SELECT `document_id`, `document_type` FROM tbl_document_type WHERE `is_active` = 1 ORDER BY `document_id` ASC
			")->getResultArray(),
			'employees' => $this->db->query("
				SELECT `full_name`, `position` FROM tbl_employee WHERE `is_active` = 1 ORDER BY `full_name` ASC
			")->getResultArray(),
			// "Noted by" must be someone who can sign in and sign: an active account
			// whose role may approve requests, and not yourself
			'approvers' => $this->db->query("
				SELECT u.`full_name`, u.`position` FROM myua_user u
				WHERE u.`is_active` = 1 AND u.`username` <> ? AND {$this->approver_sql()}
				ORDER BY u.`full_name` ASC
			", [(string) $this->cuser, Permissions::REQUEST_APPROVE])->getResultArray(),
		];
	}

	// SQL condition on myua_user u: the user's role is switched on and may approve
	// (bind Permissions::REQUEST_APPROVE in its place)
	private function approver_sql() {
		return "EXISTS (
			SELECT 1 FROM tbl_roles r JOIN tbl_role_permission p ON p.`role_id` = r.`recid`
			WHERE r.`recid` = u.`role_id` AND r.`is_active` = 1 AND p.`perm_code` = ?
		)";
	}

	// The account whose name matches "Noted by" (null when nobody who may approve has that name)
	public function approver_find(string $name) {
		return $this->db->query("
			SELECT u.`username`, u.`full_name` FROM myua_user u
			WHERE u.`full_name` = ? AND u.`is_active` = 1 AND u.`username` <> ? AND {$this->approver_sql()}
			ORDER BY u.`recid` ASC LIMIT 1
		", [$name, (string) $this->cuser, Permissions::REQUEST_APPROVE])->getRowArray();
	}

	public function section_exists($divisionId, $sectionId) {
		return (bool) $this->db->query("
			SELECT `recid` FROM tbl_section WHERE `division_id` = ? AND `section_id` = ? AND `is_active` = 1 LIMIT 1
		", [(string) $divisionId, (string) $sectionId])->getRowArray();
	}

	//REQUESTS - LIST by point of view: 'mine' = filed by you, 'approval' = naming
	// you as "Noted by", 'hr' = submitted to HR. `unread` counts the messages
	// (and status notes) from the others you have not opened yet.
	public function request_list(string $mode) {
		if ($mode === 'hr') {
			$where = drf_can(Permissions::HR_VIEW) ? "a.`status` IN ('" . implode("','", RequestForms::HR_STATUSES) . "')" : '1 = 0';
			$binds = [];
		} else {
			$where = $mode === 'approval' ? 'a.`noted_by_user` = ?' : 'a.`added_by` = ?';
			$binds = [$this->cuser];
		}
		// a signatory is only told about log entries, not the messages they cannot read
		$chat = $mode === 'approval' ? 0 : 1;
		$rows = $this->db->query("
			SELECT a.`recid`, a.`request_no`, a.`document_id`, a.`full_name`, a.`division_id`, a.`section_id`,
				a.`subject`, a.`status`, a.`request_date`, a.`added_at`, a.`noted_by`,
				COALESCE(d.`document_type`, a.`document_id`) document_type,
				-- the Special Order can be printed from the list once HR has released it
				COALESCE(x.`doc_status` = ?, 0) so_released,
				(
					SELECT COUNT(*) FROM tbl_request_msg m
					WHERE m.`request_id` = a.`recid` AND m.`added_by` <> ? AND (m.`is_system` = 1 OR {$chat} = 1)
					AND m.`recid` > COALESCE((SELECT s.`last_read_id` FROM tbl_request_seen s WHERE s.`request_id` = a.`recid` AND s.`username` = ?), 0)
				) unread
			FROM tbl_request_hd a
			LEFT JOIN tbl_document_type d ON d.`document_id` = a.`document_id`
			LEFT JOIN tbl_request_doc x ON x.`request_id` = a.`recid`
			WHERE {$where}
			ORDER BY a.`recid` DESC
		", array_merge([\Config\Sorf\DocumentTemplates::STATUS_RELEASED, $this->cuser, $this->cuser], $binds))->getResultArray();

		foreach ($rows as &$r) {
			$r['recid'] = (int) $r['recid'];
			$r['unread'] = (int) $r['unread'];
			$r['so_released'] = (int) $r['so_released'] === 1;
		}
		unset($r);
		return $rows;
	}

	//REQUESTS - ONE header row. null unless you filed it, you are its "Noted by"
	// signatory, or your role sees HR's requests and it has been submitted to HR.
	public function request_get($recid) {
		$hd = $this->db->query("
			SELECT a.*,
				COALESCE(d.`document_type`, a.`document_id`) document_type,
				COALESCE(v.`division_name`, a.`division_id`) division_name,
				COALESCE(s.`section_name`, a.`section_id`) section_name
			FROM tbl_request_hd a
			LEFT JOIN tbl_document_type d ON d.`document_id` = a.`document_id`
			LEFT JOIN tbl_division v ON v.`division_id` = a.`division_id`
			LEFT JOIN tbl_section s ON s.`division_id` = a.`division_id` AND s.`section_id` = a.`section_id`
			WHERE a.`recid` = ?
		", [(int) $recid])->getRowArray();

		if (!$hd) {
			return null;
		}
		$mine = $hd['added_by'] === $this->cuser || ($hd['noted_by_user'] !== '' && $hd['noted_by_user'] === $this->cuser);
		if (!$mine && !(drf_can(Permissions::HR_VIEW) && $this->request_at_hr($hd))) {
			return null;
		}
		$hd['recid'] = (int) $hd['recid'];
		return $hd;
	}

	// Both signatures are in: the request is HR's to decide
	public function request_at_hr(array $hd) {
		return in_array($hd['status'], RequestForms::HR_STATUSES, true);
	}

	// The owner may change a request until HR approves or denies it
	public function request_is_editable(array $hd) {
		return $hd['added_by'] === $this->cuser && drf_can(Permissions::REQUEST_FILE) && !in_array($hd['status'], RequestForms::LOCKED, true);
	}

	// The conversation is between the requester and HR. The "Noted by"
	// signatory only follows the request's log (signed, returned, status changes).
	public function request_can_chat(array $hd) {
		return $hd['added_by'] === $this->cuser || (drf_can(Permissions::HR_VIEW) && $this->request_at_hr($hd));
	}

	// The "Noted by" signatory acts on a request while it waits for approval
	public function request_can_approve(array $hd) {
		return $hd['noted_by_user'] !== '' && $hd['noted_by_user'] === $this->cuser && $hd['status'] === RequestForms::STATUS_FOR_APPROVAL
			&& drf_can(Permissions::REQUEST_APPROVE);
	}

	//REQUESTS - ONE request with its lines (and the travel order part)
	public function request_load($recid) {
		$hd = $this->request_get($recid);
		if ($hd === null) {
			return null;
		}

		$rows = $this->db->query("
			SELECT `subject`, `staff_name`, `position`, `division_agency`, `duration`, `purpose`,
				`amount`, `total_amount`, `registration_fee`, `source_of_fund`, `remarks`
			FROM tbl_request_dt WHERE `request_id` = ? ORDER BY `recid` ASC
		", [$hd['recid']])->getResultArray();

		$lto = $this->db->query("
			SELECT `destination`, `travel_dates`, `purpose`, `is_general_fund`, `project_fund`, `other_fund`, `remarks`
			FROM tbl_request_lto WHERE `request_id` = ?
		", [$hd['recid']])->getRowArray();

		$expenses = [];
		foreach ($this->db->query("
			SELECT `expense_code`, `general_fund`, `project_fund`, `other_fund`
			FROM tbl_request_lto_expense WHERE `request_id` = ?
		", [$hd['recid']])->getResultArray() as $e) {
			$expenses[$e['expense_code']] = $e;
		}

		return ['hd' => $hd, 'rows' => $rows, 'lto' => $lto ?: null, 'expenses' => $expenses];
	}

	//REQUESTS - SAVE (new, or the owner changing one HR has not decided yet).
	// The requester signs it and it goes to the "Noted by" signatory. Saving a
	// change clears that signatory's signature: they sign what they read.
	public function request_save() {
		$signature = (string) $this->request->getPost('signature');
		$recid = (int) $this->request->getPost('recid');
		$document_id = (string) $this->request->getPost('document_id');
		$division_id = (string) $this->request->getPost('division_id');
		$section_id = (string) $this->request->getPost('section_id');
		$request_date = (string) $this->request->getPost('request_date');
		$subject = trim((string) $this->request->getPost('subject'));
		$noted_by = trim((string) $this->request->getPost('noted_by'));
		$rowsdata = $this->request->getPost('rows');
		$ltodata = (array) $this->request->getPost('lto');
		$expensedata = (array) $this->request->getPost('expenses');

		if (!drf_can(Permissions::REQUEST_FILE)) {
			return ['error' => 'Your role cannot file requests.'];
		}
		$user = drf_current_user();
		if ($user['username'] === '') {
			return ['error' => 'Your session has ended. Please sign in again.'];
		}

		$old = null;
		if ($recid > 0) {
			$old = $this->request_get($recid);
			if ($old === null || !$this->request_is_editable($old)) {
				return ['error' => 'This request can no longer be changed.'];
			}
			// the document type of a filed request stays as it is
			$document_id = $old['document_id'];
		}

		$config = config(\Config\Sorf\RequestForms::class);
		$form = $config->forms[$document_id] ?? null;
		$active = $this->db->query("SELECT `recid` FROM tbl_document_type WHERE `document_id` = ? AND `is_active` = 1", [$document_id])->getRowArray();
		if ($form === null || (!$active && $old === null)) {
			return ['error' => 'Select the type of document you are requesting.'];
		}
		if (!$this->section_exists($division_id, $section_id)) {
			return ['error' => 'Select your division and section.'];
		}
		$date = \DateTime::createFromFormat('Y-m-d', $request_date);
		if (!$date || $date->format('Y-m-d') !== $request_date) {
			return ['error' => 'Date of request is required.'];
		}
		if ($noted_by === '') {
			return ['error' => 'Choose who will note (sign) this request.'];
		}
		$approver = $this->approver_find($noted_by);
		// the signatory already on this request may have changed their name in My Account since
		if ($approver === null && $old !== null && $old['noted_by_user'] !== '' && $old['noted_by_user'] !== $this->cuser && strcasecmp($noted_by, $old['noted_by']) === 0) {
			$approver = $this->db->query("
				SELECT u.`username`, u.`full_name` FROM myua_user u WHERE u.`username` = ? AND u.`is_active` = 1 AND {$this->approver_sql()} LIMIT 1
			", [$old['noted_by_user'], Permissions::REQUEST_APPROVE])->getRowArray();
		}
		if ($approver === null) {
			return ['error' => "\"{$noted_by}\" has no account yet. Whoever notes the request must be registered here to sign it."];
		}
		$noted_by = $approver['full_name'];

		[$rows, $rowError] = $this->clean_rows($form, $rowsdata);
		if ($rowError !== '') {
			return ['error' => $rowError];
		}

		$lto = null;
		$expenses = [];
		if ($form['layout'] === 'lto') {
			[$lto, $expenses] = $this->clean_lto($ltodata, $expensedata);
			if ($lto['destination'] === '' || $lto['travel_dates'] === '' || $lto['purpose'] === '') {
				return ['error' => 'Destination, inclusive dates and purpose of the travel are required.'];
			}
			// the travel order form has no subject line of its own
			$subject = 'Travel to ' . preg_replace('/\s+/', ' ', $lto['destination']);
		} elseif ($subject === '') {
			return ['error' => 'Subject is required.'];
		}
		$subject = mb_substr($subject, 0, 255);

		//SIGNATURE - a new one, or the one already on the request
		$store = new \App\Libraries\Sorf\SignatureStore();
		$oldSign = $old['requester_sign'] ?? '';
		$newSign = '';
		if ($signature !== '') {
			[$newSign, $signError] = $store->store($signature);
			if ($signError !== '') {
				return ['error' => $signError];
			}
		} elseif ($oldSign === '' || $store->path($oldSign) === null) {
			return ['error' => 'Sign the request before submitting it.'];
		}

		$this->db->transStart();

		if ($old === null) {
			//INSERTING HD DATA
			$request_no = $this->next_request_no();
			$this->db->query("
				INSERT INTO tbl_request_hd (
					`request_no`, `document_id`, `full_name`, `position`, `division_id`, `section_id`,
					`request_date`, `subject`, `noted_by`, `noted_by_user`, `requester_sign`, `requester_signed_at`, `status`, `added_by`
				)
				VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?, ?)
			", [
				$request_no, $document_id, $user['full_name'], $user['position'], $division_id, $section_id,
				$request_date, $subject, $noted_by, $approver['username'], $newSign, RequestForms::STATUS_FOR_APPROVAL, $this->cuser,
			]);
			$recid = (int) $this->db->insertID();
			$this->note($recid, "{$user['full_name']} signed the request and sent it to {$noted_by} for approval.");
		} else {
			$this->db->query("
				UPDATE tbl_request_hd SET
					`division_id` = ?, `section_id` = ?, `request_date` = ?, `subject` = ?,
					`noted_by` = ?, `noted_by_user` = ?, `noted_sign` = '', `noted_signed_at` = NULL, `noted_remarks` = '',
					`status` = ?, `updated_at` = NOW()
				WHERE `recid` = ?
			", [$division_id, $section_id, $request_date, $subject, $noted_by, $approver['username'], RequestForms::STATUS_FOR_APPROVAL, $recid]);
			if ($newSign !== '') {
				$this->db->query("UPDATE tbl_request_hd SET `requester_sign` = ?, `requester_signed_at` = NOW() WHERE `recid` = ?", [$newSign, $recid]);
			}

			$this->db->query("DELETE FROM tbl_request_dt WHERE `request_id` = ?", [$recid]);
			$this->db->query("DELETE FROM tbl_request_lto WHERE `request_id` = ?", [$recid]);
			$this->db->query("DELETE FROM tbl_request_lto_expense WHERE `request_id` = ?", [$recid]);
			$this->note($recid, "{$user['full_name']} updated the request and sent it to {$noted_by} for approval.");
		}

		//INSERTING DT DATA
		foreach ($rows as $row) {
			$this->db->query("
				INSERT INTO tbl_request_dt (
					`request_id`, `subject`, `staff_name`, `position`, `division_agency`, `duration`, `purpose`,
					`amount`, `total_amount`, `registration_fee`, `source_of_fund`, `remarks`, `added_by`
				)
				VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
			", [
				$recid, $row['subject'], $row['staff_name'], $row['position'], $row['division_agency'], $row['duration'], $row['purpose'],
				$row['amount'], $row['total_amount'], $row['registration_fee'], $row['source_of_fund'], $row['remarks'], $this->cuser,
			]);
		}

		//INSERTING LOCAL TRAVEL ORDER DATA
		if ($lto !== null) {
			$this->db->query("
				INSERT INTO tbl_request_lto (
					`request_id`, `destination`, `travel_dates`, `purpose`, `is_general_fund`, `project_fund`, `other_fund`, `remarks`, `added_by`
				)
				VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
			", [
				$recid, $lto['destination'], $lto['travel_dates'], $lto['purpose'], $lto['is_general_fund'],
				$lto['project_fund'], $lto['other_fund'], $lto['remarks'], $this->cuser,
			]);
			foreach ($expenses as $code => $line) {
				$this->db->query("
					INSERT INTO tbl_request_lto_expense (`request_id`, `expense_code`, `general_fund`, `project_fund`, `other_fund`, `added_by`)
					VALUES (?, ?, ?, ?, ?, ?)
				", [$recid, $code, $line['general_fund'], $line['project_fund'], $line['other_fund'], $this->cuser]);
			}
		}

		$this->db->transComplete();

		if ($this->db->transStatus() === false) {
			$store->delete($newSign);
			return ['error' => 'Your request was not saved. Please try again.'];
		}
		// the next request form starts on the same division and section
		$this->db->query("UPDATE myua_user SET `division` = ?, `section` = ? WHERE `username` = ?", [$division_id, $section_id, $this->cuser]);
		// signature files the request no longer uses
		if ($old !== null) {
			$store->delete($old['noted_sign']);
			if ($newSign !== '') {
				$store->delete($oldSign);
			}
		}
		return ['ok' => true, 'recid' => $recid, 'message' => "Request sent to {$noted_by} for approval."];
	}

	//APPROVAL - the "Noted by" signatory returns the request, or signs and
	// approves it - which submits it to HR
	public function approval_save() {
		$recid = (int) $this->request->getPost('recid');
		$action = (string) $this->request->getPost('action');
		$remarks = trim((string) $this->request->getPost('noted_remarks'));
		$signature = (string) $this->request->getPost('signature');

		$hd = $this->request_get($recid);
		if ($hd === null || $hd['noted_by_user'] !== $this->cuser) {
			return ['error' => 'This request is not assigned to you.'];
		}
		if (!$this->request_can_approve($hd)) {
			return ['error' => 'This request is no longer waiting for your approval.'];
		}
		if (!in_array($action, ['approve', 'return'], true)) {
			return ['error' => 'Choose to approve or return the request.'];
		}
		if (mb_strlen($remarks) > 500) {
			return ['error' => 'Remarks are too long (500 characters max).'];
		}
		$me = drf_current_user()['full_name'];

		if ($action === 'return') {
			if ($remarks === '') {
				return ['error' => 'Tell the requester what needs to change.'];
			}
			$this->db->transStart();
			$this->db->query("
				UPDATE tbl_request_hd SET `status` = ?, `noted_remarks` = ?, `updated_at` = NOW() WHERE `recid` = ?
			", [RequestForms::STATUS_RETURNED, $remarks, $recid]);
			$this->note($recid, "{$me} returned the request to the requester.\n{$remarks}");
			$this->db->transComplete();

			if ($this->db->transStatus() === false) {
				return ['error' => 'The request was not updated.'];
			}
			return ['ok' => true, 'status' => RequestForms::STATUS_RETURNED, 'message' => 'Request returned to the requester.'];
		}

		if ($signature === '') {
			return ['error' => 'Sign the request to approve it.'];
		}
		$store = new \App\Libraries\Sorf\SignatureStore();
		[$sign, $signError] = $store->store($signature);
		if ($signError !== '') {
			return ['error' => $signError];
		}

		$this->db->transStart();
		$this->db->query("
			UPDATE tbl_request_hd SET
				`status` = ?, `noted_sign` = ?, `noted_signed_at` = NOW(), `noted_remarks` = ?, `submitted_at` = NOW(), `updated_at` = NOW()
			WHERE `recid` = ?
		", [RequestForms::STATUS_PENDING, $sign, $remarks, $recid]);
		$this->note($recid, "{$me} signed and approved the request. It has been submitted to HR." . ($remarks !== '' ? "\n{$remarks}" : ''));
		$this->db->transComplete();

		if ($this->db->transStatus() === false) {
			$store->delete($sign);
			return ['error' => 'The request was not updated.'];
		}
		$store->delete($hd['noted_sign']);
		return ['ok' => true, 'status' => RequestForms::STATUS_PENDING, 'message' => 'Request signed, approved and submitted to HR.'];
	}

	// Posted travel order part -> [lto row, expense lines by code], trimmed to the column sizes
	private function clean_lto(array $ltodata, array $expensedata) {
		$lto = [
			'destination' => mb_substr(trim((string) ($ltodata['destination'] ?? '')), 0, 500),
			'travel_dates' => mb_substr(trim((string) ($ltodata['travel_dates'] ?? '')), 0, 500),
			'purpose' => mb_substr(trim((string) ($ltodata['purpose'] ?? '')), 0, 1000),
			'is_general_fund' => empty($ltodata['is_general_fund']) ? 0 : 1,
			'project_fund' => mb_substr(trim((string) ($ltodata['project_fund'] ?? '')), 0, 150),
			'other_fund' => mb_substr(trim((string) ($ltodata['other_fund'] ?? '')), 0, 150),
			'remarks' => mb_substr(trim((string) ($ltodata['remarks'] ?? '')), 0, 500),
		];
		$expenses = [];
		foreach (config(\Config\Sorf\RequestForms::class)->ltoExpenseCodes() as $code) {
			$e = (array) ($expensedata[$code] ?? []);
			$line = [
				'general_fund' => mb_substr(trim((string) ($e['general_fund'] ?? '')), 0, 50),
				'project_fund' => mb_substr(trim((string) ($e['project_fund'] ?? '')), 0, 50),
				'other_fund' => mb_substr(trim((string) ($e['other_fund'] ?? '')), 0, 50),
			];
			if (implode('', $line) !== '') {
				$expenses[$code] = $line;
			}
		}
		return [$lto, $expenses];
	}

	//REQUESTS - PREVIEW: the printed form from what is on the request form right
	// now, half-filled or not. Nothing is saved or checked strictly here; saving
	// still runs every check. Returns ['form', 'record', 'kept_sign'] or ['error'].
	public function request_preview() {
		if (!drf_can(Permissions::REQUEST_FILE)) {
			return ['error' => 'Your role cannot file requests.'];
		}
		$recid = (int) $this->request->getPost('recid');
		$document_id = (string) $this->request->getPost('document_id');
		$old = null;
		if ($recid > 0) {
			$old = $this->request_get($recid);
			if ($old === null || !$this->request_is_editable($old)) {
				return ['error' => 'This request can no longer be changed.'];
			}
			$document_id = $old['document_id'];
		}
		$config = config(\Config\Sorf\RequestForms::class);
		$form = $config->forms[$document_id] ?? null;
		if ($form === null) {
			return ['error' => 'Select the type of document you are requesting.'];
		}

		$date = (string) $this->request->getPost('request_date');
		[$rows] = $this->clean_rows($form, $this->request->getPost('rows'), true);
		$lto = null;
		$expenses = [];
		$subject = mb_substr(trim((string) $this->request->getPost('subject')), 0, 255);
		if ($form['layout'] === 'lto') {
			[$lto, $expenses] = $this->clean_lto((array) $this->request->getPost('lto'), (array) $this->request->getPost('expenses'));
			$subject = '';
		}

		return [
			'form' => $form,
			'record' => [
				'hd' => [
					'request_no' => $old['request_no'] ?? 'New request',
					'request_date' => preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) ? $date : '',
					'subject' => $subject,
					'full_name' => $old['full_name'] ?? drf_current_user()['full_name'],
					'noted_by' => mb_substr(trim((string) $this->request->getPost('noted_by')), 0, 100),
					'hr_remarks' => $old['hr_remarks'] ?? '',
					'so_no' => $old['so_no'] ?? '',
				],
				'rows' => $rows,
				'lto' => $lto,
				'expenses' => $expenses,
			],
			'kept_sign' => $old['requester_sign'] ?? '',
		];
	}

	// Posted lines -> rows ready to insert. Blank lines are dropped; only the
	// columns the document type uses are kept. Returns [rows, error].
	// $preview: never fails - an amount that is not a number yet is left blank.
	private function clean_rows(array $form, $rowsdata, bool $preview = false) {
		if (!is_array($rowsdata)) {
			$rowsdata = [];
		}
		$keys = array_column($form['fields'], 'key');
		$labels = array_column($form['fields'], 'label', 'key');

		$rows = [];
		$line = 0;
		foreach ($rowsdata as $posted) {
			$line++;
			if (!is_array($posted)) {
				continue;
			}
			$row = array_fill_keys(array_keys(self::DT_TEXT), '') + array_fill_keys(self::DT_MONEY, null);
			$filled = false;
			foreach ($keys as $key) {
				$value = trim((string) ($posted[$key] ?? ''));
				if ($value === '') {
					continue;
				}
				$filled = true;
				if (isset(self::DT_TEXT[$key])) {
					$row[$key] = mb_substr($value, 0, self::DT_TEXT[$key]);
					continue;
				}
				$number = str_replace([',', ' '], '', $value);
				if (!is_numeric($number) || (float) $number < 0 || (float) $number > 9999999999999) {
					if ($preview) {
						continue;
					}
					return [[], "{$labels[$key]} on line {$line} is not a valid amount."];
				}
				$row[$key] = round((float) $number, 2);
			}
			if ($filled) {
				if (!$preview && in_array('staff_name', $keys, true) && $row['staff_name'] === '') {
					return [[], "{$labels['staff_name']} is required on line {$line}."];
				}
				$rows[] = $row;
			}
		}

		if ($preview) {
			return [array_slice($rows, 0, self::MAX_ROWS), ''];
		}
		if (empty($rows)) {
			return [[], 'Add at least one line to the request details.'];
		}
		if (count($rows) > self::MAX_ROWS) {
			return [[], 'A request can have up to ' . self::MAX_ROWS . ' lines.'];
		}
		return [$rows, ''];
	}

	// SOR-<year>-<series>; the counter row stays locked until the save commits
	private function next_request_no() {
		$year = date('Y');
		$this->db->query("
			INSERT INTO myctr_request (`CTR_YEAR`, `CTRL_NO01`) VALUES (?, 1)
			ON DUPLICATE KEY UPDATE `CTRL_NO01` = `CTRL_NO01` + 1
		", [$year]);
		$row = $this->db->query("SELECT `CTRL_NO01` FROM myctr_request WHERE `CTR_YEAR` = ?", [$year])->getRowArray();
		return 'SOR-' . $year . '-' . str_pad((string) $row['CTRL_NO01'], 4, '0', STR_PAD_LEFT);
	}

	//HR - UPDATE STATUS / remarks (printed under "Remarks: (by HRMS)") / S.O. number
	public function status_save() {
		$recid = (int) $this->request->getPost('recid');
		$status = (string) $this->request->getPost('status');
		$hr_remarks = trim((string) $this->request->getPost('hr_remarks'));
		$so_no = trim((string) $this->request->getPost('so_no'));

		if (!drf_can(Permissions::HR_STATUS)) {
			return ['error' => 'Your role cannot update requests.'];
		}
		$hd = $this->request_get($recid);
		if ($hd === null || !$this->request_at_hr($hd)) {
			return ['error' => 'Request not found.'];
		}
		if (!in_array($status, RequestForms::HR_STATUSES, true)) {
			return ['error' => 'Choose a status.'];
		}
		if (mb_strlen($hr_remarks) > 500) {
			return ['error' => 'Remarks are too long (500 characters max).'];
		}
		if (mb_strlen($so_no) > 50) {
			return ['error' => 'S.O. number is too long (50 characters max).'];
		}
		if ($hr_remarks === '' && $status === RequestForms::STATUS_COMPLIANCE) {
			return ['error' => 'Tell the requester which requirements are needed.'];
		}
		if ($hr_remarks === '' && $status === RequestForms::STATUS_DENIED) {
			return ['error' => 'Tell the requester why the request is denied.'];
		}
		if ($status === $hd['status'] && $hr_remarks === $hd['hr_remarks'] && $so_no === $hd['so_no']) {
			return ['ok' => true, 'status' => $status, 'message' => 'Nothing to update.'];
		}

		$this->db->transStart();
		$this->db->query("
			UPDATE tbl_request_hd SET `status` = ?, `hr_remarks` = ?, `so_no` = ?, `handled_by` = ?, `updated_at` = NOW()
			WHERE `recid` = ?
		", [$status, $hr_remarks, $so_no, $this->cuser, $recid]);

		// the requester sees the change as a note in the conversation
		if ($status !== $hd['status']) {
			$text = "Status changed to {$status}.";
			if ($hr_remarks !== '') {
				$text .= "\n" . $hr_remarks;
			}
		} elseif ($hr_remarks !== $hd['hr_remarks'] && $hr_remarks !== '') {
			$text = "HR remarks updated.\n" . $hr_remarks;
		} else {
			$text = 'HR updated the request.';
		}
		if ($so_no !== '' && $so_no !== $hd['so_no']) {
			$text .= "\nS.O. No.: " . $so_no;
		}
		$this->note($recid, $text);
		$this->db->transComplete();

		if ($this->db->transStatus() === false) {
			return ['error' => 'The request was not updated.'];
		}
		return ['ok' => true, 'status' => $status, 'message' => 'Request updated.'];
	}

	// A note in the request's conversation (status changes, edits, the Special Order)
	public function note($requestId, $text) {
		$this->db->query("
			INSERT INTO tbl_request_msg (`request_id`, `message`, `is_system`, `added_by`) VALUES (?, ?, 1, ?)
		", [(int) $requestId, $text, $this->cuser]);
	}

} //end main class
