<?php
echo view('templates/myheader.php', ['pageTitle' => 'My account']);
$roleLabel = $account['role_name'] !== '' ? $account['role_name'] : 'No role assigned';
$office = implode(' · ', array_filter([$account['division'], $account['section']], fn ($v) => trim((string) $v) !== '' && $v !== '-'));
?>
<head>
<style>
/* Profile card (left; stays in view while the forms scroll on wide screens) */
.acct-profile { overflow: hidden; }
@media (min-width: 1200px) { .acct-profile { position: sticky; top: 76px; } }
.acct-cover { height: 84px; background: linear-gradient(135deg, var(--fods-teal) 0%, #14b8a6 55%, #5eead4 100%); }
.acct-id { display: flex; flex-direction: column; align-items: center; text-align: center; padding: 0 1.25rem 1.25rem; margin-top: -56px; }
.acct-photo-drop { position: relative; border-radius: 50%; cursor: pointer; }
.acct-photo-drop .fods-avatar { width: 112px; height: 112px; font-size: 2.1rem; border: 4px solid #fff; box-shadow: 0 4px 14px rgba(15,23,42,.12); }
.acct-photo-drop:focus-visible { outline: 2px solid var(--fods-teal); outline-offset: 3px; }
.acct-photo-drop.is-over .fods-avatar { box-shadow: 0 0 0 3px var(--fods-teal-soft), 0 0 0 5px var(--fods-teal); }
.acct-photo-cam { position: absolute; right: 4px; bottom: 4px; width: 32px; height: 32px; border-radius: 50%; display: grid; place-items: center; background: #fff; border: 1px solid var(--fods-border); color: var(--fods-text); box-shadow: 0 2px 6px rgba(15,23,42,.12); }
.acct-photo-cam .ti { font-size: 1rem; }
.acct-photo-busy { position: absolute; inset: 4px; border-radius: 50%; display: none; place-items: center; background: rgba(255,255,255,.7); }
.acct-photo-drop.is-busy .acct-photo-busy { display: grid; }
.acct-name { margin-top: .75rem; font-weight: 600; font-size: 1.1rem; color: var(--fods-text); line-height: 1.3; overflow-wrap: anywhere; }
.acct-user { font-size: .82rem; color: var(--fods-muted); overflow-wrap: anywhere; }
.acct-photo-actions { display: flex; flex-wrap: wrap; justify-content: center; gap: .5rem; margin-top: 1rem; }
.acct-hint { font-size: .75rem; color: var(--fods-muted); margin-top: .5rem; }
.acct-facts { margin: 0; padding: .4rem 1.25rem 1rem; border-top: 1px solid var(--fods-border); font-size: .82rem; }
.acct-facts div { display: flex; justify-content: space-between; gap: 1rem; padding: .5rem 0; border-bottom: 1px dashed #eef2f6; }
.acct-facts div:last-child { border-bottom: 0; }
.acct-facts dt { font-weight: 500; color: var(--fods-muted); white-space: nowrap; }
.acct-facts dd { margin: 0; color: var(--fods-text); font-weight: 500; text-align: right; overflow-wrap: anywhere; min-width: 0; }
.acct-facts dd.is-empty { color: #a3adbb; font-weight: 400; }

/* Form cards (right) */
.acct-card .fods-card-head .ti { color: var(--fods-teal); font-size: 1.1rem; }
.acct-card .fods-card-body { padding: 1.25rem; }
.acct-sub { display: flex; align-items: center; gap: .75rem; margin: 1.5rem 0 1rem; font-size: .72rem; font-weight: 600; letter-spacing: .05em; text-transform: uppercase; color: var(--fods-muted); }
.acct-sub::after { content: ""; flex: 1; height: 1px; background: var(--fods-border); }
.acct-foot { display: flex; justify-content: flex-end; gap: .5rem; padding: .85rem 1.25rem; border-top: 1px solid var(--fods-border); background: #fafbfc; border-radius: 0 0 var(--fods-radius) var(--fods-radius); }
.acct-card .form-label.req::after { content: " *"; color: #dc2626; }

@media (max-width: 575.98px) {
    .acct-card .fods-card-body, .acct-foot { padding-left: 1rem; padding-right: 1rem; }
    .acct-foot .btn, .acct-photo-actions .btn { flex: 1 1 auto; }
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
                    <li class="breadcrumb-item active" aria-current="page">My account</li>
                </ol>
            </nav>
            <h1 class="fods-page-title">My account</h1>
            <p class="fods-page-sub">Your profile picture, details, contact information and password.</p>
        </div>
    </div>

    <div class="row g-4 align-items-start">
        <!-- Profile -->
        <div class="col-lg-5 col-xl-4">
            <div class="fods-card acct-profile">
                <div class="acct-cover"></div>
                <div class="acct-id">
                    <div class="acct-photo-drop" id="acctPhotoDrop" role="button" tabindex="0" aria-label="Change profile picture">
                        <span class="fods-avatar tone-teal<?= $account['photo'] !== '' ? ' has-photo' : '' ?>" id="acctPhoto" data-initials="<?= esc(fods_initials($account['full_name']), 'attr') ?>">
                            <?php if ($account['photo'] !== ''): ?><img src="<?= esc($account['photo'], 'attr') ?>" alt=""><?php else: ?><?= esc(fods_initials($account['full_name'])) ?><?php endif; ?>
                        </span>
                        <span class="acct-photo-cam"><i class="ti ti-camera"></i></span>
                        <span class="acct-photo-busy"><span class="spinner-border spinner-border-sm text-secondary"></span></span>
                    </div>
                    <div class="acct-name"><?= esc($account['full_name']) ?></div>
                    <div class="acct-user">@<?= esc($account['username']) ?></div>
                    <span class="fods-chip tone-teal mt-2"><i class="ti ti-shield-check"></i><?= esc($roleLabel) ?></span>
                    <div class="acct-photo-actions">
                        <button type="button" class="btn btn-sm btn-fods px-3" id="acctPhotoPick"><i class="ti ti-upload me-1"></i>Upload picture</button>
                        <button type="button" class="btn btn-sm btn-fods-ghost px-3<?= $account['photo'] === '' ? ' d-none' : '' ?>" id="acctPhotoRemove"><i class="ti ti-trash me-1"></i>Remove</button>
                    </div>
                    <div class="acct-hint">JPG, PNG, GIF or WEBP, up to 5 MB.</div>
                    <input type="file" id="acctPhotoFile" accept="image/jpeg,image/png,image/gif,image/webp" class="d-none">
                </div>
                <dl class="acct-facts">
                    <div><dt>Position</dt><dd class="<?= trim($account['position']) === '' ? 'is-empty' : '' ?>"><?= esc(trim($account['position']) !== '' ? $account['position'] : 'Not set') ?></dd></div>
                    <div><dt>Office</dt><dd class="<?= $office === '' ? 'is-empty' : '' ?>"><?= esc($office !== '' ? $office : 'Not set') ?></dd></div>
                    <div><dt>Email</dt><dd class="<?= $account['email'] === '' ? 'is-empty' : '' ?>"><?= esc($account['email'] !== '' ? $account['email'] : 'Not set') ?></dd></div>
                    <div><dt>Contact no.</dt><dd class="<?= $account['contact_no'] === '' ? 'is-empty' : '' ?>"><?= esc($account['contact_no'] !== '' ? $account['contact_no'] : 'Not set') ?></dd></div>
                </dl>
            </div>
        </div>

        <!-- Forms -->
        <div class="col-lg-7 col-xl-8">
            <form id="accountForm" class="fods-card acct-card mb-4" novalidate>
                <div class="fods-card-head">
                    <h6 class="fods-card-title d-flex align-items-center gap-2"><i class="ti ti-id-badge-2"></i>Details</h6>
                </div>
                <div class="fods-card-body">
                    <div class="row g-3">
                        <div class="col-sm-6">
                            <label class="form-label" for="acct_username">Username</label>
                            <input type="text" class="form-control" id="acct_username" value="<?= esc($account['username'], 'attr') ?>" disabled>
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label" for="acct_role">Role</label>
                            <input type="text" class="form-control" id="acct_role" value="<?= esc($roleLabel, 'attr') ?>" disabled>
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label req" for="full_name">Full name</label>
                            <input type="text" class="form-control" id="full_name" name="full_name" maxlength="50" value="<?= esc($account['full_name'], 'attr') ?>" data-original="<?= esc($account['full_name'], 'attr') ?>" required>
                            <div class="invalid-feedback">Full name is required.</div>
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label req" for="position">Position</label>
                            <input type="text" class="form-control" id="position" name="position" maxlength="50" value="<?= esc($account['position'], 'attr') ?>" required>
                            <div class="invalid-feedback">Position is required.</div>
                        </div>
                        <div class="col-12 d-none" id="acctNameWarn">
                            <div class="alert alert-warning py-2 px-3 small mb-0">
                                You sign <?= (int) $account['signs'] ?> approval box<?= $account['signs'] == 1 ? '' : 'es' ?> in Approver Mapping as <b><?= esc($account['full_name']) ?></b>. After a name change, the administrator must update Approver Mapping, or those approvals will no longer reach you.
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label req" for="division">Division</label>
                            <input type="text" class="form-control" id="division" name="division" maxlength="50" value="<?= esc($account['division'], 'attr') ?>" required>
                            <div class="invalid-feedback">Division is required.</div>
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label req" for="section">Section</label>
                            <input type="text" class="form-control" id="section" name="section" maxlength="100" value="<?= esc($account['section'], 'attr') ?>" required>
                            <div class="invalid-feedback">Section is required.</div>
                        </div>
                    </div>

                    <div class="acct-sub">Contact</div>
                    <div class="row g-3">
                        <div class="col-sm-6">
                            <label class="form-label" for="email">Email address</label>
                            <input type="email" class="form-control" id="email" name="email" maxlength="100" value="<?= esc($account['email'], 'attr') ?>" placeholder="name@fnri.dost.gov.ph">
                            <div class="invalid-feedback">Enter a valid email address.</div>
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label" for="contact_no">Contact no.</label>
                            <input type="text" class="form-control" id="contact_no" name="contact_no" maxlength="50" value="<?= esc($account['contact_no'], 'attr') ?>" placeholder="e.g. 0917 123 4567">
                            <div class="invalid-feedback">Numbers, spaces and + ( ) - . / only.</div>
                        </div>
                    </div>
                </div>
                <div class="acct-foot">
                    <button type="submit" class="btn btn-sm btn-fods px-4" id="submitBtn">Save changes</button>
                </div>
            </form>

            <form id="passwordForm" class="fods-card acct-card" novalidate autocomplete="off">
                <div class="fods-card-head">
                    <h6 class="fods-card-title d-flex align-items-center gap-2"><i class="ti ti-lock"></i>Password</h6>
                </div>
                <div class="fods-card-body">
                    <div class="row g-3">
                        <div class="col-sm-6">
                            <label class="form-label req" for="current_password">Current password</label>
                            <input type="password" class="form-control" id="current_password" autocomplete="current-password" required>
                            <div class="invalid-feedback">Enter your current password.</div>
                        </div>
                        <div class="col-sm-6 d-none d-sm-block"></div>
                        <div class="col-sm-6">
                            <label class="form-label req" for="new_password">New password</label>
                            <input type="password" class="form-control" id="new_password" autocomplete="new-password" minlength="8" required>
                            <div class="invalid-feedback">At least 8 characters.</div>
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label req" for="confirm_password">Confirm new password</label>
                            <input type="password" class="form-control" id="confirm_password" autocomplete="new-password" minlength="8" required>
                            <div class="invalid-feedback">The new passwords do not match.</div>
                        </div>
                    </div>
                </div>
                <div class="acct-foot">
                    <button type="submit" class="btn btn-sm btn-fods px-4" id="passwordBtn">Change password</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    var __acct_signs = <?= (int) $account['signs'] ?>;
</script>
<script src="<?=base_url('assets/js/account/myaccount.js?v=3');?>"></script>
<?php echo view('templates/myfooter.php'); ?>
