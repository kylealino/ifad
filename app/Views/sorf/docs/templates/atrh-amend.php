<?php
// Special Order: amendment of an Authority to Receive Incentive / Honorarium.
// $template['kind'] is "Incentive" or "Honorarium". The request lines fill the
// "As Amended" columns; HR types what was previously approved.
$kind = $template['kind'];
?>
<p class="doc-so">DOST-FNRI Special Order No. <b><?= drf_doc_so_no('ATRH-', $hd['so_no']) ?></b><br>Series of <?= esc($year) ?></p>

<p style="text-align: center"><b>Subject: Amendment of DOST-FNRI Special Order No. ATRH-<span class="doc-fill">[000]</span><br>Series <?= esc($year) ?> Re: Authority to Receive <?= esc($kind) ?></b></p>

<p class="doc-indent">In the interest of service, DOST-FNRI Special Order No. ATRH-<span class="doc-fill">[000]</span> Series of <?= esc($year) ?> Re: Authority to Receive <?= esc($kind) ?> for services rendered in connection with the conduct of <?= $kind === 'Incentive' ? 'the project titled ' : '' ?><b>&ldquo;<?= drf_doc_fill($hd['subject'], 'project title') ?>&rdquo;</b>, is hereby amended as follows:</p>

<table class="doc-table doc-small">
    <thead>
        <tr>
            <th><u>Name As Approved</u></th>
            <th><u>Name As Amended</u></th>
            <th><u>Designation As Approved</u></th>
            <th><u>Designation As Amended</u></th>
            <th><u>Duration As Approved</u></th>
            <th><u>Duration As Amended</u></th>
            <th><u>Amount As Approved</u></th>
            <th><u>Amount As Amended</u></th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($rows as $row): ?>
        <tr>
            <td><br></td>
            <td><?= esc($row['staff_name']) ?></td>
            <td><br></td>
            <td><?= esc($row['position']) ?></td>
            <td><br></td>
            <td><?= esc($row['duration']) ?></td>
            <td style="text-align: right"><br></td>
            <td style="text-align: right"><?= drf_money($row['amount']) ?></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<p class="doc-indent">All other provisions from the previously approved Special Order shall remain valid. This Special Order shall take effect immediately and remain valid until superseded.</p>

<p class="doc-sign"><b><?= esc($signatoryName) ?></b><br><?= esc($signatoryPosition) ?></p>
