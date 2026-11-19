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
			fods_require_access(\Config\AccessCodes::UACS_ADD, 'Saving');
			$query = $this->db->query("
				INSERT INTO `mst_uacs`(
					`allotment_class`,
					`object_code`,
					`sub_object_code`,
					`uacs_code`,
					`added_at`,
					`added_by`
				)
				VALUES(?, ?, ?, ?, NOW(), ?)
			", [$allotment_class, $object_code, $sub_object_code, $uacs_code, $this->cuser]);
			if ($query) { MyHistoryModel::log('uacs', $this->db->insertID(), 'CREATED', '', null, $uacs_code); }
			$status = "UACS Saved successfully";
			$color = "success";
		}else{
			fods_require_access(\Config\AccessCodes::UACS_UPDATE, 'Updating');
			$before = MyHistoryModel::row('mst_uacs', $recid);
			$query = $this->db->query("
				UPDATE
					`mst_uacs`
				SET
					`allotment_class` = ?,
					`object_code` = ?,
					`sub_object_code` = ?,
					`uacs_code` = ?
				WHERE `recid` = ?
			", [$allotment_class, $object_code, $sub_object_code, $uacs_code, $recid]);
			if ($query) { MyHistoryModel::log('uacs', $recid, 'UPDATED', '', MyHistoryModel::diff($before, MyHistoryModel::row('mst_uacs', $recid)), $uacs_code); }
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
						timeOut:2000,
					});
				setTimeout(function() {
						window.location.href = 'myuacs?meaction=MAIN'; // Redirect to MAIN view
					}, 1000); // reload after 1 s; the toast still runs its 2.5 s (fods-flash.js carries it over)
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
