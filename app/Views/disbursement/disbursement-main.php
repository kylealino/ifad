<?php
echo view('templates/myheader.php');
?>
<head>
<style>
/* Editor grids */
.dv-grid-card { border:1px solid var(--fods-border); background:#fff; margin-bottom:1rem; border-radius:12px; overflow:hidden; }
.dv-grid-head { display:flex; flex-wrap:wrap; gap:.5rem; align-items:center; justify-content:space-between; padding:.65rem .9rem; border-bottom:1px solid var(--fods-border); background:#fafbfc; }
.dv-grid-title { font-weight:600; color:var(--fods-text); }
.dv-grid-sub { font-size:.75rem; color:var(--fods-muted); }
.dv-grid-wrap { overflow-x:auto; scrollbar-width:thin; }
.dv-grid { width:100%; min-width:640px; table-layout:fixed; border-collapse:separate; border-spacing:0; font-size:.85rem; }
.dv-grid col.c-act { width:84px; }
.dv-grid col.c-rc { width:auto; }
.dv-grid col.c-mfo { width:220px; }
.dv-grid col.c-amt { width:190px; }
.dv-grid th { background:#f8fafc; color:var(--fods-muted); font-size:.68rem; font-weight:600; text-transform:uppercase; letter-spacing:.04em; padding:.6rem .6rem; border-bottom:1px solid var(--fods-border); white-space:nowrap; text-align:left; }
.dv-grid th.num, .dv-grid td.num { text-align:right; }
.dv-grid td { padding:.45rem .6rem; border-bottom:1px solid #f1f4f8; vertical-align:middle; background:#fff; }
.dv-grid tbody tr:hover td { background:#fbfcfd; }
.dv-grid input { width:100%; height:36px; border:1px solid var(--fods-border); background:#fff; padding:.35rem .65rem; font-size:.85rem; color:var(--fods-text); border-radius:8px; text-overflow:ellipsis; }
.dv-grid input::placeholder { color:#a3adbb; }
.dv-grid input:hover:not(:disabled):not([readonly]) { border-color:#c7d0db; }
.dv-grid input:focus { outline:none; border-color:var(--fods-teal); background:#fff; box-shadow:0 0 0 3px var(--fods-teal-soft); }
.dv-grid input[type=number] { text-align:right; font-variant-numeric:tabular-nums; font-weight:600; }
.dv-grid input:disabled, .dv-grid input[readonly] { color:#475569; background:#f1f4f8; border-color:transparent; }
.dv-grid tfoot td { font-weight:600; background:#fafbfc; border-top:1px solid var(--fods-border); border-bottom:0; padding:.6rem .6rem; font-variant-numeric:tabular-nums; }
.dv-row-actions { display:flex; gap:.1rem; }
.dv-row-actions .fods-icon-btn { width:28px; height:28px; font-size:.95rem; }
.dv-empty { padding:1.25rem; text-align:center; color:var(--fods-muted); font-size:.82rem; }

/* Source (ORS / BURS) card */
.dv-source { border:1px dashed var(--fods-border); background:#fafbfc; border-radius:12px; padding:.75rem .9rem; }
.dv-source .form-text { margin-top:.35rem; }

/* Amount summary on the Details tab */
.dv-sum { border:1px solid var(--fods-border); background:#fafbfc; border-radius:12px; padding:.9rem 1rem; }
.dv-sum-row { display:flex; justify-content:space-between; gap:1rem; padding:.3rem 0; font-size:.85rem; color:var(--fods-muted); }
.dv-sum-row b { color:var(--fods-text); font-variant-numeric:tabular-nums; }
.dv-sum-row.net { border-top:1px solid var(--fods-border); margin-top:.35rem; padding-top:.6rem; }
.dv-sum-row.net b { color:#15803d; font-size:1.05rem; }

/* Signatory status */
.dv-sign { border:1px solid var(--fods-border); border-radius:12px; padding:.85rem 1rem; background:#fff; height:100%; }
.dv-sign-head { display:flex; justify-content:space-between; align-items:center; gap:.5rem; margin-bottom:.6rem; }
.dv-sign-box { font-size:.7rem; font-weight:600; text-transform:uppercase; letter-spacing:.05em; color:var(--fods-muted); }
.dv-sign-note { font-size:.75rem; color:var(--fods-muted); margin-top:.5rem; }


/* Editor footer totals */
.dv-totals { display:flex; flex-wrap:wrap; gap:1.25rem; align-items:center; font-size:.82rem; color:var(--fods-muted); }
.dv-totals b { color:var(--fods-text); font-size:.95rem; font-variant-numeric:tabular-nums; }
.dv-tab-amt { font-size:.7rem; color:var(--fods-muted); margin-left:.35rem; font-weight:500; }

@media (min-width: 1200px) {
    .dv-dialog { max-width: min(1240px, calc(100vw - 4rem)); }
}

/* jQuery UI autocomplete above the modal */
.ui-autocomplete { z-index:2000 !important; max-height:280px; overflow-y:auto; overflow-x:hidden; font-size:.82rem; }
.ui-autocomplete .ors-ac-sub { display:block; font-size:.72rem; color:var(--fods-muted); }

@media (max-width: 767.98px) {
    .dv-totals { gap:.5rem 1rem; width:100%; }
}
/* Other deductions: type + amount per row */
.dv-ded-row { display:grid; grid-template-columns:minmax(0,1fr) 140px 32px; gap:.5rem; align-items:center; margin-bottom:.5rem; }
.dv-ded-row input[type=number] { text-align:right; font-variant-numeric:tabular-nums; }
.dv-ded-empty { font-size:.78rem; color:var(--fods-muted); margin-bottom:.5rem; }
.dv-ded-total { font-size:.78rem; color:var(--fods-muted); text-align:right; }
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
                    <li class="breadcrumb-item"><a href="#">Accounting</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Disbursement</li>
                </ol>
            </nav>
            <h1 class="fods-page-title">Disbursement Vouchers</h1>
            <p class="fods-page-sub">Prepare disbursement vouchers from an ORS or BURS, then send them for approval: Box A, Box C, then Box D.</p>
        </div>
        <div class="fods-page-actions d-flex">
            <select class="form-select form-select-sm fods-year" id="dvYear" aria-label="Year"></select>
            <?php if ($perms['add']): ?>
            <button type="button" class="btn btn-fods btn-sm px-3 py-2" id="dvTableAdd"><i class="ti ti-plus me-1"></i> New DV</button>
            <?php endif; ?>
        </div>
    </div>

    <!-- Stat cards (filled by mydisbursement.js for the selected year) -->
    <!-- Stat cards (filled by mydisbursement.js for the selected year) -->
    <div class="row g-3 mb-4" id="dvTableCards">
        <div class="col-6 col-xl-3">
            <button type="button" class="fods-stat" data-table="dvTable" data-filter="pending">
                <div class="fods-stat-icon tone-amber"><i class="ti ti-hourglass"></i></div>
                <div><div class="fods-stat-value" data-card-value="pending">0</div><div class="fods-stat-label" data-card-label="pending">For approval</div></div>
            </button>
        </div>
        <div class="col-6 col-xl-3">
            <button type="button" class="fods-stat" data-table="dvTable" data-filter="disapproved">
                <div class="fods-stat-icon tone-red"><i class="ti ti-arrow-back-up"></i></div>
                <div><div class="fods-stat-value" data-card-value="returned">0</div><div class="fods-stat-label" data-card-label="returned">Returned</div></div>
            </button>
        </div>
        <div class="col-6 col-xl-3">
            <button type="button" class="fods-stat" data-table="dvTable" data-filter="approved">
                <div class="fods-stat-icon tone-green"><i class="ti ti-circle-check"></i></div>
                <div><div class="fods-stat-value" data-card-value="approved">0</div><div class="fods-stat-label" data-card-label="approved">Approved</div></div>
            </button>
        </div>
        <div class="col-6 col-xl-3">
            <button type="button" class="fods-stat" data-table="dvTable" data-filter="all">
                <div class="fods-stat-icon tone-teal"><i class="ti ti-cash"></i></div>
                <div><div class="fods-stat-value" data-card-value="total">₱0.00</div><div class="fods-stat-label" data-card-label="total">Total amount</div></div>
            </button>
        </div>
    </div>

    <div class="fods-card">
        <div class="fods-card-head">
            <div class="fods-filter" id="dvTableFilter">
                <button type="button" class="active" data-filter="all">All records</button>
                <button type="button" data-filter="pending">For approval</button>
                <button type="button" data-filter="disapproved">Returned</button>
                <button type="button" data-filter="approved">Approved</button>
            </div>
            <div class="fods-search">
                <i class="ti ti-search"></i>
                <input type="search" class="form-control form-control-sm" id="dvTableSearch" placeholder="Search DV no., serial no., payee...">
            </div>
        </div>
        <div class="fods-card-body fods-dt">
            <table id="dvTable" class="table fods-table fods-table-stack w-100">
                <thead>
                    <tr>
                        <th>DV</th>
                        <th class="d-none d-md-table-cell">Payee</th>
                        <th class="d-none d-lg-table-cell">ORS / BURS no.</th>
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
<div class="modal fade fods-modal fods-flat" id="dvModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable modal-fullscreen-md-down dv-dialog">
        <div class="modal-content">
            <form id="dvForm" novalidate>
                <div class="modal-header">
                    <div class="d-flex align-items-center" style="min-width:0">
                        <div class="fods-modal-icon tone-teal"><i class="ti ti-file-dollar"></i></div>
                        <div style="min-width:0">
                            <h5 class="modal-title text-truncate" id="dvModalTitle">New Disbursement Voucher</h5>
                            <div class="fods-modal-sub" id="dvModalSub">Pick the ORS or BURS to copy from, then check the amounts.</div>
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-2 ms-auto ps-3 flex-shrink-0 text-nowrap">
                        <button type="button" class="btn btn-sm btn-fods-ghost d-none" id="dvPrintBtn"><i class="ti ti-printer me-1"></i>Print DV</button>
                        <button type="button" class="btn btn-sm btn-danger d-none" id="dvDisapproveBtn"><i class="ti ti-x me-1"></i>Disapprove</button>
                        <button type="button" class="btn btn-sm btn-success d-none" id="dvApproveBtn"><i class="ti ti-check me-1"></i>Approve</button>
                        <button type="button" class="btn btn-sm btn-fods-ghost d-none" data-history-module="dv" data-history-field="recid"><i class="ti ti-history me-1"></i>History</button>
                        <button type="button" class="btn-close ms-1" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                </div>

                <ul class="nav fods-tabs" role="tablist">
                    <li class="nav-item"><button class="nav-link active" type="button" data-bs-toggle="tab" data-bs-target="#dvPaneDetails"><i class="ti ti-file-description"></i>Details</button></li>
                    <li class="nav-item"><button class="nav-link" type="button" data-bs-toggle="tab" data-bs-target="#dvPanePs"><i class="ti ti-users"></i>Personnel Services<span class="dv-tab-amt" data-tab-total="ps"></span></button></li>
                    <li class="nav-item"><button class="nav-link" type="button" data-bs-toggle="tab" data-bs-target="#dvPaneMooe"><i class="ti ti-tools"></i>MOOE<span class="dv-tab-amt" data-tab-total="mooe"></span></button></li>
                    <li class="nav-item"><button class="nav-link" type="button" data-bs-toggle="tab" data-bs-target="#dvPaneCo"><i class="ti ti-building"></i>Capital Outlay<span class="dv-tab-amt" data-tab-total="co"></span></button></li>
                    <li class="nav-item"><button class="nav-link" type="button" data-bs-toggle="tab" data-bs-target="#dvPaneSign"><i class="ti ti-signature"></i>Certification</button></li>
                </ul>

                <div class="modal-body">
                    <input type="hidden" id="recid" name="recid">

                    <div id="dvBanner" class="alert d-none mt-3 mb-0"></div>

                    <div class="tab-content">
                        <!-- Details -->
                        <div class="tab-pane fade show active" id="dvPaneDetails">
                            <section class="fods-fsec">
                                <div class="fods-fsec-head">
                                    <h6>Source obligation</h6>
                                </div>
                                <div class="dv-source">
                                    <label class="form-label" for="serialno">ORS / BURS serial no.</label>
                                    <input type="text" class="form-control" id="serialno" name="serialno" placeholder="Type or pick the ORS / BURS this DV pays" autocomplete="off">
                                    <div class="form-text" id="dvSourceHint">Picking one copies its payee, deductions, signatory and line items into this DV.</div>
                                </div>
                            </section>

                            <section class="fods-fsec">
                                <div class="fods-fsec-head">
                                    <h6>Voucher</h6>
                                </div>
                                <div class="row g-3">
                                    <div class="col-md-5">
                                        <label class="form-label" for="dvno">DV no.</label>
                                        <input type="text" class="form-control" id="dvno" name="dvno" placeholder="Enter DV no.">
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <label class="form-label" for="disbursement_date">DV date</label>
                                        <input type="date" class="form-control" id="disbursement_date" name="disbursement_date">
                                    </div>
                                    <div class="w-100 d-none d-md-block m-0"></div>
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
                                            <div class="col-5 d-flex align-items-end dv-tax d-none">
                                                <div class="form-check form-switch mb-2">
                                                    <input class="form-check-input" type="checkbox" id="is_vatable" value="1">
                                                    <label class="form-check-label" for="is_vatable">Subject to VAT</label>
                                                </div>
                                            </div>

                                            <div class="col-4 dv-tax dv-vat d-none">
                                                <label class="form-label" for="vat_percent">VAT rate (%)</label>
                                                <input type="number" step="0.01" class="form-control" id="vat_percent" placeholder="e.g. 12">
                                            </div>
                                            <div class="col-4 dv-tax dv-ewt d-none">
                                                <label class="form-label" for="ewt_percent">EWT rate (%)</label>
                                                <input type="number" step="0.01" class="form-control" id="ewt_percent" placeholder="1, 2, 5">
                                            </div>
                                            <div class="col-4 dv-tax dv-pt d-none">
                                                <label class="form-label" for="pt_percent">Percentage tax (%)</label>
                                                <input type="number" step="0.01" class="form-control" id="pt_percent" placeholder="e.g. 5">
                                            </div>
                                            <!-- Other deductions: one row each; their sum and types go in the two hidden fields the totals use -->
                                            <div class="col-12 dv-other d-none">
                                                <div class="d-flex align-items-center justify-content-between mb-1">
                                                    <label class="form-label mb-0">Other deductions (type and amount)</label>
                                                    <button type="button" class="btn btn-sm btn-fods-ghost dv-ded-add"><i class="ti ti-plus me-1"></i>Add deduction</button>
                                                </div>
                                                <div id="dvDeductionRows" class="dv-ded-rows"></div>
                                                <input type="hidden" id="deduction_name">
                                                <input type="hidden" id="other_deductions">
                                            </div>

                                            <div class="col-md-6 dv-income d-none">
                                                <label class="form-label" for="income_tax">Income tax amount</label>
                                                <input type="number" step="0.01" class="form-control" id="income_tax">
                                            </div>

                                            <div class="col-6 dv-payroll d-none"><label class="form-label" for="sss">SSS contribution</label><input type="number" step="0.01" class="form-control" id="sss"></div>
                                            <div class="col-6 dv-payroll d-none"><label class="form-label" for="hdmf">HDMF (Pag-IBIG) contribution</label><input type="number" step="0.01" class="form-control" id="hdmf"></div>
                                            <div class="col-6 dv-payroll d-none"><label class="form-label" for="pagibig_mp2">Pag-IBIG MP2 savings</label><input type="number" step="0.01" class="form-control" id="pagibig_mp2"></div>
                                            <div class="col-6 dv-payroll d-none"><label class="form-label" for="pagibig_loan">Pag-IBIG loan payment</label><input type="number" step="0.01" class="form-control" id="pagibig_loan"></div>
                                            <div class="col-6 dv-payroll d-none"><label class="form-label" for="hmo">HMO premium</label><input type="number" step="0.01" class="form-control" id="hmo"></div>
                                            <div class="col-6 dv-payroll d-none"><label class="form-label" for="wtax">Withholding tax (WTAX)</label><input type="number" step="0.01" class="form-control" id="wtax"></div>

                                            <div class="col-12 dv-none"><div class="form-text mt-0">Pick a category to compute taxes and deductions.</div></div>
                                        </div>
                                    </div>
                                    <div class="col-lg-5">
                                        <div class="dv-sum">
                                            <div class="dv-sum-row"><span>Gross amount</span><b id="dvGross">₱0.00</b></div>
                                            <div class="dv-sum-row"><span>Total deductions</span><b class="text-danger" id="dvDeduction">₱0.00</b></div>
                                            <div class="dv-sum-row net"><span>Net amount payable</span><b id="dvNet">₱0.00</b></div>
                                        </div>
                                        <input type="hidden" id="gross_amount">
                                        <input type="hidden" id="total_deduction">
                                        <input type="hidden" id="net_amount">
                                    </div>
                                </div>
                            </section>
                        </div>

                        <!-- Line items -->
                        <div class="tab-pane fade" id="dvPanePs">
                            <div class="fods-pane-pad">
                                <div class="dv-grid-card" data-grid="ps_direct"></div>
                                <div class="dv-grid-card" data-grid="ps_indirect"></div>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="dvPaneMooe">
                            <div class="fods-pane-pad">
                                <div class="dv-grid-card" data-grid="mooe_direct"></div>
                                <div class="dv-grid-card" data-grid="mooe_indirect"></div>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="dvPaneCo">
                            <div class="fods-pane-pad">
                                <div class="dv-grid-card" data-grid="co_direct"></div>
                                <div class="dv-grid-card" data-grid="co_indirect"></div>
                            </div>
                        </div>

                        <!-- Certification: each box is approved in order by the person named on it -->
                        <div class="tab-pane fade" id="dvPaneSign">
                            <section class="fods-fsec">
                                <div class="fods-fsec-head">
                                    <h6>Box A · Supervisor <span id="dvSignStatusA"></span></h6>
                                </div>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label req" for="certified_a">Certified by</label>
                                        <select class="form-select" id="certified_a" name="certified_a"></select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label" for="position_a">Position</label>
                                        <input type="text" class="form-control" id="position_a" name="position_a" readonly>
                                    </div>
                                    <div class="col-12 dv-sign-note mt-1" id="dvSignNoteA"></div>
                                </div>
                            </section>

                            <section class="fods-fsec">
                                <div class="fods-fsec-head">
                                    <h6>Box C · Accounting <span id="dvSignStatusC"></span></h6>
                                </div>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label req" for="certified_c">Certified by</label>
                                        <select class="form-select" id="certified_c" name="certified_c"></select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label" for="position_c">Position</label>
                                        <input type="text" class="form-control" id="position_c" name="position_c" readonly>
                                    </div>
                                    <div class="col-12 dv-sign-note mt-1" id="dvSignNoteC"></div>
                                </div>
                            </section>

                            <section class="fods-fsec">
                                <div class="fods-fsec-head">
                                    <h6>Box D · Approved for payment <span id="dvSignStatusD"></span></h6>
                                </div>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label req" for="certified_d">Approved by</label>
                                        <select class="form-select" id="certified_d" name="certified_d"></select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label" for="position_d">Position</label>
                                        <input type="text" class="form-control" id="position_d" name="position_d" readonly>
                                    </div>
                                    <div class="col-12 dv-sign-note mt-1" id="dvSignNoteD"></div>
                                </div>
                            </section>
                        </div>
                    </div>
                </div>

                <div class="modal-footer justify-content-between">
                    <div class="dv-totals">
                        <span>Gross <b id="dvFootGross">₱0.00</b></span>
                        <span>Deductions <b id="dvFootDeduction">₱0.00</b></span>
                        <span>Net <b id="dvFootNet">₱0.00</b></span>
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


<!-- DV print preview -->
<div class="modal fade fods-modal" id="dvPrintModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-fullscreen-md-down">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Disbursement voucher — print preview</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0">
                <iframe id="dvPrintFrame" src="about:blank" style="width:100%; height:80vh; border:0;"></iframe>
            </div>
        </div>
    </div>
</div>

<script>
    var __dv_data = <?= json_encode($disbursementhddata, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
    var __dv_lookups = <?= json_encode($lookups, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
    var __dv_perms = <?= json_encode($perms) ?>;
    var __dv_open = <?= json_encode($open, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
</script>
<script src="<?=base_url('assets/js/disbursement/mydisbursement.js?v=15');?>"></script>
<?php echo view('templates/myfooter.php'); ?>
