<?php
namespace App\Models\Sorf;
use CodeIgniter\Model;

class MyRequestChatModel extends Model
{

	protected $db;
	protected $session;
	protected $request;
	protected $cuser;

	// Image attachments: extension => mime type (checked against the file's real content)
	public const IMAGE_TYPES = ['jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png' => 'image/png', 'gif' => 'image/gif', 'webp' => 'image/webp'];
	// Other files a message may carry: extension => mime type sent back on download
	public const DOC_TYPES = [
		'pdf' => 'application/pdf',
		'doc' => 'application/msword',
		'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
		'xls' => 'application/vnd.ms-excel',
		'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
		'ppt' => 'application/vnd.ms-powerpoint',
		'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
		'csv' => 'text/csv',
		'txt' => 'text/plain',
		'zip' => 'application/zip',
	];
	// Content that is never accepted, whatever the file is called
	public const BLOCKED_MIMES = [
		'text/html', 'text/x-php', 'application/x-php', 'application/x-httpd-php', 'text/javascript', 'application/javascript',
		'application/x-dosexec', 'application/x-msdownload', 'application/x-executable', 'application/x-sharedlib',
		'application/x-sh', 'text/x-shellscript', 'image/svg+xml', 'application/x-msi',
	];
	public const MAX_FILES = 5;
	public const MAX_FILE_BYTES = 10485760;     // 10 MB per file
	public const MAX_TOTAL_BYTES = 31457280;    // 30 MB per message (PHP post_max_size is 40M)
	public const MAX_MESSAGE = 2000;

	public function __construct(){
		parent::__construct();
		$this->session = session();
		$this->request = \Config\Services::request();
		$this->db = \Config\Database::connect('sorf');
		$this->cuser = $this->session->get('__xsys_myuserzicas__');
	}

	//CONVERSATION - MESSAGES of a request newer than $afterId, oldest first.
	// The caller has already checked the request is yours (MyRequestModel::request_get).
	// $logOnly leaves out the messages: just the request's log entries.
	public function thread_messages($requestId, $afterId = 0, bool $logOnly = false) {
		$only = $logOnly ? 'AND x.`is_system` = 1' : '';
		$rows = $this->db->query("
			SELECT * FROM (
				SELECT x.`recid`, x.`message`, x.`is_system`, x.`added_by`, x.`added_at`,
					COALESCE(u.`full_name`, x.`added_by`) sender_name,
					-- HR label beside the name: the sender's role sees HR's requests
					EXISTS (SELECT 1 FROM tbl_role_permission p WHERE p.`role_id` = u.`role_id` AND p.`perm_code` = ?) is_hr
				FROM tbl_request_msg x
				LEFT JOIN myua_user u ON u.`username` = x.`added_by`
				WHERE x.`request_id` = ? AND x.`recid` > ? {$only}
				ORDER BY x.`recid` DESC
				LIMIT 200
			) t ORDER BY t.`recid` ASC
		", [\Config\Sorf\Permissions::HR_VIEW, (int) $requestId, (int) $afterId])->getResultArray();

		$files = [];
		if (!empty($rows)) {
			foreach ($this->db->query("
				SELECT `recid`, `msg_id`, `orig_name`, `file_size`, `is_image`
				FROM tbl_request_attachment
				WHERE `request_id` = ? AND `msg_id` >= ?
				ORDER BY `recid` ASC
			", [(int) $requestId, $rows[0]['recid']])->getResultArray() as $f) {
				$files[(int) $f['msg_id']][] = ['recid' => (int) $f['recid'], 'name' => $f['orig_name'], 'size' => (int) $f['file_size'], 'is_image' => (int) $f['is_image']];
			}
		}

		foreach ($rows as &$r) {
			$r['recid'] = (int) $r['recid'];
			$r['is_system'] = (int) $r['is_system'];
			$r['is_hr'] = (int) $r['is_hr'];
			$r['files'] = $files[$r['recid']] ?? [];
		}
		unset($r);

		if (!empty($rows)) {
			$this->mark_read($requestId, end($rows)['recid']);
		}
		return $rows;
	}

	private function mark_read($requestId, $msgId) {
		$this->db->query("
			INSERT INTO tbl_request_seen (`request_id`, `username`, `last_read_id`, `added_by`) VALUES (?, ?, ?, ?)
			ON DUPLICATE KEY UPDATE `last_read_id` = GREATEST(`last_read_id`, VALUES(`last_read_id`))
		", [(int) $requestId, $this->cuser, (int) $msgId, $this->cuser]);
	}

	//CONVERSATION - SEND a message and/or files
	public function msg_send() {
		$requestId = (int) $this->request->getPost('recid');
		$message = trim((string) $this->request->getPost('message'));

		$requests = model('App\Models\Sorf\MyRequestModel');
		$hd = $requests->request_get($requestId);
		if ($hd === null) {
			return ['error' => 'Request not found.'];
		}
		if (!$requests->request_can_chat($hd)) {
			return ['error' => 'The conversation is between the requester and HR.'];
		}
		[$files, $fileError] = $this->upload_files();
		if ($fileError !== '') {
			return ['error' => $fileError];
		}
		if ($message === '' && empty($files)) {
			return ['error' => 'Type a message or attach a file.'];
		}
		if (mb_strlen($message) > self::MAX_MESSAGE) {
			return ['error' => 'Message is too long (' . self::MAX_MESSAGE . ' characters max).'];
		}

		$this->db->transStart();
		$this->db->query("
			INSERT INTO tbl_request_msg (`request_id`, `message`, `is_system`, `added_by`) VALUES (?, ?, 0, ?)
		", [$requestId, $message, $this->cuser]);
		$msgId = (int) $this->db->insertID();

		//INSERTING ATTACHMENTS
		foreach ($files as $up) {
			$row = $this->upload_store($up);
			$this->db->query("
				INSERT INTO tbl_request_attachment (`request_id`, `msg_id`, `file_name`, `orig_name`, `mime_type`, `file_size`, `is_image`, `added_by`)
				VALUES (?, ?, ?, ?, ?, ?, ?, ?)
			", [$requestId, $msgId, $row['file_name'], $row['orig_name'], $row['mime_type'], $row['file_size'], $row['is_image'], $this->cuser]);
		}
		// your own message counts as read
		$this->mark_read($requestId, $msgId);
		$this->db->transComplete();

		if ($this->db->transStatus() === false) {
			return ['error' => 'Your message was not sent.'];
		}
		return ['ok' => true];
	}

	//CONVERSATION - ATTACHMENT for download (null unless you filed the request or you are HR)
	public function attachment($recid) {
		$file = $this->db->query("SELECT * FROM tbl_request_attachment WHERE `recid` = ?", [(int) $recid])->getRowArray();
		$requests = model('App\Models\Sorf\MyRequestModel');
		$hd = $file ? $requests->request_get($file['request_id']) : null;
		if ($hd === null || !$requests->request_can_chat($hd)) {
			return null;
		}
		$file['path'] = $this->upload_path($file['file_name']);
		$file['is_image'] = (int) $file['is_image'];
		return $file['path'] === null ? null : $file;
	}

	//UPLOADS - VALIDATE the posted "attachments[]". Returns [files, error].
	// Each file comes back as ['file' => UploadedFile, 'ext', 'mime', 'is_image'].
	// Types are checked against the file's content, not the name the browser sent.
	private function upload_files() {
		$files = array_values(array_filter((array) ($this->request->getFileMultiple('attachments') ?? []), fn ($f) => $f !== null && $f->getError() !== UPLOAD_ERR_NO_FILE));

		if (count($files) > self::MAX_FILES) {
			return [[], 'Attach up to ' . self::MAX_FILES . ' files at a time.'];
		}

		$out = [];
		$total = 0;
		foreach ($files as $file) {
			$name = $file->getClientName();
			if (!$file->isValid()) {
				return [[], "{$name} did not upload. Please try again."];
			}
			if ($file->getSize() > self::MAX_FILE_BYTES) {
				return [[], "{$name} is over " . round(self::MAX_FILE_BYTES / 1048576) . ' MB.'];
			}
			$total += $file->getSize();

			$ext = strtolower($file->getClientExtension());
			$mime = (string) $file->getMimeType();
			if (isset(self::IMAGE_TYPES[$ext])) {
				if (!in_array($mime, self::IMAGE_TYPES, true) || @getimagesize($file->getTempName()) === false) {
					return [[], "{$name} is not a valid image."];
				}
				$out[] = ['file' => $file, 'ext' => $ext, 'mime' => $mime, 'is_image' => 1];
				continue;
			}
			if (!isset(self::DOC_TYPES[$ext]) || in_array($mime, self::BLOCKED_MIMES, true) || ($ext === 'pdf' && $mime !== 'application/pdf')) {
				return [[], "{$name} can't be sent. Allowed: images, PDF, Word, Excel, PowerPoint, CSV, TXT and ZIP."];
			}
			$out[] = ['file' => $file, 'ext' => $ext, 'mime' => self::DOC_TYPES[$ext], 'is_image' => 0];
		}

		if ($total > self::MAX_TOTAL_BYTES) {
			return [[], 'Files are too big together (' . round(self::MAX_TOTAL_BYTES / 1048576) . ' MB per message).'];
		}
		return [$out, ''];
	}

	//UPLOADS - MOVE one validated file to writable/uploads/sorf/requests/<year>/
	private function upload_store(array $up) {
		$sub = date('Y');
		$dir = WRITEPATH . 'uploads/sorf/requests/' . $sub;
		if (!is_dir($dir)) {
			mkdir($dir, 0755, true);
		}
		$file = $up['file'];
		$stored = bin2hex(random_bytes(16)) . '.' . $up['ext'];
		$row = [
			'file_name' => $sub . '/' . $stored,
			'orig_name' => mb_substr($file->getClientName(), 0, 255),
			'mime_type' => $up['mime'],
			'file_size' => (int) $file->getSize(),
			'is_image' => $up['is_image'],
		];
		$file->move($dir, $stored);
		return $row;
	}

	//UPLOADS - PATH of a stored file (null if the name is odd or the file is gone)
	private function upload_path(string $fileName) {
		$path = WRITEPATH . 'uploads/sorf/requests/' . $fileName;
		if (!preg_match('~^\d{4}/[\w.]+$~', $fileName) || !is_file($path)) {
			return null;
		}
		return $path;
	}

} //end main class
