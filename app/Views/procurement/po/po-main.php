<?php
echo view('templates/myheader.php');
?>
<head>
<style>
/* Editor grid */
.po-grid-card { border:1px solid var(--fods-border); background:#fff; margin-bottom:1rem; border-radius:12px; overflow:hidden; }
.po-grid-head { display:flex; flex-wrap:wrap; gap:.5rem; align-items:center; justify-content:space-between; padding:.65rem .9rem; border-bottom:1px solid var(--fods-border); background:#fafbfc; }
.po-grid-title { font-weight:600; color:var(--fods-text); }
.po-grid-sub { font-size:.75rem; color:var(--fods-muted); }
.po-grid-wrap { overflow-x:auto; scrollbar-width:thin; }
/* sized so every column shows without scrolling in the 1200px dialog */
.po-grid { width:max-content; min-width:100%; table-layout:fixed; border-collapse:separate; border-spacing:0; font-size:.85rem; }
.po-grid col.c-act { width:84px; }
.po-grid col.c-stock { width:130px; }
.po-grid col.c-unit { width:100px; }
.po-grid col.c-desc { width:390px; }
.po-grid col.c-qty { width:100px; }
.po-grid col.c-amt { width:150px; }
.po-grid th { background:#f8fafc; color:var(--fods-muted); font-size:.68rem; font-weight:600; text-transform:uppercase; letter-spacing:.04em; padding:.6rem .6rem; border-bottom:1px solid var(--fods-border); white-space:nowrap; text-align:left; }
.po-grid th.num, .po-grid td.num { text-align:right; }
.po-grid td { padding:.45rem .6rem; border-bottom:1px solid #f1f4f8; vertical-align:middle; background:#fff; }
.po-grid tbody tr:hover td { background:#fbfcfd; }
.po-grid input, .po-grid textarea { width:100%; min-height:36px; border:1px solid var(--fods-border); background:#fff; padding:.35rem .65rem; font-size:.85rem; color:var(--fods-text); border-radius:8px; }
.po-grid input { height:36px; text-overflow:ellipsis; }
.po-grid textarea { display:block; resize:vertical; line-height:1.4; }
.po-grid input::placeholder, .po-grid textarea::placeholder { color:#a3adbb; }
.po-grid input:hover:not(:disabled):not([readonly]), .po-grid textarea:hover:not(:disabled) { border-color:#c7d0db; }
.po-grid input:focus, .po-grid textarea:focus { outline:none; border-color:var(--fods-teal); background:#fff; box-shadow:0 0 0 3px var(--fods-teal-soft); }
.po-grid input[type=number] { text-align:right; font-variant-numeric:tabular-nums; font-weight:600; }
.po-grid input:disabled, .po-grid input[readonly], .po-grid textarea:disabled { color:#475569; background:#f1f4f8; border-color:transparent; }
.po-grid tfoot td { font-weight:600; background:#fafbfc; border-top:1px solid var(--fods-border); border-bottom:0; padding:.6rem .6rem; font-variant-numeric:tabular-nums; }
/* Row actions stay on the left and the amount on the right while scrolling */
.po-grid .sticky-l { position:sticky; left:0; z-index:1; }
.po-grid .sticky-r { position:sticky; right:0; z-index:1; box-shadow:-8px 0 8px -8px rgba(15,23,42,.18); }
.po-grid th.sticky-l, .po-grid th.sticky-r { background:#f8fafc; z-index:2; }
.po-grid tfoot .sticky-l, .po-grid tfoot .sticky-r { background:#fafbfc; }
.po-row-actions { display:flex; gap:.1rem; }
.po-row-actions .fods-icon-btn { width:28px; height:28px; font-size:.95rem; }
.po-empty { padding:1.25rem; text-align:center; color:var(--fods-muted); font-size:.82rem; }

/* The PR the PO is prepared from */
.po-source { border:1px dashed #c7d0db; background:#fafbfc; border-radius:12px; padding:.8rem 1rem; }
.po-note { font-size:.78rem; color:var(--fods-muted); }

/* Signatory status (Certification tab) */
.po-sign { border:1px solid var(--fods-border); border-radius:12px; padding:.85rem 1rem; background:#fff; height:100%; }
.po-sign-head { display:flex; justify-content:space-between; align-items:center; gap:.5rem; margin-bottom:.6rem; }
.po-sign-box { font-size:.7rem; font-weight:600; text-transform:uppercase; letter-spacing:.05em; color:var(--fods-muted); }
.po-sign-note { font-size:.75rem; color:var(--fods-muted); margin-top:.5rem; }

/* Editor footer totals */
.po-totals { display:flex; flex-wrap:wrap; gap:1.25rem; align-items:center; font-size:.82rem; color:var(--fods-muted); }
.po-totals b { color:var(--fods-text); font-size:.95rem; font-variant-numeric:tabular-nums; }
.po-tab-amt { font-size:.7rem; color:var(--fods-muted); margin-left:.35rem; font-weight:500; }
.po-dialog .fods-segment .nav-link { white-space:nowrap; }

/* Editor dialog: a bit wider than modal-xl on big screens so the grid fits */
@media (min-width: 1200px) {
    .po-dialog { max-width: min(1200px, calc(100vw - 4rem)); }
}

/* jQuery UI autocomplete above the modal */
.ui-autocomplete { z-index:2000 !important; max-height:280px; overflow-y:auto; overflow-x:hidden; font-size:.82rem; }
.ui-autocomplete .po-ac-sub { display:block; font-size:.72rem; color:var(--fods-muted); }

@media (max-width: 767.98px) {
    .po-totals { gap:.5rem 1rem; width:100%; }
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
                    <li class="breadcrumb-item"><a href="#">Procurement</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Purchase Order</li>
                </ol>
            </nav>
            <h1 class="fods-page-title">Purchase Order</h1>
            <p class="fods-page-sub">Prepare purchase orders from purchase requests, then send them for accounting and approval.</p>
        </div>
        <div class="fods-page-actions d-flex">
            <select class="form-select form-select-sm fods-year" id="poYear" aria-label="Year"></select>
            <?php if ($perms['add']): ?>
                <button type="button" class="btn btn-fods btn-sm px-3 py-2" id="poTableAdd"><i class="ti ti-plus me-1"></i> New PO</button>
            <?php endif; ?>
        </div>
    </div>

    <!-- Stat cards (filled by mypo.js for the selected year) -->
    <div class="row g-3 mb-4" id="poTableCards">
        <div class="col-6 col-xl-3">
            <button type="button" class="fods-stat" data-table="poTable" data-filter="pending">
                <div class="fods-stat-icon tone-amber"><i class="ti ti-hourglass"></i></div>
                <div><div class="fods-stat-value" data-card-value="pending">0</div><div class="fods-stat-label" data-card-label="pending">Pending</div></div>
            </button>
        </div>
        <div class="col-6 col-xl-3">
            <button type="button" class="fods-stat" data-table="poTable" data-filter="disapproved">
                <div class="fods-stat-icon tone-red"><i class="ti ti-arrow-back-up"></i></div>
                <div><div class="fods-stat-value" data-card-value="returned">0</div><div class="fods-stat-label" data-card-label="returned">Returned</div></div>
            </button>
        </div>
        <div class="col-6 col-xl-3">
            <button type="button" class="fods-stat" data-table="poTable" data-filter="approved">
                <div class="fods-stat-icon tone-green"><i class="ti ti-circle-check"></i></div>
                <div><div class="fods-stat-value" data-card-value="approved">0</div><div class="fods-stat-label" data-card-label="approved">Approved</div></div>
            </button>
        </div>
        <div class="col-6 col-xl-3">
            <button type="button" class="fods-stat" data-table="poTable" data-filter="all">
                <div class="fods-stat-icon tone-teal"><i class="ti ti-cash"></i></div>
                <div><div class="fods-stat-value" data-card-value="total">₱0.00</div><div class="fods-stat-label" data-card-label="total">Total amount</div></div>
            </button>
        </div>
    </div>

    <div class="fods-card">
        <div class="fods-card-head">
            <div class="fods-filter" id="poTableFilter">
                <button type="button" class="active" data-filter="all">All</button>
                <button type="button" data-filter="pending">Pending</button>
                <button type="button" data-filter="disapproved">Returned</button>
                <button type="button" data-filter="approved">Approved</button>
            </div>
            <div class="fods-search">
                <i class="ti ti-search"></i>
                <input type="search" class="form-control form-control-sm" id="poTableSearch" placeholder="Search PO no., supplier, PR no...">
            </div>
        </div>
        <div class="fods-card-body fods-dt">
            <table id="poTable" class="table fods-table fods-table-stack w-100">
                <thead>
                    <tr>
                        <th>Purchase order</th>
                        <th class="d-none d-md-table-cell">Supplier</th>
                        <th class="d-none d-lg-table-cell">Date</th>
                        <th class="text-end">Amount</th>
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
<div class="modal fade fods-modal" id="poModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable modal-fullscreen-md-down po-dialog">
        <div class="modal-content">
            <form id="poForm" novalidate>
                <div class="modal-header border-bottom">
                    <div class="d-flex align-items-center" style="min-width:0">
                        <div class="fods-modal-icon tone-teal"><i class="ti ti-receipt"></i></div>
                        <div style="min-width:0">
                            <h5 class="modal-title text-truncate" id="poModalTitle">New purchase order</h5>
                            <div class="fods-modal-sub" id="poModalSub">Pick the purchase request to prepare it from, then the supplier.</div>
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-2 ms-auto ps-3 flex-shrink-0 text-nowrap">
                        <button type="button" class="btn btn-sm btn-fods-ghost d-none" id="poPrintBtn"><i class="ti ti-printer me-1"></i>Print</button>
                        <button type="button" class="btn-close ms-1" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                </div>

                <div class="modal-body" style="background:var(--fods-bg);">
                    <input type="hidden" id="recid" name="recid">
                    <input type="hidden" id="pr_id" name="pr_id">

                    <div id="poBanner" class="alert d-none mb-3"></div>

                    <ul class="nav fods-segment mb-3 flex-nowrap fods-scroll-x" role="tablist" style="max-width:100%;">
                        <li class="nav-item"><button class="nav-link active" type="button" data-bs-toggle="tab" data-bs-target="#poPaneDetails"><i class="ti ti-file-description"></i>Details</button></li>
                        <li class="nav-item"><button class="nav-link" type="button" data-bs-toggle="tab" data-bs-target="#poPaneItems"><i class="ti ti-list-details"></i>Items<span class="po-tab-amt" data-tab-total></span></button></li>
                        <li class="nav-item"><button class="nav-link" type="button" data-bs-toggle="tab" data-bs-target="#poPaneTerms"><i class="ti ti-truck-delivery"></i>Delivery &amp; funds</button></li>
                        <li class="nav-item"><button class="nav-link" type="button" data-bs-toggle="tab" data-bs-target="#poPaneSign"><i class="ti ti-signature"></i>Certification</button></li>
                    </ul>

                    <div class="tab-content">
                        <!-- Details -->
                        <div class="tab-pane fade show active" id="poPaneDetails">
                            <div class="row g-3">
                                <div class="col-lg-6">
                                    <div class="fods-card h-100"><div class="fods-card-body pt-3">
                                        <div class="po-source mb-3">
                                            <label class="form-label" for="prno">Purchase request</label>
                                            <input type="text" class="form-control" id="prno" name="prno" maxlength="100" placeholder="Type or pick the PR no. this PO is for" autocomplete="off">
                                            <div class="form-text" id="poSourceHint">Picking one copies its charge-to, end-user, fund cluster and items into this PO.</div>
                                        </div>

                                        <div class="fods-section mt-0">Order</div>
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <label class="form-label" for="pono">PO no.</label>
                                                <input type="text" class="form-control" id="pono" name="pono" maxlength="100" placeholder="e.g. 570-2025">
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label" for="po_date">PO date</label>
                                                <input type="date" class="form-control" id="po_date" name="po_date" required>
                                                <div class="invalid-feedback">PO date is required.</div>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label" for="pr_date">PR date</label>
                                                <input type="date" class="form-control" id="pr_date" name="pr_date">
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label" for="mode_of_procurement">Mode of procurement</label>
                                                <input type="text" class="form-control" id="mode_of_procurement" name="mode_of_procurement" maxlength="100" placeholder="Pick a mode" autocomplete="off">
                                            </div>
                                            <div class="col-12">
                                                <label class="form-label" for="charge_to">Charged to</label>
                                                <textarea class="form-control" id="charge_to" name="charge_to" rows="2" maxlength="250"></textarea>
                                            </div>
                                            <div class="col-12">
                                                <label class="form-label" for="end_user">End-user</label>
                                                <input type="text" class="form-control" id="end_user" name="end_user" maxlength="100">
                                            </div>
                                        </div>
                                    </div></div>
                                </div>

                                <div class="col-lg-6">
                                    <div class="fods-card h-100"><div class="fods-card-body pt-3">
                                        <div class="fods-section mt-0">Supplier</div>
                                        <div class="row g-3">
                                            <div class="col-12" id="poRfqPick">
                                                <label class="form-label" for="po_rfq">From an RFQ of the PR</label>
                                                <select class="form-select" id="po_rfq" aria-label="RFQ"></select>
                                                <div class="form-text">Picking one fills the supplier, delivery term and payment term.</div>
                                            </div>
                                            <div class="col-12">
                                                <label class="form-label" for="supplier_name">Supplier</label>
                                                <input type="text" class="form-control" id="supplier_name" name="supplier_name" maxlength="250" placeholder="Type or pick a supplier" autocomplete="off" required>
                                                <div class="invalid-feedback">Supplier is required.</div>
                                            </div>
                                            <div class="col-12">
                                                <label class="form-label" for="supplier_address">Address</label>
                                                <input type="text" class="form-control" id="supplier_address" name="supplier_address" maxlength="250">
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label" for="supplier_tel">Telephone</label>
                                                <input type="text" class="form-control" id="supplier_tel" name="supplier_tel" maxlength="100">
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label" for="supplier_tin">TIN</label>
                                                <input type="text" class="form-control" id="supplier_tin" name="supplier_tin" maxlength="100">
                                            </div>
                                        </div>
                                    </div></div>
                                </div>
                            </div>
                        </div>

                        <!-- Items -->
                        <div class="tab-pane fade" id="poPaneItems">
                            <div class="po-grid-card" id="poGrid"></div>
                        </div>

                        <!-- Delivery & funds -->
                        <div class="tab-pane fade" id="poPaneTerms">
                            <div class="row g-3">
                                <div class="col-lg-6">
                                    <div class="fods-card h-100"><div class="fods-card-body pt-3">
                                        <div class="fods-section mt-0">Delivery</div>
                                        <div class="row g-3">
                                            <div class="col-12">
                                                <label class="form-label" for="place_of_delivery">Place of delivery</label>
                                                <input type="text" class="form-control" id="place_of_delivery" name="place_of_delivery" maxlength="250">
                                            </div>
                                            <div class="col-12">
                                                <label class="form-label" for="date_of_delivery">Date of delivery</label>
                                                <input type="text" class="form-control" id="date_of_delivery" name="date_of_delivery" maxlength="250">
                                            </div>
                                            <div class="col-12">
                                                <label class="form-label" for="delivery_term">Delivery term</label>
                                                <input type="text" class="form-control" id="delivery_term" name="delivery_term" maxlength="250">
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label" for="payment_term">Payment term</label>
                                                <input type="text" class="form-control" id="payment_term" name="payment_term" maxlength="100">
                                            </div>
                                        </div>

                                    </div></div>
                                </div>

                                <div class="col-lg-6">
                                    <div class="fods-card h-100"><div class="fods-card-body pt-3">
                                        <div class="fods-section mt-0">Funds</div>
                                        <div class="row g-3">
                                            <div class="col-md-5">
                                                <label class="form-label" for="fund_cluster">Fund cluster</label>
                                                <input type="text" class="form-control" id="fund_cluster" name="fund_cluster" maxlength="50" placeholder="Pick a cluster" autocomplete="off">
                                            </div>
                                            <div class="col-md-7">
                                                <label class="form-label" for="funds_available">Funds available</label>
                                                <input type="number" step="any" class="form-control text-end" id="funds_available" name="funds_available" placeholder="0.00">
                                            </div>
                                        </div>

                                        <div class="fods-section">Obligation</div>
                                        <div class="row g-3">
                                            <div class="col-12">
                                                <label class="form-label" for="ors_burs_no">ORS / BURS no.</label>
                                                <input type="text" class="form-control" id="ors_burs_no" name="ors_burs_no" maxlength="100" placeholder="Type or pick the ORS / BURS" autocomplete="off">
                                                <div class="form-text">Filled in once the PO is obligated; picking one fills its date and amount.</div>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label" for="ors_burs_date">Date of the ORS / BURS</label>
                                                <input type="date" class="form-control" id="ors_burs_date" name="ors_burs_date">
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label" for="ors_burs_amount">Amount</label>
                                                <input type="number" step="any" class="form-control text-end" id="ors_burs_amount" name="ors_burs_amount" placeholder="0.00">
                                            </div>
                                        </div>
                                    </div></div>
                                </div>
                            </div>
                        </div>

                        <!-- Certification -->
                        <div class="tab-pane fade" id="poPaneSign">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="po-sign">
                                        <div class="po-sign-head"><span class="po-sign-box">Box A · Accounting</span><span id="poSignStatusA"></span></div>
                                        <label class="form-label" for="accountant">Funds available certified by</label>
                                        <select class="form-select" id="accountant" name="accountant" required></select>
                                        <div class="invalid-feedback">Pick the accountant who certifies the funds.</div>
                                        <label class="form-label mt-2" for="accountant_position">Position</label>
                                        <input type="text" class="form-control" id="accountant_position" name="accountant_position" readonly>
                                        <div class="po-sign-note" id="poSignNoteA"></div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="po-sign">
                                        <div class="po-sign-head"><span class="po-sign-box">Box B · Approval</span><span id="poSignStatusB"></span></div>
                                        <label class="form-label" for="approved_by">Approved by (very truly yours)</label>
                                        <input type="text" class="form-control" id="approved_by" name="approved_by" readonly>
                                        <label class="form-label mt-2" for="approved_position">Position</label>
                                        <input type="text" class="form-control" id="approved_position" name="approved_position" readonly>
                                        <div class="form-text" id="poApprovalRule"></div>
                                        <div class="po-sign-note" id="poSignNoteB"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer justify-content-between">
                    <div class="po-totals">
                        <span>Items <b id="poFootItems">0</b></span>
                        <span>Total amount <b id="poFootTotal">₱0.00</b></span>
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

<!-- PO print preview -->
<div class="modal fade fods-modal" id="poPrintModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-fullscreen-md-down">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Purchase order — print preview</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0">
                <iframe id="poPrintFrame" src="about:blank" style="width:100%; height:80vh; border:0;"></iframe>
            </div>
        </div>
    </div>
</div>

<script>
    var __po_data = <?= json_encode($pohddata, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
    var __po_lookups = <?= json_encode($lookups, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
    var __po_perms = <?= json_encode($perms) ?>;
    var __po_open = <?= json_encode($open, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
</script>
<script src="<?=base_url('assets/js/procurement/po/mypo.js?v=4');?>"></script>
<?php echo view('templates/myfooter.php'); ?>
