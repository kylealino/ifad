<?php
namespace App\Models;
use CodeIgniter\Model;

class MyMessagesModel extends Model
{

	protected $db;

	// Ticket choices (kept in step with the dropdowns in messages-main.php)
	public const CATEGORIES = ['Request', 'Bug report'];
	public const PRIORITIES = ['Low', 'Normal', 'High'];
	public const STATUSES = ['Open', 'In progress', 'Resolved', 'Closed'];

	// Image attachments: extension => mime type (checked against the file's real content)
	public const IMAGE_TYPES = ['jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png' => 'image/png', 'gif' => 'image/gif', 'webp' => 'image/webp'];
	// Other files a chat may carry: extension => mime type sent back on download
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
	public const MAX_FILE_BYTES = 5242880;          // tickets: 5 MB per image
	public const MAX_CHAT_FILE_BYTES = 10485760;    // chats: 10 MB per file
	public const MAX_CHAT_TOTAL_BYTES = 31457280;   // chats: 30 MB per message (PHP post_max_size is 40M)
	public const MAX_MESSAGE = 2000;

	public function __construct(){
		parent::__construct();
		$this->session = session();
		$this->request = \Config\Services::request();
		$this->db = \Config\Database::connect();
		$this->cuser = $this->session->get('__xsys_myuserzicas__');
	}

	//LOOKUP - active users you can message (everyone but yourself)
	public function users_lookup() {
		return $this->db->query("
			SELECT `username`, MAX(`full_name`) full_name, MAX(`position`) position, MAX(`division`) division
			FROM myua_user
			WHERE `is_active` = 1 AND `username` <> ?
			GROUP BY `username`
			ORDER BY full_name ASC
		", [$this->cuser])->getResultArray();
	}

	//UNREAD - chats + ticket updates (topbar badge)
	public function unread_total() {
		$chats = $this->db->query("
			SELECT COUNT(*) cnt
			FROM tbl_conversation_member m
			JOIN tbl_conversation_msg x ON x.`conv_id` = m.`conv_id` AND x.`recid` > m.`last_read_id` AND x.`added_by` <> m.`username`
			WHERE m.`username` = ?
		", [$this->cuser])->getRowArray();

		return (int) $chats['cnt'] + count(array_filter($this->ticket_list(), fn ($t) => $t['unread'] > 0));
	}

	//INBOX - topbar dropdown: latest chats + tickets with news
	public function inbox() {
		$tickets = array_values(array_filter($this->ticket_list(), fn ($t) => $t['unread'] > 0));
		return [
			'unread' => $this->unread_total(),
			'convs' => array_slice(array_values(array_filter($this->conv_list(), fn ($c) => !$c['is_archived'])), 0, 6),
			'tickets' => array_slice($tickets, 0, 4),
		];
	}

	// =====================================================================
	// CHATS
	// =====================================================================

	//CHATS - LIST (newest activity first, with unread count + last message)
	public function conv_list() {
		$rows = $this->db->query("
			SELECT h.`recid`, h.`conv_type`, h.`title`, m.`is_archived`, COALESCE(h.`last_msg_at`, h.`added_at`) last_at,
				(SELECT COUNT(*) FROM tbl_conversation_msg x WHERE x.`conv_id` = h.`recid` AND x.`recid` > m.`last_read_id` AND x.`added_by` <> m.`username`) unread,
				(SELECT x.`message` FROM tbl_conversation_msg x WHERE x.`conv_id` = h.`recid` ORDER BY x.`recid` DESC LIMIT 1) last_message,
				(SELECT x.`added_by` FROM tbl_conversation_msg x WHERE x.`conv_id` = h.`recid` ORDER BY x.`recid` DESC LIMIT 1) last_sender,
				(SELECT x.`is_system` FROM tbl_conversation_msg x WHERE x.`conv_id` = h.`recid` ORDER BY x.`recid` DESC LIMIT 1) last_system
			FROM tbl_conversation_member m
			JOIN tbl_conversation_hd h ON h.`recid` = m.`conv_id`
			WHERE m.`username` = ?
			ORDER BY last_at DESC, h.`recid` DESC
		", [$this->cuser])->getResultArray();

		if (empty($rows)) {
			return [];
		}

		$members = $this->conv_members(array_column($rows, 'recid'));
		foreach ($rows as &$row) {
			$row['recid'] = (int) $row['recid'];
			$row['unread'] = (int) $row['unread'];
			$row['is_archived'] = (int) $row['is_archived'];
			$row['last_system'] = (int) $row['last_system'];
			$row['members'] = $members[$row['recid']] ?? [];
			$row['name'] = $this->conv_name($row);
		}
		return $rows;
	}

	//CHATS - MEMBERS of several conversations, keyed by conversation
	private function conv_members(array $convIds) {
		$marks = implode(',', array_fill(0, count($convIds), '?'));
		$rows = $this->db->query("
			SELECT m.`conv_id`, m.`username`, m.`last_read_id`,
				COALESCE((SELECT u.`full_name` FROM myua_user u WHERE u.`username` = m.`username` LIMIT 1), m.`username`) full_name
			FROM tbl_conversation_member m
			WHERE m.`conv_id` IN ({$marks})
			ORDER BY full_name ASC
		", array_values($convIds))->getResultArray();

		$out = [];
		foreach ($rows as $r) {
			$out[(int) $r['conv_id']][] = ['username' => $r['username'], 'full_name' => $r['full_name'], 'last_read_id' => (int) $r['last_read_id']];
		}
		return $out;
	}

	// Direct chat = the other person's name; group = its title, else the first names
	private function conv_name(array $conv) {
		$others = array_values(array_filter($conv['members'], fn ($m) => strcasecmp($m['username'], $this->cuser) !== 0));
		if ($conv['conv_type'] === 'group' && trim($conv['title']) !== '') {
			return $conv['title'];
		}
		if (empty($others)) {
			return 'Just you';
		}
		if ($conv['conv_type'] !== 'group') {
			return $others[0]['full_name'];
		}
		$names = array_map(fn ($m) => explode(' ', trim($m['full_name']))[0], $others);
		return implode(', ', array_slice($names, 0, 3)) . (count($names) > 3 ? ' +' . (count($names) - 3) : '');
	}

	//CHATS - one conversation the user belongs to (null if not a member)
	public function conv_get($convId) {
		$row = $this->db->query("
			SELECT h.`recid`, h.`conv_type`, h.`title`, h.`added_by`, h.`added_at`, m.`is_archived`
			FROM tbl_conversation_hd h
			JOIN tbl_conversation_member m ON m.`conv_id` = h.`recid` AND m.`username` = ?
			WHERE h.`recid` = ?
		", [$this->cuser, (int) $convId])->getRowArray();

		if (!$row) {
			return null;
		}
		$row['recid'] = (int) $row['recid'];
		$row['is_archived'] = (int) $row['is_archived'];
		$row['members'] = $this->conv_members([$row['recid']])[$row['recid']] ?? [];
		$row['name'] = $this->conv_name($row);
		return $row;
	}

	//CHATS - MESSAGES (last 200, or only those after $afterId when polling); marks them read
	public function conv_messages($convId, $afterId = 0) {
		$rows = $this->db->query("
			SELECT * FROM (
				SELECT x.`recid`, x.`message`, x.`is_system`, x.`added_by`, x.`added_at`,
					COALESCE((SELECT u.`full_name` FROM myua_user u WHERE u.`username` = x.`added_by` LIMIT 1), x.`added_by`) sender_name
				FROM tbl_conversation_msg x
				WHERE x.`conv_id` = ? AND x.`recid` > ?
				ORDER BY x.`recid` DESC
				LIMIT 200
			) t ORDER BY t.`recid` ASC
		", [(int) $convId, (int) $afterId])->getResultArray();

		$files = [];
		if (!empty($rows)) {
			foreach ($this->db->query("
				SELECT `recid`, `msg_id`, `orig_name`, `file_size`, `is_image`
				FROM tbl_conversation_attachment
				WHERE `conv_id` = ? AND `msg_id` >= ?
				ORDER BY `recid` ASC
			", [(int) $convId, $rows[0]['recid']])->getResultArray() as $f) {
				$files[(int) $f['msg_id']][] = ['recid' => (int) $f['recid'], 'name' => $f['orig_name'], 'size' => (int) $f['file_size'], 'is_image' => (int) $f['is_image']];
			}
		}

		foreach ($rows as &$r) {
			$r['recid'] = (int) $r['recid'];
			$r['is_system'] = (int) $r['is_system'];
			$r['files'] = $files[$r['recid']] ?? [];
		}
		unset($r);

		if (!empty($rows)) {
			$this->db->query("
				UPDATE tbl_conversation_member SET `last_read_id` = GREATEST(`last_read_id`, ?)
				WHERE `conv_id` = ? AND `username` = ?
			", [end($rows)['recid'], (int) $convId, $this->cuser]);
		}
		return $rows;
	}

	//CHATS - START (reuses the direct chat with that person if there is one)
	public function conv_start() {
		$recipients = (array) ($this->request->getPost('recipients') ?? []);
		$title = trim((string) $this->request->getPost('title'));
		$message = trim((string) $this->request->getPost('message'));

		$recipients = array_values(array_unique(array_filter(array_map('trim', $recipients), fn ($u) => $u !== '' && strcasecmp($u, $this->cuser) !== 0)));
		if (empty($recipients)) {
			return ['error' => 'Pick at least one person.'];
		}

		// Only active FODS users
		$marks = implode(',', array_fill(0, count($recipients), '?'));
		$valid = $this->db->query("SELECT DISTINCT `username` FROM myua_user WHERE `is_active` = 1 AND `username` IN ({$marks})", $recipients)->getResultArray();
		if (count($valid) !== count($recipients)) {
			return ['error' => 'Someone you picked is no longer an active user.'];
		}
		$recipients = array_column($valid, 'username');

		if (mb_strlen($message) > self::MAX_MESSAGE) {
			return ['error' => 'Message is too long (' . self::MAX_MESSAGE . ' characters max).'];
		}

		$isGroup = count($recipients) > 1;
		if (!$isGroup) {
			$existing = $this->db->query("
				SELECT h.`recid`
				FROM tbl_conversation_hd h
				JOIN tbl_conversation_member a ON a.`conv_id` = h.`recid` AND a.`username` = ?
				JOIN tbl_conversation_member b ON b.`conv_id` = h.`recid` AND b.`username` = ?
				WHERE h.`conv_type` = 'direct'
				LIMIT 1
			", [$this->cuser, $recipients[0]])->getRowArray();
		}

		$this->db->transStart();

		if (!empty($existing)) {
			$convId = (int) $existing['recid'];
			$this->db->query("UPDATE tbl_conversation_member SET `is_archived` = 0 WHERE `conv_id` = ? AND `username` = ?", [$convId, $this->cuser]);
		} else {
			//INSERTING HD DATA
			$this->db->query("
				INSERT INTO tbl_conversation_hd (`conv_type`, `title`, `added_by`) VALUES (?, ?, ?)
			", [$isGroup ? 'group' : 'direct', $isGroup ? mb_substr($title, 0, 100) : '', $this->cuser]);
			$convId = (int) $this->db->insertID();

			//INSERTING MEMBERS
			foreach (array_merge([$this->cuser], $recipients) as $username) {
				$this->db->query("
					INSERT INTO tbl_conversation_member (`conv_id`, `username`, `added_by`) VALUES (?, ?, ?)
				", [$convId, $username, $this->cuser]);
			}
		}

		if ($message !== '') {
			$this->msg_insert($convId, $message);
		}

		$this->db->transComplete();

		if ($this->db->transStatus() === false) {
			return ['error' => 'An error occurred while starting the chat.'];
		}
		return ['conv_id' => $convId];
	}

	//CHATS - SEND
	public function msg_send() {
		$convId = (int) $this->request->getPost('conv_id');
		$message = trim((string) $this->request->getPost('message'));

		if ($this->conv_get($convId) === null) {
			return ['error' => 'Chat not found.'];
		}
		[$files, $fileError] = $this->upload_files(false, self::MAX_CHAT_FILE_BYTES, self::MAX_CHAT_TOTAL_BYTES);
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
		$msgId = $this->msg_insert($convId, $message);

		//INSERTING ATTACHMENTS
		foreach ($files as $up) {
			$row = $this->upload_store('chats', $up);
			$this->db->query("
				INSERT INTO tbl_conversation_attachment (`conv_id`, `msg_id`, `file_name`, `orig_name`, `mime_type`, `file_size`, `is_image`, `added_by`)
				VALUES (?, ?, ?, ?, ?, ?, ?, ?)
			", [$convId, $msgId, $row['file_name'], $row['orig_name'], $row['mime_type'], $row['file_size'], $row['is_image'], $this->cuser]);
		}
		$this->db->transComplete();

		if ($this->db->transStatus() === false) {
			return ['error' => 'Your message was not sent.'];
		}
		return ['ok' => true];
	}

	// $isSystem = 1 for a note in the chat (e.g. "removed X from the group")
	private function msg_insert($convId, $message, $isSystem = 0) {
		$this->db->query("
			INSERT INTO tbl_conversation_msg (`conv_id`, `message`, `is_system`, `added_by`) VALUES (?, ?, ?, ?)
		", [$convId, $message, $isSystem, $this->cuser]);
		$msgId = (int) $this->db->insertID();

		$this->db->query("UPDATE tbl_conversation_hd SET `last_msg_at` = NOW() WHERE `recid` = ?", [$convId]);
		// a new message brings an archived chat back to everyone's list
		$this->db->query("UPDATE tbl_conversation_member SET `is_archived` = 0 WHERE `conv_id` = ? AND `is_archived` = 1", [$convId]);
		// your own message counts as read
		$this->db->query("
			UPDATE tbl_conversation_member SET `last_read_id` = GREATEST(`last_read_id`, ?)
			WHERE `conv_id` = ? AND `username` = ?
		", [$msgId, $convId, $this->cuser]);
		return $msgId;
	}

	//CHATS - ARCHIVE / UNARCHIVE (only for you; the others keep the chat)
	public function conv_archive() {
		$convId = (int) $this->request->getPost('conv_id');
		$archived = (int) $this->request->getPost('archived') === 1 ? 1 : 0;

		if ($this->conv_get($convId) === null) {
			return ['error' => 'Chat not found.'];
		}
		$this->db->query("
			UPDATE tbl_conversation_member SET `is_archived` = ? WHERE `conv_id` = ? AND `username` = ?
		", [$archived, $convId, $this->cuser]);
		return ['ok' => true, 'archived' => $archived];
	}

	//CHATS - REMOVE A GROUP MEMBER (only the person who created the group)
	public function conv_remove() {
		$convId = (int) $this->request->getPost('conv_id');
		$username = trim((string) $this->request->getPost('username'));

		$conv = $this->conv_get($convId);
		if ($conv === null) {
			return ['error' => 'Chat not found.'];
		}
		if ($conv['conv_type'] !== 'group') {
			return ['error' => 'Only group chats have members to remove.'];
		}
		if (strcasecmp($conv['added_by'], (string) $this->cuser) !== 0) {
			return ['error' => 'Only the person who created this group can remove members.'];
		}
		if (strcasecmp($username, (string) $this->cuser) === 0) {
			return ['error' => "You can't remove yourself from your own group."];
		}
		$member = null;
		foreach ($conv['members'] as $m) {
			if (strcasecmp($m['username'], $username) === 0) {
				$member = $m;
			}
		}
		if ($member === null) {
			return ['error' => 'That person is not in this group.'];
		}

		$this->db->transStart();
		$this->db->query("DELETE FROM tbl_conversation_member WHERE `conv_id` = ? AND `username` = ?", [$convId, $member['username']]);
		$this->msg_insert($convId, 'removed ' . $member['full_name'] . ' from the group', 1);
		$this->db->transComplete();

		if ($this->db->transStatus() === false) {
			return ['error' => 'An error occurred while removing the member.'];
		}
		return ['ok' => true];
	}

	//CHATS - ATTACHMENT for download (null unless you are in that chat)
	public function chat_attachment($recid) {
		$file = $this->db->query("SELECT * FROM tbl_conversation_attachment WHERE `recid` = ?", [(int) $recid])->getRowArray();
		if (!$file || $this->conv_get($file['conv_id']) === null) {
			return null;
		}
		$file['path'] = $this->upload_path('chats', $file['file_name']);
		$file['is_image'] = (int) $file['is_image'];
		return $file['path'] === null ? null : $file;
	}

	// =====================================================================
	// TICKETS
	// =====================================================================

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

	// =====================================================================
	// UPLOADS (shared by tickets and chats)
	// =====================================================================

	//UPLOADS - VALIDATE the 'attachments[]' files: [files, error]
	// Each file comes back as ['file' => UploadedFile, 'ext', 'mime', 'is_image'].
	// Types are checked against the file's content, not the name the browser sent.
	private function upload_files(bool $imagesOnly, int $maxBytes, int $maxTotal = 0) {
		$files = array_values(array_filter((array) ($this->request->getFileMultiple('attachments') ?? []), fn ($f) => $f !== null && $f->getError() !== UPLOAD_ERR_NO_FILE));
		$what = $imagesOnly ? 'images' : 'files';

		if (count($files) > self::MAX_FILES) {
			return [[], 'Attach up to ' . self::MAX_FILES . " {$what} at a time."];
		}

		$out = [];
		$total = 0;
		foreach ($files as $file) {
			$name = $file->getClientName();
			if (!$file->isValid()) {
				return [[], "{$name} did not upload. Please try again."];
			}
			if ($file->getSize() > $maxBytes) {
				return [[], "{$name} is over " . round($maxBytes / 1048576) . ' MB.'];
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
			if ($imagesOnly) {
				return [[], "{$name} is not an image (JPG, PNG, GIF or WEBP only)."];
			}
			if (!isset(self::DOC_TYPES[$ext]) || in_array($mime, self::BLOCKED_MIMES, true) || ($ext === 'pdf' && $mime !== 'application/pdf')) {
				return [[], "{$name} can't be sent. Allowed: images, PDF, Word, Excel, PowerPoint, CSV, TXT and ZIP."];
			}
			$out[] = ['file' => $file, 'ext' => $ext, 'mime' => self::DOC_TYPES[$ext], 'is_image' => 0];
		}

		if ($maxTotal > 0 && $total > $maxTotal) {
			return [[], 'Files are too big together (' . round($maxTotal / 1048576) . ' MB per message).'];
		}
		return [$out, ''];
	}

	//UPLOADS - MOVE one validated file to writable/uploads/<folder>/<year>/
	// Returns the columns every attachment table shares.
	private function upload_store(string $folder, array $up) {
		$sub = date('Y');
		$dir = WRITEPATH . 'uploads/' . $folder . '/' . $sub;
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
	private function upload_path(string $folder, string $fileName) {
		$path = WRITEPATH . 'uploads/' . $folder . '/' . $fileName;
		if (!preg_match('~^\d{4}/[\w.]+$~', $fileName) || !is_file($path)) {
			return null;
		}
		return $path;
	}

	//TICKETS - SAVE ATTACHMENTS to writable/uploads/tickets/<year>/
	private function ticket_store_files($ticketId, $replyId, array $files) {
		foreach ($files as $up) {
			$row = $this->upload_store('tickets', $up);
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
		$file['path'] = $this->upload_path('tickets', $file['file_name']);
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
		[$files, $fileError] = $this->upload_files(true, self::MAX_FILE_BYTES);
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
						window.location.href = 'mymessages?meaction=MAIN&tab=tickets';
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

		[$files, $fileError] = $this->upload_files(true, self::MAX_FILE_BYTES);
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
