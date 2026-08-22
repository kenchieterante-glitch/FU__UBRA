<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<?php
$jobOrders = $jobOrders ?? [];
function joStatusClass($status) {
  return match(strtoupper((string) $status)) {
    'ACTIVE','COMPLETED' => 'status-active',
    'EXPIRING_SOON','PENDING','FOR_RENEWAL','DRAFT' => 'status-pending',
    'EXPIRED','CANCELLED' => 'status-critical',
    default => 'status-inactive',
  };
}
?>

<div class="page-header">
  <div>
    <h1>Job Orders</h1>
    <p class="page-subtitle">Manage project-based Job Orders and their staffing requirements.</p>
  </div>
  <div class="action-buttons">
    <a href="<?= base_url('personnel/monitoring') ?>" class="btn-add" style="background:#fff;color:var(--maroon);border:1px solid var(--maroon);"><i class="bi bi-speedometer2"></i> Monitoring Dashboard</a>
    <button class="btn-add" onclick="document.getElementById('addJobOrderModal').style.display='flex'">+ Add Job Order</button>
  </div>
</div>

<div class="stat-back-bar">
  <a href="<?= base_url('personnel') ?>" class="stat-back-btn"><i class="bi bi-arrow-left"></i> Back to Personnel Management</a>
</div>

<div class="table-card">
  <div class="table-toolbar">
    <div class="toolbar-left">
      <div class="toolbar-search">
        <input type="text" id="jobOrderSearch" class="search-box" placeholder="Search Job Orders..." oninput="filterJobOrderTable()">
        <i class="bi bi-search search-icon"></i>
      </div>
    </div>
  </div>

  <div class="personnel-table-scroll">
  <table id="jobOrderTable" class="data-table">
    <thead>
      <tr>
        <th>Job Order #</th>
        <th>Project / Position</th>
        <th>Location</th>
        <th>Staffing</th>
        <th>Period</th>
        <th>Status</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php if (!empty($jobOrders)): ?>
        <?php foreach ($jobOrders as $jo): ?>
          <tr data-search="<?= esc(strtolower(($jo['job_order_number'] ?? '') . ' ' . ($jo['job_order_title'] ?? '') . ' ' . ($jo['assignment_location'] ?? ''))) ?>">
            <td><a href="<?= base_url('personnel/job-orders/view/' . $jo['id']) ?>"><strong><?= esc($jo['job_order_number']) ?></strong></a></td>
            <td><?= esc($jo['job_order_title']) ?><br><small><?= esc($jo['position'] ?: '') ?></small></td>
            <td><?= esc($jo['assignment_location'] ?: '—') ?></td>
            <td><?= (int) $jo['assigned_count'] ?> / <?= (int) $jo['personnel_required'] ?></td>
            <td><?= esc($jo['start_date'] ?: '—') ?> &rarr; <?= esc($jo['end_date'] ?: '—') ?></td>
            <td><span class="status-badge <?= joStatusClass($jo['status']) ?>"><?= esc(str_replace('_',' ',$jo['status'])) ?></span></td>
            <td>
              <div class="action-buttons">
                <button class="icon-btn" onclick="document.getElementById('editJoModal<?= $jo['id'] ?>').style.display='flex'" title="Edit"><i class="fa-solid fa-pen"></i></button>
                <form method="post" action="<?= base_url('personnel/job-orders/archive/' . $jo['id']) ?>" onsubmit="return confirm('Archive this Job Order?')" style="display:contents;">
                  <?= csrf_field() ?>
                  <button type="submit" class="icon-btn delete" title="Archive"><i class="fa-solid fa-archive"></i></button>
                </form>
              </div>
            </td>
          </tr>

          <div class="modal" id="editJoModal<?= $jo['id'] ?>">
            <div class="modal-box">
              <h3>Edit Job Order</h3>
              <form action="<?= site_url('personnel/job-orders/edit/' . $jo['id']) ?>" method="post">
                <?= csrf_field() ?>
                <label>Job Order Title <span class="required-mark">*</span></label>
                <input type="text" name="job_order_title" value="<?= esc($jo['job_order_title']) ?>" required>
                <label>Project Name</label>
                <input type="text" name="project_name" value="<?= esc($jo['project_name']) ?>">
                <label>Position</label>
                <input type="text" name="position" value="<?= esc($jo['position']) ?>">
                <label>Assignment Location</label>
                <input type="text" name="assignment_location" value="<?= esc($jo['assignment_location']) ?>">
                <label>Personnel Required</label>
                <input type="number" min="1" name="personnel_required" value="<?= (int) $jo['personnel_required'] ?>">
                <label>Supervisor</label>
                <input type="text" name="supervisor" value="<?= esc($jo['supervisor']) ?>">
                <label>Start Date</label>
                <input type="date" name="start_date" value="<?= esc($jo['start_date']) ?>">
                <label>End Date</label>
                <input type="date" name="end_date" value="<?= esc($jo['end_date']) ?>">
                <label>Status</label>
                <select name="status">
                  <?php foreach (['DRAFT','PENDING','ACTIVE','COMPLETED','CANCELLED','FOR_RENEWAL'] as $s): ?>
                    <option value="<?= $s ?>" <?= $jo['status']==$s?'selected':'' ?>><?= esc($s) ?></option>
                  <?php endforeach; ?>
                </select>
                <label>Description</label>
                <input type="text" name="description" value="<?= esc($jo['description']) ?>">
                <label>Remarks</label>
                <input type="text" name="remarks" value="<?= esc($jo['remarks']) ?>">
                <div class="modal-actions">
                  <button type="button" onclick="document.getElementById('editJoModal<?= $jo['id'] ?>').style.display='none'">Cancel</button>
                  <button type="submit" class="btn-maroon">Save Changes</button>
                </div>
              </form>
            </div>
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <tr><td colspan="7">No Job Orders yet.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
  </div>
</div>

<div class="modal" id="addJobOrderModal">
  <div class="modal-box">
    <h3>Add Job Order</h3>
    <form action="<?= site_url('personnel/job-orders/add') ?>" method="post">
      <?= csrf_field() ?>
      <p class="required-note">Fields marked <span class="required-mark">*</span> are required.</p>
      <label>Job Order # <small>(auto-generated if left blank)</small></label>
      <input type="text" name="job_order_number" placeholder="JO-2026-001">
      <label>Job Order Title <span class="required-mark">*</span></label>
      <input type="text" name="job_order_title" required>
      <label>Project Name</label>
      <input type="text" name="project_name">
      <label>Position</label>
      <input type="text" name="position" placeholder="Utility Worker">
      <label>Assignment Location</label>
      <input type="text" name="assignment_location" placeholder="Main Campus">
      <label>Personnel Required</label>
      <input type="number" min="1" name="personnel_required" value="1">
      <label>Supervisor</label>
      <input type="text" name="supervisor">
      <label>Start Date</label>
      <input type="date" name="start_date">
      <label>End Date</label>
      <input type="date" name="end_date">
      <label>Status</label>
      <select name="status">
        <option value="ACTIVE">ACTIVE</option>
        <option value="DRAFT">DRAFT</option>
        <option value="PENDING">PENDING</option>
      </select>
      <label>Description</label>
      <input type="text" name="description">
      <label>Remarks</label>
      <input type="text" name="remarks">
      <div class="modal-actions">
        <button type="button" onclick="document.getElementById('addJobOrderModal').style.display='none'">Cancel</button>
        <button type="submit" class="btn-maroon">Save</button>
      </div>
    </form>
  </div>
</div>

<script>
function filterJobOrderTable() {
  const searchValue = document.getElementById('jobOrderSearch').value.toLowerCase().trim();
  document.querySelectorAll('#jobOrderTable tbody tr[data-search]').forEach(row => {
    const text = (row.dataset.search || '').toLowerCase();
    row.style.display = (!searchValue || text.includes(searchValue)) ? '' : 'none';
  });
}
</script>

<?= $this->endSection() ?>
