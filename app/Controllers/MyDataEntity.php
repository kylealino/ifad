<?php
namespace App\Controllers;
class MyDataEntity extends BaseController
{
	
	public function __construct()
	{
		$this->request = \Config\Services::request();
		$this->db = \Config\Database::connect();
	}
	
	public function index() { 
		$meaction = $this->request->getGetPost('meaction');
		switch ($meaction) {
			case 'DATUM-FUNDCLUSTER':
				$this->lookup_fundcluster();
				break;
		
		}
		
	}

	public function lookup_fundcluster() { 
		$term = $this->request->getPostGet('term');

		$stroptn = " (fund_cluster_code like '%{$term}%') ";
		
		$autoCompleteResult = array();

		$query = $this->db->query("
		SELECT
			`fund_cluster_code` AS __mdata
		FROM
			`tbl_fundcluster`
		where {$stroptn} LIMIT 50
		");
	
		if($query->getNumRows() > 0) { 
			$rrec = $query->getResultArray();
			foreach($rrec as $row): 
				array_push($autoCompleteResult,array("value" => $row['__mdata']));
			endforeach;
		}
		$query->freeResult();
		echo json_encode($autoCompleteResult);
	}

}

