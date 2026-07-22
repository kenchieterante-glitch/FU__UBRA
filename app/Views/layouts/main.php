<?php
  $uri = uri_string();
  function navActive($seg){ return (strpos(uri_string(), $seg) === 0) ? 'active' : ''; }
  $fullName = session()->get('full_name') ?? 'System Admin';
  $role     = session()->get('role') ?? 'Operations';
  $initials = strtoupper(substr($fullName,0,1) . (strpos($fullName,' ') !== false ? substr($fullName, strpos($fullName,' ')+1,1) : ''));
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
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
</head>
<body>
<div class="app-wrapper">

  <!-- ============================= SIDEBAR ============================= -->
  <aside class="sidebar">
    <div class="sidebar-logo">
      <div class="mark"><i class="fa-solid fa-shield-halved"></i></div>
      <div class="txt"><b>FU_UBRA</b><small>FOUNDATION UNIVERSITY</small></div>
    </div>

    <nav class="sidebar-nav">
      <a href="<?= base_url('dashboard') ?>" class="<?= navActive('dashboard') ?>"><i class="fa-solid fa-table-cells-large"></i> Dashboard</a>

      <?php $isPersonnelSection = (strpos(uri_string(), 'personnel') === 0); ?>
      <div class="nav-parent-group <?= $isPersonnelSection ? 'open' : '' ?>">
        <a href="<?= base_url('personnel') ?>" class="nav-parent-link <?= $isPersonnelSection ? 'active' : '' ?>" data-personnel-toggle>
          <i class="fa-solid fa-users"></i>
          <span>Personnel Management</span>
          <i class="fa-solid fa-chevron-down nav-parent-caret"></i>
        </a>
        <div class="nav-submenu" id="personnel-submenu">
          <a href="<?= base_url('personnel/drivers') ?>" class="<?= navActive('personnel/drivers') ?>"><i class="fa-solid fa-id-badge"></i> Drivers</a>
          <a href="<?= base_url('personnel/janitors') ?>" class="<?= navActive('personnel/janitors') ?>"><i class="fa-solid fa-broom"></i> Janitors</a>
          <a href="<?= base_url('personnel/carpentries') ?>" class="<?= navActive('personnel/carpentries') ?>"><i class="fa-solid fa-hammer"></i> Carpentries</a>
          <a href="<?= base_url('personnel/maintenance') ?>" class="<?= navActive('personnel/maintenance') ?>"><i class="fa-solid fa-wrench"></i> Maintenance</a>
        </div>
      </div>

      <a href="<?= base_url('tools') ?>" class="<?= navActive('tools') ?>"><i class="fa-solid fa-boxes-stacked"></i> Tools Management</a>
      <a href="<?= base_url('vehicles') ?>" class="<?= navActive('vehicles') ?>"><i class="fa-solid fa-truck"></i> Vehicle Management</a>

      <?php $isTravelSection = (strpos(uri_string(), 'travel') === 0 || strpos(uri_string(), 'gps') === 0); ?>
      <div class="nav-parent-group <?= $isTravelSection ? 'open' : '' ?>">
        <a href="<?= base_url('travel') ?>" class="nav-parent-link <?= $isTravelSection ? 'active' : '' ?>" data-travel-toggle>
          <i class="fa-solid fa-route"></i>
          <span>Travel Management</span>
          <i class="fa-solid fa-chevron-down nav-parent-caret"></i>
        </a>
        <div class="nav-submenu" id="travel-submenu">
          <a href="<?= base_url('travel') ?>" class="<?= navActive('travel') ?>"><i class="fa-solid fa-plane-departure"></i> Driver's Trip Ticket</a>
          <a href="<?= base_url('gps') ?>" class="<?= navActive('gps') ?>"><i class="fa-solid fa-location-dot"></i> GPS Tracker</a>
        </div>
      </div>

      <a href="<?= base_url('safety') ?>" class="<?= navActive('safety') ?>"><i class="fa-solid fa-hard-hat"></i> Safety Maintenance</a>
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

  <!-- ============================ MAIN CONTENT ========================= -->
  <div class="main-content">
    <header class="topbar">
      <div class="search"><i class="fa-solid fa-magnifying-glass"></i>
        <input type="text" class="search-box" placeholder="Search operational resources...">
      </div>
      <div class="topbar-right">
        <span class="date"><i class="fa-regular fa-calendar"></i> <?= date('l, F d, Y') ?></span>
        <a href="<?= base_url('notifications') ?>" class="icon-btn"><i class="fa-regular fa-bell"></i></a>
        <a href="<?= base_url('auth/logout') ?>" class="icon-btn" title="Logout"><i class="fa-solid fa-right-from-bracket"></i></a>
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
// generic modal helpers used across pages
function openModal(id){ document.getElementById(id).classList.add('open'); }
function closeModal(id){ document.getElementById(id).classList.remove('open'); }
document.querySelectorAll('.modal').forEach(m=>m.addEventListener('click',e=>{ if(e.target===m) m.classList.remove('open'); }));

const personnelLink = document.querySelector('[data-personnel-toggle]');
const personnelGroup = document.querySelectorAll('.nav-parent-group')[0];
if (personnelLink && personnelGroup) {
  personnelLink.addEventListener('click', (event) => {
    event.preventDefault();
    personnelGroup.classList.toggle('open');
    personnelLink.classList.toggle('open');
  });
}

const travelLink = document.querySelector('[data-travel-toggle]');
const travelGroup = document.querySelectorAll('.nav-parent-group')[1];
if (travelLink && travelGroup) {
  travelLink.addEventListener('click', (event) => {
    event.preventDefault();
    travelGroup.classList.toggle('open');
    travelLink.classList.toggle('open');
  });
}

const safetyLink = document.querySelector('[data-safety-toggle]');
const safetyGroup = document.querySelectorAll('.nav-parent-group')[2];
if (safetyLink && safetyGroup) {
  safetyLink.addEventListener('click', (event) => {
    event.preventDefault();
    safetyGroup.classList.toggle('open');
    safetyLink.classList.toggle('open');
  });
}
</script>
<?= $this->renderSection('scripts') ?>
</body>
</html>
