<?php
$user = fods_current_user();
$cuser = $user['username'];
$role_name = $user['role_name'];
$module = fods_nav_current()['label'] ?? '';

echo view('templates/myheader.php');
?>
<head>
<style>
.ar-wrap { display:flex; align-items:center; justify-content:center; padding:2rem 0; }
.ar-card { max-width:520px; width:100%; padding:2.5rem 2.25rem 2rem; text-align:center; animation:arIn .35s ease-out; }
.ar-badge { position:relative; width:84px; height:84px; margin:0 auto 1.5rem; }
.ar-badge::before { content:""; position:absolute; inset:-10px; border-radius:50%; background:#fef4e2; opacity:.55; }
.ar-badge-inner { position:relative; width:100%; height:100%; border-radius:50%; display:grid; place-items:center; font-size:2.2rem; }
.ar-code { display:inline-block; font-size:.7rem; font-weight:600; letter-spacing:.08em; text-transform:uppercase; color:#b45309; background:#fef4e2; border-radius:999px; padding:.2rem .65rem; margin-bottom:.85rem; }
.ar-title { font-size:1.4rem; font-weight:700; color:var(--fods-text); margin-bottom:.5rem; letter-spacing:-.01em; }
.ar-text { color:var(--fods-muted); font-size:.92rem; margin-bottom:1.5rem; line-height:1.55; }
.ar-details { border:1px solid var(--fods-border); border-radius:12px; text-align:left; margin-bottom:1.5rem; }
.ar-row { display:flex; justify-content:space-between; align-items:center; gap:1rem; padding:.7rem 1rem; font-size:.85rem; }
.ar-row + .ar-row { border-top:1px solid var(--fods-border); }
.ar-row-label { color:var(--fods-muted); display:flex; align-items:center; gap:.45rem; }
.ar-row-value { font-weight:600; color:var(--fods-text); text-align:right; overflow-wrap:anywhere; }
.ar-actions { display:flex; gap:.6rem; justify-content:center; }
.ar-actions .btn { padding:.55rem 1.1rem; }
.ar-help { margin-top:1.25rem; font-size:.78rem; color:#94a3b8; }
@keyframes arIn { from { opacity:0; transform:translateY(8px); } to { opacity:1; transform:none; } }
@media (max-width: 575.98px) {
    .ar-card { padding:2rem 1.25rem 1.5rem; }
    .ar-title { font-size:1.2rem; }
    .ar-actions { flex-direction:column-reverse; }
    .ar-actions .btn { width:100%; }
}
</style>
</head>
<div class="container-fluid">
    <div class="ar-wrap">
        <div class="fods-card ar-card">
            <div class="ar-badge">
                <div class="ar-badge-inner tone-amber"><i class="ti ti-lock"></i></div>
            </div>

            <span class="ar-code">Access restricted</span>
            <h1 class="ar-title">You don't have access to <?= $module !== '' ? esc($module) : 'this page' ?></h1>
            <p class="ar-text">Your account's role doesn't include permission for this module. If you need it for your work, ask your system administrator to update your role.</p>

            <div class="ar-details">
                <div class="ar-row">
                    <span class="ar-row-label"><i class="ti ti-user"></i>Signed in as</span>
                    <span class="ar-row-value">@<?= esc($cuser) ?></span>
                </div>
                <div class="ar-row">
                    <span class="ar-row-label"><i class="ti ti-shield"></i>Your role</span>
                    <span class="ar-row-value">
                        <?php if (!empty($role_name)): ?>
                            <span class="fods-chip tone-teal"><?= esc($role_name) ?></span>
                        <?php else: ?>
                            <span class="fods-chip tone-amber">No role assigned</span>
                        <?php endif; ?>
                    </span>
                </div>
                <?php if ($module !== ''): ?>
                <div class="ar-row">
                    <span class="ar-row-label"><i class="ti ti-apps"></i>Requested module</span>
                    <span class="ar-row-value"><?= esc($module) ?></span>
                </div>
                <?php endif; ?>
            </div>

            <div class="ar-actions">
                <button type="button" class="btn btn-fods-ghost" id="arBack"><i class="ti ti-arrow-left me-1"></i>Go back</button>
                <a href="<?=site_url();?>mydashboard" class="btn btn-fods"><i class="ti ti-home me-1"></i>Back to dashboard</a>
            </div>

            <div class="ar-help">Think this is a mistake? Your administrator can check your role under Administration → User Management.</div>
        </div>
    </div>
</div>
<script>
    (function () {
        document.getElementById('arBack').addEventListener('click', function () {
            if (document.referrer && history.length > 1) { history.back(); }
            else { window.location.href = '<?=site_url();?>mydashboard'; }
        });
    })();
</script>
<?php
    echo view('templates/myfooter.php');
?>
