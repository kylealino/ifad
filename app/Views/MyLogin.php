<?php
// IFAD Portal sign-in: one framed sheet on a quiet background. Left: the
// portal and an index of its systems (Config\Systems, named and labelled as
// on the portal page: Live / In development), plus Ifa, the portal guide, with
// a few sign-in tips (wide screens only); right: the sign-in form (posts
// MyUsername / MyPassword to mylogin-auth). Both halves slide in on arrival
// and slide out when signing in.
$systems = config(\Config\Systems::class)->systems;
$error = session()->getFlashdata('mesyszicas_memsg_login');
// "A, B and C"
$andList = fn (array $names) => count($names) > 1 ? implode(', ', array_slice($names, 0, -1)) . ' and ' . end($names) : implode('', $names);
$byStage = ['live' => [], 'development' => [], 'integration' => []];
foreach ($systems as $s) {
    $byStage[ifad_stage($s)['key']][] = $s['short'];
}
$words = [1 => 'one', 'two', 'three', 'four', 'five', 'six', 'seven', 'eight', 'nine'];
$devCount = count($byStage['development']);
// e.g. "FODS and SORF are live today. Three more are in development, and IHRMIS is being connected."
$status = $andList($byStage['live']) . (count($byStage['live']) === 1 ? ' is' : ' are') . ' live today.';
$more = [];
if ($devCount) {
    $more[] = ucfirst($words[$devCount] ?? (string) $devCount) . ' more ' . ($devCount === 1 ? 'is' : 'are') . ' in development';
}
if ($byStage['integration']) {
    $more[] = $andList($byStage['integration']) . (count($byStage['integration']) === 1 ? ' is' : ' are') . ' being connected';
}
if ($more) {
    $status .= ' ' . implode(', and ', $more) . '. Preview them after signing in.';
}
// Ifa's tips here: plain text, escaped on output
$tips = [
    "Hi! I'm Ifa, your portal guide. Sign in once to open every system you have a role in.",
    $status,
    'After you sign in, the bell at the top shows what is waiting for your approval.',
    'Forgot your password? The system administrator can reset it for you.',
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Sign in · IFAD Portal</title>
  <link rel="shortcut icon" type="image/png" href="<?=base_url('assets/images/logos/fnrilogo.png')?>" />
  <link rel="stylesheet" href="<?=base_url('assets/css/styles.css')?>" />
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,500;0,9..144,600;1,9..144,500&display=swap" />
  <style>
    :root {
      --ink: #0b2a36; --ink-2: #0f3a48; --teal: #0f6f6f; --teal-hi: #5fc4bd;
      --paper: #ffffff; --bg: #e9eef1; --line: #e3e8ee; --text: #1e293b; --muted: #64748b;
      --serif: 'Fraunces', Georgia, 'Times New Roman', serif;
      --ease: cubic-bezier(.65, 0, .35, 1);
    }
    html, body { height: 100%; }
    body {
      margin: 0; color: var(--text); overflow-x: hidden;
      background:
        radial-gradient(circle at 1px 1px, rgba(11,42,54,.09) 1px, transparent 0) 0 0 / 22px 22px,
        linear-gradient(180deg, #eef2f4 0%, var(--bg) 100%);
    }
    .sheet-wrap { min-height: 100vh; display: flex; }
    .sheet { position: relative; width: 100%; min-height: 100vh; display: grid; grid-template-columns: minmax(0, 65fr) minmax(0, 35fr); overflow: hidden; transition: transform .7s var(--ease), opacity .6s ease; }

    /* ---- Left: the portal ---- */
    .panel { position: relative; display: flex; flex-direction: column; padding: clamp(2rem, 4vw, 3.5rem) clamp(2rem, 5vw, 5rem); color: #e6eef1; background: linear-gradient(160deg, var(--ink-2) 0%, var(--ink) 70%); overflow: hidden; transition: transform .7s var(--ease), opacity .6s ease; }
    .panel::before { content: ""; position: absolute; inset: 0; background: linear-gradient(90deg, rgba(255,255,255,.035) 1px, transparent 1px) 0 0 / 56px 100%; pointer-events: none; }
    .panel::after { content: ""; position: absolute; left: 0; top: 2.5rem; width: 4px; height: 64px; background: var(--teal-hi); border-radius: 0 4px 4px 0; }
    .panel > * { position: relative; z-index: 1; width: 100%; max-width: 640px; }
    .panel::after { top: clamp(2rem, 4vw, 3.5rem); }
    .agency { display: flex; align-items: center; gap: .7rem; font-size: .8rem; color: rgba(230,238,241,.75); }
    .agency img { width: 38px; height: 38px; object-fit: contain; background: #fff; border-radius: 10px; padding: 4px; }
    .agency strong { display: block; color: #fff; font-size: .86rem; letter-spacing: .02em; }
    .title { margin: auto 0 0; padding-top: 2.5rem; }
    .kicker { font-size: .7rem; font-weight: 600; letter-spacing: .18em; text-transform: uppercase; color: var(--teal-hi); }
    .title h1 { font-family: var(--serif); font-weight: 500; font-size: clamp(3rem, 4.2vw, 4.2rem); line-height: 1.02; letter-spacing: -.015em; color: #fff; margin: .7rem 0 .8rem; }
    .title h1 em { font-style: italic; color: var(--teal-hi); }
    .title p { margin: 0; max-width: 430px; font-size: .9rem; line-height: 1.55; color: rgba(230,238,241,.78); }

    .index { margin-top: 2.5rem; }
    .index-head { display: flex; justify-content: space-between; font-size: .66rem; font-weight: 700; letter-spacing: .16em; text-transform: uppercase; color: rgba(230,238,241,.5); padding-bottom: .55rem; border-bottom: 1px solid rgba(255,255,255,.14); }
    .index ol { list-style: none; margin: 0; padding: 0; }
    .index li { display: grid; grid-template-columns: 2.1rem minmax(0, 1fr) auto; align-items: center; gap: .9rem; padding: .62rem 0; border-bottom: 1px solid rgba(255,255,255,.08); transition: transform .6s var(--ease), opacity .5s ease; transition-delay: calc(var(--i) * 70ms); }
    .index-no { font-family: var(--serif); font-size: 1.05rem; color: var(--teal-hi); font-variant-numeric: tabular-nums; }
    .index-name { min-width: 0; }
    .index-name strong { display: block; font-size: .9rem; font-weight: 600; color: #fff; }
    .index-name small { display: block; font-size: .74rem; color: rgba(230,238,241,.6); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .index-state { display: inline-flex; align-items: center; gap: .4rem; font-size: .66rem; font-weight: 700; letter-spacing: .1em; text-transform: uppercase; color: rgba(230,238,241,.5); white-space: nowrap; }
    .index-state::before { content: ""; width: 7px; height: 7px; border-radius: 50%; border: 1.5px solid currentColor; }
    .index li.is-live .index-state { color: #7ee2b8; }
    .index li.is-live .index-state::before { background: currentColor; box-shadow: 0 0 0 3px rgba(126,226,184,.18); }
    .index li.is-soon .index-name strong { color: rgba(255,255,255,.85); }
    .index-name strong .ti { font-size: .95rem; color: var(--teal-hi); margin-right: .15rem; vertical-align: -1px; }
    /* same wording and colours as the portal: Live (green), In development (amber, pulsing) */
    .index li.is-soon .index-state { color: #fbbf24; }
    .index li.is-soon .index-state::before { border: 0; background: #f59e0b; animation: ping 1.6s ease-out infinite; }
    @keyframes ping { 0% { box-shadow: 0 0 0 0 rgba(245,158,11,.55); } 80%, 100% { box-shadow: 0 0 0 6px rgba(245,158,11,0); } }
    /* Integration in progress (an existing system being connected): blue */
    .index li.is-int .index-state { color: #93c5fd; }
    .index li.is-int .index-state::before { background: #60a5fa; animation-name: ping-int; }
    @keyframes ping-int { 0% { box-shadow: 0 0 0 0 rgba(96,165,250,.55); } 80%, 100% { box-shadow: 0 0 0 6px rgba(96,165,250,0); } }
    .values { display: flex; flex-wrap: wrap; gap: .4rem 1.1rem; margin-top: 1rem; font-size: .78rem; font-weight: 600; color: rgba(230,238,241,.85); }
    .values span { display: inline-flex; align-items: center; gap: .3rem; }
    .values .ti { color: var(--teal-hi); font-size: .95rem; }

    /* The systems list and Ifa, the portal guide, side by side: Ifa stands
       beside the list, feet level with its last row (wide screens only) */
    .panel > .systems { max-width: none; display: flex; align-items: flex-end; gap: clamp(2.5rem, 4vw, 4.5rem); margin-top: 2.5rem; }
    .systems .index { flex: 0 1 640px; min-width: 0; margin-top: 0; }
    .guide { flex: 0 0 250px; display: none; flex-direction: column; align-items: center; gap: .6rem; padding-bottom: .2rem; transition: transform .7s var(--ease), opacity .6s ease; transition-delay: .45s; }
    @media (min-width: 1440px) { .guide { display: flex; } }
    .bubble { position: relative; width: 100%; padding: .8rem .95rem .65rem; border-radius: 14px; background: #fff; color: var(--text); box-shadow: 0 14px 34px -12px rgba(0,0,0,.55); }
    .bubble::after { content: ""; position: absolute; left: 50%; bottom: -7px; margin-left: -8px; border: 8px solid transparent; border-top-color: #fff; border-bottom: 0; }
    .bubble p { margin: 0; min-height: 4.35em; font-size: .82rem; line-height: 1.45; }
    .bubble p.is-in { animation: tipIn .3s ease-out; }
    @keyframes tipIn { from { opacity: 0; transform: translateY(4px); } }
    .dots { display: flex; gap: 4px; margin-top: .45rem; }
    .dots i { width: 6px; height: 6px; border-radius: 50%; background: #d6dee6; transition: width .2s ease, background .2s ease; }
    .dots i.is-on { width: 14px; border-radius: 3px; background: var(--teal); }
    .mascot { border: 0; background: none; padding: 0; line-height: 0; cursor: pointer; border-radius: 12px; }
    .mascot:focus-visible { outline: 2px solid var(--teal-hi); outline-offset: 3px; }
    .mascot svg { display: block; width: 132px; height: 157px; overflow: visible; }
    .mascot .m-body { animation: bob 3.2s ease-in-out infinite; }
    .mascot .m-shadow { fill: rgba(0,0,0,.3); transform-box: fill-box; transform-origin: center; animation: shadow 3.2s ease-in-out infinite; }
    .mascot .m-eyes { transform-box: fill-box; transform-origin: center; animation: blink 4.5s infinite; }
    .mascot .m-wave { transform-origin: 106px 112px; animation: wave 2.6s ease-in-out infinite; }
    .mascot .m-bulb { animation: glow 1.8s ease-in-out infinite; }
    @keyframes bob { 50% { transform: translateY(-5px); } }
    @keyframes shadow { 50% { transform: scaleX(.85); opacity: .7; } }
    @keyframes blink { 0%, 92%, 100% { transform: scaleY(1); } 95% { transform: scaleY(.1); } }
    @keyframes wave { 0%, 60%, 100% { transform: rotate(0); } 15%, 45% { transform: rotate(-14deg); } 30% { transform: rotate(10deg); } }
    @keyframes glow { 50% { fill: #fde68a; } }
    .is-arriving .guide { transform: translateY(16px); opacity: 0; }
    .is-leaving .guide { opacity: 0; transition-delay: 0s; }
    .panel-foot { margin-top: auto; padding-top: 2.5rem; font-size: .72rem; color: rgba(230,238,241,.5); }

    /* ---- Right: sign in ---- */
    .form-side { display: flex; flex-direction: column; padding: clamp(2rem, 4vw, 3.5rem) clamp(2rem, 4vw, 4rem); background: transparent; transition: transform .7s var(--ease), opacity .55s ease; }
    .form-tag { display: inline-flex; align-items: center; gap: .35rem; margin-bottom: 1.1rem; padding: .3rem .7rem; border: 1px solid #d5dde6; border-radius: 999px; background: #fff; font-size: .7rem; font-weight: 600; letter-spacing: .06em; text-transform: uppercase; color: var(--muted); }
    .form-tag .ti { color: var(--teal); font-size: .9rem; }
    .form-body { margin: auto 0; width: 100%; max-width: 380px; align-self: center; }
    .form-body h2 { font-family: var(--serif); font-weight: 500; font-size: 2rem; letter-spacing: -.01em; color: var(--ink); margin: 0 0 .35rem; }
    .form-sub { margin: 0 0 1.75rem; font-size: .88rem; color: var(--muted); }
    .alert-line { display: flex; gap: .55rem; align-items: flex-start; padding: .7rem .85rem; margin-bottom: 1.25rem; border-left: 3px solid #dc2626; border-radius: 0 10px 10px 0; background: #fef2f2; color: #991b1b; font-size: .85rem; }
    .alert-line .ti { font-size: 1.05rem; margin-top: .05rem; }
    .fld { margin-bottom: 1.1rem; }
    .fld label { display: block; font-size: .72rem; font-weight: 700; letter-spacing: .1em; text-transform: uppercase; color: var(--ink); margin-bottom: .45rem; }
    .fld-box { position: relative; }
    .fld-box > .ti { position: absolute; left: .95rem; top: 50%; transform: translateY(-50%); color: #94a3b8; font-size: 1.1rem; pointer-events: none; transition: color .2s ease; }
    .fld-box input { width: 100%; height: 50px; padding: 0 1rem 0 2.75rem; border: 1px solid #d5dde6; border-radius: 12px; background: #f8fafc; font-size: .95rem; color: var(--text); outline: none; transition: border-color .2s ease, box-shadow .2s ease, background .2s ease; }
    .fld-box input::placeholder { color: #a0aab8; }
    .fld-box input:focus { background: #fff; border-color: var(--teal); box-shadow: 0 0 0 4px rgba(15,111,111,.12); }
    .fld-box:focus-within > .ti { color: var(--teal); }
    #MyPassword { padding-right: 3rem; }
    .eye { position: absolute; right: .45rem; top: 50%; transform: translateY(-50%); border: 0; background: none; color: #94a3b8; padding: .4rem .5rem; border-radius: 8px; font-size: 1.1rem; }
    .eye:hover { color: var(--teal); }
    .submit { position: relative; overflow: hidden; width: 100%; height: 52px; margin-top: .6rem; border: 0; border-radius: 12px; background: var(--ink); color: #fff; font-weight: 600; font-size: .95rem; letter-spacing: .02em; display: flex; align-items: center; justify-content: space-between; padding: 0 1.1rem 0 1.25rem; transition: background .2s ease; }
    .submit::after { content: ""; position: absolute; inset: 0; background: var(--teal); transform: translateX(-101%); transition: transform .45s var(--ease); }
    .submit > * { position: relative; z-index: 1; }
    .submit:hover::after, .submit:focus-visible::after { transform: none; }
    .submit .ti { display: grid; place-items: center; width: 32px; height: 32px; border-radius: 9px; background: rgba(255,255,255,.14); font-size: 1.05rem; transition: transform .25s ease; }
    .submit:hover .ti { transform: translateX(3px); }
    .submit:disabled { opacity: .85; }
    .help { margin: 1.4rem 0 0; padding-top: 1.1rem; border-top: 1px dashed var(--line); font-size: .8rem; color: var(--muted); display: flex; gap: .45rem; align-items: center; }
    .help .ti { color: var(--teal); font-size: 1rem; }
    .form-foot { font-size: .72rem; color: var(--muted); text-align: center; }
    .shake { animation: shake .35s ease; }
    @keyframes shake { 25% { transform: translateX(-6px); } 75% { transform: translateX(6px); } }

    /* ---- Arrive / leave ---- */
    .is-arriving .sheet { transform: translateY(18px) scale(.985); opacity: 0; }
    .is-arriving .panel { transform: translateX(-60px); opacity: 0; }
    .is-arriving .index li { transform: translateX(-30px); opacity: 0; }
    .is-arriving .form-side { transform: translateX(60px); opacity: 0; }
    .panel { transition-delay: .1s; }
    .form-side { transition-delay: .18s; }
    .is-leaving .index li { transform: translateX(-30px); opacity: 0; transition-delay: calc((4 - var(--i)) * 45ms); }
    .is-leaving .form-side { transform: translateX(110%); opacity: 0; transition-delay: 0s; }
    .is-leaving .panel { transform: translateX(-110%); opacity: 0; transition-delay: .2s; }
    .is-leaving .sheet { transform: scale(.97); opacity: 0; transition-delay: .35s; }

    @media (max-width: 991.98px) {
      .sheet-wrap { justify-content: flex-start; padding: 0; }
      .sheet { grid-template-columns: minmax(0, 1fr); min-height: 100vh; border-radius: 0; box-shadow: none; }
      .panel { padding: 1.5rem 1.25rem 1.6rem; }
      .panel::after { top: 1.5rem; height: 40px; }
      .title { margin-top: 1.25rem; padding-top: 0; }
      .title h1 { font-size: 2.3rem; }
      .systems, .panel-foot { display: none; }
      .form-side { padding: 1.75rem 1.25rem 1.5rem; }
      .form-body { margin: 0 0 2rem; max-width: none; }
      .is-arriving .panel, .is-leaving .panel { transform: translateY(-40px); }
      .is-arriving .form-side, .is-leaving .form-side { transform: translateY(40px); }
    }
    @media (prefers-reduced-motion: reduce) {
      *, *::before, *::after { transition-duration: .01ms !important; transition-delay: 0s !important; animation-duration: .01ms !important; }
    }
  </style>
</head>
<body>
  <div class="sheet-wrap is-arriving" id="login">
    <div class="sheet">
      <section class="panel">
        <div class="agency">
          <img src="<?=base_url('assets/images/logos/fnrilogo.png')?>" alt="DOST-FNRI logo">
          <span><strong>DOST-FNRI</strong>Food and Nutrition Research Institute</span>
        </div>

        <div class="title">
          <div class="kicker">Integrated Finance and Administrative Division</div>
          <h1>IFAD <em>Portal</em></h1>
          <p>The division's single sign-in for finance, document requests, facilities, quality and human resources. Every request moves along one clear, trackable path.</p>
          <div class="values">
            <span><i class="ti ti-plug-connected"></i> Integrated</span>
            <span><i class="ti ti-bolt"></i> Efficient</span>
            <span><i class="ti ti-eye-check"></i> Transparent</span>
            <span><i class="ti ti-discount-check"></i> Quality-Driven</span>
          </div>
        </div>

        <!-- the systems list, with Ifa standing beside it (wide screens) -->
        <div class="systems">
        <div class="index">
          <div class="index-head"><span>One sign-in for every system</span><span>Status</span></div>
          <ol>
            <?php $i = 0; foreach ($systems as $s): $live = $s['status'] === 'live'; $stage = ifad_stage($s); ?>
              <li class="<?= $live ? 'is-live' : 'is-soon ' . $stage['class'] ?>" style="--i:<?= $i++ ?>">
                <span class="index-no"><?= sprintf('%02d', $s['no']) ?></span>
                <span class="index-name">
                  <strong><i class="ti <?= $s['icon'] ?>"></i> <?= esc($s['short']) ?></strong>
                  <small><?= esc($s['name']) ?></small>
                </span>
                <span class="index-state"><?= esc($stage['label']) ?></span>
              </li>
            <?php endforeach; ?>
          </ol>
        </div>

        <!-- Ifa, the portal guide (wide screens): a speech bubble of sign-in tips -->
        <div class="guide" aria-live="polite">
          <div class="bubble">
            <p id="guideTip"><?= esc($tips[0]) ?></p>
            <span class="dots" aria-hidden="true"><?php foreach ($tips as $n => $t): ?><i class="<?= $n === 0 ? 'is-on' : '' ?>"></i><?php endforeach; ?></span>
          </div>
          <button type="button" class="mascot" id="guideMascot" aria-label="Ifa, the portal guide: show the next tip"><?= view('portal/ifa-mascot') ?></button>
        </div>
        </div>

        <div class="panel-foot">Department of Science and Technology &middot; Food and Nutrition Research Institute</div>
      </section>

      <main class="form-side">
        <div class="form-body">
          <span class="form-tag"><i class="ti ti-shield-lock"></i> Authorized personnel only</span>
          <h2>Sign in</h2>
          <p class="form-sub">Use your IFAD Portal account to continue.</p>

          <?php if ($error): ?>
            <div class="alert-line" role="alert"><i class="ti ti-alert-circle"></i><span><?= esc($error) ?></span></div>
          <?php endif; ?>

          <form action="<?=site_url('mylogin-auth');?>" method="post" id="loginForm" novalidate>
            <div class="fld">
              <label for="MyUsername">Username</label>
              <div class="fld-box">
                <i class="ti ti-user"></i>
                <input type="text" name="MyUsername" id="MyUsername" placeholder="Enter your username" autocomplete="username" autofocus>
              </div>
            </div>

            <div class="fld">
              <label for="MyPassword">Password</label>
              <div class="fld-box">
                <i class="ti ti-lock"></i>
                <input type="password" name="MyPassword" id="MyPassword" placeholder="Enter your password" autocomplete="current-password">
                <button type="button" class="eye" id="loginEye" aria-label="Show password" title="Show password"><i class="ti ti-eye"></i></button>
              </div>
            </div>

            <button type="submit" class="submit" id="loginBtn"><span>Sign in</span><i class="ti ti-arrow-right"></i></button>
          </form>

          <p class="help"><i class="ti ti-info-circle"></i> Forgot your password? Ask the system administrator.</p>
        </div>

        <div class="form-foot">&copy; <?= date('Y') ?> IFAD Portal &mdash; Integrated Finance and Administrative Division</div>
      </main>
    </div>
  </div>

  <script>
    (function () {
      var root = document.getElementById('login');
      var form = document.getElementById('loginForm');
      var user = document.getElementById('MyUsername');
      var pass = document.getElementById('MyPassword');
      var eye = document.getElementById('loginEye');
      var btn = document.getElementById('loginBtn');
      var reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

      // slide in on arrival
      requestAnimationFrame(function () { requestAnimationFrame(function () { root.classList.remove('is-arriving'); }); });

      // Ifa's tips: next on a click; they also turn every 8 s (not with reduced motion)
      var tips = <?= json_encode($tips, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
      var tipEl = document.getElementById('guideTip');
      var dots = document.querySelectorAll('.dots i');
      var at = 0, tipTimer = null;
      function showTip(n) {
        at = (n + tips.length) % tips.length;
        tipEl.classList.remove('is-in'); void tipEl.offsetWidth;
        tipEl.textContent = tips[at];
        tipEl.classList.add('is-in');
        dots.forEach(function (d, i) { d.classList.toggle('is-on', i === at); });
      }
      document.getElementById('guideMascot').addEventListener('click', function () {
        if (tipTimer) { clearInterval(tipTimer); tipTimer = null; }
        showTip(at + 1);
      });
      if (!reduced) { tipTimer = setInterval(function () { if (!document.hidden) { showTip(at + 1); } }, 8000); }

      eye.addEventListener('click', function () {
        var show = pass.type === 'password';
        pass.type = show ? 'text' : 'password';
        eye.innerHTML = '<i class="ti ' + (show ? 'ti-eye-off' : 'ti-eye') + '"></i>';
        eye.setAttribute('aria-label', show ? 'Hide password' : 'Show password');
        eye.title = eye.getAttribute('aria-label');
        pass.focus();
      });

      function shake(input) {
        var box = input.parentNode;
        box.classList.remove('shake'); void box.offsetWidth; box.classList.add('shake');
        input.focus();
      }

      // sign in: the form and the panel slide away, then the form posts
      var leaving = false;
      form.addEventListener('submit', function (e) {
        e.preventDefault();
        if (leaving) { return; }
        if (user.value.trim() === '') { shake(user); return; }
        if (pass.value === '') { shake(pass); return; }
        leaving = true;
        btn.disabled = true;
        btn.innerHTML = '<span>Signing in…</span><span class="spinner-border spinner-border-sm"></span>';
        root.classList.add('is-leaving');
        setTimeout(function () { form.submit(); }, reduced ? 0 : 850);
      });

      // back to this page with the browser's Back button: undo the slide-out
      window.addEventListener('pageshow', function (e) {
        if (!e.persisted) { return; }
        leaving = false;
        root.classList.remove('is-leaving');
        btn.disabled = false;
        btn.innerHTML = '<span>Sign in</span><i class="ti ti-arrow-right"></i>';
      });
    })();
  </script>
</body>
</html>
