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
                    <li class="breadcrumb-item active" aria-current="page">Projects</li>
                </ol>
            </nav>
            <h1 class="fods-page-title">Projects</h1>
            <p class="fods-page-sub">Reference projects used in budget allotments, ORS and BURS — by fund cluster and division.</p>
        </div>
        <?php if ($perms['add']): ?>
        <div class="fods-page-actions d-flex">
            <button type="button" class="btn btn-fods btn-sm px-3 py-2" id="projectTableAdd"><i class="ti ti-plus me-1"></i> Add Project</button>
        </div>
        <?php endif; ?>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <button type="button" class="fods-stat" data-table="projectTable" data-filter="all">
                <div class="fods-stat-icon tone-teal"><i class="ti ti-folders"></i></div>
                <div><div class="fods-stat-value"><?= number_format(count($projectdata)) ?></div><div class="fods-stat-label">Projects</div></div>
            </button>
        </div>
        <?php $tones = ['tone-indigo', 'tone-amber', 'tone-green', 'tone-slate']; $i = 0; ?>
        <?php foreach (array_slice($byCluster, 0, 2, true) as $fc => $n): ?>
        <div class="col-6 col-lg-3">
            <button type="button" class="fods-stat" data-table="projectTable" data-filter="fc:<?= esc($fc, 'attr') ?>">
                <div class="fods-stat-icon <?= $tones[$i++ % 4] ?>"><i class="ti ti-wallet"></i></div>
                <div><div class="fods-stat-value"><?= number_format($n) ?></div><div class="fods-stat-label">Fund cluster <?= esc($fc) ?></div></div>
            </button>
        </div>
        <?php endforeach; ?>
        <div class="col-6 col-lg-3">
            <div class="fods-stat">
                <div class="fods-stat-icon tone-slate"><i class="ti ti-building"></i></div>
                <div><div class="fods-stat-value"><?= number_format(count($divisiondata)) ?></div><div class="fods-stat-label">Divisions</div></div>
            </div>
        </div>
    </div>

    <div class="fods-card">
        <div class="fods-card-head">
            <div class="fods-filter" id="projectTableFilter">
                <button type="button" class="active" data-filter="all">All</button>
                <?php foreach (array_keys($byCluster) as $fc): ?>
                    <button type="button" data-filter="fc:<?= esc($fc, 'attr') ?>">Fund cluster <?= esc($fc) ?></button>
                <?php endforeach; ?>
            </div>
            <div class="fods-search">
                <i class="ti ti-search"></i>
                <input type="search" class="form-control form-control-sm" id="projectTableSearch" placeholder="Search title, code, leader...">
            </div>
        </div>
        <div class="fods-card-body fods-dt">
            <table id="projectTable" class="table fods-table fods-table-stack w-100">
                <thead>
                    <tr>
                        <th>Project</th>
                        <th>Fund cluster</th>
                        <th>Division</th>
                        <th class="d-none d-lg-table-cell">Project leader</th>
                        <th class="d-none d-xl-table-cell">MFO / PAP</th>
                        <th class="text-end"></th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>

<!-- Add / edit -->
<div class="modal fade fods-modal" id="projectModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable modal-fullscreen-sm-down">
        <div class="modal-content">
            <form id="projectForm" novalidate>
                <div class="modal-header">
                    <div class="d-flex align-items-center">
                        <div class="fods-modal-icon tone-teal"><i class="ti ti-folders"></i></div>
                        <div>
                            <h5 class="modal-title" id="projectModalTitle">Add Project</h5>
                            <div class="fods-modal-sub" id="projectModalSub">A reference project for budget and obligations.</div>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="recid">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label" for="project_title">Project title</label>
                            <textarea class="form-control" id="project_title" name="project_title" rows="3" required></textarea>
                            <div class="invalid-feedback">Project title is required.</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="responsibility_code">Responsibility code</label>
                            <input type="text" class="form-control" id="responsibility_code" name="responsibility_code" required>
                            <div class="invalid-feedback">Responsibility code is required.</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="project_leader">Project leader</label>
                            <input type="text" class="form-control" id="project_leader" name="project_leader" required>
                            <div class="invalid-feedback">Project leader is required.</div>
                        </div>
                        <div class="col-md-4 col-6">
                            <label class="form-label" for="fundcluster_id">Fund cluster</label>
                            <select class="form-select" id="fundcluster_id" name="fundcluster_id" required>
                                <option value="">Choose…</option>
                                <?php foreach ($fundclusterdata as $fc): ?>
                                    <option value="<?= esc($fc['fundcluster_id'], 'attr') ?>"><?= esc($fc['fund_cluster_code']) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <div class="invalid-feedback">Choose a fund cluster.</div>
                        </div>
                        <div class="col-md-4 col-6">
                            <label class="form-label" for="division_id">Division</label>
                            <select class="form-select" id="division_id" name="division_id" required>
                                <option value="">Choose…</option>
                                <?php foreach ($divisiondata as $d): ?>
                                    <option value="<?= esc($d['division_id'], 'attr') ?>"><?= esc($d['division_name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <div class="invalid-feedback">Choose a division.</div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="mfopaps_code">MFO / PAP</label>
                            <select class="form-select" id="mfopaps_code" name="mfopaps_code">
                                <option value="">—</option>
                                <?php foreach ($mfopapsdata as $m): ?>
                                    <option value="<?= esc($m['mfopaps_code'], 'attr') ?>"><?= esc($m['mfopaps_code']) ?></option>
                                <?php endforeach; ?>
                            </select>
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
    var __project_data = <?= json_encode($projectdata, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
    var __project_perms = <?= json_encode($perms) ?>;
</script>
<script src="<?=base_url('assets/js/maintenance/myproject.js?v=3');?>"></script>
<?php echo view('templates/myfooter.php'); ?>
