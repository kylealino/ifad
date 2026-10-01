<?php
echo view('templates/myheader.php');
?>
<head>
<style>
/* Report lines grid */
.rc-grid-card { border:1px solid var(--fods-border); background:#fff; border-radius:12px; overflow:hidden; }
.rc-grid-head { display:flex; flex-wrap:wrap; gap:.5rem; align-items:center; justify-content:space-between; padding:.65rem .9rem; border-bottom:1px solid var(--fods-border); background:#fafbfc; }
.rc-grid-title { font-weight:600; color:var(--fods-text); }
.rc-grid-sub { font-size:.75rem; color:var(--fods-muted); }
.rc-grid-wrap { overflow-x:auto; scrollbar-width:thin; }
.rc-grid { width:max-content; min-width:100%; table-layout:fixed; border-collapse:separate; border-spacing:0; font-size:.85rem; }
.rc-grid col.c-no { width:44px; }
.rc-grid col.c-ld { width:190px; }
.rc-grid col.c-date { width:150px; }
.rc-grid col.c-ck { width:130px; }
.rc-grid col.c-dv { width:150px; }
.rc-grid col.c-serial { width:200px; }
.rc-grid col.c-rc { width:220px; }
.rc-grid col.c-payee { width:240px; }
.rc-grid col.c-part { width:280px; }
.rc-grid col.c-amt { width:130px; }
.rc-grid col.c-act { width:52px; }
.rc-grid th { background:#f8fafc; color:var(--fods-muted); font-size:.68rem; font-weight:600; text-transform:uppercase; letter-spacing:.04em; padding:.6rem .5rem; border-bottom:1px solid var(--fods-border); white-space:nowrap; text-align:left; }
.rc-grid th.num, .rc-grid td.num { text-align:right; }
.rc-grid td { padding:.4rem .5rem; border-bottom:1px solid #f1f4f8; vertical-align:middle; background:#fff; }
.rc-grid input { width:100%; height:34px; border:1px solid var(--fods-border); background:#fff; padding:.3rem .55rem; font-size:.84rem; color:var(--fods-text); border-radius:8px; text-overflow:ellipsis; }
.rc-grid input:focus { outline:none; border-color:var(--fods-teal); box-shadow:0 0 0 3px var(--fods-teal-soft); }
.rc-grid input[type=number] { text-align:right; font-variant-numeric:tabular-nums; }
.rc-grid input.g-net { font-weight:600; }
.rc-grid input:disabled { color:#475569; background:#f1f4f8; border-color:transparent; }
.rc-grid tfoot td { font-weight:600; background:#fafbfc; border-top:1px solid var(--fods-border); border-bottom:0; padding:.6rem .5rem; font-variant-numeric:tabular-nums; }
.rc-grid .sticky-l { position:sticky; left:0; z-index:1; }
.rc-grid .sticky-r { position:sticky; right:0; z-index:1; box-shadow:-8px 0 8px -8px rgba(15,23,42,.18); }
.rc-grid th.sticky-l, .rc-grid th.sticky-r { background:#f8fafc; z-index:2; }
.rc-grid tfoot .sticky-l, .rc-grid tfoot .sticky-r { background:#fafbfc; }
.rc-empty { padding:1.25rem; text-align:center; color:var(--fods-muted); font-size:.82rem; }
.rc-totals { display:flex; flex-wrap:wrap; gap:1.25rem; align-items:center; font-size:.82rem; color:var(--fods-muted); }
.rc-totals b { color:var(--fods-text); font-size:.95rem; font-variant-numeric:tabular-nums; }
.fods-stat.is-static { cursor:default; }
@media (min-width: 1200px) { .rc-dialog { max-width: min(1320px, calc(100vw - 4rem)); } }
.ui-autocomplete { z-index:2000 !important; max-height:280px; overflow-y:auto; overflow-x:hidden; font-size:.82rem; }
.ui-autocomplete .ors-ac-sub { display:block; font-size:.72rem; color:var(--fods-muted); }
@media (max-width: 767.98px) { .rc-totals { gap:.5rem 1rem; width:100%; } }
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
                    <li class="breadcrumb-item"><a href="#">Cashier</a></li>
                    <li class="breadcrumb-item active" aria-current="page">RCI</li>
                </ol>
            </nav>
            <h1 class="fods-page-title">Report of Checks Issued</h1>
            <p class="fods-page-sub">Report the checks and ADAs issued — pick the LDDAP-ADAs and their DVs fill in.</p>
        </div>
        <div class="fods-page-actions d-flex">
            <select class="form-select form-select-sm fods-year" id="rcYear" aria-label="Year"></select>
            <?php if ($perms['add']): ?>
                <button type="button" class="btn btn-fods btn-sm px-3 py-2" id="rcTableAdd"><i class="ti ti-plus me-1"></i> New RCI</button>
            <?php endif; ?>
        </div>
    </div>

    <!-- Stat cards (filled by myrci.js for the selected year) -->
    <div class="row g-3 mb-4" id="rcTableCards">
        <div class="col-6 col-xl-3">
            <div class="fods-stat is-static">
                <div class="fods-stat-icon tone-indigo"><i class="ti ti-report"></i></div>
                <div><div class="fods-stat-value" data-card-value="reports">0</div><div class="fods-stat-label" data-card-label="reports">Reports</div></div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="fods-stat is-static">
                <div class="fods-stat-icon tone-amber"><i class="ti ti-building-bank"></i></div>
                <div><div class="fods-stat-value" data-card-value="lddapadas">0</div><div class="fods-stat-label" data-card-label="lddapadas">LDDAP-ADAs covered</div></div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="fods-stat is-static">
                <div class="fods-stat-icon tone-green"><i class="ti ti-file-dollar"></i></div>
                <div><div class="fods-stat-value" data-card-value="dvs">0</div><div class="fods-stat-label" data-card-label="dvs">DVs paid</div></div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="fods-stat is-static">
                <div class="fods-stat-icon tone-teal"><i class="ti ti-cash"></i></div>
                <div><div class="fods-stat-value" data-card-value="total">₱0.00</div><div class="fods-stat-label" data-card-label="total">Total amount</div></div>
            </div>
        </div>
    </div>

    <div class="fods-card">
        <div class="fods-card-head">
            <div class="fw-semibold text-dark">Reports</div>
            <div class="fods-search">
                <i class="ti ti-search"></i>
                <input type="search" class="form-control form-control-sm" id="rcTableSearch" placeholder="Search report no., LDDAP-ADA no....">
            </div>
        </div>
        <div class="fods-card-body fods-dt">
            <table id="rcTable" class="table fods-table fods-table-stack w-100">
                <thead>
                    <tr>
                        <th>Report</th>
                        <th class="d-none d-lg-table-cell">Account</th>
                        <th class="d-none d-md-table-cell">DVs</th>
                        <th class="text-end">Net amount</th>
                        <th class="d-none d-xl-table-cell">Encoded</th>
                        <th class="text-end"></th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>

<!-- ============================= EDITOR ============================= -->
<div class="modal fade fods-modal" id="rcModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable modal-fullscreen-md-down rc-dialog">
        <div class="modal-content">
            <form id="rcForm" novalidate>
                <div class="modal-header border-bottom">
                    <div class="d-flex align-items-center" style="min-width:0">
                        <div class="fods-modal-icon tone-teal"><i class="ti ti-report"></i></div>
                        <div style="min-width:0">
                            <h5 class="modal-title text-truncate" id="rcModalTitle">New RCI</h5>
                            <div class="fods-modal-sub" id="rcModalSub">Fill in the report details, then add the LDDAP-ADAs.</div>
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-2 ms-auto ps-3 flex-shrink-0 text-nowrap">
                        <button type="button" class="btn btn-sm btn-fods-ghost d-none" id="rcPrintBtn"><i class="ti ti-printer me-1"></i>Print</button>
                        <button type="button" class="btn-close ms-1" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                </div>

                <div class="modal-body" style="background:var(--fods-bg);">
                    <input type="hidden" id="recid" name="recid">

                    <div class="fods-card mb-3"><div class="fods-card-body pt-3">
                        <div class="fods-section mt-0">Report</div>
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label" for="reportno">Report no.</label>
                                <input type="text" class="form-control" id="reportno" name="reportno">
                            </div>
                            <div class="col-md-2 col-6">
                                <label class="form-label" for="fund_cluster_code">Fund cluster</label>
                                <select class="form-select" id="fund_cluster_code" name="fund_cluster_code" required></select>
                                <div class="invalid-feedback">Required.</div>
                            </div>
                            <div class="col-md-4 col-6">
                                <label class="form-label" for="mds_branch">MDS-GSB branch</label>
                                <input type="text" class="form-control" id="mds_branch" name="mds_branch" required>
                                <div class="invalid-feedback">Required.</div>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label" for="mds_accountno">MDS sub-account no.</label>
                                <input type="text" class="form-control" id="mds_accountno" name="mds_accountno">
                            </div>
                        </div>
                    </div></div>

                    <div class="rc-grid-card">
                        <div class="rc-grid-head">
                            <div>
                                <div class="rc-grid-title">Checks / ADAs issued <span class="rc-grid-sub" id="rcCount"></span></div>
                                <div class="rc-grid-sub">Picking an LDDAP-ADA adds one line per DV on it</div>
                            </div>
                            <div class="d-flex flex-wrap gap-2 align-items-center" id="rcAddRow">
                                <input type="text" class="form-control form-control-sm" id="ld_lookup" placeholder="Add an LDDAP-ADA…" autocomplete="off" style="min-width:260px;">
                                <button type="button" class="btn btn-sm btn-fods-ghost" id="rcAddBlank"><i class="ti ti-plus me-1"></i>Blank line</button>
                            </div>
                        </div>
                        <div class="rc-grid-wrap">
                            <table class="rc-grid" id="rcGrid">
                                <colgroup><col class="c-no"><col class="c-ld"><col class="c-date"><col class="c-ck"><col class="c-dv"><col class="c-serial"><col class="c-rc"><col class="c-payee"><col class="c-part"><col class="c-amt"><col class="c-amt"><col class="c-amt"><col class="c-amt"><col class="c-act"></colgroup>
                                <thead><tr><th class="sticky-l">#</th><th>LDDAP-ADA no.</th><th>Check date</th><th>Check no.</th><th>DV no.</th><th>ORS / BURS no.</th><th>Responsibility center</th><th>Payee</th><th>Nature of payment</th><th class="num">Gross</th><th class="num">PhilHealth</th><th class="num">Tax</th><th class="num">Net amount</th><th class="sticky-r"></th></tr></thead>
                                <tbody></tbody>
                                <tfoot><tr><td class="sticky-l"></td><td colspan="8">Total</td><td class="num" data-sum="gross"></td><td class="num" data-sum="philhealth"></td><td class="num" data-sum="tax"></td><td class="num" data-sum="net"></td><td class="sticky-r"></td></tr></tfoot>
                            </table>
                        </div>
                        <div class="rc-empty d-none" id="rcEmpty">No lines yet — add an LDDAP-ADA above.</div>
                    </div>
                </div>

                <div class="modal-footer justify-content-between">
                    <div class="rc-totals">
                        <span>DVs <b id="rcFootCount">0</b></span>
                        <span>Gross <b id="rcFootGross">₱0.00</b></span>
                        <span>Net <b id="rcFootNet">₱0.00</b></span>
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

<!-- Print preview -->
<div class="modal fade fods-modal" id="rcPrintModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-fullscreen-md-down">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">RCI — print preview</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0">
                <iframe id="rcPrintFrame" src="about:blank" style="width:100%; height:80vh; border:0;"></iframe>
            </div>
        </div>
    </div>
</div>

<script>
    var __rc_data = <?= json_encode($rcidata, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
    var __rc_lookups = <?= json_encode($lookups, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
    var __rc_perms = <?= json_encode($perms) ?>;
    var __rc_open = <?= json_encode($open, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
</script>
<script src="<?=base_url('assets/js/cashier/myrci.js?v=2');?>"></script>
<?php echo view('templates/myfooter.php'); ?>
