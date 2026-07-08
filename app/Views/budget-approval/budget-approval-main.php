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
    `tbl_budget_hd`
WHERE `tagging` = 'For Approval'
");

$totaldata = $totalquery->getRowArray();
$total_transactions = $totaldata['total_transactions'];

$pendingquery = $this->db->query("
SELECT
    COALESCE(COUNT(recid), 0) AS total_pending
FROM
    `tbl_budget_hd`
WHERE 
    `is_pending` = '1' and `tagging` = 'For Approval'
");

$pendingdata = $pendingquery->getRowArray();
$total_pending = $pendingdata['total_pending'];

$approvedquery = $this->db->query("
SELECT
    COALESCE(COUNT(recid), 0) AS total_approved
FROM
    `tbl_budget_hd`
WHERE 
    `is_approved` = '1'
");

$approveddata = $approvedquery->getRowArray();
$total_approved = $approveddata['total_approved'];

$disapprovedquery = $this->db->query("
SELECT
    COALESCE(COUNT(recid), 0) AS total_disapproved
FROM
    `tbl_budget_hd`
WHERE 
    `is_disapproved` = '1'
");

$disapproveddata = $disapprovedquery->getRowArray();
$total_disapproved = $disapproveddata['total_disapproved'];




echo view('templates/myheader.php');
?>
<div class="container-fluid">
    <div class="row">
        <!-- <div class="col-lg-12 mb-3">
            <div class="card-group">
                <div class="card" style="min-height: 50px;">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                        <h4 class="card-title text-primary">Total Projects</h4>
                        <div class="ms-auto">
                            <span class="btn round-48 fs-7 rounded-circle bg-primary-subtle d-flex align-items-center justify-content-center">
                            <i class="ti ti-list"></i>
                            </span>
                        </div>
                        </div>
                        <div class="mt-3 text-center">
                            <h2 class="fs-8 mb-0"><?=$total_transactions;?></h2>
                        </div>
                    </div>
                </div>
                <div class="card" style="min-height: 50px;">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                        <h4 class="card-title text-info">For Approval</h4>
                        <div class="ms-auto">
                            <span class="btn round-48 fs-7 rounded-circle bg-info-subtle d-flex align-items-center justify-content-center">
                            <i class="ti ti-bookmark"></i>
                            </span>
                        </div>
                        </div>
                        <div class="mt-3 text-center">
                        <h2 class="fs-8 mb-0"><?=$total_pending;?></h2>
                        </div>
                    </div>
                </div>
                <div class="card" style="min-height: 50px;">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                        <h4 class="card-title text-success">Approved</h4>
                        <div class="ms-auto">
                            <span class="btn round-48 fs-7 rounded-circle bg-success-subtle d-flex align-items-center justify-content-center">
                            <i class="ti ti-check"></i>
                            </span>
                        </div>
                        </div>
                        <div class="mt-3 text-center">
                        <h2 class="fs-8 mb-0"><?=$total_approved;?></h2>
                        </div>
                    </div>
                </div>
                <div class="card" style="min-height: 50px;">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                        <h4 class="card-title text-danger">Disapproved</h4>
                        <div class="ms-auto">
                            <span class="btn round-48 fs-7 rounded-circle bg-danger-subtle d-flex align-items-center justify-content-center">
                            <i class="ti ti-x"></i>
                            </span>
                        </div>
                        </div>
                        <div class="mt-3 text-center">
                        <h2 class="fs-8 mb-0"><?=$total_disapproved;?></h2>
                        </div>
                    </div>
                </div>
            </div>
        </div> -->

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
                                <h3 class="text-success mb-0 fs-9"><?=$total_approved;?></h3>
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
                                <h3 class="text-danger mb-0 fs-9"><?=$total_disapproved;?></h3>
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
                    <h4 class="card-title">For Approval Transactions</h4>
                    <p class="card-subtitle">List of pending transactions</p>
                    
                    <table id="datatablesSimple" class="table table-bordered table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Action</th>
                                <th>Project Title</th>
                                <th>Budget</th>
                            </tr>
                        </thead>
                        <tbody class="align-middle">
                            <?php if(!empty($pendingbudgetdata)):
                                
                                foreach ($pendingbudgetdata as $data):
                                    $recid = $data['recid'];
                                    $project_title = $data['project_title'];
                                    $approved_budget = $data['approved_budget'];
                            ?>
                            <tr>
                                <td class="text-center align-middle">
                                    <a class="text-info nav-icon-hover fs-6" href="mybudgetallotment?meaction=MAIN&recid=<?= $recid?>&action=appr_pending">
                                        <i class="ti ti-eye"></i>
                                    </a>
                                </td>
                                <td class="text-center"><?=$project_title;?></td>
                                <td class="text-center"><?= '₱'. number_format($approved_budget,2);?></td>
                            </tr>
                            <?php endforeach; endif;?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-12">
            <div class="row">
                <div class="col-sm-6">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="card-title text-success">Approved Transactions</h4>
                            <p class="card-subtitle">List of approved transactions</p>
                            
                            <table id="approved_table" class="table table-bordered table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>Action</th>
                                        <th>Project Title</th>
                                        <th>Budget</th>
                                    </tr>
                                </thead>
                                <tbody class="align-middle">
                                    <?php if(!empty($approvedbudgetdata)):
                                        
                                        foreach ($approvedbudgetdata as $data):
                                            $recid = $data['recid'];
                                            $project_title = $data['project_title'];
                                            $approved_budget = $data['approved_budget'];
                                    ?>
                                    <tr>
                                        <td class="text-center align-middle">
                                            <a class="text-info nav-icon-hover fs-6" href="mybudgetallotment?meaction=MAIN&recid=<?= $recid?>&action=appr_approved">
                                                <i class="ti ti-eye"></i>
                                            </a>
                                        </td>
                                        <td class="text-center"><?=$project_title;?></td>
                                        <td class="text-center"><?= '₱'. number_format($approved_budget,2);?></td>
                                    </tr>
                                    <?php endforeach; endif;?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="card-title text-danger">Disapproved Transactions</h4>
                            <p class="card-subtitle">List of disapproved transactions</p>
                            
                            <table id="disapproved_table" class="table table-bordered table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>Action</th>
                                        <th>Project Title</th>
                                        <th>Budget</th>
                                    </tr>
                                </thead>
                                <tbody class="align-middle">
                                    <?php if(!empty($disapprovedbudgetdata)):
                                        
                                        foreach ($disapprovedbudgetdata as $data):
                                            $recid = $data['recid'];
                                            $project_title = $data['project_title'];
                                            $approved_budget = $data['approved_budget'];
                                    ?>
                                    <tr>
                                        <td class="text-center align-middle">
                                            <a class="text-info nav-icon-hover fs-6" href="mybudgetallotment?meaction=MAIN&recid=<?= $recid?>&action=appr_disapproved">
                                                <i class="ti ti-eye"></i>
                                            </a>
                                        </td>
                                        <td class="text-center"><?=$project_title;?></td>
                                        <td class="text-center"><?= '₱'. number_format($approved_budget,2);?></td>
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
</div>


<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="<?=base_url('assets/js/mysysapps.js');?>"></script>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(document).ready(function () {
        $('#datatablesSimple').DataTable({
            pageLength: 10,
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