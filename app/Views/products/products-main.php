<?php echo view('templates/myheader.php'); ?>
<div class="container-fluid">
    <input type="hidden" id="__siteurl" data-mesiteurl="<?=site_url();?>" />
    <div class="row me-outp-msg mx-0"></div>

    <div class="fods-page-head">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="<?=site_url();?>"><i class="ti ti-home"></i></a></li>
                    <li class="breadcrumb-item"><a href="#">Maintenance</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Supplies</li>
                </ol>
            </nav>
            <h1 class="fods-page-title">Supplies</h1>
            <p class="fods-page-sub">Supply items used in PPMP, purchase requests and abstracts — with unit, price and stock.</p>
        </div>
        <?php if ($perms['add']): ?>
        <div class="fods-page-actions d-flex">
            <button type="button" class="btn btn-fods btn-sm px-3 py-2" id="productsTableAdd"><i class="ti ti-plus me-1"></i> Add Item</button>
        </div>
        <?php endif; ?>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <button type="button" class="fods-stat" data-table="productsTable" data-filter="all">
                <div class="fods-stat-icon tone-teal"><i class="ti ti-package"></i></div>
                <div><div class="fods-stat-value"><?= number_format($stats['total']) ?></div><div class="fods-stat-label">Supply items</div></div>
            </button>
        </div>
        <div class="col-6 col-lg-3">
            <button type="button" class="fods-stat" data-table="productsTable" data-filter="out">
                <div class="fods-stat-icon tone-amber"><i class="ti ti-package-off"></i></div>
                <div><div class="fods-stat-value"><?= number_format($stats['out_of_stock']) ?></div><div class="fods-stat-label">Out of stock</div></div>
            </button>
        </div>
        <div class="col-6 col-lg-3">
            <div class="fods-stat">
                <div class="fods-stat-icon tone-indigo"><i class="ti ti-ruler-measure"></i></div>
                <div><div class="fods-stat-value"><?= number_format($stats['uoms']) ?></div><div class="fods-stat-label">Units of measure</div></div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="fods-stat">
                <div class="fods-stat-icon tone-green"><i class="ti ti-coin"></i></div>
                <div><div class="fods-stat-value" title="₱<?= number_format($stats['value'], 2) ?>">₱<?= number_format($stats['value'] >= 1e6 ? $stats['value'] / 1e6 : $stats['value'] / 1e3, 1) . ($stats['value'] >= 1e6 ? 'M' : 'K') ?></div><div class="fods-stat-label">Stock value</div></div>
            </div>
        </div>
    </div>

    <div class="fods-card">
        <div class="fods-card-head">
            <div class="fods-filter" id="productsTableFilter">
                <button type="button" class="active" data-filter="all">All</button>
                <button type="button" data-filter="in">In stock</button>
                <button type="button" data-filter="out">Out of stock</button>
            </div>
            <div class="fods-search">
                <i class="ti ti-search"></i>
                <input type="search" class="form-control form-control-sm" id="productsTableSearch" placeholder="Search code, description...">
            </div>
        </div>
        <div class="fods-card-body fods-dt">
            <table id="productsTable" class="table fods-table fods-table-stack w-100">
                <thead>
                    <tr>
                        <th>Item</th>
                        <th class="d-none d-md-table-cell">Unit</th>
                        <th class="text-end">Unit price</th>
                        <th class="text-end">Stock</th>
                        <th class="d-none d-xl-table-cell">Remarks</th>
                        <th class="text-end"></th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>

<!-- Add / edit -->
<div class="modal fade fods-modal" id="productsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable modal-fullscreen-sm-down">
        <div class="modal-content">
            <form id="productsForm" novalidate>
                <div class="modal-header">
                    <div class="d-flex align-items-center">
                        <div class="fods-modal-icon tone-teal"><i class="ti ti-package"></i></div>
                        <div>
                            <h5 class="modal-title" id="productsModalTitle">Add Item</h5>
                            <div class="fods-modal-sub" id="productsModalSub">A supply item and its unit price.</div>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="recid">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label" for="product_code">Item code</label>
                            <input type="text" class="form-control" id="product_code" name="product_code">
                        </div>
                        <div class="col-md-8">
                            <label class="form-label" for="product_desc">Description</label>
                            <input type="text" class="form-control" id="product_desc" name="product_desc" required>
                            <div class="invalid-feedback">Description is required.</div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="uom">Unit of measure</label>
                            <input type="text" class="form-control" id="uom" name="uom" list="uomList" placeholder="e.g. piece, ream, box">
                            <datalist id="uomList">
                                <?php foreach ($uoms as $u): ?><option value="<?= esc($u, 'attr') ?>"><?php endforeach; ?>
                            </datalist>
                        </div>
                        <div class="col-md-4 col-6">
                            <label class="form-label" for="price">Unit price (₱)</label>
                            <input type="number" class="form-control" id="price" name="price" step="0.01" min="0" data-default="0">
                        </div>
                        <div class="col-md-4 col-6">
                            <label class="form-label" for="quantity">Stock quantity</label>
                            <input type="number" class="form-control" id="quantity" name="quantity" step="1" min="0" data-default="0">
                        </div>
                        <div class="col-12">
                            <label class="form-label" for="remarks">Remarks</label>
                            <textarea class="form-control" id="remarks" name="remarks" rows="2"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-fods-ghost px-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-sm btn-fods px-4" id="submitBtn">Create</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    var __products_data = <?= json_encode($productsdata, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
    var __products_perms = <?= json_encode($perms) ?>;
</script>
<script src="<?=base_url('assets/js/products/myproducts.js?v=3');?>"></script>
<?php echo view('templates/myfooter.php'); ?>
