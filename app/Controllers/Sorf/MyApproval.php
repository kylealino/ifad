<?php

namespace App\Controllers\Sorf;

class MyApproval extends SorfBaseController
{
    protected $myrequest;

    public function __construct()
    {
        $this->request = \Config\Services::request();
        $this->myrequest = model('App\Models\Sorf\MyRequestModel');
    }

    // For My Approval: requests that name you as "Noted by". Opening one goes
    // to the shared request page (myrequest?meaction=VIEW) where you return it
    // to the requester, or sign and approve it - which submits it to HR.
    public function index() {

        if (!drf_can(\Config\Sorf\Permissions::REQUEST_APPROVE)) {
            return $this->deny();
        }

        $meaction = $this->request->getPostGet('meaction');

        switch ($meaction) {
            case 'APPROVAL-SAVE':
                if (strtolower($this->request->getMethod()) !== 'post') {
                    return $this->response->setStatusCode(405)->setJSON(['error' => 'Not allowed.']);
                }
                $result = $this->myrequest->approval_save();
                return $this->response->setStatusCode(isset($result['error']) ? 422 : 200)->setJSON($result);

            case 'MAIN':
            default:
                return view('sorf/request/request-main', [
                    'rows' => $this->myrequest->request_list('approval'),
                    'mode' => 'approval',
                ]);
        }
    }

}
