<?php
$this->request = \Config\Services::request();
$this->db = \Config\Database::connect();
$recid = $this->request->getPostGet('recid');
$this->session = session();
$this->cuser = $this->session->get('__xsys_myuserzicas__');

$totalquery = $this->db->query("
SELECT
    COALESCE(COUNT(recid), 0) AS total_transactions
FROM
    `tbl_ors_hd`
");

$totaldata = $totalquery->getRowArray();
$total_transactions = $totaldata['total_transactions'];

$pendingquery = $this->db->query("
SELECT
    COALESCE(COUNT(recid), 0) AS total_pending
FROM
    `tbl_ors_hd`
WHERE 
    `is_pending` = '1'
");

$pendingdata = $pendingquery->getRowArray();
$total_pending = $pendingdata['total_pending'];

$approvedquery = $this->db->query("
SELECT
    COALESCE(COUNT(recid), 0) AS total_approved_certa
FROM
    `tbl_ors_hd`
WHERE 
    `is_approved_certa` = '1'
");

$approveddata = $approvedquery->getRowArray();
$total_approved_certa = $approveddata['total_approved_certa'];

$disapprovedquery = $this->db->query("
SELECT
    COALESCE(COUNT(recid), 0) AS total_disapproved_certa
FROM
    `tbl_ors_hd`
WHERE 
    `is_disapproved_certa` = '1'
");

$disapproveddata = $disapprovedquery->getRowArray();
$total_disapproved_certa = $disapproveddata['total_disapproved_certa'];




echo view('templates/myheader.php');
?>
<div class="container-fluid">
    <div class="row">
        <div class="col-sm-12 mb-3">
            <div class="row">
                <div class="col-lg-3 col-md-6">
                    <div class="card overflow-hidden">
                        <div class="d-flex flex-row">
                            <div class="p-3 bg-info-subtle d-flex align-items-center">
                                <h3 class="text-info box mb-0">
                                <i class="ti ti-list"></i>
                                </h3>
                            </div>
                            <div class="p-3">
                                <h3 class="text-info mb-0 fs-9"><?=$total_transactions;?></h3>
                                <span>Total Projects</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="card overflow-hidden">
                        <div class="d-flex flex-row">
                            <div class="p-3 bg-warning-subtle d-flex align-items-center">
                                <h3 class="text-warning box mb-0">
                                <i class="ti ti-flag"></i>
                                </h3>
                            </div>
                            <div class="p-3">
                                <h3 class="text-warning mb-0 fs-9"><?=$total_pending;?></h3>
                                <span>Pending</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="card overflow-hidden">
                        <div class="d-flex flex-row">
                            <div class="p-3 bg-success-subtle d-flex align-items-center">
                                <h3 class="text-success box mb-0">
                                <i class="ti ti-check"></i>
                                </h3>
                            </div>
                            <div class="p-3">
                                <h3 class="text-success mb-0 fs-9"><?=$total_approved_certa;?></h3>
                                <span>Approved</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="card overflow-hidden">
                        <div class="d-flex flex-row">
                            <div class="p-3 bg-danger-subtle d-flex align-items-center">
                                <h3 class="text-danger box mb-0">
                                <i class="ti ti-x"></i>
                                </h3>
                            </div>
                            <div class="p-3">
                                <h3 class="text-danger mb-0 fs-9"><?=$total_disapproved_certa;?></h3>
                                <span>Disapproved</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-12">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">Requesting Office Approval</h4>
                    <p class="card-subtitle">List of pending transactions</p>
                    <table id="datatablesSimple" class="table table-bordered table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Serial No.</th>
                                <th>Particulars</th>
                                <th>Amount</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody class="align-middle">
                            <?php if(!empty($certifyadata)):
                                
                                foreach ($certifyadata as $data):
                                    $recid = $data['recid'];
                                    $particulars = $data['particulars'];
                                    $serialno = $data['serialno'];
                                    $amount = $data['amount'];
                            ?>
                            <tr>
                                <td class="text-center"><?=$serialno;?></td>
                                <td class="text-center"><?=$particulars;?></td>
                                <td class="text-center"><?= '₱'. number_format($amount,2);?></td>
                                <td class="text-center align-middle">
                                    <a class="text-info nav-icon-hover fs-6" href="myors?meaction=MAIN&recid=<?= $recid?>&action=certifya_appr_pending" title="View Transaction">
                                        <i class="ti ti-eye"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; endif;?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-12">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">Budget Division Approval</h4>
                    <p class="card-subtitle">List of pending transactions</p>
                    
                    <table id="datatablesSimple2" class="table table-bordered table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Particulars</th>
                                <th>Amount</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody class="align-middle">
                            <?php if(!empty($certifybdata)):
                                
                                foreach ($certifybdata as $data):
                                    $recid = $data['recid'];
                                    $particulars = $data['particulars'];
                                    $amount = $data['amount'];
                            ?>
                            <tr>
                                <td class="text-center"><?=$particulars;?></td>
                                <td class="text-center"><?= '₱'. number_format($amount,2);?></td>
                                <td class="text-center align-middle">
                                    <a class="text-info nav-icon-hover fs-6" title="View Transaction" href="myors?meaction=MAIN&recid=<?= $recid?>&action=certifyb_appr_pending">
                                        <i class="ti ti-eye"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; endif;?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-12">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">Approval History</h4>
                    <table id="datatablesSimple3" class="table table-bordered table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Particulars</th>
                                <th>Amount</th>
                                <th>Requesting Office Approval</th>
                                <th>Budget Division Approval</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody class="align-middle">
                            <?php if(!empty($transactiondata)):
                                $office_status="";
                                $budget_status="";
                                $remarks="";
                                $approver="";
                                $color="";
                                foreach ($transactiondata as $data):
                                    $recid = $data['recid'];
                                    $particulars = $data['particulars'];
                                    $amount = $data['amount'];
                                    $is_approved_certa = $data['is_approved_certa'];
                                    $is_disapproved_certa = $data['is_disapproved_certa'];
                                    $is_approved_certb = $data['is_approved_certb'];
                                    $is_disapproved_certb = $data['is_disapproved_certb'];
                                    $certa_remarks = $data['certa_remarks'];
                                    $certa_approver = $data['certa_approver'];
                                    $certb_remarks = $data['certb_remarks'];
                                    $certb_approver = $data['certb_approver'];

                                    if ($is_approved_certa == '1' && $is_disapproved_certa == '0') {
                                        $office_status = '<i class="ti ti-flag"></i>&nbsp;' . 'Approved';
                                        $color="text-success";
                                    }elseif($is_approved_certa == '0' && $is_disapproved_certa == '1'){
                                        $office_status = '<i class="ti ti-flag"></i>&nbsp;' . 'Disapproved';
                                        $color="text-danger";
                                    }else{
                                        $office_status = '<i class="ti ti-flag"></i>&nbsp;' . 'Pending';
                                        $color="text-primary";
                                    }

                                    if($is_approved_certa == '1' && $is_disapproved_certa == '0' && $is_approved_certb == '1' && $is_disapproved_certb == '0'){
                                        $budget_status = '<i class="ti ti-flag"></i>&nbsp;' . 'Approved';
                                        $budget_color="text-success";
                                    }elseif($is_approved_certa == '1' && $is_disapproved_certa == '0' && $is_approved_certb == '0' && $is_disapproved_certb == '1'){
                                        $budget_status = '<i class="ti ti-flag"></i>&nbsp;' . 'Disapproved';
                                        $budget_color="text-danger";
                                    }else{
                                        $budget_status = '<i class="ti ti-flag"></i>&nbsp;' . 'Pending';
                                        $budget_color="text-primary";
                                    }
                            ?>
                            <tr>
                                <td class="text-center"><?=$particulars;?></td>
                                <td class="text-center"><?= '₱'. number_format($amount,2);?></td>
                                <td class="text-center <?=$color;?>">
                                    <?=$office_status;?>
                                </td>
                                <td class="text-center <?=$budget_color;?>">
                                    <?=$budget_status;?>
                                </td>
                                <td class="text-center align-middle">
                                    <a class="text-info nav-icon-hover fs-6" title="View Transaction" href="myors?meaction=MAIN&recid=<?= $recid?>&action=transaction_viewing">
                                        <i class="ti ti-eye"></i>
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
</div>


<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="<?=base_url('assets/js/mysysapps.js');?>"></script>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(document).ready(function () {
        $('#datatablesSimple').DataTable({
            pageLength: 5,
            lengthChange: false,
            language: {
            search: "Search:"
            }
        });

        $('#datatablesSimple2').DataTable({
            pageLength: 5,
            lengthChange: false,
            language: {
            search: "Search:"
            }
        });

        $('#datatablesSimple3').DataTable({
            pageLength: 5,
            lengthChange: false,
            language: {
            search: "Search:"
            }
        });

        $('#approved_table').DataTable({
            pageLength: 5,
            lengthChange: false,
            language: {
            search: "Search:"
            }
        });

        $('#disapproved_table').DataTable({
            pageLength: 5,
            lengthChange: false,
            language: {
            search: "Search:"
            }
        });
  
    });
</script>
<?php
    echo view('templates/myfooter.php');
?>