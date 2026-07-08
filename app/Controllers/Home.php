<?php

namespace App\Controllers;
use CodeIgniter\HTTP\Response;
class Home extends BaseController
{
    public function __construct(){
		$this->request = \Config\Services::request();
        $this->db = \Config\Database::connect();
        $this->session = session();
        $this->cuser = $this->session->get('__xsys_myuserzicas__');
	}
    public function index(): string
    {
        if (!session('__xsys_myuserzicas_is_logged__')) {
            return view('MyLogin');
        }
    

        // Check if cuser starts with "NAMD"
        // if (str_starts_with($this->cuser, 'BS')) {
        //     return view('MyDashboard');       // For users starting with NAMD
        // } else {
        //     return view('MyDashboardDev');    // For all others
        // }

        return view('MyDashboardDev'); 
    }
    
}
