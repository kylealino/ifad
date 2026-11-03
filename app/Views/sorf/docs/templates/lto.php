<?php
// Local Travel Order (LTO). Travellers are the request lines; the travel and
// its expenses come from the travel order part of the request.
$lto = $lto ?? ['destination' => '', 'travel_dates' => '', 'purpose' => '', 'is_general_fund' => 0, 'project_fund' => '', 'other_fund' => '', 'remarks' => ''];
$funds = ['general_fund', 'project_fund', 'other_fund'];
$used = array_fill_keys($funds, false);
foreach ($expenses as $line) {
    foreach ($funds as $fund) {
        $used[$fund] = $used[$fund] || trim((string) $line[$fund]) !== '';
    }
}
$box = fn (bool $on) => $on ? '&#9745;' : '&#9744;';
$mark = fn (bool $on) => $on ? '( &#10003; )' : '( &nbsp; )';
$amount = fn (string $code, string $fund) => trim((string) ($expenses[$code][$fund] ?? '')) !== '' ? esc($expenses[$code][$fund]) : '<br>';
?>
<p class="doc-so"><b>LOCAL TRAVEL ORDER No. <?= drf_doc_so_no('', $hd['so_no']) ?></b><br>Series of <?= esc($year) ?></p>

<p>Authority to travel is hereby granted to:</p>

<table class="doc-plain">
    <thead>
        <tr>
            <th style="width: 36%">NAME</th>
            <th style="width: 34%">POSITION</th>
            <th style="width: 30%">DIVISION/ AGENCY</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($rows as $row): ?>
        <tr>
            <td style="text-align: center"><?= esc($row['staff_name']) ?></td>
            <td style="text-align: center"><?= esc($row['position']) ?></td>
            <td style="text-align: center"><?= esc($row['division_agency']) ?></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<table class="doc-plain">
    <thead>
        <tr>
            <th style="width: 30%">Destination/s:</th>
            <th style="width: 34%">Inclusive Date/s of Travel:</th>
            <th style="width: 36%">Purpose (s) of the Travel:</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td style="text-align: center"><?= drf_doc_fill($lto['destination'], 'destination') ?></td>
            <td style="text-align: center"><?= drf_doc_fill($lto['travel_dates'], 'dates of travel') ?></td>
            <td style="text-align: center"><?= drf_doc_fill($lto['purpose'], 'purpose') ?></td>
        </tr>
    </tbody>
</table>

<table class="doc-plain doc-lines">
    <thead>
        <tr>
            <th style="width: 25%">Travel Expenses to be incurred:</th>
            <th colspan="3" class="doc-boxed">Travel expenses appropriation/fund charging:</th>
        </tr>
        <tr>
            <td><br></td>
            <td style="width: 25%"><?= $box((int) $lto['is_general_fund'] === 1 || $used['general_fund']) ?> General Fund:</td>
            <td style="width: 25%"><?= $box($lto['project_fund'] !== '' || $used['project_fund']) ?> Project Funds: <?= esc($lto['project_fund']) ?></td>
            <td style="width: 25%"><?= $box($lto['other_fund'] !== '' || $used['other_fund']) ?> Others: (e.g. sponsor/ requesting agency) <?= esc($lto['other_fund']) ?></td>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td><?= $mark(isset($expenses['actual'])) ?> <b>Actual</b></td>
            <?php foreach ($funds as $fund): ?><td class="doc-line"><?= $amount('actual', $fund) ?></td><?php endforeach; ?>
        </tr>
        <tr>
            <td colspan="4"><?= $mark(isset($expenses['accommodation']) || isset($expenses['meals']) || isset($expenses['incidental'])) ?> <b>Per Diem</b></td>
        </tr>
        <?php foreach (['accommodation' => 'Accommodation', 'meals' => 'Meals/Food', 'incidental' => 'Incidental expenses'] as $code => $label): ?>
        <tr>
            <td style="padding-left: 22px"><?= $box(isset($expenses[$code])) ?> <?= $label ?></td>
            <?php foreach ($funds as $fund): ?><td class="doc-line"><?= $amount($code, $fund) ?></td><?php endforeach; ?>
        </tr>
        <?php endforeach; ?>
        <tr>
            <td colspan="4"><?= $mark(isset($expenses['official_vehicle']) || isset($expenses['public_conveyance'])) ?> <b>Transportation</b></td>
        </tr>
        <?php foreach (['official_vehicle' => 'Official Vehicle', 'public_conveyance' => 'Public conveyance (Airplane, Bus, Taxi)'] as $code => $label): ?>
        <tr>
            <td style="padding-left: 22px"><?= $box(isset($expenses[$code])) ?> <?= $label ?></td>
            <?php foreach ($funds as $fund): ?><td class="doc-line"><?= $amount($code, $fund) ?></td><?php endforeach; ?>
        </tr>
        <?php endforeach; ?>
        <tr>
            <td><?= $mark(isset($expenses['others'])) ?> <b>Others</b></td>
            <?php foreach ($funds as $fund): ?><td class="doc-line"><?= $amount('others', $fund) ?></td><?php endforeach; ?>
        </tr>
        <tr>
            <td>Remarks/ Special Instructions:</td>
            <td colspan="3" class="doc-line"><?= $lto['remarks'] !== '' ? nl2br(esc($lto['remarks'])) : '<br>' ?></td>
        </tr>
    </tbody>
</table>

<p><i>A report of your travel must be submitted to the Agency Head/Supervising Official within 7 days from completion of travel. Liquidation of cash advance should be in accordance with Executive Order No. 77: Prescribing Rules and Regulations and Rates of Expenses and Allowances for Official Local and Foreign Travels of Government Personnel and DOST Administrative Order no. 012 series of 2019.</i></p>

<p class="doc-sign"><b>APPROVED BY:</b><br><br><br><b><?= esc($signatoryName) ?></b><br><?= esc($signatoryPosition) ?></p>
