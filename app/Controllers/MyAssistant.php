<?php

namespace App\Controllers;

use CodeIgniter\Controller;

class MyAssistant extends BaseController
{
    public function __construct()
    {
        $this->request = \Config\Services::request();
        $this->myassistant = model('App\Models\MyAssistantModel');
        $this->session = session();
        $this->db = \Config\Database::connect();
        $this->cuser = $this->session->get('__xsys_myuserzicas__');
    }

    // Everyone signed in can use the assistant (the route's myauthuser filter
    // keeps everyone else out). What it can look up still follows the user's
    // own module access, checked in the model for each lookup.
    public function index() {

        $meaction = $this->request->getPostGet('meaction');

        switch ($meaction) {
            // One question (+ the conversation so far) -> one answer
            case 'ASK':
                if (strtolower($this->request->getMethod()) !== 'post') {
                    return $this->response->setStatusCode(405)->setJSON(['error' => 'Use POST.']);
                }
                // (the model frees the session once it has counted the question,
                // so a slow answer doesn't hold up the user's other tabs)
                try {
                    $result = $this->myassistant->ask();
                } catch (\Throwable $e) {
                    // the real cause goes to the log; the user gets a plain message
                    log_message('critical', 'Assistant: ' . get_class($e) . ': ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
                    $result = ['error' => 'The assistant hit a server error. Ask your administrator to check the FODS log (writable/logs).'];
                }
                return $this->response->setStatusCode(isset($result['error']) ? 422 : 200)->setJSON($result);

            // The assistant lives in the floating panel on every page
            // (templates/myfooter.php); there is no page of its own
            default:
                return redirect()->to('mydashboard');
        }
    }

}
