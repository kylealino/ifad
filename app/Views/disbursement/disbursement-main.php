<?php
$this->request = \Config\Services::request();
$this->db = \Config\Database::connect();
$this->mydisbursement = model('App\Models\MyDisbursementModel');
$action = $this->request->getPostGet('action');
$recid = $this->request->getPostGet('recid');
$ors_id = $this->request->getPostGet('ors_id');

$disbursement_date = '';
$particulars = '';
$funding_source = '';
$payee_name = '';
$payee_office = '';
$payee_address = '';
$certified_a = '';
$certified_b = '';
$position_a = '';
$position_b = '';
$serialno = '';
$dvno = '';
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
$status_msg_b = "";

$is_vatable = "";
$vat_percent = "";
$ewt_percent = "";
$pt_percent = "";
$category = "";

if((!empty($recid) || !is_null($recid)) && (empty($ors_id))) { 
    $query = $this->db->query("
    SELECT
        `disbursement_date`,
        `particulars`,
        `funding_source`,
        `payee_name`,
        `payee_office`,
        `payee_address`,
        `certified_a`,
        `certified_b`,
        `position_a`,
        `position_b`,
        `serialno`,
        `dvno`,
        `fund_cluster_code`,
        `is_pending`,
        `is_approved_certa`,
        `is_disapproved_certa`,
        `is_approved_certb`,
        `is_disapproved_certb`,
        `certa_remarks`,
        `certb_remarks`,
        `certa_approver`,
        `certb_approver`,
        `is_vatable`,
        `vat_percent`,
        `ewt_percent`,
        `pt_percent`,
        `category`
    FROM
        `tbl_disbursement_hd`
    WHERE 
        `recid` = '$recid'"
    );

    $data = $query->getRowArray();
    $disbursement_date = $data['disbursement_date'];
    $particulars = $data['particulars'];
    $funding_source = $data['funding_source'];
    $payee_name = $data['payee_name'];
    $payee_office = $data['payee_office'];
    $payee_address = $data['payee_address'];
    $certified_a = $data['certified_a'];
    $position_a = $data['position_a'];
    $certified_b = $data['certified_b'];
    $position_b = $data['position_b'];
    $serialno = $data['serialno'];
    $dvno = $data['dvno'];
    $fund_cluster_code = $data['fund_cluster_code'];
    $is_pending = $data['is_pending'];
    $is_approved_certa = $data['is_approved_certa'];
    $is_disapproved_certa = $data['is_disapproved_certa'];
    $is_approved_certb = $data['is_approved_certb'];
    $is_disapproved_certb = $data['is_disapproved_certb'];
    $certa_remarks = $data['certa_remarks'];
    $certb_remarks = $data['certb_remarks'];
    $certa_approver = $data['certa_approver'];
    $certb_approver = $data['certb_approver'];
    $is_vatable = $data['is_vatable'];
    $vat_percent = $data['vat_percent'];
    $ewt_percent = $data['ewt_percent'];
    $pt_percent = $data['pt_percent'];
    $category = $data['category'];

    if ($action == 'certifya_appr_pending') {
        $MDL_jsscript = "
        <script>
            __mysys_disbursement_ent.__approve_certa();
            __mysys_disbursement_ent.__disapprove_certa();
        </script>
       ";	
    }elseif ($action == 'is_disapproved_certa') {
        $MDL_jsscript = "
        <script>
           __mysys_disbursement_ent.__disapprove_certa();
        </script>
       ";	
    }elseif ($action == 'is_approved_certa') {
        $MDL_jsscript = "
        <script>
           __mysys_disbursement_ent.__approve_certa();
        </script>
       ";	
    }elseif ($action == 'certifyb_appr_pending') {
        $MDL_jsscript = "
        <script>
           __mysys_disbursement_ent.__approve_certb();
           __mysys_disbursement_ent.__disapprove_certb();
        </script>
       ";
    }elseif ($action == 'is_disapproved_certb') {
        $MDL_jsscript = "
        <script>
           __mysys_disbursement_ent.__disapprove_certb();
        </script>
       ";	
    }elseif ($action == 'is_approved_certb') {
        $MDL_jsscript = "
        <script>
           __mysys_disbursement_ent.__approve_certb();
        </script>
       ";	
    }else{
        $MDL_jsscript = "

       ";	
    }

}

if(!empty($ors_id) || !is_null($ors_id)) { 
    $query = $this->db->query("
    SELECT
        `ors_date`,
        `particulars`,
        `funding_source`,
        `payee_name`,
        `payee_office`,
        `payee_address`,
        `certified_a`,
        `certified_b`,
        `position_a`,
        `position_b`,
        `serialno`,
        '' AS `dvno`,
        '' AS `fund_cluster_code`,
        `is_pending`,
        `is_approved_certa`,
        `is_disapproved_certa`,
        `is_approved_certb`,
        `is_disapproved_certb`,
        `certa_remarks`,
        `certb_remarks`,
        `certa_approver`,
        `certb_approver`
    FROM
        `tbl_ors_hd`
    WHERE 
        `recid` = '$ors_id'"
    );

    $data = $query->getRowArray();
    $disbursement_date = $data['ors_date'];
    $particulars = $data['particulars'];
    $funding_source = $data['funding_source'];
    $payee_name = $data['payee_name'];
    $payee_office = $data['payee_office'];
    $payee_address = $data['payee_address'];
    $certified_a = $data['certified_a'];
    $position_a = $data['position_a'];
    $certified_b = $data['certified_b'];
    $position_b = $data['position_b'];
    $serialno = $data['serialno'];
    $dvno = $data['dvno'];
    $fund_cluster_code = $data['fund_cluster_code'];
    $is_pending = $data['is_pending'];
    $is_approved_certa = $data['is_approved_certa'];
    $is_disapproved_certa = $data['is_disapproved_certa'];
    $is_approved_certb = $data['is_approved_certb'];
    $is_disapproved_certb = $data['is_disapproved_certb'];
    $certa_remarks = $data['certa_remarks'];
    $certb_remarks = $data['certb_remarks'];
    $certa_approver = $data['certa_approver'];
    $certb_approver = $data['certb_approver'];

    if ($action == 'certifya_appr_pending') {
        $MDL_jsscript = "
        <script>
            __mysys_disbursement_ent.__approve_certa();
            __mysys_disbursement_ent.__disapprove_certa();
        </script>
       ";	
    }elseif ($action == 'is_disapproved_certa') {
        $MDL_jsscript = "
        <script>
           __mysys_disbursement_ent.__disapprove_certa();
        </script>
       ";	
    }elseif ($action == 'is_approved_certa') {
        $MDL_jsscript = "
        <script>
           __mysys_disbursement_ent.__approve_certa();
        </script>
       ";	
    }elseif ($action == 'certifyb_appr_pending') {
        $MDL_jsscript = "
        <script>
           __mysys_disbursement_ent.__approve_certb();
           __mysys_disbursement_ent.__disapprove_certb();
        </script>
       ";
    }elseif ($action == 'is_disapproved_certb') {
        $MDL_jsscript = "
        <script>
           __mysys_disbursement_ent.__disapprove_certb();
        </script>
       ";	
    }elseif ($action == 'is_approved_certb') {
        $MDL_jsscript = "
        <script>
           __mysys_disbursement_ent.__approve_certb();
        </script>
       ";	
    }else{
        $MDL_jsscript = "

       ";	
    }

}

if ($action == 'certifya_appr_pending') {
    $status_color = 'text-warning';
    $status_msg = 'For Approval';
    $status_color_b = 'text-warning';
    $status_msg_b = 'For Approval';
}elseif ($action == 'certifyb_appr_pending'){
    if ($is_approved_certa == '1' && $is_disapproved_certa == '0'&& $is_approved_certb == '0' && $is_disapproved_certb == '0') {
        $status_color = 'text-success';
        $status_msg = 'Approved by ' . $certa_approver;
        $status_color_b = 'text-warning';
        $status_msg_b = 'For Approval';
    }elseif($is_approved_certa == '1' && $is_disapproved_certa == '0'&& $is_approved_certb == '0' && $is_disapproved_certb == '1'){
        $status_color = 'text-success';
        $status_msg = 'Approved by ' . $certa_approver;
        $status_color_b = 'text-danger';
        $status_msg_b = 'Disapproved';
    }else{
        $status_color = 'text-warning';
        $status_msg = 'For Approval';
        $status_color_b = 'text-warning';
        $status_msg_b = 'For Approval';
    }

}elseif ($action == 'transaction_viewing') {
    if ($is_approved_certa == '1' && $is_approved_certb == '0' && $is_disapproved_certb == '0') {
        $status_color = 'text-success';
        $status_msg = 'Approved by ' . $certa_approver;
        $status_color_b = 'text-warning';
        $status_msg_b = 'For Approval';
    }elseif ($is_approved_certa == '1' && $is_approved_certb == '1') {
        $status_color_b = 'text-success';
        $status_msg_b = 'Approved by ' . $certb_approver;
        $status_color = 'text-success';
        $status_msg = 'Approved by ' . $certa_approver;
    }elseif($is_approved_certa == '1' && $is_disapproved_certb == '1'){
        $status_color = 'text-success';
        $status_msg = 'Approved by ' . $certa_approver;
        $status_color_b = 'text-danger';
        $status_msg_b = 'Disapproved';
    }else{
        $status_color = 'text-warning';
        $status_msg = 'For Approval';
        $status_color_b = 'text-warning';
        $status_msg_b = 'For Approval';
    }
}else{
    $status_color_b = 'text-info';
    $status_msg_b = '';
}


echo view('templates/myheader.php');
?>

<div class="container-fluid">
    <div class="row me-mydisbursementburs-appr-outp-msg mx-0">
    </div>
    
    <input type="hidden" id="__siteurl" data-mesiteurl="<?=site_url();?>" />
    <div class="row mb-2 mt-0">
        <h4 class="fw-semibold mb-8">Disbursement</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a class="text-muted text-decoration-none" href="<?=site_url();?>"><i class="ti ti-home fs-5"></i></a>
            </li>
            <li class="breadcrumb-item" aria-current="page">Accounting</li>
            <li class="breadcrumb-item" aria-current="page"><span class="form-label fw-bold">Disbursement</span></li>
            </ol>
        </nav>
    </div>

    <div class="card rounded">
        <div class="row mydisbursement-outp-msg mx-0">

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
                    <?php if($action == 'certifya_appr_pending' || $action == 'certifyb_appr_pending'):?>
                        <button type="button" id="btn_approve" name="btn_approve" class="btn_approve btn btn-sm btn-success">
                            Approve
                        </button>
                        <button type="button" id="btn_disapprove" name="btn_disapprove" class="btn_disapprove btn btn-sm btn-danger">
                            Dispprove
                        </button>
                    <?php endif;?>
                </div>
            </div>
        </div>						
        <div class="card-body p-0 px-4 py-2 my-2">
            <form action="<?=site_url();?>mydisbursement?meaction=MAIN-SAVE" method="post" class="mydisbursement-validation">
                <input type="hidden" class="form-control form-control-sm" id="recid" name="recid" value="<?=$recid;?>"/>

                <div class="row">
                    <div class="col-sm-6">
                        <div class="row">
                            <div class="col-sm-12">
                                <div class="row mb-2">
                                    <div class="col-sm-4">
                                        <span>Particulars</span>
                                    </div>
                                    <div class="col-sm-8">
                                        <textarea name="particulars" id="particulars" placeholder="" rows="4" class="form-control form-control-sm"><?= (!empty($recid) || !empty($ors_id)) ? $particulars : ''; ?></textarea>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-12">
                                <div class="row mb-2">
                                    <div class="col-sm-4">
                                        <span>Funding Source:</span>
                                    </div>
                                    <div class="col-sm-8">
                                        <select name="" id="funding_source" class="form-select form-select-sm">
                                        <?php if(!empty($recid)):?>
                                            <option value="<?=$funding_source;?>"><?=$funding_source;?></option>
                                        <?php elseif(!empty($ors_id)):?>
                                            <option value="<?=$funding_source;?>"><?=$funding_source;?></option>
                                        <?php else:?>
                                            <option value="">Choose...</option>
                                        <?php endif;?>
                                            <option value="101101">101101</option>
                                            <option value="102101">102101</option>
                                            <option value="102406">102406</option>
                                            <option value="102407">102407</option>
                                            <option value="104102">104102</option>
                                            <option value="105462">105462</option>
                                            <option value="308601">308601</option>
                                            <option value="308602">308602</option>
                                            <option value="308603">308603</option>
                                        </select>
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
                                        <span>Date</span>
                                    </div>
                                    <div class="col-sm-8">
                                        <input type="date" name="disbursement_date" id="disbursement_date" value="<?=$disbursement_date;?>" class="form form-control form-control-sm">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-sm-4">
                                <span class="fw-bold">Payee:</span>
                            </div>
                            <div class="col-sm-8">
                                <input type="text" 
                                    name="payee_name" 
                                    id="payee_name"
                                    class="form-control form-control-sm payee_name"
                                    value="<?= (!empty($recid) || !empty($ors_id)) ? $payee_name : '' ?>"
                                    placeholder="Type or select payee...">
                            </div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-sm-4">
                                <span class="fw-bold">Office:</span>
                            </div>
                            <div class="col-sm-8">
                                <input type="text" id="payee_office" name="payee_office"
                                    value="<?=$payee_office;?>"
                                    class="form-control form-control-sm" disabled />
                            </div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-sm-4">
                                <span class="fw-bold">Address:</span>
                            </div>
                            <div class="col-sm-8">
                                <input type="text" id="payee_address" name="payee_address"
                                    value="<?=$payee_address;?>"
                                    class="form-control form-control-sm" disabled />
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="row mb-2">
                            <div class="col-sm-4">
                                <span class="fw-bold">Serial No.:</span>
                            </div>
                            <div class="col-sm-8">
                                <input type="text" id="serialno" name="serialno" value="<?=$serialno;?>" class="form-control form-control-sm"/>
                            </div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-sm-4">
                                <span class="fw-bold">DV No.:</span>
                            </div>
                            <div class="col-sm-8">
                                <input type="text" id="dvno" name="dvno" value="<?=$dvno;?>" class="form-control form-control-sm"/>
                            </div>
                        </div>

                        <hr>

                        <div class="row mb-2">
                            <div class="col-sm-12">
                                <div class="row mb-2">
                                    <div class="col-sm-4">
                                        <span>Category:</span>
                                    </div>
                                    <div class="col-sm-8">
                                        <select name="" id="category" class="form-select form-select-sm">
                                        <?php if(!empty($recid)):?>
                                            <option value="<?=$category;?>"><?=$category;?></option>
                                        <?php elseif(!empty($ors_id)):?>
                                            <option value="<?=$category;?>"><?=$category;?></option>
                                        <?php else:?>
                                            <option value="">Choose...</option>
                                        <?php endif;?>
                                            <option value="Goods">Goods</option>
                                            <option value="Services">Services</option>
                                            <option value="Manual">Manual</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
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

                        <hr>
                        
                        <div class="row mb-2">
                            <div class="col-sm-4">
                                <span class="fw-semibold">Gross Amount:</span>
                            </div>
                            <div class="col-sm-8">
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text">₱</span>
                                    <input type="text" id="gross_amount"
                                        class="form-control text-end fw-bold"
                                        readonly>
                                </div>
                            </div>
                        </div>


                        <input type="hidden" id="total_deduction" name="total_deduction">


                        <div class="row mb-2">
                            <div class="col-sm-4">
                                <span class="fw-semibold">Net Amount Payable:</span>
                            </div>
                            <div class="col-sm-8">
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text">₱</span>
                                    <input type="text" id="net_amount"
                                        class="form-control text-end fw-bold text-success"
                                        readonly>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <hr>

                <div class="row mb-2">
                    <div class="col-sm-12">
                        <!-- Nav tabs -->
                        <ul class="nav nav-pills mb-3 gap-2" role="tablist">
                            <li class="nav-item" role="presentation">
                                <a class="nav-link active rounded-pill px-3 fs-3 fw-semibold" data-bs-toggle="tab" href="#ps-pill" role="tab">
                                I. Personal Services
                                </a>
                            </li>
                            <li class="nav-item" role="presentation">
                                <a class="nav-link rounded-pill px-3 fs-3 fw-semibold" data-bs-toggle="tab" href="#mooe-pill" role="tab">
                                II. Maintenance
                                </a>
                            </li>
                            <li class="nav-item" role="presentation">
                                <a class="nav-link rounded-pill px-3 fs-3 fw-semibold" data-bs-toggle="tab" href="#co-pill" role="tab">
                                III. Capital Outlay
                                </a>
                            </li>
                        </ul>

                        <!-- Tab panes -->
                        <div class="tab-content border">
                            <!-- PS TAB CONTENT -->
                            <div class="tab-pane active p-3" id="ps-pill" role="tabpanel">
                                <div class="row">
                                    <div class="table-responsive pe-2 ps-0">
                                        <div class="col-md-12 mb-2">
                                            <span class="ms-3 fw-bold">Direct Cost:</span>
                                            <table id="budget_line_items" class="table-sm table-striped budgetdata-list">
                                                <thead>
                                                    <th class="text-center">
                                                        <a class="text-info px-2 fs-7 bg-hover-primary nav-icon-hover position-relative z-index-5" id="btn_trxjournalitem_add" href="javascript:__mysys_disbursement_ent.my_add_budget_line();"><i class="ti ti-new-section"></i></a>
                                                    </th>
                                                    <th class="text-center align-middle">Responsibility Code</th>
                                                    <th class="text-center align-middle">MFO/PAP</th>
                                                    <th class="text-center align-middle">Amount</th>
                                                </thead>
                                                <tbody>
                                                    <tr style="display:none;">
                                                        <td class="text-center align-middle">
                                                            <div class="d-inline-flex gap-1 justify-content-center">
                                                                <a class="text-danger fs-5 bg-hover-danger nav-icon-hover"
                                                                href="javascript:void(0)"
                                                                onclick="$(this).closest('tr').remove();">
                                                                    <i class="ti ti-trash"></i>
                                                                </a>
                                                                <a class="text-success fs-5 bg-hover-primary nav-icon-hover"
                                                                href="javascript:void(0)"
                                                                title="Add rows above"
                                                                onclick="__mysys_disbursement_ent.my_add_budget_line_above(this);">
                                                                    <i class="ti ti-plus"></i>
                                                                </a>
                                                            </div>
                                                        </td>
                                                        <td class="align-middle" nowrap>
                                                            <input type="text" id="responsibility_code"  value="" size="35"  name="responsibility_code" class="responsibility_code text-center">
                                                        </td>
                                                        <td class="align-middle" nowrap>
                                                            <input type="text" id="mfopaps_code"  value="" size="25" name="mfopaps_code" class="mfopaps_code text-center"/>
                                                        </td>
                                                        <td class="text-center align-middle" nowrap>
                                                            <input type="number" id="amount"  value="" size="25" step="any" name="amount" class="amount text-center"/>
                                                        </td>
                                                    </tr>
                                                    <?php if(!empty($recid) && empty($ors_id)):
                                                        $query = $this->db->query("
                                                        SELECT
                                                            `recid`,
                                                            `responsibility_code`,
                                                            `mfopaps_code`,
                                                            SUM(`amount`) AS amount
                                                        FROM
                                                            `tbl_disbursement_direct_ps_dt`
                                                        WHERE 
                                                            `project_id` = '$recid'
                                                        GROUP BY 
                                                            `responsibility_code`
                                                        ");
                                                        $result = $query->getResultArray();
                                                        foreach ($result as $data):
                                                            $dt_id = $data['recid'];
                                                            $responsibility_code = $data['responsibility_code'];
                                                            $mfopaps_code = $data['mfopaps_code'];
                                                            $amount = $data['amount'];
                                                    ?>
                                                    <tr>
                                                        <td class="text-center align-middle">
                                                            <div class="d-inline-flex gap-1 justify-content-center">
                                                                <a class="text-danger fs-5 bg-hover-danger nav-icon-hover"
                                                                href="javascript:void(0)"
                                                                onclick="$(this).closest('tr').remove();">
                                                                    <i class="ti ti-trash"></i>
                                                                </a>
                                                                <a class="text-success fs-5 bg-hover-primary nav-icon-hover"
                                                                href="javascript:void(0)"
                                                                title="Add rows above"
                                                                onclick="__mysys_disbursement_ent.my_add_budget_line_above(this);">
                                                                    <i class="ti ti-plus"></i>
                                                                </a>
                                                            </div>
                                                        </td>
                                                        <td class="align-middle" nowrap>
                                                            <input type="text" id="responsibility_code"  value="<?=$responsibility_code;?>" size="35"  name="responsibility_code" class="responsibility_code text-center">
                                                        </td>
                                                        <td class="align-middle" nowrap>
                                                            <input type="text" id="mfopaps_code"  value="<?=$mfopaps_code;?>" size="25" name="mfopaps_code" class="mfopaps_code text-center"/>
                                                        </td>
                                                        <td class="text-center align-middle" nowrap>
                                                            <input type="number" id="amount"  value="<?=$amount;?>" size="25" step="any" name="amount" class="amount text-center"/>
                                                        </td>
                                                    </tr>
                                                    <?php endforeach; endif;?>
                                                    <?php if(!empty($ors_id) && !empty($recid)):
                                                        $query = $this->db->query("
                                                        SELECT
                                                            `recid`,
                                                            `responsibility_code`,
                                                            `mfopaps_code`,
                                                            SUM(`amount`) AS amount
                                                        FROM
                                                            `tbl_ors_direct_ps_dt`
                                                        WHERE 
                                                            `project_id` = '$ors_id'
                                                        GROUP BY 
                                                            `responsibility_code`
                                                        ");
                                                        $result = $query->getResultArray();
                                                        foreach ($result as $data):
                                                            $dt_id = $data['recid'];
                                                            $responsibility_code = $data['responsibility_code'];
                                                            $mfopaps_code = $data['mfopaps_code'];
                                                            $amount = $data['amount'];
                                                    ?>
                                                    <tr>
                                                        <td class="text-center align-middle">
                                                            <div class="d-inline-flex gap-1 justify-content-center">
                                                                <a class="text-danger fs-5 bg-hover-danger nav-icon-hover"
                                                                href="javascript:void(0)"
                                                                onclick="$(this).closest('tr').remove();">
                                                                    <i class="ti ti-trash"></i>
                                                                </a>
                                                                <a class="text-success fs-5 bg-hover-primary nav-icon-hover"
                                                                href="javascript:void(0)"
                                                                title="Add rows above"
                                                                onclick="__mysys_disbursement_ent.my_add_budget_line_above(this);">
                                                                    <i class="ti ti-plus"></i>
                                                                </a>
                                                            </div>
                                                        </td>
                                                        <td class="align-middle" nowrap>
                                                            <input type="text" id="responsibility_code"  value="<?=$responsibility_code;?>" size="35"  name="responsibility_code" class="responsibility_code text-center">
                                                        </td>
                                                        <td class="align-middle" nowrap>
                                                            <input type="text" id="mfopaps_code"  value="<?=$mfopaps_code;?>" size="25" name="mfopaps_code" class="mfopaps_code text-center"/>
                                                        </td>
                                                        <td class="text-center align-middle" nowrap>
                                                            <input type="number" id="amount"  value="<?=$amount;?>" size="25" step="any" name="amount" class="amount text-center"/>
                                                        </td>
                                                    </tr>
                                                    <?php endforeach; endif;?>
                                                    <?php if(!empty($ors_id)):
                                                        $query = $this->db->query("
                                                        SELECT
                                                            `recid`,
                                                            `responsibility_code`,
                                                            `mfopaps_code`,
                                                            SUM(`amount`) AS amount
                                                        FROM
                                                            `tbl_ors_direct_ps_dt`
                                                        WHERE 
                                                            `project_id` = '$ors_id'
                                                        GROUP BY 
                                                            `responsibility_code`
                                                        ");
                                                        $result = $query->getResultArray();
                                                        foreach ($result as $data):
                                                            $dt_id = $data['recid'];
                                                            $responsibility_code = $data['responsibility_code'];
                                                            $mfopaps_code = $data['mfopaps_code'];
                                                            $amount = $data['amount'];
                                                    ?>
                                                    <tr>
                                                        <td class="text-center align-middle">
                                                            <div class="d-inline-flex gap-1 justify-content-center">
                                                                <a class="text-danger fs-5 bg-hover-danger nav-icon-hover"
                                                                href="javascript:void(0)"
                                                                onclick="$(this).closest('tr').remove();">
                                                                    <i class="ti ti-trash"></i>
                                                                </a>
                                                                <a class="text-success fs-5 bg-hover-primary nav-icon-hover"
                                                                href="javascript:void(0)"
                                                                title="Add rows above"
                                                                onclick="__mysys_disbursement_ent.my_add_budget_line_above(this);">
                                                                    <i class="ti ti-plus"></i>
                                                                </a>
                                                            </div>
                                                        </td>
                                                        <td class="align-middle" nowrap>
                                                            <input type="text" id="responsibility_code"  value="<?=$responsibility_code;?>" size="35"  name="responsibility_code" class="responsibility_code text-center">
                                                        </td>
                                                        <td class="align-middle" nowrap>
                                                            <input type="text" id="mfopaps_code"  value="<?=$mfopaps_code;?>" size="25" name="mfopaps_code" class="mfopaps_code text-center"/>
                                                        </td>
                                                        <td class="text-center align-middle" nowrap>
                                                            <input type="number" id="amount"  value="<?=$amount;?>" size="25" step="any" name="amount" class="amount text-center"/>
                                                        </td>
                                                    </tr>
                                                    <?php endforeach; endif;?>
                                                </tbody>
                                            </table>
                                        </div>
                                        <hr>
                                        <div class="col-sm-12">
                                            <span class="ms-3 fw-bold">Indirect Cost:</span>
                                            <table id="budget_indirect_line_items" class="table-sm table-striped budgetdata-indirect-list">
                                                <thead>
                                                    <th class="text-center">
                                                        <a class="text-info px-2 fs-7 bg-hover-primary nav-icon-hover position-relative z-index-5" id="btn_trxjournalitem_add" href="javascript:__mysys_disbursement_ent.my_add_budget_indirect_line();"><i class="ti ti-new-section"></i></a>
                                                    </th>
                                                    <th class="text-center align-middle">Responsibility Code</th>
                                                    <th class="text-center align-middle">MFO/PAP</th>
                                                    <th class="text-center align-middle">Amount</th>
                                                </thead>
                                                <tbody>
                                                    <tr style="display:none;">
                                                        <td class="text-center align-middle">
                                                            <div class="d-inline-flex gap-1 justify-content-center">
                                                                <a class="text-danger fs-5 bg-hover-danger nav-icon-hover"
                                                                href="javascript:void(0)"
                                                                onclick="$(this).closest('tr').remove();">
                                                                    <i class="ti ti-trash"></i>
                                                                </a>
                                                                <a class="text-success fs-5 bg-hover-primary nav-icon-hover"
                                                                href="javascript:void(0)"
                                                                title="Add rows above"
                                                                onclick="__mysys_disbursement_ent.my_add_budget_indirect_line_above(this);">
                                                                    <i class="ti ti-plus"></i>
                                                                </a>
                                                            </div>
                                                        </td>
                                                        <td class="align-middle" nowrap>
                                                            <input type="text" id="responsibility_code"  value="" size="35"  name="responsibility_code" class="responsibility_code text-center">
                                                        </td>
                                                        <td class="align-middle" nowrap>
                                                            <input type="text" id="mfopaps_code"  value="" size="25" name="mfopaps_code" class="mfopaps_code text-center"/>
                                                        </td>
                                                        <td class="text-center align-middle" nowrap>
                                                            <input type="number" id="amount"  value="" size="25" step="any" name="amount" class="amount text-center"/>
                                                        </td>
                                                    </tr>
                                                    <?php if(!empty($recid) && empty($ors_id)):
                                                        $query = $this->db->query("
                                                        SELECT
                                                            `recid`,
                                                            `responsibility_code`,
                                                            `mfopaps_code`,
                                                            SUM(`amount`) AS amount
                                                        FROM
                                                            `tbl_disbursement_indirect_ps_dt`
                                                        WHERE 
                                                            `project_id` = '$recid'
                                                        GROUP BY 
                                                            `responsibility_code`"
                                                        );
                                                        $result = $query->getResultArray();
                                                        foreach ($result as $data):
                                                            $dt_id = $data['recid'];
                                                            $responsibility_code = $data['responsibility_code'];
                                                            $mfopaps_code = $data['mfopaps_code'];
                                                            $amount = $data['amount'];
                                                    ?>
                                                    <tr>
                                                        <td class="text-center align-middle">
                                                            <div class="d-inline-flex gap-1 justify-content-center">
                                                                <a class="text-danger fs-5 bg-hover-danger nav-icon-hover"
                                                                href="javascript:void(0)"
                                                                onclick="$(this).closest('tr').remove();">
                                                                    <i class="ti ti-trash"></i>
                                                                </a>
                                                                <a class="text-success fs-5 bg-hover-primary nav-icon-hover"
                                                                href="javascript:void(0)"
                                                                title="Add rows above"
                                                                onclick="__mysys_disbursement_ent.my_add_budget_line_above(this);">
                                                                    <i class="ti ti-plus"></i>
                                                                </a>
                                                            </div>
                                                        </td>
                                                        <td class="align-middle" nowrap>
                                                            <input type="text" id="responsibility_code"  value="<?=$responsibility_code;?>" size="35"  name="responsibility_code" class="responsibility_code text-center">
                                                        </td>
                                                        <td class="align-middle" nowrap>
                                                            <input type="text" id="mfopaps_code"  value="<?=$mfopaps_code;?>" size="25" name="mfopaps_code" class="mfopaps_code text-center"/>
                                                        </td>
                                                        <td class="text-center align-middle" nowrap>
                                                            <input type="number" id="amount"  value="<?=$amount;?>" size="25" step="any" name="amount" class="amount text-center"/>
                                                        </td>
                                                    </tr>
                                                    <?php endforeach; endif;?>
                                                    <?php if(!empty($ors_id) && !empty($recid)):
                                                        $query = $this->db->query("
                                                        SELECT
                                                            `recid`,
                                                            `responsibility_code`,
                                                            `mfopaps_code`,
                                                            SUM(`amount`) AS amount
                                                        FROM
                                                            `tbl_ors_indirect_ps_dt`
                                                        WHERE 
                                                            `project_id` = '$ors_id'
                                                        GROUP BY 
                                                            `responsibility_code`"
                                                        );
                                                        $result = $query->getResultArray();
                                                        foreach ($result as $data):
                                                            $dt_id = $data['recid'];
                                                            $responsibility_code = $data['responsibility_code'];
                                                            $mfopaps_code = $data['mfopaps_code'];
                                                            $amount = $data['amount'];
                                                    ?>
                                                    <tr>
                                                        <td class="text-center align-middle">
                                                            <div class="d-inline-flex gap-1 justify-content-center">
                                                                <a class="text-danger fs-5 bg-hover-danger nav-icon-hover"
                                                                href="javascript:void(0)"
                                                                onclick="$(this).closest('tr').remove();">
                                                                    <i class="ti ti-trash"></i>
                                                                </a>
                                                                <a class="text-success fs-5 bg-hover-primary nav-icon-hover"
                                                                href="javascript:void(0)"
                                                                title="Add rows above"
                                                                onclick="__mysys_disbursement_ent.my_add_budget_line_above(this);">
                                                                    <i class="ti ti-plus"></i>
                                                                </a>
                                                            </div>
                                                        </td>
                                                        <td class="align-middle" nowrap>
                                                            <input type="text" id="responsibility_code"  value="<?=$responsibility_code;?>" size="35"  name="responsibility_code" class="responsibility_code text-center">
                                                        </td>
                                                        <td class="align-middle" nowrap>
                                                            <input type="text" id="mfopaps_code"  value="<?=$mfopaps_code;?>" size="25" name="mfopaps_code" class="mfopaps_code text-center"/>
                                                        </td>
                                                        <td class="text-center align-middle" nowrap>
                                                            <input type="number" id="amount"  value="<?=$amount;?>" size="25" step="any" name="amount" class="amount text-center"/>
                                                        </td>
                                                    </tr>
                                                    <?php endforeach; endif;?>
                                                    <?php if(!empty($ors_id)):
                                                        $query = $this->db->query("
                                                        SELECT
                                                            `recid`,
                                                            `responsibility_code`,
                                                            `mfopaps_code`,
                                                            SUM(`amount`) AS amount
                                                        FROM
                                                            `tbl_ors_indirect_ps_dt`
                                                        WHERE 
                                                            `project_id` = '$ors_id'
                                                        GROUP BY 
                                                            `responsibility_code`"
                                                        );
                                                        $result = $query->getResultArray();
                                                        foreach ($result as $data):
                                                            $dt_id = $data['recid'];
                                                            $responsibility_code = $data['responsibility_code'];
                                                            $mfopaps_code = $data['mfopaps_code'];
                                                            $amount = $data['amount'];
                                                    ?>
                                                    <tr>
                                                        <td class="text-center align-middle">
                                                            <div class="d-inline-flex gap-1 justify-content-center">
                                                                <a class="text-danger fs-5 bg-hover-danger nav-icon-hover"
                                                                href="javascript:void(0)"
                                                                onclick="$(this).closest('tr').remove();">
                                                                    <i class="ti ti-trash"></i>
                                                                </a>
                                                                <a class="text-success fs-5 bg-hover-primary nav-icon-hover"
                                                                href="javascript:void(0)"
                                                                title="Add rows above"
                                                                onclick="__mysys_disbursement_ent.my_add_budget_line_above(this);">
                                                                    <i class="ti ti-plus"></i>
                                                                </a>
                                                            </div>
                                                        </td>
                                                        <td class="align-middle" nowrap>
                                                            <input type="text" id="responsibility_code"  value="<?=$responsibility_code;?>" size="35"  name="responsibility_code" class="responsibility_code text-center">
                                                        </td>
                                                        <td class="align-middle" nowrap>
                                                            <input type="text" id="mfopaps_code"  value="<?=$mfopaps_code;?>" size="25" name="mfopaps_code" class="mfopaps_code text-center"/>
                                                        </td>
                                                        <td class="text-center align-middle" nowrap>
                                                            <input type="number" id="amount"  value="<?=$amount;?>" size="25" step="any" name="amount" class="amount text-center"/>
                                                        </td>
                                                    </tr>
                                                    <?php endforeach; endif;?>
                                                </tbody>
                                            </table>              
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- MOOE TAB CONTENT -->
                            <div class="tab-pane p-3" id="mooe-pill" role="tabpanel">
                                <div class="row">
                                    <div class="table-responsive pe-2 ps-0">
                                        <div class="col-md-12 mb-2">
                                            <span class="ms-3 fw-bold">Direct Cost:</span>
                                            <table id="budget_mooe_line_items" class="table-sm table-striped budgetmooedata-list">
                                                <thead>
                                                    <th class="text-center">
                                                        <a class="text-info px-2 fs-7 bg-hover-primary nav-icon-hover position-relative z-index-5" id="btn_trxjournalitem_add" href="javascript:__mysys_disbursement_ent.my_add_budget_mooe_line();"><i class="ti ti-new-section"></i></a>
                                                    </th>
                                                    <th class="text-center align-middle">Responsibility Code</th>
                                                    <th class="text-center align-middle">MFO/PAP</th>
                                                    <th class="text-center align-middle">Amount</th>
                                                </thead>
                                                <tbody>
                                                    <tr style="display:none;">
                                                        <td class="text-center align-middle">
                                                            <div class="d-inline-flex gap-1 justify-content-center">
                                                                <a class="text-danger fs-5 bg-hover-danger nav-icon-hover"
                                                                href="javascript:void(0)"
                                                                onclick="$(this).closest('tr').remove();">
                                                                    <i class="ti ti-trash"></i>
                                                                </a>
                                                                <a class="text-success fs-5 bg-hover-primary nav-icon-hover"
                                                                href="javascript:void(0)"
                                                                title="Add rows above"
                                                                onclick="__mysys_disbursement_ent.my_add_budget_mooe_line_above(this);">
                                                                    <i class="ti ti-plus"></i>
                                                                </a>
                                                            </div>
                                                        </td>
                                                        <td class="align-middle" nowrap>
                                                            <input type="text" id="responsibility_code"  value="" size="35"  name="responsibility_code" class="responsibility_code text-center">
                                                        </td>
                                                        <td class="align-middle" nowrap>
                                                            <input type="text" id="mfopaps_code"  value="" size="25" name="mfopaps_code" class="mfopaps_code text-center"/>
                                                        </td>
                                                        <td class="text-center align-middle" nowrap>
                                                            <input type="number" id="amount"  value="" size="25" step="any" name="amount" class="amount text-center"/>
                                                        </td>
                                                    </tr>
                                                    <?php if(!empty($recid) && empty($ors_id)):
                                                        $query = $this->db->query("
                                                        SELECT
                                                            `recid`,
                                                            `responsibility_code`,
                                                            `mfopaps_code`,
                                                            SUM(`amount`) AS amount
                                                        FROM
                                                            `tbl_disbursement_direct_mooe_dt`
                                                        WHERE 
                                                            `project_id` = '$recid'
                                                        GROUP BY 
                                                            `responsibility_code`
                                                        ");
                                                        $result = $query->getResultArray();
                                                        foreach ($result as $data):
                                                            $dt_id = $data['recid'];
                                                            $responsibility_code = $data['responsibility_code'];
                                                            $mfopaps_code = $data['mfopaps_code'];
                                                            $amount = $data['amount'];
                                                    ?>
                                                    <tr>
                                                        <td class="text-center align-middle">
                                                            <div class="d-inline-flex gap-1 justify-content-center">
                                                                <a class="text-danger fs-5 bg-hover-danger nav-icon-hover"
                                                                href="javascript:void(0)"
                                                                onclick="$(this).closest('tr').remove();">
                                                                    <i class="ti ti-trash"></i>
                                                                </a>
                                                                <a class="text-success fs-5 bg-hover-primary nav-icon-hover"
                                                                href="javascript:void(0)"
                                                                title="Add rows above"
                                                                onclick="__mysys_disbursement_ent.my_add_budget_mooe_line_above(this);">
                                                                    <i class="ti ti-plus"></i>
                                                                </a>
                                                            </div>
                                                        </td>
                                                        <td class="align-middle" nowrap>
                                                            <input type="text" id="responsibility_code"  value="<?=$responsibility_code;?>" size="35"  name="responsibility_code" class="responsibility_code text-center">
                                                        </td>
                                                        <td class="align-middle" nowrap>
                                                            <input type="text" id="mfopaps_code"  value="<?=$mfopaps_code;?>" size="25" name="mfopaps_code" class="mfopaps_code text-center"/>
                                                        </td>
                                                        <td class="text-center align-middle" nowrap>
                                                            <input type="number" id="amount"  value="<?=$amount;?>" size="25" step="any" name="amount" class="amount text-center"/>
                                                        </td>
                                                    </tr>
                                                    <?php endforeach; endif;?>
                                                    <?php if(!empty($ors_id)&& !empty($recid)):
                                                        $query = $this->db->query("
                                                        SELECT
                                                            `recid`,
                                                            `responsibility_code`,
                                                            `mfopaps_code`,
                                                            SUM(`amount`) AS amount
                                                        FROM
                                                            `tbl_ors_direct_mooe_dt`
                                                        WHERE 
                                                            `project_id` = '$ors_id'
                                                        GROUP BY 
                                                            `responsibility_code`
                                                        ");
                                                        $result = $query->getResultArray();
                                                        foreach ($result as $data):
                                                            $dt_id = $data['recid'];
                                                            $responsibility_code = $data['responsibility_code'];
                                                            $mfopaps_code = $data['mfopaps_code'];
                                                            $amount = $data['amount'];
                                                    ?>
                                                    <tr>
                                                        <td class="text-center align-middle">
                                                            <div class="d-inline-flex gap-1 justify-content-center">
                                                                <a class="text-danger fs-5 bg-hover-danger nav-icon-hover"
                                                                href="javascript:void(0)"
                                                                onclick="$(this).closest('tr').remove();">
                                                                    <i class="ti ti-trash"></i>
                                                                </a>
                                                                <a class="text-success fs-5 bg-hover-primary nav-icon-hover"
                                                                href="javascript:void(0)"
                                                                title="Add rows above"
                                                                onclick="__mysys_disbursement_ent.my_add_budget_mooe_line_above(this);">
                                                                    <i class="ti ti-plus"></i>
                                                                </a>
                                                            </div>
                                                        </td>
                                                        <td class="align-middle" nowrap>
                                                            <input type="text" id="responsibility_code"  value="<?=$responsibility_code;?>" size="35"  name="responsibility_code" class="responsibility_code text-center">
                                                        </td>
                                                        <td class="align-middle" nowrap>
                                                            <input type="text" id="mfopaps_code"  value="<?=$mfopaps_code;?>" size="25" name="mfopaps_code" class="mfopaps_code text-center"/>
                                                        </td>
                                                        <td class="text-center align-middle" nowrap>
                                                            <input type="number" id="amount"  value="<?=$amount;?>" size="25" step="any" name="amount" class="amount text-center"/>
                                                        </td>
                                                    </tr>
                                                    <?php endforeach; endif;?>
                                                    <?php if(!empty($ors_id)):
                                                        $query = $this->db->query("
                                                        SELECT
                                                            `recid`,
                                                            `responsibility_code`,
                                                            `mfopaps_code`,
                                                            SUM(`amount`) AS amount
                                                        FROM
                                                            `tbl_ors_direct_mooe_dt`
                                                        WHERE 
                                                            `project_id` = '$ors_id'
                                                        GROUP BY 
                                                            `responsibility_code`
                                                        ");
                                                        $result = $query->getResultArray();
                                                        foreach ($result as $data):
                                                            $dt_id = $data['recid'];
                                                            $responsibility_code = $data['responsibility_code'];
                                                            $mfopaps_code = $data['mfopaps_code'];
                                                            $amount = $data['amount'];
                                                    ?>
                                                    <tr>
                                                        <td class="text-center align-middle">
                                                            <div class="d-inline-flex gap-1 justify-content-center">
                                                                <a class="text-danger fs-5 bg-hover-danger nav-icon-hover"
                                                                href="javascript:void(0)"
                                                                onclick="$(this).closest('tr').remove();">
                                                                    <i class="ti ti-trash"></i>
                                                                </a>
                                                                <a class="text-success fs-5 bg-hover-primary nav-icon-hover"
                                                                href="javascript:void(0)"
                                                                title="Add rows above"
                                                                onclick="__mysys_disbursement_ent.my_add_budget_mooe_line_above(this);">
                                                                    <i class="ti ti-plus"></i>
                                                                </a>
                                                            </div>
                                                        </td>
                                                        <td class="align-middle" nowrap>
                                                            <input type="text" id="responsibility_code"  value="<?=$responsibility_code;?>" size="35"  name="responsibility_code" class="responsibility_code text-center">
                                                        </td>
                                                        <td class="align-middle" nowrap>
                                                            <input type="text" id="mfopaps_code"  value="<?=$mfopaps_code;?>" size="25" name="mfopaps_code" class="mfopaps_code text-center"/>
                                                        </td>
                                                        <td class="text-center align-middle" nowrap>
                                                            <input type="number" id="amount"  value="<?=$amount;?>" size="25" step="any" name="amount" class="amount text-center"/>
                                                        </td>
                                                    </tr>
                                                    <?php endforeach; endif;?>
                                                </tbody>
                                            </table>
                                        </div>
                                        <div class="col-sm-12">
                                            <span class="ms-3 fw-bold">Indirect Cost:</span>
                                            <table id="budget_mooe_indirect_line_items" class="table-sm table-striped budgetmooedata-indirect-list">
                                                <thead>
                                                    <th class="text-center">
                                                        <a class="text-info px-2 fs-7 bg-hover-primary nav-icon-hover position-relative z-index-5" id="btn_trxjournalitem_add" href="javascript:__mysys_disbursement_ent.my_add_budget_indirect_mooe_line();"><i class="ti ti-new-section"></i></a>
                                                    </th>
                                                    <th class="text-center align-middle">Responsibility Code</th>
                                                    <th class="text-center align-middle">MFO/PAP</th>
                                                    <th class="text-center align-middle">Amount</th>
                                                </thead>
                                                <tbody>
                                                    <tr style="display:none;">
                                                        <td class="text-center align-middle">
                                                            <div class="d-inline-flex gap-1 justify-content-center">
                                                                <a class="text-danger fs-5 bg-hover-danger nav-icon-hover"
                                                                href="javascript:void(0)"
                                                                onclick="$(this).closest('tr').remove();">
                                                                    <i class="ti ti-trash"></i>
                                                                </a>
                                                                <a class="text-success fs-5 bg-hover-primary nav-icon-hover"
                                                                href="javascript:void(0)"
                                                                title="Add rows above"
                                                                onclick="__mysys_disbursement_ent.my_add_budget_indirect_mooe_line_above(this);">
                                                                    <i class="ti ti-plus"></i>
                                                                </a>
                                                            </div>
                                                        </td>
                                                        <td class="align-middle" nowrap>
                                                            <input type="text" id="responsibility_code"  value="" size="35"  name="responsibility_code" class="responsibility_code text-center">
                                                        </td>
                                                        <td class="align-middle" nowrap>
                                                            <input type="text" id="mfopaps_code"  value="" size="25" name="mfopaps_code" class="mfopaps_code text-center"/>
                                                        </td>
                                                        <td class="text-center align-middle" nowrap>
                                                            <input type="number" id="amount"  value="" size="25" step="any" name="amount" class="amount text-center"/>
                                                        </td>
                                                    </tr>
                                                    <?php if(!empty($recid) && empty($ors_id)):
                                                        $query = $this->db->query("
                                                        SELECT
                                                            `recid`,
                                                            `responsibility_code`,
                                                            `mfopaps_code`,
                                                            SUM(`amount`) AS amount
                                                        FROM
                                                            `tbl_disbursement_indirect_mooe_dt`
                                                        WHERE 
                                                            `project_id` = '$recid'
                                                        GROUP BY 
                                                            `responsibility_code`
                                                        ");
                                                        $result = $query->getResultArray();
                                                        foreach ($result as $data):
                                                            $responsibility_code = $data['responsibility_code'];
                                                            $mfopaps_code = $data['mfopaps_code'];
                                                            $amount = $data['amount'];
                                                    ?>
                                                    <tr>
                                                        <td class="text-center align-middle">
                                                            <div class="d-inline-flex gap-1 justify-content-center">
                                                                <a class="text-danger fs-5 bg-hover-danger nav-icon-hover"
                                                                href="javascript:void(0)"
                                                                onclick="$(this).closest('tr').remove();">
                                                                    <i class="ti ti-trash"></i>
                                                                </a>
                                                                <a class="text-success fs-5 bg-hover-primary nav-icon-hover"
                                                                href="javascript:void(0)"
                                                                title="Add rows above"
                                                                onclick="__mysys_disbursement_ent.my_add_budget_indirect_mooe_line_above(this);">
                                                                    <i class="ti ti-plus"></i>
                                                                </a>
                                                            </div>
                                                        </td>
                                                        <td class="align-middle" nowrap>
                                                            <input type="text" id="responsibility_code"  value="<?=$responsibility_code;?>" size="35"  name="responsibility_code" class="responsibility_code text-center">
                                                        </td>
                                                        <td class="align-middle" nowrap>
                                                            <input type="text" id="mfopaps_code"  value="<?=$mfopaps_code;?>" size="25" name="mfopaps_code" class="mfopaps_code text-center"/>
                                                        </td>
                                                        <td class="text-center align-middle" nowrap>
                                                            <input type="number" id="amount"  value="<?=$amount;?>" size="25" step="any" name="amount" class="amount text-center"/>
                                                        </td>
                                                    </tr>
                                                    <?php endforeach; endif;?>
                                                    <?php if(!empty($ors_id) && !empty($recid)):
                                                        $query = $this->db->query("
                                                        SELECT
                                                            `recid`,
                                                            `responsibility_code`,
                                                            `mfopaps_code`,
                                                            SUM(`amount`) AS amount
                                                        FROM
                                                            `tbl_ors_indirect_mooe_dt`
                                                        WHERE 
                                                            `project_id` = '$ors_id'
                                                        GROUP BY 
                                                            `responsibility_code`
                                                        ");
                                                        $result = $query->getResultArray();
                                                        foreach ($result as $data):
                                                            $responsibility_code = $data['responsibility_code'];
                                                            $mfopaps_code = $data['mfopaps_code'];
                                                            $amount = $data['amount'];
                                                    ?>
                                                    <tr>
                                                        <td class="text-center align-middle">
                                                            <div class="d-inline-flex gap-1 justify-content-center">
                                                                <a class="text-danger fs-5 bg-hover-danger nav-icon-hover"
                                                                href="javascript:void(0)"
                                                                onclick="$(this).closest('tr').remove();">
                                                                    <i class="ti ti-trash"></i>
                                                                </a>
                                                                <a class="text-success fs-5 bg-hover-primary nav-icon-hover"
                                                                href="javascript:void(0)"
                                                                title="Add rows above"
                                                                onclick="__mysys_disbursement_ent.my_add_budget_indirect_mooe_line_above(this);">
                                                                    <i class="ti ti-plus"></i>
                                                                </a>
                                                            </div>
                                                        </td>
                                                        <td class="align-middle" nowrap>
                                                            <input type="text" id="responsibility_code"  value="<?=$responsibility_code;?>" size="35"  name="responsibility_code" class="responsibility_code text-center">
                                                        </td>
                                                        <td class="align-middle" nowrap>
                                                            <input type="text" id="mfopaps_code"  value="<?=$mfopaps_code;?>" size="25" name="mfopaps_code" class="mfopaps_code text-center"/>
                                                        </td>
                                                        <td class="text-center align-middle" nowrap>
                                                            <input type="number" id="amount"  value="<?=$amount;?>" size="25" step="any" name="amount" class="amount text-center"/>
                                                        </td>
                                                    </tr>
                                                    <?php endforeach; endif;?>
                                                    <?php if(!empty($ors_id)):
                                                        $query = $this->db->query("
                                                        SELECT
                                                            `recid`,
                                                            `responsibility_code`,
                                                            `mfopaps_code`,
                                                            SUM(`amount`) AS amount
                                                        FROM
                                                            `tbl_ors_indirect_mooe_dt`
                                                        WHERE 
                                                            `project_id` = '$ors_id'
                                                        GROUP BY 
                                                            `responsibility_code`
                                                        ");
                                                        $result = $query->getResultArray();
                                                        foreach ($result as $data):
                                                            $responsibility_code = $data['responsibility_code'];
                                                            $mfopaps_code = $data['mfopaps_code'];
                                                            $amount = $data['amount'];
                                                    ?>
                                                    <tr>
                                                        <td class="text-center align-middle">
                                                            <div class="d-inline-flex gap-1 justify-content-center">
                                                                <a class="text-danger fs-5 bg-hover-danger nav-icon-hover"
                                                                href="javascript:void(0)"
                                                                onclick="$(this).closest('tr').remove();">
                                                                    <i class="ti ti-trash"></i>
                                                                </a>
                                                                <a class="text-success fs-5 bg-hover-primary nav-icon-hover"
                                                                href="javascript:void(0)"
                                                                title="Add rows above"
                                                                onclick="__mysys_disbursement_ent.my_add_budget_indirect_mooe_line_above(this);">
                                                                    <i class="ti ti-plus"></i>
                                                                </a>
                                                            </div>
                                                        </td>
                                                        <td class="align-middle" nowrap>
                                                            <input type="text" id="responsibility_code"  value="<?=$responsibility_code;?>" size="35"  name="responsibility_code" class="responsibility_code text-center">
                                                        </td>
                                                        <td class="align-middle" nowrap>
                                                            <input type="text" id="mfopaps_code"  value="<?=$mfopaps_code;?>" size="25" name="mfopaps_code" class="mfopaps_code text-center"/>
                                                        </td>
                                                        <td class="text-center align-middle" nowrap>
                                                            <input type="number" id="amount"  value="<?=$amount;?>" size="25" step="any" name="amount" class="amount text-center"/>
                                                        </td>
                                                    </tr>
                                                    <?php endforeach; endif;?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- CO TAB CONTENT -->
                            <div class="tab-pane p-3" id="co-pill" role="tabpanel">
                                <div class="row">
                                    <div class="table-responsive pe-2 ps-0">
                                        <div class="col-md-12 mb-2">
                                            <span class="ms-3 fw-bold">Direct Cost:</span>
                                            <table id="budget_co_line_items" class="table-sm table-striped budgetcodata-list">
                                                <thead>
                                                    <th class="text-center">
                                                        <a class="text-info px-2 fs-7 bg-hover-primary nav-icon-hover position-relative z-index-5" id="btn_budgetcoitem_add" disabled href="javascript:__mysys_disbursement_ent.my_add_budget_co_line();"><i class="ti ti-new-section"></i></a>
                                                    </th>
                                                    <th class="text-center align-middle">Responsibility Code</th>
                                                    <th class="text-center align-middle">MFO/PAP</th>
                                                    <th class="text-center align-middle">Amount</th>
                                                </thead>
                                                <tbody>
                                                    <tr style="display:none;">
                                                        <td class="text-center align-middle">
                                                            <div class="d-inline-flex gap-1 justify-content-center">
                                                                <a class="text-danger fs-5 bg-hover-danger nav-icon-hover"
                                                                href="javascript:void(0)"
                                                                onclick="$(this).closest('tr').remove();">
                                                                    <i class="ti ti-trash"></i>
                                                                </a>
                                                                <a class="text-success fs-5 bg-hover-primary nav-icon-hover"
                                                                href="javascript:void(0)"
                                                                title="Add rows above"
                                                                onclick="__mysys_disbursement_ent.my_add_budget_co_line_above(this);">
                                                                    <i class="ti ti-plus"></i>
                                                                </a>
                                                            </div>
                                                        </td>
                                                        <td class="align-middle" nowrap>
                                                            <input type="text" id="responsibility_code"  value="" size="35"  name="responsibility_code" class="responsibility_code text-center">
                                                        </td>
                                                        <td class="align-middle" nowrap>
                                                            <input type="text" id="mfopaps_code"  value="" size="25" name="mfopaps_code" class="mfopaps_code text-center"/>
                                                        </td>
                                                        <td class="text-center align-middle" nowrap>
                                                            <input type="number" id="amount"  value="" size="25" step="any" name="amount" class="amount text-center"/>
                                                        </td>
                                                    </tr>
                                                    <?php if(!empty($recid) && empty($ors_id)):
                                                        $query = $this->db->query("
                                                        SELECT
                                                            `recid`,
                                                            `responsibility_code`,
                                                            `mfopaps_code`,
                                                            SUM(`amount`) AS amount
                                                        FROM
                                                            `tbl_disbursement_direct_co_dt`
                                                        WHERE 
                                                            `project_id` = '$recid'
                                                        GROUP BY 
                                                            `responsibility_code`
                                                        ");
                                                        $result = $query->getResultArray();
                                                        foreach ($result as $data):
                                                            $dt_id = $data['recid'];
                                                            $responsibility_code = $data['responsibility_code'];
                                                            $mfopaps_code = $data['mfopaps_code'];
                                                            $amount = $data['amount'];
                                                    ?>
                                                    <tr>
                                                        <td class="text-center align-middle">
                                                            <div class="d-inline-flex gap-1 justify-content-center">
                                                                <a class="text-danger fs-5 bg-hover-danger nav-icon-hover"
                                                                href="javascript:void(0)"
                                                                onclick="$(this).closest('tr').remove();">
                                                                    <i class="ti ti-trash"></i>
                                                                </a>
                                                                <a class="text-success fs-5 bg-hover-primary nav-icon-hover"
                                                                href="javascript:void(0)"
                                                                title="Add rows above"
                                                                onclick="__mysys_disbursement_ent.my_add_budget_co_line_above(this);">
                                                                    <i class="ti ti-plus"></i>
                                                                </a>
                                                            </div>
                                                        </td>
                                                        <td class="align-middle" nowrap>
                                                            <input type="text" id="responsibility_code"  value="<?=$responsibility_code;?>" size="35"  name="responsibility_code" class="responsibility_code text-center">
                                                        </td>
                                                        <td class="align-middle" nowrap>
                                                            <input type="text" id="mfopaps_code"  value="<?=$mfopaps_code;?>" size="25" name="mfopaps_code" class="mfopaps_code text-center"/>
                                                        </td>
                                                        <td class="text-center align-middle" nowrap>
                                                            <input type="number" id="amount"  value="<?=$amount;?>" size="25" step="any" name="amount" class="amount text-center"/>
                                                        </td>
                                                    </tr>
                                                    <?php endforeach; endif;?>
                                                    <?php if(!empty($ors_id)&& !empty($recid)):
                                                        $query = $this->db->query("
                                                        SELECT
                                                            `recid`,
                                                            `responsibility_code`,
                                                            `mfopaps_code`,
                                                            SUM(`amount`) AS amount
                                                        FROM
                                                            `tbl_ors_direct_co_dt`
                                                        WHERE 
                                                            `project_id` = '$ors_id'
                                                        GROUP BY 
                                                            `responsibility_code`
                                                        ");
                                                        $result = $query->getResultArray();
                                                        foreach ($result as $data):
                                                            $dt_id = $data['recid'];
                                                            $responsibility_code = $data['responsibility_code'];
                                                            $mfopaps_code = $data['mfopaps_code'];
                                                            $amount = $data['amount'];
                                                    ?>
                                                    <tr>
                                                        <td class="text-center align-middle">
                                                            <div class="d-inline-flex gap-1 justify-content-center">
                                                                <a class="text-danger fs-5 bg-hover-danger nav-icon-hover"
                                                                href="javascript:void(0)"
                                                                onclick="$(this).closest('tr').remove();">
                                                                    <i class="ti ti-trash"></i>
                                                                </a>
                                                                <a class="text-success fs-5 bg-hover-primary nav-icon-hover"
                                                                href="javascript:void(0)"
                                                                title="Add rows above"
                                                                onclick="__mysys_disbursement_ent.my_add_budget_co_line_above(this);">
                                                                    <i class="ti ti-plus"></i>
                                                                </a>
                                                            </div>
                                                        </td>
                                                        <td class="align-middle" nowrap>
                                                            <input type="text" id="responsibility_code"  value="<?=$responsibility_code;?>" size="35"  name="responsibility_code" class="responsibility_code text-center">
                                                        </td>
                                                        <td class="align-middle" nowrap>
                                                            <input type="text" id="mfopaps_code"  value="<?=$mfopaps_code;?>" size="25" name="mfopaps_code" class="mfopaps_code text-center"/>
                                                        </td>
                                                        <td class="text-center align-middle" nowrap>
                                                            <input type="number" id="amount"  value="<?=$amount;?>" size="25" step="any" name="amount" class="amount text-center"/>
                                                        </td>
                                                    </tr>
                                                    <?php endforeach; endif;?>
                                                    <?php if(!empty($ors_id)):
                                                        $query = $this->db->query("
                                                        SELECT
                                                            `recid`,
                                                            `responsibility_code`,
                                                            `mfopaps_code`,
                                                            SUM(`amount`) AS amount
                                                        FROM
                                                            `tbl_ors_direct_co_dt`
                                                        WHERE 
                                                            `project_id` = '$ors_id'
                                                        GROUP BY 
                                                            `responsibility_code`
                                                        ");
                                                        $result = $query->getResultArray();
                                                        foreach ($result as $data):
                                                            $dt_id = $data['recid'];
                                                            $responsibility_code = $data['responsibility_code'];
                                                            $mfopaps_code = $data['mfopaps_code'];
                                                            $amount = $data['amount'];
                                                    ?>
                                                    <tr>
                                                        <td class="text-center align-middle">
                                                            <div class="d-inline-flex gap-1 justify-content-center">
                                                                <a class="text-danger fs-5 bg-hover-danger nav-icon-hover"
                                                                href="javascript:void(0)"
                                                                onclick="$(this).closest('tr').remove();">
                                                                    <i class="ti ti-trash"></i>
                                                                </a>
                                                                <a class="text-success fs-5 bg-hover-primary nav-icon-hover"
                                                                href="javascript:void(0)"
                                                                title="Add rows above"
                                                                onclick="__mysys_disbursement_ent.my_add_budget_co_line_above(this);">
                                                                    <i class="ti ti-plus"></i>
                                                                </a>
                                                            </div>
                                                        </td>
                                                        <td class="align-middle" nowrap>
                                                            <input type="text" id="responsibility_code"  value="<?=$responsibility_code;?>" size="35"  name="responsibility_code" class="responsibility_code text-center">
                                                        </td>
                                                        <td class="align-middle" nowrap>
                                                            <input type="text" id="mfopaps_code"  value="<?=$mfopaps_code;?>" size="25" name="mfopaps_code" class="mfopaps_code text-center"/>
                                                        </td>
                                                        <td class="text-center align-middle" nowrap>
                                                            <input type="number" id="amount"  value="<?=$amount;?>" size="25" step="any" name="amount" class="amount text-center"/>
                                                        </td>
                                                    </tr>
                                                    <?php endforeach; endif;?>
                                                </tbody>
                                            </table>
                                        </div>
                                        <div class="col-sm-12">
                                            <span class="ms-3 fw-bold">Indirect Cost:</span>
                                            <table id="budget_indirect_co_line_items" class="table-sm table-striped budgetcodata-indirect-list">
                                                <thead>
                                                    <th class="text-center">
                                                        <a class="text-info px-2 fs-7 bg-hover-primary nav-icon-hover position-relative z-index-5" id="btn_budgetcoitem_add" disabled href="javascript:__mysys_disbursement_ent.my_add_budget_indirect_co_line();"><i class="ti ti-new-section"></i></a>
                                                    </th>
                                                    <th class="text-center align-middle">Responsibility Code</th>
                                                    <th class="text-center align-middle">MFO/PAP</th>
                                                    <th class="text-center align-middle">Amount</th>
                                                </thead>
                                                <tbody>
                                                    <tr style="display:none;">
                                                        <td class="text-center align-middle">
                                                            <div class="d-inline-flex gap-1 justify-content-center">
                                                                <a class="text-danger fs-5 bg-hover-danger nav-icon-hover"
                                                                href="javascript:void(0)"
                                                                onclick="$(this).closest('tr').remove();">
                                                                    <i class="ti ti-trash"></i>
                                                                </a>
                                                                <a class="text-success fs-5 bg-hover-primary nav-icon-hover"
                                                                href="javascript:void(0)"
                                                                title="Add rows above"
                                                                onclick="__mysys_disbursement_ent.my_add_budget_indirect_co_line_above(this);">
                                                                    <i class="ti ti-plus"></i>
                                                                </a>
                                                            </div>
                                                        </td>
                                                        <td class="align-middle" nowrap>
                                                            <input type="text" id="responsibility_code"  value="" size="35"  name="responsibility_code" class="responsibility_code text-center">
                                                        </td>
                                                        <td class="align-middle" nowrap>
                                                            <input type="text" id="mfopaps_code"  value="" size="25" name="mfopaps_code" class="mfopaps_code text-center"/>
                                                        </td>
                                                        <td class="text-center align-middle" nowrap>
                                                            <input type="number" id="amount"  value="" size="25" step="any" name="amount" class="amount text-center"/>
                                                        </td>
                                                    </tr>
                                                    <?php if(!empty($recid) && empty($ors_id)):
                                                        $query = $this->db->query("
                                                        SELECT
                                                            `recid`,
                                                            `responsibility_code`,
                                                            `mfopaps_code`,
                                                            SUM(`amount`) AS amount
                                                        FROM
                                                            `tbl_disbursement_indirect_co_dt`
                                                        WHERE 
                                                            `project_id` = '$recid'
                                                        GROUP BY 
                                                            `responsibility_code`
                                                        ");
                                                        $result = $query->getResultArray();
                                                        foreach ($result as $data):
                                                            $dt_id = $data['recid'];
                                                            $responsibility_code = $data['responsibility_code'];
                                                            $mfopaps_code = $data['mfopaps_code'];
                                                            $amount = $data['amount'];
                                                    ?>
                                                    <tr>
                                                        <td class="text-center align-middle">
                                                            <div class="d-inline-flex gap-1 justify-content-center">
                                                                <a class="text-danger fs-5 bg-hover-danger nav-icon-hover"
                                                                href="javascript:void(0)"
                                                                onclick="$(this).closest('tr').remove();">
                                                                    <i class="ti ti-trash"></i>
                                                                </a>
                                                                <a class="text-success fs-5 bg-hover-primary nav-icon-hover"
                                                                href="javascript:void(0)"
                                                                title="Add rows above"
                                                                onclick="__mysys_disbursement_ent.my_add_budget_indirect_co_line_above(this);">
                                                                    <i class="ti ti-plus"></i>
                                                                </a>
                                                            </div>
                                                        </td>
                                                        <td class="align-middle" nowrap>
                                                            <input type="text" id="responsibility_code"  value="<?=$responsibility_code;?>" size="35"  name="responsibility_code" class="responsibility_code text-center">
                                                        </td>
                                                        <td class="align-middle" nowrap>
                                                            <input type="text" id="mfopaps_code"  value="<?=$mfopaps_code;?>" size="25" name="mfopaps_code" class="mfopaps_code text-center"/>
                                                        </td>
                                                        <td class="text-center align-middle" nowrap>
                                                            <input type="number" id="amount"  value="<?=$amount;?>" size="25" step="any" name="amount" class="amount text-center"/>
                                                        </td>
                                                    </tr>
                                                    <?php endforeach; endif;?>
                                                    <?php if(!empty($ors_id) && !empty($recid)):
                                                        $query = $this->db->query("
                                                        SELECT
                                                            `recid`,
                                                            `responsibility_code`,
                                                            `mfopaps_code`,
                                                            SUM(`amount`) AS amount
                                                        FROM
                                                            `tbl_ors_indirect_co_dt`
                                                        WHERE 
                                                            `project_id` = '$ors_id'
                                                        GROUP BY 
                                                            `responsibility_code`
                                                        ");
                                                        $result = $query->getResultArray();
                                                        foreach ($result as $data):
                                                            $dt_id = $data['recid'];
                                                            $responsibility_code = $data['responsibility_code'];
                                                            $mfopaps_code = $data['mfopaps_code'];
                                                            $amount = $data['amount'];
                                                    ?>
                                                    <tr>
                                                        <td class="text-center align-middle">
                                                            <div class="d-inline-flex gap-1 justify-content-center">
                                                                <a class="text-danger fs-5 bg-hover-danger nav-icon-hover"
                                                                href="javascript:void(0)"
                                                                onclick="$(this).closest('tr').remove();">
                                                                    <i class="ti ti-trash"></i>
                                                                </a>
                                                                <a class="text-success fs-5 bg-hover-primary nav-icon-hover"
                                                                href="javascript:void(0)"
                                                                title="Add rows above"
                                                                onclick="__mysys_disbursement_ent.my_add_budget_indirect_co_line_above(this);">
                                                                    <i class="ti ti-plus"></i>
                                                                </a>
                                                            </div>
                                                        </td>
                                                        <td class="align-middle" nowrap>
                                                            <input type="text" id="responsibility_code"  value="<?=$responsibility_code;?>" size="35"  name="responsibility_code" class="responsibility_code text-center">
                                                        </td>
                                                        <td class="align-middle" nowrap>
                                                            <input type="text" id="mfopaps_code"  value="<?=$mfopaps_code;?>" size="25" name="mfopaps_code" class="mfopaps_code text-center"/>
                                                        </td>
                                                        <td class="text-center align-middle" nowrap>
                                                            <input type="number" id="amount"  value="<?=$amount;?>" size="25" step="any" name="amount" class="amount text-center"/>
                                                        </td>
                                                    </tr>
                                                    <?php endforeach; endif;?>
                                                    <?php if(!empty($ors_id)):
                                                        $query = $this->db->query("
                                                        SELECT
                                                            `recid`,
                                                            `responsibility_code`,
                                                            `mfopaps_code`,
                                                            SUM(`amount`) AS amount
                                                        FROM
                                                            `tbl_ors_indirect_co_dt`
                                                        WHERE 
                                                            `project_id` = '$ors_id'
                                                        GROUP BY 
                                                            `responsibility_code`
                                                        ");
                                                        $result = $query->getResultArray();
                                                        foreach ($result as $data):
                                                            $dt_id = $data['recid'];
                                                            $responsibility_code = $data['responsibility_code'];
                                                            $mfopaps_code = $data['mfopaps_code'];
                                                            $amount = $data['amount'];
                                                    ?>
                                                    <tr>
                                                        <td class="text-center align-middle">
                                                            <div class="d-inline-flex gap-1 justify-content-center">
                                                                <a class="text-danger fs-5 bg-hover-danger nav-icon-hover"
                                                                href="javascript:void(0)"
                                                                onclick="$(this).closest('tr').remove();">
                                                                    <i class="ti ti-trash"></i>
                                                                </a>
                                                                <a class="text-success fs-5 bg-hover-primary nav-icon-hover"
                                                                href="javascript:void(0)"
                                                                title="Add rows above"
                                                                onclick="__mysys_disbursement_ent.my_add_budget_indirect_co_line_above(this);">
                                                                    <i class="ti ti-plus"></i>
                                                                </a>
                                                            </div>
                                                        </td>
                                                        <td class="align-middle" nowrap>
                                                            <input type="text" id="responsibility_code"  value="<?=$responsibility_code;?>" size="35"  name="responsibility_code" class="responsibility_code text-center">
                                                        </td>
                                                        <td class="align-middle" nowrap>
                                                            <input type="text" id="mfopaps_code"  value="<?=$mfopaps_code;?>" size="25" name="mfopaps_code" class="mfopaps_code text-center"/>
                                                        </td>
                                                        <td class="text-center align-middle" nowrap>
                                                            <input type="number" id="amount"  value="<?=$amount;?>" size="25" step="any" name="amount" class="amount text-center"/>
                                                        </td>
                                                    </tr>
                                                    <?php endforeach; endif;?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </dwiv>
                </div>

                <hr class="fw-bolder">

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
                                        <?php elseif(!empty($ors_id)):?>
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
                            <div class="col-sm-12">
                                <div class="row mb-2">
                                    <?php if(!empty($action)):?>
                                        <div class="col-sm-4">
                                            <span class="fw-bold">Status:</span>
                                        </div>
                                        <div class="col-sm-8 <?=$status_color;?> fs-3 fw-bold">
                                            <span><i class="ti ti-flag"></i> <?=$status_msg;?></span>
                                        </div>
                                    <?php endif;?>
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
                            <th class="text-center" style="width:250px;">Serial No.</th>
                            <th class="text-center">Particulars</th>
                            <th class="text-center">Payee</th>
                            <th class="text-center">Total Amount</th>
                        </tr>
                    </thead>
                    <tbody class="align-middle">
                        <?php if(!empty($disbursementhddata)):
                            foreach ($disbursementhddata as $data):
                                $dt_recid = $data['recid'];
                                $serialno = $data['serialno'];
                                $particulars = $data['particulars'];
                                $payee_name = $data['payee_name'];
                                $total_amount = $data['amount'];
                        ?>
                        <tr>
                            <td class="text-center align-middle">
                                <div class="d-flex justify-content-center gap-2">
                                    <a class="text-info nav-icon-hover fs-6" 
                                    href="mydisbursement?meaction=MAIN&recid=<?= $dt_recid ?>" 
                                    title="Edit Transaction">
                                    <i class="ti ti-edit"></i>
                                    </a>
                                    <button class="btn btn-sm fs-6 text-warning p-0 border-0 bg-transparent" 
                                            onclick="__mysys_disbursement_ent.__showPdfInModal('<?= base_url('mydisbursement?meaction=PRINT-DISBURSEMENT&recid='.$dt_recid) ?>')" 
                                            title="Print DV">
                                    <i class="ti ti-printer"></i>
                                    </button>
                                </div>
                            </td>
                            <td class="text-center"><?=$serialno;?></td>
                            <td class="text-center"><?=$particulars;?></td>
                            <td class="text-center"><?=$payee_name;?></td>
                            <td class="text-center"><?= 'P'. number_format($total_amount,2);?></td>
                        </tr>
                        <?php endforeach; endif;?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- APPROVAL -->
<div class="modal fade" id="confirmApproveModal" tabindex="-1" aria-labelledby="confirmApproveModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
        <div class="modal-header bg-info text-white">
            <h5 class="modal-title text-white" id="confirmApproveModalLabel">Confirm Approval</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
            <div class="row">
                <div class="col-sm-12">
                    <div class="row mb-2">
                        <div class="col-sm-4">
                            <span>Approver:</span>
                        </div>
                        <div class="col-sm-8">
                            <?php if($action == 'certifya_appr_approved' && !empty($recid)):?>
                                <input type="text" id="approved_by" name="approved_by" value="<?=$approver;?>" class="form-control form-control-sm" readonly/>
                            <?php else:?>
                                <input type="text" id="approved_by" name="approved_by" value="<?=$this->cuser;?>" class="form-control form-control-sm" readonly/>
                            <?php endif;?>
                        </div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-sm-4">
                            <span>Remarks:</span>       
                        </div>
                        <div class="col-sm-8">
                        <?php if($action == 'certifya_appr_approved' && !empty($recid)):?>
                            <textarea name="approved_remarks" id="approved_remarks" placeholder="" rows="3" class="form-control form-control-sm" disabled><?=$certa_remarks;?></textarea>
                        <?php else:?>
                            <textarea name="approved_remarks" id="approved_remarks" placeholder="" rows="3" class="form-control form-control-sm"></textarea>
                        <?php endif;?>
                        </div>
                    </div>
                </div>
            </div>
            
        </div>
        <div class="modal-footer">
            <?php if($action == 'certifya_appr_approved' && !empty($recid)):?>
                <button type="button" class="btn bg-secondary-subtle btn-sm" data-bs-dismiss="modal">Back</button>
            <?php else:?>
                <button type="button" class="btn btn-success btn-sm px-3" id="confirmApproveBtn">Approve</button>
                <button type="button" class="btn bg-secondary-subtle btn-sm" data-bs-dismiss="modal">Cancel</button>
            <?php endif;?>
        </div>
        </div>
    </div>
</div>

<div class="modal fade" id="confirmDisapproveModal" tabindex="-1" aria-labelledby="confirmDisapproveModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
        <div class="modal-header bg-info text-white">
            <h5 class="modal-title text-white" id="confirmDisapproveModalLabel">Confirm Disapproval</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
            <div class="row">
                <div class="col-sm-12">
                    <div class="row mb-2">
                        <div class="col-sm-4">
                            <span>Disapprover:</span>
                        </div>
                        <div class="col-sm-8">
                            <?php if($action == 'certifya_appr_disapproved' && !empty($recid)):?>
                                <input type="text" id="disapproved_by" name="disapproved_by" value="<?=$approver;?>" class="form-control form-control-sm" readonly/>
                            <?php else:?>
                                <input type="text" id="disapproved_by" name="disapproved_by" value="<?=$this->cuser;?>" class="form-control form-control-sm" readonly/>
                            <?php endif;?>
                        </div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-sm-4">
                            <span>Remarks:</span>       
                        </div>
                        <div class="col-sm-8">
                            <?php if($action == 'certifya_appr_disapproved' && !empty($recid)):?>
                                <textarea name="disapproved_remarks" id="disapproved_remarks" placeholder="" rows="3" class="form-control form-control-sm" disabled><?=$certb_remarks;?></textarea>
                            <?php else:?>
                                <textarea name="disapproved_remarks" id="disapproved_remarks" placeholder="" rows="3" class="form-control form-control-sm"></textarea>
                            <?php endif;?>
                        </div>
                    </div>
                </div>
            </div>
            
        </div>
        <div class="modal-footer">
            <?php if($action == 'certifya_appr_disapproved' && !empty($recid)):?>
                <button type="button" class="btn bg-secondary-subtle btn-sm" data-bs-dismiss="modal">Back</button>
            <?php else:?>
                <button type="button" class="btn btn-danger btn-sm px-3" id="confirmDisapproveBtn">Disapprove</button>
                <button type="button" class="btn bg-secondary-subtle btn-sm" data-bs-dismiss="modal">Cancel</button>
            <?php endif;?>
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
<script src="<?=base_url('assets/js/disbursement/disbursement.js?v=1');?>"></script>
<script src="<?=base_url('assets/js/mysysapps.js');?>"></script>

<script>
$(function () {

    function computeGross() {
        let total = 0;
        $(".amount").each(function () {
            total += parseFloat($(this).val()) || 0;
        });
        $("#gross_amount").val(total.toFixed(2));
        return total;
    }

    function resetTaxes() {
        $("#vat_percent").val('');
        $("#ewt_percent").val('');
        $("#pt_percent").val('');
    }

    function applyTaxRules() {

        let gross = computeGross();
        let category = $("#category").val();
        let isVat = $("#is_vatable").is(":checked");

        // DEFAULT
        if (!category) {
            resetTaxes();
            $("#net_amount").val(gross.toFixed(2));
            $("#total_deduction").val(0); // 👈 ADD
            return;
        }

        let net = 0;
        let vat = 0;
        let ewt = 0;
        let pt = 0;
        let totalDeduction = 0;

        // =========================
        // MANUAL MODE (CPA RULE)
        // =========================
        if (category === "Manual") {

            let vatPercent = parseFloat($("#vat_percent").val()) || 0;
            let ewtPercent = parseFloat($("#ewt_percent").val()) || 0;
            let ptPercent  = parseFloat($("#pt_percent").val()) || 0;

            if (isVat) {
                net = gross / 1.12;
                vat = net * (vatPercent / 100);
                ewt = net * (ewtPercent / 100);

                $("#pt_percent").val('');
            } else {
                net = gross;
                pt = gross * (ptPercent / 100);
                ewt = gross * (ewtPercent / 100);

                $("#vat_percent").val('');
            }

            let netPayable = gross - vat - ewt - pt;
            totalDeduction = vat + ewt + pt; // 👈 FIX (removed duplicate let)

            $("#net_amount").val(netPayable.toFixed(2));
            $("#total_deduction").val(totalDeduction.toFixed(2)); // 👈 ADD
            return;
        }

        // =========================
        // AUTO MODE (GOODS/SERVICES)
        // =========================
        if (isVat) {

            net = gross / 1.12;

            $("#vat_percent").val(5);
            vat = net * 0.05;

            if (category === "Goods") {
                $("#ewt_percent").val(1);
                ewt = net * 0.01;
            } else if (category === "Services") {
                $("#ewt_percent").val(2);
                ewt = net * 0.02;
            }

            $("#pt_percent").val('');

        } else {

            net = gross;

            $("#vat_percent").val('');

            if (category === "Goods") {
                $("#ewt_percent").val(1);
                ewt = gross * 0.01;
            } else if (category === "Services") {
                $("#ewt_percent").val(2);
                ewt = gross * 0.02;
            }

            $("#pt_percent").val(3);
            pt = gross * 0.03;
        }

        // FINAL
        let netPayable = gross - vat - ewt - pt;
        totalDeduction = vat + ewt + pt; // 👈 ADD

        $("#net_amount").val(netPayable.toFixed(2));
        $("#total_deduction").val(totalDeduction.toFixed(2)); // 👈 ADD
    }

    function toggleFields() {
        if ($("#is_vatable").is(":checked")) {
            $(".vat-field").show();
            $(".ewt-field").show();
            $(".pt-field").hide();
        } else {
            $(".vat-field").hide();
            $(".ewt-field").show();
            $(".pt-field").show();
        }
    }

    // VALIDATION
    $("#is_vatable").on("change", function () {

        let category = $("#category").val();

        if (!category) {
            $(this).prop("checked", false);

            toastr.warning("Select category first before enabling VAT.", "Validation", {
                progressBar: true,
                closeButton: true,
                timeOut: 2000
            });

            return;
        }

        toggleFields();
        applyTaxRules();
    });

    $("#category").on("change", function () {
        applyTaxRules();
    });

    $(document).on("input", ".amount, #vat_percent, #ewt_percent, #pt_percent", function () {
        applyTaxRules();
    });

    // INIT
    toggleFields();
    applyTaxRules();
});
</script>

<!-- PROJECT TITLE LOOKUP -->
<script>
<?php
$projects = [];
foreach ($projectdata as $data) {
    $projects[$data['responsibility_code']] = [
        'mfopaps' => $data['mfopaps_code']
    ];
}
?>
var projects = <?= json_encode($projects); ?>;
var rc_codes = Object.keys(projects);

$(function () {

  $(document).on("focus", ".responsibility_code", function () {

    if (!$(this).data("ui-autocomplete")) {

      $(this).autocomplete({
        source: rc_codes,
        select: function (event, ui) {

          let row = $(this).closest('tr');

          // set RC (selected value)
          row.find('.responsibility_code').val(ui.item.value);

          // set corresponding MFO/PAPs
          row.find('.mfopaps_code')
             .val(projects[ui.item.value].mfopaps);

        }
      });

    }

  });

});
</script>

<script>
<?php
    $serialdata_js = [];

    foreach ($serialdata as $data) {
        $serialdata_js[] = [
            'label' => $data['serialno'], // shown in dropdown
            'value' => $data['serialno'], // inserted in input
            'ors_id' => $data['ors_id']   // hidden value to pass
        ];
    }
?>

var serialdata = <?php echo json_encode($serialdata_js); ?>;
var mesiteurl = "<?= base_url(); ?>";

$(function() {

    // 🔹 For existing input
    $("#serialno").autocomplete({
        source: serialdata,
        minLength: 0,
        select: function(event, ui) {

            var ors_id = ui.item.ors_id;
            var recid = $("#recid").val(); // 👈 get from hidden input

            window.location.href = mesiteurl + 
                'mydisbursement?meaction=MAIN'
                + '&ors_id=' + encodeURIComponent(ors_id)
                + '&recid=' + encodeURIComponent(recid);
        }
    }).focus(function() {
        $(this).autocomplete("search", "");
    });

    // 🔹 For dynamically added inputs
    $(document).on("focus", ".serialno", function () {

        if (!$(this).data("uiAutocomplete")) {

            $(this).autocomplete({
                source: serialdata,
                minLength: 0,
                select: function(event, ui) {

                    var ors_id = ui.item.ors_id;

                    window.location.href = mesiteurl + 
                        'mydisbursement?meaction=MAIN&ors_id=' + encodeURIComponent(ors_id);
                }
            }).autocomplete("search", "");
        }
    });

});
</script>


<script>
<?php
$payee_js = [];

foreach ($payeedata as $data) {
    $payee_js[] = [
        'label'   => $data['payee_name'],
        'value'   => $data['payee_name'],
        'office'  => $data['payee_office'],
        'address' => $data['payee_address']
    ];
}
?>

var payees = <?= json_encode($payee_js); ?>;

$(function () {

    $("#payee_name").autocomplete({
        source: payees,
        minLength: 0,

        select: function (event, ui) {

            // set payee
            $("#payee_name").val(ui.item.value);

            // auto-fill visible fields
            $("#payee_office").val(ui.item.office);
            $("#payee_address").val(ui.item.address);
        }
    }).focus(function () {
        $(this).autocomplete("search", "");
    });

});
</script>


<?php
	echo $MDL_jsscript;
?>
<script>
    __mysys_disbursement_ent.__disbursement_saving();
</script>

<?php
    echo view('templates/myfooter.php');
?>


