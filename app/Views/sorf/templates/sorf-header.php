<?php
// SORF page top inside the IFAD Portal: the portal's own header and sidebar
// (templates/myheader.php, SORF menu from Config\Navigation::$sorf) plus what
// SORF's pages need - its base URL and CSRF token for drf-app.js, and its
// stylesheet, recoloured to the portal's palette.
// Pass 'pageTitle' and 'nav' (the page's SORF menu key).
$headExtra = '
  <meta name="drf-siteurl" content="' . esc(rtrim(site_url('sorf'), '/') . '/', 'attr') . '" />
  <meta name="drf-csrf" content="' . esc(csrf_hash(), 'attr') . '" />
  <link rel="stylesheet" href="' . base_url('assets/sorf/css/drf.css?v=22') . '" />
  <style>
    /* SORF in the portal: its accents follow the portal palette and its sticky
       panels sit under the portal topbar */
    :root { --drf-navy: var(--fods-teal); --drf-navy-2: var(--fods-teal-2); --drf-navy-soft: var(--fods-teal-soft); --drf-bg: var(--fods-bg); --drf-topbar-h: var(--fods-topbar-h); }
    .ifad-sorf.drf-content { padding: 0; }
  </style>';

echo view('templates/myheader.php', [
    'ifadSystem' => 'sorf',
    'pageTitle' => $pageTitle ?? 'Requests',
    'sorfNav' => $nav ?? '',
    'headExtra' => $headExtra,
]);
?>
<div class="drf-body drf-content ifad-sorf">
