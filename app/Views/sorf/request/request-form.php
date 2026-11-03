<?php
// New request, or ($record) changing one HR has not decided yet. The requester
// signs here; saving sends the request to the "Noted by" signatory.
// Section 03 is built by myrequest-form.js from Config\RequestForms.
$hd = $record['hd'] ?? null;
$division = $hd['division_id'] ?? $user['division'];
$section = $hd['section_id'] ?? $user['section'];
$signed = $hd !== null && $hd['requester_sign'] !== '';

echo view('sorf/templates/sorf-header', ['pageTitle' => $hd ? 'Edit ' . $hd['request_no'] : 'New Request', 'nav' => $hd ? '' : 'form']);
?>
<div class="drf-wrap">
    <a class="drf-back" href="<?= $hd ? sorf_url('myrequest?meaction=VIEW&recid=' . $hd['recid']) : sorf_url('myrequest'); ?>"><i class="ti ti-arrow-left"></i> <?= $hd ? 'Back to request' : 'My Requests' ?></a>

    <?php if ($hd): ?>
        <div class="drf-next" role="note">
            <div>
                <div class="drf-next-title">Saving sends this request for approval again</div>
                <div class="drf-next-text">A changed request is signed again by the person under "Noted by"<?= $hd['noted_sign'] !== '' ? ', so their current signature is removed' : '' ?><?= in_array($hd['status'], \Config\Sorf\RequestForms::HR_STATUSES, true) ? ', and it goes to HR again once they sign' : '' ?>.</div>
            </div>
        </div>
    <?php endif; ?>

    <?php // the form, and beside it the printed form filling in as it is typed (myrequest-form.js) ?>
    <div class="row g-4">
    <div class="col-xl-7">
    <div class="drf-card">
        <form class="drf-card-body p-4" id="requestForm" novalidate>
            <input type="hidden" name="recid" value="<?= (int) ($hd['recid'] ?? 0) ?>">

            <section class="drf-section">
                <h2 class="drf-section-title">01 — Requestor Information</h2>
                <p class="drf-section-sub">Taken from your account. Change the division or section if this request is for another office.</p>

                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label" for="full_name">Full Name</label>
                        <input type="text" class="form-control" id="full_name" value="<?= esc($hd['full_name'] ?? $user['full_name'], 'attr') ?>" readonly>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="division_id">Division <span class="drf-req">*</span></label>
                        <select class="form-select" name="division_id" id="division_id" required>
                            <option value="">Select Division</option>
                            <?php foreach ($lookups['divisions'] as $d): ?>
                                <option value="<?= esc($d['division_id'], 'attr') ?>" <?= $division === $d['division_id'] ? 'selected' : '' ?>><?= esc($d['division_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="section_id">Section <span class="drf-req">*</span></label>
                        <select class="form-select" name="section_id" id="section_id" data-drf-sections="#division_id" required>
                            <option value="">Select Section</option>
                            <?php foreach ($lookups['sections'] as $s): ?>
                                <option value="<?= esc($s['section_id'], 'attr') ?>" data-division="<?= esc($s['division_id'], 'attr') ?>" <?= $division === $s['division_id'] && $section === $s['section_id'] ? 'selected' : '' ?>><?= esc($s['section_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </section>

            <section class="drf-section">
                <h2 class="drf-section-title">02 — Document Type</h2>
                <p class="drf-section-sub"><?= $hd ? 'The document type of a filed request cannot be changed.' : 'Select the type of document you are requesting.' ?></p>

                <label class="form-label" for="document_id">Document Type <span class="drf-req">*</span></label>
                <select class="form-select" name="document_id" id="document_id" required <?= $hd ? 'disabled' : '' ?>>
                    <option value="">Select Document Type</option>
                    <?php foreach ($lookups['documents'] as $doc): ?>
                        <?php if (isset($forms[$doc['document_id']])): ?>
                            <option value="<?= esc($doc['document_id'], 'attr') ?>" <?= ($hd['document_id'] ?? '') === $doc['document_id'] ? 'selected' : '' ?>><?= esc($doc['document_type']) ?></option>
                        <?php endif; ?>
                    <?php endforeach; ?>
                    <?php // keep showing a type that was turned off after this request was filed ?>
                    <?php if ($hd && !in_array($hd['document_id'], array_column($lookups['documents'], 'document_id'), true)): ?>
                        <option value="<?= esc($hd['document_id'], 'attr') ?>" selected><?= esc($hd['document_type']) ?></option>
                    <?php endif; ?>
                </select>
            </section>

            <section class="drf-section">
                <h2 class="drf-section-title">03 — Request Details</h2>
                <p class="drf-section-sub">These details are printed on the request form.</p>

                <div id="requestDetails">
                    <p class="drf-hint mb-0">Select a document type above to enter the required information.</p>
                </div>
            </section>

            <section class="drf-section">
                <h2 class="drf-section-title">04 — Sign and Submit</h2>
                <p class="drf-section-sub">Your signature goes over "Requested by" on the printed form. The request is then sent to the person you named under "Noted by" to sign. Once they approve it, it is submitted to HR.</p>

                <label class="form-label">Your signature <span class="drf-req">*</span></label>
                <?= view('sorf/request/signature-pad', ['current' => $signed ? sorf_url('myrequest?meaction=SIGN-FILE&who=requester&recid=' . $hd['recid']) : '']) ?>
            </section>

            <div class="drf-form-foot">
                <a class="btn btn-drf-ghost px-4 py-2" href="<?= $hd ? sorf_url('myrequest?meaction=VIEW&recid=' . $hd['recid']) : sorf_url('myrequest'); ?>">Cancel</a>
                <button type="submit" class="btn btn-drf px-4 py-2" id="requestSubmit"><?= $hd ? 'Save and Send for Approval' : 'Submit for Approval' ?></button>
            </div>
        </form>
    </div>
    </div>

    <!-- Live preview: the request form PDF of what is typed so far (nothing is saved) -->
    <aside class="col-xl-5" aria-label="Live preview">
        <div class="drf-card drf-preview" id="previewPanel">
            <div class="drf-card-head">
                <div>
                    <h2 class="drf-card-title">Live preview</h2>
                    <div class="drf-sub">The request form as it will print. It follows what you type.</div>
                </div>
                <span class="drf-preview-state" id="previewState" aria-live="polite"></span>
            </div>
            <div class="drf-preview-body">
                <?php // PDF.js draws the pages here (no browser PDF viewer, so nothing flashes) ?>
                <div class="drf-preview-pages" id="previewPages" role="img" aria-label="Request form preview"></div>
                <div class="drf-empty drf-preview-note" id="previewEmpty">
                    <i class="ti ti-file-text"></i>
                    Select a document type to see its form here.
                </div>
            </div>
            <div class="drf-preview-foot">
                <span class="drf-hint">Request No. is given when you submit.</span>
                <a class="drf-preview-full" id="previewFull" href="#" target="_blank" rel="noopener" hidden><i class="ti ti-arrows-maximize me-1"></i>Full size</a>
            </div>
        </div>
    </aside>
    </div>
</div>

<datalist id="employeeList">
    <?php foreach ($lookups['employees'] as $e): ?>
        <option value="<?= esc($e['full_name'], 'attr') ?>"><?= esc($e['position']) ?></option>
    <?php endforeach; ?>
</datalist>

<?php // registered users who can be named under "Noted by" ?>
<datalist id="approverList">
    <?php foreach ($lookups['approvers'] as $a): ?>
        <option value="<?= esc($a['full_name'], 'attr') ?>"><?= esc($a['position']) ?></option>
    <?php endforeach; ?>
</datalist>

<script>
    var __request_form = <?= json_encode([
        'forms' => $forms,
        'ltoExpenses' => $ltoExpenses,
        'employees' => $lookups['employees'],
        'approvers' => count($lookups['approvers']),
        'record' => $record,
        'today' => date('Y-m-d'),
        'pdfWorker' => base_url('assets/libs/pdfjs/pdf.worker.min.js'),
    ], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
</script>
<?php // PDF.js 3.11.174 (Mozilla, Apache-2.0), kept in the project so the preview works offline ?>
<script src="<?= base_url('assets/libs/pdfjs/pdf.min.js') ?>"></script>
<?php echo view('sorf/templates/sorf-footer', ['scripts' => ['drf-signature.js?v=2', 'request/myrequest-form.js?v=5']]); ?>
