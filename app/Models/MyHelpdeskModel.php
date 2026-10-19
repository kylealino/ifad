<?php
namespace App\Models;
use CodeIgniter\Model;

// Helpdesk tickets: everyone files and follows their own (My tickets);
// support staff see and work every ticket (Support desk). The thread of a
// ticket is tbl_ticket_dt (first row = the description, is_system rows =
// status notes), screenshots are tbl_ticket_attachment, and tbl_ticket_seen
// keeps how far each person has read.
class MyHelpdeskModel extends Model
{

	protected $db;

	// Ticket choices (kept in step with the dropdowns in helpdesk-main.php)
	public const CATEGORIES = ['Request', 'Bug report'];
	public const PRIORITIES = ['Low', 'Normal', 'High'];
	public const STATUSES = ['Open', 'In progress', 'Resolved', 'Closed'];
	public const MAX_FILE_BYTES = 5242880;          // 5 MB per screenshot
	public const MAX_MESSAGE = 2000;

	public function __construct(){
		parent::__construct();
		$this->session = session();
		$this->request = \Config\Services::request();
		$this->db = \Config\Database::connect();
		$this->cuser = $this->session->get('__xsys_myuserzicas__');
		$this->uploads = model('App\Models\MyUploadsModel');
	}

	//UNREAD - tickets with news for you (topbar helpdesk badge)
	public function unread_total() {
		return count(array_filter($this->ticket_list(), fn ($t) => $t['unread'] > 0));
	}

	//INBOX - topbar helpdesk dropdown: tickets with news first, then your
	// other tickets that are still open
	public function inbox() {
		$list = $this->ticket_list();
		$news = array_values(array_filter($list, fn ($t) => $t['unread'] > 0));
		$open = array_values(array_filter($list, fn ($t) => $t['unread'] === 0 && $t['mine'] && in_array($t['status'], ['Open', 'In progress'], true)));
		return [
			'unread' => count($news),
			'tickets' => array_slice(array_merge($news, $open), 0, 6),
		];
	}

	public function ticket_is_owner(array $hd) {
		return strcasecmp($hd['added_by'], (string) $this->cuser) === 0;
	}

	// Support desk (everyone's tickets): View, or Update for support staff
	public function desk_access() {
		return fods_has_any_access([\Config\AccessCodes::TICKET_VIEW, \Config\AccessCodes::TICKET_UPDATE]);
	}

	//TICKETS - LIST (your own; support desk users also get everyone else's)
	// Each row says whether it is yours ('mine'), its last post and whether
	// support owes the requester an answer ('needs_reply').
	public function ticket_list() {
		$seeAll = $this->desk_access() ? 1 : 0;
		$rows = $this->db->query("
			SELECT h.`recid`, h.`ticket_no`, h.`category`, h.`priority`, h.`module`, h.`subject`, h.`status`,
				h.`handled_by`, h.`added_by`, h.`added_at`, COALESCE(h.`updated_at`, h.`added_at`) updated_at,
				COALESCE((SELECT u.`full_name` FROM myua_user u WHERE u.`username` = h.`added_by` LIMIT 1), h.`added_by`) requester_name,
				COALESCE((SELECT u.`full_name` FROM myua_user u WHERE u.`username` = h.`handled_by` LIMIT 1), h.`handled_by`) handler_name,
				(SELECT COUNT(*) FROM tbl_ticket_dt d WHERE d.`ticket_id` = h.`recid` AND d.`is_system` = 0) posts,
				(SELECT COUNT(*) FROM tbl_ticket_attachment a WHERE a.`ticket_id` = h.`recid`) files,
				(SELECT d.`message` FROM tbl_ticket_dt d WHERE d.`ticket_id` = h.`recid` AND d.`is_system` = 0 ORDER BY d.`recid` DESC LIMIT 1) last_message,
				(SELECT d.`added_by` FROM tbl_ticket_dt d WHERE d.`ticket_id` = h.`recid` AND d.`is_system` = 0 ORDER BY d.`recid` DESC LIMIT 1) last_sender,
				(SELECT COUNT(*) FROM tbl_ticket_dt d WHERE d.`ticket_id` = h.`recid` AND d.`added_by` <> ?
					AND d.`recid` > COALESCE((SELECT s.`last_read_id` FROM tbl_ticket_seen s WHERE s.`ticket_id` = h.`recid` AND s.`username` = ?), 0)) unread
			FROM tbl_ticket_hd h
			WHERE (? = 1 OR h.`added_by` = ?)
			ORDER BY updated_at DESC, h.`recid` DESC
		", [$this->cuser, $this->cuser, $seeAll, $this->cuser])->getResultArray();

		foreach ($rows as &$r) {
			$r['recid'] = (int) $r['recid'];
			$r['replies'] = max(0, (int) $r['posts'] - 1);   // first post is the description
			$r['files'] = (int) $r['files'];
			$r['unread'] = (int) $r['unread'];
			$r['mine'] = $this->ticket_is_owner($r);
			// the requester spoke last on a ticket that is still open
			$r['needs_reply'] = in_array($r['status'], ['Open', 'In progress'], true) && strcasecmp((string) $r['last_sender'], $r['added_by']) === 0;
			unset($r['posts']);
		}
		return $rows;
	}

	//TICKETS - HEADER the user may open (null unless it is theirs or they use the Support desk)
	public function ticket_get($recid) {
		$hd = $this->db->query("
			SELECT h.*,
				COALESCE((SELECT u.`full_name` FROM myua_user u WHERE u.`username` = h.`added_by` LIMIT 1), h.`added_by`) requester_name,
				COALESCE((SELECT u.`full_name` FROM myua_user u WHERE u.`username` = h.`handled_by` LIMIT 1), h.`handled_by`) handler_name
			FROM tbl_ticket_hd h
			WHERE h.`recid` = ?
		", [(int) $recid])->getRowArray();

		$canOpen = $hd && ($this->ticket_is_owner($hd) || $this->desk_access());
		if (!$canOpen) {
			return null;
		}
		return $hd;
	}

	//TICKETS - LOAD (header + thread + attachments); marks it seen
	public function ticket_load($recid) {
		$hd = $this->ticket_get($recid);
		if ($hd === null) {
			return null;
		}

		$thread = $this->db->query("
			SELECT d.`recid`, d.`message`, d.`is_system`, d.`added_by`, d.`added_at`,
				COALESCE((SELECT u.`full_name` FROM myua_user u WHERE u.`username` = d.`added_by` LIMIT 1), d.`added_by`) sender_name
			FROM tbl_ticket_dt d
			WHERE d.`ticket_id` = ?
			ORDER BY d.`recid` ASC
		", [$hd['recid']])->getResultArray();

		$files = $this->db->query("
			SELECT `recid`, `reply_id`, `orig_name`, `file_size`
			FROM tbl_ticket_attachment
			WHERE `ticket_id` = ?
			ORDER BY `recid` ASC
		", [$hd['recid']])->getResultArray();

		$byReply = [];
		foreach ($files as $f) {
			$byReply[(int) $f['reply_id']][] = ['recid' => (int) $f['recid'], 'name' => $f['orig_name'], 'size' => (int) $f['file_size']];
		}
		foreach ($thread as &$t) {
			$t['recid'] = (int) $t['recid'];
			$t['is_system'] = (int) $t['is_system'];
			$t['files'] = $byReply[$t['recid']] ?? [];
			$t['mine'] = strcasecmp($t['added_by'], (string) $this->cuser) === 0;
		}

		if (!empty($thread)) {
			$this->db->query("
				INSERT INTO tbl_ticket_seen (`ticket_id`, `username`, `last_read_id`, `added_by`) VALUES (?, ?, ?, ?)
				ON DUPLICATE KEY UPDATE `last_read_id` = GREATEST(`last_read_id`, VALUES(`last_read_id`))
			", [$hd['recid'], $this->cuser, end($thread)['recid'], $this->cuser]);
		}

		$isOwner = $this->ticket_is_owner($hd);
		$canWork = fods_has_access(\Config\AccessCodes::TICKET_UPDATE);
		return [
			'hd' => $hd,
			'thread' => $thread,
			'perms' => [
				'owner' => $isOwner,
				'work' => $canWork,
				'reply' => $isOwner || $canWork,
			],
		];
	}

	//TICKETS - SAVE ATTACHMENTS to writable/uploads/tickets/<year>/
	private function ticket_store_files($ticketId, $replyId, array $files) {
		foreach ($files as $up) {
			$row = $this->uploads->upload_store('tickets', $up);
			$this->db->query("
				INSERT INTO tbl_ticket_attachment (`ticket_id`, `reply_id`, `file_name`, `orig_name`, `mime_type`, `file_size`, `added_by`)
				VALUES (?, ?, ?, ?, ?, ?, ?)
			", [$ticketId, $replyId, $row['file_name'], $row['orig_name'], $row['mime_type'], $row['file_size'], $this->cuser]);
		}
	}

	//TICKETS - ATTACHMENT for download (null if the user can't open that ticket)
	public function ticket_attachment($recid) {
		$file = $this->db->query("SELECT * FROM tbl_ticket_attachment WHERE `recid` = ?", [(int) $recid])->getRowArray();
		if (!$file || $this->ticket_get($file['ticket_id']) === null) {
			return null;
		}
		$file['path'] = $this->uploads->upload_path('tickets', $file['file_name']);
		$file['is_image'] = 1;
		return $file['path'] === null ? null : $file;
	}

	//TICKET - SAVE (new request / bug report)
	public function ticket_save() {
		$category = trim((string) $this->request->getPost('category'));
		$priority = trim((string) $this->request->getPost('priority'));
		$module = mb_substr(trim((string) $this->request->getPost('module')), 0, 100);
		$subject = trim((string) $this->request->getPost('subject'));
		$description = trim((string) $this->request->getPost('description'));

		$error = '';
		if ($subject === '') {
			$error = 'Subject is required!';
		} elseif (mb_strlen($subject) > 150) {
			$error = 'Subject is too long (150 characters max)!';
		} elseif ($description === '') {
			$error = 'Please describe what you need!';
		} elseif (mb_strlen($description) > self::MAX_MESSAGE) {
			$error = 'Description is too long (' . self::MAX_MESSAGE . ' characters max)!';
		}
		[$files, $fileError] = $this->uploads->upload_files(true, self::MAX_FILE_BYTES);
		if ($error === '' && $fileError !== '') {
			$error = $fileError;
		}

		if ($error !== '') {
			$error = addslashes($error);
			echo "
			<script>
			toastr.error('{$error}', 'Oops!', {
					progressBar: true,
					closeButton: true,
					timeOut:2000,
				});
			</script>
			";
			die();
		}

		if (!in_array($category, self::CATEGORIES, true)) {
			$category = 'Request';
		}
		if (!in_array($priority, self::PRIORITIES, true)) {
			$priority = 'Normal';
		}

		$this->db->transStart();

		//INSERTING HD DATA
		$this->db->query("
			INSERT INTO tbl_ticket_hd (`category`, `priority`, `module`, `subject`, `status`, `updated_at`, `added_by`)
			VALUES (?, ?, ?, ?, 'Open', NOW(), ?)
		", [$category, $priority, $module, $subject, $this->cuser]);
		$ticketId = (int) $this->db->insertID();
		$ticketNo = 'TKT-' . date('Ym') . '-' . str_pad((string) $ticketId, 4, '0', STR_PAD_LEFT);
		$this->db->query("UPDATE tbl_ticket_hd SET `ticket_no` = ? WHERE `recid` = ?", [$ticketNo, $ticketId]);

		//INSERTING DT DATA (the description is the first post)
		$this->db->query("
			INSERT INTO tbl_ticket_dt (`ticket_id`, `message`, `added_by`) VALUES (?, ?, ?)
		", [$ticketId, $description, $this->cuser]);
		$replyId = (int) $this->db->insertID();

		$this->ticket_store_files($ticketId, $replyId, $files);

		$this->db->transComplete();

		if ($this->db->transStatus() !== false) {
			echo "
			<script>
				toastr.success('Ticket {$ticketNo} sent!', 'Well Done!', {
						progressBar: true,
						closeButton: true,
						timeOut:2500,
					});
				// close the form and refresh the list (the ticket is not opened)
				bootstrap.Modal.getOrCreateInstance(document.getElementById('ticketModal')).hide();
				setTimeout(function() {
						window.location.href = 'myhelpdesk?meaction=MAIN&tab=tickets';
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

	//TICKET - REPLY (owner or support; may attach images)
	public function ticket_reply() {
		$hd = $this->ticket_get($this->request->getPost('recid'));
		$message = trim((string) $this->request->getPost('message'));

		if ($hd === null) {
			return ['error' => 'Ticket not found.'];
		}
		$isOwner = $this->ticket_is_owner($hd);
		$canWork = fods_has_access(\Config\AccessCodes::TICKET_UPDATE);
		if (!$isOwner && !$canWork) {
			return ['error' => 'Reply Access Denied! Please Contact the Administrator.'];
		}

		[$files, $fileError] = $this->uploads->upload_files(true, self::MAX_FILE_BYTES);
		if ($fileError !== '') {
			return ['error' => $fileError];
		}
		if ($message === '' && empty($files)) {
			return ['error' => 'Type a reply or attach an image.'];
		}
		if (mb_strlen($message) > self::MAX_MESSAGE) {
			return ['error' => 'Reply is too long (' . self::MAX_MESSAGE . ' characters max).'];
		}

		$this->db->transStart();

		$this->db->query("
			INSERT INTO tbl_ticket_dt (`ticket_id`, `message`, `added_by`) VALUES (?, ?, ?)
		", [$hd['recid'], $message, $this->cuser]);
		$replyId = (int) $this->db->insertID();
		$this->ticket_store_files($hd['recid'], $replyId, $files);

		// Support answering an open ticket picks it up; the requester
		// answering a resolved/closed one reopens it
		$status = $hd['status'];
		$handler = $hd['handled_by'];
		if ($canWork && !$isOwner && $status === 'Open') {
			$status = 'In progress';
			$handler = $this->cuser;
		} elseif ($isOwner && in_array($status, ['Resolved', 'Closed'], true)) {
			$status = 'Open';
		}
		if ($status !== $hd['status']) {
			$this->ticket_log($hd['recid'], "changed the status to {$status}");
		}
		$this->db->query("
			UPDATE tbl_ticket_hd SET `status` = ?, `handled_by` = ?, `updated_at` = NOW() WHERE `recid` = ?
		", [$status, $handler, $hd['recid']]);

		$this->db->transComplete();

		if ($this->db->transStatus() === false) {
			return ['error' => 'An error occurred while sending your reply.'];
		}
		return ['ok' => true];
	}

	//TICKET - STATUS (support: any status; requester: close it or reopen it)
	public function ticket_status() {
		$hd = $this->ticket_get($this->request->getPost('recid'));
		$status = trim((string) $this->request->getPost('status'));

		if ($hd === null) {
			return ['error' => 'Ticket not found.'];
		}
		if (!in_array($status, self::STATUSES, true)) {
			return ['error' => 'Unknown status.'];
		}
		if ($status === $hd['status']) {
			return ['ok' => true];
		}

		$canWork = fods_has_access(\Config\AccessCodes::TICKET_UPDATE);
		$ownerMove = $this->ticket_is_owner($hd) && (
			$status === 'Closed' ||
			($status === 'Open' && in_array($hd['status'], ['Resolved', 'Closed'], true))
		);
		if (!$canWork && !$ownerMove) {
			return ['error' => 'Updating Access Denied! Please Contact the Administrator.'];
		}

		$handler = $hd['handled_by'];
		if ($canWork && $handler === '' && $status !== 'Open') {
			$handler = $this->cuser;
		}

		$this->db->transStart();
		$this->ticket_log($hd['recid'], "changed the status to {$status}");
		$this->db->query("
			UPDATE tbl_ticket_hd SET `status` = ?, `handled_by` = ?, `updated_at` = NOW() WHERE `recid` = ?
		", [$status, $handler, $hd['recid']]);
		$this->db->transComplete();

		if ($this->db->transStatus() === false) {
			return ['error' => 'An error occurred while updating the ticket.'];
		}
		return ['ok' => true];
	}

	// Status changes show in the thread as a one-line note
	private function ticket_log($ticketId, $text) {
		$this->db->query("
			INSERT INTO tbl_ticket_dt (`ticket_id`, `message`, `is_system`, `added_by`) VALUES (?, ?, 1, ?)
		", [$ticketId, $text, $this->cuser]);
	}

} //end main class
?>
