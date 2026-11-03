<?php

namespace App\Controllers;

use CodeIgniter\Controller;

class MyUserManagement extends BaseController
{
    public function __construct()
	{
		$this->request = \Config\Services::request();
        $this->myua = model('App\Models\MyUserManagementModel');
        $this->db = \Config\Database::connect();
        $this->session = session();
        $this->cuser = $this->session->get('__xsys_myuserzicas__');
	}

    public function index() {

        $meaction = $this->request->getPostGet('meaction');

        switch ($meaction) {
            case 'MAIN':
                if (fods_has_access(\Config\AccessCodes::USERMGMT_VIEW)) {
                    return $this->loadMainView();
                    break;
                }else {
                    return view('errors/html/access-restricted');
                }

            case 'MAIN-SAVE':
                $this->myua->user_save();
                return redirect()->to('myua?meaction=MAIN');
                break;

            case 'USER-TOGGLE-ACTIVE':
                $this->myua->user_toggle_active();
                return redirect()->to('myua?meaction=MAIN');
                break;

            case 'ROLE-SAVE':
                $this->myua->role_save();
                return redirect()->to('myua?meaction=MAIN');
                break;

            case 'ROLE-TOGGLE-ACTIVE':
                $this->myua->role_toggle_active();
                return redirect()->to('myua?meaction=MAIN');
                break;

            case 'ROLE-DELETE':
                $this->myua->role_delete();
                return redirect()->to('myua?meaction=MAIN');
                break;

            case 'ROLE-ACCESS-SAVE':
                $this->myua->role_access_save();
                return redirect()->to('myua?meaction=MAIN');
                break;
        }
    }


    private function loadMainView() {

        $myuaquery = $this->db->query("
            SELECT u.*, r.role_name
            FROM myua_user u
            LEFT JOIN tbl_roles r ON r.recid = u.role_id
            ORDER BY u.full_name ASC
        ");
        $myuadata = $myuaquery->getResultArray();

        $rolesquery = $this->db->query("
            SELECT r.*,
                (SELECT COUNT(*) FROM myua_user u WHERE u.role_id = r.recid) AS user_count,
                (SELECT GROUP_CONCAT(ra.access_code) FROM tbl_role_access ra WHERE ra.role_id = r.recid) AS access_codes
            FROM tbl_roles r
            ORDER BY r.role_name ASC
        ");
        $rolesdata = $rolesquery->getResultArray();

        $modulesquery = $this->db->query("SELECT `access_code`, `access_name` FROM tbl_access_modules ORDER BY `access_code` ASC");
        $modulesdata = $modulesquery->getResultArray();

        // Group codes by module family (the code minus its 3-digit action
        // suffix, e.g. 26001..26004 => family "26" = ORS) so the permission
        // editor shows one card per module, in sidebar order. Names are
        // "<Module> - <Action>": the module names the card, the action
        // labels each checkbox.
        $modulefamilies = [];
        foreach ($modulesdata as $mod) {
            $code = $mod['access_code'];
            $family = substr($code, 0, -3);
            [$module, $action] = array_pad(explode(' - ', $mod['access_name'], 2), 2, '');
            if (!isset($modulefamilies[$family])) {
                $modulefamilies[$family] = [
                    'label' => trim($module),
                    'items' => [],
                ];
            }
            $mod['action'] = $action !== '' ? trim($action) : $mod['access_name'];
            $modulefamilies[$family]['items'][] = $mod;
        }
        // Show the groups in sidebar order (Budget Approval, family 36, sits
        // right after Budget Allotment); anything not in the sidebar goes last
        $navOrder = [];
        foreach (config(\Config\Navigation::class)->groups as $group) {
            foreach ($group['items'] as $item) {
                foreach ((array) $item['access'] as $code) {
                    $navOrder[substr($code, 0, -3)] ??= count($navOrder);
                }
            }
        }
        uksort($modulefamilies, fn ($a, $b) => [$navOrder[$a] ?? PHP_INT_MAX, (int) $a] <=> [$navOrder[$b] ?? PHP_INT_MAX, (int) $b]);

        $tab = $this->request->getPostGet('tab');

        // IFAD Portal: each account's role in the Document Request System (SORF),
        // kept in the SORF database (null = SORF's database cannot be reached)
        $sorfRoles = null;
        $sorfMembers = [];
        try {
            $members = model('App\Models\Sorf\SorfMemberModel');
            $sorfRoles = $members->roles();
            $sorfMembers = $members->member_roles();
        } catch (\Throwable $e) {
            log_message('error', 'User Management - SORF roles: ' . $e->getMessage());
        }

        return view('user-management/ua-main', [
            'myuadata' => $myuadata,
            'rolesdata' => $rolesdata,
            'sorfRoles' => $sorfRoles,
            'sorfMembers' => $sorfMembers,
            'modulefamilies' => $modulefamilies,
            'active_tab' => ($tab === 'roles') ? 'roles' : 'users',
        ]);
    }


}
