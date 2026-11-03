<?php
// One request: where it stands, its details and signatures. Seen by the
// requester ($isOwner), the "Noted by" signatory ($canApprove while it waits
// for them) and, once submitted, HR ($isHr; $canStatus to decide on it, $canDoc
// to prepare the Special Order - each a permission of the viewer's role).
// The requester and HR share the conversation ($canChat); the signatory sees
// the request's log instead.
use Config\Sorf\RequestForms;

$hd = $record['hd'];
$lto = $record['lto'];
$fields = $form['fields'] ?? [];
$isLto = ($form['layout'] ?? '') === 'lto';
$status = $hd['status'];
$withHr = in_array($status, RequestForms::HR_STATUSES, true);
$print = sorf_url('myrequest?meaction=PRINT&recid=' . $hd['recid']);
$back = $isHr ? ['myhrrequest', 'All Requests'] : ($isOwner ? ['myrequest', 'My Requests'] : ($isNoted ? ['myapproval', 'For My Approval'] : ['', 'Home']));

// The progress timeline: [state, title, detail, when]
// state: done | current | stopped | '' (not reached); when: date it happened ('' = not yet)
$stamp = fn ($at) => $at ? date('M j, Y · g:i A', strtotime($at)) : '';
$decided = in_array($status, [RequestForms::STATUS_APPROVED, RequestForms::STATUS_DENIED], true);
$steps = [
    [$hd['requester_sign'] !== '' ? 'done' : 'current', 'Signed by requester', $hd['full_name'], $stamp($hd['requester_signed_at'])],
    // one step: the signatory's signature notes the request and endorses it to HR
    [
        $withHr ? 'done' : ($status === RequestForms::STATUS_RETURNED ? 'stopped' : ($status === RequestForms::STATUS_FOR_APPROVAL ? 'current' : '')),
        $status === RequestForms::STATUS_RETURNED ? 'Returned for revision' : 'Noted & Endorsed to HR',
        $hd['noted_by'] !== '' ? $hd['noted_by'] : 'No signatory yet',
        $withHr ? $stamp($hd['submitted_at'] ?: $hd['noted_signed_at']) : ($status === RequestForms::STATUS_RETURNED ? $stamp($hd['updated_at']) : ''),
    ],
    [
        $status === RequestForms::STATUS_APPROVED ? 'done' : ($status === RequestForms::STATUS_DENIED ? 'stopped' : ($withHr ? 'current' : '')),
        $decided ? $status : 'HR decision',
        $withHr && !$decided ? $status : ($decided ? 'by HR' : 'Approved or denied by HR'),
        $decided ? $stamp($hd['updated_at']) : '',
    ],
];
// how far the line between the steps is filled: up to the last step reached
$reached = 0;
foreach ($steps as $i => [$state]) {
    if ($state !== '') {
        $reached = $i;
    }
}
$signs = [
    ['Requested by', 'requester', $hd['full_name'], $hd['requester_sign'], $hd['requester_signed_at']],
    [rtrim($form['noted_label'] ?? 'Noted by', ':'), 'noted', $hd['noted_by'], $hd['noted_sign'], $hd['noted_signed_at']],
];

echo view('sorf/templates/sorf-header', ['pageTitle' => $hd['request_no'], 'nav' => $isHr ? 'hr' : ($isOwner ? 'list' : ($isNoted ? 'approval' : ''))]);
?>
<div class="drf-wrap">
    <a class="drf-back" href="<?= site_url($back[0]); ?>"><i class="ti ti-arrow-left"></i> <?= $back[1] ?></a>

    <div class="drf-page-head">
        <div>
            <h1 class="drf-page-title d-flex flex-wrap align-items-center gap-2">
                <?= esc($hd['request_no']) ?>
                <span class="drf-chip <?= drf_status_tone($status) ?>" id="requestStatus"><span class="drf-dot"></span><span data-status-text><?= esc($status) ?></span></span>
            </h1>
            <p class="drf-page-sub"><?= esc($hd['document_type']) ?></p>
        </div>
        <div class="drf-page-actions">
            <?php if ($canEdit): ?>
                <a class="btn btn-drf-ghost px-3 py-2" href="<?= sorf_url('myrequest?meaction=FORM&recid=' . $hd['recid']); ?>"><i class="ti ti-pencil me-1"></i> Edit</a>
            <?php endif; ?>
            <a class="btn btn-drf-ghost px-3 py-2" href="<?= $print ?>" data-drf-print="<?= esc($hd['request_no'], 'attr') ?>"><i class="ti ti-printer me-1"></i> Print Form (PDF)</a>
        </div>
    </div>

    <ol class="drf-steps" aria-label="Progress of this request" style="--drf-steps: <?= count($steps) ?>; --drf-progress: <?= round($reached / (count($steps) - 1), 3) ?>">
        <?php foreach ($steps as $n => [$state, $title, $detail, $when]): ?>
        <li class="drf-step <?= $state !== '' ? 'is-' . $state : '' ?>" <?= $state === 'current' ? 'aria-current="step"' : '' ?>>
            <span class="drf-step-mark" aria-hidden="true"><?= $state === 'done' ? '<i class="ti ti-check"></i>' : ($state === 'stopped' ? '<i class="ti ti-x"></i>' : $n + 1) ?></span>
            <span class="drf-step-body">
                <span class="drf-step-title"><?= esc($title) ?></span>
                <span class="drf-step-sub"><?= esc($detail) ?></span>
                <?php if ($when !== ''): ?>
                    <span class="drf-step-when"><i class="ti ti-clock"></i> <?= esc($when) ?></span>
                <?php elseif ($state === 'current'): ?>
                    <span class="drf-step-when is-now">In progress</span>
                <?php endif; ?>
            </span>
        </li>
        <?php endforeach; ?>
    </ol>

    <?php // what the requester should do, or wait for, next ?>
    <?php if ($isOwner && $status === RequestForms::STATUS_RETURNED): ?>
        <div class="drf-next is-warn">
            <div>
                <div class="drf-next-title"><?= esc($hd['noted_by']) ?> returned this request</div>
                <div class="drf-next-text"><?= esc($hd['noted_remarks']) ?></div>
            </div>
            <a class="btn btn-drf px-4 py-2" href="<?= sorf_url('myrequest?meaction=FORM&recid=' . $hd['recid']); ?>"><i class="ti ti-pencil me-1"></i> Edit and Send Again</a>
        </div>
    <?php elseif ($isOwner && $status === RequestForms::STATUS_FOR_APPROVAL): ?>
        <div class="drf-next">
            <div>
                <div class="drf-next-title">Waiting for <?= esc($hd['noted_by']) ?></div>
                <div class="drf-next-text">They will see this request under "For My Approval". Once they sign and approve it, it is submitted to HR.</div>
            </div>
        </div>
    <?php endif; ?>

    <?php // the Special Order: HR prepares it in the document editor; others see it once shared ?>
    <?php if ($canDoc || $doc !== null): ?>
        <?php $docReleased = $doc !== null && $doc['doc_status'] === \Config\Sorf\DocumentTemplates::STATUS_RELEASED; ?>
        <div class="drf-next<?= $docReleased ? ' is-ok' : '' ?>">
            <div>
                <?php // "released" = shared with the requester as a draft (there is no final release yet) ?>
                <div class="drf-next-title"><?= $docReleased ? 'Draft Special Order shared' : ($doc === null ? 'Special Order not started' : 'Special Order in draft') ?></div>
                <div class="drf-next-text"><?php if ($docReleased): ?>Shared by HR on <?= date('M j, Y g:i A', strtotime($doc['released_at'])) ?> as a draft, not the final Special Order. <?= $canDoc ? 'The requester can view, print and download it.' : 'You can view, print or download it to check the details.' ?><?php elseif ($doc === null): ?>Prepare the official document for this request from its template.<?php else: ?>Not visible to the requester until you share it.<?php endif; ?></div>
            </div>
            <?php if ($docReleased): ?>
                <a class="btn btn-drf-ghost px-3 py-2" href="<?= sorf_url('myrequest?meaction=DOC-WORD&recid=' . $hd['recid']); ?>"><i class="ti ti-download me-1"></i> Word</a>
                <a class="btn btn-drf px-3 py-2" href="<?= sorf_url('myrequest?meaction=DOC&frame=1&recid=' . $hd['recid']); ?>" data-drf-print="<?= esc($hd['request_no'], 'attr') ?>" data-drf-download="<?= esc(sorf_url('myrequest?meaction=DOC-WORD&recid=' . $hd['recid']), 'attr') ?>"><i class="ti ti-file-text me-1"></i> View / Print</a>
            <?php endif; ?>
            <?php if ($canDoc): ?>
                <a class="btn <?= $docReleased ? 'btn-drf-ghost' : 'btn-drf' ?> px-3 py-2" href="<?= sorf_url('myhrdoc?recid=' . $hd['recid']); ?>"><i class="ti ti-edit me-1"></i> <?= $doc === null ? 'Prepare Special Order' : 'Open Document Editor' ?></a>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <?php
    // The "Noted by" signatory gets "Your approval" beside the request details
    // (pinned while the details scroll), and the request log under the details.
    // Everyone else: details on the left, conversation / log on the right.
    ?>
    <?php if ($canApprove): ?>
    <?php ob_start(); ?>
            <!-- "Noted by" signatory: sign and approve, or return -->
            <div class="drf-card drf-approval">
                <div class="drf-card-head">
                    <div>
                        <h2 class="drf-card-title">Your approval</h2>
                        <div class="drf-sub">You are named under "<?= esc($signs[1][0]) ?>".</div>
                    </div>
                </div>
                <form class="drf-card-body" id="approvalForm" novalidate>
                    <input type="hidden" name="recid" value="<?= $hd['recid'] ?>">
                    <p class="drf-hint">Check the request details, then return it to <?= esc($hd['full_name']) ?> with what needs to change, or sign to approve it. Approving submits the request to HR.</p>

                    <label class="form-label">Your signature</label>
                    <?= view('sorf/request/signature-pad', ['current' => '']) ?>

                    <label class="form-label mt-3" for="noted_remarks">Remarks</label>
                    <textarea class="form-control" name="noted_remarks" id="noted_remarks" rows="2" maxlength="500" placeholder="Optional when approving. Required when returning the request."></textarea>

                    <div class="drf-form-foot">
                        <button type="button" class="btn btn-drf-ghost px-4 py-2" data-approval="return"><i class="ti ti-arrow-back-up me-1"></i> Return to Requester</button>
                        <button type="button" class="btn btn-drf px-4 py-2" data-approval="approve"><i class="ti ti-writing-sign me-1"></i> Sign, Approve and Submit to HR</button>
                    </div>
                </form>
            </div>
    <?php $approvalCard = ob_get_clean(); ?>
    <?php endif; ?>

    <?php ob_start(); ?>
            <!-- Conversation (requester and HR) / request log (signatory) -->
            <div class="drf-card drf-chat<?= $canChat ? '' : ' is-log' ?>">
                <div class="drf-card-head">
                    <div>
                        <h2 class="drf-card-title"><?= $canChat ? 'Conversation' : 'Request log' ?></h2>
                        <div class="drf-sub"><?= $canChat ? ($isHr ? 'Ask for requirements or send files to the requester.' : 'Message HR and send the requirements they ask for.') : 'What has happened to this request so far.' ?></div>
                    </div>
                </div>
                <div class="drf-chat-body" id="chatBody" aria-live="polite">
                    <div class="drf-empty" id="chatEmpty"><span class="spinner-border spinner-border-sm"></span></div>
                </div>
                <?php if ($canChat): ?>
                <form class="drf-chat-form" id="chatForm" novalidate>
                    <div class="drf-picked" id="chatPicked"></div>
                    <div class="drf-chat-row">
                        <input type="file" id="chatFiles" multiple hidden accept=".jpg,.jpeg,.png,.gif,.webp,.pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.csv,.txt,.zip">
                        <button type="button" class="drf-icon-btn" id="chatAttach" title="Attach files" aria-label="Attach files"><i class="ti ti-paperclip"></i></button>
                        <textarea class="form-control" id="chatMessage" rows="1" maxlength="2000" placeholder="Write a message..." aria-label="Message"></textarea>
                        <button type="submit" class="btn btn-drf px-3" id="chatSend" aria-label="Send"><i class="ti ti-send"></i></button>
                    </div>
                    <div class="drf-hint mt-1">Up to 5 files, 10 MB each: images, PDF, Word, Excel, PowerPoint, CSV, TXT, ZIP.</div>
                </form>
                <?php endif; ?>
            </div>
    <?php $chatCard = ob_get_clean(); ?>

    <div class="row g-4">
        <div class="col-xl-7">

            <?php if ($canStatus): ?>
            <!-- HR: status, remarks, S.O. number -->
            <div class="drf-card mb-4">
                <div class="drf-card-head"><h2 class="drf-card-title">Update this request</h2></div>
                <form class="drf-card-body" id="statusForm" novalidate>
                    <input type="hidden" name="recid" value="<?= $hd['recid'] ?>">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label" for="status">Status</label>
                            <select class="form-select" name="status" id="status">
                                <?php foreach (RequestForms::HR_STATUSES as $option): ?>
                                    <option value="<?= esc($option, 'attr') ?>" <?= $status === $option ? 'selected' : '' ?>><?= esc($option) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="so_no"><?= $isLto ? 'LTO No.' : 'S.O. No.' ?></label>
                            <input type="text" class="form-control" name="so_no" id="so_no" value="<?= esc($hd['so_no'], 'attr') ?>" maxlength="50" placeholder="Once issued">
                        </div>
                        <div class="col-12">
                            <label class="form-label" for="hr_remarks">Remarks (by HRMS)</label>
                            <textarea class="form-control" name="hr_remarks" id="hr_remarks" rows="3" maxlength="500" placeholder="Requirements needed, reason for the decision, or a note for the requester"><?= esc($hd['hr_remarks']) ?></textarea>
                            <div class="drf-hint mt-1">Printed on the form and sent to the requester. Required for "For Compliance" and "Denied".</div>
                        </div>
                    </div>
                    <div class="d-flex justify-content-end mt-3">
                        <button type="submit" class="btn btn-drf px-4 py-2" id="statusSubmit">Save Update</button>
                    </div>
                </form>
            </div>
            <?php endif; ?>

            <div class="drf-card">
                <div class="drf-card-head"><h2 class="drf-card-title">Request details</h2></div>
                <div class="drf-card-body">
                    <dl class="drf-facts">
                        <div><dt>Requested by</dt><dd><?= esc($hd['full_name']) ?></dd></div>
                        <div><dt>Division</dt><dd><?= esc($hd['division_name']) ?></dd></div>
                        <div><dt>Section</dt><dd><?= esc($hd['section_name']) ?></dd></div>
                        <div><dt>Date of request</dt><dd><?= date('F j, Y', strtotime($hd['request_date'])) ?></dd></div>
                        <div><dt>Filed</dt><dd><?= date('M j, Y g:i A', strtotime($hd['added_at'])) ?></dd></div>
                        <div><dt>Noted by</dt><dd><?= $hd['noted_by'] !== '' ? esc($hd['noted_by']) : '—' ?></dd></div>
                        <?php if ($hd['so_no'] !== ''): ?>
                            <div><dt><?= $isLto ? 'LTO No.' : 'S.O. No.' ?></dt><dd><?= esc($hd['so_no']) ?></dd></div>
                        <?php endif; ?>
                        <div class="is-wide"><dt>Subject</dt><dd><?= esc($hd['subject']) ?></dd></div>
                    </dl>

                    <?php if ($hd['noted_remarks'] !== ''): ?>
                        <div class="drf-subhead">Remarks by <?= esc($hd['noted_by']) ?></div>
                        <div class="drf-remarks"><?= esc($hd['noted_remarks']) ?></div>
                    <?php endif; ?>

                    <?php if ($hd['hr_remarks'] !== ''): ?>
                        <div class="drf-subhead">Remarks (by HRMS)</div>
                        <div class="drf-remarks"><?= esc($hd['hr_remarks']) ?></div>
                    <?php endif; ?>

                    <div class="drf-subhead"><?= $isLto ? 'Authority to travel is granted to' : 'Request lines' ?></div>
                    <div class="drf-rows drf-scroll-x">
                        <table class="drf-table">
                            <thead>
                                <tr>
                                    <?php foreach ($fields as $f): ?>
                                        <th class="<?= $f['type'] === 'money' ? 'is-num' : '' ?>"><?= esc($f['label']) ?></th>
                                    <?php endforeach; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($record['rows'] as $row): ?>
                                <tr>
                                    <?php foreach ($fields as $f): ?>
                                        <?php if ($f['type'] === 'money'): ?>
                                            <td class="is-num"><?= drf_money($row[$f['key']]) ?></td>
                                        <?php else: ?>
                                            <td class="is-wrap"><?= esc($row[$f['key']]) ?></td>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <?php if ($lto !== null): ?>
                        <div class="drf-subhead">Travel</div>
                        <dl class="drf-facts">
                            <div><dt>Destination/s</dt><dd><?= esc($lto['destination']) ?></dd></div>
                            <div><dt>Inclusive date/s of travel</dt><dd><?= esc($lto['travel_dates']) ?></dd></div>
                            <div class="is-wide"><dt>Purpose(s) of the travel</dt><dd><?= esc($lto['purpose']) ?></dd></div>
                            <div><dt>General Fund</dt><dd><?= (int) $lto['is_general_fund'] === 1 ? 'Yes' : '—' ?></dd></div>
                            <div><dt>Project Funds</dt><dd><?= $lto['project_fund'] !== '' ? esc($lto['project_fund']) : '—' ?></dd></div>
                            <div><dt>Others</dt><dd><?= $lto['other_fund'] !== '' ? esc($lto['other_fund']) : '—' ?></dd></div>
                            <?php if ($lto['remarks'] !== ''): ?>
                                <div class="is-wide"><dt>Remarks/Special instructions</dt><dd><?= esc($lto['remarks']) ?></dd></div>
                            <?php endif; ?>
                        </dl>

                        <?php if (!empty($record['expenses'])): ?>
                            <div class="drf-subhead">Travel expenses to be incurred</div>
                            <div class="drf-rows drf-scroll-x">
                                <table class="drf-table">
                                    <thead>
                                        <tr><th>Expense</th><th>General Fund</th><th>Project Funds</th><th>Others</th></tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($ltoExpenses as $group): ?>
                                            <?php foreach (($group['code'] !== '' ? [$group['code'] => $group['label']] : []) + array_map(fn ($label) => $group['label'] . ' — ' . $label, $group['items']) as $code => $label): ?>
                                                <?php if (isset($record['expenses'][$code])): ?>
                                                <tr>
                                                    <td><?= esc($label) ?></td>
                                                    <td class="is-wrap"><?= esc($record['expenses'][$code]['general_fund']) ?></td>
                                                    <td class="is-wrap"><?= esc($record['expenses'][$code]['project_fund']) ?></td>
                                                    <td class="is-wrap"><?= esc($record['expenses'][$code]['other_fund']) ?></td>
                                                </tr>
                                                <?php endif; ?>
                                            <?php endforeach; ?>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>

                    <div class="drf-subhead">Signatures</div>
                    <div class="drf-signs">
                        <?php foreach ($signs as [$label, $who, $name, $file, $signedAt]): ?>
                        <div class="drf-signed">
                            <div class="drf-signed-label"><?= esc($label) ?></div>
                            <div class="drf-signed-box">
                                <?php if ($file !== ''): ?>
                                    <img src="<?= sorf_url('myrequest?meaction=SIGN-FILE&who=' . $who . '&recid=' . $hd['recid'] . '&t=' . strtotime((string) $signedAt)) ?>" alt="Signature of <?= esc($name, 'attr') ?>">
                                <?php else: ?>
                                    <span>Not signed yet</span>
                                <?php endif; ?>
                            </div>
                            <div class="drf-signed-name"><?= $name !== '' ? esc($name) : '—' ?></div>
                            <div class="drf-sub"><?= $file !== '' && $signedAt ? 'Signed ' . date('M j, Y g:i A', strtotime($signedAt)) : 'Signature over printed name' ?></div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <?php if ($canApprove): ?>
            <div class="mt-4"><?= $chatCard ?></div>
            <?php endif; ?>
        </div>

        <div class="col-xl-5">
            <?= $canApprove ? $approvalCard : $chatCard ?>
        </div>
    </div>
</div>

<script>
    var __request_view = <?= json_encode([
        'recid' => $hd['recid'],
        'me' => $me,
        'status' => $status,
        'doc' => $docStamp,
        'canChat' => $canChat,
        'tones' => array_combine(RequestForms::STATUSES, array_map('drf_status_tone', RequestForms::STATUSES)),
    ], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
</script>
<?php echo view('sorf/templates/sorf-footer', ['scripts' => ['drf-signature.js?v=2', 'request/myrequest-view.js?v=7']]); ?>
