<?php

namespace App\Controllers;

class MyDashboard extends BaseController
{
    public function __construct(){
		$this->request = \Config\Services::request();
        $this->mydashboard = model('App\Models\MyDashboardModel');
        $this->db = \Config\Database::connect();
        $this->session = session();
        $this->cuser = $this->session->get('__xsys_myuserzicas__');
	}

    public function index(){
        if (!session('__xsys_myuserzicas_is_logged__')) {
            return view('MyLogin');
        }

        $meaction = $this->request->getPostGet('meaction');

        switch ($meaction) {
            // Drill-down for one payee (loaded into the side panel)
            case 'PAYEE-FLOW':
                $payee = (string) $this->request->getGet('payee');
                return $this->response->setJSON([
                    'payee' => $payee,
                    'obligations' => $payee === '' ? [] : $this->mydashboard->payeeFlow($payee),
                ]);

            default:
                return $this->loadMainView();
        }
    }

    private function loadMainView() {
        $years = $this->mydashboard->years();
        $year = (int) $this->request->getGet('year');
        $year = in_array($year, $years, true) ? $year : null;

        return view('dashboard/dashboard-main', [
            'years' => $years,
            'year' => $year,
            'summary' => $this->mydashboard->summary($year),
            'monthly' => $this->mydashboard->monthly($year),
            'payees' => $this->mydashboard->payees($year),
            'recent' => $this->mydashboard->recent(8),
        ]);
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
