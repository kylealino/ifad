<?php
// IFAD Portal home (no sidebar): a greeting band and every system as a tile. Systems the user can open are white tiles
// (Enter / click / their number key opens them); the others are greyed out
// with why (no access / coming soon). Live systems get the wider tiles.
$first = explode(' ', trim((string) $user['full_name']))[0];
// KYLE -> Kyle, but short all-caps names such as HR stay as they are
if (strlen($first) > 3 && strtoupper($first) === $first) {
    $first = ucfirst(strtolower($first));
}
$hour = (int) date('G');
$greeting = $hour < 12 ? 'Good morning' : ($hour < 18 ? 'Good afternoon' : 'Good evening');

// six-column grid: each group (live / coming soon) shares its row evenly
$span = fn (int $n) => $n > 0 && 6 % $n === 0 ? 6 / $n : 2;
$liveCount = count(array_filter($systems, fn ($s) => $s['status'] === 'live'));
$liveSpan = $span($liveCount);
$soonSpan = $span(count($systems) - $liveCount);

echo view('templates/myheader.php', [
    'ifadSystem' => 'portal',
    'pageTitle' => 'Portal Home',
    'headExtra' => '<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,500;1,9..144,500&display=swap" />',
]);
?>
<div class="container-fluid ifad-home">
    <div>
        <header class="ifad-hello">
            <div>
                <div class="ifad-hello-kicker">IFAD Portal</div>
                <h1><?= esc($greeting) ?>, <em><?= esc($first) ?></em></h1>
                <p>Choose the system you want to use.</p>
            </div>
            <div class="ifad-hello-side">
                <span class="ifad-hello-date"><i class="ti ti-calendar"></i> <?= date('l, F j, Y') ?></span>
                <span class="ifad-hello-tag">Integrated. Efficient. Transparent. Quality-Driven.</span>
            </div>
        </header>

        <?php if (session()->getFlashdata('ifad_notice')): ?>
            <div class="ifad-note" role="alert"><i class="ti ti-info-circle"></i><span><?= esc(session()->getFlashdata('ifad_notice')) ?></span></div>
        <?php endif; ?>
        <?php if ($openCount === 0): ?>
            <div class="ifad-note" role="alert"><i class="ti ti-lock"></i><span>You have no system to open yet. Ask the administrator for a role in FODS or the Document Request System.</span></div>
        <?php endif; ?>

        <section class="ifad-grid" aria-label="Systems">
            <?php $i = 0; foreach ($systems as $s):
                $live = $s['status'] === 'live';
                $open = $s['url'] !== '';
                $tag = $open ? 'a' : 'div';
            ?>
                <<?= $tag ?> class="ifad-tile <?= $open ? 'is-open' : ($live ? 'is-locked' : 'is-soon') ?>"
                    style="--span:<?= $live ? $liveSpan : $soonSpan ?>;--i:<?= $i++ ?>"
                    <?= $open ? 'href="' . $s['url'] . '" data-key="' . $s['no'] . '"' : 'aria-disabled="true"' ?>>
                    <div class="ifad-tile-top">
                        <span class="ifad-tile-icon <?= $s['tone'] ?>"><i class="ti <?= $s['icon'] ?>"></i></span>
                        <?php if ($open): ?>
                            <span class="ifad-tile-state is-live">Available</span>
                        <?php elseif ($live): ?>
                            <span class="ifad-tile-state"><i class="ti ti-lock"></i> No access</span>
                        <?php else: ?>
                            <span class="ifad-tile-state"><i class="ti ti-hourglass"></i> Coming soon</span>
                        <?php endif; ?>
                    </div>
                    <div class="ifad-tile-body">
                        <span class="ifad-tile-area"><?= esc($s['area']) ?></span>
                        <strong class="ifad-tile-name"><?= esc($live ? $s['short'] : $s['name']) ?></strong>
                        <?php if ($live): ?><span class="ifad-tile-full"><?= esc($s['name']) ?></span><?php endif; ?>
                    </div>
                    <?php if ($open): ?>
                        <div class="ifad-tile-go">
                            <span>Open <?= esc($s['short']) ?></span>
                            <i class="ti ti-arrow-right"></i>
                        </div>
                    <?php endif; ?>
                </<?= $tag ?>>
            <?php endforeach; ?>
        </section>
    </div>
</div>
<script>
// Number keys open the matching system (outside text boxes)
document.addEventListener('keydown', function (e) {
    if (e.ctrlKey || e.metaKey || e.altKey || e.repeat) { return; }
    var t = e.target;
    if (t.isContentEditable || /^(INPUT|TEXTAREA|SELECT)$/.test(t.tagName)) { return; }
    var tile = document.querySelector('.ifad-tile[data-key="' + e.key + '"]');
    if (tile) { window.location.href = tile.href; }
});
</script>
<?php echo view('templates/myfooter.php'); ?>
