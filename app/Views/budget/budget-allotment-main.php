<?php
echo view('templates/myheader.php');
?>
<head>
<style>
/* Editor grids */
.bud-grid-card { border:1px solid var(--fods-border); background:#fff; margin-bottom:1rem; border-radius:12px; overflow:hidden; }
.bud-grid-head { display:flex; flex-wrap:wrap; gap:.5rem; align-items:center; justify-content:space-between; padding:.65rem .9rem; border-bottom:1px solid var(--fods-border); background:#fafbfc; }
.bud-grid-title { font-weight:600; color:var(--fods-text); }
.bud-grid-sub { font-size:.75rem; color:var(--fods-muted); }
.bud-grid-wrap { overflow-x:auto; }
.bud-grid { width:100%; border-collapse:collapse; font-size:.82rem; }
/* sum of the column widths, so narrow screens scroll the grid instead of clipping values */
.bud-grid[data-kind="item"] { min-width:1240px; }
.bud-grid[data-kind="co"] { min-width:1120px; }
.bud-grid[data-kind="ac"] { min-width:620px; }
.bud-grid th { background:#f8fafc; color:var(--fods-muted); font-size:.68rem; font-weight:600; text-transform:uppercase; letter-spacing:.04em; padding:.5rem .45rem; border-bottom:1px solid var(--fods-border); white-space:nowrap; text-align:left; }
.bud-grid th.num, .bud-grid td.num { text-align:right; }
.bud-grid td { padding:.3rem .35rem; border-bottom:1px solid #f1f4f8; vertical-align:middle; }
.bud-grid input { width:100%; border:1px solid transparent; background:transparent; padding:.3rem .4rem; font-size:.82rem; color:var(--fods-text); }
.bud-grid input:hover:not(:disabled):not([readonly]) { border-color:var(--fods-border); }
.bud-grid input:focus { outline:none; border-color:var(--fods-teal); background:#fff; box-shadow:0 0 0 2px var(--fods-teal-soft); }
.bud-grid input[type=number] { text-align:right; font-variant-numeric:tabular-nums; }
.bud-grid input:disabled, .bud-grid input[readonly] { color:#64748b; background:#f8fafc; }
.bud-grid .g-proposed { font-weight:600; color:var(--fods-text) !important; }
.bud-grid tfoot td { font-weight:600; background:#fafbfc; border-top:1px solid var(--fods-border); padding:.5rem .45rem; font-variant-numeric:tabular-nums; }
.bud-row-actions { display:flex; gap:.1rem; }
.bud-row-actions .fods-icon-btn { width:28px; height:28px; font-size:.95rem; }
.bud-grid input { border-radius:6px; }
.bud-empty { padding:1.25rem; text-align:center; color:var(--fods-muted); font-size:.82rem; }

/* Editor footer totals */
.bud-totals { display:flex; flex-wrap:wrap; gap:1.25rem; align-items:center; font-size:.82rem; color:var(--fods-muted); }
.bud-totals b { color:var(--fods-text); font-size:.95rem; font-variant-numeric:tabular-nums; }
.bud-tab-amt { font-size:.7rem; color:var(--fods-muted); margin-left:.35rem; font-weight:500; }

/* Editor dialog: a bit wider than modal-xl on big screens so the grids fit */
@media (min-width: 1200px) {
    .bud-dialog { max-width: min(1320px, calc(100vw - 4rem)); }
}

/* jQuery UI autocomplete above the modal */
.ui-autocomplete { z-index:2000 !important; max-height:280px; overflow-y:auto; overflow-x:hidden; font-size:.82rem; }

@media (max-width: 767.98px) {
    .bud-totals { gap:.5rem 1rem; width:100%; }
}
</style>
</head>
<div class="container-fluid">
    <input type="hidden" id="__siteurl" data-mesiteurl="<?=site_url();?>" data-uploads="<?=base_url('uploads/');?>" />
    <div class="row me-outp-msg mx-0"></div>

    <div class="fods-page-head">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="<?=site_url();?>"><i class="ti ti-home"></i></a></li>
                    <li class="breadcrumb-item"><a href="#">Budget</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Budget Allotment</li>
                </ol>
            </nav>
            <h1 class="fods-page-title">Budget Allotment</h1>
            <p class="fods-page-sub">Line-item budgets (LIB) per project — Personnel Services, MOOE, Capital Outlay and admin cost, with realignments.</p>
        </div>
        <div class="fods-page-actions d-flex">
            <select class="form-select form-select-sm fods-year" id="budgetYear" aria-label="Year"></select>
            <?php if ($perms['add']): ?>
                <button type="button" class="btn btn-fods btn-sm px-3 py-2" id="budgetTableAdd"><i class="ti ti-plus me-1"></i> New Allotment</button>
            <?php endif; ?>
        </div>
    </div>

    <!-- Stat cards (filled by mybudgetallotment.js for the selected year) -->
    <div class="row g-3 mb-4" id="budgetTableCards">
        <div class="col-6 col-xl-3">
            <button type="button" class="fods-stat" data-table="budgetTable" data-filter="pending">
                <div class="fods-stat-icon tone-amber"><i class="ti ti-hourglass"></i></div>
                <div><div class="fods-stat-value" data-card-value="pending">0</div><div class="fods-stat-label" data-card-label="pending">Pending</div></div>
            </button>
        </div>
        <div class="col-6 col-xl-3">
            <button type="button" class="fods-stat" data-table="budgetTable" data-filter="disapproved">
                <div class="fods-stat-icon tone-red"><i class="ti ti-arrow-back-up"></i></div>
                <div><div class="fods-stat-value" data-card-value="returned">0</div><div class="fods-stat-label" data-card-label="returned">Returned</div></div>
            </button>
        </div>
        <div class="col-6 col-xl-3">
            <button type="button" class="fods-stat" data-table="budgetTable" data-filter="approved">
                <div class="fods-stat-icon tone-green"><i class="ti ti-circle-check"></i></div>
                <div><div class="fods-stat-value" data-card-value="approved">0</div><div class="fods-stat-label" data-card-label="approved">Approved</div></div>
            </button>
        </div>
        <div class="col-6 col-xl-3">
            <button type="button" class="fods-stat" data-table="budgetTable" data-filter="all">
                <div class="fods-stat-icon tone-teal"><i class="ti ti-cash"></i></div>
                <div><div class="fods-stat-value" data-card-value="total">₱0.00</div><div class="fods-stat-label" data-card-label="total">Total amount</div></div>
            </button>
        </div>
    </div>

    <div class="fods-card">
        <div class="fods-card-head">
            <div class="fods-filter" id="budgetTableFilter">
                <button type="button" class="active" data-filter="all">All</button>
                <button type="button" data-filter="draft">Drafts</button>
                <button type="button" data-filter="pending">Pending</button>
                <button type="button" data-filter="disapproved">Returned</button>
                <button type="button" data-filter="approved">Approved</button>
            </div>
            <div class="fods-search">
                <i class="ti ti-search"></i>
                <input type="search" class="form-control form-control-sm" id="budgetTableSearch" placeholder="Search project, LIB no., code...">
            </div>
        </div>
        <div class="fods-card-body fods-dt">
            <table id="budgetTable" class="table fods-table fods-table-stack w-100">
                <thead>
                    <tr>
                        <th>Project</th>
                        <th class="d-none d-lg-table-cell">Responsibility code</th>
                        <th class="d-none d-md-table-cell">Fund cluster</th>
                        <th class="text-end">Approved budget</th>
                        <th>Status</th>
                        <th class="d-none d-xl-table-cell">Encoded</th>
                        <th class="text-end"></th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>

<!-- ============================= EDITOR ============================= -->
<div class="modal fade fods-modal" id="budgetModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable modal-fullscreen-md-down bud-dialog">
        <div class="modal-content">
            <form id="budgetForm" novalidate>
                <div class="modal-header border-bottom">
                    <div class="d-flex align-items-center" style="min-width:0">
                        <div class="fods-modal-icon tone-teal"><i class="ti ti-wallet"></i></div>
                        <div style="min-width:0">
                            <h5 class="modal-title text-truncate" id="budgetModalTitle">New Budget Allotment</h5>
                            <div class="fods-modal-sub" id="budgetModalSub">Fill in the project details, then the line items.</div>
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-2 ms-auto ps-3 flex-shrink-0 text-nowrap">
                        <button type="button" class="btn btn-sm btn-fods-ghost d-none" id="budgetPrintBtn"><i class="ti ti-printer me-1"></i>Print LIB</button>
                        <button type="button" class="btn btn-sm btn-danger d-none" id="budgetDisapproveBtn"><i class="ti ti-x me-1"></i>Disapprove</button>
                        <button type="button" class="btn btn-sm btn-success d-none" id="budgetApproveBtn"><i class="ti ti-check me-1"></i>Approve</button>
                        <button type="button" class="btn-close ms-1" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                </div>

                <div class="modal-body" style="background:var(--fods-bg);">
                    <input type="hidden" id="recid" name="recid">

                    <div id="budgetDecision" class="alert d-none mb-3"></div>

                    <ul class="nav fods-segment mb-3 flex-nowrap fods-scroll-x" role="tablist" style="max-width:100%;">
                        <li class="nav-item"><button class="nav-link active" type="button" data-bs-toggle="tab" data-bs-target="#budPaneDetails"><i class="ti ti-file-description"></i>Details</button></li>
                        <li class="nav-item"><button class="nav-link" type="button" data-bs-toggle="tab" data-bs-target="#budPanePs"><i class="ti ti-users"></i>Personnel Services<span class="bud-tab-amt" data-tab-total="ps"></span></button></li>
                        <li class="nav-item"><button class="nav-link" type="button" data-bs-toggle="tab" data-bs-target="#budPaneMooe"><i class="ti ti-tools"></i>MOOE<span class="bud-tab-amt" data-tab-total="mooe"></span></button></li>
                        <li class="nav-item"><button class="nav-link" type="button" data-bs-toggle="tab" data-bs-target="#budPaneCo"><i class="ti ti-building"></i>Capital Outlay<span class="bud-tab-amt" data-tab-total="co"></span></button></li>
                        <li class="nav-item"><button class="nav-link" type="button" data-bs-toggle="tab" data-bs-target="#budPaneAc"><i class="ti ti-coin"></i>Admin Cost<span class="bud-tab-amt" data-tab-total="ac"></span></button></li>
                        <li class="nav-item"><button class="nav-link" type="button" data-bs-toggle="tab" data-bs-target="#budPaneFiles"><i class="ti ti-paperclip"></i>Attachments<span class="bud-tab-amt" id="budFilesCount"></span></button></li>
                    </ul>

                    <div class="tab-content">
                        <!-- Details -->
                        <div class="tab-pane fade show active" id="budPaneDetails">
                            <div class="fods-card mb-3"><div class="fods-card-body pt-3">
                                <div class="fods-section mt-0">Project</div>
                                <div class="row g-3">
                                    <div class="col-lg-6">
                                        <label class="form-label" for="program_title">Program title</label>
                                        <input type="text" class="form-control" id="program_title" name="program_title" placeholder="Start typing to pick a program">
                                    </div>
                                    <div class="col-lg-6">
                                        <label class="form-label" for="project_title">Project title</label>
                                        <input type="text" class="form-control" id="project_title" name="project_title" placeholder="Start typing to pick a project" required>
                                        <div class="invalid-feedback">Project title is required.</div>
                                    </div>
                                    <div class="col-md-4 col-6">
                                        <label class="form-label" for="responsibility_code">Responsibility code</label>
                                        <input type="text" class="form-control" id="responsibility_code" name="responsibility_code">
                                    </div>
                                    <div class="col-md-2 col-6">
                                        <label class="form-label" for="fund_cluster_code">Fund cluster</label>
                                        <input type="text" class="form-control" id="fund_cluster_code" name="fund_cluster_code" readonly>
                                    </div>
                                    <div class="col-md-2 col-6">
                                        <label class="form-label" for="division_name">Division</label>
                                        <input type="text" class="form-control" id="division_name" name="division_name" readonly>
                                    </div>
                                    <div class="col-md-4 col-6">
                                        <label class="form-label" for="trxno">LIB no.</label>
                                        <input type="text" class="form-control" id="trxno" name="trxno" placeholder="System-generated" readonly>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label" for="project_leader">Project leader</label>
                                        <input type="text" class="form-control" id="project_leader" name="project_leader">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label" for="program_leader">Program leader</label>
                                        <input type="text" class="form-control" id="program_leader" name="program_leader" required>
                                        <div class="invalid-feedback">Program leader is required.</div>
                                    </div>
                                </div>

                                <div class="fods-section">Duration</div>
                                <div class="row g-3 align-items-end">
                                    <div class="col-md-3 col-6">
                                        <label class="form-label" for="duration_from">From</label>
                                        <input type="date" class="form-control" id="duration_from" name="duration_from" required>
                                        <div class="invalid-feedback">Required.</div>
                                    </div>
                                    <div class="col-md-3 col-6">
                                        <label class="form-label" for="duration_to">To</label>
                                        <input type="date" class="form-control" id="duration_to" name="duration_to" required>
                                        <div class="invalid-feedback">Required.</div>
                                    </div>
                                    <div class="col-md-3 col-6">
                                        <label class="form-label" for="project_duration">Project duration</label>
                                        <input type="text" class="form-control" id="project_duration" name="project_duration" readonly>
                                    </div>
                                    <div class="col-md-3 col-6">
                                        <div class="form-check form-switch mb-2">
                                            <input class="form-check-input" type="checkbox" id="with_extension">
                                            <label class="form-check-label" for="with_extension">With extension</label>
                                        </div>
                                    </div>
                                    <div class="col-md-3 col-6 bud-ext d-none">
                                        <label class="form-label" for="extended_from">Extended from</label>
                                        <input type="date" class="form-control" id="extended_from" name="extended_from">
                                    </div>
                                    <div class="col-md-3 col-6 bud-ext d-none">
                                        <label class="form-label" for="extended_to">Extended to</label>
                                        <input type="date" class="form-control" id="extended_to" name="extended_to">
                                    </div>
                                </div>

                                <div class="fods-section">Agencies</div>
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label class="form-label" for="implementing_agency">Implementing agency</label>
                                        <input type="text" class="form-control" id="implementing_agency" name="implementing_agency" required>
                                        <div class="invalid-feedback">Implementing agency is required.</div>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label" for="monitoring_agency">Monitoring agency</label>
                                        <input type="text" class="form-control" id="monitoring_agency" name="monitoring_agency" required>
                                        <div class="invalid-feedback">Monitoring agency is required.</div>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label" for="funding_agency">Funding agency</label>
                                        <input type="text" class="form-control" id="funding_agency" name="funding_agency" required>
                                        <div class="invalid-feedback">Funding agency is required.</div>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label" for="collaborating_agencies">Collaborating agencies</label>
                                        <textarea class="form-control" id="collaborating_agencies" name="collaborating_agencies" rows="2" required></textarea>
                                        <div class="invalid-feedback">Collaborating agencies is required.</div>
                                    </div>
                                </div>

                                <div class="fods-section">Status</div>
                                <div class="row g-3 align-items-end">
                                    <div class="col-md-4">
                                        <label class="form-label" for="lddap_refno">LDDAP-ADA reference no.</label>
                                        <input type="text" class="form-control" id="lddap_refno" name="lddap_refno" required>
                                        <div class="invalid-feedback">LDDAP-ADA reference no. is required.</div>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label" for="tagging">Save as</label>
                                        <select class="form-select" id="tagging" name="tagging">
                                            <option value="Save to Draft">Save to Draft</option>
                                            <option value="For Approval">For Approval</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label d-block">Realignment</label>
                                        <div class="d-flex gap-3 pt-1">
                                            <div class="form-check form-switch mb-0"><input class="form-check-input" type="checkbox" id="is_realign1" disabled><label class="form-check-label" for="is_realign1">1st</label></div>
                                            <div class="form-check form-switch mb-0"><input class="form-check-input" type="checkbox" id="is_realign2" disabled><label class="form-check-label" for="is_realign2">2nd</label></div>
                                            <div class="form-check form-switch mb-0"><input class="form-check-input" type="checkbox" id="is_realign3" disabled><label class="form-check-label" for="is_realign3">3rd</label></div>
                                        </div>
                                        <div class="form-text" id="realignHint">Available once the allotment is approved.</div>
                                    </div>
                                </div>
                            </div></div>
                        </div>

                        <!-- Line items -->
                        <div class="tab-pane fade" id="budPanePs">
                            <div class="bud-grid-card" data-grid="ps_direct"></div>
                            <div class="bud-grid-card" data-grid="ps_indirect"></div>
                        </div>
                        <div class="tab-pane fade" id="budPaneMooe">
                            <div class="bud-grid-card" data-grid="mooe_direct"></div>
                            <div class="bud-grid-card" data-grid="mooe_indirect"></div>
                        </div>
                        <div class="tab-pane fade" id="budPaneCo">
                            <div class="bud-grid-card" data-grid="co_direct"></div>
                            <div class="bud-grid-card" data-grid="co_indirect"></div>
                        </div>
                        <div class="tab-pane fade" id="budPaneAc">
                            <div class="bud-grid-card" data-grid="ac"></div>
                        </div>

                        <!-- Attachments -->
                        <div class="tab-pane fade" id="budPaneFiles">
                            <div class="fods-card"><div class="fods-card-body pt-3">
                                <div class="d-flex flex-wrap gap-2 align-items-end mb-3" id="budUploadRow">
                                    <div class="flex-grow-1" style="min-width:220px;">
                                        <label class="form-label" for="budUploadFile">Upload a file</label>
                                        <input type="file" class="form-control" id="budUploadFile">
                                    </div>
                                    <button type="button" class="btn btn-fods px-3" id="budUploadBtn"><i class="ti ti-upload me-1"></i>Upload</button>
                                </div>
                                <div class="form-text mb-3 d-none" id="budUploadNote">Save the allotment first — files are attached to its LIB number.</div>
                                <div id="budFilesList"></div>
                            </div></div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer justify-content-between">
                    <div class="bud-totals">
                        <span>Approved <b id="budTotalApproved">₱0.00</b></span>
                        <span>Proposed <b id="budTotalProposed">₱0.00</b></span>
                        <span id="budBalance"></span>
                        <input type="hidden" id="total_approved_combined" name="total_approved_combined">
                        <input type="hidden" id="total_proposed_combined" name="total_proposed_combined">
                    </div>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-sm btn-fods-ghost px-3" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-sm btn-fods px-4" id="submitBtn">Save</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Approve / disapprove -->
<div class="modal fade fods-modal" id="budgetDecisionModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width:460px;">
        <div class="modal-content">
            <div class="modal-header">
                <div class="d-flex align-items-center">
                    <div class="fods-modal-icon" id="budDecisionIcon"><i class="ti ti-check"></i></div>
                    <div>
                        <h5 class="modal-title" id="budDecisionTitle">Approve allotment</h5>
                        <div class="fods-modal-sub" id="budDecisionSub"></div>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label" for="budDecisionBy">Decided by</label>
                    <input type="text" class="form-control" id="budDecisionBy" value="<?= esc(session('__xsys_myuserzicas__'), 'attr') ?>" readonly>
                </div>
                <div>
                    <label class="form-label" for="budDecisionRemarks">Remarks</label>
                    <textarea class="form-control" id="budDecisionRemarks" rows="3"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-sm btn-fods-ghost px-3" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-sm px-4" id="budDecisionConfirm">Approve</button>
            </div>
        </div>
    </div>
</div>

<!-- LIB print preview -->
<div class="modal fade fods-modal" id="budgetPrintModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-fullscreen-md-down">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Line-Item Budget — print preview</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0">
                <iframe id="budgetPrintFrame" src="about:blank" style="width:100%; height:80vh; border:0;"></iframe>
            </div>
        </div>
    </div>
</div>

<script>
    var __budget_data = <?= json_encode($budgetdtdata, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
    var __budget_lookups = <?= json_encode([
        'projects' => $projectdata,
        'psuacs' => $psuacs,
        'mooeuacs' => $mooeuacs,
        'programs' => $programs,
    ], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
    var __budget_perms = <?= json_encode($perms) ?>;
    var __budget_open = <?= json_encode($open, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
</script>
<script src="<?=base_url('assets/js/budget/mybudgetallotment.js?v=10');?>"></script>
<?php echo view('templates/myfooter.php'); ?>
