<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<?php
$jobOrder = $jobOrder ?? [];
$assignments = $assignments ?? [];
$availablePersonnel = $availablePersonnel ?? [];
function joStatusClass2($status) {
  return match(strtoupper((string) $status)) {
    'ACTIVE','COMPLETED' => 'status-active',
    'EXPIRING_SOON','PENDING','FOR_RENEWAL','DRAFT','TRANSFERRED' => 'status-pending',
    'EXPIRED','CANCELLED','TERMINATED' => 'status-critical',
    default => 'status-inactive',
  };
}
$remaining = max(0, (int) $jobOrder['personnel_required'] - (int) $jobOrder['assigned_count']);
?>

<div class="page-header">
  <div>
    <h1><?= esc($jobOrder['job_order_number']) ?></h1>
    <p class="page-subtitle"><?= esc($jobOrder['job_order_title']) ?></p>
  </div>
  <div class="action-buttons">
    <a href="<?= base_url('personnel/job-orders') ?>" class="btn-add" style="background:#fff;color:var(--maroon);border:1px solid var(--maroon);"><i class="bi bi-arrow-left"></i> Back to Job Orders</a>
    <button class="btn-add" onclick="document.getElementById('assignPersonnelModal').style.display='flex'">+ Assign Personnel</button>
  </div>
</div>

<div class="stat-cards">
  <div class="stat-card">
    <span class="stat-icon tone-maroon"><i class="fa-solid fa-users"></i></span>
    <h3>Required</h3>
    <div class="value"><?= (int) $jobOrder['personnel_required'] ?></div>
  </div>
  <div class="stat-card">
    <span class="stat-icon tone-green"><i class="fa-solid fa-user-check"></i></span>
    <h3>Assigned</h3>
    <div class="value"><?= (int) $jobOrder['assigned_count'] ?></div>
  </div>
  <div class="stat-card">
    <span class="stat-icon <?= $remaining > 0 ? 'tone-red' : 'tone-neutral' ?>"><i class="fa-solid fa-triangle-exclamation"></i></span>
    <h3>Remaining</h3>
    <div class="value"><?= $remaining ?></div>
  </div>
  <div class="stat-card">
    <span class="stat-icon tone-gold"><i class="fa-solid fa-hourglass-half"></i></span>
    <h3>Days Remaining</h3>
    <div class="value"><?= $jobOrder['end_date'] ? (int) $jobOrder['days_remaining'] : '—' ?></div>
  </div>
</div>

<div class="table-card">
  <div class="table-toolbar"><div class="toolbar-left"><h3 style="margin:0;">Job Order Details</h3></div></div>
  <div style="padding:16px 20px; display:grid; grid-template-columns:repeat(auto-fit,minmax(220px,1fr)); gap:14px;">
    <div><small>Status</small><br><span class="status-badge <?= joStatusClass2($jobOrder['status']) ?>"><?= esc(str_replace('_',' ',$jobOrder['status'])) ?></span></div>
    <div><small>Project</small><br><?= esc($jobOrder['project_name'] ?: '—') ?></div>
    <div><small>Position</small><br><?= esc($jobOrder['position'] ?: '—') ?></div>
    <div><small>Assignment Location</small><br><?= esc($jobOrder['assignment_location'] ?: '—') ?></div>
    <div><small>Supervisor</small><br><?= esc($jobOrder['supervisor'] ?: '—') ?></div>
    <div><small>Period</small><br><?= esc($jobOrder['start_date'] ?: '—') ?> &rarr; <?= esc($jobOrder['end_date'] ?: '—') ?></div>
    <div style="grid-column:1/-1;"><small>Description</small><br><?= esc($jobOrder['description'] ?: '—') ?></div>
  </div>
</div>

<div class="table-card">
  <div class="table-toolbar"><div class="toolbar-left"><h3 style="margin:0;">Assigned Personnel</h3></div></div>
  <div class="personnel-table-scroll">
  <table class="data-table">
    <thead><tr><th>Personnel</th><th>Position</th><th>Location</th><th>Period</th><th>Status</th><th>Actions</th></tr></thead>
    <tbody>
      <?php if (!empty($assignments)): ?>
        <?php foreach ($assignments as $asg): ?>
          <tr>
            <td><a href="<?= base_url('personnel/view/' . $asg['personnel_id']) ?>"><?= esc($asg['full_name']) ?></a><br><small><?= esc($asg['emp_id']) ?></small></td>
            <td><?= esc($asg['position'] ?: '—') ?></td>
            <td><?= esc($asg['assignment_location'] ?: '—') ?></td>
            <td><?= esc($asg['assignment_start_date'] ?: '—') ?> &rarr; <?= esc($asg['assignment_end_date'] ?: '—') ?></td>
            <td><span class="status-badge <?= joStatusClass2($asg['status']) ?>"><?= esc($asg['status']) ?></span></td>
            <td>
              <?php if ($asg['status'] === 'ACTIVE'): ?>
                <form method="post" action="<?= base_url('personnel/assignments/end/' . $asg['id']) ?>" onsubmit="return confirm('End this assignment?')" style="display:contents;">
                  <?= csrf_field() ?>
                  <input type="hidden" name="status" value="ENDED">
                  <button type="submit" class="icon-btn delete" title="End Assignment"><i class="fa-solid fa-circle-xmark"></i></button>
                </form>
              <?php else: ?>
                <small>—</small>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
      <?php else: ?>
        <tr><td colspan="6">No personnel assigned yet.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
  </div>
</div>

<div class="modal" id="assignPersonnelModal">
  <div class="modal-box">
    <h3>Assign Personnel to <?= esc($jobOrder['job_order_number']) ?></h3>
    <form action="<?= site_url('personnel/job-orders/assign/' . $jobOrder['id']) ?>" method="post">
      <?= csrf_field() ?>
      <label>Personnel <span class="required-mark">*</span></label>
      <select name="personnel_id" required>
        <option value="">Select Personnel</option>
        <?php foreach ($availablePersonnel as $p): ?>
          <option value="<?= $p['id'] ?>"><?= esc($p['full_name']) ?> (<?= esc($p['emp_id']) ?>)</option>
        <?php endforeach; ?>
      </select>
      <label>Position</label>
      <input type="text" name="position" value="<?= esc($jobOrder['position']) ?>">
      <label>Assignment Location</label>
      <input type="text" name="assignment_location" value="<?= esc($jobOrder['assignment_location']) ?>">
      <label>Supervisor</label>
      <input type="text" name="supervisor" value="<?= esc($jobOrder['supervisor']) ?>">
      <label>Assignment Start Date</label>
      <input type="date" name="assignment_start_date" value="<?= date('Y-m-d') ?>">
      <label>Assignment End Date</label>
      <input type="date" name="assignment_end_date" value="<?= esc($jobOrder['end_date']) ?>">
      <label>Contract Number</label>
      <input type="text" name="contract_number" placeholder="Optional">
      <?php if ($jobOrder['status'] === 'EXPIRED'): ?>
        <label style="display:flex;align-items:center;gap:8px;margin-top:8px;">
          <input type="checkbox" name="override_expired" value="1" style="width:auto;"> This Job Order has expired — assign anyway
        </label>
      <?php endif; ?>
      <div class="modal-actions">
        <button type="button" onclick="document.getElementById('assignPersonnelModal').style.display='none'">Cancel</button>
        <button type="submit" class="btn-maroon">Assign</button>
      </div>
    </form>
  </div>
</div>

<?= $this->endSection() ?>
