<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<?php
$totalAlerts = (int) $contract_counts['EXPIRING_SOON'] + (int) $contract_counts['EXPIRED']
    + (int) $job_order_counts['EXPIRING_SOON'] + (int) $job_order_counts['EXPIRED']
    + (int) $incomplete_docs;
function moStatusClass($status) {
  return match(strtoupper((string) $status)) {
    'ACTIVE' => 'status-active',
    'EXPIRING_SOON' => 'status-pending',
    'EXPIRED' => 'status-critical',
    default => 'status-inactive',
  };
}
?>

<div class="role-dash-hero">
    <div class="role-dash-hero-text">
        <h1>Job Order Personnel Monitoring<?= !empty($full_name) ? ', ' . esc($full_name) : '' ?></h1>
        <p>Centralized, automated monitoring of Job Order and project-based personnel — so nothing relies on manual tracking.</p>
        <span class="role-dash-hero-time"><i class="bi bi-clock-history"></i> <?= esc($last_updated) ?></span>
    </div>
    <div class="role-dash-hero-status <?= $totalAlerts > 0 ? 'is-alert' : 'is-clear' ?>">
        <i class="bi <?= $totalAlerts > 0 ? 'bi-exclamation-triangle-fill' : 'bi-shield-check' ?>"></i>
        <div>
            <strong><?= $totalAlerts > 0 ? $totalAlerts . ' Item' . ($totalAlerts === 1 ? '' : 's') . ' Need Attention' : 'All Systems Normal' ?></strong>
            <small><?= $totalAlerts > 0 ? 'Expiring/expired contracts, Job Orders, or incomplete documents' : 'No issues right now' ?></small>
        </div>
    </div>
</div>

<div class="stat-back-bar">
  <a href="<?= base_url('personnel/job-orders') ?>" class="stat-back-btn"><i class="fa-solid fa-file-contract"></i> Job Orders</a>
</div>

<div class="role-dash-panel">
    <div class="role-dash-panel-title"><i class="bi bi-people"></i> Job Order Personnel Monitoring</div>
    <div class="stat-cards">
        <div class="stat-card"><span class="stat-icon tone-maroon"><i class="fa-solid fa-users"></i></span><h3>Total Job Order Personnel</h3><div class="value"><?= (int) $total_jo_personnel ?></div></div>
        <div class="stat-card"><span class="stat-icon tone-green"><i class="fa-solid fa-circle-check"></i></span><h3>Active</h3><div class="value"><?= (int) $active_assignments ?></div></div>
        <div class="stat-card"><span class="stat-icon tone-gold"><i class="fa-solid fa-hourglass-half"></i></span><h3>Expiring Soon</h3><div class="value"><?= (int) $contract_counts['EXPIRING_SOON'] ?></div></div>
        <div class="stat-card"><span class="stat-icon tone-red"><i class="fa-solid fa-triangle-exclamation"></i></span><h3>Expired</h3><div class="value"><?= (int) $contract_counts['EXPIRED'] ?></div></div>
        <div class="stat-card"><span class="stat-icon tone-neutral"><i class="fa-solid fa-user-slash"></i></span><h3>Inactive</h3><div class="value"><?= (int) $inactive_assignments ?></div></div>
    </div>
</div>

<div class="role-dash-panel">
    <div class="role-dash-panel-title"><i class="bi bi-file-earmark-text"></i> Job Order Monitoring</div>
    <div class="stat-cards">
        <div class="stat-card stat-card-clickable" onclick="window.location.href='<?= base_url('personnel/job-orders') ?>'" role="button" tabindex="0"><span class="stat-icon tone-maroon"><i class="fa-solid fa-file-contract"></i></span><h3>Total Job Orders</h3><div class="value"><?= (int) $total_job_orders ?></div></div>
        <div class="stat-card"><span class="stat-icon tone-green"><i class="fa-solid fa-circle-check"></i></span><h3>Active</h3><div class="value"><?= (int) $job_order_counts['ACTIVE'] ?></div></div>
        <div class="stat-card"><span class="stat-icon tone-gold"><i class="fa-solid fa-hourglass-half"></i></span><h3>Expiring Soon</h3><div class="value"><?= (int) $job_order_counts['EXPIRING_SOON'] ?></div></div>
        <div class="stat-card"><span class="stat-icon tone-red"><i class="fa-solid fa-triangle-exclamation"></i></span><h3>Expired</h3><div class="value"><?= (int) $job_order_counts['EXPIRED'] ?></div></div>
    </div>
</div>

<div class="role-dash-panel">
    <div class="role-dash-panel-title"><i class="bi bi-file-earmark-check"></i> Document Monitoring</div>
    <div class="stat-cards">
        <div class="stat-card"><span class="stat-icon tone-green"><i class="fa-solid fa-circle-check"></i></span><h3>Complete</h3><div class="value"><?= (int) $complete_docs ?></div></div>
        <div class="stat-card"><span class="stat-icon tone-gold"><i class="fa-solid fa-file-circle-exclamation"></i></span><h3>Incomplete</h3><div class="value"><?= (int) $incomplete_docs ?></div></div>
        <div class="stat-card"><span class="stat-icon tone-red"><i class="fa-solid fa-file-circle-xmark"></i></span><h3>Expired</h3><div class="value"><?= (int) $expired_docs ?></div></div>
    </div>
</div>

<?php if (!empty($understaffedJobOrders)): ?>
<div class="table-card">
  <div class="table-toolbar"><div class="toolbar-left"><h3 style="margin:0;">Job Orders Needing Additional Personnel</h3></div></div>
  <div class="personnel-table-scroll">
  <table class="data-table">
    <thead><tr><th>Job Order #</th><th>Title</th><th>Required</th><th>Assigned</th><th>Remaining</th></tr></thead>
    <tbody>
      <?php foreach ($understaffedJobOrders as $jo): ?>
        <tr>
          <td><a href="<?= base_url('personnel/job-orders/view/' . $jo['id']) ?>"><?= esc($jo['job_order_number']) ?></a></td>
          <td><?= esc($jo['job_order_title']) ?></td>
          <td><?= (int) $jo['personnel_required'] ?></td>
          <td><?= (int) $jo['assigned_count'] ?></td>
          <td><strong><?= max(0, (int) $jo['personnel_required'] - (int) $jo['assigned_count']) ?></strong></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  </div>
</div>
<?php endif; ?>

<?php if (!empty($expiringContracts) || !empty($expiredContracts)): ?>
<div class="table-card">
  <div class="table-toolbar"><div class="toolbar-left"><h3 style="margin:0;">Contracts Requiring Attention</h3></div></div>
  <div class="personnel-table-scroll">
  <table class="data-table">
    <thead><tr><th>Personnel</th><th>Job Order</th><th>Period</th><th>Days Remaining</th><th>Status</th></tr></thead>
    <tbody>
      <?php foreach (array_merge($expiredContracts, $expiringContracts) as $c): ?>
        <tr>
          <td><a href="<?= base_url('personnel/view/' . $c['personnel_id']) ?>"><?= esc($c['full_name'] ?? '—') ?></a></td>
          <td><?= esc($c['job_order_number'] ?? '—') ?></td>
          <td><?= esc($c['contract_start_date'] ?: '—') ?> &rarr; <?= esc($c['contract_end_date'] ?: '—') ?></td>
          <td><?= (int) $c['days_remaining'] ?> day(s)</td>
          <td><span class="status-badge <?= moStatusClass($c['contract_status']) ?>"><?= esc(str_replace('_',' ',$c['contract_status'])) ?></span></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  </div>
</div>
<?php endif; ?>

<?= $this->endSection() ?>
