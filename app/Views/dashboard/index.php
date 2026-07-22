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
    <div class="trend neutral">3 zones complete</div>
    <a href="<?= base_url('safety') ?>" class="sc-link">View Map →</a>
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

  <!-- Safety & Janitorial Overview -->
  <div class="card dash-safety-panel">
    <div class="card-head">
      <span class="card-title"><i class="fa-solid fa-shield-halved" style="color:var(--maroon)"></i> Safety &amp; Janitorial Overview</span>
      <a href="<?= base_url('safety') ?>" class="card-link">Open Module</a>
    </div>
    <div class="dsafe-grid">
      <a href="<?= base_url('safety') ?>" class="dsafe-item dsafe-fire">
        <i class="fa-solid fa-fire"></i>
        <div>
          <div class="dsafe-val">21</div>
          <div class="dsafe-lbl">Fire Extinguishers</div>
          <div class="dsafe-sub">3 need attention</div>
        </div>
      </a>
      <a href="<?= base_url('safety') ?>" class="dsafe-item dsafe-clean">
        <i class="fa-solid fa-broom"></i>
        <div>
          <div class="dsafe-val">8</div>
          <div class="dsafe-lbl">Janitorial Zones</div>
          <div class="dsafe-sub">3 fully complete</div>
        </div>
      </a>
      <a href="<?= base_url('safety') ?>" class="dsafe-item dsafe-key">
        <i class="fa-solid fa-key"></i>
        <div>
          <div class="dsafe-val">2</div>
          <div class="dsafe-lbl">Active Key Borrows</div>
          <div class="dsafe-sub">Guard log updated</div>
        </div>
      </a>
      <a href="<?= base_url('safety') ?>" class="dsafe-item dsafe-wo">
        <i class="fa-solid fa-wrench"></i>
        <div>
          <div class="dsafe-val">3</div>
          <div class="dsafe-lbl">Open Work Orders</div>
          <div class="dsafe-sub">1 critical</div>
        </div>
      </a>
    </div>
    <a href="<?= base_url('safety') ?>" class="dsafe-btn">
      <i class="fa-solid fa-arrow-right"></i> Open Safety &amp; Janitorial Module
    </a>
  </div>

</div>



<?= $this->endSection() ?>