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
.saob-report-sub { font-size:.8rem; color:var(--fods-muted); margin:0 0 .9rem; font-weight:400; }
.rpt-asof { display:flex; align-items:center; gap:.5rem; }
.rpt-asof label { font-size:.8rem; font-weight:500; color:var(--fods-muted); margin:0; white-space:nowrap; }
.rpt-asof .form-control { width:auto; min-width:170px; border-radius:10px; border-color:var(--fods-border); }
.rpt-status-row { display:flex; flex-wrap:wrap; gap:1rem; align-items:flex-start; }
.rpt-program { flex:1 1 380px; min-width:0; }
.rpt-actions { margin-top:1.55rem !important; padding-top:0 !important; }
.rpt-hint { font-size:.74rem; color:var(--fods-muted); margin-top:.3rem; min-height:1.1em; }
.rpt-preview { display:grid; grid-template-columns:repeat(auto-fit, minmax(140px, 1fr)); gap:.75rem 1.25rem; margin-top:1rem; padding-top:1rem; border-top:1px dashed var(--fods-border); }
.rpt-preview span { display:block; font-size:.74rem; color:var(--fods-muted); }
.rpt-preview b { font-size:1rem; font-weight:700; color:var(--fods-text); font-variant-numeric:tabular-nums; }
.rpt-preview.is-loading b { opacity:.45; }
.rpt-period { display:inline-flex; align-items:center; gap:.45rem; padding:.45rem .8rem; border-radius:10px; background:var(--fods-teal-soft); color:var(--fods-teal); font-weight:600; font-size:.85rem; }
.rpt-range { display:grid; grid-template-columns:repeat(2, minmax(0, 1fr)); gap:1rem; align-items:end; }
.rpt-range .rpt-period { width:100%; padding:.55rem .8rem; }
.saob-report-actions { display:flex; flex-wrap:wrap; gap:.5rem; margin-top:auto; padding-top:1.25rem; }
.saob-report .form-label { font-size:.78rem; font-weight:500; color:var(--fods-muted); margin-bottom:.3rem; }
.saob-report .form-control, .saob-report .form-select { border-radius:10px; border-color:var(--fods-border); }
.saob-report .form-control:focus, .saob-report .form-select:focus { border-color:var(--fods-teal); box-shadow:0 0 0 3px var(--fods-teal-soft); }

/* Monthly status dashboard */
.st-toolbar { display:flex; flex-wrap:wrap; gap:.75rem 1rem; align-items:flex-end; margin-bottom:1rem; }
.st-field .form-label { font-size:.74rem; font-weight:500; color:var(--fods-muted); margin-bottom:.25rem; }
.st-field .form-control, .st-field .form-select { border-radius:10px; border-color:var(--fods-border); }
.st-program { flex:1 1 320px; min-width:0; }
.st-actions { display:flex; gap:.5rem; margin-left:auto; }
.st-body { background:var(--fods-bg); }
.st-cards { display:grid; gap:1rem; grid-template-columns:repeat(auto-fit, minmax(200px, 1fr)); margin-bottom:1rem; }
.st-body .fods-stat { min-height:72px; }
.st-chart-scroll { max-height:520px; overflow-y:auto; overflow-x:hidden; }
.st-stat-text { min-width:0; }
.st-body .fods-stat-value { font-size:clamp(.98rem, 1.15vw, 1.15rem); font-variant-numeric:tabular-nums; overflow-wrap:anywhere; }
.st-bar { height:8px; border-radius:99px; background:#eef1f5; overflow:hidden; }
.st-bar > span { display:block; height:100%; max-width:100%; border-radius:99px; background:#0f6f6f; transition:width .4s ease; }
.st-bar.st-low > span { background:#d97706; }
.st-bar.st-high > span { background:#15803d; }
.st-bar.st-over > span { background:#b91c1c; }
.st-legend { display:flex; flex-wrap:wrap; gap:.35rem .9rem; font-size:.74rem; color:var(--fods-muted); }
.st-legend span { display:inline-flex; align-items:center; gap:.3rem; }
.st-dot { width:9px; height:9px; border-radius:3px; display:inline-block; }
.st-dot.st-low { background:#d97706; }
.st-dot.st-mid { background:#0f6f6f; }
.st-dot.st-high { background:#15803d; }
.st-dot.st-over { background:#b91c1c; }
.st-count { font-size:.78rem; font-weight:500; color:var(--fods-muted); margin-left:.25rem; }
.st-search { max-width:280px; border-radius:10px; }
.st-table { font-size:.8rem; }
.st-table thead th { white-space:nowrap; cursor:pointer; user-select:none; font-size:.74rem; color:var(--fods-muted); font-weight:600; background:#f8fafc; }
.st-table th[data-sort]::after { content:"\21C5"; opacity:.25; margin-left:.3rem; font-size:.7rem; }
.st-table th.is-asc::after { content:"\2191"; opacity:.8; }
.st-table th.is-desc::after { content:"\2193"; opacity:.8; }
.st-table td { vertical-align:middle; }
.st-table td.num, .st-table tfoot td.num { text-align:right; font-variant-numeric:tabular-nums; white-space:nowrap; }
.st-table .st-proj { min-width:280px; max-width:440px; }
.st-table .st-who { min-width:150px; }
.st-table .st-proj b { display:block; font-weight:500; color:var(--fods-text); }
.st-table .st-proj small { color:var(--fods-muted); font-size:.72rem; }
.st-pct-col { min-width:160px; }
.st-pct { display:flex; align-items:center; gap:.5rem; }
.st-pct .st-bar { flex:1; min-width:60px; }
.st-pct b { min-width:56px; text-align:right; font-weight:600; font-variant-numeric:tabular-nums; }
.st-table tfoot td { font-weight:600; background:#f8fafc; }
.st-empty { text-align:center; color:var(--fods-muted); padding:2.5rem 1rem !important; }
@media (max-width: 767.98px) {
    .st-actions { margin-left:0; width:100%; }
    .st-actions .btn { flex:1; }
    .st-search { max-width:none; width:100%; }
}

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
    .rpt-asof { width:100%; }
    .rpt-asof .form-control { flex:1; }
    .rpt-actions { margin-top:0 !important; width:100%; }
    .rpt-actions .btn { flex:1; }
    .rpt-actions .btn:first-child { flex-basis:100%; }
    .rpt-preview .rpt-util { grid-column:1 / -1; }
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
            <div class="rpt-asof d-none" id="rptAsOfWrap">
                <label for="rptAsOf">As of</label>
                <input type="month" class="form-control form-control-sm" id="rptAsOf">
            </div>
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
            <!-- Program status: one program as of the month in the page head -->
            <div class="fods-card saob-report mb-3">
                <div class="fods-card-head">
                    <div>
                        <h2 class="fods-card-title"><i class="ti ti-chart-bar me-1"></i>Program status</h2>
                        <p class="saob-report-sub mb-0">Allotment, obligations and balance per project of one program, as of the month above.</p>
                    </div>
                </div>
                <div class="fods-card-body">
                    <div class="rpt-status-row">
                        <div class="rpt-program">
                            <label class="form-label" for="rptProgram">Program title</label>
                            <select class="form-select" id="rptProgram"></select>
                            <div class="rpt-hint" id="rptProgramHint"></div>
                        </div>
                        <div class="saob-report-actions rpt-actions">
                            <button type="button" class="btn btn-sm btn-fods px-3" id="rptMonthlyDash"><i class="ti ti-chart-bar me-1"></i>View dashboard</button>
                            <button type="button" class="btn btn-sm btn-fods-ghost px-3" id="rptMonthlyPrint"><i class="ti ti-printer me-1"></i>Print PDF</button>
                            <button type="button" class="btn btn-sm btn-fods-ghost px-3" id="rptMonthlyXlsx"><i class="ti ti-file-spreadsheet me-1"></i>Excel</button>
                        </div>
                    </div>
                    <div class="rpt-preview" id="rptPreview" hidden>
                        <div><span>Revised allotment</span><b data-rp="revised">&mdash;</b></div>
                        <div><span data-rp-label="this_month">This month</span><b data-rp="this_month">&mdash;</b></div>
                        <div><span>Obligated to date</span><b data-rp="todate">&mdash;</b></div>
                        <div><span>Balance</span><b data-rp="balance">&mdash;</b></div>
                        <div class="rpt-util"><span>Utilization</span><div class="d-flex align-items-center gap-2"><div class="st-bar flex-grow-1" id="rptUtilBar"><span style="width:0"></span></div><b data-rp="percent">&mdash;</b></div></div>
                    </div>
                </div>
            </div>

            <div class="row g-3">
                <div class="col-md-6">
                    <div class="fods-card saob-report">
                        <div class="fods-card-head">
                            <div>
                                <h2 class="fods-card-title"><i class="ti ti-report-analytics me-1"></i>SAOB statement</h2>
                                <p class="saob-report-sub mb-0">The full statement up to the month above.</p>
                            </div>
                        </div>
                        <div class="fods-card-body">
                            <div class="rpt-period"><i class="ti ti-calendar"></i><span id="rptSaobPeriod">&nbsp;</span></div>
                            <div class="saob-report-actions">
                                <button type="button" class="btn btn-sm btn-fods px-3" id="rptSaobPdf"><i class="ti ti-file-search me-1"></i>Preview PDF</button>
                                <button type="button" class="btn btn-sm btn-fods-ghost px-3" id="rptSaobXlsx"><i class="ti ti-file-spreadsheet me-1"></i>Excel</button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="fods-card saob-report">
                        <div class="fods-card-head">
                            <div>
                                <h2 class="fods-card-title"><i class="ti ti-file-invoice me-1"></i>ORS extraction</h2>
                                <p class="saob-report-sub mb-0">Every ORS line dated from the month you pick to the month above.</p>
                            </div>
                        </div>
                        <div class="fods-card-body">
                            <div class="rpt-range">
                                <div>
                                    <label class="form-label" for="rptFrom">From</label>
                                    <input type="month" class="form-control" id="rptFrom">
                                </div>
                                <div>
                                    <span class="form-label d-block">To</span>
                                    <div class="rpt-period mb-0"><i class="ti ti-calendar"></i><span id="rptOrsTo">&nbsp;</span></div>
                                </div>
                            </div>
                            <div class="saob-report-actions">
                                <button type="button" class="btn btn-sm btn-fods px-3" id="rptOrsXlsx"><i class="ti ti-file-spreadsheet me-1"></i>Excel</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ============================= EDITOR ============================= -->
<div class="modal fade fods-modal fods-flat" id="saobModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable modal-fullscreen-md-down saob-dialog">
        <div class="modal-content">
            <form id="saobForm" novalidate>
                <div class="modal-header">
                    <div class="d-flex align-items-center" style="min-width:0">
                        <div class="fods-modal-icon tone-teal"><i class="ti ti-report-analytics"></i></div>
                        <div style="min-width:0">
                            <h5 class="modal-title text-truncate" id="saobModalTitle">New allotment</h5>
                            <div class="fods-modal-sub" id="saobModalSub">Fill in the program details, then the line items.</div>
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-2 ms-auto ps-3 flex-shrink-0 text-nowrap">
                        <button type="button" class="btn btn-sm btn-fods-ghost d-none" data-history-module="saob" data-history-field="recid"><i class="ti ti-history me-1"></i>History</button>
                        <button type="button" class="btn-close ms-1" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                </div>

                <ul class="nav fods-tabs" role="tablist">
                    <li class="nav-item"><button class="nav-link active" type="button" data-bs-toggle="tab" data-bs-target="#saobPaneDetails"><i class="ti ti-file-description"></i>Details</button></li>
                    <li class="nav-item"><button class="nav-link" type="button" data-bs-toggle="tab" data-bs-target="#saobPanePs"><i class="ti ti-users"></i>Personnel Services<span class="saob-tab-amt" data-tab-total="ps"></span></button></li>
                    <li class="nav-item"><button class="nav-link" type="button" data-bs-toggle="tab" data-bs-target="#saobPaneMooe"><i class="ti ti-tools"></i>MOOE<span class="saob-tab-amt" data-tab-total="mooe"></span></button></li>
                    <li class="nav-item"><button class="nav-link" type="button" data-bs-toggle="tab" data-bs-target="#saobPaneCo"><i class="ti ti-building"></i>Capital Outlay<span class="saob-tab-amt" data-tab-total="co"></span></button></li>
                </ul>

                <div class="modal-body">
                    <input type="hidden" id="recid" name="recid">
                    <input type="hidden" id="trxno" name="trxno">

                    <div id="saobBanner" class="alert d-none mt-3 mb-0"></div>

                    <div class="tab-content">
                        <!-- Details -->
                        <div class="tab-pane fade show active" id="saobPaneDetails">
                            <section class="fods-fsec">
                                <div class="fods-fsec-head">
                                    <h6>Program</h6>
                                </div>
                                <div class="row g-3">
                                    <div class="col-12">
                                        <label class="form-label req" for="program_title">Program title</label>
                                        <input type="text" class="form-control" id="program_title" name="program_title" maxlength="250" placeholder="Type or pick a program" autocomplete="off" required>
                                        <div class="invalid-feedback">Program title is required.</div>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label" for="project_title">Project title</label>
                                        <input type="text" class="form-control" id="project_title" name="project_title" maxlength="250">
                                    </div>
                                    <div class="col-md-5">
                                        <label class="form-label req" for="department">Department</label>
                                        <input type="text" class="form-control" id="department" name="department" maxlength="100" required>
                                        <div class="invalid-feedback">Department is required.</div>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label req" for="agency">Agency</label>
                                        <input type="text" class="form-control" id="agency" name="agency" maxlength="100" required>
                                        <div class="invalid-feedback">Agency is required.</div>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label req" for="current_year">Budget year</label>
                                        <select class="form-select" id="current_year" name="current_year" required></select>
                                        <div class="invalid-feedback">Pick the budget year.</div>
                                    </div>
                                </div>
                            </section>

                            <section class="fods-fsec">
                                <div class="fods-fsec-head">
                                    <h6>Revision months</h6>
                                </div>
                                <div class="row g-4">
                                    <div class="col-lg-7">
                                        <div class="saob-months" id="saobMonths"></div>
                                        <div class="saob-months-note" id="saobMonthsNote"></div>
                                    </div>
                                    <div class="col-lg-5">
                                        <div class="saob-sum">
                                            <div class="saob-sum-row"><span>Total approved budget</span><input type="text" id="total_approved_combined" name="total_approved_combined" readonly tabindex="-1" aria-label="Total approved budget"></div>
                                            <div class="saob-sum-row"><span>Total revised allotment</span><input type="text" id="total_proposed_combined" name="total_proposed_combined" readonly tabindex="-1" aria-label="Total revised allotment"></div>
                                            <div class="saob-sum-row net"><span>Realignments</span><span id="saobBalance"></span></div>
                                        </div>
                                    </div>
                                </div>
                            </section>
                        </div>

                        <!-- Line items -->
                        <div class="tab-pane fade" id="saobPanePs">
                            <div class="fods-pane-pad"><div class="saob-grid-card" data-grid="ps"></div></div>
                        </div>
                        <div class="tab-pane fade" id="saobPaneMooe">
                            <div class="fods-pane-pad"><div class="saob-grid-card" data-grid="mooe"></div></div>
                        </div>
                        <div class="tab-pane fade" id="saobPaneCo">
                            <div class="fods-pane-pad"><div class="saob-grid-card" data-grid="co"></div></div>
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

<!-- Monthly status dashboard (same figures as Print status) -->
<div class="modal fade fods-modal" id="saobStatusModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable modal-fullscreen-lg-down saob-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <div class="d-flex align-items-center" style="min-width:0">
                    <div class="fods-modal-icon tone-teal"><i class="ti ti-chart-bar"></i></div>
                    <div style="min-width:0">
                        <h5 class="modal-title text-truncate" id="stTitle">Monthly status</h5>
                        <div class="fods-modal-sub text-truncate" id="stSub">&nbsp;</div>
                    </div>
                </div>
                <button type="button" class="btn-close ms-3" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body st-body">
                <div class="st-toolbar">
                    <div class="st-field st-program">
                        <label class="form-label" for="stProgram">Program title</label>
                        <select class="form-select form-select-sm" id="stProgram"></select>
                    </div>
                    <div class="st-field">
                        <label class="form-label" for="stMonth">As of</label>
                        <input type="month" class="form-control form-control-sm" id="stMonth">
                    </div>
                    <div class="st-field st-actions">
                        <button type="button" class="btn btn-sm btn-fods-ghost px-3" id="stXlsx"><i class="ti ti-file-spreadsheet me-1"></i>Excel</button>
                        <button type="button" class="btn btn-sm btn-fods px-3" id="stPrint"><i class="ti ti-printer me-1"></i>Print status</button>
                    </div>
                </div>

                <div class="st-cards" id="stCards">
                    <div class="fods-stat"><div class="fods-stat-icon tone-indigo"><i class="ti ti-wallet"></i></div><div class="st-stat-text"><div class="fods-stat-value" data-st="revised">&#8369;0.00</div><div class="fods-stat-label">Revised allotment</div></div></div>
                    <div class="fods-stat"><div class="fods-stat-icon tone-amber"><i class="ti ti-calendar-event"></i></div><div class="st-stat-text"><div class="fods-stat-value" data-st="this_month">&#8369;0.00</div><div class="fods-stat-label" id="stThisMonthLabel">Obligated this month</div></div></div>
                    <div class="fods-stat"><div class="fods-stat-icon tone-teal"><i class="ti ti-file-invoice"></i></div><div class="st-stat-text"><div class="fods-stat-value" data-st="todate">&#8369;0.00</div><div class="fods-stat-label">Obligated to date</div></div></div>
                    <div class="fods-stat"><div class="fods-stat-icon tone-green"><i class="ti ti-cash"></i></div><div class="st-stat-text"><div class="fods-stat-value" data-st="balance">&#8369;0.00</div><div class="fods-stat-label">Balance</div></div></div>
                    <div class="fods-stat"><div class="st-stat-text w-100"><div class="d-flex justify-content-between align-items-baseline gap-2"><div class="fods-stat-value" data-st="percent">0%</div><div class="fods-stat-label">Utilization</div></div><div class="st-bar mt-2" id="stUtilBar"><span style="width:0"></span></div></div></div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-lg-7">
                        <div class="fods-card h-100">
                            <div class="fods-card-head">
                                <h2 class="fods-card-title"><i class="ti ti-chart-bar me-1"></i>Utilization per project</h2>
                                <div class="st-legend">
                                    <span><i class="st-dot st-low"></i>Below 50%</span>
                                    <span><i class="st-dot st-mid"></i>50&ndash;89%</span>
                                    <span><i class="st-dot st-high"></i>90&ndash;100%</span>
                                    <span><i class="st-dot st-over"></i>Over 100%</span>
                                </div>
                            </div>
                            <div class="fods-card-body"><div class="st-chart-scroll"><div id="stProjectChart"></div></div></div>
                        </div>
                    </div>
                    <div class="col-lg-5">
                        <div class="fods-card h-100">
                            <div class="fods-card-head"><h2 class="fods-card-title"><i class="ti ti-chart-line me-1"></i>Obligations by month</h2></div>
                            <div class="fods-card-body"><div id="stMonthChart"></div></div>
                        </div>
                    </div>
                </div>

                <div class="fods-card">
                    <div class="fods-card-head">
                        <h2 class="fods-card-title"><i class="ti ti-list-details me-1"></i>Projects <span class="st-count" id="stCount"></span></h2>
                        <input type="search" class="form-control form-control-sm st-search" id="stSearch" placeholder="Search project, code, unit or leader">
                    </div>
                    <div class="table-responsive">
                        <table class="table st-table mb-0" id="stTable">
                            <thead>
                                <tr>
                                    <th data-sort="project_title">Project</th>
                                    <th data-sort="unit_division">Unit</th>
                                    <th data-sort="project_leader">Leader</th>
                                    <th data-sort="revised" class="text-end">Revised allotment</th>
                                    <th data-sort="this_month" class="text-end">This month</th>
                                    <th data-sort="todate" class="text-end">To date</th>
                                    <th data-sort="balance" class="text-end">Balance</th>
                                    <th data-sort="percent" class="st-pct-col">Utilization</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                            <tfoot></tfoot>
                        </table>
                    </div>
                </div>
            </div>
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
    var __saob_apex = <?= json_encode(base_url('assets/libs/apexcharts/dist/apexcharts.min.js')) ?>; // loaded on first use (status dashboard)
    var __saob_open = <?= json_encode($open, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
    var __saob_copy = <?= json_encode($copy, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
</script>
<script src="<?=base_url('assets/js/report/mysaobreport.js?v=17');?>"></script>
<?php echo view('templates/myfooter.php'); ?>
