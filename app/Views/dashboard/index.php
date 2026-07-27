<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="page-header">
  <h1>Main Dashboard</h1>
  <p class="page-subtitle">Real-time systemic oversight and asset analytics.</p>
</div>

<!-- ── ROW 1: KPI STAT CARDS ──────────────────────────────── -->
<div class="stat-cards">

  <div class="stat-card">
    <div class="label"><i class="fa-solid fa-boxes-stacked"></i> Total Tools</div>
    <div class="value"><?= $total_tools ?? 0 ?></div>
    <div class="trend neutral">Tools Inventory</div>
  </div>

  <div class="stat-card">
    <div class="label"><i class="fa-solid fa-truck"></i> Vehicle Fleet</div>
    <div class="value"><?= $total_vehicles ?? 0 ?></div>
    <div class="trend neutral">Registered vehicles</div>
  </div>

  <div class="stat-card">
    <div class="label"><i class="fa-solid fa-route"></i> Scheduled Trips</div>
    <div class="value"><?= $scheduled_trips ?? 0 ?></div>
    <div class="trend neutral">Active requests</div>
  </div>

  <div class="stat-card">
    <div class="label"><i class="fa-solid fa-users"></i> Active Personnel</div>
    <div class="value"><?= $total_personnel ?? 0 ?></div>
    <div class="trend neutral">On duty today</div>
  </div>

  <div class="stat-card accent-red">
    <div class="label"><i class="fa-solid fa-fire"></i> Fire Extinguishers</div>
    <div class="value">21</div>
    <div class="trend down">3 need attention</div>
    <a href="<?= base_url('safety') ?>" class="sc-link">View Registry →</a>
  </div>

  <div class="stat-card accent-amber">
    <div class="label"><i class="fa-solid fa-broom"></i> Areas Being Cleaned</div>
    <div class="value">8</div>
    <div class="trend neutral attention-text">8 areas already cleaned</div>
    <div class="dash-cleaning-list">
      <span class="status-chip success">8 Cleaned</span>
      <span class="status-chip danger">2 Pending</span>
    </div>
    <a href="<?= base_url('safety') ?>" class="sc-link">View Map →</a>
  </div>

  <div class="stat-card">
    <div class="label"><i class="fa-solid fa-door-open"></i> Janitorial Zones</div>
    <div class="value">8</div>
    <div class="trend neutral">8 cleaned • 2 pending</div>
    <a href="<?= base_url('janitorial') ?>" class="sc-link">View Zones →</a>
  </div>

  <div class="stat-card">
    <div class="label"><i class="fa-solid fa-key"></i> Active Key Borrowers</div>
    <div class="value">2</div>
    <div class="trend neutral">Guard log updated</div>
    <a href="<?= base_url('safety/guard-dashboard') ?>" class="sc-link">View Logs →</a>
  </div>

  <div class="stat-card">
    <div class="label"><i class="fa-solid fa-wrench"></i> Open Work Orders</div>
    <div class="value">3</div>
    <div class="trend warning">1 critical</div>
    <a href="<?= base_url('safety') ?>" class="sc-link">View Orders →</a>
  </div>
</div>

<!-- ── ROW 2: MAIN CONTENT GRID ────────────────────────────── -->
<div class="dash-grid">

  <!-- Active Vehicle Status -->
  <div class="card">
    <div class="card-head">
      <span class="card-title"><i class="fa-solid fa-location-dot" style="color:var(--maroon)"></i> Active Vehicle Status &amp; GPS Tracker</span>
      <a href="<?= base_url('vehicles') ?>" class="card-link">View All</a>
    </div>
    <table class="data-table">
      <thead>
        <tr>
          <th>Vehicle Plate</th>
          <th>Assigned Driver</th>
          <th>Destination</th>
          <th>Status</th>
        </tr>
      </thead>
      <tbody>
        <?php if (!empty($active_vehicles)): ?>
          <?php foreach ($active_vehicles as $v): ?>
            <tr>
              <td><strong><?= esc($v['plate_no']) ?></strong></td>
              <td><?= esc($v['driver']) ?></td>
              <td>—</td>
              <td><span class="status-badge status-active"><?= esc($v['availability']) ?></span></td>
            </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr><td colspan="4" class="empty-cell">No active vehicles at this time.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

</div>



<?= $this->endSection() ?>