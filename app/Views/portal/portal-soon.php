<?php
// Preview page of a system that is not live yet (portal/<key>, Portal::soon),
// sized to the window like the portal page (body.ifad-fit). Its wording follows
// the system's stage (ifad_stage): "In development" (being built here) or
// "Integration in progress" (an existing system the portal is connecting to).
// Left: what the system is, its stage, and what it covers (its steps from
// Config\Systems); right: an animated scene with Ifa (inline SVG, CSS
// animation only, off for reduced motion). No progress figure is shown:
// there is no real one to show.
$stage = ifad_stage($system);
$integration = $stage['key'] === 'integration';
$lead = (string) ($system['tip'] ?? '');
$lead = preg_replace('/^Coming soon:\s*/i', '', $lead);
$lead = $lead !== '' ? ucfirst($lead) : $system['name'] . ' is ' . $stage['verb'] . ' for the IFAD Portal.';

echo view('templates/myheader.php', [
    'ifadSystem' => $key,
    'pageTitle' => $system['short'] . ' · ' . $stage['label'],
    'bodyClass' => 'ifad-fit',
    'headExtra' => '<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,500;1,9..144,500&display=swap" />',
]);
?>
<div class="container-fluid ifad-home">
    <section class="ifad-soon">
        <div class="ifad-soon-text">
            <a class="ifad-soon-back" href="<?= site_url('portal') ?>"><i class="ti ti-arrow-left"></i> All systems</a>

            <div class="ifad-soon-head">
                <span class="ifad-soon-icon <?= $system['tone'] ?>"><i class="ti <?= $system['icon'] ?>"></i></span>
                <div>
                    <span class="ifad-soon-area"><?= esc($system['area']) ?></span>
                    <span class="ifad-soon-status <?= $stage['class'] ?>"><i></i> <?= esc($stage['label']) ?></span>
                </div>
            </div>

            <h1><?= esc($system['short']) ?> <em><?= $integration ? 'is being connected' : 'is on its way' ?></em></h1>
            <p class="ifad-soon-name"><?= esc($system['name']) ?></p>
            <p class="ifad-soon-lead"><?= esc($lead) ?></p>

            <div class="ifad-soon-bar <?= $stage['class'] ?>" role="img" aria-label="<?= esc($stage['label'], 'attr') ?>">
                <span></span>
            </div>
            <p class="ifad-soon-note"><i class="ti ti-info-circle"></i>
                <?= $integration
                    ? 'It runs as its own system. Once the connection is ready, you will reach it from here with your same IFAD Portal sign-in.'
                    : 'It will open here, under your same IFAD Portal sign-in, once it is ready.' ?></p>

            <h2><?= $integration ? 'What it covers' : 'What it will cover' ?></h2>
            <ol class="ifad-soon-steps">
                <?php foreach ($system['steps'] as $k => $step): ?>
                    <li style="--i:<?= $k ?>"><span><?= $k + 1 ?></span><?= esc($step) ?></li>
                <?php endforeach; ?>
            </ol>
        </div>

        <div class="ifad-soon-art" aria-hidden="true">
            <svg viewBox="0 0 360 300" focusable="false">
                <!-- blueprint backdrop -->
                <defs>
                    <pattern id="ifadGrid" width="18" height="18" patternUnits="userSpaceOnUse">
                        <path d="M18 0H0V18" fill="none" stroke="rgba(95,196,189,.16)" stroke-width="1" />
                    </pattern>
                </defs>
                <circle cx="185" cy="150" r="138" fill="url(#ifadGrid)" />
                <circle cx="185" cy="150" r="138" fill="none" stroke="rgba(95,196,189,.25)" stroke-dasharray="4 7" class="s-orbit" />

                <!-- gears -->
                <g class="s-gear s-gear-a">
                    <?php for ($t = 0; $t < 8; $t++): ?><rect x="293" y="30" width="14" height="14" rx="3" fill="#5fc4bd" transform="rotate(<?= $t * 45 ?> 300 62)" /><?php endfor; ?>
                    <circle cx="300" cy="62" r="24" fill="#5fc4bd" />
                    <circle cx="300" cy="62" r="9" fill="#0b2a36" />
                </g>
                <g class="s-gear s-gear-b">
                    <?php for ($t = 0; $t < 6; $t++): ?><rect x="255" y="83" width="11" height="11" rx="2.5" fill="#fbbf24" transform="rotate(<?= $t * 60 + 30 ?> 260 104)" /><?php endfor; ?>
                    <circle cx="260" cy="104" r="16" fill="#fbbf24" />
                    <circle cx="260" cy="104" r="6" fill="#0b2a36" />
                </g>

                <!-- ground -->
                <rect x="40" y="262" width="290" height="6" rx="3" fill="rgba(255,255,255,.12)" />

                <!-- blocks being stacked (loop) -->
                <g class="s-block" style="--d:0s"><rect x="196" y="226" width="78" height="36" rx="6" fill="#148a8a" /><rect x="206" y="236" width="24" height="5" rx="2.5" fill="rgba(255,255,255,.5)" /></g>
                <g class="s-block" style="--d:.7s"><rect x="210" y="190" width="64" height="36" rx="6" fill="#0f6f6f" /><rect x="220" y="200" width="30" height="5" rx="2.5" fill="rgba(255,255,255,.45)" /></g>
                <g class="s-block" style="--d:1.4s"><rect x="203" y="154" width="56" height="36" rx="6" fill="#5fc4bd" /><path d="M222 172 l6 6 l12 -12" fill="none" stroke="#0b2a36" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round" /></g>

                <!-- sparks where Ifa taps -->
                <g class="s-spark"><path d="M186 214 l-8 -6 M190 206 l-2 -10 M196 212 l8 -6" stroke="#fde68a" stroke-width="3" stroke-linecap="round" /></g>

                <!-- Ifa with a hard hat and a wrench -->
                <g class="s-ifa" transform="translate(52 90)">
                    <ellipse cx="80" cy="176" rx="34" ry="5" fill="rgba(0,0,0,.25)" />
                    <g class="s-ifa-body">
                        <rect x="38" y="32" width="84" height="66" rx="26" fill="#0f6f6f" />
                        <rect x="48" y="44" width="64" height="42" rx="17" fill="#e6f4f4" />
                        <g class="s-eyes" fill="#0b2a36"><ellipse cx="68" cy="61" rx="4.5" ry="5.5" /><ellipse cx="92" cy="61" rx="4.5" ry="5.5" /></g>
                        <path d="M72 71 Q80 78 88 71" fill="none" stroke="#0b2a36" stroke-width="3" stroke-linecap="round" />
                        <rect x="31" y="54" width="9" height="20" rx="4.5" fill="#0b2a36" />
                        <rect x="120" y="54" width="9" height="20" rx="4.5" fill="#0b2a36" />
                        <!-- hard hat -->
                        <path d="M42 40 Q44 8 80 8 Q116 8 118 40 Z" fill="#fbbf24" />
                        <rect x="32" y="36" width="96" height="9" rx="4.5" fill="#f59e0b" />
                        <rect x="76" y="10" width="8" height="28" rx="4" fill="#f59e0b" />
                        <!-- body, legs -->
                        <rect x="52" y="100" width="56" height="50" rx="18" fill="#148a8a" />
                        <rect x="58" y="112" width="44" height="7" rx="3.5" fill="#fbbf24" />
                        <rect x="62" y="148" width="13" height="24" rx="6.5" fill="#0b2a36" />
                        <rect x="85" y="148" width="13" height="24" rx="6.5" fill="#0b2a36" />
                        <path d="M54 112 Q40 124 44 142" fill="none" stroke="#148a8a" stroke-width="10" stroke-linecap="round" />
                        <!-- tapping arm with wrench -->
                        <g class="s-arm">
                            <path d="M106 112 Q124 116 130 128" fill="none" stroke="#148a8a" stroke-width="10" stroke-linecap="round" />
                            <g transform="rotate(35 132 130)">
                                <rect x="128" y="112" width="8" height="34" rx="4" fill="#cbd5e1" />
                                <path d="M124 108 a10 10 0 1 1 16 0 l-4 0 l0 6 l-8 0 l0 -6 Z" fill="#cbd5e1" />
                            </g>
                        </g>
                    </g>
                </g>
            </svg>
        </div>
    </section>
</div>
<?php echo view('templates/myfooter.php'); ?>
