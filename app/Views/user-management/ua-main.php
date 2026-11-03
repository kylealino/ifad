<?php
// Initials for the avatar: drop anything after a comma (", CPA, CTT") and
// any token ending in "." (titles / middle initials like "ATTY." or "M.").
if (!function_exists('ua_initials')) {
function ua_initials($name) {
    $base = trim(explode(',', (string) $name)[0]);
    $tokens = array_values(array_filter(preg_split('/\s+/', $base), function ($t) {
        return $t !== '' && substr($t, -1) !== '.';
    }));
    if (empty($tokens)) { return strtoupper(substr($base, 0, 1)); }
    $first = substr($tokens[0], 0, 1);
    $last = count($tokens) > 1 ? substr(end($tokens), 0, 1) : '';
    return strtoupper($first . $last);
}
}

if (!function_exists('ua_tone')) {
function ua_tone($seed) {
    $tones = ['tone-teal', 'tone-indigo', 'tone-amber', 'tone-green', 'tone-slate'];
    return $tones[abs(crc32((string) $seed)) % count($tones)];
}
}

$total_users = count($myuadata);
$active_users = count(array_filter($myuadata, fn($u) => $u['is_active'] == 1));
$no_role_users = count(array_filter($myuadata, fn($u) => empty($u['role_id'])));
$total_roles = count($rolesdata);
$active_roles = count(array_filter($rolesdata, fn($r) => $r['is_active'] == 1));

echo view('templates/myheader.php');
?>
<head>
<style>
.ua-user-cell { display:flex; align-items:center; gap:.75rem; min-width:220px; }
.ua-user-name { font-weight:600; color:var(--fods-text); line-height:1.2; }
.ua-user-sub { font-size:.78rem; color:var(--fods-muted); }
.ua-role-icon { width:36px; height:36px; border-radius:10px; display:grid; place-items:center; font-size:1.1rem; flex-shrink:0; }
.ua-actions { display:flex; justify-content:flex-end; gap:.15rem; }
.perm-toolbar { display:flex; flex-wrap:wrap; gap:.5rem; align-items:center; justify-content:space-between; margin-bottom:1rem; }
.perm-grid { display:grid; grid-template-columns:repeat(auto-fill, minmax(min(100%, 280px), 1fr)); gap:.75rem; }
.perm-family { border:1px solid var(--fods-border); border-radius:12px; background:#fff; transition:border-color .15s; }
.perm-family.has-selection { border-color:#bfe0e0; }
.perm-family-head { display:flex; align-items:center; justify-content:space-between; padding:.65rem .9rem; border-bottom:1px solid var(--fods-border); }
.perm-family-head .form-check-label { font-weight:600; color:var(--fods-text); font-size:.875rem; }
.perm-family-count { font-size:.72rem; color:var(--fods-muted); background:#f1f5f9; border-radius:999px; padding:.1rem .5rem; }
.perm-family.has-selection .perm-family-count { background:var(--fods-teal-soft); color:var(--fods-teal); }
.perm-family-body { padding:.5rem .9rem .7rem; }
.perm-family-body .form-check { margin-bottom:.3rem; }
.perm-family-body .form-check-label { font-size:.83rem; color:#334155; }
.perm-code { font-size:.7rem; color:#94a3b8; margin-left:.25rem; }
.ua-preview-avatar { width:42px; height:42px; font-size:.9rem; margin-right:.85rem; }
.ua-user-extra { font-size:.78rem; color:var(--fods-muted); }

/* Tablet: long cells wrap instead of forcing sideways scroll */
@media (max-width: 1199.98px) {
    .ua-user-cell { min-width:0; }
    .fods-chip { white-space:normal; }
}

/* Phone */
@media (max-width: 767.98px) {
    .ua-user-name { overflow-wrap:anywhere; }
    .ua-actions { gap:.25rem; }
    .perm-toolbar { flex-direction:column; align-items:stretch; }
    .perm-toolbar > .d-flex > .btn { flex:1; }
    .perm-family-body .form-check { margin-bottom:.55rem; }
    #permSelectedCount { width:100%; text-align:center; }
    #rolesTable .ua-role-actions { grid-column:1 / -1; grid-row:auto; align-self:stretch; padding-top:.6rem !important; border-top:1px solid #f1f4f8 !important; }
    #rolesTable .ua-role-actions .ua-actions { justify-content:stretch; }
    #rolesTable .ua-role-actions .me-role-perms { flex:1; }
}
</style>
</head>
<div class="container-fluid">
    <input type="hidden" id="__siteurl" data-mesiteurl="<?=site_url();?>" />
    <div class="row me-myua-outp-msg mx-0"></div>

    <!-- Page header -->
    <div class="fods-page-head">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="<?=site_url();?>"><i class="ti ti-home"></i></a></li>
                    <li class="breadcrumb-item"><a href="#">Administration</a></li>
                    <li class="breadcrumb-item active" aria-current="page">User Management</li>
                </ol>
            </nav>
            <h1 class="fods-page-title">User Management</h1>
            <p class="fods-page-sub">Manage accounts, assign roles, and control which modules each role can access.</p>
        </div>
        <div class="d-flex gap-2 fods-page-actions">
            <button type="button" class="btn btn-fods-ghost btn-sm px-3 py-2" id="btn_add_role_head"><i class="ti ti-shield me-1"></i> New Role</button>
            <button type="button" class="btn btn-fods btn-sm px-3 py-2" id="btn_add_user"><i class="ti ti-user-plus me-1"></i> Add User</button>
        </div>
    </div>

    <!-- Stats (the user tiles double as quick filters) -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <button type="button" class="fods-stat ua-stat-filter" data-filter="all">
                <div class="fods-stat-icon tone-teal"><i class="ti ti-users"></i></div>
                <div><div class="fods-stat-value"><?= $total_users ?></div><div class="fods-stat-label">Total users</div></div>
            </button>
        </div>
        <div class="col-6 col-lg-3">
            <button type="button" class="fods-stat ua-stat-filter" data-filter="active">
                <div class="fods-stat-icon tone-green"><i class="ti ti-user-check"></i></div>
                <div><div class="fods-stat-value"><?= $active_users ?></div><div class="fods-stat-label">Active accounts</div></div>
            </button>
        </div>
        <div class="col-6 col-lg-3">
            <button type="button" class="fods-stat ua-stat-filter" data-filter="norole">
                <div class="fods-stat-icon tone-amber"><i class="ti ti-user-exclamation"></i></div>
                <div><div class="fods-stat-value"><?= $no_role_users ?></div><div class="fods-stat-label">Without a role</div></div>
            </button>
        </div>
        <div class="col-6 col-lg-3">
            <div class="fods-stat">
                <div class="fods-stat-icon tone-indigo"><i class="ti ti-shield-lock"></i></div>
                <div><div class="fods-stat-value"><?= $active_roles ?><span class="text-muted fs-4 fw-normal"> / <?= $total_roles ?></span></div><div class="fods-stat-label">Active roles</div></div>
            </div>
        </div>
    </div>

    <!-- Tabs -->
    <ul class="nav fods-segment mb-3" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link <?= $active_tab === 'users' ? 'active' : '' ?>" id="users-tab" data-bs-toggle="tab" data-bs-target="#usersPane" type="button" role="tab">
                <i class="ti ti-users"></i> Users <span class="fods-count"><?= $total_users ?></span>
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link <?= $active_tab === 'roles' ? 'active' : '' ?>" id="roles-tab" data-bs-toggle="tab" data-bs-target="#rolesPane" type="button" role="tab">
                <i class="ti ti-shield-lock"></i> Roles<span class="d-none d-sm-inline">&amp; Permissions</span> <span class="fods-count"><?= $total_roles ?></span>
            </button>
        </li>
    </ul>

    <div class="tab-content">

        <!-- ============================= USERS ============================= -->
        <div class="tab-pane fade <?= $active_tab === 'users' ? 'show active' : '' ?>" id="usersPane" role="tabpanel">
            <div class="fods-card">
                <div class="fods-card-head">
                    <div class="fods-filter" id="userFilter">
                        <button type="button" class="active" data-filter="all">All</button>
                        <button type="button" data-filter="active">Active</button>
                        <button type="button" data-filter="inactive">Inactive</button>
                        <button type="button" data-filter="norole">No role</button>
                    </div>
                    <div class="fods-search">
                        <i class="ti ti-search"></i>
                        <input type="search" class="form-control form-control-sm" id="usersSearch" placeholder="Search name, username, division...">
                    </div>
                </div>
                <div class="fods-card-body fods-dt">
                    <table id="usersTable" class="table fods-table fods-table-stack w-100">
                        <thead>
                            <tr>
                                <th>User</th>
                                <th class="d-none d-lg-table-cell">Division / Section</th>
                                <th class="d-none d-xl-table-cell">Position</th>
                                <th>Role</th>
                                <th>Status</th>
                                <th class="text-end"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($myuadata as $u):
                                $sorf = $sorfMembers[strtolower($u['username'])] ?? null;
                            ?>
                            <tr data-recid="<?= $u['recid'] ?>"
                                data-full_name="<?= esc($u['full_name'], 'attr') ?>"
                                data-division="<?= esc($u['division'], 'attr') ?>"
                                data-section="<?= esc($u['section'], 'attr') ?>"
                                data-position="<?= esc($u['position'], 'attr') ?>"
                                data-username="<?= esc($u['username'], 'attr') ?>"
                                data-role_id="<?= $u['role_id'] ?>"
                                data-sorf_role_id="<?= $sorf ? $sorf['role_id'] : '' ?>"
                                data-status="<?= $u['is_active'] == 1 ? 'active' : 'inactive' ?>"
                                data-hasrole="<?= empty($u['role_id']) ? '0' : '1' ?>">
                                <td class="cell-primary">
                                    <div class="ua-user-cell">
                                        <div class="fods-avatar <?= ua_tone($u['username']) ?>"><?= esc(ua_initials($u['full_name'])) ?></div>
                                        <div style="min-width:0">
                                            <div class="ua-user-name"><?= esc($u['full_name']) ?></div>
                                            <div class="ua-user-sub">@<?= esc($u['username']) ?></div>
                                            <?php /* Columns hidden on smaller screens fold into the name cell */ ?>
                                            <div class="ua-user-extra d-xl-none"><?= esc($u['position']) ?><span class="d-lg-none"> · <?= esc($u['division']) ?></span></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="d-none d-lg-table-cell">
                                    <div><?= esc($u['division']) ?></div>
                                    <?php if (!empty($u['section']) && $u['section'] !== '-'): ?>
                                        <div class="ua-user-sub"><?= esc($u['section']) ?></div>
                                    <?php endif; ?>
                                </td>
                                <td class="d-none d-xl-table-cell"><?= esc($u['position']) ?></td>
                                <td class="cell-left">
                                    <?php if (!empty($u['role_name'])): ?>
                                        <span class="fods-chip tone-teal" title="FODS role"><i class="ti ti-shield"></i><?= esc($u['role_name']) ?></span>
                                    <?php else: ?>
                                        <span class="fods-chip tone-amber" title="No FODS role">No FODS role</span>
                                    <?php endif; ?>
                                    <?php if ($sorf): ?>
                                        <span class="fods-chip tone-indigo mt-1" title="Document Request System (SORF) role"><i class="ti ti-file-description"></i>SORF · <?= esc($sorf['role_name']) ?><?= $sorf['role_active'] ? '' : ' (off)' ?></span>
                                    <?php endif; ?>
                                </td>
                                <td class="cell-right">
                                    <?php if ($u['is_active'] == 1): ?>
                                        <span class="fods-chip tone-green"><span class="fods-dot"></span>Active</span>
                                    <?php else: ?>
                                        <span class="fods-chip tone-slate"><span class="fods-dot"></span>Inactive</span>
                                    <?php endif; ?>
                                </td>
                                <td class="cell-actions">
                                    <div class="ua-actions">
                                        <button type="button" class="fods-icon-btn is-teal me-user-edit" title="Edit user"><i class="ti ti-pencil"></i></button>
                                        <?php if ($u['is_active'] == 1): ?>
                                            <button type="button" class="fods-icon-btn is-danger me-user-toggle" data-recid="<?= $u['recid'] ?>" title="Deactivate"><i class="ti ti-user-off"></i></button>
                                        <?php else: ?>
                                            <button type="button" class="fods-icon-btn is-teal me-user-toggle" data-recid="<?= $u['recid'] ?>" title="Activate"><i class="ti ti-user-check"></i></button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- ======================= ROLES & PERMISSIONS ======================= -->
        <div class="tab-pane fade <?= $active_tab === 'roles' ? 'show active' : '' ?>" id="rolesPane" role="tabpanel">
            <div class="fods-card">
                <div class="fods-card-head">
                    <div>
                        <h6 class="fods-card-title">Roles</h6>
                        <div class="ua-user-sub">A user gets every module permission of the role assigned to them.</div>
                    </div>
                    <div class="d-flex flex-column flex-sm-row gap-2 align-items-stretch align-items-sm-center">
                        <div class="fods-search">
                            <i class="ti ti-search"></i>
                            <input type="search" class="form-control form-control-sm" id="rolesSearch" placeholder="Search roles...">
                        </div>
                        <button type="button" class="btn btn-fods btn-sm px-3" id="btn_add_role"><i class="ti ti-plus me-1"></i> Add Role</button>
                    </div>
                </div>
                <div class="fods-card-body fods-dt">
                    <table id="rolesTable" class="table fods-table fods-table-stack w-100">
                        <thead>
                            <tr>
                                <th>Role</th>
                                <th>Access</th>
                                <th class="d-none d-lg-table-cell">Members</th>
                                <th>Status</th>
                                <th class="text-end"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($rolesdata as $r):
                                $codeCount = empty($r['access_codes']) ? 0 : count(explode(',', $r['access_codes']));
                            ?>
                            <tr data-recid="<?= $r['recid'] ?>"
                                data-role_name="<?= esc($r['role_name'], 'attr') ?>"
                                data-description="<?= esc($r['description'], 'attr') ?>"
                                data-access_codes="<?= esc((string) $r['access_codes'], 'attr') ?>">
                                <td class="cell-primary">
                                    <div class="ua-user-cell">
                                        <div class="ua-role-icon <?= ua_tone($r['role_name']) ?>"><i class="ti ti-shield-lock"></i></div>
                                        <div style="min-width:0">
                                            <div class="ua-user-name"><?= esc($r['role_name']) ?></div>
                                            <div class="ua-user-sub"><?= esc($r['description']) ?: '&nbsp;' ?></div>
                                            <div class="ua-user-extra d-lg-none"><i class="ti ti-users me-1"></i><?= (int) $r['user_count'] ?> member<?= (int) $r['user_count'] === 1 ? '' : 's' ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="cell-left" data-order="<?= $codeCount ?>">
                                    <?php if ($codeCount > 0): ?>
                                        <span class="fods-chip tone-indigo text-nowrap"><i class="ti ti-key"></i><?= $codeCount ?> permission<?= $codeCount === 1 ? '' : 's' ?></span>
                                    <?php else: ?>
                                        <span class="fods-chip tone-amber">No permissions</span>
                                    <?php endif; ?>
                                </td>
                                <td class="d-none d-lg-table-cell" data-order="<?= (int) $r['user_count'] ?>">
                                    <span class="text-muted"><i class="ti ti-users me-1"></i><?= (int) $r['user_count'] ?></span>
                                </td>
                                <td class="cell-right">
                                    <?php if ($r['is_active'] == 1): ?>
                                        <span class="fods-chip tone-green"><span class="fods-dot"></span>Active</span>
                                    <?php else: ?>
                                        <span class="fods-chip tone-slate"><span class="fods-dot"></span>Inactive</span>
                                    <?php endif; ?>
                                </td>
                                <td class="cell-actions ua-role-actions">
                                    <div class="ua-actions align-items-center">
                                        <button type="button" class="btn btn-sm btn-fods-ghost me-1 me-role-perms"><i class="ti ti-shield-check"></i><span class="d-md-none d-xl-inline ms-1">Permissions</span></button>
                                        <button type="button" class="fods-icon-btn is-teal me-role-edit" title="Edit role"><i class="ti ti-pencil"></i></button>
                                        <?php if ($r['is_active'] == 1): ?>
                                            <button type="button" class="fods-icon-btn me-role-toggle" data-roleid="<?= $r['recid'] ?>" title="Deactivate"><i class="ti ti-lock"></i></button>
                                        <?php else: ?>
                                            <button type="button" class="fods-icon-btn is-teal me-role-toggle" data-roleid="<?= $r['recid'] ?>" title="Activate"><i class="ti ti-lock-open"></i></button>
                                        <?php endif; ?>
                                        <button type="button" class="fods-icon-btn is-danger me-role-delete" data-roleid="<?= $r['recid'] ?>" title="Delete role"><i class="ti ti-trash"></i></button>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</div>

<!-- ============================= USER MODAL ============================= -->
<div class="modal fade fods-modal" id="userModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable modal-fullscreen-sm-down">
        <div class="modal-content">
            <form class="myua-validation" novalidate>
                <div class="modal-header">
                    <div class="d-flex align-items-center">
                        <div class="fods-avatar ua-preview-avatar tone-teal" id="userPreviewAvatar"><i class="ti ti-user-plus"></i></div>
                        <div>
                            <h5 class="modal-title" id="userModalTitle">Add User</h5>
                            <div class="fods-modal-sub" id="userModalSub">Create an account and assign a role.</div>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="recid" value=""/>

                    <div class="fods-section mt-0">Profile</div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label" for="full_name">Full name</label>
                            <input type="text" id="full_name" class="form-control" placeholder="e.g. Juan A. Dela Cruz" required/>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="position">Position</label>
                            <input type="text" id="position" class="form-control" required/>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="division">Division</label>
                            <input type="text" id="division" class="form-control" required/>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="section">Section</label>
                            <input type="text" id="section" class="form-control" required/>
                        </div>
                    </div>

                    <div class="fods-section">Account &amp; access</div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label" for="username">Username</label>
                            <div class="input-group">
                                <span class="input-group-text bg-white">@</span>
                                <input type="text" id="username" class="form-control" required/>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="password">Password</label>
                            <div class="input-group">
                                <input type="password" id="password" class="form-control" autocomplete="new-password"/>
                                <button class="btn btn-fods-ghost" type="button" id="togglePassword" style="border-radius:0 10px 10px 0;"><i class="ti ti-eye" id="toggleIcon"></i></button>
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-label" for="role_id">Role</label>
                            <select id="role_id" class="form-select">
                                <option value="">No role — no module access</option>
                                <?php foreach ($rolesdata as $r): ?>
                                    <option value="<?= $r['recid'] ?>"><?= esc($r['role_name']) ?><?= $r['is_active'] == 0 ? ' (inactive)' : '' ?></option>
                                <?php endforeach; ?>
                            </select>
                            <div class="form-text">The user gets every module permission of this role.</div>
                        </div>
                        <?php if ($sorfRoles !== null): ?>
                        <div class="col-12">
                            <label class="form-label" for="sorf_role_id">Document Request System (SORF) role</label>
                            <select id="sorf_role_id" class="form-select">
                                <option value="">No access to SORF</option>
                                <?php foreach ($sorfRoles as $r): ?>
                                    <option value="<?= $r['recid'] ?>"><?= esc($r['role_name']) ?><?= $r['is_active'] == 0 ? ' (switched off)' : '' ?></option>
                                <?php endforeach; ?>
                            </select>
                            <div class="form-text">IFAD Portal system 2. What each SORF role may do is set in SORF under Roles &amp; Permissions.</div>
                        </div>
                        <?php endif; ?>
                    </div>

                    <!-- Signatory designations (cert_tag, ada_tag, is_ppmp_signatory) are not set here: they stay as they are in the database -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-fods-ghost px-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-sm btn-fods px-4" id="userSubmitBtn">Create user</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ============================= ROLE MODAL ============================= -->
<div class="modal fade fods-modal" id="roleModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-fullscreen-sm-down">
        <div class="modal-content">
            <form class="myua-role-validation" novalidate>
                <div class="modal-header">
                    <div class="d-flex align-items-center">
                        <div class="fods-modal-icon tone-indigo"><i class="ti ti-shield"></i></div>
                        <div>
                            <h5 class="modal-title" id="roleModalTitle">Add Role</h5>
                            <div class="fods-modal-sub">Set module permissions after saving.</div>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="recid" id="role_recid" value=""/>
                    <div class="mb-3">
                        <label class="form-label" for="role_name">Role name</label>
                        <input type="text" name="role_name" id="role_name" class="form-control" placeholder="e.g. Budget Officer" required>
                    </div>
                    <div>
                        <label class="form-label" for="role_description">Description</label>
                        <textarea name="description" id="role_description" rows="3" class="form-control" placeholder="What does this role do?"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-fods-ghost px-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-sm btn-fods px-4" id="roleSubmitBtn">Create role</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ========================== PERMISSIONS MODAL ========================== -->
<div class="modal fade fods-modal" id="permissionsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable modal-fullscreen-md-down">
        <div class="modal-content">
            <div class="modal-header">
                <div class="d-flex align-items-center">
                    <div class="fods-modal-icon tone-teal"><i class="ti ti-shield-check"></i></div>
                    <div>
                        <h5 class="modal-title">Module permissions</h5>
                        <div class="fods-modal-sub">Role: <span class="fw-semibold text-dark" id="permModalRoleName"></span></div>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="rolePermissionsForm">
                <input type="hidden" id="perm_role_id" value=""/>
                <div class="perm-toolbar">
                    <div class="fods-search">
                        <i class="ti ti-search"></i>
                        <input type="search" class="form-control form-control-sm" id="permSearch" placeholder="Filter modules...">
                    </div>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-sm btn-fods-ghost" id="permSelectAll"><i class="ti ti-checks me-1"></i>Select all</button>
                        <button type="button" class="btn btn-sm btn-fods-ghost" id="permClearAll"><i class="ti ti-x me-1"></i>Clear</button>
                    </div>
                </div>
                <div class="perm-grid">
                    <?php foreach ($modulefamilies as $familyKey => $family): ?>
                        <div class="perm-family" data-search="<?= esc(strtolower($family['label'] . ' ' . implode(' ', array_column($family['items'], 'access_name'))), 'attr') ?>">
                            <div class="perm-family-head">
                                <div class="form-check mb-0">
                                    <input class="form-check-input family-select-all" type="checkbox" id="family_<?= $familyKey ?>">
                                    <label class="form-check-label" for="family_<?= $familyKey ?>"><?= esc($family['label']) ?></label>
                                </div>
                                <span class="perm-family-count">0/<?= count($family['items']) ?></span>
                            </div>
                            <div class="perm-family-body">
                                <?php foreach ($family['items'] as $item): ?>
                                    <div class="form-check">
                                        <input class="form-check-input access-code-checkbox" type="checkbox"
                                            id="access_<?= $item['access_code'] ?>" value="<?= $item['access_code'] ?>">
                                        <label class="form-check-label" for="access_<?= $item['access_code'] ?>">
                                            <?= esc($item['action']) ?><span class="perm-code"><?= esc($item['access_code']) ?></span>
                                        </label>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="modal-footer justify-content-between">
                <span class="small text-muted" id="permSelectedCount"></span>
                <div class="d-flex gap-2 fods-modal-actions">
                    <button type="button" class="btn btn-sm btn-fods-ghost px-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-sm btn-fods px-4" id="btn_save_role_access">Save permissions</button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ========================== DELETE ROLE MODAL ========================== -->
<div class="modal fade fods-modal" id="confirmRoleDeleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width:380px;">
        <div class="modal-content">
            <div class="modal-body text-center pt-4 pb-3">
                <div class="fods-modal-icon tone-red mx-auto mb-3" style="margin-right:auto !important;"><i class="ti ti-trash"></i></div>
                <h5 class="modal-title mb-1">Delete this role?</h5>
                <div class="fods-modal-sub">Only roles with no assigned users can be deleted. This can't be undone.</div>
            </div>
            <div class="modal-footer justify-content-center">
                <button type="button" class="btn btn-sm btn-fods-ghost px-3" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-sm btn-danger px-4" style="border-radius:10px;" id="confirmRoleDeleteBtn">Delete role</button>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="<?=base_url('assets/js/maintenance/myua.js?v=8');?>"></script>
<script src="<?=base_url('assets/js/mysysapps.js');?>"></script>
<script>
    __mysys_myua_ent.__user_modal();
    __mysys_myua_ent.__user_saving();
    __mysys_myua_ent.__user_toggle();
    __mysys_myua_ent.__role_modal();
    __mysys_myua_ent.__role_saving();
    __mysys_myua_ent.__role_toggle();
    __mysys_myua_ent.__role_delete();
    __mysys_myua_ent.__role_permissions();

    $(document).ready(function () {
        __mysys_myua_ent.__tables();
    });
</script>
<?php
    echo view('templates/myfooter.php');
?>
