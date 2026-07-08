<?php
namespace App\Models;
use CodeIgniter\Model;

class MyProductsModel extends Model
{

    protected $db;

    public function __construct(){
        parent::__construct();
        $this->session = session();
        $this->request = \Config\Services::request();
		$this->mylibzsys = model('App\Models\MyLibzSysModel');
		$this->db = \Config\Database::connect();
		$this->cuser = $this->session->get('__xsys_myuserzicas__');
        
    }

	public function products_save() { 
		$recid = $this->request->getPostGet('recid');
		$product_code = $this->request->getPostGet('product_code');
		$product_desc = $this->request->getPostGet('product_desc');
		$uom = $this->request->getPostGet('uom');
		$price = $this->request->getPostGet('price');
		$quantity = $this->request->getPostGet('quantity');
		$remarks = $this->request->getPostGet('remarks');

		if (empty($recid)) {
			$accessquery = $this->db->query("
				SELECT `recid`FROM tbl_user_access WHERE `username` = '{$this->cuser}' AND `access_code` = '10002' AND `is_active` = '1'
			");
			if ($accessquery->getNumRows() == 0) {
				echo "
				<script>
				toastr.error('Saving Access Denied! Please Contact the Administrator.', 'Oops!', {
						progressBar: true,
						closeButton: true,
						timeOut:2000,
					});
				</script>
				";
				die();
			}
			//INSERTING HD DATA
			$query = $this->db->query("
				INSERT INTO `mst_products`(
					`product_code`,
					`product_desc`,
					`uom`,
					`price`,
					`quantity`,
					`remarks`,
					`added_by`
				)
				VALUES (?, ?, ?, ?, ?, ?, ?)", 
				[
					$product_code,
					$product_desc,
					$uom,
					$price,
					$quantity,
					$remarks,
					$this->cuser
				]
			);

			$status = "Product Saved Successfully!";
			$color = "success";
		}else{
			$accessquery = $this->db->query("
				SELECT `recid`FROM tbl_user_access WHERE `username` = '{$this->cuser}' AND `access_code` = '10003' AND `is_active` = '1'
			");
			if ($accessquery->getNumRows() == 0) {
				echo "
				<script>
				toastr.error('Updating Access Denied! Please Contact the Administrator.', 'Oops!', {
						progressBar: true,
						closeButton: true,
						timeOut:2000,
					});
				</script>
				";
				die();
			}
			$query = $this->db->query("
				UPDATE
					`mst_products`
				SET
					`product_code` = ?,
					`product_desc` = ?,
					`uom` = ?,
					`price` = ?,
					`quantity` = ?,
					`remarks` = ?
				WHERE recid = ?
			", [
				$product_code,
				$product_desc,
				$uom,
				$price,
				$quantity,
				$remarks,
				$recid
			]);

			$status = "Product Updated Successfully!";
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
						window.location.href = 'myproducts?meaction=MAIN'; // Redirect to MAIN view
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