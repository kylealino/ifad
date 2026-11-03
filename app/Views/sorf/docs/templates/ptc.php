<?php
// Special Order: Project Team Composition (PTC). The request lines are sorted
// under the form's role headings by their designation.
$roles = ['Program Leader' => [], 'Project Leader' => [], 'Co-Project Leader' => [], 'Project Members' => []];
foreach ($rows as $row) {
    $designation = strtolower(trim($row['position']));
    if (strpos($designation, 'program lead') !== false) {
        $roles['Program Leader'][] = $row;
    } elseif (strpos($designation, 'co-project lead') !== false || strpos($designation, 'co project lead') !== false) {
        $roles['Co-Project Leader'][] = $row;
    } elseif (strpos($designation, 'project lead') !== false) {
        $roles['Project Leader'][] = $row;
    } else {
        $roles['Project Members'][] = $row;
    }
}
?>
<p class="doc-so">DOST-FNRI Special Order No. <b><?= drf_doc_so_no('PTC-', $hd['so_no']) ?></b><br>Series of <?= esc($year) ?></p>

<table class="doc-plain">
    <tr>
        <td style="width: 16%"><b>Subject:</b></td>
        <td>Project Team Composition for <?= drf_doc_fill($hd['subject'], 'project title') ?></td>
    </tr>
</table>

<table class="doc-table">
    <tr>
        <td style="width: 52%">Division: <?= esc($hd['division_name']) ?></td>
        <td>Section/Unit: <?= esc($hd['section_name']) ?></td>
    </tr>
</table>

<table class="doc-table">
    <thead>
        <tr>
            <th style="width: 28%">Name of<br>Staff Involved/ Project Team</th>
            <th style="width: 11%">% Involvement</th>
            <th style="width: 36%">Specific Duties &amp; Responsibilities</th>
            <th style="width: 25%">Specific Deliverables/ Material Title</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($roles as $role => $people): ?>
        <tr>
            <td><b><?= esc($role) ?>:</b><?php if (!empty($people)): ?><br><?= esc($people[0]['staff_name']) ?><?php endif; ?></td>
            <td style="text-align: center"><br></td>
            <td><br></td>
            <td><br></td>
        </tr>
        <?php foreach (array_slice($people, 1) as $person): ?>
        <tr>
            <td><?= esc($person['staff_name']) ?></td>
            <td style="text-align: center"><br></td>
            <td><br></td>
            <td><br></td>
        </tr>
        <?php endforeach; ?>
        <?php endforeach; ?>
    </tbody>
</table>

<p class="doc-indent">This Special Order shall take effect immediately and shall remain valid until superseded.</p>

<p class="doc-sign"><b><?= esc($signatoryName) ?></b><br><?= esc($signatoryPosition) ?></p>

<p>Project Leader</p>
<p>Co-Project Leader</p>
