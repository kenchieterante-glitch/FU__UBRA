<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<?php
  $title = $title ?? 'Guard Dashboard';
  $trips = $trips ?? [];
  $key_logs_json = $key_logs_json ?? '[]';
  $activity_json = $activity_json ?? '[]';
?>

<link rel="stylesheet" href="<?= base_url('Assets/css/safety.css') . '?v=' . @filemtime(FCPATH.'Assets/css/safety.css') ?>">

<div class="page-header">
  <div>
    <h1><?= esc($title) ?></h1>
    <p class="page-subtitle">Overview of guard activity, shifts, key issuance, and dashboard metrics.</p>
  </div>
</div>

<div class="guard-grid">
  <div class="guard-main-column">
    <div class="guard-card">
      <div class="pane-header">
        <div class="pane-title"><i class="bi bi-person-badge-fill"></i> Security Guard Dashboard</div>
        <div class="pane-actions">
          <span class="guard-shift">On Duty: <strong>Guard Santos, J.</strong></span>
          <span class="guard-time" id="guardClock"></span>
        </div>
      </div>
    </div>

    <div class="guard-card guard-card-full">
      <div class="gc-title"><i class="bi bi-ticket-perforated-fill"></i> Approved Trip Tickets — Check In / Check Out</div>
      <div class="table-wrap">
        <table class="sj-table">
          <thead>
            <tr><th>Trip ID</th><th>Requester</th><th>Destination</th><th>Driver / Vehicle</th><th>Departure</th><th>Gate Status</th><th>Action</th></tr>
          </thead>
          <tbody>
            <?php if (empty($trips)): ?>
              <tr><td colspan="7" class="empty-row">No approved trip tickets awaiting gate action.</td></tr>
            <?php else: ?>
              <?php foreach ($trips as $trip): ?>
                <tr>
                  <td><strong><?= esc($trip['trip_id']) ?></strong></td>
                  <td><?= esc($trip['requester_name'] ?? 'Unknown') ?></td>
                  <td><?= esc($trip['destination']) ?></td>
                  <td>
                    <?= esc($trip['driver_name'] ?? 'Unassigned') ?><br>
                    <small><?= esc($trip['plate_no'] ?? 'No vehicle') ?></small>
                  </td>
                  <td><?= date('M d, h:i A', strtotime($trip['travel_date'] . ' ' . $trip['departure_time'])) ?></td>
                  <td>
                    <?php if (!empty($trip['check_out_time'])): ?>
                      <span class="tt-badge tt-completed">Returned <?= date('h:i A', strtotime($trip['check_out_time'])) ?></span>
                    <?php elseif (!empty($trip['check_in_time'])): ?>
                      <span class="tt-badge tt-released">Out since <?= date('h:i A', strtotime($trip['check_in_time'])) ?></span>
                    <?php else: ?>
                      <span class="tt-badge tt-pending">Awaiting Dispatch</span>
                    <?php endif; ?>
                  </td>
                  <td>
                    <?php if (empty($trip['check_in_time'])): ?>
                      <form method="post" action="<?= base_url('travel/checkin/'.$trip['id']) ?>" style="display:contents;">
                        <?= csrf_field() ?>
                        <button type="submit" class="btn-primary btn-sm">Check In</button>
                      </form>
                    <?php elseif (empty($trip['check_out_time'])): ?>
                      <form method="post" action="<?= base_url('travel/checkout/'.$trip['id']) ?>" style="display:contents;">
                        <?= csrf_field() ?>
                        <button type="submit" class="btn-submit btn-sm">Check Out</button>
                      </form>
                    <?php else: ?>
                      <span class="text-muted">Trip Complete</span>
                    <?php endif; ?>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

    <div class="guard-card guard-card-full">
      <div class="gc-title"><i class="bi bi-key-fill"></i> Keylogs</div>
      <div class="table-wrap">
        <table class="sj-table">
          <thead>
            <tr><th>Log #</th><th>Name</th><th>Key</th><th>Status</th></tr>
          </thead>
          <tbody id="guardKeylogBody"></tbody>
        </table>
      </div>
    </div>
  </div>

  <div class="guard-side-column">
    <div class="guard-card">
      <div class="gc-title"><i class="bi bi-key-fill"></i> Currently Borrowed Keys</div>
      <div id="activeBorrows" class="borrow-cards"></div>
    </div>

    <div class="guard-card guard-card-narrow">
      <div class="gc-title"><i class="bi bi-activity"></i> Guard Activity Log</div>
      <ul class="act-log" id="guardActLog"></ul>
    </div>
  </div>
</div>

<script>
// Key logs and the activity feed come from the database — see SafetyController::guardDashboard().
const keyLogs = <?= $key_logs_json ?>;
const guardActLog = <?= $activity_json ?>;

function renderGuardDashboard() {
  const active = keyLogs.filter(k => k.status === 'Active');
  document.getElementById('activeBorrows').innerHTML = active.length
    ? active.map(k => `
      <div class="borrow-card">
        <div class="bc-av">${k.name.charAt(0)}</div>
        <div class="bc-info">
          <div class="bc-name">${k.name}</div>
          <div class="bc-dept">${k.dept}</div>
          <div class="bc-key"><i class="bi bi-key-fill"></i> ${k.key}</div>
          <div class="bc-time"><i class="bi bi-clock"></i> Since ${k.inTime}</div>
        </div>
      </div>`).join('')
    : '<div class="no-data">No active key borrows.</div>';

  document.getElementById('guardActLog').innerHTML = guardActLog.map(l => `
    <li><span class="al-time">${l.time}</span> ${l.action}</li>`).join('');

  document.getElementById('guardKeylogBody').innerHTML = keyLogs.map(k => `
    <tr>
      <td><strong>${k.id}</strong></td>
      <td>${k.name}</td>
      <td>${k.key}</td>
      <td><span class="kl-badge ${k.status === 'Active' ? 'kl-active' : 'kl-done'}">${k.status}</span></td>
    </tr>`).join('');

  updateGuardClock();
}

function updateGuardClock() {
  const el = document.getElementById('guardClock');
  if (el) el.textContent = new Date().toLocaleTimeString('en-PH', { hour:'2-digit', minute:'2-digit', second:'2-digit' });
}

setInterval(updateGuardClock, 1000);
window.addEventListener('DOMContentLoaded', renderGuardDashboard);
</script>

<?= $this->endSection() ?>
