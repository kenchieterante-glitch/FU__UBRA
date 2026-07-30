<?php
  $currentUri = trim(uri_string(), '/');
  function navActive($seg){
      $uri = trim(uri_string(), '/');
      if ($seg === 'safety') {
          return $uri === 'safety' ? 'active' : '';
      }
      if ($seg === 'safety/guard-dashboard') {
          return $uri === 'safety/guard-dashboard' ? 'active' : '';
      }
      if ($seg === 'personnel') {
          return $uri === 'personnel' || strpos($uri, 'personnel/') === 0 ? 'active' : '';
      }
      if ($seg === 'vehicles') {
          return $uri === 'vehicles' || $uri === 'travel' || $uri === 'gps' ? 'active' : '';
      }
      return $uri === $seg || strpos($uri, $seg . '/') === 0 ? 'active' : '';
  }
  $fullName = session()->get('full_name') ?? 'System Admin';
  $role     = session()->get('role') ?? 'Operations';
  $initials = strtoupper(substr($fullName,0,1) . (strpos($fullName,' ') !== false ? substr($fullName, strpos($fullName,' ')+1,1) : ''));
  $logoAsset = null;
  $logoCandidates = [
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
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>FU_UBRA | <?= esc($title ?? 'Dashboard') ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Helvetica+Neue&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <link rel="stylesheet" href="<?= base_url('Assets/css/base.css') ?>">
  <?php if (!empty($pageCss)): ?><link rel="stylesheet" href="<?= base_url('Assets/css/'.$pageCss) ?>"><?php endif; ?>
  <?php if (!empty($logoAsset)): ?><link rel="icon" href="<?= esc($logoAsset) ?>">
  <?php endif; ?>
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
</head>
<body>
<div class="app-wrapper">

  <!-- ============================= SIDEBAR ============================= -->
  <aside class="sidebar">
    <div class="sidebar-logo">
      <?php if (!empty($logoAsset)): ?>
        <img class="sidebar-logo-image" src="<?= esc($logoAsset) ?>" alt="System logo">
      <?php else: ?>
        <div class="mark"><i class="fa-solid fa-shield-halved"></i></div>
      <?php endif; ?>
      <div class="txt"><b>FU_UBRA</b><small>FOUNDATION UNIVERSITY</small></div>
      <button type="button" class="sidebar-toggle-btn" onclick="toggleSidebar()" aria-label="Toggle sidebar" title="Toggle sidebar">
        <i class="fa-solid fa-angles-left"></i>
      </button>
    </div>

    <nav class="sidebar-nav">
      <a href="<?= base_url('dashboard') ?>" class="<?= navActive('dashboard') ?>" data-tooltip="Dashboard"><i class="fa-solid fa-table-cells-large"></i> <span class="nav-label">Dashboard</span></a>

      <?php $isPersonnelSection = $currentUri === 'personnel' || strpos($currentUri, 'personnel/') === 0; ?>
      <div class="nav-parent-group <?= $isPersonnelSection ? 'open' : '' ?>">
        <a href="<?= base_url('personnel') ?>" class="nav-parent-link <?= $isPersonnelSection ? 'active open' : '' ?>" data-personnel-toggle data-tooltip="Personnel Management">
          <i class="fa-solid fa-users"></i>
          <span class="nav-label">Personnel Management</span>
          <i class="fa-solid fa-chevron-down nav-parent-caret"></i>
        </a>
        <div class="nav-submenu" id="personnel-submenu">
          <a href="<?= base_url('personnel') ?>" class="<?= navActive('personnel') ?>"><i class="fa-solid fa-users"></i> <span class="nav-label">All Personnel</span></a>
          <a href="<?= base_url('personnel/drivers') ?>" class="<?= navActive('personnel/drivers') ?>"><i class="fa-solid fa-id-badge"></i> <span class="nav-label">Drivers</span></a>
          <a href="<?= base_url('personnel/janitors') ?>" class="<?= navActive('personnel/janitors') ?>"><i class="fa-solid fa-broom"></i> <span class="nav-label">Janitors</span></a>
          <a href="<?= base_url('personnel/carpentries') ?>" class="<?= navActive('personnel/carpentries') ?>"><i class="fa-solid fa-hammer"></i> <span class="nav-label">Carpentries</span></a>
          <a href="<?= base_url('personnel/maintenance') ?>" class="<?= navActive('personnel/maintenance') ?>"><i class="fa-solid fa-wrench"></i> <span class="nav-label">Maintenance</span></a>
        </div>
      </div>

      <?php $isVehicleSection = $currentUri === 'vehicles' || $currentUri === 'travel' || $currentUri === 'gps'; ?>
      <div class="nav-parent-group <?= $isVehicleSection ? 'open' : '' ?>">
        <a href="<?= base_url('vehicles') ?>" class="nav-parent-link <?= $isVehicleSection ? 'active open' : '' ?>" data-vehicle-toggle data-tooltip="Vehicle Management">
          <i class="fa-solid fa-truck"></i>
          <span class="nav-label">Vehicle Management</span>
          <i class="fa-solid fa-chevron-down nav-parent-caret"></i>
        </a>
        <div class="nav-submenu" id="vehicle-submenu">
          <a href="<?= base_url('vehicles') ?>" class="<?= navActive('vehicles') ?>"><i class="fa-solid fa-truck"></i> <span class="nav-label">Vehicle Management</span></a>
          <a href="<?= base_url('travel') ?>" class="<?= navActive('travel') ?>"><i class="fa-solid fa-road"></i> <span class="nav-label">Travel Management</span></a>
          <a href="<?= base_url('gps') ?>" class="<?= navActive('gps') ?>"><i class="fa-solid fa-location-dot"></i> <span class="nav-label">GPS Tracker</span></a>
        </div>
      </div>

      <a href="<?= base_url('tools') ?>" class="<?= navActive('tools') ?>" data-tooltip="Tools Management"><i class="fa-solid fa-boxes-stacked"></i> <span class="nav-label">Tools Management</span></a>

      <a href="<?= base_url('safety') ?>" class="<?= navActive('safety') ?>" data-tooltip="Safety Maintenance"><i class="fa-solid fa-hard-hat"></i> <span class="nav-label">Safety Maintenance</span></a>
      <a href="<?= base_url('safety/guard-dashboard') ?>" class="<?= navActive('safety/guard-dashboard') ?>" data-tooltip="Guard Dashboard"><i class="fa-solid fa-tv"></i> <span class="nav-label">Guard Dashboard</span></a>
      <a href="<?= base_url('janitorial') ?>" class="<?= navActive('janitorial') ?>" data-tooltip="Janitorial Monitoring"><i class="fa-solid fa-broom"></i> <span class="nav-label">Janitorial Monitoring</span></a>
      <a href="<?= base_url('calendar') ?>" class="<?= navActive('calendar') ?>" data-tooltip="Calendar"><i class="fa-solid fa-calendar-days"></i> <span class="nav-label">Calendar</span></a>
      <a href="<?= base_url('notifications') ?>" class="<?= navActive('notifications') ?>" data-tooltip="Notifications"><i class="fa-solid fa-bell"></i> <span class="nav-label">Notifications</span></a>
      <a href="<?= base_url('reports') ?>" class="<?= navActive('reports') ?>" data-tooltip="Records, Archiving & Reports"><i class="fa-solid fa-folder-closed"></i> <span class="nav-label">Records, Archiving & Reports</span></a>
      <div class="nav-sep"></div>
      <a href="<?= base_url('ubra') ?>" class="ai-link <?= navActive('ubra') ?>" data-tooltip="Mr. UBRA AI"><i class="fa-solid fa-robot"></i> <span class="nav-label">Mr. UBRA AI</span> <span class="dot"></span></a>
      <a href="<?= base_url('settings') ?>" class="<?= navActive('settings') ?>" data-tooltip="Settings"><i class="fa-solid fa-gear"></i> <span class="nav-label">Settings</span></a>
    </nav>

    <div class="sidebar-footer">
      <a href="<?= base_url('profile') ?>" data-tooltip="View Profile">
        <span class="av"><?= esc($initials) ?></span>
        <span class="nav-label"><?= esc($fullName) ?><small>View Profile</small></span>
      </a>
    </div>
  </aside>

  <!--MAIN CONTENT  -->
  <div class="main-content">
    <?php $showTopbar = $showTopbar ?? true; ?>
    <?php if ($showTopbar): ?>
    <header class="topbar">
      <div class="topbar-left">
        <div class="search">
          <input type="text" class="search-box" placeholder="Search operational resources...">
          <i class="fa-solid fa-magnifying-glass search-icon"></i>
        </div>
      </div>
      <div class="topbar-right">
        <span class="date"><i class="fa-regular fa-calendar"></i> <?= date('l, F d, Y') ?></span>
        <a href="<?= base_url('notifications') ?>" class="icon-btn"><i class="fa-regular fa-bell"></i></a>
        <?php if (!empty($logoAsset)): ?>
          <img class="topbar-logo" src="<?= esc($logoAsset) ?>" alt="System logo">
        <?php endif; ?>
      </div>
    </header>
    <?php endif; ?>

    <main class="page-content">
      <?php if (session()->getFlashdata('success')): ?>
        <div class="alert-success"><i class="fa-solid fa-circle-check"></i> <?= esc(session()->getFlashdata('success')) ?></div>
      <?php endif; ?>
      <?php if (session()->getFlashdata('error')): ?>
        <div class="alert-error"><i class="fa-solid fa-circle-exclamation"></i> <?= esc(session()->getFlashdata('error')) ?></div>
      <?php endif; ?>

      <?= $this->renderSection('content') ?>
    </main>
  </div>
</div>

<script>
// CSRF helper for fetch()-based POST calls. The token cookie is re-issued by
// the server after every validated request, so this must read document.cookie
// fresh on each call rather than caching the value at page load.
function csrfHeaders(extra) {
  const match = document.cookie.match('(^|;)\\s*<?= esc(config('Security')->cookieName, 'js') ?>\\s*=\\s*([^;]+)');
  const token = match ? decodeURIComponent(match.pop()) : '';
  return Object.assign({}, extra || {}, { '<?= esc(config('Security')->headerName, 'js') ?>': token });
}

// Sidebar toggle function
function updateSidebarToggleIcon() {
  const icon = document.querySelector('.sidebar-toggle-btn i');
  if (!icon) return;
  const isCollapsed = document.body.classList.contains('sidebar-collapsed');
  icon.classList.toggle('fa-angles-left', !isCollapsed);
  icon.classList.toggle('fa-angles-right', isCollapsed);
}

function toggleSidebar() {
  document.body.classList.toggle('sidebar-collapsed');
  const isCollapsed = document.body.classList.contains('sidebar-collapsed');
  localStorage.setItem('sidebarCollapsed', isCollapsed);
  updateSidebarToggleIcon();
  hideRailTooltip();
}

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

// generic modal helpers used across pages
function openModal(id){ document.getElementById(id).classList.add('open'); }
function closeModal(id){ document.getElementById(id).classList.remove('open'); }
document.querySelectorAll('.modal').forEach(m=>m.addEventListener('click',e=>{ if(e.target===m) m.classList.remove('open'); }));

const personnelLink = document.querySelector('[data-personnel-toggle]');
const personnelGroup = personnelLink?.closest('.nav-parent-group');
if (personnelLink && personnelGroup) {
  personnelLink.addEventListener('click', (event) => {
    const currentPath = window.location.pathname.replace(/^\/+|\/+$/g, '');
    const isPersonnelRoute = currentPath === 'personnel' || currentPath.startsWith('personnel/');
    const clickedCaret = event.target.closest('.nav-parent-caret');

    if (clickedCaret || isPersonnelRoute) {
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
    const isVehicleRoute = currentPath === 'vehicles' || currentPath === 'travel' || currentPath === 'gps';
    const clickedCaret = event.target.closest('.nav-parent-caret');

    if (clickedCaret || isVehicleRoute) {
      event.preventDefault();
      vehicleGroup.classList.toggle('open');
      vehicleLink.classList.toggle('open');
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
  <script src="<?= base_url('assets/js/dashboard.js') ?>"></script>
