<?php

namespace App\Controllers;

/**
 * Topbar notifications (the bell): INBOX returns what is waiting for the
 * signed-in user across the systems they can open (MyNotificationsModel).
 * Everyone signed in has it; there is no page, so anything else goes back
 * to the portal.
 */
class MyNotifications extends BaseController
{
    public function index()
    {
        if ($this->request->getPostGet('meaction') === 'INBOX') {
            $fresh = $this->request->getGet('fresh') === '1';
            return $this->response->setJSON(model('App\Models\MyNotificationsModel')->inbox($fresh));
        }
        return redirect()->to(site_url('portal'));
    }
}
