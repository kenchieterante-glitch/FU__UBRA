<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<?php
helper('facilities');
$status = $status ?? [];
$active_keys = $active_keys ?? [];
$gate_trips = $gate_trips ?? [];
$events = array_slice($events ?? [], 0, 10);
?>

<div class="page-header">
  <div>
    <h1>Guard Station</h1>
    <p class="page-subtitle">Lend and receive keys, and verify vehicles leaving and coming back.</p>
  </div>
</div>

<div class="stat-cards">
  <?php foreach ($status as $s): ?>
    <div class="stat-card">
      <span class="stat-icon tone-<?= esc($s['tone']) ?>"><i class="bi <?= esc($s['icon']) ?>"></i></span>
      <h3><?= esc($s['label']) ?></h3>
      <div class="value"><?= esc((string) $s['value']) ?></div>
    </div>
  <?php endforeach; ?>
</div>

<?= view('security_dept/_guard_desk', ['deskBase' => 'guard-station', 'barcodeUrl' => 'guard-station/barcodes', 'guard_keys' => $guard_keys ?? []]) ?>

<div class="guard-card" style="padding:18px;margin-bottom:16px;">
  <div class="dp-section-title" style="margin-top:0"><i class="bi bi-key-fill"></i> Keys Currently Out</div>
  <div class="table-wrap">
    <table class="sj-table">
      <thead><tr><th>Log #</th><th>Borrower</th><th>Department</th><th>Key Item</th><th>Date Borrowed</th><th>Status</th></tr></thead>
      <tbody id="gKeysOutBody">
        <?php if (empty($active_keys)): ?>
          <tr><td colspan="6" class="empty-row">No keys are out right now.</td></tr>
        <?php else: foreach ($active_keys as $l): ?>
          <tr>
            <td><strong><?= esc($l['log_number']) ?></strong></td>
            <td><?= esc($l['full_name']) ?></td>
            <td><?= esc($l['department']) ?></td>
            <td><?= esc($l['key_item']) ?></td>
            <td><?= date('M d, Y g:i A', strtotime($l['scan_in'])) ?></td>
            <?= fac_cell('Borrowed') ?>
          </tr>
        <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
</div>

<div class="guard-card" style="padding:18px;margin-bottom:16px;">
  <div class="dp-section-title" style="margin-top:0"><i class="bi bi-truck"></i> Vehicles at the Gate</div>
  <div class="table-wrap">
    <table class="sj-table">
      <thead><tr><th>Trip ID</th><th>Requester</th><th>Destination</th><th>Assigned Driver</th><th>Departure</th><th>Gate Status</th><th>Action</th></tr></thead>
      <tbody>
        <?php if (empty($gate_trips)): ?>
          <tr><td colspan="7" class="empty-row">No approved trips waiting at the gate.</td></tr>
        <?php else: foreach ($gate_trips as $t): $out = $t['status'] === 'In Transit'; ?>
          <tr>
            <td><strong><?= esc($t['trip_id']) ?></strong></td>
            <td><?= esc($t['requester_name'] ?? 'Unknown') ?></td>
            <td><?= esc($t['destination']) ?></td>
            <td><?= esc($t['driver_name'] ?? 'Unassigned') ?><br><small><?= esc($t['plate_no'] ?? 'No vehicle') ?></small></td>
            <td><?= date('M d, h:i A', strtotime($t['travel_date'] . ' ' . $t['departure_time'])) ?></td>
            <?= fac_cell($out ? 'Vehicle Out' : 'Awaiting Dispatch') ?>
            <td>
              <form method="post" action="<?= base_url(($out ? 'travel/checkout/' : 'travel/checkin/') . $t['id']) ?>" style="display:inline;">
                <?= csrf_field() ?>
                <button type="submit" class="status-badge <?= $out ? 'status-completed' : 'status-pending' ?> fac-action-pill"><?= $out ? 'Verify Return' : 'Verify Exit' ?></button>
              </form>
            </td>
          </tr>
        <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
</div>

<div class="guard-card" style="padding:18px;margin-bottom:16px;">
  <div class="dp-section-title" style="margin-top:0"><i class="bi bi-journal-text"></i> Recent Activity</div>
  <div class="table-wrap">
    <table class="sj-table">
      <thead><tr><th>Date</th><th>Time</th><th>Guard</th><th>Activity</th></tr></thead>
      <tbody>
        <?php if (empty($events)): ?>
          <tr><td colspan="4" class="empty-row">No activity yet.</td></tr>
        <?php else: foreach ($events as $e): ?>
          <tr>
            <td><?= date('M d, Y', strtotime($e['time'])) ?></td>
            <td><?= date('g:i A', strtotime($e['time'])) ?></td>
            <td><?= esc($e['guard']) ?></td>
            <td><?= esc($e['action']) ?></td>
          </tr>
        <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
</div>
<?= $this->endSection() ?>
