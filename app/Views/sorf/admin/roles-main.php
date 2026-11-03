<?php
// Administration - Roles. What each role may do. Who has which role is set in
// the portal's User Management (FODS).
// A role is changed on its own page (myroles?meaction=FORM).
echo view('sorf/templates/sorf-header', ['pageTitle' => 'Roles & Permissions', 'nav' => 'admin']);
?>
<div class="drf-wrap">
    <div class="drf-page-head">
        <div>
            <h1 class="drf-page-title">Roles &amp; Permissions</h1>
            <p class="drf-page-sub">Each account has one role. A role's permissions decide which pages and actions the account gets.</p>
        </div>
        <div class="drf-page-actions">
            <a class="btn btn-drf px-3 py-2" href="<?= sorf_url('myroles?meaction=FORM') ?>"><i class="ti ti-plus me-1"></i> New Role</a>
        </div>
    </div>

    <div class="drf-card">
        <div class="drf-list-scroll">
        <table class="drf-table drf-table-stack">
            <thead>
                <tr>
                    <th>Role</th>
                    <th>Permissions</th>
                    <th>Accounts</th>
                    <th>Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($roles as $r): ?>
                <tr>
                    <td data-label="Role">
                        <span class="text-start">
                            <a class="drf-reqno" href="<?= sorf_url('myroles?meaction=FORM&recid=' . $r['recid']) ?>"><?= esc($r['role_name']) ?></a>
                            <?php if ($r['recid'] === drf_current_user()['role_id']): ?><span class="drf-chip tone-indigo ms-1">Your role</span><?php endif; ?>
                            <span class="drf-sub d-block"><?= esc($r['description']) ?></span>
                        </span>
                    </td>
                    <td data-label="Permissions" class="drf-perm-cell">
                        <?php if (empty($r['permissions'])): ?>
                            <span class="drf-sub">None</span>
                        <?php else: ?>
                            <span class="drf-perm-chips">
                                <?php foreach ($r['permissions'] as $code): ?>
                                    <span class="drf-perm-chip"><?= esc($permissions->label($code)) ?></span>
                                <?php endforeach; ?>
                            </span>
                        <?php endif; ?>
                    </td>
                    <td data-label="Accounts" class="text-nowrap">
                        <?= number_format($r['active_users']) ?> active<?php if ($r['users'] > $r['active_users']): ?> <span class="drf-sub">· <?= number_format($r['users'] - $r['active_users']) ?> off</span><?php endif; ?>
                    </td>
                    <td data-label="Status">
                        <span class="drf-chip <?= $r['is_active'] ? 'tone-green' : 'tone-slate' ?>"><span class="drf-dot"></span><?= $r['is_active'] ? 'On' : 'Switched off' ?></span>
                    </td>
                    <td>
                        <span class="d-flex justify-content-end gap-1">
                            <a class="drf-icon-btn" href="<?= sorf_url('myroles?meaction=FORM&recid=' . $r['recid']) ?>" title="Edit role" aria-label="Edit <?= esc($r['role_name'], 'attr') ?>"><i class="ti ti-pencil"></i></a>
                            <?php if ($r['users'] === 0): ?>
                                <button type="button" class="drf-icon-btn is-danger" data-role-delete="<?= $r['recid'] ?>" data-name="<?= esc($r['role_name'], 'attr') ?>" title="Delete role" aria-label="Delete <?= esc($r['role_name'], 'attr') ?>"><i class="ti ti-trash"></i></button>
                            <?php else: ?>
                                <span class="drf-icon-btn is-muted" title="Accounts have this role: it cannot be deleted"><i class="ti ti-trash-off"></i></span>
                            <?php endif; ?>
                        </span>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        </div>
    </div>

    <p class="drf-hint mt-3"><i class="ti ti-info-circle me-1"></i>Changes apply on the next page each person opens. A switched-off role gives no access, without having to move its accounts.</p>
</div>
<?php echo view('sorf/templates/sorf-footer', ['scripts' => ['admin/myroles.js?v=1']]); ?>
