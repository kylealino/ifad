<?php
// The Special Order of a request, as a page to read and print. Standalone (no
// app chrome). $frame = shown inside the print modal, which has its own buttons.
$word = sorf_url('myrequest?meaction=DOC-WORD&recid=' . $hd['recid']);
// no final release yet: every copy is stamped DRAFT (Config\DocumentTemplates::$draftOnly)
$draft = config(\Config\Sorf\DocumentTemplates::class)->draftOnly;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?= esc(($draft ? 'DRAFT Special Order · ' : 'Special Order · ') . $hd['request_no']) ?></title>
  <link rel="stylesheet" href="<?=base_url('assets/sorf/css/drf-doc.css?v=5')?>" />
  <style>
    html, body { margin: 0; background: #e9edf2; }
    .doc-bar { position: sticky; top: 0; z-index: 2; display: flex; flex-wrap: wrap; gap: 8px; align-items: center; justify-content: space-between; padding: 10px 16px; background: #15375f; color: #fff; font: 600 14px Arial, Helvetica, sans-serif; }
    .doc-bar a, .doc-bar button { display: inline-block; padding: 8px 14px; border: 1px solid rgba(255,255,255,.35); border-radius: 8px; background: transparent; color: #fff; font: inherit; text-decoration: none; cursor: pointer; }
    .doc-bar button { background: #fff; color: #15375f; border-color: #fff; }
    .doc-desk { padding: 16px 12px 32px; }
    @media print { .doc-desk { padding: 0; } }
    /* DRAFT across the page; "fixed" repeats it on every printed page */
    .doc-watermark { position: fixed; inset: 0; z-index: 3; display: flex; align-items: center; justify-content: center; pointer-events: none; overflow: hidden; }
    .doc-watermark span { transform: rotate(-35deg); font: 700 clamp(72px, 16vw, 150px) Arial, Helvetica, sans-serif; letter-spacing: .12em; color: rgba(220, 38, 38, .13); white-space: nowrap; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
    .doc-bar .doc-draft-tag { margin-left: 8px; padding: 2px 8px; border-radius: 6px; background: #fdecec; color: #b91c1c; font-size: 12px; }
  </style>
</head>
<body>
<?php if (!$frame): ?>
  <div class="doc-bar doc-noprint">
    <span><?= esc('Special Order · ' . $hd['request_no']) ?><?php if ($draft): ?><span class="doc-draft-tag">DRAFT · not final</span><?php endif; ?></span>
    <span>
      <a href="<?= sorf_url('myrequest?meaction=VIEW&recid=' . $hd['recid']) ?>">Back to request</a>
      <a href="<?= $word ?>">Download Word</a>
      <button type="button" onclick="window.print()">Print</button>
    </span>
  </div>
<?php endif; ?>

  <?php if ($draft): ?>
  <div class="doc-watermark" aria-hidden="true"><span>DRAFT</span></div>
  <?php endif; ?>

  <div class="doc-desk">
    <div class="doc-sheet">
      <table class="doc-frame">
        <thead><tr><td><div class="doc-letterhead"><img src="<?=base_url('assets/sorf/images/doc-header.png')?>" alt="Republic of the Philippines, Department of Science and Technology, Food and Nutrition Research Institute"></div></td></tr></thead>
        <tfoot><tr><td><div class="doc-foot-space"></div></td></tr></tfoot>
        <tbody><tr><td><div class="doc-body"><?= $doc['doc_html'] ?></div></td></tr></tbody>
      </table>
      <?= view('sorf/docs/doc-footer') ?>
    </div>
  </div>
</body>
</html>
