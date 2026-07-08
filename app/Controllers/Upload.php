<?php

namespace App\Controllers;

use CodeIgniter\Controller;

class Upload extends Controller
{
    public function __construct()
	{
		$this->request = \Config\Services::request();
        // $this->mybudgetallotment = model('App\Models\MyBudgetAllotmentModel');
        $this->db = \Config\Database::connect();
        $this->session = session();
        $this->cuser = $this->session->get('__xsys_myuserzicas__');
	}
    
    public function index()
    {
        $meaction = $this->request->getPostGet('meaction');
        switch ($meaction) {

            case 'BUDGET-UPLOAD': 

                $this->uploadFile();
                return redirect()->to('mybudgetallotment?meaction=MAIN');
                break;


        }
    }

    public function uploadFile(){
        $file = $this->request->getFile('userfile');
        $trxno = $this->request->getPostGet('hd_trxno');

        $query = $this->db->query("SELECT `recid` FROM tbl_budget_hd WHERE `trxno` = '$trxno' ");
        if ($query->getNumRows() > 0) {
            $rw = $query->getRowArray();
            $recid = $rw['recid'];
        } 

        if (!$file || !$file->isValid()) {
            return redirect()->to('mybudgetallotment?meaction=MAIN')
                            ->with('error', 'No file selected or invalid file.');
        }

        $uploadPath = FCPATH . 'uploads/';

        if (!is_dir($uploadPath)) {
            mkdir($uploadPath, 0777, true);
        }

        $originalName = pathinfo($file->getName(), PATHINFO_FILENAME);

        $newFileName = $originalName . '_' . $file->getRandomName();
        $file->move($uploadPath, $newFileName);

        $query = $this->db->query("
        INSERT INTO `tbl_budget_attachments`(
            `trxno`,
            `file_name`,
            `added_by`
        )
        VALUES(
            '$trxno',
            '$newFileName',
            '{$this->cuser}'
        )
        ");

        $status = 'File uploaded successfully';
        $redirectUrl = 'mybudgetallotment?meaction=MAIN&recid=' . $recid;
        
        echo "
            <script>
                alert('$status');
                window.location.href = '$redirectUrl';
            </script>
        ";
        exit; // Stop execution
    }


    public function viewFile($fileName)
    {
        $filePath = WRITEPATH . 'uploads/' . $fileName;
    
        if (file_exists($filePath)) {
            return $this->response->download($filePath, null)->setFileName($fileName)->setContentType(mime_content_type($filePath));
        } else {
            return "File not found!";
        }
    }
    
}
