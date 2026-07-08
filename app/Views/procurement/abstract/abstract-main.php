<?php
$this->request = \Config\Services::request();
$this->mybudgetallotment = model('App\Models\MyBudgetAllotmentModel');
$this->db = \Config\Database::connect();
$this->session = session();
$recid = $this->request->getPostGet('recid');
$lnk_prno = $this->request->getPostGet('prno');
$this->cuser = $this->session->get('__xsys_myuserzicas__');
$today = new DateTime();
$prno = '';
$abstract_date = '';
$availability_date = '';
$transaction_no = '';
$bidder_1 = '';
$bidder_2 = '';
$bidder_3 = '';
$bidder_4 = '';
$bidder_5 = '';

if(!empty($recid) || !is_null($recid)) { 
    $query = $this->db->query("
    SELECT
        `recid`,
        `prno`,
        `transaction_no`,
        `abstract_date`,
        `availability_date`,
        `bidder_1`,
        `bidder_2`,
        `bidder_3`,
        `bidder_4`,
        `bidder_5`
    FROM
        `tbl_abstract_hd`
    WHERE 
        `recid` = '$recid'"
    );

    $data = $query->getRowArray();
    $prno = $data['prno'];
    $transaction_no = $data['transaction_no'];
    $abstract_date = $data['abstract_date'];
    $availability_date = $data['availability_date'];
    $bidder_1 = $data['bidder_1'];
    $bidder_2 = $data['bidder_2'];
    $bidder_3 = $data['bidder_3'];
    $bidder_4 = $data['bidder_4'];
    $bidder_5 = $data['bidder_5'];

}else{
    $prno = $lnk_prno;
}

$allowed = new DateTime($availability_date);

$canPrint = $today >= $allowed;

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
        <h4 class="fw-semibold mb-8">ABSTRACT</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a class="text-muted text-decoration-none" href="<?=site_url();?>"><i class="ti ti-home fs-5"></i></a>
            </li>
            <li class="breadcrumb-item" aria-current="page">Procurement </li>
            <li class="breadcrumb-item" aria-current="page"><span class="form-label fw-bold">Abstract</span></li>
            </ol>
        </nav>
    </div>
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="row myabstract-outp-msg mx-0">

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
                    <form action="<?=site_url();?>myabstract?meaction=ABSTRACT-SAVE" method="post" class="myabstract-validation">
                        <div class="row mb-2">
                            <div class="col-sm-6">
                                <div class="row mb-2">
                                    <div class="col-sm-4">
                                        <span class="fw-bold">PR No.:</span>
                                    </div>
                                    <??>
                                    <div class="col-sm-8">
                                        <input type="hidden" id="recid" name="recid" value="<?=$recid;?>" />
                                        <input type="text"  name="prno" id="prno" value="<?=$prno;?>" placeholder="Enter PR No." class="form-control form-control-sm prno"/>
                                    </div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-sm-4">
                                        <span class="fw-bold">Transaction No.:</span>
                                    </div>
                                    <??>
                                    <div class="col-sm-8">
                                        <input type="text"  name="transaction_no" id="transaction_no" value="<?=$transaction_no;?>"  class="form-control form-control-sm transaction_no"/>
                                    </div>
                                </div>
                            </div>

                            <div class="col-sm-6">
                                <div class="row mb-2">
                                    <div class="col-sm-4">
                                        <span class="fw-bold">Transaction Date:</span>
                                    </div>
                                    <div class="col-sm-8">
                                        <input type="date" id="abstract_date" name="abstract_date" value="<?=$abstract_date;?>" class="form-control form-control-sm abstract_date"/>
                                    </div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-sm-6">
                                        <span class="fw-bold">Abstract Printing Availability Date:</span>
                                    </div>
                                    <div class="col-sm-6">
                                        <input type="date" id="availability_date" name="availability_date" value="<?=$availability_date;?>" class="form-control form-control-sm availability_date"/>
                                    </div>
                                </div>
                            </div>

                            <hr>

                            <div class="col-sm-12">
                                <div class="row mb-2">
                                    <div class="table-responsive pe-2 ps-0">
                                        <div class="col-md-12 mb-2">
                                            <table id="abstract_line_items" class=" table-striped abstractdata-list">
                                                <thead>
                                                    <th></th>
                                                    <th></th>
                                                    <th></th>
                                                    <th></th>
                                                    <th>
                                                        <div class="d-flex justify-content-between align-items-center">
                                                            <!-- LEFT -->
                                                            <label for="bidder_1" class="mb-0">Bidder 1 </label>
                                                            <!-- RIGHT -->
                                                            <div class="d-flex align-items-center gap-2">
                                                                <a class="text-secondary fs-4 bg-hover-danger nav-icon-hover"
                                                                data-bs-toggle="upload"
                                                                data-bs-placement="top"
                                                                title="Upload email reference"
                                                                href="javascript:void(0)">
                                                                    <i class="ti ti-upload"></i>
                                                                </a>

                                                            <input type="checkbox"
                                                                class="one-only mb-1"
                                                                name="option[]"
                                                                value="1"
                                                                data-bs-toggle="tooltip"
                                                                data-bs-placement="top"
                                                                title="Tag as Winner">

                                                            </div>
                                                        </div>
                                                    </th>
                                                    <th>
                                                        <div class="d-flex justify-content-between align-items-center">
                                                            <!-- LEFT -->
                                                            <label for="bidder_2" class="mb-0">Bidder 2 </label>

                                                            <!-- RIGHT -->
                                                            <div class="d-flex align-items-center gap-2">
                                                                <a class="text-secondary fs-4 bg-hover-danger nav-icon-hover"
                                                                data-bs-toggle="upload"
                                                                data-bs-placement="top"
                                                                title="Upload email reference"
                                                                href="javascript:void(0)">
                                                                    <i class="ti ti-upload"></i>
                                                                </a>

                                                            <input type="checkbox"
                                                                class="one-only mb-1"
                                                                name="option[]"
                                                                value="2"
                                                                data-bs-toggle="tooltip"
                                                                data-bs-placement="top"
                                                                title="Tag as Winner">

                                                            </div>
                                                        </div>
                                                    </th>
                                                    <th>
                                                        <div class="d-flex justify-content-between align-items-center">
                                                            <!-- LEFT -->
                                                            <label for="bidder_3" class="mb-0">Bidder 3 </label>

                                                            <!-- RIGHT -->
                                                            <div class="d-flex align-items-center gap-2">
                                                                <a class="text-secondary fs-4 bg-hover-danger nav-icon-hover"
                                                                data-bs-toggle="upload"
                                                                data-bs-placement="top"
                                                                title="Upload email reference"
                                                                href="javascript:void(0)">
                                                                    <i class="ti ti-upload"></i>
                                                                </a>

                                                            <input type="checkbox"
                                                                class="one-only mb-1"
                                                                name="option[]"
                                                                value="3"
                                                                data-bs-toggle="tooltip"
                                                                data-bs-placement="top"
                                                                title="Tag as Winner">

                                                            </div>
                                                        </div>
                                                    </th>
                                                    <th>
                                                        <div class="d-flex justify-content-between align-items-center">
                                                            <!-- LEFT -->
                                                            <label for="bidder_4" class="mb-0">Bidder 4 </label>

                                                            <!-- RIGHT -->
                                                            <div class="d-flex align-items-center gap-2">
                                                                <a class="text-secondary fs-4 bg-hover-danger nav-icon-hover"
                                                                data-bs-toggle="upload"
                                                                data-bs-placement="top"
                                                                title="Upload email reference"
                                                                href="javascript:void(0)">
                                                                    <i class="ti ti-upload"></i>
                                                                </a>

                                                            <input type="checkbox"
                                                                class="one-only mb-1"
                                                                name="option[]"
                                                                value="4"
                                                                data-bs-toggle="tooltip"
                                                                data-bs-placement="top"
                                                                title="Tag as Winner">

                                                            </div>
                                                        </div>
                                                    </th>
                                                    <th>
                                                        <div class="d-flex justify-content-between align-items-center">
                                                            <!-- LEFT -->
                                                            <label for="bidder_5" class="mb-0">Bidder 5 </label>

                                                            <!-- RIGHT -->
                                                            <div class="d-flex align-items-center gap-2">
                                                                <a class="text-secondary fs-4 bg-hover-danger nav-icon-hover"
                                                                data-bs-toggle="upload"
                                                                data-bs-placement="top"
                                                                title="Upload email reference"
                                                                href="javascript:void(0)">
                                                                    <i class="ti ti-upload"></i>
                                                                </a>

                                                            <input type="checkbox"
                                                                class="one-only mb-1"
                                                                name="option[]"
                                                                value="5"
                                                                data-bs-toggle="tooltip"
                                                                data-bs-placement="top"
                                                                title="Tag as Winner">

                                                            </div>
                                                        </div>
                                                    </th>
                                                </thead>
                                                <thead>
                                                    <th class="text-center">
                                                        <a class="text-info px-2 fs-7 bg-hover-primary nav-icon-hover position-relative z-index-5" id="btn_trxjournalitem_add" href="javascript:__mysys_abstract_ent.my_add_abstract_line();"><i class="ti ti-new-section"></i></a>
                                                    </th>
                                                    <th class="text-center align-middle">Quantity</th>
                                                    <th class="text-center align-middle">Unit</th>
                                                    <th class="text-center align-middle" style="width:500px;">Article/Description</th>
                                                    <th  class="text-center">
                                                        <input type="text" name="bidder_1" value="<?=$bidder_1;?>" placeholder="Company Name" id="bidder_1" class="text-center bidder_1">
                                                    </th>
                                                    <th class="text-center">
                                                        <input type="text" name="bidder_2" value="<?=$bidder_2;?>" placeholder="Company Name" id="bidder_2" class="text-center bidder_2">
                                                    </th>
                                                    <th class="text-center">
                                                        <input type="text" name="bidder_3" value="<?=$bidder_3;?>" placeholder="Company Name" id="bidder_3" class="text-center bidder_3">
                                                    </th>
                                                    <th class="text-center">
                                                        <input type="text" name="bidder_4" value="<?=$bidder_4;?>" placeholder="Company Name" id="bidder_4" class="text-center bidder_4">
                                                    </th>
                                                    <th class="text-center">
                                                        <input type="text" name="bidder_5" value="<?=$bidder_5;?>" placeholder="Company Name" id="bidder_5" class="text-center bidder_5">
                                                    </th>
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
                                                                onclick="__mysys_abstract_ent.my_add_abstract_line_above(this);">
                                                                    <i class="ti ti-plus"></i>
                                                                </a>
                                                            </div>
                                                        </td>
                                                        <td class="text-center align-middle" nowrap>
                                                            <input type="number" id="quantity"  value="" size="25" step="any"  name="quantity" data-dtid=""  class="quantity text-center"/>
                                                        </td>
                                                        <td class="text-center align-middle" nowrap>
                                                            <input type="text" id="unit"  value="" size="25" step="any"  name="unit" class="unit text-center"/>
                                                        </td>
                                                        <td class="text-center align-middle" nowrap>
                                                            <textarea name="item_desc" placeholder="" rows="1"  style="width:600px;" class="form-control form-control-sm border-black text-black item_desc"></textarea>
                                                        </td>
                                                        <td class="text-center align-middle" nowrap>
                                                            <input type="number" name="bidder_dt1" placeholder="0" id="bidder_dt1" class="text-center bidder_dt1">
                                                        </td>
                                                        <td class="text-center align-middle" nowrap>
                                                            <input type="number" name="bidder_dt2" placeholder="0" id="bidder_dt2" class="text-center bidder_dt2">
                                                        </td>
                                                        <td class="text-center align-middle" nowrap>
                                                            <input type="number" name="bidder_dt3" placeholder="0" id="bidder_dt3" class="text-center bidder_dt3">
                                                        </td>
                                                        <td class="text-center align-middle" nowrap>
                                                            <input type="number" name="bidder_dt4" placeholder="0" id="bidder_dt4" class="text-center bidder_dt4">
                                                        </td>
                                                        <td class="text-center align-middle" nowrap>
                                                            <input type="number" name="bidder_dt5" placeholder="0" id="bidder_dt5" class="text-center bidder_dt5">
                                                        </td>
                                                    </tr>
                                                    <?php if(!empty($recid)):
                                                        $query = $this->db->query("
                                                        SELECT
                                                            `recid`,
                                                            `pr_id`,
                                                            `prno`,
                                                            `quantity`,
                                                            `unit`,
                                                            `item_desc`,
                                                            `bidder_dt1`,
                                                            `bidder_dt2`,
                                                            `bidder_dt3`,
                                                            `bidder_dt4`,
                                                            `bidder_dt5`
                                                        FROM
                                                            `tbl_abstract_dt`
                                                        WHERE 
                                                            `prno` = '$prno'"
                                                        );
                                                        $result = $query->getResultArray();
                                                        foreach ($result as $data):
                                                            $quantity = $data['quantity'];
                                                            $unit = $data['unit'];
                                                            $item_desc = $data['item_desc'];
                                                            $bidder_dt1 = $data['bidder_dt1'];
                                                            $bidder_dt2 = $data['bidder_dt2'];
                                                            $bidder_dt3 = $data['bidder_dt3'];
                                                            $bidder_dt4 = $data['bidder_dt4'];
                                                            $bidder_dt5 = $data['bidder_dt5'];
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
                                                                onclick="__mysys_abstract_ent.my_add_abstract_line_above(this);">
                                                                    <i class="ti ti-plus"></i>
                                                                </a>
                                                            </div>
                                                        </td>
                                                        <td class="text-center align-middle" nowrap>
                                                            <input type="number" id="quantity" size="25" step="any" value="<?=$quantity;?>" name="quantity" data-dtid=""  class="quantity text-center"/>
                                                        </td>
                                                        <td class="text-center align-middle" nowrap>
                                                            <input type="text" id="unit" size="25" step="any" value="<?=$unit;?>"  name="unit" class="unit text-center"/>
                                                        </td>
                                                        <td class="text-center align-middle" nowrap>
                                                            <textarea name="item_desc" placeholder="" rows="1"  style="width:600px;" class="form-control form-control-sm border-black text-black item_desc"><?=$item_desc;?></textarea>
                                                        </td>
                                                        <td class="text-center align-middle" nowrap>
                                                            <input type="number" name="bidder_dt1" placeholder="0" id="bidder_dt1" value="<?=$bidder_dt1;?>" class="text-center bidder_dt1">
                                                        </td>
                                                        <td class="text-center align-middle" nowrap>
                                                            <input type="number" name="bidder_dt2" placeholder="0" id="bidder_dt2" value="<?=$bidder_dt2;?>" class="text-center bidder_dt2">
                                                        </td>
                                                        <td class="text-center align-middle" nowrap>
                                                            <input type="number" name="bidder_dt3" placeholder="0" id="bidder_dt3" value="<?=$bidder_dt3;?>" class="text-center bidder_dt3">
                                                        </td>
                                                        <td class="text-center align-middle" nowrap>
                                                            <input type="number" name="bidder_dt4" placeholder="0" id="bidder_dt4" value="<?=$bidder_dt4;?>" class="text-center bidder_dt4">
                                                        </td>
                                                        <td class="text-center align-middle" nowrap>
                                                            <input type="number" name="bidder_dt5" placeholder="0" id="bidder_dt5" value="<?=$bidder_dt5;?>" class="text-center bidder_dt5">
                                                        </td>
                                                    </tr>
                                                    <?php endforeach; endif;?>
                                                    <?php if(!empty($lnk_prno)):
                                                        $query = $this->db->query("
                                                        SELECT
                                                            `recid`,
                                                            `pr_id`,
                                                            `prno`,
                                                            `quantity`,
                                                            `unit`,
                                                            `item_desc`
                                                        FROM
                                                            `tbl_pr_dt`
                                                        WHERE 
                                                            `prno` = '$prno'"
                                                        );
                                                        $result = $query->getResultArray();
                                                        foreach ($result as $data):
                                                            $quantity = $data['quantity'];
                                                            $unit = $data['unit'];
                                                            $item_desc = $data['item_desc'];
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
                                                                onclick="__mysys_abstract_ent.my_add_abstract_line_above(this);">
                                                                    <i class="ti ti-plus"></i>
                                                                </a>
                                                            </div>
                                                        </td>
                                                        <td class="text-center align-middle" nowrap>
                                                            <input type="number" id="quantity" size="25" step="any" value="<?=$quantity;?>" name="quantity" data-dtid=""  class="quantity text-center"/>
                                                        </td>
                                                        <td class="text-center align-middle" nowrap>
                                                            <input type="text" id="unit" size="25" step="any" value="<?=$unit;?>"  name="unit" class="unit text-center"/>
                                                        </td>
                                                        <td class="text-center align-middle" nowrap>
                                                            <textarea name="item_desc" placeholder="" rows="1"  style="width:600px;" class="form-control form-control-sm border-black text-black item_desc"><?=$item_desc;?></textarea>
                                                        </td>
                                                        <td class="text-center align-middle" nowrap>
                                                            <input type="number" name="bidder_dt1" placeholder="0" id="bidder_dt1" class="text-center bidder_dt1">
                                                        </td>
                                                        <td class="text-center align-middle" nowrap>
                                                            <input type="number" name="bidder_dt2" placeholder="0" id="bidder_dt2" class="text-center bidder_dt2">
                                                        </td>
                                                        <td class="text-center align-middle" nowrap>
                                                            <input type="number" name="bidder_dt3" placeholder="0" id="bidder_dt3" class="text-center bidder_dt3">
                                                        </td>
                                                        <td class="text-center align-middle" nowrap>
                                                            <input type="number" name="bidder_dt4" placeholder="0" id="bidder_dt4" class="text-center bidder_dt4">
                                                        </td>
                                                        <td class="text-center align-middle" nowrap>
                                                            <input type="number" name="bidder_dt5" placeholder="0" id="bidder_dt5" class="text-center bidder_dt5">
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
                                <span class="pt-1">Abstract List</span>
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
                                <th>Transaction No.</th>
                                <th>Abstract Date</th>
                                <th>Bidder 1 </th>
                                <th>Bidder 2</th>
                                <th>Bidder 3</th>
                                <th>Bidder 4</th>
                                <th>Bidder 5</th>
                                <th>Abstract Print</th>
                                <th>PO Print</th>
                            </tr>
                        </thead>
                        <tbody class="align-middle">
                            <?php if(!empty($abstractlistdata)):
                                foreach ($abstractlistdata as $data):
                                    $dt_recid = $data['recid'];
                                    $prno = $data['prno'];
                                    $transaction_no = $data['transaction_no'];
                                    $abstract_date = $data['abstract_date'];
                                    $bidder_1 = $data['bidder_1'];
                                    $bidder_2 = $data['bidder_2'];
                                    $bidder_3 = $data['bidder_3'];
                                    $bidder_4 = $data['bidder_4'];
                                    $bidder_5 = $data['bidder_5'];
                                    
                            ?>
                            <tr>
                                <td class="text-center align-middle">
                                    <div class="d-flex justify-content-center gap-2">
                                        <a class="text-info nav-icon-hover fs-6" 
                                        href="myabstract?meaction=MAIN&recid=<?= $dt_recid ?>" 
                                        title="Edit Transaction">
                                        <i class="ti ti-edit"></i>
                                        </a>
                                    </div>
                                </td>
                                <td class="text-center"><?=$prno;?></td>
                                <td class="text-center"><?=$transaction_no;?></td>
                                <td class="text-center"><?=$abstract_date;?></td>
                                <td class="text-center"><?=$bidder_1;?></td>
                                <td class="text-center"><?=$bidder_2;?></td>
                                <td class="text-center"><?=$bidder_3;?></td>
                                <td class="text-center"><?=$bidder_4;?></td>
                                <td class="text-center"><?=$bidder_5;?></td>
                                <td class="text-center align-middle">
                                    <div class="d-flex justify-content-center gap-2">
                                        <?php if($canPrint):?>
                                            <button class="btn btn-sm fs-6 text-warning p-0 border-0 bg-transparent" 
                                                    onclick="__mysys_abstract_ent.__showPdfInModal('<?= base_url('myabstract?meaction=ABSTRACT-PRINT&recid='.$dt_recid) ?>')" 
                                                    title="Print ABSTRACT">
                                            <i class="ti ti-printer"></i>
                                            </button>
                                        <?php else:?>
                                            <button class="btn btn-sm fs-6 text-warning p-0 border-0 bg-transparent" 
                                                    disabled
                                                    title="Print ABSTRACT">
                                            <i class="ti ti-printer"></i>
                                            </button>
                                        <?php endif;?>
                                    </div>
                                </td>
                                <td class="text-center align-middle">
                                    <div class="d-flex justify-content-center gap-2">
                                        <?php if($canPrint):?>
                                            <button class="btn btn-sm fs-6 text-warning p-0 border-0 bg-transparent" 
                                                    onclick="__mysys_abstract_ent.__showPOPdfInModal('<?= base_url('myabstract?meaction=PO-PRINT&recid='.$dt_recid) ?>')" 
                                                    title="Print PO">
                                            <i class="ti ti-printer"></i>
                                            </button>
                                        <?php else:?>
                                            <button class="btn btn-sm fs-6 text-warning p-0 border-0 bg-transparent" 
                                                    disabled 
                                                    title="Print PO">
                                            <i class="ti ti-printer"></i>
                                            </button>
                                        <?php endif;?>
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

<!-- Abstract Modal -->
<div class="modal fade" id="pdfModal" tabindex="-1" aria-labelledby="pdfModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="pdfModalLabel">Abstract Preview</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <iframe id="pdfFrame" src="" style="width: 100%; height: 80vh;" frameborder="0"></iframe>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="pdfModalPO" tabindex="-1" aria-labelledby="pdfModalLabelPO" aria-hidden="true">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="pdfModalLabelPO">PO Preview</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <iframe id="pdfFramePO" src="" style="width: 100%; height: 80vh;" frameborder="0"></iframe>
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
<script src="<?=base_url('assets/js/procurement/abstract/myabstract.js?v=1');?>"></script>
<script src="<?=base_url('assets/js/mysysapps.js');?>"></script>

<script>
    __mysys_abstract_ent.__abstract_saving();
<?php
    $suggestions = [];
    foreach ($productsdata as $data) {
        $suggestions[] = $data['product_desc'];
    }
?>
var suggestions = <?php echo json_encode($suggestions); ?>;

$(function() {

  // Initialize for existing fields
  $(".item_desc").autocomplete({
    source: suggestions
  }).on("click", function() {
    $(this).autocomplete("search", ""); // show on click
  });

  // Re-initialize for dynamically added fields
  $(document).on("focus", ".item_desc", function () {
    if (!$(this).data("uiAutocomplete")) {
      $(this).autocomplete({
        source: suggestions
      });
    }
  });

  // Also trigger autocomplete on click for dynamic fields
  $(document).on("click", ".item_desc", function () {
    $(this).autocomplete("search", "");
  });

});
</script>


<script>
<?php
    $prno = [];
    foreach ($prlistdata as $data) {
        $prno[] = $data['prno']; 
    }
?>
var prno = <?php echo json_encode($prno); ?>;
var mesiteurl = "<?= base_url(); ?>"; // your base URL

$(function() {
    // Initialize autocomplete for existing inputs
    $(".prno").autocomplete({
        source: prno,
        minLength: 0, // show suggestions on empty input
        select: function(event, ui) {
            var selectedPrno = ui.item.value;
            // Redirect to myabstract with meaction=MAIN and prno
            window.location.href = mesiteurl + 'myabstract?meaction=MAIN&prno=' + encodeURIComponent(selectedPrno);
        }
    }).focus(function() {
        $(this).autocomplete("search", "");
    });

    // Handle dynamically added inputs
    $(document).on("focus", ".prno", function () {
        if (!$(this).data("uiAutocomplete")) {
            $(this).autocomplete({
                source: prno,
                minLength: 0,
                select: function(event, ui) {
                    var selectedPrno = ui.item.value;
                    window.location.href = mesiteurl + 'myabstract?meaction=MAIN&prno=' + encodeURIComponent(selectedPrno);
                }
            }).autocomplete("search", "");
        }
    });
});
</script>
<script>
document.querySelectorAll('.one-only').forEach((checkbox) => {
    checkbox.addEventListener('change', function () {
        if (this.checked) {
            document.querySelectorAll('.one-only').forEach(cb => {
                if (cb !== this) cb.disabled = true;
            });
        } else {
            document.querySelectorAll('.one-only').forEach(cb => {
                cb.disabled = false;
            });
        }
    });
});
</script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    var tooltipTriggerList = [].slice.call(
        document.querySelectorAll('[data-bs-toggle="tooltip"]')
        document.querySelectorAll('[data-bs-toggle="upload"]')
    );
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});
</script>




<?php
    echo view('templates/myfooter.php');
?>


