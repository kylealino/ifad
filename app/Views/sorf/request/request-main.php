<?php
// One list, three points of view ($mode):
//   mine     - requests you filed
//   approval - requests naming you as "Noted by", to sign and approve
//   hr       - every request submitted to HR
use Config\Sorf\RequestForms;

$views = [
    'mine' => [
        'title' => 'My Requests',
        'sub' => 'Your special order requests and where each one stands.',
        'empty' => 'You have not filed a request yet.',
        'statuses' => RequestForms::STATUSES,
        'tiles' => [
            [RequestForms::STATUS_FOR_APPROVAL, 'Waiting for approval', 'ti-writing-sign', 'tone-amber'],
            [RequestForms::STATUS_PENDING, 'With HR', 'ti-send', 'tone-slate'],
            [RequestForms::STATUS_APPROVED, 'Approved', 'ti-circle-check', 'tone-green'],
        ],
    ],
    'approval' => [
        'title' => 'For My Approval',
        'sub' => 'Requests that name you as "Noted by". Open one to return it, or to sign and approve it, which sends it to HR.',
        'empty' => 'No request names you as "Noted by" yet.',
        'statuses' => RequestForms::STATUSES,
        'tiles' => [
            [RequestForms::STATUS_FOR_APPROVAL, 'Waiting for you', 'ti-writing-sign', 'tone-amber'],
            [RequestForms::STATUS_RETURNED, 'Returned by you', 'ti-arrow-back-up', 'tone-red'],
            [RequestForms::STATUS_PENDING, 'Signed, now with HR', 'ti-send', 'tone-slate'],
        ],
    ],
    'hr' => [
        'title' => 'All Requests',
        'sub' => 'Every request signed by the requester and approved by their chief.',
        'empty' => 'No request has reached HR yet.',
        'statuses' => RequestForms::HR_STATUSES,
        'tiles' => [
            [RequestForms::STATUS_PENDING, 'Pending', 'ti-clock', 'tone-slate'],
            [RequestForms::STATUS_COMPLIANCE, 'For compliance', 'ti-file-alert', 'tone-indigo'],
            [RequestForms::STATUS_APPROVED, 'Approved', 'ti-circle-check', 'tone-green'],
        ],
    ],
];
$pov = $views[$mode];
$counts = array_count_values(array_column($rows, 'status'));
// whose name the list shows beside each request
$showRequester = $mode !== 'mine';

echo view('sorf/templates/sorf-header', ['pageTitle' => $pov['title'], 'nav' => ['mine' => 'list', 'approval' => 'approval', 'hr' => 'hr'][$mode]]);
?>
<div class="drf-wrap">
    <?php if (session()->getFlashdata('drf_notice')): ?>
        <div class="alert alert-warning py-2" role="alert"><?= esc(session()->getFlashdata('drf_notice')) ?></div>
    <?php endif; ?>

    <div class="drf-page-head">
        <div>
            <h1 class="drf-page-title"><?= esc($pov['title']) ?></h1>
            <p class="drf-page-sub"><?= esc($pov['sub']) ?></p>
        </div>
        <?php if ($mode === 'mine'): ?>
        <div class="drf-page-actions">
            <a class="btn btn-drf px-3 py-2" href="<?= sorf_url('myrequest?meaction=FORM'); ?>"><i class="ti ti-plus me-1"></i> New Request</a>
        </div>
        <?php endif; ?>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <button type="button" class="drf-stat" data-drf-filter="all">
                <span class="drf-stat-icon tone-navy"><i class="ti ti-files"></i></span>
                <span>
                    <span class="drf-stat-value d-block"><?= number_format(count($rows)) ?></span>
                    <span class="drf-stat-label">Total requests</span>
                </span>
            </button>
        </div>
        <?php foreach ($pov['tiles'] as [$status, $label, $icon, $tone]): ?>
        <div class="col-6 col-lg-3">
            <button type="button" class="drf-stat" data-drf-filter="<?= esc($status, 'attr') ?>">
                <span class="drf-stat-icon <?= $tone ?>"><i class="ti <?= $icon ?>"></i></span>
                <span>
                    <span class="drf-stat-value d-block"><?= number_format($counts[$status] ?? 0) ?></span>
                    <span class="drf-stat-label"><?= esc($label) ?></span>
                </span>
            </button>
        </div>
        <?php endforeach; ?>
    </div>

    <div class="drf-card" data-drf-list>
        <div class="drf-card-head">
            <div class="drf-filter">
                <button type="button" class="active" data-drf-filter="all">All</button>
                <?php foreach ($pov['statuses'] as $status): ?>
                    <button type="button" data-drf-filter="<?= esc($status, 'attr') ?>"><?= esc($status) ?></button>
                <?php endforeach; ?>
            </div>
            <div class="drf-search">
                <i class="ti ti-search"></i>
                <input type="search" class="form-control form-control-sm" data-drf-search placeholder="Search request no., name, subject..." aria-label="Search requests">
            </div>
        </div>

        <?php if (empty($rows)): ?>
            <div class="drf-empty">
                <i class="ti ti-file-off"></i>
                <?= esc($pov['empty']) ?>
                <?php if ($mode === 'mine'): ?>
                    <div class="mt-3"><a class="btn btn-drf btn-sm px-3" href="<?= sorf_url('myrequest?meaction=FORM'); ?>">File your first request</a></div>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="drf-list-scroll">
            <table class="drf-table drf-table-stack">
                <thead>
                    <tr>
                        <th>Request No.</th>
                        <th>Date filed</th>
                        <th><?= $showRequester ? 'Requested by' : 'Noted by' ?></th>
                        <th>Document type</th>
                        <th>Subject</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($rows as $r): ?>
                    <?php $open = sorf_url('myrequest?meaction=VIEW&recid=' . $r['recid']); ?>
                    <tr data-status="<?= esc($r['status'], 'attr') ?>">
                        <td data-label="Request No.">
                            <span class="d-inline-flex align-items-center gap-2">
                                <a class="drf-reqno" href="<?= $open ?>"><?= esc($r['request_no']) ?></a>
                                <?php if ($r['unread'] > 0): ?>
                                    <span class="drf-unread" title="New messages"><?= $r['unread'] > 99 ? '99+' : $r['unread'] ?></span>
                                <?php endif; ?>
                            </span>
                        </td>
                        <td data-label="Date filed" class="text-nowrap"><?= date('M j, Y', strtotime($r['added_at'])) ?></td>
                        <?php if ($showRequester): ?>
                        <td data-label="Requested by">
                            <span>
                                <span class="d-block"><?= esc($r['full_name']) ?></span>
                                <span class="drf-sub"><?= esc($r['division_id'] . ' · ' . $r['section_id']) ?></span>
                            </span>
                        </td>
                        <?php else: ?>
                        <td data-label="Noted by"><?= $r['noted_by'] !== '' ? esc($r['noted_by']) : '—' ?></td>
                        <?php endif; ?>
                        <td data-label="Document type"><?= esc($r['document_type']) ?></td>
                        <td data-label="Subject" class="is-wrap"><?= esc($r['subject']) ?></td>
                        <td data-label="Status"><span class="drf-chip <?= drf_status_tone($r['status']) ?>"><span class="drf-dot"></span><?= esc($r['status']) ?></span></td>
                        <td>
                            <span class="d-flex justify-content-end gap-1">
                                <a class="drf-icon-btn" href="<?= $open ?>" title="Open request" aria-label="Open request"><i class="ti ti-eye"></i></a>
                                <a class="drf-icon-btn" href="<?= sorf_url('myrequest?meaction=PRINT&recid=' . $r['recid']); ?>" data-drf-print="<?= esc($r['request_no'], 'attr') ?>" title="Print request form (PDF)" aria-label="Print request form (PDF)"><i class="ti ti-printer"></i></a>
                                <?php // the Special Order: printable once HR shares it (as a draft), greyed out until then ?>
                                <?php if ($r['so_released']): ?>
                                    <a class="drf-icon-btn is-so" href="<?= sorf_url('myrequest?meaction=DOC&frame=1&recid=' . $r['recid']); ?>" data-drf-print="<?= esc($r['request_no'], 'attr') ?>" data-drf-download="<?= esc(sorf_url('myrequest?meaction=DOC-WORD&recid=' . $r['recid']), 'attr') ?>" title="Print Special Order (draft)" aria-label="Print Special Order (draft)"><i class="ti ti-file-certificate"></i></a>
                                <?php else: ?>
                                    <span class="drf-icon-btn is-muted" title="Special Order not shared yet" aria-label="Special Order not shared yet"><i class="ti ti-file-certificate"></i></span>
                                <?php endif; ?>
                            </span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            </div>
            <div class="drf-empty" data-drf-nomatch hidden><i class="ti ti-search"></i>No requests match.</div>
        <?php endif; ?>
    </div>
</div>
<?php echo view('sorf/templates/sorf-footer'); ?>
