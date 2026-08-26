<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<?php
$personnelDetail = $personnelDetail ?? [];
$jobOrders = $jobOrders ?? [];
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
        <p>Centralized, automated monitoring of Job Order and project-based personnel — so nothing relies on manual tracking. Click any number below to see the records behind it.</p>
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
  <a href="<?= base_url('personnel/job-orders') ?>" class="stat-back-btn"><i class="fa-solid fa-file-contract"></i> Manage Job Orders</a>
</div>

<div class="role-dash-panel">
    <div class="role-dash-panel-title"><i class="bi bi-people"></i> Job Order Personnel Monitoring</div>
    <div class="stat-cards">
        <div class="stat-card stat-card-clickable" role="button" tabindex="0" onclick="filterMonitoringTable('personnelDetailTable','assignmentStatus','')"><span class="stat-icon tone-maroon"><i class="fa-solid fa-users"></i></span><h3>Total Job Order Personnel</h3><div class="value"><?= (int) $total_jo_personnel ?></div></div>
        <div class="stat-card stat-card-clickable" role="button" tabindex="0" onclick="filterMonitoringTable('personnelDetailTable','assignmentStatus','ACTIVE')"><span class="stat-icon tone-green"><i class="fa-solid fa-circle-check"></i></span><h3>Active</h3><div class="value"><?= (int) $active_assignments ?></div></div>
        <div class="stat-card stat-card-clickable" role="button" tabindex="0" onclick="filterMonitoringTable('personnelDetailTable','contractStatus','EXPIRING_SOON')"><span class="stat-icon tone-gold"><i class="fa-solid fa-hourglass-half"></i></span><h3>Expiring Soon</h3><div class="value"><?= (int) $contract_counts['EXPIRING_SOON'] ?></div></div>
        <div class="stat-card stat-card-clickable" role="button" tabindex="0" onclick="filterMonitoringTable('personnelDetailTable','contractStatus','EXPIRED')"><span class="stat-icon tone-red"><i class="fa-solid fa-triangle-exclamation"></i></span><h3>Expired</h3><div class="value"><?= (int) $contract_counts['EXPIRED'] ?></div></div>
        <div class="stat-card stat-card-clickable" role="button" tabindex="0" onclick="filterMonitoringTable('personnelDetailTable','assignmentStatus','INACTIVE')"><span class="stat-icon tone-neutral"><i class="fa-solid fa-user-slash"></i></span><h3>Inactive</h3><div class="value"><?= (int) $inactive_assignments ?></div></div>
    </div>
</div>

<div class="role-dash-panel">
    <div class="role-dash-panel-title"><i class="bi bi-file-earmark-text"></i> Job Order Monitoring</div>
    <div class="stat-cards">
        <div class="stat-card stat-card-clickable" role="button" tabindex="0" onclick="filterMonitoringTable('jobOrdersTable','status','')"><span class="stat-icon tone-maroon"><i class="fa-solid fa-file-contract"></i></span><h3>Total Job Orders</h3><div class="value"><?= (int) $total_job_orders ?></div></div>
        <div class="stat-card stat-card-clickable" role="button" tabindex="0" onclick="filterMonitoringTable('jobOrdersTable','status','ACTIVE')"><span class="stat-icon tone-green"><i class="fa-solid fa-circle-check"></i></span><h3>Active</h3><div class="value"><?= (int) $job_order_counts['ACTIVE'] ?></div></div>
        <div class="stat-card stat-card-clickable" role="button" tabindex="0" onclick="filterMonitoringTable('jobOrdersTable','status','EXPIRING_SOON')"><span class="stat-icon tone-gold"><i class="fa-solid fa-hourglass-half"></i></span><h3>Expiring Soon</h3><div class="value"><?= (int) $job_order_counts['EXPIRING_SOON'] ?></div></div>
        <div class="stat-card stat-card-clickable" role="button" tabindex="0" onclick="filterMonitoringTable('jobOrdersTable','status','EXPIRED')"><span class="stat-icon tone-red"><i class="fa-solid fa-triangle-exclamation"></i></span><h3>Expired</h3><div class="value"><?= (int) $job_order_counts['EXPIRED'] ?></div></div>
    </div>
</div>

<div class="role-dash-panel">
    <div class="role-dash-panel-title"><i class="bi bi-file-earmark-check"></i> Document Monitoring</div>
    <div class="stat-cards">
        <div class="stat-card stat-card-clickable" role="button" tabindex="0" onclick="filterMonitoringTable('personnelDetailTable','docStatus','COMPLETE')"><span class="stat-icon tone-green"><i class="fa-solid fa-circle-check"></i></span><h3>Complete</h3><div class="value"><?= (int) $complete_docs ?></div></div>
        <div class="stat-card stat-card-clickable" role="button" tabindex="0" onclick="filterMonitoringTable('personnelDetailTable','docStatus','INCOMPLETE')"><span class="stat-icon tone-gold"><i class="fa-solid fa-file-circle-exclamation"></i></span><h3>Incomplete</h3><div class="value"><?= (int) $incomplete_docs ?></div></div>
        <div class="stat-card stat-card-clickable" role="button" tabindex="0" onclick="filterMonitoringTable('personnelDetailTable','hasExpiredDoc','1')"><span class="stat-icon tone-red"><i class="fa-solid fa-file-circle-xmark"></i></span><h3>Expired</h3><div class="value"><?= (int) $expired_docs ?></div></div>
    </div>
</div>

<div class="table-card" id="personnelDetailTableCard">
  <div class="table-toolbar">
    <div class="toolbar-left"><h3 style="margin:0;">Job Order Personnel</h3></div>
    <div class="toolbar-right"><span id="personnelDetailTableLabel" style="color:var(--muted,#888); font-size:13px;">Showing all</span></div>
  </div>
  <div class="personnel-table-scroll">
  <table id="personnelDetailTable" class="data-table">
    <thead><tr><th>Personnel</th><th>Job Order</th><th>Assignment</th><th>Contract</th><th>Documents</th></tr></thead>
    <tbody>
      <?php if (!empty($personnelDetail)): ?>
        <?php foreach ($personnelDetail as $row): ?>
          <tr data-row="1"
              data-assignment-status="<?= esc($row['assignment_status']) ?>"
              data-contract-status="<?= esc($row['contract_status'] ?? '') ?>"
              data-doc-status="<?= $row['doc_complete'] ? 'COMPLETE' : 'INCOMPLETE' ?>"
              data-has-expired-doc="<?= $row['has_expired_doc'] ? '1' : '0' ?>">
            <td><a href="<?= base_url('personnel/view/' . $row['id']) ?>"><?= esc($row['full_name']) ?></a><br><small><?= esc($row['emp_id']) ?></small></td>
            <td><?= esc($row['job_order_number'] ?: '—') ?><br><small><?= esc($row['job_order_title'] ?: '') ?></small></td>
            <td><span class="status-badge <?= moStatusClass($row['assignment_status'] === 'ACTIVE' ? 'ACTIVE' : 'EXPIRED') ?>"><?= esc($row['assignment_status']) ?></span></td>
            <td>
              <?php if ($row['contract_status']): ?>
                <span class="status-badge <?= moStatusClass($row['contract_status']) ?>"><?= esc(str_replace('_',' ',$row['contract_status'])) ?></span>
                <?php if ($row['contract_days_remaining'] !== null && $row['contract_status'] !== 'TERMINATED'): ?><br><small><?= (int) $row['contract_days_remaining'] ?> day(s)</small><?php endif; ?>
              <?php else: ?>—<?php endif; ?>
            </td>
            <td>
              <span class="status-badge <?= $row['doc_complete'] ? 'status-active' : 'status-pending' ?>"><?= (int) $row['doc_verified'] ?>/<?= (int) $row['doc_required'] ?></span>
              <?php if ($row['has_expired_doc']): ?> <span class="status-badge status-critical">Expired doc</span><?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
      <?php else: ?>
        <tr><td colspan="5">No Job Order personnel yet.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
  </div>
</div>

<div class="table-card" id="jobOrdersTableCard">
  <div class="table-toolbar">
    <div class="toolbar-left"><h3 style="margin:0;">Job Orders</h3></div>
    <div class="toolbar-right"><span id="jobOrdersTableLabel" style="color:var(--muted,#888); font-size:13px;">Showing all</span></div>
  </div>
  <div class="personnel-table-scroll">
  <table id="jobOrdersTable" class="data-table">
    <thead><tr><th>Job Order #</th><th>Title</th><th>Required</th><th>Assigned</th><th>Remaining</th><th>Status</th></tr></thead>
    <tbody>
      <?php if (!empty($jobOrders)): ?>
        <?php foreach ($jobOrders as $jo): ?>
          <?php $remaining = max(0, (int) $jo['personnel_required'] - (int) $jo['assigned_count']); ?>
          <tr data-row="1" data-status="<?= esc($jo['status']) ?>">
            <td><a href="<?= base_url('personnel/job-orders/view/' . $jo['id']) ?>"><?= esc($jo['job_order_number']) ?></a></td>
            <td><?= esc($jo['job_order_title']) ?></td>
            <td><?= (int) $jo['personnel_required'] ?></td>
            <td><?= (int) $jo['assigned_count'] ?></td>
            <td><?= $remaining > 0 ? '<strong>' . $remaining . '</strong>' : $remaining ?></td>
            <td><span class="status-badge <?= moStatusClass($jo['status']) ?>"><?= esc(str_replace('_',' ',$jo['status'])) ?></span></td>
          </tr>
        <?php endforeach; ?>
      <?php else: ?>
        <tr><td colspan="6">No Job Orders yet.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
  </div>
</div>

<script>
// Every stat card above targets one of the two tables here and filters its
// rows by a single data-* facet (assignment status, contract status,
// document completeness, or Job Order status) — clicking a number shows
// exactly the records that make it up, instead of the number standing alone.
function filterMonitoringTable(tableId, field, value) {
  const attr = 'data-' + field.replace(/([A-Z])/g, '-$1').toLowerCase();
  document.querySelectorAll('#' + tableId + ' tbody tr[data-row]').forEach(row => {
    const match = value === '' || row.getAttribute(attr) === value;
    row.style.display = match ? '' : 'none';
  });
  const label = document.getElementById(tableId + 'Label');
  if (label) {
    label.textContent = value === '' ? 'Showing all' : 'Filtered: ' + value.replace(/_/g, ' ');
  }
  document.getElementById(tableId + 'Card')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
}
</script>

<?= $this->endSection() ?>
