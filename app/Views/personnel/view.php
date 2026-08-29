<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<?php
$person = $person ?? [];
$activeAssignment = $activeAssignment ?? null;
$activeContract = $activeContract ?? null;
$assignmentHistory = $assignmentHistory ?? [];
$documents = $documents ?? [];
$documentTypes = $documentTypes ?? [];
$completeness = $completeness ?? ['required' => 0, 'verified' => 0, 'complete' => false];
$installedExtinguishers = $installedExtinguishers ?? [];
$installedAircon = $installedAircon ?? [];
function pvStatusClass($status) {
  return match(strtoupper((string) $status)) {
    'ACTIVE','VERIFIED','COMPLETED' => 'status-active',
    'EXPIRING_SOON','PENDING','FOR_RENEWAL','TRANSFERRED' => 'status-pending',
    'EXPIRED','REJECTED','TERMINATED','MISSING' => 'status-critical',
    default => 'status-inactive',
  };
}
?>

<div class="page-header">
  <div>
    <h1><?= esc($person['full_name']) ?></h1>
    <p class="page-subtitle"><?= esc($person['emp_id']) ?> &middot; <?= esc($person['position'] ?: 'No position set') ?></p>
  </div>
  <a href="<?= base_url('personnel') ?>" class="btn-add" style="background:#fff;color:var(--maroon);border:1px solid var(--maroon);"><i class="bi bi-arrow-left"></i> Back to Personnel</a>
</div>

<div class="table-card">
  <div class="table-toolbar"><div class="toolbar-left"><h3 style="margin:0;">Personal & Employment Information</h3></div></div>
  <div style="padding:16px 20px; display:grid; grid-template-columns:repeat(auto-fit,minmax(220px,1fr)); gap:14px;">
    <div><small>Email</small><br><?= esc($person['email'] ?: '—') ?></div>
    <div><small>Department</small><br><?= esc($person['department_name'] ?? '—') ?></div>
    <div><small>Employment Type</small><br><?= esc($person['employment_type'] ?? 'Regular') ?></div>
    <div><small>Status</small><br><span class="status-badge <?= pvStatusClass($person['status'] ?? 'Active') ?>"><?= esc($person['status'] ?? 'Active') ?></span></div>
  </div>
</div>

<?php if (($person['employment_type'] ?? 'Regular') === 'JobOrder' || $activeAssignment): ?>
<div class="table-card">
  <div class="table-toolbar"><div class="toolbar-left"><h3 style="margin:0;">Job Order Assignment</h3></div></div>
  <?php if ($activeAssignment): ?>
    <div style="padding:16px 20px; display:grid; grid-template-columns:repeat(auto-fit,minmax(220px,1fr)); gap:14px;">
      <div><small>Job Order</small><br><a href="<?= base_url('personnel/job-orders/view/' . $activeAssignment['job_order_id']) ?>"><?= esc($activeAssignment['job_order_number'] ?? '—') ?></a> &mdash; <?= esc($activeAssignment['job_order_title'] ?? '') ?></div>
      <div><small>Assignment Location</small><br><?= esc($activeAssignment['assignment_location'] ?: '—') ?></div>
      <div><small>Supervisor</small><br><?= esc($activeAssignment['supervisor'] ?: '—') ?></div>
      <div><small>Period</small><br><?= esc($activeAssignment['assignment_start_date'] ?: '—') ?> &rarr; <?= esc($activeAssignment['assignment_end_date'] ?: '—') ?></div>
      <div><small>Contract #</small><br><?= esc($activeContract['contract_number'] ?? '') ?: '—' ?></div>
      <div><small>Status</small><br><span class="status-badge <?= pvStatusClass($activeAssignment['status']) ?>"><?= esc($activeAssignment['status']) ?></span><?php if ($activeContract): ?> <span class="status-badge <?= pvStatusClass($activeContract['contract_status']) ?>"><?= esc(str_replace('_',' ',$activeContract['contract_status'])) ?> contract</span><?php endif; ?></div>
    </div>
  <?php else: ?>
    <p style="padding:16px 20px; margin:0; color:var(--muted, #888);">No active Job Order assignment.</p>
  <?php endif; ?>
</div>

<?php if (!empty($assignmentHistory)): ?>
<div class="table-card">
  <div class="table-toolbar"><div class="toolbar-left"><h3 style="margin:0;">Past Job Order Assignments</h3></div></div>
  <div class="personnel-table-scroll">
  <table class="data-table">
    <thead><tr><th>Job Order</th><th>Period</th><th>Contract #</th><th>Status</th></tr></thead>
    <tbody>
      <?php foreach ($assignmentHistory as $h): ?>
        <tr>
          <td><?= esc($h['job_order_number'] ?? '—') ?></td>
          <td><?= esc($h['assignment_start_date'] ?: '—') ?> &rarr; <?= esc($h['assignment_end_date'] ?: '—') ?></td>
          <td><?= esc($h['contract']['contract_number'] ?? '') ?: '—' ?></td>
          <td><span class="status-badge <?= pvStatusClass($h['status']) ?>"><?= esc($h['status']) ?></span></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  </div>
</div>
<?php endif; ?>
<?php endif; ?>

<div class="table-card">
  <div class="table-toolbar">
    <div class="toolbar-left"><h3 style="margin:0;">Document Requirements (<?= (int) $completeness['verified'] ?>/<?= (int) $completeness['required'] ?> Complete)</h3></div>
    <div class="toolbar-right">
      <button class="btn-add" onclick="document.getElementById('addDocumentModal').style.display='flex'">+ Add Document</button>
    </div>
  </div>
  <div class="personnel-table-scroll">
  <table class="data-table">
    <thead><tr><th>Document Type</th><th>Number</th><th>Issued</th><th>Expires</th><th>Status</th><th>Actions</th></tr></thead>
    <tbody>
      <?php if (!empty($documents)): ?>
        <?php foreach ($documents as $d): ?>
          <tr>
            <td><?= esc($d['document_type_name'] ?? '—') ?></td>
            <td><?= esc($d['document_number'] ?: '—') ?></td>
            <td><?= esc($d['issue_date'] ?: '—') ?></td>
            <td><?= esc($d['expiration_date'] ?: '—') ?></td>
            <td><span class="status-badge <?= pvStatusClass($d['verification_status']) ?>"><?= esc($d['verification_status']) ?></span></td>
            <td>
              <?php if ($d['verification_status'] === 'PENDING'): ?>
                <div class="action-buttons">
                  <form method="post" action="<?= base_url('personnel/documents/verify/' . $d['id']) ?>" style="display:contents;">
                    <?= csrf_field() ?>
                    <button type="submit" class="icon-btn" title="Verify"><i class="fa-solid fa-check"></i></button>
                  </form>
                  <button class="icon-btn delete" title="Reject" onclick="document.getElementById('rejectDocModal<?= $d['id'] ?>').style.display='flex'"><i class="fa-solid fa-xmark"></i></button>
                </div>
                <div class="modal" id="rejectDocModal<?= $d['id'] ?>">
                  <div class="modal-box">
                    <h3>Reject Document</h3>
                    <form method="post" action="<?= base_url('personnel/documents/reject/' . $d['id']) ?>">
                      <?= csrf_field() ?>
                      <label>Rejection Reason <span class="required-mark">*</span></label>
                      <input type="text" name="remarks" required placeholder="e.g. Medical certificate is expired">
                      <div class="modal-actions">
                        <button type="button" onclick="document.getElementById('rejectDocModal<?= $d['id'] ?>').style.display='none'">Cancel</button>
                        <button type="submit" class="btn-maroon">Reject</button>
                      </div>
                    </form>
                  </div>
                </div>
              <?php else: ?>
                <small><?= esc($d['remarks'] ?: '—') ?></small>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
      <?php else: ?>
        <tr><td colspan="6">No documents submitted yet.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
  </div>
</div>

<?php if (!empty($installedExtinguishers) || !empty($installedAircon)): ?>
<div class="table-card">
  <div class="table-toolbar"><div class="toolbar-left"><h3 style="margin:0;">Equipment Installed</h3></div></div>
  <div class="personnel-table-scroll">
  <table class="data-table">
    <thead><tr><th>Type</th><th>Unit</th><th>Location</th><th>Floor</th></tr></thead>
    <tbody>
      <?php foreach ($installedExtinguishers as $fe): ?>
        <tr>
          <td>Fire Extinguisher</td>
          <td><?= esc($fe['unit_id']) ?></td>
          <td><?= esc($fe['location']) ?></td>
          <td><?= esc($fe['floor'] ?: 'Ground Floor') ?></td>
        </tr>
      <?php endforeach; ?>
      <?php foreach ($installedAircon as $ac): ?>
        <tr>
          <td>Aircon Unit</td>
          <td><?= esc($ac['unit_name']) ?></td>
          <td><?= esc($ac['location']) ?></td>
          <td><?= esc($ac['floor'] ?: 'Ground Floor') ?></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  </div>
</div>
<?php endif; ?>

<div class="modal" id="addDocumentModal">
  <div class="modal-box">
    <h3>Add Document</h3>
    <form action="<?= site_url('personnel/documents/add') ?>" method="post">
      <?= csrf_field() ?>
      <input type="hidden" name="personnel_id" value="<?= $person['id'] ?>">
      <label>Document Type <span class="required-mark">*</span></label>
      <select name="document_type_id" required>
        <?php foreach ($documentTypes as $dt): ?>
          <option value="<?= $dt['id'] ?>"><?= esc($dt['name']) ?><?= $dt['is_required'] ? ' (Required)' : '' ?></option>
        <?php endforeach; ?>
      </select>
      <label>Document Number</label>
      <input type="text" name="document_number">
      <label>Issue Date</label>
      <input type="date" name="issue_date">
      <label>Expiration Date</label>
      <input type="date" name="expiration_date">
      <label>Remarks</label>
      <input type="text" name="remarks">
      <div class="modal-actions">
        <button type="button" onclick="document.getElementById('addDocumentModal').style.display='none'">Cancel</button>
        <button type="submit" class="btn-maroon">Save</button>
      </div>
    </form>
  </div>
</div>

<?= $this->endSection() ?>
