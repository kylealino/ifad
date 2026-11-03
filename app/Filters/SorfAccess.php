<?php
namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;

/**
 * SORF pages (IFAD Portal system 2): signed in (myauthuser runs first) and
 * given a SORF role in User Management. Everyone else goes back to the portal.
 */
class SorfAccess implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        if (ifad_can_open('sorf')) {
            return;
        }
        if (strtolower($request->getMethod()) === 'post' || $request->isAJAX()) {
            return service('response')->setStatusCode(403)->setJSON(['error' => 'You do not have access to the Document Request System.']);
        }
        session()->setFlashdata('ifad_notice', 'You do not have access to the Document Request System. Ask the administrator for a role there.');
        return redirect()->to(site_url('portal'));
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
    }
}
