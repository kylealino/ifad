<?php
$this->request = \Config\Services::request();
$this->mybudgetallotment = model('App\Models\MyBudgetAllotmentModel');
$this->db = \Config\Database::connect();
$recid = $this->request->getPostGet('recid');

$product_code = '';
$product_desc = '';
$uom = '';
$price = '';
$quantity = '';
$remarks = '';

if(!empty($recid) || !is_null($recid)) { 
    $query = $this->db->query("
    SELECT
        `recid`,
        `product_code`,
        `product_desc`,
        `uom`,
        `price`,
        `quantity`,
        `remarks`
    FROM
        `mst_products`
    WHERE 
        `recid` = '$recid'"
    );

    $data = $query->getRowArray();
    $product_code = $data['product_code'];
    $product_desc = $data['product_desc'];
    $uom = $data['uom'];
    $price = $data['price'];
    $quantity = $data['quantity'];
    $remarks = $data['remarks'];
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
        <h4 class="fw-semibold mb-8">Supplies</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a class="text-muted text-decoration-none" href="<?=site_url();?>"><i class="ti ti-home fs-5"></i></a>
            </li>
            <li class="breadcrumb-item" aria-current="page">Maintenance</li>
            <li class="breadcrumb-item" aria-current="page"><span class="form-label fw-bold">Supplies</span></li>
            </ol>
        </nav>
    </div>
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="row myproducts-outp-msg mx-0">

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
                    <form action="<?=site_url();?>myproducts?meaction=PRODUCTS-SAVE" method="post" class="myproducts-validation">
                        <div class="row mb-2">
                            <div class="col-sm-6">
                                <div class="row mb-2">
                                    <div class="col-sm-4">
                                        <span class="fw-bold">Product Code:</span>
                                    </div>
                                    <div class="col-sm-8">
                                        <input type="hidden" id="recid" name="recid" value="<?=$recid;?>" class="form-control form-control-sm"/>
                                        <input type="text" id="product_code" name="product_code" value="<?=$product_code;?>" class="form-control form-control-sm"/>
                                    </div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-sm-4">
                                        <span class="fw-bold">UOM:</span>
                                    </div>
                                    <div class="col-sm-8">
                                        <input type="text" id="uom" name="uom" value="<?=$uom;?>" class="form-control form-control-sm"/>
                                    </div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-sm-4">
                                        <span class="fw-bold">Price:</span>
                                    </div>
                                    <div class="col-sm-8">
                                        <input type="number" id="price" name="price" value="<?=$price;?>" class="form-control form-control-sm"/>
                                    </div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-sm-4">
                                        <span class="fw-bold">Quantity:</span>
                                    </div>
                                    <div class="col-sm-8">
                                        <input type="number" id="quantity" name="quantity" value="<?=$quantity;?>" class="form-control form-control-sm"/>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="row mb-2">
                                    <div class="col-sm-4">
                                        <span class="fw-bold">Product Description:</span>
                                    </div>
                                    <div class="col-sm-8">
                                        <textarea name="product_desc" id="product_desc" placeholder="" rows="3" class="form-control form-control-sm"><?=$product_desc;?></textarea>
                                    </div>
                                </div>
                            <div class="row mb-2">
                                    <div class="col-sm-4">
                                        <span class="fw-bold">Remarks:</span>
                                    </div>
                                    <div class="col-sm-8">
                                        <textarea name="remarks" id="remarks" placeholder="" rows="3" class="form-control form-control-sm"><?=$remarks;?></textarea>
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
                                <th>Action</th>
                                <th>Product Code</th>
                                <th>Description</th>
                                <th>UOM</th>
                                <th>Price</th>
                                <th>Quantity</th>
                                <th>Remarks</th>
                            </tr>
                        </thead>
                        <tbody class="align-middle">
                            <?php if(!empty($productsdata)):
                                foreach ($productsdata as $data):
                                    $dt_recid = $data['recid'];
                                    $product_code = $data['product_code'];
                                    $product_desc = $data['product_desc'];
                                    $uom = $data['uom'];
                                    $price = $data['price'];
                                    $quantity = $data['quantity'];
                                    $remarks = $data['remarks'];
                            ?>
                            <tr>
                                <td class="text-center align-middle">
                                    <div class="d-flex justify-content-center gap-2">
                                        <a class="text-info nav-icon-hover fs-6" 
                                        href="myproducts?meaction=MAIN&recid=<?= $dt_recid ?>" 
                                        title="Edit Transaction">
                                        <i class="ti ti-edit"></i>
                                        </a>
                                    </div>
                                </td>
                                <td class="text-center"><?=$product_code;?></td>
                                <td class="text-center"><?=$product_desc;?></td>
                                <td class="text-center"><?=$uom;?></td>
                                <td class="text-center"><?= 'P'. number_format($price,2);?></td>
                                <td class="text-center"><?=$quantity;?></td>
                                <td class="text-center"><?=$remarks;?></td>
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
<!-- Modal -->
<div class="modal fade" id="pdfModal" tabindex="-1" aria-labelledby="pdfModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="pdfModalLabel">PRODUCTS Preview</h5>
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

<script src="<?=base_url('assets/js/products/myproducts.js?v=1');?>"></script>
<script src="<?=base_url('assets/js/mysysapps.js');?>"></script>


<script>
    __mysys_products_ent.__products_saving();
</script>



<?php
    echo view('templates/myfooter.php');
?>


