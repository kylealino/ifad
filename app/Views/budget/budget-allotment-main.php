<?php
$this->request = \Config\Services::request();
$this->db = \Config\Database::connect();
$recid = $this->request->getPostGet('recid');
$realign_id = $this->request->getPostGet('realign_id');
$action = $this->request->getPostGet('action');
$this->session = session();
$this->cuser = $this->session->get('__xsys_myuserzicas__');

$trxno = "";
$project_title = "";
$responsibility_code = "";
$fund_cluster_code = "";
$division_name = "";
$is_pending = "";
$is_approved = "";
$is_disapproved = "";
$approver = "";
$remarks = "";
$program_title = "";
$total_duration = "";
$duration_from = "";
$duration_to = "";
$program_leader = "";
$project_leader = "";
$monitoring_agency = "";
$collaborating_agencies = "";
$implementing_agency = "";
$funding_agency = "";
$tagging = "";
$is_realign1 = "";
$is_realign2 = "";
$is_realign3 = "";
$with_extension = "";
$extended_from = "";
$extended_to = "";
$lddap_refno = "";
$MDL_jsscript = "";
$counter = 1;

if(!empty($recid) || !is_null($recid)) { 

    $query = $this->db->query("
    SELECT
        `trxno`,
        `project_title`,
        `responsibility_code`,
        `fund_cluster_code`,
        `division_name`,
        `added_at`,
        `added_by`,
        `is_approved`,
        `is_disapproved`,
        `is_pending`,
        `approver`,
        `remarks`,
        `program_title`,
        `total_duration`,
        `duration_from`,
        `duration_to`,
        `program_leader`,
        `project_leader`,
        `monitoring_agency`,
        `collaborating_agencies`,
        `implementing_agency`,
        `funding_agency`,
        `tagging`,
        `is_realign1`,
        `is_realign2`,
        `is_realign3`,
        `with_extension`,
        `extended_from`,
        `extended_to`,
        `lddap_refno`
    FROM
        `tbl_budget_hd`
    WHERE 
        `recid` = '$recid'"
    );

    $data = $query->getRowArray();
    $trxno = $data['trxno'];
    $project_title = $data['project_title'];
    $responsibility_code = $data['responsibility_code'];
    $fund_cluster_code = $data['fund_cluster_code'];
    $division_name = $data['division_name'];
    $is_pending = $data['is_pending'];
    $is_approved = $data['is_approved'];
    $is_disapproved = $data['is_disapproved'];
    $approver = $data['approver'];
    $remarks = $data['remarks'];
    $program_title = $data['program_title'];
    $total_duration = $data['total_duration'];
    $duration_from = $data['duration_from'];
    $duration_to = $data['duration_to'];
    $program_leader = $data['program_leader'];
    $project_leader = $data['project_leader'];
    $monitoring_agency = $data['monitoring_agency'];
    $collaborating_agencies = $data['collaborating_agencies'];
    $implementing_agency = $data['implementing_agency'];
    $funding_agency = $data['funding_agency'];
    $tagging = $data['tagging'];
    $is_realign1 = $data['is_realign1'];
    $is_realign2 = $data['is_realign2'];
    $is_realign3 = $data['is_realign3'];
    $with_extension = $data['with_extension'];
    $extended_from = $data['extended_from'];
    $extended_to = $data['extended_to'];
    $lddap_refno = $data['lddap_refno'];


    if ($action == 'appr_pending') {
        $MDL_jsscript = "
        <script>
           __mysys_budget_allotment_ent.__approve_budget();
           __mysys_budget_allotment_ent.__disapprove_budget();
        </script>
       ";	
    }elseif ($action == 'appr_disapproved') {
        $MDL_jsscript = "
        <script>
           __mysys_budget_allotment_ent.__disapprove_budget();
        </script>
       ";	
    }elseif ($action == 'appr_approved') {
        $MDL_jsscript = "
        <script>
           __mysys_budget_allotment_ent.__approve_budget();
        </script>
       ";	
    }else{
        $MDL_jsscript = "

       ";	
    }
}

echo view('templates/myheader.php');
?>

<div class="container-fluid">
    <div class="row me-mybudgetallotment-appr-outp-msg mx-0">
    </div>

    <input type="hidden" id="__siteurl" data-mesiteurl="<?=site_url();?>" />
    <div class="row mb-2 mt-0">
        <h4 class="fw-semibold mb-8">Budget Allotment</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a class="text-muted text-decoration-none" href="<?=site_url();?>"><i class="ti ti-home fs-5"></i></a>
            </li>
            <li class="breadcrumb-item" aria-current="page">Budget</li>
            <li class="breadcrumb-item" aria-current="page"><span class="form-label fw-bold">Budget Allotment Module</span></li>
            </ol>
        </nav>
    </div>
    <div class="card rounded">
        <div class="row mybudgetallotment-outp-msg mx-0">

        </div>
        <div class="card-header   bg-info p-1">
            <div class="row d-flex align-items-center">
                <div class="col-sm-6 d-flex align-items-center text-start">
                    <h6 class="mb-0 lh-base px-3 text-white fw-semibold d-flex align-items-center">
                        <i class="ti ti-pencil fs-5 me-1"></i>
                        <span class="pt-1">Entry</span>
                    </h6>
                </div>
                <div class="col-sm-6 text-end ">
                    <?php if ($action == 'appr_pending'):?>
                        <button type="button" id="btn_approve" name="btn_approve" class="btn_approve btn btn-sm btn-success">
                            Approve
                        </button>
                        <button type="button" id="btn_disapprove" name="btn_disapprove" class="btn_disapprove btn btn-sm btn-danger">
                            Dispprove
                        </button>
                    <?php elseif($action == 'appr_approved'):?>
                        <button type="button" id="btn_approve" name="btn_approve" class="btn_approve btn btn-sm btn-success mx-3">
                            Approved
                        </button>
                    <?php elseif($action == 'appr_disapproved'):?>
                        <button type="button" id="btn_disapprove" name="btn_disapprove" class="btn_disapprove btn btn-sm btn-danger mx-3">
                            Dispproved
                        </button>
                    <?php endif;?>
                </div>
            </div>
		</div>						
        <div class="card-body p-0 px-4 py-2 my-2">
            <form action="<?=site_url();?>mybudgetallotment?meaction=MAIN-SAVE" method="post" class="mybudgetallotment-validation">
                <div class="row">
                    <div class="col-sm-12 mb-2">
                        <div class="row mb-2">
                            <div class="col-sm-2">
                                <span class="fw-bold">Program Title:</span>
                            </div>
                            <div class="col-sm-10">
                                <input type="text" id="program_title"  value="<?=$program_title;?>" placeholder="Enter program title"  name="program_title" class="program_title form-control form-control-sm">
                            </div>
                            
                        </div>
                        <div class="row">
                            <div class="col-sm-2">
                                <span class="fw-bold">Project Title:</span>
                            </div>
                            <div class="col-sm-10">
                                <input type="text" id="project_title"  value="<?=$project_title;?>" placeholder="Enter project title"  name="project_title" class="project_title form-control form-control-sm">
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="row mb-2">
                            <div class="col-sm-4">
                                <span class="fw-bold">Fund Cluster:</span>
                            </div>
                            <div class="col-sm-8">
                                <input type="text" id="fund_cluster_code" name="fund_cluster_code" value="<?=$fund_cluster_code;?>" class="form-control form-control-sm" readonly />
                            </div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-sm-4">
                                <span class="fw-bold">Division:</span>
                            </div>
                            <div class="col-sm-8">
                                <input type="text" id="division_name" name="division_name" value="<?=$division_name;?>" class="form-control form-control-sm" readonly />
                            </div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-sm-4">
                                <span class="fw-bold">From:</span>
                            </div>
                            <div class="col-sm-8">
                                <input type="date" id="duration_from" name="duration_from" value="<?=$duration_from;?>" class="form-control form-control-sm" />
                            </div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-sm-4">
                                <span class="fw-bold">To:</span>
                            </div>
                            <div class="col-sm-8">
                                <input type="date" id="duration_to" name="duration_to" value="<?=$duration_to;?>" class="form-control form-control-sm" />
                            </div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-sm-4">
                                <span class="fw-bold">Duration:</span>
                            </div>
                            <div class="col-sm-5">
                                <input type="text" id="project_duration" name="project_duration" readonly class="form-control form-control-sm" />
                            </div>
                            <div class="col-sm-3 d-flex align-items-center">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" onchange="__mysys_budget_allotment_ent.__toggleExtensionFields(this)" id="with_extension" <?= ($with_extension == 1) ? 'checked': '';?>/>
                                    <label class="form-check-label small" for="with_extension">
                                        With Extension
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div id="extension_fields" style="display: none;">
                            <h6 class="fw-bold text-success">Extended Duration</h6>
                            <div class="row mb-2">
                                <div class="col-sm-4">
                                    <label class="fw-bold">From:</label>
                                </div>
                                <div class="col-sm-8">
                                    <input type="date" id="extended_from" name="extended_from" value="<?=$extended_from;?>" class="form-control form-control-sm" />
                                </div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-sm-4">
                                    <label class="fw-bold">To:</label>
                                </div>
                                <div class="col-sm-8">
                                    <input type="date" id="extended_to" name="extended_to" value="<?=$extended_to;?>" class="form-control form-control-sm" />
                                </div>
                            </div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-sm-4">
                                <span class="fw-bold text-nowrap">LDDAP-ADA Reference No.:</span>
                            </div>
                            <div class="col-sm-8">
                                <input type="text" id="lddap_refno" name="lddap_refno" value="<?=$lddap_refno;?>" class="form-control form-control-sm"/>
                            </div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-sm-4">
                                <span class="fw-bold">Project Leader:</span>
                            </div>
                            <div class="col-sm-8">
                                <input type="text" id="project_leader" name="project_leader" value="<?=$project_leader;?>" class="form-control form-control-sm"/>
                            </div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-sm-4">
                                <span class="fw-bold">Transaction No.:</span>
                            </div>
                            <div class="col-sm-8">
                                <input type="text" id="trxno" name="trxno" placeholder="-system-generated-" value="<?=$trxno;?>" class="form-control form-control-sm" disabled/>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="row mb-2">
                            <div class="col-sm-4">
                                <span class="fw-bold">Responsibility Code:</span>
                            </div>
                            <div class="col-sm-8">
                                <input type="hidden" class="form-control form-control-sm" id="recid" name="recid" value="<?=$recid;?>"/>
                                <input type="text" id="responsibility_code" name="responsibility_code" value="<?=$responsibility_code;?>" class="form-control form-control-sm" readonly />
                            </div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-sm-4">
                                <span class="fw-bold">Program Leader:</span>
                            </div>
                            <div class="col-sm-8">
                                <input type="text" id="program_leader" name="program_leader" value="<?=$program_leader;?>" class="form-control form-control-sm"/>
                            </div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-sm-4">
                                <span class="fw-bold">Implementing Agency:</span>
                            </div>
                            <div class="col-sm-8">
                                <input type="text" id="implementing_agency" name="implementing_agency" value="<?=$implementing_agency;?>" class="form-control form-control-sm"/>
                            </div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-sm-4">
                                <span class="fw-bold">Monitoring Agency:</span>
                            </div>
                            <div class="col-sm-8">
                                <input type="text" id="monitoring_agency" name="monitoring_agency" value="<?=$monitoring_agency;?>" class="form-control form-control-sm"/>
                            </div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-sm-4">
                                <span class="fw-bold">Collaborating Agencies:</span>
                            </div>
                            <div class="col-sm-8">
                                <textarea name="collaborating_agencies" id="collaborating_agencies" placeholder="" rows="3" class="form-control form-control-sm"><?=$collaborating_agencies;?></textarea>
                            </div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-sm-4">
                                <span class="fw-bold">Funding Agency:</span>
                            </div>
                            <div class="col-sm-8">
                                <input type="text" id="funding_agency" name="funding_agency" value="<?=$funding_agency;?>" class="form-control form-control-sm"/>
                            </div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-sm-4">
                                <span class="fw-bold">Tagging:</span>
                            </div>
                            <div class="col-sm-8">
                                <select name="" id="tagging" class="form-select form-select-sm">
                                    <?php if(!empty($recid)):?>
                                        <option value="<?=$tagging;?>"><?=$tagging;?></option>
                                    <?php endif;?>
                                    <option value="Save to Draft" class="text-success">Save to Draft</option>
                                    <option value="For Approval" class="text-info">For Approval</option>
                                </select>
                            </div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-sm-4">
                                <span class="fw-bold">Realignment:</span>
                            </div>
                            <div class="col-sm-8">

                                <?php if(!empty($recid) && $is_approved == '1' && $tagging == 'For Approval' && $is_realign1 == '0' && $is_realign2 == '0' && $is_realign3 == '0'):?>
                                <div class="d-flex gap-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="is_realign1">
                                        <label class="form-check-label" for="is_realign1">1st</label>
                                    </div>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="is_realign2" disabled>
                                        <label class="form-check-label" for="is_realign2">2nd</label>
                                    </div>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="is_realign3" disabled>
                                        <label class="form-check-label" for="is_realign3">3rd</label>
                                    </div>
                                </div>
                                <?php elseif(!empty($recid) && $is_approved == '1' && $tagging == 'For Approval' && $tagging == 'For Approval' && $is_realign1 == '1' && $is_realign2 == '0' && $is_realign3 == '0'):?>
                                <div class="d-flex gap-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="is_realign1" checked disabled>
                                        <label class="form-check-label" for="is_realign1">1st</label>
                                    </div>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="is_realign2">
                                        <label class="form-check-label" for="is_realign2">2nd</label>
                                    </div>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="is_realign3" disabled>
                                        <label class="form-check-label" for="is_realign3">3rd</label>
                                    </div>
                                </div>
                                <?php elseif(!empty($recid) && $is_approved == '1' && $tagging == 'For Approval' && $is_realign1 == '1' && $is_realign2 == '1' && $is_realign3 == '0'):?>
                                <div class="d-flex gap-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="is_realign1" checked disabled>
                                        <label class="form-check-label" for="is_realign1">1st</label>
                                    </div>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="is_realign2" checked disabled>
                                        <label class="form-check-label" for="is_realign2">2nd</label>
                                    </div>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="is_realign3">
                                        <label class="form-check-label" for="is_realign3">3rd</label>
                                    </div>
                                </div>
                                <?php elseif(!empty($recid) && $is_approved == '1' && $tagging == 'For Approval' && $is_realign1 == '1' && $is_realign2 == '1' && $is_realign3 == '1'):?>
                                <div class="d-flex gap-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="is_realign1" checked disabled>
                                        <label class="form-check-label" for="is_realign1">1st</label>
                                    </div>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="is_realign2" checked disabled>
                                        <label class="form-check-label" for="is_realign2">2nd</label>
                                    </div>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="is_realign3" checked disabled>
                                        <label class="form-check-label" for="is_realign3">3rd</label>
                                    </div>
                                </div>
                                <?php else:?>
                                <div class="d-flex gap-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="is_realign1" disabled>
                                        <label class="form-check-label" for="is_realign1">1st</label>
                                    </div>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="is_realign2" disabled>
                                        <label class="form-check-label" for="is_realign2">2nd</label>
                                    </div>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="is_realign3" disabled>
                                        <label class="form-check-label" for="is_realign3">3rd</label>
                                    </div>
                                </div>
                                <?php endif;?>
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
                                I. Personnel Services
                                </a>
                            </li>
                            <li class="nav-item" role="presentation">
                                <a class="nav-link rounded-pill px-3 fs-3 fw-semibold" data-bs-toggle="tab" href="#mooe-pill" role="tab">
                                II. Maintenance and Other Operating Expenses
                                </a>
                            </li>
                            <li class="nav-item" role="presentation">
                                <a class="nav-link rounded-pill px-3 fs-3 fw-semibold" data-bs-toggle="tab" href="#co-pill" role="tab">
                                III. Capital Outlay
                                </a>
                            </li>
                            <li class="nav-item" role="presentation">
                                <a class="nav-link rounded-pill px-3 fs-3 fw-semibold" data-bs-toggle="tab" href="#ac-pill" role="tab">
                                IV. Admin Cost
                                </a>
                            </li>
                        </ul>

                        <!-- Tab panes -->
                        <div class="tab-content border mb-0">
                            <!-- PS TAB CONTENT -->
                            <div class="tab-pane active p-3 pb-0" id="ps-pill" role="tabpanel">
                                <div class="row mb-2">
                                    <div class="table-responsive pe-2 ps-0">
                                        <div class="col-md-12 mb-2">
                                            <span class="ms-3 fw-bold">Direct Cost:</span>
                                            <table id="budget_line_items" class="table-sm table-striped budgetdata-list">
                                                <thead>
                                                    <th class="text-center">
                                                        <a class="text-info px-2 fs-7 bg-hover-primary nav-icon-hover position-relative z-index-5" id="btn_trxjournalitem_add" href="javascript:__mysys_budget_allotment_ent.my_add_budget_line();"><i class="ti ti-new-section"></i></a>
                                                    </th>
                                                    <th class="text-center align-middle">Expense Item</th>
                                                    <th class="text-center align-middle">PS - Particulars</th>
                                                    <th class="text-center align-middle">UACS.</th>
                                                    <th class="text-center align-middle">Approved Budget</th>
                                                    <th class="text-center align-middle">1st Realignment</th>
                                                    <th class="text-center align-middle">2nd Realignment</th>
                                                    <th class="text-center align-middle">3rd Realignment</th>
                                                    <th class="text-center align-middle">Proposed Realignment</th>
                                                </thead>
                                                <tbody class="align-middle">
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
                                                                onclick="__mysys_budget_allotment_ent.my_add_budget_line_above(this);">
                                                                    <i class="ti ti-plus"></i>
                                                                </a>
                                                            </div>
                                                        </td>
                                                        <td class="text-center align-middle" nowrap>
                                                            <input type="text" id="expense_item"  value="" size="60"  name="expense_item" class="expense_item">
                                                        </td>
                                                        <td class="align-middle" nowrap>
                                                            <input type="text" id="sub_object_code"  value="" size="60" placeholder="Enter sub-object code"  name="sub_object_code" class="psuacs_code sub_object_code">
                                                        </td>
                                                        <td class="text-center align-middle" nowrap>
                                                            <input type="text" id="uacs"  value="" size="25"  name="uacs" class="uacs text-center" disabled>
                                                        </td>
                                                        <td class="text-center align-middle" nowrap>
                                                            <input type="number" id="approved_budget"  value="" size="25" step="any" name="approved_budget" data-dtid="" <?= ($tagging == 'For Approval') ? 'disabled' : ''; ?>  class="approved_budget text-center" onchange="__mysys_budget_allotment_ent.__direct_ps_totals(); __mysys_budget_allotment_ent.__combined_totals();" onmouseout="__mysys_budget_allotment_ent.__direct_ps_totals(); __mysys_budget_allotment_ent.__combined_totals();" />
                                                        </td>
                                                        <td class="text-center align-middle" nowrap>
                                                            <input type="number" id="r1_approved_budget"  value="" size="25" step="any" name="r1_approved_budget" data-dtid="" class="r1_approved_budget text-center" onchange="__mysys_budget_allotment_ent.__direct_ps_totals(); __mysys_budget_allotment_ent.__combined_totals();" onmouseout="__mysys_budget_allotment_ent.__direct_ps_totals(); __mysys_budget_allotment_ent.__combined_totals();" />
                                                        </td>
                                                        <td class="text-center align-middle" nowrap>
                                                            <input type="number" id="r2_approved_budget"  value="" size="25" step="any" name="r2_approved_budget" data-dtid="" class="r2_approved_budget text-center" onchange="__mysys_budget_allotment_ent.__direct_ps_totals(); __mysys_budget_allotment_ent.__combined_totals();" onmouseout="__mysys_budget_allotment_ent.__direct_ps_totals(); __mysys_budget_allotment_ent.__combined_totals();" />
                                                        </td>
                                                        <td class="text-center align-middle" nowrap>
                                                            <input type="number" id="r3_approved_budget"  value="" size="25" step="any" name="r3_approved_budget" data-dtid="" class="r3_approved_budget text-center" onchange="__mysys_budget_allotment_ent.__direct_ps_totals(); __mysys_budget_allotment_ent.__combined_totals();" onmouseout="__mysys_budget_allotment_ent.__direct_ps_totals(); __mysys_budget_allotment_ent.__combined_totals();" />
                                                        </td>
                                                        <td class="text-center align-middle" nowrap>
                                                            <input type="number" id="proposed_realignment"  value="" size="25" step="any" name="proposed_realignment" data-dtid="" class="proposed_realignment text-center" disabled/>
                                                        </td>
                                                    </tr>
                                                    <?php if(!empty($recid)):
                                                        $query = $this->db->query("
                                                        SELECT
                                                            `recid`,
                                                            `expense_item`,
                                                            `particulars`,
                                                            `code`,
                                                            `approved_budget`,
                                                            `r1_approved_budget`,
                                                            `r2_approved_budget`,
                                                            `r3_approved_budget`,
                                                            `proposed_realignment`
                                                        FROM
                                                            `tbl_budget_direct_ps_dt`
                                                        WHERE 
                                                            `project_id` = '$recid'"
                                                        );
                                                        $result = $query->getResultArray();
                                                        foreach ($result as $data):
                                                            $dt_id = $data['recid'];
                                                            $expense_item = $data['expense_item'];
                                                            $particulars = $data['particulars'];
                                                            $code = $data['code'];
                                                            $approved_budget = $data['approved_budget'];
                                                            $r1_approved_budget = $data['r1_approved_budget'];
                                                            $r2_approved_budget = $data['r2_approved_budget'];
                                                            $r3_approved_budget = $data['r3_approved_budget'];
                                                            $proposed_realignment = $data['proposed_realignment'];
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
                                                                    onclick="__mysys_budget_allotment_ent.my_add_budget_line_above(this);">
                                                                    <i class="ti ti-plus"></i>
                                                                </a>
                                                            </div>
                                                        </td>
                                                        <td class="text-center align-middle" nowrap>
                                                            <input type="text" id="expense_item"  value="<?=$expense_item;?>" size="60"  name="expense_item" class="expense_item">
                                                        </td>
                                                        <td class="align-middle" nowrap>
                                                            <input type="text" id="sub_object_code"  value="<?=$particulars;?>" size="60" placeholder="Enter sub-object code"  name="sub_object_code" class="psuacs_code sub_object_code">
                                                        </td>
                                                        <td class="text-center align-middle" nowrap>
                                                            <input type="text" id="uacs"  value="<?=$code;?>" size="25"  name="uacs" class="uacs text-center" disabled>
                                                        </td>
                                                        <td class="text-center align-middle" nowrap>
                                                            <input type="number" id="approved_budget"  value="<?=$approved_budget;?>" size="25" step="any" data-dtid="<?=$dt_id;?>" <?= ($tagging == 'For Approval') ? 'disabled' : ''; ?> name="approved_budget" class="approved_budget text-center" onchange="__mysys_budget_allotment_ent.__direct_ps_totals(); __mysys_budget_allotment_ent.__combined_totals();" onmouseout="__mysys_budget_allotment_ent.__direct_ps_totals(); __mysys_budget_allotment_ent.__combined_totals();" />
                                                        </td>
                                                        <td class="text-center align-middle" nowrap>
                                                            <input type="number" id="r1_approved_budget"  value="<?=$r1_approved_budget;?>" size="25" step="any" name="r1_approved_budget" data-dtid="" class="r1_approved_budget text-center" disabled onchange="__mysys_budget_allotment_ent.__direct_ps_totals(); __mysys_budget_allotment_ent.__combined_totals();" onmouseout="__mysys_budget_allotment_ent.__direct_ps_totals(); __mysys_budget_allotment_ent.__combined_totals();" />
                                                        </td>
                                                        <td class="text-center align-middle" nowrap>
                                                            <input type="number" id="r2_approved_budget"  value="<?=$r2_approved_budget;?>" size="25" step="any" name="r2_approved_budget" data-dtid="" class="r2_approved_budget text-center" disabled onchange="__mysys_budget_allotment_ent.__direct_ps_totals(); __mysys_budget_allotment_ent.__combined_totals();" onmouseout="__mysys_budget_allotment_ent.__direct_ps_totals(); __mysys_budget_allotment_ent.__combined_totals();" />
                                                        </td>
                                                        <td class="text-center align-middle" nowrap>
                                                            <input type="number" id="r3_approved_budget"  value="<?=$r3_approved_budget;?>" size="25" step="any" name="r3_approved_budget" data-dtid="" class="r3_approved_budget text-center" disabled onchange="__mysys_budget_allotment_ent.__direct_ps_totals(); __mysys_budget_allotment_ent.__combined_totals();" onmouseout="__mysys_budget_allotment_ent.__direct_ps_totals(); __mysys_budget_allotment_ent.__combined_totals();" />
                                                        </td>
                                                        <td class="text-center align-middle" nowrap>
                                                            <input type="number" id="proposed_realignment"  value="<?=$proposed_realignment;?>" size="25" step="any" name="proposed_realignment" data-dtid="" class="proposed_realignment text-center" disabled/>
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
                                                        <a class="text-info px-2 fs-7 bg-hover-primary nav-icon-hover position-relative z-index-5" id="btn_trxjournalitem_add" href="javascript:__mysys_budget_allotment_ent.my_add_budget_indirect_line();"><i class="ti ti-new-section"></i></a>
                                                    </th>
                                                    <th class="text-center align-middle">Expense Item</th>
                                                    <th class="text-center align-middle">PS - Particulars</th>
                                                    <th class="text-center align-middle">UACS.</th>
                                                    <th class="text-center align-middle">Approved Budget</th>
                                                    <th class="text-center align-middle">1st Realignment</th>
                                                    <th class="text-center align-middle">2nd Realignment</th>
                                                    <th class="text-center align-middle">3rd Realignment</th>
                                                    <th class="text-center align-middle">Proposed Realignment</th>
                                                </thead>
                                                <tbody class="align-middle">
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
                                                                    onclick="__mysys_budget_allotment_ent.my_add_budget_indirect_line_above(this);">
                                                                    <i class="ti ti-plus"></i>
                                                                </a>
                                                            </div>
                                                        </td>
                                                        <td class="text-center align-middle" nowrap>
                                                            <input type="text" id="expense_item"  value="" size="60"  name="expense_item" class="expense_item">
                                                        </td>
                                                        <td class="align-middle" nowrap>
                                                            <input type="text" id="sub_object_code"  value="" size="60" placeholder="Enter sub-object code"  name="sub_object_code" class="psuacs_code sub_object_code">
                                                        </td>
                                                        <td class="text-center align-middle" nowrap>
                                                            <input type="text" id="uacs"  value="" size="25"  name="uacs" class="uacs text-center" disabled>
                                                        </td>
                                                        <td class="text-center align-middle" nowrap>
                                                            <input type="number" id="approved_budget"  value="" size="25" step="any" name="approved_budget" <?= ($tagging == 'For Approval') ? 'disabled' : ''; ?> data-dtid="" class="approved_budget text-center" onchange="__mysys_budget_allotment_ent.__indirect_ps_totals(); __mysys_budget_allotment_ent.__combined_totals();" onmouseout="__mysys_budget_allotment_ent.__indirect_ps_totals(); __mysys_budget_allotment_ent.__combined_totals();"/>
                                                        </td>
                                                        <td class="text-center align-middle" nowrap>
                                                            <input type="number" id="r1_approved_budget"  value="" size="25" step="any" name="r1_approved_budget" data-dtid="" class="r1_approved_budget text-center" disabled onchange="__mysys_budget_allotment_ent.__indirect_ps_totals(); __mysys_budget_allotment_ent.__combined_totals();" onmouseout="__mysys_budget_allotment_ent.__indirect_ps_totals(); __mysys_budget_allotment_ent.__combined_totals();"/>
                                                        </td>
                                                        <td class="text-center align-middle" nowrap>
                                                            <input type="number" id="r2_approved_budget"  value="" size="25" step="any" name="r2_approved_budget" data-dtid="" class="r2_approved_budget text-center" disabled onchange="__mysys_budget_allotment_ent.__indirect_ps_totals(); __mysys_budget_allotment_ent.__combined_totals();" onmouseout="__mysys_budget_allotment_ent.__indirect_ps_totals(); __mysys_budget_allotment_ent.__combined_totals();"/>
                                                        </td>
                                                        <td class="text-center align-middle" nowrap>
                                                            <input type="number" id="r3_approved_budget"  value="" size="25" step="any" name="r3_approved_budget" data-dtid="" class="r3_approved_budget text-center" disabled onchange="__mysys_budget_allotment_ent.__indirect_ps_totals(); __mysys_budget_allotment_ent.__combined_totals();" onmouseout="__mysys_budget_allotment_ent.__indirect_ps_totals(); __mysys_budget_allotment_ent.__combined_totals();"/>
                                                        </td>
                                                        <td class="text-center align-middle" nowrap>
                                                            <input type="number" id="proposed_realignment"  value="" size="25" step="any" name="proposed_realignment" data-dtid="" class="proposed_realignment text-center" disabled/>
                                                        </td>
                                                    </tr>
                                                    <?php if(!empty($recid)):
                                                        $query = $this->db->query("
                                                        SELECT
                                                            `recid`,
                                                            `expense_item`,
                                                            `particulars`,
                                                            `code`,
                                                            `approved_budget`,
                                                            `r1_approved_budget`,
                                                            `r2_approved_budget`,
                                                            `r3_approved_budget`,
                                                            `proposed_realignment`
                                                        FROM
                                                            `tbl_budget_indirect_ps_dt`
                                                        WHERE 
                                                            `project_id` = '$recid'"
                                                        );
                                                        $result = $query->getResultArray();
                                                        foreach ($result as $data):
                                                            $dt_id = $data['recid'];
                                                            $expense_item = $data['expense_item'];
                                                            $particulars = $data['particulars'];
                                                            $code = $data['code'];
                                                            $approved_budget = $data['approved_budget'];
                                                            $r1_approved_budget = $data['r1_approved_budget'];
                                                            $r2_approved_budget = $data['r2_approved_budget'];
                                                            $r3_approved_budget = $data['r3_approved_budget'];
                                                            $proposed_realignment = $data['proposed_realignment'];
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
                                                                    onclick="__mysys_budget_allotment_ent.my_add_budget_indirect_line_above(this);">
                                                                    <i class="ti ti-plus"></i>
                                                                </a>
                                                            </div>
                                                        </td>
                                                        <td class="text-center align-middle" nowrap>
                                                            <input type="text" id="expense_item"  value="<?=$expense_item;?>" size="60"  name="expense_item" class="expense_item">
                                                        </td>
                                                        <td class="align-middle" nowrap>
                                                            <input type="text" id="sub_object_code"  value="<?=$particulars;?>" size="60" placeholder="Enter sub-object code"  name="sub_object_code" class="psuacs_code sub_object_code">
                                                        </td>
                                                        <td class="text-center align-middle" nowrap>
                                                            <input type="text" id="uacs"  value="<?=$code;?>" size="25"  name="uacs" class="uacs text-center" disabled>
                                                        </td>
                                                        <td class="text-center align-middle" nowrap>
                                                            <input type="number" id="approved_budget"  value="<?=$approved_budget;?>" size="25" step="any" data-dtid="<?=$dt_id;?>" <?= ($tagging == 'For Approval') ? 'disabled' : ''; ?> name="approved_budget" class="approved_budget text-center" onchange="__mysys_budget_allotment_ent.__indirect_ps_totals(); __mysys_budget_allotment_ent.__combined_totals();" onmouseout="__mysys_budget_allotment_ent.__indirect_ps_totals(); __mysys_budget_allotment_ent.__combined_totals();"/>
                                                        </td>
                                                        <td class="text-center align-middle" nowrap>
                                                            <input type="number" id="r1_approved_budget"  value="<?=$r1_approved_budget;?>" size="25" step="any" name="r1_approved_budget" data-dtid="" class="r1_approved_budget text-center" disabled onchange="__mysys_budget_allotment_ent.__indirect_ps_totals(); __mysys_budget_allotment_ent.__combined_totals();" onmouseout="__mysys_budget_allotment_ent.__indirect_ps_totals(); __mysys_budget_allotment_ent.__combined_totals();"/>
                                                        </td>
                                                        <td class="text-center align-middle" nowrap>
                                                            <input type="number" id="r2_approved_budget"  value="<?=$r2_approved_budget;?>" size="25" step="any" name="r2_approved_budget" data-dtid="" class="r2_approved_budget text-center" disabled onchange="__mysys_budget_allotment_ent.__indirect_ps_totals(); __mysys_budget_allotment_ent.__combined_totals();" onmouseout="__mysys_budget_allotment_ent.__indirect_ps_totals(); __mysys_budget_allotment_ent.__combined_totals();"/>
                                                        </td>
                                                        <td class="text-center align-middle" nowrap>
                                                            <input type="number" id="r3_approved_budget"  value="<?=$r3_approved_budget;?>" size="25" step="any" name="r3_approved_budget" data-dtid="" class="r3_approved_budget text-center" disabled onchange="__mysys_budget_allotment_ent.__indirect_ps_totals(); __mysys_budget_allotment_ent.__combined_totals();" onmouseout="__mysys_budget_allotment_ent.__indirect_ps_totals(); __mysys_budget_allotment_ent.__combined_totals();"/>
                                                        </td>
                                                        <td class="text-center align-middle" nowrap>
                                                            <input type="number" id="proposed_realignment"  value="<?=$proposed_realignment;?>" size="25" step="any" name="proposed_realignment" data-dtid="" class="proposed_realignment text-center" disabled/>
                                                        </td>
                                                    </tr>
                                                    <?php endforeach; endif;?>
                                                </tbody>
                                            </table>                  
                                        </div>
                                    </div>
                                </div>
                                <hr>
 
                            </div>

                            <!-- MOOE TAB CONTENT -->
                            <div class="tab-pane p-3 pb-0" id="mooe-pill" role="tabpanel">
                                <div class="row">
                                    <div class="table-responsive pe-2 ps-0">
                                        <div class="col-md-12 mb-2">
                                            <span class="ms-3 fw-bold">Direct Cost:</span>
                                            <table id="budget_mooe_line_items" class="table-sm table-striped budgetmooedata-list">
                                                <thead>
                                                    <th class="text-center">
                                                        <a class="text-info px-2 fs-7 bg-hover-primary nav-icon-hover position-relative z-index-5" id="btn_budgetmooeitem_add" href="javascript:__mysys_budget_allotment_ent.my_add_budget_mooe_line();"><i class="ti ti-new-section"></i></a>
                                                    </th>
                                                    <th class="text-center align-middle">Expense Item</th>
                                                    <th class="text-center align-middle">MOOE - Particulars</th>
                                                    <th class="text-center align-middle">UACS.</th>
                                                    <th class="text-center align-middle">Approved Budget</th>
                                                    <th class="text-center align-middle">1st Realignment</th>
                                                    <th class="text-center align-middle">2nd Realignment</th>
                                                    <th class="text-center align-middle">3rd Realignment</th>
                                                    <th class="text-center align-middle">Proposed Realignment</th>
                                                </thead>
                                                <tbody class="align-middle">
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
                                                                onclick="__mysys_budget_allotment_ent.my_add_budget_mooe_line_above(this);">
                                                                    <i class="ti ti-plus"></i>
                                                                </a>
                                                            </div>
                                                        </td>
                                                        <td class="text-center align-middle" nowrap>
                                                            <input type="text" id="expense_item"  value="" size="60"  name="expense_item" class="expense_item">
                                                        </td>
                                                        <td class="align-middle" nowrap>
                                                            <input type="text" id="sub_object_code"  value="" size="60" placeholder="Enter sub-object code"  name="sub_object_code" class="mooeuacs_code sub_object_code">
                                                        </td>
                                                        <td class="text-center align-middle" nowrap>
                                                            <input type="text" id="uacs"  value="" size="25"  name="uacs" class="uacs text-center" disabled>
                                                        </td>
                                                        <td class="text-center align-middle" nowrap>
                                                            <input type="number" id="approved_budget"  value="" size="25" step="any" name="approved_budget" data-dtid="" <?= ($tagging == 'For Approval') ? 'disabled' : ''; ?> class="approved_budget text-center" onchange="__mysys_budget_allotment_ent.__direct_mooe_totals(); __mysys_budget_allotment_ent.__combined_totals();" onmouseout="__mysys_budget_allotment_ent.__direct_mooe_totals(); __mysys_budget_allotment_ent.__combined_totals();"/>
                                                        </td>
                                                        <td class="text-center align-middle" nowrap>
                                                            <input type="number" id="r1_approved_budget"  value="" size="25" step="any" name="r1_approved_budget" data-dtid="" class="r1_approved_budget text-center" onchange="__mysys_budget_allotment_ent.__direct_mooe_totals(); __mysys_budget_allotment_ent.__combined_totals();" onmouseout="__mysys_budget_allotment_ent.__direct_mooe_totals(); __mysys_budget_allotment_ent.__combined_totals();" />
                                                        </td>
                                                        <td class="text-center align-middle" nowrap>
                                                            <input type="number" id="r2_approved_budget"  value="" size="25" step="any" name="r2_approved_budget" data-dtid="" class="r2_approved_budget text-center" onchange="__mysys_budget_allotment_ent.__direct_mooe_totals(); __mysys_budget_allotment_ent.__combined_totals();" onmouseout="__mysys_budget_allotment_ent.__direct_mooe_totals(); __mysys_budget_allotment_ent.__combined_totals();" />
                                                        </td>
                                                        <td class="text-center align-middle" nowrap>
                                                            <input type="number" id="r3_approved_budget"  value="" size="25" step="any" name="r3_approved_budget" data-dtid="" class="r3_approved_budget text-center" onchange="__mysys_budget_allotment_ent.__direct_mooe_totals(); __mysys_budget_allotment_ent.__combined_totals();" onmouseout="__mysys_budget_allotment_ent.__direct_mooe_totals(); __mysys_budget_allotment_ent.__combined_totals();" />
                                                        </td>
                                                        <td class="text-center align-middle" nowrap>
                                                            <input type="number" id="proposed_realignment"  value="" size="25" step="any" name="proposed_realignment" data-dtid="" class="proposed_realignment text-center" disabled/>
                                                        </td>
                                                    </tr>
                                                    <?php if(!empty($recid)):
                                                        $query = $this->db->query("
                                                        SELECT
                                                            `recid`,
                                                            `expense_item`,
                                                            `particulars`,
                                                            `code`,
                                                            `approved_budget`,
                                                            `r1_approved_budget`,
                                                            `r2_approved_budget`,
                                                            `r3_approved_budget`,
                                                            `proposed_realignment`
                                                        FROM
                                                            `tbl_budget_direct_mooe_dt`
                                                        WHERE 
                                                            `project_id` = '$recid'"
                                                        );
                                                        $result = $query->getResultArray();
                                                        foreach ($result as $data):
                                                            $dt_id = $data['recid'];
                                                            $expense_item = $data['expense_item'];
                                                            $particulars = $data['particulars'];
                                                            $code = $data['code'];
                                                            $approved_budget = $data['approved_budget'];
                                                            $r1_approved_budget = $data['r1_approved_budget'];
                                                            $r2_approved_budget = $data['r2_approved_budget'];
                                                            $r3_approved_budget = $data['r3_approved_budget'];
                                                            $proposed_realignment = $data['proposed_realignment'];
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
                                                                onclick="__mysys_budget_allotment_ent.my_add_budget_mooe_line_above(this);">
                                                                    <i class="ti ti-plus"></i>
                                                                </a>
                                                            </div>
                                                        </td>
                                                        <td class="text-center align-middle" nowrap>
                                                            <input type="text" id="expense_item"  value="<?=$expense_item;?>" size="60"  name="expense_item" class="expense_item">
                                                        </td>
                                                        <td class="align-middle" nowrap>
                                                            <input type="text" id="sub_object_code"  value="<?=$particulars;?>" size="60" placeholder="Enter sub-object code"  name="sub_object_code" class="mooeuacs_code sub_object_code">
                                                        </td>
                                                        <td class="text-center align-middle" nowrap>
                                                            <input type="text" id="uacs"  value="<?=$code;?>" size="25"  name="uacs" class="uacs text-center" disabled>
                                                        </td>
                                                        <td class="text-center align-middle" nowrap>
                                                            <input type="number" id="approved_budget"  value="<?=$approved_budget;?>" size="25" step="any" name="approved_budget" data-dtid="<?=$dt_id;?>" <?= ($tagging == 'For Approval') ? 'disabled' : ''; ?> class="approved_budget text-center" onchange="__mysys_budget_allotment_ent.__direct_mooe_totals(); __mysys_budget_allotment_ent.__combined_totals();" onmouseout="__mysys_budget_allotment_ent.__direct_mooe_totals(); __mysys_budget_allotment_ent.__combined_totals();"/>
                                                        </td>
                                                        <td class="text-center align-middle" nowrap>
                                                            <input type="number" id="r1_approved_budget"  value="<?=$r1_approved_budget;?>" size="25" step="any" name="r1_approved_budget" data-dtid="" class="r1_approved_budget text-center" onchange="__mysys_budget_allotment_ent.__direct_mooe_totals(); __mysys_budget_allotment_ent.__combined_totals();" onmouseout="__mysys_budget_allotment_ent.__direct_mooe_totals(); __mysys_budget_allotment_ent.__combined_totals();" />
                                                        </td>
                                                        <td class="text-center align-middle" nowrap>
                                                            <input type="number" id="r2_approved_budget"  value="<?=$r2_approved_budget;?>" size="25" step="any" name="r2_approved_budget" data-dtid="" class="r2_approved_budget text-center" onchange="__mysys_budget_allotment_ent.__direct_mooe_totals(); __mysys_budget_allotment_ent.__combined_totals();" onmouseout="__mysys_budget_allotment_ent.__direct_mooe_totals(); __mysys_budget_allotment_ent.__combined_totals();" />
                                                        </td>
                                                        <td class="text-center align-middle" nowrap>
                                                            <input type="number" id="r3_approved_budget"  value="<?=$r3_approved_budget;?>" size="25" step="any" name="r3_approved_budget" data-dtid="" class="r3_approved_budget text-center" onchange="__mysys_budget_allotment_ent.__direct_mooe_totals(); __mysys_budget_allotment_ent.__combined_totals();" onmouseout="__mysys_budget_allotment_ent.__direct_mooe_totals(); __mysys_budget_allotment_ent.__combined_totals();" />
                                                        </td>
                                                        <td class="text-center align-middle" nowrap>
                                                            <input type="number" id="proposed_realignment"  value="<?=$proposed_realignment;?>" size="25" step="any" name="proposed_realignment" data-dtid="" class="proposed_realignment text-center" disabled/>
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
                                                        <a class="text-info px-2 fs-7 bg-hover-primary nav-icon-hover position-relative z-index-5" id="btn_budgetmooeitem_add" href="javascript:__mysys_budget_allotment_ent.my_add_budget_indirect_mooe_line();"><i class="ti ti-new-section"></i></a>
                                                    </th>
                                                    <th class="text-center align-middle">Expense Item</th>
                                                    <th class="text-center align-middle">MOOE - Particulars</th>
                                                    <th class="text-center align-middle">UACS.</th>
                                                    <th class="text-center align-middle">Approved Budget</th>
                                                    <th class="text-center align-middle">1st Realignment</th>
                                                    <th class="text-center align-middle">2nd Realignment</th>
                                                    <th class="text-center align-middle">3rd Realignment</th>
                                                    <th class="text-center align-middle">Proposed Realignment</th>
                                                </thead>
                                                <tbody class="align-middle">
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
                                                                onclick="__mysys_budget_allotment_ent.my_add_budget_indirect_mooe_line_above(this);">
                                                                    <i class="ti ti-plus"></i>
                                                                </a>
                                                            </div>
                                                        </td>
                                                        <td class="text-center align-middle" nowrap>
                                                            <input type="text" id="expense_item"  value="" size="60"  name="expense_item" class="expense_item">
                                                        </td>
                                                        <td class="align-middle" nowrap>
                                                            <input type="text" id="sub_object_code"  value="" size="60" placeholder="Enter sub-object code"  name="sub_object_code" class="mooeuacs_code sub_object_code">
                                                        </td>
                                                        <td class="text-center align-middle" nowrap>
                                                            <input type="text" id="uacs"  value="" size="25"  name="uacs" class="uacs text-center" disabled>
                                                        </td>
                                                        <td class="text-center align-middle" nowrap>
                                                            <input type="number" id="approved_budget"  value="" size="25" step="any" name="approved_budget" data-dtid="" <?= ($tagging == 'For Approval') ? 'disabled' : ''; ?> class="approved_budget text-center" onchange="__mysys_budget_allotment_ent.__indirect_mooe_totals(); __mysys_budget_allotment_ent.__combined_totals();" onmouseout="__mysys_budget_allotment_ent.__indirect_mooe_totals(); __mysys_budget_allotment_ent.__combined_totals();"/>
                                                        </td>
                                                        <td class="text-center align-middle" nowrap>
                                                            <input type="number" id="r1_approved_budget"  value="" size="25" step="any" name="r1_approved_budget" data-dtid="" class="r1_approved_budget text-center" onchange="__mysys_budget_allotment_ent.__indirect_mooe_totals(); __mysys_budget_allotment_ent.__combined_totals();" onmouseout="__mysys_budget_allotment_ent.__indirect_mooe_totals(); __mysys_budget_allotment_ent.__combined_totals();" />
                                                        </td>
                                                        <td class="text-center align-middle" nowrap>
                                                            <input type="number" id="r2_approved_budget"  value="" size="25" step="any" name="r2_approved_budget" data-dtid="" class="r2_approved_budget text-center" onchange="__mysys_budget_allotment_ent.__indirect_mooe_totals(); __mysys_budget_allotment_ent.__combined_totals();" onmouseout="__mysys_budget_allotment_ent.__indirect_mooe_totals(); __mysys_budget_allotment_ent.__combined_totals();" />
                                                        </td>
                                                        <td class="text-center align-middle" nowrap>
                                                            <input type="number" id="r3_approved_budget"  value="" size="25" step="any" name="r3_approved_budget" data-dtid="" class="r3_approved_budget text-center" onchange="__mysys_budget_allotment_ent.__indirect_mooe_totals(); __mysys_budget_allotment_ent.__combined_totals();" onmouseout="__mysys_budget_allotment_ent.__indirect_mooe_totals(); __mysys_budget_allotment_ent.__combined_totals();" />
                                                        </td>
                                                        <td class="text-center align-middle" nowrap>
                                                            <input type="number" id="proposed_realignment"  value="" size="25" step="any" name="proposed_realignment" data-dtid="" class="proposed_realignment text-center" disabled/>
                                                        </td>
                                                    </tr>
                                                    <?php if(!empty($recid)):
                                                        $query = $this->db->query("
                                                        SELECT
                                                            `recid`,
                                                            `expense_item`,
                                                            `particulars`,
                                                            `code`,
                                                            `approved_budget`,
                                                            `r1_approved_budget`,
                                                            `r2_approved_budget`,
                                                            `r3_approved_budget`,
                                                            `proposed_realignment`
                                                        FROM
                                                            `tbl_budget_indirect_mooe_dt`
                                                        WHERE 
                                                            `project_id` = '$recid'"
                                                        );
                                                        $result = $query->getResultArray();
                                                        foreach ($result as $data):
                                                            $dt_id = $data['recid'];
                                                            $expense_item = $data['expense_item'];
                                                            $particulars = $data['particulars'];
                                                            $code = $data['code'];
                                                            $approved_budget = $data['approved_budget'];
                                                            $r1_approved_budget = $data['r1_approved_budget'];
                                                            $r2_approved_budget = $data['r2_approved_budget'];
                                                            $r3_approved_budget = $data['r3_approved_budget'];
                                                            $proposed_realignment = $data['proposed_realignment'];
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
                                                                    onclick="__mysys_budget_allotment_ent.my_add_budget_indirect_mooe_line_above(this);">
                                                                    <i class="ti ti-plus"></i>
                                                                </a>
                                                            </div>
                                                        </td>
                                                        <td class="text-center align-middle" nowrap>
                                                            <input type="text" id="expense_item"  value="<?=$expense_item;?>" size="60"  name="expense_item" class="expense_item">
                                                        </td>
                                                        <td class="align-middle" nowrap>
                                                            <input type="text" id="sub_object_code"  value="<?=$particulars;?>" size="60" placeholder="Enter sub-object code"  name="sub_object_code" class="mooeuacs_code sub_object_code">
                                                        </td>
                                                        <td class="text-center align-middle" nowrap>
                                                            <input type="text" id="uacs"  value="<?=$code;?>" size="25"  name="uacs" class="uacs text-center" disabled>
                                                        </td>
                                                        <td class="text-center align-middle" nowrap>
                                                            <input type="number" id="approved_budget"  value="<?=$approved_budget;?>" size="25" step="any" name="approved_budget" data-dtid="<?=$dt_id;?>" <?= ($tagging == 'For Approval') ? 'disabled' : ''; ?> class="approved_budget text-center" onchange="__mysys_budget_allotment_ent.__indirect_mooe_totals(); __mysys_budget_allotment_ent.__combined_totals();" onmouseout="__mysys_budget_allotment_ent.__indirect_mooe_totals(); __mysys_budget_allotment_ent.__combined_totals();"/>
                                                        </td>
                                                        <td class="text-center align-middle" nowrap>
                                                            <input type="number" id="r1_approved_budget"  value="<?=$r1_approved_budget;?>" size="25" step="any" name="r1_approved_budget" data-dtid="" class="r1_approved_budget text-center" onchange="__mysys_budget_allotment_ent.__indirect_mooe_totals(); __mysys_budget_allotment_ent.__combined_totals();" onmouseout="__mysys_budget_allotment_ent.__indirect_mooe_totals(); __mysys_budget_allotment_ent.__combined_totals();" />
                                                        </td>
                                                        <td class="text-center align-middle" nowrap>
                                                            <input type="number" id="r2_approved_budget"  value="<?=$r2_approved_budget;?>" size="25" step="any" name="r2_approved_budget" data-dtid="" class="r2_approved_budget text-center" onchange="__mysys_budget_allotment_ent.__indirect_mooe_totals(); __mysys_budget_allotment_ent.__combined_totals();" onmouseout="__mysys_budget_allotment_ent.__indirect_mooe_totals(); __mysys_budget_allotment_ent.__combined_totals();" />
                                                        </td>
                                                        <td class="text-center align-middle" nowrap>
                                                            <input type="number" id="r3_approved_budget"  value="<?=$r3_approved_budget;?>" size="25" step="any" name="r3_approved_budget" data-dtid="" class="r3_approved_budget text-center" onchange="__mysys_budget_allotment_ent.__indirect_mooe_totals(); __mysys_budget_allotment_ent.__combined_totals();" onmouseout="__mysys_budget_allotment_ent.__indirect_mooe_totals(); __mysys_budget_allotment_ent.__combined_totals();" />
                                                        </td>
                                                        <td class="text-center align-middle" nowrap>
                                                            <input type="number" id="proposed_realignment"  value="<?=$proposed_realignment;?>" size="25" step="any" name="proposed_realignment" data-dtid="" class="proposed_realignment text-center" disabled/>
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
                            <div class="tab-pane p-3 pb-0" id="co-pill" role="tabpanel">
                                <div class="row">
                                    <div class="table-responsive pe-2 ps-0">
                                        <div class="col-md-12 mb-2">
                                            <span class="ms-3 fw-bold">Direct Cost:</span>
                                            <table id="budget_co_line_items" class="table-sm table-striped budgetcodata-list">
                                                <thead>
                                                    <th class="text-center">
                                                        <a class="text-info px-2 fs-7 bg-hover-primary nav-icon-hover position-relative z-index-5" id="btn_budgetcoitem_add" href="javascript:__mysys_budget_allotment_ent.my_add_budget_co_line();"><i class="ti ti-new-section"></i></a>
                                                    </th>
                                                    <th class="text-center align-middle">CO - Expense Item</th>
                                                    <th class="text-center align-middle">UACS.</th>
                                                    <th class="text-center align-middle">Approved Budget</th>
                                                    <th class="text-center align-middle">1st Realignment</th>
                                                    <th class="text-center align-middle">2nd Realignment</th>
                                                    <th class="text-center align-middle">3rd Realignment</th>
                                                    <th class="text-center align-middle">Proposed Realignment</th>
                                                </thead>
                                                <tbody class="align-middle">
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
                                                                    onclick="__mysys_budget_allotment_ent.my_add_budget_co_line_above(this);">
                                                                    <i class="ti ti-plus"></i>
                                                                </a>
                                                            </div>
                                                        </td>
                                                        <td class="text-center align-middle" nowrap>
                                                            <input type="text" id="particulars"  value="" style="width:300px; height:30px;"  name="particulars" class="particulars text-center">
                                                        </td>
                                                        <td class="text-center align-middle" nowrap>
                                                            <input type="text" id="uacs"  value="" size="25"  name="uacs" class="uacs text-center">
                                                        </td>
                                                        <td class="text-center align-middle" nowrap>
                                                            <input type="number" id="approved_budget"  value="" size="25" step="any" name="approved_budget" data-dtid="" <?= ($tagging == 'For Approval') ? 'disabled' : ''; ?>  class="approved_budget text-center" onchange="__mysys_budget_allotment_ent.__direct_co_totals(); __mysys_budget_allotment_ent.__combined_totals();" onmouseout="__mysys_budget_allotment_ent.__direct_co_totals(); __mysys_budget_allotment_ent.__combined_totals();" />
                                                        </td>
                                                        <td class="text-center align-middle" nowrap>
                                                            <input type="number" id="r1_approved_budget"  value="" size="25" step="any" name="r1_approved_budget" data-dtid="" class="r1_approved_budget text-center" onchange="__mysys_budget_allotment_ent.__direct_co_totals(); __mysys_budget_allotment_ent.__combined_totals();" onmouseout="__mysys_budget_allotment_ent.__direct_co_totals(); __mysys_budget_allotment_ent.__combined_totals();" />
                                                        </td>
                                                        <td class="text-center align-middle" nowrap>
                                                            <input type="number" id="r2_approved_budget"  value="" size="25" step="any" name="r2_approved_budget" data-dtid="" class="r2_approved_budget text-center" onchange="__mysys_budget_allotment_ent.__direct_co_totals(); __mysys_budget_allotment_ent.__combined_totals();" onmouseout="__mysys_budget_allotment_ent.__direct_co_totals(); __mysys_budget_allotment_ent.__combined_totals();" />
                                                        </td>
                                                        <td class="text-center align-middle" nowrap>
                                                            <input type="number" id="r3_approved_budget"  value="" size="25" step="any" name="r3_approved_budget" data-dtid="" class="r3_approved_budget text-center" onchange="__mysys_budget_allotment_ent.__direct_co_totals(); __mysys_budget_allotment_ent.__combined_totals();" onmouseout="__mysys_budget_allotment_ent.__direct_co_totals(); __mysys_budget_allotment_ent.__combined_totals();" />
                                                        </td>
                                                        <td class="text-center align-middle" nowrap>
                                                            <input type="number" id="proposed_realignment"  value="" size="25" step="any" name="proposed_realignment" data-dtid="" class="proposed_realignment text-center" disabled/>
                                                        </td>
                                                    </tr>
                                                    <?php if(!empty($recid)):
                                                        $query = $this->db->query("
                                                        SELECT
                                                            `recid`,
                                                            `particulars`,
                                                            `code`,
                                                            `approved_budget`,
                                                            `r1_approved_budget`,
                                                            `r2_approved_budget`,
                                                            `r3_approved_budget`,
                                                            `proposed_realignment`
                                                        FROM
                                                            `tbl_budget_direct_co_dt`
                                                        WHERE 
                                                            `project_id` = '$recid'"
                                                        );
                                                        $result = $query->getResultArray();
                                                        foreach ($result as $data):
                                                            $dt_id = $data['recid'];
                                                            $particulars = $data['particulars'];
                                                            $code = $data['code'];
                                                            $approved_budget = $data['approved_budget'];
                                                            $r1_approved_budget = $data['r1_approved_budget'];
                                                            $r2_approved_budget = $data['r2_approved_budget'];
                                                            $r3_approved_budget = $data['r3_approved_budget'];
                                                            $proposed_realignment = $data['proposed_realignment'];
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
                                                                    onclick="__mysys_budget_allotment_ent.my_add_budget_co_line_above(this);">
                                                                    <i class="ti ti-plus"></i>
                                                                </a>
                                                            </div>
                                                        </td>
                                                        <td class="text-center align-middle" nowrap>
                                                            <input type="text" id="particulars"  value="<?=$particulars;?>" style="width:300px; height:30px;"  name="particulars" class="particulars text-center">
                                                        </td>
                                                        <td class="text-center align-middle" nowrap>
                                                            <input type="text" id="uacs"  value="<?=$code;?>" size="25"  name="uacs" class="uacs text-center">
                                                        </td>
                                                        <td class="text-center align-middle" nowrap>
                                                            <input type="number" id="approved_budget"  value="<?=$approved_budget;?>" size="25" step="any" data-dtid="<?=$dt_id;?>" <?= ($tagging == 'For Approval') ? 'disabled' : ''; ?> name="approved_budget" class="approved_budget text-center" onchange="__mysys_budget_allotment_ent.__direct_co_totals(); __mysys_budget_allotment_ent.__combined_totals();" onmouseout="__mysys_budget_allotment_ent.__direct_co_totals(); __mysys_budget_allotment_ent.__combined_totals();" />
                                                        </td>
                                                        <td class="text-center align-middle" nowrap>
                                                            <input type="number" id="r1_approved_budget"  value="<?=$r1_approved_budget;?>" size="25" step="any" name="r1_approved_budget" data-dtid="" class="r1_approved_budget text-center" disabled onchange="__mysys_budget_allotment_ent.__direct_co_totals(); __mysys_budget_allotment_ent.__combined_totals();" onmouseout="__mysys_budget_allotment_ent.__direct_co_totals(); __mysys_budget_allotment_ent.__combined_totals();" />
                                                        </td>
                                                        <td class="text-center align-middle" nowrap>
                                                            <input type="number" id="r2_approved_budget"  value="<?=$r2_approved_budget;?>" size="25" step="any" name="r2_approved_budget" data-dtid="" class="r2_approved_budget text-center" disabled onchange="__mysys_budget_allotment_ent.__direct_co_totals(); __mysys_budget_allotment_ent.__combined_totals();" onmouseout="__mysys_budget_allotment_ent.__direct_co_totals(); __mysys_budget_allotment_ent.__combined_totals();" />
                                                        </td>
                                                        <td class="text-center align-middle" nowrap>
                                                            <input type="number" id="r3_approved_budget"  value="<?=$r3_approved_budget;?>" size="25" step="any" name="r3_approved_budget" data-dtid="" class="r3_approved_budget text-center" disabled onchange="__mysys_budget_allotment_ent.__direct_co_totals(); __mysys_budget_allotment_ent.__combined_totals();" onmouseout="__mysys_budget_allotment_ent.__direct_co_totals(); __mysys_budget_allotment_ent.__combined_totals();" />
                                                        </td>
                                                        <td class="text-center align-middle" nowrap>
                                                            <input type="number" id="proposed_realignment"  value="<?=$proposed_realignment;?>" size="25" step="any" name="proposed_realignment" data-dtid="" class="proposed_realignment text-center" disabled/>
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
                                                        <a class="text-info px-2 fs-7 bg-hover-primary nav-icon-hover position-relative z-index-5" id="btn_budgetcoitem_add" href="javascript:__mysys_budget_allotment_ent.my_add_budget_indirect_co_line();"><i class="ti ti-new-section"></i></a>
                                                    </th>
                                                    <th class="text-center align-middle">CO - Expense Item</th>
                                                    <th class="text-center align-middle">UACS.</th>
                                                    <th class="text-center align-middle">Approved Budget</th>
                                                    <th class="text-center align-middle">1st Realignment</th>
                                                    <th class="text-center align-middle">2nd Realignment</th>
                                                    <th class="text-center align-middle">3rd Realignment</th>
                                                    <th class="text-center align-middle">Proposed Realignment</th>
                                                </thead>
                                                <tbody class="align-middle">
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
                                                                    onclick="__mysys_budget_allotment_ent.my_add_budget_indirect_co_line_above(this);">
                                                                    <i class="ti ti-plus"></i>
                                                                </a>
                                                            </div>
                                                        </td>
                                                        <td class="text-center align-middle" nowrap>
                                                            <input type="text" id="particulars"  value="" style="width:300px; height:30px;"   name="particulars" class="particulars text-center">
                                                        </td>
                                                        <td class="text-center align-middle" nowrap>
                                                            <input type="text" id="uacs"  value="" size="25"  name="uacs" class="uacs text-center">
                                                        </td>
                                                        <td class="text-center align-middle" nowrap>
                                                            <input type="number" id="approved_budget"  value="" size="25" step="any" name="approved_budget" data-dtid="" <?= ($tagging == 'For Approval') ? 'disabled' : ''; ?>  class="approved_budget text-center" onchange="__mysys_budget_allotment_ent.__indirect_co_totals(); __mysys_budget_allotment_ent.__combined_totals();" onmouseout="__mysys_budget_allotment_ent.__indirect_co_totals(); __mysys_budget_allotment_ent.__combined_totals();" />
                                                        </td>
                                                        <td class="text-center align-middle" nowrap>
                                                            <input type="number" id="r1_approved_budget"  value="" size="25" step="any" name="r1_approved_budget" data-dtid="" class="r1_approved_budget text-center" onchange="__mysys_budget_allotment_ent.__indirect_co_totals(); __mysys_budget_allotment_ent.__combined_totals();" onmouseout="__mysys_budget_allotment_ent.__indirect_co_totals(); __mysys_budget_allotment_ent.__combined_totals();" />
                                                        </td>
                                                        <td class="text-center align-middle" nowrap>
                                                            <input type="number" id="r2_approved_budget"  value="" size="25" step="any" name="r2_approved_budget" data-dtid="" class="r2_approved_budget text-center" onchange="__mysys_budget_allotment_ent.__indirect_co_totals(); __mysys_budget_allotment_ent.__combined_totals();" onmouseout="__mysys_budget_allotment_ent.__indirect_co_totals(); __mysys_budget_allotment_ent.__combined_totals();" />
                                                        </td>
                                                        <td class="text-center align-middle" nowrap>
                                                            <input type="number" id="r3_approved_budget"  value="" size="25" step="any" name="r3_approved_budget" data-dtid="" class="r3_approved_budget text-center" onchange="__mysys_budget_allotment_ent.__indirect_co_totals(); __mysys_budget_allotment_ent.__combined_totals();" onmouseout="__mysys_budget_allotment_ent.__indirect_co_totals(); __mysys_budget_allotment_ent.__combined_totals();" />
                                                        </td>
                                                        <td class="text-center align-middle" nowrap>
                                                            <input type="number" id="proposed_realignment"  value="" size="25" step="any" name="proposed_realignment" data-dtid="" class="proposed_realignment text-center" disabled/>
                                                        </td>
                                                    </tr>
                                                    <?php if(!empty($recid)):
                                                        $query = $this->db->query("
                                                        SELECT
                                                            `recid`,
                                                            `particulars`,
                                                            `code`,
                                                            `approved_budget`,
                                                            `r1_approved_budget`,
                                                            `r2_approved_budget`,
                                                            `r3_approved_budget`,
                                                            `proposed_realignment`
                                                        FROM
                                                            `tbl_budget_indirect_co_dt`
                                                        WHERE 
                                                            `project_id` = '$recid'"
                                                        );
                                                        $result = $query->getResultArray();
                                                        foreach ($result as $data):
                                                            $dt_id = $data['recid'];
                                                            $particulars = $data['particulars'];
                                                            $code = $data['code'];
                                                            $approved_budget = $data['approved_budget'];
                                                            $r1_approved_budget = $data['r1_approved_budget'];
                                                            $r2_approved_budget = $data['r2_approved_budget'];
                                                            $r3_approved_budget = $data['r3_approved_budget'];
                                                            $proposed_realignment = $data['proposed_realignment'];
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
                                                                    onclick="__mysys_budget_allotment_ent.my_add_budget_indirect_co_line_above(this);">
                                                                    <i class="ti ti-plus"></i>
                                                                </a>
                                                            </div>
                                                        </td>
                                                        <td class="text-center align-middle" nowrap>
                                                            <input type="text" id="particulars"  value="<?=$particulars;?>" style="width:300px; height:30px;"  name="particulars" class="particulars text-center">
                                                        </td>
                                                        <td class="text-center align-middle" nowrap>
                                                            <input type="text" id="uacs"  value="<?=$code;?>" size="25"  name="uacs" class="uacs text-center">
                                                        </td>
                                                        <td class="text-center align-middle" nowrap>
                                                            <input type="number" id="approved_budget"  value="<?=$approved_budget;?>" size="25" step="any" data-dtid="<?=$dt_id;?>" <?= ($tagging == 'For Approval') ? 'disabled' : ''; ?> name="approved_budget" class="approved_budget text-center" onchange="__mysys_budget_allotment_ent.__indirect_co_totals(); __mysys_budget_allotment_ent.__combined_totals();" onmouseout="__mysys_budget_allotment_ent.__indirect_co_totals(); __mysys_budget_allotment_ent.__combined_totals();" />
                                                        </td>
                                                        <td class="text-center align-middle" nowrap>
                                                            <input type="number" id="r1_approved_budget"  value="<?=$r1_approved_budget;?>" size="25" step="any" name="r1_approved_budget" data-dtid="" class="r1_approved_budget text-center" disabled onchange="__mysys_budget_allotment_ent.__indirect_co_totals(); __mysys_budget_allotment_ent.__combined_totals();" onmouseout="__mysys_budget_allotment_ent.__indirect_co_totals(); __mysys_budget_allotment_ent.__combined_totals();" />
                                                        </td>
                                                        <td class="text-center align-middle" nowrap>
                                                            <input type="number" id="r2_approved_budget"  value="<?=$r2_approved_budget;?>" size="25" step="any" name="r2_approved_budget" data-dtid="" class="r2_approved_budget text-center" disabled onchange="__mysys_budget_allotment_ent.__indirect_co_totals(); __mysys_budget_allotment_ent.__combined_totals();" onmouseout="__mysys_budget_allotment_ent.__indirect_co_totals(); __mysys_budget_allotment_ent.__combined_totals();" />
                                                        </td>
                                                        <td class="text-center align-middle" nowrap>
                                                            <input type="number" id="r3_approved_budget"  value="<?=$r3_approved_budget;?>" size="25" step="any" name="r3_approved_budget" data-dtid="" class="r3_approved_budget text-center" disabled onchange="__mysys_budget_allotment_ent.__indirect_co_totals(); __mysys_budget_allotment_ent.__combined_totals();" onmouseout="__mysys_budget_allotment_ent.__indirect_co_totals(); __mysys_budget_allotment_ent.__combined_totals();" />
                                                        </td>
                                                        <td class="text-center align-middle" nowrap>
                                                            <input type="number" id="proposed_realignment"  value="<?=$proposed_realignment;?>" size="25" step="any" name="proposed_realignment" data-dtid="" class="proposed_realignment text-center" disabled/>
                                                        </td>
                                                    </tr>
                                                    <?php endforeach; endif;?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            
                            <!-- AC TAB CONTENT -->
                            <div class="tab-pane p-3 pb-0" id="ac-pill" role="tabpanel">
                                <div class="row">
                                    <div class="table-responsive pe-2 ps-0">
                                        <div class="col-md-12 mb-2">
                                            <table id="budget_ac_line_items" class="table-sm table-striped budgetacdata-list">
                                                <thead>
                                                    <th class="text-center">
                                                        <a class="text-info px-2 fs-7 bg-hover-primary nav-icon-hover position-relative z-index-5" id="btn_budgetacitem_add" href="javascript:__mysys_budget_allotment_ent.my_add_budget_ac_line();"><i class="ti ti-new-section"></i></a>
                                                    </th>
                                                    <th class="text-center align-middle">Expense Item</th>
                                                    <th class="text-center align-middle">UACS.</th>
                                                    <th class="text-center align-middle">Approved Budget</th>
                                                </thead>
                                                <tbody class="align-middle">
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
                                                                    onclick="__mysys_budget_allotment_ent.my_add_budget_ac_line_above(this);">
                                                                    <i class="ti ti-plus"></i>
                                                                </a>
                                                            </div>
                                                        </td>
                                                        <td class="text-center align-middle" nowrap>
                                                            <input type="text" id="particulars"  value="" style="width:300px; height:30px;"  name="particulars" class="particulars text-center">
                                                        </td>
                                                        <td class="text-center align-middle" nowrap>
                                                            <input type="text" id="uacs"  value="" size="25"  name="uacs" class="uacs text-center">
                                                        </td>
                                                        <td class="text-center align-middle" nowrap>
                                                            <input type="number" id="approved_budget"  value="" size="25" step="any" data-dtid="" name="approved_budget"  class="approved_budget text-center"/>
                                                        </td>
                                                    </tr>
                                                    <?php if(!empty($recid)):
                                                        $query = $this->db->query("
                                                        SELECT
                                                            `recid`,
                                                            `particulars`,
                                                            `code`,
                                                            `approved_budget`
                                                        FROM
                                                            `tbl_budget_savings_dt`
                                                        WHERE 
                                                            `project_id` = '$recid'"
                                                        );
                                                        $result = $query->getResultArray();
                                                        foreach ($result as $data):
                                                            $dt_id = $data['recid'];
                                                            $particulars = $data['particulars'];
                                                            $code = $data['code'];
                                                            $approved_budget = $data['approved_budget'];
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
                                                                    onclick="__mysys_budget_allotment_ent.my_add_budget_ac_line_above(this);">
                                                                    <i class="ti ti-plus"></i>
                                                                </a>
                                                            </div>
                                                        </td>
                                                        <td class="text-center align-middle" nowrap>
                                                            <input type="text" id="particulars"  value="<?=$particulars;?>" style="width:300px; height:30px;"  name="particulars" class="particulars text-center">
                                                        </td>
                                                        <td class="text-center align-middle" nowrap>
                                                            <input type="text" id="uacs"  value="<?=$code;?>" size="25"  name="uacs" class="uacs text-center">
                                                        </td>
                                                        <td class="text-center align-middle" nowrap>
                                                            <input type="number" id="approved_budget"  value="<?=$approved_budget;?>" size="25" step="any" data-dtid="<?=$dt_id;?>" name="approved_budget" class="approved_budget text-center"/>
                                                        </td>
                                                    </tr>
                                                    <?php endforeach; endif;?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <hr>

                            <div class="row mb-3">
                                <div class="col-sm-6">

                                </div>
                                <div class="col-sm-6">
                                    <div class="row">
                                        <div class="col-sm-6">
                                            <span class="fw-bolder">Total Approved Budget:</span>
                                            <input type="number" id="total_approved_combined" name="total_approved_combined" value="" class="form-control form-control-sm text-center fw-bold" style="border-bottom: 2px solid #000;"  readonly/>
                                        </div>
                                        <div class="col-sm-6">
                                            <span class="fw-bolder">Total Proposed Realignment:</span>
                                            <input type="number" id="total_proposed_combined" name="total_proposed_combined" value="" class="form-control form-control-sm text-center fw-bold" style="border-bottom: 2px solid #000;"  readonly/>
                                        </div>
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
                        <th>Transaction No.</th>
                        <th>Project Title</th>
                        <th>Responsibility Code</th>
                        <th>Encode Date</th>
                        <th>Budget</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody class="align-middle">
                    <?php if(!empty($budgetdtdata)):
                        
                        foreach ($budgetdtdata as $data):
                            $dt_recid = $data['recid'];
                            $hdtrxno = $data['trxno'];
                            $project_title = $data['project_title'];
                            $responsibility_code = $data['responsibility_code'];
                            $fund_cluster_code = $data['fund_cluster_code'];
                            $division_name = $data['division_name'];
                            $approved_budget = $data['approved_budget'];
                            $is_pending = $data['is_pending'];
                            $is_approved = $data['is_approved'];
                            $is_disapproved = $data['is_disapproved'];
                            $added_at = $data['added_at'];

                            if ($is_approved == '1' && $is_disapproved == '0' && $is_pending == '0') {
                                $status = "APPROVED";
                                $color = "success";
                            }elseif ($is_approved == '0' && $is_disapproved == '1' && $is_pending == '0') {
                                $status = "DISAPPROVED";
                                $color = "danger";
                            }else{
                                $status = "PENDING";
                                $color = "info";
                            }

                    ?>
                    <tr>
                        <td class="text-center align-middle">
                            <div class="d-flex justify-content-center gap-2">
                                <a class="text-info nav-icon-hover fs-6" title="Edit Transaction" href="mybudgetallotment?meaction=MAIN&recid=<?= $dt_recid ?>">
                                    <i class="ti ti-edit"></i>
                                </a>
                                <button class="btn btn-sm fs-6 text-warning p-0 border-0 bg-transparent"  onclick="__mysys_budget_allotment_ent.__showPdfInModal('<?= base_url('mybudgetallotment?meaction=PRINT-LIB&recid='.$dt_recid) ?>')">
                                    <i class="ti ti-printer"></i>
                                </button>
                            </div>
                        </td>
                        <td class="text-center"><?=$hdtrxno;?></td>
                        <td class="text-center"><?=$project_title;?></td>
                        <td class="text-center"><?=$responsibility_code;?></td>
                        <td class="text-center"><?=$added_at;?></td>
                        <td class="text-center"><?= 'P'. number_format($approved_budget,2);?></td>
                        <td class="text-center text-<?=$color;?>"><?=$status;?></td>
                    </tr>
                    <?php endforeach; endif;?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="card">
        <div class="card-header bg-info p-1">
            <div class="row">
                <div class="col-sm-6 d-flex align-items-center text-start">
                    <h6 class="mb-0 lh-base px-3 text-white fw-semibold d-flex align-items-center">
                        <i class="ti ti-files fs-5 me-1"></i>
                        <span class="pt-1">Project Attachments</span>
                    </h6>
                </div>
            </div>
		</div>						
        <div class="card-body p-0 px-4 py-2 my-2">
            <form id="uploadForm" action="<?=site_url();?>mybudgetallotment" method="post" enctype="multipart/form-data">
            <div class="row">
                <div class="col-sm-6 mb-2">
                    <label for="formFileSm" class="form-label">Uploading:</label>
                    <div class="d-flex gap-2">
                        <input class="form-control form-control-sm" name="userfile" type="file" />
                        <input type="hidden" name="hd_trxno" value="<?=$trxno;?>">
                        <input type="hidden" name="meaction"  value="MAIN-UPLOAD">
                        
                        <?php if(!empty($recid)):?>
                            <button type="submit" class="btn btn-sm btn-primary">Upload</button>
                        <?php else:?>
                            <button type="submit" class="btn btn-sm btn-primary" disabled>Upload</button>
                        <?php endif;?>
                    </div>
                </div>
                
                <div class="col-sm-12">
                    <label for="formFileSm" class="form-label">List of uploaded files:</label>
                    <table class="table table-bordered table-striped table-hover">
                        <thead>
                            <tr>
                                <th class="text-center">No.</th>
                                <th class="text-center">File Name</th>
                            </tr>
                        </thead>
                        <tbody class="align-middle">
                            <?php if(!empty($trxno)):
                                $query = $this->db->query("
                                SELECT
                                    `recid`,
                                    `file_name`
                                FROM
                                    `tbl_budget_attachments`
                                WHERE 
                                    `trxno` = '$trxno'"
                                );
                                $result = $query->getResultArray();
                                foreach ($result as $data):
                                    $recid = $data['recid'];
                                    $file_name = $data['file_name'];
                            ?>
                            <tr>
                                <td class="text-center"><?=$counter++;?></td>
                                <td class="text-center">
                                    <a href="<?= base_url('uploads/' . urlencode($file_name)) ?>" target="_blank">
                                        <?=$file_name;?>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; endif;?>
                        </tbody>
                    </table>
                </div>
            </div>
            </form>
        </div>
    </div>

</div>
<!-- Delete Confirmation Modal -->
<div class="modal fade" id="confirmDeleteModal" tabindex="-1" aria-labelledby="confirmDeleteModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-info text-white">
        <h5 class="modal-title text-white" id="confirmDeleteModalLabel">Confirm Delete</h5>
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
                        <?php if($action == 'appr_approved' && !empty($recid)):?>
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
                    <?php if($action == 'appr_approved' && !empty($recid)):?>
                        <textarea name="approved_remarks" id="approved_remarks" placeholder="" rows="3" class="form-control form-control-sm" disabled><?=$remarks;?></textarea>
                    <?php else:?>
                        <textarea name="approved_remarks" id="approved_remarks" placeholder="" rows="3" class="form-control form-control-sm"></textarea>
                    <?php endif;?>
                    </div>
                </div>
            </div>
        </div>
        
      </div>
      <div class="modal-footer">
        <?php if($action == 'appr_approved' && !empty($recid)):?>
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
                        <?php if($action == 'appr_disapproved' && !empty($recid)):?>
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
                        <?php if($action == 'appr_disapproved' && !empty($recid)):?>
                            <textarea name="disapproved_remarks" id="disapproved_remarks" placeholder="" rows="3" class="form-control form-control-sm" disabled><?=$remarks;?></textarea>
                        <?php else:?>
                            <textarea name="disapproved_remarks" id="disapproved_remarks" placeholder="" rows="3" class="form-control form-control-sm"></textarea>
                        <?php endif;?>
                    </div>
                </div>
            </div>
        </div>
        
      </div>
      <div class="modal-footer">
        <?php if($action == 'appr_disapproved' && !empty($recid)):?>
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
<script src="<?=base_url('assets/js/budget/mybudgetallotment.js?v=8');?>"></script>
<script src="<?=base_url('assets/js/mysysapps.js');?>"></script>

<!-- Bootstrap JS (and Popper.js) -->
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
<?php
	echo $MDL_jsscript;
?>
<script>
    __mysys_budget_allotment_ent.__budget_saving();
    __mysys_budget_allotment_ent.__direct_ps_totals();
    __mysys_budget_allotment_ent.__indirect_ps_totals();
    __mysys_budget_allotment_ent.__direct_mooe_totals();
    __mysys_budget_allotment_ent.__indirect_mooe_totals();
    __mysys_budget_allotment_ent.__direct_co_totals();
    __mysys_budget_allotment_ent.__indirect_co_totals();
    __mysys_budget_allotment_ent.__combined_totals();

    $(document).ready(function () {
        $('#datatablesSimple').DataTable({
            pageLength: 5,
            lengthChange: false,
            order: [[4, 'desc']],
            language: {
            search: "Search:"
            }
        });

        $('.r1_approved_budget').prop('disabled', true);
        $('.r2_approved_budget').prop('disabled', true);
        $('.r3_approved_budget').prop('disabled', true);

        // Toggle all based on the checkbox
        $('#is_realign1').on('change', function () {
            const isChecked = $(this).is(':checked');
            $('.r1_approved_budget').prop('disabled', !isChecked);
       
        });
        $('#is_realign2').on('change', function () {
            const isChecked = $(this).is(':checked');
            $('.r2_approved_budget').prop('disabled', !isChecked);
            $('.r1_approved_budget').prop('disabled', isChecked);
        });
        $('#is_realign3').on('change', function () {
            const isChecked = $(this).is(':checked');
            $('.r3_approved_budget').prop('disabled', !isChecked);
            $('.r2_approved_budget').prop('disabled', isChecked);
        });

        function computeDuration() {
            const from = document.getElementById("duration_from").value;
            const to = document.getElementById("duration_to").value;
            const extendedTo = document.getElementById("extended_to")?.value;

            if (!from || (!to && !extendedTo)) {
                document.getElementById("project_duration").value = '';
                return;
            }

            const fromDate = new Date(from);
            let endDate = new Date(to); // default to original

            if (extendedTo) {
                const extDate = new Date(extendedTo);
                if (!isNaN(extDate) && extDate > fromDate) {
                    endDate = extDate; // override with extended if valid
                }
            }

            if (endDate < fromDate) {
                document.getElementById("project_duration").value = 'Invalid range';
                return;
            }

            // âœ… Add 1 day to endDate to include the last day
            endDate.setDate(endDate.getDate() + 1);

            let years = endDate.getFullYear() - fromDate.getFullYear();
            let months = endDate.getMonth() - fromDate.getMonth();

            if (months < 0) {
                years--;
                months += 12;
            }

            let result = '';
            if (years > 0) result += years + (years === 1 ? ' year' : ' years');
            if (months > 0) {
                if (result !== '') result += ' and ';
                result += months + (months === 1 ? ' month' : ' months');
            }

            if (result === '') result = '0 months';

            document.getElementById("project_duration").value = result;
        }

        document.getElementById("duration_from").addEventListener("change", computeDuration);
        document.getElementById("duration_to").addEventListener("change", computeDuration);
        document.getElementById("extended_from").addEventListener("change", computeDuration);
        document.getElementById("extended_to").addEventListener("change", computeDuration);

        // Trigger on load
        computeDuration();

    
    });


    $(document).on('change', '#selProjectTitle', function() {
        var selected = $(this).find('option:selected');

        // Extract data from selected option
        var fund = selected.data('fund') || '';
        var division = selected.data('division') || '';
        var responsibility = selected.data('responsibility') || '';

        // Set the values into inputs
        $('#fund_cluster_code').val(fund);
        $('#division_name').val(division);
        $('#responsibility_code').val(responsibility);
    });

   document.addEventListener("DOMContentLoaded", function() {
        var checkbox = document.getElementById("with_extension");
        __mysys_budget_allotment_ent.__toggleExtensionFields(checkbox);
    });



</script>

<!-- PROJECT TITLE LOOKUP -->
<script>
<?php
$projects = [];
foreach ($projectdata as $data) {
    $projects[$data['project_title']] = [
        'rc'       => $data['responsibility_code'],
        'division' => $data['division_name'],
        'fund'     => $data['fund_cluster_code'],
        'leader'     => $data['project_leader']
    ];
}
?>
const projects = <?= json_encode($projects); ?>;
const project_titles = Object.keys(projects);

$(function () {

  $('.project_title').autocomplete({
    source: project_titles,
    minLength: 1,
    select: function (event, ui) {

      const selected = projects[ui.item.value];

      if (!selected) return;

      $('#responsibility_code').val(selected.rc ?? '');
      $('#division_name').val(selected.division ?? '');
      $('#fund_cluster_code').val(selected.fund ?? '');
      $('#project_leader').val(selected.leader ?? '');

    }
  });

});
</script>


<!-- PSUACS TITLE LOOKUP -->
 <?php
$psuacs = [];
foreach ($psuacsdata as $data) {
    $psuacs[] = [
        'label'     => $data['sub_object_code'], // shown in dropdown
        'value'     => $data['sub_object_code'], // filled in input
        'uacs_code' => $data['uacs_code'],        // mapped value
    ];
}
?>
<script>
var psuacs = <?= json_encode($psuacs); ?>;

$(document).on("focus", ".psuacs_code", function () {

  if (!$(this).data("ui-autocomplete")) {

    $(this).autocomplete({
      source: psuacs,
      select: function (event, ui) {

        let row = $(this).closest('tr');

        // Set selected values
        $(this).val(ui.item.value);
        row.find('.uacs').val(ui.item.uacs_code);

        return false; // stop default behavior
      }
    });

  }
});
</script>


<!-- MOOEUACS TITLE LOOKUP -->
 <?php
$mooeuacs = [];
foreach ($mooeuacsdata as $data) {
    $mooeuacs[] = [
        'label'     => $data['sub_object_code'], // what user sees
        'value'     => $data['sub_object_code'], // what goes in input
        'uacs_code' => $data['uacs_code'],        // extra data
    ];
}
?>

<script>
var mooeuacs = <?= json_encode($mooeuacs); ?>;

$(document).on("focus", ".mooeuacs_code", function () {

  if (!$(this).data("ui-autocomplete")) {

    $(this).autocomplete({
      source: mooeuacs,
      select: function (event, ui) {

        let row = $(this).closest('tr');

        // Set selected values
        $(this).val(ui.item.value);
        row.find('.uacs').val(ui.item.uacs_code);

        return false; // prevent default replace
      }
    });

  }
});
</script>

<!-- PROGRAM TITLE LOOKUP -->
<script>
<?php
    $programs = [
        'General Administration and Support Service',
        'Scientific Research and Development Services on Basic and Applied Researches on Food and Nutrition',
        "Expanding the FNRI's Nutrigenomics Laboratory: Towards Establishment of a World Class Philippines Nutrigenomics Center",
        'Nutritional Assessment and Monitoring on Food and Nutrition',
        'Expanded National Nutrition Survey',
        'Technical Services on Food and Nutrition'
    ];
?>
var programs = <?php echo json_encode($programs); ?>;
$(function() {
  // Initialize autocomplete for all existing textareas
  $("#program_title").autocomplete({
    source: programs
  });

  // Re-initialize whenever a new row is added dynamically
  $(document).on("focus", "#program_title", function () {
    if (!$(this).data("uiAutocomplete")) {
      $(this).autocomplete({
        source: programs
      });
    }
  });
});
</script>
<?php
    echo view('templates/myfooter.php');
?>


