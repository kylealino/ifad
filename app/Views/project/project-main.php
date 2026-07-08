<?php
$this->request = \Config\Services::request();
$this->db = \Config\Database::connect();
$recid = $this->request->getPostGet('recid');

$project_title = "";
$responsibility_code = "";
$project_leader = "";
$mfopaps_code = "";
$fund_cluster_code = "";
$fundcluster_id = "";
$division_name = "";
$division_id = "";
$counter = 1;

if(!empty($recid) || !is_null($recid)) { 

    $query = $this->db->query("
    SELECT
        a.`recid`,
        a.`fundcluster_id`,
        b.`fund_cluster_code`,
        a.`division_id`,
        c.`division_name`,
        a.`responsibility_code`,
        a.`project_leader`,
        a.`project_title`,
        a.`mfopaps_code`
    FROM
        `tbl_reference_project` a
    JOIN
        `tbl_fundcluster`b
    ON 
        a.fundcluster_id = b.`recid`
    JOIN
        `tbl_division` c
    ON
        a.`division_id` = c.recid
    WHERE 
        a.`recid` = '$recid'"
    );

    $data = $query->getRowArray();
    $project_title = $data['project_title'];
    $fundcluster_id = $data['fundcluster_id'];
    $fund_cluster_code = $data['fund_cluster_code'];
    $division_name = $data['division_name'];
    $division_id = $data['division_id'];
    $responsibility_code = $data['responsibility_code'];
    $project_leader = $data['project_leader'];
    $mfopaps_code = $data['mfopaps_code'];

}
echo view('templates/myheader.php');
?>

<div class="container-fluid">
    <div class="row me-mypayee-outp-msg mx-0">
    </div>
    <input type="hidden" id="__siteurl" data-mesiteurl="<?=site_url();?>" />
    <div class="row mb-2 mt-0">
        <h4 class="fw-semibold mb-8">Projects</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a class="text-muted text-decoration-none" href="<?=site_url();?>"><i class="ti ti-home fs-5"></i></a>
            </li>
            <li class="breadcrumb-item" aria-current="page">Maintenance</li>
            <li class="breadcrumb-item" aria-current="page"><span class="form-label fw-bold">Projects</span></li>
            </ol>
        </nav>
    </div>
    <div class="card">
        <div class="card-header bg-info p-1">
            <div class="row">
                <div class="col-sm-6 d-flex align-items-center text-start">
                    <h6 class="mb-0 lh-base px-3 text-white fw-semibold d-flex align-items-center">
                        <i class="ti ti-pencil fs-5 me-1"></i>
                        <span class="pt-1">Entry</span>
                    </h6>
                </div>
                <div class="col-sm-6 text-end ">
                    <!-- <button type="button" id="btn_delete" name="btn_delete" class="btn_delete btn btn-sm btn-danger mx-3">
                        Remove Payee
                    </button> -->
                </div>
            </div>
		</div>						
        <div class="card-body p-0 px-4 py-2 my-2">
            <form action="<?=site_url();?>myproject?meaction=MAIN-SAVE" method="post" class="myproject-validation">
                <div class="row">
                    <div class="col-sm-12 mb-2">
                        <div class="row">
                            <div class="col-sm-2">
                                <span>Project Title:</span>
                            </div>
                            <div class="col-sm-10">
                                <textarea name="project_title" id="project_title" placeholder="" rows="4" class="form-control form-control-sm"><?=$project_title;?></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="row mb-2">
                            <div class="col-sm-4">
                                <span>Fund Cluster:</span>
                            </div>
                            <div class="col-sm-8">
                                <?php if(!empty($recid)):?>
                                    <select name="selFundcluster" id="selFundcluster" class="form-control select2 form-select-sm show-tick">
                                        <option selected value="<?=$fund_cluster_code;?>"><?=$fund_cluster_code;?></option>
                                        <?php foreach($fundclusterdata as $data): ?>
                                            <option 
                                                value="<?= $data['fund_cluster_code'] ?>"
                                                data-fund="<?= $data['fundcluster_id'] ?>"
                                            >
                                                <?= $data['fund_cluster_code'] ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <input type="hidden" id="fundcluster_id" name="fundcluster_id" value="<?=$fundcluster_id;?>" class="form-control form-control-sm" readonly />
                                <?php else:?>
                                    <select name="selFundcluster" id="selFundcluster" class="form-control select2 form-select-sm show-tick">
                                        <option selected value="">Choose...</option>
                                        <?php foreach($fundclusterdata as $data): ?>
                                            <option 
                                                value="<?= $data['fund_cluster_code'] ?>"
                                                data-fund="<?= $data['fundcluster_id'] ?>"
                                            >
                                                <?= $data['fund_cluster_code'] ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <input type="hidden" id="fundcluster_id" name="fundcluster_id" value="" class="form-control form-control-sm" readonly />
                                <?php endif;?>
                            </div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-sm-4">
                                <span>Division:</span>
                            </div>
                            <div class="col-sm-8">
                                <?php if(!empty($recid)):?>
                                    <select name="selDivision" id="selDivision" class="form-control select2 form-select-sm show-tick">
                                        <option selected value="<?=$division_name;?>"><?=$division_name;?></option>
                                        <?php foreach($divisiondata as $data): ?>
                                            <option 
                                                value="<?= $data['division_name'] ?>"
                                                data-division="<?= $data['division_id'] ?>"
                                            >
                                                <?= $data['division_name'] ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <input type="hidden" id="division_id" name="division_id" value="<?=$division_id;?>" class="form-control form-control-sm" readonly />
                                <?php else:?>
                                    <select name="selDivision" id="selDivision" class="form-control select2 form-select-sm show-tick">
                                        <option selected value="">Choose...</option>
                                        <?php foreach($divisiondata as $data): ?>
                                            <option 
                                                value="<?= $data['division_name'] ?>"
                                                data-division="<?= $data['division_id'] ?>"
                                            >
                                                <?= $data['division_name'] ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <input type="hidden" id="division_id" name="division_id" value="" class="form-control form-control-sm" readonly />
                                <?php endif;?>
                            </div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-sm-4">
                                <span>Responsibility Code:</span>
                            </div>
                            <div class="col-sm-8">
                                <input type="hidden" class="form-control form-control-sm" id="recid" name="recid" value="<?=$recid;?>"/>
                                <input type="text" id="responsibility_code" name="responsibility_code" value="<?=$responsibility_code;?>" class="form-control form-control-sm" />
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="row mb-2">
                            <div class="col-sm-4">
                                <span>MFO/PAPS:</span>
                            </div>
                            <div class="col-sm-8">
                                <?php if(!empty($recid)):?>
                                    <select name="mfopaps_code" id="mfopaps_code" class="form-control select2 form-select-sm show-tick">
                                        <option selected value="<?=$mfopaps_code;?>"><?=$mfopaps_code;?></option>
                                        <?php foreach($mfopapsdata as $data): ?>
                                            <option 
                                                value="<?= $data['mfopaps_code'] ?>"
                                            >
                                                <?= $data['mfopaps_code'] ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                <?php else:?>
                                    <select name="mfopaps_code" id="mfopaps_code" class="form-control select2 form-select-sm show-tick">
                                        <option selected value="">Choose...</option>
                                        <?php foreach($mfopapsdata as $data): ?>
                                            <option 
                                                value="<?= $data['mfopaps_code'] ?>"
                                            >
                                                <?= $data['mfopaps_code'] ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                <?php endif;?>
                            </div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-sm-4">
                                <span>Project Leader:</span>
                            </div>
                            <div class="col-sm-8">
                                <input type="text" id="project_leader" name="project_leader" value="<?=$project_leader;?>" class="form-control form-control-sm" />
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row mb-2">  
                    <div class="col-sm-12 text-end">
                        <button type="submit" id="submitBtn" class="btn bg-<?= empty($recid) ? 'success' : 'info' ?>-subtle text-<?= empty($recid) ? 'success' : 'info' ?> btn-sm rounded-pill "><i class="ti ti-brand-doctrine mt-1 fs-4 me-1"></i>
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
                        <th>Project Title</th>
                        <th>Responsibility Code</th>
                        <th>Fund Cluster</th>
                        <th>Division</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(!empty($projectdata)):
                        
                        foreach ($projectdata as $data):
                            $recid = $data['recid'];
                            $project_title = $data['project_title'];
                            $responsibility_code = $data['responsibility_code'];
                            $fund_cluster_code = $data['fund_cluster_code'];
                            $division_name = $data['division_name'];
                    ?>
                    <tr>
                        <td class="text-center align-middle">
                            <a class="text-info nav-icon-hover fs-6" href="myproject?meaction=MAIN&recid=<?= $recid ?>">
                                <i class="ti ti-eye" aria-hidden="true"></i>
                            </a>
                        </td>
                        <td class="text-center"><?=$project_title;?></td>
                        <td class="text-center"><?=$responsibility_code;?></td>
                        <td class="text-center"><?=$fund_cluster_code;?></td>
                        <td class="text-center"><?=$division_name;?></td>
                    </tr>
                    <?php endforeach; endif;?>
                </tbody>
            </table>
        </div>
    </div>
    <div class="row project-outp-msg">
	</div>
</div>
<!-- Delete Confirmation Modal -->
<div class="modal fade" id="confirmDeleteModal" tabindex="-1" aria-labelledby="confirmDeleteModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-secondary-subtle text-white">
        <h5 class="modal-title" id="confirmDeleteModalLabel">Confirm Delete</h5>
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


<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="<?=base_url('assets/js/maintenance/myproject.js?v=1');?>"></script>
<script src="<?=base_url('assets/js/mysysapps.js');?>"></script>
<script>
    __mysys_project_ent.__project_saving();
    $(document).ready(function () {
        $('#datatablesSimple').DataTable({
            pageLength: 5,
            lengthChange: false,
            language: {
            search: "Search:"
            }
        });
    });

    $(document).on('change', '#selFundcluster', function() {
        var selected = $(this).find('option:selected');

        // Extract data from selected option
        var fund = selected.data('fund') || '';
        // Set the values into inputs
        $('#fundcluster_id').val(fund);
    });

    $(document).on('change', '#selDivision', function() {
        var selected = $(this).find('option:selected');

        // Extract data from selected option
        var division = selected.data('division') || '';
        // Set the values into inputs
        $('#division_id').val(division);
    });

</script>

<?php
    echo view('templates/myfooter.php');
?>


