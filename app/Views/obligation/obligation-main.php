<?php
echo view('templates/myheader.php');
?>
<head>
<style>
/* Editor grids */
.obl-grid-card { border:1px solid var(--fods-border); background:#fff; margin-bottom:1rem; border-radius:12px; overflow:hidden; }
.obl-grid-head { display:flex; flex-wrap:wrap; gap:.5rem; align-items:center; justify-content:space-between; padding:.65rem .9rem; border-bottom:1px solid var(--fods-border); background:#fafbfc; }
.obl-grid-title { font-weight:600; color:var(--fods-text); }
.obl-grid-sub { font-size:.75rem; color:var(--fods-muted); }
.obl-grid-wrap { overflow-x:auto; scrollbar-width:thin; }
/* Fixed, roomy columns: the grid scrolls sideways instead of squeezing the fields */
.obl-grid { width:max-content; min-width:100%; table-layout:fixed; border-collapse:separate; border-spacing:0; font-size:.85rem; }
.obl-grid col.c-act { width:84px; }
.obl-grid col.c-program { width:320px; }
.obl-grid col.c-project { width:360px; }
.obl-grid col.c-rc { width:250px; }
.obl-grid col.c-mfo { width:180px; }
.obl-grid col.c-sub { width:300px; }
.obl-grid col.c-uacs { width:140px; }
.obl-grid col.c-amt { width:170px; }
.obl-grid th { background:#f8fafc; color:var(--fods-muted); font-size:.68rem; font-weight:600; text-transform:uppercase; letter-spacing:.04em; padding:.6rem .6rem; border-bottom:1px solid var(--fods-border); white-space:nowrap; text-align:left; }
.obl-grid th.num, .obl-grid td.num { text-align:right; }
.obl-grid td { padding:.45rem .6rem; border-bottom:1px solid #f1f4f8; vertical-align:middle; background:#fff; }
.obl-grid tbody tr:hover td { background:#fbfcfd; }
.obl-grid input { width:100%; height:36px; border:1px solid var(--fods-border); background:#fff; padding:.35rem .65rem; font-size:.85rem; color:var(--fods-text); border-radius:8px; text-overflow:ellipsis; }
.obl-grid input::placeholder { color:#a3adbb; }
.obl-grid input:hover:not(:disabled):not([readonly]) { border-color:#c7d0db; }
.obl-grid input:focus { outline:none; border-color:var(--fods-teal); background:#fff; box-shadow:0 0 0 3px var(--fods-teal-soft); }
.obl-grid input[type=number] { text-align:right; font-variant-numeric:tabular-nums; font-weight:600; }
.obl-grid input:disabled, .obl-grid input[readonly] { color:#475569; background:#f1f4f8; border-color:transparent; }
.obl-grid tfoot td { font-weight:600; background:#fafbfc; border-top:1px solid var(--fods-border); border-bottom:0; padding:.6rem .6rem; font-variant-numeric:tabular-nums; }
/* Row actions stay on the left and the amount on the right while scrolling */
.obl-grid .sticky-l { position:sticky; left:0; z-index:1; }
.obl-grid .sticky-r { position:sticky; right:0; z-index:1; box-shadow:-8px 0 8px -8px rgba(15,23,42,.18); }
.obl-grid th.sticky-l, .obl-grid th.sticky-r { background:#f8fafc; z-index:2; }
.obl-grid tfoot .sticky-l, .obl-grid tfoot .sticky-r { background:#fafbfc; }
.obl-grid-hint { font-size:.72rem; color:var(--fods-muted); }
.obl-row-actions { display:flex; gap:.1rem; }
.obl-row-actions .fods-icon-btn { width:28px; height:28px; font-size:.95rem; }
.obl-empty { padding:1.25rem; text-align:center; color:var(--fods-muted); font-size:.82rem; }

/* Amount summary on the Details tab */
.obl-sum { border:1px solid var(--fods-border); background:#fafbfc; border-radius:12px; padding:.9rem 1rem; }
.obl-sum-row { display:flex; justify-content:space-between; gap:1rem; padding:.3rem 0; font-size:.85rem; color:var(--fods-muted); }
.obl-sum-row b { color:var(--fods-text); font-variant-numeric:tabular-nums; }
.obl-sum-row.net { border-top:1px solid var(--fods-border); margin-top:.35rem; padding-top:.6rem; }
.obl-sum-row.net b { color:#15803d; font-size:1.05rem; }

/* Signatory status */
.obl-sign { border:1px solid var(--fods-border); border-radius:12px; padding:.85rem 1rem; background:#fff; height:100%; }
.obl-sign-head { display:flex; justify-content:space-between; align-items:center; gap:.5rem; margin-bottom:.6rem; }
.obl-sign-box { font-size:.7rem; font-weight:600; text-transform:uppercase; letter-spacing:.05em; color:var(--fods-muted); }
.obl-sign-note { font-size:.75rem; color:var(--fods-muted); margin-top:.5rem; }

/* Editor footer totals */
.obl-totals { display:flex; flex-wrap:wrap; gap:1.25rem; align-items:center; font-size:.82rem; color:var(--fods-muted); }
.obl-totals b { color:var(--fods-text); font-size:.95rem; font-variant-numeric:tabular-nums; }
.obl-tab-amt { font-size:.7rem; color:var(--fods-muted); margin-left:.35rem; font-weight:500; }

/* Form type (ORS / BURS): picked on a new record, fixed once saved */
.obl-type { display:flex; flex-wrap:wrap; gap:.6rem; }
.obl-type-opt { position:relative; flex:1 1 260px; margin:0; cursor:pointer; }
.obl-type-opt input { position:absolute; opacity:0; pointer-events:none; }
.obl-type-opt span { display:flex; flex-direction:column; gap:.1rem; border:1px solid var(--fods-border); border-radius:10px; padding:.6rem .85rem; background:#fff; transition:border-color .12s, box-shadow .12s, background .12s; }
.obl-type-opt b { color:var(--fods-text); font-size:.9rem; }
.obl-type-opt small { color:var(--fods-muted); font-size:.74rem; }
.obl-type-opt:hover span { border-color:#c7d0db; }
.obl-type-opt input:checked + span { border-color:var(--fods-teal); background:var(--fods-teal-soft); box-shadow:0 0 0 1px var(--fods-teal) inset; }
.obl-type-opt input:focus-visible + span { box-shadow:0 0 0 3px var(--fods-teal-soft); }
.obl-type-opt input:disabled + span { cursor:default; }
.obl-type-opt input:disabled:not(:checked) + span { opacity:.45; }
.obl-type-chip { display:inline-block; font-size:.64rem; font-weight:700; letter-spacing:.04em; padding:.08rem .38rem; border-radius:6px; margin-right:.4rem; vertical-align:1px; }

/* Editor dialog: a bit wider than modal-xl on big screens so the grids fit */
@media (min-width: 1200px) {
    .obl-dialog { max-width: min(1320px, calc(100vw - 4rem)); }
}

/* jQuery UI autocomplete above the modal */
.ui-autocomplete { z-index:2000 !important; max-height:280px; overflow-y:auto; overflow-x:hidden; font-size:.82rem; }
.ui-autocomplete .obl-ac-sub { display:block; font-size:.72rem; color:var(--fods-muted); }

@media (max-width: 767.98px) {
    .obl-totals { gap:.5rem 1rem; width:100%; }
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
                    <li class="breadcrumb-item"><a href="#">Obligations</a></li>
                    <li class="breadcrumb-item active" aria-current="page">ORS / BURS</li>
                </ol>
            </nav>
            <h1 class="fods-page-title">Obligations (ORS / BURS)</h1>
            <p class="fods-page-sub">Obligation Request and Status (Fund Cluster 01) and Budget Utilization Request and Status (Fund Cluster 07), certified at Box A and Box B.</p>
        </div>
        <div class="fods-page-actions d-flex">
            <select class="form-select form-select-sm fods-year" id="oblYear" aria-label="Year"></select>
            <?php if (array_filter(array_column($perms, 'add'))): ?>
                <button type="button" class="btn btn-fods btn-sm px-3 py-2" id="oblTableAdd"><i class="ti ti-plus me-1"></i> New ORS / BURS</button>
            <?php endif; ?>
        </div>
    </div>

    <!-- Stat cards (filled by myobligation.js for the selected year and form) -->
    <div class="row g-3 mb-4" id="oblTableCards">
        <div class="col-6 col-xl-3">
            <button type="button" class="fods-stat" data-table="oblTable" data-filter="pending">
                <div class="fods-stat-icon tone-amber"><i class="ti ti-hourglass"></i></div>
                <div><div class="fods-stat-value" data-card-value="pending">0</div><div class="fods-stat-label" data-card-label="pending">For approval</div></div>
            </button>
        </div>
        <div class="col-6 col-xl-3">
            <button type="button" class="fods-stat" data-table="oblTable" data-filter="disapproved">
                <div class="fods-stat-icon tone-red"><i class="ti ti-arrow-back-up"></i></div>
                <div><div class="fods-stat-value" data-card-value="returned">0</div><div class="fods-stat-label" data-card-label="returned">Returned</div></div>
            </button>
        </div>
        <div class="col-6 col-xl-3">
            <button type="button" class="fods-stat" data-table="oblTable" data-filter="approved">
                <div class="fods-stat-icon tone-green"><i class="ti ti-circle-check"></i></div>
                <div><div class="fods-stat-value" data-card-value="approved">0</div><div class="fods-stat-label" data-card-label="approved">Approved</div></div>
            </button>
        </div>
        <div class="col-6 col-xl-3">
            <button type="button" class="fods-stat" data-table="oblTable" data-filter="all">
                <div class="fods-stat-icon tone-teal"><i class="ti ti-cash"></i></div>
                <div><div class="fods-stat-value" data-card-value="total">₱0.00</div><div class="fods-stat-label" data-card-label="total">Total amount</div></div>
            </button>
        </div>
    </div>

    <div class="fods-card">
        <div class="fods-card-head">
            <div class="fods-filter" id="oblTableFilter">
                <button type="button" class="active" data-filter="all">All records</button>
                <button type="button" data-filter="pending">For approval</button>
                <button type="button" data-filter="disapproved">Returned</button>
                <button type="button" data-filter="approved">Approved</button>
            </div>
            <?php if (count($types) > 1): ?>
            <div class="fods-filter" id="oblTypeFilter" aria-label="Form">
                <button type="button" class="active" data-type="">ORS and BURS</button>
                <button type="button" data-type="ors">ORS</button>
                <button type="button" data-type="burs">BURS</button>
            </div>
            <?php endif; ?>
            <div class="fods-search">
                <i class="ti ti-search"></i>
                <input type="search" class="form-control form-control-sm" id="oblTableSearch" placeholder="Search serial no., payee, particulars...">
            </div>
        </div>
        <div class="fods-card-body fods-dt">
            <table id="oblTable" class="table fods-table fods-table-stack w-100">
                <thead>
                    <tr>
                        <th>Serial no.</th>
                        <th class="d-none d-md-table-cell">Payee</th>
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
<div class="modal fade fods-modal fods-flat" id="oblModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable modal-fullscreen-md-down obl-dialog">
        <div class="modal-content">
            <form id="oblForm" novalidate>
                <div class="modal-header">
                    <div class="d-flex align-items-center" style="min-width:0">
                        <div class="fods-modal-icon tone-teal"><i class="ti ti-file-invoice"></i></div>
                        <div style="min-width:0">
                            <h5 class="modal-title text-truncate" id="oblModalTitle">New ORS</h5>
                            <div class="fods-modal-sub" id="oblModalSub">Fill in the payee and deductions, then the line items.</div>
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-2 ms-auto ps-3 flex-shrink-0 text-nowrap">
                        <button type="button" class="btn btn-sm btn-fods-ghost d-none" id="oblPrintBtn"><i class="ti ti-printer me-1"></i><span id="oblPrintLabel">ORS</span></button>
                        <button type="button" class="btn btn-sm btn-fods-ghost d-none" id="oblPrintDvBtn"><i class="ti ti-printer me-1"></i>DV</button>
                        <button type="button" class="btn btn-sm btn-danger d-none" id="oblDisapproveBtn"><i class="ti ti-x me-1"></i>Disapprove</button>
                        <button type="button" class="btn btn-sm btn-success d-none" id="oblApproveBtn"><i class="ti ti-check me-1"></i>Certify</button>
                        <button type="button" class="btn btn-sm btn-fods-ghost d-none" id="oblHistBtn" data-history-module="ors" data-history-field="recid"><i class="ti ti-history me-1"></i>History</button>
                        <button type="button" class="btn-close ms-1" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                </div>

                <ul class="nav fods-tabs" role="tablist">
                    <li class="nav-item"><button class="nav-link active" type="button" data-bs-toggle="tab" data-bs-target="#oblPaneDetails"><i class="ti ti-file-description"></i>Details</button></li>
                    <li class="nav-item"><button class="nav-link" type="button" data-bs-toggle="tab" data-bs-target="#oblPanePs"><i class="ti ti-users"></i>Personnel Services<span class="obl-tab-amt" data-tab-total="ps"></span></button></li>
                    <li class="nav-item"><button class="nav-link" type="button" data-bs-toggle="tab" data-bs-target="#oblPaneMooe"><i class="ti ti-tools"></i>MOOE<span class="obl-tab-amt" data-tab-total="mooe"></span></button></li>
                    <li class="nav-item"><button class="nav-link" type="button" data-bs-toggle="tab" data-bs-target="#oblPaneCo"><i class="ti ti-building"></i>Capital Outlay<span class="obl-tab-amt" data-tab-total="co"></span></button></li>
                    <li class="nav-item"><button class="nav-link" type="button" data-bs-toggle="tab" data-bs-target="#oblPaneSign"><i class="ti ti-signature"></i>Certification</button></li>
                </ul>

                <div class="modal-body">
                    <input type="hidden" id="recid" name="recid">

                    <div id="oblBanner" class="alert d-none mt-3 mb-0"></div>

                    <div class="tab-content">
                        <!-- Details -->
                        <div class="tab-pane fade show active" id="oblPaneDetails">
                            <section class="fods-fsec">
                                <div class="fods-fsec-head">
                                    <h6>Form</h6>
                                </div>
                                <div class="row g-3">
                                    <div class="col-12">
                                        <label class="form-label req">Form type</label>
                                        <div class="obl-type" id="oblType" role="radiogroup" aria-label="Form type">
                                            <?php foreach (['ors' => ['ORS', 'Obligation Request and Status', 'Fund Cluster 01'], 'burs' => ['BURS', 'Budget Utilization Request and Status', 'Fund Cluster 07']] as $k => $t): ?>
                                                <?php if (in_array($k, $types, true)): ?>
                                                <label class="obl-type-opt">
                                                    <input type="radio" name="obl_type" value="<?= $k ?>">
                                                    <span><b><?= $t[0] ?></b><small><?= $t[1] ?> · <?= $t[2] ?></small></span>
                                                </label>
                                                <?php endif; ?>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                </div>
                            </section>

                            <section class="fods-fsec">
                                <div class="fods-fsec-head">
                                    <h6 id="oblSecTitle">Obligation</h6>
                                </div>
                                <div class="row g-3">
                                    <div class="col-md-5">
                                        <label class="form-label" for="serialno" id="oblSerialLabel">ORS serial no.</label>
                                        <input type="text" class="form-control" id="serialno" name="serialno">
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <label class="form-label" for="doc_date" id="oblDateLabel">ORS date</label>
                                        <input type="date" class="form-control" id="doc_date" name="doc_date">
                                    </div>
                                    <div class="col-6 col-md-4">
                                        <label class="form-label" for="dvno">DV no.</label>
                                        <input type="text" class="form-control" id="dvno" placeholder="Set by Accounting" readonly>
                                    </div>
                                    <div class="col-md-5">
                                        <label class="form-label req" for="funding_source">Funding source</label>
                                        <select class="form-select" id="funding_source" name="funding_source" required></select>
                                        <div class="invalid-feedback">Pick the funding source.</div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <label class="form-label" for="fund_cluster_code">Fund cluster</label>
                                        <select class="form-select" id="fund_cluster_code" name="fund_cluster_code"></select>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label" for="particulars">Particulars</label>
                                        <textarea class="form-control" id="particulars" name="particulars" rows="3" maxlength="250"></textarea>
                                    </div>
                                </div>
                            </section>

                            <section class="fods-fsec">
                                <div class="fods-fsec-head">
                                    <h6>Payee</h6>
                                </div>
                                <div class="row g-3">
                                    <div class="col-12">
                                        <label class="form-label req" for="payee_name">Payee name</label>
                                        <input type="text" class="form-control" id="payee_name" name="payee_name" placeholder="Start typing to pick a payee" autocomplete="off" required>
                                        <div class="invalid-feedback">Payee is required.</div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label" for="payee_office">Payee office</label>
                                        <input type="text" class="form-control" id="payee_office" name="payee_office" readonly>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label" for="payee_address">Payee address</label>
                                        <input type="text" class="form-control" id="payee_address" name="payee_address" readonly>
                                    </div>
                                </div>
                            </section>

                            <section class="fods-fsec">
                                <div class="fods-fsec-head">
                                    <h6>Deductions</h6>
                                </div>
                                <div class="row g-4">
                                    <div class="col-lg-7">
                                        <div class="row g-3">
                                            <div class="col-7">
                                                <label class="form-label" for="category">Deduction category</label>
                                                <select class="form-select" id="category" name="category"></select>
                                            </div>
                                            <div class="col-5 d-flex align-items-end obl-tax d-none">
                                                <div class="form-check form-switch mb-2">
                                                    <input class="form-check-input" type="checkbox" id="is_vatable" value="1">
                                                    <label class="form-check-label" for="is_vatable">Subject to VAT</label>
                                                </div>
                                            </div>

                                            <div class="col-4 obl-tax obl-vat d-none">
                                                <label class="form-label" for="vat_percent">VAT rate (%)</label>
                                                <input type="number" step="0.01" class="form-control" id="vat_percent" placeholder="e.g. 12">
                                            </div>
                                            <div class="col-4 obl-tax obl-ewt d-none">
                                                <label class="form-label" for="ewt_percent">EWT rate (%)</label>
                                                <input type="number" step="0.01" class="form-control" id="ewt_percent" placeholder="1, 2, 5">
                                            </div>
                                            <div class="col-4 obl-tax obl-pt d-none">
                                                <label class="form-label" for="pt_percent">Percentage tax (%)</label>
                                                <input type="number" step="0.01" class="form-control" id="pt_percent" placeholder="e.g. 5">
                                            </div>
                                            <div class="col-7 obl-tax d-none">
                                                <label class="form-label" for="deduction_name">Other deduction type</label>
                                                <input type="text" class="form-control" id="deduction_name" placeholder="e.g. Delay penalty">
                                            </div>
                                            <div class="col-5 obl-tax d-none">
                                                <label class="form-label" for="other_deductions">Other deduction amount</label>
                                                <input type="number" step="0.01" class="form-control" id="other_deductions">
                                            </div>

                                            <div class="col-md-6 obl-income d-none">
                                                <label class="form-label" for="income_tax">Income tax amount</label>
                                                <input type="number" step="0.01" class="form-control" id="income_tax">
                                            </div>

                                            <div class="col-6 obl-payroll d-none"><label class="form-label" for="sss">SSS contribution</label><input type="number" step="0.01" class="form-control" id="sss"></div>
                                            <div class="col-6 obl-payroll d-none"><label class="form-label" for="hdmf">HDMF (Pag-IBIG) contribution</label><input type="number" step="0.01" class="form-control" id="hdmf"></div>
                                            <div class="col-6 obl-payroll d-none"><label class="form-label" for="pagibig_mp2">Pag-IBIG MP2 savings</label><input type="number" step="0.01" class="form-control" id="pagibig_mp2"></div>
                                            <div class="col-6 obl-payroll d-none"><label class="form-label" for="pagibig_loan">Pag-IBIG loan payment</label><input type="number" step="0.01" class="form-control" id="pagibig_loan"></div>
                                            <div class="col-6 obl-payroll d-none"><label class="form-label" for="hmo">HMO premium</label><input type="number" step="0.01" class="form-control" id="hmo"></div>
                                            <div class="col-6 obl-payroll d-none"><label class="form-label" for="wtax">Withholding tax (WTAX)</label><input type="number" step="0.01" class="form-control" id="wtax"></div>

                                            <div class="col-12 obl-none"><div class="form-text mt-0">Pick a category to compute taxes and deductions.</div></div>
                                        </div>
                                    </div>
                                    <div class="col-lg-5">
                                        <div class="obl-sum">
                                            <div class="obl-sum-row"><span>Gross amount</span><b id="oblGross">₱0.00</b></div>
                                            <div class="obl-sum-row"><span>Total deductions</span><b class="text-danger" id="oblDeduction">₱0.00</b></div>
                                            <div class="obl-sum-row net"><span>Net amount payable</span><b id="oblNet">₱0.00</b></div>
                                        </div>
                                        <input type="hidden" id="gross_amount">
                                        <input type="hidden" id="total_deduction">
                                        <input type="hidden" id="net_amount">
                                    </div>
                                </div>
                            </section>
                        </div>

                        <!-- Line items -->
                        <div class="tab-pane fade" id="oblPanePs">
                            <div class="fods-pane-pad">
                                <div class="obl-grid-card" data-grid="ps_direct"></div>
                                <div class="obl-grid-card" data-grid="ps_indirect"></div>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="oblPaneMooe">
                            <div class="fods-pane-pad">
                                <div class="obl-grid-card" data-grid="mooe_direct"></div>
                                <div class="obl-grid-card" data-grid="mooe_indirect"></div>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="oblPaneCo">
                            <div class="fods-pane-pad">
                                <div class="obl-grid-card" data-grid="co_direct"></div>
                                <div class="obl-grid-card" data-grid="co_indirect"></div>
                            </div>
                        </div>

                        <!-- Certification -->
                        <div class="tab-pane fade" id="oblPaneSign">
                            <section class="fods-fsec">
                                <div class="fods-fsec-head">
                                    <h6>Box A · Requesting office <span id="oblSignStatusA"></span></h6>
                                </div>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label" for="certified_a">Certified by</label>
                                        <select class="form-select" id="certified_a" name="certified_a"></select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label" for="position_a">Position</label>
                                        <input type="text" class="form-control" id="position_a" name="position_a" readonly>
                                    </div>
                                    <div class="col-12 obl-sign-note mt-1" id="oblSignNoteA"></div>
                                </div>
                            </section>

                            <section class="fods-fsec">
                                <div class="fods-fsec-head">
                                    <h6>Box B · Budget <span id="oblSignStatusB"></span></h6>
                                </div>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label" for="certified_b">Certified by</label>
                                        <select class="form-select" id="certified_b" name="certified_b"></select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label" for="position_b">Position</label>
                                        <input type="text" class="form-control" id="position_b" name="position_b" readonly>
                                    </div>
                                    <div class="col-12 obl-sign-note mt-1" id="oblSignNoteB"></div>
                                </div>
                            </section>
                        </div>
                    </div>
                </div>

                <div class="modal-footer justify-content-between">
                    <div class="obl-totals">
                        <span>Gross <b id="oblFootGross">₱0.00</b></span>
                        <span>Deductions <b id="oblFootDeduction">₱0.00</b></span>
                        <span>Net <b id="oblFootNet">₱0.00</b></span>
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


<!-- ORS / BURS / DV print preview -->
<div class="modal fade fods-modal" id="oblPrintModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-fullscreen-md-down">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="oblPrintTitle">Print preview</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0">
                <iframe id="oblPrintFrame" src="about:blank" style="width:100%; height:80vh; border:0;"></iframe>
            </div>
        </div>
    </div>
</div>

<script>
    var __obl_data = <?= json_encode($rows, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
    var __obl_types = <?= json_encode($types) ?>;
    var __obl_show = <?= json_encode($show) ?>;
    var __obl_lookups = <?= json_encode($lookups, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
    var __obl_perms = <?= json_encode($perms) ?>;
    var __obl_open = <?= json_encode($open, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
</script>
<script src="<?=base_url('assets/js/obligation/myobligation.js?v=1');?>"></script>
<?php echo view('templates/myfooter.php'); ?>
