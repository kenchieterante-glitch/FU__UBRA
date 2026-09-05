<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<?php $logList = $logs ?? []; ?>

<div class="page-header">
  <div>
    <h1><?= esc($title ?? 'Aircon Inspection Log') ?></h1>
    <p class="page-subtitle">F-FAC-PMP-AIL-003 — One sheet per submission, one row per unit inspected/cleaned.</p>
  </div>
  <button class="btn-add" onclick="document.getElementById('addModal').style.display='flex'">+ New Log Sheet</button>
</div>

<div class="table-card">
  <div class="tools-table-scroll">
  <table id="mfTable" class="data-table">
    <thead>
      <tr>
        <th>Performed by</th>
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
            <td><?= esc($l['performed_by'] ?? '—') ?></td>
            <td><?= !empty($l['date_submitted']) ? esc(date('M j, Y', strtotime($l['date_submitted']))) : '—' ?></td>
            <td><?= count($l['entries']) ?></td>
            <td><?= esc($l['approved_by'] ?? '—') ?></td>
            <td>
              <div class="action-buttons">
                <button type="button" class="icon-btn" onclick="document.getElementById('viewModal<?= $l['id'] ?>').style.display='flex'" title="Open"><i class="fa-solid fa-eye"></i></button>
                <form method="post" action="<?= base_url('maintenance-forms/aircon-log/delete/'.$l['id']) ?>" onsubmit="return confirm('Archive this log sheet?')" style="display:contents;">
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
      <h3>Aircon Inspection — <?= esc($l['performed_by'] ?? 'Log Sheet') ?></h3>
      <p class="mf-meta">Date Submitted: <strong><?= !empty($l['date_submitted']) ? esc(date('M j, Y', strtotime($l['date_submitted']))) : '—' ?></strong></p>

      <div class="tools-table-scroll">
      <table class="mf-entries-table">
        <thead>
          <tr>
            <th>Date</th><th>Department</th><th>Qty</th><th>Room No.</th>
            <th>Aircon Type</th><th>Work Done</th><th>Remarks</th><th></th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($l['entries'] as $e): ?>
            <tr>
              <form method="post" action="<?= base_url('maintenance-forms/aircon-log/editEntry/'.$e['id']) ?>" style="display:contents;">
                <?= csrf_field() ?>
                <td><input type="date" name="entry_date" value="<?= esc($e['entry_date'] ?? '') ?>"></td>
                <td><input type="text" name="department" value="<?= esc($e['department'] ?? '') ?>"></td>
                <td><input type="number" name="qty" value="<?= esc((string) ($e['qty'] ?? '')) ?>" style="width:50px;"></td>
                <td><input type="text" name="room_no" value="<?= esc($e['room_no'] ?? '') ?>"></td>
                <td><input type="text" name="aircon_type" value="<?= esc($e['aircon_type'] ?? '') ?>"></td>
                <td><input type="text" name="work_done" value="<?= esc($e['work_done'] ?? '') ?>"></td>
                <td><input type="text" name="remarks" value="<?= esc($e['remarks'] ?? '') ?>"></td>
                <td>
                  <div class="action-buttons">
                    <button type="submit" class="icon-btn" title="Save"><i class="fa-solid fa-floppy-disk"></i></button>
                  </div>
                </td>
              </form>
              <td>
                <form method="post" action="<?= base_url('maintenance-forms/aircon-log/deleteEntry/'.$e['id']) ?>" onsubmit="return confirm('Delete this entry?')" style="display:contents;">
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
      <form method="post" action="<?= base_url('maintenance-forms/aircon-log/addEntry/'.$l['id']) ?>">
        <?= csrf_field() ?>
        <label>Date</label>
        <input type="date" name="entry_date" value="<?= date('Y-m-d') ?>">
        <label>Department</label>
        <input type="text" name="department">
        <label>Quantity</label>
        <input type="number" name="qty" value="1">
        <label>Room No.</label>
        <input type="text" name="room_no">
        <label>Aircon Type</label>
        <input type="text" name="aircon_type" placeholder="e.g. Window, Split, Inverter">
        <label>Work Done</label>
        <input type="text" name="work_done">
        <label>Remarks</label>
        <input type="text" name="remarks">
        <div class="modal-actions">
          <button type="submit" class="btn-maroon">Add Entry</button>
        </div>
      </form>

      <div class="mf-section-title">Sign-off</div>
      <form method="post" action="<?= base_url('maintenance-forms/aircon-log/updateHeader/'.$l['id']) ?>">
        <?= csrf_field() ?>
        <label>Reviewed by (Maintenance Head)</label>
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
    <h3>New Aircon Inspection Log Sheet</h3>
    <form action="<?= base_url('maintenance-forms/aircon-log/add') ?>" method="post">
      <?= csrf_field() ?>
      <label>Performed by</label>
      <input type="text" name="performed_by" required>
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
