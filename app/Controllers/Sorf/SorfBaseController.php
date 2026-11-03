<?php

namespace App\Controllers\Sorf;

use App\Controllers\BaseController;

/**
 * Base of the SORF (Document Request System) pages inside the IFAD Portal.
 * The 'sorfaccess' route filter has already checked the user may open SORF.
 */
abstract class SorfBaseController extends BaseController
{
    // A page the user's SORF role does not open: AJAX gets a 403, a page
    // visit goes to their SORF start page (or the portal when there is none)
    protected function deny()
    {
        if (strtolower($this->request->getMethod()) === 'post') {
            return $this->response->setStatusCode(403)->setJSON(['error' => 'Your role does not allow this. Reload the page.']);
        }
        $home = drf_home_path();
        return redirect()->to($home !== null && strpos(uri_string(), $home) !== 0 ? site_url($home) : site_url('portal'));
    }
}
