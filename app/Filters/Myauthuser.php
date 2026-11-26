<?php
namespace App\Filters;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;

class Myauthuser implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        if (!session('__xsys_myuserzicas_is_logged__'))
        {
            return redirect()
                ->to('/');
        }

        // A user deactivated in User Management is signed out on their next request,
        // not only at their next login
        $username = session('__xsys_myuserzicas__');
        $user = \Config\Database::connect()->query(
            "SELECT `is_active` FROM `myua_user` WHERE `username` = ? ORDER BY `is_active` DESC LIMIT 1",
            [$username]
        )->getRowArray();
        if (!$user || (string) $user['is_active'] !== '1') {
            session()->remove(['__xsys_myuserzicas_is_logged__', '__xsys_myuserzicas__']);
            return redirect()->to('/');
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {

    }
}  //end main class
