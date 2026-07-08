<?php
$this->request = \Config\Services::request();
$this->mybudgetallotment = model('App\Models\MyBudgetAllotmentModel');
$this->db = \Config\Database::connect();
$recid = $this->request->getPostGet('recid');
$reuse_recid = $this->request->getPostGet('reuse_recid');

$program_title = '';
$project_title = '';
$department = '';
$trxno = '';
$agency = '';
$current_year = '';
$is_jan = 0;
$is_feb = 0;
$is_mar = 0;
$is_apr = 0;
$is_may = 0;
$is_jun = 0;
$is_jul = 0;
$is_aug = 0;
$is_sep = 0;
$is_oct = 0;
$is_nov = 0;
$is_dec = 0;
$todate_realignment = 0.00;
if(!empty($recid) || !is_null($recid)) { 

    $query = $this->db->query("
    SELECT
        `trxno`,
        `program_title`,
        `project_title`,
        `department`,
        `agency`,
        `current_year`,
        `is_jan`,
        `is_feb`,
        `is_mar`,
        `is_apr`,
        `is_may`,
        `is_jun`,
        `is_jul`,
        `is_aug`,
        `is_sep`,
        `is_oct`,
        `is_nov`,
        `is_dec`
    FROM
        `tbl_saob_hd`
    WHERE 
        `recid` = '$recid'"
    );

    $data = $query->getRowArray();
    $trxno = $data['trxno'];
    $program_title = $data['program_title'];
    $project_title = $data['project_title'];
    $department = $data['department'];
    $agency = $data['agency'];
    $current_year = $data['current_year'];
    $is_jan = $data['is_jan'];
    $is_feb = $data['is_feb'];
    $is_mar = $data['is_mar'];
    $is_apr = $data['is_apr'];
    $is_may = $data['is_may'];
    $is_jun = $data['is_jun'];
    $is_jul = $data['is_jul'];
    $is_aug = $data['is_aug'];
    $is_sep = $data['is_sep'];
    $is_oct = $data['is_oct'];
    $is_nov = $data['is_nov'];
    $is_dec = $data['is_dec'];
}

if(!empty($reuse_recid) || !is_null($reuse_recid)) { 

    $query = $this->db->query("
    SELECT
        `trxno`,
        `program_title`,
        `project_title`,
        `department`,
        `agency`,
        `current_year`,
        `is_jan`,
        `is_feb`,
        `is_mar`,
        `is_apr`,
        `is_may`,
        `is_jun`,
        `is_jul`,
        `is_aug`,
        `is_sep`,
        `is_oct`,
        `is_nov`,
        `is_dec`
    FROM
        `tbl_saob_hd`
    WHERE 
        `recid` = '$reuse_recid'"
    );

    $data = $query->getRowArray();
    $program_title = $data['program_title'];
    $project_title = $data['project_title'];
    $department = $data['department'];
    $agency = $data['agency'];
}

echo view('templates/myheader.php');
?>
<head>
<style>
#datatablesSimple td {
    white-space: normal !important;
    word-wrap: break-word;
    word-break: break-word;
}
#datatablesSimple thead th {
        text-align: center;
    }

</style>

</head>
<div class="container-fluid">
    <input type="hidden" id="__siteurl" data-mesiteurl="<?=site_url();?>" />
    <div class="row mb-2 mt-0">
        <h4 class="fw-semibold mb-8">SAOB</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a class="text-muted text-decoration-none" href="<?=site_url();?>"><i class="ti ti-home fs-5"></i></a>
            </li>
            <li class="breadcrumb-item" aria-current="page">Reports</li>
            <li class="breadcrumb-item" aria-current="page"><span class="form-label fw-bold">SAOB</span></li>
            </ol>
        </nav>
    </div>
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="row mysaob-outp-msg mx-0">

                </div>
                <div class="card-header bg-info p-1">
                    <div class="row">
                        <div class="col-sm-6 d-flex align-items-center text-start">
                            <h6 class="mb-0 lh-base px-3 text-white fw-semibold d-flex align-items-center">
                                <i class="ti ti-pencil fs-5 me-1"></i>
                                <span class="pt-1">Entry</span>
                            </h6>
                        </div>
                        <div class="col-sm-6 text-end ">

                        </div>
                    </div>
                </div>						
                <div class="card-body p-0 px-4 py-2 my-2">
                    <form action="<?=site_url();?>mysaobrpt?meaction=MAIN-SAVE" method="post" class="mysaob-validation">
                        <div class="row mb-2">
                            <div class="col-sm-12">
                                <div class="row mb-2">
                                    <div class="col-sm-2">
                                        <span class="fw-bold">Program Title:</span>
                                    </div>
                                    <div class="col-sm-10">
                                        <input type="text" id="program_title" name="program_title" value="<?=$program_title;?>" class="form-control form-control-sm"/>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-12">
                                <div class="row mb-2">
                                    <div class="col-sm-2">
                                        <span class="fw-bold">Project Title:</span>
                                    </div>
                                    <div class="col-sm-10">
                                        <input type="text" id="project_title" name="project_title" value="<?=$project_title;?>" class="form-control form-control-sm"/>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="row mb-2">
                                    <div class="col-sm-4">
                                        <span class="fw-bold">Department:</span>
                                    </div>
                                    <div class="col-sm-8">
                                        <input type="hidden" id="recid" name="recid" value="<?=$recid;?>" class="form-control form-control-sm"/>
                                        <input type="hidden" id="is_jan_val" name="is_jan_val" value="<?=$is_jan;?>" class="form-control form-control-sm"/>
                                        <input type="hidden" id="trxno" name="trxno" value="<?=$trxno;?>" class="form-control form-control-sm"/>
                                        <input type="text" id="department" name="department" value="<?=$department;?>" class="form-control form-control-sm"/>
                                    </div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-sm-4">
                                        <span class="fw-bold">Agency:</span>
                                    </div>
                                    <div class="col-sm-8">
                                        <input type="text" id="agency" name="agency" value="<?=$agency;?>" class="form-control form-control-sm"/>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="row mb-2">
                                    <div class="col-sm-4">
                                        <span class="fw-bold">Year:</span>
                                    </div>
                                    <div class="col-sm-8">
                                        <?php if(!empty($recid)):?>
                                            <select name="current_year" id="current_year" class="form-select form-select-sm">
                                                <option value="<?=$current_year;?>"><?=$current_year;?></option>
                                                <option value="2026">2026</option>
                                                <option value="2025">2025</option>
                                            </select>
                                        <?php else:?>
                                            <select name="current_year" id="current_year" class="form-select form-select-sm">
                                                <option value="">-- Select Year --</option>
                                                <option value="2026">2026</option>
                                                <option value="2025">2025</option>
                                            </select>
                                        <?php endif;?>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-sm-4">
                                        <span class="fw-bold">Revision:</span>
                                    </div>
                                    <div class="col-sm-8">
                                        <div class="d-flex gap-3">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" id="is_jan" <?= $is_jan == '1' ? 'checked disabled': '';?>>
                                                <label class="form-check-label" for="is_jan">Jan</label>
                                            </div>
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" id="is_feb" <?= $is_feb == '1' ? 'checked disabled': '';?>>
                                                <label class="form-check-label" for="is_feb">Feb</label>
                                            </div>
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" id="is_mar" <?= $is_mar == '1' ? 'checked disabled': '';?>>
                                                <label class="form-check-label" for="is_mar">Mar</label>
                                            </div>
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" id="is_apr" <?= $is_apr == '1' ? 'checked disabled': '';?>>
                                                <label class="form-check-label" for="is_apr">Apr</label>
                                            </div>
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" id="is_may" <?= $is_may == '1' ? 'checked disabled': '';?>>
                                                <label class="form-check-label" for="is_may">May</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-sm-4">
                                        <span class="fw-bold"></span>
                                    </div>
                                    <div class="col-sm-8">
                                        <div class="d-flex gap-3">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" id="is_jun" <?= $is_jun == '1' ? 'checked disabled': '';?>>
                                                <label class="form-check-label" for="is_jun">Jun</label>
                                            </div>
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" id="is_jul" <?= $is_jul == '1' ? 'checked disabled': '';?>>
                                                <label class="form-check-label" for="is_jul">Jul</label>
                                            </div>
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" id="is_aug" <?= $is_aug == '1' ? 'checked disabled': '';?>>
                                                <label class="form-check-label" for="is_aug">Aug</label>
                                            </div>
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" id="is_sep" <?= $is_sep == '1' ? 'checked disabled': '';?>>
                                                <label class="form-check-label" for="is_sep">Sep</label>
                                            </div>
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" id="is_oct" <?= $is_oct == '1' ? 'checked disabled': '';?>>
                                                <label class="form-check-label" for="is_oct">Oct</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-sm-4">
                                        <span class="fw-bold"></span>
                                    </div>
                                    <div class="col-sm-8">
                                        <div class="d-flex gap-3">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" id="is_nov" <?= $is_nov == '1' ? 'checked': '';?>>
                                                <label class="form-check-label" for="is_nov">Nov</label>
                                            </div>
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" id="is_dec" <?= $is_dec == '1' ? 'checked': '';?>>
                                                <label class="form-check-label" for="is_dec">Dec</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <hr>
                            <div class="col-sm-12">
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
                                </ul>
                                <div class="tab-content border mb-0">
                                    <!-- PS TAB CONTENT -->
                                    <div class="tab-pane active p-3 pb-0" id="ps-pill" role="tabpanel">
                                        <div class="row mb-2">
                                            <div class="table-responsive pe-2 ps-0">
                                                <div class="col-md-12 mb-2">
                                                    <table id="budget_line_items" class="table-sm table-striped budgetdata-list">
                                                        <thead>
                                                            <th class="text-center">
                                                                <a class="text-info px-2 fs-7 bg-hover-primary nav-icon-hover position-relative z-index-5" id="btn_trxjournalitem_add" href="javascript:__mysys_saob_rpt_ent.my_add_budget_line();"><i class="ti ti-new-section"></i></a>
                                                            </th>
                                                            <th class="text-center align-middle">Object Code</th>
                                                            <th class="text-center align-middle">Particulars</th>
                                                            <th class="text-center align-middle">UACS.</th>
                                                            <th class="text-center align-middle">Approved Budget</th>
                                                            <th class="text-center align-middle">Todate Realignment</th>
                                                            <th class="text-center align-middle">Proposed Realignment</th>
                                                            <th class="text-center align-middle">Revised Allotment</th>
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
                                                                        onclick="__mysys_saob_rpt_ent.my_add_budget_line_above(this);">
                                                                            <i class="ti ti-plus"></i>
                                                                        </a>
                                                                    </div>
                                                                </td>
                                                                <td class="text-center align-middle" nowrap>
                                                                    <select name="selObject" class="selObject form" style="width:300px; height:30px;">
                                                                        <option selected value ="">Choose...</option>
                                                                        <?php foreach($psobjectdata as $data){
                                                                            $object_code = $data['object_code'];
                                                                        ?>
                                                                            <option value="<?=$object_code?>"><?=$object_code?></option>
                                                                        <?php }?>
                                                                    </select>
                                                                </td>
                                                                <td class="text-center align-middle" nowrap>
                                                                    <select name="selUacs" class="selUacs form" style="width:300px; height:30px;">
                                                                        <option selected value ="">Choose...</option>
                                                                        <?php foreach($psuacsdata as $data){
                                                                            $sub_object_code = $data['sub_object_code'];
                                                                            $uacs_code = $data['uacs_code'];
                                                                        ?>
                                                                            <option value="<?=$sub_object_code?>" data-uacs="<?=$uacs_code;?>"><?=$sub_object_code?></option>
                                                                        <?php }?>
                                                                    </select>
                                                                </td>
                                                                <td class="text-center align-middle" nowrap>
                                                                    <input type="text" id="uacs"  value="" size="25"  name="uacs" class="uacs text-center" disabled>
                                                                </td>
                                                                <td class="text-center align-middle" nowrap>
                                                                    <input type="number" id="approved_budget"  value="" size="25" step="any" <?= empty($recid) ? '' : ($is_jan == '0' ? '' : 'disabled') ?>  name="approved_budget" data-dtid=""  class="approved_budget text-center" onchange="__mysys_saob_rpt_ent.__direct_ps_totals(); __mysys_saob_rpt_ent.__combined_totals();" onmouseout="__mysys_saob_rpt_ent.__direct_ps_totals(); __mysys_saob_rpt_ent.__combined_totals();" />
                                                                </td>
                                                                <td class="text-center align-middle" nowrap>
                                                                    <input type="number" id="todate_realignment"  value="" size="25" step="any" disabled  name="todate_realignment" data-dtid=""  disabled class="todate_realignment text-center" onchange="__mysys_saob_rpt_ent.__direct_ps_totals(); __mysys_saob_rpt_ent.__combined_totals();" onmouseout="__mysys_saob_rpt_ent.__direct_ps_totals(); __mysys_saob_rpt_ent.__combined_totals();" />
                                                                </td>
                                                                <td class="text-center align-middle" nowrap>
                                                                    <input type="number" id="realignment"  value="" size="25" step="any" name="realignment" data-dtid="" class="realignment text-center" onchange="__mysys_saob_rpt_ent.__direct_ps_totals(); __mysys_saob_rpt_ent.__combined_totals();" onmouseout="__mysys_saob_rpt_ent.__direct_ps_totals(); __mysys_saob_rpt_ent.__combined_totals();" />
                                                                </td>
                                                                <td class="text-center align-middle" nowrap>
                                                                    <input type="number" id="revised_allotment"  value="" size="25" step="any" name="revised_allotment" data-dtid="" class="revised_allotment text-center" disabled onchange="__mysys_saob_rpt_ent.__direct_ps_totals(); __mysys_saob_rpt_ent.__combined_totals();" onmouseout="__mysys_saob_rpt_ent.__direct_ps_totals(); __mysys_saob_rpt_ent.__combined_totals();"/>
                                                                </td>
                                                            </tr>
                                                            <?php if(!empty($recid)):
                                                                $query = $this->db->query("
                                                                SELECT
                                                                    `recid`,
                                                                    `object_code`,
                                                                    `particulars`,
                                                                    `code`,
                                                                    `approved_budget`,
                                                                    `january_revision`,
                                                                    `february_revision`,
                                                                    `march_revision`,
                                                                    `april_revision`,
                                                                    `may_revision`,
                                                                    `june_revision`,
                                                                    `july_revision`,
                                                                    `august_revision`,
                                                                    `september_revision`,
                                                                    `october_revision`,
                                                                    `november_revision`,
                                                                    `december_revision`
                                                                FROM
                                                                    `tbl_saob_ps_dt`
                                                                WHERE 
                                                                    `project_id` = '$recid'"
                                                                );
                                                                $result = $query->getResultArray();
                                                                foreach ($result as $data):
                                                                    $dt_id = $data['recid'];
                                                                    $object_code = $data['object_code'];
                                                                    $particulars = $data['particulars'];
                                                                    $code = $data['code'];
                                                                    $approved_budget = $data['approved_budget'];
                                                                    $january_revision = $data['january_revision'];
                                                                    $february_revision = $data['february_revision'];
                                                                    $march_revision = $data['march_revision'];
                                                                    $april_revision = $data['april_revision'];
                                                                    $may_revision = $data['may_revision'];
                                                                    $june_revision = $data['june_revision'];
                                                                    $july_revision = $data['july_revision'];
                                                                    $august_revision = $data['august_revision'];
                                                                    $september_revision = $data['september_revision'];
                                                                    $october_revision = $data['october_revision'];
                                                                    $november_revision = $data['november_revision'];
                                                                    $december_revision = $data['december_revision'];


                                                                    if ($is_jan == '1' && $is_feb == '0') {
                                                                        $todate_realignment = $january_revision;

                                                                    }elseif ($is_jan == '1' && $is_feb == '1' && $is_mar == '0') {
                                                                        $todate_realignment = $january_revision + $february_revision;

                                                                    }elseif ($is_jan == '1' && $is_feb == '1' && $is_mar == '1' && $is_apr == '0') {
                                                                        $todate_realignment = $january_revision + $february_revision + $march_revision;

                                                                    }elseif ($is_jan == '1' && $is_feb == '1' && $is_mar == '1' && $is_apr == '1' && $is_may == '0') {
                                                                        $todate_realignment = $january_revision + $february_revision + $march_revision + $april_revision;

                                                                    }elseif ($is_jan == '1' && $is_feb == '1' && $is_mar == '1' && $is_apr == '1' && $is_may == '1' && $is_jun == '0') {
                                                                        $todate_realignment = $january_revision + $february_revision + $march_revision + $april_revision + $may_revision;

                                                                    }elseif ($is_jan == '1' && $is_feb == '1' && $is_mar == '1' && $is_apr == '1' && $is_may == '1' && $is_jun == '1' && $is_jul == '0') {
                                                                        $todate_realignment = $january_revision + $february_revision + $march_revision + $april_revision + $may_revision + $june_revision;

                                                                    }elseif ($is_jan == '1' && $is_feb == '1' && $is_mar == '1' && $is_apr == '1' && $is_may == '1' && $is_jun == '1' && $is_jul == '1' && $is_aug == '0') {
                                                                        $todate_realignment = $january_revision + $february_revision + $march_revision + $april_revision + $may_revision + $june_revision + $july_revision;

                                                                    }elseif ($is_jan == '1' && $is_feb == '1' && $is_mar == '1' && $is_apr == '1' && $is_may == '1' && $is_jun == '1' && $is_jul == '1' && $is_aug == '1' && $is_sep == '0') {
                                                                        $todate_realignment = $january_revision + $february_revision + $march_revision + $april_revision + $may_revision + $june_revision + $july_revision + $august_revision;

                                                                    }elseif ($is_jan == '1' && $is_feb == '1' && $is_mar == '1' && $is_apr == '1' && $is_may == '1' && $is_jun == '1' && $is_jul == '1' && $is_aug == '1' && $is_sep == '1' && $is_oct == '0') {
                                                                        $todate_realignment = $january_revision + $february_revision + $march_revision + $april_revision + $may_revision + $june_revision + $july_revision + $august_revision + $september_revision;

                                                                    }elseif ($is_jan == '1' && $is_feb == '1' && $is_mar == '1' && $is_apr == '1' && $is_may == '1' && $is_jun == '1' && $is_jul == '1' && $is_aug == '1' && $is_sep == '1' && $is_oct == '1' && $is_nov == '0') {
                                                                        $todate_realignment = $january_revision + $february_revision + $march_revision + $april_revision + $may_revision + $june_revision + $july_revision + $august_revision + $september_revision + $october_revision;

                                                                    }elseif ($is_jan == '1' && $is_feb == '1' && $is_mar == '1' && $is_apr == '1' && $is_may == '1' && $is_jun == '1' && $is_jul == '1' && $is_aug == '1' && $is_sep == '1' && $is_oct == '1' && $is_nov == '1' && $is_dec == '0') {
                                                                        $todate_realignment = $january_revision + $february_revision + $march_revision + $april_revision + $may_revision + $june_revision + $july_revision + $august_revision + $september_revision + $october_revision + $november_revision;

                                                                    }elseif ($is_jan == '1' && $is_feb == '1' && $is_mar == '1' && $is_apr == '1' && $is_may == '1' && $is_jun == '1' && $is_jul == '1' && $is_aug == '1' && $is_sep == '1' && $is_oct == '1' && $is_nov == '1' && $is_dec == '1') {
                                                                        $todate_realignment = $january_revision + $february_revision + $march_revision + $april_revision + $may_revision + $june_revision + $july_revision + $august_revision + $september_revision + $october_revision + $november_revision + $december_revision;
                                                                    }else{
                                                                        $todate_realignment = 0.00;
                                                                    }

                                                            ?>
                                                            <tr>
                                                                <td class="text-center align-middle">
                                                                    <div class="d-inline-flex gap-1 justify-content-center">
                                                                        <a class="text-danger fs-5 bg-hover-danger nav-icon-hover"
                                                                            href="javascript:void(0)"
                                                                            onclick="">
                                                                            <i class="ti ti-trash"></i>
                                                                        </a>
                                                                        <a class="text-success fs-5 bg-hover-primary nav-icon-hover"
                                                                            href="javascript:void(0)"
                                                                            title="Add rows above"
                                                                            onclick="__mysys_saob_rpt_ent.my_add_budget_line_above(this);">
                                                                            <i class="ti ti-plus"></i>
                                                                        </a>
                                                                    </div>
                                                                </td>
                                                                <td class="text-center align-middle" nowrap>
                                                                    <select name="selObject" class="selObject form" style="width:300px; height:30px;">
                                                                        <option selected value ="<?=$object_code;?>"><?=$object_code;?></option>
                                                                        <?php foreach($psobjectdata as $data){
                                                                            $object_code = $data['object_code'];
                                                                        ?>
                                                                            <option value="<?=$object_code?>"><?=$object_code?></option>
                                                                        <?php }?>
                                                                    </select>
                                                                </td>
                                                                <td class="text-center align-middle" nowrap>
                                                                    <select name="selUacs" class="selUacs form"  style="width:300px; height:30px;">
                                                                        <option selected value ="<?=$particulars;?>"><?=$particulars;?></option>
                                                                        <?php foreach($psuacsdata as $data){
                                                                            $sub_object_code = $data['sub_object_code'];
                                                                            $uacs_code = $data['uacs_code'];
                                                                        ?>
                                                                            <option value="<?=$sub_object_code?>"  data-uacs="<?=$uacs_code;?>"><?=$sub_object_code?></option>
                                                                        <?php }?>
                                                                    </select>
                                                                </td>
                                                                <td class="text-center align-middle" nowrap>
                                                                    <input type="text" id="uacs"  value="<?=$code;?>" size="25"  name="uacs" class="uacs text-center" disabled>
                                                                </td>
                                                                <td class="text-center align-middle" nowrap>
                                                                    <input type="number" id="approved_budget" size="25" value="<?=$approved_budget;?>" step="any" <?= empty($is_jan) ? '' : 'disabled' ;?> data-dtid="<?=$dt_id;?>" name="approved_budget" class="approved_budget text-center" onchange="__mysys_saob_rpt_ent.__direct_ps_totals(); __mysys_saob_rpt_ent.__combined_totals();" onmouseout="__mysys_saob_rpt_ent.__direct_ps_totals(); __mysys_saob_rpt_ent.__combined_totals();"/>
                                                                </td>
                                                                <td class="text-center align-middle" nowrap>
                                                                    <input type="number" id="todate_realignment" disabled value="<?=$todate_realignment;?>" size="25" step="any" name="todate_realignment" data-dtid=""  class="todate_realignment text-center" onchange="__mysys_saob_rpt_ent.__direct_ps_totals(); __mysys_saob_rpt_ent.__combined_totals();" onmouseout="__mysys_saob_rpt_ent.__direct_ps_totals(); __mysys_saob_rpt_ent.__combined_totals();" />
                                                                </td>
                                                                <td class="text-center align-middle" nowrap>
                                                                    <input type="number" id="proposed_realignment"  value="" size="25" step="any" name="proposed_realignment" data-dtid="" class="proposed_realignment text-center" disabled onchange="__mysys_saob_rpt_ent.__direct_ps_totals(); __mysys_saob_rpt_ent.__combined_totals();" onmouseout="__mysys_saob_rpt_ent.__direct_ps_totals(); __mysys_saob_rpt_ent.__combined_totals();" />
                                                                </td>
                                                                <td class="text-center align-middle" nowrap>
                                                                    <input type="number" id="revised_allotment"  value="" size="25" step="any" name="revised_allotment" class="revised_allotment text-center" disabled onchange="__mysys_saob_rpt_ent.__direct_ps_totals(); __mysys_saob_rpt_ent.__combined_totals();" onmouseout="__mysys_saob_rpt_ent.__direct_ps_totals(); __mysys_saob_rpt_ent.__combined_totals();"/>
                                                                    <input type="hidden" id="january_revision"  value="<?=$january_revision;?>" size="25" step="any" name="january_revision" class="january_revision text-center"/>
                                                                    <input type="hidden" id="february_revision"  value="<?=$february_revision;?>" size="25" step="any" name="february_revision" class="february_revision text-center"/>
                                                                    <input type="hidden" id="march_revision"  value="<?=$march_revision;?>" size="25" step="any" name="march_revision" class="march_revision text-center"/>
                                                                    <input type="hidden" id="april_revision"  value="<?=$april_revision;?>" size="25" step="any" name="april_revision" class="april_revision text-center"/>
                                                                    <input type="hidden" id="may_revision"  value="<?=$may_revision;?>" size="25" step="any" name="may_revision" class="may_revision text-center"/>
                                                                    <input type="hidden" id="june_revision"  value="<?=$june_revision;?>" size="25" step="any" name="june_revision" class="june_revision text-center"/>
                                                                    <input type="hidden" id="july_revision"  value="<?=$july_revision;?>" size="25" step="any" name="july_revision" class="july_revision text-center"/>
                                                                    <input type="hidden" id="august_revision"  value="<?=$august_revision;?>" size="25" step="any" name="august_revision" class="august_revision text-center"/>
                                                                    <input type="hidden" id="september_revision"  value="<?=$september_revision;?>" size="25" step="any" name="september_revision" class="september_revision text-center"/>
                                                                    <input type="hidden" id="october_revision"  value="<?=$october_revision;?>" size="25" step="any" name="october_revision" class="october_revision text-center"/>
                                                                    <input type="hidden" id="november_revision"  value="<?=$november_revision;?>" size="25" step="any" name="november_revision" class="november_revision text-center"/>
                                                                    <input type="hidden" id="december_revision"  value="<?=$december_revision;?>" size="25" step="any" name="december_revision" class="december_revision text-center"/>
                                                                </td>
                                                            </tr>
                                                            <?php endforeach; endif;?>
                                                            <?php if(!empty($reuse_recid)):
                                                                $query = $this->db->query("
                                                                SELECT
                                                                    `recid`,
                                                                    `object_code`,
                                                                    `particulars`,
                                                                    `code`,
                                                                    `approved_budget`,
                                                                    `january_revision`,
                                                                    `february_revision`,
                                                                    `march_revision`,
                                                                    `april_revision`,
                                                                    `may_revision`,
                                                                    `june_revision`,
                                                                    `july_revision`,
                                                                    `august_revision`,
                                                                    `september_revision`,
                                                                    `october_revision`,
                                                                    `november_revision`,
                                                                    `december_revision`
                                                                FROM
                                                                    `tbl_saob_ps_dt`
                                                                WHERE 
                                                                    `project_id` = '$reuse_recid'"
                                                                );
                                                                $result = $query->getResultArray();
                                                                foreach ($result as $data):
                                                                    $dt_id = $data['recid'];
                                                                    $object_code = $data['object_code'];
                                                                    $particulars = $data['particulars'];
                                                                    $code = $data['code'];
                                                                    $approved_budget = $data['approved_budget'];
                                                                    $january_revision = $data['january_revision'];
                                                                    $february_revision = $data['february_revision'];
                                                                    $march_revision = $data['march_revision'];
                                                                    $april_revision = $data['april_revision'];
                                                                    $may_revision = $data['may_revision'];
                                                                    $june_revision = $data['june_revision'];
                                                                    $july_revision = $data['july_revision'];
                                                                    $august_revision = $data['august_revision'];
                                                                    $september_revision = $data['september_revision'];
                                                                    $october_revision = $data['october_revision'];
                                                                    $november_revision = $data['november_revision'];
                                                                    $december_revision = $data['december_revision'];

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
                                                                            onclick="__mysys_saob_rpt_ent.my_add_budget_line_above(this);">
                                                                            <i class="ti ti-plus"></i>
                                                                        </a>
                                                                    </div>
                                                                </td>
                                                                <td class="text-center align-middle" nowrap>
                                                                    <select name="selObject" class="selObject form" style="width:300px; height:30px;">
                                                                        <option selected value ="<?=$object_code;?>"><?=$object_code;?></option>
                                                                        <?php foreach($psobjectdata as $data){
                                                                            $object_code = $data['object_code'];
                                                                        ?>
                                                                            <option value="<?=$object_code?>"><?=$object_code?></option>
                                                                        <?php }?>
                                                                    </select>
                                                                </td>
                                                                <td class="text-center align-middle" nowrap>
                                                                    <select name="selUacs" class="selUacs form"  style="width:300px; height:30px;">
                                                                        <option selected value ="<?=$particulars;?>"><?=$particulars;?></option>
                                                                        <?php foreach($psuacsdata as $data){
                                                                            $sub_object_code = $data['sub_object_code'];
                                                                            $uacs_code = $data['uacs_code'];
                                                                        ?>
                                                                            <option value="<?=$sub_object_code?>"  data-uacs="<?=$uacs_code;?>"><?=$sub_object_code?></option>
                                                                        <?php }?>
                                                                    </select>
                                                                </td>
                                                                <td class="text-center align-middle" nowrap>
                                                                    <input type="text" id="uacs"  value="<?=$code;?>" size="25"  name="uacs" class="uacs text-center" disabled>
                                                                </td>
                                                                <td class="text-center align-middle" nowrap>
                                                                    <input type="number" id="approved_budget" size="25" value="" step="any" <?= empty($is_jan) ? '' : 'disabled' ;?> data-dtid="<?=$dt_id;?>" name="approved_budget" class="approved_budget text-center" onchange="__mysys_saob_rpt_ent.__direct_ps_totals(); __mysys_saob_rpt_ent.__combined_totals();" onmouseout="__mysys_saob_rpt_ent.__direct_ps_totals(); __mysys_saob_rpt_ent.__combined_totals();"/>
                                                                </td>
                                                                <td class="text-center align-middle" nowrap>
                                                                    <input type="number" id="todate_realignment" disabled value="" size="25" step="any" name="todate_realignment" data-dtid=""  class="todate_realignment text-center" onchange="__mysys_saob_rpt_ent.__direct_ps_totals(); __mysys_saob_rpt_ent.__combined_totals();" onmouseout="__mysys_saob_rpt_ent.__direct_ps_totals(); __mysys_saob_rpt_ent.__combined_totals();" />
                                                                </td>
                                                                <td class="text-center align-middle" nowrap>
                                                                    <input type="number" id="proposed_realignment"  value="" size="25" step="any" name="proposed_realignment" data-dtid="" class="proposed_realignment text-center" disabled onchange="__mysys_saob_rpt_ent.__direct_ps_totals(); __mysys_saob_rpt_ent.__combined_totals();" onmouseout="__mysys_saob_rpt_ent.__direct_ps_totals(); __mysys_saob_rpt_ent.__combined_totals();" />
                                                                </td>
                                                                <td class="text-center align-middle" nowrap>
                                                                    <input type="number" id="revised_allotment"  value="" size="25" step="any" name="revised_allotment" data-dtid="" class="revised_allotment text-center" disabled/>
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
                                    <div class="tab-pane p-3 pb-0" id="mooe-pill" role="tabpanel">
                                        <div class="row">
                                            <div class="table-responsive pe-2 ps-0">
                                                <div class="col-md-12 mb-2">
                                                    <table id="budget_mooe_line_items" class="table-sm table-striped budgetmooedata-list">
                                                        <thead>
                                                            <th class="text-center">
                                                                <a class="text-info px-2 fs-7 bg-hover-primary nav-icon-hover position-relative z-index-5" id="btn_budgetmooeitem_add" href="javascript:__mysys_saob_rpt_ent.my_add_budget_mooe_line();"><i class="ti ti-new-section"></i></a>
                                                            </th>
                                                            <th class="text-center align-middle">Object Code</th>
                                                            <th class="text-center align-middle">Particulars</th>
                                                            <th class="text-center align-middle">UACS.</th>
                                                            <th class="text-center align-middle">Approved Budget</th>
                                                            <th class="text-center align-middle">Todate Realignment</th>
                                                            <th class="text-center align-middle">Proposed Realignment</th>
                                                            <th class="text-center align-middle">Revised Allotment</th>
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
                                                                        onclick="__mysys_saob_rpt_ent.my_add_budget_mooe_line_above(this);">
                                                                            <i class="ti ti-plus"></i>
                                                                        </a>
                                                                    </div>
                                                                </td>
                                                                <td class="text-center align-middle" nowrap>
                                                                    <select name="selObject" class="selObject form" style="width:300px; height:30px;">
                                                                        <option selected value ="">Choose...</option>
                                                                        <?php foreach($mooeobjectdata as $data){
                                                                            $object_code = $data['object_code'];
                                                                        ?>
                                                                            <option value="<?=$object_code?>"><?=$object_code?></option>
                                                                        <?php }?>
                                                                    </select>
                                                                </td>
                                                                <td class="text-center align-middle" nowrap>
                                                                    <select name="selUacs" class="selUacs form" style="width:300px; height:30px;">
                                                                        <option selected value ="">Choose...</option>
                                                                        <?php foreach($mooeuacsdata as $data){
                                                                            $sub_object_code = $data['sub_object_code'];
                                                                            $uacs_code = $data['uacs_code'];
                                                                        ?>
                                                                            <option value="<?=$sub_object_code?>" data-uacs="<?=$uacs_code;?>"><?=$sub_object_code?></option>
                                                                        <?php }?>
                                                                    </select>
                                                                </td>
                                                                <td class="text-center align-middle" nowrap>
                                                                    <input type="text" id="uacs"  value="" size="25"  name="uacs" class="uacs text-center" disabled>
                                                                </td>
                                                                <td class="text-center align-middle" nowrap>
                                                                    <input type="number" id="approved_budget"  value="" <?= empty($recid) ? '' : ($is_jan == '0' ? '' : 'disabled') ?>  size="25" step="any" name="approved_budget" data-dtid="" class="approved_budget text-center" onchange="__mysys_saob_rpt_ent.__direct_mooe_totals(); __mysys_saob_rpt_ent.__combined_totals();" onmouseout="__mysys_saob_rpt_ent.__direct_mooe_totals(); __mysys_saob_rpt_ent.__combined_totals();"/>
                                                                </td>
                                                                <td class="text-center align-middle" nowrap>
                                                                    <input type="number" id="todate_realignment" disabled  value="" size="25" step="any" name="todate_realignment" data-dtid="" class="todate_realignment text-center" onchange="__mysys_saob_rpt_ent.__direct_mooe_totals(); __mysys_saob_rpt_ent.__combined_totals();" onmouseout="__mysys_saob_rpt_ent.__direct_mooe_totals(); __mysys_saob_rpt_ent.__combined_totals();"/>
                                                                </td>
                                                                <td class="text-center align-middle" nowrap>
                                                                    <input type="number" id="proposed_realignment"  value="" size="25" step="any" name="proposed_realignment" data-dtid="" class="proposed_realignment text-center" onchange="__mysys_saob_rpt_ent.__direct_mooe_totals(); __mysys_saob_rpt_ent.__combined_totals();" onmouseout="__mysys_saob_rpt_ent.__direct_mooe_totals(); __mysys_saob_rpt_ent.__combined_totals();" />
                                                                </td>
                                                                <td class="text-center align-middle" nowrap>
                                                                    <input type="number" id="revised_allotment"  value="" size="25" step="any" name="revised_allotment" data-dtid="" class="revised_allotment text-center" disabled/>
                                                                </td>
                                                            </tr>
                                                            <?php if(!empty($recid)):
                                                                $query = $this->db->query("
                                                                SELECT
                                                                    `recid`,
                                                                    `object_code`,
                                                                    `particulars`,
                                                                    `code`,
                                                                    `approved_budget`,
                                                                    `january_revision`,
                                                                    `february_revision`,
                                                                    `march_revision`,
                                                                    `april_revision`,
                                                                    `may_revision`,
                                                                    `june_revision`,
                                                                    `july_revision`,
                                                                    `august_revision`,
                                                                    `september_revision`,
                                                                    `october_revision`,
                                                                    `november_revision`,
                                                                    `december_revision`
                                                                FROM
                                                                    `tbl_saob_mooe_dt`
                                                                WHERE 
                                                                    `project_id` = '$recid'
                                                                ORDER BY recid"
                                                                
                                                                );
                                                                $result = $query->getResultArray();
                                                                foreach ($result as $data):
                                                                    $dt_id = $data['recid'];
                                                                    $object_code = $data['object_code'];
                                                                    $particulars = $data['particulars'];
                                                                    $code = $data['code'];
                                                                    $approved_budget = $data['approved_budget'];
                                                                    $january_revision = $data['january_revision'];
                                                                    $february_revision = $data['february_revision'];
                                                                    $march_revision = $data['march_revision'];
                                                                    $april_revision = $data['april_revision'];
                                                                    $may_revision = $data['may_revision'];
                                                                    $june_revision = $data['june_revision'];
                                                                    $july_revision = $data['july_revision'];
                                                                    $august_revision = $data['august_revision'];
                                                                    $september_revision = $data['september_revision'];
                                                                    $october_revision = $data['october_revision'];
                                                                    $november_revision = $data['november_revision'];
                                                                    $december_revision = $data['december_revision'];

                                                                    if ($is_jan == '1' && $is_feb == '0') {
                                                                        $todate_realignment = $january_revision;

                                                                    }elseif ($is_jan == '1' && $is_feb == '1' && $is_mar == '0') {
                                                                        $todate_realignment = $january_revision + $february_revision;

                                                                    }elseif ($is_jan == '1' && $is_feb == '1' && $is_mar == '1' && $is_apr == '0') {
                                                                        $todate_realignment = $january_revision + $february_revision + $march_revision;

                                                                    }elseif ($is_jan == '1' && $is_feb == '1' && $is_mar == '1' && $is_apr == '1' && $is_may == '0') {
                                                                        $todate_realignment = $january_revision + $february_revision + $march_revision + $april_revision;

                                                                    }elseif ($is_jan == '1' && $is_feb == '1' && $is_mar == '1' && $is_apr == '1' && $is_may == '1' && $is_jun == '0') {
                                                                        $todate_realignment = $january_revision + $february_revision + $march_revision + $april_revision + $may_revision;

                                                                    }elseif ($is_jan == '1' && $is_feb == '1' && $is_mar == '1' && $is_apr == '1' && $is_may == '1' && $is_jun == '1' && $is_jul == '0') {
                                                                        $todate_realignment = $january_revision + $february_revision + $march_revision + $april_revision + $may_revision + $june_revision;

                                                                    }elseif ($is_jan == '1' && $is_feb == '1' && $is_mar == '1' && $is_apr == '1' && $is_may == '1' && $is_jun == '1' && $is_jul == '1' && $is_aug == '0') {
                                                                        $todate_realignment = $january_revision + $february_revision + $march_revision + $april_revision + $may_revision + $june_revision + $july_revision;

                                                                    }elseif ($is_jan == '1' && $is_feb == '1' && $is_mar == '1' && $is_apr == '1' && $is_may == '1' && $is_jun == '1' && $is_jul == '1' && $is_aug == '1' && $is_sep == '0') {
                                                                        $todate_realignment = $january_revision + $february_revision + $march_revision + $april_revision + $may_revision + $june_revision + $july_revision + $august_revision;

                                                                    }elseif ($is_jan == '1' && $is_feb == '1' && $is_mar == '1' && $is_apr == '1' && $is_may == '1' && $is_jun == '1' && $is_jul == '1' && $is_aug == '1' && $is_sep == '1' && $is_oct == '0') {
                                                                        $todate_realignment = $january_revision + $february_revision + $march_revision + $april_revision + $may_revision + $june_revision + $july_revision + $august_revision + $september_revision;

                                                                    }elseif ($is_jan == '1' && $is_feb == '1' && $is_mar == '1' && $is_apr == '1' && $is_may == '1' && $is_jun == '1' && $is_jul == '1' && $is_aug == '1' && $is_sep == '1' && $is_oct == '1' && $is_nov == '0') {
                                                                        $todate_realignment = $january_revision + $february_revision + $march_revision + $april_revision + $may_revision + $june_revision + $july_revision + $august_revision + $september_revision + $october_revision;

                                                                    }elseif ($is_jan == '1' && $is_feb == '1' && $is_mar == '1' && $is_apr == '1' && $is_may == '1' && $is_jun == '1' && $is_jul == '1' && $is_aug == '1' && $is_sep == '1' && $is_oct == '1' && $is_nov == '1' && $is_dec == '0') {
                                                                        $todate_realignment = $january_revision + $february_revision + $march_revision + $april_revision + $may_revision + $june_revision + $july_revision + $august_revision + $september_revision + $october_revision + $november_revision;

                                                                    }elseif ($is_jan == '1' && $is_feb == '1' && $is_mar == '1' && $is_apr == '1' && $is_may == '1' && $is_jun == '1' && $is_jul == '1' && $is_aug == '1' && $is_sep == '1' && $is_oct == '1' && $is_nov == '1' && $is_dec == '1') {
                                                                        $todate_realignment = $january_revision + $february_revision + $march_revision + $april_revision + $may_revision + $june_revision + $july_revision + $august_revision + $september_revision + $october_revision + $november_revision + $december_revision;
                                                                    }else{
                                                                        $todate_realignment = 0.00;
                                                                    }

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
                                                                        onclick="__mysys_saob_rpt_ent.my_add_budget_mooe_line_above(this);">
                                                                            <i class="ti ti-plus"></i>
                                                                        </a>
                                                                    </div>
                                                                </td>
                                                                <td class="text-center align-middle" nowrap>
                                                                    <select name="selObject" class="selObject form" style="width:300px; height:30px;">
                                                                        <option selected value ="<?=$object_code;?>"><?=$object_code;?></option>
                                                                        <?php foreach($mooeobjectdata as $data){
                                                                            $object_code = $data['object_code'];
                                                                        ?>
                                                                            <option value="<?=$object_code?>"><?=$object_code?></option>
                                                                        <?php }?>
                                                                    </select>
                                                                </td>
                                                                <td class="text-center align-middle" nowrap>
                                                                    <select name="selUacs" class="selUacs form"  style="width:300px; height:30px;">
                                                                        <option selected value ="<?=$particulars;?>"><?=$particulars;?></option>
                                                                        <?php foreach($mooeuacsdata as $data){
                                                                            $sub_object_code = $data['sub_object_code'];
                                                                            $uacs_code = $data['uacs_code'];
                                                                        ?>
                                                                            <option value="<?=$sub_object_code?>"  data-uacs="<?=$uacs_code;?>"><?=$sub_object_code?></option>
                                                                        <?php }?>
                                                                    </select>
                                                                </td>
                                                                <td class="text-center align-middle" nowrap>
                                                                    <input type="text" id="uacs"  value="<?=$code;?>" size="25"  name="uacs" class="uacs text-center" disabled>
                                                                </td>
                                                                <td class="text-center align-middle" nowrap>
                                                                    <input type="number" id="approved_budget"  value="<?=$approved_budget;?>" <?= empty($is_jan) ? '' : 'disabled' ;?> size="25" step="any" name="approved_budget" data-dtid="<?=$dt_id;?>"  class="approved_budget text-center" onchange="__mysys_saob_rpt_ent.__direct_mooe_totals(); __mysys_saob_rpt_ent.__combined_totals();" onmouseout="__mysys_saob_rpt_ent.__direct_mooe_totals(); __mysys_saob_rpt_ent.__combined_totals();"/>
                                                                </td>
                                                                <td class="text-center align-middle" nowrap>
                                                                    <input type="number" id="todate_realignment" disabled  value="<?= $todate_realignment ?>" size="25" step="any" name="todate_realignment" data-dtid="" class="todate_realignment text-center" onchange="__mysys_saob_rpt_ent.__direct_mooe_totals(); __mysys_saob_rpt_ent.__combined_totals();" onmouseout="__mysys_saob_rpt_ent.__direct_mooe_totals(); __mysys_saob_rpt_ent.__combined_totals();"/>
                                                                </td>
                                                                <td class="text-center align-middle" nowrap>
                                                                    <input type="number" id="proposed_realignment"  value="" size="25" step="any" name="proposed_realignment" data-dtid="" disabled class="proposed_realignment text-center" onchange="__mysys_saob_rpt_ent.__direct_mooe_totals(); __mysys_saob_rpt_ent.__combined_totals();" onmouseout="__mysys_saob_rpt_ent.__direct_mooe_totals(); __mysys_saob_rpt_ent.__combined_totals();" />
                                                                </td>
                                                                <td class="text-center align-middle" nowrap>
                                                                    <input type="number" id="revised_allotment"  value="" size="25" step="any" name="revised_allotment" data-dtid="" class="revised_allotment text-center" disabled/>
                                                                    <input type="hidden" id="january_revision"  value="<?=$january_revision;?>" size="25" step="any" name="january_revision" class="january_revision text-center"/>
                                                                    <input type="hidden" id="february_revision"  value="<?=$february_revision;?>" size="25" step="any" name="february_revision" class="february_revision text-center"/>
                                                                    <input type="hidden" id="march_revision"  value="<?=$march_revision;?>" size="25" step="any" name="march_revision" class="march_revision text-center"/>
                                                                    <input type="hidden" id="april_revision"  value="<?=$april_revision;?>" size="25" step="any" name="april_revision" class="april_revision text-center"/>
                                                                    <input type="hidden" id="may_revision"  value="<?=$may_revision;?>" size="25" step="any" name="may_revision" class="may_revision text-center"/>
                                                                    <input type="hidden" id="june_revision"  value="<?=$june_revision;?>" size="25" step="any" name="june_revision" class="june_revision text-center"/>
                                                                    <input type="hidden" id="july_revision"  value="<?=$july_revision;?>" size="25" step="any" name="july_revision" class="july_revision text-center"/>
                                                                    <input type="hidden" id="august_revision"  value="<?=$august_revision;?>" size="25" step="any" name="august_revision" class="august_revision text-center"/>
                                                                    <input type="hidden" id="september_revision"  value="<?=$september_revision;?>" size="25" step="any" name="september_revision" class="september_revision text-center"/>
                                                                    <input type="hidden" id="october_revision"  value="<?=$october_revision;?>" size="25" step="any" name="october_revision" class="october_revision text-center"/>
                                                                    <input type="hidden" id="november_revision"  value="<?=$november_revision;?>" size="25" step="any" name="november_revision" class="november_revision text-center"/>
                                                                    <input type="hidden" id="december_revision"  value="<?=$december_revision;?>" size="25" step="any" name="december_revision" class="december_revision text-center"/>
                                                                </td>
                                                            </tr>
                                                            <?php endforeach; endif;?>
                                                            <?php if(!empty($reuse_recid)):
                                                                $query = $this->db->query("
                                                                SELECT
                                                                    `recid`,
                                                                    `object_code`,
                                                                    `particulars`,
                                                                    `code`,
                                                                    `approved_budget`,
                                                                    `january_revision`,
                                                                    `february_revision`,
                                                                    `march_revision`,
                                                                    `april_revision`,
                                                                    `may_revision`,
                                                                    `june_revision`,
                                                                    `july_revision`,
                                                                    `august_revision`,
                                                                    `september_revision`,
                                                                    `october_revision`,
                                                                    `november_revision`,
                                                                    `december_revision`
                                                                FROM
                                                                    `tbl_saob_mooe_dt`
                                                                WHERE 
                                                                    `project_id` = '$reuse_recid'
                                                                ORDER BY recid"
                                                                
                                                                );
                                                                $result = $query->getResultArray();
                                                                foreach ($result as $data):
                                                                    $dt_id = $data['recid'];
                                                                    $object_code = $data['object_code'];
                                                                    $particulars = $data['particulars'];
                                                                    $code = $data['code'];
                                                                    $approved_budget = $data['approved_budget'];
                                                                    $january_revision = $data['january_revision'];
                                                                    $february_revision = $data['february_revision'];
                                                                    $march_revision = $data['march_revision'];
                                                                    $april_revision = $data['april_revision'];
                                                                    $may_revision = $data['may_revision'];
                                                                    $june_revision = $data['june_revision'];
                                                                    $july_revision = $data['july_revision'];
                                                                    $august_revision = $data['august_revision'];
                                                                    $september_revision = $data['september_revision'];
                                                                    $october_revision = $data['october_revision'];
                                                                    $november_revision = $data['november_revision'];
                                                                    $december_revision = $data['december_revision'];

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
                                                                        onclick="__mysys_saob_rpt_ent.my_add_budget_mooe_line_above(this);">
                                                                            <i class="ti ti-plus"></i>
                                                                        </a>
                                                                    </div>
                                                                </td>
                                                                <td class="text-center align-middle" nowrap>
                                                                    <select name="selObject" class="selObject form" style="width:300px; height:30px;">
                                                                        <option selected value ="<?=$object_code;?>"><?=$object_code;?></option>
                                                                        <?php foreach($mooeobjectdata as $data){
                                                                            $object_code = $data['object_code'];
                                                                        ?>
                                                                            <option value="<?=$object_code?>"><?=$object_code?></option>
                                                                        <?php }?>
                                                                    </select>
                                                                </td>
                                                                <td class="text-center align-middle" nowrap>
                                                                    <select name="selUacs" class="selUacs form"  style="width:300px; height:30px;">
                                                                        <option selected value ="<?=$particulars;?>"><?=$particulars;?></option>
                                                                        <?php foreach($mooeuacsdata as $data){
                                                                            $sub_object_code = $data['sub_object_code'];
                                                                            $uacs_code = $data['uacs_code'];
                                                                        ?>
                                                                            <option value="<?=$sub_object_code?>"  data-uacs="<?=$uacs_code;?>"><?=$sub_object_code?></option>
                                                                        <?php }?>
                                                                    </select>
                                                                </td>
                                                                <td class="text-center align-middle" nowrap>
                                                                    <input type="text" id="uacs"  value="<?=$code;?>" size="25"  name="uacs" class="uacs text-center" disabled>
                                                                </td>
                                                                <td class="text-center align-middle" nowrap>
                                                                    <input type="number" id="approved_budget"  value="" size="25" step="any" name="approved_budget" data-dtid="<?=$dt_id;?>"  class="approved_budget text-center" onchange="__mysys_saob_rpt_ent.__direct_mooe_totals(); __mysys_saob_rpt_ent.__combined_totals();" onmouseout="__mysys_saob_rpt_ent.__direct_mooe_totals(); __mysys_saob_rpt_ent.__combined_totals();"/>
                                                                </td>
                                                                <td class="text-center align-middle" nowrap>
                                                                    <input type="number" id="todate_realignment" disabled  value="" size="25" step="any" name="todate_realignment" data-dtid="" class="todate_realignment text-center" onchange="__mysys_saob_rpt_ent.__direct_mooe_totals(); __mysys_saob_rpt_ent.__combined_totals();" onmouseout="__mysys_saob_rpt_ent.__direct_mooe_totals(); __mysys_saob_rpt_ent.__combined_totals();"/>
                                                                </td>
                                                                <td class="text-center align-middle" nowrap>
                                                                    <input type="number" id="proposed_realignment"  value="" size="25" step="any" name="proposed_realignment" data-dtid=""  class="proposed_realignment text-center" onchange="__mysys_saob_rpt_ent.__direct_mooe_totals(); __mysys_saob_rpt_ent.__combined_totals();" onmouseout="__mysys_saob_rpt_ent.__direct_mooe_totals(); __mysys_saob_rpt_ent.__combined_totals();" />
                                                                </td>
                                                                <td class="text-center align-middle" nowrap>
                                                                    <input type="number" id="revised_allotment"  value="" size="25" step="any" name="revised_allotment" data-dtid="" class="revised_allotment text-center" disabled/>
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
                                                    <table id="budget_co_line_items" class="table-sm table-striped budgetcodata-list">
                                                        <thead>
                                                            <th class="text-center">
                                                                <a class="text-info px-2 fs-7 bg-hover-primary nav-icon-hover position-relative z-index-5" id="btn_budgetcoitem_add" href="javascript:__mysys_saob_rpt_ent.my_add_budget_co_line();"><i class="ti ti-new-section"></i></a>
                                                            </th>
                                                            <th class="text-center align-middle">Object Code</th>
                                                            <th class="text-center align-middle">Particular</th>
                                                            <th class="text-center align-middle">UACS.</th>
                                                            <th class="text-center align-middle">Approved Budget</th>
                                                            <th class="text-center align-middle">Todate Realignment</th>
                                                            <th class="text-center align-middle">Proposed Realignment</th>
                                                            <th class="text-center align-middle">Revised Allotment</th>
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
                                                                            onclick="__mysys_saob_rpt_ent.my_add_budget_co_line_above(this);">
                                                                            <i class="ti ti-plus"></i>
                                                                        </a>
                                                                    </div>
                                                                </td>
                                                                <td class="text-center align-middle" nowrap>
                                                                    <select name="selObject" class="selObject form" style="width:300px; height:30px;">
                                                                        <option selected value =""></option>
                                                                        <?php foreach($coobjectdata as $data){
                                                                            $object_code = $data['object_code'];
                                                                        ?>
                                                                            <option value="<?=$object_code?>"><?=$object_code?></option>
                                                                        <?php }?>
                                                                    </select>
                                                                </td>
                                                                <td class="text-center align-middle" nowrap>
                                                                    <select name="selUacs" class="selUacs form"  style="width:300px; height:30px;">
                                                                        <option selected value ="">Choose...</option>
                                                                        <?php foreach($couacsdata as $data){
                                                                            $sub_object_code = $data['sub_object_code'];
                                                                            $uacs_code = $data['uacs_code'];
                                                                        ?>
                                                                            <option value="<?=$sub_object_code?>"  data-uacs="<?=$uacs_code;?>"><?=$sub_object_code?></option>
                                                                        <?php }?>
                                                                    </select>
                                                                </td>
                                                                <td class="text-center align-middle" nowrap>
                                                                    <input type="text" id="uacs"  value="" size="25"  name="uacs" class="uacs text-center" disabled>
                                                                </td>
                                                                <td class="text-center align-middle" nowrap>
                                                                    <input type="number" id="approved_budget"  value="" <?= empty($recid) ? '' : ($is_jan == '0' ? '' : 'disabled') ?> size="25" step="any" data-dtid=""  name="approved_budget" class="approved_budget text-center" onchange="__mysys_saob_rpt_ent.__direct_co_totals(); __mysys_saob_rpt_ent.__combined_totals();" onmouseout="__mysys_saob_rpt_ent.__direct_co_totals(); __mysys_saob_rpt_ent.__combined_totals();" />
                                                                </td>
                                                                <td class="text-center align-middle" nowrap>
                                                                    <input type="number" id="todate_realignment" disabled value="" size="25" step="any" data-dtid=""  name="todate_realignment" class="todate_realignment text-center" onchange="__mysys_saob_rpt_ent.__direct_co_totals(); __mysys_saob_rpt_ent.__combined_totals();" onmouseout="__mysys_saob_rpt_ent.__direct_co_totals(); __mysys_saob_rpt_ent.__combined_totals();" />
                                                                </td>
                                                                <td class="text-center align-middle" nowrap>
                                                                    <input type="number" id="proposed_realignment"  value="" size="25" step="any" name="proposed_realignment" data-dtid=""   class="proposed_realignment text-center" onchange="__mysys_saob_rpt_ent.__direct_co_totals(); __mysys_saob_rpt_ent.__combined_totals();" onmouseout="__mysys_saob_rpt_ent.__direct_co_totals(); __mysys_saob_rpt_ent.__combined_totals();" />
                                                                </td>
                                                                <td class="text-center align-middle" nowrap>
                                                                    <input type="number" id="revised_allotment"  value="" size="25" step="any" name="revised_allotment" data-dtid="" class="revised_allotment text-center" disabled/>
                                                                </td>
                                                            </tr>
                                                            <?php if(!empty($recid)):
                                                                $query = $this->db->query("
                                                                SELECT
                                                                    `recid`,
                                                                    `object_code`,
                                                                    `particulars`,
                                                                    `code`,
                                                                    `approved_budget`,
                                                                    `january_revision`,
                                                                    `february_revision`,
                                                                    `march_revision`,
                                                                    `april_revision`,
                                                                    `may_revision`,
                                                                    `june_revision`,
                                                                    `july_revision`,
                                                                    `august_revision`,
                                                                    `september_revision`,
                                                                    `october_revision`,
                                                                    `november_revision`,
                                                                    `december_revision`
                                                                FROM
                                                                    `tbl_saob_co_dt`
                                                                WHERE 
                                                                    `project_id` = '$recid'"
                                                                );
                                                                $result = $query->getResultArray();
                                                                foreach ($result as $data):
                                                                    $dt_id = $data['recid'];
                                                                    $object_code = $data['object_code'];
                                                                    $particulars = $data['particulars'];
                                                                    $code = $data['code'];
                                                                    $approved_budget = $data['approved_budget'];
                                                                    $january_revision = $data['january_revision'];
                                                                    $february_revision = $data['february_revision'];
                                                                    $march_revision = $data['march_revision'];
                                                                    $april_revision = $data['april_revision'];
                                                                    $may_revision = $data['may_revision'];
                                                                    $june_revision = $data['june_revision'];
                                                                    $july_revision = $data['july_revision'];
                                                                    $august_revision = $data['august_revision'];
                                                                    $september_revision = $data['september_revision'];
                                                                    $october_revision = $data['october_revision'];
                                                                    $november_revision = $data['november_revision'];
                                                                    $december_revision = $data['december_revision'];

                                                                    if ($is_jan == '1' && $is_feb == '0') {
                                                                        $todate_realignment = $january_revision;

                                                                    }elseif ($is_jan == '1' && $is_feb == '1' && $is_mar == '0') {
                                                                        $todate_realignment = $january_revision + $february_revision;

                                                                    }elseif ($is_jan == '1' && $is_feb == '1' && $is_mar == '1' && $is_apr == '0') {
                                                                        $todate_realignment = $january_revision + $february_revision + $march_revision;

                                                                    }elseif ($is_jan == '1' && $is_feb == '1' && $is_mar == '1' && $is_apr == '1' && $is_may == '0') {
                                                                        $todate_realignment = $january_revision + $february_revision + $march_revision + $april_revision;

                                                                    }elseif ($is_jan == '1' && $is_feb == '1' && $is_mar == '1' && $is_apr == '1' && $is_may == '1' && $is_jun == '0') {
                                                                        $todate_realignment = $january_revision + $february_revision + $march_revision + $april_revision + $may_revision;

                                                                    }elseif ($is_jan == '1' && $is_feb == '1' && $is_mar == '1' && $is_apr == '1' && $is_may == '1' && $is_jun == '1' && $is_jul == '0') {
                                                                        $todate_realignment = $january_revision + $february_revision + $march_revision + $april_revision + $may_revision + $june_revision;

                                                                    }elseif ($is_jan == '1' && $is_feb == '1' && $is_mar == '1' && $is_apr == '1' && $is_may == '1' && $is_jun == '1' && $is_jul == '1' && $is_aug == '0') {
                                                                        $todate_realignment = $january_revision + $february_revision + $march_revision + $april_revision + $may_revision + $june_revision + $july_revision;

                                                                    }elseif ($is_jan == '1' && $is_feb == '1' && $is_mar == '1' && $is_apr == '1' && $is_may == '1' && $is_jun == '1' && $is_jul == '1' && $is_aug == '1' && $is_sep == '0') {
                                                                        $todate_realignment = $january_revision + $february_revision + $march_revision + $april_revision + $may_revision + $june_revision + $july_revision + $august_revision;

                                                                    }elseif ($is_jan == '1' && $is_feb == '1' && $is_mar == '1' && $is_apr == '1' && $is_may == '1' && $is_jun == '1' && $is_jul == '1' && $is_aug == '1' && $is_sep == '1' && $is_oct == '0') {
                                                                        $todate_realignment = $january_revision + $february_revision + $march_revision + $april_revision + $may_revision + $june_revision + $july_revision + $august_revision + $september_revision;

                                                                    }elseif ($is_jan == '1' && $is_feb == '1' && $is_mar == '1' && $is_apr == '1' && $is_may == '1' && $is_jun == '1' && $is_jul == '1' && $is_aug == '1' && $is_sep == '1' && $is_oct == '1' && $is_nov == '0') {
                                                                        $todate_realignment = $january_revision + $february_revision + $march_revision + $april_revision + $may_revision + $june_revision + $july_revision + $august_revision + $september_revision + $october_revision;

                                                                    }elseif ($is_jan == '1' && $is_feb == '1' && $is_mar == '1' && $is_apr == '1' && $is_may == '1' && $is_jun == '1' && $is_jul == '1' && $is_aug == '1' && $is_sep == '1' && $is_oct == '1' && $is_nov == '1' && $is_dec == '0') {
                                                                        $todate_realignment = $january_revision + $february_revision + $march_revision + $april_revision + $may_revision + $june_revision + $july_revision + $august_revision + $september_revision + $october_revision + $november_revision;

                                                                    }elseif ($is_jan == '1' && $is_feb == '1' && $is_mar == '1' && $is_apr == '1' && $is_may == '1' && $is_jun == '1' && $is_jul == '1' && $is_aug == '1' && $is_sep == '1' && $is_oct == '1' && $is_nov == '1' && $is_dec == '1') {
                                                                        $todate_realignment = $january_revision + $february_revision + $march_revision + $april_revision + $may_revision + $june_revision + $july_revision + $august_revision + $september_revision + $october_revision + $november_revision + $december_revision;
                                                                    }else{
                                                                        $todate_realignment = 0.00;
                                                                    }
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
                                                                            onclick="__mysys_saob_rpt_ent.my_add_budget_co_line_above(this);">
                                                                            <i class="ti ti-plus"></i>
                                                                        </a>
                                                                    </div>
                                                                </td>
                                                                <td class="text-center align-middle" nowrap>
                                                                    <select name="selObject" class="selObject form" style="width:300px; height:30px;">
                                                                        <option selected value ="<?=$object_code;?>"><?=$object_code;?></option>
                                                                        <?php foreach($coobjectdata as $data){
                                                                            $object_code = $data['object_code'];
                                                                        ?>
                                                                            <option value="<?=$object_code?>"><?=$object_code?></option>
                                                                        <?php }?>
                                                                    </select>
                                                                </td>
                                                                <td class="text-center align-middle" nowrap>
                                                                    <select name="selUacs" class="selUacs form" style="width:300px; height:30px;">
                                                                        <option selected value ="<?=$particulars;?>"><?=$particulars;?></option>
                                                                        <?php foreach($couacsdata as $data){
                                                                            $sub_object_code = $data['sub_object_code'];
                                                                            $uacs_code = $data['uacs_code'];
                                                                        ?>
                                                                            <option value="<?=$sub_object_code?>" data-uacs="<?=$uacs_code;?>"><?=$sub_object_code?></option>
                                                                        <?php }?>
                                                                    </select>
                                                                </td>
                                                                <td class="text-center align-middle" nowrap>
                                                                    <input type="text" id="uacs"  value="<?=$code;?>" size="25"  name="uacs" class="uacs text-center">
                                                                </td>
                                                                <td class="text-center align-middle" nowrap>
                                                                    <input type="number" id="approved_budget" value="<?=$approved_budget;?>" <?= empty($is_jan) ? '' : 'disabled' ;?> size="25" step="any" data-dtid="<?=$dt_id;?>"  name="approved_budget" class="approved_budget text-center" onchange="__mysys_saob_rpt_ent.__direct_co_totals(); __mysys_saob_rpt_ent.__combined_totals();" onmouseout="__mysys_saob_rpt_ent.__direct_co_totals(); __mysys_saob_rpt_ent.__combined_totals();" />
                                                                </td>
                                                                <td class="text-center align-middle" nowrap>
                                                                    <input type="number" id="todate_realignment" disabled  value="<?=$todate_realignment;?>" size="25" step="any" data-dtid=""  name="todate_realignment" class="todate_realignment text-center" onchange="__mysys_saob_rpt_ent.__direct_co_totals(); __mysys_saob_rpt_ent.__combined_totals();" onmouseout="__mysys_saob_rpt_ent.__direct_co_totals(); __mysys_saob_rpt_ent.__combined_totals();" />
                                                                </td>
                                                                <td class="text-center align-middle" nowrap>
                                                                    <input type="number" id="proposed_realignment"  value="" size="25" step="any" name="proposed_realignment" data-dtid="" class="proposed_realignment text-center" disabled onchange="__mysys_saob_rpt_ent.__direct_co_totals(); __mysys_saob_rpt_ent.__combined_totals();" onmouseout="__mysys_saob_rpt_ent.__direct_co_totals(); __mysys_saob_rpt_ent.__combined_totals();" />
                                                                </td>
                                                                <td class="text-center align-middle" nowrap>
                                                                    <input type="number" id="revised_allotment"  value="" size="25" step="any" name="revised_allotment" data-dtid="" class="revised_allotment text-center" disabled/>
                                                                    <input type="hidden" id="january_revision"  value="<?=$january_revision;?>" size="25" step="any" name="january_revision" class="january_revision text-center"/>
                                                                    <input type="hidden" id="february_revision"  value="<?=$february_revision;?>" size="25" step="any" name="february_revision" class="february_revision text-center"/>
                                                                    <input type="hidden" id="march_revision"  value="<?=$march_revision;?>" size="25" step="any" name="march_revision" class="march_revision text-center"/>
                                                                    <input type="hidden" id="april_revision"  value="<?=$april_revision;?>" size="25" step="any" name="april_revision" class="april_revision text-center"/>
                                                                    <input type="hidden" id="may_revision"  value="<?=$may_revision;?>" size="25" step="any" name="may_revision" class="may_revision text-center"/>
                                                                    <input type="hidden" id="june_revision"  value="<?=$june_revision;?>" size="25" step="any" name="june_revision" class="june_revision text-center"/>
                                                                    <input type="hidden" id="july_revision"  value="<?=$july_revision;?>" size="25" step="any" name="july_revision" class="july_revision text-center"/>
                                                                    <input type="hidden" id="august_revision"  value="<?=$august_revision;?>" size="25" step="any" name="august_revision" class="august_revision text-center"/>
                                                                    <input type="hidden" id="september_revision"  value="<?=$september_revision;?>" size="25" step="any" name="september_revision" class="september_revision text-center"/>
                                                                    <input type="hidden" id="october_revision"  value="<?=$october_revision;?>" size="25" step="any" name="october_revision" class="october_revision text-center"/>
                                                                    <input type="hidden" id="november_revision"  value="<?=$november_revision;?>" size="25" step="any" name="november_revision" class="november_revision text-center"/>
                                                                    <input type="hidden" id="december_revision"  value="<?=$december_revision;?>" size="25" step="any" name="december_revision" class="december_revision text-center"/>
                                                                </td>
                                                            </tr>
                                                            <?php endforeach; endif;?>
                                                            <?php if(!empty($reuse_recid)):
                                                                $query = $this->db->query("
                                                                SELECT
                                                                    `recid`,
                                                                    `object_code`,
                                                                    `particulars`,
                                                                    `code`,
                                                                    `approved_budget`,
                                                                    `january_revision`,
                                                                    `february_revision`,
                                                                    `march_revision`,
                                                                    `april_revision`,
                                                                    `may_revision`,
                                                                    `june_revision`,
                                                                    `july_revision`,
                                                                    `august_revision`,
                                                                    `september_revision`,
                                                                    `october_revision`,
                                                                    `november_revision`,
                                                                    `december_revision`
                                                                FROM
                                                                    `tbl_saob_co_dt`
                                                                WHERE 
                                                                    `project_id` = '$reuse_recid'"
                                                                );
                                                                $result = $query->getResultArray();
                                                                foreach ($result as $data):
                                                                    $dt_id = $data['recid'];
                                                                    $object_code = $data['object_code'];
                                                                    $particulars = $data['particulars'];
                                                                    $code = $data['code'];
                                                                    $approved_budget = $data['approved_budget'];
                                                                    $january_revision = $data['january_revision'];
                                                                    $february_revision = $data['february_revision'];
                                                                    $march_revision = $data['march_revision'];
                                                                    $april_revision = $data['april_revision'];
                                                                    $may_revision = $data['may_revision'];
                                                                    $june_revision = $data['june_revision'];
                                                                    $july_revision = $data['july_revision'];
                                                                    $august_revision = $data['august_revision'];
                                                                    $september_revision = $data['september_revision'];
                                                                    $october_revision = $data['october_revision'];
                                                                    $november_revision = $data['november_revision'];
                                                                    $december_revision = $data['december_revision'];

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
                                                                            onclick="__mysys_saob_rpt_ent.my_add_budget_co_line_above(this);">
                                                                            <i class="ti ti-plus"></i>
                                                                        </a>
                                                                    </div>
                                                                </td>
                                                                <td class="text-center align-middle" nowrap>
                                                                    <select name="selObject" class="selObject form" style="width:300px; height:30px;">
                                                                        <option selected value ="<?=$object_code;?>"><?=$object_code;?></option>
                                                                        <?php foreach($coobjectdata as $data){
                                                                            $object_code = $data['object_code'];
                                                                        ?>
                                                                            <option value="<?=$object_code?>"><?=$object_code?></option>
                                                                        <?php }?>
                                                                    </select>
                                                                </td>
                                                                <td class="text-center align-middle" nowrap>
                                                                    <select name="selUacs" class="selUacs form" style="width:300px; height:30px;">
                                                                        <option selected value ="<?=$particulars;?>"><?=$particulars;?></option>
                                                                        <?php foreach($couacsdata as $data){
                                                                            $sub_object_code = $data['sub_object_code'];
                                                                            $uacs_code = $data['uacs_code'];
                                                                        ?>
                                                                            <option value="<?=$sub_object_code?>" data-uacs="<?=$uacs_code;?>"><?=$sub_object_code?></option>
                                                                        <?php }?>
                                                                    </select>
                                                                </td>
                                                                <td class="text-center align-middle" nowrap>
                                                                    <input type="text" id="uacs"  value="<?=$code;?>" size="25"  name="uacs" class="uacs text-center">
                                                                </td>
                                                                <td class="text-center align-middle" nowrap>
                                                                    <input type="number" id="approved_budget" value="" <?= empty($is_jan) ? '' : 'disabled' ;?> size="25" step="any" data-dtid="<?=$dt_id;?>"  name="approved_budget" class="approved_budget text-center" onchange="__mysys_saob_rpt_ent.__direct_co_totals(); __mysys_saob_rpt_ent.__combined_totals();" onmouseout="__mysys_saob_rpt_ent.__direct_co_totals(); __mysys_saob_rpt_ent.__combined_totals();" />
                                                                </td>
                                                                <td class="text-center align-middle" nowrap>
                                                                    <input type="number" id="todate_realignment" disabled  value="" size="25" step="any" data-dtid=""  name="todate_realignment" class="todate_realignment text-center" onchange="__mysys_saob_rpt_ent.__direct_co_totals(); __mysys_saob_rpt_ent.__combined_totals();" onmouseout="__mysys_saob_rpt_ent.__direct_co_totals(); __mysys_saob_rpt_ent.__combined_totals();" />
                                                                </td>
                                                                <td class="text-center align-middle" nowrap>
                                                                    <input type="number" id="proposed_realignment"  value="" size="25" step="any" name="proposed_realignment" data-dtid="" class="proposed_realignment text-center" disabled onchange="__mysys_saob_rpt_ent.__direct_co_totals(); __mysys_saob_rpt_ent.__combined_totals();" onmouseout="__mysys_saob_rpt_ent.__direct_co_totals(); __mysys_saob_rpt_ent.__combined_totals();" />
                                                                </td>
                                                                <td class="text-center align-middle" nowrap>
                                                                    <input type="number" id="revised_allotment"  value="" size="25" step="any" name="revised_allotment" data-dtid="" class="revised_allotment text-center" disabled/>
                                                                </td>
                                                            </tr>
                                                            <?php endforeach; endif;?>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row mb-3">
                                        <div class="col-sm-6">

                                        </div>
                                        <div class="col-sm-6">
                                            <div class="row">
                                                <div class="col-sm-6">
                                                    <span class="fw-bolder">Total Approved Budget:</span>
                                                    <input type="text" id="total_approved_combined" name="total_approved_combined" value="" class="form-control form-control-sm text-center fw-bold" style="border-bottom: 2px solid #000;"  readonly/>
                                                </div>
                                                <div class="col-sm-6">
                                                    <span class="fw-bolder">Total Proposed Realignment:</span>
                                                    <input type="text" id="total_proposed_combined" name="total_proposed_combined" value="" class="form-control form-control-sm text-center fw-bold" style="border-bottom: 2px solid #000;"  readonly/>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row mb-2">  
                            <div class="col-sm-12 text-end">
                                <button type="submit" id="submitBtnMain" class="btn bg-<?= empty($recid) ? 'success' : 'info' ?>-subtle text-<?= empty($recid) ? 'success' : 'info' ?> btn-sm"><i class="ti ti-brand-doctrine mt-1 fs-4 me-1"></i>
                                    <?= empty($recid) ? 'Save' : 'Update' ?>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- RECORD LIST -->
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
                                <th>Action</th>
                                <th>Program/Project Title</th>
                                <th>Year</th>
                                <th>Reuse</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(!empty($saobhddata)):
                                foreach ($saobhddata as $data):
                                    $dt_recid = $data['recid'];
                                    $hdtrxno = $data['trxno'];
                                    $program_title = $data['program_title'];
                                    $project_title = $data['project_title'];
                                    $current_year = $data['current_year'];
                            ?>
                            <tr>
                                <td class="text-center align-middle">
                                    <a class="text-info nav-icon-hover" href="mysaobrpt?meaction=MAIN&recid=<?= $dt_recid ?>" title="Edit Transaction">
                                        <i class="ti ti-edit"></i>
                                    </a>
                                </td>
                                <td class="text-center"><?=$project_title;?></td>
                                <td class="text-center"><?=$current_year;?></td>
                                <td class="text-center">                                    
                                    <a class="text-warning nav-icon-hover" href="mysaobrpt?meaction=MAIN&reuse_recid=<?= $dt_recid ?>" title="Reuse Transaction">
                                        <i class="ti ti-arrow-bear-right"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; endif;?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>


    <!-- PDF EXTRACTION -->
    <div class="row mb-2">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header bg-info p-1">
                    <div class="row">
                        <div class="col-sm-6 d-flex align-items-center text-start">
                            <h6 class="mb-0 lh-base px-3 text-white fw-semibold d-flex align-items-center">
                                <i class="ti ti-file"></i>
                                <span class="pt-1 px-1">SAOB Extraction</span>
                            </h6>
                        </div>
                        <div class="col-sm-6 text-end ">

                        </div>
                    </div>
                </div>						
                <div class="card-body p-0 px-4 py-2 my-2">
                    <div class="row mb-2">
                        <div class="col-sm-4">
                            <select name="month" id="month" class="form-select form-select-sm">
                                <option value="">-- Select Month --</option>
                                <option value="January">January</option>
                                <option value="February">February</option>
                                <option value="March">March</option>
                                <option value="April">April</option>
                                <option value="May">May</option>
                                <option value="June">June</option>
                                <option value="July">July</option>
                                <option value="August">August</option>
                                <option value="September">September</option>
                                <option value="October">October</option>
                                <option value="November">November</option>
                                <option value="December">December</option>
                            </select>
                        </div>
                        <div class="col-sm-4">
                            <select name="year" id="year" class="form-select form-select-sm">
                                <option value="">-- Select Year --</option>
                                <option value="2026">2026</option>
                                <option value="2025">2025</option>
                                <option value="2024">2024</option>
                                <option value="2023">2023</option>
                                <option value="2022">2022</option>
                            </select>
                        </div>
                        <div class="col-sm-2">
                            <button class="btn btn-sm btn-outline-primary" onclick="__mysys_saob_rpt_ent.__saob_print('<?= base_url('mysaobrpt?meaction=SAOB-PDF')?>')">
                                Generate
                            </button>
                            <button class="btn btn-sm text-success p-0 border-0 bg-transparent"
                                onclick="__mysys_saob_rpt_ent.__saob_export_csv('<?= base_url('saob-export-csv')?>')"
                                title="Export SAOB CSV file">
                                <i class="ti ti-file-analytics fs-8"></i>
                            </button>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-12">
                            <div id="pdfContainer" style="width: 100%; height: 600px; border: 1px solid #ccc; position: relative;">
                            <iframe id="pdfFrame" style="width: 100%; height: 100%; border: none; display: none;"></iframe>

                            <div class="text-white fw-bolder" id="pdfPlaceholder" style="
                                position: absolute;
                                top: 0; left: 0; right: 0; bottom: 0;
                                display: flex;
                                justify-content: center;
                                align-items: center;
                                font-size: 1.2rem;
                                background:rgb(156, 147, 147);
                            ">
                                No PDF loaded yet. Please select month and year.
                            </div>
                        </div>

                        </div>
                    </div>                    
                </div>
            </div>
        </div>
    </div>

    <!-- ORS CSV EXTRACTION -->
    <div class="row mb-2">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header bg-info p-1">
                    <div class="row">
                        <div class="col-sm-6 d-flex align-items-center text-start">
                            <h6 class="mb-0 lh-base px-3 text-white fw-semibold d-flex align-items-center">
                                <i class="ti ti-file"></i>
                                <span class="pt-1 px-1">ORS Extraction</span>
                            </h6>
                        </div>
                        <div class="col-sm-6 text-end ">

                        </div>
                    </div>
                </div>						
                <div class="card-body p-0 px-4 py-2 my-2">
                    <div class="row mb-2">
                        <div class="col-sm-4">
                            <input type="month" id="date_from" class="form-control form-control-sm">
                        </div>

                        <div class="col-sm-4">
                            <input type="month" id="date_to" class="form-control form-control-sm">
                        </div>

                        <div class="col-sm-2">
                            <button class="btn btn-success btn-sm"
                                    onclick="__mysys_saob_rpt_ent.__saob_export_csv_range('<?= base_url('export-csv')?>')">
                                Export CSV
                            </button>
                        </div>
                    </div>                 
                </div>
            </div>
        </div>
    </div>

    <!-- MONTHLY CSV EXTRACTION -->
    <div class="row mb-2">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header bg-info p-1">
                    <div class="row">
                        <div class="col-sm-6 d-flex align-items-center text-start">
                            <h6 class="mb-0 lh-base px-3 text-white fw-semibold d-flex align-items-center">
                                <i class="ti ti-file"></i>
                                <span class="pt-1 px-1">Monthly Report Extraction</span>
                            </h6>
                        </div>
                        <div class="col-sm-6 text-end ">

                        </div>
                    </div>
                </div>						
                <div class="card-body p-0 px-4 py-2 my-2">
                    <div class="row mb-2 align-items-end">
                        <!-- Program Title -->
                        <div class="col-sm-4">
                            <label class="fw-bold">Program Title:</label>
                            <input type="text" id="monthly_program_title" placeholder="Select program title" name="monthly_program_title" 
                                class="form-control form-control-sm"/>
                        </div>
                        <!-- Month + Icons -->
                        <div class="col-sm-4">
                            <label class="fw-bold">Select Month:</label>
                            <div class="d-flex align-items-center gap-2">
                                <input type="month" id="month_year" 
                                    class="form-control form-control-sm">
                                <!-- CSV Icon -->
                                <button class="btn btn-sm text-success p-0 border-0 bg-transparent"
                                    onclick="__mysys_saob_rpt_ent.__saob_export_csv_month('<?= base_url('monthly-export-csv')?>')"
                                    title="Export CSV file">
                                    <i class="ti ti-file-analytics fs-8"></i>
                                </button>
                                <!-- Print Icon -->
                                <button class="btn btn-sm text-secondary p-0 border-0 bg-transparent"
                                    onclick="__mysys_saob_rpt_ent.__printSavings('<?= base_url('mysaobrpt') ?>')" 
                                    title="Print Monthly Status">
                                    <i class="ti ti-printer fs-8"></i>
                                </button>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>



    <div class="row me-myua-access-outp-msg mx-0">
    </div>
</div>
<!-- Modal -->
<div class="modal fade" id="pdfModal" tabindex="-1" aria-labelledby="pdfModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="pdfModalLabel">PDF Preview</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <iframe id="pdfFrame" src="" style="width: 100%; height: 80vh;" frameborder="0"></iframe>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="pdfModalSavings" tabindex="-1" aria-labelledby="pdfModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Monthly Status Preview</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <iframe id="pdfSavings" src="" style="width: 100%; height: 80vh;" frameborder="0"></iframe>
            </div>
        </div>
    </div>
</div>



<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.min.js"></script>
<script src="<?=base_url('assets/js/report/mysaobreport.js?v=6');?>"></script>
<script src="<?=base_url('assets/js/mysysapps.js');?>"></script>

</script>
<script>
    __mysys_saob_rpt_ent.__saob_saving();
    __mysys_saob_rpt_ent.__combined_totals();
</script>

<!-- PROJECT TITLE LOOKUP -->
<script>
<?php
$projects = [];
foreach ($projectdata as $data) {
    $projects[$data['project_title']] = [
        'rc' => $data['responsibility_code']
    ];
}
?>
var projects = <?= json_encode($projects); ?>;
var project_titles = Object.keys(projects);

$(function () {

  $(document).on("focus", ".project_title", function () {

    if (!$(this).data("ui-autocomplete")) {

      $(this).autocomplete({
        source: project_titles,
        select: function (event, ui) {

          let row = $(this).closest('tr'); // change if not in table

          row.find('.responsibility_code')
             .val(projects[ui.item.value].rc);

        }
      });

    }

  });

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
  $("#monthly_program_title").autocomplete({
    source: programs
  });

  // Re-initialize whenever a new row is added dynamically
  $(document).on("focus", "#monthly_program_title", function () {
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


