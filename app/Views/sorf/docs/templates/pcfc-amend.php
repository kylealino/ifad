<?php
// Special Order: amendment of a Petty Cash Fund Custodian order. The request
// lines give the fund (project) and the amount as amended.
?>
<p class="doc-so">DOST-FNRI Special Order No. <b><?= drf_doc_so_no('PCFC-', $hd['so_no']) ?></b><br>Series of <?= esc($year) ?></p>

<table class="doc-plain">
    <tr>
        <td style="width: 22%; text-align: right">Subject:</td>
        <td><b><u>Amendment of DOST-FNRI Special Order No. PCFC-<span class="doc-fill">[000]</span> s. <?= esc($year) ?></u></b></td>
    </tr>
</table>

<p class="doc-indent">In the interest of the service, DOST-FNRI Special Order No. PCFC-<span class="doc-fill">[000]</span> Series of <?= esc($year) ?> re: <?= drf_doc_fill($hd['subject'], 'subject of the PCFC to be amended') ?>, is hereby amended as follows:</p>

<table class="doc-plain">
    <thead>
        <tr>
            <th style="width: 52%">Project Title</th>
            <th style="width: 24%">Previously Approved Amount</th>
            <th style="width: 24%">Amount As Amended</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($rows as $row): ?>
        <tr>
            <td>&bull; <?= drf_doc_fill($row['source_of_fund'], 'project title') ?></td>
            <td style="text-align: center"><br></td>
            <td style="text-align: center"><?= drf_money($row['amount']) ?></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<p>Removal of the previously approved:</p>

<table class="doc-plain">
    <thead>
        <tr>
            <th style="width: 60%">Project Title</th>
            <th style="width: 40%">Amount</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>&bull; <br></td>
            <td style="text-align: center"><br></td>
        </tr>
    </tbody>
</table>

<p class="doc-indent">All other provisions from the previous Special Order shall remain valid. This Special Order shall take effect immediately and remain valid until superseded.</p>

<p class="doc-sign"><b><?= esc($signatoryName) ?></b><br><?= esc($signatoryPosition) ?></p>
