<?php
echo view('templates/myheader.php');
?>
<head>
<style>
/* Editor grid */
.pr-grid-card { border:1px solid var(--fods-border); background:#fff; margin-bottom:1rem; border-radius:12px; overflow:hidden; }
.pr-grid-head { display:flex; flex-wrap:wrap; gap:.5rem; align-items:center; justify-content:space-between; padding:.65rem .9rem; border-bottom:1px solid var(--fods-border); background:#fafbfc; }
.pr-grid-title { font-weight:600; color:var(--fods-text); }
.pr-grid-sub { font-size:.75rem; color:var(--fods-muted); }
.pr-grid-wrap { overflow-x:auto; scrollbar-width:thin; }
/* Fixed, roomy columns: the grid scrolls sideways instead of squeezing the fields */
.pr-grid { width:max-content; min-width:100%; table-layout:fixed; border-collapse:separate; border-spacing:0; font-size:.85rem; }
/* sized so every column shows without scrolling in the 1200px dialog */
.pr-grid col.c-act { width:84px; }
.pr-grid col.c-desc { width:420px; }
.pr-grid col.c-unit { width:120px; }
.pr-grid col.c-qty { width:120px; }
.pr-grid col.c-amt { width:175px; }
.pr-grid th { background:#f8fafc; color:var(--fods-muted); font-size:.68rem; font-weight:600; text-transform:uppercase; letter-spacing:.04em; padding:.6rem .6rem; border-bottom:1px solid var(--fods-border); white-space:nowrap; text-align:left; }
.pr-grid th.num, .pr-grid td.num { text-align:right; }
.pr-grid td { padding:.45rem .6rem; border-bottom:1px solid #f1f4f8; vertical-align:middle; background:#fff; }
.pr-grid tbody tr:hover td { background:#fbfcfd; }
.pr-grid input, .pr-grid textarea { width:100%; min-height:36px; border:1px solid var(--fods-border); background:#fff; padding:.35rem .65rem; font-size:.85rem; color:var(--fods-text); border-radius:8px; }
.pr-grid input { height:36px; text-overflow:ellipsis; }
.pr-grid textarea { display:block; resize:vertical; line-height:1.4; }
.pr-grid input::placeholder, .pr-grid textarea::placeholder { color:#a3adbb; }
.pr-grid input:hover:not(:disabled):not([readonly]), .pr-grid textarea:hover:not(:disabled) { border-color:#c7d0db; }
.pr-grid input:focus, .pr-grid textarea:focus { outline:none; border-color:var(--fods-teal); background:#fff; box-shadow:0 0 0 3px var(--fods-teal-soft); }
.pr-grid input[type=number] { text-align:right; font-variant-numeric:tabular-nums; font-weight:600; }
.pr-grid input:disabled, .pr-grid input[readonly], .pr-grid textarea:disabled { color:#475569; background:#f1f4f8; border-color:transparent; }
.pr-grid tfoot td { font-weight:600; background:#fafbfc; border-top:1px solid var(--fods-border); border-bottom:0; padding:.6rem .6rem; font-variant-numeric:tabular-nums; }
/* Row actions stay on the left and the total on the right while scrolling */
.pr-grid .sticky-l { position:sticky; left:0; z-index:1; }
.pr-grid .sticky-r { position:sticky; right:0; z-index:1; box-shadow:-8px 0 8px -8px rgba(15,23,42,.18); }
.pr-grid th.sticky-l, .pr-grid th.sticky-r { background:#f8fafc; z-index:2; }
.pr-grid tfoot .sticky-l, .pr-grid tfoot .sticky-r { background:#fafbfc; }
.pr-grid-hint { font-size:.72rem; color:var(--fods-muted); }
.pr-row-actions { display:flex; gap:.1rem; }
.pr-row-actions .fods-icon-btn { width:28px; height:28px; font-size:.95rem; }
.pr-empty { padding:1.25rem; text-align:center; color:var(--fods-muted); font-size:.82rem; }

/* PPMPs the PR takes its items from */
.pr-ppmp-list { display:flex; flex-wrap:wrap; gap:.4rem; min-height:2rem; align-items:center; }
.pr-ppmp-list .fods-chip { font-size:.8rem; padding:.25rem .35rem .25rem .7rem; }
.pr-ppmp-list .fods-chip button { border:0; background:transparent; color:inherit; line-height:1; padding:0 .2rem; font-size:1rem; opacity:.7; }
.pr-ppmp-list .fods-chip button:hover { opacity:1; }
.pr-note { font-size:.78rem; color:var(--fods-muted); }

/* RFQ tab */
.pr-rfq-table { width:100%; border-collapse:collapse; font-size:.85rem; }
.pr-rfq-table th { font-size:.68rem; text-transform:uppercase; letter-spacing:.04em; color:var(--fods-muted); font-weight:600; padding:.5rem .75rem; border-bottom:1px solid var(--fods-border); white-space:nowrap; text-align:left; }
.pr-rfq-table td { padding:.55rem .75rem; border-bottom:1px solid #f1f4f8; color:var(--fods-text); vertical-align:middle; overflow-wrap:anywhere; }
.pr-rfq-table tr:last-child td { border-bottom:0; }

/* Signatory status (Certification tab) */
.pr-sign { border:1px solid var(--fods-border); border-radius:12px; padding:.85rem 1rem; background:#fff; height:100%; }
.pr-sign-head { display:flex; justify-content:space-between; align-items:center; gap:.5rem; margin-bottom:.6rem; }
.pr-sign-box { font-size:.7rem; font-weight:600; text-transform:uppercase; letter-spacing:.05em; color:var(--fods-muted); }
.pr-sign-note { font-size:.75rem; color:var(--fods-muted); margin-top:.5rem; }

/* Editor footer totals */
.pr-totals { display:flex; flex-wrap:wrap; gap:1.25rem; align-items:center; font-size:.82rem; color:var(--fods-muted); }
.pr-totals b { color:var(--fods-text); font-size:.95rem; font-variant-numeric:tabular-nums; }
.pr-tab-amt { font-size:.7rem; color:var(--fods-muted); margin-left:.35rem; font-weight:500; }
.pr-dialog .fods-segment .nav-link { white-space:nowrap; }

/* Editor dialog: a bit wider than modal-xl on big screens so the grid fits */
@media (min-width: 1200px) {
    .pr-dialog { max-width: min(1200px, calc(100vw - 4rem)); }
}

/* jQuery UI autocomplete above the modal */
.ui-autocomplete { z-index:2000 !important; max-height:280px; overflow-y:auto; overflow-x:hidden; font-size:.82rem; }
.ui-autocomplete .pr-ac-sub { display:block; font-size:.72rem; color:var(--fods-muted); }

@media (max-width: 767.98px) {
    .pr-totals { gap:.5rem 1rem; width:100%; }
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
                    <li class="breadcrumb-item active" aria-current="page">Purchase Request</li>
                </ol>
            </nav>
            <h1 class="fods-page-title">Purchase Request</h1>
            <p class="fods-page-sub">Request items from the PPMPs and send them for approval (Box A requesting office, then Box B), then issue the RFQs once it has a PR no.</p>
        </div>
        <div class="fods-page-actions d-flex">
            <select class="form-select form-select-sm fods-year" id="prYear" aria-label="Year"></select>
            <?php if ($perms['add']): ?>
                <button type="button" class="btn btn-fods btn-sm px-3 py-2" id="prTableAdd"><i class="ti ti-plus me-1"></i> New PR</button>
            <?php endif; ?>
        </div>
    </div>

    <!-- Stat cards (filled by mypr.js for the selected year) -->
    <div class="row g-3 mb-4" id="prTableCards">
        <div class="col-6 col-xl-3">
            <button type="button" class="fods-stat" data-table="prTable" data-filter="unnumbered">
                <div class="fods-stat-icon tone-amber"><i class="ti ti-hourglass"></i></div>
                <div><div class="fods-stat-value" data-card-value="unnumbered">0</div><div class="fods-stat-label" data-card-label="unnumbered">Waiting for a PR no.</div></div>
            </button>
        </div>
        <div class="col-6 col-xl-3">
            <button type="button" class="fods-stat" data-table="prTable" data-filter="numbered">
                <div class="fods-stat-icon tone-indigo"><i class="ti ti-hash"></i></div>
                <div><div class="fods-stat-value" data-card-value="numbered">0</div><div class="fods-stat-label" data-card-label="numbered">PR no. assigned</div></div>
            </button>
        </div>
        <div class="col-6 col-xl-3">
            <button type="button" class="fods-stat" data-table="prTable" data-filter="rfq">
                <div class="fods-stat-icon tone-green"><i class="ti ti-file-invoice"></i></div>
                <div><div class="fods-stat-value" data-card-value="rfq">0</div><div class="fods-stat-label" data-card-label="rfq">RFQ issued</div></div>
            </button>
        </div>
        <div class="col-6 col-xl-3">
            <button type="button" class="fods-stat" data-table="prTable" data-filter="all">
                <div class="fods-stat-icon tone-teal"><i class="ti ti-cash"></i></div>
                <div><div class="fods-stat-value" data-card-value="total">₱0.00</div><div class="fods-stat-label" data-card-label="total">Estimated cost</div></div>
            </button>
        </div>
    </div>

    <div class="fods-card">
        <div class="fods-card-head">
            <div class="fods-filter" id="prTableFilter">
                <button type="button" class="active" data-filter="all">All</button>
                <button type="button" data-filter="in_approval">In approval</button>
                <button type="button" data-filter="disapproved">Returned</button>
                <button type="button" data-filter="approved">Approved</button>
                <button type="button" data-filter="unnumbered">No PR no. yet</button>
                <button type="button" data-filter="numbered">PR no. assigned</button>
                <button type="button" data-filter="rfq">RFQ issued</button>
            </div>
            <div class="fods-search">
                <i class="ti ti-search"></i>
                <input type="search" class="form-control form-control-sm" id="prTableSearch" placeholder="Search PR no., purpose, end-user...">
            </div>
        </div>
        <div class="fods-card-body fods-dt">
            <table id="prTable" class="table fods-table fods-table-stack w-100">
                <thead>
                    <tr>
                        <th>Purchase request</th>
                        <th class="d-none d-md-table-cell">End-user</th>
                        <th class="d-none d-lg-table-cell">Date</th>
                        <th class="text-end">Estimated cost</th>
                        <th>Approval</th>
                        <th class="d-none d-lg-table-cell">Status</th>
                        <th class="d-none d-xl-table-cell">Encoded</th>
                        <th class="text-end"></th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>

<!-- ============================= EDITOR ============================= -->
<div class="modal fade fods-modal" id="prModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable modal-fullscreen-md-down pr-dialog">
        <div class="modal-content">
            <form id="prForm" novalidate>
                <div class="modal-header border-bottom">
                    <div class="d-flex align-items-center" style="min-width:0">
                        <div class="fods-modal-icon tone-teal"><i class="ti ti-shopping-cart"></i></div>
                        <div style="min-width:0">
                            <h5 class="modal-title text-truncate" id="prModalTitle">New purchase request</h5>
                            <div class="fods-modal-sub" id="prModalSub">Fill in the request, then pick the PPMPs to take the items from.</div>
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-2 ms-auto ps-3 flex-shrink-0 text-nowrap">
                        <button type="button" class="btn btn-sm btn-fods-ghost d-none" id="prPrintBtn"><i class="ti ti-printer me-1"></i>Print</button>
                        <button type="button" class="btn-close ms-1" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                </div>

                <div class="modal-body" style="background:var(--fods-bg);">
                    <input type="hidden" id="recid" name="recid">

                    <div id="prBanner" class="alert d-none mb-3"></div>

                    <ul class="nav fods-segment mb-3 flex-nowrap fods-scroll-x" role="tablist" style="max-width:100%;">
                        <li class="nav-item"><button class="nav-link active" type="button" data-bs-toggle="tab" data-bs-target="#prPaneDetails"><i class="ti ti-file-description"></i>Details</button></li>
                        <li class="nav-item"><button class="nav-link" type="button" data-bs-toggle="tab" data-bs-target="#prPaneItems"><i class="ti ti-list-details"></i>Items<span class="pr-tab-amt" data-tab-total></span></button></li>
                        <li class="nav-item"><button class="nav-link" type="button" data-bs-toggle="tab" data-bs-target="#prPaneSign"><i class="ti ti-signature"></i>Certification</button></li>
                        <li class="nav-item d-none" id="prRfqTab"><button class="nav-link" type="button" data-bs-toggle="tab" data-bs-target="#prPaneRfq"><i class="ti ti-file-invoice"></i>RFQ<span class="pr-tab-amt" id="prRfqCount"></span></button></li>
                    </ul>

                    <div class="tab-content">
                        <!-- Details -->
                        <div class="tab-pane fade show active" id="prPaneDetails">
                            <div class="row g-3">
                                <div class="col-lg-6">
                                    <div class="fods-card h-100"><div class="fods-card-body pt-3">
                                        <div class="fods-section mt-0">Request</div>
                                        <div class="row g-3">
                                            <div class="col-md-7">
                                                <label class="form-label" for="prno">PR no.</label>
                                                <input type="text" class="form-control" id="prno" name="prno" maxlength="100" placeholder="Assigned when the PR is numbered">
                                            </div>
                                            <div class="col-md-5">
                                                <label class="form-label" for="pr_date">PR date</label>
                                                <input type="date" class="form-control" id="pr_date" name="pr_date" required>
                                                <div class="invalid-feedback">PR date is required.</div>
                                            </div>
                                            <div class="col-12">
                                                <label class="form-label" for="entity_name">Entity name</label>
                                                <input type="text" class="form-control" id="entity_name" name="entity_name" maxlength="250">
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label" for="office">Office / section</label>
                                                <input type="text" class="form-control" id="office" name="office" maxlength="200">
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label" for="fund_cluster">Fund cluster</label>
                                                <input type="text" class="form-control" id="fund_cluster" name="fund_cluster" maxlength="50">
                                            </div>
                                            <div class="col-12">
                                                <label class="form-label" for="responsibility_code">Responsibility code</label>
                                                <input type="text" class="form-control" id="responsibility_code" name="responsibility_code" maxlength="150">
                                            </div>
                                        </div>
                                    </div></div>
                                </div>

                                <div class="col-lg-6">
                                    <div class="fods-card h-100"><div class="fods-card-body pt-3">
                                        <div class="fods-section mt-0">Purpose</div>
                                        <div class="row g-3">
                                            <div class="col-12">
                                                <label class="form-label" for="end_user">End-user</label>
                                                <input type="text" class="form-control" id="end_user" name="end_user" maxlength="100">
                                            </div>
                                            <div class="col-12">
                                                <label class="form-label" for="purpose">Purpose</label>
                                                <input type="text" class="form-control" id="purpose" name="purpose" maxlength="150">
                                            </div>
                                            <div class="col-12">
                                                <label class="form-label" for="charge_to">Charge to</label>
                                                <textarea class="form-control" id="charge_to" name="charge_to" rows="3" maxlength="250"></textarea>
                                            </div>
                                        </div>

                                        <div class="fods-section">PPMP</div>
                                        <div class="input-group">
                                            <select class="form-select" id="ppmp_lookup" aria-label="PPMP"></select>
                                            <button type="button" class="btn btn-fods-ghost" id="btn_add_ppmp"><i class="ti ti-plus me-1"></i>Add</button>
                                        </div>
                                        <div class="pr-ppmp-list mt-2" id="selected_ppmp_list"></div>
                                        <div class="pr-note mt-1">Adding or removing a PPMP replaces the items with those of the PPMPs picked.</div>
                                    </div></div>
                                </div>
                            </div>
                        </div>

                        <!-- Items -->
                        <div class="tab-pane fade" id="prPaneItems">
                            <div class="pr-grid-card" id="prGrid"></div>
                            <input type="hidden" id="estimated_cost" name="estimated_cost">
                        </div>

                        <!-- Certification -->
                        <div class="tab-pane fade" id="prPaneSign">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="pr-sign">
                                        <div class="pr-sign-head"><span class="pr-sign-box">Box A · Requesting office</span><span id="prSignStatusA"></span></div>
                                        <label class="form-label" for="requested_by">Requested by</label>
                                        <select class="form-select" id="requested_by" name="requested_by" required></select>
                                        <div class="invalid-feedback">Pick the head of the requesting office.</div>
                                        <label class="form-label mt-2" for="requested_position">Designation</label>
                                        <input type="text" class="form-control" id="requested_position" name="requested_position" readonly>
                                        <div class="pr-sign-note" id="prSignNoteA"></div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="pr-sign">
                                        <div class="pr-sign-head"><span class="pr-sign-box">Box B · Approval</span><span id="prSignStatusB"></span></div>
                                        <label class="form-label" for="approved_by">Approved by</label>
                                        <input type="text" class="form-control" id="approved_by" name="approved_by" readonly>
                                        <label class="form-label mt-2" for="approved_position">Designation</label>
                                        <input type="text" class="form-control" id="approved_position" name="approved_position" readonly>
                                        <div class="form-text" id="prApprovalRule"></div>
                                        <div class="pr-sign-note" id="prSignNoteB"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- RFQ -->
                        <div class="tab-pane fade" id="prPaneRfq">
                            <div class="fods-card mb-3"><div class="fods-card-body pt-3">
                                <div class="fods-section mt-0">Requests for quotation</div>
                                <div id="prRfqList"></div>
                            </div></div>

                            <div class="fods-card" id="prRfqForm"><div class="fods-card-body pt-3">
                                <div class="fods-section mt-0">Add an RFQ</div>
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label class="form-label" for="quotation_no">Quotation no.</label>
                                        <input type="text" class="form-control" id="quotation_no" maxlength="100">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label" for="quotation_date">Quotation date</label>
                                        <input type="date" class="form-control" id="quotation_date">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label" for="company_name">Company name</label>
                                        <input type="text" class="form-control" id="company_name" maxlength="150">
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label" for="company_address">Company address</label>
                                        <textarea class="form-control" id="company_address" rows="2" maxlength="200"></textarea>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label" for="delivery_period">Delivery period</label>
                                        <input type="text" class="form-control" id="delivery_period" maxlength="100">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label" for="terms">Terms of payment</label>
                                        <input type="text" class="form-control" id="terms" maxlength="100">
                                    </div>
                                </div>
                                <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mt-3">
                                    <span class="pr-note" id="prRfqNote"></span>
                                    <button type="button" class="btn btn-sm btn-fods px-3" id="prRfqAdd"><i class="ti ti-plus me-1"></i>Add RFQ</button>
                                </div>
                            </div></div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer justify-content-between">
                    <div class="pr-totals">
                        <span>Items <b id="prFootItems">0</b></span>
                        <span>Estimated cost <b id="prFootCost">₱0.00</b></span>
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

<!-- PR / RFQ print preview -->
<div class="modal fade fods-modal" id="prPrintModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-fullscreen-md-down">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="prPrintTitle">Print preview</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0">
                <iframe id="prPrintFrame" src="about:blank" style="width:100%; height:80vh; border:0;"></iframe>
            </div>
        </div>
    </div>
</div>

<script>
    var __pr_data = <?= json_encode($prhddata, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
    var __pr_lookups = <?= json_encode($lookups, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
    var __pr_perms = <?= json_encode($perms) ?>;
    var __pr_open = <?= json_encode($open, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
    var __pr_tab = <?= json_encode($tab, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
</script>
<script src="<?=base_url('assets/js/procurement/pr/mypr.js?v=7');?>"></script>
<?php echo view('templates/myfooter.php'); ?>
