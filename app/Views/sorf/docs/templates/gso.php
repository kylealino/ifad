<?php
// Special Order: General Special Order (a committee and its functions).
// Each request line is one member: position = role on the committee,
// purpose = a function of the committee.
$functions = array_values(array_unique(array_filter(array_map(fn ($row) => trim($row['purpose']), $rows))));
?>
<p class="doc-so">DOST-FNRI Special Order No. <b><?= drf_doc_so_no('', $hd['so_no']) ?></b><br>Series of <?= esc($year) ?></p>

<table class="doc-plain">
    <tr>
        <td style="width: 16%"><b>Subject:</b></td>
        <td><b><?= drf_doc_fill($hd['subject'], 'subject') ?></b></td>
    </tr>
</table>

<p class="doc-indent">In the interest of the service, DOST-FNRI <?= drf_doc_fill($hd['subject'], 'committee') ?> is hereby <span class="doc-fill">[reconstituted/updated/created]</span> and shall be composed of the following officials/employees:</p>

<p style="text-align: center"><b><?= drf_doc_fill(mb_strtoupper($hd['subject']), 'COMMITTEE NAME') ?></b></p>

<table class="doc-plain">
    <thead>
        <tr>
            <th style="width: 28%"><br></th>
            <th style="width: 38%">PRINCIPAL</th>
            <th style="width: 34%">ALTERNATES</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($rows as $row): ?>
        <tr>
            <td><b><?= drf_doc_fill($row['position'], 'Chairperson / Member / Secretariat') ?></b></td>
            <td style="text-align: center"><?= esc($row['staff_name']) ?></td>
            <td style="text-align: center"><br></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<p>The Committee shall perform the following functions:</p>
<ol>
    <?php if (empty($functions)): ?>
        <li><span class="doc-fill">[function of the committee]</span></li>
    <?php endif; ?>
    <?php foreach ($functions as $function): ?>
        <li><?= esc($function) ?></li>
    <?php endforeach; ?>
</ol>

<p class="doc-indent">This Special Order shall take effect immediately and remain valid until superseded.</p>

<p class="doc-sign"><b><?= esc($signatoryName) ?></b><br><?= esc($signatoryPosition) ?></p>
