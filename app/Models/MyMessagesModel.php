<?php
namespace App\Models;
use CodeIgniter\Model;

class MyMessagesModel extends Model
{

	protected $db;

	public const MAX_CHAT_FILE_BYTES = 10485760;    // chats: 10 MB per file
	public const MAX_CHAT_TOTAL_BYTES = 31457280;   // chats: 30 MB per message (PHP post_max_size is 40M)
	public const MAX_MESSAGE = 2000;
	public const TYPING_SECONDS = 6;   // shown as typing this long after the last keystroke ping

	public function __construct(){
		parent::__construct();
		$this->session = session();
		$this->request = \Config\Services::request();
		$this->db = \Config\Database::connect();
		$this->cuser = $this->session->get('__xsys_myuserzicas__');
		$this->uploads = model('App\Models\MyUploadsModel');
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

	//UNREAD - messages you haven't read yet (topbar messages badge)
	public function unread_total() {
		$chats = $this->db->query("
			SELECT COUNT(*) cnt
			FROM tbl_conversation_member m
			JOIN tbl_conversation_msg x ON x.`conv_id` = m.`conv_id` AND x.`recid` > m.`last_read_id` AND x.`added_by` <> m.`username`
			WHERE m.`username` = ?
		", [$this->cuser])->getRowArray();

		return (int) $chats['cnt'];
	}

	//INBOX - topbar messages dropdown: latest chats
	public function inbox() {
		return [
			'unread' => $this->unread_total(),
			'convs' => array_slice(array_values(array_filter($this->conv_list(), fn ($c) => !$c['is_archived'])), 0, 6),
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

	//CHATS - MEMBERS of several conversations, keyed by conversation, with how
	// far each has read (last_read_id, and when: last_read_at) and whether
	// they are typing right now (typed in the last TYPING_SECONDS)
	private function conv_members(array $convIds) {
		$marks = implode(',', array_fill(0, count($convIds), '?'));
		$rows = $this->db->query("
			SELECT m.`conv_id`, m.`username`, m.`last_read_id`, m.`last_read_at`,
				IF(m.`typing_at` IS NOT NULL AND m.`typing_at` > NOW() - INTERVAL " . self::TYPING_SECONDS . " SECOND, 1, 0) is_typing,
				COALESCE((SELECT u.`full_name` FROM myua_user u WHERE u.`username` = m.`username` LIMIT 1), m.`username`) full_name
			FROM tbl_conversation_member m
			WHERE m.`conv_id` IN ({$marks})
			ORDER BY full_name ASC
		", array_values($convIds))->getResultArray();

		$out = [];
		foreach ($rows as $r) {
			$out[(int) $r['conv_id']][] = [
				'username' => $r['username'],
				'full_name' => $r['full_name'],
				'last_read_id' => (int) $r['last_read_id'],
				'last_read_at' => $r['last_read_at'],
				'is_typing' => (int) $r['is_typing'],
			];
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
			// last_read_at first: it compares against the old last_read_id
			$lastId = end($rows)['recid'];
			$this->db->query("
				UPDATE tbl_conversation_member
				SET `last_read_at` = IF(? > `last_read_id`, NOW(), `last_read_at`),
					`last_read_id` = GREATEST(`last_read_id`, ?)
				WHERE `conv_id` = ? AND `username` = ?
			", [$lastId, $lastId, (int) $convId, $this->cuser]);
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
		[$files, $fileError] = $this->uploads->upload_files(false, self::MAX_CHAT_FILE_BYTES, self::MAX_CHAT_TOTAL_BYTES);
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
			$row = $this->uploads->upload_store('chats', $up);
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
		// your own message counts as read (so does everything before it: you
		// saw it when you replied), and you are no longer typing
		$this->db->query("
			UPDATE tbl_conversation_member
			SET `last_read_at` = IF(? > `last_read_id`, NOW(), `last_read_at`),
				`last_read_id` = GREATEST(`last_read_id`, ?),
				`typing_at` = NULL
			WHERE `conv_id` = ? AND `username` = ?
		", [$msgId, $msgId, $convId, $this->cuser]);
		return $msgId;
	}

	//CHATS - TYPING: the page pings while you type (typing=1) and clears it
	// when the box is emptied or you leave the chat (typing=0). Only touches
	// your own row, and only in a chat you belong to.
	public function conv_typing() {
		$convId = (int) $this->request->getPost('conv_id');
		$typing = (int) $this->request->getPost('typing') === 1;
		$this->db->query("
			UPDATE tbl_conversation_member SET `typing_at` = " . ($typing ? 'NOW()' : 'NULL') . "
			WHERE `conv_id` = ? AND `username` = ?
		", [$convId, $this->cuser]);
		return ['ok' => true];
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
		$file['path'] = $this->uploads->upload_path('chats', $file['file_name']);
		$file['is_image'] = (int) $file['is_image'];
		return $file['path'] === null ? null : $file;
	}

} //end main class
?>
