<?php

namespace App\Controllers;

use CodeIgniter\Controller;

// My Account (profile dropdown > My account): every signed-in user edits their own
// details, changes their password and sets a profile picture. No access code: it
// only ever touches the signed-in user's own myua_user row (role and signatory flags
// stay with User Management).
class MyAccount extends BaseController
{
    public function __construct()
	{
		$this->request = \Config\Services::request();
        $this->myaccount = model('App\Models\MyAccountModel');
        $this->session = session();
        $this->db = \Config\Database::connect();
        $this->cuser = $this->session->get('__xsys_myuserzicas__');
	}

    public function index() {

        $meaction = $this->request->getPostGet('meaction');

        switch ($meaction) {
            case 'MAIN-SAVE':
                $this->myaccount->account_save();
                return redirect()->to('myaccount?meaction=MAIN');
                break;

            case 'PASSWORD-SAVE':
                $this->myaccount->password_save();
                return redirect()->to('myaccount?meaction=MAIN');
                break;

            // Profile picture: upload / remove take effect at once and answer JSON
            case 'PHOTO-UPLOAD':
                return $this->json($this->myaccount->photo_upload());

            case 'PHOTO-REMOVE':
                return $this->json($this->myaccount->photo_remove());

            // Any signed-in user's picture (topbar, chats), by username
            case 'PHOTO':
                return $this->photo($this->myaccount->photo_file((string) $this->request->getGet('u')));

            case 'MAIN':
            default:
                return $this->loadMainView();
        }
    }

    // Model results carry 'error' on failure -> 422 so the JS shows it
    private function json(array $result) {
        return $this->response->setStatusCode(isset($result['error']) ? 422 : 200)->setJSON($result);
    }

    // The URL carries the file name (fods_avatar_url), so the browser may keep it
    private function photo(?array $file) {
        if ($file === null) {
            return $this->response->setStatusCode(404)->setBody('Picture not found.');
        }
        return $this->response
            ->setHeader('Content-Type', $file['mime_type'])
            ->setHeader('Content-Disposition', 'inline; filename="avatar.' . $file['ext'] . '"')
            ->setHeader('Cache-Control', 'private, max-age=31536000, immutable')
            ->setBody(file_get_contents($file['path']));
    }

    private function loadMainView() {
        $user = fods_current_user();

        // Boxes this user signs in Approver Mapping (by full name): renaming
        // yourself stops them from matching, so the page says so
        $boxes = $this->db->query("
            SELECT DISTINCT `module`, `box`
            FROM tbl_approver_map
            WHERE `is_active` = 1 AND LOWER(TRIM(`full_name`)) = LOWER(TRIM(?))
            ORDER BY `module`, `box`
        ", [$user['full_name']])->getResultArray();

        return view('account/myaccount-main', [
            'account' => [
                'username' => $user['username'],
                'full_name' => $user['full_name'],
                'position' => $user['position'],
                'division' => $user['division'],
                'section' => $user['section'],
                'email' => $user['email'],
                'contact_no' => $user['contact_no'],
                'role_name' => $user['role_name'],
                'photo' => fods_avatar_url($user['username'], $user['avatar']),
                'signs' => count($boxes),
            ],
            'pageTitle' => 'My account',
        ]);
    }
}
