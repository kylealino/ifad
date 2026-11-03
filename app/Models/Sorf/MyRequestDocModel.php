<?php
namespace App\Models\Sorf;
use CodeIgniter\Model;
use Config\Sorf\DocumentTemplates;

class MyRequestDocModel extends Model
{

	protected $db;
	protected $session;
	protected $request;
	protected $cuser;

	public function __construct(){
		parent::__construct();
		$this->session = session();
		$this->request = \Config\Services::request();
		$this->db = \Config\Database::connect('sorf');
		$this->cuser = $this->session->get('__xsys_myuserzicas__');
	}

	//DOCUMENT - the Special Order row of a request (null when HR has not started one)
	public function doc_get($requestId) {
		$doc = $this->db->query("
			SELECT x.*, COALESCE(u.`full_name`, x.`released_by`) released_name
			FROM tbl_request_doc x
			LEFT JOIN myua_user u ON u.`username` = x.`released_by`
			WHERE x.`request_id` = ?
		", [(int) $requestId])->getRowArray();
		return $doc ?: null;
	}

	//DOCUMENT - for whoever is looking: whoever prepares Special Orders sees the
	// draft too; everyone else only once it is released. null otherwise.
	// $hd comes from MyRequestModel::request_get(), which already checked the request is theirs.
	public function doc_view(array $hd) {
		$doc = $this->doc_get($hd['recid']);
		$drafts = drf_can(\Config\Sorf\Permissions::HR_DOCUMENT) && model('App\Models\Sorf\MyRequestModel')->request_at_hr($hd);
		if ($doc === null || (!$drafts && $doc['doc_status'] !== DocumentTemplates::STATUS_RELEASED)) {
			return null;
		}
		return $doc;
	}

	//DOCUMENT - the official form of $code filled in from the request
	public function template_render(string $code, array $record) {
		$config = config(\Config\Sorf\DocumentTemplates::class);
		$template = $config->templates[$code];

		return \App\Libraries\Sorf\DocHtml::clean(view('sorf/docs/templates/' . $template['view'], [
			'hd' => $record['hd'],
			'rows' => $record['rows'],
			'lto' => $record['lto'],
			'expenses' => $record['expenses'],
			'template' => $template,
			'year' => date('Y', strtotime($record['hd']['request_date'])),
			'signatoryName' => $config->signatoryName,
			'signatoryPosition' => $config->signatoryPosition,
		], ['saveData' => false]));
	}

	// HR works on a request once it has reached HR; returns [record, error]
	private function hr_record($requestId) {
		if (!drf_can(\Config\Sorf\Permissions::HR_DOCUMENT)) {
			return [null, 'Your role cannot prepare the Special Order.'];
		}
		$requests = model('App\Models\Sorf\MyRequestModel');
		$record = $requests->request_load($requestId);
		if ($record === null || !$requests->request_at_hr($record['hd'])) {
			return [null, 'Request not found.'];
		}
		return [$record, ''];
	}

	//DOCUMENT - START from a template (also "start over": replaces the draft)
	public function doc_start() {
		$requestId = (int) $this->request->getPost('recid');
		$code = (string) $this->request->getPost('template_code');

		[$record, $error] = $this->hr_record($requestId);
		if ($error !== '') {
			return ['error' => $error];
		}
		if (!isset(config(\Config\Sorf\DocumentTemplates::class)->templates[$code])) {
			return ['error' => 'Choose a template.'];
		}
		$doc = $this->doc_get($requestId);
		if ($doc !== null && $doc['doc_status'] === DocumentTemplates::STATUS_RELEASED) {
			return ['error' => 'This draft is shared with the requester. Withdraw it first to start over.'];
		}

		$this->db->query("
			INSERT INTO tbl_request_doc (`request_id`, `template_code`, `doc_html`, `doc_status`, `added_by`)
			VALUES (?, ?, ?, ?, ?)
			ON DUPLICATE KEY UPDATE `template_code` = VALUES(`template_code`), `doc_html` = VALUES(`doc_html`), `updated_at` = NOW()
		", [$requestId, $code, $this->template_render($code, $record), DocumentTemplates::STATUS_DRAFT, $this->cuser]);

		return ['ok' => true, 'message' => 'Template loaded.'];
	}

	//DOCUMENT - SAVE the edited page (draft only)
	public function doc_save() {
		$requestId = (int) $this->request->getPost('recid');
		$html = (string) $this->request->getPost('doc_html');

		[$record, $error] = $this->hr_record($requestId);
		if ($error !== '') {
			return ['error' => $error];
		}
		$doc = $this->doc_get($requestId);
		if ($doc === null) {
			return ['error' => 'Choose a template first.'];
		}
		if ($doc['doc_status'] === DocumentTemplates::STATUS_RELEASED) {
			return ['error' => 'This draft is shared with the requester. Withdraw it to edit it.'];
		}
		if (strlen($html) > \App\Libraries\Sorf\DocHtml::MAX_BYTES) {
			return ['error' => 'The document is too long to save.'];
		}
		$html = \App\Libraries\Sorf\DocHtml::clean($html);
		if (trim(strip_tags($html)) === '') {
			return ['error' => 'The document is empty.'];
		}

		$this->db->query("
			UPDATE tbl_request_doc SET `doc_html` = ?, `updated_at` = NOW() WHERE `recid` = ?
		", [$html, $doc['recid']]);

		return ['ok' => true, 'message' => 'Draft saved.', 'doc_html' => $html];
	}

	//DOCUMENT - RELEASE to the requester (after saving what is on the page)
	public function doc_release() {
		$saved = $this->doc_save();
		if (isset($saved['error'])) {
			return $saved;
		}
		$requestId = (int) $this->request->getPost('recid');

		$this->db->transStart();
		$this->db->query("
			UPDATE tbl_request_doc SET `doc_status` = ?, `released_at` = NOW(), `released_by` = ?, `updated_at` = NOW()
			WHERE `request_id` = ?
		", [DocumentTemplates::STATUS_RELEASED, $this->cuser, $requestId]);
		model('App\Models\Sorf\MyRequestModel')->note($requestId, 'HR shared a draft of the Special Order. It can be viewed, printed or downloaded from this request. It is not final yet.');
		$this->db->transComplete();

		if ($this->db->transStatus() === false) {
			return ['error' => 'The draft was not shared.'];
		}
		return ['ok' => true, 'message' => 'Draft shared with the requester.'];
	}

	//DOCUMENT - WITHDRAW a released document to edit it again
	public function doc_withdraw() {
		$requestId = (int) $this->request->getPost('recid');

		[$record, $error] = $this->hr_record($requestId);
		if ($error !== '') {
			return ['error' => $error];
		}
		$doc = $this->doc_get($requestId);
		if ($doc === null || $doc['doc_status'] !== DocumentTemplates::STATUS_RELEASED) {
			return ['error' => 'This draft is not shared with the requester.'];
		}

		$this->db->transStart();
		$this->db->query("
			UPDATE tbl_request_doc SET `doc_status` = ?, `released_at` = NULL, `released_by` = '', `updated_at` = NOW() WHERE `recid` = ?
		", [DocumentTemplates::STATUS_DRAFT, $doc['recid']]);
		model('App\Models\Sorf\MyRequestModel')->note($requestId, 'HR withdrew the draft Special Order to revise it.');
		$this->db->transComplete();

		if ($this->db->transStatus() === false) {
			return ['error' => 'The document was not withdrawn.'];
		}
		return ['ok' => true, 'message' => 'Draft withdrawn. You can edit it again.'];
	}

} //end main class
