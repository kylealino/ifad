<?php
// IFAD Portal home (no sidebar), sized to fit one screen without scrolling:
//   1. a greeting band (what the portal is) with Ifa, the portal's guide: an
//      inline SVG character whose speech bubble cycles tips (CSS animation,
//      off for reduced motion)
//   2. every system as a tile: live ones are wide and list their steps (from
//      Config\Systems); those the user can open link to their start page
//      (click / Enter / their number key), the others say why not
//      (no access); systems not live yet open their preview page
//   3. one strip: how a request moves, and the portal's four values
$first = explode(' ', trim((string) $user['full_name']))[0];
// KYLE -> Kyle, but short all-caps names such as HR stay as they are
if (strlen($first) > 3 && strtoupper($first) === $first) {
    $first = ucfirst(strtolower($first));
}
$hour = (int) date('G');
$greeting = $hour < 12 ? 'Good morning' : ($hour < 18 ? 'Good afternoon' : 'Good evening');

$openSystems = array_filter($systems, fn ($s) => $s['url'] !== '');

// Ifa's tips, in order: [text (plain; escaped on output), CSS selector of what
// the tip talks about, or '' for none]. Ifa stays put; the thing it mentions
// gets a short highlight ring (.ifad-spot) while the tip shows.
// Order: hello, one tip per system (its 'tip' in Config\Systems), then how to
// get around the portal.
$tips = [["Hi {$first}! I'm Ifa, your guide. Here's a quick look at each system.", $openSystems ? '.ifad-tile.is-open:not(.is-dev)' : '.ifad-grid']];
foreach ($systems as $key => $s) {
    if (!empty($s['tip'])) {
        $tips[] = [$s['tip'], '.ifad-tile[data-system="' . $key . '"]'];
    }
}
if ($openSystems) {
    $keys = array_map(fn ($s) => $s['no'] . ' for ' . $s['short'], $openSystems);
    $tips[] = ['Shortcut: press ' . implode(', ', $keys) . ' on your keyboard to jump straight in.', '.ifad-tile.is-open:not(.is-dev) kbd'];
}
$tips[] = ['Systems not live yet open a preview of what they will cover. Press 3 to 6 to peek.', '.ifad-tile.is-dev'];
$tips[] = ['One sign-in opens every system you have a role in. Switch anytime with the tabs at the top left.', '.ifad-tabs'];
$tips[] = ['The bell shows what is waiting for your approval in every system, with a link to each.', '#fodsNotifBtn'];
if (isset($openSystems['fods'])) {
    $tips[] = ['The chat button at the bottom right is the FODS Assistant: ask it about your obligations, DVs and approvals.', '#fodsAiFab'];
}
$tips[] = ['Chat with colleagues in Messages, the speech-bubble icon in the topbar.', '#fodsInboxBtn'];
$tips[] = ['Stuck? File a ticket in the Helpdesk (the lifebuoy icon) and support will reply there.', '#fodsHelpdeskBtn'];

$flow = [['ti-file-plus', 'Submit'], ['ti-user-check', 'Review & approve'], ['ti-settings', 'Process'], ['ti-send', 'Release'], ['ti-timeline', 'Track']];
$values = [
    ['ti-plug-connected', 'Integrated', 'One account and shared data across every system'],
    ['ti-bolt', 'Efficient', 'Online forms and routed approvals instead of paper'],
    ['ti-eye-check', 'Transparent', 'Every request has a status, a history and a named signatory'],
    ['ti-discount-check', 'Quality-Driven', 'Processes kept in line with the quality management system'],
];

echo view('templates/myheader.php', [
    'ifadSystem' => 'portal',
    'pageTitle' => 'Portal Home',
    'bodyClass' => 'ifad-fit', // fods.css: the page fits the window, no scrolling
    'headExtra' => '<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,500;1,9..144,500&display=swap" />',
]);
?>
<div class="container-fluid ifad-home">
    <header class="ifad-hello">
        <div class="ifad-hello-main">
            <div class="ifad-hello-kicker">Integrated Finance and Administrative Division <span><?= date('l, F j, Y') ?></span></div>
            <h1><?= esc($greeting) ?>, <em><?= esc($first) ?></em></h1>
            <p>The IFAD Portal is the division's single sign-in for finance, document requests, facilities, quality and human resources. Every request moves along one clear, trackable path.</p>
        </div>

        <div class="ifad-guide" id="ifadGuide">
            <div class="ifad-bubble" role="status" aria-live="polite">
                <p id="ifadTip"><?= esc($tips[0][0]) ?></p>
                <div class="ifad-bubble-foot">
                    <span class="ifad-dots" aria-hidden="true">
                        <?php foreach ($tips as $n => $t): ?><i class="<?= $n === 0 ? 'is-on' : '' ?>"></i><?php endforeach; ?>
                    </span>
                    <button type="button" class="ifad-bubble-next" id="ifadTipNext">Next tip <i class="ti ti-arrow-right"></i></button>
                </div>
            </div>
            <button type="button" class="ifad-mascot" id="ifadMascot" aria-label="Ifa, the portal guide: show the next tip">
                <?= view('portal/ifa-mascot') ?>
            </button>
        </div>
    </header>

    <?php if (session()->getFlashdata('ifad_notice')): ?>
        <div class="ifad-note" role="alert"><i class="ti ti-info-circle"></i><span><?= esc(session()->getFlashdata('ifad_notice')) ?></span></div>
    <?php endif; ?>
    <?php if ($openCount === 0): ?>
        <div class="ifad-note" role="alert"><i class="ti ti-lock"></i><span>You have no system to open yet. Ask the administrator for a role in FODS or the Document Request System.</span></div>
    <?php endif; ?>

    <section class="ifad-grid" aria-label="Systems">
        <?php $i = 0; foreach ($systems as $key => $s):
            $live = $s['status'] === 'live';
            $open = $s['url'] !== '';
            // systems not live yet (in development or being integrated) open their
            // preview ("under development" page, portal/<key>) like any tile
            $dev = !$live;
            $href = $open ? $s['url'] : ($dev ? site_url('portal/' . $key) : '');
            $tag = $href !== '' ? 'a' : 'div';
        ?>
            <<?= $tag ?> class="ifad-tile <?= $open ? 'is-open' : ($dev ? 'is-open is-dev' : 'is-locked') ?>"
                style="--i:<?= $i++ ?>" data-system="<?= esc($key, 'attr') ?>"
                <?= $href !== '' ? 'href="' . $href . '" data-key="' . $s['no'] . '"' : 'aria-disabled="true"' ?>>
                <div class="ifad-tile-top">
                    <span class="ifad-tile-icon <?= $s['tone'] ?>"><i class="ti <?= $s['icon'] ?>"></i></span>
                    <div class="ifad-tile-body">
                        <span class="ifad-tile-area"><?= esc($s['area']) ?></span>
                        <strong class="ifad-tile-name"><?= esc($s['short']) ?></strong>
                        <span class="ifad-tile-full"><?= esc($s['name']) ?></span>
                    </div>
                    <?php if ($open): ?>
                        <span class="ifad-tile-state is-live">Available</span>
                    <?php elseif ($live): ?>
                        <span class="ifad-tile-state"><i class="ti ti-lock"></i> No access</span>
                    <?php else: ?>
                        <?php $stage = ifad_stage($s); ?><span class="ifad-tile-state <?= $stage['class'] ?>"><?= esc($stage['label']) ?></span>
                    <?php endif; ?>
                </div>
                <ol class="ifad-steps" aria-label="What <?= esc($s['short'], 'attr') ?> covers">
                    <?php foreach ($s['steps'] as $k => $step): ?>
                        <li><span><?= $k + 1 ?></span><?= esc($step) ?></li>
                    <?php endforeach; ?>
                </ol>
                <div class="ifad-tile-go">
                    <?php if ($open): ?>
                        <span>Open <?= esc($s['short']) ?> <kbd><?= (int) $s['no'] ?></kbd></span>
                        <i class="ti ti-arrow-right"></i>
                    <?php elseif ($live): ?>
                        <span class="is-muted"><i class="ti ti-lock"></i> Ask the administrator for a role</span>
                    <?php else: ?>
                        <span>Preview <?= esc($s['tab'] ?? $s['short']) ?> <kbd><?= (int) $s['no'] ?></kbd></span>
                        <i class="ti ti-arrow-right"></i>
                    <?php endif; ?>
                </div>
            </<?= $tag ?>>
        <?php endforeach; ?>
    </section>

    <section class="ifad-strip" aria-label="How the portal works">
        <div class="ifad-strip-part">
            <span class="ifad-strip-label">Every request</span>
            <ol class="ifad-flow">
                <?php foreach ($flow as [$icon, $label]): ?>
                    <li><i class="ti <?= $icon ?>"></i><span><?= esc($label) ?></span></li>
                <?php endforeach; ?>
            </ol>
        </div>
        <div class="ifad-strip-part ifad-values">
            <?php foreach ($values as [$icon, $title, $text]): ?>
                <span class="ifad-value" title="<?= esc($text, 'attr') ?>"><i class="ti <?= $icon ?>"></i><?= esc($title) ?></span>
            <?php endforeach; ?>
        </div>
    </section>
</div>
<script>
(function () {
    // Number keys open the matching system (outside text boxes)
    document.addEventListener('keydown', function (e) {
        if (e.ctrlKey || e.metaKey || e.altKey || e.repeat) { return; }
        var t = e.target;
        if (t.isContentEditable || /^(INPUT|TEXTAREA|SELECT)$/.test(t.tagName)) { return; }
        var tile = document.querySelector('.ifad-tile[data-key="' + e.key + '"]');
        if (tile) { window.location.href = tile.href; }
    });

    // Ifa's tips: next on the button or the character; they also turn by
    // themselves every 9 s until the user takes over (not with reduced motion).
    // Ifa stays put; what the tip mentions gets a highlight ring (.ifad-spot)
    // for a few seconds. Tips whose target is missing (e.g. no FODS) still show.
    var tips = <?= json_encode(array_values($tips), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
    var tipEl = document.getElementById('ifadTip');
    var dots = document.querySelectorAll('.ifad-dots i');
    var guide = document.getElementById('ifadGuide');
    var at = 0, timer = null, spotTimer = null;
    function clearSpot() {
        document.querySelectorAll('.ifad-spot').forEach(function (el) { el.classList.remove('ifad-spot'); });
    }
    function spot(sel) {
        clearSpot();
        clearTimeout(spotTimer);
        if (!sel) { return; }
        var els = document.querySelectorAll(sel);
        if (!els.length) { return; }
        void document.body.offsetWidth; // restart the ring animation
        els.forEach(function (el) { el.classList.add('ifad-spot'); });
        spotTimer = setTimeout(clearSpot, 4000);
    }
    function show(n) {
        at = (n + tips.length) % tips.length;
        tipEl.classList.remove('is-in');
        void tipEl.offsetWidth;
        tipEl.textContent = tips[at][0];
        spot(tips[at][1]);
        tipEl.classList.add('is-in');
        dots.forEach(function (d, i) { d.classList.toggle('is-on', i === at); });
        guide.classList.remove('is-hop');
        void guide.offsetWidth;
        guide.classList.add('is-hop');
    }
    function takeOver() { if (timer) { clearInterval(timer); timer = null; } show(at + 1); }
    document.getElementById('ifadTipNext').addEventListener('click', takeOver);
    document.getElementById('ifadMascot').addEventListener('click', takeOver);
    if (!window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        timer = setInterval(function () { if (!document.hidden) { show(at + 1); } }, 9000);
    }
    // Steps that don't fit a tile are hidden whole (never cut mid-line) and
    // summed up in a "+N more" chip whose tooltip lists them. Redone on resize.
    function fitSteps() {
        document.querySelectorAll('.ifad-steps').forEach(function (ol) {
            var more = ol.querySelector('.ifad-steps-more');
            if (more) { more.remove(); }
            var items = Array.prototype.slice.call(ol.children);
            items.forEach(function (li) { li.hidden = false; });
            if (ol.scrollHeight <= ol.clientHeight + 1) { return; }
            more = document.createElement('li');
            more.className = 'ifad-steps-more';
            ol.appendChild(more);
            var shown = items.length;
            do {
                items[--shown].hidden = true;
                var rest = items.slice(shown);
                more.textContent = '+' + rest.length + ' more';
                more.title = rest.map(function (li) { return li.textContent.replace(/^\d+/, '').trim(); }).join('\n');
            } while (shown > 1 && ol.scrollHeight > ol.clientHeight + 1);
        });
    }
    fitSteps();
    if (document.fonts && document.fonts.ready) { document.fonts.ready.then(fitSteps); }
    var fitTimer = null;
    window.addEventListener('resize', function () { clearTimeout(fitTimer); fitTimer = setTimeout(fitSteps, 120); });

    // the first tip's highlight, once the tiles have risen in
    setTimeout(function () { if (at === 0) { spot(tips[0][1]); } }, 700);
})();
</script>
<?php echo view('templates/myfooter.php'); ?>
