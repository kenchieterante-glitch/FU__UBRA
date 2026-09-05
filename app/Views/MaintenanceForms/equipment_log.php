<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<?php $logList = $logs ?? []; ?>

<div class="page-header">
  <div>
    <h1><?= esc($title ?? 'Equipment Maintenance Log') ?></h1>
    <p class="page-subtitle">F-FAC-PMP-EML-002 — One sheet per department, one row per asset serviced.</p>
  </div>
  <button class="btn-add" onclick="document.getElementById('addModal').style.display='flex'">+ New Log Sheet</button>
</div>

<div class="table-card">
  <div class="tools-table-scroll">
  <table id="mfTable" class="data-table">
    <thead>
      <tr>
        <th>Department</th>
        <th>Date Submitted</th>
        <th>Entries</th>
        <th>Approved by</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php if (!empty($logList)): ?>
        <?php foreach ($logList as $l): ?>
          <tr>
            <td><?= esc($l['department'] ?? '—') ?></td>
            <td><?= !empty($l['date_submitted']) ? esc(date('M j, Y', strtotime($l['date_submitted']))) : '—' ?></td>
            <td><?= count($l['entries']) ?></td>
            <td><?= esc($l['approved_by'] ?? '—') ?></td>
            <td>
              <div class="action-buttons">
                <button type="button" class="icon-btn" onclick="document.getElementById('viewModal<?= $l['id'] ?>').style.display='flex'" title="Open"><i class="fa-solid fa-eye"></i></button>
                <form method="post" action="<?= base_url('maintenance-forms/equipment-log/delete/'.$l['id']) ?>" onsubmit="return confirm('Archive this log sheet?')" style="display:contents;">
                  <?= csrf_field() ?>
                  <button type="submit" class="icon-btn delete" title="Archive"><i class="fa-solid fa-archive"></i></button>
                </form>
              </div>
            </td>
          </tr>
        <?php endforeach; ?>
      <?php else: ?>
        <tr><td colspan="5">No log sheets recorded yet.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
  </div>
</div>

<?php foreach ($logList as $l): ?>
  <div class="modal" id="viewModal<?= $l['id'] ?>">
    <div class="modal-box mf-big-box">
      <h3><?= esc($l['department'] ?? 'Equipment Maintenance Log') ?></h3>
      <p class="mf-meta">Date Submitted: <strong><?= !empty($l['date_submitted']) ? esc(date('M j, Y', strtotime($l['date_submitted']))) : '—' ?></strong></p>

      <div class="tools-table-scroll">
      <table class="mf-entries-table">
        <thead>
          <tr>
            <th>Date</th><th>Asset Name/Model</th><th>Serial No.</th><th>Frequency</th>
            <th>Work Performed</th><th>Status</th><th>Next Due</th><th>Performed By</th><th></th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($l['entries'] as $e): ?>
            <tr>
              <form method="post" action="<?= base_url('maintenance-forms/equipment-log/editEntry/'.$e['id']) ?>" style="display:contents;">
                <?= csrf_field() ?>
                <td><input type="date" name="entry_date" value="<?= esc($e['entry_date'] ?? '') ?>"></td>
                <td><input type="text" name="asset_name" value="<?= esc($e['asset_name'] ?? '') ?>"></td>
                <td><input type="text" name="serial_number" value="<?= esc($e['serial_number'] ?? '') ?>"></td>
                <td><input type="text" name="maintenance_frequency" value="<?= esc($e['maintenance_frequency'] ?? '') ?>"></td>
                <td><input type="text" name="work_description" value="<?= esc($e['work_description'] ?? '') ?>"></td>
                <td><input type="text" name="status" value="<?= esc($e['status'] ?? '') ?>"></td>
                <td><input type="date" name="next_due_date" value="<?= esc($e['next_due_date'] ?? '') ?>"></td>
                <td><input type="text" name="performed_by" value="<?= esc($e['performed_by'] ?? '') ?>"></td>
                <td>
                  <div class="action-buttons">
                    <button type="submit" class="icon-btn" title="Save"><i class="fa-solid fa-floppy-disk"></i></button>
                  </div>
                </td>
              </form>
              <td>
                <form method="post" action="<?= base_url('maintenance-forms/equipment-log/deleteEntry/'.$e['id']) ?>" onsubmit="return confirm('Delete this entry?')" style="display:contents;">
                  <?= csrf_field() ?>
                  <div class="action-buttons">
                    <button type="submit" class="icon-btn delete" title="Delete"><i class="fa-solid fa-trash"></i></button>
                  </div>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
      </div>

      <div class="mf-section-title">Add Entry</div>
      <form method="post" action="<?= base_url('maintenance-forms/equipment-log/addEntry/'.$l['id']) ?>">
        <?= csrf_field() ?>
        <label>Date</label>
        <input type="date" name="entry_date" value="<?= date('Y-m-d') ?>">
        <label>Asset Name/Model</label>
        <input type="text" name="asset_name" required>
        <label>Serial Number</label>
        <input type="text" name="serial_number">
        <label>Maintenance Frequency</label>
        <input type="text" name="maintenance_frequency" placeholder="e.g. Monthly, Quarterly">
        <label>Description of Work Performed</label>
        <input type="text" name="work_description">
        <label>Status</label>
        <input type="text" name="status" placeholder="e.g. Completed, Pending Parts">
        <label>Next Due Date</label>
        <input type="date" name="next_due_date">
        <label>Performed/Checked By</label>
        <input type="text" name="performed_by">
        <div class="modal-actions">
          <button type="submit" class="btn-maroon">Add Entry</button>
        </div>
      </form>

      <div class="mf-section-title">Sign-off</div>
      <form method="post" action="<?= base_url('maintenance-forms/equipment-log/updateHeader/'.$l['id']) ?>">
        <?= csrf_field() ?>
        <label>Reviewed by (Unit Head)</label>
        <input type="text" name="reviewed_by" value="<?= esc($l['reviewed_by'] ?? '') ?>">
        <label>Review Date</label>
        <input type="date" name="reviewed_date" value="<?= esc($l['reviewed_date'] ?? '') ?>">
        <label>Approved by (Facilities Director)</label>
        <input type="text" name="approved_by" value="<?= esc($l['approved_by'] ?? '') ?>">
        <div class="modal-actions">
          <button type="button" onclick="document.getElementById('viewModal<?= $l['id'] ?>').style.display='none'">Close</button>
          <button type="submit" class="btn-maroon">Save Sign-off</button>
        </div>
      </form>
    </div>
  </div>
<?php endforeach; ?>

<div class="modal" id="addModal">
  <div class="modal-box">
    <h3>New Equipment Maintenance Log Sheet</h3>
    <form action="<?= base_url('maintenance-forms/equipment-log/add') ?>" method="post">
      <?= csrf_field() ?>
      <label>Department</label>
      <input type="text" name="department" required>
      <label>Date Submitted</label>
      <input type="date" name="date_submitted" value="<?= date('Y-m-d') ?>">
      <div class="modal-actions">
        <button type="button" onclick="document.getElementById('addModal').style.display='none'">Cancel</button>
        <button type="submit" class="btn-maroon">Create</button>
      </div>
    </form>
  </div>
</div>

<?= $this->endSection() ?>
