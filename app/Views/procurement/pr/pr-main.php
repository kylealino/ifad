<?php
$this->request = \Config\Services::request();
$this->mybudgetallotment = model('App\Models\MyBudgetAllotmentModel');
$this->db = \Config\Database::connect();
$this->session = session();
$recid = $this->request->getPostGet('recid');
$this->cuser = $this->session->get('__xsys_myuserzicas__');

$entity_name = '';
$office = '';
$prno = '';
$trxno = '';
$responsibility_code = '';
$current_year = '';
$fund_cluster = '';
$pr_date = '';
$end_user = '';
$charge_to = '';
$purpose = '';
$ppmp_list = [];

if(!empty($recid) || !is_null($recid)) { 
    $query = $this->db->query("
    SELECT
        `recid`,
        `entity_name`,
        `office`,
        `prno`,
        `responsibility_code`,
        `fund_cluster`,
        `pr_date`,
        `end_user`,
        `position`,
        `charge_to`,
        `purpose`
    FROM
        `tbl_pr_hd`
    WHERE 
        `recid` = '$recid'"
    );

    $data = $query->getRowArray();
    $entity_name = $data['entity_name'];
    $office = $data['office'];
    $prno = $data['prno'];
    $responsibility_code = $data['responsibility_code'];
    $fund_cluster = $data['fund_cluster'];
    $pr_date = $data['pr_date'];
    $end_user = $data['end_user'];
    $position = $data['position'];
    $charge_to = $data['charge_to'];
    $purpose = $data['purpose'];

    $query_ppmp = $this->db->query("
        SELECT ppmpno 
        FROM tbl_pr_ppmp 
        WHERE pr_id = '$recid'
    ");

    $result_ppmp = $query_ppmp->getResultArray();

    foreach($result_ppmp as $row){
        $ppmp_list[] = $row['ppmpno'];
    }
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
        <h4 class="fw-semibold mb-8">PURCHASE REQUEST</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a class="text-muted text-decoration-none" href="<?=site_url();?>"><i class="ti ti-home fs-5"></i></a>
            </li>
            <li class="breadcrumb-item" aria-current="page">Procurement</li>
            <li class="breadcrumb-item" aria-current="page"><span class="form-label fw-bold">Purchase Request</span></li>
            </ol>
        </nav>
    </div>
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="row myprocpr-outp-msg mx-0">

                </div>
                <div class="card-header bg-info p-1">
                    <div class="row px-3">
                        <div class="col-sm-6 d-flex align-items-center text-start">
                            <h6 class="mb-0 lh-base text-white fw-semibold d-flex align-items-center">
                                <i class="ti ti-pencil fs-5 me-1"></i>
                                <span class="pt-1">End-User Entry</span>
                            </h6>
                        </div>
                        <div class="col-sm-6 text-end">
                            <?php if (strpos($this->cuser, 'PPT') !== false && !empty($recid) && !empty($prno)):?>
                                <button type="button" id="btn_approve" name="btn_approve" class="btn_approve btn btn-sm btn-warning">
                                    <i class="ti ti-plus fs-3 me-1"></i>
                                    Add RFQ
                                </button>
                            <?php elseif(strpos($this->cuser, 'PPT') !== false && !empty($recid) && empty($prno)):?>
                                <button type="button" id="btn_approve" disabled name="btn_approve" class="btn_approve btn btn-sm btn-warning">
                                    <i class="ti ti-plus fs-3 me-1"></i>
                                    Add RFQ
                                </button>
                            <?php else:?>
                                <button type="button" id="btn_approve" disabled name="btn_approve" class="btn_approve btn btn-sm btn-warning d-none">
                                    <i class="ti ti-plus fs-3 me-1"></i>
                                    Add RFQ
                                </button>
                            <?php endif;?>
                        </div>
                    </div>
                </div>						
                <div class="card-body p-0 px-4 py-2 my-2">
                    <form action="<?=site_url();?>mypr?meaction=PR-SAVE" method="post" class="mypr-validation">
                        <div class="row mb-2">
                            <div class="col-sm-6">
                                <div class="row mb-2">
                                    <div class="col-sm-4">
                                        <span class="fw-bold">PR No.:</span>
                                    </div>
                                    <??>
                                    <div class="col-sm-8">
                                        <?php if (strpos($this->cuser, 'PPT') !== false):?>
                                            <input type="text" id="prno" name="prno" value="<?=$prno;?>" class="form-control form-control-sm"/>
                                        <?php else:?>
                                            <input type="text" id="prno" name="prno" disabled value="<?=$prno;?>" class="form-control form-control-sm"/>
                                        <?php endif;?>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="row mb-2">
                                    <div class="col-sm-4">
                                        <span class="fw-bold">Office/Section:</span>
                                    </div>
                                    <div class="col-sm-8">
                                        <input type="text" id="office" name="office" value="<?=$office;?>" class="form-control form-control-sm"/>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="row mb-2">
                                    <div class="col-sm-4">
                                        <span class="fw-bold">Entity Name:</span>
                                    </div>
                                    <div class="col-sm-8">
                                        <input type="hidden" id="recid" name="recid" value="<?=$recid;?>" class="form-control form-control-sm"/>
                                        <input type="text" id="entity_name" name="entity_name" value="<?=$entity_name;?>" class="form-control form-control-sm"/>
                                    </div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-sm-4">
                                        <span class="fw-bold">Responsibility Code:</span>
                                    </div>
                                    <div class="col-sm-8">
                                        <input type="text" id="responsibility_code" name="responsibility_code" value="<?=$responsibility_code;?>" class="form-control form-control-sm"/>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="row mb-2">
                                    <div class="col-sm-4">
                                        <span class="fw-bold">Fund Cluster:</span>
                                    </div>
                                    <div class="col-sm-8">
                                        <input type="text" id="fund_cluster" name="fund_cluster" value="<?=$fund_cluster;?>" class="form-control form-control-sm"/>
                                    </div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-sm-4">
                                        <span class="fw-bold">PR Date:</span>
                                    </div>
                                    <div class="col-sm-8">
                                        <input type="date" id="pr_date" name="pr_date" value="<?=$pr_date;?>" class="form-control form-control-sm"/>
                                    </div>
                                </div>
                            </div>

                            <hr>

                            <div class="col-sm-6">
                                <div class="row mb-2">
                                    <div class="col-sm-4">
                                        <span class="fw-bold">Charge To:</span>
                                    </div>
                                    <div class="col-sm-8">
                                        <textarea name="charge_to" id="charge_to" placeholder="" rows="3" class="form-control form-control-sm"><?=$charge_to;?></textarea>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="row mb-2">
                                    <div class="col-sm-4">
                                        <span class="fw-bold">End-User:</span>
                                    </div>
                                    <div class="col-sm-8">
                                        <input type="text" id="end_user" name="end_user" value="<?=$end_user;?>" class="form-control form-control-sm"/>
                                    </div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-sm-4">
                                        <span class="fw-bold">Purpose:</span>
                                    </div>
                                    <div class="col-sm-8">
                                        <input type="text" id="purpose" name="purpose" value="<?=$purpose;?>" class="form-control form-control-sm"/>
                                    </div>
                                </div>
                            </div>
          
                            <hr>

                            <div class="col-sm-12">
                                <div class="row mb-3 align-items-end">

                                    <!-- LEFT SIDE -->
                                    <div class="col-sm-6">
                                        <label class="fw-bold mb-1">PPMP No.</label>
                                        <div class="input-group input-group-sm">
                                            <select id="ppmp_lookup" class="form-control">
                                                <option value="">-- Select PPMP --</option>
                                                <?php
                                                $query = $this->db->query("SELECT ppmpno FROM tbl_ppmp_hd ORDER BY ppmpno ASC");
                                                $result = $query->getResultArray();
                                                foreach($result as $row):
                                                ?>
                                                    <option value="<?=$row['ppmpno'];?>"><?=$row['ppmpno'];?></option>
                                                <?php endforeach;?>
                                            </select>

                                            <button type="button" id="btn_add_ppmp" class="btn btn-primary">
                                                <i class="ti ti-plus"></i> Add
                                            </button>
                                        </div>
                                    </div>

                                    <!-- RIGHT SIDE -->
                                    <div class="col-sm-6">
                                        <label class="fw-bold mb-1">Selected PPMP</label>
                                        <div id="selected_ppmp_list" class="form-control form-control-sm d-flex flex-wrap gap-1" style="min-height:30px;">
                                            
                                        </div>
                                    </div>

                                </div>
                            </div>
                            
                            <hr>

                            <div class="col-sm-12">
                                <div class="row mb-2">
                                    <div class="table-responsive pe-2 ps-0">
                                        <div class="col-md-12 mb-2">
                                            <table id="pr_line_items" class="table-sm table-striped prdata-list">
                                                <thead>
                                                    <th class="text-center">
                                                        <a class="text-info px-2 fs-7 bg-hover-primary nav-icon-hover position-relative z-index-5" id="btn_trxjournalitem_add" href="javascript:__mysys_proc_pr_ent.my_add_pr_line();"><i class="ti ti-new-section"></i></a>
                                                    </th>
                                                    <th class="text-center align-middle" style="width:500px;">Item Description</th>
                                                    <th class="text-center align-middle">Unit</th>
                                                    <th class="text-center align-middle">Quantity</th>
                                                    <th class="text-center align-middle">Unit Cost</th>
                                                    <th class="text-center align-middle">Total Cost</th>
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
                                                                onclick="__mysys_proc_pr_ent.my_add_pr_line_above(this);">
                                                                    <i class="ti ti-plus"></i>
                                                                </a>
                                                            </div>
                                                        </td>
                                                        <td class="text-center align-middle" nowrap>
                                                            <textarea name="item_desc" placeholder="" rows="1"  class="form-control form-control-sm border-black text-black item_desc"></textarea>
                                                        </td>
                                                        <td class="text-center align-middle" nowrap>
                                                            <input type="text" id="unit"  value="" size="25" step="any"  name="unit" class="unit text-center" onchange="__mysys_proc_pr_ent.__direct_ps_totals(); __mysys_proc_pr_ent.__combined_totals();" onmouseout="__mysys_proc_pr_ent.__direct_ps_totals(); __mysys_proc_pr_ent.__combined_totals();" />
                                                        </td>
                                                        <td class="text-center align-middle" nowrap>
                                                            <input type="number" id="quantity"  value="" size="25" step="any"  name="quantity" data-dtid=""  class="quantity text-center" onchange="__mysys_proc_pr_ent.__direct_ps_totals(); __mysys_proc_pr_ent.__combined_totals();" onmouseout="__mysys_proc_pr_ent.__direct_ps_totals(); __mysys_proc_pr_ent.__combined_totals();" />
                                                        </td>
                                                        <td class="text-center align-middle" nowrap>
                                                            <input type="number" id="unit_cost"  value="" size="25" step="any" name="unit_cost" data-dtid="" class="unit_cost text-center" onchange="__mysys_proc_pr_ent.__direct_ps_totals(); __mysys_proc_pr_ent.__combined_totals();" onmouseout="__mysys_proc_pr_ent.__direct_ps_totals(); __mysys_proc_pr_ent.__combined_totals();" />
                                                        </td>
                                                        <td class="text-center align-middle" nowrap>
                                                            <input type="number" id="total_cost"  value="" size="25" step="any" name="total_cost" data-dtid="" class="total_cost text-center" disabled/>
                                                        </td>
                                                    </tr>
                                                    <?php if(!empty($recid)):
                                                        $query = $this->db->query("
                                                        SELECT
                                                            `item_desc`,
                                                            `unit`,
                                                            `quantity`,
                                                            `unit_cost`,
                                                            `total_cost`
                                                        FROM
                                                            `tbl_pr_dt`
                                                        WHERE 
                                                            `pr_id` = '$recid'"
                                                        );
                                                        $result = $query->getResultArray();
                                                        foreach ($result as $data):
                                                            $item_desc = $data['item_desc'];
                                                            $unit = $data['unit'];
                                                            $quantity = $data['quantity'];
                                                            $unit_cost = $data['unit_cost'];
                                                            $total_cost = $data['total_cost'];
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
                                                                onclick="__mysys_proc_pr_ent.my_add_pr_line_above(this);">
                                                                    <i class="ti ti-plus"></i>
                                                                </a>
                                                            </div>
                                                        </td>
                                                        <td class="text-center align-middle" nowrap>
                                                            <textarea name="item_desc" placeholder="" rows="1"  class="form-control form-control-sm border-black text-black item_desc"><?=$item_desc;?></textarea>
                                                        </td>
                                                        <td class="text-center align-middle" nowrap>
                                                            <input type="text" id="unit" size="25" step="any" value="<?=$unit;?>"  name="unit" class="unit text-center" onchange="__mysys_proc_pr_ent.__direct_ps_totals(); __mysys_proc_pr_ent.__combined_totals();" onmouseout="__mysys_proc_pr_ent.__direct_ps_totals(); __mysys_proc_pr_ent.__combined_totals();" />
                                                        </td>
                                                        <td class="text-center align-middle" nowrap>
                                                            <input type="number" id="quantity" size="25" step="any" value="<?=$quantity;?>" name="quantity" data-dtid=""  class="quantity text-center" onchange="__mysys_proc_pr_ent.__direct_ps_totals(); __mysys_proc_pr_ent.__combined_totals();" onmouseout="__mysys_proc_pr_ent.__direct_ps_totals(); __mysys_proc_pr_ent.__combined_totals();" />
                                                        </td>
                                                        <td class="text-center align-middle" nowrap>
                                                            <input type="number" id="unit_cost" size="25" step="any" value="<?=$unit_cost;?>" name="unit_cost" data-dtid="" class="unit_cost text-center" onchange="__mysys_proc_pr_ent.__direct_ps_totals(); __mysys_proc_pr_ent.__combined_totals();" onmouseout="__mysys_proc_pr_ent.__direct_ps_totals(); __mysys_proc_pr_ent.__combined_totals();" />
                                                        </td>
                                                        <td class="text-center align-middle" nowrap>
                                                            <input type="number" id="total_cost" size="25" step="any" value="<?=$total_cost;?>" name="total_cost" data-dtid="" class="total_cost text-center" disabled/>
                                                        </td>
                                                    </tr>
                                                    <?php endforeach; endif;?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-sm-6">

                                    </div>
                                    <div class="col-sm-6">
                                        <div class="row">
                                            <div class="col-sm-6">
                                                
                                            </div>
                                            <div class="col-sm-6">
                                                <span class="fw-bolder">Estimated Cost:</span>
                                                <input type="text" id="estimated_cost" name="estimated_cost" value="" class="form-control form-control-sm text-center fw-bold" style="border-bottom: 2px solid #000;"  readonly/>
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

                        <hr>

                    </form>
                    <?php if (strpos($this->cuser, 'PPT') !== false && !empty($recid)):?>
                    <div class="row">
                        <div class="col-sm-12 d-flex align-items-center text-start bg-light mx-3">
                            <h6 class="mb-0 lh-base fw-semibold d-flex align-items-center">
                                <i class="ti ti-list fs-5 me-1 text-info"></i>
                                <span class="pt-1 text-info">RFQ List</span>
                            </h6>
                        </div>
                        <div class="col-sm-12">
                            <table id="datatablesSimples" class="table table-bordered table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>Quotation No.</th>
                                        <th>Company Name</th>
                                        <th>Delivery Period</th>
                                        <th>Terms of Payment</th>
                                        <th>Quotation Date</th>
                                        <th>RFQ Print</th>
                                    </tr>
                                </thead>
                                <tbody class="align-middle">
                                    <?php if(!empty($recid)):
                                        $query = $this->db->query("
                                        SELECT
                                            `recid`,
                                            `quotation_no`,
                                            `company_name`,
                                            `company_address`,
                                            `delivery_period`,
                                            `terms`,
                                            `quotation_date`
                                        FROM
                                            `tbl_pr_rfq`
                                        WHERE
                                            `prno` = '$prno'"
                                        );
                                        $result = $query->getResultArray();
                                        foreach ($result as $data):
                                            $rfq_recid = $data['recid'];
                                            $quotation_no = $data['quotation_no'];
                                            $company_name = $data['company_name'];
                                            $company_address = $data['company_address'];
                                            $delivery_period = $data['delivery_period'];
                                            $terms = $data['terms'];
                                            $quotation_date = $data['quotation_date'];
                                    ?>
                                    <tr>
                                        <td class="text-center"><?=$quotation_no;?></td>
                                        <td class="text-center"><?=$company_name;?></td>
                                        <td class="text-center"><?=$delivery_period;?></td>
                                        <td class="text-center"><?=$terms;?></td>
                                        <td class="text-center"><?=$quotation_date;?></td>
                                        <td class="text-center align-middle">
                                            <div class="d-flex justify-content-center gap-2">
                                                <button class="btn btn-sm fs-6 text-primary p-0 border-0 bg-transparent" 
                                                        onclick="__mysys_proc_pr_ent.__showPdfInModalRFQ('<?= base_url('mypr?meaction=RFQ-PRINT&recid=') .$recid .'&rfq_recid=' . $rfq_recid ?>')" 
                                                        title="Print RFQ">
                                                <i class="ti ti-printer"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; endif;?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <?php endif;?>
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
                                <span class="pt-1">PR List</span>
                            </h6>
                        </div>
                    </div>
                </div>						
                <div class="card-body p-0 px-4 py-2 my-2">
                    <table id="datatablesSimple" class="table table-bordered table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Action</th>
                                <th>PR No.</th>
                                <th>Charge to</th>
                                <th>Purpose</th>
                                <th>End User</th>
                                <th>Estimated Cost</th>
                                <th>PR Print</th>
                            </tr>
                        </thead>
                        <tbody class="align-middle">
                            <?php if(!empty($prhddata)):
                                foreach ($prhddata as $data):
                                    $dt_recid = $data['recid'];
                                    $prno = $data['prno'];
                                    $charge_to = $data['charge_to'];
                                    $purpose = $data['purpose'];
                                    $end_user = $data['end_user'];
                                    $estimated_cost = $data['estimated_cost'];
                            ?>
                            <tr>
                                <td class="text-center align-middle">
                                    <div class="d-flex justify-content-center gap-2">
                                        <a class="text-info nav-icon-hover fs-6" 
                                        href="mypr?meaction=PR-MAIN&recid=<?= $dt_recid ?>" 
                                        title="Edit Transaction">
                                        <i class="ti ti-edit"></i>
                                        </a>
                                    </div>
                                </td>
                                <td class="text-center"><?=$prno;?></td>
                                <td class="text-center"><?=$charge_to;?></td>
                                <td class="text-center"><?=$purpose;?></td>
                                <td class="text-center"><?=$end_user;?></td>
                                <td class="text-center"><?= 'P'. number_format($estimated_cost,2);?></td>
                                <td class="text-center align-middle">
                                    <div class="d-flex justify-content-center gap-2">
                                        <button class="btn btn-sm fs-6 text-warning p-0 border-0 bg-transparent" 
                                                onclick="__mysys_proc_pr_ent.__showPdfInModalPR('<?= base_url('mypr?meaction=PR-PRINT&recid='.$dt_recid) ?>')" 
                                                title="Print ORS">
                                        <i class="ti ti-printer"></i>
                                        </button>
                                    </div>
                                </td>
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
<!-- PR Modal -->
<div class="modal fade" id="pdfModalPR" tabindex="-1" aria-labelledby="pdfModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="pdfModalLabel">PR Preview</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <iframe id="pdfFramePR" src="" style="width: 100%; height: 80vh;" frameborder="0"></iframe>
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

<script src="<?=base_url('assets/js/procurement/pr/mypr.js?v=2');?>"></script>
<script src="<?=base_url('assets/js/mysysapps.js');?>"></script>
<script>
<?php
    $suggestions = [];
    foreach ($productsdata as $data) {
        $suggestions[] = $data['product_desc']; // or another field
    }
?>
var suggestions = <?php echo json_encode($suggestions); ?>;

$(function() {
  // Initialize autocomplete for all existing textareas
  $(".item_desc").autocomplete({
    source: suggestions
  });

  // Re-initialize whenever a new row is added dynamically
  $(document).on("focus", ".item_desc", function () {
    if (!$(this).data("uiAutocomplete")) {
      $(this).autocomplete({
        source: suggestions
      });
    }
  });
});
</script>

<script>
    __mysys_proc_pr_ent.__combined_totals();
    __mysys_proc_pr_ent.__pr_saving();
    __mysys_proc_pr_ent.__add_rfq();
    
</script>

<script>


var selectedPPMP = <?= json_encode($ppmp_list); ?>;

$(document).ready(function() {

    // render existing PPMP from DB
    if (selectedPPMP.length > 0) {
        renderSelectedPPMP();

        var baseUrl = $('#__siteurl').data('mesiteurl');
        loadPPMPItems(baseUrl);
    }

});

$(document).on('click', '#btn_add_ppmp', function() {

    var baseUrl = $('#__siteurl').data('mesiteurl');
    var ppmp = $('#ppmp_lookup').val();

    if (!ppmp) {
        alert('Select PPMP first');
        return;
    }

    // prevent duplicate
    if (selectedPPMP.includes(ppmp)) {
        alert('Already added');
        return;
    }

    selectedPPMP.push(ppmp);

    renderSelectedPPMP();

    loadPPMPItems(baseUrl);
});

function renderSelectedPPMP() {
    var html = '';

    selectedPPMP.forEach(function(ppmp) {
        html += `
            <span class="badge bg-primary me-1" data-ppmp="${ppmp}">    
                ${ppmp}
                <a href="javascript:void(0)" onclick="removePPMP('${ppmp}')" 
                class="ms-1 text-white">&times;</a>
            </span>
        `;
    });

    $('#selected_ppmp_list').html(html);
}

function removePPMP(ppmp) {
    selectedPPMP = selectedPPMP.filter(p => p !== ppmp);
    renderSelectedPPMP();

    var baseUrl = $('#__siteurl').data('mesiteurl');
    loadPPMPItems(baseUrl);
}

function loadPPMPItems(baseUrl) {

    if (selectedPPMP.length === 0) {
        $('#pr_line_items tbody tr').not(':first').remove();
        return;
    }

    $.ajax({
        url: baseUrl + 'mypr?meaction=LOAD-PPMP',
        type: 'POST',
        data: {ppmpno: selectedPPMP},
        dataType: 'json',
        success: function(res) {

            $('#pr_line_items tbody tr').not(':first').remove();
            var rowTemplate = $('#pr_line_items tbody tr:first');

            $.each(res, function(i, row) {

                var newRow = rowTemplate.clone().show();

                newRow.find('.item_desc').val(row.item_desc);
                newRow.find('.unit').val(row.unit);
                newRow.find('.quantity').val(row.quantity);
                newRow.find('.unit_cost').val(row.unit_cost);
                newRow.find('.total_cost').val(row.total_cost);

                $('#pr_line_items tbody').append(newRow);
            });

            __mysys_proc_pr_ent.__combined_totals();
        }
    });
}
</script>

<?php
    echo view('templates/myfooter.php');
?>


