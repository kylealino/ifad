<?php
namespace App\Models;
use CodeIgniter\Model;

class MyUACSModel extends Model
{

    protected $db;

    public function __construct(){
        parent::__construct();
        $this->session = session();
        $this->request = \Config\Services::request();
		$this->db = \Config\Database::connect();
		$this->cuser = $this->session->get('__xsys_myuserzicas__');
        
    }

	public function uacs_save() { 
		$recid = $this->request->getPostGet('recid');
		$allotment_class = $this->request->getPostGet('allotment_class');
		$object_code = $this->request->getPostGet('object_code');
		$sub_object_code = $this->request->getPostGet('sub_object_code');
		$uacs_code = $this->request->getPostGet('uacs_code');

		if (empty($allotment_class)) {
			echo "
			<script>
			toastr.error('Allotment Class is required!', 'Oops!', {
					progressBar: true,
					closeButton: true,
					timeOut:2000,
				});
			</script>
			";
			die();
		}
		if (empty($object_code)) {
			echo "
			<script>
			toastr.error('Object code is required!', 'Oops!', {
					progressBar: true,
					closeButton: true,
					timeOut:2000,
				});
			</script>
			";
			die();
		}
		if (empty($sub_object_code)) {
			echo "
			<script>
			toastr.error('Sub-object Code is required!', 'Oops!', {
					progressBar: true,
					closeButton: true,
					timeOut:2000,
				});
			</script>
			";
			die();
		}
		if (empty($uacs_code)) {
			echo "
			<script>
			toastr.error('Code is required!', 'Oops!', {
					progressBar: true,
					closeButton: true,
					timeOut:2000,
				});
			</script>
			";
			die();
		}

		if (empty($recid)) {
			$query = $this->db->query("
				INSERT INTO `mst_uacs`(
					`allotment_class`,
					`object_code`,
					`sub_object_code`,
					`uacs_code`,
					`added_at`,
					`added_by`
				)
				VALUES(
					'$allotment_class',
					'$object_code',
					'$sub_object_code',
					'$uacs_code',
					NOW(),
					'{$this->cuser}'
				)
			");
			$status = "UACS Saved successfully";
			$color = "success";
		}else{
			$query = $this->db->query("
				UPDATE
					`mst_uacs`
				SET
					`allotment_class` = '$allotment_class',
					`object_code` = '$object_code',
					`sub_object_code` = '$sub_object_code',
					`uacs_code` = '$uacs_code'
				WHERE `recid` = '$recid'
			");
			$status = "UACS Updated successfully";
			$color = "info";
		}

		if ($query) {
			// Echo JavaScript to show the toast and then redirect
			echo "
			<script>
				toastr.$color('{$status}!', 'Well Done!', {
						progressBar: true,
						closeButton: true,
						timeOut:2500,
					});
				setTimeout(function() {
						window.location.href = 'myuacs?meaction=MAIN'; // Redirect to MAIN view
					}, 2500); // 2-second delay for user to see the toast
			</script>
			";
			exit; // Stop further PHP execution after the toast
		} else {
			// If there's an error, show an alert message
			echo "<script type='text/javascript'>
					alert('An error occurred while executing the query.');
				  </script>";
			exit;
		}
	}

	
} //end main class
?>