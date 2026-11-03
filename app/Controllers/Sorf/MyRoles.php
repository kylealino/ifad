<?php

namespace App\Controllers\Sorf;

use Config\Sorf\Permissions;

class MyRoles extends SorfBaseController
{
    protected $myroles;

    public function __construct()
    {
        $this->request = \Config\Services::request();
        $this->myroles = model('App\Models\Sorf\MyRoleModel');
    }

    // Administration - Roles: what each role may do (Config\Permissions),
    // which role new accounts get, roles switched off or deleted.
    public function index() {

        if (!drf_can(Permissions::ADMIN_ROLES)) {
            return $this->deny();
        }

        $meaction = $this->request->getPostGet('meaction');

        // changes only ever come from a form post
        if (in_array($meaction, ['ROLE-SAVE', 'ROLE-DELETE'], true) && strtolower($this->request->getMethod()) !== 'post') {
            return $this->response->setStatusCode(405)->setJSON(['error' => 'Not allowed.']);
        }

        switch ($meaction) {
            case 'ROLE-SAVE':
                return $this->json($this->myroles->role_save());

            case 'ROLE-DELETE':
                return $this->json($this->myroles->role_delete());

            //ROLE FORM - new, or ?recid= to change one
            case 'FORM':
                $role = null;
                $recid = (int) $this->request->getGet('recid');
                if ($recid > 0) {
                    $role = $this->myroles->role_get($recid);
                    if ($role === null) {
                        return redirect()->to(sorf_url('myroles'));
                    }
                }
                return view('sorf/admin/role-form', [
                    'role' => $role,
                    'permissions' => config(\Config\Sorf\Permissions::class),
                    'isMine' => $role !== null && $role['recid'] === drf_current_user()['role_id'],
                ]);

            case 'MAIN':
            default:
                return view('sorf/admin/roles-main', [
                    'roles' => $this->myroles->role_list(),
                    'permissions' => config(\Config\Sorf\Permissions::class),
                ]);
        }
    }

    // Model results carry 'error' on failure -> 422 so the JS shows it
    private function json(array $result) {
        return $this->response->setStatusCode(isset($result['error']) ? 422 : 200)->setJSON($result);
    }

}
