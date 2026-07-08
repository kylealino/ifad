<?php
$this->request = \Config\Services::request();
$this->db = \Config\Database::connect();
$recid = $this->request->getPostGet('recid');

$full_name = "";
$division = "";
$username = "";
$hash_password = "";
$hash_value = "";


echo view('templates/myheader.php');
?>
<head>


</head>
<div class="container-fluid">
    <div class="row me-myua-outp-msg mx-0">
    </div>
    <input type="hidden" id="__siteurl" data-mesiteurl="<?=site_url();?>" />
    <div class="row mb-2 mt-0">
        <h4 class="fw-semibold mb-8">Account Settting</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a class="text-muted text-decoration-none" href="<?=site_url();?>"><i class="ti ti-home fs-5"></i></a>
            </li>
            <li class="breadcrumb-item" aria-current="page">Maintenance</li>
            <li class="breadcrumb-item" aria-current="page"><span class="form-label fw-bold">Account Setting</span></li>
            </ol>
        </nav>
    </div>


    <div class="row">
      <div class="col-sm-12">
      <div class="card">
                <div class="card-body">
                  <div class="row">
                    <div class="col-md-3">
                      <!-- Nav tabs -->
                      <div class="nav flex-column nav-pills mb-4 mb-md-0" id="v-pills-tab" role="tablist" aria-orientation="vertical">
                        <a class="nav-link active" id="v-pills-home-tab" data-bs-toggle="pill" href="#v-pills-home" role="tab" aria-controls="v-pills-home" aria-selected="true">
                          Details
                        </a>
                        <a class="nav-link" id="v-pills-profile-tab" data-bs-toggle="pill" href="#v-pills-profile" role="tab" aria-controls="v-pills-profile" aria-selected="false">
                          Module Access
                        </a>
                        <a class="nav-link" id="v-pills-list-tab" data-bs-toggle="pill" href="#v-pills-list" role="tab" aria-controls="v-pills-list" aria-selected="false">
                          User list
                        </a>
                      </div>
                      </div>
                          <div class="col-md-9">
                            <div class="tab-content" id="v-pills-tabContent">
                              <div class="tab-pane fade show active" id="v-pills-home" role="tabpanel" aria-labelledby="v-pills-home-tab">
                              <div class="row">
                              <div class="col-lg-6 d-flex align-items-stretch">
                                  <div class="card w-100 border position-relative overflow-hidden">
                                      <div class="card-body p-4">
                                          <h4 class="card-title">Change Profile</h4>
                                          <p class="card-subtitle mb-4">Change your profile picture from here</p>
                                          <div class="text-center">
                                              <img src="<?=base_url('assets/images/logos/fnrilogo.png')?>" alt="flexy-img" class="img-fluid rounded-circle" width="120" height="120">
                                              <div class="d-flex align-items-center justify-content-center my-4 gap-6">
                                                  <button class="btn btn-primary">Upload</button>
                                                  <button class="btn bg-danger-subtle text-danger">Reset</button>
                                              </div>
                                              <p class="mb-0">Allowed JPG, GIF or PNG. Max size of 800K</p>
                                          </div>
                                      </div>
                                  </div>
                              </div>
                              <div class="col-lg-6 d-flex align-items-stretch">
                                  <div class="card w-100 border position-relative overflow-hidden">
                                      <div class="card-body p-4">
                                          <h4 class="card-title">Change Password</h4>
                                          <p class="card-subtitle mb-4">To change your password please confirm here</p>
                                          <form>
                                              <div class="mb-3">
                                                  <label for="exampleInputPassword1" class="form-label">Current Password</label>
                                                  <input type="password" class="form-control" id="exampleInputPassword1" value="12345678910">
                                              </div>
                                              <div class="mb-3">
                                                  <label for="exampleInputPassword2" class="form-label">New Password</label>
                                                  <input type="password" class="form-control" id="exampleInputPassword2" value="12345678910">
                                              </div>
                                              <div>
                                                  <label for="exampleInputPassword3" class="form-label">Confirm Password</label>
                                                  <input type="password" class="form-control" id="exampleInputPassword3" value="12345678910">
                                              </div>
                                          </form>
                                      </div>
                                  </div>
                              </div>
                              <div class="col-12">
                              <div class="card w-100 border position-relative overflow-hidden mb-0">
                                  <div class="card-body p-4">
                                      <h4 class="card-title">Personal Details</h4>
                                      <p class="card-subtitle mb-4">To change your personal detail , edit and save from here</p>
                                      <form>
                                          <div class="row">
                                          <div class="col-lg-6">
                                              <div class="mb-3">
                                                  <label for="exampleInputtext" class="form-label">Your Name</label>
                                                  <input type="text" class="form-control" id="exampleInputtext" placeholder="Johnathan Doe">
                                              </div>
                                              <div class="mb-3">
                                                  <label class="form-label">Division</label>
                                                  <select class="form-select" id="division" aria-label="Default select example">
                                                      <option selected>Choose...</option>
                                                      <option value="OD">OD</option>
                                                      <option value="FAD">FAD</option>
                                                      <option value="NFRDD">NFRDD</option>
                                                      <option value="NAMD">NAMD</option>
                                                      <option value="TDSTSD">TDSTSD</option>
                                                      <option value="SLG">SLG</option>
                                                  </select>
                                                  </div>
                                                  <div class="mb-3">
                                                  <label for="exampleInputtext1" class="form-label">Email</label>
                                                  <input type="email" class="form-control" id="MyEmail" placeholder="sample@gmail.com">
                                              </div>
                                          </div>
                                          <div class="col-lg-6">
                                              <div class="mb-3">
                                                  <label for="MyUsername" class="form-label">Username</label>
                                                  <input type="text" class="form-control" id="MyUsername">
                                              </div>
                                              <div class="mb-3">
                                                  <label for="MyPassword" class="form-label">Password</label>
                                                  <input type="password" class="form-control" id="MyPassword" >
                                              </div>
                                          </div>
                                          <div class="col-12">
                                              <div>
                                                  <label for="MyAddress" class="form-label">Address</label>
                                                  <input type="text" class="form-control" id="MyAddress" placeholder="814 Howard Street, 120065, India">
                                              </div>
                                          </div>
                                          <div class="col-12">
                                              <div class="d-flex align-items-center justify-content-end mt-4 gap-6">
                                                  <button class="btn btn-primary">Save</button>
                                                  <button class="btn bg-danger-subtle text-danger">Cancel</button>
                                              </div>
                                          </div>
                                          </div>
                                      </form>
                                  </div>
                              </div>
                              </div>
                          </div>
                        </div>

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
<script src="<?=base_url('assets/js/maintenance/myua.js');?>"></script>
<script src="<?=base_url('assets/js/mysysapps.js');?>"></script>
<script>
    __mysys_myua_ent.__user_saving();
    $(document).ready(function () {
    $('#datatablesSimple').DataTable({
        pageLength: 5,
        lengthChange: false,
        language: {
            search: "Search:"
        },
        columnDefs: [
            { targets: '_all', className: 'text-center' } // Center all columns
        ]
    });

    });
</script>
<?php
    echo view('templates/myfooter.php');
?>


