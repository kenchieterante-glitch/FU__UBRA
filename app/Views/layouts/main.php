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
    </div>

    <nav class="sidebar-nav">
      <a href="<?= base_url('dashboard') ?>" class="<?= navActive('dashboard') ?>"><i class="fa-solid fa-table-cells-large"></i> Dashboard</a>

      <?php $isPersonnelSection = $currentUri === 'personnel' || strpos($currentUri, 'personnel/') === 0; ?>
      <div class="nav-parent-group <?= $isPersonnelSection ? 'open' : '' ?>">
        <a href="<?= base_url('personnel') ?>" class="nav-parent-link <?= $isPersonnelSection ? 'active open' : '' ?>" data-personnel-toggle>
          <i class="fa-solid fa-users"></i>
          <span>Personnel Management</span>
          <i class="fa-solid fa-chevron-down nav-parent-caret"></i>
        </a>
        <div class="nav-submenu" id="personnel-submenu">
          <a href="<?= base_url('personnel') ?>" class="<?= navActive('personnel') ?>"><i class="fa-solid fa-users"></i> All Personnel</a>
          <a href="<?= base_url('personnel/drivers') ?>" class="<?= navActive('personnel/drivers') ?>"><i class="fa-solid fa-id-badge"></i> Drivers</a>
          <a href="<?= base_url('personnel/janitors') ?>" class="<?= navActive('personnel/janitors') ?>"><i class="fa-solid fa-broom"></i> Janitors</a>
          <a href="<?= base_url('personnel/carpentries') ?>" class="<?= navActive('personnel/carpentries') ?>"><i class="fa-solid fa-hammer"></i> Carpentries</a>
          <a href="<?= base_url('personnel/maintenance') ?>" class="<?= navActive('personnel/maintenance') ?>"><i class="fa-solid fa-wrench"></i> Maintenance</a>
        </div>
      </div>

      <?php $isVehicleSection = $currentUri === 'vehicles' || $currentUri === 'travel' || $currentUri === 'gps'; ?>
      <div class="nav-parent-group <?= $isVehicleSection ? 'open' : '' ?>">
        <a href="<?= base_url('vehicles') ?>" class="nav-parent-link <?= $isVehicleSection ? 'active open' : '' ?>" data-vehicle-toggle>
          <i class="fa-solid fa-truck"></i>
          <span>Vehicle Management</span>
          <i class="fa-solid fa-chevron-down nav-parent-caret"></i>
        </a>
        <div class="nav-submenu" id="vehicle-submenu">
          <a href="<?= base_url('vehicles') ?>" class="<?= navActive('vehicles') ?>"><i class="fa-solid fa-truck"></i> Vehicle Management</a>
          <a href="<?= base_url('travel') ?>" class="<?= navActive('travel') ?>"><i class="fa-solid fa-road"></i> Travel Management</a>
          <a href="<?= base_url('gps') ?>" class="<?= navActive('gps') ?>"><i class="fa-solid fa-location-dot"></i> GPS Tracker</a>
        </div>
      </div>

      <a href="<?= base_url('tools') ?>" class="<?= navActive('tools') ?>"><i class="fa-solid fa-boxes-stacked"></i> Tools Management</a>

      <a href="<?= base_url('safety') ?>" class="<?= navActive('safety') ?>"><i class="fa-solid fa-hard-hat"></i> Safety Maintenance</a>
      <a href="<?= base_url('safety/guard-dashboard') ?>" class="<?= navActive('safety/guard-dashboard') ?>"><i class="fa-solid fa-tv"></i> Guard Dashboard</a>
      <a href="<?= base_url('janitorial') ?>" class="<?= navActive('janitorial') ?>"><i class="fa-solid fa-broom"></i> Janitorial Monitoring</a>
      <a href="<?= base_url('calendar') ?>" class="<?= navActive('calendar') ?>"><i class="fa-solid fa-calendar-days"></i> Calendar</a>
      <a href="<?= base_url('notifications') ?>" class="<?= navActive('notifications') ?>"><i class="fa-solid fa-bell"></i> Notifications</a>
      <a href="<?= base_url('reports') ?>" class="<?= navActive('reports') ?>"><i class="fa-solid fa-folder-closed"></i> Records, Archiving & Reports</a>
      <div class="nav-sep"></div>
      <a href="<?= base_url('ubra') ?>" class="ai-link <?= navActive('ubra') ?>"><i class="fa-solid fa-robot"></i> Mr. UBRA AI <span class="dot"></span></a>
      <a href="<?= base_url('settings') ?>" class="<?= navActive('settings') ?>"><i class="fa-solid fa-gear"></i> Settings</a>
    </nav>

    <div class="sidebar-footer">
      <a href="<?= base_url('profile') ?>">
        <span class="av"><?= esc($initials) ?></span>
        <span><?= esc($fullName) ?><small>View Profile</small></span>
      </a>
    </div>
  </aside>

  <!--MAIN CONTENT  -->
  <div class="main-content">
    <header class="topbar">
      <div class="topbar-left">
        <button type="button" class="sidebar-toggle-btn" onclick="toggleSidebar()" aria-label="Toggle sidebar" title="Toggle sidebar">
          <i class="fa-solid fa-bars"></i>
        </button>
        <div class="search">
          <button type="button" class="topbar-search-btn" aria-label="Search">
            <i class="fa-solid fa-magnifying-glass"></i>
          </button>
          <input type="text" class="search-box" placeholder="Search operational resources...">
        </div>
      </div>
      <div class="topbar-right">
        <span class="date"><i class="fa-regular fa-calendar"></i> <?= date('l, F d, Y') ?></span>
        <a href="<?= base_url('notifications') ?>" class="icon-btn"><i class="fa-regular fa-bell"></i></a>
        <div class="user-chip">
          <span class="av"><?= esc($initials) ?></span>
          <span><b><?= esc($fullName) ?></b><small><?= esc($role) ?></small></span>
        </div>
      </div>
    </header>

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
// Sidebar toggle function
function toggleSidebar() {
  document.body.classList.toggle('sidebar-collapsed');
  const isCollapsed = document.body.classList.contains('sidebar-collapsed');
  localStorage.setItem('sidebarCollapsed', isCollapsed);
}

// Search toggle for collapsed sidebar
function toggleSearchBar() {
  const searchEl = document.querySelector('.topbar .search');
  const isSidebarCollapsed = document.body.classList.contains('sidebar-collapsed');
  
  if (isSidebarCollapsed && searchEl) {
    searchEl.classList.toggle('expanded');
    // Focus input when expanding
    if (searchEl.classList.contains('expanded')) {
      const input = searchEl.querySelector('.search-box');
      if (input) input.focus();
    }
  }
}

// Load sidebar state from localStorage on page load
window.addEventListener('DOMContentLoaded', () => {
  const sidebarCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
  if (sidebarCollapsed) {
    document.body.classList.add('sidebar-collapsed');
  }
  
  // Add search toggle handler
  const searchBtn = document.querySelector('.topbar .search button.topbar-search-btn');
  if (searchBtn) {
    searchBtn.addEventListener('click', toggleSearchBar);
  }
  
  // Close search when clicking outside (on collapsed view)
  document.addEventListener('click', (e) => {
    const searchEl = document.querySelector('.topbar .search');
    const searchBtn = document.querySelector('.topbar .search button.topbar-search-btn');
    const isSidebarCollapsed = document.body.classList.contains('sidebar-collapsed');
    
    if (isSidebarCollapsed && searchEl && searchEl.classList.contains('expanded')) {
      if (!searchEl.contains(e.target)) {
        searchEl.classList.remove('expanded');
      }
    }
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
