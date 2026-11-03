<?php
// Administration - one role: name, on/off and its
// permissions (Config\Permissions). $isMine = the role you are signed in with:
// it stays on and keeps "Manage roles and permissions".
use Config\Sorf\Permissions;

$perms = $role['permissions'] ?? [];
echo view('sorf/templates/sorf-header', ['pageTitle' => $role ? 'Role · ' . $role['role_name'] : 'New Role', 'nav' => 'admin']);
?>
<div class="drf-wrap is-narrow">
    <a class="drf-back" href="<?= sorf_url('myroles') ?>"><i class="ti ti-arrow-left"></i> Roles &amp; Permissions</a>

    <div class="drf-page-head">
        <div>
            <h1 class="drf-page-title"><?= $role ? esc($role['role_name']) : 'New Role' ?></h1>
            <p class="drf-page-sub"><?= $role ? number_format($role['users']) . ($role['users'] === 1 ? ' account has' : ' accounts have') . ' this role.' : 'Name the role, then tick what it may do.' ?></p>
        </div>
    </div>

    <?php if ($isMine): ?>
        <div class="drf-next is-warn" role="note">
            <div>
                <div class="drf-next-title">This is your own role</div>
                <div class="drf-next-text">It stays switched on and keeps "Manage roles and permissions", so you cannot lock yourself out.</div>
            </div>
        </div>
    <?php endif; ?>

    <form class="drf-card" id="roleForm" novalidate>
        <input type="hidden" name="recid" value="<?= (int) ($role['recid'] ?? 0) ?>">
        <div class="drf-card-body p-4">
            <section class="drf-section">
                <h2 class="drf-section-title">01 — Role</h2>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label" for="role_name">Role name <span class="drf-req">*</span></label>
                        <input type="text" class="form-control" name="role_name" id="role_name" value="<?= esc($role['role_name'] ?? '', 'attr') ?>" maxlength="100" placeholder="e.g. Division Chief" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="description">Description</label>
                        <input type="text" class="form-control" name="description" id="description" value="<?= esc($role['description'] ?? '', 'attr') ?>" maxlength="255" placeholder="Who has this role">
                    </div>
                    <div class="col-md-6">
                        <div class="form-check form-switch drf-switch">
                            <input class="form-check-input" type="checkbox" role="switch" name="is_active" id="is_active" value="1" <?= ($role['is_active'] ?? 1) ? 'checked' : '' ?> <?= $isMine ? 'disabled' : '' ?>>
                            <label class="form-check-label" for="is_active">Switched on<span class="drf-hint d-block">Off: accounts with this role can sign in but open nothing.</span></label>
                        </div>
                        <?php if ($isMine): ?><input type="hidden" name="is_active" value="1"><?php endif; ?>
                    </div>
                </div>
            </section>

            <section class="drf-section">
                <h2 class="drf-section-title">02 — Permissions</h2>
                <p class="drf-section-sub">Tick what this role may do. Some permissions need another one, which is ticked for you.</p>

                <?php foreach ($permissions->groups as $group => $items): ?>
                <fieldset class="drf-perm-group">
                    <legend><?= esc($group) ?></legend>
                    <?php foreach ($items as $code => [$label, $help]): ?>
                        <?php $locked = $isMine && $code === Permissions::ADMIN_ROLES; ?>
                        <div class="form-check drf-perm">
                            <input class="form-check-input" type="checkbox" name="perms[]" value="<?= esc($code, 'attr') ?>" id="perm_<?= esc(str_replace('.', '_', $code), 'attr') ?>"
                                data-requires="<?= esc(implode(' ', $permissions->requires[$code] ?? []), 'attr') ?>"
                                <?= in_array($code, $perms, true) || $locked ? 'checked' : '' ?> <?= $locked ? 'disabled' : '' ?>>
                            <label class="form-check-label" for="perm_<?= esc(str_replace('.', '_', $code), 'attr') ?>">
                                <strong><?= esc($label) ?></strong>
                                <span class="drf-hint d-block"><?= esc($help) ?></span>
                            </label>
                            <?php if ($locked): ?><input type="hidden" name="perms[]" value="<?= esc($code, 'attr') ?>"><?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </fieldset>
                <?php endforeach; ?>
            </section>

            <div class="drf-form-foot">
                <?php if ($role && $role['users'] === 0): ?>
                    <button type="button" class="btn btn-drf-ghost text-danger px-4 py-2 me-auto" data-role-delete="<?= $role['recid'] ?>" data-name="<?= esc($role['role_name'], 'attr') ?>"><i class="ti ti-trash me-1"></i> Delete Role</button>
                <?php endif; ?>
                <a class="btn btn-drf-ghost px-4 py-2" href="<?= sorf_url('myroles') ?>">Cancel</a>
                <button type="submit" class="btn btn-drf px-4 py-2" id="roleSubmit"><?= $role ? 'Save Role' : 'Create Role' ?></button>
            </div>
        </div>
    </form>
</div>
<?php echo view('sorf/templates/sorf-footer', ['scripts' => ['admin/myroles.js?v=1']]); ?>
