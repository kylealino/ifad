<?php
use Config\AccessCodes;

if (!function_exists('db_money')) {
    /** Compact peso amount for tiles: ₱1.60B, ₱566.4M, ₱12.3K */
    function db_money($n): string
    {
        $n = (float) $n;
        $abs = abs($n);
        if ($abs >= 1e9) { return '₱' . number_format($n / 1e9, 2) . 'B'; }
        if ($abs >= 1e6) { return '₱' . number_format($n / 1e6, 1) . 'M'; }
        if ($abs >= 1e3) { return '₱' . number_format($n / 1e3, 1) . 'K'; }
        return '₱' . number_format($n, 2);
    }
}
if (!function_exists('db_pct')) {
    function db_pct($part, $whole): int
    {
        return $whole > 0 ? (int) round($part / $whole * 100) : 0;
    }
}
if (!function_exists('db_date')) {
    function db_date($d): string
    {
        return (!empty($d) && substr((string) $d, 0, 4) !== '0000') ? date('M d, Y', strtotime($d)) : '—';
    }
}
if (!function_exists('db_ago')) {
    function db_ago($d): string
    {
        if (empty($d)) { return ''; }
        $days = (int) floor((time() - strtotime($d)) / 86400);
        if ($days <= 0) { return 'today'; }
        if ($days === 1) { return 'yesterday'; }
        if ($days < 30) { return "{$days} days ago"; }
        if ($days < 365) { return floor($days / 30) . ' mo ago'; }
        return floor($days / 365) . ' yr ago';
    }
}

$s = $summary;
$stages = [
    [
        'key' => 'obl', 'step' => 1, 'title' => 'Obligation', 'tone' => 'tone-teal', 'icon' => 'ti-file-invoice',
        'count' => $s['obl_n'], 'noun' => 'ORS / BURS', 'amount' => $s['obl_amt'],
        'meta' => number_format($s['ors_n']) . ' ORS · ' . number_format($s['burs_n']) . ' BURS · ' . number_format($s['obl_payees']) . ' payees',
        'moved' => null,
    ],
    [
        'key' => 'dv', 'step' => 2, 'title' => 'Disbursement', 'tone' => 'tone-indigo', 'icon' => 'ti-file-dollar',
        'count' => $s['dv_n'], 'noun' => 'vouchers', 'amount' => $s['dv_amt'],
        'meta' => 'Net of deductions',
        'moved' => [$s['reached_dv'], $s['obl_n'], 'of obligations reached a DV'],
    ],
    [
        'key' => 'pay', 'step' => 3, 'title' => 'Payment', 'tone' => 'tone-amber', 'icon' => 'ti-building-bank',
        'count' => $s['pay_n'], 'noun' => 'payments', 'amount' => $s['pay_amt'],
        'meta' => number_format($s['lddap_n']) . ' LDDAP-ADA · ' . number_format($s['ada_n']) . ' ADA',
        'moved' => [$s['reached_pay'], $s['reached_dv'], 'of disbursed obligations paid'],
    ],
    [
        'key' => 'rci', 'step' => 4, 'title' => 'RCI', 'tone' => 'tone-green', 'icon' => 'ti-receipt-2',
        'count' => $s['rci_n'], 'noun' => 'entries', 'amount' => $s['rci_amt'],
        'meta' => number_format($s['rci_reports']) . ' report' . ($s['rci_reports'] == 1 ? '' : 's'),
        'moved' => [$s['reached_rci'], $s['reached_pay'], 'of paid obligations reported'],
    ],
];

$queues = [
    ['title' => 'Awaiting disbursement', 'desc' => 'Obligations with no DV yet', 'data' => $s['wait_dv'], 'tone' => 'tone-indigo', 'icon' => 'ti-file-dollar',
     'link' => fods_has_access(AccessCodes::DISBURSEMENT_VIEW) ? ['Open Disbursement', site_url('mydisbursement?meaction=MAIN')] : null],
    ['title' => 'Awaiting payment', 'desc' => 'DVs not yet in an LDDAP-ADA or ADA', 'data' => $s['wait_pay'], 'tone' => 'tone-amber', 'icon' => 'ti-building-bank',
     'link' => fods_has_access(AccessCodes::LDDAPADA_VIEW) ? ['Open LDDAP-ADA', site_url('mylddapada?meaction=MAIN')] : null],
    ['title' => 'Awaiting RCI', 'desc' => 'LDDAP-ADA payments not yet reported', 'data' => $s['wait_rci'], 'tone' => 'tone-green', 'icon' => 'ti-receipt-2',
     'link' => fods_has_access(AccessCodes::RCI_VIEW) ? ['Open RCI', site_url('myrci?meaction=MAIN')] : null],
];

$stageMeta = [
    'obligation'   => ['tone-teal', 'ti-file-invoice', 'Obligation'],
    'disbursement' => ['tone-indigo', 'ti-file-dollar', 'Disbursement'],
    'payment'      => ['tone-amber', 'ti-building-bank', 'Payment'],
    'rci'          => ['tone-green', 'ti-receipt-2', 'RCI'],
];


echo view('templates/myheader.php');
?>
<head>
<style>
/* Pipeline */
.db-pipeline { display:grid; grid-template-columns:repeat(4, minmax(0,1fr)); gap:1rem; margin-bottom:1.5rem; }
.db-stage { position:relative; padding:1.1rem 1.2rem 1rem; display:flex; flex-direction:column; gap:.2rem; }
.db-stage:not(:last-child)::after {
  content:"\203A"; font-weight:700; line-height:1; position:absolute; right:-.95rem; top:50%; transform:translateY(-50%);
  width:1.5rem; height:1.5rem; border-radius:50%; display:grid; place-items:center; z-index:2;
  background:#fff; border:1px solid var(--fods-border); color:var(--fods-muted); font-size:1.1rem; padding-bottom:2px;
}
.db-stage-top { display:flex; align-items:center; gap:.65rem; margin-bottom:.6rem; }
.db-stage-icon { width:38px; height:38px; border-radius:11px; display:grid; place-items:center; font-size:1.2rem; flex-shrink:0; }
.db-stage-step { font-size:.68rem; font-weight:600; letter-spacing:.08em; text-transform:uppercase; color:var(--fods-muted); }
.db-stage-title { font-weight:700; color:var(--fods-text); line-height:1.1; }
.db-stage-count { font-size:1.65rem; font-weight:700; color:var(--fods-text); line-height:1.1; letter-spacing:-.01em; }
.db-stage-count small { font-size:.8rem; font-weight:500; color:var(--fods-muted); margin-left:.25rem; }
.db-stage-amount { font-weight:600; color:var(--fods-text); }
.db-stage-meta { font-size:.76rem; color:var(--fods-muted); }
.db-moved { margin-top:.75rem; padding-top:.7rem; border-top:1px dashed var(--fods-border); font-size:.75rem; color:var(--fods-muted); }
.db-moved-bar { height:6px; border-radius:6px; background:#eef1f5; overflow:hidden; margin-bottom:.35rem; }
.db-moved-bar span { display:block; height:100%; border-radius:6px; min-width:3px; }
.db-moved b { color:var(--fods-text); }

/* Waiting queues */
.db-queue { display:flex; gap:.9rem; align-items:flex-start; padding:1rem 1.15rem; height:100%; }
.db-queue-n { font-size:1.35rem; font-weight:700; color:var(--fods-text); line-height:1.1; }
.db-queue-title { font-weight:600; color:var(--fods-text); font-size:.9rem; }
.db-queue-desc { font-size:.75rem; color:var(--fods-muted); }
.db-queue-foot { display:flex; flex-wrap:wrap; gap:.35rem .8rem; align-items:center; margin-top:.5rem; font-size:.76rem; color:var(--fods-muted); }
.db-queue-foot a { font-weight:600; color:var(--fods-teal); text-decoration:none; }

/* Activity */
.db-activity { list-style:none; margin:0; padding:0; }
.db-activity li { display:flex; gap:.75rem; padding:.7rem 0; border-bottom:1px solid #f1f4f8; }
.db-activity li:last-child { border-bottom:0; }
.db-activity .ic { width:34px; height:34px; border-radius:10px; display:grid; place-items:center; flex-shrink:0; font-size:1rem; }
.db-activity .ref { font-weight:600; color:var(--fods-text); font-size:.84rem; overflow-wrap:anywhere; }
.db-activity .who { font-size:.76rem; color:var(--fods-muted); overflow-wrap:anywhere; }
.db-activity .amt { font-weight:600; font-size:.82rem; color:var(--fods-text); white-space:nowrap; }
.db-activity .when { font-size:.72rem; color:#94a3b8; white-space:nowrap; }

/* Payee table */
.db-steps { display:flex; align-items:center; gap:0; }
.db-step { width:26px; height:26px; border-radius:50%; display:grid; place-items:center; font-size:.68rem; font-weight:700; background:#eef1f5; color:#94a3b8; flex-shrink:0; }
.db-step-line { width:14px; height:2px; background:#e5e9f0; }
.db-step.on-obl { background:var(--fods-teal-soft); color:var(--fods-teal); }
.db-step.on-dv { background:#eceefe; color:#4338ca; }
.db-step.on-pay { background:#fef4e2; color:#b45309; }
.db-step.on-rci { background:#e7f6ee; color:#15803d; }
#payeeTable tbody tr { cursor:pointer; }
.db-amt { white-space:nowrap; font-variant-numeric:tabular-nums; }

/* Payee flow panel */
.db-panel { width:min(640px, 100vw) !important; border-left:0; }
.db-panel .offcanvas-header { border-bottom:1px solid var(--fods-border); padding:1.1rem 1.4rem; }
.db-panel .offcanvas-body { background:var(--fods-bg); padding:1rem 1.4rem 1.5rem; }
.db-flow { background:#fff; border:1px solid var(--fods-border); border-radius:12px; margin-bottom:.75rem; }
.db-flow-head { display:flex; justify-content:space-between; gap:.75rem; padding:.8rem 1rem; border-bottom:1px solid #f1f4f8; }
.db-flow-ref { font-weight:600; color:var(--fods-text); font-size:.86rem; overflow-wrap:anywhere; }
.db-flow-sub { font-size:.74rem; color:var(--fods-muted); }
.db-flow-body { padding:.65rem 1rem .8rem; }
.db-chain { display:grid; grid-template-columns:repeat(3, minmax(0,1fr)); gap:.5rem; }
.db-chain + .db-chain { margin-top:.5rem; padding-top:.5rem; border-top:1px dashed var(--fods-border); }
.db-link { border-radius:9px; padding:.5rem .6rem; font-size:.74rem; line-height:1.35; min-width:0; }
.db-link .lbl { font-size:.64rem; font-weight:600; text-transform:uppercase; letter-spacing:.06em; opacity:.8; }
.db-link .no { font-weight:600; overflow-wrap:anywhere; }
.db-link.empty { background:#f8fafc; color:#94a3b8; border:1px dashed #e2e8f0; }
.db-waiting { font-size:.78rem; color:var(--fods-muted); display:flex; align-items:center; gap:.4rem; }

@media (max-width: 1199.98px) {
  .db-pipeline { grid-template-columns:repeat(2, minmax(0,1fr)); }
  .db-stage:nth-child(2)::after { display:none; }
}
@media (max-width: 575.98px) {
  .db-pipeline { grid-template-columns:1fr; }
  .db-stage:not(:last-child)::after { display:none; }
  .db-chain { grid-template-columns:1fr; }
}
</style>
</head>
<div class="container-fluid">
    <input type="hidden" id="__siteurl" data-mesiteurl="<?=site_url();?>" />

    <!-- Header -->
    <div class="fods-page-head">
        <div>
            <h1 class="fods-page-title">Payee Transaction Flow</h1>
            <p class="fods-page-sub">Every payee from obligation (ORS / BURS) to disbursement, payment (LDDAP-ADA / ADA) and RCI.</p>
        </div>
        <form method="get" action="<?=site_url('mydashboard');?>" class="fods-page-actions d-flex gap-2 align-items-center">
            <label for="yearFilter" class="small text-muted text-nowrap mb-0">Obligation year</label>
            <select id="yearFilter" name="year" class="form-select form-select-sm" style="min-width:140px;border-radius:10px;" onchange="this.form.submit()">
                <option value="">All years</option>
                <?php foreach ($years as $y): ?>
                    <option value="<?= $y ?>" <?= $year === $y ? 'selected' : '' ?>><?= $y ?></option>
                <?php endforeach; ?>
            </select>
        </form>
    </div>

    <!-- Pipeline -->
    <div class="db-pipeline">
        <?php foreach ($stages as $st): ?>
        <div class="fods-card db-stage">
            <div class="db-stage-top">
                <div class="db-stage-icon <?= $st['tone'] ?>"><i class="ti <?= $st['icon'] ?>"></i></div>
                <div>
                    <div class="db-stage-step">Step <?= $st['step'] ?></div>
                    <div class="db-stage-title"><?= $st['title'] ?></div>
                </div>
            </div>
            <div class="db-stage-count"><?= number_format($st['count']) ?><small><?= $st['noun'] ?></small></div>
            <div class="db-stage-amount" title="₱<?= number_format($st['amount'], 2) ?>"><?= db_money($st['amount']) ?></div>
            <div class="db-stage-meta"><?= $st['meta'] ?></div>
            <?php if ($st['moved']): [$part, $whole, $label] = $st['moved']; $pct = db_pct($part, $whole); ?>
                <div class="db-moved">
                    <div class="db-moved-bar"><span class="<?= $st['tone'] ?>" style="width:<?= max($pct, $part > 0 ? 1 : 0) ?>%;background:currentColor;"></span></div>
                    <b><?= $pct ?>%</b> <?= $label ?> <span class="text-nowrap">(<?= number_format($part) ?> of <?= number_format($whole) ?>)</span>
                </div>
            <?php else: ?>
                <div class="db-moved"><b><?= number_format($s['obl_payees']) ?></b> payees started here</div>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Waiting queues -->
    <div class="row g-3 mb-4">
        <?php foreach ($queues as $q): $d = $q['data']; ?>
        <div class="col-md-4">
            <div class="fods-card db-queue">
                <div class="db-stage-icon <?= $q['tone'] ?>"><i class="ti <?= $q['icon'] ?>"></i></div>
                <div class="flex-grow-1" style="min-width:0">
                    <div class="d-flex justify-content-between align-items-start gap-2">
                        <div>
                            <div class="db-queue-title"><?= $q['title'] ?></div>
                            <div class="db-queue-desc"><?= $q['desc'] ?></div>
                        </div>
                        <div class="db-queue-n"><?= number_format($d['n']) ?></div>
                    </div>
                    <div class="db-queue-foot">
                        <span title="₱<?= number_format($d['amt'], 2) ?>"><i class="ti ti-coin me-1"></i><?= db_money($d['amt']) ?></span>
                        <?php if (!empty($d['oldest'])): ?>
                            <span><i class="ti ti-clock me-1"></i>oldest <?= db_date($d['oldest']) ?></span>
                        <?php endif; ?>
                        <?php if ($q['link']): ?>
                            <a href="<?= $q['link'][1] ?>" class="ms-auto"><?= $q['link'][0] ?> <i class="ti ti-arrow-right"></i></a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Chart + activity -->
    <div class="row g-3 mb-4">
        <div class="col-xl-8">
            <div class="fods-card h-100">
                <div class="fods-card-head">
                    <div>
                        <h6 class="fods-card-title">Monthly amounts</h6>
                        <div class="db-queue-desc"><?= $year ? "January – December {$year}" : 'Last 12 months' ?> · obligations on the left axis, disbursements and payments on the right</div>
                    </div>
                </div>
                <div class="fods-card-body pt-2">
                    <div id="monthlyChart" style="min-height:320px;"></div>
                </div>
            </div>
        </div>
        <div class="col-xl-4">
            <div class="fods-card h-100">
                <div class="fods-card-head">
                    <h6 class="fods-card-title">Recent activity</h6>
                </div>
                <div class="fods-card-body pt-1">
                    <?php if (empty($recent)): ?>
                        <p class="text-muted small mb-0">No activity yet.</p>
                    <?php else: ?>
                    <ul class="db-activity">
                        <?php foreach ($recent as $e): [$tone, $icon, $label] = $stageMeta[$e['stage']]; ?>
                        <li>
                            <div class="ic <?= $tone ?>"><i class="ti <?= $icon ?>"></i></div>
                            <div class="flex-grow-1" style="min-width:0">
                                <div class="ref"><?= esc($e['ref']) ?></div>
                                <div class="who"><?= esc($e['payee_name']) ?></div>
                            </div>
                            <div class="text-end">
                                <div class="amt"><?= db_money($e['amount']) ?></div>
                                <div class="when" title="<?= esc(db_date($e['at'])) ?>"><?= db_ago($e['at']) ?></div>
                            </div>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Payees -->
    <div class="fods-card mb-3">
        <div class="fods-card-head">
            <div>
                <h6 class="fods-card-title">Payees <span class="text-muted fw-normal">(<?= number_format(count($payees)) ?>)</span></h6>
                <div class="db-queue-desc">Click a payee to follow each of their transactions.</div>
            </div>
            <div class="d-flex flex-wrap gap-2 align-items-center">
                <div class="fods-filter" id="payeeFilter">
                    <button type="button" class="active" data-filter="all">All</button>
                    <button type="button" data-filter="dv">Reached DV</button>
                    <button type="button" data-filter="pay">Paid</button>
                    <button type="button" data-filter="rci">In RCI</button>
                </div>
                <div class="fods-search">
                    <i class="ti ti-search"></i>
                    <input type="search" class="form-control form-control-sm" id="payeeSearch" placeholder="Search payee...">
                </div>
            </div>
        </div>
        <div class="fods-card-body fods-dt">
            <table id="payeeTable" class="table fods-table fods-table-stack w-100">
                <thead>
                    <tr>
                        <th>Payee</th>
                        <th class="text-end">Obligated</th>
                        <th class="text-end d-none d-md-table-cell">Disbursed</th>
                        <th class="text-end d-none d-lg-table-cell">Paid</th>
                        <th>Progress</th>
                        <th class="d-none d-lg-table-cell">Last activity</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($payees as $p):
                        $n = (int) $p['obl_n'];
                    ?>
                    <tr data-payee="<?= esc($p['payee_name'], 'attr') ?>"
                        data-dv="<?= (int) $p['reached_dv'] ?>" data-pay="<?= (int) $p['reached_pay'] ?>" data-rci="<?= (int) $p['reached_rci'] ?>">
                        <td class="cell-primary" data-order="<?= esc($p['payee_name'], 'attr') ?>">
                            <div class="d-flex align-items-center gap-2" style="min-width:0">
                                <div class="fods-avatar tone-slate"><?= esc(fods_initials($p['payee_name'])) ?></div>
                                <div style="min-width:0">
                                    <div class="fw-semibold text-dark" style="overflow-wrap:anywhere"><?= esc($p['payee_name']) ?></div>
                                    <div class="db-queue-desc"><?= number_format($n) ?> obligation<?= $n === 1 ? '' : 's' ?></div>
                                </div>
                            </div>
                        </td>
                        <td class="text-end db-amt cell-left" data-order="<?= (float) $p['obl_amt'] ?>" title="₱<?= number_format($p['obl_amt'], 2) ?>"><?= db_money($p['obl_amt']) ?></td>
                        <td class="text-end db-amt d-none d-md-table-cell" data-order="<?= (float) $p['dv_amt'] ?>"><?= $p['dv_amt'] > 0 ? db_money($p['dv_amt']) : '<span class="text-muted">—</span>' ?></td>
                        <td class="text-end db-amt d-none d-lg-table-cell" data-order="<?= (float) $p['pay_amt'] ?>"><?= $p['pay_amt'] > 0 ? db_money($p['pay_amt']) : '<span class="text-muted">—</span>' ?></td>
                        <td class="cell-right" data-order="<?= (int) $p['reached_rci'] * 1e6 + (int) $p['reached_pay'] * 1e3 + (int) $p['reached_dv'] ?>">
                            <div class="db-steps" title="<?= "{$n} obligated · {$p['reached_dv']} disbursed · {$p['reached_pay']} paid · {$p['reached_rci']} in RCI" ?>">
                                <span class="db-step on-obl">O</span><span class="db-step-line"></span>
                                <span class="db-step <?= $p['reached_dv'] > 0 ? 'on-dv' : '' ?>">D</span><span class="db-step-line"></span>
                                <span class="db-step <?= $p['reached_pay'] > 0 ? 'on-pay' : '' ?>">P</span><span class="db-step-line"></span>
                                <span class="db-step <?= $p['reached_rci'] > 0 ? 'on-rci' : '' ?>">R</span>
                            </div>
                        </td>
                        <td class="d-none d-lg-table-cell text-muted small" data-order="<?= esc((string) $p['last_activity'], 'attr') ?>"><?= db_date($p['last_activity']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<!-- Payee flow panel -->
<div class="offcanvas offcanvas-end db-panel" tabindex="-1" id="payeePanel" aria-labelledby="payeePanelTitle">
    <div class="offcanvas-header">
        <div class="d-flex align-items-center gap-3" style="min-width:0">
            <div class="fods-avatar tone-slate" id="payeePanelAvatar"></div>
            <div style="min-width:0">
                <h5 class="offcanvas-title fw-bold mb-0" id="payeePanelTitle" style="overflow-wrap:anywhere"></h5>
                <div class="db-queue-desc" id="payeePanelSub"></div>
            </div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
        <div class="fods-filter mb-3" id="flowFilter">
            <button type="button" class="active" data-filter="all">All</button>
            <button type="button" data-filter="moving">Past obligation</button>
            <button type="button" data-filter="waiting">Awaiting DV</button>
        </div>
        <div id="payeePanelBody"></div>
    </div>
</div>

<script>
    var __db_monthly = <?= json_encode($monthly, JSON_HEX_TAG | JSON_HEX_AMP) ?>;
</script>
<script src="<?=base_url('assets/libs/apexcharts/dist/apexcharts.min.js');?>"></script>
<script src="<?=base_url('assets/js/dashboard/mydashboard.js?v=1');?>"></script>
<?php
    echo view('templates/myfooter.php');
?>
