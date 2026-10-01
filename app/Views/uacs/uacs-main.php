<?php
$classes = [
    'Personnel Services' => ['ps', 'PS', 'tone-teal', 'ti-users'],
    'Maintenance and Other Operating Expenses' => ['mooe', 'MOOE', 'tone-indigo', 'ti-tools'],
    'Capital Outlay' => ['co', 'CO', 'tone-amber', 'ti-building'],
];
echo view('templates/myheader.php');
?>
<div class="container-fluid">
    <input type="hidden" id="__siteurl" data-mesiteurl="<?=site_url();?>" />
    <div class="row me-outp-msg mx-0"></div>

    <div class="fods-page-head">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="<?=site_url();?>"><i class="ti ti-home"></i></a></li>
                    <li class="breadcrumb-item"><a href="#">Maintenance</a></li>
                    <li class="breadcrumb-item active" aria-current="page">UACS</li>
                </ol>
            </nav>
            <h1 class="fods-page-title">UACS Codes</h1>
            <p class="fods-page-sub">Unified Accounts Code Structure — object and sub-object codes by allotment class.</p>
        </div>
        <?php if ($perms['add']): ?>
        <div class="fods-page-actions d-flex">
            <button type="button" class="btn btn-fods btn-sm px-3 py-2" id="uacsTableAdd"><i class="ti ti-plus me-1"></i> Add UACS Code</button>
        </div>
        <?php endif; ?>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <button type="button" class="fods-stat" data-table="uacsTable" data-filter="all">
                <div class="fods-stat-icon tone-slate"><i class="ti ti-list-numbers"></i></div>
                <div><div class="fods-stat-value"><?= number_format(count($uacsdata)) ?></div><div class="fods-stat-label">UACS codes</div></div>
            </button>
        </div>
        <?php foreach ($classes as $name => [$key, $short, $tone, $icon]): ?>
        <div class="col-6 col-lg-3">
            <button type="button" class="fods-stat" data-table="uacsTable" data-filter="<?= $key ?>">
                <div class="fods-stat-icon <?= $tone ?>"><i class="ti <?= $icon ?>"></i></div>
                <div><div class="fods-stat-value"><?= number_format($byClass[$name] ?? 0) ?></div><div class="fods-stat-label"><?= esc($name) ?></div></div>
            </button>
        </div>
        <?php endforeach; ?>
    </div>

    <div class="fods-card">
        <div class="fods-card-head">
            <div class="fods-filter" id="uacsTableFilter">
                <button type="button" class="active" data-filter="all">All</button>
                <?php foreach ($classes as $name => [$key, $short]): ?>
                    <button type="button" data-filter="<?= $key ?>"><?= $short ?></button>
                <?php endforeach; ?>
            </div>
            <div class="fods-search">
                <i class="ti ti-search"></i>
                <input type="search" class="form-control form-control-sm" id="uacsTableSearch" placeholder="Search code or object...">
            </div>
        </div>
        <div class="fods-card-body fods-dt">
            <table id="uacsTable" class="table fods-table fods-table-stack w-100">
                <thead>
                    <tr>
                        <th>UACS code</th>
                        <th class="d-none d-md-table-cell">Object code</th>
                        <th>Allotment class</th>
                        <th class="d-none d-lg-table-cell">Added</th>
                        <th class="text-end"></th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>

<!-- Add / edit -->
<div class="modal fade fods-modal" id="uacsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-fullscreen-sm-down">
        <div class="modal-content">
            <form id="uacsForm" novalidate>
                <div class="modal-header">
                    <div class="d-flex align-items-center">
                        <div class="fods-modal-icon tone-teal"><i class="ti ti-list-numbers"></i></div>
                        <div>
                            <h5 class="modal-title" id="uacsModalTitle">Add UACS Code</h5>
                            <div class="fods-modal-sub" id="uacsModalSub">An object / sub-object code and its UACS code.</div>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="recid">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label" for="allotment_class">Allotment class</label>
                            <select class="form-select" id="allotment_class" name="allotment_class" required>
                                <option value="">Choose…</option>
                                <?php foreach ($classes as $name => $c): ?>
                                    <option value="<?= esc($name, 'attr') ?>"><?= esc($name) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <div class="invalid-feedback">Choose an allotment class.</div>
                        </div>
                        <div class="col-12">
                            <label class="form-label" for="object_code">Object code</label>
                            <input type="text" class="form-control" id="object_code" name="object_code" required>
                            <div class="invalid-feedback">Object code is required.</div>
                        </div>
                        <div class="col-12">
                            <label class="form-label" for="sub_object_code">Sub-object code</label>
                            <input type="text" class="form-control" id="sub_object_code" name="sub_object_code" required>
                            <div class="invalid-feedback">Sub-object code is required.</div>
                        </div>
                        <div class="col-12">
                            <label class="form-label" for="uacs_code">UACS code</label>
                            <input type="text" class="form-control" id="uacs_code" name="uacs_code" placeholder="e.g. 50203010-00" required>
                            <div class="invalid-feedback">UACS code is required.</div>
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
    var __uacs_data = <?= json_encode($uacsdata, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
    var __uacs_perms = <?= json_encode($perms) ?>;
</script>
<script src="<?=base_url('assets/js/maintenance/myuacs.js?v=3');?>"></script>
<?php echo view('templates/myfooter.php'); ?>
