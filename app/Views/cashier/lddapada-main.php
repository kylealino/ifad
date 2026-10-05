<?php
echo view('templates/myheader.php');
?>
<head>
<style>
/* DV lines grid */
.ld-grid-card { border:1px solid var(--fods-border); background:#fff; border-radius:12px; overflow:hidden; }
.ld-grid-wrap { overflow-x:auto; scrollbar-width:thin; }
.ld-grid { width:max-content; min-width:100%; table-layout:fixed; border-collapse:separate; border-spacing:0; font-size:.85rem; }
.ld-grid col.c-no { width:44px; }
.ld-grid col.c-dv { width:150px; }
.ld-grid col.c-payee { width:260px; }
.ld-grid col.c-acct { width:150px; }
.ld-grid col.c-serial { width:200px; }
.ld-grid col.c-uacs { width:140px; }
.ld-grid col.c-amt { width:140px; }
.ld-grid col.c-remarks { width:200px; }
.ld-grid col.c-act { width:52px; }
.ld-grid th { background:#f8fafc; color:var(--fods-muted); font-size:.68rem; font-weight:600; text-transform:uppercase; letter-spacing:.04em; padding:.6rem .5rem; border-bottom:1px solid var(--fods-border); white-space:nowrap; text-align:left; }
.ld-grid th.num, .ld-grid td.num { text-align:right; }
.ld-grid td { padding:.4rem .5rem; border-bottom:1px solid #f1f4f8; vertical-align:middle; background:#fff; }
.ld-grid input { width:100%; height:34px; border:1px solid var(--fods-border); background:#fff; padding:.3rem .55rem; font-size:.84rem; color:var(--fods-text); border-radius:8px; text-overflow:ellipsis; }
.ld-grid input:focus { outline:none; border-color:var(--fods-teal); box-shadow:0 0 0 3px var(--fods-teal-soft); }
.ld-grid input[type=number] { text-align:right; font-variant-numeric:tabular-nums; }
.ld-grid input.g-net { font-weight:600; }
.ld-grid input:disabled, .ld-grid input[readonly] { color:#475569; background:#f1f4f8; border-color:transparent; }
.ld-grid tfoot td { font-weight:600; background:#fafbfc; border-top:1px solid var(--fods-border); border-bottom:0; padding:.6rem .5rem; font-variant-numeric:tabular-nums; }
.ld-grid .sticky-l { position:sticky; left:0; z-index:1; }
.ld-grid .sticky-r { position:sticky; right:0; z-index:1; box-shadow:-8px 0 8px -8px rgba(15,23,42,.18); }
.ld-grid th.sticky-l, .ld-grid th.sticky-r { background:#f8fafc; z-index:2; }
.ld-grid tfoot .sticky-l, .ld-grid tfoot .sticky-r { background:#fafbfc; }
.ld-empty { padding:1.25rem; text-align:center; color:var(--fods-muted); font-size:.82rem; }
.ld-chips { display:flex; flex-wrap:wrap; gap:.35rem; min-height:36px; align-items:center; }
.ld-chip { display:inline-flex; align-items:center; gap:.35rem; padding:.25rem .55rem; border-radius:999px; background:var(--fods-teal-soft); color:var(--fods-teal); font-size:.78rem; font-weight:600; }
.ld-chip button { border:0; background:transparent; color:inherit; padding:0; line-height:1; font-size:.95rem; }
.ld-totals { display:flex; flex-wrap:wrap; gap:1.25rem; align-items:center; font-size:.82rem; color:var(--fods-muted); }
.ld-totals b { color:var(--fods-text); font-size:.95rem; font-variant-numeric:tabular-nums; }
@media (min-width: 1200px) { .ld-dialog { max-width: min(1320px, calc(100vw - 4rem)); } }
.ui-autocomplete { z-index:2000 !important; max-height:280px; overflow-y:auto; overflow-x:hidden; font-size:.82rem; }
.ui-autocomplete .ors-ac-sub { display:block; font-size:.72rem; color:var(--fods-muted); }
@media (max-width: 767.98px) { .ld-totals { gap:.5rem 1rem; width:100%; } }
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
                    <li class="breadcrumb-item active" aria-current="page">LDDAP-ADA</li>
                </ol>
            </nav>
            <h1 class="fods-page-title">LDDAP-ADA</h1>
            <p class="fods-page-sub">List of due and demandable accounts payable with advice to debit account — group DVs for payment, then send for approval.</p>
        </div>
        <div class="fods-page-actions d-flex">
            <select class="form-select form-select-sm fods-year" id="ldYear" aria-label="Year"></select>
            <?php if ($perms['add']): ?>
                <button type="button" class="btn btn-fods btn-sm px-3 py-2" id="ldTableAdd"><i class="ti ti-plus me-1"></i> New LDDAP-ADA</button>
            <?php endif; ?>
        </div>
    </div>

    <!-- Stat cards (filled by mylddapada.js for the selected year) -->
    <div class="row g-3 mb-4" id="ldTableCards">
        <div class="col-6 col-xl-3">
            <button type="button" class="fods-stat" data-table="ldTable" data-filter="pending">
                <div class="fods-stat-icon tone-amber"><i class="ti ti-hourglass"></i></div>
                <div><div class="fods-stat-value" data-card-value="pending">0</div><div class="fods-stat-label" data-card-label="pending">Pending</div></div>
            </button>
        </div>
        <div class="col-6 col-xl-3">
            <button type="button" class="fods-stat" data-table="ldTable" data-filter="disapproved">
                <div class="fods-stat-icon tone-red"><i class="ti ti-arrow-back-up"></i></div>
                <div><div class="fods-stat-value" data-card-value="returned">0</div><div class="fods-stat-label" data-card-label="returned">Returned</div></div>
            </button>
        </div>
        <div class="col-6 col-xl-3">
            <button type="button" class="fods-stat" data-table="ldTable" data-filter="approved">
                <div class="fods-stat-icon tone-green"><i class="ti ti-circle-check"></i></div>
                <div><div class="fods-stat-value" data-card-value="approved">0</div><div class="fods-stat-label" data-card-label="approved">Approved</div></div>
            </button>
        </div>
        <div class="col-6 col-xl-3">
            <button type="button" class="fods-stat" data-table="ldTable" data-filter="all">
                <div class="fods-stat-icon tone-teal"><i class="ti ti-cash"></i></div>
                <div><div class="fods-stat-value" data-card-value="total">₱0.00</div><div class="fods-stat-label" data-card-label="total">Total amount</div></div>
            </button>
        </div>
    </div>

    <div class="fods-card">
        <div class="fods-card-head">
            <div class="fods-filter" id="ldTableFilter">
                <button type="button" class="active" data-filter="all">All</button>
                <button type="button" data-filter="pending">Pending</button>
                <button type="button" data-filter="disapproved">Returned</button>
                <button type="button" data-filter="approved">Approved</button>
            </div>
            <div class="fods-search">
                <i class="ti ti-search"></i>
                <input type="search" class="form-control form-control-sm" id="ldTableSearch" placeholder="Search LDDAP-ADA no., check no....">
            </div>
        </div>
        <div class="fods-card-body fods-dt">
            <table id="ldTable" class="table fods-table fods-table-stack w-100">
                <thead>
                    <tr>
                        <th>LDDAP-ADA</th>
                        <th class="d-none d-md-table-cell">Date</th>
                        <th class="d-none d-lg-table-cell">Fund cluster</th>
                        <th class="text-end">Net amount</th>
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
<div class="modal fade fods-modal" id="ldModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable modal-fullscreen-md-down ld-dialog">
        <div class="modal-content">
            <form id="ldForm" novalidate>
                <div class="modal-header border-bottom">
                    <div class="d-flex align-items-center" style="min-width:0">
                        <div class="fods-modal-icon tone-teal"><i class="ti ti-building-bank"></i></div>
                        <div style="min-width:0">
                            <h5 class="modal-title text-truncate" id="ldModalTitle">New LDDAP-ADA</h5>
                            <div class="fods-modal-sub" id="ldModalSub">Fill in the account details, then add the DVs to pay.</div>
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-2 ms-auto ps-3 flex-shrink-0 text-nowrap">
                        <button type="button" class="btn btn-sm btn-fods-ghost d-none" id="ldPrintBtn"><i class="ti ti-printer me-1"></i>Print</button>
                        <button type="button" class="btn-close ms-1" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                </div>

                <div class="modal-body" style="background:var(--fods-bg);">
                    <input type="hidden" id="recid" name="recid">
                    <input type="hidden" id="funding_source" name="funding_source">

                    <div id="ldBanner" class="alert d-none mb-3"></div>

                    <div class="fods-card mb-3"><div class="fods-card-body pt-3">
                        <div class="fods-section mt-0">LDDAP-ADA</div>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label" for="lddapadano">LDDAP-ADA no.</label>
                                <input type="text" class="form-control" id="lddapadano" name="lddapadano">
                            </div>
                            <div class="col-md-4 col-6">
                                <label class="form-label" for="ckno">Check no.</label>
                                <input type="text" class="form-control" id="ckno" name="ckno">
                            </div>
                            <div class="col-md-4 col-6">
                                <label class="form-label" for="lddapada_date">Date</label>
                                <input type="date" class="form-control" id="lddapada_date" name="lddapada_date" required>
                                <div class="invalid-feedback">Date is required.</div>
                            </div>
                            <div class="col-md-2 col-6">
                                <label class="form-label" for="fund_cluster_code">Fund cluster</label>
                                <select class="form-select" id="fund_cluster_code" name="fund_cluster_code" required></select>
                                <div class="invalid-feedback">Required.</div>
                            </div>
                            <div class="col-md-4 col-6">
                                <label class="form-label" for="mds_branch">MDS-GSB branch</label>
                                <input type="text" class="form-control" id="mds_branch" name="mds_branch" readonly>
                            </div>
                            <div class="col-md-3 col-6">
                                <label class="form-label" for="mds_accountno">MDS sub-account no.</label>
                                <input type="text" class="form-control" id="mds_accountno" name="mds_accountno" readonly>
                            </div>
                            <div class="col-md-3 col-6 d-flex align-items-end">
                                <div class="form-check form-switch mb-2">
                                    <input class="form-check-input" type="checkbox" id="is_ci" value="1">
                                    <label class="form-check-label" for="is_ci">Internal creditor</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="ada_approver">ADA approver</label>
                                <select class="form-select" id="ada_approver" name="ada_approver"></select>
                                <div class="form-text" id="ada_approver_position"></div>
                            </div>
                        </div>
                    </div></div>

                    <div class="fods-card mb-3"><div class="fods-card-body pt-3">
                        <div class="fods-section mt-0">Disbursement vouchers</div>
                        <div class="row g-3 align-items-start">
                            <div class="col-lg-5">
                                <label class="form-label" for="dv_lookup">Add a DV</label>
                                <input type="text" class="form-control" id="dv_lookup" placeholder="Type or pick a DV no. / payee" autocomplete="off">
                            </div>
                            <div class="col-lg-7">
                                <label class="form-label">Selected DVs</label>
                                <div class="ld-chips" id="ldChips"></div>
                            </div>
                        </div>
                    </div></div>

                    <div class="ld-grid-card">
                        <div class="ld-grid-wrap">
                            <table class="ld-grid" id="ldGrid">
                                <colgroup><col class="c-no"><col class="c-dv"><col class="c-payee"><col class="c-acct"><col class="c-serial"><col class="c-uacs"><col class="c-amt"><col class="c-amt"><col class="c-amt"><col class="c-remarks"><col class="c-act"></colgroup>
                                <thead><tr><th class="sticky-l">#</th><th>DV no.</th><th>Payee</th><th>Account no.</th><th>ORS / BURS no.</th><th>Allotment class</th><th class="num">Gross</th><th class="num">Withholding tax</th><th class="num">Net amount</th><th>Remarks</th><th class="sticky-r"></th></tr></thead>
                                <tbody></tbody>
                                <tfoot><tr><td class="sticky-l"></td><td colspan="5">Total</td><td class="num" data-sum="gross"></td><td class="num" data-sum="tax"></td><td class="num" data-sum="net"></td><td></td><td class="sticky-r"></td></tr></tfoot>
                            </table>
                        </div>
                        <div class="ld-empty d-none" id="ldEmpty">No DVs yet — pick one above.</div>
                    </div>
                </div>

                <div class="modal-footer justify-content-between">
                    <div class="ld-totals">
                        <span>DVs <b id="ldFootCount">0</b></span>
                        <span>Gross <b id="ldFootGross">₱0.00</b></span>
                        <span>Net payable <b id="ldFootNet">₱0.00</b></span>
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
<div class="modal fade fods-modal" id="ldPrintModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-fullscreen-md-down">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">LDDAP-ADA — print preview</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0">
                <iframe id="ldPrintFrame" src="about:blank" style="width:100%; height:80vh; border:0;"></iframe>
            </div>
        </div>
    </div>
</div>

<script>
    var __ld_data = <?= json_encode($lddapdata, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
    var __ld_lookups = <?= json_encode($lookups, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
    var __ld_perms = <?= json_encode($perms) ?>;
    var __ld_open = <?= json_encode($open, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
</script>
<script src="<?=base_url('assets/js/cashier/mylddapada.js?v=2');?>"></script>
<?php echo view('templates/myfooter.php'); ?>
