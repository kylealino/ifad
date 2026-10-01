<?php echo view('templates/myheader.php'); ?>
<div class="container-fluid">
    <input type="hidden" id="__siteurl" data-mesiteurl="<?=site_url();?>" />
    <div class="row me-outp-msg mx-0"></div>

    <div class="fods-page-head">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="<?=site_url();?>"><i class="ti ti-home"></i></a></li>
                    <li class="breadcrumb-item"><a href="#">Maintenance</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Payee</li>
                </ol>
            </nav>
            <h1 class="fods-page-title">Payees</h1>
            <p class="fods-page-sub">Everyone FODS pays — their account, TIN and how they're paid.</p>
        </div>
        <?php if ($perms['add']): ?>
        <div class="fods-page-actions d-flex">
            <button type="button" class="btn btn-fods btn-sm px-3 py-2" id="payeeTableAdd"><i class="ti ti-plus me-1"></i> Add Payee</button>
        </div>
        <?php endif; ?>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <button type="button" class="fods-stat" data-table="payeeTable" data-filter="all">
                <div class="fods-stat-icon tone-teal"><i class="ti ti-users"></i></div>
                <div><div class="fods-stat-value"><?= number_format($stats['total']) ?></div><div class="fods-stat-label">Total payees</div></div>
            </button>
        </div>
        <div class="col-6 col-lg-3">
            <button type="button" class="fods-stat" data-table="payeeTable" data-filter="lddap">
                <div class="fods-stat-icon tone-indigo"><i class="ti ti-building-bank"></i></div>
                <div><div class="fods-stat-value"><?= number_format($stats['lddap']) ?></div><div class="fods-stat-label">Paid via LDDAP-ADA</div></div>
            </button>
        </div>
        <div class="col-6 col-lg-3">
            <button type="button" class="fods-stat" data-table="payeeTable" data-filter="noaccount">
                <div class="fods-stat-icon tone-amber"><i class="ti ti-credit-card-off"></i></div>
                <div><div class="fods-stat-value"><?= number_format($stats['no_account']) ?></div><div class="fods-stat-label">No account number</div></div>
            </button>
        </div>
        <div class="col-6 col-lg-3">
            <button type="button" class="fods-stat" data-table="payeeTable" data-filter="notin">
                <div class="fods-stat-icon tone-red"><i class="ti ti-id-badge-2"></i></div>
                <div><div class="fods-stat-value"><?= number_format($stats['no_tin']) ?></div><div class="fods-stat-label">No TIN</div></div>
            </button>
        </div>
    </div>

    <div class="fods-card">
        <div class="fods-card-head">
            <div class="fods-filter" id="payeeTableFilter">
                <button type="button" class="active" data-filter="all">All</button>
                <button type="button" data-filter="lddap">LDDAP-ADA</button>
                <button type="button" data-filter="noaccount">No account no.</button>
                <button type="button" data-filter="notin">No TIN</button>
            </div>
            <div class="fods-search">
                <i class="ti ti-search"></i>
                <input type="search" class="form-control form-control-sm" id="payeeTableSearch" placeholder="Search name, account, TIN...">
            </div>
        </div>
        <div class="fods-card-body fods-dt">
            <table id="payeeTable" class="table fods-table fods-table-stack w-100">
                <thead>
                    <tr>
                        <th>Payee</th>
                        <th>Account no.</th>
                        <th class="d-none d-lg-table-cell">TIN</th>
                        <th>Method</th>
                        <th class="d-none d-xl-table-cell">Contact</th>
                        <th class="text-end"></th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>

<!-- Add / edit -->
<div class="modal fade fods-modal" id="payeeModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable modal-fullscreen-sm-down">
        <div class="modal-content">
            <form id="payeeForm" novalidate>
                <div class="modal-header">
                    <div class="d-flex align-items-center">
                        <div class="fods-modal-icon tone-teal"><i class="ti ti-user-check"></i></div>
                        <div>
                            <h5 class="modal-title" id="payeeModalTitle">Add Payee</h5>
                            <div class="fods-modal-sub" id="payeeModalSub">Who gets paid and how.</div>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="recid">
                    <div class="fods-section mt-0">Payee</div>
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label" for="payee_name">Payee name</label>
                            <input type="text" class="form-control" id="payee_name" name="payee_name" required>
                            <div class="invalid-feedback">Payee name is required.</div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="payee_tin">TIN</label>
                            <input type="text" class="form-control" id="payee_tin" name="payee_tin" placeholder="000-000-000-000">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="payee_office">Office</label>
                            <input type="text" class="form-control" id="payee_office" name="payee_office">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="contact_no">Contact no.</label>
                            <input type="text" class="form-control" id="contact_no" name="contact_no">
                        </div>
                        <div class="col-12">
                            <label class="form-label" for="payee_address">Address</label>
                            <textarea class="form-control" id="payee_address" name="payee_address" rows="2"></textarea>
                        </div>
                    </div>

                    <div class="fods-section">Payment</div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label" for="payee_account_num">Account no.</label>
                            <input type="text" class="form-control" id="payee_account_num" name="payee_account_num">
                        </div>
                        <div class="col-md-3 col-6">
                            <label class="form-label" for="disb_method">Method</label>
                            <select class="form-select" id="disb_method" name="disb_method">
                                <option value="">—</option>
                                <?php foreach ($methods as $m): ?>
                                    <option value="<?= esc($m, 'attr') ?>"><?= esc($m) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3 col-6">
                            <label class="form-label" for="currency">Currency</label>
                            <select class="form-select" id="currency" name="currency" data-default="PHP">
                                <option value="">—</option>
                                <option value="PHP">PHP</option>
                                <option value="USD">USD</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-fods-ghost px-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-sm btn-fods px-4" id="submitBtn">Create</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete confirmation -->
<div class="modal fade fods-modal" id="payeeDeleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width:400px;">
        <div class="modal-content">
            <div class="modal-body text-center pt-4 pb-3">
                <div class="fods-modal-icon tone-red mx-auto mb-3" style="margin-right:auto !important;"><i class="ti ti-trash"></i></div>
                <h5 class="modal-title mb-1">Delete this payee?</h5>
                <div class="fw-semibold text-dark mb-1" id="payeeDeleteName"></div>
                <div class="fods-modal-sub">This can't be undone.</div>
            </div>
            <div class="modal-footer justify-content-center">
                <button type="button" class="btn btn-sm btn-fods-ghost px-3" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-sm btn-danger px-4" style="border-radius:10px;" id="payeeDeleteBtn">Delete</button>
            </div>
        </div>
    </div>
</div>

<script>
    var __payee_data = <?= json_encode($results, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
    var __payee_perms = <?= json_encode($perms) ?>;
</script>
<script src="<?=base_url('assets/js/maintenance/mypayee.js?v=4');?>"></script>
<?php echo view('templates/myfooter.php'); ?>
