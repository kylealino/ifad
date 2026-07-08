<?php
namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;

class UacsApi extends ResourceController
{
    protected $format = 'json'; // ensures API returns JSON
    
    public function __construct(){
		$this->request = \Config\Services::request();
        $this->myabstract = model('App\Models\MyAbstractModel');
        $this->session = session();
        $this->db = \Config\Database::connect();
        $this->cuser = $this->session->get('__xsys_myuserzicas__');
	}


    public function index()
    {
        // Run your existing query
        $uacsQuery = $this->db->query("
            SELECT
                `recid`,
                `allotment_class`,
                `object_code`,
                `sub_object_code`,
                `uacs_code`,
                `added_at`,
                `added_by`
            FROM `mst_uacs`
            ORDER BY recid DESC
        ");

        $uacsData = $uacsQuery->getResultArray();

        // Return JSON response
        return $this->respond($uacsData);
    }
}