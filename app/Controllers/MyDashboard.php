<?php

namespace App\Controllers;

class MyDashboard extends BaseController
{
    public function __construct(){
		$this->request = \Config\Services::request();
        $this->db = \Config\Database::connect();
        $this->session = session();
        $this->cuser = $this->session->get('__xsys_myuserzicas__');
	}

    public function index(){
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

    public function getSaobSessionData() {
        $session = session();

        $data = [
            'grandtotal_total_project_budget' => $session->get('grandtotal_total_project_budget') ?? 0,
            'grandtotal_todate_grand_total' => $session->get('grandtotal_todate_grand_total') ?? 0,
            'grandtotal_grand_unobligated' => $session->get('grandtotal_grand_unobligated') ?? 0,
            'grandtotal_grand_percentage_minus' => $session->get('grandtotal_grand_percentage_minus') ?? 0,
        ];

        return $this->response->setJSON($data);
    }

}
