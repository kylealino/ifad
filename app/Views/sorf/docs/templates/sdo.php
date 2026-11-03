<?php
// Special Order: designation of a Special Disbursing Officer (SDO). The first
// request line names the officer; every line is a fund, duration and amount.
$officer = $rows[0] ?? ['staff_name' => '', 'position' => ''];
?>
<p class="doc-so">DOST-FNRI Special Order No. <b><?= drf_doc_so_no('SDO-', $hd['so_no']) ?></b><br>Series of <?= esc($year) ?></p>

<table class="doc-plain">
    <tr>
        <td style="width: 24%; text-align: right">Subject:</td>
        <td><b><u>Designation of <?= drf_doc_fill($officer['staff_name'], 'NAME') ?> as<br>Special Disbursing Officer (SDO)</u></b></td>
    </tr>
</table>

<p class="doc-indent">In the interest of the service, <?= drf_doc_fill($officer['staff_name'], 'NAME') ?>, <?= drf_doc_fill($officer['position'], 'DESIGNATION') ?>, is hereby designated as Special Disbursing Officer (SDO) and is authorized to hold Cash Advance in the amount stated opposite his/her name, to defray operating expenses to be incurred relevant to the activities of the project stated herewith:</p>

<table class="doc-plain">
    <thead>
        <tr>
            <th style="width: 50%"><u>Source of Fund</u></th>
            <th style="width: 28%"><u>Duration</u></th>
            <th style="width: 22%"><u>Amount</u></th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($rows as $row): ?>
        <tr>
            <td style="text-align: center"><?= drf_doc_fill($row['source_of_fund'], 'source of fund') ?></td>
            <td style="text-align: center"><?= esc($row['duration']) ?></td>
            <td style="text-align: center"><?= drf_money($row['amount']) ?></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<p class="doc-indent">The designated Special Disbursing Officer shall perform the following duties and responsibilities:</p>
<ul>
    <li>Maintain a Cash Disbursement Record (Appendix 40), and keep a copy of Petty Cash Voucher (Appendix 48) and Report on Paid Petty Cash Vouchers (Appendix 49);</li>
    <li>Open a separate ATM account for the purpose of maintaining and monitoring of the cash advances, which must also be kept in a safekeeping device, either a vault or locked container;</li>
    <li>Required to liquidate the cash advance on or before <u>December 15, <?= esc($year) ?></u> or before the end date of the project duration on which the funds of the cash advance were drawn, whichever is earlier;</li>
    <li>The designated employee shall immediately liquidate the full amount of cash advances due to but not limited to the following circumstances:
        <ul>
            <li>separation from the agency</li>
            <li>on leave for a month or more</li>
        </ul>
    </li>
</ul>

<p class="doc-indent">Failure to do so would mean an automatic deduction from his/her salary of the amount granted in the next succeeding payday. This Special Order shall take effect immediately and will remain valid until superseded.</p>

<p class="doc-sign"><b><?= esc($signatoryName) ?></b><br><?= esc($signatoryPosition) ?></p>
