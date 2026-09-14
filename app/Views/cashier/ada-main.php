<?php
$this->request = \Config\Services::request();
$this->db = \Config\Database::connect();
$this->myada = model('App\Models\MyADAModel');
$action = $this->request->getPostGet('action');
$recid = $this->request->getPostGet('recid');
$dvno = $this->request->getPostGet('dvno');
$this->session = session();
$this->cuser = $this->session->get('__xsys_myuserzicas__');

$disbursement_date = '';
$particulars = '';
$funding_source = '';
$payee_name = '';
$payee_office = '';
$payee_address = '';
$certified_a = '';
$certified_b = '';
$certified_c = '';
$position_a = '';
$position_b = '';
$position_c = '';
$serialno = '';
$fund_cluster_code = '';
$is_pending = '';
$is_approved_certa = '';
$is_disapproved_certa = '';
$is_approved_certb = '';
$is_disapproved_certb = '';
$certa_remarks = '';
$certb_remarks = '';
$MDL_jsscript = "";
$status_color= "";
$status_msg = "";
$status_color_b= "";
$status_msg_c = "";

$is_vatable = "";
$vat_percent = "";
$ewt_percent = "";
$pt_percent = "";
$other_deductions = "";
$income_tax = "";
$category = "";
$net_amount = "";

$deduction_name = "";
$sss = "";
$hdmf = "";
$pagibig_mp2 = "";
$pagibig_loan = "";
$hmo = "";
$wtax = "";

$adano = "";
$particulars = "";
$payee_name = "";
$payee_accountno = "";
$ada_date = "";
$net_amount = "";
$signatory_name1 = "";
$signatory_position1 = "";
$signatory_name2 = "";
$signatory_position2 = "";

$payee_accountno = "";

$certified_a = '';
$certified_b = '';
$position_a = '';
$position_b = '';

if((!empty($recid) || !is_null($recid)) && (empty($dvno))) { 
    $query = $this->db->query("
    SELECT
        `adano`,
        `dvno`,
        `particulars`,
        `payee_name`,
        `payee_accountno`,
        `ada_date`,
        `net_amount`,
        `certified_a`,
        `certified_b`,
        `position_a`,
        `position_b`
    FROM tbl_ada
    WHERE 
        `recid` = '$recid'"
    );

   $data = $query->getRowArray();
    $adano = $data['adano'];
    $dvno = $data['dvno'];
    $particulars = $data['particulars'];
    $payee_name = $data['payee_name'];
    $payee_accountno = $data['payee_accountno'];
    $ada_date = $data['ada_date'];
    $net_amount = $data['net_amount'];
    $certified_a = $data['certified_a'];
    $position_a = $data['position_a'];
    $certified_b = $data['certified_b'];
    $position_b = $data['position_b'];

}

if(!empty($dvno) || !is_null($dvno)) { 
    $query = $this->db->query("
    SELECT
    b.`disbursement_date`,
    b.`particulars`,
    b.`funding_source`,
    b.`payee_name`,
    b.`payee_office`,
    b.`payee_address`,
    (SELECT a.`payee_account_num` FROM tbl_payee a WHERE a.`payee_name` = b.`payee_name` LIMIT 1) AS `payee_accountno`,
    b.`certified_a`,
    b.`certified_c`,
    b.`position_a`,
    b.`position_c`,
    b.`serialno`,
    b.`fund_cluster_code`,
    b.`is_pending`,
    b.`is_approved_certa`,
    b.`is_disapproved_certa`,
    b.`is_approved_certb`,
    b.`is_disapproved_certb`,
    b.`certa_remarks`,
    b.`certb_remarks`,
    b.`certa_approver`,
    b.`certb_approver`,
    b.`is_vatable`,
    b.`vat_percent`,
    b.`ewt_percent`,
    b.`pt_percent`,
    b.`other_deductions`,
    b.`income_tax`,
    b.`deduction_name`,
    b.`sss`,
    b.`hdmf`,
    b.`pagibig_mp2`,
    b.`pagibig_loan`,
    b.`hmo`,
    b.`wtax`,
    b.`category`,
    b.`net_amount`
    FROM
        `tbl_disbursement_hd` b
    WHERE 
        b.`dvno` = '$dvno'"
    );

    $data = $query->getRowArray();
    $disbursement_date = $data['disbursement_date'];
    $particulars = $data['particulars'];
    $funding_source = $data['funding_source'];
    $payee_name = $data['payee_name'];
    $payee_office = $data['payee_office'];
    $payee_address = $data['payee_address'];
    $serialno = $data['serialno'];
    $fund_cluster_code = $data['fund_cluster_code'];
    $is_pending = $data['is_pending'];
    $is_approved_certa = $data['is_approved_certa'];
    $is_disapproved_certa = $data['is_disapproved_certa'];
    $is_approved_certb = $data['is_approved_certb'];
    $is_disapproved_certb = $data['is_disapproved_certb'];
    $is_vatable = $data['is_vatable'];
    $vat_percent = $data['vat_percent'];
    $ewt_percent = $data['ewt_percent'];
    $pt_percent = $data['pt_percent'];
    $other_deductions = $data['other_deductions'];
    $income_tax = $data['income_tax'];
    $deduction_name = $data['deduction_name'];
    $sss = $data['sss'];
    $hdmf = $data['hdmf'];
    $pagibig_mp2 = $data['pagibig_mp2'];
    $pagibig_loan = $data['pagibig_loan'];
    $hmo = $data['hmo'];
    $wtax = $data['wtax'];
    $category = $data['category'];
    $net_amount = $data['net_amount'];
    $payee_accountno = $data['payee_accountno'];

}


echo view('templates/myheader.php');
?>

<div class="container-fluid">
    <div class="row me-myadaburs-appr-outp-msg mx-0">
    </div>
    
    <input type="hidden" id="__siteurl" data-mesiteurl="<?=site_url();?>" />
    <div class="row mb-2 mt-0">
        <h4 class="fw-semibold mb-8">Authority To Debit/Credit Account</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a class="text-muted text-decoration-none" href="<?=site_url();?>"><i class="ti ti-home fs-5"></i></a>
            </li>
            <li class="breadcrumb-item" aria-current="page">Cashier</li>
            <li class="breadcrumb-item" aria-current="page"><span class="form-label fw-bold">ADA</span></li>
            </ol>
        </nav>
    </div>

    <div class="card rounded">
        <div class="row myada-outp-msg mx-0">

        </div>
        <div class="card-header   bg-info p-1">
            <div class="row d-flex align-items-center">
                <div class="col-sm-6 d-flex align-items-center text-start">
                    <h6 class="mb-0 lh-base px-3 text-white fw-semibold d-flex align-items-center">
                        <i class="ti ti-pencil fs-5 me-1"></i>
                        <span class="pt-1">Entry</span>
                    </h6>
                </div>
                <div class="col-sm-6 text-end px-4">
                </div>
            </div>
        </div>						
        <div class="card-body p-0 px-4 py-2 my-2">
            <form action="<?=site_url();?>myada?meaction=MAIN-SAVE" method="post" class="myada-validation">
                <input type="hidden" class="form-control form-control-sm" id="recid" name="recid" value="<?=$recid;?>"/>

                <div class="row">
                    <div class="col-sm-6">
                        <div class="row">
                            <div class="col-sm-12">
                                <div class="row mb-2">
                                    <div class="col-sm-4">
                                        <span class="fw-bold">DV No.:</span>
                                    </div>
                                    <div class="col-sm-8">
                                        <input type="text" id="dvno" name="dvno" value="<?=$dvno;?>" placeholder="Select DV no."class="form-control form-control-sm"/>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-12">
                                <div class="row mb-2">
                                    <div class="col-sm-4">
                                        <span class="fw-bold">ADA No.:</span>
                                    </div>
                                    <div class="col-sm-8">
                                        <input type="text" id="adano" name="adano" value="<?=$adano;?>" placeholder="Enter ADA no."class="form-control form-control-sm"/>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-12">
                                <div class="row mb-2">
                                    <div class="col-sm-4">
                                        <span>Ada Date</span>
                                    </div>
                                    <div class="col-sm-8">
                                        <input type="date" name="ada_date" id="ada_date" value="<?=$ada_date;?>" class="form form-control form-control-sm">
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-12">
                                <div class="row mb-2">
                                    <div class="col-sm-4">
                                        <span>Particulars</span>
                                    </div>
                                    <div class="col-sm-8">
                                        <textarea name="particulars" id="particulars" placeholder="" disabled rows="4" class="form-control form-control-sm"><?= (!empty($recid) || !empty($dvno)) ? $particulars : ''; ?></textarea>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="row mb-2">
                            <div class="col-sm-4">
                                <span class="fw-bold">Payee:</span>
                            </div>
                            <div class="col-sm-8">
                                <input type="text" 
                                    name="payee_name" 
                                    id="payee_name"
                                    class="form-control form-control-sm payee_name"
                                    disabled
                                    value="<?= (!empty($recid) || !empty($dvno)) ? $payee_name : '' ?>"
                                    placeholder="Type or select payee...">
                            </div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-sm-4">
                                <span class="fw-bold">Account No.:</span>
                            </div>
                            <div class="col-sm-8">
                                <input type="text" id="payee_accountno" name="payee_accountno"
                                    value="<?=$payee_accountno;?>"
                                    class="form-control form-control-sm" disabled />
                            </div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-sm-4">
                                <span class="fw-bold">Net Amount:</span>
                            </div>
                            <div class="col-sm-8">
                                <input type="text" id="net_amount" name="net_amount"
                                    value="<?=$net_amount;?>"
                                    class="form-control form-control-sm" disabled />
                            </div>
                        </div>
                        <!-- ============================================ -->
                        <!-- GROSS AND NET AMOUNT (Always visible)        -->
                        <!-- ============================================ -->

                    </div>
                </div>

                <hr>

                <div class="row">
                    <div class="col-sm-6">
                        <div class="row">
                            <div class="col-sm-12">
                                <div class="row mb-2">
                                    <div class="col-sm-4">
                                        <span class="fw-bold">Certified A:</span>
                                    </div>
                                    <div class="col-sm-8">
                                        <?php if(!empty($recid)):?>
                                            <select name="certified_a" id="certified_a" class="form-control select2 form-select-sm show-tick">
                                                <option selected value="<?=$certified_a;?>"><?=$certified_a;?></option>
                                                <?php foreach($certifyadata as $data): ?>
                                                    <option 
                                                        value="<?= $data['full_name'] ?>"
                                                        data-position="<?= $data['position'] ?>"
                                                    >
                                                        <?= $data['full_name'] ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        <?php else:?>
                                            <select name="certified_a" id="certified_a" class="form-control select2 form-select-sm show-tick">
                                                <option selected value="">Choose...</option>
                                                <?php foreach($certifyadata as $data): ?>
                                                    <option 
                                                        value="<?= $data['full_name'] ?>"
                                                        data-position="<?= $data['position'] ?>"
                                                    >
                                                        <?= $data['full_name'] ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        <?php endif;?>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-12">
                                <div class="row mb-2">
                                    <div class="col-sm-4">
                                        <span class="fw-bold">Position:</span>
                                    </div>
                                    <div class="col-sm-8">
                                        <input type="text" id="position_a" name="position_a" value="<?=$position_a;?>" class="form-control form-control-sm" disabled/>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="row mb-2">
                            <div class="col-sm-4">
                                <span class="fw-bold">Certified B:</span>
                            </div>
                            <div class="col-sm-8">
                                <?php if(!empty($recid)):?>
                                    <select name="certified_b" id="certified_b" class="form-control select2 form-select-sm show-tick">
                                        <option selected value="<?=$certified_b;?>"><?=$certified_b;?></option>
                                        <?php foreach($certifybdata as $data): ?>
                                            <option 
                                                value="<?= $data['full_name'] ?>"
                                                data-position="<?= $data['position'] ?>"
                                            >
                                                <?= $data['full_name'] ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                <?php else:?>
                                    <select name="certified_b" id="certified_b" class="form-control select2 form-select-sm show-tick">
                                        <option selected value="">Choose...</option>
                                        <?php foreach($certifybdata as $data): ?>
                                            <option 
                                                value="<?= $data['full_name'] ?>"
                                                data-position="<?= $data['position'] ?>"
                                            >
                                                <?= $data['full_name'] ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                <?php endif;?>
                            </div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-sm-12">
                                <div class="row mb-2">
                                    <div class="col-sm-4">
                                        <span class="fw-bold">Position:</span>
                                    </div>
                                    <div class="col-sm-8">
                                        <input type="text" id="position_b" name="position_b" value="<?=$position_b;?>" class="form-control form-control-sm" disabled/>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mb-2">  
                    <div class="col-sm-12 text-end">
                        <button type="submit" id="submitBtn" class="btn bg-<?= empty($recid) ? 'success' : 'info' ?>-subtle text-<?= empty($recid) ? 'success' : 'info' ?> btn-sm"><i class="ti ti-brand-doctrine mt-1 fs-4 me-1"></i>
                            <?= empty($recid) ? 'Save' : 'Update' ?>
                        </button>
                    </div>
                </div>
            </form>

        </div>
    </div>

    <div class="row mb-2">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header bg-info p-1">
                    <div class="row align-items-center">
                        <div class="col-sm-6 d-flex align-items-center text-start">
                            <h6 class="mb-0 lh-base px-3 text-white fw-semibold d-flex align-items-center">
                                <i class="ti ti-list fs-5 me-1"></i>
                                <span class="pt-1">List</span>
                            </h6>
                        </div>
                    </div>
                </div>

                <div class="card-body p-3">
                    <div class="table-responsive">
                        <table id="datatablesSimple" class="table table-bordered table-striped table-hover align-middle mb-0" style="font-size:0.85rem;">
                            <thead>
                                <tr>
                                    <th class="text-center">Action</th>
                                    <th class="text-center">ADA No.</th>
                                    <th class="text-center">DV No.</th>
                                    <th class="text-center">Particulars</th>
                                    <th class="text-center">Payee</th>
                                    <th class="text-center">Payee Account No.</th>
                                    <th class="text-center">ADA Date</th>
                                    <th class="text-center">Net Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(!empty($adadata)):
                                    foreach ($adadata as $data):
                                        $dt_recid        = $data['recid'];
                                        $adano           = $data['adano'];
                                        $dvno            = $data['dvno'];
                                        $particulars     = $data['particulars'];
                                        $payee_name      = $data['payee_name'];
                                        $payee_accountno = $data['payee_accountno'];
                                        $ada_date        = $data['ada_date'];
                                        $net_amount      = $data['net_amount'];
                                ?>
                                <tr>
                                    <td class="text-center">
                                        <div class="d-flex justify-content-center gap-2">
                                            <a class="text-info nav-icon-hover fs-6"
                                            href="myada?meaction=MAIN&recid=<?= $dt_recid ?>"
                                            title="Edit">
                                                <i class="ti ti-edit"></i>
                                            </a>
                                            <button class="btn btn-sm fs-6 text-warning p-0 border-0 bg-transparent"
                                                    onclick="__mysys_ada_ent.__showPdfInModal('<?= base_url('myada?meaction=PRINT-ADA&dvno='.$dvno) ?>')"
                                                    title="Print ADA">
                                                <i class="ti ti-printer"></i>
                                            </button>
                                        </div>
                                    </td>
                                    <td class="text-center"><?= $adano; ?></td>
                                    <td class="text-center"><?= $dvno; ?></td>
                                    <td><?= $particulars; ?></td>
                                    <td><?= $payee_name; ?></td>
                                    <td class="text-center"><?= $payee_accountno; ?></td>
                                    <td class="text-center"><?= !empty($ada_date) ? date('M d, Y', strtotime($ada_date)) : ''; ?></td>
                                    <td class="text-end"><?= 'P' . number_format($net_amount, 2); ?></td>
                                </tr>
                                <?php endforeach; endif;?>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>


<!-- PDF Modal -->
<div class="modal fade" id="pdfModal" tabindex="-1" aria-labelledby="pdfModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Printing Preview</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <iframe id="pdfFrame" src="" style="width: 100%; height: 80vh;" frameborder="0"></iframe>
            </div>
        </div>
    </div>
</div>



<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.min.js"></script>
<script src="<?=base_url('assets/js/ada/ada.js?v=1');?>"></script>
<script src="<?=base_url('assets/js/mysysapps.js');?>"></script>


<script>
<?php
    $dvdata_js = [];

    foreach ($dvdata as $row) {
        $dvdata_js[] = [
            'label' => $row['dvno'],
            'value' => $row['dvno'],
            'dvno'  => $row['dvno'],
        ];
    }
?>

var dvdata   = <?= json_encode($dvdata_js); ?>;
var mesiteurl = "<?= base_url(); ?>";

$(function () {

    $("#dvno").autocomplete({
        source: dvdata,
        minLength: 0,
        delay: 0,
        autoFocus: true,
        select: function (event, ui) {
            // Prevent jQuery UI from overwriting the input before we redirect
            event.preventDefault();

            var dvno = ui.item.dvno;

            window.location.href = mesiteurl
                + 'myada?meaction=MAIN'
                + '&dvno=' + encodeURIComponent(dvno);
        }
    }).on("focus", function () {
        $(this).autocomplete("search", "");
    });

});
</script>

<script>
    __mysys_ada_ent.__ada_saving();

</script>

<?php
    echo view('templates/myfooter.php');
?>


