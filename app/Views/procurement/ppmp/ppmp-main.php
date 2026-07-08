<?php
$this->request = \Config\Services::request();
$this->mybudgetallotment = model('App\Models\MyBudgetAllotmentModel');
$this->db = \Config\Database::connect();
$this->session = session();
$recid = $this->request->getPostGet('recid');
$this->cuser = $this->session->get('__xsys_myuserzicas__');

$ppmpno = '';
$end_user = '';
$fiscal_year = '';
$project_title = '';
$responsibility_code = '';
$is_indicative = '';
$is_final = '';
$prepared_by = '';
$submitted_by = '';

if(!empty($recid) || !is_null($recid)) { 
    $query = $this->db->query("
    SELECT
        `recid`,
        `ppmpno`,
        `end_user`,
        `fiscal_year`,
        `project_title`,
        `responsibility_code`,
        `is_indicative`,
        `is_final`,
        `prepared_by`,
        `submitted_by`
    FROM
        `tbl_ppmp_hd`
    WHERE 
        `recid` = '$recid'"
    );

    $data = $query->getRowArray();
    $ppmpno = $data['ppmpno'];
    $end_user = $data['end_user'];
    $fiscal_year = $data['fiscal_year'];
    $project_title = $data['project_title'];
    $responsibility_code = $data['responsibility_code'];
    $is_indicative = $data['is_indicative'];
    $is_final = $data['is_final'];
    $prepared_by = $data['prepared_by'];
    $submitted_by = $data['submitted_by'];

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
        <h4 class="fw-semibold mb-8">PPMP</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a class="text-muted text-decoration-none" href="<?=site_url();?>"><i class="ti ti-home fs-5"></i></a>
            </li>
            <li class="breadcrumb-item" aria-current="page">Procurement</li>
            <li class="breadcrumb-item" aria-current="page"><span class="form-label fw-bold">PPMP</span></li>
            </ol>
        </nav>
    </div>
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="row myppmp-outp-msg mx-0">

                </div>
                <div class="card-header bg-info p-1">
                    <div class="row px-3">
                        <div class="col-sm-6 d-flex align-items-center text-start">
                            <h6 class="mb-0 lh-base text-white fw-semibold d-flex align-items-center">
                                <i class="ti ti-pencil fs-5 me-1"></i>
                                <span class="pt-1">Entry</span>
                            </h6>
                        </div>
                        <div class="col-sm-6 text-end">
                        </div>
                    </div>
                </div>						
                <div class="card-body p-0 px-4 py-2 my-2">
                    <form action="<?=site_url();?>myppmp?meaction=PPMP-SAVE" method="post" class="myppmp-validation">
                        <div class="row mb-2">
                            <div class="col-sm-6">
                                <div class="row mb-2">
                                    <div class="col-sm-4">
                                        <span class="fw-bold">PPMP No.:</span>
                                    </div>
                                    <??>
                                    <div class="col-sm-8">
                                        <input type="hidden" id="recid" name="recid" value="<?=$recid;?>" />
                                        <input type="text"  name="ppmpno" id="ppmpno" value="<?=$ppmpno;?>" placeholder="Enter PPMP No." class="form-control form-control-sm ppmpno"/>
                                    </div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-sm-4">
                                        <span class="fw-bold">End-user:</span>
                                    </div>
                                    <div class="col-sm-8">
                                        <input type="text"  name="end_user" id="end_user" value="<?=$end_user;?>" placeholder="Enter Implementing Unit"  class="form-control form-control-sm end_user"/>
                                    </div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-sm-4">
                                        <span class="fw-bold">Fiscal Year:</span>
                                    </div>
                                    <div class="col-sm-8">
                                        <select class="form-select form-select-sm fiscal_year" name="fiscal_year" id="fiscal_year">
                                            <?php if(!empty($recid)):?>
                                                <option value="<?=$fiscal_year;?>"><?=$fiscal_year;?></option>
                                            <?php else:?>
                                                <option value="">Select Year</option>
                                            <?php endif;?>
                                            <option value="2026">2026</option>
                                            <option value="2025">2025</option>
                                            <option value="2024">2024</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-sm-4">
                                        <span class="fw-bold">Prepared by:</span>
                                    </div>
                                    <div class="col-sm-8">
                                        <input type="text" name="prepared_by" id="prepared_by" value="<?=$prepared_by;?>" placeholder="Enter prepared by" class="form-control form-control-sm prepared_by"/>
                                    </div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-sm-4">
                                        <span class="fw-bold">Submitted by:</span>
                                    </div>
                                    <div class="col-sm-8">
                                        <input type="text"  name="submitted_by" id="submitted_by" value="<?=$submitted_by;?>" placeholder="Select Authorized Signatory"  class="form-control form-control-sm submitted_by"/>
                                    </div>
                                </div>
                            </div>

                            <div class="col-sm-6">
                                <div class="row mb-2">
                                    <div class="col-sm-4">
                                        <span class="fw-bold">RC Code.:</span>
                                    </div>
                                    <div class="col-sm-8">
                                        <input type="text"  name="responsibility_code" id="responsibility_code" value="<?=$responsibility_code;?>" placeholder="Select RC Code" class="form-control form-control-sm responsibility_code"/>
                                    </div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-sm-4">
                                        <span class="fw-bold">Project, Programs & Activities:</span>
                                    </div>
                                    <div class="col-sm-8">
                                        <input type="text"  name="project_title" id="project_title" value="<?=$project_title;?>" disabled class="form-control form-control-sm project_title"/>
                                    </div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-sm-4">

                                    </div>
                                    <div class="col-sm-8">
                                        <div class="d-flex gap-3">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" id="is_indicative" <?= $is_indicative == '1' ? 'checked': '';?>>
                                                <label class="form-check-label" for="is_indicative">Indicative</label>
                                            </div>
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" id="is_final" <?= $is_final == '1' ? 'checked': '';?>>
                                                <label class="form-check-label" for="is_final">Final</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <hr>

                            <div class="col-sm-12">
                                <div class="row">
                                    <div class="table-responsive pe-2 ps-0">
                                        <div class="col-md-12">
                                            <table id="ppmp_line_items" class="table table-striped ppmpdata-list">
                                                <thead>
                                                    <th class="text-center my-0">
                                                        <a class="text-info px-2 fs-7 bg-hover-primary nav-icon-hover position-relative z-index-5" id="btn_trxjournalitem_add" href="javascript:__mysys_ppmp_ent.my_add_ppmp_line();"><i class="ti ti-new-section"></i></a>
                                                    </th>
                                                    <th class="text-center align-middle text-wrap text-break fs-1 my-0 py-0 px-0 mx-0" style="min-width:200px;;">General Description and objective of the Project to be Procured</th>
                                                    <th class="text-center align-middle text-wrap text-break fs-1 my-0 py-0 px-0 mx-0" style="min-width:128px;">Type of the Project to be Procured</th>
                                                    <th class="text-center align-middle text-wrap text-break fs-1 my-0 py-0 px-0 mx-0" style="min-width:80px;">Quantity</th>
                                                    <th class="text-center align-middle text-wrap text-break fs-1 my-0 py-0 px-0 mx-0" style="min-width:128px;">Size</th>
                                                    <th class="text-center align-middle text-wrap text-break fs-1 my-0 py-0 px-0 mx-0" style="min-width:128px;">Unit Cost</th>
                                                    <th class="text-center align-middle text-wrap text-break fs-1 my-0 py-0 px-0 mx-0" style="min-width:128px;">Mode of Procurement</th>
                                                    <th class="text-center align-middle text-wrap text-break fs-1 my-0 py-0 px-0 mx-0" style="min-width:128px;">Pre-Procurement Conference, if applicable(Yes/No)</th>
                                                    <th class="text-center align-middle text-wrap text-break fs-1 my-0 py-0 px-0 mx-0" style="min-width:128px;">Start of Procurement Activity</th>
                                                    <th class="text-center align-middle text-wrap text-break fs-1 my-0 py-0 px-0 mx-0" style="min-width:128px;">End of Procurement Activity</th>
                                                    <th class="text-center align-middle text-wrap text-break fs-1 my-0 py-0 px-0 mx-0" style="min-width:128px;">Expected Delivery/Implementation Period</th>
                                                    <th class="text-center align-middle text-wrap text-break fs-1 my-0 py-0 px-0 mx-0" style="min-width:128px;">Source of Funds</th>
                                                    <th class="text-center align-middle text-wrap text-break fs-1 my-0 py-0 px-0 mx-0" style="min-width:128px;">Estimated Budget / Authorized Budgetary Allocation(PHP)</th>
                                                    <th class="text-center align-middle text-wrap text-break fs-1 my-0 py-0 px-0 mx-0" style="min-width:128px;">ATTACHED SUPPORTING DOCUMENTS</th>
                                                    <th class="text-center align-middle text-wrap text-break fs-1 my-0 py-0 px-0 mx-0" style="min-width:128px;">REMARKS</th>
                                                </thead>
                                                <tbody>
                                                    <tr style="display:none;">
                                                        <td class="text-center align-middle mx-0 px-1">
                                                            <div class="d-inline-flex gap-1 justify-content-center">
                                                                <a class="text-danger fs-5 bg-hover-danger nav-icon-hover"
                                                                href="javascript:void(0)"
                                                                onclick="$(this).closest('tr').remove();">
                                                                    <i class="ti ti-trash"></i>
                                                                </a>
                                                                <a class="text-success fs-5 bg-hover-primary nav-icon-hover"
                                                                href="javascript:void(0)"
                                                                title="Add rows above"
                                                                onclick="__mysys_ppmp_ent.my_add_ppmp_line_above(this);">
                                                                    <i class="ti ti-plus"></i>
                                                                </a>
                                                            </div>
                                                        </td>
                                                        <td class="text-center align-middle mx-0 px-1" nowrap>
                                                            <input type="text" id="item_desc"  value="" step="any" size="50"  name="item_desc" class="item_desc fs-2"/>
                                                        </td>
                                                        <td class="text-center align-middle mx-0 px-1" nowrap>
                                                            <input type="text" id="item_type"  value="" step="any" size="20" name="item_type" class="item_type text-center fs-2"/>
                                                        </td>
                                                        <td class="text-center align-middle mx-0 px-1" nowrap>
                                                            <input type="number" id="quantity"  value="" step="any" size="20"  name="quantity"  class="quantity text-center fs-2"/>
                                                        </td>
                                                        <td class="text-center align-middle mx-0 px-1" nowrap>
                                                            <input type="text" id="size"  value="" step="any" size="20"  name="size" class="size text-center fs-2"/>
                                                        </td>
                                                        <td class="text-center align-middle mx-0 px-1" nowrap>
                                                            <input type="number" id="unit_cost"  value="" step="any" size="20"  name="unit_cost"  class="unit_cost text-center fs-2"/>
                                                        </td>
                                                        <td class="text-center align-middle mx-0 px-1" nowrap>
                                                            <input type="text" id="mop"  value="" step="any" size="20"  name="mop" class="mop text-center fs-2"/>
                                                        </td>
                                                        <td class="text-center align-middle mx-0 px-1" nowrap>
                                                            <input type="text" id="is_preproc"  value="" step="any" size="20"  name="is_preproc" class="is_preproc text-center fs-2"/>
                                                        </td>
                                                        <td class="text-center align-middle mx-0 px-1" nowrap>
                                                            <input type="month" name="proc_start" id="proc_start" size="20" class="text-center proc_start fs-2">
                                                        </td>
                                                        <td class="text-center align-middle mx-0 px-1" nowrap>
                                                            <input type="month" name="proc_end" id="proc_end" size="20" class="text-center proc_end fs-2">
                                                        </td>
                                                        <td class="text-center align-middle mx-0 px-1" nowrap>
                                                            <input type="month" name="expected_delivery_from" id="expected_delivery_from" size="20" class="text-center expected_delivery_from fs-2">
                                                            to
                                                            <input type="month" name="expected_delivery_to" id="expected_delivery_to" size="20" class="text-center expected_delivery_to fs-2">
                                                        </td>
                                                        <td class="text-center align-middle mx-0 px-1" nowrap>
                                                            <input type="text" id="funding_source"  value="" step="any"  name="funding_source" size="20" class="funding_source text-center fs-2"/>
                                                        </td>
                                                        <td class="text-center align-middle mx-0 px-1" nowrap>
                                                            <input type="number" id="estimated_budget"  value="" step="any"  name="estimated_budget" size="20" class="estimated_budget text-center fs-2"/>
                                                        </td>
                                                        <td class="text-center align-middle mx-0 px-1" nowrap>
                                                            <input type="text" id="attached_document"  value="" step="any"  name="attached_document" size="20" class="attached_document text-center fs-2"/>
                                                        </td>
                                                        <td class="text-center align-middle mx-0 px-1" nowrap>
                                                            <input type="text" id="remarks"  value="" step="any"  name="remarks" size="20" class="remarks text-center fs-2"/>
                                                        </td>
                                                    </tr>
                                                    <?php if(!empty($recid)):
                                                        $query = $this->db->query("
                                                        SELECT
                                                            `item_desc`,
                                                            `item_type`,
                                                            `quantity`,
                                                            `size`,
                                                            `unit_cost`,
                                                            `mop`,
                                                            `is_preproc`,
                                                            `proc_start`,
                                                            `proc_end`,
                                                            `expected_delivery_from`,
                                                            `expected_delivery_to`,
                                                            `funding_source`,
                                                            `estimated_budget`,
                                                            `attached_document`,
                                                            `remarks`
                                                        FROM
                                                            `tbl_ppmp_dt`
                                                        WHERE 
                                                            `ppmp_id` = '$recid'"
                                                        );
                                                        $result = $query->getResultArray();
                                                        foreach ($result as $data):
                                                            $item_desc = $data['item_desc'];
                                                            $item_type = $data['item_type'];
                                                            $quantity = $data['quantity'];
                                                            $size = $data['size'];
                                                            $unit_cost = $data['unit_cost'];
                                                            $mop = $data['mop'];
                                                            $is_preproc = $data['is_preproc'];
                                                            $proc_start = $data['proc_start'];
                                                            $proc_end = $data['proc_end'];
                                                            $expected_delivery_from = $data['expected_delivery_from'];
                                                            $expected_delivery_to = $data['expected_delivery_to'];
                                                            $funding_source = $data['funding_source'];
                                                            $estimated_budget = $data['estimated_budget'];
                                                            $attached_document = $data['attached_document'];
                                                            $remarks = $data['remarks'];
                                                            
                                                    ?>
                                                    <tr>
                                                        <td class="text-center align-middle mx-0 px-1">
                                                            <div class="d-inline-flex gap-1 justify-content-center">
                                                                <a class="text-danger fs-5 bg-hover-danger nav-icon-hover"
                                                                href="javascript:void(0)"
                                                                onclick="$(this).closest('tr').remove();">
                                                                    <i class="ti ti-trash"></i>
                                                                </a>
                                                                <a class="text-success fs-5 bg-hover-primary nav-icon-hover"
                                                                href="javascript:void(0)"
                                                                title="Add rows above"
                                                                onclick="__mysys_ppmp_ent.my_add_ppmp_line_above(this);">
                                                                    <i class="ti ti-plus"></i>
                                                                </a>
                                                            </div>
                                                        </td>
                                                        <td class="text-center align-middle mx-0 px-1" nowrap>
                                                            <input type="text" id="item_desc"  value="<?=$item_desc;?>" step="any" size="50"  name="item_desc" class="item_desc fs-2"/>
                                                        </td>
                                                        <td class="text-center align-middle mx-0 px-1" nowrap>
                                                            <input type="text" id="item_type"  value="<?=$item_type;?>" step="any" size="20" name="item_type" class="item_type text-center fs-2"/>
                                                        </td>
                                                        <td class="text-center align-middle mx-0 px-1" nowrap>
                                                            <input type="number" id="quantity"  value="<?=$quantity;?>" step="any" size="20"  name="quantity"  class="quantity text-center fs-2"/>
                                                        </td>
                                                        <td class="text-center align-middle mx-0 px-1" nowrap>
                                                            <input type="text" id="size"  value="<?=$size;?>" step="any" size="20"  name="size" class="size text-center fs-2"/>
                                                        </td>
                                                        <td class="text-center align-middle mx-0 px-1" nowrap>
                                                            <input type="number" id="unit_cost"  value="<?=$unit_cost;?>" step="any" size="20"  name="unit_cost"  class="unit_cost text-center fs-2"/>
                                                        </td>
                                                        <td class="text-center align-middle mx-0 px-1" nowrap>
                                                            <input type="text" id="mop"  value="<?=$mop;?>" step="any" size="20"  name="mop" class="mop text-center fs-2"/>
                                                        </td>
                                                        <td class="text-center align-middle mx-0 px-1" nowrap>
                                                            <input type="text" id="is_preproc"  value="<?=$is_preproc;?>" step="any" size="20"  name="is_preproc" class="is_preproc text-center fs-2"/>
                                                        </td>
                                                        <td class="text-center align-middle mx-0 px-1" nowrap>
                                                            <input type="month" name="proc_start" id="proc_start" size="20" value="<?=$proc_start;?>" class="text-center proc_start fs-2">
                                                        </td>
                                                        <td class="text-center align-middle mx-0 px-1" nowrap>
                                                            <input type="month" name="proc_end" id="proc_end"  size="20" value="<?=$proc_end;?>" class="text-center proc_end fs-2">
                                                        </td>
                                                        <td class="text-center align-middle mx-0 px-1" nowrap>
                                                            <input type="month" name="expected_delivery_from" id="expected_delivery_from" value="<?=$expected_delivery_from;?>" size="20" class="text-center expected_delivery_from fs-2">
                                                            to
                                                            <input type="month" name="expected_delivery_to" id="expected_delivery_to" value="<?=$expected_delivery_to;?>" size="20" class="text-center expected_delivery_to fs-2">
                                                        </td>
                                                        <td class="text-center align-middle mx-0 px-1" nowrap>
                                                            <input type="text" id="funding_source"  value="<?=$funding_source;?>" step="any"  name="funding_source" size="20" class="funding_source text-center fs-2"/>
                                                        </td>
                                                        <td class="text-center align-middle mx-0 px-1" nowrap>
                                                            <input type="number" id="estimated_budget"  value="<?=$estimated_budget;?>" step="any"  name="estimated_budget" size="20" class="estimated_budget text-center fs-2"/>
                                                        </td>
                                                        <td class="text-center align-middle mx-0 px-1" nowrap>
                                                            <input type="text" id="attached_document"  value="<?=$attached_document;?>" step="any"  name="attached_document" size="20" class="attached_document text-center fs-2"/>
                                                        </td>
                                                        <td class="text-center align-middle mx-0 px-1" nowrap>
                                                            <input type="text" id="remarks"  value="<?=$remarks;?>" step="any"  name="remarks" size="20" class="remarks text-center fs-2"/>
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
                        <div class="row mb-2">  
                            <div class="col-sm-12 text-end">
                                <button type="submit" id="submitBtn" class="btn bg-<?= empty($recid) ? 'success' : 'info' ?>-subtle text-<?= empty($recid) ? 'success' : 'info' ?> btn-sm"><i class="ti ti-brand-doctrine mt-1 fs-4 me-1"></i>
                                    <?= empty($recid) ? 'Save' : 'Update' ?>
                                </button>
                            </div>
                        </div>

                        <hr>

                    </form>

                </div>
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
                                <span class="pt-1">PPMP List</span>
                            </h6>
                        </div>
                    </div>
                </div>						
                <div class="card-body p-0 px-4 py-2 my-2">
                    <table id="datatablesSimple" class="table table-bordered table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Action</th>
                                <th>PPMP No.</th>
                                <th>End-User.</th>
                                <th>Fiscal Year</th>
                                <th>Project Title</th>
                                <th>Responsibility Code</th>
                            </tr>
                        </thead>
                        <tbody class="align-middle">
                            <?php if(!empty($ppmplistdata)):
                                foreach ($ppmplistdata as $data):
                                    $dt_recid = $data['recid'];
                                    $ppmpno = $data['ppmpno'];
                                    $end_user = $data['end_user'];
                                    $fiscal_year = $data['fiscal_year'];
                                    $project_title = $data['project_title'];
                                    $responsibility_code = $data['responsibility_code'];
                                    
                            ?>
                            <tr>
                                <td class="text-center align-middle">
                                    <div class="d-flex justify-content-center gap-2">
                                        <a class="text-info nav-icon-hover fs-6" 
                                        href="myppmp?meaction=MAIN&recid=<?= $dt_recid ?>" 
                                        title="Edit Transaction">
                                        <i class="ti ti-edit"></i>
                                        </a>
                                        <button class="btn btn-sm fs-6 text-warning p-0 border-0 bg-transparent" 
                                                onclick="__mysys_ppmp_ent.__showPdfInModal('<?= base_url('myppmp?meaction=PPMP-PRINT&recid='.$dt_recid) ?>')" 
                                                title="Print PPMP">
                                        <i class="ti ti-printer"></i>
                                        </button>
                                    </div>
                                </td>
                                <td class="text-center"><?=$ppmpno;?></td>
                                <td class="text-center"><?=$end_user;?></td>
                                <td class="text-center"><?=$fiscal_year;?></td>
                                <td class="text-center"><?=$project_title;?></td>
                                <td class="text-center"><?=$responsibility_code;?></td>

                            </tr>
                            <?php endforeach; endif;?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>


    <div class="row me-myua-access-outp-msg mx-0">
    </div>
</div>

<!-- PPMP Modal -->
<div class="modal fade" id="pdfModal" tabindex="-1" aria-labelledby="pdfModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="pdfModalLabel">PPMP Preview</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <iframe id="pdfFrame" src="" style="width: 100%; height: 80vh;" frameborder="0"></iframe>
      </div>
    </div>
  </div>
</div>

<!-- RFQ Modal -->
<div class="modal fade" id="pdfModalRFQ" tabindex="-1" aria-labelledby="pdfModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="pdfModalLabel">RFQ Preview</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <iframe id="pdfFrameRFQ" src="" style="width: 100%; height: 80vh;" frameborder="0"></iframe>
      </div>
    </div>
  </div>
</div>

<!-- APPROVAL -->
<div class="modal fade" id="confirmApproveModal" tabindex="-1" aria-labelledby="confirmApproveModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-md">
        <div class="modal-content">
            <div class="modal-header bg-info text-white py-2">
                <h5 class="modal-title text-white fs-4" id="confirmApproveModalLabel">RFQ Entry</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-sm-12">
                        <div class="row mb-2">
                            <div class="col-sm-4 d-flex align-items-center">
                                <span class="fw-bold">Quotation No.:</span>
                            </div>
                            <div class="col-sm-8">
                                <input type="text" id="quotation_no" name="quotation_no" value="" class="form-control form-control-sm"/>
                            </div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-sm-4 d-flex align-items-center text-nowrap">
                                <span class="fw-bold ">Quotation Date:</span>
                            </div>
                            <div class="col-sm-8">
                                <input type="date" id="quotation_date" name="quotation_date" value="" class="form-control form-control-sm"/>
                            </div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-sm-4 d-flex align-items-center">
                                <span class="fw-bold">Company Name:</span>
                            </div>
                            <div class="col-sm-8">
                                <input type="text" id="company_name" name="company_name" value="" class="form-control form-control-sm"/>
                            </div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-sm-4 d-flex align-items-center text-nowrap">
                                <span class="fw-bold ">Company Address:</span>
                            </div>
                            <div class="col-sm-8">
                                <textarea name="company_address" id="company_address" placeholder="" rows="2"  class="form-control form-control-sm  text-black company_address"></textarea>
                            </div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-sm-4 d-flex align-items-center">
                                <span class="fw-bold">Delivery Period:</span>
                            </div>
                            <div class="col-sm-8">
                                <input type="text" id="delivery_period" name="delivery_period" value="" class="form-control form-control-sm"/>
                            </div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-sm-4 d-flex align-items-center">
                                <span class="fw-bold">Terms of Payment:</span>
                            </div>
                            <div class="col-sm-8">
                                <input type="text" id="terms" name="terms" value="" class="form-control form-control-sm"/>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-success btn-sm px-3" id="confirmApproveBtn">Add</button>
                <button type="button" class="btn bg-secondary-subtle btn-sm" data-bs-dismiss="modal">Cancel</button>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.min.js"></script>
<script src="<?=base_url('assets/js/procurement/ppmp/myppmp.js?v=3');?>"></script>
<script src="<?=base_url('assets/js/mysysapps.js');?>"></script>

<script>
__mysys_ppmp_ent.__ppmp_saving();

<?php
    $suggestions = [];
    $item_type = ['Goods', 'Infrastructure','Consulting Services'];
    $size = ['pc', 'btl','pack','box','cyl','crtg'];
    $mop = ['SVP', 'Direct Acquisition','Direct Contracting','Public Bidding'];
    $is_preproc = ['Yes', 'No'];
    $funding_source  = ['General Fund','Trust Fund', 'Project Fund (Externally Funded)'];
    foreach ($productsdata as $data) {
        $suggestions[] = $data['product_desc'];
    }
?>
var suggestions = <?php echo json_encode($suggestions); ?>;
var item_type = <?php echo json_encode($item_type); ?>;
var size = <?php echo json_encode($size); ?>;
var mop = <?php echo json_encode($mop); ?>;
var is_preproc = <?php echo json_encode($is_preproc); ?>;
var funding_source = <?php echo json_encode($funding_source); ?>;
$(function() {

    // Generic function to initialize autocomplete
    function initAutocomplete(selector, sourceData) {
        $(document).on("focus click", selector, function () {
            var $this = $(this);

            // Initialize autocomplete if not already
            if (!$this.data("uiAutocomplete")) {
                $this.autocomplete({
                    source: sourceData,
                    minLength: 0 // so empty string search shows all suggestions
                });
            }

            // Trigger the search to show suggestions
            $this.autocomplete("search", "");
        });
    }

    // Initialize autocomplete for both fields
    initAutocomplete(".item_desc", suggestions);
    initAutocomplete(".item_type", item_type);
    initAutocomplete(".size", size);
    initAutocomplete(".mop", mop);
    initAutocomplete(".is_preproc", is_preproc);
    initAutocomplete(".funding_source", funding_source);

});
</script>



<script>
<?php
$project_map = [];
foreach ($projectsdata as $data) {
    $project_map[$data['responsibility_code']] = $data['project_title'];
}
?>
var project_map = <?= json_encode($project_map); ?>;

$(function() {
    $("#responsibility_code").autocomplete({
        source: Object.keys(project_map),
        minLength: 0,
        select: function (event, ui) {
            $("#project_title").val(project_map[ui.item.value]);
        }
    }).focus(function () {
        $(this).autocomplete("search", "");
    });
});
</script>


<script>
<?php
    $signatories = [];
    foreach ($signatoriesdata as $data) {
        $signatories[] = $data['full_name'];
    }
?>
var signatories = <?php echo json_encode($signatories); ?>;

$(function() {
  function initAutocomplete(el) {
    $(el).autocomplete({
      source: signatories,
      minLength: 0 // allows showing results without typing
    }).on("focus", function () {
      $(this).autocomplete("search", ""); // show all options on click
    });
  }

  // Initialize existing fields
  $(".submitted_by").each(function() {
    initAutocomplete(this);
  });

  // Initialize dynamically added fields
  $(document).on("focus", ".submitted_by", function () {
    if (!$(this).data("uiAutocomplete")) {
      initAutocomplete(this);
    }
  });
});
</script>




<?php
    echo view('templates/myfooter.php');
?>


