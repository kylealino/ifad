<?php
// Module views read these off the shared renderer after including the header
$this->request = \Config\Services::request();
$this->db = \Config\Database::connect();
$this->session = session();
$this->cuser = $this->session->get('__xsys_myuserzicas__');

$user = fods_current_user();
$nav = fods_nav();
$current = fods_nav_current();
$role_name = $user['role_name'] !== '' ? $user['role_name'] : 'No role assigned';
$org_unit = implode(' · ', array_filter([$user['division'], $user['section']], fn ($v) => $v !== '' && $v !== '-'));
// Pages outside the sidebar (e.g. Messages) name themselves
$page_title = $pageTitle ?? ($current['label'] ?? 'Home');

// Topbar badges: unread chats (Messages) and tickets with news (Helpdesk);
// fods-inbox.js keeps them fresh
$inbox_unread = 0;
$helpdesk_unread = 0;
try {
    $inbox_unread = model('App\Models\MyMessagesModel')->unread_total();
} catch (\Throwable $e) {
    log_message('error', 'Messages badge: ' . $e->getMessage());
}
try {
    $helpdesk_unread = model('App\Models\MyHelpdeskModel')->unread_total();
} catch (\Throwable $e) {
    log_message('error', 'Helpdesk badge: ' . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en" dir="ltr" data-bs-theme="light" data-color-theme="Blue_Theme">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link rel="shortcut icon" type="image/png" href="<?=base_url('assets/images/logos/fnrilogo.png')?>" />
  <title><?= esc($page_title . ' · FODS') ?></title>
  <link rel="stylesheet" href="<?=base_url('assets/css/styles.css')?>" />
  <link rel="stylesheet" href="<?=base_url('assets/css/fods.css?v=8')?>" />
  <script>
    // Apply the remembered collapsed sidebar before first paint (no flicker)
    try { if (localStorage.getItem('fods.sidebar.collapsed') === '1') document.documentElement.classList.add('fods-collapsed'); } catch (e) {}
  </script>
</head>
<body<?= !empty($bodyClass) ? ' class="' . esc($bodyClass, 'attr') . '"' : '' ?>>
  <div class="fods-overlay" id="fodsOverlay"></div>

  <!-- Sidebar -->
  <aside class="fods-sidebar" id="fodsSidebar">
    <div class="fods-brand">
      <a href="<?=site_url('mydashboard');?>">
        <img src="<?=base_url('assets/images/logos/fnrilogo.png')?>" alt="" />
        <span class="fods-brand-text">FODS</span>
      </a>
      <button type="button" class="fods-sidebar-close d-xl-none" id="fodsSidebarClose" aria-label="Close menu"><i class="ti ti-x"></i></button>
    </div>

    <nav class="fods-nav" aria-label="Main">
      <?php foreach ($nav as $group): ?>
        <div class="fods-nav-label"><?= esc($group['label']) ?></div>
        <ul class="fods-nav-list">
          <?php foreach ($group['items'] as $item): ?>
            <li>
              <a class="fods-nav-link <?= $item['active'] ? 'active' : '' ?>" href="<?= $item['url'] ?>" title="<?= esc($item['label'], 'attr') ?>" <?= $item['active'] ? 'aria-current="page"' : '' ?>>
                <i class="ti <?= $item['icon'] ?>"></i>
                <span class="fods-nav-text"><?= esc($item['label']) ?></span>
                <?php if (!empty($item['badge'])): ?>
                  <span class="badge bg-danger rounded-pill" id="<?= $item['badge'] ?>" style="display:none;">0</span>
                <?php endif; ?>
              </a>
            </li>
          <?php endforeach; ?>
        </ul>
      <?php endforeach; ?>
    </nav>

    <div class="fods-sidebar-foot">
      <button type="submit" form="fodsLogoutForm" class="fods-nav-link fods-logout" title="Log out">
        <i class="ti ti-logout"></i>
        <span class="fods-nav-text">Log out</span>
      </button>
    </div>
  </aside>

  <div class="fods-page">
    <!-- Topbar -->
    <header class="fods-topbar">
      <button type="button" class="fods-icon-btn" id="fodsSidebarToggle" aria-label="Toggle menu"><i class="ti ti-menu-2"></i></button>

      <div class="fods-topbar-end ms-auto">
        <!-- Messages (chats), then the Helpdesk (tickets), before the user details -->
        <div class="dropdown">
          <button type="button" class="fods-topbar-btn" id="fodsInboxBtn" data-inbox="chats" data-url="<?= site_url('mymessages') ?>" data-bs-toggle="dropdown" data-bs-offset="0,8" aria-expanded="false" aria-label="Messages" title="Messages">
            <i class="ti ti-message-circle"></i>
            <span class="fods-topbar-badge" id="fodsInboxBadge"<?= $inbox_unread > 0 ? '' : ' hidden' ?>><?= $inbox_unread > 99 ? '99+' : $inbox_unread ?></span>
          </button>

          <div class="dropdown-menu dropdown-menu-end fods-inbox-menu">
            <div class="fods-inbox-head">
              <strong>Messages</strong>
              <a href="<?= site_url('mymessages?meaction=MAIN&new=1') ?>" class="fods-inbox-link"><i class="ti ti-message-plus"></i> New message</a>
            </div>
            <div class="fods-inbox-body" id="fodsInboxBody">
              <div class="fods-inbox-empty"><span class="spinner-border spinner-border-sm"></span></div>
            </div>
            <div class="fods-inbox-foot">
              <a href="<?= site_url('mymessages?meaction=MAIN') ?>" class="btn btn-sm btn-fods-ghost w-100 py-2">Open messages</a>
            </div>
          </div>
        </div>

        <div class="dropdown">
          <button type="button" class="fods-topbar-btn" id="fodsHelpdeskBtn" data-inbox="tickets" data-url="<?= site_url('myhelpdesk') ?>" data-bs-toggle="dropdown" data-bs-offset="0,8" aria-expanded="false" aria-label="Helpdesk" title="Helpdesk">
            <i class="ti ti-lifebuoy"></i>
            <span class="fods-topbar-badge" id="fodsHelpdeskBadge"<?= $helpdesk_unread > 0 ? '' : ' hidden' ?>><?= $helpdesk_unread > 99 ? '99+' : $helpdesk_unread ?></span>
          </button>

          <div class="dropdown-menu dropdown-menu-end fods-inbox-menu">
            <div class="fods-inbox-head">
              <strong>Helpdesk</strong>
              <a href="<?= site_url('myhelpdesk?meaction=MAIN&tab=tickets&new=1') ?>" class="fods-inbox-link"><i class="ti ti-plus"></i> New ticket</a>
            </div>
            <div class="fods-inbox-body" id="fodsHelpdeskBody">
              <div class="fods-inbox-empty"><span class="spinner-border spinner-border-sm"></span></div>
            </div>
            <div class="fods-inbox-foot">
              <a href="<?= site_url('myhelpdesk?meaction=MAIN') ?>" class="btn btn-sm btn-fods-ghost w-100 py-2">Open helpdesk</a>
            </div>
          </div>
        </div>

        <div class="dropdown">
          <button type="button" class="fods-profile" data-bs-toggle="dropdown" data-bs-offset="0,8" aria-expanded="false">
            <span class="fods-avatar tone-teal"><?= esc(fods_initials($user['full_name'])) ?></span>
            <span class="fods-profile-text d-none d-md-flex">
              <strong><?= esc($user['full_name']) ?></strong>
              <small><?= esc($role_name) ?></small>
            </span>
            <i class="ti ti-chevron-down"></i>
          </button>

          <div class="dropdown-menu dropdown-menu-end fods-profile-menu">
            <div class="fods-profile-head">
              <span class="fods-avatar tone-teal"><?= esc(fods_initials($user['full_name'])) ?></span>
              <div style="min-width:0">
                <div class="fods-profile-name"><?= esc($user['full_name']) ?></div>
                <div class="fods-profile-meta">@<?= esc($user['username']) ?></div>
              </div>
            </div>
            <div class="fods-profile-rows">
              <div><span>Role</span><span><?= esc($role_name) ?></span></div>
              <?php if ($user['position'] !== '' && $user['position'] !== '-'): ?>
                <div><span>Position</span><span><?= esc($user['position']) ?></span></div>
              <?php endif; ?>
              <?php if ($org_unit !== ''): ?>
                <div><span>Office</span><span><?= esc($org_unit) ?></span></div>
              <?php endif; ?>
            </div>
            <div class="fods-profile-foot">
              <form action="<?= site_url('mylogout'); ?>" method="post" id="fodsLogoutForm">
                <?= csrf_field(); ?>
                <button type="submit" class="btn btn-sm btn-fods-ghost w-100 py-2"><i class="ti ti-logout me-1"></i>Log out</button>
              </form>
            </div>
          </div>
        </div>
      </div>
    </header>

    <main class="fods-content">
