<?php
namespace App\Models;
use CodeIgniter\Model;

// My Account: the signed-in user's own row in myua_user (never another user's;
// the row is always found by the session username, never by a posted id)
class MyAccountModel extends Model
{

	// Profile pictures: images only, one at a time (the page sends a 512 px square)
	const PHOTO_MAX_BYTES = 5 * 1048576;
	const PHOTO_FOLDER = 'avatars';
	const PASSWORD_MIN = 8;

    protected $db;

    public function __construct(){
        parent::__construct();
        $this->session = session();
        $this->request = \Config\Services::request();
		$this->db = \Config\Database::connect();
		$this->cuser = $this->session->get('__xsys_myuserzicas__');
		$this->uploads = model('App\Models\MyUploadsModel');
    }

	private function me() {
		return $this->db->query("SELECT * FROM myua_user WHERE `username` = ? LIMIT 1", [$this->cuser])->getRowArray();
	}

	private function errorOut($msg) {
		echo "
		<script>
		toastr.error(" . json_encode($msg, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) . ", 'Oops!', {
				progressBar: true,
				closeButton: true,
				timeOut:2500,
			});
		</script>
		";
		die();
	}

	private function done($status, $color = 'info') {
		echo "
		<script>
			toastr.$color('{$status}!', 'Well Done!', {
					progressBar: true,
					closeButton: true,
					timeOut:2000,
				});
			setTimeout(function() {
					window.location.href = 'myaccount?meaction=MAIN';
				}, 1000); // reload after 1 s; the toast still runs its 2.5 s (fods-flash.js carries it over)
		</script>
		";
		exit;
	}

	//MY ACCOUNT - SAVE DETAILS (name, position, office, contact)
	public function account_save() {
		$full_name = trim((string) $this->request->getPost('full_name'));
		$position = trim((string) $this->request->getPost('position'));
		$division = trim((string) $this->request->getPost('division'));
		$section = trim((string) $this->request->getPost('section'));
		$email = trim((string) $this->request->getPost('email'));
		$contact_no = trim((string) $this->request->getPost('contact_no'));

		$me = $this->me();
		if (empty($me)) { $this->errorOut('Your account was not found. Please sign in again.'); }

		// same required fields as User Management
		if ($full_name === '') { $this->errorOut('Full name is required!'); }
		if ($position === '') { $this->errorOut('Position is required!'); }
		if ($division === '') { $this->errorOut('Division is required!'); }
		if ($section === '') { $this->errorOut('Section is required!'); }
		if (mb_strlen($full_name) > 50 || mb_strlen($position) > 50 || mb_strlen($division) > 50 || mb_strlen($section) > 100) {
			$this->errorOut('A detail is too long (full name, position and division up to 50 characters, section up to 100).');
		}
		if ($email !== '' && (mb_strlen($email) > 100 || !filter_var($email, FILTER_VALIDATE_EMAIL))) {
			$this->errorOut('Enter a valid email address.');
		}
		if ($contact_no !== '' && (mb_strlen($contact_no) > 50 || !preg_match('/^[0-9+()\-.\s\/]+$/', $contact_no))) {
			$this->errorOut('Contact no. can only have numbers, spaces and + ( ) - . /');
		}

		// A full name belongs to one user only: approvals match signatories on it.
		// Checked when the name is changed, so users who already share one can still save.
		if (mb_strtoupper(trim((string) $me['full_name'])) !== mb_strtoupper($full_name)) {
			$dup = $this->db->query("
				SELECT `recid` FROM myua_user WHERE UPPER(TRIM(`full_name`)) = UPPER(?) AND `recid` <> ? LIMIT 1
			", [$full_name, (int) $me['recid']])->getRowArray();
			if ($dup) { $this->errorOut('Another user already has this full name.'); }
		}

		$before = MyHistoryModel::row('myua_user', $me['recid']);
		$query = $this->db->query("
			UPDATE myua_user
			SET
				`full_name` = ?,
				`position` = ?,
				`division` = ?,
				`section` = ?,
				`email` = ?,
				`contact_no` = ?
			WHERE `recid` = ?
		", [
			$full_name,
			$position,
			$division,
			$section,
			$email,
			$contact_no,
			$me['recid']
		]);

		if (!$query) {
			echo "<script type='text/javascript'>
					alert('An error occurred while executing the query.');
				  </script>";
			exit;
		}
		MyHistoryModel::log('user', $me['recid'], 'UPDATED', 'Updated on My Account',
			MyHistoryModel::diff($before, MyHistoryModel::row('myua_user', $me['recid'])), $me['username']);
		$this->done('Your details were saved');
	}

	//MY ACCOUNT - CHANGE PASSWORD (current password required)
	public function password_save() {
		$current = (string) $this->request->getPost('current_password');
		$new = (string) $this->request->getPost('new_password');
		$confirm = (string) $this->request->getPost('confirm_password');

		$me = $this->me();
		if (empty($me)) { $this->errorOut('Your account was not found. Please sign in again.'); }

		// stored as SHA-512 hex (MyLogIn::Verify_Password, User Management)
		if ($current === '' || !hash_equals(strtolower((string) $me['hash_password']), hash('sha512', $current))) {
			$this->errorOut('Your current password is not correct.');
		}
		if (mb_strlen($new) < self::PASSWORD_MIN) {
			$this->errorOut('The new password needs at least ' . self::PASSWORD_MIN . ' characters.');
		}
		if ($new !== $confirm) {
			$this->errorOut('The new passwords do not match.');
		}
		if ($new === $current) {
			$this->errorOut('The new password is the same as the current one.');
		}

		$query = $this->db->query("UPDATE myua_user SET `hash_password` = ? WHERE `recid` = ?", [hash('sha512', $new), $me['recid']]);
		if (!$query) {
			echo "<script type='text/javascript'>
					alert('An error occurred while executing the query.');
				  </script>";
			exit;
		}
		MyHistoryModel::log('user', $me['recid'], 'UPDATED', 'Password changed on My Account', null, $me['username']);
		$this->done('Your password was changed');
	}

	//MY ACCOUNT - PROFILE PICTURE: upload (replaces the old file)
	public function photo_upload() {
		$me = $this->me();
		if (empty($me)) {
			return ['error' => 'Your account was not found. Please sign in again.'];
		}
		[$files, $error] = $this->uploads->upload_files(true, self::PHOTO_MAX_BYTES, 0, 1);
		if ($error !== '') {
			return ['error' => $error];
		}
		if (empty($files)) {
			return ['error' => 'Choose a picture first.'];
		}

		$row = $this->uploads->upload_store(self::PHOTO_FOLDER, $files[0]);
		$query = $this->db->query("UPDATE myua_user SET `avatar` = ? WHERE `recid` = ?", [$row['file_name'], $me['recid']]);
		if (!$query) {
			$this->delete_file($row['file_name']);
			return ['error' => 'The picture could not be saved. Please try again.'];
		}
		$this->delete_file((string) ($me['avatar'] ?? ''));
		MyHistoryModel::log('user', $me['recid'], 'UPDATED', 'Profile picture changed', null, $me['username']);
		return ['ok' => 1, 'url' => fods_avatar_url($me['username'], $row['file_name'])];
	}

	//MY ACCOUNT - PROFILE PICTURE: remove (back to initials)
	public function photo_remove() {
		$me = $this->me();
		if (empty($me)) {
			return ['error' => 'Your account was not found. Please sign in again.'];
		}
		if (trim((string) ($me['avatar'] ?? '')) === '') {
			return ['ok' => 1];
		}
		$this->db->query("UPDATE myua_user SET `avatar` = '' WHERE `recid` = ?", [$me['recid']]);
		$this->delete_file($me['avatar']);
		MyHistoryModel::log('user', $me['recid'], 'UPDATED', 'Profile picture removed', null, $me['username']);
		return ['ok' => 1];
	}

	//MY ACCOUNT - PROFILE PICTURE FILE of any user (null when they have none)
	public function photo_file($username) {
		if (trim($username) === '') {
			return null;
		}
		$u = $this->db->query("SELECT `avatar` FROM myua_user WHERE `username` = ? LIMIT 1", [$username])->getRowArray();
		$name = trim((string) ($u['avatar'] ?? ''));
		$path = $name === '' ? null : $this->uploads->upload_path(self::PHOTO_FOLDER, $name);
		if ($path === null) {
			return null;
		}
		$ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
		$mime = MyUploadsModel::IMAGE_TYPES[$ext] ?? null;
		return $mime === null ? null : ['path' => $path, 'mime_type' => $mime, 'ext' => $ext];
	}

	private function delete_file($name) {
		$path = trim((string) $name) === '' ? null : $this->uploads->upload_path(self::PHOTO_FOLDER, $name);
		if ($path !== null) {
			@unlink($path);
		}
	}

} //end main class
?>
