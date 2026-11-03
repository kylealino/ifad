<?php namespace App\Controllers;

use CodeIgniter\Controller;

class MyLogIn extends BaseController
{

	public function __construct()
	{
        $this->request = \Config\Services::request();
        $this->db = \Config\Database::connect();
        $this->session = session();
	}

    public function index()
    {
        echo view('mylogin');
    }

public function auth()
{
    $meusername = $this->request->getPostGet('MyUsername');
    $password = $this->request->getPostGet('MyPassword');
    $data = $this->Verify_User($meusername)->getRowArray();

    if($data) {
        $passdb = $data['hash_password'];
        $verify_pass = $this->Verify_Password($passdb, $password);
        if($verify_pass) {
            // a fresh session id at sign-in, and the username exactly as it is
            // saved (the portal's systems compare usernames exactly)
            $this->session->regenerate(true);
            $ses_data = array(
                '__xsys_myuserzicas_is_logged__' => TRUE,
                '__xsys_myuserzicas__' => $data['username']
            );

            $this->session->set($ses_data);
            // the IFAD Portal; someone with a single system goes straight into it
            $this->session->setFlashdata('ifad_after_login', true);
            return redirect()->to('/portal');
        } else {
            // Set flashdata and debug
            $this->session->setFlashdata('mesyszicas_memsg_login', 'Wrong Password');
            log_message('error', 'Flashdata set: Wrong Password'); // Check logs
            return redirect()->to('/');
        }
    } else {
        // Set flashdata and debug
        $this->session->setFlashdata('mesyszicas_memsg_login', 'User Name not Found');
        log_message('error', 'Flashdata set: User Name not Found'); // Check logs
        return redirect()->to('/');
    }
}

    public function logout()
    {
        $this->session->destroy();
        return redirect()->to('/');
    }

    public function Verify_User($cuser='') {
		$q = $this->db->query("select `username`,`hash_password` FROM myua_user where username = ? and `is_active` = 1 limit 1 ",[$cuser]);
		return $q;
	}

    public function Verify_Password($cuserpassdb='',$cuserpass='') {
		// accounts brought over from SORF keep their bcrypt hash until the
		// password is next set in User Management (which saves SHA-512)
		if (strncmp((string) $cuserpassdb, '$2y$', 4) === 0) {
			return password_verify((string) $cuserpass, (string) $cuserpassdb) ? 1 : 0;
		}
		$query = $this->db->query("select if(? = sha2(?,512),1,0) metruefalse limit 1 ",[$cuserpassdb,$cuserpass]);
		$row = $query->getRowArray();
		$query->freeResult();
		return $row['metruefalse'];
	}
}
