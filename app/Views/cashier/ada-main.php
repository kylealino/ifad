<?php
echo view('templates/myheader.php');
?>
<head>
<style>
.ada-sum { border:1px solid var(--fods-border); background:#fafbfc; border-radius:12px; padding:.9rem 1rem; }
.ada-sum-row { display:flex; justify-content:space-between; gap:1rem; font-size:.85rem; color:var(--fods-muted); }
.ada-sum-row b { color:#15803d; font-size:1.1rem; font-variant-numeric:tabular-nums; }
.ada-sign { border:1px solid var(--fods-border); border-radius:12px; padding:.85rem 1rem; background:#fff; height:100%; }
.ada-sign-box { font-size:.7rem; font-weight:600; text-transform:uppercase; letter-spacing:.05em; color:var(--fods-muted); margin-bottom:.6rem; }
.ui-autocomplete { z-index:2000 !important; max-height:280px; overflow-y:auto; overflow-x:hidden; font-size:.82rem; }
.ui-autocomplete .ors-ac-sub { display:block; font-size:.72rem; color:var(--fods-muted); }
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
                    <li class="breadcrumb-item active" aria-current="page">ADA</li>
                </ol>
            </nav>
            <h1 class="fods-page-title">Advice to Debit Account</h1>
            <p class="fods-page-sub">Prepare an ADA for a disbursement voucher, then send it for approval.</p>
        </div>
        <div class="fods-page-actions d-flex">
            <select class="form-select form-select-sm fods-year" id="adaYear" aria-label="Year"></select>
            <?php if ($perms['add']): ?>
                <button type="button" class="btn btn-fods btn-sm px-3 py-2" id="adaTableAdd"><i class="ti ti-plus me-1"></i> New ADA</button>
            <?php endif; ?>
        </div>
    </div>

    <!-- Stat cards (filled by myada.js for the selected year) -->
    <div class="row g-3 mb-4" id="adaTableCards">
        <div class="col-6 col-xl-3">
            <button type="button" class="fods-stat" data-table="adaTable" data-filter="pending">
                <div class="fods-stat-icon tone-amber"><i class="ti ti-hourglass"></i></div>
                <div><div class="fods-stat-value" data-card-value="pending">0</div><div class="fods-stat-label" data-card-label="pending">Pending</div></div>
            </button>
        </div>
        <div class="col-6 col-xl-3">
            <button type="button" class="fods-stat" data-table="adaTable" data-filter="disapproved">
                <div class="fods-stat-icon tone-red"><i class="ti ti-arrow-back-up"></i></div>
                <div><div class="fods-stat-value" data-card-value="returned">0</div><div class="fods-stat-label" data-card-label="returned">Returned</div></div>
            </button>
        </div>
        <div class="col-6 col-xl-3">
            <button type="button" class="fods-stat" data-table="adaTable" data-filter="approved">
                <div class="fods-stat-icon tone-green"><i class="ti ti-circle-check"></i></div>
                <div><div class="fods-stat-value" data-card-value="approved">0</div><div class="fods-stat-label" data-card-label="approved">Approved</div></div>
            </button>
        </div>
        <div class="col-6 col-xl-3">
            <button type="button" class="fods-stat" data-table="adaTable" data-filter="all">
                <div class="fods-stat-icon tone-teal"><i class="ti ti-cash"></i></div>
                <div><div class="fods-stat-value" data-card-value="total">₱0.00</div><div class="fods-stat-label" data-card-label="total">Total amount</div></div>
            </button>
        </div>
    </div>

    <div class="fods-card">
        <div class="fods-card-head">
            <div class="fods-filter" id="adaTableFilter">
                <button type="button" class="active" data-filter="all">All</button>
                <button type="button" data-filter="pending">Pending</button>
                <button type="button" data-filter="disapproved">Returned</button>
                <button type="button" data-filter="approved">Approved</button>
            </div>
            <div class="fods-search">
                <i class="ti ti-search"></i>
                <input type="search" class="form-control form-control-sm" id="adaTableSearch" placeholder="Search ADA no., DV no., payee...">
            </div>
        </div>
        <div class="fods-card-body fods-dt">
            <table id="adaTable" class="table fods-table fods-table-stack w-100">
                <thead>
                    <tr>
                        <th>ADA</th>
                        <th class="d-none d-md-table-cell">Payee</th>
                        <th class="d-none d-lg-table-cell">Date</th>
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
<div class="modal fade fods-modal" id="adaModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable modal-fullscreen-md-down">
        <div class="modal-content">
            <form id="adaForm" novalidate>
                <div class="modal-header border-bottom">
                    <div class="d-flex align-items-center" style="min-width:0">
                        <div class="fods-modal-icon tone-teal"><i class="ti ti-credit-card"></i></div>
                        <div style="min-width:0">
                            <h5 class="modal-title text-truncate" id="adaModalTitle">New ADA</h5>
                            <div class="fods-modal-sub" id="adaModalSub">Pick the DV to pay; its payee and amount fill in.</div>
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-2 ms-auto ps-3 flex-shrink-0 text-nowrap">
                        <button type="button" class="btn btn-sm btn-fods-ghost d-none" id="adaPrintBtn"><i class="ti ti-printer me-1"></i>Print</button>
                        <button type="button" class="btn-close ms-1" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                </div>

                <div class="modal-body" style="background:var(--fods-bg);">
                    <input type="hidden" id="recid" name="recid">
                    <div id="adaBanner" class="alert d-none mb-3"></div>

                    <div class="fods-card mb-3"><div class="fods-card-body pt-3">
                        <div class="fods-section mt-0">Advice</div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label" for="dvno">DV no.</label>
                                <input type="text" class="form-control" id="dvno" name="dvno" placeholder="Type or pick a DV no. / payee" autocomplete="off" required>
                                <div class="invalid-feedback">Pick the DV this ADA pays.</div>
                            </div>
                            <div class="col-md-3 col-6">
                                <label class="form-label" for="adano">ADA no.</label>
                                <input type="text" class="form-control" id="adano" name="adano" placeholder="Enter ADA no.">
                            </div>
                            <div class="col-md-3 col-6">
                                <label class="form-label" for="ada_date">ADA date</label>
                                <input type="date" class="form-control" id="ada_date" name="ada_date">
                            </div>
                            <div class="col-12">
                                <label class="form-label" for="particulars">Particulars</label>
                                <textarea class="form-control" id="particulars" name="particulars" rows="3" readonly></textarea>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="payee_name">Payee</label>
                                <input type="text" class="form-control" id="payee_name" name="payee_name" readonly>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="payee_accountno">Account no.</label>
                                <input type="text" class="form-control" id="payee_accountno" name="payee_accountno" readonly>
                            </div>
                            <div class="col-12">
                                <div class="ada-sum">
                                    <div class="ada-sum-row"><span>Net amount</span><b id="adaNet">₱0.00</b></div>
                                </div>
                                <input type="hidden" id="net_amount" name="net_amount">
                            </div>
                        </div>
                    </div></div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="ada-sign">
                                <div class="ada-sign-box">Signatory 1</div>
                                <label class="form-label" for="certified_a">Name</label>
                                <select class="form-select" id="certified_a" name="certified_a"></select>
                                <label class="form-label mt-2" for="position_a">Position</label>
                                <input type="text" class="form-control" id="position_a" name="position_a" readonly>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="ada-sign">
                                <div class="ada-sign-box">Signatory 2</div>
                                <label class="form-label" for="certified_b">Name</label>
                                <select class="form-select" id="certified_b" name="certified_b"></select>
                                <label class="form-label mt-2" for="position_b">Position</label>
                                <input type="text" class="form-control" id="position_b" name="position_b" readonly>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-fods-ghost px-3" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-sm btn-fods px-4" id="submitBtn">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Print preview -->
<div class="modal fade fods-modal" id="adaPrintModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-fullscreen-md-down">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">ADA — print preview</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0">
                <iframe id="adaPrintFrame" src="about:blank" style="width:100%; height:80vh; border:0;"></iframe>
            </div>
        </div>
    </div>
</div>

<script>
    var __ada_data = <?= json_encode($adadata, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
    var __ada_lookups = <?= json_encode($lookups, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
    var __ada_perms = <?= json_encode($perms) ?>;
    var __ada_open = <?= json_encode($open, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
</script>
<script src="<?=base_url('assets/js/cashier/myada.js?v=1');?>"></script>
<?php echo view('templates/myfooter.php'); ?>
