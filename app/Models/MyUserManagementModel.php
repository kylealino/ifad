<?php
namespace App\Models;
use CodeIgniter\Model;

class MyUserManagementModel extends Model
{

    protected $db;

    public function __construct(){
        parent::__construct();
        $this->session = session();
        $this->request = \Config\Services::request();
		$this->db = \Config\Database::connect();
		$this->cuser = $this->session->get('__xsys_myuserzicas__');
        
    }

	public function user_save() { 
		$recid = $this->request->getPostGet('recid');
		$full_name = $this->request->getPostGet('full_name');
		$division = $this->request->getPostGet('division');
		$section = $this->request->getPostGet('section');
		$position = $this->request->getPostGet('position');
		$username = $this->request->getPostGet('username');
		$hash_value = $this->request->getPostGet('hash_value');
		$hash_password = hash('sha512', $hash_value);

		if (empty($full_name)) {
			echo "
			<script>
			toastr.error('Full name is required!', 'Oops!', {
					progressBar: true,
					closeButton: true,
					timeOut:2000,
				});
			</script>
			";
			die();
		}
		if (empty($division)) {
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
		if (empty($section)) {
			echo "
			<script>
			toastr.error('Section is required!', 'Oops!', {
					progressBar: true,
					closeButton: true,
					timeOut:2000,
				});
			</script>
			";
			die();
		}
		if (empty($position)) {
			echo "
			<script>
			toastr.error('Position is required!', 'Oops!', {
					progressBar: true,
					closeButton: true,
					timeOut:2000,
				});
			</script>
			";
			die();
		}
		if (empty($username)) {
			echo "
			<script>
			toastr.error('Username is required!', 'Oops!', {
					progressBar: true,
					closeButton: true,
					timeOut:2000,
				});
			</script>
			";
			die();
		}
		if (empty($hash_value)) {
			echo "
			<script>
			toastr.error('Password is required!', 'Oops!', {
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
				INSERT INTO `myua_user`(
					`username`,
					`hash_password`,
					`hash_value`,
					`full_name`,
					`division`,
					`section`,
					`position`,
					`added_at`,
					`added_by`
				)
				VALUES(
					'$username',
					'$hash_password',	
					'$hash_value',
					'$full_name',
					'$division',
					'$section',
					'$position',
					NOW(),
					'{$this->cuser}'
				)
			");
			$status = "User Saved successfully";
			$color = "success";
		}else{
			$query = $this->db->query("
			UPDATE
				`myua_user`
			SET
				`username` = '$username',
				`hash_password` = '$hash_password',
				`hash_value` = '$hash_value',
				`full_name` = '$full_name',
				`division` = '$division',
				`section` = '$section',
				`position` = '$position'
				WHERE `recid` = '$recid'
			");
			$status = "User Updated successfully";
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
						window.location.href = 'myua?meaction=MAIN'; // Redirect to MAIN view
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

	public function user_access_save() { 
		$username = $this->request->getPostGet('username');
		$accessdata = $this->request->getPostGet('accessdata');

		$query = $this->db->query("DELETE FROM tbl_user_access WHERE username = '$username'");
		if (!empty($accessdata)) {
			for($aa = 0; $aa < count($accessdata); $aa++){
				$medata = explode("x|x",$accessdata[$aa]);
				$access_name = $medata[0]; 
				$access_code = $medata[1]; 
				$is_checked = $medata[2]; 
				$dtid = $medata[3]; 
				if ($is_checked == 'true') {
					$is_active = '1';
				}else{
					$is_active = '0';
				}
				$query = $this->db->query("
					INSERT INTO `tbl_user_access`(
						`username`,
						`access_code`,
						`is_active`,
						`added_by`
					)
					VALUES(
						'$username',
						'$access_code',
						'$is_active',
						'{$this->cuser}'
					)
				");

			}

			$status = "Access updated successfully!";
			$color = "success";
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
						window.location.href = 'myua?meaction=MAIN'; // Redirect to MAIN view
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