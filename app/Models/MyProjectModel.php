<?php
namespace App\Models;
use CodeIgniter\Model;

class MyProjectModel extends Model
{

    protected $db;

    public function __construct(){
        parent::__construct();
        $this->session = session();
        $this->request = \Config\Services::request();
		$this->db = \Config\Database::connect();
		$this->cuser = $this->session->get('__xsys_myuserzicas__');
        
    }

	public function project_save() { 
		$recid = $this->request->getPostGet('recid');
		$project_title = $this->request->getPostGet('project_title');
		$fund_cluster_code = $this->request->getPostGet('fund_cluster_code');
		$fundcluster_id = $this->request->getPostGet('fundcluster_id');
		$division_name = $this->request->getPostGet('division_name');
		$division_id = $this->request->getPostGet('division_id');
		$responsibility_code = $this->request->getPostGet('responsibility_code');
		$project_leader = $this->request->getPostGet('project_leader');
		$mfopaps_code = $this->request->getPostGet('mfopaps_code');

		if (empty($project_title)) {
			echo "
			<script>
			toastr.error('Project Title is required!', 'Oops!', {
					progressBar: true,
					closeButton: true,
					timeOut:2000,
				});
			</script>
			";
			die();
		}
		if (empty($fund_cluster_code)) {
			echo "
			<script>
			toastr.error('Fundcluster is required!', 'Oops!', {
					progressBar: true,
					closeButton: true,
					timeOut:2000,
				});
			</script>
			";
			die();
		}
		if (empty($division_name)) {
			echo "
			<script>
			toastr.error('Division is required!', 'Oops!', {
					progressBar: true,
					closeButton: true,
					timeOut:2000,
				});
			</script>
			";
			die();
		}
		if (empty($responsibility_code)) {
			echo "
			<script>
			toastr.error('Responsibility code is required!', 'Oops!', {
					progressBar: true,
					closeButton: true,
					timeOut:2000,
				});
			</script>
			";
			die();
		}
		if (empty($project_leader)) {
			echo "
			<script>
			toastr.error('Project Leader is required!', 'Oops!', {
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
				INSERT INTO tbl_reference_project (
					fundcluster_id,
					division_id,
					responsibility_code,
					project_leader,
					mfopaps_code,
					project_title,
					added_on,
					added_by,
					active_status
				) VALUES (
					?, ?, ?, ?, ?, ?, NOW(), ?, '1'
				)
			", [
				$fundcluster_id,
				$division_id,
				$responsibility_code,
				$project_leader,
				$mfopaps_code,
				$project_title,
				$this->cuser
			]);

			$status = "Project Saved successfully";
			$color = "success";
		} else {
			$query = $this->db->query("
				UPDATE tbl_reference_project
				SET
					fundcluster_id = ?,
					division_id = ?,
					responsibility_code = ?,
					project_leader = ?,
					mfopaps_code = ?,
					project_title = ?
				WHERE recid = ?
			", [
				$fundcluster_id,
				$division_id,
				$responsibility_code,
				$project_leader,
				$mfopaps_code,
				$project_title,
				$recid
			]);

			$status = "Project Updated successfully";
			$color = "info";
		}


		if ($query) {
			// Echo JavaScript to show the toast and then redirect
			echo "
			<script>
				document.getElementById('submitBtn').disabled = true;
				toastr.$color('{$status}!', 'Well Done!', {
						progressBar: true,
						closeButton: true,
						timeOut:2500,
					});
				setTimeout(function() {
						window.location.href = 'myproject?meaction=MAIN'; // Redirect to MAIN view
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
	
	public function payee_delete() { 
		$recid = $this->request->getPostGet('recid');

		$query = $this->db->query("
			DELETE FROM `tbl_payee` WHERE `recid` = '$recid'
		");
		$status = "Payee deleted successfully";
		

		if ($query) {
			// Echo JavaScript to show the toast and then redirect
			echo "
			<div class=\"alert alert-danger\" role=\"alert\"><strong>Info: </strong> $status </div>
				<script type='text/javascript'>

					// Redirect after a short delay (e.g., 2 seconds)
					setTimeout(function() {
						window.location.href = 'mypayee?meaction=MAIN'; // Redirect to MAIN view
					}, 2000); // 2-second delay for user to see the toast
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