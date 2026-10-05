<?php
echo view('templates/myheader.php');
?>
<head>
<style>
/* Editor grids */
.saob-grid-card { border:1px solid var(--fods-border); background:#fff; margin-bottom:1rem; border-radius:12px; overflow:hidden; }
.saob-grid-head { display:flex; flex-wrap:wrap; gap:.5rem; align-items:center; justify-content:space-between; padding:.65rem .9rem; border-bottom:1px solid var(--fods-border); background:#fafbfc; }
.saob-grid-title { font-weight:600; color:var(--fods-text); }
.saob-grid-sub { font-size:.75rem; color:var(--fods-muted); }
.saob-grid-wrap { overflow-x:auto; scrollbar-width:thin; }
/* Fixed, roomy columns: the grid scrolls sideways instead of squeezing the fields */
.saob-grid { width:max-content; min-width:100%; table-layout:fixed; border-collapse:separate; border-spacing:0; font-size:.85rem; }
/* sized so all four amount columns show without scrolling on a 1440px screen */
.saob-grid col.c-act { width:84px; }
.saob-grid col.c-object { width:200px; }
.saob-grid col.c-particulars { width:248px; }
.saob-grid col.c-uacs { width:132px; }
.saob-grid col.c-amt { width:145px; }
.saob-grid th { background:#f8fafc; color:var(--fods-muted); font-size:.68rem; font-weight:600; text-transform:uppercase; letter-spacing:.04em; padding:.6rem .6rem; border-bottom:1px solid var(--fods-border); white-space:nowrap; text-align:left; }
.saob-grid th.num, .saob-grid td.num { text-align:right; }
.saob-grid td { padding:.45rem .6rem; border-bottom:1px solid #f1f4f8; vertical-align:middle; background:#fff; }
.saob-grid tbody tr:hover td { background:#fbfcfd; }
.saob-grid input, .saob-grid select { width:100%; height:36px; border:1px solid var(--fods-border); background:#fff; padding:.35rem .65rem; font-size:.85rem; color:var(--fods-text); border-radius:8px; text-overflow:ellipsis; }
.saob-grid input::placeholder { color:#a3adbb; }
.saob-grid input:hover:not(:disabled):not([readonly]), .saob-grid select:hover:not(:disabled) { border-color:#c7d0db; }
.saob-grid input:focus, .saob-grid select:focus { outline:none; border-color:var(--fods-teal); background:#fff; box-shadow:0 0 0 3px var(--fods-teal-soft); }
.saob-grid input[type=number] { text-align:right; font-variant-numeric:tabular-nums; font-weight:600; }
.saob-grid input:disabled, .saob-grid input[readonly], .saob-grid select:disabled { color:#475569; background:#f1f4f8; border-color:transparent; opacity:1; }
.saob-grid tfoot td { font-weight:600; background:#fafbfc; border-top:1px solid var(--fods-border); border-bottom:0; padding:.6rem .6rem; font-variant-numeric:tabular-nums; }
/* Row actions stay on the left and the revised allotment on the right while scrolling */
.saob-grid .sticky-l { position:sticky; left:0; z-index:1; }
.saob-grid .sticky-r { position:sticky; right:0; z-index:1; box-shadow:-8px 0 8px -8px rgba(15,23,42,.18); }
.saob-grid th.sticky-l, .saob-grid th.sticky-r { background:#f8fafc; z-index:2; }
.saob-grid tfoot .sticky-l, .saob-grid tfoot .sticky-r { background:#fafbfc; }
.saob-grid-hint { font-size:.72rem; color:var(--fods-muted); }
.saob-row-actions { display:flex; gap:.1rem; }
.saob-row-actions .fods-icon-btn { width:28px; height:28px; font-size:.95rem; }
.saob-empty { padding:1.25rem; text-align:center; color:var(--fods-muted); font-size:.82rem; }

/* Revision months on the Details tab */
.saob-months { display:grid; grid-template-columns:repeat(6, minmax(0,1fr)); gap:.5rem .75rem; }
.saob-months .form-check { margin:0; }
.saob-months-note { font-size:.78rem; color:var(--fods-muted); margin-top:.6rem; }

/* Budget summary on the Details tab */
.saob-sum { border:1px solid var(--fods-border); background:#fafbfc; border-radius:12px; padding:.9rem 1rem; }
.saob-sum-row { display:flex; justify-content:space-between; gap:1rem; padding:.3rem 0; font-size:.85rem; color:var(--fods-muted); }
.saob-sum-row b { color:var(--fods-text); font-variant-numeric:tabular-nums; }
.saob-sum-row.net { border-top:1px solid var(--fods-border); margin-top:.35rem; padding-top:.6rem; align-items:center; }
.saob-sum input { border:0; background:transparent; padding:0; text-align:right; font-weight:700; color:var(--fods-text); font-variant-numeric:tabular-nums; width:11rem; max-width:55%; }
.saob-sum input:focus { outline:none; }

/* Editor footer totals */
.saob-totals { display:flex; flex-wrap:wrap; gap:1.25rem; align-items:center; font-size:.82rem; color:var(--fods-muted); }
.saob-totals b { color:var(--fods-text); font-size:.95rem; font-variant-numeric:tabular-nums; }
.saob-tab-amt { font-size:.7rem; color:var(--fods-muted); margin-left:.35rem; font-weight:500; }
.saob-dialog .fods-segment .nav-link { white-space:nowrap; }

/* Reports tab */
.saob-report { height:100%; display:flex; flex-direction:column; }
.saob-report .fods-card-body { display:flex; flex-direction:column; flex:1; }
.saob-report-sub { font-size:.8rem; color:var(--fods-muted); margin:0 0 .9rem; }
.saob-report-actions { display:flex; flex-wrap:wrap; gap:.5rem; margin-top:auto; padding-top:1rem; }
.saob-report .form-label { font-size:.78rem; font-weight:500; color:var(--fods-muted); margin-bottom:.3rem; }
.saob-report .form-control, .saob-report .form-select { border-radius:10px; border-color:var(--fods-border); }
.saob-report .form-control:focus, .saob-report .form-select:focus { border-color:var(--fods-teal); box-shadow:0 0 0 3px var(--fods-teal-soft); }

/* Editor dialog: a bit wider than modal-xl on big screens so the grids fit */
@media (min-width: 1200px) {
    .saob-dialog { max-width: min(1320px, calc(100vw - 4rem)); }
}

/* jQuery UI autocomplete above the modal */
.ui-autocomplete { z-index:2000 !important; max-height:280px; overflow-y:auto; overflow-x:hidden; font-size:.82rem; }

@media (max-width: 991.98px) {
    .saob-months { grid-template-columns:repeat(4, minmax(0,1fr)); }
}
@media (max-width: 767.98px) {
    .saob-totals { gap:.5rem 1rem; width:100%; }
    .saob-months { grid-template-columns:repeat(3, minmax(0,1fr)); }
}
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
                    <li class="breadcrumb-item"><a href="#">Reports</a></li>
                    <li class="breadcrumb-item active" aria-current="page">SAOB</li>
                </ol>
            </nav>
            <h1 class="fods-page-title">Statement of Allotment, Obligation and Balances</h1>
            <p class="fods-page-sub">Keep each program's approved allotment and its monthly realignments, then generate the SAOB and its exports.</p>
        </div>
        <div class="fods-page-actions d-flex">
            <select class="form-select form-select-sm fods-year" id="saobYear" aria-label="Year"></select>
            <?php if ($perms['add']): ?>
                <button type="button" class="btn btn-fods btn-sm px-3 py-2" id="saobTableAdd"><i class="ti ti-plus me-1"></i> New allotment</button>
            <?php endif; ?>
        </div>
    </div>

    <ul class="nav fods-segment mb-3" role="tablist" id="saobTabs">
        <li class="nav-item"><button class="nav-link active" type="button" data-bs-toggle="tab" data-bs-target="#saobPaneList"><i class="ti ti-list-details"></i>Allotments</button></li>
        <li class="nav-item"><button class="nav-link" type="button" data-bs-toggle="tab" data-bs-target="#saobPaneReports"><i class="ti ti-report-analytics"></i>Reports</button></li>
    </ul>

    <div class="tab-content">
        <!-- ============================= ALLOTMENTS ============================= -->
        <div class="tab-pane fade show active" id="saobPaneList">
            <!-- Stat cards (filled by mysaobreport.js for the selected year) -->
            <div class="row g-3 mb-4" id="saobTableCards">
                <div class="col-6 col-xl-3">
                    <button type="button" class="fods-stat" data-table="saobTable" data-filter="all">
                        <div class="fods-stat-icon tone-indigo"><i class="ti ti-files"></i></div>
                        <div><div class="fods-stat-value" data-card-value="records">0</div><div class="fods-stat-label" data-card-label="records">Allotments</div></div>
                    </button>
                </div>
                <div class="col-6 col-xl-3">
                    <button type="button" class="fods-stat" data-table="saobTable" data-filter="original">
                        <div class="fods-stat-icon tone-slate"><i class="ti ti-file-text"></i></div>
                        <div><div class="fods-stat-value" data-card-value="original">0</div><div class="fods-stat-label" data-card-label="original">No revisions yet</div></div>
                    </button>
                </div>
                <div class="col-6 col-xl-3">
                    <button type="button" class="fods-stat" data-table="saobTable" data-filter="revised">
                        <div class="fods-stat-icon tone-amber"><i class="ti ti-arrows-exchange"></i></div>
                        <div><div class="fods-stat-value" data-card-value="revised">0</div><div class="fods-stat-label" data-card-label="revised">With revisions</div></div>
                    </button>
                </div>
                <div class="col-6 col-xl-3">
                    <button type="button" class="fods-stat" data-table="saobTable" data-filter="all">
                        <div class="fods-stat-icon tone-teal"><i class="ti ti-cash"></i></div>
                        <div><div class="fods-stat-value" data-card-value="total">₱0.00</div><div class="fods-stat-label" data-card-label="total">Approved budget</div></div>
                    </button>
                </div>
            </div>

            <div class="fods-card">
                <div class="fods-card-head">
                    <div class="fods-filter" id="saobTableFilter">
                        <button type="button" class="active" data-filter="all">All</button>
                        <button type="button" data-filter="original">No revisions</button>
                        <button type="button" data-filter="revised">With revisions</button>
                    </div>
                    <div class="fods-search">
                        <i class="ti ti-search"></i>
                        <input type="search" class="form-control form-control-sm" id="saobTableSearch" placeholder="Search project, program, reference no...">
                    </div>
                </div>
                <div class="fods-card-body fods-dt">
                    <table id="saobTable" class="table fods-table fods-table-stack w-100">
                        <thead>
                            <tr>
                                <th>Allotment</th>
                                <th class="d-none d-md-table-cell">Year</th>
                                <th class="d-none d-lg-table-cell">Lines</th>
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

        <!-- ============================= REPORTS ============================= -->
        <div class="tab-pane fade" id="saobPaneReports">
            <div class="row g-3">
                <div class="col-lg-4 col-md-6">
                    <div class="fods-card saob-report">
                        <div class="fods-card-head"><h2 class="fods-card-title"><i class="ti ti-report-analytics me-1"></i>SAOB report</h2></div>
                        <div class="fods-card-body">
                            <p class="saob-report-sub">The full statement up to the month you pick, as a PDF or a CSV file.</p>
                            <div class="row g-3">
                                <div class="col-7">
                                    <label class="form-label" for="rptMonth">Month</label>
                                    <select class="form-select" id="rptMonth"></select>
                                </div>
                                <div class="col-5">
                                    <label class="form-label" for="rptYear">Year</label>
                                    <select class="form-select" id="rptYear"></select>
                                </div>
                            </div>
                            <div class="saob-report-actions">
                                <button type="button" class="btn btn-sm btn-fods px-3" id="rptSaobPdf"><i class="ti ti-file-search me-1"></i>Preview PDF</button>
                                <button type="button" class="btn btn-sm btn-fods-ghost px-3" id="rptSaobCsv"><i class="ti ti-download me-1"></i>CSV</button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4 col-md-6">
                    <div class="fods-card saob-report">
                        <div class="fods-card-head"><h2 class="fods-card-title"><i class="ti ti-file-invoice me-1"></i>ORS extraction</h2></div>
                        <div class="fods-card-body">
                            <p class="saob-report-sub">Every ORS line dated within the months you pick, as a CSV file.</p>
                            <div class="row g-3">
                                <div class="col-6">
                                    <label class="form-label" for="rptFrom">From</label>
                                    <input type="month" class="form-control" id="rptFrom">
                                </div>
                                <div class="col-6">
                                    <label class="form-label" for="rptTo">To</label>
                                    <input type="month" class="form-control" id="rptTo">
                                </div>
                            </div>
                            <div class="saob-report-actions">
                                <button type="button" class="btn btn-sm btn-fods px-3" id="rptOrsCsv"><i class="ti ti-download me-1"></i>Export CSV</button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4 col-md-12">
                    <div class="fods-card saob-report">
                        <div class="fods-card-head"><h2 class="fods-card-title"><i class="ti ti-calendar-stats me-1"></i>Monthly report</h2></div>
                        <div class="fods-card-body">
                            <p class="saob-report-sub">Allotment, obligations and balance per project of one program.</p>
                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label" for="rptProgram">Program title</label>
                                    <input type="text" class="form-control" id="rptProgram" placeholder="Start typing to pick a program" autocomplete="off">
                                </div>
                                <div class="col-12">
                                    <label class="form-label" for="rptProgramMonth">Month</label>
                                    <input type="month" class="form-control" id="rptProgramMonth">
                                </div>
                            </div>
                            <div class="saob-report-actions">
                                <button type="button" class="btn btn-sm btn-fods px-3" id="rptMonthlyCsv"><i class="ti ti-download me-1"></i>Export CSV</button>
                                <button type="button" class="btn btn-sm btn-fods-ghost px-3" id="rptMonthlyPrint"><i class="ti ti-printer me-1"></i>Print status</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ============================= EDITOR ============================= -->
<div class="modal fade fods-modal" id="saobModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable modal-fullscreen-md-down saob-dialog">
        <div class="modal-content">
            <form id="saobForm" novalidate>
                <div class="modal-header border-bottom">
                    <div class="d-flex align-items-center" style="min-width:0">
                        <div class="fods-modal-icon tone-teal"><i class="ti ti-report-analytics"></i></div>
                        <div style="min-width:0">
                            <h5 class="modal-title text-truncate" id="saobModalTitle">New allotment</h5>
                            <div class="fods-modal-sub" id="saobModalSub">Fill in the program details, then the line items.</div>
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-2 ms-auto ps-3 flex-shrink-0 text-nowrap">
                        <button type="button" class="btn-close ms-1" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                </div>

                <div class="modal-body" style="background:var(--fods-bg);">
                    <input type="hidden" id="recid" name="recid">
                    <input type="hidden" id="trxno" name="trxno">

                    <div id="saobBanner" class="alert d-none mb-3"></div>

                    <ul class="nav fods-segment mb-3 flex-nowrap fods-scroll-x" role="tablist" style="max-width:100%;">
                        <li class="nav-item"><button class="nav-link active" type="button" data-bs-toggle="tab" data-bs-target="#saobPaneDetails"><i class="ti ti-file-description"></i>Details</button></li>
                        <li class="nav-item"><button class="nav-link" type="button" data-bs-toggle="tab" data-bs-target="#saobPanePs"><i class="ti ti-users"></i>Personnel Services<span class="saob-tab-amt" data-tab-total="ps"></span></button></li>
                        <li class="nav-item"><button class="nav-link" type="button" data-bs-toggle="tab" data-bs-target="#saobPaneMooe"><i class="ti ti-tools"></i>MOOE<span class="saob-tab-amt" data-tab-total="mooe"></span></button></li>
                        <li class="nav-item"><button class="nav-link" type="button" data-bs-toggle="tab" data-bs-target="#saobPaneCo"><i class="ti ti-building"></i>Capital Outlay<span class="saob-tab-amt" data-tab-total="co"></span></button></li>
                    </ul>

                    <div class="tab-content">
                        <!-- Details -->
                        <div class="tab-pane fade show active" id="saobPaneDetails">
                            <div class="row g-3">
                                <div class="col-lg-7">
                                    <div class="fods-card h-100"><div class="fods-card-body pt-3">
                                        <div class="fods-section mt-0">Program</div>
                                        <div class="row g-3">
                                            <div class="col-12">
                                                <label class="form-label" for="program_title">Program title</label>
                                                <input type="text" class="form-control" id="program_title" name="program_title" maxlength="250" placeholder="Type or pick a program" autocomplete="off" required>
                                                <div class="invalid-feedback">Program title is required.</div>
                                            </div>
                                            <div class="col-12">
                                                <label class="form-label" for="project_title">Project title</label>
                                                <input type="text" class="form-control" id="project_title" name="project_title" maxlength="250">
                                            </div>
                                            <div class="col-12">
                                                <label class="form-label" for="department">Department</label>
                                                <input type="text" class="form-control" id="department" name="department" maxlength="100" required>
                                                <div class="invalid-feedback">Department is required.</div>
                                            </div>
                                            <div class="col-md-8">
                                                <label class="form-label" for="agency">Agency</label>
                                                <input type="text" class="form-control" id="agency" name="agency" maxlength="100" required>
                                                <div class="invalid-feedback">Agency is required.</div>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label" for="current_year">Year</label>
                                                <select class="form-select" id="current_year" name="current_year" required></select>
                                                <div class="invalid-feedback">Required.</div>
                                            </div>
                                        </div>
                                    </div></div>
                                </div>

                                <div class="col-lg-5">
                                    <div class="fods-card h-100"><div class="fods-card-body pt-3">
                                        <div class="fods-section mt-0">Revision months</div>
                                        <div class="saob-months" id="saobMonths"></div>
                                        <div class="saob-months-note" id="saobMonthsNote"></div>

                                        <div class="saob-sum mt-3">
                                            <div class="saob-sum-row"><span>Total approved budget</span><input type="text" id="total_approved_combined" name="total_approved_combined" readonly tabindex="-1" aria-label="Total approved budget"></div>
                                            <div class="saob-sum-row"><span>Total revised allotment</span><input type="text" id="total_proposed_combined" name="total_proposed_combined" readonly tabindex="-1" aria-label="Total revised allotment"></div>
                                            <div class="saob-sum-row net"><span>Realignments</span><span id="saobBalance"></span></div>
                                        </div>
                                    </div></div>
                                </div>
                            </div>
                        </div>

                        <!-- Line items -->
                        <div class="tab-pane fade" id="saobPanePs">
                            <div class="saob-grid-card" data-grid="ps"></div>
                        </div>
                        <div class="tab-pane fade" id="saobPaneMooe">
                            <div class="saob-grid-card" data-grid="mooe"></div>
                        </div>
                        <div class="tab-pane fade" id="saobPaneCo">
                            <div class="saob-grid-card" data-grid="co"></div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer justify-content-between">
                    <div class="saob-totals">
                        <span>Approved <b id="saobFootApproved">₱0.00</b></span>
                        <span>Revised <b id="saobFootRevised">₱0.00</b></span>
                    </div>
                    <div class="fods-modal-actions d-flex gap-2">
                        <button type="button" class="btn btn-sm btn-fods-ghost px-3" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-sm btn-fods px-4" id="submitBtn">Save</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- SAOB / monthly status print preview -->
<div class="modal fade fods-modal" id="saobPrintModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-fullscreen-md-down">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="saobPrintTitle">Print preview</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0">
                <iframe id="saobPrintFrame" src="about:blank" style="width:100%; height:80vh; border:0;"></iframe>
            </div>
        </div>
    </div>
</div>

<script>
    var __saob_data = <?= json_encode($saobhddata, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
    var __saob_lookups = <?= json_encode($lookups, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
    var __saob_perms = <?= json_encode($perms) ?>;
    var __saob_open = <?= json_encode($open, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
    var __saob_copy = <?= json_encode($copy, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
</script>
<script src="<?=base_url('assets/js/report/mysaobreport.js?v=9');?>"></script>
<?php echo view('templates/myfooter.php'); ?>
