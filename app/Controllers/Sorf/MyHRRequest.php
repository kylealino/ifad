<?php

namespace App\Controllers\Sorf;

class MyHRRequest extends SorfBaseController
{
    protected $myrequest;

    public function __construct()
    {
        $this->request = \Config\Services::request();
        $this->myrequest = model('App\Models\Sorf\MyRequestModel');
    }

    // HR desk: every request that reached HR - signed by the requester and
    // approved by its "Noted by" signatory. Opening one goes to the shared request page
    // (myrequest?meaction=VIEW) where HR also updates the status.
    public function index() {

        if (!drf_can(\Config\Sorf\Permissions::HR_VIEW)) {
            return $this->deny();
        }

        $meaction = $this->request->getPostGet('meaction');

        switch ($meaction) {
            case 'STATUS-SAVE':
                if (strtolower($this->request->getMethod()) !== 'post') {
                    return $this->response->setStatusCode(405)->setJSON(['error' => 'Not allowed.']);
                }
                $result = $this->myrequest->status_save();
                return $this->response->setStatusCode(isset($result['error']) ? 422 : 200)->setJSON($result);

            case 'MAIN':
            default:
                return view('sorf/request/request-main', [
                    'rows' => $this->myrequest->request_list('hr'),
                    'mode' => 'hr',
                ]);
        }
    }

}
