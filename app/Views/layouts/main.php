<?php
  // Appends the file's last-modified time as a query string, so the browser
  // treats every edit as a brand-new URL and fetches it automatically on
  // the next normal page load — no more manual hard refresh (Ctrl+Shift+R)
  // needed to see CSS/JS changes.
  function assetVer($relativePath) {
      $full = FCPATH . ltrim($relativePath, '/');
      return base_url($relativePath) . (file_exists($full) ? '?v=' . filemtime($full) : '');
  }
  $currentUri = trim(uri_string(), '/');
  function navActive($seg){
      $uri = trim(uri_string(), '/');
      if ($seg === 'safety') {
          return $uri === 'safety' ? 'active' : '';
      }
      if ($seg === 'safety/guard-dashboard') {
          return $uri === 'safety/guard-dashboard' ? 'active' : '';
      }
      // "All Personnel" / "All Tools" are the base/index link sitting
      // alongside their own sub-routes (personnel/drivers, tools/consumable,
      // etc.) in the same submenu — prefix-matching here made the base link
      // stay highlighted even when a specific sub-route was selected, so
      // both looked active at once. These need an exact match only.
      if ($seg === 'personnel' || $seg === 'tools') {
          return $uri === $seg ? 'active' : '';
      }
      if ($seg === 'vehicles') {
          return $uri === 'vehicles' ? 'active' : '';
      }
      return $uri === $seg || strpos($uri, $seg . '/') === 0 ? 'active' : '';
  }
  $fullName = session()->get('full_name') ?? 'System Admin';
  $role     = session()->get('role') ?? 'Operations';
  $initials = strtoupper(substr($fullName,0,1) . (strpos($fullName,' ') !== false ? substr($fullName, strpos($fullName,' ')+1,1) : ''));
  $userPhoto = session()->get('photo');
  $userPhotoUrl = $userPhoto ? base_url('uploads/' . $userPhoto) : null;
  $topbarUnreadCount = session()->get('isLoggedIn') ? (new \App\Models\NotificationModel())->getUnreadCountForRole((string) $role) : 0;
  $logoAsset = null;
  $logoCandidates = [
      // Temporary/unofficial logo — takes priority until a real one replaces it.
      FCPATH . 'images/UBRA LOGO (no background).png',
      FCPATH . 'uploads/logo.png',
      FCPATH . 'uploads/logo.jpg',
      FCPATH . 'uploads/logo.jpeg',
      FCPATH . 'uploads/logo.webp',
      FCPATH . 'uploads/logo.svg',
      FCPATH . 'Assets/images/logo.png',
      FCPATH . 'Assets/images/logo.jpg',
      FCPATH . 'Assets/images/logo.jpeg',
      FCPATH . 'Assets/images/logo.webp',
      FCPATH . 'Assets/images/logo.svg',
  ];
  foreach ($logoCandidates as $candidate) {
      if (file_exists($candidate)) {
          $logoAsset = str_replace('\\', '/', str_replace(FCPATH, base_url(), $candidate));
          break;
      }
  }
  $aiIconAsset = null;
  $aiIconCandidates = [
      FCPATH . 'images/Ubraicon.jpg',
      FCPATH . 'uploads/ai-icon.png',
      FCPATH . 'uploads/ai-icon.jpg',
      FCPATH . 'uploads/ai-icon.jpeg',
      FCPATH . 'uploads/ai-icon.webp',
      FCPATH . 'uploads/ai-icon.svg',
      FCPATH . 'Assets/images/ai-icon.png',
      FCPATH . 'Assets/images/ai-icon.jpg',
      FCPATH . 'Assets/images/ai-icon.jpeg',
      FCPATH . 'Assets/images/ai-icon.webp',
      FCPATH . 'Assets/images/ai-icon.svg',
      FCPATH . 'images/ai-icon.png',
      FCPATH . 'images/ai-icon.jpg',
  ];
  foreach ($aiIconCandidates as $candidate) {
      if (file_exists($candidate)) {
          $aiIconAsset = str_replace('\\', '/', str_replace(FCPATH, base_url(), $candidate));
          break;
      }
  }
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <script>
    // Applied before any CSS paints, so the page never flashes light-then-dark.
    if (localStorage.getItem('theme') === 'dark') {
      document.documentElement.setAttribute('data-theme', 'dark');
    }
  </script>
  <title>UBRA | <?= esc($title ?? 'Dashboard') ?></title>
  <meta name="csrf-token-name" content="<?= esc(csrf_token(), 'attr') ?>">
  <meta name="csrf-token-value" content="<?= esc(csrf_hash(), 'attr') ?>">
  <meta name="csrf-header-name" content="<?= esc(csrf_header(), 'attr') ?>">
  <link rel="preload" href="<?= assetVer('fonts/bebas-neue/bebas-neue-latin.woff2') ?>" as="font" type="font/woff2">
  <link rel="stylesheet" href="<?= assetVer('fonts/bebas-neue/bebas-neue.css') ?>">
  <link rel="stylesheet" href="<?= assetVer('icons/bootstrap-icons/bootstrap-icons.css') ?>">
  <link rel="stylesheet" href="<?= assetVer('Assets/css/base.css') ?>">
  <?php if (!empty($pageCss)): ?><link rel="stylesheet" href="<?= assetVer('Assets/css/'.$pageCss) ?>"><?php endif; ?>
  <?php if (!empty($logoAsset)): ?><link rel="icon" href="<?= esc($logoAsset) ?>">
  <?php endif; ?>
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
</head>
<body>
<div class="app-wrapper">

  <!-- ============================= SIDEBAR ============================= -->
  <aside class="sidebar">
    <div class="sidebar-logo">
      <div class="brand">
        <?php if (!empty($logoAsset)): ?>
          <img class="sidebar-logo-image" src="<?= esc($logoAsset) ?>" alt="System logo">
        <?php else: ?>
          <div class="mark"><i class="bi bi-shield-fill"></i></div>
        <?php endif; ?>
        <div class="txt"><b>UBRA</b><small>FOUNDATION UNIVERSITY</small></div>
      </div>
      <button type="button" class="sidebar-toggle-btn" onclick="toggleSidebar()" aria-label="Toggle sidebar" title="Toggle sidebar">
        <i class="bi bi-chevron-double-left"></i>
      </button>
    </div>

    <?php
      $userRole              = strtolower((string) (session()->get('role') ?? ''));
      $isSecurityHead        = $userRole === 'security';
      $isToolsHead           = $userRole === 'tools';
      $isFacilitiesSupervisor = $userRole === 'facilities';
      $isJanitorialSupervisor = $userRole === 'janitorial';
      $isFullAccess          = !$isSecurityHead && !$isToolsHead && !$isFacilitiesSupervisor && !$isJanitorialSupervisor;
      $dashboardUrl          = $isSecurityHead ? 'security-dashboard'
        : ($isToolsHead ? 'tools-dashboard'
        : ($isFacilitiesSupervisor ? 'facilities-dashboard'
        : ($isJanitorialSupervisor ? 'janitorial-dashboard' : 'dashboard')));
    ?>
    <nav class="sidebar-nav">
      <a href="<?= base_url($dashboardUrl) ?>" class="<?= navActive($dashboardUrl) ?>" data-tooltip="Dashboard"><i class="bi bi-grid-1x2"></i> <span class="nav-label">Dashboard</span></a>

      <?php if ($isFullAccess || $isFacilitiesSupervisor): ?>
      <?php $isPersonnelSection = $currentUri === 'personnel' || strpos($currentUri, 'personnel/') === 0; ?>
      <div class="nav-parent-group <?= $isPersonnelSection ? 'open' : '' ?>">
        <a href="<?= base_url('personnel') ?>" class="nav-parent-link <?= $isPersonnelSection ? 'active open' : '' ?>" data-personnel-toggle data-tooltip="Personnel Management">
          <i class="bi bi-people"></i>
          <span class="nav-label">Personnel Management</span>
          <i class="bi bi-chevron-down nav-parent-caret"></i>
        </a>
        <div class="nav-submenu" id="personnel-submenu">
          <a href="<?= base_url('personnel') ?>" class="<?= navActive('personnel') ?>"><i class="bi bi-people-fill"></i> <span class="nav-label">All Personnel</span></a>
          <a href="<?= base_url('personnel/drivers') ?>" class="<?= navActive('personnel/drivers') ?>"><i class="bi bi-person-vcard-fill"></i> <span class="nav-label">Drivers</span></a>
          <a href="<?= base_url('personnel/janitors') ?>" class="<?= navActive('personnel/janitors') ?>"><i class="bi bi-brush"></i> <span class="nav-label">Janitors</span></a>
          <a href="<?= base_url('personnel/carpentries') ?>" class="<?= navActive('personnel/carpentries') ?>"><i class="bi bi-hammer"></i> <span class="nav-label">Carpentries Shop</span></a>
          <a href="<?= base_url('personnel/maintenance') ?>" class="<?= navActive('personnel/maintenance') ?>"><i class="bi bi-wrench"></i> <span class="nav-label">Maintenance</span></a>
          <a href="<?= base_url('personnel/construction-workers') ?>" class="<?= navActive('personnel/construction-workers') ?>"><i class="bi bi-cone-striped"></i> <span class="nav-label">Construction Workers</span></a>
        </div>
      </div>
      <?php endif; ?>

      <?php if ($isFullAccess || $isSecurityHead): ?>
      <?php $isVehicleSection = $currentUri === 'vehicles' || $currentUri === 'gps' || $currentUri === 'travel'; ?>
      <div class="nav-parent-group <?= $isVehicleSection ? 'open' : '' ?>">
        <a href="<?= base_url('vehicles') ?>" class="nav-parent-link <?= $isVehicleSection ? 'active open' : '' ?>" data-vehicle-toggle data-tooltip="Vehicle Management">
          <i class="bi bi-truck"></i>
          <span class="nav-label">Vehicle Management</span>
          <i class="bi bi-chevron-down nav-parent-caret"></i>
        </a>
        <div class="nav-submenu" id="vehicle-submenu">
          <a href="<?= base_url('vehicles') ?>" class="<?= navActive('vehicles') ?>"><i class="bi bi-truck"></i> <span class="nav-label">Vehicle Management</span></a>
          <a href="<?= base_url('gps') ?>" class="<?= navActive('gps') ?>"><i class="bi bi-geo-alt-fill"></i> <span class="nav-label">GPS Tracker</span></a>
          <a href="<?= base_url('travel') ?>" class="<?= navActive('travel') ?>"><i class="bi bi-ticket-perforated"></i> <span class="nav-label">Trip Ticket</span></a>
        </div>
      </div>
      <?php endif; ?>

      <?php if ($isFullAccess || $isToolsHead || $isFacilitiesSupervisor): ?>
      <?php $isToolsSection = $currentUri === 'tools' || strpos($currentUri, 'tools/') === 0; ?>
      <div class="nav-parent-group <?= $isToolsSection ? 'open' : '' ?>">
        <a href="<?= base_url('tools') ?>" class="nav-parent-link <?= $isToolsSection ? 'active open' : '' ?>" data-tools-toggle data-tooltip="Tools Management">
          <i class="bi bi-tools"></i>
          <span class="nav-label">Tools Management</span>
          <i class="bi bi-chevron-down nav-parent-caret"></i>
        </a>
        <div class="nav-submenu" id="tools-submenu">
          <a href="<?= base_url('tools') ?>" class="<?= navActive('tools') ?>"><i class="bi bi-boxes"></i> <span class="nav-label">All Tools</span></a>
          <a href="<?= base_url('tools/power-tools') ?>" class="<?= navActive('tools/power-tools') ?>"><i class="bi bi-lightning-fill"></i> <span class="nav-label">Power Tools</span></a>
          <a href="<?= base_url('tools/consumable') ?>" class="<?= navActive('tools/consumable') ?>"><i class="bi bi-box-seam-fill"></i> <span class="nav-label">Consumable</span></a>
          <a href="<?= base_url('tools/sports-equipment') ?>" class="<?= navActive('tools/sports-equipment') ?>"><i class="bi bi-trophy-fill"></i> <span class="nav-label">Sports Equipment</span></a>
          <a href="<?= base_url('tools/borrowing') ?>" class="<?= navActive('tools/borrowing') ?>"><i class="bi bi-hand-index-thumb-fill"></i> <span class="nav-label">Borrowing</span></a>
        </div>
      </div>
      <?php endif; ?>

      <?php if ($isFullAccess || $isSecurityHead): ?>
      <a href="<?= base_url('safety') ?>" class="<?= navActive('safety') ?>" data-tooltip="Maintenance"><i class="bi bi-wrench-adjustable"></i> <span class="nav-label">Maintenance</span></a>
      <a href="<?= base_url('safety/guard-dashboard') ?>" class="<?= navActive('safety/guard-dashboard') ?>" data-tooltip="Guard"><i class="bi bi-shield-check"></i> <span class="nav-label">Guard</span></a>
      <?php endif; ?>

      <?php if ($isFullAccess || $isJanitorialSupervisor): ?>
      <a href="<?= base_url('janitorial') ?>" class="<?= navActive('janitorial') ?>" data-tooltip="Janitorial Monitoring"><i class="bi bi-brush"></i> <span class="nav-label">Janitorial Monitoring</span></a>
      <?php endif; ?>

      <?php if ($isFullAccess || $isSecurityHead || $isFacilitiesSupervisor || $isJanitorialSupervisor): ?>
      <a href="<?= base_url('calendar') ?>" class="<?= navActive('calendar') ?>" data-tooltip="Calendar"><i class="bi bi-calendar3"></i> <span class="nav-label">Calendar</span></a>
      <?php endif; ?>

      <a href="<?= base_url('notifications') ?>" class="<?= navActive('notifications') ?>" data-tooltip="Notifications"><i class="bi bi-bell"></i> <span class="nav-label">Notifications</span><span class="nav-count" id="navBellCount" <?= $topbarUnreadCount > 0 ? '' : 'style="display:none"' ?>><?= $topbarUnreadCount > 99 ? '99+' : (int) $topbarUnreadCount ?></span></a>
      <?php $isInfoHubSection = $currentUri === 'reports' || strpos($currentUri, 'maintenance-forms/') === 0; ?>
      <div class="nav-parent-group <?= $isInfoHubSection ? 'open' : '' ?>">
        <a href="<?= base_url('reports') ?>" class="nav-parent-link <?= $isInfoHubSection ? 'active open' : '' ?>" data-infohub-toggle data-tooltip="Information Hub">
          <i class="bi bi-folder2"></i>
          <span class="nav-label">Information Hub</span>
          <i class="bi bi-chevron-down nav-parent-caret"></i>
        </a>
        <div class="nav-submenu" id="infohub-submenu">
          <a href="<?= base_url('reports') ?>" class="<?= navActive('reports') ?>"><i class="bi bi-list-task"></i> <span class="nav-label">All Records</span></a>
          <?php if (!$isJanitorialSupervisor): ?>
          <a href="<?= base_url('maintenance-forms/facility') ?>" class="<?= navActive('maintenance-forms/facility') ?>"><i class="bi bi-clipboard2-check"></i> <span class="nav-label">Facility Checklist</span></a>
          <a href="<?= base_url('maintenance-forms/equipment-log') ?>" class="<?= navActive('maintenance-forms/equipment-log') ?>"><i class="bi bi-wrench-adjustable"></i> <span class="nav-label">Equipment Log</span></a>
          <a href="<?= base_url('maintenance-forms/aircon-log') ?>" class="<?= navActive('maintenance-forms/aircon-log') ?>"><i class="bi bi-snow2"></i> <span class="nav-label">Aircon Inspection Log</span></a>
          <a href="<?= base_url('maintenance-forms/vehicle-checklist') ?>" class="<?= navActive('maintenance-forms/vehicle-checklist') ?>"><i class="bi bi-truck"></i> <span class="nav-label">Vehicle Checklist</span></a>
          <?php endif; ?>
          <a href="<?= base_url('maintenance-forms/restroom') ?>" class="<?= navActive('maintenance-forms/restroom') ?>"><i class="bi bi-brush"></i> <span class="nav-label">Restroom Checklist</span></a>
        </div>
      </div>
      <div class="nav-sep"></div>
      <a href="<?= base_url('ubra') ?>" class="ai-link <?= navActive('ubra') ?>" data-tooltip="Mr. UBRA AI"><i class="bi bi-robot"></i> <span class="nav-label">Mr. UBRA AI</span> <span class="dot"></span></a>
      <?php if ($isFullAccess || $isSecurityHead): ?>
      <a href="<?= base_url('settings') ?>" class="<?= navActive('settings') ?>" data-tooltip="Settings"><i class="bi bi-gear"></i> <span class="nav-label">Settings</span></a>
      <?php endif; ?>
    </nav>

    <div class="sidebar-footer">
      <a href="<?= base_url('profile') ?>" data-tooltip="View Profile">
        <?php if ($userPhotoUrl): ?>
          <img class="av" src="<?= esc($userPhotoUrl) ?>" alt="<?= esc($fullName) ?>"
               onclick="event.preventDefault(); event.stopPropagation(); openAvatarPreview(this.src, '<?= esc($fullName, 'js') ?>');">
        <?php else: ?>
          <span class="av"><?= esc($initials) ?></span>
        <?php endif; ?>
        <span class="nav-label"><?= esc($fullName) ?><small>View Profile</small></span>
      </a>
      <?php if (!($isFullAccess || $isSecurityHead)): ?>
      <!-- Only for roles with no Settings page (Facilities, Tools Head,
           Janitorial Supervisor) — everyone else logs out from
           Settings → System, so it isn't duplicated under the profile. -->
      <a href="<?= site_url('logout') ?>" class="sidebar-logout-link" data-tooltip="Logout">
        <span class="av av-logout"><i class="bi bi-box-arrow-right"></i></span>
        <span class="nav-label">Logout</span>
      </a>
      <?php endif; ?>
    </div>
  </aside>

  <div class="mobile-nav-backdrop" onclick="toggleMobileNav(false)"></div>

  <!-- Avatar preview lightbox — clicking the sidebar profile photo opens
       this instead of navigating to /profile; clicking the name still
       navigates normally. -->
  <div class="avatar-lightbox" id="avatarLightbox" onclick="if (event.target === this) closeAvatarPreview();">
    <button type="button" class="avatar-lightbox-close" onclick="closeAvatarPreview()" aria-label="Close">
      <i class="bi bi-x-lg"></i>
    </button>
    <figure class="avatar-lightbox-figure">
      <img id="avatarLightboxImg" src="" alt="">
      <figcaption id="avatarLightboxCaption"></figcaption>
    </figure>
  </div>

  <!--MAIN CONTENT  -->
  <div class="main-content">
    <?php $showTopbar = $showTopbar ?? true; ?>
    <?php if ($showTopbar): ?>
    <header class="topbar">
      <div class="topbar-left">
        <button type="button" class="mobile-nav-toggle" onclick="toggleMobileNav()" aria-label="Open navigation menu">
          <i class="bi bi-list"></i>
        </button>
      </div>
      <div class="topbar-right">
        <div class="topbar-date-group">
          <span class="date"><i class="bi bi-calendar3"></i> <?= date('l, F d, Y') ?></span>
          <?php if (isset($last_updated)): ?>
            <span class="topbar-last-updated">Last updated: <?= esc($last_updated) ?></span>
          <?php endif; ?>
        </div>
        <a href="<?= base_url('notifications') ?>" class="icon-btn" title="Notifications">
          <i class="bi bi-bell"></i>
          <span class="badge-dot" id="topbarBellBadge" <?= $topbarUnreadCount > 0 ? '' : 'style="display:none"' ?>><?= $topbarUnreadCount > 99 ? '99+' : (int) $topbarUnreadCount ?></span>
        </a>
      </div>
    </header>
    <?php endif; ?>

    <?php if (session()->getFlashdata('success') || session()->getFlashdata('error')): ?>
      <div class="flash-toast-stack">
        <?php if (session()->getFlashdata('success')): ?>
          <div class="flash-toast flash-toast-success" id="flashToastSuccess">
            <i class="bi bi-check-circle-fill"></i>
            <span><?= esc(session()->getFlashdata('success')) ?></span>
            <button type="button" class="flash-toast-close" onclick="this.closest('.flash-toast').remove()" aria-label="Dismiss"><i class="bi bi-x-lg"></i></button>
          </div>
        <?php endif; ?>
        <?php if (session()->getFlashdata('error')): ?>
          <div class="flash-toast flash-toast-error" id="flashToastError">
            <i class="bi bi-exclamation-circle-fill"></i>
            <span><?= esc(session()->getFlashdata('error')) ?></span>
            <button type="button" class="flash-toast-close" onclick="this.closest('.flash-toast').remove()" aria-label="Dismiss"><i class="bi bi-x-lg"></i></button>
          </div>
        <?php endif; ?>
      </div>
    <?php endif; ?>

    <?php if (session()->get('isLoggedIn')): ?>
      <div class="notif-toast-stack" id="notifToastStack"></div>
    <?php endif; ?>

    <main class="page-content">
      <?= $this->renderSection('content') ?>
    </main>

    <footer class="app-footer">
      <span>&copy; <?= date('Y') ?> UBRA — <span class="fu-serif">Foundation University</span> Buildings &amp; Grounds</span>
      <span class="app-footer-sep">&middot;</span>
      <span>Operational Portal</span>
    </footer>
  </div>
</div>

<?php if (session()->get('isLoggedIn') && $currentUri !== 'ubra'): ?>
<div class="ai-fab-wrapper">
  <button type="button" class="ai-fab" id="aiFabBtn" aria-label="Open Mr. UBRA Assistant" title="Ask Mr. UBRA">
    <?php if ($aiIconAsset): ?>
      <img src="<?= esc($aiIconAsset) ?>" alt="Mr. UBRA">
    <?php else: ?>
      <i class="bi bi-stars"></i>
    <?php endif; ?>
  </button>
  <div class="ai-fab-panel" id="aiFabPanel">
    <div class="ai-fab-header">
      <div class="ai-fab-header-title">
        <span class="ai-fab-avatar">
          <?php if ($aiIconAsset): ?>
            <img src="<?= esc($aiIconAsset) ?>" alt="Mr. UBRA">
          <?php else: ?>
            <i class="bi bi-stars"></i>
          <?php endif; ?>
        </span>
        <div>
          <strong>Mr. UBRA</strong>
          <small>Intelligent Operations Assistant</small>
        </div>
      </div>
      <button type="button" class="ai-fab-close" id="aiFabClose" aria-label="Close chat"><i class="bi bi-x-lg"></i></button>
    </div>
    <div class="ai-fab-messages" id="aiFabMessages">
      <div class="ai-fab-msg ai-fab-msg-bot">Hi, I'm Mr. UBRA. Ask me about vehicles, personnel, tools, or anything operational — I can help.</div>
    </div>
    <form class="ai-fab-input-row" id="aiFabForm">
      <input type="text" id="aiFabInput" placeholder="Ask Mr. UBRA…" autocomplete="off">
      <button type="submit" aria-label="Send"><i class="bi bi-send"></i></button>
    </form>
    <a href="<?= base_url('ubra') ?>" class="ai-fab-fullview">Open full assistant <i class="bi bi-arrow-up-right"></i></a>
  </div>
</div>
<?php endif; ?>

<script>
// CSRF helper for fetch()-based POST calls. The token is session-backed
// (not cookie-based) and rendered into a <meta> tag at page load — it can't
// be read from document.cookie, since the session cookie itself is HttpOnly
// (as it should be, to keep it safe from XSS) and was never meant to hold
// the CSRF value in the first place.
function csrfHeaders(extra) {
  const headerName = document.querySelector('meta[name="csrf-header-name"]')?.content || '';
  const token = document.querySelector('meta[name="csrf-token-value"]')?.content || '';
  return Object.assign({}, extra || {}, { [headerName]: token });
}

// One toast look for every page's JS-triggered messages ("refilled
// successfully", "assigned to…", errors): same top-right green/red card as
// the redirect flash messages, instead of each page drawing its own
// bottom-corner black toast.
function uiToast(msg, isError) {
  let stack = document.querySelector('.flash-toast-stack');
  if (!stack) {
    stack = document.createElement('div');
    stack.className = 'flash-toast-stack';
    document.body.appendChild(stack);
  }
  const t = document.createElement('div');
  t.className = 'flash-toast ' + (isError ? 'flash-toast-error' : 'flash-toast-success');
  t.innerHTML = '<i class="bi ' + (isError ? 'bi-exclamation-circle-fill' : 'bi-check-circle-fill') + '"></i><span></span>' +
    '<button type="button" class="flash-toast-close" aria-label="Dismiss"><i class="bi bi-x-lg"></i></button>';
  t.querySelector('span').textContent = msg;
  const close = () => { t.classList.remove('show'); setTimeout(() => t.remove(), 300); };
  t.querySelector('button').addEventListener('click', close);
  stack.appendChild(t);
  requestAnimationFrame(() => t.classList.add('show'));
  setTimeout(close, 4000);
}

// Styled replacement for the browser's native confirm() (the black
// "localhost says" box). uiConfirm() returns a Promise<boolean>; inline
// `onsubmit/onclick="return confirm('…')"` attributes across every page are
// upgraded automatically below, so no view needs to change individually.
function uiConfirm(message, opts) {
  opts = opts || {};
  const word = String(message).trim().split(/\s+/)[0].replace(/[^A-Za-z]/g, '');
  const danger = opts.danger ?? /^(delete|archive|end|remove)$/i.test(word);
  const title = opts.title || (word ? word.charAt(0).toUpperCase() + word.slice(1).toLowerCase() + '?' : 'Are you sure?');
  const okLabel = opts.okLabel || (word && word.length < 10 ? word.charAt(0).toUpperCase() + word.slice(1).toLowerCase() : 'Confirm');

  return new Promise(resolve => {
    const overlay = document.createElement('div');
    overlay.className = 'ui-confirm-overlay';
    overlay.innerHTML =
      '<div class="ui-confirm-box' + (danger ? ' is-danger' : '') + '" role="alertdialog" aria-modal="true">' +
        '<div class="ui-confirm-icon"><i class="bi ' + (danger ? 'bi-exclamation-triangle-fill' : 'bi-question-circle-fill') + '"></i></div>' +
        '<h3 class="ui-confirm-title"></h3>' +
        '<p class="ui-confirm-msg"></p>' +
        '<div class="ui-confirm-actions">' +
          '<button type="button" class="ui-confirm-cancel">Cancel</button>' +
          '<button type="button" class="ui-confirm-ok"></button>' +
        '</div>' +
      '</div>';
    overlay.querySelector('.ui-confirm-title').textContent = title;
    overlay.querySelector('.ui-confirm-msg').textContent = message;
    overlay.querySelector('.ui-confirm-ok').textContent = okLabel;
    document.body.appendChild(overlay);
    requestAnimationFrame(() => overlay.classList.add('show'));

    const done = result => {
      document.removeEventListener('keydown', onKey);
      overlay.classList.remove('show');
      setTimeout(() => overlay.remove(), 150);
      resolve(result);
    };
    const onKey = e => {
      if (e.key === 'Escape') done(false);
      if (e.key === 'Enter') done(true);
    };
    document.addEventListener('keydown', onKey);
    overlay.querySelector('.ui-confirm-ok').addEventListener('click', () => done(true));
    overlay.querySelector('.ui-confirm-cancel').addEventListener('click', () => done(false));
    overlay.addEventListener('click', e => { if (e.target === overlay) done(false); });
    overlay.querySelector('.ui-confirm-ok').focus();
  });
}

document.querySelectorAll('[onsubmit*="confirm("], [onclick*="confirm("]').forEach(el => {
  const attr = el.hasAttribute('onsubmit') ? 'onsubmit' : 'onclick';
  const m = /confirm\((['"])([\s\S]*?)\1\)/.exec(el.getAttribute(attr));
  if (!m) return;
  const message = m[2];
  el.removeAttribute(attr);

  if (attr === 'onsubmit') {
    el.addEventListener('submit', e => {
      if (el.dataset.confirmed) return;
      e.preventDefault();
      uiConfirm(message).then(ok => {
        if (!ok) return;
        el.dataset.confirmed = '1';
        el.submit();
      });
    });
  } else {
    el.addEventListener('click', e => {
      if (el.dataset.confirmed) return;
      e.preventDefault();
      uiConfirm(message).then(ok => {
        if (!ok) return;
        el.dataset.confirmed = '1';
        el.click();
      });
    });
  }
});

// Flash message toasts (redirect-based "Personnel updated successfully"
// style messages) fade in, then auto-dismiss on their own after a few
// seconds instead of sitting on screen until the page is reloaded again.
document.querySelectorAll('.flash-toast').forEach(toast => {
  requestAnimationFrame(() => toast.classList.add('show'));
  setTimeout(() => {
    toast.classList.remove('show');
    setTimeout(() => toast.remove(), 300);
  }, 4000);
});

<?php if (session()->get('isLoggedIn')): ?>
// Live notification popups — polls notifications/unread-count every 25s and
// pops a toast on the right side for any unread notification id it hasn't
// shown yet. "Already shown" ids are kept in localStorage so a page reload
// (or navigating between tabs) doesn't re-pop the same notification, and the
// very first poll on a fresh browser only primes the seen-list rather than
// dumping every existing unread notification as toasts at once.
(function () {
  const POLL_MS   = 25000;
  const SEEN_KEY  = 'ubra_notif_seen_ids';
  const stack     = document.getElementById('notifToastStack');
  const bellBadge = document.getElementById('topbarBellBadge');
  const navBadge  = document.getElementById('navBellCount');
  let primed = false;

  function getSeen() {
    try { return new Set(JSON.parse(localStorage.getItem(SEEN_KEY) || '[]')); }
    catch (e) { return new Set(); }
  }
  function saveSeen(set) {
    try { localStorage.setItem(SEEN_KEY, JSON.stringify(Array.from(set)).slice(-2000)); }
    catch (e) {}
  }

  function updateBadge(el, count) {
    if (!el) return;
    if (count > 0) {
      el.textContent = count > 99 ? '99+' : String(count);
      el.style.display = '';
    } else {
      el.style.display = 'none';
    }
  }

  function iconFor(priority) {
    if (priority === 'CRITICAL') return 'bi-exclamation-triangle-fill';
    if (priority === 'MODERATE') return 'bi-exclamation-circle-fill';
    return 'bi-bell-fill';
  }

  function popToast(n) {
    if (!stack) return;
    const el = document.createElement('div');
    el.className = 'notif-toast notif-toast-' + (n.priority || 'routine').toLowerCase();
    el.innerHTML =
      '<i class="bi ' + iconFor(n.priority) + ' notif-toast-icon"></i>' +
      '<div class="notif-toast-body">' +
        '<span class="notif-toast-cat">' + escapeHtml(n.category || 'Notification') + '</span>' +
        '<span class="notif-toast-desc">' + escapeHtml(n.description || '') + '</span>' +
      '</div>' +
      '<button type="button" class="notif-toast-close" aria-label="Dismiss"><i class="bi bi-x-lg"></i></button>';
    el.addEventListener('click', (e) => {
      if (e.target.closest('.notif-toast-close')) { dismiss(); return; }
      window.location.href = '<?= base_url('notifications') ?>';
    });
    function dismiss() {
      el.classList.remove('show');
      setTimeout(() => el.remove(), 250);
    }
    stack.appendChild(el);
    requestAnimationFrame(() => el.classList.add('show'));
    setTimeout(dismiss, 8000);
  }

  function escapeHtml(s) {
    const d = document.createElement('div');
    d.textContent = s;
    return d.innerHTML;
  }

  async function poll() {
    try {
      const res = await fetch('<?= base_url('notifications/unreadCount') ?>', { headers: csrfHeaders() });
      if (!res.ok) return;
      const data = await res.json();
      updateBadge(bellBadge, data.count || 0);
      updateBadge(navBadge, data.count || 0);

      const seen = getSeen();
      const latest = Array.isArray(data.latest) ? data.latest : [];
      if (!primed) {
        // First run on a fresh browser: just remember what's already unread,
        // don't pop a wall of toasts for notifications that predate this tab.
        latest.forEach(n => seen.add(n.id));
        saveSeen(seen);
        primed = true;
        return;
      }
      latest
        .filter(n => !seen.has(n.id))
        .reverse() // oldest of the new batch first, so the newest ends up on top
        .forEach(n => { popToast(n); seen.add(n.id); });
      saveSeen(seen);
    } catch (e) {
      // Silent — a missed poll just tries again next interval.
    }
  }

  poll();
  setInterval(poll, POLL_MS);
})();
<?php endif; ?>

// Site-wide safety net: whenever any .modal popup is open, the page behind
// it shouldn't also be scrollable — that's what shows up as a second,
// confusing scrollbar at the edge of the browser window. Pages that toggle
// their own modals already set this directly, but this covers every other
// modal (Edit forms, Assign to Job Order, etc.) without editing each one.
document.addEventListener('click', () => {
  setTimeout(() => {
    // Most pages hide a closed modal with display:none; the GPS Tracker's
    // modal instead stays display:flex permanently and hides via
    // opacity/pointer-events — checking both conventions covers either.
    const anyOpen = Array.from(document.querySelectorAll('.modal')).some(m => {
      const cs = getComputedStyle(m);
      return cs.display !== 'none' && cs.opacity !== '0';
    });
    document.body.style.overflow = anyOpen ? 'hidden' : '';
  }, 0);
});

// Sidebar toggle function
function updateSidebarToggleIcon() {
  const icon = document.querySelector('.sidebar-toggle-btn i');
  if (!icon) return;
  const isCollapsed = document.body.classList.contains('sidebar-collapsed');
  icon.classList.toggle('bi-chevron-double-left', !isCollapsed);
  icon.classList.toggle('bi-chevron-double-right', isCollapsed);
}

function toggleSidebar() {
  document.body.classList.toggle('sidebar-collapsed');
  const isCollapsed = document.body.classList.contains('sidebar-collapsed');
  localStorage.setItem('sidebarCollapsed', isCollapsed);
  updateSidebarToggleIcon();
  hideRailTooltip();
}

// Mobile off-canvas nav (< 768px) — separate from the desktop icon-rail
// collapse above. Pass true/false to force a state, or omit to toggle.
function toggleMobileNav(open) {
  const shouldOpen = typeof open === 'boolean' ? open : !document.body.classList.contains('mobile-nav-open');
  document.body.classList.toggle('mobile-nav-open', shouldOpen);
}

// Avatar preview lightbox — sidebar profile photo click
function openAvatarPreview(src, name) {
  document.getElementById('avatarLightboxImg').src = src;
  document.getElementById('avatarLightboxImg').alt = name;
  document.getElementById('avatarLightboxCaption').textContent = name;
  document.getElementById('avatarLightbox').classList.add('open');
}
function closeAvatarPreview() {
  document.getElementById('avatarLightbox').classList.remove('open');
}
document.addEventListener('keydown', e => {
  if (e.key === 'Escape') closeAvatarPreview();
});

// Floating tooltip for the collapsed icon-rail sidebar
const railTooltip = document.createElement('div');
railTooltip.className = 'rail-tooltip';
document.body.appendChild(railTooltip);

function hideRailTooltip() {
  railTooltip.classList.remove('visible');
}

document.querySelectorAll('.sidebar-nav a[data-tooltip], .sidebar-footer a[data-tooltip]').forEach(link => {
  link.addEventListener('mouseenter', () => {
    if (!document.body.classList.contains('sidebar-collapsed')) return;
    const rect = link.getBoundingClientRect();
    railTooltip.textContent = link.dataset.tooltip;
    railTooltip.style.top = (rect.top + rect.height / 2) + 'px';
    railTooltip.style.left = (rect.right + 12) + 'px';
    railTooltip.classList.add('visible');
  });
  link.addEventListener('mouseleave', hideRailTooltip);
});

// Load sidebar state from localStorage on page load
window.addEventListener('DOMContentLoaded', () => {
  const sidebarCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
  if (sidebarCollapsed) {
    document.body.classList.add('sidebar-collapsed');
  }
  updateSidebarToggleIcon();
});

// ── Dark / light mode ────────────────────────────────────────────
// The <html data-theme="dark"> attribute is what base.css keys off of;
// it's also set early in <head> (see top of file) to avoid a flash of
// the light theme on load. Any page can have its own toggle switch —
// they all call this same function and stay in sync via the shared
// 'theme' localStorage key plus the 'themechange' event below.
function isDarkTheme() {
  return document.documentElement.getAttribute('data-theme') === 'dark';
}

function setTheme(isDark) {
  if (isDark) {
    document.documentElement.setAttribute('data-theme', 'dark');
  } else {
    document.documentElement.removeAttribute('data-theme');
  }
  localStorage.setItem('theme', isDark ? 'dark' : 'light');
  document.querySelectorAll('[data-theme-toggle]').forEach(el => { el.checked = isDark; });
}

function toggleTheme() {
  setTheme(!isDarkTheme());
}

window.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('[data-theme-toggle]').forEach(el => {
    el.checked = isDarkTheme();
    el.addEventListener('change', () => setTheme(el.checked));
  });
});

// generic modal helpers used across pages
function openModal(id){ document.getElementById(id).classList.add('open'); }
function closeModal(id){ document.getElementById(id).classList.remove('open'); }
document.querySelectorAll('.modal').forEach(m=>m.addEventListener('click',e=>{ if(e.target===m) m.classList.remove('open'); }));

const personnelLink = document.querySelector('[data-personnel-toggle]');
const personnelGroup = personnelLink?.closest('.nav-parent-group');
if (personnelLink && personnelGroup) {
  personnelLink.addEventListener('click', (event) => {
    // Only the caret toggles the submenu open/closed — clicking anywhere
    // else on the row (or navigating to a page within this section) must
    // never silently expand/collapse it on its own.
    const clickedCaret = event.target.closest('.nav-parent-caret');
    if (clickedCaret) {
      event.preventDefault();
      personnelGroup.classList.toggle('open');
      personnelLink.classList.toggle('open');
    }
  });
}

const vehicleLink = document.querySelector('[data-vehicle-toggle]');
const vehicleGroup = vehicleLink?.closest('.nav-parent-group');
if (vehicleLink && vehicleGroup) {
  vehicleLink.addEventListener('click', (event) => {
    const currentPath = window.location.pathname.replace(/^\/+|\/+$/g, '');
    const isVehicleRoute = currentPath === 'vehicles' || currentPath === 'gps' || currentPath === 'travel';
    const clickedCaret = event.target.closest('.nav-parent-caret');

    if (clickedCaret || isVehicleRoute) {
      event.preventDefault();
      vehicleGroup.classList.toggle('open');
      vehicleLink.classList.toggle('open');
    }
  });
}

const toolsLink = document.querySelector('[data-tools-toggle]');
const toolsGroup = toolsLink?.closest('.nav-parent-group');
if (toolsLink && toolsGroup) {
  toolsLink.addEventListener('click', (event) => {
    const currentPath = window.location.pathname.replace(/^\/+|\/+$/g, '');
    const isToolsRoute = currentPath === 'tools' || currentPath.startsWith('tools/');
    const clickedCaret = event.target.closest('.nav-parent-caret');

    if (clickedCaret || isToolsRoute) {
      event.preventDefault();
      toolsGroup.classList.toggle('open');
      toolsLink.classList.toggle('open');
    }
  });
}

const infoHubLink = document.querySelector('[data-infohub-toggle]');
const infoHubGroup = infoHubLink?.closest('.nav-parent-group');
if (infoHubLink && infoHubGroup) {
  infoHubLink.addEventListener('click', (event) => {
    const currentPath = window.location.pathname.replace(/^\/+|\/+$/g, '');
    const isInfoHubRoute = currentPath === 'reports' || currentPath.startsWith('maintenance-forms/');
    const clickedCaret = event.target.closest('.nav-parent-caret');

    if (clickedCaret || isInfoHubRoute) {
      event.preventDefault();
      infoHubGroup.classList.toggle('open');
      infoHubLink.classList.toggle('open');
    }
  });
}

const safetyLink = document.querySelector('[data-safety-toggle]');
const safetyGroup = safetyLink?.closest('.nav-parent-group');
if (safetyLink && safetyGroup) {
  safetyLink.addEventListener('click', (event) => {
    event.preventDefault();
    safetyGroup.classList.toggle('open');
    safetyLink.classList.toggle('open');
  });
}
</script>
  <script src="<?= assetVer('assets/js/dashboard.js') ?>"></script>

<?php if (session()->get('isLoggedIn') && $currentUri !== 'ubra'): ?>
<script>
(function() {
  const fabBtn     = document.getElementById('aiFabBtn');
  const panel      = document.getElementById('aiFabPanel');
  const closeBtn   = document.getElementById('aiFabClose');
  const form       = document.getElementById('aiFabForm');
  const input      = document.getElementById('aiFabInput');
  const messagesEl = document.getElementById('aiFabMessages');
  if (!fabBtn || !panel) return;

  const CHAT_URL    = '<?= base_url('ubra/chat') ?>';
  const HISTORY_URL = '<?= base_url('ubra/history') ?>';
  let history = [];
  let historyLoaded = false;

  fabBtn.addEventListener('click', () => {
    panel.classList.toggle('open');
    if (panel.classList.contains('open')) {
      input.focus();
      if (!historyLoaded) loadHistory();
    }
  });
  closeBtn.addEventListener('click', () => panel.classList.remove('open'));

  // Same lightweight markdown rendering as the full /ubra assistant page —
  // so **bold**, bullet lists, etc. actually render instead of showing the
  // raw asterisks/markup in this floating widget.
  function renderMarkdown(text) {
    return text
        .replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;')
        .replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>')
        .replace(/\*(.+?)\*/g, '<em>$1</em>')
        .replace(/`(.+?)`/g, '<code>$1</code>')
        .replace(/^#{1,3} (.+)$/gm, '<strong>$1</strong>')
        .replace(/^[-•] (.+)$/gm, '<li>$1</li>')
        .replace(/(<li>.*<\/li>)/gs, '<ul>$1</ul>')
        .replace(/\n\n/g, '</p><p>')
        .replace(/\n/g, '<br>');
  }

  function addMessage(text, role, download = null) {
    const div = document.createElement('div');
    div.className = 'ai-fab-msg ' + (role === 'user' ? 'ai-fab-msg-user' : 'ai-fab-msg-bot');
    div.innerHTML = renderMarkdown(text);

    // A real, working file link (report requests) — opens in its own tab
    // instead of navigating the widget's page away.
    if (download && download.url) {
      const btn = document.createElement('button');
      btn.type = 'button';
      btn.className = 'ai-fab-download-btn';
      btn.innerHTML = `<i class="bi bi-download"></i> ${(download.label || 'Download report').replace(/</g, '&lt;')}`;
      btn.addEventListener('click', () => window.open(download.url, '_blank'));
      div.appendChild(btn);
    }

    messagesEl.appendChild(div);
    messagesEl.scrollTop = messagesEl.scrollHeight;
    return div;
  }

  async function loadHistory() {
    historyLoaded = true;
    try {
      const res  = await fetch(HISTORY_URL, { headers: csrfHeaders() });
      const data = await res.json();
      const rows = data.history || [];
      if (rows.length === 0) return;

      messagesEl.innerHTML = '';
      rows.forEach(row => {
        addMessage(row.message, row.role);
        history.push({ role: row.role, content: row.message });
      });
    } catch (err) {
      // Leave the default welcome message in place if history can't load.
    }
  }

  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    const msg = input.value.trim();
    if (!msg) return;
    input.value = '';
    addMessage(msg, 'user');
    history.push({ role: 'user', content: msg });

    const typing = document.createElement('div');
    typing.className = 'ai-fab-msg ai-fab-msg-typing';
    typing.textContent = 'Mr. UBRA is typing…';
    messagesEl.appendChild(typing);
    messagesEl.scrollTop = messagesEl.scrollHeight;

    try {
      const fd = new FormData();
      fd.append('message', msg);
      fd.append('history', JSON.stringify(history.slice(-10)));
      const res  = await fetch(CHAT_URL, { method: 'POST', headers: csrfHeaders(), body: fd });
      const data = await res.json();
      const reply = data.reply || data.error || 'Sorry, something went wrong.';
      typing.remove();
      addMessage(reply, 'assistant', data.download || null);
      history.push({ role: 'assistant', content: reply });
    } catch (err) {
      typing.remove();
      addMessage('Connection error. Please try again.', 'assistant');
    }
  });
})();
</script>
<?php endif; ?>
