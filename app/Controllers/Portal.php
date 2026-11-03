<?php

namespace App\Controllers;

/**
 * IFAD Portal home: pick the system to use. Every system in Config\Systems
 * is listed; those the user can open (ifad_can_open) link to their start
 * page, the rest show "No access" or "Coming soon". Straight after signing
 * in, someone who can open only one system goes right into it. Inside a
 * system, the topbar's system tabs switch to another in one click.
 */
class Portal extends BaseController
{
    public function index()
    {
        $open = ifad_open_systems();
        if (session()->getFlashdata('ifad_after_login') && count($open) === 1) {
            return redirect()->to(site_url(ifad_home($open[0])));
        }

        $systems = [];
        foreach (ifad_systems() as $key => $system) {
            $canOpen = in_array($key, $open, true);
            $systems[$key] = $system + [
                'url' => $canOpen ? site_url(ifad_home($key)) : '',
                'why' => $canOpen ? '' : ($system['status'] === 'live' ? 'No access' : 'Coming soon'),
            ];
        }

        return view('portal/portal-main', [
            'user' => fods_current_user(),
            'systems' => $systems,
            'openCount' => count($open),
        ]);
    }
}
