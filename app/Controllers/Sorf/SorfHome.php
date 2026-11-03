<?php

namespace App\Controllers\Sorf;

/**
 * sorf/ itself: the first SORF page the user's role opens
 * (the sorfaccess filter already made sure there is one).
 */
class SorfHome extends SorfBaseController
{
    public function index()
    {
        return redirect()->to(site_url(drf_home_path() ?? 'portal'));
    }
}
