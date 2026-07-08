<?php
$this->request = \Config\Services::request();
$this->db = \Config\Database::connect();
$this->mylddapada = model('App\Models\MylddapadaModel');
$action = $this->request->getPostGet('action');
$recid = $this->request->getPostGet('recid');

$lddapadano = '';
$mds_branch = '';
$mds_accountno = '';
$fund_cluster_code = '';
$funding_source = '';
$is_ci = '';
$ada_approver = '';
$dv_list = [];

$lddapada_date = date('Y-m-d');


if((!empty($recid) || !is_null($recid)) ) { 
    $query = $this->db->query("
        SELECT
            `recid`,
            `lddapadano`,
            `mds_branch`,
            `mds_accountno`,
            `lddapada_date`,
            `fund_cluster_code`,
            `funding_source`,
            `is_ci`,
            `ada_approver`
        FROM
            `tbl_lddapada_hd`
        WHERE 
            `recid` = '$recid'
        "
    );

    $data = $query->getRowArray();
    $lddapadano        = $data['lddapadano'];
    $mds_branch        = $data['mds_branch'];
    $mds_accountno     = $data['mds_accountno'];
    $lddapada_date     = $data['lddapada_date'];
    $fund_cluster_code = $data['fund_cluster_code'];
    $funding_source = $data['funding_source'];
    $is_ci = $data['is_ci'];
    $ada_approver = $data['ada_approver'];
}

    $query_dvno = $this->db->query("
        SELECT dvno 
        FROM tbl_lddapada_dvno 
        WHERE lddapada_id = '$recid'
    ");

    $result_dvno = $query_dvno->getResultArray();

    foreach($result_dvno as $row){
        $dv_list[] = $row['dvno'];
    }


echo view('templates/myheader.php');
?>
<style>
    #dv_line_items input,
    #dv_line_items textarea {
        width: 100%;
        box-sizing: border-box;
    }
</style>
<div class="container-fluid">
    <div class="row me-mylddapadaburs-appr-outp-msg mx-0">
    </div>
    
    <input type="hidden" id="__siteurl" data-mesiteurl="<?=site_url();?>" />
    <div class="row mb-2 mt-0">
        <h4 class="fw-semibold mb-8">LDDAP-ADA</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a class="text-muted text-decoration-none" href="<?=site_url();?>"><i class="ti ti-home fs-5"></i></a>
            </li>
            <li class="breadcrumb-item" aria-current="page">Cashier</li>
            <li class="breadcrumb-item" aria-current="page"><span class="form-label fw-bold">LDDAP-ADA</span></li>
            </ol>
        </nav>
    </div>

    <div class="card rounded">
        <div class="row mylddapada-outp-msg mx-0">

        </div>
        <div class="card-header   bg-info p-1">
            <div class="row d-flex align-items-center">
                <div class="col-sm-6 d-flex align-items-center text-start">
                    <h6 class="mb-0 lh-base px-3 text-white fw-semibold d-flex align-items-center">
                        <i class="ti ti-pencil fs-5 me-1"></i>
                        <span class="pt-1">Entry</span>
                    </h6>
                </div>
                <div class="col-sm-6 text-end">
                </div>
            </div>
        </div>						
        <div class="card-body p-0 px-4 py-2 my-2">
            <form action="<?=site_url();?>mylddapada?meaction=MAIN-SAVE" method="post" class="mylddapada-validation">
                <input type="hidden" class="form-control form-control-sm" id="recid" name="recid" value="<?=$recid;?>"/>

                <div class="row">
                    <div class="col-sm-6">
                        <div class="row">
                            <div class="row mb-2">
                                <div class="col-sm-4">
                                    <span class="fw-bold">MDS-GSB BRANCH:</span>
                                </div>
                                <div class="col-sm-8">
                                    <input type="text" id="mds_branch" name="mds_branch" value="<?= !empty($recid) ? $mds_branch : 'Land Bank of the Philippines - DOST Branch';?>" class="form-control form-control-sm" disabled/>
                                </div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-sm-4">
                                    <span class="fw-bold">MDS SUB ACCOUNT NO.:</span>
                                </div>
                                <div class="col-sm-8">
                                    <input type="text" id="mds_accountno" name="mds_accountno" value="<?= !empty($recid) ? $mds_accountno : '2182-9001-36';?>" class="form-control form-control-sm" disabled/>
                                </div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-sm-4">
                                    <span class="fw-bold">LDDAP-ADA No.:</span>
                                </div>
                                <div class="col-sm-8">
                                    <input type="text" 
                                        name="lddapadano" 
                                        id="lddapadano"
                                        class="form-control form-control-sm lddapadano"
                                        value="<?= $lddapadano;?>" disabled
                                        >
                                </div>
                            </div>
                            <input type="hidden" id="funding_source" name="funding_source" value="<?=$funding_source;?>" class="form-control form-control-sm"/>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="col-sm-12">
                            <div class="row mb-2">
                                <div class="col-sm-4">
                                    <span>Date</span>
                                </div>
                                <div class="col-sm-8">
                                    <input type="date" name="lddapada_date" id="lddapada_date" value="<?=$lddapada_date;?>" class="form form-control form-control-sm">
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-12">
                            <div class="row mb-2">
                                <div class="col-sm-4">
                                    <span>Fund Cluster:</span>
                                </div>
                                <div class="col-sm-8">
                                    <select name="" id="fund_cluster_code" class="form-select form-select-sm">
                                    <?php if(!empty($recid)):?>
                                        <option value="<?=$fund_cluster_code;?>"><?=$fund_cluster_code;?></option>
                                    <?php else:?>
                                        <option value="">Choose...</option>
                                    <?php endif;?>
                                        <option value="01">01</option>
                                        <option value="07">07</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-12">
                            <div class="row mb-2">
                                <div class="col-sm-4">
                                    <span>is CI?</span>
                                </div>
                                <div class="col-sm-8">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" 
                                            type="checkbox" 
                                            id="is_ci" 
                                            name="is_ci" 
                                            value="1"
                                            <?= (isset($is_ci) && $is_ci == 1) ? 'checked' : '' ?>>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <hr>

                <div class="col-sm-12">
                    <div class="row mb-3 align-items-end">

                        <!-- LEFT SIDE -->
                        <div class="col-sm-6">
                            <label class="fw-bold mb-1">Voucher No.</label>
                            <div class="input-group input-group-sm">
                                <select id="dv_lookup" class="form-control">
                                    <option value="">-- Select Voucher --</option>
                                    <?php
                                    $query = $this->db->query("SELECT dvno FROM tbl_disbursement_hd ORDER BY recid DESC");
                                    $result = $query->getResultArray();
                                    foreach($result as $row):
                                    ?>
                                        <option value="<?=$row['dvno'];?>"><?=$row['dvno'];?></option>
                                    <?php endforeach;?>
                                </select>

                                <button type="button" id="btn_add_dv" class="btn btn-primary">
                                    <i class="ti ti-plus"></i> Add
                                </button>
                            </div>
                        </div>

                        <!-- RIGHT SIDE -->
                        <div class="col-sm-6">
                            <label class="fw-bold mb-1">Selected DVNO</label>
                            <div id="selected_dvno_list" class="form-control form-control-sm d-flex flex-wrap gap-1" style="min-height:30px;">
                                
                            </div>
                        </div>

                    </div>
                </div>

                <div class="col-sm-12">
                    <div class="row mb-2">
                        <div class="table-responsive pe-2 ps-0">
                            <div class="col-md-12 mb-2">
                                <table id="dv_line_items" class="table-sm table-striped dvdata-list">
                                    <colgroup>
                                        <col style="width:40px;">
                                        <col style="width:140px;">   <!-- # -->
                                        <col style="width:500px;">  <!-- Payee -->
                                        <col style="width:300px;">
                                        <col style="width:300px;">  
                                        <col style="width:140px;">
                                        <col style="width:140px;">
                                        <col style="width:140px;">
                                        <col style="width:140px;">
                                        <col style="width:200px;"> <!-- Remarks -->
                                    </colgroup>
                                    <thead>
                                        <th class="text-center align-middle">#</th>
                                        <th class="text-center align-middle">DV No.</th>
                                        <th class="text-center align-middle">Payee</th>
                                        <th class="text-center align-middle">Account No.</th>
                                        <th class="text-center align-middle">Serial No.</th>
                                        <th class="text-center align-middle">Allotment Class</th>
                                        <th class="text-center align-middle">Gross Amount</th>
                                        <th class="text-center align-middle">Withholding Tax</th>
                                        <th class="text-center align-middle">Net Amount</th>
                                        <th class="text-center align-middle">Remarks</th>
                                    </thead>
                                    <tbody>
                                        <tr style="display:none;">
                                            <td class="text-center align-middle"></td>
                                            <td class="text-center align-middle" nowrap>
                                                <input type="text"  value="" name="dvno" class="dvno text-center"/>
                                            </td>
                                            <td class="text-center align-middle" nowrap>
                                                <input type="text"  value="" name="payee_name" class="payee_name text-center"/>
                                            </td>
                                            <td class="text-center align-middle" nowrap>
                                                <input type="text"  value="" name="payee_account_num" class="payee_account_num text-center"/>
                                            </td>
                                            <td class="text-center align-middle" nowrap>
                                                <input type="text"  value="" name="serialno" class="serialno text-center"/>
                                            </td>
                                            <td class="text-center align-middle" nowrap>
                                                <input type="text" value="" name="allotment_class" class="allotment_class text-center"/>
                                            </td>
                                            <td class="text-center align-middle" nowrap>
                                                <input type="number" value="" step="any"  name="gross_amount" class="gross_amount text-center"/>
                                            </td>
                                            <td class="text-center align-middle" nowrap>
                                                <input type="number"  value="" step="any"  name="total_deduction" data-dtid=""  class="total_deduction text-center"/>
                                            </td>
                                            <td class="text-center align-middle" nowrap>
                                                <input type="number"  value="" step="any" name="net_amount" data-dtid="" class="net_amount text-center"/>
                                            </td>
                                            <td class="text-center align-middle" nowrap>
                                                <textarea name="remarks" placeholder="" rows="1"  class="form-control form-control-sm border-black text-black remarks"></textarea>
                                            </td>
                                        </tr>
                                            <?php if(!empty($recid)):
                                                $query = $this->db->query("
                                                SELECT
                                                    `recid`,
                                                    `lddapada_id`,
                                                    `dvno`,
                                                    `payee_name`,
                                                    `payee_account_num`,
                                                    `serialno`,
                                                    `uacs_code`,
                                                    `gross_amount`,
                                                    `total_deduction`,
                                                    `net_amount`,
                                                    `remarks`,
                                                    `added_by`,
                                                    `added_at`
                                                FROM
                                                    `tbl_lddapada_dt`
                                                WHERE
                                                    `lddapada_id` = '$recid'
                                                ");
                                                $result = $query->getResultArray();
                                                foreach ($result as $data):
                                                    $dt_id = $data['recid'];
                                                    $dvno = $data['dvno'];
                                                    $payee_name = $data['payee_name'];
                                                    $payee_account_num = $data['payee_account_num'];
                                                    $serialno = $data['serialno'];
                                                    $uacs_code = $data['uacs_code'];
                                                    $gross_amount = $data['gross_amount'];
                                                    $total_deduction = $data['total_deduction'];
                                                    $net_amount = $data['net_amount'];
                                                    $remarks = $data['remarks'];
                                            ?>
                                            <tr>
                                                <td class="text-center align-middle"></td>
                                                <td class="text-center align-middle" nowrap>
                                                    <input type="text"  value="<?=$dvno;?>" name="dvno" class="dvno text-center"/>
                                                </td>
                                                <td class="text-center align-middle" nowrap>
                                                    <input type="text"  value="<?=$payee_name;?>" name="payee_name" class="payee_name text-center"/>
                                                </td>
                                                <td class="text-center align-middle" nowrap>
                                                    <input type="text"  value="<?=$payee_account_num;?>" name="payee_account_num" class="payee_account_num text-center"/>
                                                </td>
                                                <td class="text-center align-middle" nowrap>
                                                    <input type="text"  value="<?=$serialno;?>" name="serialno" class="serialno text-center"/>
                                                </td>
                                                <td class="text-center align-middle" nowrap>
                                                    <input type="text" value="<?=$uacs_code;?>" name="allotment_class" class="allotment_class text-center"/>
                                                </td>
                                                <td class="text-center align-middle" nowrap>
                                                    <input type="number" value="<?=$gross_amount;?>" step="any"  name="gross_amount" class="gross_amount text-center"/>
                                                </td>
                                                <td class="text-center align-middle" nowrap>
                                                    <input type="number"  value="<?=$total_deduction;?>" step="any"  name="total_deduction" data-dtid=""  class="total_deduction text-center"/>
                                                </td>
                                                <td class="text-center align-middle" nowrap>
                                                    <input type="number"  value="<?=$net_amount;?>" step="any" name="net_amount" data-dtid="" class="net_amount text-center"/>
                                                </td>
                                                <td class="text-center align-middle" nowrap>
                                                    <textarea name="remarks" placeholder="" rows="1"  class="form-control form-control-sm border-black text-black remarks"><?=$remarks;?></textarea>
                                                </td>
                                            </tr>
                                        <?php endforeach; endif;?>

                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <hr>
                    <div class="row mb-3 align-items-center justify-content-center">

                        <div class="col-sm-6">
                            <div class="row">
                                <div class="col-sm-12">
                                    <div class="row mb-2">
                                        <div class="col-sm-4">
                                            <span class="fw-bold">ADA Approver:</span>
                                        </div>
                                        <div class="col-sm-8">
                                            <?php if(!empty($recid)):?>
                                                <select name="ada_approver" id="ada_approver" class="form-control select2 form-select-sm show-tick">
                                                    <option selected value="<?=$ada_approver;?>"><?=$ada_approver;?></option>
                                                    <?php foreach($adaapprover as $data): ?>
                                                        <option 
                                                            value="<?= $data['full_name'] ?>"
                                                            data-position="<?= $data['position'] ?>"
                                                        >
                                                            <?= $data['full_name'] ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            <?php else:?>
                                                <select name="ada_approver" id="ada_approver" class="form-control select2 form-select-sm show-tick">
                                                    <option selected value="">Choose...</option>
                                                    <?php foreach($adaapprover as $data): ?>
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
                            </div>
                        </div>

                        <div class="col-sm-6">
                            <div class="row align-items-center">
                                
                                <div class="col-sm-6 d-flex align-items-center justify-content-end">
                                    <span class="fw-semibold me-2">Net Amount Payable:</span>
                                </div>

                                <div class="col-sm-6">
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text">₱</span>
                                        <input type="text" id="net_amount_payable"
                                            class="form-control text-end fw-bolder"
                                            readonly>
                                    </div>
                                </div>

                            </div>
                        </div>

                    </div>
                </div>
                <hr>
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
                            <th class="text-center">Action</th>
                            <th class="text-center" style="width:250px;">LDDAP-ADA No.</th>
                            <th class="text-center">MDS Branch</th>
                            <th class="text-center">MDS Account No.</th>
                            <th class="text-center">Fund Cluster</th>
                            <th class="text-center">LDDAP-ADA Date</th>
                        </tr>
                    </thead>
                    <tbody class="align-middle">
                        <?php if(!empty($dvhddata)):
                            foreach ($dvhddata as $data):
                                $dt_recid = $data['recid'];
                                $lddapadano = $data['lddapadano'];
                                $mds_branch = $data['mds_branch'];
                                $mds_accountno = $data['mds_accountno'];
                                $lddapada_date = $data['lddapada_date'];
                                $fund_cluster_code = $data['fund_cluster_code'];

                        ?>
                        <tr>
                            <td class="text-center align-middle">
                                <div class="d-flex justify-content-center gap-2">
                                    <a class="text-info nav-icon-hover fs-6" 
                                    href="mylddapada?meaction=MAIN&recid=<?= $dt_recid ?>" 
                                    title="Edit Transaction">
                                    <i class="ti ti-edit"></i>
                                    </a>
                                    <button class="btn btn-sm fs-6 text-warning p-0 border-0 bg-transparent" 
                                            onclick="__mysys_lddapada_ent.__showPdfInModal('<?= base_url('mylddapada?meaction=PRINT-LDDAPADA&recid='.$dt_recid) ?>')" 
                                            title="Print LDDAPADA">
                                    <i class="ti ti-printer"></i>
                                    </button>
                                </div>
                            </td>
                            <td class="text-center"><?=$lddapadano;?></td>
                            <td class="text-center"><?=$mds_branch;?></td>
                            <td class="text-center"><?=$mds_accountno;?></td>
                            <td class="text-center"><?=$fund_cluster_code;?></td>
                            <td class="text-center"><?=$lddapada_date;?></td>
                        </tr>
                        <?php endforeach; endif;?>
                    </tbody>
                </table>
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
<script src="<?=base_url('assets/js/cashier/lddapada.js?v=1');?>"></script>
<script src="<?=base_url('assets/js/mysysapps.js');?>"></script>


<script>

var selectedDV = <?= json_encode($dv_list); ?>;

$(document).ready(function() {

    // =========================================
    // IF EDIT MODE (WITH RECID)
    // DO NOT RELOAD FROM AJAX
    // JUST COMPUTE EXISTING ROWS
    // =========================================
    <?php if(!empty($recid)): ?>

        renderSelectedDV();
        renumberRows();
        computeTotals();

    <?php else: ?>

        // =========================================
        // NEW RECORD
        // =========================================
        if (selectedDV.length > 0) {

            renderSelectedDV();

            var baseUrl = $('#__siteurl').data('mesiteurl');
            loadDVItems(baseUrl);
        }

    <?php endif; ?>

});

// ===============================
// ADD DV
// ===============================
$(document).on('click', '#btn_add_dv', function() {

    var baseUrl = $('#__siteurl').data('mesiteurl');
    var dv = $('#dv_lookup').val();

    if (!dv) {
        alert('Select DV first');
        return;
    }

    // prevent duplicate
    if (selectedDV.includes(dv)) {
        alert('Already added');
        return;
    }

    // Add to array
    selectedDV.push(dv);

    renderSelectedDV();
    loadDVItems(baseUrl);
});

// ===============================
// RENDER BADGES
// ===============================
function renderSelectedDV() {

    var html = '';

    selectedDV.forEach(function(dv) {

        html += `
            <span class="badge bg-primary me-1" data-dvno="${dv}">
                ${dv}
                <a href="javascript:void(0)" 
                   onclick="removeDV('${dv}')" 
                   class="ms-1 text-white text-decoration-none">
                   &times;
                </a>
            </span>
        `;
    });

    $('#selected_dvno_list').html(html);
}

// ===============================
// REMOVE DV
// ===============================
function removeDV(dv) {

    selectedDV = selectedDV.filter(p => p !== dv);

    renderSelectedDV();

    // remove existing row
    $('#dv_line_items tbody tr').each(function() {

        let rowDV = $(this).find('.dvno').val();

        if (rowDV == dv) {
            $(this).remove();
        }

    });

    renumberRows();
    computeTotals();
}

// ===============================
// RENUMBER TABLE ROWS
// ===============================
function renumberRows() {

    $('#dv_line_items tbody tr:visible').each(function(index) {

        $(this).find('td:eq(0)').html('<b>' + (index + 1) + '</b>');

    });
}

// ===============================
// COMPUTE TOTALS
// ===============================
function computeTotals() {

    let totalNet = 0;

    $('#dv_line_items tbody tr:visible').each(function() {

        let net = parseFloat($(this).find('.net_amount').val()) || 0;

        totalNet += net;

    });

    $('#net_amount_payable').val(
        totalNet.toLocaleString('en-PH', {
            minimumFractionDigits: 2
        })
    );
}

// ===============================
// LOAD DV ITEMS
// ===============================
function loadDVItems(baseUrl) {

    if (selectedDV.length === 0) {

        $('#dv_line_items tbody tr:visible').remove();

        computeTotals();

        return;
    }

    $.ajax({

        url: baseUrl + 'mylddapada?meaction=LOAD-DV',

        type: 'POST',

        data: {
            dvno: selectedDV,
        },

        dataType: 'json',

        success: function(res) {

            // clear rows except template
            $('#dv_line_items tbody tr:visible').remove();

            var rowTemplate = $('#dv_line_items tbody tr:first');

            // map
            var dataMap = {};

            $.each(res, function(i, row) {

                dataMap[row.dvno] = row;

            });

            // preserve order
            $.each(selectedDV, function(i, dvNumber) {

                var row = dataMap[dvNumber];

                if (row) {

                    var newRow = rowTemplate.clone().show();

                    newRow.find('.dvno').val(row.dvno);
                    newRow.find('.payee_name').val(row.payee_name);
                    newRow.find('.payee_account_num').val(row.payee_account_num);
                    newRow.find('.serialno').val(row.serialno);
                    newRow.find('.allotment_class').val(row.uacs_code);
                    newRow.find('.gross_amount').val(row.gross_amount);
                    newRow.find('.total_deduction').val(row.total_deduction);
                    newRow.find('.net_amount').val(row.net_amount);

                    $('#dv_line_items tbody').append(newRow);
                }

            });

            renumberRows();
            computeTotals();
        },

        error: function(xhr) {

            console.log(xhr.responseText);

            alert('Error loading DV items');

        }
    });
}

// ===============================
// AUTO COMPUTE
// ===============================
$(document).on('input', '.net_amount', function() {

    computeTotals();

});

</script>

<script>
    __mysys_lddapada_ent.__lddapada_saving();
</script>

<?php
    echo view('templates/myfooter.php');
?>


