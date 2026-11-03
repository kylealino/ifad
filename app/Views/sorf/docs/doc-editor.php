<?php
// HR document editor: the Special Order of one request. HR picks the official
// template (filled in from the request), edits the page, then shares it with the requester as a draft.
use Config\Sorf\DocumentTemplates;

$released = $doc !== null && $doc['doc_status'] === DocumentTemplates::STATUS_RELEASED;
$status = $doc === null ? 'Not started' : ($released ? 'Shared (draft)' : 'Draft');
$back = sorf_url('myrequest?meaction=VIEW&recid=' . $hd['recid']);
$view = sorf_url('myrequest?meaction=DOC&frame=1&recid=' . $hd['recid']);
$word = sorf_url('myrequest?meaction=DOC-WORD&recid=' . $hd['recid']);

// a template chooser, used to start and to start over
$chooser = function (string $id) use ($templates, $suggested, $doc) {
    $current = $doc['template_code'] ?? $suggested;
    $html = '<select class="form-select" id="' . $id . '" aria-label="Template">';
    foreach ($templates as $code => $template) {
        $html .= '<option value="' . esc($code, 'attr') . '"' . ($code === $current ? ' selected' : '') . '>' . esc($template['name']) . '</option>';
    }
    return $html . '</select>';
};

echo view('sorf/templates/sorf-header', ['pageTitle' => 'Special Order · ' . $hd['request_no']]);
?>
<link rel="stylesheet" href="<?=base_url('assets/sorf/css/drf-doc.css?v=5')?>" />

<div class="drf-wrap">
    <a class="drf-back" href="<?= $back ?>"><i class="ti ti-arrow-left"></i> Back to <?= esc($hd['request_no']) ?></a>

    <div class="drf-page-head">
        <div>
            <h1 class="drf-page-title d-flex flex-wrap align-items-center gap-2">
                Special Order
                <span class="drf-chip <?= $released ? 'tone-green' : ($doc === null ? 'tone-slate' : 'tone-amber') ?>"><span class="drf-dot"></span><?= esc($status) ?></span>
            </h1>
            <p class="drf-page-sub"><?= esc($hd['request_no'] . ' · ' . $hd['document_type'] . ' · requested by ' . $hd['full_name']) ?></p>
        </div>
        <?php if ($doc !== null): ?>
        <div class="drf-page-actions">
            <a class="btn btn-drf-ghost px-3 py-2" href="<?= $view ?>" data-drf-print="<?= esc($hd['request_no'], 'attr') ?>" data-drf-download="<?= esc($word, 'attr') ?>" data-doc-preview><i class="ti ti-printer me-1"></i> Preview / Print</a>
            <?php if ($released): ?>
                <button type="button" class="btn btn-drf px-3 py-2" id="docWithdraw"><i class="ti ti-pencil me-1"></i> Withdraw to Edit</button>
            <?php else: ?>
                <button type="button" class="btn btn-drf-ghost px-3 py-2" id="docSave"><i class="ti ti-device-floppy me-1"></i> Save Draft</button>
                <button type="button" class="btn btn-drf px-3 py-2" id="docRelease"><i class="ti ti-send me-1"></i> Share Draft with Requester</button>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>

    <?php if ($doc === null): ?>
        <!-- No document yet: choose the template -->
        <div class="drf-card drf-wrap is-narrow">
            <div class="drf-card-head"><h2 class="drf-card-title">Choose the template</h2></div>
            <div class="drf-card-body">
                <p class="drf-hint">The official form is filled in with what <?= esc($hd['full_name']) ?> entered on the request. You can then edit every part of it before sharing it.</p>
                <label class="form-label" for="templateStart">Template</label>
                <?= $chooser('templateStart') ?>
                <div class="drf-form-foot">
                    <button type="button" class="btn btn-drf px-4 py-2" data-doc-start="#templateStart"><i class="ti ti-file-plus me-1"></i> Start Document</button>
                </div>
            </div>
        </div>
    <?php else: ?>
        <?php if ($released): ?>
            <div class="drf-next">
                <div>
                    <div class="drf-next-title">Shared with the requester as a draft</div>
                    <div class="drf-next-text">Shared <?= date('M j, Y g:i A', strtotime($doc['released_at'])) ?> by <?= esc($doc['released_name']) ?>. It is marked DRAFT: there is no final release yet. Withdraw it to make changes; the requester cannot open it while it is withdrawn.</div>
                </div>
            </div>
        <?php else: ?>
            <!-- Formatting toolbar -->
            <div class="drf-doc-tools" id="docTools" role="toolbar" aria-label="Formatting">
                <div class="drf-doc-group">
                    <button type="button" data-cmd="bold" title="Bold (Ctrl+B)" aria-label="Bold"><i class="ti ti-bold"></i></button>
                    <button type="button" data-cmd="italic" title="Italic (Ctrl+I)" aria-label="Italic"><i class="ti ti-italic"></i></button>
                    <button type="button" data-cmd="underline" title="Underline (Ctrl+U)" aria-label="Underline"><i class="ti ti-underline"></i></button>
                </div>
                <div class="drf-doc-group">
                    <button type="button" data-cmd="justifyLeft" title="Align left" aria-label="Align left"><i class="ti ti-align-left"></i></button>
                    <button type="button" data-cmd="justifyCenter" title="Centre" aria-label="Centre"><i class="ti ti-align-center"></i></button>
                    <button type="button" data-cmd="justifyRight" title="Align right" aria-label="Align right"><i class="ti ti-align-right"></i></button>
                    <button type="button" data-cmd="justifyFull" title="Justify" aria-label="Justify"><i class="ti ti-align-justified"></i></button>
                </div>
                <div class="drf-doc-group">
                    <button type="button" data-cmd="insertUnorderedList" title="Bulleted list" aria-label="Bulleted list"><i class="ti ti-list"></i></button>
                    <button type="button" data-cmd="insertOrderedList" title="Numbered list" aria-label="Numbered list"><i class="ti ti-list-numbers"></i></button>
                    <button type="button" data-cmd="outdent" title="Decrease indent" aria-label="Decrease indent"><i class="ti ti-indent-decrease"></i></button>
                    <button type="button" data-cmd="indent" title="Increase indent" aria-label="Increase indent"><i class="ti ti-indent-increase"></i></button>
                </div>
                <div class="drf-doc-group">
                    <button type="button" data-row="below" title="Add a table row below"><i class="ti ti-row-insert-bottom"></i><span>Row below</span></button>
                    <button type="button" data-row="above" title="Add a table row above"><i class="ti ti-row-insert-top"></i><span>Row above</span></button>
                    <button type="button" data-row="delete" title="Delete this table row"><i class="ti ti-trash"></i><span>Delete row</span></button>
                </div>
                <div class="drf-doc-group">
                    <button type="button" data-cmd="undo" title="Undo (Ctrl+Z)" aria-label="Undo"><i class="ti ti-arrow-back-up"></i></button>
                    <button type="button" data-cmd="redo" title="Redo (Ctrl+Y)" aria-label="Redo"><i class="ti ti-arrow-forward-up"></i></button>
                    <button type="button" data-cmd="removeFormat" title="Clear formatting" aria-label="Clear formatting"><i class="ti ti-clear-formatting"></i></button>
                </div>
                <span class="drf-doc-state" id="docState" aria-live="polite">Saved</span>
            </div>
        <?php endif; ?>

        <!-- The page -->
        <div class="drf-doc-desk">
            <div class="doc-sheet">
                <div class="doc-letterhead"><img src="<?=base_url('assets/sorf/images/doc-header.png')?>" alt="Republic of the Philippines, Department of Science and Technology, Food and Nutrition Research Institute"></div>
                <div class="doc-body" id="docBody" <?= $released ? '' : 'contenteditable="true" spellcheck="true" role="textbox" aria-multiline="true" aria-label="Special Order document"' ?>><?= $doc['doc_html'] ?></div>
                <?= view('sorf/docs/doc-footer') ?>
            </div>
        </div>

        <?php if (!$released): ?>
        <div class="drf-card drf-wrap is-narrow mt-4">
            <div class="drf-card-body">
                <p class="drf-hint mb-2"><span class="doc-fill">[Yellow marks]</span> are blanks the request did not fill in. Click one and type over it. The letterhead and the footer are fixed.</p>
                <label class="form-label" for="templateAgain">Start over from a template</label>
                <div class="d-flex flex-wrap gap-2">
                    <div class="flex-grow-1"><?= $chooser('templateAgain') ?></div>
                    <button type="button" class="btn btn-drf-ghost px-3" data-doc-start="#templateAgain" data-doc-again><i class="ti ti-refresh me-1"></i> Start Over</button>
                </div>
            </div>
        </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<script>
    var __doc_editor = <?= json_encode([
        'recid' => $hd['recid'],
        'editable' => $doc !== null && !$released,
    ], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
</script>
<?php echo view('sorf/templates/sorf-footer', ['scripts' => ['docs/mydoceditor.js?v=6']]); ?>
