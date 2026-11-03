<?php
// Special Order: Authority to Attend Activity (ATAA).
$funds = array_values(array_unique(array_filter(array_map(fn ($row) => trim($row['source_of_fund']), $rows))));
?>
<p class="doc-so">DOST-FNRI Special Order No. <b><?= drf_doc_so_no('ATAA-', $hd['so_no']) ?></b><br>Series of <?= esc($year) ?></p>

<table class="doc-plain">
    <tr>
        <td style="width: 16%; text-align: right">Subject:</td>
        <td><b><?= drf_doc_fill($hd['subject'], 'subject') ?></b></td>
    </tr>
</table>

<p class="doc-indent">In the interest of the service, the following DOST-FNRI personnel are hereby authorized to attend the <b><?= drf_doc_fill($hd['subject'], 'training title') ?></b>, on <span class="doc-fill">[date]</span> at the <span class="doc-fill">[venue]</span>:</p>

<table class="doc-plain">
    <thead>
        <tr>
            <th style="width: 8%; text-align: left"><u>No.</u></th>
            <th style="width: 38%"><u>Name</u></th>
            <th style="width: 32%"><u>Position</u></th>
            <th style="width: 22%"><u>Registration Fee</u></th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($rows as $n => $row): ?>
        <tr>
            <td><?= $n + 1 ?>.</td>
            <td><?= esc($row['staff_name']) ?></td>
            <td><?= esc($row['position']) ?></td>
            <td style="text-align: center"><?= $row['registration_fee'] !== null ? drf_money($row['registration_fee']) : 'N/A' ?></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<p><b><u>Source of Fund:</u></b> <?= !empty($funds) ? esc(implode('; ', $funds)) : 'N/A' ?></p>

<p class="doc-indent">As such, the attendees are responsible for the following:</p>
<ol>
    <li>To submit a Back-to-Office report per DOST-FNRI Memorandum dated November 14, 2012;</li>
    <li>To liquidate cash advances incurred, if any, and</li>
    <li>To submit to the HRM Section copy of the Certificate of Training/Seminar/ Attendance immediately upon return to work.</li>
</ol>

<p class="doc-sign"><b><?= esc($signatoryName) ?></b><br><?= esc($signatoryPosition) ?></p>
