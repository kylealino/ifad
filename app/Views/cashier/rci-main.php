<?php
$this->request = \Config\Services::request();
$this->db = \Config\Database::connect();
$action = $this->request->getPostGet('action');
$recid = $this->request->getPostGet('recid');

$mds_branch = '';
$mds_accountno = '';
$reportno = '';
$funding_source = '';


if((!empty($recid) || !is_null($recid)) ) { 
    $query = $this->db->query("
        SELECT
            `mds_branch`,
            `mds_accountno`,
            `fund_cluster_code`,
            `reportno`
        FROM
            `tbl_rci_hd`
        WHERE 
            `recid` = '$recid'
        "
    );

    $data = $query->getRowArray();
    $mds_branch        = $data['mds_branch'];
    $mds_accountno        = $data['mds_accountno'];
    $fund_cluster_code     = $data['fund_cluster_code'];
    $reportno     = $data['reportno'];
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
    <div class="row me-myrciburs-appr-outp-msg mx-0">
    </div>
    
    <input type="hidden" id="__siteurl" data-mesiteurl="<?=site_url();?>" />
    <div class="row mb-2 mt-0">
        <h4 class="fw-semibold mb-8">Reports of Checks Issued</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a class="text-muted text-decoration-none" href="<?=site_url();?>"><i class="ti ti-home fs-5"></i></a>
            </li>
            <li class="breadcrumb-item" aria-current="page">Cashier</li>
            <li class="breadcrumb-item" aria-current="page"><span class="form-label fw-bold">RCI</span></li>
            </ol>
        </nav>
    </div>

    <div class="card rounded">
        <div class="row myrci-outp-msg mx-0">

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
            <form action="<?=site_url();?>myrci?meaction=MAIN-SAVE" method="post" class="myrci-validation">
                <input type="hidden" class="form-control form-control-sm" id="recid" name="recid" value="<?=$recid;?>"/>
                <div class="row">
                    <div class="col-sm-6">
                        <div class="row">
                            <div class="row mb-2">
                                <div class="col-sm-4">
                                    <span class="fw-bold">BRANCH:</span>
                                </div>
                                <div class="col-sm-8">
                                    <input type="text" id="mds_branch" name="mds_branch" value="<?= !empty($recid) ? $mds_branch : 'Land Bank of the Philippines - DOST Branch';?>" class="form-control form-control-sm"/>
                                </div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-sm-4">
                                    <span class="fw-bold">ACCOUNT NO.:</span>
                                </div>
                                <div class="col-sm-8">
                                    <input type="text" id="mds_accountno" name="mds_accountno" value="<?= !empty($recid) ? $mds_accountno : '2182-9001-36';?>" class="form-control form-control-sm"/>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6">
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
                        <div class="row mb-2">
                            <div class="col-sm-4">
                                <span class="fw-bold">Report No.:</span>
                            </div>
                            <div class="col-sm-8">
                                <input type="text" id="reportno" name="reportno" value="<?=$reportno;?>" class="form-control form-control-sm"/>
                            </div>
                        </div>
                    </div>
                </div>
                <hr>

                <div class="col-sm-12">
                    <div class="row mb-2">
                        <div class="table-responsive pe-2 ps-0">
                            <div class="col-md-12 mb-2">
                                <table id="rci_line_items" class="table-sm table-striped rcidata-list">
                                    <thead>
                                        <th class="text-center">
                                            <a class="text-info px-2 fs-7 bg-hover-primary nav-icon-hover position-relative z-index-5" id="btn_trxrciitem_add" href="javascript:__mysys_rci_ent.my_add_rci_line();"><i class="ti ti-new-section"></i></a>
                                        </th>
                                        <th class="text-center align-middle">LDDAP-ADA #</th>
                                        <th class="text-center align-middle">Check Date</th>
                                        <th class="text-center align-middle">Check No.</th>
                                        <th class="text-center align-middle">DV #</th>
                                        <th class="text-center align-middle">ORS/BURS #</th>
                                        <th class="text-center align-middle">RESPONSIBILITY CENTER CODE</th>
                                        <th class="text-center align-middle">PAYEE</th>
                                        <th class="text-center align-middle">NATURE OF PAYMENT</th>
                                        <th class="text-center align-middle">GROSS AMOUNT</th>
                                        <th class="text-center align-middle">PHILHEALTH AMOUNT</th>
                                        <th class="text-center align-middle">TAX AMOUNT</th>
                                        <th class="text-center align-middle">NET AMOUNT</th>
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
                                                    onclick="__mysys_rci_ent.my_add_rci_line_above(this);">
                                                        <i class="ti ti-plus"></i>
                                                    </a>
                                                </div>
                                            </td>
                                            <td class="text-center align-middle" nowrap>
                                                <input type="text"  value="" name="lddapadano" class="lddapadano text-center"/>
                                            </td>
                                            <td class="text-center align-middle" nowrap>
                                                <input type="date"  value="" name="lddapada_date" class="lddapada_date text-center"/>
                                            </td>
                                            <td class="text-center align-middle" nowrap>
                                                <input type="text"  value="" name="ckno" class="ckno text-center"/>
                                            </td>
                                            <td class="text-center align-middle" nowrap>
                                                <input type="text"  value="" name="dvno" class="dvno text-center"/>
                                            </td>
                                            <td class="text-center align-middle" nowrap>
                                                <input type="text" value="" name="serialno" class="serialno text-center"/>
                                            </td>
                                            <td class="text-center align-middle" nowrap>
                                                <input type="text" value="" name="responsibility_code" class="responsibility_code text-center"/>
                                            </td>
                                            <td class="text-center align-middle" nowrap>
                                                <input type="text" value="" name="payee_name" class="payee_name text-center"/>
                                            </td>
                                            <td class="text-center align-middle" nowrap>
                                                <input type="text" value="" name="particulars" class="particulars text-center"/>
                                            </td>
                                            <td class="text-center align-middle" nowrap>
                                                <input type="number" value="" step="any"  name="gross_amount" class="gross_amount text-center"/>
                                            </td>
                                            <td class="text-center align-middle" nowrap>
                                                <input type="number"  value="" step="any"  name="philhealth_amount" data-dtid=""  class="philhealth_amount text-center"/>
                                            </td>
                                            <td class="text-center align-middle" nowrap>
                                                <input type="number"  value="" step="any"  name="tax_amount" data-dtid=""  class="tax_amount text-center"/>
                                            </td>
                                            <td class="text-center align-middle" nowrap>
                                                <input type="number"  value="" step="any" name="net_amount" data-dtid="" class="net_amount text-center"/>
                                            </td>
                                        </tr>
                                        <?php if(!empty($recid)):
                                                $query = $this->db->query("
                                                SELECT
                                                    `recid`,
                                                    `hd_rid`,
                                                    `lddapadano`,
                                                    `lddapada_date`,
                                                    `ckno`,
                                                    `dvno`,
                                                    `serialno`,
                                                    `responsibility_code`,
                                                    `payee_name`,
                                                    `particulars`,
                                                    `gross_amount`,
                                                    `philhealth_amount`,
                                                    `tax_amount`,
                                                    `net_amount`
                                                FROM
                                                    `tbl_rci_dt`
                                                WHERE
                                                    `hd_rid` = '$recid'
                                                ");
                                                $result = $query->getResultArray();
                                                foreach ($result as $data):
                                                    $dt_id = $data['recid'];
                                                    $lddapadano = $data['lddapadano'];
                                                    $lddapada_date = $data['lddapada_date'];
                                                    $ckno = $data['ckno'];
                                                    $dvno = $data['dvno'];
                                                    $serialno = $data['serialno'];
                                                    $responsibility_code = $data['responsibility_code'];
                                                    $payee_name = $data['payee_name'];
                                                    $particulars = $data['particulars'];
                                                    $gross_amount = $data['gross_amount'];
                                                    $philhealth_amount = $data['philhealth_amount'];
                                                    $tax_amount = $data['tax_amount'];
                                                    $net_amount = $data['net_amount'];
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
                                                        onclick="__mysys_rci_ent.my_add_rci_line_above(this);">
                                                            <i class="ti ti-plus"></i>
                                                        </a>
                                                    </div>
                                                </td>
                                                <td class="text-center align-middle" nowrap>
                                                    <input type="text"  value="<?=$lddapadano;?>" name="lddapadano" class="lddapadano text-center"/>
                                                </td>
                                                <td class="text-center align-middle" nowrap>
                                                    <input type="date"  value="<?=$lddapada_date;?>" name="lddapada_date" class="lddapada_date text-center"/>
                                                </td>
                                                <td class="text-center align-middle" nowrap>
                                                    <input type="text"  value="<?=$ckno;?>" name="ckno" class="ckno text-center"/>
                                                </td>
                                                <td class="text-center align-middle" nowrap>
                                                    <input type="text"  value="<?=$dvno;?>" name="dvno" class="dvno text-center"/>
                                                </td>
                                                <td class="text-center align-middle" nowrap>
                                                    <input type="text" value="<?=$serialno;?>" name="serialno" class="serialno text-center"/>
                                                </td>
                                                <td class="text-center align-middle" nowrap>
                                                    <input type="text" value="<?=$responsibility_code;?>" name="responsibility_code" class="responsibility_code text-center"/>
                                                </td>
                                                <td class="text-center align-middle" nowrap>
                                                    <input type="text" value="<?=$payee_name;?>" name="payee_name" class="payee_name text-center"/>
                                                </td>
                                                <td class="text-center align-middle" nowrap>
                                                    <input type="text" value="<?=$particulars;?>" name="particulars" class="particulars text-center"/>
                                                </td>
                                                <td class="text-center align-middle" nowrap>
                                                    <input type="number" value="<?=$gross_amount;?>" step="any"  name="gross_amount" class="gross_amount text-center"/>
                                                </td>
                                                <td class="text-center align-middle" nowrap>
                                                    <input type="number"  value="<?=$philhealth_amount;?>" step="any"  name="philhealth_amount" data-dtid=""  class="philhealth_amount text-center"/>
                                                </td>
                                                <td class="text-center align-middle" nowrap>
                                                    <input type="number"  value="<?=$tax_amount;?>" step="any"  name="tax_amount" data-dtid=""  class="tax_amount text-center"/>
                                                </td>
                                                <td class="text-center align-middle" nowrap>
                                                    <input type="number"  value="<?=$net_amount;?>" step="any" name="net_amount" data-dtid="" class="net_amount text-center"/>
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
                <div class="row mb-2">  
                    <div class="col-sm-12 text-end">
                        <button type="submit" id="submitBtn" class="btn bg-<?= empty($recid) ? 'success' : 'info' ?>-subtle text-<?= empty($recid) ? 'success' : 'info' ?> btn-sm"><i class="ti ti-brand-doctrine mt-1 fs-4 me-1"></i>
                            <?= empty($recid) ? 'Generate RCI' : 'Update' ?>
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
                                <th class="text-center">Report No.</th>
                                <th class="text-center">MDS Branch</th>
                                <th class="text-center">MDS Account No.</th>
                                <th class="text-center">Fund Cluster</th>
                            </tr>
                        </thead>
                        <tbody class="align-middle">
                            <?php if(!empty($rcidata)):
                                foreach ($rcidata as $data):
                                    $dt_recid = $data['recid'];
                                    $mds_branch = $data['mds_branch'];
                                    $mds_accountno = $data['mds_accountno'];
                                    $reportno = $data['reportno'];
                                    $fund_cluster_code = $data['fund_cluster_code'];

                            ?>
                            <tr>
                                <td class="text-center align-middle">
                                    <div class="d-flex justify-content-center gap-2">
                                        <a class="text-info nav-icon-hover fs-6" 
                                        href="myrci?meaction=MAIN&recid=<?= $dt_recid ?>" 
                                        title="Edit Transaction">
                                        <i class="ti ti-edit"></i>
                                        </a>
                                        <button class="btn btn-sm fs-6 text-warning p-0 border-0 bg-transparent" 
                                                onclick="__mysys_rci_ent.__showPdfInModal('<?= base_url('myrci?meaction=PRINT-RCI&recid='.$dt_recid) ?>')" 
                                                title="Print RCI">
                                        <i class="ti ti-printer"></i>
                                        </button>
                                    </div>
                                </td>
                                <td class="text-center"><?=$reportno;?></td>
                                <td class="text-center"><?=$mds_branch;?></td>
                                <td class="text-center"><?=$mds_accountno;?></td>
                                <td class="text-center"><?=$fund_cluster_code;?></td>
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
<script src="<?=base_url('assets/js/cashier/rci.js?v=1');?>"></script>
<script src="<?=base_url('assets/js/mysysapps.js');?>"></script>

<?php
$lddapada = [];

foreach ($lddapadadata as $data) {

    $lddapada[] = [

        'label' => $data['lddapadano'],
        'value' => $data['lddapadano'],

        'lddapada_date'      => $data['lddapada_date'],
        'ckno'               => $data['ckno'],
        'dvno'               => $data['dvno'],
        'serialno'           => $data['serialno'],
        'responsibility_code'=> $data['responsibility_code'],
        'payee_name'         => $data['payee_name'],
        'particulars'        => $data['particulars'],
        'gross_amount'       => $data['gross_amount'],
        'philhealth_amount'   => $data['philhealth_amount'],
        'tax_amount'         => $data['tax_amount'],
        'net_amount'         => $data['net_amount'],

    ];

}
?>

<script>

var lddapada = <?= json_encode($lddapada); ?>;

$(document).on("focus", ".lddapadano", function () {

    if (!$(this).data("ui-autocomplete")) {

        $(this).autocomplete({

            source: lddapada,

            select: function (event, ui) {

                let row = $(this).closest('tr');

                $(this).val(ui.item.value);

                row.find('.lddapada_date').val(ui.item.lddapada_date);
                row.find('.ckno').val(ui.item.ckno);
                row.find('.dvno').val(ui.item.dvno);
                row.find('.serialno').val(ui.item.serialno);
                row.find('.responsibility_code').val(ui.item.responsibility_code);
                row.find('.payee_name').val(ui.item.payee_name);
                row.find('.particulars').val(ui.item.particulars);
                row.find('.gross_amount').val(ui.item.gross_amount);
                row.find('.philhealth_amount').val(ui.item.philhealth_amount);
                row.find('.tax_amount').val(ui.item.tax_amount);
                row.find('.net_amount').val(ui.item.net_amount);

                return false;

            }

        });

    }

});

</script>
<script>
    __mysys_rci_ent.__rci_saving();
</script>

<?php
    echo view('templates/myfooter.php');
?>


