<?php
$this->request = \Config\Services::request();
$this->db = \Config\Database::connect();
$recid = $this->request->getPostGet('recid');

$payee_name = "";
$payee_account_num = "";
$payee_office = "";
$payee_tin = "";
$contact_no = "";
$payee_address = "";
$disb_method = "";
$currency = "";
$counter = 1;

$is_vatable = "";
$vat_percent = "";
$ewt_percent = "";
$pt_percent = "";

if(!empty($recid) || !is_null($recid)) { 

    $query = $this->db->query("
    SELECT 
        `payee_name`, 
        `payee_account_num`, 
        `payee_office`, 
        `payee_tin`,
        `contact_no`, 
        `payee_address`, 
        `disb_method`, 
        `currency`,
        `is_vatable`,
        `vat_percent`,
        `ewt_percent`,
        `pt_percent`
    FROM 
        `tbl_payee` 
    WHERE 
        `recid` = '$recid'"
    );

    $data = $query->getRowArray();
    $payee_name = $data['payee_name'];
    $payee_account_num = $data['payee_account_num'];
    $payee_office = $data['payee_office'];
    $payee_tin = $data['payee_tin'];
    $contact_no = $data['contact_no'];
    $payee_address = $data['payee_address'];
    $disb_method = $data['disb_method'];
    $currency = $data['currency'];
    $is_vatable = $data['is_vatable'];
    $vat_percent = $data['vat_percent'];
    $ewt_percent = $data['ewt_percent'];
    $pt_percent = $data['pt_percent'];

}
echo view('templates/myheader.php');
?>

<div class="container-fluid">
    <div class="row me-mypayee-outp-msg mx-0">
    </div>
    <input type="hidden" id="__siteurl" data-mesiteurl="<?=site_url();?>" />
    <div class="row mb-2 mt-0">
        <h4 class="fw-semibold mb-8">Payee</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a class="text-muted text-decoration-none" href="<?=site_url();?>"><i class="ti ti-home fs-5"></i></a>
            </li>
            <li class="breadcrumb-item" aria-current="page">Maintenance</li>
            <li class="breadcrumb-item" aria-current="page"><span class="form-label fw-bold">Payee</span></li>
            </ol>
        </nav>
    </div>
    <div class="card">
        <div class="card-header bg-info p-1">
            <div class="row">
                <div class="col-sm-6 d-flex align-items-center text-start">
                    <h6 class="mb-0 lh-base px-3 text-white fw-semibold d-flex align-items-center">
                        <i class="ti ti-pencil fs-5 me-1"></i>
                        <span class="pt-1">Entry</span>
                    </h6>
                </div>
                <div class="col-sm-6 text-end ">
                    <?php if(!empty($recid)):?>
                        <button type="button" id="btn_delete" name="btn_delete" class="btn_delete btn btn-sm btn-danger mx-3">
                            <i class="ti ti-trash fs-3 me-1"></i> Remove Payee
                        </button>
                    <?php endif;?>
                </div>
            </div>
		</div>						
        <div class="card-body p-0 px-4 py-2 my-2">
            <form action="<?=site_url();?>mypayee?meaction=MAIN-SAVE" method="post" class="mypayee-validation">
                <div class="row">
                    <div class="col-sm-6">
                        <div class="row mb-2 mt-2">
                            <div class="col-sm-4">
                                <span>Payee Name:</span>
                            </div>
                            <div class="col-sm-8">
                                <input type="hidden" class="form-control form-control-sm" id="recid" name="recid" value="<?=$recid;?>"/>
                                <input type="text" id="payee_name" name="payee_name" value="<?=$payee_name;?>" class="form-control form-control-sm"/>
                            </div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-sm-4">
                                <span>Account No:</span>
                            </div>
                            <div class="col-sm-8">
                                <input type="text" id="payee_account_num" name="payee_account_num" value="<?=$payee_account_num;?>" class="form-control form-control-sm"/>
                            </div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-sm-4">
                                <span>Office:</span>
                            </div>
                            <div class="col-sm-8">
                                <input type="text" id="payee_office" name="payee_office" value="<?=$payee_office;?>" class="form-control form-control-sm"/>
                            </div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-sm-4">
                                <span>TIN:</span>
                            </div>
                            <div class="col-sm-8">
                                <input type="text" id="payee_tin" name="payee_tin" value="<?=$payee_tin;?>" class="form-control form-control-sm"/>
                            </div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-sm-4">
                                <span>Contact No.:</span>
                            </div>
                            <div class="col-sm-8">
                                <input type="text" id="contact_no" name="contact_no" value="<?=$contact_no;?>" class="form-control form-control-sm"/>
                            </div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-sm-4">
                                <span>Address:</span>
                            </div>
                            <div class="col-sm-8">
                            <textarea name="payee_address" id="payee_address" placeholder="" rows="3" class="form-control form-control-sm"><?=$payee_address;?></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6 my-2">
                        <div class="row mb-2">
                            <div class="col-sm-4">
                                <span>Disbursement Method:</span>
                            </div>
                            <div class="col-sm-8">
                                <select id="disb_method" name="disb_method" class="form-select form-select-sm">
                                    <?php if(!empty($recid)):?>
                                    <option value="<?=$disb_method;?>"><?=$disb_method;?></option>
                                    <?php endif;?>
                                    <option value="LDDAP-ADA">LDDAP-ADA</option>
                                    <option value="LDDAP-IC">LDDAP-IC</option>
                                    <option value="CHECK">CHECK</option>
                                    <option value="ADA">ADA</option>
                                </select>
                            </div>
                        </div>
                        <div class="row mb-2 mt-2">
                            <div class="col-sm-4">
                                <span>Currency:</span>
                            </div>
                            <div class="col-sm-8">
                                <select id="currency" name="currency" class="form-select form-select-sm">
                                <?php if(!empty($recid)):?>
                                    <option value="<?=$currency;?>"><?=$currency;?></option>
                                    <?php endif;?>
                                    <option value="PHP">PHP</option>
                                    <option value="USD">USD</option>
                                </select>
                            </div>
                        </div>
                        <hr>
                        <div class="row mb-2">
                            <div class="col-sm-4">
                                <span>is Vatable?</span>
                            </div>
                            <div class="col-sm-8">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" 
                                        type="checkbox" 
                                        id="is_vatable" 
                                        name="is_vatable" 
                                        value="1"
                                        <?= (isset($is_vatable) && $is_vatable == 1) ? 'checked' : '' ?>>
                                </div>
                            </div>
                        </div>

                        <!-- VAT FIELD -->
                        <div class="row mb-2 vat-field">
                            <div class="col-sm-4">
                                <span>VAT (%):</span>
                            </div>
                            <div class="col-sm-8">
                                <input type="number" step="0.01" id="vat_percent" name="vat_percent"
                                    value="<?= $vat_percent ?? '' ?>"
                                    placeholder="e.g. 12"
                                    class="form-control form-control-sm">
                                <small class="text-muted">Standard VAT: 12%</small>
                            </div>
                        </div>

                        <!-- EWT FIELD -->
                        <div class="row mb-2 ewt-field">
                            <div class="col-sm-4">
                                <span>EWT (%):</span>
                            </div>
                            <div class="col-sm-8">
                                <input type="number" step="0.01" id="ewt_percent" name="ewt_percent"
                                    value="<?= $ewt_percent ?? '' ?>"
                                    placeholder="e.g. 1, 2, 5"
                                    class="form-control form-control-sm">
                                <small class="text-muted">Depends on ATC (e.g. 1%, 2%, 5%)</small>
                            </div>
                        </div>

                        <!-- PT FIELD -->
                        <div class="row mb-2 pt-field">
                            <div class="col-sm-4">
                                <span>PT (%):</span>
                            </div>
                            <div class="col-sm-8">
                                <input type="number" step="0.01" id="pt_percent" name="pt_percent"
                                    value="<?= $pt_percent ?? '' ?>"
                                    placeholder="e.g. 5"
                                    class="form-control form-control-sm">
                                <small class="text-muted">Percentage Tax (if non-vatable, usually 5%)</small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row mb-2">  
                    <div class="col-sm-12 text-end">
                        <button type="submit" class="btn bg-<?= empty($recid) ? 'success' : 'info' ?>-subtle text-<?= empty($recid) ? 'success' : 'info' ?> btn-sm"><i class="ti ti-brand-doctrine mt-1 fs-4 me-1"></i>
                            <?= empty($recid) ? 'Save' : 'Update' ?>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    
    <div class="card">
        <div class="card-header bg-info p-1">
            <div class="row">
                <div class="col-sm-6 d-flex align-items-center text-start">
                    <h6 class="mb-0 lh-base px-3 text-white fw-semibold d-flex align-items-center">
                        <i class="ti ti-list fs-5 me-1"></i>
                        <span class="pt-1">List</span>
                    </h6>
                </div>
            </div>
		</div>						
        <div class="card-body p-0 px-4 py-2 my-2">
            <table id="datatablesSimple" class="table table-bordered table-striped table-hover">
                <thead>
                    <tr>
                        <th>Action</th>
                        <th>Full Name</th>
                        <th>Account No.</th>
                        <th>Office</th>
                        <th>Tin</th>
                        <th>Address</th>
                        <th>Method</th>
                        <th>Currency</th>
                    </tr>
                </thead>
                <tbody class="align-middle">
                    <?php if(!empty($results)):
                        
                        foreach ($results as $data):
                            $recid = $data['recid'];
                            $payee_name = $data['payee_name'];
                            $payee_account_num = $data['payee_account_num'];
                            $payee_office = $data['payee_office'];
                            $payee_tin = $data['payee_tin'];
                            $payee_address = $data['payee_address'];
                            $disb_method = $data['disb_method'];
                            $currency = $data['currency'];
                    ?>
                    <tr>
                        <td class="text-center align-middle">
                            <a class="text-info nav-icon-hover fs-6" href="mypayee?meaction=MAIN&recid=<?= $recid ?>">
                                <i class="ti ti-eye" aria-hidden="true"></i>
                            </a>
                        </td>
                        <td class="text-center"><?=$payee_name;?></td>
                        <td class="text-center"><?=$payee_account_num;?></td>
                        <td class="text-center"><?=$payee_office;?></td>
                        <td class="text-center"><?=$payee_tin;?></td>
                        <td class="text-center"><?=$payee_address;?></td>
                        <td class="text-center"><?=$disb_method;?></td>
                        <td class="text-center"><?=$currency;?></td>
                    </tr>
                    <?php endforeach; endif;?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<!-- Delete Confirmation Modal -->
<div class="modal fade" id="confirmDeleteModal" tabindex="-1" aria-labelledby="confirmDeleteModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-secondary-subtle text-white">
        <h5 class="modal-title" id="confirmDeleteModalLabel">Confirm Delete</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        Are you sure you want to delete this payee?
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-success btn-sm px-3" id="confirmDeleteBtn">Yes</button>
      </div>
    </div>
  </div>
</div>


<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="<?=base_url('assets/js/maintenance/mypayee.js?v=2');?>"></script>
<script src="<?=base_url('assets/js/mysysapps.js');?>"></script>
<script>
    __mysys_payee_ent.__payee_saving();
    $(document).ready(function () {
  $('#datatablesSimple').DataTable({
    pageLength: 5,
    lengthChange: false,
    language: {
      search: "Search:"
    }
  });
});
</script>

<script>
$(function () {

    function toggleTaxFields() {
        if ($("#is_vatable").is(":checked")) {

            // SHOW VAT + EWT
            $(".vat-field").show();
            $(".ewt-field").show();

            // HIDE PT
            $(".pt-field").hide();

            // ✅ CLEAR PT VALUE
            $("#pt_percent").val('');

            // Optional default VAT
            if (!$("#vat_percent").val()) {
                $("#vat_percent").val();
            }

        } else {

            // HIDE VAT
            $(".vat-field").hide();

            // SHOW EWT + PT
            $(".ewt-field").show();
            $(".pt-field").show();

            // ✅ CLEAR VAT VALUE
            $("#vat_percent").val('');

            // Optional default PT
            if (!$("#pt_percent").val()) {
                $("#pt_percent").val();
            }
        }
    }

    // On page load
    toggleTaxFields();

    // On checkbox change
    $("#is_vatable").on("change", function () {
        toggleTaxFields();
    });

});
</script>
<?php
    echo view('templates/myfooter.php');
?>


