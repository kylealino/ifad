<?php
echo view('templates/myheader.php');
?>
<head>
<style>
.rev-grid { display:grid; grid-template-columns:repeat(auto-fill, minmax(200px, 1fr)); gap:.9rem 1.5rem; }
.rev-field .lbl { font-size:.7rem; font-weight:600; text-transform:uppercase; letter-spacing:.05em; color:var(--fods-muted); margin-bottom:.15rem; }
.rev-field .val { color:var(--fods-text); font-size:.88rem; overflow-wrap:anywhere; }
.rev-totals { display:grid; grid-template-columns:repeat(4, minmax(0,1fr)); gap:.75rem; }
.rev-total { border:1px solid var(--fods-border); background:#fff; padding:.75rem .9rem; border-radius:12px; }
.rev-total .lbl { font-size:.72rem; color:var(--fods-muted); }
.rev-total .amt { font-weight:700; color:var(--fods-text); font-variant-numeric:tabular-nums; }
.rev-total.grand { border-color:var(--fods-teal); background:var(--fods-teal-soft); }
.rev-table-wrap { overflow-x:auto; border:1px solid var(--fods-border); background:#fff; margin-top:1rem; border-radius:12px; }
.rev-table { width:100%; border-collapse:collapse; font-size:.82rem; }
.rev-table caption { caption-side:top; padding:.5rem .75rem; font-size:.72rem; font-weight:600; text-transform:uppercase; letter-spacing:.05em; color:var(--fods-muted); background:#fafbfc; border-bottom:1px solid var(--fods-border); }
.rev-table th { font-size:.68rem; text-transform:uppercase; letter-spacing:.04em; color:var(--fods-muted); font-weight:600; padding:.45rem .75rem; border-bottom:1px solid var(--fods-border); white-space:nowrap; text-align:left; }
.rev-table td { padding:.45rem .75rem; border-bottom:1px solid #f1f4f8; color:var(--fods-text); }
.rev-table .num { text-align:right; font-variant-numeric:tabular-nums; white-space:nowrap; }
.rev-table tfoot td { font-weight:700; background:#fafbfc; border-bottom:0; }
.rev-confirm { display:flex; flex-wrap:wrap; align-items:center; gap:.5rem; }
@media (max-width: 767.98px) { .rev-totals { grid-template-columns:repeat(2, minmax(0,1fr)); } }
@media (min-width: 1200px) { .rev-dialog { max-width:min(1200px, calc(100vw - 4rem)); } }
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
                    <li class="breadcrumb-item active" aria-current="page">LDDAP-ADA Approval</li>
                </ol>
            </nav>
            <h1 class="fods-page-title">LDDAP-ADA Approval</h1>
            <p class="fods-page-sub">Review each LDDAP-ADA and its DVs, then approve or disapprove it for payment.</p>
        </div>
        <div class="fods-page-actions d-flex">
            <select class="form-select form-select-sm fods-year" id="approvalYear" aria-label="Year"></select>
        </div>
    </div>

    <!-- Stat cards (filled by mylddapadaapproval.js for the selected year) -->
    <div class="row g-3 mb-4" id="approvalTableCards">
        <div class="col-6 col-xl-3">
            <button type="button" class="fods-stat" data-table="approvalTable" data-filter="pending">
                <div class="fods-stat-icon tone-amber"><i class="ti ti-hourglass"></i></div>
                <div><div class="fods-stat-value" data-card-value="pending">0</div><div class="fods-stat-label" data-card-label="pending">Waiting for you</div></div>
            </button>
        </div>
        <div class="col-6 col-xl-3">
            <button type="button" class="fods-stat" data-table="approvalTable" data-filter="disapproved">
                <div class="fods-stat-icon tone-red"><i class="ti ti-arrow-back-up"></i></div>
                <div><div class="fods-stat-value" data-card-value="returned">0</div><div class="fods-stat-label" data-card-label="returned">Returned</div></div>
            </button>
        </div>
        <div class="col-6 col-xl-3">
            <button type="button" class="fods-stat" data-table="approvalTable" data-filter="approved">
                <div class="fods-stat-icon tone-green"><i class="ti ti-circle-check"></i></div>
                <div><div class="fods-stat-value" data-card-value="approved">0</div><div class="fods-stat-label" data-card-label="approved">Approved</div></div>
            </button>
        </div>
        <div class="col-6 col-xl-3">
            <button type="button" class="fods-stat" data-table="approvalTable" data-filter="all">
                <div class="fods-stat-icon tone-teal"><i class="ti ti-cash"></i></div>
                <div><div class="fods-stat-value" data-card-value="total">₱0.00</div><div class="fods-stat-label" data-card-label="total">Total amount</div></div>
            </button>
        </div>
    </div>

    <div class="fods-card">
        <div class="fods-card-head">
            <div class="fods-filter" id="approvalTableFilter">
                <button type="button" class="active" data-filter="pending">Waiting for you</button>
                <button type="button" data-filter="disapproved">Returned</button>
                <button type="button" data-filter="approved">Approved</button>
                <button type="button" data-filter="all">All</button>
            </div>
            <div class="fods-search">
                <i class="ti ti-search"></i>
                <input type="search" class="form-control form-control-sm" id="approvalTableSearch" placeholder="Search LDDAP-ADA no., check no....">
            </div>
        </div>
        <div class="fods-card-body fods-dt">
            <table id="approvalTable" class="table fods-table fods-table-stack w-100">
                <thead>
                    <tr>
                        <th>LDDAP-ADA</th>
                        <th class="d-none d-md-table-cell">Date</th>
                        <th class="text-end">Net amount</th>
                        <th>Status</th>
                        <th class="d-none d-lg-table-cell">Submitted</th>
                        <th class="d-none d-xl-table-cell">Decided by</th>
                        <th class="text-end"></th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>

<!-- ============================= REVIEW ============================= -->
<div class="modal fade fods-modal" id="reviewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable modal-fullscreen-md-down rev-dialog">
        <div class="modal-content">
            <div class="modal-header border-bottom">
                <div class="d-flex align-items-center" style="min-width:0">
                    <div class="fods-modal-icon tone-teal"><i class="ti ti-file-search"></i></div>
                    <div style="min-width:0">
                        <h5 class="modal-title text-truncate" id="reviewTitle">Review LDDAP-ADA</h5>
                        <div class="fods-modal-sub" id="reviewSub"></div>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2 ms-auto ps-3 flex-shrink-0 text-nowrap">
                    <button type="button" class="btn btn-sm btn-fods-ghost" id="reviewPrintBtn"><i class="ti ti-printer me-1"></i>Print</button>
                    <button type="button" class="btn-close ms-1" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
            </div>

            <div class="modal-body" style="background:var(--fods-bg);">
                <div id="reviewBanner" class="alert d-none mb-3"></div>

                <div class="fods-card mb-3"><div class="fods-card-body pt-3">
                    <div class="fods-section mt-0">LDDAP-ADA</div>
                    <div class="rev-grid" id="reviewFields"></div>
                </div></div>

                <div class="rev-totals" id="reviewTotals"></div>
                <div id="reviewItems"></div>

                <div class="fods-card mt-3" id="reviewDecisionCard"><div class="fods-card-body pt-3">
                    <div class="fods-section mt-0">Your decision</div>
                    <label class="form-label" for="reviewRemarks">Remarks</label>
                    <textarea class="form-control" id="reviewRemarks" rows="3" maxlength="250" placeholder="Optional for approval — explain what needs to change when disapproving"></textarea>
                </div></div>
            </div>

            <div class="modal-footer justify-content-between">
                <span class="small text-muted" id="reviewFooterNote"></span>
                <div id="reviewActions" class="d-flex gap-2">
                    <button type="button" class="btn btn-sm btn-fods-ghost px-3" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-sm btn-danger px-3" id="reviewDisapproveBtn" style="border-radius:10px;"><i class="ti ti-x me-1"></i>Disapprove</button>
                    <button type="button" class="btn btn-sm btn-success px-4" id="reviewApproveBtn" style="border-radius:10px;"><i class="ti ti-check me-1"></i>Approve</button>
                </div>
                <div id="reviewConfirm" class="rev-confirm d-none">
                    <span class="fw-semibold" id="reviewConfirmText"></span>
                    <button type="button" class="btn btn-sm btn-fods-ghost px-3" id="reviewConfirmCancel">Cancel</button>
                    <button type="button" class="btn btn-sm px-4" id="reviewConfirmYes" style="border-radius:10px;"></button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Print preview -->
<div class="modal fade fods-modal" id="approvalPrintModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-fullscreen-md-down">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">LDDAP-ADA — print preview</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0">
                <iframe id="approvalPrintFrame" src="about:blank" style="width:100%; height:80vh; border:0;"></iframe>
            </div>
        </div>
    </div>
</div>

<script>
    var __approval_data = <?= json_encode($lddapdata, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
    var __approval_open = <?= json_encode($open, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
    var __approval_perms = <?= json_encode($perms) ?>;
</script>
<script src="<?=base_url('assets/js/cashier/mylddapadaapproval.js?v=1');?>"></script>
<?php echo view('templates/myfooter.php'); ?>
