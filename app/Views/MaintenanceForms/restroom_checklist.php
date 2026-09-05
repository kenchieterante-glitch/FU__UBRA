<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<?php $checklistList = $checklists ?? []; ?>

<div class="page-header">
  <div>
    <h1><?= esc($title ?? 'Restroom Checklist') ?></h1>
    <p class="page-subtitle">F-FAC-GAL-RC-002 — One sheet per location, one row per cleaning round.</p>
  </div>
  <button class="btn-add" onclick="document.getElementById('addModal').style.display='flex'">+ New Checklist</button>
</div>

<div class="table-card">
  <div class="tools-table-scroll">
  <table id="mfTable" class="data-table">
    <thead>
      <tr>
        <th>Location</th>
        <th>Entries</th>
        <th>Reviewed by</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php if (!empty($checklistList)): ?>
        <?php foreach ($checklistList as $c): ?>
          <tr>
            <td><?= esc($c['location'] ?? '—') ?></td>
            <td><?= count($c['entries']) ?></td>
            <td><?= esc($c['reviewed_by'] ?? '—') ?></td>
            <td>
              <div class="action-buttons">
                <button type="button" class="icon-btn" onclick="document.getElementById('viewModal<?= $c['id'] ?>').style.display='flex'" title="Open"><i class="fa-solid fa-eye"></i></button>
                <form method="post" action="<?= base_url('maintenance-forms/restroom/delete/'.$c['id']) ?>" onsubmit="return confirm('Archive this checklist?')" style="display:contents;">
                  <?= csrf_field() ?>
                  <button type="submit" class="icon-btn delete" title="Archive"><i class="fa-solid fa-archive"></i></button>
                </form>
              </div>
            </td>
          </tr>
        <?php endforeach; ?>
      <?php else: ?>
        <tr><td colspan="4">No checklists recorded yet.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
  </div>
</div>

<?php foreach ($checklistList as $c): ?>
  <div class="modal" id="viewModal<?= $c['id'] ?>">
    <div class="modal-box mf-big-box">
      <h3>Restroom Checklist — <?= esc($c['location'] ?? '—') ?></h3>

      <div class="tools-table-scroll">
      <table class="mf-entries-table">
        <thead>
          <tr>
            <th>Date</th><th>Time</th><th>Trash</th><th>Paper</th><th>Soap</th>
            <th>Floor</th><th>Sink</th><th>Toilet</th><th>Cleaned By</th><th></th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($c['entries'] as $e): ?>
            <tr>
              <form method="post" action="<?= base_url('maintenance-forms/restroom/editEntry/'.$e['id']) ?>" style="display:contents;">
                <?= csrf_field() ?>
                <td><input type="date" name="entry_date" value="<?= esc($e['entry_date'] ?? '') ?>" style="width:120px;"></td>
                <td><input type="time" name="entry_time" value="<?= esc(substr($e['entry_time'] ?? '', 0, 5)) ?>" style="width:90px;"></td>
                <td class="mf-checkbox-cell"><input type="checkbox" name="empty_trash" value="1" <?= !empty($e['empty_trash']) ? 'checked' : '' ?>></td>
                <td class="mf-checkbox-cell"><input type="checkbox" name="refill_paper" value="1" <?= !empty($e['refill_paper']) ? 'checked' : '' ?>></td>
                <td class="mf-checkbox-cell"><input type="checkbox" name="refill_soap" value="1" <?= !empty($e['refill_soap']) ? 'checked' : '' ?>></td>
                <td class="mf-checkbox-cell"><input type="checkbox" name="clean_floor" value="1" <?= !empty($e['clean_floor']) ? 'checked' : '' ?>></td>
                <td class="mf-checkbox-cell"><input type="checkbox" name="clean_sink" value="1" <?= !empty($e['clean_sink']) ? 'checked' : '' ?>></td>
                <td class="mf-checkbox-cell"><input type="checkbox" name="clean_toilet" value="1" <?= !empty($e['clean_toilet']) ? 'checked' : '' ?>></td>
                <td><input type="text" name="cleaned_by" value="<?= esc($e['cleaned_by'] ?? '') ?>" style="width:110px;"></td>
                <td>
                  <div class="action-buttons">
                    <button type="submit" class="icon-btn" title="Save"><i class="fa-solid fa-floppy-disk"></i></button>
                  </div>
                </td>
              </form>
              <td>
                <form method="post" action="<?= base_url('maintenance-forms/restroom/deleteEntry/'.$e['id']) ?>" onsubmit="return confirm('Delete this entry?')" style="display:contents;">
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
      <form method="post" action="<?= base_url('maintenance-forms/restroom/addEntry/'.$c['id']) ?>">
        <?= csrf_field() ?>
        <label>Date</label>
        <input type="date" name="entry_date" value="<?= date('Y-m-d') ?>">
        <label>Time</label>
        <input type="time" name="entry_time" value="<?= date('H:i') ?>">
        <label style="display:flex;align-items:center;gap:8px;"><input type="checkbox" name="empty_trash" value="1"> Empty Trash</label>
        <label style="display:flex;align-items:center;gap:8px;"><input type="checkbox" name="refill_paper" value="1"> Refill Paper</label>
        <label style="display:flex;align-items:center;gap:8px;"><input type="checkbox" name="refill_soap" value="1"> Refill Soap</label>
        <label style="display:flex;align-items:center;gap:8px;"><input type="checkbox" name="clean_floor" value="1"> Clean Floor</label>
        <label style="display:flex;align-items:center;gap:8px;"><input type="checkbox" name="clean_sink" value="1"> Clean Sink</label>
        <label style="display:flex;align-items:center;gap:8px;"><input type="checkbox" name="clean_toilet" value="1"> Clean Toilet</label>
        <label>Cleaned By</label>
        <input type="text" name="cleaned_by">
        <div class="modal-actions">
          <button type="submit" class="btn-maroon">Add Entry</button>
        </div>
      </form>

      <div class="mf-section-title">Sign-off</div>
      <form method="post" action="<?= base_url('maintenance-forms/restroom/updateHeader/'.$c['id']) ?>">
        <?= csrf_field() ?>
        <label>Reviewed by (Janitorial Head)</label>
        <input type="text" name="reviewed_by" value="<?= esc($c['reviewed_by'] ?? '') ?>">
        <label>Review Date</label>
        <input type="date" name="reviewed_date" value="<?= esc($c['reviewed_date'] ?? '') ?>">
        <div class="modal-actions">
          <button type="button" onclick="document.getElementById('viewModal<?= $c['id'] ?>').style.display='none'">Close</button>
          <button type="submit" class="btn-maroon">Save Sign-off</button>
        </div>
      </form>
    </div>
  </div>
<?php endforeach; ?>

<div class="modal" id="addModal">
  <div class="modal-box">
    <h3>New Restroom Checklist</h3>
    <form action="<?= base_url('maintenance-forms/restroom/add') ?>" method="post">
      <?= csrf_field() ?>
      <label>Location</label>
      <input type="text" name="location" required>
      <div class="modal-actions">
        <button type="button" onclick="document.getElementById('addModal').style.display='none'">Cancel</button>
        <button type="submit" class="btn-maroon">Create</button>
      </div>
    </form>
  </div>
</div>

<?= $this->endSection() ?>
