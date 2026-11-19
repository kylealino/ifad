<?php echo view('templates/myheader.php'); ?>
<head>
<style>
.map-module { border:1px solid var(--fods-border); border-radius:12px; background:#fff; }
.map-module + .map-module { margin-top:.75rem; }
.map-module-head { display:flex; align-items:center; justify-content:space-between; padding:.6rem .9rem; border-bottom:1px solid var(--fods-border); font-weight:600; color:var(--fods-text); font-size:.875rem; }
.map-module-count { font-size:.72rem; color:var(--fods-muted); background:#f1f5f9; border-radius:999px; padding:.1rem .5rem; font-weight:500; }
.map-module.has-selection { border-color:#bfe0e0; }
.map-module.has-selection .map-module-count { background:var(--fods-teal-soft); color:var(--fods-teal); }
.map-box { padding:.55rem .9rem; }
.map-box + .map-box { border-top:1px solid #f1f4f8; }
.map-box .form-check-label { font-size:.85rem; color:#334155; }
.map-box-fields { margin-top:.45rem; padding-left:1.5rem; }
.map-box-fields.map-box-nofields { display:none !important; }
.map-box-note { font-size:.75rem; color:var(--fods-muted); }
.map-signs { display:flex; flex-wrap:wrap; gap:.3rem; }
</style>
</head>
<div class="container-fluid">
    <input type="hidden" id="__siteurl" data-mesiteurl="<?=site_url();?>" />
    <div class="row me-outp-msg mx-0"></div>

    <div class="fods-page-head">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="<?=site_url();?>"><i class="ti ti-home"></i></a></li>
                    <li class="breadcrumb-item"><a href="#">Library</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Approver Mapping</li>
                </ol>
            </nav>
            <h1 class="fods-page-title">Approver Mapping</h1>
            <p class="fods-page-sub">Who signs each box of each module. Changes apply to records saved from now on.</p>
        </div>
        <?php if ($perms['add']): ?>
        <div class="fods-page-actions d-flex">
            <button type="button" class="btn btn-fods btn-sm px-3 py-2" id="mapTableAdd"><i class="ti ti-plus me-1"></i> Add Approver</button>
        </div>
        <?php endif; ?>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <button type="button" class="fods-stat" data-table="mapTable" data-filter="all">
                <div class="fods-stat-icon tone-teal"><i class="ti ti-signature"></i></div>
                <div><div class="fods-stat-value"><?= number_format($stats['approvers']) ?></div><div class="fods-stat-label">Active approvers</div></div>
            </button>
        </div>
        <div class="col-6 col-lg-3">
            <button type="button" class="fods-stat" data-table="mapTable" data-filter="all">
                <div class="fods-stat-icon tone-green"><i class="ti ti-checks"></i></div>
                <div><div class="fods-stat-value"><?= number_format($stats['boxes']) ?> / <?= number_format($stats['box_total']) ?></div><div class="fods-stat-label">Boxes set up</div></div>
            </button>
        </div>
        <div class="col-6 col-lg-3">
            <button type="button" class="fods-stat" data-table="mapTable" data-filter="empty">
                <div class="fods-stat-icon tone-amber"><i class="ti ti-alert-triangle"></i></div>
                <div><div class="fods-stat-value"><?= number_format($stats['empty']) ?></div><div class="fods-stat-label">Boxes with no approver</div></div>
            </button>
        </div>
        <div class="col-6 col-lg-3">
            <button type="button" class="fods-stat" data-table="mapTable" data-filter="inactive">
                <div class="fods-stat-icon tone-slate"><i class="ti ti-user-off"></i></div>
                <div><div class="fods-stat-value"><?= number_format($stats['inactive']) ?></div><div class="fods-stat-label">Inactive</div></div>
            </button>
        </div>
    </div>

    <div class="fods-card">
        <div class="fods-card-head">
            <div class="fods-filter" id="mapTableFilter">
                <button type="button" class="active" data-filter="all">All</button>
                <?php foreach ($boxes as $module => $m): ?>
                    <button type="button" data-filter="<?= esc($module, 'attr') ?>"><?= esc($m['label']) ?></button>
                <?php endforeach; ?>
            </div>
            <div class="fods-search">
                <i class="ti ti-search"></i>
                <input type="search" class="form-control form-control-sm" id="mapTableSearch" placeholder="Search name, position, module...">
            </div>
        </div>
        <div class="fods-card-body fods-dt">
            <table id="mapTable" class="table fods-table fods-table-stack w-100">
                <thead>
                    <tr>
                        <th>Approver</th>
                        <th>Signs</th>
                        <th class="d-none d-lg-table-cell">Status</th>
                        <th class="text-end"></th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>

<!-- Add / edit one person and every box they sign -->
<div class="modal fade fods-modal fods-flat" id="mapModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable modal-fullscreen-sm-down">
        <div class="modal-content">
            <form id="mapForm" novalidate>
                <div class="modal-header">
                    <div class="d-flex align-items-center">
                        <div class="fods-modal-icon tone-teal"><i class="ti ti-signature"></i></div>
                        <div>
                            <h5 class="modal-title" id="mapModalTitle">Add Approver</h5>
                            <div class="fods-modal-sub" id="mapModalSub">Pick the person, then tick every box they sign.</div>
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-2 ms-auto ps-3">
                        <button type="button" class="btn btn-sm btn-fods-ghost d-none" data-history-module="approvermap" data-history-field="orig_name"><i class="ti ti-history me-1"></i>History</button>
                        <button type="button" class="btn-close ms-1" data-bs-dismiss="modal"></button>
                    </div>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="orig_name">
                    <section class="fods-fsec">
                        <div class="fods-fsec-head">
                            <h6>Approver</h6>
                        </div>
                        <div class="row g-3">
                            <div class="col-md-7">
                                <label class="form-label req" for="map_full_name">FODS user</label>
                                <select class="form-select" id="map_full_name" name="full_name" required>
                                    <option value="">Choose a FODS user...</option>
                                    <?php foreach ($users as $u): ?>
                                        <option value="<?= esc($u['full_name'], 'attr') ?>" data-position="<?= esc($u['position'], 'attr') ?>"><?= esc($u['full_name']) ?><?= trim((string) $u['position']) !== '' ? ' — ' . esc($u['position']) : '' ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="form-text">Active FODS accounts only.</div>
                                <div class="invalid-feedback">Pick the approver.</div>
                            </div>
                            <div class="col-md-5">
                                <label class="form-label" for="map_position">Printed position</label>
                                <input type="text" class="form-control" id="map_position" maxlength="100">
                            </div>
                            <div class="col-12">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" role="switch" id="map_is_active" name="is_active" value="1" checked>
                                    <label class="form-check-label" for="map_is_active">Active (offered on the forms and used for approvals)</label>
                                </div>
                            </div>
                        </div>
                    </section>

                    <section class="fods-fsec is-wide">
                        <div class="fods-fsec-head">
                            <h6>Boxes they sign</h6>
                            <p id="mapBoxCount">None ticked</p>
                        </div>
                        <div>
                    <?php foreach ($boxes as $module => $m): ?>
                    <div class="map-module" data-module="<?= esc($module, 'attr') ?>">
                        <div class="map-module-head"><span><?= esc($m['label']) ?></span><span class="map-module-count">0 of <?= count($m['boxes']) ?></span></div>
                        <?php foreach ($m['boxes'] as $box => $b): $id = 'mb_' . $module . '_' . $box; ?>
                        <div class="map-box" data-module="<?= esc($module, 'attr') ?>" data-box="<?= esc($box, 'attr') ?>" data-mode="<?= esc($b['mode'], 'attr') ?>">
                            <div class="form-check mb-0">
                                <input class="form-check-input map-box-check" type="checkbox" id="<?= esc($id, 'attr') ?>">
                                <label class="form-check-label" for="<?= esc($id, 'attr') ?>"><?= esc($b['label']) ?>
                                    <?php if ($b['mode'] === 'amount'): ?><span class="map-box-note">· follows the <?= esc($b['amount']) ?></span><?php endif; ?>
                                    <?php if (!empty($b['range'])): ?><span class="map-box-note">· by <?= esc($b['range']) ?></span><?php endif; ?>
                                </label>
                            </div>
                            <!-- every box prints the person's position (set on save); only amount and range boxes have fields to fill -->
                            <?php $hasFields = $b['mode'] === 'amount' || !empty($b['range']); ?>
                            <div class="row g-2 map-box-fields d-none<?= $hasFields ? '' : ' map-box-nofields' ?>">
                                <input type="hidden" class="map-box-position">
                                <?php if ($hasFields): ?>
                                <div class="col-sm-5">
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text">From ₱</span>
                                        <input type="number" class="form-control map-box-min" min="0" step="0.01" placeholder="0.00" aria-label="From amount">
                                    </div>
                                </div>
                                <?php endif; ?>
                                <?php if (!empty($b['range'])): ?>
                                <div class="col-sm-5">
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text">Up to ₱</span>
                                        <input type="number" class="form-control map-box-max" min="0" step="0.01" placeholder="No limit" aria-label="Up to amount">
                                    </div>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endforeach; ?>
                        </div>
                    </section>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-fods-ghost px-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-sm btn-fods px-4" id="submitBtn">Create</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete confirmation -->
<div class="modal fade fods-modal" id="mapDeleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width:400px;">
        <div class="modal-content">
            <div class="modal-body text-center pt-4 pb-3">
                <div class="fods-modal-icon tone-red mx-auto mb-3" style="margin-right:auto !important;"><i class="ti ti-trash"></i></div>
                <h5 class="modal-title mb-1">Remove this approver?</h5>
                <div class="fw-semibold text-dark mb-1" id="mapDeleteName"></div>
                <div class="fods-modal-sub">They come off every box. Records already saved keep the name they have.</div>
            </div>
            <div class="modal-footer justify-content-center">
                <button type="button" class="btn btn-sm btn-fods-ghost px-3" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-sm btn-danger px-4" style="border-radius:10px;" id="mapDeleteBtn">Remove</button>
            </div>
        </div>
    </div>
</div>

<script>
    var __map_data = <?= json_encode($results, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
    var __map_boxes = <?= json_encode($boxes, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
    var __map_users = <?= json_encode($users, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
    var __map_perms = <?= json_encode($perms) ?>;
</script>
<script src="<?=base_url('assets/js/maintenance/myapprovermap.js?v=10');?>"></script>
<?php echo view('templates/myfooter.php'); ?>
