<?php
namespace App\Models;
use CodeIgniter\Model;

class MyADAModel extends Model
{

    protected $db;

    public function __construct(){
        parent::__construct();
        $this->session = session();
        $this->request = \Config\Services::request();
		$this->db = \Config\Database::connect();
		$this->cuser = $this->session->get('__xsys_myuserzicas__');
        
    }

	public function ada_save() { 
		$recid = $this->request->getPostGet('recid');
		$adano = $this->request->getPostGet('adano');
		$dvno = $this->request->getPostGet('dvno');
		$particulars = $this->request->getPostGet('particulars');
		$ada_date = $this->request->getPostGet('ada_date');
		$payee_name = $this->request->getPostGet('payee_name');
		$payee_accountno = $this->request->getPostGet('payee_accountno');
		$net_amount = $this->request->getPostGet('net_amount');
		$certified_a = $this->request->getPostGet('certified_a');
		$position_a = $this->request->getPostGet('position_a');
		$certified_b = $this->request->getPostGet('certified_b');
		$position_b = $this->request->getPostGet('position_b');

		// var_dump(
		// 	$adano,
		// 	$dvno,
		// 	$particulars,
		// 	$ada_date,
		// 	$payee_name,
		// 	$payee_accountno,
		// 	$net_amount
		// );
		// die();
		// if (empty($payee_name)) {
		// 	echo "
		// 	<script>
		// 	toastr.error('Payee name is required!', 'Oops!', {
		// 			progressBar: true,
		// 			closeButton: true,
		// 			timeOut:2000,
		// 		});
		// 	</script>
		// 	";
		// 	die();
		// }


		if (empty($recid)) {
			//INSERTING HD DATA
			$query = $this->db->query("
				INSERT INTO tbl_ada (
					`adano`,
					`dvno`,
					`particulars`,
					`payee_name`,
					`payee_accountno`,
					`ada_date`,
					`net_amount`,
					`certified_a`,
					`position_a`,
					`certified_b`,
					`position_b`
				)
				VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
				[
					$adano,
					$dvno,
					$particulars,
					$payee_name,
					$payee_accountno,
					$ada_date,
					$net_amount,
					$certified_a,
					$position_a,
					$certified_b,
					$position_b
				]
			);

			$project_id = $this->db->insertID();

			$status = "ADA Saved Successfully!";
			$color = "success";
		}else{

			$query = $this->db->query("
				UPDATE tbl_ada
				SET
					`adano` = ?,
					`dvno` = ?,
					`particulars` = ?,
					`payee_name` = ?,
					`payee_accountno` = ?,
					`ada_date` = ?,
					`net_amount` = ?,
					`certified_a` = ?,
					`position_a` = ?,
					`certified_b` = ?,
					`position_b` = ?
				WHERE recid = ?
			", [
				$adano,
				$dvno,
				$particulars,
				$payee_name,
				$payee_accountno,
				$ada_date,
				$net_amount,
				$certified_a,
				$position_a,
				$certified_b,
				$position_b,
				$recid
			]);

			$project_id = $recid;

			$status = "ADA Updated Successfully!";
			$color = "info";
		}

		if ($query) {
			echo "
			<script>
				document.getElementById('submitBtn').disabled = true;
				toastr.{$color}('{$status}!', 'Well Done!', {
						progressBar: true,
						closeButton: true,
						timeOut:2500,
					});
				setTimeout(function() {
						window.location.href = 'myada?meaction=MAIN';
					}, 2500);
			</script>
			";
			exit;
		} else {
			echo "<script type='text/javascript'>
					alert('An error occurred while executing the query.');
				  </script>";
			exit;
		}
		
	}
	
} //end main class
?>